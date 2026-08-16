import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import { useGalleryModals } from '@/modules/GalleryManager/ui/composables/useGalleryModals';

const TestComponent = {
  setup() {
    return useGalleryModals();
  },
  template: '<div></div>',
};

describe('useGalleryModals', () => {
  it('initializes with all modals closed and no loading', () => {
    const wrapper = mount(TestComponent);

    expect(wrapper.vm.createModalOpen).toBe(false);
    expect(wrapper.vm.deleteModalOpen).toBe(false);
    expect(wrapper.vm.deleteLoading).toBe(false);
  });

  it('opens create modal', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.openCreateModal();
    expect(wrapper.vm.createModalOpen).toBe(true);
  });

  it('closes create modal', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.openCreateModal();
    wrapper.vm.closeCreateModal();
    expect(wrapper.vm.createModalOpen).toBe(false);
  });

  it('opens delete modal', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.openDeleteModal();
    expect(wrapper.vm.deleteModalOpen).toBe(true);
  });

  it('closes delete modal and resets loading', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.openDeleteModal();
    wrapper.vm.deleteLoading = true;
    wrapper.vm.closeDeleteModal();

    expect(wrapper.vm.deleteModalOpen).toBe(false);
    expect(wrapper.vm.deleteLoading).toBe(false);
  });

  it('maintains independent state between modals', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.openCreateModal();
    expect(wrapper.vm.createModalOpen).toBe(true);
    expect(wrapper.vm.deleteModalOpen).toBe(false);

    wrapper.vm.openDeleteModal();
    expect(wrapper.vm.deleteModalOpen).toBe(true);
    // create modal state remains unchanged
    expect(wrapper.vm.createModalOpen).toBe(true);
  });
});
