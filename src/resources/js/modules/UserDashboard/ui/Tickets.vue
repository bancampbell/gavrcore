<template>
    <DashboardLayout>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Оставить заявку</h1>

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
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import ShortcodeRenderer from '@/components/shared/ShortcodeRenderer.vue';

const props = defineProps({
    user: Object,
    currentTheme: String,
    forms: {
        type: Object,
        default: () => ({})
    }
});

const formContent = `
    [form id="5"]
`;
</script>
