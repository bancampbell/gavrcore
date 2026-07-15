<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="modal-content image-modal">
            <div class="modal-header">
                <h3 class="modal-title">{{ isEditMode ? 'Редактирование изображения' : 'Вставка изображения' }}</h3>
                <button @click="close" class="modal-close">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-row">
                    <label class="admin-form-label">URL изображения</label>
                    <div class="input-group">
                        <input
                            v-model="imageUrl"
                            type="text"
                            class="admin-form-input"
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
                    <label class="admin-form-label">Alt текст</label>
                    <input
                        v-model="imageAlt"
                        type="text"
                        class="admin-form-input"
                        placeholder="Описание изображения"
                    />
                </div>

                <div class="form-row">
                    <label class="admin-form-label">Заголовок</label>
                    <input
                        v-model="imageTitle"
                        type="text"
                        class="admin-form-input"
                        placeholder="Всплывающая подсказка"
                    />
                </div>

                <div class="form-row">
                    <label class="admin-form-label">Ширина</label>
                    <input
                        v-model="imageWidth"
                        type="text"
                        class="admin-form-input w-24"
                        placeholder="auto"
                    />
                </div>

                <div class="form-row">
                    <label class="admin-form-label">Высота</label>
                    <input
                        v-model="imageHeight"
                        type="text"
                        class="admin-form-input w-24"
                        placeholder="auto"
                    />
                </div>

                <div class="form-row">
                    <label class="admin-form-label">Выравнивание</label>
                    <select v-model="imageAlign" class="admin-form-select w-32">
                        <option value="">-- Не выбрано --</option>
                        <option value="left">Слева</option>
                        <option value="center">По центру</option>
                        <option value="right">Справа</option>
                    </select>
                </div>

                <div class="form-row">
                    <label class="admin-form-label">Обтекание текстом</label>
                    <select v-model="imageFloat" class="admin-form-select w-32">
                        <option value="">-- Не выбрано --</option>
                        <option value="left">Изображение слева</option>
                        <option value="right">Изображение справа</option>
                    </select>
                </div>

                <div class="form-row">
                    <label class="admin-form-label">Отступ от текста (px)</label>
                    <input
                        v-model="imageMargin"
                        type="number"
                        class="admin-form-input w-24"
                        placeholder="10"
                        min="0"
                        max="100"
                    />
                </div>
            </div>

            <div class="modal-footer">
                <button @click="close" class="btn-cancel">Отмена</button>
                <button @click="insertImage" class="btn-primary">{{ isEditMode ? 'Обновить' : 'Вставить' }}</button>
            </div>
        </div>
    </div>

    <MediaManagerModal
        :key="imageUrl"
        :show="showMediaManager"
        :user="user"
        :selected-url="imageUrl.replace(/^https?:\/\/[^\/]+\/storage\/uploads\//, '')"
        mode="file"
        @close="showMediaManager = false"
        @select="onMediaSelect"
    />
</template>

<script setup lang="ts">
import { ref, watch, inject, computed } from 'vue';
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
        float?: string;
        margin?: string;
        _pos?: number;
    } | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'insert', data: {
        url: string;
        alt: string;
        title: string;
        width?: string;
        height?: string;
        align?: string;
        float?: string;
        margin?: string;
        oldUrl?: string;
        _pos?: number;
    }): void;
}>();

const user = inject('user') as any;

const imageUrl = ref('');
const imageAlt = ref('');
const imageTitle = ref('');
const imageWidth = ref('');
const imageHeight = ref('');
const imageAlign = ref('');
const imageFloat = ref('');
const imageMargin = ref('');
const showMediaManager = ref(false);

const isEditMode = computed(() => !!props.editData?.url);

const loadData = () => {
    if (props.editData) {
        imageUrl.value = props.editData.url || '';
        imageAlt.value = props.editData.alt || '';
        imageTitle.value = props.editData.title || '';
        imageWidth.value = props.editData.width ? String(props.editData.width).replace('px', '') : '';
        imageHeight.value = props.editData.height ? String(props.editData.height).replace('px', '') : '';
        imageAlign.value = props.editData.align || '';
        imageFloat.value = props.editData.float || '';
        imageMargin.value = props.editData.margin || '';
    } else {
        imageUrl.value = '';
        imageAlt.value = '';
        imageTitle.value = '';
        imageWidth.value = '';
        imageHeight.value = '';
        imageAlign.value = '';
        imageFloat.value = '';
        imageMargin.value = '';
    }
};

watch(() => props.show, (val) => {
    if (val) {
        loadData();
    }
}, { immediate: true });

watch(() => props.editData, () => {
    if (props.show) {
        loadData();
    }
}, { deep: true, immediate: true });

const close = () => {
    emit('close');
};

const openFileManager = () => {
    showMediaManager.value = true;
};

const onMediaSelect = (file: { url: string; name: string; path: string; options?: any }) => {
    const path = file.path || file.url.replace('/storage/uploads/', '');
    imageUrl.value = path;
    if (!imageAlt.value) {
        imageAlt.value = file.name;
    }
    if (file.options) {
        if (file.options.alt) imageAlt.value = file.options.alt;
        if (file.options.width) imageWidth.value = String(file.options.width).replace('px', '');
        if (file.options.height) imageHeight.value = String(file.options.height).replace('px', '');
    }
    showMediaManager.value = false;
};

const normalizePath = (url: string): string => {
    let path = url;
    path = path.replace(/^https?:\/\/[^\/]+/, '');
    return path;
};

const insertImage = () => {
    if (!imageUrl.value) {
        alert('Введите URL изображения');
        return;
    }

    let fullUrl = normalizePath(imageUrl.value);
    if (!fullUrl.startsWith('/storage/uploads/') && !fullUrl.startsWith('/')) {
        fullUrl = `/storage/uploads/${fullUrl}`;
    } else if (!fullUrl.startsWith('/storage/uploads/') && fullUrl.startsWith('/')) {
        fullUrl = `/storage/uploads${fullUrl}`;
    }

    emit('insert', {
        url: fullUrl,
        alt: imageAlt.value,
        title: imageTitle.value,
        width: imageWidth.value,
        height: imageHeight.value,
        align: imageAlign.value,
        float: imageFloat.value,
        margin: imageMargin.value,
        oldUrl: props.editData?.url || undefined,
        _pos: props.editData?._pos || undefined
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

.modal-content.image-modal {
    background: white;
    border-radius: 0.5rem;
    width: 550px;
    max-width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #ddd;
    border-bottom: 1px solid #ccc;
    flex-shrink: 0;
}

.modal-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.modal-close {
    color: #4b5563;
    font-size: 1.75rem;
    font-weight: 700;
    background: none;
    border: none;
    cursor: pointer;
    line-height: 1;
    opacity: 0.7;
}

.modal-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 1.5rem;
    flex: 1;
    overflow-y: auto;
}

.modal-footer {
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

.form-row .admin-form-label {
    width: 160px;
    flex-shrink: 0;
    margin-bottom: 0;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
}

.form-row .admin-form-input,
.form-row .input-group,
.form-row .admin-form-select {
    flex: 1;
}

.input-group {
    display: flex;
    gap: 0.5rem;
    flex: 1;
}

.admin-form-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.admin-form-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.admin-form-select {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    background: white;
}

.admin-form-select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
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
