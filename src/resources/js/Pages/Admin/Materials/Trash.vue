<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
        <div class="bg-white rounded-lg shadow">
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2 justify-between items-center">
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="restoreSelected"
                            :disabled="selectedMaterials.length === 0"
                            class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition disabled:opacity-50"
                        >
                            Восстановить
                        </button>
                        <button
                            @click="openDeleteModal"
                            :disabled="selectedMaterials.length === 0"
                            class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-red-600 hover:bg-red-50 transition disabled:opacity-50"
                        >
                            Удалить навсегда
                        </button>
                        <button
                            @click="openEmptyTrashModal"
                            :disabled="materials.data?.length === 0"
                            class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-red-600 hover:bg-red-50 transition disabled:opacity-50"
                        >
                            Очистить корзину
                        </button>
                    </div>
                    <Link href="/admin/materials" class="text-sm text-gray-500 hover:text-gray-700">
                        ← Назад к материалам
                    </Link>
                </div>
            </div>

            <!-- Таблица -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 w-10">
                            <input type="checkbox" @change="selectAll" v-model="allSelected" class="rounded border-gray-300">
                        </th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Заголовок</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Доступ</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Автор</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Язык</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Дата создания</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Просмотров</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">ID</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(material, index) in materials.data" :key="material.id"
                        :class="[
                                'border-b border-gray-100 hover:bg-gray-100 cursor-pointer',
                                index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                            ]">
                        <td class="px-4 py-3">
                            <input type="checkbox" v-model="selectedMaterials" :value="material.id" class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ material.title }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">Алиас: {{ material.alias }}</div>
                            <div class="text-xs text-gray-400">Категория: {{ material.category?.name || 'Uncategorized' }}</div>
                        </td>
                        <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium" :class="{
                                    'bg-green-100 text-green-800': material.access === 'public',
                                    'bg-yellow-100 text-yellow-800': material.access === 'registered',
                                    'bg-red-100 text-red-800': material.access === 'special'
                                }">
                                    {{ material.access }}
                                </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ material.user?.name }}</td>
                        <td class="px-4 py-3 text-gray-600">Все</td>
                        <td class="px-4 py-3 text-gray-600">{{ formatDate(material.created_at) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ material.views }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ material.id }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ materials.from || 0 }} - {{ materials.to || 0 }} из {{ materials.total || 0 }}
                </div>
                <div class="flex gap-1">
                    <button
                        @click="prevPage"
                        :disabled="materials.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        ← Назад
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-600">
                        {{ materials.current_page }} / {{ materials.last_page }}
                    </span>
                    <button
                        @click="nextPage"
                        :disabled="materials.current_page === materials.last_page"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        Вперед →
                    </button>
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

        <!-- Toast уведомление -->
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
    selectAll,
    prevPage,
    nextPage,
    restoreSelected,
    openDeleteModal,
    openEmptyTrashModal,
    confirmAction
} = useMaterials(props);
</script>
