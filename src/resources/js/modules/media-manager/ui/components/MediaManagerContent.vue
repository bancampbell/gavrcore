<template>
    <div class="media-manager-container" :class="{ 'picker-mode': mode === 'picker' }">
        <MediaHeader
            :current-path-display="currentPathDisplay"
            :folders-count="foldersCount"
            :files-count="filesCount"
            @create-folder="openCreateFolderModal"
            @upload="openUploadModal"
        />

        <div class="media-manager-main">
            <div class="media-manager-sidebar">
                <div class="sidebar-header">Каталоги</div>
                <div class="sidebar-content">
                    <div v-if="loadingFolders" class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div>
                    </div>
                    <FolderTree
                        v-for="folder in rootFolders"
                        :key="folder.path.toString()"
                        :folder="folder"
                        :current-path="currentPath"
                        :all-folders="allFolders"
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
                :is-selected="(path) => selectedItems.includes(path) || selectedFileForPicker?.path === path"
                :can-go-back="canGoBack"
                :is-asc-active="isAscActive"
                :is-desc-active="isDescActive"
                :mode="mode"
                @go-home="goHome"
                @go-back="goBack"
                @sort-asc="setSortOrder('asc')"
                @sort-desc="setSortOrder('desc')"
                @open-search="openSearch"
                @clear-search="clearSearch"
                @update:search-query="(val) => searchQuery = val"
                @toggle-select="toggleSelect"
                @select-folder="selectFolder"
                @open-folder="openFolder"
                @select-file="selectFileItem"
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
        @upload="handleUpload"
    />
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { useToast } from '@/composables/useToast';
import FolderTree from './FolderTree.vue';
import MediaHeader from './MediaHeader.vue';
import ContentPanel from './ContentPanel.vue';
import InfoPanel from './InfoPanel.vue';
import CreateFolderModal from './CreateFolderModal.vue';
import RenameModal from './RenameModal.vue';
import DeleteModal from './DeleteModal.vue';
import UploadModal from './UploadModal.vue';
import { useMediaManager } from '@/modules/media-manager/ui/composables/useMediaManager';
import type { MediaItem } from '@/modules/media-manager/domain/entities/MediaItem';

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
const isSelectingFile = ref(false);
const contentPanelRef = ref<InstanceType<typeof ContentPanel> | null>(null);
const uploadModalRef = ref<InstanceType<typeof UploadModal> | null>(null);
let dataLoaded = false;

const {
    allFolders,
    currentPath,
    showCreateModal,
    showRenameModal,
    showDeleteModal,
    showUploadModal,
    showSearch,
    searchQuery,
    selectedItems,
    selectedItem,
    renameItemData,
    deleteItemData,
    uploadLoading,
    loadingFolders,
    loadingContents,
    rootFolders,
    sortedFilteredFolders,
    sortedFilteredFiles,
    currentPathDisplay,
    foldersCount,
    filesCount,
    canGoBack,
    isAscActive,
    isDescActive,
    loadData,
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
    toggleSelect: originalToggleSelect,
    selectFolder,
    openFolder,
    selectFileItem: originalSelectFileItem,
    selectedFileForPicker,
    contents,
} = useMediaManager(
    showNotification,
    props.mode,
    props.acceptedFiles,
);

const scrollToSelectedFile = () => {
    if (!selectedFileForPicker.value?.path) return;
    nextTick(() => {
        const selectedElement = document.querySelector(`[data-file-path="${selectedFileForPicker.value.path}"]`);
        if (selectedElement) {
            selectedElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
};

const expandFolderPath = (folderPath: string) => {
    if (!folderPath) return;
    const parts = folderPath.split('/');
    let currentPathAcc = '';
    for (const part of parts) {
        currentPathAcc = currentPathAcc ? `${currentPathAcc}/${part}` : part;
        if (!expandedFolders.value.includes(currentPathAcc)) {
            expandedFolders.value.push(currentPathAcc);
        }
    }
};

const toggleFolderExpand = (folderPath: string) => {
    const index = expandedFolders.value.indexOf(folderPath);
    if (index > -1) {
        expandedFolders.value.splice(index, 1);
    } else {
        expandedFolders.value.push(folderPath);
    }
};

const selectFileByUrl = async (url: string) => {
    if (!url || props.mode !== 'picker' || isSelectingFile.value) return;
    isSelectingFile.value = true;
    try {
        const filePath = url.replace('/storage/uploads/', '');
        const lastSlashIndex = filePath.lastIndexOf('/');
        const folderPath = lastSlashIndex > 0 ? filePath.substring(0, lastSlashIndex) : '';

        if (folderPath) {
            expandFolderPath(folderPath);
            await navigateToFolder(folderPath);
            await nextTick();
            await new Promise(resolve => setTimeout(resolve, 200));
        } else {
            if (currentPath.value !== '') {
                await navigateToFolder('');
                await nextTick();
                await new Promise(resolve => setTimeout(resolve, 200));
            }
        }

        const file = contents.value.find(f => f.type === 'file' && f.path.toString() === filePath);
        if (file) {
            const fileData = { url: `/storage/uploads/${file.path.toString()}`, name: file.name, path: file.path.toString() };
            selectedItems.value = [];
            selectedFileForPicker.value = fileData;
            originalToggleSelect(file.path.toString(), file);
            emit('fileSelected', fileData);
            scrollToSelectedFile();
        }
    } finally {
        isSelectingFile.value = false;
    }
};

const toggleSelect = (path: string, item: MediaItem) => {
    if (props.mode === 'picker' && item.type === 'file') {
        const isSelected = selectedItems.value.includes(path);
        if (!isSelected) {
            selectedItems.value = [];
            selectedFileForPicker.value = null;
            const fileData = { url: `/storage/uploads/${item.path.toString()}`, name: item.name, path: item.path.toString() };
            selectedFileForPicker.value = fileData;
            originalToggleSelect(path, item);
            emit('fileSelected', fileData);
            setTimeout(() => scrollToSelectedFile(), 100);
        } else {
            selectedFileForPicker.value = null;
            originalToggleSelect(path, item);
            emit('fileSelected', null);
        }
    } else {
        originalToggleSelect(path, item);
    }
};

const selectFileItem = (item: MediaItem) => {
    if (!item) return;
    if (props.mode === 'picker') {
        const fileData = { url: `/storage/uploads/${item.path.toString()}`, name: item.name, path: item.path.toString() };
        if (selectedFileForPicker.value?.path === item.path.toString()) {
            selectedFileForPicker.value = null;
            if (selectedItems.value.includes(item.path.toString())) {
                originalToggleSelect(item.path.toString(), item);
            }
            emit('fileSelected', null);
            return;
        }
        selectedItems.value = [];
        selectedFileForPicker.value = fileData;
        originalToggleSelect(item.path.toString(), item);
        emit('fileSelected', fileData);
        setTimeout(() => scrollToSelectedFile(), 100);
    } else {
        originalSelectFileItem(item);
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
    selectFileByUrl,
    getSelectedFile: () => selectedFileForPicker.value,
});

onMounted(async () => {
    if (!dataLoaded) {
        await loadData();
        dataLoaded = true;
        emit('loaded');
    }
});
</script>
