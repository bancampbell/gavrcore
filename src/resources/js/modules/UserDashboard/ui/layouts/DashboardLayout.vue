<template>
    <LayoutSwitcher :current-theme="currentTheme">
        <div class="flex flex-col md:flex-row gap-8 py-8">
            <!-- ЛЕВАЯ КОЛОНКА: МЕНЮ -->
            <aside class="w-full md:w-64 flex-shrink-0">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sticky top-8">
                    <!-- Аватар -->
                    <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-lg">
                            {{ user?.name?.charAt(0) || 'U' }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
                        </div>
                    </div>

                    <!-- Меню -->
                    <nav class="space-y-1">
                        <Link href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors" :class="$page.url === '/dashboard' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Обзор
                        </Link>

                        <Link href="/dashboard/profile" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors" :class="$page.url === '/dashboard/profile' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Профиль
                        </Link>

                        <Link href="/dashboard/settings" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors" :class="$page.url === '/dashboard/settings' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Настройки
                        </Link>

                        <!-- ===== ЗАЯВКИ (НОВЫЙ ПУНКТ) ===== -->
                        <Link href="/dashboard/tickets" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors" :class="$page.url === '/dashboard/tickets' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Заявки
                        </Link>

                        <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                Уведомления
                            </div>
                        </div>

                        <div class="pt-2">
                            <button @click="logout" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 w-full transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Выйти
                            </button>
                        </div>
                    </nav>
                </div>
            </aside>

            <!-- ПРАВАЯ КОЛОНКА: КОНТЕНТ -->
            <div class="flex-1 min-h-[600px]">
                <slot />
            </div>
        </div>
    </LayoutSwitcher>
</template>

<script setup>
import { usePage, Link, router } from '@inertiajs/vue3';
import LayoutSwitcher from '@/layouts/LayoutSwitcher.vue';

const page = usePage();

const props = defineProps({
    user: {
        type: Object,
        default: null
    },
    currentTheme: {
        type: String,
        default: 'default'
    }
});

const user = props.user || page.props.auth?.user || null;

const logout = () => {
    router.post('/logout');
};
</script>
