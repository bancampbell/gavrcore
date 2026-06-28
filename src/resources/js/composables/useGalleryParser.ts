import axios from 'axios';

export function useGalleryParser() {
    const parseGalleries = async (content: string): Promise<{ content: string; galleries: Record<string, any> }> => {
        const regex = /\[gallery\s+id="(\d+)"(?:\s+name="([^"]*)")?\]/g;
        const matches = content.matchAll(regex);

        let processedContent = content;
        const galleryMap: Record<string, any> = {};
        const galleryIds: string[] = [];

        for (const match of matches) {
            const fullMatch = match[0];
            const galleryId = match[1];

            // Заменяем шорткод на HTML-элемент с data-атрибутом
            const marker = `<div data-gallery-id="${galleryId}"></div>`;
            processedContent = processedContent.replace(fullMatch, marker);

            if (!galleryMap[galleryId] && !galleryIds.includes(galleryId)) {
                galleryIds.push(galleryId);
            }
        }

        // Загружаем все галереи
        for (const id of galleryIds) {
            try {
                const response = await axios.get(`/api/galleries/${id}`);
                galleryMap[id] = response.data;
            } catch (error) {
                console.error(`Gallery ${id} not found`);
                galleryMap[id] = null;
            }
        }

        return {
            content: processedContent,
            galleries: galleryMap
        };
    };

    return {
        parseGalleries
    };
}
