<template>
    <EmptyLayout :user="user">
        <div class="flex flex-col min-h-screen">
            <!-- Шапка -->
            <div class="bg-white border-b border-gray-200">
                <div class="px-6 py-4">
                    <h1 class="text-xl font-semibold text-gray-800">Браузер файлов</h1>
                </div>

                <div class="px-6 pb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm">
                        <Link href="/admin/dashboard">
                            <svg class="w-4 h-4 text-gray-500 hover:text-blue-600 cursor-pointer transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </Link>
                        <span class="text-gray-600">{{ currentPathDisplay }}</span>
                        <span class="text-gray-400">({{ foldersCount }} папок, {{ filesCount }} файлов)</span>
                    </div>

                    <div class="flex gap-2">
                        <button @click="openCreateFolderModal" class="px-4 py-1.5 text-sm bg-gray-100 text-gray-700 rounded border border-gray-300 hover:bg-gray-200 transition">
                            Новая папка
                        </button>
                        <button class="px-4 py-1.5 text-sm bg-gray-100 text-gray-700 rounded border border-gray-300 hover:bg-gray-200 transition">
                            Загрузить
                        </button>
                        <button class="px-4 py-1.5 text-sm bg-gray-100 text-gray-700 rounded border border-gray-300 hover:bg-gray-200 transition">
                            Справка
                        </button>
                    </div>
                </div>
            </div>

            <!-- Основной контент: 3 колонки через Flex -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Левая колонка - дерево папок -->
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

                <!-- Центральная колонка - список файлов -->
                <div class="flex-1 bg-white flex flex-col overflow-hidden">
                    <div class="px-4 py-1.5 border-b border-gray-200 bg-gray-50 flex items-center gap-2">
                        <button
                            @click="goHome"
                            class="p-1 rounded hover:bg-gray-100 transition"
                            title="В корень"
                        >
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </button>
                        <button
                            @click="goBack"
                            :disabled="!canGoBack"
                            class="p-1 rounded hover:bg-gray-100 transition disabled:opacity-30 disabled:cursor-not-allowed"
                            title="Назад"
                        >
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-2">
                        <div v-if="loadingContents" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                        </div>

                        <div v-else class="space-y-0.5">
                            <!-- Папки -->
                            <div
                                v-for="item in folders"
                                :key="item.path"
                                class="flex items-center py-1 px-2 rounded-md transition-colors hover:bg-[#e6f0fa] group cursor-pointer"
                                :class="{ 'bg-[#e6f0fa]': selectedItems.includes(item.path) }"
                                @click="selectFolder(item)"
                            >
                                <input
                                    type="checkbox"
                                    :checked="selectedItems.includes(item.path)"
                                    @change="toggleSelect(item.path, item)"
                                    class="mr-2 w-3.5 h-3.5 rounded border-gray-300 cursor-pointer"
                                    @click.stop
                                />
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="#D2B073" stroke="#D2B073" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <span class="text-sm flex-1" :class="selectedItems.includes(item.path) ? 'text-[#333] font-medium' : 'text-gray-700'">{{ item.name }}</span>
                            </div>

                            <!-- Файлы -->
                            <div
                                v-for="item in files"
                                :key="item.path"
                                class="flex items-center py-1 px-2 rounded-md transition-colors hover:bg-[#e6f0fa] group cursor-pointer"
                                :class="{ 'bg-[#e6f0fa]': selectedItems.includes(item.path) }"
                                @click="selectFile(item)"
                            >
                                <input
                                    type="checkbox"
                                    :checked="selectedItems.includes(item.path)"
                                    @change="toggleSelect(item.path, item)"
                                    class="mr-2 w-3.5 h-3.5 rounded border-gray-300 cursor-pointer"
                                    @click.stop
                                />
                                <span class="text-base mr-2 flex-shrink-0">{{ getFileIcon(item.name) }}</span>
                                <span class="text-sm flex-1" :class="selectedItems.includes(item.path) ? 'text-[#333] font-medium' : 'text-gray-700'">{{ item.name }}</span>
                                <span class="text-xs text-gray-400 flex-shrink-0">{{ formatFileSize(item.size) }}</span>
                            </div>
                        </div>

                        <div v-if="!loadingContents && contents.length === 0" class="text-center py-12 text-gray-400">
                            Папка пуста
                        </div>
                    </div>
                </div>

                <!-- Правая колонка - информация -->
                <div class="w-72 border-l border-gray-200 bg-white flex flex-col overflow-hidden">
                    <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 text-sm font-medium text-gray-600">
                        Подробная информация
                    </div>
                    <div class="flex-1 overflow-y-auto p-3">
                        <div v-if="selectedItem" class="space-y-4">
                            <div>
                                <h3 class="font-medium text-gray-800 text-sm break-all">{{ selectedItem.name }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ selectedItem.type === 'folder' ? 'Папка' : 'Файл' }}</p>
                                <p v-if="selectedItem.modified" class="text-xs text-gray-400 mt-1">Изменено: {{ formatDate(selectedItem.modified) }}</p>
                                <p v-if="selectedItem.type === 'file' && selectedItem.size" class="text-xs text-gray-400">Размер: {{ formatFileSize(selectedItem.size) }}</p>
                            </div>
                            <div class="flex gap-2 pt-3 border-t border-gray-200">
                                <button @click="openDeleteModal" class="p-1.5 rounded hover:bg-gray-100 transition" title="Удалить">
                                    <svg class="w-4 h-4 text-gray-500 hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                <button @click="openRenameModal" class="p-1.5 rounded hover:bg-gray-100 transition" title="Переименовать">
                                    <svg class="w-4 h-4 text-gray-500 hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="copySelectedItem" class="p-1.5 rounded hover:bg-gray-100 transition" title="Копировать">
                                    <svg class="w-4 h-4 text-gray-500 hover:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-400 py-12">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm">Выберите файл или папку</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Футер -->
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 text-center text-xs text-gray-500">
                GavrCore CMS — © 2026
            </div>
        </div>

        <!-- Модалка создания папки -->
        <div v-if="showCreateModal" class="modal-overlay">
            <div class="modal-content">
                <h2 class="text-lg font-semibold mb-4">Создать новую папку</h2>
                <input
                    v-model="newFolderName"
                    type="text"
                    placeholder="Название папки"
                    class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    @keyup.enter="createFolder"
                />
                <div class="flex justify-end gap-2">
                    <button @click="showCreateModal = false" class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">Отмена</button>
                    <button @click="createFolder" :disabled="!newFolderName.trim()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">Создать</button>
                </div>
            </div>
        </div>

        <!-- Модалка переименования -->
        <div v-if="showRenameModal" class="modal-overlay">
            <div class="modal-content">
                <h2 class="text-lg font-semibold mb-4">Переименовать</h2>
                <input
                    v-model="newItemName"
                    type="text"
                    placeholder="Новое имя"
                    class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    @keyup.enter="confirmRename"
                />
                <div class="flex justify-end gap-2">
                    <button @click="showRenameModal = false" class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">Отмена</button>
                    <button @click="confirmRename" :disabled="!newItemName.trim()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">Переименовать</button>
                </div>
            </div>
        </div>

        <!-- Модалка подтверждения удаления -->
        <div v-if="showDeleteModal" class="modal-overlay">
            <div class="modal-content">
                <h2 class="text-lg font-semibold mb-4">Подтверждение удаления</h2>
                <p class="text-sm text-gray-600 mb-6">
                    Вы уверены, что хотите удалить "{{ deleteItem?.name }}"?
                    Это действие нельзя отменить.
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">
                        Отмена
                    </button>
                    <button @click="confirmDelete" class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                        Удалить
                    </button>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import EmptyLayout from '../../../layouts/EmptyLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import FolderTree from './components/FolderTree.vue';
