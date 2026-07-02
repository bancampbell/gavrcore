<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-all" @click="close"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ isEdit ? 'Редактировать галерею' : 'Создать галерею' }}
                    </h3>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <!-- Название -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Введите название..."
                        />
                    </div>

                    <!-- Тип -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Тип галереи *</label>
                        <select
                            v-model="form.type"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="grid">Сетка (Grid)</option>
                            <option value="slideshow">Слайд-шоу (Slideshow)</option>
                            <option value="slider">Слайдер (Slider)</option>
                            <option value="switcher">Switcher</option>
                        </select>
                    </div>

                    <!-- Статус -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                        <div class="flex items-center gap-3">
                            <button
                                @click="form.status = !form.status"
                                type="button"
                                class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                :class="form.status ? 'bg-indigo-600' : 'bg-gray-300'"
                            >
                                <span
                                    class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                    :class="form.status ? 'translate-x-4.5' : 'translate-x-0.5'"
                                />
                            </button>
                            <span class="text-sm text-gray-700">{{ form.status ? 'Опубликовано' : 'Черновик' }}</span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
                    <button
                        @click="close"
                        class="px-4 py-2 rounded-xl bg-gray-200 text-gray-700 hover:bg-gray-300 transition font-medium"
                    >
                        Отмена
                    </button>
                    <button
                        @click="save"
                        :disabled="loading"
                        class="px-4 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700 transition font-medium disabled:opacity-50"
                    >
                        <span v-if="loading" class="inline-block animate-spin mr-2">⏳</span>
                        {{ isEdit ? 'Сохранить' : 'Создать' }}
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

const close = () => {
    emit('close');
};

const save = () => {
    if (!form.value.title.trim()) {
        return;
    }
    emit('save', form.value);
};

// Заполняем форму при редактировании
watch(() => props.galleryData, (data) => {
    if (data) {
        form.value = {
            title: data.title || '',
            type: data.type || 'grid',
            status: data.status ?? true,
        };
    }
}, { immediate: true });

// Сбрасываем форму при закрытии
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
