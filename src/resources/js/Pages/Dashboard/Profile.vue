<template>
    <DashboardLayout>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Мой профиль</h1>

            <form @submit.prevent="updateProfile" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Имя</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="loading"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg disabled:opacity-50 transition-colors"
                    >
                        {{ loading ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                </div>

                <p v-if="message" class="text-sm text-green-600 dark:text-green-400">{{ message }}</p>
                <p v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

const props = defineProps({
    user: Object,
    currentTheme: String,
});

const form = ref({
    name: props.user?.name || '',
    email: props.user?.email || '',
});

const loading = ref(false);
const message = ref('');
const error = ref('');

const updateProfile = async () => {
    loading.value = true;
    message.value = '';
    error.value = '';

    try {
        await router.put('/dashboard/profile', form.value, {
            onSuccess: () => {
                message.value = 'Профиль обновлён';
            },
            onError: (err) => {
                error.value = err.message || 'Ошибка обновления';
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    } catch (err) {
        error.value = 'Ошибка обновления';
        loading.value = false;
    }
};
</script>
