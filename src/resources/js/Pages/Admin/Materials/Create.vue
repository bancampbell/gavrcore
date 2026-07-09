<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Менеджер материалов: Создать материал</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button
                        @click="save"
                        :disabled="loading"
                        class="admin-btn admin-btn-primary"
                    >
                        Сохранить
                    </button>
                    <button
                        @click="saveAndClose"
                        :disabled="loading"
                        class="admin-btn admin-btn-secondary"
                    >
                        Сохранить и закрыть
                    </button>
                    <button
                        @click="saveAndCreate"
                        :disabled="loading"
                        class="admin-btn admin-btn-secondary"
                    >
                        Сохранить и создать
                    </button>
                    <button
                        @click="cancel"
                        class="admin-btn admin-btn-secondary"
                    >
                        Отменить
                    </button>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <!-- Верхняя панель: заголовок + слаг + показывать на главной -->
                    <div class="p-6 border-b border-slate-200">
                        <div class="flex flex-wrap items-center gap-6">
                            <div class="flex items-center gap-3">
                                <label class="admin-form-label whitespace-nowrap">Заголовок *</label>
                                <input
                                    v-model="form.title"
                                    @input="updateSlug"
                                    type="text"
                                    class="admin-form-input"
                                    style="width: 384px;"
                                    placeholder="Введите заголовок..."
                                />
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="admin-form-label whitespace-nowrap">Слаг (ЧПУ)</label>
                                <input
                                    v-model="form.slug"
                                    @input="onSlugInput"
                                    type="text"
                                    class="admin-form-input"
                                    style="width: 256px;"
                                    placeholder="останется пустым - сгенерируется автоматически"
                                />
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="admin-form-label whitespace-nowrap">На главной</label>
                                <button
                                    @click="form.show_on_homepage = form.show_on_homepage === '1' ? '0' : '1'"
                                    type="button"
                                    class="admin-toggle"
                                    :class="form.show_on_homepage === '1' ? 'admin-toggle-on' : 'admin-toggle-off'"
                                >
                                    <span
                                        class="admin-toggle-slider"
                                        :class="form.show_on_homepage === '1' ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                    />
                                </button>
                                <span class="text-sm text-slate-700">{{ form.show_on_homepage === '1' ? 'Да' : 'Нет' }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Только один материал может быть отмечен на главной</p>
                    </div>

                    <!-- Редактор + правая панель -->
                    <div class="flex flex-col lg:flex-row gap-6 p-6 min-h-[calc(100vh-280px)]">
                        <!-- Редактор -->
                        <div class="flex-1">
                            <div class="border border-slate-300 rounded-lg overflow-hidden h-full">
                                <Editor
                                    ref="editorRef"
                                    v-model="form.content"
                                    @open-link-modal="openLinkModal"
                                    @open-image-manager="openImageManager"
                                    @edit-link="handleEditLink"
                                    @open-gallery-modal="openGalleryModal"
                                />
                            </div>
                        </div>

                        <!-- Правая панель -->
                        <div class="w-full lg:w-80 flex-shrink-0 space-y-4">
                            <!-- Состояние -->
                            <div>
                                <h3 class="admin-form-label">Состояние</h3>
                                <select
                                    v-model="form.state"
                                    class="admin-form-select w-full"
                                    :class="{
                                        'admin-form-select-status-published': form.state === 'published',
                                        'admin-form-select-status-draft': form.state === 'draft',
                                        'admin-form-select-status-archived': form.state === 'archived'
                                    }"
                                >
                                    <option value="published" class="bg-white text-slate-800">Опубликовано</option>
                                    <option value="draft" class="bg-white text-slate-800">Не опубликовано</option>
                                    <option value="archived" class="bg-white text-slate-800">Архив</option>
                                </select>
                            </div>

                            <!-- Категория -->
                            <div>
                                <h3 class="admin-form-label">Категория *</h3>
                                <select v-model="form.category_id" class="admin-form-select w-full">
                                    <option :value="null">Выберите категорию</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Доступ -->
                            <div>
                                <h3 class="admin-form-label">Доступ</h3>
                                <select v-model="form.access" class="admin-form-select w-full">
                                    <option value="public">Public</option>
                                    <option value="registered">Registered</option>
                                    <option value="special">Special</option>
                                </select>
                            </div>

                            <!-- Отображение -->
                            <div>
                                <h3 class="admin-form-label">Отображение</h3>
                                <div class="border border-slate-300 rounded-lg p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-slate-700">Глобальные настройки</span>
                                        <button
                                            @click="form.use_global_settings = !form.use_global_settings"
                                            type="button"
                                            class="admin-toggle"
                                            :class="form.use_global_settings ? 'admin-toggle-on' : 'admin-toggle-off'"
                                        >
                                            <span
                                                class="admin-toggle-slider"
                                                :class="form.use_global_settings ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                            />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between opacity-50" :class="!form.use_global_settings && 'opacity-100'">
                                        <span class="text-sm text-slate-700">Дата создания</span>
                                        <button
                                            @click="form.show_date = !form.show_date"
                                            :disabled="form.use_global_settings"
                                            type="button"
                                            class="admin-toggle"
                                            :class="form.show_date ? 'admin-toggle-on' : 'admin-toggle-off'"
                                        >
                                            <span
                                                class="admin-toggle-slider"
                                                :class="form.show_date ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                            />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between opacity-50" :class="!form.use_global_settings && 'opacity-100'">
                                        <span class="text-sm text-slate-700">Автор</span>
                                        <button
                                            @click="form.show_author = !form.show_author"
                                            :disabled="form.use_global_settings"
                                            type="button"
                                            class="admin-toggle"
                                            :class="form.show_author ? 'admin-toggle-on' : 'admin-toggle-off'"
                                        >
                                            <span
                                                class="admin-toggle-slider"
                                                :class="form.show_author ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                            />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between opacity-50" :class="!form.use_global_settings && 'opacity-100'">
                                        <span class="text-sm text-slate-700">Категория</span>
                                        <button
                                            @click="form.show_category = !form.show_category"
                                            :disabled="form.use_global_settings"
                                            type="button"
                                            class="admin-toggle"
                                            :class="form.show_category ? 'admin-toggle-on' : 'admin-toggle-off'"
                                        >
                                            <span
                                                class="admin-toggle-slider"
                                                :class="form.show_category ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                            />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between opacity-50" :class="!form.use_global_settings && 'opacity-100'">
                                        <span class="text-sm text-slate-700">Просмотры</span>
                                        <button
                                            @click="form.show_views = !form.show_views"
                                            :disabled="form.use_global_settings"
                                            type="button"
                                            class="admin-toggle"
                                            :class="form.show_views ? 'admin-toggle-on' : 'admin-toggle-off'"
                                        >
                                            <span
                                                class="admin-toggle-slider"
                                                :class="form.show_views ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                            />
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">
                                    <span v-if="form.use_global_settings">Используются глобальные настройки</span>
                                    <span v-else>Индивидуальные настройки</span>
                                </p>
                            </div>

                            <!-- Метки -->
                            <div>
                                <h3 class="admin-form-label">Метки</h3>
                                <input
                                    v-model="form.tags"
                                    type="text"
                                    class="admin-form-input w-full"
                                    placeholder="Введите метки через запятую"
                                />
                                <p class="text-xs text-slate-400 mt-1">Введите метки через запятую</p>
                            </div>
                        </div>
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

        <GallerySelectModal
            :show="showGalleryModal"
            @close="closeGalleryModal"
            @select="insertGallery"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import type { User, Category } from '../../../types';
