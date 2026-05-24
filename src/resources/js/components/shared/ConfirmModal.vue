<template>
    <Teleport to="body">
        <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-all" @click="close"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center',
                            type === 'danger' ? 'bg-red-100' : 'bg-yellow-100'
                        ]">
                            <svg v-if="type === 'danger'" class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <svg v-else class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">{{ title }}</h3>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <p class="text-gray-600">{{ message }}</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
                    <button @click="close" class="px-4 py-2 rounded-xl bg-gray-200 text-gray-700 hover:bg-gray-300 transition font-medium">
                        Отмена
                    </button>
                    <button @click="confirm" :disabled="loading" :class="[
                        'px-4 py-2 rounded-xl font-medium transition disabled:opacity-50',
                        type === 'danger' ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-yellow-600 text-white hover:bg-yellow-700'
                    ]">
                        <span v-if="loading" class="inline-block animate-spin mr-2">⏳</span>
                        {{ confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
const props = defineProps({
    isOpen: Boolean,
    title: String,
    message: String,
    confirmText: String,
    type: String,
    loading: Boolean
});

const emit = defineEmits(['close', 'confirm']);

const close = () => emit('close');
const confirm = () => emit('confirm');
</script>
