import { ref, watch, type Ref } from 'vue';

export const useSidebarSections = (maxOpen: Ref<number> | number = 2) => {
    const maxOpenRef = ref(typeof maxOpen === 'number' ? maxOpen : maxOpen.value);
    const openSections = ref<string[]>(['materials']);
    let resizeTimeout: any = null;

    // Функция для обрезки открытых разделов до лимита
    const trimOpenSections = () => {
        if (openSections.value.length > maxOpenRef.value) {
            openSections.value = openSections.value.slice(-maxOpenRef.value);
        }
    };

    // Функция для открытия всех разделов (при увеличении лимита)
    const expandOpenSections = () => {
        const allSections = ['materials', 'navigation', 'structure', 'users', 'settings'];
        const limited = allSections.slice(0, maxOpenRef.value);
        openSections.value = limited;
    };

    // Если передали реф, следим за ним
    if (typeof maxOpen !== 'number') {
        watch(maxOpen, (newVal, oldVal) => {
            // Очищаем предыдущий таймаут
            if (resizeTimeout) clearTimeout(resizeTimeout);

            // Делаем задержку 150ms чтобы избежать рывков
            resizeTimeout = setTimeout(() => {
                maxOpenRef.value = newVal;

                // Если лимит увеличился — открываем все разделы
                if (newVal > oldVal) {
                    expandOpenSections();
                }
                // Если лимит уменьшился — обрезаем
                else if (newVal < oldVal) {
                    trimOpenSections();
                }

                resizeTimeout = null;
            }, 150);
        });
    }

    const isOpen = (key: string): boolean => {
        return openSections.value.includes(key);
    };

    const toggleSection = (key: string) => {
        const index = openSections.value.indexOf(key);

        if (index !== -1) {
            openSections.value.splice(index, 1);
            return;
        }

        if (openSections.value.length >= maxOpenRef.value) {
            openSections.value.shift();
        }

        openSections.value.push(key);
    };

    const setOpenSections = (keys: string[]) => {
        const limited = keys.slice(0, maxOpenRef.value);
        openSections.value = limited;
    };

    return {
        openSections,
        isOpen,
        toggleSection,
        setOpenSections,
        maxOpen: maxOpenRef,
    };
};
