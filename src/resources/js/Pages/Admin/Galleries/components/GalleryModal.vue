<template>
    <Teleport to="body">
        <div v-if="show" class="modal-overlay" @mousedown="checkClose">
            <div class="modal-content" @mousedown.stop>
                <div class="modal-header">
                    <h3 class="modal-title">
                        {{ isEdit ? 'Редактировать галерею' : 'Создать галерею' }}
                    </h3>
                    <button @click="close" class="modal-close">×</button>
                </div>

                <div class="modal-body">
                    <!-- Название -->
                    <div class="form-row">
                        <label class="admin-form-label">Название *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="admin-form-input"
                            placeholder="Введите название..."
                        />
                    </div>

                    <!-- Тип -->
                    <div class="form-row">
                        <label class="admin-form-label">Тип галереи *</label>
                        <select
                            v-model="form.type"
                            class="admin-form-select"
                        >
                            <option value="grid">Сетка (Grid)</option>
                            <option value="slideshow">Слайд-шоу (Slideshow)</option>
                            <option value="slider">Слайдер (Slider)</option>
                            <option value="switcher">Switcher</option>
                        </select>
                    </div>

                    <!-- Статус -->
                    <div class="form-row">
                        <label class="admin-form-label">Статус</label>
                        <div class="flex items-center gap-3">
                            <button
                                @click="form.status = !form.status"
                                type="button"
                                class="admin-toggle"
                                :class="form.status ? 'admin-toggle-on' : 'admin-toggle-off'"
                            >
                                <span
                                    class="admin-toggle-slider"
                                    :class="form.status ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                />
                            </button>
                            <span class="text-sm text-slate-700">{{ form.status ? 'Опубликовано' : 'Черновик' }}</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="close" class="btn-cancel">Отмена</button>
                    <button
                        @click="save"
                        :disabled="loading || !form.title.trim()"
                        class="btn-primary"
                    >
                        {{ loading ? 'Сохранение...' : (isEdit ? 'Сохранить' : 'Создать') }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    isEdit?: boolean;
    galleryData?: any;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', data: any): void;
}>();

const loading = ref(false);
const form = ref({
    title: '',
    type: 'grid',
    status: true,
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
    if (!form.value.title.trim()) {
        return;
    }
    emit('save', form.value);
};

watch(() => props.galleryData, (data) => {
    if (data) {
        form.value = {
            title: data.title || '',
            type: data.type || 'grid',
            status: data.status ?? true,
        };
    }
}, { immediate: true });

watch(() => props.show, (val) => {
    if (!val && !props.isEdit) {
        form.value = {
            title: '',
            type: 'grid',
            status: true,
        };
    }
});
</script>
