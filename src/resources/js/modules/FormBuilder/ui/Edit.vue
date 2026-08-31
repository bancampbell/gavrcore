<template>
    <AdminLayout :user="user">
        <Head><title>{{ title }}</title></Head>
        <div class="flex flex-col h-full w-full">
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">{{ title }}</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button @click="save" :disabled="loading" class="admin-btn admin-btn-primary">Сохранить</button>
                    <button @click="saveAndClose" :disabled="loading" class="admin-btn admin-btn-secondary">Сохранить и закрыть</button>
                    <button @click="goToBuilder" class="admin-btn admin-btn-secondary">Конструктор</button>
                    <button @click="cancel" class="admin-btn admin-btn-secondary">Закрыть</button>
                </div>
            </div>
            <div class="admin-page-content">
                <div class="admin-page-card w-full p-6">
                    <div class="max-w-2xl space-y-6">
                        <div>
                            <label class="admin-form-label">Название *</label>
                            <input v-model="form.title" @input="updateAlias" type="text" class="admin-form-input w-full" placeholder="Введите название формы..." />
                        </div>
                        <div>
                            <label class="admin-form-label">Алиас</label>
                            <input v-model="form.alias" @input="onAliasInput" type="text" class="admin-form-input w-full" placeholder="автоматически из названия" />
                            <p class="text-xs text-slate-400 mt-1">Используется для URL и шорткода. Оставьте пустым для автоматической генерации.</p>
                        </div>
                        <div>
                            <label class="admin-form-label">Описание</label>
                            <textarea v-model="form.description" rows="3" class="admin-form-textarea w-full" placeholder="Краткое описание формы..." />
                        </div>
                        <div class="pt-4 border-t border-slate-200">
                            <label class="admin-form-label">Email для уведомлений</label>
                            <input v-model="form.notificationEmailsInput" type="text" class="admin-form-input w-full" placeholder="email@example.com, email2@example.com" />
                            <p class="text-xs text-slate-400 mt-1">Укажите один или несколько email-адресов через запятую.</p>
                        </div>
                        <div>
                            <label class="admin-form-label">Статус</label>
                            <select v-model="form.status" class="admin-form-select w-full max-w-xs">
                                <option :value="true">Опубликовано</option>
                                <option :value="false">Черновик</option>
                            </select>
                        </div>
                        <div class="pt-4 border-t border-slate-200">
                            <label class="admin-form-label">Шорткод</label>
                            <div class="bg-slate-50 p-3 rounded-md font-mono text-sm text-slate-700">[form id="{{ props.form.id }}"]</div>
                            <p class="text-xs text-slate-400 mt-1">Скопируйте этот шорткод и вставьте в материал</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';

interface Form { id: number; title: string; alias: string; description: string | null; status: boolean; fields: any[]; notification_emails?: string[] | null; }
const props = defineProps<{ user: any; form: Form; title: string; }>();
const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
const form = ref({ title: props.form.title, alias: props.form.alias || '', description: props.form.description || '', status: props.form.status, notificationEmailsInput: '' });
const isAliasManuallyEdited = ref(!!props.form.alias);

onMounted(() => { if (props.form.notification_emails && Array.isArray(props.form.notification_emails)) form.value.notificationEmailsInput = props.form.notification_emails.join(', '); });

const showNotification = (message: string, type: 'success' | 'error' = 'success') => { notification.value = { show: true, message, type }; setTimeout(() => notification.value.show = false, 5000); };

const generateAlias = (text: string): string => {
    let alias = text.toLowerCase().replace(/[^a-zа-яё0-9]+/g, '-').replace(/^-+|-+$/g, '');
    const ruMap: Record<string, string> = { 'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'e','ж':'zh','з':'z','и':'i','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t','у':'u','ф':'f','х':'h','ц':'c','ч':'ch','ш':'sh','щ':'sh','ъ':'','ы':'y','ь':'','э':'e','ю':'yu','я':'ya' };
    alias = alias.split('').map(char => ruMap[char] || char).join(''); return alias;
};

const updateAlias = () => { if (isAliasManuallyEdited.value) return; if (!form.value.title) { form.value.alias = ''; return; } form.value.alias = generateAlias(form.value.title); };
const onAliasInput = () => { if (form.value.alias.trim() !== '') isAliasManuallyEdited.value = true; };
watch(() => form.value.alias, (newVal) => { if (newVal === '') isAliasManuallyEdited.value = false; });

const buildPayload = () => {
    const payload: Record<string, any> = { title: form.value.title, status: form.value.status };
    if (form.value.alias?.trim()) payload.alias = form.value.alias.trim();
    if (form.value.description?.trim()) payload.description = form.value.description.trim();
    if (form.value.notificationEmailsInput?.trim()) payload.notification_emails = form.value.notificationEmailsInput.split(',').map(e => e.trim()).filter(e => e);
    else payload.notification_emails = [];
    return payload;
};

const goToBuilder = () => {
    const formId = props.form?.id;
    if (!formId) {
        showNotification('ID формы не определён', 'error');
        return;
    }
    router.visit(`/admin/forms/${formId}/builder`);
};

const save = async () => { if (!form.value.title.trim()) { showNotification('Введите название', 'error'); return; } loading.value = true; try { await axios.put(`/admin/forms/${props.form.id}`, buildPayload()); showNotification('Форма обновлена', 'success'); } catch (error: any) { showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error'); } finally { loading.value = false; } };
const saveAndClose = async () => { if (!form.value.title.trim()) { showNotification('Введите название', 'error'); return; } loading.value = true; try { await axios.put(`/admin/forms/${props.form.id}`, buildPayload()); router.visit('/admin/forms?message=Форма+обновлена'); } catch (error: any) { showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error'); loading.value = false; } };
const cancel = () => { router.visit('/admin/forms'); };
</script>