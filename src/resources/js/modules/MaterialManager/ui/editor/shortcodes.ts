// Сборка шорткодов для вставки в редактор.
// Кавычки в значениях атрибутов экранируются ('"' → '&quot;'): неэкранированная
// кавычка разрывает regex-парсер ShortcodeRenderer (name="([^"]*)"), и шорткод
// рендерится как обычный текст. '&quot;' regex пропускает ([^"]* не исключает '&'),
// галерея рендерится по id, так что decode на фронте не требуется.
export function escapeShortcodeAttr(value: string): string {
    return value.replace(/"/g, '&quot;');
}

export function buildGalleryShortcode(galleryId: number, galleryName: string): string {
    return `[gallery id="${galleryId}" name="${escapeShortcodeAttr(galleryName)}"]`;
}

export function buildFormShortcode(formId: number): string {
    return `[form id="${formId}"]`;
}
