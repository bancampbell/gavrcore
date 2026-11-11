<template>
    <div
        v-if="driver === 'turnstile'"
        ref="container"
        class="cf-turnstile"
        :data-sitekey="siteKey"
        :data-callback="successCallbackName"
        :data-expired-callback="expiredCallbackName"
    />
</template>

<script lang="ts">
// Модульный скоуп: скрипт Turnstile грузим один раз на страницу,
// повторные виджеты дожидаются того же промиса; счётчик даёт
// уникальные имена глобальных колбэков для нескольких форм на странице.
const TURNSTILE_SRC = 'https://challenges.cloudflare.com/turnstile/v1/api.js';

interface TurnstileApi {
    render: (container: HTMLElement, params: Record<string, unknown>) => number;
    reset: () => void;
}

export function getTurnstile(): TurnstileApi | undefined {
    return (window as unknown as { turnstile?: TurnstileApi }).turnstile;
}

let turnstileLoading: Promise<void> | null = null;

export function loadTurnstile(): Promise<void> {
    if (getTurnstile()) return Promise.resolve();
    if (turnstileLoading) return turnstileLoading;

    turnstileLoading = new Promise<void>((resolve) => {
        const script = document.createElement('script');
        script.src = TURNSTILE_SRC;
        script.async = true;
        script.defer = true;
        script.onload = () => resolve();
        script.onerror = () => {
            // Разрешаем повторную попытку при следующем монтировании виджета.
            turnstileLoading = null;
            resolve();
        };
        document.head.appendChild(script);
    });

    return turnstileLoading;
}

let callbackSeq = 0;

export function nextCallbackName(prefix: string): string {
    callbackSeq += 1;
    return `${prefix}${callbackSeq}`;
}
</script>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';

interface Props {
    driver: string;
    siteKey: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:token', token: string): void;
}>();

const container = ref<HTMLElement | null>(null);
const successCallbackName = nextCallbackName('gavrTurnstileSuccess');
const expiredCallbackName = nextCallbackName('gavrTurnstileExpired');

onMounted(async () => {
    if (props.driver !== 'turnstile') {
        if (props.driver && props.driver !== 'none') {
            console.warn(`[CaptchaWidget] Неизвестный драйвер капчи: "${props.driver}" — виджет не отрендерен.`);
        }
        return;
    }

    (window as unknown as Record<string, unknown>)[successCallbackName] = (token: string) => emit('update:token', token);
    (window as unknown as Record<string, unknown>)[expiredCallbackName] = () => emit('update:token', '');

    const wasLoaded = !!getTurnstile();
    await loadTurnstile();

    const turnstile = getTurnstile();
    if (!turnstile) return; // скрипт не поднялся — сабмит без токена ответит 422 с сообщением сервера

    if (wasLoaded && container.value) {
        // Скрипт загрузился раньше этого компонента: implicit-сканирование
        // DOM уже прошло и наш div ему не достался — рендерим явно.
        turnstile.render(container.value, {
            sitekey: props.siteKey,
            callback: successCallbackName,
            'expired-callback': expiredCallbackName,
        });
    }
    // Иначе только что загруженный api.js сам найдёт div.cf-turnstile (implicit render).
});

onBeforeUnmount(() => {
    delete (window as unknown as Record<string, unknown>)[successCallbackName];
    delete (window as unknown as Record<string, unknown>)[expiredCallbackName];
});

// Токен одноразовый — виджет сбрасываем после каждого submit. Виджет рендерится
// implicit'ом (id недоступен), поэтому reset() сбрасывает все виджеты Turnstile
// на странице; при одной форме на странице это эквивалентно точечному сбросу.
function reset() {
    getTurnstile()?.reset();
}

defineExpose({ reset });
</script>
