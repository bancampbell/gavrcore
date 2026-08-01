<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Менеджер материалов: Создать материал</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button @click="save" :disabled="loading" class="admin-btn admin-btn-primary">Сохранить</button>
                    <button @click="saveAndClose" :disabled="loading" class="admin-btn admin-btn-secondary">Сохранить и закрыть</button>
                    <button @click="saveAndCreate" :disabled="loading" class="admin-btn admin-btn-secondary">Сохранить и создать</button>
                    <button @click="cancel" class="admin-btn admin-btn-secondary">Отменить</button>
                </div>
            </div>

            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <div class="p-6 border-b border-slate-200">
                        <div class="flex flex-wrap items-center gap-6">
                            <div class="flex items-center gap-3">
                                <label class="admin-form-label whitespace-nowrap">Заголовок *</label>
                                <input v-model="form.title" @input="updateSlug" type="text" class="admin-form-input" style="width: 384px;" placeholder="Введите заголовок..." />
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="admin-form-label whitespace-nowrap">Слаг (ЧПУ)</label>
                                <input v-model="form.slug" @input="onSlugInput" type="text" class="admin-form-input" style="width: 256px;" placeholder="останется пустым - сгенерируется автоматически" />
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="admin-form-label whitespace-nowrap">На главной</label>
                                <button @click="form.show_on_homepage = form.show_on_homepage === '1' ? '0' : '1'" type="button" class="admin-toggle" :class="form.show_on_homepage === '1' ? 'admin-toggle-on' : 'admin-toggle-off'">
                                    <span class="admin-toggle-slider" :class="form.show_on_homepage === '1' ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                </button>
                                <span class="text-sm text-slate-700">{{ form.show_on_homepage === '1' ? 'Да' : 'Нет' }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Только один материал может быть отмечен на главной</p>
                    </div>

                    <div class="flex flex-col lg:flex-row gap-6 p-6 min-h-[calc(100vh-280px)]">
                        <div class="flex-1">
                            <div class="border border-slate-300 rounded-lg overflow-hidden h-full">
                                <Editor
                                    v-if="!showRawHtml"
                                    :key="editorKey"
                                    ref="editorRef"
                                    v-model="form.content"
                                    @open-link-modal="openLinkModal"
                                    @open-image-modal="openImageModal"
                                    @open-image-manager="openImageManager"
                                    @edit-link="handleEditLink"
                                    @open-gallery-modal="openGalleryModal"
                                    @toggle-raw-html="toggleRawHtml"
                                />
                                <RawHtmlEditor
                                    v-else
                                    :model-value="rawHtmlContent"
                                    @update:model-value="applyRawHtml"
                                    @close="closeRawHtml"
                                />
                            </div>
                        </div>

                        <div class="w-full lg:w-80 flex-shrink-0 space-y-4 max-h-[calc(100vh-250px)] overflow-y-auto">
                            <div>
                                <h3 class="admin-form-label">Состояние</h3>
                                <select v-model="form.state" class="admin-form-select w-full" :class="{
                                    'admin-form-select-status-published': form.state === 'published',
                                    'admin-form-select-status-draft': form.state === 'draft',
                                    'admin-form-select-status-archived': form.state === 'archived'
                                }">
                                    <option value="published" class="bg-white text-slate-800">Опубликовано</option>
                                    <option value="draft" class="bg-white text-slate-800">Не опубликовано</option>
                                    <option value="archived" class="bg-white text-slate-800">Архив</option>
                                </select>
                            </div>

                            <div>
                                <h3 class="admin-form-label">Категория *</h3>
                                <select v-model="form.category_id" class="admin-form-select w-full">
                                    <option :value="null">Выберите категорию</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>

                            <div>
                                <h3 class="admin-form-label">Доступ</h3>
                                <select v-model="form.access" class="admin-form-select w-full">
                                    <option value="public">Public</option>
                                    <option value="registered">Registered</option>
                                    <option value="special">Special</option>
                                </select>
                            </div>

                            <!-- ===== АККОРДЕОН: ОТОБРАЖЕНИЕ ===== -->
                            <div class="border border-slate-200 rounded-lg overflow-hidden">
                                <button
                                    @click="accordion.toggleSection('display')"
                                    class="w-full flex items-center justify-between px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                                >
                                    <span>Отображение</span>
                                    <svg
                                        class="w-4 h-4 transition-transform duration-200"
                                        :class="accordion.isOpen('display') ? 'rotate-180' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div v-show="accordion.isOpen('display')" class="px-3 pb-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-slate-700">Глобальные настройки</span>
                                        <button @click="form.use_global_settings = !form.use_global_settings" type="button" class="admin-toggle" :class="form.use_global_settings ? 'admin-toggle-on' : 'admin-toggle-off'">
                                            <span class="admin-toggle-slider" :class="form.use_global_settings ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between" :class="{ 'opacity-50': form.use_global_settings }">
                                        <span class="text-sm text-slate-700">Дата создания</span>
                                        <button @click="form.show_date = !form.show_date" :disabled="form.use_global_settings" type="button" class="admin-toggle" :class="form.show_date ? 'admin-toggle-on' : 'admin-toggle-off'">
                                            <span class="admin-toggle-slider" :class="form.show_date ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between" :class="{ 'opacity-50': form.use_global_settings }">
                                        <span class="text-sm text-slate-700">Автор</span>
                                        <button @click="form.show_author = !form.show_author" :disabled="form.use_global_settings" type="button" class="admin-toggle" :class="form.show_author ? 'admin-toggle-on' : 'admin-toggle-off'">
                                            <span class="admin-toggle-slider" :class="form.show_author ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between" :class="{ 'opacity-50': form.use_global_settings }">
                                        <span class="text-sm text-slate-700">Категория</span>
                                        <button @click="form.show_category = !form.show_category" :disabled="form.use_global_settings" type="button" class="admin-toggle" :class="form.show_category ? 'admin-toggle-on' : 'admin-toggle-off'">
                                            <span class="admin-toggle-slider" :class="form.show_category ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between" :class="{ 'opacity-50': form.use_global_settings }">
                                        <span class="text-sm text-slate-700">Просмотры</span>
                                        <button @click="form.show_views = !form.show_views" :disabled="form.use_global_settings" type="button" class="admin-toggle" :class="form.show_views ? 'admin-toggle-on' : 'admin-toggle-off'">
                                            <span class="admin-toggle-slider" :class="form.show_views ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">
                                        <span v-if="form.use_global_settings">Используются глобальные настройки</span>
                                        <span v-else>Индивидуальные настройки</span>
                                    </p>
                                </div>
                            </div>

                            <!-- ===== АККОРДЕОН: SEO ===== -->
                            <div class="border border-slate-200 rounded-lg overflow-hidden">
                                <button
                                    @click="accordion.toggleSection('seo')"
                                    class="w-full flex items-center justify-between px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                                >
                                    <span>SEO</span>
                                    <svg
                                        class="w-4 h-4 transition-transform duration-200"
                                        :class="accordion.isOpen('seo') ? 'rotate-180' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div v-show="accordion.isOpen('seo')" class="px-3 pb-3 space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-slate-700 block mb-1">Meta Title</label>
                                        <input v-model="form.meta_title" type="text" class="admin-form-input w-full" placeholder="Заголовок для поисковиков" maxlength="70" />
                                        <p class="text-xs text-slate-400 mt-1">{{ form.meta_title?.length || 0 }}/70</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-slate-700 block mb-1">Meta Description</label>
                                        <input v-model="form.meta_description" type="text" class="admin-form-input w-full" placeholder="Описание для поисковиков (до 160 символов)" maxlength="160" />
                                        <p class="text-xs text-slate-400 mt-1">{{ form.meta_description?.length || 0 }}/160</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-slate-700 block mb-1">Meta Keywords</label>
                                        <input v-model="form.meta_keywords" type="text" class="admin-form-input w-full" placeholder="Ключевые слова через запятую" />
                                    </div>
                                    <p class="text-xs text-slate-400">Если поля пустые - используются глобальные настройки</p>
                                </div>
                            </div>

                            <!-- ===== АККОРДЕОН: МЕТКИ ===== -->
                            <div class="border border-slate-200 rounded-lg overflow-hidden">
                                <button
                                    @click="accordion.toggleSection('tags')"
                                    class="w-full flex items-center justify-between px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                                >
                                    <span>Метки</span>
                                    <svg
                                        class="w-4 h-4 transition-transform duration-200"
                                        :class="accordion.isOpen('tags') ? 'rotate-180' : ''"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div v-show="accordion.isOpen('tags')" class="px-3 pb-3">
                                    <input v-model="form.tags" type="text" class="admin-form-input w-full" placeholder="Введите метки через запятую" />
                                    <p class="text-xs text-slate-400 mt-1">Введите метки через запятую</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />

        <LinkModal :show="showLinkModal" :categories="categories" :materials="materials" :edit-data="editLinkData" :selected-text="selectedLinkText" @close="closeLinkModal" @insert="insertLink" @edit="updateLink" />

        <ImageModal :show="showImageModal" :edit-data="editImageData" @close="closeImageModal" @insert="onImageInsert" />

        <MediaManagerModal :show="showImageManager" :user="user" :selected-url="selectedMediaUrl" mode="file" @close="closeImageManager" @select="onMediaManagerSelect" />

        <GallerySelectModal :show="showGalleryModal" @close="closeGalleryModal" @select="insertGallery" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import type { User, Category } from '../../../types';
