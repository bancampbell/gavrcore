import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import GalleryRenderer from '@/modules/GalleryManager/ui/components/GalleryRenderer.vue';
import type { Gallery, GalleryImage } from '@/modules/GalleryManager/ui/types';

vi.mock('@/modules/GalleryManager/ui/styles/gallery.css', () => ({}));

describe('GalleryRenderer', () => {
  const mockImages: GalleryImage[] = [
    { id: 1, gallery_id: 1, image_path: '/img/1.jpg', title: 'First', description: 'Desc 1', alt_text: 'Alt 1', link: null, ordering: 1, status: true },
    { id: 2, gallery_id: 1, image_path: '/img/2.jpg', title: 'Second', description: null, alt_text: null, link: 'https://example.com', ordering: 2, status: true },
  ];

  const createGallery = (type: Gallery['type'], settings: any = {}): Gallery => ({
    id: 1,
    title: 'Test Gallery',
    type,
    status: true,
    settings,
    images: mockImages,
    created_at: '2024-01-01',
    updated_at: '2024-01-01',
  });

  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('renders fallback when gallery is null', () => {
    const wrapper = mount(GalleryRenderer, {
      props: { gallery: null },
    });
    expect(wrapper.text()).toContain('Галерея не найдена');
  });

  describe('Grid type', () => {
    it('renders grid layout with images', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid') },
      });
      expect(wrapper.find('.gallery-grid').exists()).toBe(true);
      expect(wrapper.findAll('.gallery-item')).toHaveLength(2);
    });

    it('applies gutter style from settings', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid', { gutter: 30 }) },
      });
      const grid = wrapper.find('.gallery-grid');
      expect(grid.attributes('style')).toContain('gap: 30px');
    });

    it('shows title when content.show_title is true', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid', { content: { show_title: true } }) },
      });
      expect(wrapper.text()).toContain('First');
    });

    it('hides title when content.show_title is false', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid', { content: { show_title: false } }) },
      });
      expect(wrapper.text()).not.toContain('First');
    });

    it('shows description when content.show_content is true', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid', { content: { show_content: true } }) },
      });
      expect(wrapper.text()).toContain('Desc 1');
    });

    it('shows link button when link.show is true and image has link', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid', { link: { show: true, style: 'button', text: 'Подробнее' } }) },
      });
      const link = wrapper.find('a[href="https://example.com"]');
      expect(link.exists()).toBe(true);
      expect(link.text()).toBe('Подробнее');
    });

    it('applies border classes from media settings', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid', { media: { border: 'rounded' } }) },
      });
      expect(wrapper.find('.rounded-lg').exists()).toBe(true);
    });

    it('opens lightbox on image click', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid') },
      });
      await wrapper.find('.gallery-item').trigger('click');
      expect(wrapper.find('.fixed.inset-0').exists()).toBe(true);
    });

    it('does not open lightbox when mode is disabled', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid', { lightbox: { mode: 'disabled' } }) },
      });
      await wrapper.find('.gallery-item').trigger('click');
      expect(wrapper.find('.fixed.inset-0').exists()).toBe(false);
    });
  });

  describe('Switcher type', () => {
    it('renders switcher layout', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('switcher') },
      });
      expect(wrapper.find('.switcher-gallery').exists()).toBe(true);
      expect(wrapper.find('.switcher-main').exists()).toBe(true);
      expect(wrapper.findAll('.switcher-thumbnail')).toHaveLength(2);
    });

    it('switches image on thumbnail click', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('switcher') },
      });
      const thumbnails = wrapper.findAll('.switcher-thumbnail');
      await thumbnails[1].trigger('click');
      expect(thumbnails[1].classes()).toContain('active');
    });

    it('shows labels when show_labels is true', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('switcher', { show_labels: true }) },
      });
      expect(wrapper.find('.label-overlay').exists()).toBe(true);
    });

    it('applies thumbnail size from settings', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('switcher', { thumbnail_size: 120 }) },
      });
      const thumb = wrapper.find('.switcher-thumbnail');
      expect(thumb.attributes('style')).toContain('width: 120px');
    });
  });

  describe('Slideshow type', () => {
    it('renders slideshow with navigation buttons', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('slideshow') },
      });
      expect(wrapper.findAll('button[aria-label="Previous slide"]')).toHaveLength(1);
      expect(wrapper.findAll('button[aria-label="Next slide"]')).toHaveLength(1);
    });

    it('does not show nav buttons with single image', () => {
      const singleImageGallery = createGallery('slideshow');
      singleImageGallery.images = [mockImages[0]];
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: singleImageGallery },
      });
      expect(wrapper.findAll('button[aria-label="Previous slide"]')).toHaveLength(0);
    });

    it('advances slide on next button click', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('slideshow') },
      });
      await wrapper.find('button[aria-label="Next slide"]').trigger('click');
      expect(wrapper.vm.currentSlide).toBe(1);
    });

    it('cycles back to first slide from last', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('slideshow') },
      });
      await wrapper.find('button[aria-label="Next slide"]').trigger('click');
      await wrapper.find('button[aria-label="Next slide"]').trigger('click');
      expect(wrapper.vm.currentSlide).toBe(0);
    });

    it('auto-advances slides on interval', () => {
      mount(GalleryRenderer, {
        props: { gallery: createGallery('slideshow', { autoplay_interval: 3000 }) },
      });
      vi.advanceTimersByTime(3000);
      // autoplay starts on mount
    });
  });

  describe('Slider type', () => {
    it('renders slider with transform style', () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('slider') },
      });
      const track = wrapper.find('.flex.transition-transform');
      expect(track.exists()).toBe(true);
      expect(track.attributes('style')).toContain('translateX(-0%)');
    });

    it('translates track on next slide', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('slider') },
      });
      await wrapper.find('button[aria-label="Next slide"]').trigger('click');
      const track = wrapper.find('.flex.transition-transform');
      expect(track.attributes('style')).toContain('translateX(-100%)');
    });
  });

  describe('Lightbox', () => {
    it('opens lightbox with correct image', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid') },
      });
      await wrapper.find('.gallery-item').trigger('click');
      expect(wrapper.vm.lightboxOpen).toBe(true);
      expect(wrapper.vm.lightboxImage?.id).toBe(1);
    });

    it('closes lightbox on close button click', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid') },
      });
      await wrapper.find('.gallery-item').trigger('click');
      await wrapper.find('button[aria-label="Close lightbox"]').trigger('click');
      expect(wrapper.vm.lightboxOpen).toBe(false);
    });

    it('navigates lightbox with prev/next', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid') },
      });
      await wrapper.find('.gallery-item').trigger('click');
      expect(wrapper.vm.lightboxIndex).toBe(0);

      await wrapper.find('button[aria-label="Next image"]').trigger('click');
      expect(wrapper.vm.lightboxIndex).toBe(1);

      await wrapper.find('button[aria-label="Previous image"]').trigger('click');
      expect(wrapper.vm.lightboxIndex).toBe(0);
    });

    it('closes lightbox on Escape key', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid') },
      });
      await wrapper.find('.gallery-item').trigger('click');
      window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
      expect(wrapper.vm.lightboxOpen).toBe(false);
    });

    it('shows counter in lightbox', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid') },
      });
      await wrapper.find('.gallery-item').trigger('click');
      expect(wrapper.text()).toContain('1 / 2');
    });

    it('shows title in lightbox when use_title is true', async () => {
      const wrapper = mount(GalleryRenderer, {
        props: { gallery: createGallery('grid', { lightbox: { use_title: true } }) },
      });
      await wrapper.find('.gallery-item').trigger('click');
      expect(wrapper.text()).toContain('First');
    });
  });
});
