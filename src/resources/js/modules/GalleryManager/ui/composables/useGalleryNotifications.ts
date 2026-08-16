import { ref } from 'vue';

export type NotificationType = 'success' | 'error';

export function useGalleryNotifications() {
    const notification = ref({
        show: false,
        message: '',
        type: 'success' as NotificationType,
    });

    let timeoutId: ReturnType<typeof setTimeout> | null = null;

    const showNotification = (message: string, type: NotificationType = 'success') => {
        if (timeoutId) clearTimeout(timeoutId);

        notification.value = { show: true, message, type };
        timeoutId = setTimeout(() => {
            notification.value.show = false;
        }, 5000);
    };

    const hideNotification = () => {
        notification.value.show = false;
        if (timeoutId) clearTimeout(timeoutId);
    };

    return {
        notification,
        showNotification,
        hideNotification,
    };
}
