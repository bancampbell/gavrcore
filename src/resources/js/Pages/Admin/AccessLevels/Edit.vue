<template>
    <EmptyLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">Менеджер уровней доступа: Редактировать уровень доступа</h1>
            </div>
            <div class="px-6 pb-4 flex gap-2">
                <button @click="save" :disabled="loading" class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50">
                    Сохранить
                </button>
                <button @click="saveAndClose" :disabled="loading" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    Сохранить и закрыть
                </button>
                <Link href="/admin/access-levels" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                    Отменить
                </Link>
            </div>
        </div>

        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Название *</label>
                        <input
                            v-model="form.title"
                            @input="updateAlias"
                            type="text"
                            class="w-96 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введите название..."
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Алиас</label>
                        <input
                            v-model="form.alias"
                            type="text"
                            class="w-64 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="останется пустым - сгенерируется автоматически"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex gap-6 px-6 py-6 min-h-[calc(100vh-250px)]">
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Описание</label>
                            <textarea
                                v-model="form.description"
                                rows="5"
                                class="w-full max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Введите описание..."
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-80">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Группы пользователей</h3>
                        <div class="border border-gray-300 rounded-lg p-3">
                            <div v-for="group in groups" :key="group.id" class="flex items-center justify-between mb-2 last:mb-0">
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

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Статус</h3>
                        <div class="flex items-center gap-3">
                            <button
                                @click="form.status = !form.status"
                                type="button"
                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
                                :class="form.status ? 'bg-green-600' : 'bg-gray-400'"
                            >
                                <span
                                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
                                    :class="form.status ? 'translate-x-6' : 'translate-x-1'"
                                />
                            </button>
                            <span class="text-sm font-medium" :class="form.status ? 'text-green-600' : 'text-gray-500'">
                                {{ form.status ? 'Активно' : 'Не активно' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import EmptyLayout from '@/layouts/EmptyLayout.vue';
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
