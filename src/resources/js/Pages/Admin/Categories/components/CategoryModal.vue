<template>
    <div v-if="show" class="modal-overlay">
        <div class="category-modal">
            <div class="category-modal-header">
                <h3>{{ isEdit ? 'Редактировать категорию' : 'Создать категорию' }}</h3>
                <button @click="close" class="category-modal-close">×</button>
            </div>

            <div class="tab-content-fixed">
                <form @submit.prevent="save" class="space-y-4">
                    <div class="form-row">
                        <label class="form-label">Название *</label>
                        <input
                            v-model="localForm.name"
                            type="text"
                            class="form-input"
                            placeholder="Введите название"
                            required
                        />
                    </div>

                    <div class="form-row">
                        <label class="form-label">Алиас</label>
                        <input
                            v-model="localForm.alias"
                            type="text"
                            class="form-input"
                            placeholder="останется пустым - сгенерируется автоматически"
                        />
                    </div>

                    <div class="form-row">
                        <label class="form-label">Родительская категория</label>
                        <select v-model="localForm.parent_id" class="form-input">
                            <option :value="null">— Без родителя —</option>
                            <option v-for="(name, id) in parentOptions" :key="id" :value="id">
                                {{ name }}
                            </option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Описание</label>
                        <textarea
                            v-model="localForm.description"
                            rows="4"
                            class="form-textarea"
                            placeholder="Введите описание..."
                        ></textarea>
                    </div>
                </form>
            </div>

            <div class="category-modal-footer">
                <button @click="close" class="btn-cancel">Отмена</button>
                <button @click="save" class="btn-primary" :disabled="loading">
                    {{ loading ? 'Сохранение...' : (isEdit ? 'Обновить' : 'Создать') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

interface CategoryFormData {
    name: string;
    alias: string;
    description: string;
    parent_id: number | null;
}

const props = defineProps<{
    show: boolean;
    isEdit: boolean;
    categoryData?: any;
    parentOptions: Record<number, string>;
    loading?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', data: CategoryFormData): void;
}>();

const localForm = ref<CategoryFormData>({
    name: '',
    alias: '',
    description: '',
    parent_id: null,
});

watch(() => props.show, (val) => {
    if (val) {
        if (props.isEdit && props.categoryData) {
            localForm.value = {
                name: props.categoryData.name || '',
                alias: props.categoryData.alias || '',
                description: props.categoryData.description || '',
                parent_id: props.categoryData.parent_id || null,
            };
        } else {
            localForm.value = {
                name: '',
                alias: '',
                description: '',
                parent_id: null,
            };
        }
    }
});

const close = () => {
    emit('close');
};

const save = () => {
    if (!localForm.value.name) {
        alert('Введите название');
        return;
    }

    emit('save', { ...localForm.value });
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

.category-modal {
    background: white;
    border-radius: 0.5rem;
    width: 550px;
    max-width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    max-height: 85vh;
}

.category-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #f3f4f6;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.category-modal-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.category-modal-close {
    color: #4b5563;
    font-size: 1.75rem;
    font-weight: 700;
    background: none;
    border: none;
    cursor: pointer;
    line-height: 1;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.category-modal-close:hover {
    opacity: 1;
    color: #1f2937;
}

.tab-content-fixed {
    padding: 1.5rem;
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

.form-row {
    margin-bottom: 1rem;
}

.form-row .form-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
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

.form-textarea {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s;
    resize: vertical;
}

.form-textarea:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.category-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
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

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
