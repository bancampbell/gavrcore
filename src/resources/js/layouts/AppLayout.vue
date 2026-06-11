<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <h1 class="text-xl font-bold text-gray-900">
                    <Link href="/" class="hover:text-indigo-600 transition">GavrCore CMS</Link>
                </h1>
            </div>
        </header>

        <main class="flex-1">
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>

        <footer class="bg-white border-t mt-12">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
                &copy; {{ new Date().getFullYear() }} GavrCore CMS. Все права защищены.
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
