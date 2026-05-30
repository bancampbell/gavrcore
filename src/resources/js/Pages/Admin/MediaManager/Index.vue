<template>
    <EmptyLayout :user="user">
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

        <div class="p-6 pt-4">
            <table class="w-full border-collapse">
                <thead>
                <tr>
                    <th class="border border-gray-300 bg-gray-100 py-2 px-3 text-left font-normal text-gray-600 text-sm" style="width: 280px;">
                        Каталоги
                    </th>
                    <th class="border border-gray-300 bg-gray-100 py-2 px-3 text-left font-normal text-gray-600 text-sm">
                        Содержание
                    </th>
                    <th class="border border-gray-300 bg-gray-100 py-2 px-3 text-left font-normal text-gray-600 text-sm" style="width: 280px;">
                        Подробная информация
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <!-- Левая колонка - Дерево каталогов -->
                    <td class="border border-gray-300 p-3 align-top bg-white">
                        <div v-if="loadingFolders" class="text-center py-4">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div>
                        </div>
                        <div v-else>
                            <div class="text-sm font-semibold text-gray-700 mb-3 pb-1 border-b border-gray-200">Папки</div>
                            <FolderTree
                                v-for="folder in rootFolders"
                                :key="folder.path"
                                :folder="folder"
                                :current-path="currentPath"
                                :all-folders="allFolders"
                                @navigate="navigateToFolder"
                            />
                        </div>
                    </td>

                    <!-- Средняя колонка - Содержание -->
                    <td class="border border-gray-300 p-3 align-top bg-white">
                        <div class="flex items-center gap-2 mb-2">
                            <button
                                @click="goHome"
                                class="p-1 rounded hover:bg-gray-100 transition"
                                title="В корень"
                            >
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </button>
                            <button
                                @click="goBack"
                                :disabled="!canGoBack"
                                class="p-1 rounded hover:bg-gray-100 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                title="Назад"
                            >
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                        </div>

                        <div v-if="loadingContents" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                        </div>

                        <div v-else class="space-y-0.5">
                            <div
                                v-for="item in folders"
                                :key="item.path"
                                class="flex items-center py-1 px-2 rounded-md transition-colors hover:bg-[#e6f0fa] group"
                                :class="{ 'bg-[#e6f0fa]': selectedItems.includes(item.path) }"
                            >
                                <input
                                    type="checkbox"
                                    :checked="selectedItems.includes(item.path)"
                                    @change="toggleSelect(item.path)"
                                    class="mr-2 w-3.5 h-3.5 rounded border-gray-300 cursor-pointer"
                                    @click.stop
                                />
                                <div
                                    @click="navigateToFolder(item.path)"
                                    class="flex items-center flex-1 cursor-pointer"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="#D2B073" stroke="#D2B073" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                    <span class="text-sm" :class="selectedItems.includes(item.path) ? 'text-[#333] font-medium' : 'text-gray-700'">{{ item.name }}</span>
                                </div>
                            </div>

                            <div
                                v-for="item in files"
                                :key="item.path"
                                class="flex items-center py-1 px-2 rounded-md transition-colors hover:bg-[#e6f0fa] group"
                                :class="{ 'bg-[#e6f0fa]': selectedItems.includes(item.path) }"
                            >
                                <input
                                    type="checkbox"
                                    :checked="selectedItems.includes(item.path)"
                                    @change="toggleSelect(item.path)"
                                    class="mr-2 w-3.5 h-3.5 rounded border-gray-300 cursor-pointer"
                                    @click.stop
                                />
                                <div
                                    @click="selectFile(item)"
                                    class="flex items-center flex-1 cursor-pointer"
                                >
                                    <span class="text-base mr-2">{{ getFileIcon(item.name) }}</span>
                                    <span class="text-sm" :class="selectedItems.includes(item.path) ? 'text-[#333] font-medium' : 'text-gray-700'">{{ item.name }}</span>
                                    <span class="text-xs text-gray-400 ml-auto">{{ formatFileSize(item.size) }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="!loadingContents && contents.length === 0" class="text-center py-8 text-gray-400">
                            Папка пуста
                        </div>
                    </td>

                    <!-- Правая колонка - Подробная информация -->
                    <td class="border border-gray-300 p-3 align-top bg-white">
                        <div v-if="selectedFile" class="p-2">
                            <div class="text-center mb-4">
                                <div class="text-6xl mb-2">{{ getFileIcon(selectedFile.name) }}</div>
                                <h3 class="font-medium text-gray-800 text-sm">{{ selectedFile.name }}</h3>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Тип:</span>
                                    <span class="text-gray-700">{{ selectedFile.mime_type || 'Неизвестно' }}</span>
                                </div>
                                <div v-if="selectedFile.size" class="flex justify-between">
                                    <span class="text-gray-500">Размер:</span>
                                    <span class="text-gray-700">{{ formatFileSize(selectedFile.size) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Путь:</span>
                                    <span class="text-gray-700 text-xs break-all">{{ selectedFile.path }}</span>
                                </div>
                            </div>
                            <button @click="selectedFile = null" class="w-full mt-4 px-3 py-1.5 text-sm bg-gray-100 rounded hover:bg-gray-200">
                                Закрыть
                            </button>
                        </div>
                        <div v-else class="text-center text-gray-400 py-8">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm">Выберите файл для просмотра информации</p>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

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
                    <button @click="showCreateModal = false" class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">
                        Отмена
                    </button>
                    <button @click="createFolder" :disabled="!newFolderName.trim()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                        Создать
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
}

const props = defineProps<{
    user: User;
}>();

const loadingFolders = ref(false);
const loadingContents = ref(false);
const allFolders = ref<MediaItem[]>([]);
const contents = ref<MediaItem[]>([]);
const selectedFile = ref<MediaItem | null>(null);
const currentPath = ref('');
const searchQuery = ref('');
const showCreateModal = ref(false);
const newFolderName = ref('');
const selectedItems = ref<string[]>([]);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });

const rootFolders = computed(() => allFolders.value.filter(f => !f.path.includes('/')));
const folders = computed(() => contents.value.filter(i => i.type === 'folder'));
const files = computed(() => contents.value.filter(i => i.type === 'file'));

const currentPathDisplay = computed(() => {
    return currentPath.value || '/';
});

const foldersCount = computed(() => folders.value.length);
const filesCount = computed(() => files.value.length);

const canGoBack = computed(() => {
    return currentPath.value !== '';
});

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

const toggleSelect = (path: string) => {
    const index = selectedItems.value.indexOf(path);
    if (index === -1) {
        selectedItems.value.push(path);
    } else {
        selectedItems.value.splice(index, 1);
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
        selectedFile.value = null;
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

const selectFile = (file: MediaItem) => {
    selectedFile.value = file;
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
