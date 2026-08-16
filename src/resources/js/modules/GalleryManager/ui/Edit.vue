<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">{{ title }}</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button @click="save" :disabled="saving" class="admin-btn admin-btn-primary">
                        {{ saving ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                    <button @click="saveAndClose" :disabled="saving" class="admin-btn admin-btn-secondary">
                        {{ saving ? 'Сохранение...' : 'Сохранить и закрыть' }}
                    </button>
                    <button @click="cancel" class="admin-btn admin-btn-secondary">Закрыть</button>
                </div>
            </div>

            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <div class="p-4 border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <div class="flex-1 max-w-md">
                                <label class="admin-form-label text-xs text-slate-500">НАЗВАНИЕ</label>
                                <input v-model="form.title" type="text" class="admin-form-input" placeholder="Введите название..." />
                            </div>
                            <div class="w-56 flex-shrink-0">
                                <label class="admin-form-label text-xs text-slate-500">ТИП ГАЛЕРЕИ</label>
                                <select v-model="form.type" class="admin-form-select">
                                    <option value="grid">Сетка (Grid)</option>
                                    <option value="switcher">Switcher</option>
                                    <option value="slideshow">Слайд-шоу (Slideshow)</option>
                                    <option value="slider">Слайдер (Slider)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-slate-200">
                        <div class="flex px-4">
                            <button
                                @click="activeTab = 'media'"
                                class="px-4 py-3 text-sm font-medium border-b-2 transition"
                                :class="activeTab === 'media' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            >МЕДИА</button>
                            <button
                                @click="activeTab = 'settings'"
                                class="px-4 py-3 text-sm font-medium border-b-2 transition"
                                :class="activeTab === 'settings' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            >НАСТРОЙКИ</button>
                        </div>
                    </div>

                    <div class="admin-tab-content">
                        <div v-if="activeTab === 'media'" class="p-4">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-1 border-r border-slate-200 pr-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-sm font-medium text-slate-700">Изображения</span>
                                        <button @click="triggerUpload" :disabled="uploading" class="text-sm text-blue-600 hover:text-blue-800 disabled:opacity-50">
                                            {{ uploading ? 'Загрузка...' : '+ Добавить' }}
                                        </button>
                                        <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="uploadImages" />
                                    </div>

                                    <div class="space-y-1 max-h-[500px] overflow-y-auto">
                                        <div
                                            v-for="image in images"
                                            :key="image.id"
                                            @click="selectImage(image)"
                                            class="flex items-center gap-3 p-2 rounded cursor-pointer hover:bg-slate-50 transition"
                                            :class="selectedImage?.id === image.id ? 'bg-blue-50 border border-blue-200' : ''"
                                        >
                                            <img :src="image.image_path" alt="" class="w-12 h-12 object-cover rounded border border-slate-200" />
                                            <span class="text-sm text-slate-700 truncate flex-1">{{ image.title || 'Без названия' }}</span>
                                            <button @click.stop="deleteImage(image)" class="text-slate-400 hover:text-red-600">✕</button>
                                        </div>
                                        <div v-if="images.length === 0 && !uploading" class="text-center py-8 text-slate-400 text-sm">Нет изображений</div>
                                        <div v-if="uploading" class="text-center py-4 text-sm text-slate-500">Загрузка...</div>
                                    </div>
                                </div>

                                <div class="lg:col-span-2">
                                    <div v-if="selectedImage" class="space-y-4">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="admin-form-label text-xs text-slate-500">НАЗВАНИЕ</label>
                                                <input v-model="selectedImage.title" type="text" class="admin-form-input" placeholder="Введите название..." />
                                            </div>
                                            <div>
                                                <label class="admin-form-label text-xs text-slate-500">ОПИСАНИЕ</label>
                                                <textarea v-model="selectedImage.description" rows="3" class="admin-form-textarea resize-none" placeholder="Введите описание изображения..." />
                                            </div>
                                            <div>
                                                <label class="admin-form-label text-xs text-slate-500">ALT ТЕКСТ</label>
                                                <input v-model="selectedImage.alt_text" type="text" class="admin-form-input" placeholder="ALT текст..." />
                                            </div>
                                            <div>
                                                <label class="admin-form-label text-xs text-slate-500">ССЫЛКА</label>
                                                <input v-model="selectedImage.link" type="text" class="admin-form-input" placeholder="https://..." />
                                            </div>
                                            <div>
                                                <label class="admin-form-label text-xs text-slate-500">ПУТЬ</label>
                                                <input :value="selectedImage.image_path" type="text" readonly class="admin-form-input bg-slate-50 text-slate-500 cursor-not-allowed" />
                                            </div>
                                        </div>

                                        <div class="border border-slate-200 rounded-lg overflow-hidden bg-slate-50 p-2">
                                            <img :src="selectedImage.image_path" :alt="selectedImage.title || 'Изображение'" class="w-full max-w-[600px] h-64 object-contain" />
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-12 text-slate-400">Выберите изображение для редактирования</div>
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'settings'" class="p-4">
                            <div class="max-w-3xl space-y-6">
                                <div>
                                    <h3 class="admin-form-section-title text-sm">Макет</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Отступ (px)</label>
                                            <input v-model.number="settings.gutter" type="number" min="0" max="50" class="admin-form-input w-32" />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button @click="settings.match_height = !settings.match_height" type="button" class="admin-toggle" :class="settings.match_height ? 'admin-toggle-on' : 'admin-toggle-off'">
                                                <span class="admin-toggle-slider" :class="settings.match_height ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                            </button>
                                            <span class="text-sm text-slate-700">Выравнивание высоты</span>
                                        </div>
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Выравнивание</label>
                                            <select v-model="settings.alignment" class="admin-form-select w-full max-w-xs">
                                                <option value="left">По левому краю</option>
                                                <option value="center">По центру</option>
                                                <option value="right">По правому краю</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="admin-form-section-title text-sm">Медиа</h3>
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-3 max-w-md">
                                            <div>
                                                <label class="admin-form-label text-xs text-slate-500">Ширина (px)</label>
                                                <input v-model="settings.media.width" type="text" class="admin-form-input" placeholder="авто" />
                                            </div>
                                            <div>
                                                <label class="admin-form-label text-xs text-slate-500">Высота (px)</label>
                                                <input v-model="settings.media.height" type="text" class="admin-form-input" placeholder="авто" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Граница</label>
                                            <select v-model="settings.media.border" class="admin-form-select w-full max-w-xs">
                                                <option value="none">Нет</option>
                                                <option value="rounded">Скругленная</option>
                                                <option value="circle">Круг</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="admin-form-section-title text-sm">Контент</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3">
                                            <button @click="settings.content.show_title = !settings.content.show_title" type="button" class="admin-toggle" :class="settings.content.show_title ? 'admin-toggle-on' : 'admin-toggle-off'">
                                                <span class="admin-toggle-slider" :class="settings.content.show_title ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                            </button>
                                            <span class="text-sm text-slate-700">Показывать заголовок</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button @click="settings.content.show_content = !settings.content.show_content" type="button" class="admin-toggle" :class="settings.content.show_content ? 'admin-toggle-on' : 'admin-toggle-off'">
                                                <span class="admin-toggle-slider" :class="settings.content.show_content ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                            </button>
                                            <span class="text-sm text-slate-700">Показывать описание</span>
                                        </div>
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Размер заголовка</label>
                                            <select v-model="settings.content.title_size" class="admin-form-select w-full max-w-xs">
                                                <option value="default">По умолчанию</option>
                                                <option value="small">Маленький</option>
                                                <option value="large">Большой</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="admin-form-section-title text-sm">Ссылка</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3">
                                            <button @click="settings.link.show = !settings.link.show" type="button" class="admin-toggle" :class="settings.link.show ? 'admin-toggle-on' : 'admin-toggle-off'">
                                                <span class="admin-toggle-slider" :class="settings.link.show ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                            </button>
                                            <span class="text-sm text-slate-700">Показывать ссылку</span>
                                        </div>
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Стиль</label>
                                            <select v-model="settings.link.style" class="admin-form-select w-full max-w-xs">
                                                <option value="button">Кнопка</option>
                                                <option value="text">Текст</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Текст</label>
                                            <input v-model="settings.link.text" type="text" class="admin-form-input w-full max-w-xs" placeholder="Подробнее" />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="admin-form-section-title text-sm">Лайтбокс</h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Лайтбокс</label>
                                            <select v-model="settings.lightbox.mode" class="admin-form-select w-full max-w-xs">
                                                <option value="default">По умолчанию</option>
                                                <option value="enabled">Включен</option>
                                                <option value="disabled">Отключен</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button @click="settings.lightbox.use_title = !settings.lightbox.use_title" type="button" class="admin-toggle" :class="settings.lightbox.use_title ? 'admin-toggle-on' : 'admin-toggle-off'">
                                                <span class="admin-toggle-slider" :class="settings.lightbox.use_title ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                            </button>
                                            <span class="text-sm text-slate-700">Использовать заголовок</span>
                                        </div>
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Подпись</label>
                                            <input v-model="settings.lightbox.caption" type="text" class="admin-form-input w-full max-w-xs" placeholder="Подпись к изображению" />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button @click="settings.lightbox.show_second_media = !settings.lightbox.show_second_media" type="button" class="admin-toggle" :class="settings.lightbox.show_second_media ? 'admin-toggle-on' : 'admin-toggle-off'">
                                                <span class="admin-toggle-slider" :class="settings.lightbox.show_second_media ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                            </button>
                                            <span class="text-sm text-slate-700">Показывать второй медиа-элемент в лайтбоксе</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="admin-form-section-title text-sm">Кнопка</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3">
                                            <button @click="settings.lightbox.button.enabled = !settings.lightbox.button.enabled" type="button" class="admin-toggle" :class="settings.lightbox.button.enabled ? 'admin-toggle-on' : 'admin-toggle-off'">
                                                <span class="admin-toggle-slider" :class="settings.lightbox.button.enabled ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                            </button>
                                            <span class="text-sm text-slate-700">Включить ссылку лайтбокса</span>
                                        </div>
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Стиль</label>
                                            <select v-model="settings.lightbox.button.style" class="admin-form-select w-full max-w-xs">
                                                <option value="button">Кнопка</option>
                                                <option value="text">Текст</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Текст</label>
                                            <input v-model="settings.lightbox.button.text" type="text" class="admin-form-input w-full max-w-xs" placeholder="Подробнее" />
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.type === 'switcher'">
                                    <h3 class="admin-form-section-title text-sm mt-6">Switcher</h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="admin-form-label text-xs text-slate-500">Размер миниатюр (px)</label>
                                            <input v-model.number="settings.thumbnail_size" type="number" min="40" max="200" class="admin-form-input w-32" />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button @click="settings.show_labels = !settings.show_labels" type="button" class="admin-toggle" :class="settings.show_labels ? 'admin-toggle-on' : 'admin-toggle-off'">
                                                <span class="admin-toggle-slider" :class="settings.show_labels ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                            </button>
                                            <span class="text-sm text-slate-700">Показывать подписи под миниатюрами</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />

        <ConfirmModal
            :is-open="confirmModal.isOpen"
            :title="confirmModal.title"
            :message="confirmModal.message"
            :confirm-text="confirmModal.confirmText"
            :type="confirmModal.type"
            :loading="confirmModal.loading"
            @close="confirmModal.isOpen = false"
            @confirm="confirmModal.onConfirm"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import { galleryApi } from '../infrastructure/api/gallery-api';
import { useGallerySettings } from './composables/useGallerySettings';
import { useGalleryNotifications } from './composables/useGalleryNotifications';
import type { Gallery, GalleryImage } from '../types';

const props = defineProps<{
    user: any;
    gallery: Gallery;
    title: string;
}>();

const { settings } = useGallerySettings(props.gallery.settings);
const { notification, showNotification } = useGalleryNotifications();

const saving = ref(false);
const uploading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const activeTab = ref('media');

const confirmModal = ref({
    isOpen: false,
    title: '',
    message: '',
    confirmText: 'Удалить',
    type: 'danger' as 'danger' | 'warning',
    loading: false,
    onConfirm: () => {},
});

const form = ref({
    title: props.gallery.title,
    type: props.gallery.type,
    status: props.gallery.status,
});

const images = ref<GalleryImage[]>(JSON.parse(JSON.stringify(props.gallery.images || [])));
const selectedImage = ref<GalleryImage | null>(null);

const showConfirmModal = (options: {
    title: string;
    message: string;
    confirmText?: string;
    type?: 'danger' | 'warning';
    onConfirm: () => void | Promise<void>;
}) => {
    confirmModal.value = {
        isOpen: true,
        title: options.title,
        message: options.message,
        confirmText: options.confirmText || 'Удалить',
        type: options.type || 'danger',
        loading: false,
        onConfirm: options.onConfirm,
    };
};

const triggerUpload = () => { fileInput.value?.click(); };

const uploadImages = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (!files || files.length === 0) return;

    uploading.value = true;
    try {
        for (const file of files) {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('title', file.name);

            const response = await galleryApi.uploadImage(props.gallery.id, formData);
            if (response.data.image) images.value.push(response.data.image);
        }
        showNotification('Изображения загружены', 'success');
        if (images.value.length > 0 && !selectedImage.value) selectedImage.value = images.value[0];
    } catch (error) {
        showNotification('Ошибка при загрузке', 'error');
    } finally {
        uploading.value = false;
        target.value = '';
    }
};

const selectImage = (image: GalleryImage) => { selectedImage.value = image; };

const deleteImage = (image: GalleryImage) => {
    showConfirmModal({
        title: 'Удалить изображение?',
        message: 'Вы уверены, что хотите удалить это изображение? Это действие нельзя отменить.',
        confirmText: 'Удалить',
        type: 'danger',
        onConfirm: async () => {
            confirmModal.value.loading = true;
            try {
                await galleryApi.deleteImage(props.gallery.id, image.id);
                images.value = images.value.filter(img => img.id !== image.id);
                showNotification('Изображение удалено', 'success');
                if (selectedImage.value?.id === image.id) {
                    selectedImage.value = images.value.length > 0 ? images.value[0] : null;
                }
            } catch (error) {
                showNotification('Ошибка при удалении', 'error');
            } finally {
                confirmModal.value.loading = false;
                confirmModal.value.isOpen = false;
            }
        }
    });
};

const cancel = () => { router.visit('/admin/galleries'); };

const saveGalleryData = async () => {
    await galleryApi.update(props.gallery.id, {
        title: form.value.title,
        type: form.value.type,
        status: form.value.status,
        settings: settings as any,
    });
};

const saveImagesData = async () => {
    const updates = images.value.map(image =>
        galleryApi.updateImage(props.gallery.id, image.id, {
            title: image.title,
            description: image.description,
            alt_text: image.alt_text,
            link: image.link,
        })
    );
    await Promise.all(updates);
};

const save = async () => {
    if (!form.value.title.trim()) {
        showNotification('Введите название', 'error');
        return;
    }
    saving.value = true;
    try {
        await saveGalleryData();
        await saveImagesData();
        showNotification('Сохранено', 'success');
    } catch (error) {
        showNotification('Ошибка при сохранении', 'error');
    } finally {
        saving.value = false;
    }
};

const saveAndClose = async () => {
    if (!form.value.title.trim()) {
        showNotification('Введите название', 'error');
        return;
    }
    saving.value = true;
    try {
        await saveGalleryData();
        await saveImagesData();
        showNotification('Сохранено', 'success');
        setTimeout(() => { router.visit('/admin/galleries'); }, 500);
    } catch (error) {
        showNotification('Ошибка при сохранении', 'error');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    if (images.value.length > 0) selectedImage.value = images.value[0];
});

defineExpose({
    images,
    selectedImage,
    activeTab,
    form,
    notification,
    confirmModal,
    fileInput,
    selectImage,
    deleteImage,
});
</script>
