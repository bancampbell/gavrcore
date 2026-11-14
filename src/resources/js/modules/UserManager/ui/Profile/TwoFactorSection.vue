<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Двухфакторная аутентификация</h2>
            <span
                class="px-3 py-1 rounded-full text-xs font-medium"
                :class="isEnabled
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
            >
                {{ isEnabled ? 'Включена' : 'Выключена' }}
            </span>
        </div>

        <div v-if="!isEnabled && step === null">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                При входе дополнительно потребуется код из приложения-аутентификатора (Google Authenticator, Aegis).
            </p>
            <button
                @click="startEnable"
                :disabled="loading"
                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg disabled:opacity-50 transition-colors"
            >
                {{ loading ? 'Включение...' : 'Включить' }}
            </button>
        </div>

        <div v-if="step === 'setup'" class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Отсканируйте QR-код в приложении-аутентификаторе и введите 6-значный код из него.
            </p>
            <div class="flex justify-center">
                <img v-if="qrDataUrl" :src="qrDataUrl" alt="QR-код для настройки двухфакторной аутентификации" class="w-48 h-48" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Код из приложения</label>
                <input
                    v-model="confirmCode"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="123456"
                    required
                />
                <p v-if="errors.code" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.code }}</p>
            </div>
            <div class="flex gap-3">
                <button
                    @click="confirm"
                    :disabled="loading"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg disabled:opacity-50 transition-colors"
                >
                    {{ loading ? 'Проверка...' : 'Подтвердить' }}
                </button>
                <button
                    @click="cancelSetup"
                    class="px-6 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                >
                    Отмена
                </button>
            </div>
        </div>

        <div v-if="step === 'codes'" class="space-y-4">
            <p class="text-sm font-medium text-amber-600 dark:text-amber-400">
                Сохраните резервные коды — после закрытия этой страницы они больше не показываются.
            </p>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <li
                    v-for="code in recoveryCodes"
                    :key="code"
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-lg font-mono text-sm text-gray-900 dark:text-white"
                >
                    {{ code }}
                </li>
            </ul>
            <div class="flex gap-3">
                <button
                    @click="copyCodes"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors"
                >
                    {{ copied ? 'Скопировано' : 'Скопировать коды' }}
                </button>
                <button
                    @click="finishSetup"
                    class="px-6 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                >
                    Готово
                </button>
            </div>
        </div>

        <div v-if="isEnabled" class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Для отключения введите текущий пароль.
            </p>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текущий пароль</label>
                <input
                    v-model="currentPassword"
                    type="password"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    required
                />
                <p v-if="errors.current_password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.current_password }}</p>
            </div>
            <button
                @click="disable"
                :disabled="loading"
                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-50 transition-colors"
            >
                {{ loading ? 'Отключение...' : 'Отключить' }}
            </button>
        </div>

        <p v-if="error" class="mt-4 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import QRCode from 'qrcode';
import { twoFactorApi } from '@/modules/UserManager/infrastructure/api/user-api';

const props = defineProps<{
    enabled?: boolean;
}>();

// Inertia-проп обновится только при следующем рендере страницы (axios-эндпоинты
// /profile/2fa/* — не Inertia), поэтому статус дублируем локальным ref.
const isEnabled = ref(props.enabled ?? false);
watch(() => props.enabled, (value) => { isEnabled.value = value ?? false; });

const step = ref<null | 'setup' | 'codes'>(null);
const loading = ref(false);
const error = ref('');
const errors = ref<{ code?: string; current_password?: string }>({});

const qrDataUrl = ref('');
const confirmCode = ref('');
const recoveryCodes = ref<string[]>([]);
const copied = ref(false);
const currentPassword = ref('');

// Laravel отдаёт ошибки валидации как {message, errors: {поле: [сообщения]}}
const requestErrorMessage = (err: any, field: string): string =>
    err?.response?.data?.errors?.[field]?.[0]
    || err?.response?.data?.message
    || 'Ошибка запроса';

const startEnable = async () => {
    loading.value = true;
    error.value = '';
    errors.value = {};

    try {
        const { data } = await twoFactorApi.enable();

        qrDataUrl.value = await QRCode.toDataURL(data.data.otpauth_url, { width: 192, margin: 1 });
        step.value = 'setup';
    } catch (err: any) {
        error.value = err?.response?.data?.message || 'Ошибка включения';
    } finally {
        loading.value = false;
    }
};

const confirm = async () => {
    loading.value = true;
    error.value = '';
    errors.value = {};

    try {
        const { data } = await twoFactorApi.confirm(confirmCode.value.trim());

        recoveryCodes.value = data.data.recovery_codes;
        step.value = 'codes';
    } catch (err: any) {
        if (err?.response?.status === 422) {
            errors.value.code = requestErrorMessage(err, 'code');
        } else {
            error.value = err?.response?.data?.message || 'Ошибка подтверждения';
        }
    } finally {
        loading.value = false;
    }
};

const cancelSetup = () => {
    step.value = null;
    confirmCode.value = '';
    qrDataUrl.value = '';
    errors.value = {};
    error.value = '';
};

const finishSetup = () => {
    isEnabled.value = true;
    step.value = null;
    recoveryCodes.value = [];
    confirmCode.value = '';
    qrDataUrl.value = '';
};

const copyCodes = async () => {
    const text = recoveryCodes.value.join('\n');

    try {
        await navigator.clipboard.writeText(text);
    } catch {
        // Clipboard API недоступен (не secure context) — fallback через textarea
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        textarea.remove();
    }

    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const disable = async () => {
    loading.value = true;
    error.value = '';
    errors.value = {};

    try {
        await twoFactorApi.disable(currentPassword.value);

        isEnabled.value = false;
        currentPassword.value = '';
    } catch (err: any) {
        if (err?.response?.status === 422) {
            errors.value.current_password = requestErrorMessage(err, 'current_password');
        } else {
            error.value = err?.response?.data?.message || 'Ошибка отключения';
        }
    } finally {
        loading.value = false;
    }
};
</script>
