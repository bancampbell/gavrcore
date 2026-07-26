import type { LinkData } from '../values/LinkData';

export interface LinkPort {
    insertLink(data: LinkData, position?: { from: number; to: number }): void;
    updateLink(oldText: string, data: LinkData): void;
    getLinkAt(pos: number): LinkData | null;
}
