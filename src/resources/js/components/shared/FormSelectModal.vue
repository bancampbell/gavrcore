<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Выберите форму</h3>
                <button @click="close" class="modal-close">×</button>
            </div>

            <div class="modal-body">
                <div v-if="loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                </div>

                <div v-else-if="forms.length === 0" class="text-center py-8 text-gray-400">
                    <p>Нет доступных форм</p>
                    <p class="text-sm mt-2">Создайте форму в админке</p>
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="form in forms"
                        :key="form.id"
                        @click="selectForm(form)"
                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition"
                    >
                        <div class="flex-1">
                            <div class="font-medium text-gray-800">{{ form.title }}</div>
                            <div class="text-sm text-gray-500">ID: {{ form.id }} | Поля: {{ form.fields?.length || 0 }}</div>
                            <div class="text-xs text-gray-400">Алиас: {{ form.alias }}</div>
                        </div>
                        <div class="text-sm text-gray-400">
                            {{ form.status ? '✅ Опубликовано' : '❌ Черновик' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button @click="close" class="btn-cancel">Отмена</button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';

interface Form {
    id: number;
    title: string;
    alias: string;
    status: boolean;
    fields: any[];
}

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', form: Form): void;
}>();

const forms = ref<Form[]>([]);
const loading = ref(false);

const loadForms = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/forms/list');
        forms.value = response.data;
    } catch (error) {
        console.error('Error loading forms:', error);
    } finally {
        loading.value = false;
    }
};

const selectForm = (form: Form) => {
    emit('select', form);
    emit('close');
};

const close = () => {
    emit('close');
};

watch(() => props.show, (val) => {
    if (val) {
        loadForms();
    }
});
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 560px;
    max-width: 92%;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.modal-title {
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
}

.modal-close {
    color: #64748b;
    font-size: 24px;
    font-weight: 700;
    background: none;
    border: none;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    transition: color 0.2s ease;
}

.modal-close:hover {
    color: #1e293b;
}

.modal-body {
    padding: 24px;
    flex: 1;
    overflow-y: auto;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 24px;
    border-top: 1px solid #e5e7eb;
    background: #fafafa;
    flex-shrink: 0;
}

.btn-cancel {
    padding: 8px 16px;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    background: #f1f5f9;
}
</style>
