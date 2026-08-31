<template>
    <div class="form-renderer">
        <h3 v-if="form?.title">{{ form.title }}</h3>
        <p v-if="form?.description" class="form-subtitle">{{ form.description }}</p>

        <div v-if="loading" class="text-center py-4">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div>
        </div>

        <div v-else-if="error" class="form-error">{{ error }}</div>

        <div v-else-if="form && form.fields && form.fields.length > 0">
            <form @submit.prevent="submitForm">
                <template v-for="(field, index) in form.fields" :key="field.id || index">
                    <div v-if="checkFieldVisibility(field)" class="form-group">
                        <label :for="getFieldName(field, index)">
                            {{ field.label || field.name || 'Поле' }}
                            <span v-if="field.required" class="required">*</span>
                        </label>

                        <input v-if="field.type === 'text'" :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" type="text" :placeholder="field.placeholder || ''" :required="field.required || false" />

                        <template v-if="field.type === 'email'">
                            <input :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" type="email" :placeholder="user?.email || 'Ваш email'" :required="field.required || false" />
                            <p class="form-hint">По умолчанию — ваш email, можно изменить</p>
                        </template>

                        <input v-else-if="field.type === 'phone'" :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" type="tel" :placeholder="field.placeholder || ''" :required="field.required || false" />
                        <input v-else-if="field.type === 'number'" :id="getFieldName(field, index)" v-model.number="formData[getFieldName(field, index)]" type="number" :placeholder="field.placeholder || ''" :required="field.required || false" />
                        <input v-else-if="field.type === 'date'" :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" type="date" :required="field.required || false" />
                        <input v-else-if="field.type === 'url'" :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" type="url" :placeholder="field.placeholder || 'https://example.com'" :required="field.required || false" />
                        <div v-else-if="field.type === 'color'" class="flex items-center gap-3">
                            <input :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" type="color" :required="field.required || false" class="w-12 h-12 p-1 rounded border border-gray-300 cursor-pointer" />
                            <span class="text-sm text-gray-500">{{ formData[getFieldName(field, index)] || '#000000' }}</span>
                        </div>
                        <input v-else-if="field.type === 'time'" :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" type="time" :required="field.required || false" />
                        <input v-else-if="field.type === 'datetime'" :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" type="datetime-local" :required="field.required || false" />
                        <div v-else-if="field.type === 'range'" class="flex items-center gap-3">
                            <input :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" type="range" :min="field.min_value || 0" :max="field.max_value || 100" :required="field.required || false" class="flex-1" />
                            <span class="text-sm text-gray-500 min-w-[40px]">{{ formData[getFieldName(field, index)] || 0 }}</span>
                        </div>
                        <div v-else-if="field.type === 'rating'" class="rating-group">
                            <button v-for="star in 5" :key="star" @click="formData[getFieldName(field, index)] = star" type="button" class="rating-star" :class="star <= (formData[getFieldName(field, index)] || 0) ? 'active' : 'inactive'">★</button>
                            <span class="text-sm text-gray-500 ml-2">{{ formData[getFieldName(field, index)] || 0 }} / 5</span>
                        </div>
                        <div v-else-if="field.type === 'toggle'" class="toggle-group">
                            <button @click="formData[getFieldName(field, index)] = !formData[getFieldName(field, index)]" type="button" class="admin-toggle" :class="formData[getFieldName(field, index)] ? 'admin-toggle-on' : 'admin-toggle-off'">
                                <span class="admin-toggle-slider" :class="formData[getFieldName(field, index)] ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                            </button>
                            <span class="toggle-label">{{ formData[getFieldName(field, index)] ? 'Да' : 'Нет' }}</span>
                        </div>
                        <textarea v-else-if="field.type === 'textarea'" :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" :placeholder="field.placeholder || ''" :required="field.required || false" rows="4"></textarea>
                        <select v-else-if="field.type === 'select'" :id="getFieldName(field, index)" v-model="formData[getFieldName(field, index)]" :required="field.required || false">
                            <option value="">Выберите...</option>
                            <option v-for="option in (field.options || [])" :key="option" :value="option">{{ option }}</option>
                        </select>
                        <div v-else-if="field.type === 'checkbox'" class="checkbox-group">
                            <label v-for="option in (field.options || [])" :key="option">
                                <input v-model="formData[getFieldName(field, index)]" type="checkbox" :value="option" /> {{ option }}
                            </label>
                        </div>
                        <div v-else-if="field.type === 'radio'" class="radio-group">
                            <label v-for="option in (field.options || [])" :key="option">
                                <input v-model="formData[getFieldName(field, index)]" type="radio" :value="option" /> {{ option }}
                            </label>
                        </div>
                        <input v-else-if="field.type === 'file'" :id="getFieldName(field, index)" type="file" @change="handleFileUpload($event, getFieldName(field, index))" :required="field.required || false" />
                    </div>
                </template>

                <button type="submit" class="form-submit-btn" :disabled="submitting">{{ submitting ? 'Отправка...' : 'Отправить' }}</button>
            </form>

            <div v-if="success" class="form-success">{{ successMessage }}</div>
            <div v-if="errorMessage" class="form-error">{{ errorMessage }}</div>
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

