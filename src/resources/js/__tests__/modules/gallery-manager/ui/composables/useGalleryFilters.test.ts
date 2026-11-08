import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import { useGalleryFilters } from '@/modules/GalleryManager/ui/composables/useGalleryFilters';

const TestComponent = {
    setup() {
        return useGalleryFilters();
    },
    template: '<div></div>',
};

describe('useGalleryFilters', () => {
    it('initializes with empty filters', () => {
        const wrapper = mount(TestComponent);

        expect(wrapper.vm.filters).toEqual({ search: '', type: '', status: '' });
    });

    it('resets filters to defaults', async () => {
        const wrapper = mount(TestComponent);

        wrapper.vm.filters.search = 'test';
        wrapper.vm.filters.type = 'slider';
        wrapper.vm.filters.status = '0';
        wrapper.vm.resetFilters();
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.filters).toEqual({ search: '', type: '', status: '' });
    });
});
