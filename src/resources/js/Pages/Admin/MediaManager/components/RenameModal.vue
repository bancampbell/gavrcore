<!-- resources/js/Pages/Admin/MediaManager/components/RenameModal.vue -->

<template>
    <div v-if="show" class="modal-overlay">
        <div class="modal-content">
            <h2 class="text-lg font-semibold mb-4">Переименовать</h2>
            <input
                v-model="itemName"
                type="text"
                placeholder="Новое имя"
                class="w-full border border-gray-300 rounded px-3 py-2 mb-4 focus:outline-none focus:ring-1 focus:ring-blue-500"
                @keyup.enter="onConfirm"
                autofocus
            />
            <div class="flex justify-end gap-2">
                <button @click="onClose" class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">
                    Отмена
                </button>
                <button
                    @click="onConfirm"
                    :disabled="!itemName.trim()"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    Переименовать
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    currentName: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm', name: string): void;
}>();

const itemName = ref('');

watch(() => props.show, (val) => {
    if (val) {
        itemName.value = props.currentName;
    } else {
        itemName.value = '';
    }
});

const onClose = () => emit('close');
const onConfirm = () => {
    if (itemName.value.trim()) {
        emit('confirm', itemName.value.trim());
    }
};
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
