import { ref } from 'vue';

export function useGalleryModals() {
    const createModalOpen = ref(false);
    const deleteModalOpen = ref(false);
    const deleteLoading = ref(false);

    const openCreateModal = () => createModalOpen.value = true;
    const closeCreateModal = () => createModalOpen.value = false;
    const openDeleteModal = () => deleteModalOpen.value = true;
    const closeDeleteModal = () => {
        deleteModalOpen.value = false;
        deleteLoading.value = false;
    };

    return {
        createModalOpen,
        deleteModalOpen,
        deleteLoading,
        openCreateModal,
        closeCreateModal,
        openDeleteModal,
        closeDeleteModal,
    };
}
