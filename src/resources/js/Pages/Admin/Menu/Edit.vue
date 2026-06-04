<template>
    <EmptyLayout :user="user">
        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">Менеджер меню: Редактировать пункт меню</h1>
            </div>
            <div class="px-6 pb-4 flex gap-2">
                <button @click="save" :disabled="loading" class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50">
                    Сохранить
                </button>
                <button @click="saveAndClose" :disabled="loading" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    Сохранить и закрыть
                </button>
                <Link :href="`/admin/menu/types/${form.menu_type_id}/items`" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                    Отменить
                </Link>
            </div>
        </div>

        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Заголовок *</label>
                        <input
                            v-model="form.title"
                            @input="updateAlias"
                            type="text"
                            class="w-96 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введите заголовок..."
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Тип пункта меню *</label>
                            <select
                                v-model="form.link_type"
                                class="w-full max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="url">URL</option>
                                <option value="material">Материал</option>
                                <option value="separator">Разделитель</option>
                                <option value="heading">Заголовок</option>
                                <option value="external">Внешний URL</option>
                            </select>
                        </div>

                        <div v-if="form.link_type !== 'separator' && form.link_type !== 'heading'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ссылка</label>
                            <div class="flex gap-2 max-w-md">
                                <input
                                    v-model="form.link_value"
                                    type="text"
                                    class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    :placeholder="form.link_type === 'material' ? 'ID материала' : 'https://...'"
                                />
                                <button
                                    v-if="form.link_type === 'material'"
                                    @click="openMaterialModal"
                                    type="button"
                                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm"
                                >
                                    Выбрать
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Целевое окно</label>
                            <select
                                v-model="form.target"
                                class="w-full max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="_self">Текущее окно</option>
                                <option value="_blank">Новое окно</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-80">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Меню</h3>
                        <select
                            v-model="form.menu_type_id"
                            @change="loadParentOptions"
                            class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option v-for="type in menuTypes" :key="type.id" :value="type.id">
                                {{ type.title }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Родительский элемент</h3>
                        <select
                            v-model="form.parent_id"
                            class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option :value="null">— Корневой уровень —</option>
                            <option v-for="item in parentOptions" :key="item.id" :value="item.id" :disabled="item.id === menuItemId">
                                {{ '—'.repeat(item.level || 0) }} {{ item.title }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Статус</h3>
                        <select
                            v-model="form.status"
                            class="w-full border rounded px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 transition"
                            :class="form.status ? 'bg-green-600 text-white border-green-700' : 'bg-red-600 text-white border-red-700'"
                        >
                            <option :value="true" class="bg-white text-gray-800">Опубликовано</option>
                            <option :value="false" class="bg-white text-gray-800">Не опубликовано</option>
                        </select>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Доступ</h3>
                        <select
                            v-model="form.access"
                            class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm"
                        >
                            <option value="all">Все</option>
                            <option value="guest">Только гости</option>
                            <option value="registered">Только зарегистрированные</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import EmptyLayout from '@/layouts/EmptyLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import { menuTypesApi, menuItemsApi, type MenuType, type MenuItem } from '@/api/menu';

const props = defineProps<{
    user: any;
    menuItem: MenuItem;
    menuTypeId: number;
}>();

const menuItemId = props.menuItem.id;
const loading = ref(false);
const menuTypes = ref<MenuType[]>([]);
const parentOptions = ref<(MenuItem & { level: number })[]>([]);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const form = ref({
    menu_type_id: props.menuItem.menu_type_id,
    parent_id: props.menuItem.parent_id,
    title: props.menuItem.title,
    alias: props.menuItem.alias,
    link_type: props.menuItem.link_type,
    link_value: props.menuItem.link_value || '',
    target: props.menuItem.target,
    status: props.menuItem.status,
    access: props.menuItem.access,
    language: props.menuItem.language,
});

const loadMenuTypes = async () => {
    try {
        const response = await menuTypesApi.getAll();
        menuTypes.value = response.data.data;
    } catch (error) {
        console.error('Error loading menu types:', error);
    }
};

const loadParentOptions = async () => {
    if (form.value.menu_type_id) {
        try {
            const response = await menuItemsApi.getTree(form.value.menu_type_id);
            const flatten = (items: MenuItem[], level = 0): (MenuItem & { level: number })[] => {
                let result: (MenuItem & { level: number })[] = [];
                for (const item of items) {
                    if (item.id === menuItemId) continue;
                    result.push({ ...item, level });
                    if (item.children?.length) {
                        const children = flatten(item.children, level + 1).filter(
                            child => child.id !== menuItemId
                        );
                        result = [...result, ...children];
                    }
                }
                return result;
            };
            parentOptions.value = flatten(response.data);
        } catch (error) {
            console.error('Error loading parent options:', error);
        }
    }
};

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = window.setTimeout(() => {
        notification.value.show = false;
    }, 5000);
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

    const ruMap: Record<string, string> = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e',
        'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
        'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
        'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '',
        'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
    };

    alias = alias.split('').map(char => ruMap[char] || char).join('');
    form.value.alias = alias;
};

const save = async () => {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;
    try {
        await menuItemsApi.update(menuItemId, form.value);
        showNotification('Пункт меню сохранён', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

const saveAndClose = async () => {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;
    try {
        await menuItemsApi.update(menuItemId, form.value);
        router.visit(`/admin/menu/types/${form.value.menu_type_id}/items`);
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};

const openMaterialModal = () => {
    alert('Выбор материалов будет добавлен позже');
};

onMounted(() => {
    loadMenuTypes();
    loadParentOptions();
});
</script>
