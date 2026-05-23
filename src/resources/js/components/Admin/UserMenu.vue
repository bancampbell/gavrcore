<template>
    <div class="relative">
        <button @click="toggle" class="inline-flex items-center space-x-2 text-gray-200 hover:text-white">
            <div class="w-7 h-7 rounded-full bg-gray-500 flex items-center justify-center">
                <span class="text-xs font-medium text-white">{{ userInitial }}</span>
            </div>
            <span class="hidden md:inline text-xs">{{ user?.name || 'Администратор' }}</span>
            <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div v-if="isOpen" @mouseleave="close" class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg py-1 z-20 border border-gray-200">
            <Link href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Профиль</Link>
            <Link href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Настройки</Link>
            <hr class="my-1">
            <button @click="$emit('logout')" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Выйти</button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object
});

defineEmits(['logout']);

const isOpen = ref(false);
const userInitial = computed(() => props.user?.name?.charAt(0)?.toUpperCase() || 'A');

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const close = () => {
    isOpen.value = false;
};
</script>
