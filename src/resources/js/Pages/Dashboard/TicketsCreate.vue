<template>
    <DashboardLayout>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Новая заявка</h1>
                <Link href="/dashboard/tickets" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">
                    ← К списку заявок
                </Link>
            </div>

            <div class="max-w-2xl">
                <ShortcodeRenderer
                    :content="formContent"
                    :forms="forms"
                />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import ShortcodeRenderer from '@/components/shared/ShortcodeRenderer.vue';

const props = defineProps({
    user: Object,
    currentTheme: String,
    forms: Object,
});

const formContent = `[form id="5"]`;
</script>
