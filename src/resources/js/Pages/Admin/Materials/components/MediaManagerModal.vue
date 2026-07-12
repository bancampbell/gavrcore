<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="media-manager-modal">
            <div class="media-manager-header">
                <h3>Файловый менеджер</h3>
                <button @click="close" class="close-btn">×</button>
            </div>

            <div class="media-manager-content">
                <MediaManager
                    ref="mediaManagerRef"
                    :user="user"
                    :selected-url="selectedUrl"
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

watch(() => props.show, async (val) => {
    if (val && props.selectedUrl) {
        await nextTick();
        setTimeout(() => {
            if (mediaManagerRef.value) {
                mediaManagerRef.value.selectFileByUrl?.(props.selectedUrl);
            }
        }, 500);
    }
});

const close = () => {
    selectedFile.value = null;
    emit('close');
};

const onSelect = (file: { url: string; name: string; path: string }) => {
    selectedFile.value = file;
};

const onFileSelected = (file: { url: string; name: string; path: string } | null) => {
    if (file) {
        selectedFile.value = file;
    }
};

const insertFile = () => {
    if (selectedFile.value) {
        emit('select', selectedFile.value);
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
