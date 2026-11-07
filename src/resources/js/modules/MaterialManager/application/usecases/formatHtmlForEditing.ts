import { format } from 'prettier/standalone';
import * as htmlPlugin from 'prettier/plugins/html';
import type { Options } from 'prettier';

const FORMAT_OPTIONS: Options = {
    parser: 'html',
    plugins: [htmlPlugin],
    printWidth: 120,
    tabWidth: 4,
    htmlWhitespaceSensitivity: 'css',
};

export async function formatHtmlForEditing(html: string): Promise<string> {
    try {
        // trim: хвостовой перенос строки после корневого тега при setContent
        // превратился бы в пустой параграф в конце документа
        return (await format(html, FORMAT_OPTIONS)).trim();
    } catch {
        return html;
    }
}
