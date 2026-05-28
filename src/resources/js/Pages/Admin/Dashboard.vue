<template>
    <AdminLayout :user="user">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900">Добро пожаловать, {{ user.name }}!</h1>
            <p class="text-gray-600 mt-2">Это панель управления GavrCore CMS.</p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-indigo-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-indigo-600">Материалов</div>
                    <div class="text-2xl font-bold text-indigo-900">0</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-green-600">Категорий</div>
                    <div class="text-2xl font-bold text-green-900">0</div>
                </div>
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-blue-600">Пользователей</div>
                    <div class="text-2xl font-bold text-blue-900">1</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-purple-600">Просмотров</div>
                    <div class="text-2xl font-bold text-purple-900">0</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    user: Object,
});
</script>