import Editor from '../../../components/shared/Editor/Index.vue';
import LinkModal from './components/LinkModal.vue';
import MediaManagerModal from './components/MediaManagerModal.vue';
import GallerySelectModal from '../../../components/shared/GallerySelectModal.vue';

const props = defineProps<{
    user: User;
    title?: string;
    categories: Category[];
}>();

const loading = ref(false);
const showLinkModal = ref(false);
const showImageManager = ref(false);
const showGalleryModal = ref(false);
const materials = ref<any[]>([]);
const editLinkData = ref<any>(null);
const editImageData = ref<any>(null);
const editorRef = ref<any>(null);
const selectedLinkText = ref('');
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const form = ref({
    title: '',
    slug: '',
    content: '',
    tags: '',
    category_id: props.categories.length > 0 ? props.categories[0].id : null,
    state: 'draft',
    access: 'public',
    show_on_homepage: '0',
    use_global_settings: true,
    show_date: true,
    show_author: true,
    show_category: true,
    show_views: true,
});

const isSlugManuallyEdited = ref(false);

const loadMaterials = async () => {
    try {
        const response = await axios.get('/admin/materials/list');
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

const openGalleryModal = () => {
    showGalleryModal.value = true;
};

const closeGalleryModal = () => {
    showGalleryModal.value = false;
};

const insertGallery = (galleryId: number, galleryName: string) => {
    if (editorRef.value) {
        const shortcode = `[gallery id="${galleryId}" name="${galleryName}"]`;
        editorRef.value.insertContent(shortcode);
    }
    closeGalleryModal();
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

const cancel = () => {
    router.visit('/admin/materials');
};

const save = async () => {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;

    try {
        const payload = {
            title: form.value.title,
            slug: form.value.slug || null,
            content: form.value.content,
            tags: form.value.tags,
            category_id: form.value.category_id,
            state: form.value.state,
            access: form.value.access,
            show_on_homepage: form.value.show_on_homepage === '1' ? true : false,
            use_global_settings: form.value.use_global_settings,
            show_date: form.value.show_date,
            show_author: form.value.show_author,
            show_category: form.value.show_category,
            show_views: form.value.show_views,
        };

        const response = await axios.post('/admin/materials', payload);
        const materialId = response.data.id || response.data.data?.id;

        if (materialId) {
            router.visit(`/admin/materials/${materialId}/edit?message=Материал+сохранён`);
        } else {
            showNotification('Материал сохранён', 'success');
            resetForm();
        }
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
        const payload = {
            title: form.value.title,
            slug: form.value.slug || null,
            content: form.value.content,
            tags: form.value.tags,
            category_id: form.value.category_id,
            state: form.value.state,
            access: form.value.access,
            show_on_homepage: form.value.show_on_homepage === '1' ? true : false,
            use_global_settings: form.value.use_global_settings,
            show_date: form.value.show_date,
            show_author: form.value.show_author,
            show_category: form.value.show_category,
            show_views: form.value.show_views,
        };

        await axios.post('/admin/materials', payload);
        router.visit('/admin/materials?message=Материал+создан');
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
        const payload = {
            title: form.value.title,
            slug: form.value.slug || null,
            content: form.value.content,
            tags: form.value.tags,
            category_id: form.value.category_id,
            state: form.value.state,
            access: form.value.access,
            show_on_homepage: form.value.show_on_homepage === '1' ? true : false,
            use_global_settings: form.value.use_global_settings,
            show_date: form.value.show_date,
            show_author: form.value.show_author,
            show_category: form.value.show_category,
            show_views: form.value.show_views,
        };

        await axios.post('/admin/materials', payload);
        resetForm();
        showNotification('Материал создан. Можете создать следующий', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    form.value = {
        title: '',
        slug: '',
        content: '',
        tags: '',
        category_id: props.categories.length > 0 ? props.categories[0].id : null,
        state: 'draft',
        access: 'public',
        show_on_homepage: '0',
        use_global_settings: true,
        show_date: true,
        show_author: true,
        show_category: true,
        show_views: true,
    };
    isSlugManuallyEdited.value = false;
};

onMounted(() => {
    loadMaterials();
});
</script>
