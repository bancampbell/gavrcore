<template>
    <LayoutSwitcher>
        <AuthCard title="GavrCore CMS" subtitle="Вход в аккаунт">
            <form @submit.prevent="login" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="block w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3688d1]"
                        placeholder="user@example.com"
                        required
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Пароль</label>
                    <div class="relative">
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#3688d1]"
                            placeholder="••••••••"
                            required
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400"
                        >
                            {{ showPassword ? '🙈' : '👁️' }}
                        </button>
                    </div>
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-gradient-to-r from-[#294469] to-[#3688d1] text-white py-2.5 rounded-xl font-medium hover:from-[#1e3a5f] hover:to-[#2a6d9e] transition-all disabled:opacity-50"
                >
                    {{ loading ? 'Вход...' : 'Войти' }}
                </button>

                <p v-if="error" class="text-red-600 text-center text-sm">{{ error }}</p>

                <p class="text-center text-sm text-gray-600 mt-4">
                    Нет аккаунта?
                    <Link :href="registerUrl" class="text-[#3688d1] hover:text-[#294469] font-medium">
                        Зарегистрироваться
                    </Link>
                </p>
            </form>
        </AuthCard>
    </LayoutSwitcher>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import LayoutSwitcher from '@/layouts/LayoutSwitcher.vue';
import AuthCard from '@/components/shared/AuthCard.vue';

const props = defineProps<{
    registerUrl?: string;
}>();

const form = ref({
    email: '',
    password: '',
});

const showPassword = ref(false);
const loading = ref(false);
const error = ref('');
const errors = ref<{ email?: string; password?: string }>({});

const login = async () => {
    loading.value = true;
    error.value = '';
    errors.value = {};

    try {
        await router.post('/login', form.value, {
            preserveState: true,
            onError: (err) => {
                if (err.email) errors.value.email = err.email;
                if (err.password) errors.value.password = err.password;
                if (err.message) error.value = err.message;
            },
            onFinish: () => { loading.value = false; },
        });
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Ошибка входа';
        loading.value = false;
    }
};
</script>
