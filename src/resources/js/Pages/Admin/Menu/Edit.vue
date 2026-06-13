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
                                    v-model="form.link_value_display"
                                    type="text"
                                    class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    :placeholder="form.link_type === 'material' ? 'Выберите материал' : 'https://...'"
                                    :readonly="form.link_type === 'material'"
                                />
                                <button
                                    v-if="form.link_type === 'material'"
                                    @click="showMaterialModal = true"
                                    type="button"
                                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm whitespace-nowrap"
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
                            @change="onMenuTypeChange"
                            class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option v-for="type in menuTypes" :key="type.id" :value="type.id">
                                {{ type.title }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Позиция</h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    value="first"
                                    v-model="positionType"
                                    class="rounded border-gray-300"
                                />
                                <span class="text-sm text-gray-700">Первым (в начало списка)</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    value="after"
                                    v-model="positionType"
                                    class="rounded border-gray-300"
                                />
                                <span class="text-sm text-gray-700">После элемента</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    value="keep"
                                    v-model="positionType"
                                    class="rounded border-gray-300"
                                />
                                <span class="text-sm text-gray-700">Оставить текущую позицию</span>
                            </label>
                        </div>

                        <!-- Выбор элемента, после которого вставить -->
                        <select
                            v-if="positionType === 'after'"
                            v-model="form.after_id"
                            class="mt-2 w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option :value="null">— Выберите элемент —</option>
                            <option v-for="item in orderOptions" :key="item.id" :value="item.id" :disabled="item.id === menuItemId">
                                {{ '—'.repeat(item.level || 0) }} {{ item.title }}
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

        <MaterialSelectorModal
            :show="showMaterialModal"
            :selected-alias="form.link_value"
            @close="showMaterialModal = false"
            @select="selectMaterial"
        />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import EmptyLayout from '@/layouts/EmptyLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import MaterialSelectorModal from './components/MaterialSelectorModal.vue';
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
const orderOptions = ref<(MenuItem & { level: number })[]>([]);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
const showMaterialModal = ref(false);
const positionType = ref<'first' | 'after' | 'keep'>('keep');
let notificationTimeout: number | null = null;

const form = ref({
    menu_type_id: props.menuItem.menu_type_id,
    parent_id: props.menuItem.parent_id,
    title: props.menuItem.title,
    alias: props.menuItem.alias,
    link_type: props.menuItem.link_type,
    link_value: props.menuItem.link_value || '',
    link_value_display: '',
    target: props.menuItem.target,
    status: props.menuItem.status,
    access: props.menuItem.access,
    language: props.menuItem.language,
    after_id: null as number | null,
});

// Следим за изменением типа ссылки
watch(() => form.value.link_type, (newType) => {
    if (newType !== 'material') {
        form.value.link_value = '';
        form.value.link_value_display = '';
    }
});

const onMenuTypeChange = () => {
    loadParentOptions();
    loadOrderOptions();
};

const loadMenuTypes = async () => {
    try {
        const response = await menuTypesApi.getAll();
        menuTypes.value = response.data.data;
        await loadMaterialTitle();
        await loadParentOptions();
        await loadOrderOptions();
    } catch (error) {
        console.error('Error loading menu types:', error);
    }
};

// Загружаем название материала для отображения
const loadMaterialTitle = async () => {
    if (form.value.link_type === 'material' && form.value.link_value) {
        try {
            const response = await axios.get(`/api/materials/by-alias/${form.value.link_value}`, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            if (response.data) {
                form.value.link_value_display = response.data.title;
            }
        } catch (error) {
            form.value.link_value_display = form.value.link_value;
        }
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

// Загружаем опции для выбора порядка
const loadOrderOptions = async () => {
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
            const flat = flatten(response.data);
            flat.sort((a, b) => (a.ordering || 0) - (b.ordering || 0));
            orderOptions.value = flat;
        } catch (error) {
            console.error('Error loading order options:', error);
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

const selectMaterial = (material: { id: number; title: string; alias: string }) => {
    form.value.link_value = material.alias;
    form.value.link_value_display = material.title;
};

// Подготовка данных для отправки
const prepareSubmitData = () => {
    const submitData: any = {
        parent_id: form.value.parent_id,
        title: form.value.title,
        alias: form.value.alias,
        link_type: form.value.link_type,
        link_value: form.value.link_value,
        target: form.value.target,
        status: form.value.status,
        access: form.value.access,
        language: form.value.language,
    };

    // Добавляем информацию о позиции только если нужно переместить
    if (positionType.value === 'first') {
        submitData.position = 'first';
    } else if (positionType.value === 'after' && form.value.after_id) {
        submitData.after_id = form.value.after_id;
    }
    // Если 'keep' - не отправляем позицию, оставляем текущий ordering

    return submitData;
};

const save = async () => {
    if (!form.value.title) {
        showNotification('Введите заголовок', 'error');
        return;
    }

    loading.value = true;
    try {
        await menuItemsApi.update(menuItemId, prepareSubmitData());
        showNotification('Пункт меню сохранён', 'success');

        // После сохранения возвращаем позицию в режим "оставить"
        positionType.value = 'keep';
        form.value.after_id = null;

        // Обновляем опции
        await loadParentOptions();
        await loadOrderOptions();
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
        await menuItemsApi.update(menuItemId, prepareSubmitData());
        router.visit(`/admin/menu/types/${form.value.menu_type_id}/items?message=Пункт+меню+обновлён`);
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};

onMounted(() => {
    loadMenuTypes();
});
</script>
