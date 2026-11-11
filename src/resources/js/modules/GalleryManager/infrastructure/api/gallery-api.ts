import { galleryAxios } from './gallery-axios';
import type { Gallery, GalleryListResponse, GalleryImage, GalleryListParams } from '../../ui/types';

export interface ApiResponse<T> {
    success: boolean;
    message: string;
    data?: T;
}

export const galleryApi = {
    list: (params?: GalleryListParams) =>
        galleryAxios.get<GalleryListResponse>('/admin/galleries/list', { params }),

    store: (data: Pick<Gallery, 'title' | 'type' | 'status' | 'settings'>) =>
        galleryAxios.post<{ success: boolean; message: string; id: number }>('/admin/galleries', data),

    update: (id: number, data: Pick<Gallery, 'title' | 'type' | 'status' | 'settings'>) =>
        galleryAxios.put<{ success: boolean; message: string }>(`/admin/galleries/${id}`, data),

    destroy: (id: number) =>
        galleryAxios.delete<{ success: boolean; message: string }>(`/admin/galleries/${id}`),

    publish: (id: number) =>
        galleryAxios.post<{ success: boolean; message: string }>(`/admin/galleries/${id}/publish`),

    unpublish: (id: number) =>
        galleryAxios.post<{ success: boolean; message: string }>(`/admin/galleries/${id}/unpublish`),

    uploadImage: (galleryId: number, formData: FormData) =>
        galleryAxios.post<{ success: boolean; message: string; image: GalleryImage }>(
            `/admin/galleries/${galleryId}/images`,
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        ),

    updateImage: (galleryId: number, imageId: number, data: Partial<GalleryImage>) =>
        galleryAxios.put<{ success: boolean; message: string; image: GalleryImage }>(
            `/admin/galleries/${galleryId}/images/${imageId}`,
            data
        ),

    deleteImage: (galleryId: number, imageId: number) =>
        galleryAxios.delete<{ success: boolean; message: string }>(
            `/admin/galleries/${galleryId}/images/${imageId}`
        ),
};
