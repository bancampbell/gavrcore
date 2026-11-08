<template>
    <Teleport to="body">
        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform opacity-0 -translate-y-5"
            enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform opacity-100 translate-y-0"
            leave-to-class="transform opacity-0 -translate-y-5"
        >
            <div v-if="toasts.length > 0" class="fixed top-20 right-6 z-50 flex flex-col gap-2">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'px-4 py-3 rounded-md text-sm font-medium shadow-lg min-w-[200px] max-w-md',
                        toast.type === 'success' ? 'text-white' : toast.type === 'info' ? 'bg-blue-500 text-white' : 'bg-rose-500 text-white'
                    ]"
                    :style="toast.type === 'success' ? { backgroundColor: '#4db64d' } : {}"
                >
                    <div class="flex items-center gap-2">
                        <svg v-if="toast.type === 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else-if="toast.type === 'info'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ toast.message }}
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToast';

const { toasts } = useToast();
</script>
