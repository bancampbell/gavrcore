import { ref, onBeforeUnmount } from 'vue';
import type { Notification } from '../types';

export function useMaterialNotifications() {
    const notification = ref<Notification>({ show: false, message: '', type: 'success' });
    let notificationTimeout: number | null = null;

    const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
        if (notificationTimeout) clearTimeout(notificationTimeout);
        notification.value = { show: true, message, type };
        notificationTimeout = window.setTimeout(() => {
            notification.value.show = false;
        }, 5000);
    };

    onBeforeUnmount(() => {
        if (notificationTimeout) clearTimeout(notificationTimeout);
    });

    return {
        notification,
        showNotification,
    };
}