import type { User } from '../../../types';

interface MediaItem {
    name: string;
    path: string;
    type: 'folder' | 'file';
    size?: number;
    mime_type?: string;
    modified?: number;
}

const props = defineProps<{
    user: User;
}>();

const loadingFolders = ref(false);
const loadingContents = ref(false);
const allFolders = ref<MediaItem[]>([]);
const contents = ref<MediaItem[]>([]);
const selectedItem = ref<MediaItem | null>(null);
const currentPath = ref('');
const showCreateModal = ref(false);
const showRenameModal = ref(false);
const showDeleteModal = ref(false);
const renameItem = ref<MediaItem | null>(null);
const deleteItem = ref<MediaItem | null>(null);
const newItemName = ref('');
const newFolderName = ref('');
const selectedItems = ref<string[]>([]);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });

const rootFolders = computed(() => allFolders.value.filter((f: MediaItem) => !f.path.includes('/')));
const folders = computed(() => contents.value.filter((i: MediaItem) => i.type === 'folder'));
const files = computed(() => contents.value.filter((i: MediaItem) => i.type === 'file'));

const currentPathDisplay = computed(() => {
    return currentPath.value || '/';
});

const foldersCount = computed(() => folders.value.length);
const filesCount = computed(() => files.value.length);

const canGoBack = computed(() => {
    return currentPath.value !== '';
});

