<template>
    <EmptyLayout :user="user">
        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">Менеджер материалов: Создать материал</h1>
            </div>
            <div class="px-6 pb-4 flex gap-2">
                <button @click="save" :disabled="loading" class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50">
                    Сохранить
                </button>
                <button @click="saveAndClose" :disabled="loading" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    Сохранить и закрыть
                </button>
                <button @click="saveAndCreate" :disabled="loading" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    Сохранить и создать
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
                            @input="generateAlias"
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
            <!-- Основная колонка -->
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden h-full flex flex-col">
                    <div class="border-b border-gray-200 bg-gray-50 flex justify-end">
                        <div class="flex">
                            <button
                                @click="activeTab = 'editor'"
                                :class="[
                                    'px-4 py-2 text-sm font-medium transition',
                                    activeTab === 'editor'
                                        ? 'bg-white text-blue-600 border-b-2 border-blue-600 -mb-px'
                                        : 'text-gray-600 hover:text-gray-800'
                                ]"
                            >
                                Editor
                            </button>
                            <button
                                @click="activeTab = 'code'"
                                :class="[
                                    'px-4 py-2 text-sm font-medium transition',
                                    activeTab === 'code'
                                        ? 'bg-white text-blue-600 border-b-2 border-blue-600 -mb-px'
                                        : 'text-gray-600 hover:text-gray-800'
                                ]"
                            >
                                Code
                            </button>
                            <button
                                @click="activeTab = 'preview'"
                                :class="[
                                    'px-4 py-2 text-sm font-medium transition',
                                    activeTab === 'preview'
                                        ? 'bg-white text-blue-600 border-b-2 border-blue-600 -mb-px'
                                        : 'text-gray-600 hover:text-gray-800'
                                ]"
                            >
                                Preview
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 p-4">
                        <div v-show="activeTab === 'editor'" class="h-full">
                            <textarea
                                v-model="form.content"
                                class="w-full h-full focus:outline-none resize-none"
                                placeholder="Введите содержимое..."
                            ></textarea>
                        </div>
                        <div v-show="activeTab === 'code'" class="h-full overflow-auto">
                            <pre class="text-sm font-mono bg-gray-50 p-4 rounded h-full">{{ form.content || '<!-- HTML код будет здесь -->' }}</pre>
                        </div>
                        <div v-show="activeTab === 'preview'" class="h-full overflow-auto prose max-w-none">
                            <div v-html="form.content || '<p class=\'text-gray-400\'>Предпросмотр содержимого...</p>'"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Правая колонка -->
            <div class="w-80">
                <div class="p-4 space-y-4">
                    <div>
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

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Категория *</h3>
                        <select v-model="form.category_id" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option :value="null">Выберите категорию</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Избранные</h3>
                        <select v-model="form.featured" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="0">Нет</option>
                            <option value="1">Да</option>
                        </select>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Доступ</h3>
                        <select v-model="form.access" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="public">Public</option>
                            <option value="registered">Registered</option>
                            <option value="special">Special</option>
                        </select>
                    </div>

                    <div>
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
import type { User, Category } from '../../../types';

const props = defineProps<{
    user: User;
    categories: Category[];
}>();

const activeTab = ref('editor');
const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const form = ref({
    title: '',
    alias: '',
    content: '',
    category_id: props.categories.length > 0 ? props.categories[0].id : null,
    state: 'draft',
    access: 'public',
    featured: '0'
});

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = window.setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

// Простая генерация алиаса
const updateAlias = () => {
    if (!form.value.title) {
        form.value.alias = '';
        return;
    }

    let alias = form.value.title
        .toLowerCase()
        .replace(/[^a-zа-яё0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    // Простая замена русских букв
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
        await axios.post('/admin/materials', form.value, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        showNotification('Материал сохранён', 'success');
    } catch (error: any) {
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
        await axios.post('/admin/materials', form.value, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        router.visit('/admin/materials');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};

const saveAndCreate = async () => {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;

    try {
        await axios.post('/admin/materials', form.value, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        form.value = {
            title: '',
            alias: '',
            content: '',
            category_id: props.categories.length > 0 ? props.categories[0].id : null,
            state: 'draft',
            access: 'public',
            featured: '0'
        };
        showNotification('Материал создан. Можете создать следующий', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};
</script>
