<!-- resources/js/Pages/Admin/MediaManager/Index.vue -->
<template>
    <Head>
        <title>{{ title }}</title>
    </Head>
    <div v-if="mode === 'picker'" class="media-manager-picker-wrapper">
        <div class="media-manager-container picker-mode">
            <MediaHeader
                :current-path-display="currentPathDisplay"
                :folders-count="foldersCount"
                :files-count="filesCount"
                @create-folder="openCreateFolderModal"
                @upload="openUploadModal"
            />

            <div class="media-manager-main">
                <div class="media-manager-sidebar">
                    <div class="sidebar-header">
                        Каталоги
                    </div>
                    <div class="sidebar-content">
                        <div v-if="loadingFolders" class="text-center py-4">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div>
                        </div>
                        <FolderTree
                            v-for="folder in rootFolders"
                            :key="folder.path"
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
                    @select-file="selectFileItem"
                />

                <InfoPanel
                    :selected-item="selectedItem"
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
            :show="showUploadModal"
            :loading="uploadLoading"
            @close="showUploadModal = false"
            @upload="handleUpload"
        />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </div>

    <EmptyLayout v-else :user="user">
        <div class="media-manager-container">
            <MediaHeader
                :current-path-display="currentPathDisplay"
                :folders-count="foldersCount"
                :files-count="filesCount"
                @create-folder="openCreateFolderModal"
                @upload="openUploadModal"
            />

            <div class="media-manager-main">
                <div class="media-manager-sidebar">
                    <div class="sidebar-header">
                        Каталоги
                    </div>
                    <div class="sidebar-content">
                        <div v-if="loadingFolders" class="text-center py-4">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div>
                        </div>
                        <FolderTree
                            v-for="folder in rootFolders"
                            :key="folder.path"
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
                    :folders="sortedFilteredFolders"
                    :files="sortedFilteredFiles"
                    :loading="loadingContents"
                    :show-search="showSearch"
                    :search-query="searchQuery"
                    :is-selected="(path) => selectedItems.includes(path)"
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
                    @select-file="selectFileItem"
                />

                <InfoPanel
                    :selected-item="selectedItem"
                    @delete="openDeleteModal"
                    @rename="openRenameModal"
                    @copy="handleCopy"
                />
            </div>

            <div class="media-manager-footer">
                GavrCore CMS — © 2026
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
            :show="showUploadModal"
            :loading="uploadLoading"
            @close="showUploadModal = false"
            @upload="handleUpload"
        />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { onMounted, watch, ref, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import EmptyLayout from '../../../layouts/EmptyLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import FolderTree from './components/FolderTree.vue';
import MediaHeader from './components/MediaHeader.vue';
import ContentPanel from './components/ContentPanel.vue';
import InfoPanel from './components/InfoPanel.vue';
import CreateFolderModal from './components/CreateFolderModal.vue';
import RenameModal from './components/RenameModal.vue';
import DeleteModal from './components/DeleteModal.vue';
import UploadModal from './components/UploadModal.vue';
import { useMediaManager } from './composables/useMediaManager';
import type { User } from '../../../types';
import type { MediaItem } from './composables/useSelection';

const props = defineProps<{
    user: User;
    title?: string;
    mode?: 'full' | 'picker';
    selectedUrl?: string;
    acceptedFiles?: string[];
}>();

const emit = defineEmits<{
    (e: 'select', file: { url: string; name: string; path: string }): void;
    (e: 'fileSelected', file: { url: string; name: string; path: string } | null): void;
}>();

const expandedFolders = ref<string[]>([]);
const isSelectingFile = ref(false);
const contentPanelRef = ref<InstanceType<typeof ContentPanel> | null>(null);

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
    notification,
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
    selectFileItem: originalSelectFileItem,
    getSelectedFile,
    clearSelectedFile,
    selectedFileForPicker,
    contents
} = useMediaManager(props.mode || 'full', props.acceptedFiles);

