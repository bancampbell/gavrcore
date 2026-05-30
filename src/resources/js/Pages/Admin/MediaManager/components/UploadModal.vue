<!-- resources/js/Pages/Admin/MediaManager/components/UploadModal.vue -->

<template>
    <div v-if="show" class="modal-overlay">
        <div class="upload-modal">
            <div class="upload-modal-header">
                <h2 class="upload-modal-title">Загрузить</h2>
                <button @click="onClose" class="upload-modal-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="upload-modal-body">
                <div class="upload-dropzone" @click="triggerFileInput">
                    <svg class="upload-dropzone-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="upload-dropzone-text">Перетащите файлы сюда</p>
                </div>

                <input
                    ref="fileInput"
                    type="file"
                    multiple
                    class="hidden"
                    @change="onFileSelect"
                />

                <div v-if="selectedFiles && selectedFiles.length > 0" class="upload-files-list">
                    <div class="upload-files-header">Выбрано файлов: {{ selectedFiles.length }}</div>
                    <div class="upload-files-items">
                        <div v-for="i in Array.from(selectedFiles)" :key="i.name" class="upload-file-item">
                            {{ i.name }} ({{ formatFileSize(i.size) }})
                        </div>
                    </div>
                </div>
            </div>

            <div class="upload-modal-footer">
                <button @click="triggerFileInput" class="upload-btn upload-btn-browse">
                    Обзор
                </button>
                <button
                    @click="onUpload"
                    :disabled="!selectedFiles || selectedFiles.length === 0 || loading"
                    class="upload-btn upload-btn-upload"
                >
                    {{ loading ? 'Загрузка...' : 'Загрузить' }}
                </button>
                <button @click="onClose" class="upload-btn upload-btn-close">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { formatFileSize } from '../constants';

const props = defineProps<{
    show: boolean;
    loading: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'upload', files: FileList): void;
}>();

const selectedFiles = ref<FileList | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

watch(() => props.show, (val) => {
    if (!val) {
        selectedFiles.value = null;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
});

const triggerFileInput = () => {
    fileInput.value?.click();
};

const onFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    selectedFiles.value = target.files;
};

const onClose = () => emit('close');
const onUpload = () => {
    if (selectedFiles.value) {
        emit('upload', selectedFiles.value);
    }
};
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

.upload-modal {
    display: flex;
    flex-direction: column;
    background-color: white;
    border-radius: 0.5rem;
    width: 700px;
    max-width: 90vw;
    min-height: 500px;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.upload-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background-color: #f3f4f6;
    border-bottom: 1px solid #e5e7eb;
}

.upload-modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
}

.upload-modal-close {
    color: #9ca3af;
    transition: color 0.2s;
}

.upload-modal-close:hover {
    color: #4b5563;
}

.upload-modal-body {
    padding: 1.5rem;
    flex: 1;
    overflow-y: auto;
}

.upload-dropzone {
    border: 2px dashed #d1d5db;
    border-radius: 0.5rem;
    padding: 2rem;
    min-height: 300px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s;
}

.upload-dropzone:hover {
    border-color: #3b82f6;
}

.upload-dropzone-icon {
    width: 3rem;
    height: 3rem;
    margin: 0 auto 0.75rem;
    color: #9ca3af;
}

.upload-dropzone-text {
    color: #6b7280;
}

.upload-files-list {
    margin-top: 1rem;
}

.upload-files-header {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.upload-files-items {
    max-height: 8rem;
    overflow-y: auto;
}

.upload-files-items > :not(:first-child) {
    margin-top: 0.25rem;
}

.upload-file-item {
    font-size: 0.75rem;
    color: #4b5563;
}

.upload-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    background-color: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.upload-btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
}

.upload-btn-browse {
    background-color: #5cb85c;
    color: white;
    border: 1px solid #d1d5db;
}

.upload-btn-browse:hover {
    background-color: #4cae4c;
}

.upload-btn-upload {
    background-color: #337ab7;
    color: white;
}

.upload-btn-upload:hover:not(:disabled) {
    background-color: #286090;
}

.upload-btn-upload:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.upload-btn-close {
    background-color: white;
    color: #374151;
    border: 1px solid #d1d5db;
}

.upload-btn-close:hover {
    background-color: #f9fafb;
}
</style>
