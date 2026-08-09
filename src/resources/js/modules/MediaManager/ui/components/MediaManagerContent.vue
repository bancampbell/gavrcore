<template>
    <div class="media-manager-container" :class="{ 'picker-mode': mode === 'picker' }">
        <MediaHeader
            :current-path-display="currentPathDisplay"
            :folders-count="foldersCount"
            :files-count="filesCount"
            :total-items-count="totalItemsCount"
            @create-folder="openCreateFolderModal"
            @upload="openUploadModal"
        />

        <div class="media-manager-main">
            <div class="media-manager-sidebar">
                <div class="sidebar-header">Каталоги</div>
                <div class="sidebar-content">
                    <div v-if="foldersLoading" class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div>
                    </div>
                    <div v-else-if="rootFolders.length === 0" class="text-center py-4 text-gray-400 text-sm">
                        Папок нет
                    </div>
                    <FolderTree
                        v-for="folder in rootFolders"
                        :key="folder.path"
                        :folder="folder"
                        :current-path="currentPath"
                        :children="folderTree.get(folder.path) || []"
                        :folder-tree="folderTree"
                        :expanded-folders="expandedFolders"
                        @navigate="navigateToFolder"
                        @toggle-expand="toggleFolderExpand"
                    />
                </div>
            </div>

            <ContentPanel
                ref="contentPanelRef"
                :folders="sortedFilteredFolders"
                :files="sortedFilteredFiles"
                :loading="loadingContents"
                :show-search="showSearch"
                :search-query="searchQuery"
                :is-selected="isSelected"
                :can-go-back="canGoBack"
                :is-asc-active="isAscActive"
                :is-desc-active="isDescActive"
                :mode="mode"
                :paginated-data="paginatedData"
                @go-home="goHome"
                @go-back="goBack"
                @sort-asc="setSortOrder('asc')"
                @sort-desc="setSortOrder('desc')"
                @open-search="openSearch"
                @clear-search="clearSearch"
                @update:search-query="(val: string) => searchQuery.value = val"
                @toggle-select="toggleSelect"
                @select-folder="selectFolder"
                @open-folder="openFolder"
                @select-file="selectFileItem"
                @page-change="handlePageChange"
            />

            <InfoPanel
                :selected-item="selectedItem"
                :selected-items-count="selectedItems.length"
                @delete="openDeleteModal"
                @rename="openRenameModal"
                @copy="handleCopy"
            />
        </div>
    </div>

    <CreateFolderModal
        :show="showCreateModal"
        @close="showCreateModal = false"
        @submit="handleCreateFolder"
    />

    <RenameModal
        :show="showRenameModal"
        :current-name="renameItemData?.name || ''"
        @close="showRenameModal = false"
        @confirm="handleRename"
    />

    <DeleteModal
        :show="showDeleteModal"
        :item-name="deleteItemData?.name || ''"
        @close="showDeleteModal = false"
        @confirm="handleDelete"
    />

    <UploadModal
        ref="uploadModalRef"
        :show="showUploadModal"
        :loading="uploadLoading"
        @close="showUploadModal = false"
        @upload="handleUploadWrapper"
    />
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import FolderTree from './FolderTree.vue';
import MediaHeader from './MediaHeader.vue';
import ContentPanel from './ContentPanel.vue';
import InfoPanel from './InfoPanel.vue';
import CreateFolderModal from './CreateFolderModal.vue';
import RenameModal from './RenameModal.vue';
import DeleteModal from './DeleteModal.vue';
import UploadModal from './UploadModal.vue';
import { useMediaManager } from '../composables/useMediaManager';

const props = defineProps<{
    mode: 'full' | 'picker';
    acceptedFiles?: string[];
}>();

const emit = defineEmits<{
    (e: 'select', file: { url: string; name: string; path: string }): void;
    (e: 'fileSelected', file: { url: string; name: string; path: string } | null): void;
    (e: 'loaded'): void;
}>();

const toast = useToast();

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    if (type === 'success') {
        toast.success(message);
    } else {
        toast.error(message);
    }
};

const expandedFolders = ref<string[]>([]);
const contentPanelRef = ref<InstanceType<typeof ContentPanel> | null>(null);
const uploadModalRef = ref<InstanceType<typeof UploadModal> | null>(null);
let dataLoaded = false;

const {
    currentPath,
    showCreateModal,
    showRenameModal,
    showDeleteModal,
    showUploadModal,
    selectedItems,
    selectedItem,
    renameItemData,
    deleteItemData,
    uploadLoading,
    foldersLoading,
    loadingContents,
    rootFolders,
    folderTree,
    sortedFilteredFolders,
    sortedFilteredFiles,
    currentPathDisplay,
    foldersCount,
    filesCount,
    totalItemsCount,
    canGoBack,
    isAscActive,
    isDescActive,
    paginatedData,
    searchQuery,
    showSearch,
    loadData,
    loadFolders,
    navigateToFolder,
    goBack,
    goHome,
    openCreateFolderModal,
    handleCreateFolder,
    openRenameModal,
    handleRename,
    openDeleteModal,
    handleDelete,
    handleCopy,
    openUploadModal,
    handleUpload,
    clearSearch,
    openSearch,
    setSortOrder,
    toggleSelect,
    selectFolder,
    openFolder,
    selectFileItem,
    selectedFileForPicker,
    isSelected,
} = useMediaManager(
    showNotification,
    props.mode,
    props.acceptedFiles,
);

const handlePageChange = (page: number) => {
    loadData(page);
};

const toggleFolderExpand = (folderPath: string) => {
    const index = expandedFolders.value.indexOf(folderPath);
    if (index > -1) {
        expandedFolders.value.splice(index, 1);
    } else {
        expandedFolders.value.push(folderPath);
    }
};

const handleUploadWrapper = async (files: FileList) => {
    const success = await handleUpload(files);
    if (success) {
        uploadModalRef.value?.clearFiles();
    }
};

const confirmSelection = () => {
    if (selectedFileForPicker.value) {
        emit('select', selectedFileForPicker.value);
    }
};

defineExpose({
    confirmSelection,
    selectedFileForPicker,
    getSelectedFile: () => selectedFileForPicker.value,
});

onMounted(async () => {
    if (!dataLoaded) {
        await loadFolders();
        await loadData();
        dataLoaded = true;
        emit('loaded');
    }
});

watch(currentPath, async () => {
    await loadData(1);
});
</script>