import Editor from '../../../components/shared/Editor/Index.vue';
import RawHtmlEditor from '../../../components/shared/Editor/RawHtmlEditor.vue';
import LinkModal from './components/LinkModal.vue';
import ImageModal from './components/ImageModal.vue';
import MediaManagerModal from './components/MediaManagerModal.vue';
import GallerySelectModal from '../../../components/shared/GallerySelectModal.vue';
import { useSidebarSections } from '../../../composables/useSidebarSections';
import { createEditorContext, provideEditorContext } from '../../../infrastructure/di/editorContext';

const props = defineProps<{
    user: User;
    title?: string;
    categories: Category[];
}>();

const accordion = useSidebarSections(10);

const editorContext = createEditorContext();
provideEditorContext(editorContext);

const loading = ref(false);
const showLinkModal = ref(false);
const showImageModal = ref(false);
const showImageManager = ref(false);
const showGalleryModal = ref(false);
const materials = ref<any[]>([]);
const editLinkData = ref<any>(null);
const editImageData = ref<any>(null);
const selectedMediaUrl = ref('');
const editorRef = ref<any>(null);
const selectedLinkText = ref('');
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;
const editorKey = ref(0);

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
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

const isSlugManuallyEdited = ref(false);

const showRawHtml = ref(false);
const rawHtmlContent = ref('');

