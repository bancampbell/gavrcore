import { describe, it, expect, vi, beforeEach } from 'vitest';
import { galleryApi } from '@/modules/GalleryManager/infrastructure/api/gallery-api';
import { galleryAxios } from '@/modules/GalleryManager/infrastructure/api/gallery-axios';
import type { GalleryImage } from '@/modules/GalleryManager/ui/types';

vi.mock('@/modules/GalleryManager/infrastructure/api/gallery-axios', () => ({
  galleryAxios: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}));

describe('galleryApi', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('list', () => {
    it('fetches galleries without filters', async () => {
      const mockData = [
        { id: 1, title: 'Gallery 1', type: 'grid', status: true, images_count: 5, created_at: '2024-01-01' },
      ];
      vi.mocked(galleryAxios.get).mockResolvedValueOnce({ data: mockData });

      const result = await galleryApi.list();

      expect(galleryAxios.get).toHaveBeenCalledTimes(1);
      expect(galleryAxios.get).toHaveBeenCalledWith('/admin/galleries/list', { params: undefined });
      expect(result.data).toEqual(mockData);
    });

    it('fetches galleries with filters', async () => {
      const mockData = [];
      vi.mocked(galleryAxios.get).mockResolvedValueOnce({ data: mockData });

      await galleryApi.list({ search: 'test', type: 'grid', status: true });

      expect(galleryAxios.get).toHaveBeenCalledWith('/admin/galleries/list', {
        params: { search: 'test', type: 'grid', status: true },
      });
    });
  });

  describe('store', () => {
    it('creates gallery and returns response', async () => {
      const payload = { title: 'New Gallery', type: 'grid' as const, status: true, settings: {} };
      const mockResponse = { success: true, message: 'Галерея создана', id: 42 };
      vi.mocked(galleryAxios.post).mockResolvedValueOnce({ data: mockResponse });

      const result = await galleryApi.store(payload);

      expect(galleryAxios.post).toHaveBeenCalledWith('/admin/galleries', payload);
      expect(result.data).toEqual(mockResponse);
    });
  });

  describe('update', () => {
    it('updates gallery by id', async () => {
      const payload = { title: 'Updated', type: 'slider' as const, status: false, settings: { gutter: 10 } };
      const mockResponse = { success: true, message: 'Галерея обновлена' };
      vi.mocked(galleryAxios.put).mockResolvedValueOnce({ data: mockResponse });

      const result = await galleryApi.update(1, payload);

      expect(galleryAxios.put).toHaveBeenCalledWith('/admin/galleries/1', payload);
      expect(result.data).toEqual(mockResponse);
    });
  });

  describe('destroy', () => {
    it('deletes gallery by id', async () => {
      const mockResponse = { success: true, message: 'Галерея удалена' };
      vi.mocked(galleryAxios.delete).mockResolvedValueOnce({ data: mockResponse });

      const result = await galleryApi.destroy(5);

      expect(galleryAxios.delete).toHaveBeenCalledWith('/admin/galleries/5');
      expect(result.data).toEqual(mockResponse);
    });
  });

  describe('publish', () => {
    it('publishes gallery', async () => {
      const mockResponse = { success: true, message: 'Опубликовано' };
      vi.mocked(galleryAxios.post).mockResolvedValueOnce({ data: mockResponse });

      const result = await galleryApi.publish(3);

      expect(galleryAxios.post).toHaveBeenCalledWith('/admin/galleries/3/publish');
      expect(result.data).toEqual(mockResponse);
    });
  });

  describe('unpublish', () => {
    it('unpublishes gallery', async () => {
      const mockResponse = { success: true, message: 'Снято с публикации' };
      vi.mocked(galleryAxios.post).mockResolvedValueOnce({ data: mockResponse });

      const result = await galleryApi.unpublish(3);

      expect(galleryAxios.post).toHaveBeenCalledWith('/admin/galleries/3/unpublish');
      expect(result.data).toEqual(mockResponse);
    });
  });

  describe('uploadImage', () => {
    it('uploads image with multipart form data', async () => {
      const formData = new FormData();
      formData.append('image', new Blob(), 'test.jpg');
      const mockImage: GalleryImage = {
        id: 1, gallery_id: 10, image_path: '/storage/test.jpg',
        title: 'Test', description: null, alt_text: null, link: null,
        ordering: 1, status: true,
      };
      vi.mocked(galleryAxios.post).mockResolvedValueOnce({
        data: { success: true, message: 'Загружено', image: mockImage },
      });

      const result = await galleryApi.uploadImage(10, formData);

      expect(galleryAxios.post).toHaveBeenCalledWith(
        '/admin/galleries/10/images',
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      expect(result.data.image).toEqual(mockImage);
    });
  });

  describe('updateImage', () => {
    it('updates image metadata', async () => {
      const mockImage: GalleryImage = {
        id: 2, gallery_id: 10, image_path: '/storage/img.jpg',
        title: 'Updated', description: 'Desc', alt_text: 'Alt', link: 'https://example.com',
        ordering: 2, status: true,
      };
      vi.mocked(galleryAxios.put).mockResolvedValueOnce({
        data: { success: true, message: 'Обновлено', image: mockImage },
      });

      const result = await galleryApi.updateImage(10, 2, { title: 'Updated' });

      expect(galleryAxios.put).toHaveBeenCalledWith(
        '/admin/galleries/10/images/2',
        { title: 'Updated' }
      );
      expect(result.data.image).toEqual(mockImage);
    });
  });

  describe('deleteImage', () => {
    it('deletes image by gallery and image id', async () => {
      vi.mocked(galleryAxios.delete).mockResolvedValueOnce({
        data: { success: true, message: 'Удалено' },
      });

      const result = await galleryApi.deleteImage(10, 5);

      expect(galleryAxios.delete).toHaveBeenCalledWith('/admin/galleries/10/images/5');
      expect(result.data.success).toBe(true);
    });
  });

  describe('getPublic', () => {
    it('fetches public gallery by id', async () => {
      const mockGallery = { id: 1, title: 'Public', type: 'grid', status: true, settings: {}, images: [] };
      vi.mocked(galleryAxios.get).mockResolvedValueOnce({ data: mockGallery });

      const result = await galleryApi.getPublic(1);

      expect(galleryAxios.get).toHaveBeenCalledWith('/galleries/1');
      expect(result.data).toEqual(mockGallery);
    });
  });
});
