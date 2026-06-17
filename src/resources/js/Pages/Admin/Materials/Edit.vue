<template>
    <EmptyLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
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

        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Заголовок *</label>
                        <input
                            v-model="form.title"
                            @input="updateSlug"
                            type="text"
                            class="w-96 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введите заголовок..."
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Слаг (ЧПУ)</label>
                        <input
                            v-model="form.slug"
                            @input="onSlugInput"
                            type="text"
                            class="w-64 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="останется пустым - сгенерируется автоматически"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex gap-6 px-6 py-6 min-h-[calc(100vh-250px)]">
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden h-full">
                    <Editor
                        ref="editorRef"
                        v-model="form.content"
                        @open-link-modal="openLinkModal"
                        @open-image-manager="openImageManager"
                        @edit-link="handleEditLink"
                    />
                </div>
            </div>

            <div class="w-80">
                <div class="space-y-4">
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
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Показывать на главной</h3>
                        <div class="flex items-center gap-3">
                            <button
                                @click="form.show_on_homepage = form.show_on_homepage === '1' ? '0' : '1'"
                                type="button"
                                class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                :class="form.show_on_homepage === '1' ? 'bg-indigo-600' : 'bg-gray-300'"
                            >
                                <span
                                    class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                    :class="form.show_on_homepage === '1' ? 'translate-x-4.5' : 'translate-x-0.5'"
                                />
                            </button>
                            <span class="text-sm text-gray-700">{{ form.show_on_homepage === '1' ? 'Да' : 'Нет' }}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Только один материал может быть отмечен. При выборе "Да" у других материалов этот параметр снимется.</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Доступ</h3>
                        <select v-model="form.access" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="public">Public</option>
                            <option value="registered">Registered</option>
                            <option value="special">Special</option>
                        </select>
                    </div>

                    <!-- Отображение элементов -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Отображение</h3>
                        <div class="space-y-2 border border-gray-300 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Использовать глобальные настройки</span>
                                <button
                                    @click="form.use_global_settings = !form.use_global_settings"
                                    type="button"
                                    class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                    :class="form.use_global_settings ? 'bg-indigo-600' : 'bg-gray-300'"
                                >
                                    <span
                                        class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                        :class="form.use_global_settings ? 'translate-x-4.5' : 'translate-x-0.5'"
                                    />
                                </button>
                            </div>
                            <div class="flex items-center justify-between opacity-50" :class="!form.use_global_settings && 'opacity-100'">
                                <span class="text-sm text-gray-700">Дата создания</span>
                                <button
                                    @click="form.show_date = !form.show_date"
                                    :disabled="form.use_global_settings"
                                    type="button"
                                    class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                    :class="form.show_date ? 'bg-indigo-600' : 'bg-gray-300'"
                                >
                                    <span
                                        class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                        :class="form.show_date ? 'translate-x-4.5' : 'translate-x-0.5'"
                                    />
                                </button>
                            </div>
                            <div class="flex items-center justify-between opacity-50" :class="!form.use_global_settings && 'opacity-100'">
                                <span class="text-sm text-gray-700">Автор</span>
                                <button
                                    @click="form.show_author = !form.show_author"
                                    :disabled="form.use_global_settings"
                                    type="button"
                                    class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                    :class="form.show_author ? 'bg-indigo-600' : 'bg-gray-300'"
                                >
                                    <span
                                        class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                        :class="form.show_author ? 'translate-x-4.5' : 'translate-x-0.5'"
                                    />
                                </button>
                            </div>
                            <div class="flex items-center justify-between opacity-50" :class="!form.use_global_settings && 'opacity-100'">
                                <span class="text-sm text-gray-700">Категория</span>
                                <button
                                    @click="form.show_category = !form.show_category"
                                    :disabled="form.use_global_settings"
                                    type="button"
                                    class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                    :class="form.show_category ? 'bg-indigo-600' : 'bg-gray-300'"
                                >
                                    <span
                                        class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                        :class="form.show_category ? 'translate-x-4.5' : 'translate-x-0.5'"
                                    />
                                </button>
                            </div>
                            <div class="flex items-center justify-between opacity-50" :class="!form.use_global_settings && 'opacity-100'">
                                <span class="text-sm text-gray-700">Просмотры</span>
                                <button
                                    @click="form.show_views = !form.show_views"
                                    :disabled="form.use_global_settings"
                                    type="button"
                                    class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                    :class="form.show_views ? 'bg-indigo-600' : 'bg-gray-300'"
                                >
                                    <span
                                        class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                        :class="form.show_views ? 'translate-x-4.5' : 'translate-x-0.5'"
                                    />
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            <span v-if="form.use_global_settings">Используются глобальные настройки</span>
                            <span v-else>Индивидуальные настройки для этого материала</span>
                        </p>
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

        <LinkModal
            :show="showLinkModal"
            :categories="categories"
            :materials="materials"
            :edit-data="editLinkData"
            :selected-text="selectedLinkText"
            @close="closeLinkModal"
            @insert="insertLink"
            @edit="updateLink"
        />

        <MediaManagerModal
            :show="showImageManager"
            :user="user"
            :selected-url="editImageData?.url"
            mode="image"
            @close="closeImageManager"
            @select="onImageSelect"
        />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import EmptyLayout from '../../../layouts/EmptyLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import type { User, Category, Material } from '../../../types';
