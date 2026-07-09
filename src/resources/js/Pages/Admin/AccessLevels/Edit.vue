<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Менеджер уровней доступа: Редактировать уровень доступа</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button
                        @click="save"
                        :disabled="loading"
                        class="admin-btn admin-btn-primary"
                    >
                        Сохранить
                    </button>
                    <button
                        @click="saveAndClose"
                        :disabled="loading"
                        class="admin-btn admin-btn-secondary"
                    >
                        Сохранить и закрыть
                    </button>
                    <button
                        @click="cancel"
                        class="admin-btn admin-btn-secondary"
                    >
                        Отменить
                    </button>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="admin-page-content">
                <div class="admin-page-card w-full p-6">
                    <!-- Верхняя панель: название + алиас -->
                    <div class="flex flex-wrap items-center gap-6 mb-6">
                        <div class="flex items-center gap-3">
                            <label class="admin-form-label whitespace-nowrap">Название *</label>
                            <input
                                v-model="form.title"
                                @input="updateAlias"
                                type="text"
                                class="admin-form-input"
                                style="width: 384px;"
                                placeholder="Введите название..."
                            />
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="admin-form-label whitespace-nowrap">Алиас</label>
                            <input
                                v-model="form.alias"
                                type="text"
                                class="admin-form-input"
                                style="width: 256px;"
                                placeholder="останется пустым - сгенерируется автоматически"
                            />
                        </div>
                    </div>

                    <!-- Описание + правая панель -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Левая колонка -->
                        <div class="lg:col-span-2">
                            <div>
                                <label class="admin-form-label">Описание</label>
                                <textarea
                                    v-model="form.description"
                                    rows="5"
                                    class="admin-form-textarea w-full max-w-md"
                                    placeholder="Введите описание уровня доступа..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- Правая колонка -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="admin-form-label">Статус</h3>
                                <select
                                    v-model="form.status"
                                    class="admin-form-select w-full"
                                    :class="{
                                        'admin-form-select-status-published': form.status === true,
                                        'admin-form-select-status-draft': form.status === false
                                    }"
                                >
                                    <option :value="true" class="bg-white text-slate-800">Активно</option>
                                    <option :value="false" class="bg-white text-slate-800">Не активно</option>
                                </select>
                            </div>

                            <div>
                                <h3 class="admin-form-label">Группы пользователей</h3>
                                <div class="admin-form-group">
                                    <div
                                        v-for="group in groups"
                                        :key="group.id"
                                        class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-b-0"
                                    >
                                        <span class="text-sm text-gray-700">{{ group.name }}</span>
                                        <button
                                            @click="toggleGroup(group.id)"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="isGroupSelected(group.id) ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                                :class="isGroupSelected(group.id) ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Выберите группы, которые будут иметь этот уровень доступа</p>
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
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import axios from 'axios';

const props = defineProps<{
    user: any;
    title?: string;
    editAccessLevel: any;
    groups: any[];
}>();

const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const form = ref({
    title: '',
    alias: '',
    description: '',
    status: true,
    groups: [] as number[],
});

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = window.setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const transliterate = (text: string): string => {
    const ruMap: Record<string, string> = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e',
        'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
        'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
        'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '',
        'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
    };
    return text.split('').map(char => ruMap[char] || char).join('');
};

const updateAlias = () => {
    if (!form.value.title) {
        form.value.alias = '';
        return;
    }
    let alias = form.value.title
        .toLowerCase()
        .replace(/[^a-zа-яё0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    alias = transliterate(alias);
    form.value.alias = alias;
};

const isGroupSelected = (groupId: number) => {
    return form.value.groups.includes(groupId);
};

const toggleGroup = (groupId: number) => {
    const index = form.value.groups.indexOf(groupId);
    if (index === -1) {
        form.value.groups.push(groupId);
    } else {
        form.value.groups.splice(index, 1);
    }
};

const cancel = () => {
    router.visit('/admin/access-levels');
};

const save = async () => {
    if (!form.value.title) {
        showNotification('Введите название', 'error');
        return;
    }

    loading.value = true;
    try {
        await axios.put(`/admin/access-levels/${props.editAccessLevel.id}`, form.value);
        showNotification('Уровень доступа сохранён', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

const saveAndClose = async () => {
    if (!form.value.title) {
        showNotification('Введите название', 'error');
        return;
    }

    loading.value = true;
    try {
        await axios.put(`/admin/access-levels/${props.editAccessLevel.id}`, form.value);
        router.visit('/admin/access-levels?message=Уровень+доступа+обновлён');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};

onMounted(() => {
    form.value.title = props.editAccessLevel.title;
    form.value.alias = props.editAccessLevel.alias || '';
    form.value.description = props.editAccessLevel.description || '';
    form.value.status = props.editAccessLevel.status;
    form.value.groups = props.editAccessLevel.groups?.map((g: any) => g.id) || [];
});
</script>
