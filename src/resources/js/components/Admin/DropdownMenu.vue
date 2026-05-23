<template>
    <div class="relative">
        <button @click="toggle" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-200 hover:text-white hover:bg-gray-700 rounded-md transition-colors">
            {{ title }}
            <svg class="ml-1 w-3 h-3 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div v-if="isOpen" @mouseleave="close" class="absolute left-0 mt-1 bg-white rounded-md shadow-lg py-1 z-20 border border-gray-200 min-w-full whitespace-nowrap">
            <Link v-for="item in items" :key="item.name" :href="item.href" :class="['block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100', item.indent ? 'pl-8' : '']">
                {{ item.name }}
            </Link>
        </div>
    </div>
</template>

<script setup>
import {ref} from 'vue';
import {Link} from '@inertiajs/vue3';

defineProps({
    title: String,
    items: Array
});

const isOpen = ref(false);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const close = () => {
    isOpen.value = false;
};
</script>
