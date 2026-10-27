<template>
    <div class="min-h-screen bg-gradient-to-br from-amber-50 via-white to-orange-50 flex items-center justify-center px-4">
        <div class="max-w-2xl w-full text-center">
            <div class="relative mb-8">
                <div class="text-8xl md:text-9xl font-bold text-amber-100 select-none">
                    403
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-24 h-24 md:w-32 md:h-32 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">
                Доступ запрещён
            </h1>

            <p class="text-lg text-gray-600 mb-2">
                У вашей учётной записи нет прав для просмотра этой страницы.
            </p>

            <p class="text-gray-400 mb-8">
                Если вы считаете, что это ошибка, обратитесь к администратору сайта.
            </p>

            <Link
                href="/"
                class="inline-flex items-center gap-2 bg-amber-600 text-white px-8 py-3 rounded-xl hover:bg-amber-700 transition-all hover:shadow-lg hover:-translate-y-0.5"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 01 1 1v4a1 1 0 01-1 1h-2z" />
                </svg>
                На главную
            </Link>
        </div>
    </div>
</template>

<script setup>
import {Link} from '@inertiajs/vue3';
</script>
