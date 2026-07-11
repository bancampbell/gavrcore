<template>
    <div v-if="field" class="bg-slate-50 rounded-lg p-3 border border-slate-200">
        <h3 class="text-sm font-medium text-slate-700 mb-3">Настройки поля</h3>

        <div class="space-y-3">
            <!-- Название -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Название</label>
                <input
                    v-model="field.label"
                    type="text"
                    class="admin-form-input text-sm w-full"
                    placeholder="Название поля"
                />
            </div>

            <!-- Имя (name) -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Имя (name)</label>
                <input
                    v-model="field.name"
                    type="text"
                    class="admin-form-input text-sm w-full"
                    placeholder="field_name"
                />
            </div>

            <!-- Placeholder -->
            <div v-if="['text', 'email', 'phone', 'number', 'textarea'].includes(field.type)">
                <label class="block text-xs font-medium text-slate-500 mb-1">Placeholder</label>
                <input
                    v-model="field.placeholder"
                    type="text"
                    class="admin-form-input text-sm w-full"
                    placeholder="Подсказка..."
                />
            </div>

            <!-- Обязательное -->
            <div class="flex items-center gap-3">
                <button
                    @click="field.required = !field.required"
                    type="button"
                    class="admin-toggle"
                    :class="field.required ? 'admin-toggle-on' : 'admin-toggle-off'"
                >
                    <span class="admin-toggle-slider" :class="field.required ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                </button>
                <span class="text-sm text-slate-700">Обязательное</span>
            </div>

            <!-- Опции для select, checkbox, radio -->
            <div v-if="['select', 'checkbox', 'radio'].includes(field.type)">
                <label class="block text-xs font-medium text-slate-500 mb-1">Опции</label>
                <textarea
                    v-model="optionsText"
                    rows="3"
                    class="admin-form-textarea text-sm w-full"
                    placeholder="Опция 1&#10;Опция 2&#10;Опция 3"
                />
                <p class="text-xs text-slate-400 mt-1">Каждая опция на новой строке</p>
            </div>

            <!-- Валидация -->
            <div v-if="['text', 'email', 'phone', 'number'].includes(field.type)">
                <label class="block text-xs font-medium text-slate-500 mb-1">Валидация</label>
                <div class="space-y-2">
                    <div v-if="field.type === 'text' || field.type === 'textarea'">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-500 w-16">Мин.</label>
                            <input
                                v-model.number="field.min_length"
                                type="number"
                                min="0"
                                class="admin-form-input text-sm w-20"
                                placeholder="0"
                            />
                            <label class="text-xs text-slate-500 w-16">Макс.</label>
                            <input
                                v-model.number="field.max_length"
                                type="number"
                                min="0"
                                class="admin-form-input text-sm w-20"
                                placeholder="255"
                            />
                        </div>
                    </div>
                    <div v-if="field.type === 'number'">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-500 w-16">Мин.</label>
                            <input
                                v-model.number="field.min_value"
                                type="number"
                                class="admin-form-input text-sm w-20"
                                placeholder="0"
                            />
                            <label class="text-xs text-slate-500 w-16">Макс.</label>
                            <input
                                v-model.number="field.max_value"
                                type="number"
                                class="admin-form-input text-sm w-20"
                                placeholder="100"
                            />
                        </div>
                    </div>
                    <div v-if="field.type === 'email'">
                        <label class="flex items-center gap-2">
                            <input
                                v-model="field.validate_email"
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-600"
                            />
                            <span class="text-xs text-slate-500">Проверять формат email</span>
                        </label>
                    </div>
                    <div v-if="field.type === 'phone'">
                        <label class="flex items-center gap-2">
                            <input
                                v-model="field.validate_phone"
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-600"
                            />
                            <span class="text-xs text-slate-500">Проверять формат телефона</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Значение по умолчанию -->
            <div v-if="['text', 'email', 'phone', 'number', 'textarea', 'date'].includes(field.type)">
                <label class="block text-xs font-medium text-slate-500 mb-1">Значение по умолчанию</label>
                <input
                    v-model="field.default_value"
                    type="text"
                    class="admin-form-input text-sm w-full"
                    placeholder="Значение по умолчанию"
                />
            </div>

            <!-- CSS класс -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">CSS класс</label>
                <input
                    v-model="field.css_class"
                    type="text"
                    class="admin-form-input text-sm w-full"
                    placeholder="my-custom-class"
                />
            </div>

            <!-- Условия показа -->
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Условия показа</label>

                <div class="space-y-3">
                    <div class="bg-white rounded-lg border border-slate-200 p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-medium text-slate-500">Показывать это поле</span>
                            <span class="text-xs text-slate-400">если</span>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="(condition, index) in field.conditions || []"
                                :key="index"
                                class="flex items-center gap-2"
                            >
                                <select
                                    v-model="condition.field"
                                    class="admin-form-select text-sm flex-[2] min-w-[140px]"
                                >
                                    <option value="">Выберите поле</option>
                                    <option
                                        v-for="f in availableFields"
                                        :key="f.name"
                                        :value="f.name"
                                    >
                                        {{ f.label || f.name }}
                                    </option>
                                </select>

                                <select
                                    v-model="condition.operator"
                                    class="admin-form-select text-sm w-20"
                                >
                                    <option value="equals">равно</option>
                                    <option value="not_equals">не равно</option>
                                </select>

                                <select
                                    v-model="condition.value"
                                    class="admin-form-select text-sm flex-[2] min-w-[140px]"
                                >
                                    <option value="">Выберите значение</option>
                                    <option
                                        v-for="option in getFieldOptions(condition.field)"
                                        :key="option"
                                        :value="option"
                                    >
                                        {{ option }}
                                    </option>
                                </select>

                                <button
                                    @click="removeCondition(index)"
                                    class="text-slate-400 hover:text-red-600 flex-shrink-0"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <button
                                @click="addCondition"
                                class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Добавить условие
                            </button>
                        </div>

                        <div v-if="(field.conditions || []).length > 1" class="mt-3 pt-2 border-t border-slate-200">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-500">Выполняются:</span>
                                <div class="flex rounded-md overflow-hidden border border-slate-200">
                                    <button
                                        @click="field.conditions_logic = 'and'"
                                        class="px-3 py-1 text-xs transition"
                                        :class="field.conditions_logic === 'and' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                                    >
                                        Все условия (И)
                                    </button>
                                    <button
                                        @click="field.conditions_logic = 'or'"
                                        class="px-3 py-1 text-xs transition border-l border-slate-200"
                                        :class="field.conditions_logic === 'or' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                                    >
                                        Любое условие (ИЛИ)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-xs text-slate-400 flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full" :class="hasConditions ? 'bg-green-400' : 'bg-gray-300'"></span>
                        <span v-if="!hasConditions">Поле всегда видно</span>
                        <span v-else>
                            Поле видно если
                            {{ (field.conditions || []).length > 1 ? (field.conditions_logic === 'and' ? 'все' : 'любое') : '' }}
                            {{ (field.conditions || []).length === 1 ? 'условие' : 'условия' }} выполнены
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 pt-2 border-t border-slate-200">
                <button
                    @click="$emit('save', field)"
                    class="admin-btn admin-btn-primary text-sm px-3 py-1.5"
                >
                    Сохранить
                </button>
                <button
                    @click="$emit('cancel')"
                    class="admin-btn admin-btn-secondary text-sm px-3 py-1.5"
                >
                    Отмена
                </button>
            </div>
        </div>
    </div>

    <div v-else class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center text-sm text-slate-400 py-8">
        <p>Выберите поле</p>
        <p class="text-xs">Нажмите на поле в центре</p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Condition {
    field: string;
    operator: 'equals' | 'not_equals';
    value?: string;
}

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
    conditions?: Condition[];
    conditions_logic?: 'and' | 'or';
}

