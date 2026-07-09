<template>
    <div v-if="show" class="modal-overlay" @mousedown="checkClose">
        <div class="modal-content" @mousedown.stop>
            <div class="modal-header">
                <h3 class="modal-title">{{ isEdit ? 'Редактировать категорию' : 'Создать категорию' }}</h3>
                <button @click="close" class="modal-close">×</button>
            </div>

            <div class="modal-body">
                <form @submit.prevent="save" class="space-y-4">
                    <div class="form-row">
                        <label class="admin-form-label">Название *</label>
                        <input
                            v-model="localForm.name"
                            type="text"
                            class="admin-form-input"
                            placeholder="Введите название"
                            required
                        />
                    </div>

                    <div class="form-row">
                        <label class="admin-form-label">Алиас</label>
                        <input
                            v-model="localForm.alias"
                            type="text"
                            class="admin-form-input"
                            placeholder="останется пустым - сгенерируется автоматически"
                        />
                    </div>

                    <div class="form-row">
                        <label class="admin-form-label">Родительская категория</label>
                        <select v-model="localForm.parent_id" class="admin-form-select">
                            <option :value="null">— Без родителя —</option>
                            <option v-for="(name, id) in parentOptions" :key="id" :value="id">
                                {{ name }}
                            </option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="admin-form-label">Описание</label>
                        <textarea
                            v-model="localForm.description"
                            rows="4"
                            class="admin-form-textarea"
                            placeholder="Введите описание..."
                        ></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
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

const checkClose = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        close();
    }
};

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
