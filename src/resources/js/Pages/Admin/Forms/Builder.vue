<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">{{ title }}</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button
                        @click="saveFields"
                        :disabled="saving"
                        class="admin-btn admin-btn-primary"
                    >
                        {{ saving ? 'Сохранение...' : 'Сохранить поля' }}
                    </button>
                    <button
                        @click="cancel"
                        class="admin-btn admin-btn-secondary"
                    >
                        Назад
                    </button>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="admin-page-content">
                <div class="admin-page-card w-full p-4">
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                        <!-- Левая панель - Палитра полей -->
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
                                        <span class="text-base">{{ fieldType.icon }}</span>
                                        {{ fieldType.label }}
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 mt-3 text-center">
                                    Перетащите поле на холст
                                </p>
                            </div>
                        </div>

                        <!-- Центр - Холст -->
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
                                        @drop="onDropField($event, index)"
                                        class="bg-white border border-slate-200 rounded-lg p-3 hover:border-blue-300 transition cursor-move"
                                        :class="{
                                            'border-blue-500 bg-blue-50': editingIndex === index,
                                            'opacity-50': dragIndex === index
                                        }"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2 flex-1" @click="editField(index)">
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

                        <!-- Правая панель - Настройки поля -->
                        <div class="lg:col-span-2">
                            <FieldSettings
                                v-if="editingIndex !== null"
                                :field="fields[editingIndex]"
                                :fields-list="fields"
                                @save="saveFieldSettings"
                                @cancel="cancelEditField"
                            />
                            <div v-else class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center text-sm text-slate-400 py-8">
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
import axios from 'axios';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import FieldSettings from './components/FieldSettings.vue';

interface FormField {
    id: string;
    type: string;
    label: string;
    name: string;
    placeholder?: string;
    required: boolean;
    options?: string[];
    min_length?: number;
    max_length?: number;
    min_value?: number;
    max_value?: number;
    default_value?: string;
    css_class?: string;
    validate_email?: boolean;
    validate_phone?: boolean;
    conditions?: {
        field: string;
        operator: 'equals' | 'not_equals';
        value?: string;
    }[];
    conditions_logic?: 'and' | 'or';
}

const props = defineProps<{
    user: any;
    form: {
        id: number;
        title: string;
        fields: FormField[];
    };
    title: string;
}>();

const fields = ref<FormField[]>(props.form.fields || []);
const saving = ref(false);
const editingIndex = ref<number | null>(null);
const isDraggingOver = ref(false);
const draggedFieldType = ref<any>(null);
const dragIndex = ref<number | null>(null);

const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });

// ПОЛНЫЙ НАБОР ПОЛЕЙ
const fieldTypes = [
    // Базовые
    { type: 'text', icon: '📝', label: 'Текст' },
    { type: 'textarea', icon: '📄', label: 'Текстовая область' },
    { type: 'email', icon: '✉️', label: 'Email' },
    { type: 'phone', icon: '📞', label: 'Телефон' },
    { type: 'number', icon: '🔢', label: 'Число' },
    { type: 'date', icon: '📅', label: 'Дата' },
    { type: 'select', icon: '📋', label: 'Выпадающий список' },
    { type: 'checkbox', icon: '☑️', label: 'Чекбокс' },
    { type: 'radio', icon: '⭕', label: 'Радиокнопка' },
    { type: 'file', icon: '📎', label: 'Файл' },

    // Дополнительные
    { type: 'url', icon: '🔗', label: 'URL' },
    { type: 'color', icon: '🎨', label: 'Цвет' },
    { type: 'time', icon: '🕐', label: 'Время' },
    { type: 'datetime', icon: '📆', label: 'Дата+время' },
    { type: 'range', icon: '📊', label: 'Ползунок' },
    { type: 'rating', icon: '⭐', label: 'Рейтинг' },
    { type: 'toggle', icon: '🔘', label: 'Переключатель' },
];

const getFieldIcon = (type: string) => {
    const found = fieldTypes.find(ft => ft.type === type);
    return found ? found.icon : '📝';
};

const generateId = () => {
    return 'field_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
};

