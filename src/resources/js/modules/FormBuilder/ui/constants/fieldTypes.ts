export const FIELD_TYPES = [
    { type: 'text', icon: '📝', label: 'Текст' },
    { type: 'textarea', icon: '📄', label: 'Текстовая область' },
    { type: 'email', icon: '✉️', label: 'Email' },
    { type: 'phone', icon: '📞', label: 'Телефон' },
    { type: 'number', icon: '🔢', label: 'Число' },
    { type: 'date', icon: '📅', label: 'Дата' },
    { type: 'select', icon: '📋', label: 'Выпадающий список' },
    { type: 'checkbox', icon: '☑️', label: 'Чекбокс' },
    { type: 'radio', icon: '⭕', label: 'Радиокнопка' },
    { type: 'file', icon: '📎', label: 'Файл' },
    { type: 'url', icon: '🔗', label: 'URL' },
    { type: 'color', icon: '🎨', label: 'Цвет' },
    { type: 'time', icon: '🕐', label: 'Время' },
    { type: 'datetime', icon: '📆', label: 'Дата+время' },
    { type: 'range', icon: '📊', label: 'Ползунок' },
    { type: 'rating', icon: '⭐', label: 'Рейтинг' },
    { type: 'toggle', icon: '🔘', label: 'Переключатель' },
] as const;

export type FieldType = typeof FIELD_TYPES[number]['type'];