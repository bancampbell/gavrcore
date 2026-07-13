<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Менеджер меню: Редактировать пункт меню</h1>
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
                <div class="admin-page-card w-full">
                    <!-- Верхняя панель -->
                    <div class="p-6 border-b border-slate-200">
                        <div class="flex flex-wrap items-center gap-6">
                            <div class="flex items-center gap-3">
                                <label class="admin-form-label whitespace-nowrap">Заголовок *</label>
                                <input
                                    v-model="form.title"
                                    @input="updateAlias"
                                    type="text"
                                    class="admin-form-input"
                                    style="width: 384px;"
                                    placeholder="Введите заголовок..."
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
                    </div>

                    <!-- Основная часть -->
                    <div class="flex flex-col lg:flex-row gap-6 p-6 min-h-[calc(100vh-280px)]">
                        <div class="flex-1">
                            <div class="space-y-4">
                                <!-- Тип пункта меню -->
                                <div>
                                    <label class="admin-form-label">Тип пункта меню *</label>
                                    <select
                                        v-model="form.link_type"
                                        class="admin-form-select w-full max-w-md"
                                    >
                                        <option value="url">URL</option>
                                        <option value="material">Материал</option>
                                        <option value="separator">Разделитель</option>
                                        <option value="heading">Заголовок</option>
                                        <option value="external">Внешний URL</option>
                                    </select>
                                </div>

                                <!-- Ссылка -->
                                <div v-if="form.link_type !== 'separator' && form.link_type !== 'heading'">
                                    <label class="admin-form-label">Ссылка</label>
                                    <div class="flex gap-2 max-w-md">
                                        <input
                                            v-model="form.link_value_display"
                                            type="text"
                                            class="admin-form-input flex-1"
                                            :placeholder="form.link_type === 'material' ? 'Выберите материал' : 'https://...'"
                                            :readonly="form.link_type === 'material'"
                                        />
                                        <button
                                            v-if="form.link_type === 'material'"
                                            @click="showMaterialModal = true"
                                            type="button"
                                            class="admin-btn admin-btn-secondary whitespace-nowrap"
                                        >
                                            Выбрать
                                        </button>
                                    </div>
                                </div>

                                <!-- Целевое окно -->
                                <div>
                                    <label class="admin-form-label">Целевое окно</label>
                                    <select
                                        v-model="form.target"
                                        class="admin-form-select w-full max-w-md"
                                    >
                                        <option value="_self">Текущее окно</option>
                                        <option value="_blank">Новое окно</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Правая панель -->
                        <div class="w-full lg:w-80 flex-shrink-0 space-y-4">
                            <!-- Меню -->
                            <div>
                                <h3 class="admin-form-label">Меню</h3>
                                <select
                                    v-model="form.menu_type_id"
                                    @change="onMenuTypeChange"
                                    class="admin-form-select w-full"
                                >
                                    <option v-for="type in menuTypes" :key="type.id" :value="type.id">
                                        {{ type.title }}
                                    </option>
                                </select>
                            </div>

                            <!-- Позиция -->
                            <div>
                                <h3 class="admin-form-label">Позиция</h3>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            value="first"
                                            v-model="positionType"
                                            class="rounded border-gray-300"
                                        />
                                        <span class="text-sm text-slate-700">Первым (в начало списка)</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            value="after"
                                            v-model="positionType"
                                            class="rounded border-gray-300"
                                        />
                                        <span class="text-sm text-slate-700">После элемента</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            value="keep"
                                            v-model="positionType"
                                            class="rounded border-gray-300"
                                        />
                                        <span class="text-sm text-slate-700">Оставить текущую позицию</span>
                                    </label>
                                </div>

                                <select
                                    v-if="positionType === 'after'"
                                    v-model="form.after_id"
                                    class="admin-form-select w-full mt-2"
                                >
                                    <option :value="null">— Выберите элемент —</option>
                                    <option v-for="item in orderOptions" :key="item.id" :value="item.id" :disabled="item.id === menuItemId">
                                        {{ '—'.repeat(item.level || 0) }} {{ item.title }}
                                    </option>
                                </select>
                            </div>

                            <!-- Родительский элемент -->
                            <div>
                                <h3 class="admin-form-label">Родительский элемент</h3>
                                <select
                                    v-model="form.parent_id"
                                    class="admin-form-select w-full"
                                >
                                    <option :value="null">— Корневой уровень —</option>
                                    <option v-for="item in parentOptions" :key="item.id" :value="item.id" :disabled="item.id === menuItemId">
                                        {{ '—'.repeat(item.level || 0) }} {{ item.title }}
                                    </option>
                                </select>
                            </div>

                            <!-- Статус -->
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
                                    <option :value="true" class="bg-white text-slate-800">Опубликовано</option>
                                    <option :value="false" class="bg-white text-slate-800">Не опубликовано</option>
                                </select>
                            </div>

                            <!-- Доступ -->
                            <div>
                                <h3 class="admin-form-label">Доступ</h3>
                                <select
                                    v-model="form.access"
                                    class="admin-form-select w-full"
                                >
                                    <option value="all">Все</option>
                                    <option value="guest">Только гости</option>
                                    <option value="registered">Только зарегистрированные</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />

        <MaterialSelectorModal
            :show="showMaterialModal"
            :selected-slug="form.link_value"
            @close="showMaterialModal = false"
            @select="selectMaterial"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import MaterialSelectorModal from './components/MaterialSelectorModal.vue';
import { menuTypesApi, menuItemsApi, type MenuType, type MenuItem } from '@/api/menu';

const props = defineProps<{
    user: any;
    title?: string;
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

watch(() => form.value.link_type, (newType) => {
    if (newType !== 'material') {
        form.value.link_value = '';
        form.value.link_value_display = '';
    }
});

const cancel = () => {
    router.visit(`/admin/menu/types/${form.value.menu_type_id}/items`);
};

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

const loadMaterialTitle = async () => {
    if (form.value.link_type === 'material' && form.value.link_value) {
        try {
            const response = await axios.get(`/api/materials/by-slug/${form.value.link_value}`, {
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

const selectMaterial = (material: { id: number; title: string; slug: string }) => {
    form.value.link_value = material.slug;
    form.value.link_value_display = material.title;
};

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

    if (positionType.value === 'first') {
        submitData.position = 'first';
    } else if (positionType.value === 'after' && form.value.after_id) {
        submitData.after_id = form.value.after_id;
    }

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

        positionType.value = 'keep';
        form.value.after_id = null;

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
