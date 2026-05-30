<!-- resources/js/Pages/Admin/MediaManager/components/DeleteModal.vue -->

<template>
    <div v-if="show" class="modal-overlay">
        <div class="modal-content">
            <h2 class="text-lg font-semibold mb-4">Подтверждение удаления</h2>
            <p class="text-sm text-gray-600 mb-6">
                Вы уверены, что хотите удалить "{{ itemName }}"?
                Это действие нельзя отменить.
            </p>
            <div class="flex justify-end gap-2">
                <button @click="onClose" class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">
                    Отмена
                </button>
                <button @click="onConfirm" class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                    Удалить
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    show: boolean;
    itemName: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();

const onClose = () => emit('close');
const onConfirm = () => emit('confirm');
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background-color: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    width: 24rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>
