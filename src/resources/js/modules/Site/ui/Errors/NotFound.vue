<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center px-4">
        <div class="max-w-2xl w-full text-center">
            <!-- Анимированная иллюстрация -->
            <div class="relative mb-8">
                <div class="text-8xl md:text-9xl font-bold text-indigo-100 select-none">
                    404
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-24 h-24 md:w-32 md:h-32 text-indigo-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">
                Упс! Страница потерялась
            </h1>

            <p class="text-lg text-gray-600 mb-2">
                Похоже, мы не можем найти то, что вы ищете.
            </p>

            <p class="text-gray-400 mb-8">
                Возможно, страница была удалена или вы перешли по неверной ссылке.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 bg-indigo-600 text-white px-8 py-3 rounded-xl hover:bg-indigo-700 transition-all hover:shadow-lg hover:-translate-y-0.5"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z" />
                    </svg>
                    На главную
                </Link>

                <button
                    @click="goBack"
                    class="inline-flex items-center gap-2 text-gray-600 hover:text-indigo-600 transition-colors px-4 py-3"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Вернуться назад
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import {Link, usePage} from '@inertiajs/vue3';
import {router} from '@inertiajs/vue3';

const goBack = () => {
    if (document.referrer && document.referrer !== window.location.href) {
        window.location.href = document.referrer;
    } else {
        router.visit('/');
    }
};
</script>

<style scoped>
/* Анимация для иконки */
.animate-bounce {
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}
</style>
