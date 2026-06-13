<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="media-manager-modal">
            <div class="media-manager-header">
                <h3>Файловый менеджер</h3>
                <button @click="close" class="close-btn">×</button>
            </div>

            <div v-if="mode === 'image'" class="media-manager-extra-header">
                <div class="image-options-grid">
                    <div class="image-options-left">
                        <div class="option-row">
                            <label class="option-label">Адрес</label>
                            <input
                                v-model="imageUrl"
                                type="text"
                                class="option-input"
                                placeholder="/storage/uploads/image.jpg"
                                readonly
                            />
                        </div>
                        <div class="option-row">
                            <label class="option-label">Текст</label>
                            <input
                                v-model="imageAlt"
                                type="text"
                                class="option-input"
                                placeholder="Alt текст"
                            />
                        </div>
                        <div class="option-row">
                            <label class="option-label">Размеры</label>
                            <div class="size-group">
                                <div class="size-field">
                                    <span>Ширина</span>
                                    <input
                                        v-model="imageWidth"
                                        type="number"
                                        class="size-input"
                                        placeholder="auto"
                                        @input="onWidthChange"
                                    />
                                </div>
                                <div class="size-field">
                                    <span>Высота</span>
                                    <input
                                        v-model="imageHeight"
                                        type="number"
                                        class="size-input"
                                        placeholder="auto"
                                        @input="onHeightChange"
                                    />
                                </div>
                                <label class="proportional-checkbox">
                                    <input type="checkbox" v-model="proportional" />
                                    <span>Пропорционально</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="image-options-right">
                        <div class="preview-container" v-if="imageUrl">
                            <img
                                :src="imageUrl"
                                :alt="imageAlt"
                                class="preview-image"
                                ref="previewImage"
                                @load="onImageLoad"
                            />
                        </div>
                        <div v-else class="preview-placeholder">
                            Изображение не выбрано
                        </div>
                    </div>
                </div>
            </div>

            <div class="media-manager-content">
                <MediaManager
                    ref="mediaManagerRef"
                    :user="user"
                    :selected-url="selectedUrl"
                    :accepted-files="acceptedFiles"
                    mode="picker"
                    @select="onSelect"
                    @fileSelected="onFileSelected"
                />
            </div>

            <div class="media-manager-footer">
                <div class="footer-info">
                    <span class="text-sm text-gray-600" v-if="!selectedFile">Выберите файл для вставки</span>
                    <span class="text-sm text-blue-600" v-else>Выбран: {{ selectedFile.name }}</span>
                </div>
                <div class="footer-actions">
                    <button @click="close" class="btn-cancel">Отмена</button>
                    <button
                        @click="insertFile"
                        :disabled="!selectedFile"
                        class="btn-insert"
                    >
                        Вставить
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import MediaManager from '../../MediaManager/Index.vue';

const props = defineProps<{
    show: boolean;
    user: any;
    selectedUrl?: string;
    mode?: 'file' | 'image';
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', file: { url: string; name: string; path: string; options?: any }): void;
}>();

const mediaManagerRef = ref<InstanceType<typeof MediaManager> | null>(null);
const selectedFile = ref<{ url: string; name: string; path: string } | null>(null);

const imageUrl = ref('');
const imageAlt = ref('');
const imageWidth = ref('');
const imageHeight = ref('');
const proportional = ref(true);
const originalWidth = ref(0);
const originalHeight = ref(0);
const previewImage = ref<HTMLImageElement | null>(null);

const acceptedFiles = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];

const close = () => {
    selectedFile.value = null;
    imageUrl.value = '';
    imageAlt.value = '';
    imageWidth.value = '';
    imageHeight.value = '';
    proportional.value = true;
    originalWidth.value = 0;
    originalHeight.value = 0;
    emit('close');
};

const loadImageDimensions = (url: string) => {
    return new Promise<{ width: number; height: number }>((resolve) => {
        const img = new Image();
        img.onload = () => {
            resolve({ width: img.width, height: img.height });
        };
        img.onerror = () => {
            resolve({ width: 0, height: 0 });
        };
        img.src = url;
    });
};

