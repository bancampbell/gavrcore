// FILE: resources/js/modules/FormBuilder/ui/composables/useFormBuilder.ts
import { ref } from 'vue';
import { formApi } from '../../infrastructure/api/form-api';
import type { FormField } from '../types';
import { FIELD_TYPES } from '../constants/fieldTypes';

const FIELD_TYPE_MIME = 'application/x-formbuilder-field-type';
const FIELD_INDEX_MIME = 'application/x-formbuilder-field-index';

/**
 * Безопасное клонирование объекта поля
 */
function cloneField(field: FormField): FormField {
    return JSON.parse(JSON.stringify(field));
}

export function useFormBuilder(formFields: FormField[]) {
    const fields = ref<FormField[]>(Array.isArray(formFields) ? formFields : []);
    const saving = ref(false);
    const editingIndex = ref<number | null>(null);
    const editingField = ref<FormField | null>(null);
    const isDraggingOver = ref(false);
    const dragIndex = ref<number | null>(null);
    const draggedFieldType = ref<any>(null);

    const generateId = () => 'field_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);

    const getFieldIcon = (type: string) => {
        return FIELD_TYPES.find(ft => ft.type === type)?.icon || '📝';
    };

    const createField = (type: string): FormField => {
        const base: FormField = {
            id: generateId(),
            type,
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
                base.min_length = 0;
                base.max_length = 255;
                break;
            case 'number':
                base.min_value = 0;
                base.max_value = 100;
                break;
            case 'email':
                base.validate_email = true;
                break;
            case 'phone':
                base.validate_phone = true;
                break;
            case 'select':
            case 'checkbox':
            case 'radio':
                base.options = ['Опция 1', 'Опция 2', 'Опция 3'];
                break;
            case 'range':
                base.min_value = 0;
                base.max_value = 100;
                break;
            case 'rating':
                base.max_value = 5;
                break;
            case 'toggle':
                base.default_value = '0';
                break;
        }
        return base;
    };

    /**
     * Добавляет поле в конец списка без автоматического выбора.
     * Используется для массовых операций или для обратной совместимости.
     */
    const addField = (type: string) => {
        fields.value.push(createField(type));
    };

    /**
     * Добавляет поле и сразу переводит его в режим редактирования.
     * Используется при перетаскивании из палитры.
     */
    const addFieldAndSelect = (type: string) => {
        const newField = createField(type);
        fields.value.push(newField);
        // Новое поле — последнее в массиве
        const newIndex = fields.value.length - 1;
        editingIndex.value = newIndex;
        editingField.value = cloneField(newField);
    };

    const removeField = (index: number) => {
        fields.value.splice(index, 1);
        if (editingIndex.value === index) {
            cancelEditField();
        } else if (editingIndex.value !== null && editingIndex.value > index) {
            editingIndex.value--;
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
        // Сразу переключаем редактирование на скопированное поле
        editingIndex.value = index + 1;
        editingField.value = cloneField(newField);
    };

    const editField = (index: number) => {
        editingIndex.value = index;
        editingField.value = cloneField(fields.value[index]);
    };

    const cancelEditField = () => {
        editingIndex.value = null;
        editingField.value = null;
    };

    const saveFieldSettings = (field: FormField) => {
        if (editingIndex.value !== null) {
            fields.value[editingIndex.value] = field;
            cancelEditField();
        }
    };

    const onDragStartPalette = (event: DragEvent, fieldType: any) => {
        draggedFieldType.value = fieldType;
        event.dataTransfer?.setData(FIELD_TYPE_MIME, fieldType.type);
        if (event.dataTransfer) event.dataTransfer.effectAllowed = 'copy';
    };

    const onDragOverCanvas = (event: DragEvent) => {
        event.preventDefault();
        isDraggingOver.value = true;
    };
    const onDragLeaveCanvas = () => {
        isDraggingOver.value = false;
    };

    const onDropOnCanvas = (event: DragEvent) => {
        event.preventDefault();
        isDraggingOver.value = false;
        const type = event.dataTransfer?.getData(FIELD_TYPE_MIME) || draggedFieldType.value?.type;
        if (type) {
            // Используем addFieldAndSelect вместо addField
            addFieldAndSelect(type);
        }
        draggedFieldType.value = null;
    };

    const onDragStartField = (event: DragEvent, index: number) => {
        dragIndex.value = index;
        event.dataTransfer?.setData(FIELD_INDEX_MIME, String(index));
        if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move';
    };

    const onDragOverField = (event: DragEvent) => {
        event.preventDefault();
    };

    const onDropField = (event: DragEvent, dropIndex: number) => {
        event.preventDefault();
        if (dragIndex.value === null || dragIndex.value === dropIndex) {
            dragIndex.value = null;
            return;
        }
        const item = fields.value.splice(dragIndex.value, 1)[0];
        fields.value.splice(dropIndex, 0, item);

        // После перемещения корректируем указатель редактируемого поля
        if (editingIndex.value === dragIndex.value) {
            editingIndex.value = dropIndex;
            // Обновляем editingField, чтобы он ссылался на перемещённое поле
            editingField.value = cloneField(fields.value[dropIndex]);
        } else if (editingIndex.value !== null) {
            // Если редактируемое поле не перемещалось, но его индекс мог измениться
            // из-за сдвига, проверяем, не сдвинулся ли он
            const oldEditIndex = editingIndex.value;
            if (dragIndex.value < oldEditIndex && dropIndex >= oldEditIndex) {
                // Перетащили поле сверху вниз — индекс редактируемого сместился вверх
                editingIndex.value = oldEditIndex - 1;
            } else if (dragIndex.value > oldEditIndex && dropIndex <= oldEditIndex) {
                // Перетащили поле снизу вверх — индекс редактируемого сместился вниз
                editingIndex.value = oldEditIndex + 1;
            }
            // Обновляем editingField
            if (editingIndex.value !== null) {
                editingField.value = cloneField(fields.value[editingIndex.value]);
            }
        }
        dragIndex.value = null;
    };

    const saveFields = async (formId: number): Promise<boolean> => {
        saving.value = true;
        try {
            await formApi.updateFormFields(formId, fields.value);
            return true;
        } catch {
            return false;
        } finally {
            saving.value = false;
        }
    };

    return {
        fields,
        saving,
        editingIndex,
        editingField,
        isDraggingOver,
        dragIndex,
        fieldTypes: FIELD_TYPES,
        getFieldIcon,
        addField,
        addFieldAndSelect,
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
    };
}