const props = defineProps<{
    field: FormField | null;
    fieldsList: FormField[];
}>();

const emit = defineEmits<{
    (e: 'save', field: FormField): void;
    (e: 'cancel'): void;
}>();

const availableFields = computed(() => {
    if (!props.field) return [];
    return props.fieldsList.filter(f => f.name !== props.field?.name);
});

const hasConditions = computed(() => {
    return !!(props.field?.conditions && props.field.conditions.length > 0);
});

const optionsText = computed({
    get: () => {
        if (!props.field?.options) return '';
        return props.field.options.join('\n');
    },
    set: (value: string) => {
        if (props.field) {
            props.field.options = value.split('\n').filter(s => s.trim());
        }
    }
});

const getFieldOptions = (fieldName: string): string[] => {
    if (!fieldName) return [];
    const sourceField = props.fieldsList.find(f => f.name === fieldName);
    if (!sourceField) return [];
    return sourceField.options || [];
};

const addCondition = () => {
    if (!props.field) return;
    if (!props.field.conditions) {
        props.field.conditions = [];
    }
    props.field.conditions.push({
        field: '',
        operator: 'equals',
        value: '',
    });
    if (props.field.conditions.length > 1 && !props.field.conditions_logic) {
        props.field.conditions_logic = 'and';
    }
};

const removeCondition = (index: number) => {
    if (!props.field?.conditions) return;
    props.field.conditions.splice(index, 1);
    if (props.field.conditions.length <= 1) {
        props.field.conditions_logic = 'and';
    }
};
</script>
