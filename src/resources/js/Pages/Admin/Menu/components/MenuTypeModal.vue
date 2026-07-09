<template>
    <div v-if="show" class="modal-overlay" @mousedown="checkClose">
        <div class="modal-content" @mousedown.stop>
            <div class="modal-header">
                <h3 class="modal-title">{{ isEdit ? 'Редактировать тип меню' : 'Создать тип меню' }}</h3>
                <button @click="close" class="modal-close">×</button>
            </div>

            <div class="modal-body">
                <form @submit.prevent="save" class="space-y-4">
                    <div class="form-row">
                        <label class="admin-form-label">Название *</label>
                        <input
                            v-model="formData.title"
                            type="text"
                            class="admin-form-input"
                            placeholder="Введите название"
                            required
                        />
                    </div>

                    <div class="form-row">
                        <label class="admin-form-label">Алиас</label>
                        <input
                            v-model="formData.alias"
                            type="text"
                            class="admin-form-input"
                            placeholder="останется пустым - сгенерируется автоматически"
                        />
                    </div>

                    <div class="form-row">
                        <label class="admin-form-label">Описание</label>
                        <textarea
                            v-model="formData.description"
                            rows="4"
                            class="admin-form-textarea"
                            placeholder="Введите описание..."
                        ></textarea>
                    </div>

                    <div class="form-row">
                        <label class="admin-form-label">Статус</label>
                        <div class="flex items-center gap-3">
                            <button
                                @click="formData.status = !formData.status"
                                type="button"
                                class="admin-toggle"
                                :class="formData.status ? 'admin-toggle-on' : 'admin-toggle-off'"
                            >
                                <span
                                    class="admin-toggle-slider"
                                    :class="formData.status ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                />
                            </button>
                            <span class="text-sm text-slate-700">{{ formData.status ? 'Активно' : 'Не активно' }}</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="admin-form-label">Порядок сортировки</label>
                        <input
                            v-model.number="formData.ordering"
                            type="number"
                            class="admin-form-input"
                            style="width: 128px;"
                            placeholder="0"
                        />
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

const checkClose = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        close();
    }
};

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