const formatDate = (timestamp: number): string => {
    if (!timestamp) return '';
    const date = new Date(timestamp * 1000);
    return date.toLocaleDateString('ru-RU') + ', ' + date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

const goBack = () => {
    if (!currentPath.value) return;
    const parts = currentPath.value.split('/');
    parts.pop();
    currentPath.value = parts.join('');
    loadContents();
};

const goHome = () => {
    currentPath.value = '';
    loadContents();
};

const toggleSelect = (path: string, item: MediaItem) => {
    const index = selectedItems.value.indexOf(path);
    if (index === -1) {
        selectedItems.value.push(path);
        selectedItem.value = item;
    } else {
        selectedItems.value.splice(index, 1);
        if (selectedItem.value?.path === path) {
            selectedItem.value = null;
        }
    }
};

const selectFolder = (item: MediaItem) => {
    selectedItem.value = item;
    if (!selectedItems.value.includes(item.path)) {
        selectedItems.value.push(item.path);
    }
    navigateToFolder(item.path);
};

const selectFile = (item: MediaItem) => {
    selectedItem.value = item;
    if (!selectedItems.value.includes(item.path)) {
        selectedItems.value.push(item.path);
    }
};

const openDeleteModal = () => {
    if (!selectedItem.value) return;
    deleteItem.value = selectedItem.value;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!deleteItem.value) return;

    try {
        await axios.delete('/admin/media/item', {
            data: { path: deleteItem.value.path },
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        showNotification('Удалено успешно', 'success');
        showDeleteModal.value = false;
        deleteItem.value = null;
        selectedItem.value = null;
        loadFolders();
        loadContents();
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
    }
};

const openRenameModal = () => {
    if (!selectedItem.value) return;
    renameItem.value = selectedItem.value;
    newItemName.value = selectedItem.value.name;
    showRenameModal.value = true;
};

const confirmRename = async () => {
    if (!renameItem.value || !newItemName.value.trim()) return;

    try {
        await axios.post('/admin/media/rename', {
            old_path: renameItem.value.path,
            new_name: newItemName.value
        }, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        showNotification('Переименовано успешно', 'success');
        showRenameModal.value = false;
        renameItem.value = null;
        newItemName.value = '';
        loadFolders();
        loadContents();
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка переименования', 'error');
    }
};

const copySelectedItem = async () => {
    if (!selectedItem.value) return;

    try {
        await axios.post('/admin/media/copy', {
            path: selectedItem.value.path
        }, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        showNotification('Скопировано успешно', 'success');
        loadFolders();
        loadContents();
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка копирования', 'error');
    }
};

const formatFileSize = (bytes: number): string => {
    if (!bytes || bytes === 0) return '';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileIcon = (filename: string): string => {
    const ext = filename.split('.').pop()?.toLowerCase();
    const icons: Record<string, string> = {
        'pdf': '📄', 'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️',
        'xlsx': '📊', 'xls': '📊', 'doc': '📃', 'docx': '📃', 'txt': '📝',
        'zip': '🗜️', 'rar': '🗜️'
    };
    return icons[ext || ''] || '📄';
};

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

const loadFolders = async () => {
    loadingFolders.value = true;
    try {
        const response = await axios.get('/admin/media/folders', {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        allFolders.value = response.data;
    } catch (error) {
        showNotification('Ошибка загрузки папок', 'error');
    } finally {
        loadingFolders.value = false;
    }
};

const loadContents = async () => {
    loadingContents.value = true;
    try {
        const response = await axios.get('/admin/media/contents', {
            params: { path: currentPath.value },
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        contents.value = response.data;
        selectedItem.value = null;
    } catch (error) {
        showNotification('Ошибка загрузки содержимого', 'error');
    } finally {
        loadingContents.value = false;
    }
};

const navigateToFolder = (path: string) => {
    currentPath.value = path;
    loadContents();
};

const createFolder = async () => {
    if (!newFolderName.value.trim()) return;

    try {
        await axios.post('/admin/media/folder', {
            name: newFolderName.value,
            path: currentPath.value
        }, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });

        showNotification('Папка создана', 'success');
        showCreateModal.value = false;
        newFolderName.value = '';
        loadFolders();
        loadContents();
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка создания папки', 'error');
    }
};

const openCreateFolderModal = () => {
    showCreateModal.value = true;
    newFolderName.value = '';
};

onMounted(() => {
    loadFolders();
    loadContents();
});
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background-color: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    width: 24rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>
