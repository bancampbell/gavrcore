<template>
    <LayoutSwitcher>
        <AuthCard title="GavrCore CMS" subtitle="Двухфакторная аутентификация">
            <form @submit.prevent="verify" class="space-y-5">
                <p class="text-sm text-gray-600">
                    Введите 6-значный код из приложения-аутентификатора или резервный код
                </p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Код</label>
                    <input
                        v-model="form.code"
                        type="text"
                        autocomplete="one-time-code"
                        class="block w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3688d1]"
                        placeholder="123456"
                        required
                        autofocus
                    />
                    <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-gradient-to-r from-[#294469] to-[#3688d1] text-white py-2.5 rounded-xl font-medium hover:from-[#1e3a5f] hover:to-[#2a6d9e] transition-all disabled:opacity-50"
                >
                    {{ loading ? 'Проверка...' : 'Подтвердить' }}
                </button>

                <p v-if="error" class="text-red-600 text-center text-sm">{{ error }}</p>
            </form>
        </AuthCard>
    </LayoutSwitcher>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import LayoutSwitcher from '@/layouts/LayoutSwitcher.vue';
import AuthCard from '@/modules/UserManager/ui/Auth/AuthCard.vue';

const form = ref({
    code: '',
});

const loading = ref(false);
const error = ref('');
const errors = ref<{ code?: string }>({});

const verify = async () => {
    loading.value = true;
    error.value = '';
    errors.value = {};

    try {
        await router.post('/2fa/verify', form.value, {
            preserveState: true,
            onError: (err) => {
                if (err.code) errors.value.code = err.code;
            },
            onFinish: () => { loading.value = false; },
        });
    } catch (err: any) {
        loading.value = false;

        // Метки ожидания 2FA в сессии нет (истекла/инвалидирована) — на логин
        if (err.response?.status === 403) {
            router.visit('/login');

            return;
        }

        error.value = err.response?.data?.message || 'Ошибка проверки кода';
    }
};
</script>
