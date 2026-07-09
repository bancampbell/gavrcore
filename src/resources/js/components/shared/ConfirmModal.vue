<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
                <!-- Оверлей -->
                <div
                    class="fixed inset-0 bg-black/40 backdrop-blur-[2px] transition-all"
                    @click="close"
                ></div>

                <!-- Модалка -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="scale-95 opacity-0 translate-y-4"
                    enter-to-class="scale-100 opacity-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="scale-100 opacity-100 translate-y-0"
                    leave-to-class="scale-95 opacity-0 translate-y-4"
                >
                    <div v-if="isOpen" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                        <!-- Цветная полоса сверху -->
                        <div
                            class="h-1 w-full"
                            :class="type === 'danger' ? 'bg-gradient-to-r from-red-500 to-red-600' : 'bg-gradient-to-r from-yellow-400 to-yellow-500'"
                        ></div>

                        <!-- Заголовок -->
                        <div class="px-6 pt-5 pb-3">
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                                    :class="type === 'danger' ? 'bg-red-50' : 'bg-yellow-50'"
                                >
                                    <svg v-if="type === 'danger'" class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <svg v-else class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
                                    <p class="text-sm text-gray-500 mt-0.5">Подтвердите ваше действие</p>
                                </div>
                            </div>
                        </div>

                        <!-- Сообщение -->
                        <div class="px-6 pb-3">
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <p class="text-gray-700 text-sm leading-relaxed">{{ message }}</p>
                            </div>
                        </div>

                        <!-- Кнопки -->
                        <div class="px-6 py-4 bg-gray-50/50 flex items-center justify-end gap-3">
                            <button
                                @click="close"
                                class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-gray-200"
                            >
                                Отмена
                            </button>
                            <button
                                @click="confirm"
                                :disabled="loading"
                                class="px-5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 focus:outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="[
                                    type === 'danger'
                                        ? 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-200 shadow-sm shadow-red-100'
                                        : 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-200 shadow-sm shadow-yellow-100'
                                ]"
                            >
                                <span v-if="loading" class="inline-block mr-2">
                                    <svg class="animate-spin h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </span>
                                {{ confirmText }}
                            </button>
                        </div>

                        <!-- Декоративные элементы -->
                        <div v-if="type === 'danger'" class="absolute -top-12 -right-12 w-32 h-32 bg-red-500/5 rounded-full blur-2xl"></div>
                        <div v-else class="absolute -top-12 -right-12 w-32 h-32 bg-yellow-500/5 rounded-full blur-2xl"></div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
interface Props {
    isOpen: boolean;
    title: string;
    message: string;
    confirmText: string;
    type: 'danger' | 'warning';
    loading: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();

const close = () => emit('close');
const confirm = () => emit('confirm');
</script>