const scrollToSelectedFile = () => {
    if (!selectedFileForPicker.value?.path) return;

    nextTick(() => {
        if (contentPanelRef.value) {
            contentPanelRef.value.scrollToFile(selectedFileForPicker.value.path);
        } else {
            const selectedElement = document.querySelector(`[data-file-path="${selectedFileForPicker.value.path}"]`);
            if (selectedElement) {
                selectedElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
};

const expandFolderPath = (folderPath: string) => {
    if (!folderPath) return;

    const parts = folderPath.split('/');
    let currentPathAcc = '';

    for (const part of parts) {
        if (currentPathAcc) {
            currentPathAcc += '/' + part;
        } else {
            currentPathAcc = part;
        }

        if (!expandedFolders.value.includes(currentPathAcc)) {
            expandedFolders.value.push(currentPathAcc);
        }
    }
};

const selectFileByUrl = async (url: string) => {
    if (!url || props.mode !== 'picker' || isSelectingFile.value) return;

    isSelectingFile.value = true;

    try {
        let filePath = url;
        if (url.startsWith('/storage/uploads/')) {
            filePath = url.replace('/storage/uploads/', '');
        }

        const lastSlashIndex = filePath.lastIndexOf('/');
        const folderPath = lastSlashIndex > 0 ? filePath.substring(0, lastSlashIndex) : '';

        if (folderPath) {
            expandFolderPath(folderPath);
            await navigateToFolder(folderPath);
            await nextTick();
            await new Promise(resolve => setTimeout(resolve, 200));

            const file = contents.value.find(f => f.type === 'file' && f.path === filePath);

            if (file) {
                const fileData = {
                    url: `/storage/uploads/${file.path}`,
                    name: file.name,
                    path: file.path
                };

                selectedFileForPicker.value = fileData;

                if (!selectedItems.value.includes(file.path)) {
                    originalToggleSelect(file.path, file);
                }

                emit('fileSelected', fileData);
                scrollToSelectedFile();
            }
        } else {
            if (currentPath.value !== '') {
                await navigateToFolder('');
                await nextTick();
                await new Promise(resolve => setTimeout(resolve, 200));
            }

            const file = contents.value.find(f => f.type === 'file' && f.path === filePath);

            if (file) {
                const fileData = {
                    url: `/storage/uploads/${file.path}`,
                    name: file.name,
                    path: file.path
                };

                selectedFileForPicker.value = fileData;

                if (!selectedItems.value.includes(file.path)) {
                    originalToggleSelect(file.path, file);
                }

                emit('fileSelected', fileData);
                scrollToSelectedFile();
            }
        }
    } finally {
        isSelectingFile.value = false;
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

watch([() => props.selectedUrl, () => contents.value.length], async ([newUrl, hasContents]) => {
    if (newUrl && props.mode === 'picker' && hasContents > 0 && !isSelectingFile.value) {
        await nextTick();
        await selectFileByUrl(newUrl as string);
    }
}, { immediate: true });

watch(currentPath, async () => {
    if (props.selectedUrl && props.mode === 'picker' && !isSelectingFile.value) {
        await nextTick();
        await selectFileByUrl(props.selectedUrl);
    }
});

const toggleSelect = (path: string, item: MediaItem) => {
    if (props.mode === 'picker') {
        if (item.type === 'file') {
            const isSelected = selectedItems.value.includes(path);

            if (!isSelected) {
                const fileData = {
                    url: `/storage/uploads/${item.path}`,
                    name: item.name,
                    path: item.path
                };
                selectedFileForPicker.value = fileData;
                originalToggleSelect(path, item);
                emit('fileSelected', fileData);
                setTimeout(() => scrollToSelectedFile(), 100);
            } else {
                selectedFileForPicker.value = null;
                originalToggleSelect(path, item);
                emit('fileSelected', null);
            }
        }
    } else {
        originalToggleSelect(path, item);
    }
};

const selectFileItem = (item: MediaItem) => {
    if (!item) return;
    if (props.mode === 'picker') {
        const fileData = {
            url: `/storage/uploads/${item.path}`,
            name: item.name,
            path: item.path
        };

        if (selectedFileForPicker.value?.path === item.path) {
            selectedFileForPicker.value = null;
            if (selectedItems.value.includes(item.path)) {
                originalToggleSelect(item.path, item);
            }
            emit('fileSelected', null);
        } else {
            selectedFileForPicker.value = fileData;
            if (!selectedItems.value.includes(item.path)) {
                originalToggleSelect(item.path, item);
            }
            emit('fileSelected', fileData);
            setTimeout(() => scrollToSelectedFile(), 100);
        }
    } else {
        originalSelectFileItem(item);
    }
};

const confirmSelection = () => {
    if (selectedFileForPicker.value) {
        emit('select', selectedFileForPicker.value);
        clearSelectedFile();
    }
};

watch(selectedFileForPicker, (newFile) => {
    if (props.mode === 'picker') {
        emit('fileSelected', newFile);
    }
});

defineExpose({
    confirmSelection,
    getSelectedFile,
    selectedFileForPicker
});

onMounted(async () => {
    await loadData();

    if (props.selectedUrl && props.mode === 'picker') {
        await nextTick();
        await selectFileByUrl(props.selectedUrl);
    }
});
</script>

<style scoped>
.media-manager-container {
    display: flex;
    flex-direction: column;
    background: white;
    height: 100%;
}

.media-manager-main {
    flex: 1;
    display: flex;
    overflow: hidden;
    min-height: 0;
}

.media-manager-sidebar {
    width: 256px;
    border-right: 1px solid #e5e7eb;
    background: white;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-header {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    font-size: 0.875rem;
    font-weight: 500;
    color: #4b5563;
    flex-shrink: 0;
}

.sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;
    min-height: 0;
}

.media-manager-footer {
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    padding: 0.75rem 1.5rem;
    text-align: center;
    font-size: 0.75rem;
    color: #6b7280;
    flex-shrink: 0;
}

.media-manager-picker-wrapper {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.media-manager-container.picker-mode {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.picker-mode .media-manager-sidebar {
    width: 240px;
}

.picker-mode .sidebar-header {
    padding: 0.375rem 0.75rem;
    font-size: 0.813rem;
}
</style>
