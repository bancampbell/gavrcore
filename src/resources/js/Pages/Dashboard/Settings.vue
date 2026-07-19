<template>
    <DashboardLayout>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Настройки</h1>

            <form @submit.prevent="updatePassword" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текущий пароль</label>
                    <input
                        v-model="form.current_password"
                        type="password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Новый пароль</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        required
                        minlength="8"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Подтверждение пароля</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        required
                    />
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="loading"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg disabled:opacity-50 transition-colors"
                    >
                        {{ loading ? 'Сохранение...' : 'Сменить пароль' }}
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
    current_password: '',
    password: '',
    password_confirmation: '',
});

const loading = ref(false);
const message = ref('');
const error = ref('');

const updatePassword = async () => {
    loading.value = true;
    message.value = '';
    error.value = '';

    try {
        await router.put('/dashboard/settings', form.value, {
            onSuccess: () => {
                message.value = 'Пароль успешно изменён';
                form.value.current_password = '';
                form.value.password = '';
                form.value.password_confirmation = '';
            },
            onError: (err) => {
                error.value = err.message || 'Ошибка изменения пароля';
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    } catch (err) {
        error.value = 'Ошибка изменения пароля';
        loading.value = false;
    }
};
</script>
