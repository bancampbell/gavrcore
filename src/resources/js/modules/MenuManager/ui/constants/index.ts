export const LINK_TYPE_LABELS: Record<string, string> = {
    url: 'URL',
    material: 'Материал',
    separator: 'Разделитель',
    heading: 'Заголовок',
    external: 'Внешний URL',
};

export const ACCESS_OPTIONS = [
    { value: 'all', label: 'Все' },
    { value: 'guest', label: 'Только гости' },
    { value: 'registered', label: 'Только зарегистрированные' },
];

export const TARGET_OPTIONS = [
    { value: '_self', label: 'Текущее окно' },
    { value: '_blank', label: 'Новое окно' },
];