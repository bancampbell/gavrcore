<template>
    <div class="form-renderer">
        <h3 v-if="form?.title">{{ form.title }}</h3>
        <p v-if="form?.description" class="form-subtitle">{{ form.description }}</p>

        <div v-if="loading" class="text-center py-4">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div>
        </div>

        <div v-else-if="error" class="form-error">
            {{ error }}
        </div>

        <div v-else-if="form && form.fields && form.fields.length > 0">
            <form @submit.prevent="submitForm">
                <template v-for="(field, index) in form.fields" :key="field.id || index">
                    <div
                        v-if="checkFieldVisibility(field)"
                        class="form-group"
                    >
                        <label :for="field.name">
                            {{ field.label || field.name || 'Поле' }}
                            <span v-if="field.required" class="required">*</span>
                        </label>

                        <!-- Текст -->
                        <input
                            v-if="field.type === 'text'"
                            :id="field.name"
                            v-model="formData[field.name]"
                            type="text"
                            :placeholder="field.placeholder || ''"
                            :required="field.required || false"
                        />

                        <!-- Email -->
                        <input
                            v-else-if="field.type === 'email'"
                            :id="field.name"
                            v-model="formData[field.name]"
                            type="email"
                            :placeholder="user?.email || 'Ваш email'"
                            :required="field.required || false"
                        />
                        <p v-if="field.type === 'email'" class="form-hint">
                            По умолчанию — ваш email, можно изменить
                        </p>

                        <!-- Телефон -->
                        <input
                            v-else-if="field.type === 'phone'"
                            :id="field.name"
                            v-model="formData[field.name]"
                            type="tel"
                            :placeholder="field.placeholder || ''"
                            :required="field.required || false"
                        />

                        <!-- Число -->
                        <input
                            v-else-if="field.type === 'number'"
                            :id="field.name"
                            v-model.number="formData[field.name]"
                            type="number"
                            :placeholder="field.placeholder || ''"
                            :required="field.required || false"
                        />

                        <!-- Дата -->
                        <input
                            v-else-if="field.type === 'date'"
                            :id="field.name"
                            v-model="formData[field.name]"
                            type="date"
                            :required="field.required || false"
                        />

                        <!-- URL -->
                        <input
                            v-else-if="field.type === 'url'"
                            :id="field.name"
                            v-model="formData[field.name]"
                            type="url"
                            :placeholder="field.placeholder || 'https://example.com'"
                            :required="field.required || false"
                        />

                        <!-- Цвет -->
                        <div v-else-if="field.type === 'color'" class="flex items-center gap-3">
                            <input
                                :id="field.name"
                                v-model="formData[field.name]"
                                type="color"
                                :required="field.required || false"
                                class="w-12 h-12 p-1 rounded border border-gray-300 cursor-pointer"
                            />
                            <span class="text-sm text-gray-500">{{ formData[field.name] || '#000000' }}</span>
                        </div>

                        <!-- Время -->
                        <input
                            v-else-if="field.type === 'time'"
                            :id="field.name"
                            v-model="formData[field.name]"
                            type="time"
                            :required="field.required || false"
                        />

                        <!-- Дата+время -->
                        <input
                            v-else-if="field.type === 'datetime'"
                            :id="field.name"
                            v-model="formData[field.name]"
                            type="datetime-local"
                            :required="field.required || false"
                        />

                        <!-- Ползунок -->
                        <div v-else-if="field.type === 'range'" class="flex items-center gap-3">
                            <input
                                :id="field.name"
                                v-model="formData[field.name]"
                                type="range"
                                :min="field.min_value || 0"
                                :max="field.max_value || 100"
                                :required="field.required || false"
                                class="flex-1"
                            />
                            <span class="text-sm text-gray-500 min-w-[40px]">{{ formData[field.name] || 0 }}</span>
                        </div>

                        <!-- Рейтинг (звезды) -->
                        <div v-else-if="field.type === 'rating'" class="rating-group">
                            <button
                                v-for="star in 5"
                                :key="star"
                                @click="formData[field.name] = star"
                                type="button"
                                class="rating-star"
                                :class="star <= (formData[field.name] || 0) ? 'active' : 'inactive'"
                            >
                                ★
                            </button>
                            <span class="text-sm text-gray-500 ml-2">{{ formData[field.name] || 0 }} / 5</span>
                        </div>

                        <!-- Переключатель -->
                        <div v-else-if="field.type === 'toggle'" class="toggle-group">
                            <button
                                @click="formData[field.name] = !formData[field.name]"
                                type="button"
                                class="admin-toggle"
                                :class="formData[field.name] ? 'admin-toggle-on' : 'admin-toggle-off'"
                            >
                                <span class="admin-toggle-slider" :class="formData[field.name] ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                            </button>
                            <span class="toggle-label">{{ formData[field.name] ? 'Да' : 'Нет' }}</span>
                        </div>

                        <!-- Текстовая область -->
                        <textarea
                            v-else-if="field.type === 'textarea'"
                            :id="field.name"
                            v-model="formData[field.name]"
                            :placeholder="field.placeholder || ''"
                            :required="field.required || false"
                            rows="4"
                        ></textarea>

                        <!-- Выпадающий список -->
                        <select
                            v-else-if="field.type === 'select'"
                            :id="field.name"
                            v-model="formData[field.name]"
                            :required="field.required || false"
                        >
                            <option value="">Выберите...</option>
                            <option v-for="option in (field.options || [])" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>

                        <!-- Чекбокс -->
                        <div v-else-if="field.type === 'checkbox'" class="checkbox-group">
                            <label v-for="option in (field.options || [])" :key="option">
                                <input
                                    v-model="formData[field.name]"
                                    type="checkbox"
                                    :value="option"
                                />
                                {{ option }}
                            </label>
                        </div>

                        <!-- Радиокнопки -->
                        <div v-else-if="field.type === 'radio'" class="radio-group">
                            <label v-for="option in (field.options || [])" :key="option">
                                <input
                                    v-model="formData[field.name]"
                                    type="radio"
                                    :value="option"
                                />
                                {{ option }}
                            </label>
                        </div>

                        <!-- Файл -->
                        <input
                            v-else-if="field.type === 'file'"
                            :id="field.name"
                            type="file"
                            @change="handleFileUpload($event, field.name)"
                            :required="field.required || false"
                        />
                    </div>
                </template>

                <button type="submit" class="form-submit-btn" :disabled="submitting">
                    {{ submitting ? 'Отправка...' : 'Отправить' }}
                </button>
            </form>

            <div v-if="success" class="form-success">
                {{ successMessage }}
            </div>

            <div v-if="errorMessage" class="form-error">
                {{ errorMessage }}
            </div>
        </div>

        <div v-else-if="form && (!form.fields || form.fields.length === 0)" class="text-center py-8 text-gray-400">
            <p>В этой форме пока нет полей</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const user = page.props.auth?.user;

