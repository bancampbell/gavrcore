<template>
    <div v-if="show" class="modal-overlay">
        <div class="menu-type-modal">
            <div class="menu-type-modal-header">
                <h3>{{ isEdit ? 'Редактировать тип меню' : 'Создать тип меню' }}</h3>
                <button @click="close" class="menu-type-modal-close">×</button>
            </div>

            <div class="tab-content-fixed">
                <form @submit.prevent="save" class="space-y-4">
                    <div class="form-row">
                        <label class="form-label">Название *</label>
                        <input
                            v-model="formData.title"
                            type="text"
                            class="form-input"
                            placeholder="Введите название"
                            required
                        />
                    </div>

                    <div class="form-row">
                        <label class="form-label">Алиас</label>
                        <input
                            v-model="formData.alias"
                            type="text"
                            class="form-input"
                            placeholder="останется пустым - сгенерируется автоматически"
                        />
                    </div>

                    <div class="form-row">
                        <label class="form-label">Описание</label>
                        <textarea
                            v-model="formData.description"
                            rows="4"
                            class="form-textarea"
                            placeholder="Введите описание..."
                        ></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Статус</label>
                        <div class="flex items-center gap-2">
                            <input
                                v-model="formData.status"
                                type="checkbox"
                                class="w-4 h-4 text-indigo-600 rounded border-gray-300"
                            />
                            <span class="text-sm text-gray-700">Активно</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Порядок сортировки</label>
                        <input
                            v-model.number="formData.ordering"
                            type="number"
                            class="form-input w-32"
                            placeholder="0"
                        />
                    </div>
                </form>
            </div>

            <div class="menu-type-modal-footer">
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

interface MenuTypeForm {
    title: string;
    alias: string;
    description: string;
    status: boolean;
    ordering: number;
}

const props = defineProps<{
    show: boolean;
    isEdit: boolean;
    menuTypeData?: any;
    loading?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', data: MenuTypeForm): void;
}>();

const formData = ref<MenuTypeForm>({
    title: '',
    alias: '',
    description: '',
    status: true,
    ordering: 0,
});

watch(() => props.show, (val) => {
    if (val) {
        if (props.isEdit && props.menuTypeData) {
            formData.value = {
                title: props.menuTypeData.title || '',
                alias: props.menuTypeData.alias || '',
                description: props.menuTypeData.description || '',
                status: props.menuTypeData.status !== false,
                ordering: props.menuTypeData.ordering || 0,
            };
        } else {
            formData.value = {
                title: '',
                alias: '',
                description: '',
                status: true,
                ordering: 0,
            };
        }
    }
});

const close = () => {
    emit('close');
};

const save = () => {
    if (!formData.value.title) {
        alert('Введите название');
        return;
    }

    emit('save', { ...formData.value });
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

.menu-type-modal {
    background: white;
    border-radius: 0.5rem;
    width: 550px;
    max-width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    max-height: 85vh;
}

.menu-type-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #f3f4f6;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.menu-type-modal-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.menu-type-modal-close {
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

.menu-type-modal-close:hover {
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

.menu-type-modal-footer {
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