interface Condition { field: string; operator: 'equals' | 'not_equals'; value?: string; }
interface FormField { id?: string; type: string; label?: string; name: string; placeholder?: string; required?: boolean; options?: string[]; min_value?: number; max_value?: number; conditions?: Condition[]; conditions_logic?: 'and' | 'or'; }
interface Form { id: number; title: string; description?: string; fields: FormField[]; }

const props = defineProps<{ formId: number; centered?: boolean; }>();
const form = ref<Form | null>(null);
const formData = reactive<Record<string, any>>({});
const submitting = ref(false);
const success = ref(false);
const errorMessage = ref('');
const loading = ref(true);
const error = ref('');
const successMessage = ref('Спасибо! Форма успешно отправлена.');

const getFieldName = (field: FormField, index: number): string => {
    return field.name?.trim() || field.id || 'field_' + index;
};

const checkFieldVisibility = (field: FormField): boolean => {
    if (!field || !field.conditions || field.conditions.length === 0) return true;
    const results = field.conditions.map(condition => {
        if (!condition.field) return true;
        const value = formData[condition.field];
        switch (condition.operator) {
            case 'equals': return String(value) === String(condition.value);
            case 'not_equals': return String(value) !== String(condition.value);
            default: return true;
        }
    });
    if (field.conditions_logic === 'or') return results.some(r => r === true);
    return results.every(r => r === true);
};

const loadForm = async () => {
    loading.value = true; error.value = '';
    try {
        const response = await axios.get(`/api/forms/${props.formId}`);
        form.value = response.data;
        if (form.value?.fields) {
            form.value.fields.forEach((field, index) => {
                const name = getFieldName(field, index);
                if (field.type === 'email' && user?.email) formData[name] = user.email;
                else if (field.type === 'checkbox') formData[name] = [];
                else formData[name] = '';
            });
        }
    } catch (err: any) {
        if (err.response?.status === 404) error.value = 'Форма не найдена';
        else error.value = 'Не удалось загрузить форму';
    } finally { loading.value = false; }
};

const handleFileUpload = (event: Event, fieldName: string) => {
    const target = event.target as HTMLInputElement;
    if (target.files) formData[fieldName] = target.files[0];
};

const submitForm = async () => {
    submitting.value = true; errorMessage.value = ''; success.value = false;
    try {
        const formDataToSend = new FormData();
        Object.keys(formData).forEach(key => {
            const value = formData[key];
            if (value instanceof File) formDataToSend.append(key, value);
            else if (Array.isArray(value)) value.forEach(item => formDataToSend.append(key + '[]', item));
            else formDataToSend.append(key, value);
        });
        await axios.post(`/api/forms/${props.formId}/submit`, formDataToSend, { headers: { 'Content-Type': 'multipart/form-data' } });
        success.value = true;
        if (form.value?.fields) {
            form.value.fields.forEach((field, index) => {
                const name = getFieldName(field, index);
                if (field.type === 'email' && user?.email) formData[name] = user.email;
                else if (field.type === 'checkbox') formData[name] = [];
                else formData[name] = '';
            });
        }
    } catch (err: any) { errorMessage.value = err.response?.data?.message || 'Ошибка отправки формы'; }
    finally { submitting.value = false; }
};

onMounted(() => { loadForm(); });
</script>

<style scoped>
.form-hint { font-size: 12px; color: #9ca3af; margin-top: 4px; }
</style>