interface Condition {
    field: string;
    operator: 'equals' | 'not_equals';
    value?: string;
}

interface FormField {
    id?: string;
    type: string;
    label?: string;
    name: string;
    placeholder?: string;
    required?: boolean;
    options?: string[];
    min_value?: number;
    max_value?: number;
    conditions?: Condition[];
    conditions_logic?: 'and' | 'or';
}

interface Form {
    id: number;
    title: string;
    description?: string;
    fields: FormField[];
}

const props = defineProps<{
    formId: number;
    centered?: boolean;
}>();

const form = ref<Form | null>(null);
const formData = reactive<Record<string, any>>({});
const submitting = ref(false);
const success = ref(false);
const errorMessage = ref('');
const loading = ref(true);
const error = ref('');
const successMessage = ref('Спасибо! Форма успешно отправлена.');

const checkFieldVisibility = (field: FormField): boolean => {
    if (!field || !field.conditions || field.conditions.length === 0) {
        return true;
    }

    const results = field.conditions.map(condition => {
        if (!condition.field) return true;

        const value = formData[condition.field];
        const operator = condition.operator;
        const conditionValue = condition.value;

        switch (operator) {
            case 'equals':
                return String(value) === String(conditionValue);
            case 'not_equals':
                return String(value) !== String(conditionValue);
            default:
                return true;
        }
    });

    if (field.conditions_logic === 'or') {
        return results.some(r => r === true);
    }
    return results.every(r => r === true);
};

const loadForm = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await axios.get(`/api/forms/${props.formId}`);
        form.value = response.data;

        if (form.value?.fields) {
            form.value.fields.forEach(field => {
                const name = field.name || 'field_' + Math.random().toString(36).substr(2, 5);

                // Если поле email — подставляем email пользователя
                if (field.type === 'email' && user?.email) {
                    formData[name] = user.email;
                } else if (field.type === 'checkbox') {
                    formData[name] = [];
                } else {
                    formData[name] = '';
                }
            });
        }
    } catch (err: any) {
        console.error('Error loading form:', err);
        if (err.response?.status === 404) {
            error.value = 'Форма не найдена';
        } else {
            error.value = 'Не удалось загрузить форму';
        }
    } finally {
        loading.value = false;
    }
};

const handleFileUpload = (event: Event, fieldName: string) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        formData[fieldName] = target.files[0];
    }
};

const submitForm = async () => {
    submitting.value = true;
    errorMessage.value = '';
    success.value = false;

    try {
        const formDataToSend = new FormData();

        Object.keys(formData).forEach(key => {
            const value = formData[key];
            if (value instanceof File) {
                formDataToSend.append(key, value);
            } else if (Array.isArray(value)) {
                value.forEach(item => formDataToSend.append(key + '[]', item));
            } else {
                formDataToSend.append(key, value);
            }
        });

        await axios.post(`/api/forms/${props.formId}/submit`, formDataToSend, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        success.value = true;

        if (form.value?.fields) {
            form.value.fields.forEach(field => {
                const name = field.name || 'field_' + Math.random().toString(36).substr(2, 5);
                if (field.type === 'email' && user?.email) {
                    formData[name] = user.email;
                } else if (field.type === 'checkbox') {
                    formData[name] = [];
                } else {
                    formData[name] = '';
                }
            });
        }
    } catch (err: any) {
        errorMessage.value = err.response?.data?.message || 'Ошибка отправки формы';
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    loadForm();
});
</script>

<style scoped>
.form-hint {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 4px;
}
</style>