import Editor from '../../../components/shared/Editor/Index.vue';
import LinkModal from './components/LinkModal.vue';
import MediaManagerModal from './components/MediaManagerModal.vue';

const props = defineProps<{
    user: User;
    title?: string;
    material: Material;
    categories: Category[];
}>();

const loading = ref(false);
const showLinkModal = ref(false);
const showImageManager = ref(false);
const materials = ref<any[]>([]);
const editLinkData = ref<any>(null);
const editImageData = ref<any>(null);
const editorRef = ref<any>(null);
const selectedLinkText = ref('');
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const form = ref({
    title: props.material.title,
    slug: props.material.slug || '',
    content: props.material.content || '',
    category_id: props.material.category_id,
    state: props.material.state,
    access: props.material.access,
    featured: props.material.featured ? '1' : '0',
    show_on_homepage: props.material.show_on_homepage ? '1' : '0',
    use_global_settings: props.material.use_global_settings ?? true,
    show_date: props.material.show_date ?? true,
    show_author: props.material.show_author ?? true,
    show_category: props.material.show_category ?? true,
    show_views: props.material.show_views ?? true,
});

// Флаг для отслеживания, вводил ли пользователь slug вручную
const isSlugManuallyEdited = ref(!!form.value.slug);

const loadMaterials = async () => {
    try {
        const response = await axios.get('/admin/materials/list', {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        materials.value = response.data;
    } catch (error) {
        console.error('Error loading materials:', error);
    }
};

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = window.setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const openLinkModal = (selectedText?: string) => {
    editLinkData.value = null;
    selectedLinkText.value = selectedText || '';
    showLinkModal.value = true;
};

const openImageManager = (imageData?: { url: string; alt: string; title: string; width?: string; height?: string; align?: string }) => {
    editImageData.value = imageData || null;
    showImageManager.value = true;
};

const closeImageManager = () => {
    showImageManager.value = false;
    editImageData.value = null;
};

const onImageSelect = (file: { url: string; name: string; path: string; options?: { alt?: string; width?: string; height?: string } }) => {
    if (editImageData.value) {
        let style = '';
        if (file.options?.width && file.options.width !== '') style += `width: ${file.options.width}px; `;
        if (file.options?.height && file.options.height !== '') style += `height: ${file.options.height}px; `;

        editorRef.value?.updateImage(editImageData.value.url, {
            url: file.url,
            alt: file.options?.alt || file.name,
            title: '',
            width: file.options?.width || '',
            height: file.options?.height || '',
            align: ''
        });
    } else {
        let style = '';
        if (file.options?.width && file.options.width !== '') style += `width: ${file.options.width}px; `;
        if (file.options?.height && file.options.height !== '') style += `height: ${file.options.height}px; `;

        let imgHtml = `<img src="${file.url}" alt="${file.options?.alt || file.name}"`;
        if (style) {
            imgHtml += ` style="${style.trim()}"`;
        }
        imgHtml += ` />`;

        editorRef.value?.insertContent(imgHtml);
    }
    closeImageManager();
};

const handleEditLink = (data: { oldText: string; url: string; text: string; target: string; title: string }) => {
    editLinkData.value = data;
    showLinkModal.value = true;
};

const closeLinkModal = () => {
    showLinkModal.value = false;
    editLinkData.value = null;
    selectedLinkText.value = '';
};

const insertLink = (data: { url: string; text: string; target: string; title: string }) => {
    editorRef.value?.setLinkOnSelection(data.url, data.text, data.target, data.title);
};

const updateLink = (data: { oldText: string; newUrl: string; newText: string; newTarget: string; newTitle: string }) => {
    if (editorRef.value) {
        editorRef.value.updateExistingLink(data);
    }
};

const generateSlug = (text: string): string => {
    let slug = text
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

    slug = slug.split('').map(char => ruMap[char] || char).join('');
    return slug;
};

const updateSlug = () => {
    if (isSlugManuallyEdited.value) return;
    if (!form.value.title) {
        form.value.slug = '';
        return;
    }
    form.value.slug = generateSlug(form.value.title);
};

const onSlugInput = () => {
    isSlugManuallyEdited.value = true;
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
            slug: form.value.slug,
            content: form.value.content,
            category_id: form.value.category_id,
            state: form.value.state,
            access: form.value.access,
            featured: form.value.featured,
            show_on_homepage: form.value.show_on_homepage,
            use_global_settings: form.value.use_global_settings,
            show_date: form.value.show_date,
            show_author: form.value.show_author,
            show_category: form.value.show_category,
            show_views: form.value.show_views,
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
            slug: form.value.slug,
            content: form.value.content,
            category_id: form.value.category_id,
            state: form.value.state,
            access: form.value.access,
            featured: form.value.featured,
            show_on_homepage: form.value.show_on_homepage,
            use_global_settings: form.value.use_global_settings,
            show_date: form.value.show_date,
            show_author: form.value.show_author,
            show_category: form.value.show_category,
            show_views: form.value.show_views,
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
        router.visit('/admin/materials?message=Материал+обновлён');
    } catch (error: any) {
        console.error('Save and close error:', error);
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};

onMounted(() => {
    loadMaterials();
});
</script>