function toggleRawHtml(): void {
    if (!showRawHtml.value) {
        rawHtmlContent.value = form.value.content || '';
        showRawHtml.value = true;
    } else {
        showRawHtml.value = false;
    }
}

function closeRawHtml(): void {
    showRawHtml.value = false;
}

function applyRawHtml(html: string): void {
    form.value.content = html;
    showRawHtml.value = false;
    editorKey.value++;
    nextTick(() => {
        editorRef.value?.insertContent(html);
    });
}

function showNotification(message: string, type: 'success' | 'error' = 'success'): void {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = window.setTimeout(() => {
        notification.value.show = false;
    }, 5000);
}

function openLinkModal(selectedText?: string): void {
    editLinkData.value = null;
    selectedLinkText.value = selectedText || '';
    showLinkModal.value = true;
}

function closeLinkModal(): void {
    showLinkModal.value = false;
    editLinkData.value = null;
    selectedLinkText.value = '';
}

function insertLink(data: { url: string; text: string; target: string; title: string }): void {
    editorRef.value?.setLinkOnSelection(data.url, data.text, data.target, data.title);
}

function updateLink(data: { oldText: string; newUrl: string; newText: string; newTarget: string; newTitle: string }): void {
    editorRef.value?.updateExistingLink(data);
}

function handleEditLink(data: { oldText: string; url: string; text: string; target: string; title: string }): void {
    editLinkData.value = data;
    showLinkModal.value = true;
}

const savedCursorPosition = ref(0);

function openImageModal(imageData?: any): void {
    if (editorRef.value) {
        savedCursorPosition.value = editorRef.value.getCursorPosition?.() || 0;
    }
    editImageData.value = imageData || null;
    showImageModal.value = true;
}

function openImageManager(imageData?: any): void {
    editImageData.value = imageData || null;
    if (imageData?.url) {
        const urlParts = imageData.url.split('/');
        urlParts.pop();
        selectedMediaUrl.value = urlParts.join('/') || '/';
    } else {
        selectedMediaUrl.value = '';
    }
    showImageManager.value = true;
}

function closeImageModal(): void {
    showImageModal.value = false;
    editImageData.value = null;
}

