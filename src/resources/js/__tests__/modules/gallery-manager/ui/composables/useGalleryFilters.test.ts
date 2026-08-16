import { describe, it, expect } from 'vitest';
import { ref, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import { useGalleryFilters } from '@/modules/GalleryManager/ui/composables/useGalleryFilters';
import type { GalleryListItem } from '@/modules/GalleryManager/ui/types';

const createMockGalleries = (): GalleryListItem[] => [
  { id: 1, title: 'Grid Gallery Alpha', type: 'grid', status: true, images_count: 5, created_at: '2024-01-01' },
  { id: 2, title: 'Slider Beta', type: 'slider', status: false, images_count: 3, created_at: '2024-01-02' },
  { id: 3, title: 'Grid Gamma', type: 'grid', status: true, images_count: 0, created_at: '2024-01-03' },
  { id: 4, title: 'Slideshow Delta', type: 'slideshow', status: false, images_count: 2, created_at: '2024-01-04' },
];

const TestComponent = {
  props: ['galleries'],
  setup(props: { galleries: GalleryListItem[] }) {
    const galleriesRef = ref(props.galleries);
    return useGalleryFilters(galleriesRef);
  },
  template: '<div></div>',
};

describe('useGalleryFilters', () => {
  it('returns all galleries when filters are empty', () => {
    const wrapper = mount(TestComponent, {
      props: { galleries: createMockGalleries() },
    });

    expect(wrapper.vm.filteredGalleries).toHaveLength(4);
  });

  it('filters by search query (case-insensitive)', async () => {
    const wrapper = mount(TestComponent, {
      props: { galleries: createMockGalleries() },
    });

    wrapper.vm.filters.search = 'GRID';
    await nextTick();

    expect(wrapper.vm.filteredGalleries).toHaveLength(2);
    expect(wrapper.vm.filteredGalleries.every(g => g.title.toLowerCase().includes('grid'))).toBe(true);
  });

  it('filters by type', async () => {
    const wrapper = mount(TestComponent, {
      props: { galleries: createMockGalleries() },
    });

    wrapper.vm.filters.type = 'slider';
    await nextTick();

    expect(wrapper.vm.filteredGalleries).toHaveLength(1);
    expect(wrapper.vm.filteredGalleries[0].type).toBe('slider');
  });

  it('filters by status (published)', async () => {
    const wrapper = mount(TestComponent, {
      props: { galleries: createMockGalleries() },
    });

    wrapper.vm.filters.status = '1';
    await nextTick();

    expect(wrapper.vm.filteredGalleries).toHaveLength(2);
    expect(wrapper.vm.filteredGalleries.every(g => g.status === true)).toBe(true);
  });

  it('filters by status (draft)', async () => {
    const wrapper = mount(TestComponent, {
      props: { galleries: createMockGalleries() },
    });

    wrapper.vm.filters.status = '0';
    await nextTick();

    expect(wrapper.vm.filteredGalleries).toHaveLength(2);
    expect(wrapper.vm.filteredGalleries.every(g => g.status === false)).toBe(true);
  });

  it('combines multiple filters', async () => {
    const wrapper = mount(TestComponent, {
      props: { galleries: createMockGalleries() },
    });

    wrapper.vm.filters.search = 'Grid';
    wrapper.vm.filters.type = 'grid';
    wrapper.vm.filters.status = '1';
    await nextTick();

    expect(wrapper.vm.filteredGalleries).toHaveLength(2);
    expect(wrapper.vm.filteredGalleries.every(g => g.type === 'grid' && g.status)).toBe(true);
  });

  it('returns empty array when no matches', async () => {
    const wrapper = mount(TestComponent, {
      props: { galleries: createMockGalleries() },
    });

    wrapper.vm.filters.search = 'NonExistent';
    await nextTick();

    expect(wrapper.vm.filteredGalleries).toHaveLength(0);
  });

  it('resets filters to defaults', async () => {
    const wrapper = mount(TestComponent, {
      props: { galleries: createMockGalleries() },
    });

    wrapper.vm.filters.search = 'test';
    wrapper.vm.filters.type = 'slider';
    wrapper.vm.filters.status = '0';
    wrapper.vm.resetFilters();
    await nextTick();

    expect(wrapper.vm.filters).toEqual({ search: '', type: '', status: '' });
    expect(wrapper.vm.filteredGalleries).toHaveLength(4);
  });
});
