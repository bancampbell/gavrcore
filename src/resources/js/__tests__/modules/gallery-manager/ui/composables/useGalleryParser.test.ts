import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { useGalleryParser } from '@/modules/GalleryManager/ui/composables/useGalleryParser';
import { galleryApi } from '@/modules/GalleryManager/infrastructure/api/gallery-api';
import type { Gallery } from '@/modules/GalleryManager/ui/types';

vi.mock('@/modules/GalleryManager/infrastructure/api/gallery-api');

const TestComponent = {
  setup() {
    return useGalleryParser();
  },
  template: '<div></div>',
};

describe('useGalleryParser', () => {
  const mockGallery = (id: number): Gallery => ({
    id,
    title: `Gallery ${id}`,
    type: 'grid',
    status: true,
    settings: {},
    images: [],
    created_at: '2024-01-01',
    updated_at: '2024-01-01',
  });

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('returns original content when no shortcodes present', async () => {
    const wrapper = mount(TestComponent);
    const content = 'Plain text without any shortcodes';

    const result = await wrapper.vm.parseGalleries(content);

    expect(result.content).toBe(content);
    expect(Object.keys(result.galleries)).toHaveLength(0);
    expect(galleryApi.getPublic).not.toHaveBeenCalled();
  });

  it('replaces single shortcode with marker', async () => {
    const wrapper = mount(TestComponent);
    vi.mocked(galleryApi.getPublic).mockResolvedValue({ data: null } as any);

    const content = '[gallery id="123"] Some text after';
    const result = await wrapper.vm.parseGalleries(content);

    expect(result.content).toContain('<div data-gallery-id="123"></div>');
    expect(result.content).not.toContain('[gallery id="123"]');
    expect(galleryApi.getPublic).toHaveBeenCalledWith(123);
  });

  it('replaces multiple different shortcodes', async () => {
    const wrapper = mount(TestComponent);
    vi.mocked(galleryApi.getPublic).mockResolvedValue({ data: null } as any);

    const content = '[gallery id="1"] text [gallery id="2" name="second"]';
    const result = await wrapper.vm.parseGalleries(content);

    expect(result.content).toContain('<div data-gallery-id="1"></div>');
    expect(result.content).toContain('<div data-gallery-id="2"></div>');
    expect(galleryApi.getPublic).toHaveBeenCalledTimes(2);
  });

  it('deduplicates api calls for repeated ids', async () => {
    const wrapper = mount(TestComponent);
    vi.mocked(galleryApi.getPublic).mockResolvedValue({ data: mockGallery(5) } as any);

    const content = '[gallery id="5"] [gallery id="5"] [gallery id="5"]';
    const result = await wrapper.vm.parseGalleries(content);

    expect(galleryApi.getPublic).toHaveBeenCalledTimes(1);
    expect(galleryApi.getPublic).toHaveBeenCalledWith(5);
    expect(result.galleries['5']).toEqual(mockGallery(5));
  });

  it('fetches and stores gallery data', async () => {
    const wrapper = mount(TestComponent);
    const gallery = mockGallery(10);
    vi.mocked(galleryApi.getPublic).mockResolvedValue({ data: gallery } as any);

    const content = '[gallery id="10"]';
    const result = await wrapper.vm.parseGalleries(content);

    expect(result.galleries['10']).toEqual(gallery);
  });

  it('handles missing gallery gracefully (404)', async () => {
    const wrapper = mount(TestComponent);
    vi.mocked(galleryApi.getPublic).mockRejectedValue(new Error('Not found'));

    const content = '[gallery id="999"]';
    const result = await wrapper.vm.parseGalleries(content);

    expect(result.galleries['999']).toBeNull();
    expect(result.content).toContain('<div data-gallery-id="999"></div>');
  });

  it('parses shortcode with name attribute', async () => {
    const wrapper = mount(TestComponent);
    vi.mocked(galleryApi.getPublic).mockResolvedValue({ data: null } as any);

    const content = '[gallery id="42" name="Hero Gallery"]';
    const result = await wrapper.vm.parseGalleries(content);

    expect(result.content).toContain('<div data-gallery-id="42"></div>');
    expect(galleryApi.getPublic).toHaveBeenCalledWith(42);
  });

  it('preserves surrounding content structure', async () => {
    const wrapper = mount(TestComponent);
    vi.mocked(galleryApi.getPublic).mockResolvedValue({ data: null } as any);

    const content = '<p>Before</p>[gallery id="7"]<p>After</p>';
    const result = await wrapper.vm.parseGalleries(content);

    expect(result.content).toBe('<p>Before</p><div data-gallery-id="7"></div><p>After</p>');
  });

  it('handles mixed valid and invalid shortcodes', async () => {
    const wrapper = mount(TestComponent);
    vi.mocked(galleryApi.getPublic)
      .mockResolvedValueOnce({ data: mockGallery(1) } as any)
      .mockRejectedValueOnce(new Error('Not found'));

    const content = '[gallery id="1"] [gallery id="2"]';
    const result = await wrapper.vm.parseGalleries(content);

    expect(result.galleries['1']).toEqual(mockGallery(1));
    expect(result.galleries['2']).toBeNull();
  });
});