function buildImageHtml(data: any): string {
    let imgHtml = `<img src="${data.url}"`;
    if (data.alt) imgHtml += ` alt="${data.alt}"`;
    if (data.title) imgHtml += ` title="${data.title}"`;
    if (data.width) imgHtml += ` width="${data.width}"`;
    if (data.height) imgHtml += ` height="${data.height}"`;

    const styleParts: string[] = [];
    if (data.width) styleParts.push(`width: ${data.width}px`);
    if (data.height) styleParts.push(`height: ${data.height}px`);
    styleParts.push('display: block');

    if (data.align === 'center') {
        styleParts.push('margin-left: auto', 'margin-right: auto');
    } else if (data.align === 'left') {
        styleParts.push('margin-left: 0', 'margin-right: auto');
    } else if (data.align === 'right') {
        styleParts.push('margin-left: auto', 'margin-right: 0');
    }

    if (data.float === 'left') {
        styleParts.push('float: left');
        if (data.margin) styleParts.push(`margin-right: ${data.margin}px`);
    } else if (data.float === 'right') {
        styleParts.push('float: right');
        if (data.margin) styleParts.push(`margin-left: ${data.margin}px`);
    }

    if (styleParts.length > 0) {
        imgHtml += ` style="${styleParts.join('; ')}"`;
    }

    imgHtml += ` />`;
    return imgHtml;
}

function onImageInsert(data: any): void {
    if (data.oldUrl) {
        editorRef.value?.updateImage(data.oldUrl, data);
    } else {
        const html = buildImageHtml(data);
        editorRef.value?.insertContent(html, savedCursorPosition.value);
    }
    closeImageModal();
}

function closeImageManager(): void {
    showImageManager.value = false;
    editImageData.value = null;
    selectedMediaUrl.value = '';
}

function onMediaManagerSelect(file: { url: string; name: string; path: string; options?: { alt?: string; width?: string; height?: string } }): void {
    if (editImageData.value) {
        editorRef.value?.updateImage(editImageData.value.url, {
            url: file.url,
            alt: file.options?.alt || file.name,
            title: editImageData.value.title || '',
            width: file.options?.width || '',
            height: file.options?.height || '',
            align: editImageData.value.align || '',
            float: editImageData.value.float || '',
            margin: editImageData.value.margin || '',
        });
        editImageData.value = null;
    } else {
        const html = buildImageHtml({
            url: file.url,
            alt: file.options?.alt || file.name,
            width: file.options?.width || '',
            height: file.options?.height || '',
        });
        editorRef.value?.insertContent(html, savedCursorPosition.value);
    }
    closeImageManager();
}

function openGalleryModal(): void {
    showGalleryModal.value = true;
}

function closeGalleryModal(): void {
    showGalleryModal.value = false;
}

function insertGallery(galleryId: number, galleryName: string): void {
    const shortcode = `[gallery id="${galleryId}" name="${galleryName}"]`;
    editorRef.value?.insertContent(shortcode, savedCursorPosition.value);
    closeGalleryModal();
}

function generateSlug(text: string): string {
    let slug = text
        .toLowerCase()
        .replace(/[^a-zа-яё0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    const ruMap: Record<string, string> = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e',
        'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
        'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
        'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '',
        'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
    };

    slug = slug.split('').map(char => ruMap[char] || char).join('');
    return slug;
}

function updateSlug(): void {
    if (isSlugManuallyEdited.value) return;
    form.value.slug = form.value.title ? generateSlug(form.value.title) : '';
}

function onSlugInput(): void {
    isSlugManuallyEdited.value = true;
}

function cancel(): void {
    router.visit('/admin/materials');
}

async function save(): Promise<void> {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;
    try {
        await axios.post('/admin/materials', form.value);
        showNotification('Материал создан', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
}

async function saveAndClose(): Promise<void> {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;
    try {
        await axios.post('/admin/materials', form.value);
        router.visit('/admin/materials?message=Материал+создан');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
}

async function saveAndCreate(): Promise<void> {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;
    try {
        await axios.post('/admin/materials', form.value);

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
            meta_title: '',
            meta_description: '',
            meta_keywords: '',
        };
        isSlugManuallyEdited.value = false;
        editorKey.value++;
        rawHtmlContent.value = '';
        showRawHtml.value = false;

        showNotification('Материал создан. Можете создать следующий', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    try {
        const response = await axios.get('/admin/materials/list');
        materials.value = response.data;
    } catch (error) {
        console.error('Error loading materials:', error);
    }
});
</script>
