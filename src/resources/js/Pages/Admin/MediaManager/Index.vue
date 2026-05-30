<template>
    <EmptyLayout :user="user">
        <div class="flex flex-col min-h-screen">
            <MediaHeader
                :current-path-display="currentPathDisplay"
                :folders-count="foldersCount"
                :files-count="filesCount"
                @create-folder="openCreateFolderModal"
                @upload="openUploadModal"
            />

            <div class="flex-1 flex overflow-hidden">
                <div class="w-64 border-r border-gray-200 bg-white flex flex-col overflow-hidden">
                    <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 text-sm font-medium text-gray-600">
                        Каталоги
                    </div>
                    <div class="flex-1 overflow-y-auto p-2">
                        <div v-if="loadingFolders" class="text-center py-4">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div>
                        </div>
                        <FolderTree
                            v-for="folder in rootFolders"
                            :key="folder.path"
                            :folder="folder"
                            :current-path="currentPath"
                            :all-folders="allFolders"
                            @navigate="navigateToFolder"
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

            <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 text-center text-xs text-gray-500">
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
import { onMounted } from 'vue';
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

const props = defineProps<{
    user: User;
}>();

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
    toggleSelect,
    selectFolder,
    selectFileItem
} = useMediaManager();

onMounted(() => {
    loadData();
});
</script>
