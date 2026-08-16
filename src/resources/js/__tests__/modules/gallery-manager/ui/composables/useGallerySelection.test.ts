import { describe, it, expect } from 'vitest';
import { ref, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import { useGallerySelection } from '@/modules/GalleryManager/ui/composables/useGallerySelection';
import type { GalleryListItem } from '@/modules/GalleryManager/ui/types';

const mockItems: GalleryListItem[] = [
  { id: 1, title: 'Alpha', type: 'grid', status: true, created_at: '2024-01-01' },
  { id: 2, title: 'Beta', type: 'slider', status: false, created_at: '2024-01-02' },
  { id: 3, title: 'Gamma', type: 'slideshow', status: true, created_at: '2024-01-03' },
];

const TestComponent = {
  props: ['items'],
  setup(props: { items: GalleryListItem[] }) {
    const itemsRef = ref(props.items);
    return useGallerySelection(itemsRef);
  },
  template: '<div></div>',
};

describe('useGallerySelection', () => {
  it('initializes with empty selection', () => {
    const wrapper = mount(TestComponent, { props: { items: mockItems } });

    expect(wrapper.vm.selectedIds).toEqual([]);
    expect(wrapper.vm.allSelected).toBe(false);
  });

  it('toggles item selection on', () => {
    const wrapper = mount(TestComponent, { props: { items: mockItems } });

    wrapper.vm.toggleSelect(1);
    expect(wrapper.vm.selectedIds).toContain(1);
    expect(wrapper.vm.selectedIds).toHaveLength(1);
  });

  it('toggles item selection off', () => {
    const wrapper = mount(TestComponent, { props: { items: mockItems } });

    wrapper.vm.toggleSelect(1);
    wrapper.vm.toggleSelect(1);
    expect(wrapper.vm.selectedIds).not.toContain(1);
    expect(wrapper.vm.selectedIds).toHaveLength(0);
  });

  it('selects all items via allSelected setter', async () => {
    const wrapper = mount(TestComponent, { props: { items: mockItems } });

    wrapper.vm.allSelected = true;
    await nextTick();

    expect(wrapper.vm.selectedIds).toEqual([1, 2, 3]);
    expect(wrapper.vm.allSelected).toBe(true);
  });

  it('deselects all items via allSelected setter', async () => {
    const wrapper = mount(TestComponent, { props: { items: mockItems } });

    wrapper.vm.allSelected = true;
    await nextTick();
    wrapper.vm.allSelected = false;
    await nextTick();

    expect(wrapper.vm.selectedIds).toEqual([]);
    expect(wrapper.vm.allSelected).toBe(false);
  });

  it('computes allSelected as true only when all items selected', async () => {
    const wrapper = mount(TestComponent, { props: { items: mockItems } });

    wrapper.vm.toggleSelect(1);
    await nextTick();
    expect(wrapper.vm.allSelected).toBe(false);

    wrapper.vm.toggleSelect(2);
    wrapper.vm.toggleSelect(3);
    await nextTick();
    expect(wrapper.vm.allSelected).toBe(true);
  });

  it('handles empty items array', async () => {
    const wrapper = mount(TestComponent, { props: { items: [] } });

    expect(wrapper.vm.allSelected).toBe(false);
    wrapper.vm.allSelected = true;
    await nextTick();
    expect(wrapper.vm.selectedIds).toEqual([]);
  });

  it('clears selection', () => {
    const wrapper = mount(TestComponent, { props: { items: mockItems } });

    wrapper.vm.toggleSelect(1);
    wrapper.vm.toggleSelect(2);
    wrapper.vm.clearSelection();

    expect(wrapper.vm.selectedIds).toEqual([]);
  });

  it('does not duplicate ids on multiple toggles', () => {
    const wrapper = mount(TestComponent, { props: { items: mockItems } });

    wrapper.vm.toggleSelect(1);
    wrapper.vm.toggleSelect(1);
    wrapper.vm.toggleSelect(1);

    expect(wrapper.vm.selectedIds.filter((id: number) => id === 1)).toHaveLength(1);
  });
});
