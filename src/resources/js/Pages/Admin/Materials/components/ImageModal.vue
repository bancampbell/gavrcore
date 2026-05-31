<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="image-modal image-modal-fixed">
            <div class="image-modal-header">
                <h3>Изображение</h3>
                <button @click="close" class="image-modal-close">×</button>
            </div>

            <div class="image-modal-content">
                <div class="form-row">
                    <label class="form-label">URL изображения</label>
                    <div class="input-group">
                        <input
                            v-model="imageUrl"
                            type="text"
                            class="form-input"
                            placeholder="/storage/uploads/image.jpg"
                        />
                        <button
                            @click="openFileManager"
                            type="button"
                            class="btn-icon"
                            title="Файловый менеджер"
                        >
                            📄
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label">Alt текст</label>
                    <input
                        v-model="imageAlt"
                        type="text"
                        class="form-input"
                        placeholder="Описание изображения"
                    />
                </div>

                <div class="form-row">
                    <label class="form-label">Заголовок</label>
                    <input
                        v-model="imageTitle"
                        type="text"
                        class="form-input"
                        placeholder="Всплывающая подсказка"
                    />
                </div>

                <div class="form-row">
                    <label class="form-label">Ширина</label>
                    <input
                        v-model="imageWidth"
                        type="number"
                        class="form-input w-24"
                        placeholder="auto"
                    />
                </div>

                <div class="form-row">
                    <label class="form-label">Высота</label>
                    <input
                        v-model="imageHeight"
                        type="number"
                        class="form-input w-24"
                        placeholder="auto"
                    />
                </div>

                <div class="form-row">
                    <label class="form-label">Выравнивание</label>
                    <select v-model="imageAlign" class="form-select w-32">
                        <option value="">-- Не выбрано --</option>
                        <option value="left">Слева</option>
                        <option value="center">По центру</option>
                        <option value="right">Справа</option>
                    </select>
                </div>
            </div>

            <div class="image-modal-footer">
                <button @click="close" class="btn-cancel">Отмена</button>
                <button @click="insertImage" class="btn-primary">Вставить изображение</button>
            </div>
        </div>
    </div>

    <MediaManagerModal
        :show="showMediaManager"
        :user="user"
        :selected-url="imageUrl"
        mode="image"
        @close="showMediaManager = false"
        @select="onMediaSelect"
    />
</template>

<script setup lang="ts">
import { ref, watch, inject } from 'vue';
import MediaManagerModal from './MediaManagerModal.vue';

const props = defineProps<{
    show: boolean;
    editData?: {
        url: string;
        alt: string;
        title: string;
        width?: string;
        height?: string;
        align?: string;
    } | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'insert', data: { url: string; alt: string; title: string; width?: string; height?: string; align?: string }): void;
}>();

const user = inject('user') as any;

const imageUrl = ref('');
const imageAlt = ref('');
const imageTitle = ref('');
const imageWidth = ref('');
const imageHeight = ref('');
const imageAlign = ref('');
const showMediaManager = ref(false);

watch(() => props.show, (val) => {
    if (val) {
        if (props.editData) {
            imageUrl.value = props.editData.url || '';
            imageAlt.value = props.editData.alt || '';
            imageTitle.value = props.editData.title || '';
            imageWidth.value = props.editData.width || '';
            imageHeight.value = props.editData.height || '';
            imageAlign.value = props.editData.align || '';
        } else {
            imageUrl.value = '';
            imageAlt.value = '';
            imageTitle.value = '';
            imageWidth.value = '';
            imageHeight.value = '';
            imageAlign.value = '';
        }
    }
});

const close = () => {
    emit('close');
};

const openFileManager = () => {
    showMediaManager.value = true;
};

const onMediaSelect = (file: { url: string; name: string; path: string }) => {
    imageUrl.value = file.url;
    if (!imageAlt.value) {
        imageAlt.value = file.name;
    }
    showMediaManager.value = false;
};

const insertImage = () => {
    if (!imageUrl.value) {
        alert('Введите URL изображения');
        return;
    }

    emit('insert', {
        url: imageUrl.value,
        alt: imageAlt.value,
        title: imageTitle.value,
        width: imageWidth.value,
        height: imageHeight.value,
        align: imageAlign.value
    });
    close();
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-modal-fixed {
    background: white;
    border-radius: 0.5rem;
    width: 500px;
    max-width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
}

.image-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #ddd;
    border-bottom: 1px solid #ccc;
    flex-shrink: 0;
}

.image-modal-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.image-modal-close {
    color: #4b5563;
    font-size: 1.75rem;
    font-weight: 700;
    background: none;
    border: none;
    cursor: pointer;
    line-height: 1;
    opacity: 0.7;
}

.image-modal-close:hover {
    opacity: 1;
}

.image-modal-content {
    padding: 1.5rem;
    flex: 1;
    overflow-y: auto;
}

.image-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
}

.form-row {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
}

.form-row .form-label {
    width: 120px;
    flex-shrink: 0;
    margin-bottom: 0;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
}

.form-row .form-input,
.form-row .input-group,
.form-row .form-select {
    flex: 1;
}

.input-group {
    display: flex;
    gap: 0.5rem;
    flex: 1;
}

.form-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.form-select {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    background: white;
}

.btn-icon {
    padding: 0.5rem 0.75rem;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    cursor: pointer;
}

.btn-icon:hover {
    background: #e5e7eb;
}

.btn-cancel {
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
}

.btn-cancel:hover {
    background: #f3f4f6;
}

.btn-primary {
    padding: 0.5rem 1rem;
    background: #337ab7;
    color: white;
    border-radius: 0.375rem;
    border: none;
    font-size: 0.875rem;
    cursor: pointer;
}

.btn-primary:hover {
    background: #286090;
}

.w-24 {
    width: 96px;
}

.w-32 {
    width: 128px;
}
</style>
