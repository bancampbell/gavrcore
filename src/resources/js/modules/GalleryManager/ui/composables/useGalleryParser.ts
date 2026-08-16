import { galleryApi } from '../../infrastructure/api/gallery-api';
import type { Gallery } from '../types';

export interface ParsedContent {
    content: string;
    galleries: Record<string, Gallery | null>;
}

export function useGalleryParser() {
    const parseGalleries = async (content: string): Promise<ParsedContent> => {
        const regex = /\[gallery\s+id="(\d+)"(?:\s+name="([^"]*)")?\]/g;
        const matches = Array.from(content.matchAll(regex));

        let processedContent = content;
        const galleryMap: Record<string, Gallery | null> = {};
        const galleryIds = new Set<string>();

        for (const match of matches) {
            const fullMatch = match[0];
            const galleryId = match[1];

            const marker = `<div data-gallery-id="${galleryId}"></div>`;
            processedContent = processedContent.replace(fullMatch, marker);

            if (!galleryMap[galleryId]) {
                galleryIds.add(galleryId);
            }
        }

        await Promise.allSettled(
            Array.from(galleryIds).map(async (id) => {
                try {
                    const response = await galleryApi.getPublic(Number(id));
                    galleryMap[id] = response.data;
                } catch (error) {
                    console.error(`Gallery ${id} not found`);
                    galleryMap[id] = null;
                }
            })
        );

        return {
            content: processedContent,
            galleries: galleryMap,
        };
    };

    return {
        parseGalleries,
    };
}