const onFileSelected = async (file: { url: string; name: string; path: string } | null) => {
    selectedFile.value = file;
    if (file && props.mode === 'image') {
        imageUrl.value = file.url;
        imageAlt.value = file.name;

        const dimensions = await loadImageDimensions(file.url);
        if (dimensions.width > 0 && dimensions.height > 0) {
            originalWidth.value = dimensions.width;
            originalHeight.value = dimensions.height;
            imageWidth.value = String(dimensions.width);
            imageHeight.value = String(dimensions.height);
        }

        proportional.value = true;
        await nextTick();
    }
};

const onSelect = async (file: { url: string; name: string; path: string }) => {
    selectedFile.value = file;
    if (props.mode === 'image') {
        imageUrl.value = file.url;
        imageAlt.value = file.name;

        const dimensions = await loadImageDimensions(file.url);
        if (dimensions.width > 0 && dimensions.height > 0) {
            originalWidth.value = dimensions.width;
            originalHeight.value = dimensions.height;
            imageWidth.value = String(dimensions.width);
            imageHeight.value = String(dimensions.height);
        }

        proportional.value = true;
        await nextTick();
    }
};

const onImageLoad = () => {
    if (previewImage.value) {
        originalWidth.value = previewImage.value.naturalWidth;
        originalHeight.value = previewImage.value.naturalHeight;
        if (!imageWidth.value) imageWidth.value = String(originalWidth.value);
        if (!imageHeight.value) imageHeight.value = String(originalHeight.value);
    }
};

const onWidthChange = () => {
    if (proportional.value && originalWidth.value > 0 && imageWidth.value) {
        const ratio = originalHeight.value / originalWidth.value;
        imageHeight.value = String(Math.round(parseFloat(imageWidth.value) * ratio));
    }
};

const onHeightChange = () => {
    if (proportional.value && originalHeight.value > 0 && imageHeight.value) {
        const ratio = originalWidth.value / originalHeight.value;
        imageWidth.value = String(Math.round(parseFloat(imageHeight.value) * ratio));
    }
};

const insertFile = () => {
    if (selectedFile.value) {
        if (props.mode === 'image') {
            emit('select', {
                ...selectedFile.value,
                options: {
                    alt: imageAlt.value,
                    width: imageWidth.value,
                    height: imageHeight.value
                }
            });
        } else {
            emit('select', selectedFile.value);
        }
        close();
    }
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 10001;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.media-manager-modal {
    background: white;
    border-radius: 0.5rem;
    width: 90vw;
    max-width: 1200px;
    height: 85vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
}

.media-manager-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
}

.media-manager-header h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.close-btn {
    color: #9ca3af;
    font-size: 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn:hover {
    color: #4b5563;
    background: #e5e7eb;
}

.media-manager-extra-header {
    padding: 1rem 1.5rem;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.image-options-grid {
    display: flex;
    gap: 1.5rem;
}

.image-options-left {
    flex: 1;
    min-width: 0;
}

.image-options-right {
    width: 200px;
    flex-shrink: 0;
}

.option-row {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.option-label {
    width: 70px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
    flex-shrink: 0;
}

.option-input {
    flex: 1;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s;
    background: white;
}

.option-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.option-input[readonly] {
    background: #f3f4f6;
    cursor: default;
}

.size-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.size-field {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.size-field span {
    font-size: 0.75rem;
    color: #6b7280;
}

.size-input {
    width: 80px;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.375rem 0.5rem;
    font-size: 0.8rem;
}

.proportional-checkbox {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: #374151;
    cursor: pointer;
}

.preview-container {
    width: 100%;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafb;
    border-radius: 0.375rem;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.preview-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.preview-placeholder {
    width: 100%;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafb;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    color: #9ca3af;
    border: 1px solid #e5e7eb;
}

.media-manager-content {
    flex: 1;
    overflow: hidden;
    min-height: 0;
    display: flex;
    flex-direction: column;
}

.media-manager-content :deep(> div) {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.media-manager-content :deep(.media-manager-container) {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.media-manager-content :deep(.media-manager-container.picker-mode) {
    height: 100%;
}

.media-manager-content :deep(.media-manager-main) {
    flex: 1;
    min-height: 0;
    display: flex;
}

.media-manager-content :deep(.media-manager-sidebar) {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.media-manager-content :deep(.sidebar-content) {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

.media-manager-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f3f4f6;
    flex-shrink: 0;
}

.footer-info {
    display: flex;
    align-items: center;
}

.footer-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-cancel {
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.btn-insert {
    padding: 0.5rem 1rem;
    background: #337ab7;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-insert:hover:not(:disabled) {
    background: #286090;
}

.btn-insert:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
