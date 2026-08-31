<!-- FILE: resources/js/modules/FormBuilder/ui/Builder.vue -->
<template>
    <AdminLayout :user="user">
        <Head><title>{{ title }}</title></Head>
        <div class="flex flex-col h-full w-full">
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">{{ title }}</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button @click="saveFieldsLocal" :disabled="saving" class="admin-btn admin-btn-primary">
                        {{ saving ? 'Сохранение...' : 'Сохранить поля' }}
                    </button>
                    <button @click="cancel" class="admin-btn admin-btn-secondary">Назад</button>
                </div>
            </div>
            <div class="admin-page-content">
                <div class="admin-page-card w-full p-4">
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                        <div class="lg:col-span-1">
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                                <h3 class="text-sm font-medium text-slate-700 mb-3">Добавить поле</h3>
                                <div class="space-y-1">
                                    <div
                                        v-for="fieldType in fieldTypes"
                                        :key="fieldType.type"
                                        draggable="true"
                                        @dragstart="onDragStartPalette($event, fieldType)"
                                        @dragend="onDragEndPalette"
                                        class="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-2 cursor-grab border border-transparent hover:border-blue-200"
                                    >
                                        <span class="text-base">{{ fieldType.icon }}</span> {{ fieldType.label }}
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 mt-3 text-center">Перетащите поле на холст</p>
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <div
                                class="border-2 border-dashed border-slate-300 rounded-lg p-4 min-h-[400px] transition-colors"
                                :class="{ 'border-blue-500 bg-blue-50/50': isDraggingOver }"
                                @dragover="onDragOverCanvas"
                                @dragleave="onDragLeaveCanvas"
                                @drop="onDropOnCanvas"
                            >
                                <div v-if="fields.length === 0" class="text-center py-12 text-slate-400">
                                    <p class="text-sm">Нет полей</p>
                                    <p class="text-xs">Перетащите поле из левой панели</p>
                                </div>
                                <div v-else class="space-y-2">
                                    <div
                                        v-for="(field, index) in fields"
                                        :key="field.id"
                                        draggable="true"
                                        @dragstart="onDragStartField($event, index)"
                                        @dragover="onDragOverField($event)"
                                        @drop.stop="onDropField($event, index)"
                                        class="bg-white border border-slate-200 rounded-lg p-3 hover:border-blue-300 transition cursor-move"
                                        :class="{
                                            'border-blue-500 bg-blue-50': editingIndex === index,
                                            'opacity-50': dragIndex === index
                                        }"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div
                                                class="flex items-center gap-2 flex-1"
                                                @click="editField(index)"
                                            >
                                                <span class="cursor-grab text-slate-400 hover:text-slate-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                    </svg>
                                                </span>
                                                <span class="text-base">{{ getFieldIcon(field.type) }}</span>
                                                <span class="text-sm font-medium text-slate-700 truncate max-w-[120px]">
                                                    {{ field.label || field.name || 'Без названия' }}
                                                </span>
                                                <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded flex-shrink-0">
                                                    {{ field.type }}
                                                </span>
                                                <span v-if="field.required" class="text-xs text-red-500 flex-shrink-0">*</span>
                                            </div>
                                            <div class="flex gap-1">
                                                <button
                                                    @click="duplicateField(index)"
                                                    class="text-slate-400 hover:text-green-600 transition p-1"
                                                    title="Копировать поле"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                </button>
                                                <button
                                                    @click="removeField(index)"
                                                    class="text-slate-400 hover:text-red-600 transition p-1"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <FieldSettings
                                v-if="editingIndex !== null && editingField"
                                :field="editingField"
                                :fields-list="fields"
                                @save="saveFieldSettings"
                                @cancel="cancelEditField"
                            />
                            <div
                                v-else
                                class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center text-sm text-slate-400 py-8"
                            >
                                <p>Выберите поле</p>
                                <p class="text-xs">Нажмите на поле в центре</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import FieldSettings from './components/FieldSettings.vue';
import { useFormBuilder } from './composables/useFormBuilder';

const props = defineProps<{
    user: any;
    form: { id: number; title: string; fields: any[]; };
    title: string;
}>();

const {
    fields,
    saving,
    editingIndex,
    editingField,
    isDraggingOver,
    dragIndex,
    fieldTypes,
    getFieldIcon,
    removeField,
    duplicateField,
    editField,
    cancelEditField,
    saveFieldSettings,
    onDragStartPalette,
    onDragOverCanvas,
    onDragLeaveCanvas,
    onDropOnCanvas,
    onDragStartField,
    onDragOverField,
    onDropField,
    saveFields,
} = useFormBuilder(props.form.fields || []);

const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => notification.value.show = false, 5000);
};

const onDragEndPalette = () => {};

const saveFieldsLocal = async () => {
    const ok = await saveFields(props.form.id);
    if (ok) {
        showNotification('Поля сохранены', 'success');
    } else {
        showNotification('Ошибка сохранения', 'error');
    }
};

const cancel = () => {
    router.visit(`/admin/forms/${props.form.id}/edit`);
};
</script>