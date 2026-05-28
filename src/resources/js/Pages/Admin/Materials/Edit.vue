<template>
    <EmptyLayout :user="user">
        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">Менеджер материалов: Редактировать материал</h1>
            </div>
            <div class="px-6 pb-4 flex gap-2">
                <button @click="save" :disabled="loading" class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50">
                    Сохранить
                </button>
                <button @click="saveAndClose" :disabled="loading" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    Сохранить и закрыть
                </button>
                <Link href="/admin/materials" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                    Отменить
                </Link>
            </div>
        </div>

        <!-- Поля Заголовок и Алиас -->
        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Заголовок *</label>
                        <input
                            v-model="form.title"
                            @input="updateAlias"
                            type="text"
                            class="w-96 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введите заголовок..."
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Алиас</label>
                        <input
                            v-model="form.alias"
                            type="text"
                            class="w-64 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="останется пустым - сгенерируется автоматически"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Блок с редактором и правой колонкой -->
        <div class="flex-1 flex gap-6 px-6 py-6 min-h-[calc(100vh-250px)]">
            <!-- Основная колонка с редактором -->
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden h-full">
                    <Editor v-model="form.content" />
                </div>
            </div>

            <!-- Правая колонка -->
            <div class="w-80">
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Состояние</h3>
                        <select
                            v-model="form.state"
                            class="w-full border rounded px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 transition"
                            :class="{
                                'bg-green-600 text-white border-green-700 focus:ring-green-300': form.state === 'published',
                                'bg-red-600 text-white border-red-700 focus:ring-red-300': form.state === 'draft',
                                'bg-gray-500 text-white border-gray-600 focus:ring-gray-300': form.state === 'archived'
                            }"
                        >
                            <option value="published" class="bg-white text-gray-800">Опубликовано</option>
                            <option value="draft" class="bg-white text-gray-800">Не опубликовано</option>
                            <option value="archived" class="bg-white text-gray-800">Архив</option>
                        </select>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Категория *</h3>
                        <select v-model="form.category_id" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option :value="null">Выберите категорию</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Избранные</h3>
                        <select v-model="form.featured" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="0">Нет</option>
                            <option value="1">Да</option>
                        </select>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Доступ</h3>
                        <select v-model="form.access" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="public">Public</option>
                            <option value="registered">Registered</option>
                            <option value="special">Special</option>
                        </select>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Метки</h3>
                        <input
                            type="text"
                            class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm"
                            placeholder="Type or select some tags"
                        />
                        <p class="text-xs text-gray-400 mt-1">Введите метки через запятую</p>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import EmptyLayout from '../../../layouts/EmptyLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import type { User, Category, Material } from '../../../types';
import Editor from '../../../components/shared/Editor.vue';

const props = defineProps<{
    user: User;
    material: Material;
    categories: Category[];
}>();

const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const form = ref({
    title: props.material.title,
    alias: props.material.alias || '',
    content: props.material.content || '',
    category_id: props.material.category_id,
    state: props.material.state,
    access: props.material.access,
    featured: props.material.featured || '0'
});

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = window.setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const updateAlias = () => {
    if (!form.value.title) {
        form.value.alias = '';
        return;
    }

    let alias = form.value.title
        .toLowerCase()
        .replace(/[^a-zа-яё0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    const ruMap: Record<string, string> = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e',
        'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
        'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
        'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '',
        'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
    };

    alias = alias.split('').map(char => ruMap[char] || char).join('');

    form.value.alias = alias;
};

const save = async () => {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;

    try {
        const dataToSend = {
            title: form.value.title,
            alias: form.value.alias,
            content: form.value.content,
            category_id: form.value.category_id,
            state: form.value.state,
            access: form.value.access,
            featured: form.value.featured
        };

        await axios({
            method: 'put',
            url: `/admin/materials/${props.material.id}`,
            data: dataToSend,
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        showNotification('Материал сохранён', 'success');
    } catch (error: any) {
        console.error('Save error:', error);
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

const saveAndClose = async () => {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;

    try {
        const dataToSend = {
            title: form.value.title,
            alias: form.value.alias,
            content: form.value.content,
            category_id: form.value.category_id,
            state: form.value.state,
            access: form.value.access,
            featured: form.value.featured
        };

        await axios({
            method: 'put',
            url: `/admin/materials/${props.material.id}`,
            data: dataToSend,
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        router.visit('/admin/materials');
    } catch (error: any) {
        console.error('Save and close error:', error);
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};
</script>
