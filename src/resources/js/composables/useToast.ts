import { reactive, readonly } from 'vue';

export interface ToastMessage {
    id: number;
    message: string;
    type: 'success' | 'error' | 'warning' | 'info';
    duration?: number;
}

const state = reactive<{
    toasts: ToastMessage[];
}>({
    toasts: [],
});

let idCounter = 0;

export function useToast() {
    const show = (
        message: string,
        type: ToastMessage['type'] = 'success',
        duration: number = 3000,
    ) => {
        const id = ++idCounter;
        const toast: ToastMessage = { id, message, type, duration };

        state.toasts.push(toast);

        if (duration > 0) {
            setTimeout(() => {
                remove(id);
            }, duration);
        }

        return id;
    };

    const remove = (id: number) => {
        const index = state.toasts.findIndex(t => t.id === id);
        if (index !== -1) {
            state.toasts.splice(index, 1);
        }
    };

    const clearAll = () => {
        state.toasts = [];
    };

    const success = (message: string, duration?: number) => show(message, 'success', duration);
    const error = (message: string, duration?: number) => show(message, 'error', duration);
    const warning = (message: string, duration?: number) => show(message, 'warning', duration);
    const info = (message: string, duration?: number) => show(message, 'info', duration);

    return {
        toasts: readonly(state.toasts),
        show,
        remove,
        clearAll,
        success,
        error,
        warning,
        info,
    };
}
