<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Корзина материалов</h1>
                <div class="flex flex-wrap gap-2.5 justify-between items-center">
                    <div class="flex flex-wrap gap-2.5">
                        <button
                            @click="restoreSelected"
                            :disabled="selectedMaterials.length === 0"
                            class="admin-btn admin-btn-secondary"
                        >
                            Восстановить
                        </button>
                        <button
                            @click="openDeleteModal"
                            :disabled="selectedMaterials.length === 0"
                            class="admin-btn admin-btn-danger"
                        >
                            Удалить навсегда
                        </button>
                        <button
                            @click="openEmptyTrashModal"
                            :disabled="materials.data?.length === 0"
                            class="admin-btn admin-btn-danger"
                        >
                            Очистить корзину
                        </button>
                    </div>
                    <Link href="/admin/materials" class="text-sm text-slate-500 hover:text-slate-700">
                        ← Назад к материалам
                    </Link>
                </div>
            </div>

            <!-- Контент (скроллится) -->
            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <!-- Мобильная версия (карточки) -->
                    <div class="lg:hidden divide-y divide-slate-100">
                        <div v-for="material in materials.data" :key="material.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" v-model="selectedMaterials" :value="material.id" class="mt-1 admin-checkbox">
                                <div class="flex-1">
                                    <div class="font-medium text-slate-800">{{ material.title }}</div>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ material.id }}</div>
                                    <div class="text-xs text-slate-400">Алиас: {{ material.alias }}</div>
                                    <div class="text-xs text-[#3071a9]">Категория: {{ material.category?.name || 'Без категории' }}</div>
                                    <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                        <span class="text-slate-500">Автор: {{ material.user?.name || '—' }}</span>
                                        <span class="text-slate-500">Просмотров: {{ material.views }}</span>
                                        <span class="text-slate-500">Дата: {{ formatDate(material.created_at) }}</span>
                                        <span :class="{
                                            'text-emerald-600': material.access === 'public',
                                            'text-amber-600': material.access === 'registered',
                                            'text-rose-600': material.access === 'special'
                                        }">
                                            {{ material.access }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Десктопная версия - ЕДИНАЯ ТАБЛИЦА -->
                    <div class="hidden lg:block admin-table-scroll">
                        <table class="admin-table-fixed">
                            <thead>
                            <tr>
                                <th class="col-checkbox">
                                    <input type="checkbox" v-model="allSelected" class="admin-checkbox">
                                </th>
                                <th class="col-title">Заголовок</th>
                                <th class="col-access">Доступ</th>
                                <th class="col-author">Автор</th>
                                <th class="col-language">Язык</th>
                                <th class="col-created">Дата создания</th>
                                <th class="col-views">Просмотров</th>
                                <th class="col-id">ID</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="material in materials.data"
                                :key="material.id"
                                :class="{ 'bg-blue-50/50': selectedMaterials.includes(material.id) }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox" @click.stop="toggleSelect(material.id)">
                                    <input
                                        type="checkbox"
                                        :checked="selectedMaterials.includes(material.id)"
                                        @change="toggleSelect(material.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- Название -->
                                <td class="col-title" @click="toggleSelect(material.id)">
                                    <div class="title-text" style="display: inline-block !important;">{{ material.title }}</div>
                                    <span class="title-slug">Алиас: {{ material.alias }}</span>
                                    <span class="title-category">Категория: {{ material.category?.name || 'Без категории' }}</span>
                                </td>

                                <!-- Доступ -->
                                <td class="col-access" @click="toggleSelect(material.id)">
            <span class="access-badge" :class="{
                'access-public': material.access === 'public',
                'access-registered': material.access === 'registered',
                'access-special': material.access === 'special'
            }">
                {{ material.access }}
            </span>
                                </td>

                                <!-- Автор -->
                                <td class="col-author" @click="toggleSelect(material.id)">
                                    {{ material.user?.name || '—' }}
                                </td>

                                <!-- Язык -->
                                <td class="col-language" @click="toggleSelect(material.id)">
                                    Все
                                </td>

                                <!-- Дата создания -->
                                <td class="col-created" @click="toggleSelect(material.id)">
                                    {{ formatDate(material.created_at) }}
                                </td>

                                <!-- Просмотров -->
                                <td class="col-views" @click="toggleSelect(material.id)">
                                    {{ material.views }}
                                </td>

                                <!-- ID -->
                                <td class="col-id" @click="toggleSelect(material.id)">
                                    {{ material.id }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="materials.data?.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ materials.from || 0 }} - {{ materials.to || 0 }} из {{ materials.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="materials.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">
                                {{ materials.current_page }} / {{ materials.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="materials.current_page === materials.last_page"
                                class="admin-pagination-btn"
                            >
                                Вперед →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно -->
        <ConfirmModal
            :is-open="modalOpen"
            :title="modalTitle"
            :message="modalMessage"
            :confirm-text="modalConfirmText"
            type="danger"
            :loading="modalLoading"
            @close="modalOpen = false"
            @confirm="confirmAction"
        />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmModal from '../../../components/shared/ConfirmModal.vue';
import Toast from '../../../components/shared/Toast.vue';
import { useMaterials } from '../../../composables/useMaterials';
import type { User, MaterialsData } from '../../../types';

const props = defineProps<{
    user: User;
    title?: string;
    materials: MaterialsData;
}>();

const toggleSelect = (id: number) => {
    const index = selectedMaterials.value.indexOf(id);
    if (index === -1) {
        selectedMaterials.value.push(id);
    } else {
        selectedMaterials.value.splice(index, 1);
    }
};

const {
    selectedMaterials,
    allSelected,
    notification,
    modalOpen,
    modalTitle,
    modalMessage,
    modalConfirmText,
    modalLoading,
    formatDate,
    prevPage,
    nextPage,
    restoreSelected,
    openDeleteModal,
    openEmptyTrashModal,
    confirmAction
} = useMaterials(props);
</script>
