<template>
    <div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-orange-50 flex items-center justify-center px-4">
        <div class="max-w-2xl w-full text-center">
            <!-- Анимированная иллюстрация -->
            <div class="relative mb-8">
                <div class="text-8xl md:text-9xl font-bold text-red-100 select-none">
                    500
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-24 h-24 md:w-32 md:h-32 text-red-400 animate-pulse-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">
                Что-то пошло не так
            </h1>

            <p class="text-lg text-gray-600 mb-2">
                На сервере произошла ошибка.
            </p>

            <p class="text-gray-400 mb-8">
                Мы уже работаем над её устранением. Попробуйте обновить страницу через несколько минут.
            </p>

            <!-- Важная информация -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">📞 Телефон</h3>
                        <a href="tel:+79370063063" class="text-indigo-600 hover:text-indigo-800 font-medium text-lg">
                            +7 (937) 006-30-63
                        </a>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">✉️ Email</h3>
                        <a href="mailto:info@mirrasim.ru" class="text-indigo-600 hover:text-indigo-800 font-medium">
                            info@mirrasim.ru
                        </a>
                    </div>
                    <div class="sm:col-span-2">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">📍 Адрес</h3>
                        <p class="text-gray-600">
                            г. Москва, ул. Примерная, д. 123, офис 456
                        </p>
                    </div>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button
                    @click="reloadPage"
                    class="inline-flex items-center gap-2 bg-red-500 text-white px-8 py-3 rounded-xl hover:bg-red-600 transition-all hover:shadow-lg hover:-translate-y-0.5"
                >
                    <svg class="w-5 h-5 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Обновить страницу
                </button>

                <Link
                    href="/"
                    class="inline-flex items-center gap-2 text-gray-600 hover:text-red-600 transition-colors px-4 py-3"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z" />
                    </svg>
                    На главную
                </Link>
            </div>

            <!-- Сообщение о уведомлении -->
            <div class="mt-8 text-sm text-gray-400 bg-white/50 rounded-lg px-6 py-3 inline-block">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Мы уже уведомлены об ошибке и работаем над исправлением
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const reloadPage = () => {
    window.location.reload();
};
</script>

<style scoped>
/* Медленная пульсация для иконки */
.animate-pulse-slow {
    animation: pulse 2.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.08);
        opacity: 0.7;
    }
}

/* Медленное вращение для иконки обновления */
.animate-spin-slow {
    animation: spin 3s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Анимация для иконки при наведении */
.hover\:animate-none:hover .animate-spin-slow {
    animation: spin 0.8s linear infinite;
}
</style>
