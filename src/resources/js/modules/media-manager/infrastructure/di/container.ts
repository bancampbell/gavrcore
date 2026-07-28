import { ApiMediaRepository } from '../repositories/ApiMediaRepository';
import type { MediaRepository } from '../../domain/repositories/MediaRepository';

export interface MediaManagerContainer {
    mediaRepository: MediaRepository;
}

let container: MediaManagerContainer | null = null;

export function createMediaManagerContainer(): MediaManagerContainer {
    return {
        mediaRepository: new ApiMediaRepository(),
    };
}

export function getMediaManagerContainer(): MediaManagerContainer {
    if (!container) {
        container = createMediaManagerContainer();
    }
    return container;
}

export function resetMediaManagerContainer(): void {
    container = null;
}