const addField = (type: string) => {
    const baseField: FormField = {
        id: generateId(),
        type: type,
        label: '',
        name: 'field_' + (fields.value.length + 1),
        placeholder: '',
        required: false,
        css_class: '',
        default_value: '',
    };

    switch (type) {
        case 'text':
        case 'textarea':
            baseField.min_length = 0;
            baseField.max_length = 255;
            break;
        case 'number':
            baseField.min_value = 0;
            baseField.max_value = 100;
            break;
        case 'email':
            baseField.validate_email = true;
            break;
        case 'phone':
            baseField.validate_phone = true;
            break;
        case 'select':
        case 'checkbox':
        case 'radio':
            baseField.options = ['Опция 1', 'Опция 2', 'Опция 3'];
            break;
        case 'date':
            baseField.default_value = '';
            break;
        case 'range':
            baseField.min_value = 0;
            baseField.max_value = 100;
            break;
        case 'rating':
            baseField.max_value = 5;
            break;
        case 'toggle':
            baseField.default_value = '0';
            break;
    }

    fields.value.push(baseField);
};

const removeField = (index: number) => {
    fields.value.splice(index, 1);
    if (editingIndex.value === index) {
        editingIndex.value = null;
    }
};

const duplicateField = (index: number) => {
    const field = fields.value[index];
    const newField = {
        ...field,
        id: generateId(),
        name: field.name + '_copy',
        label: field.label + ' (копия)',
    };
    fields.value.splice(index + 1, 0, newField);
    showNotification('Поле скопировано', 'success');
};

const editField = (index: number) => {
    editingIndex.value = index;
};

const cancelEditField = () => {
    editingIndex.value = null;
};

const saveFieldSettings = (field: FormField) => {
    if (editingIndex.value !== null) {
        fields.value[editingIndex.value] = field;
        cancelEditField();
        showNotification('Настройки поля сохранены', 'success');
    }
};

// Drag & Drop из палитры на холст
const onDragStartPalette = (event: DragEvent, fieldType: any) => {
    draggedFieldType.value = fieldType;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'copy';
        event.dataTransfer.setData('text/plain', fieldType.type);
    }
};

const onDragEndPalette = () => {};

const onDragOverCanvas = (event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'copy';
    }
    isDraggingOver.value = true;
};

const onDragLeaveCanvas = (event: DragEvent) => {
    const relatedTarget = event.relatedTarget as HTMLElement;
    const target = event.target as HTMLElement;
    if (relatedTarget && target && !target.contains(relatedTarget)) {
        isDraggingOver.value = false;
    }
};

const onDropOnCanvas = (event: DragEvent) => {
    event.preventDefault();
    isDraggingOver.value = false;

    const type = event.dataTransfer?.getData('text/plain');
    if (type) {
        const found = fieldTypes.find(f => f.type === type);
        if (found) {
            addField(type);
            showNotification(`Поле "${found.label}" добавлено`, 'success');
        }
    } else if (draggedFieldType.value) {
        const type = draggedFieldType.value.type;
        const found = fieldTypes.find(f => f.type === type);
        if (found) {
            addField(type);
            showNotification(`Поле "${found.label}" добавлено`, 'success');
        }
        draggedFieldType.value = null;
    }
};

// Drag & Drop внутри холста
const onDragStartField = (event: DragEvent, index: number) => {
    dragIndex.value = index;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', String(index));
    }
};

const onDragOverField = (event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
};

const onDropField = (event: DragEvent, dropIndex: number) => {
    event.preventDefault();

    if (dragIndex.value === null || dragIndex.value === dropIndex) {
        dragIndex.value = null;
        return;
    }

    const fromIndex = dragIndex.value;
    const toIndex = dropIndex;

    const item = fields.value.splice(fromIndex, 1)[0];
    fields.value.splice(toIndex, 0, item);

    dragIndex.value = null;
};

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const saveFields = async () => {
    saving.value = true;
    try {
        await axios.put(`/admin/forms/${props.form.id}/fields`, {
            fields: fields.value
        });
        showNotification('Поля сохранены', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка сохранения', 'error');
    } finally {
        saving.value = false;
    }
};

const cancel = () => {
    router.visit(`/admin/forms/${props.form.id}/edit`);
};
</script>
