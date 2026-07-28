import { FilePath } from '../values/FilePath';
import {
    ALLOWED_EXTENSIONS,
    IMAGE_EXTENSIONS,
    VIDEO_EXTENSIONS,
    MAX_FILE_SIZE,
} from '../constants/fileTypes';

export interface ValidationResult {
    valid: boolean;
    errors: string[];
}

export class FileValidator {
    private static readonly MAX_FILE_SIZE = MAX_FILE_SIZE;
    private static readonly ALLOWED_EXTENSIONS = ALLOWED_EXTENSIONS;

    static validateFile(file: File | { name: string; size: number }): ValidationResult {
        const errors: string[] = [];

        if (file.size > this.MAX_FILE_SIZE) {
            errors.push(`Файл слишком большой. Максимальный размер: ${this.formatSize(this.MAX_FILE_SIZE)}`);
        }

        const ext = file.name.split('.').pop()?.toLowerCase() || '';
        if (!this.ALLOWED_EXTENSIONS.includes(ext)) {
            errors.push(`Недопустимый тип файла: ${ext}. Разрешены: ${this.ALLOWED_EXTENSIONS.join(', ')}`);
        }

        return {
            valid: errors.length === 0,
            errors,
        };
    }

    static validateFiles(files: (File | { name: string; size: number })[]): ValidationResult {
        const allErrors: string[] = [];
        for (const file of files) {
            const result = this.validateFile(file);
            if (!result.valid) {
                allErrors.push(`${file.name}: ${result.errors.join('; ')}`);
            }
        }
        return {
            valid: allErrors.length === 0,
            errors: allErrors,
        };
    }

    static validateExtension(path: FilePath | string): boolean {
        const ext = typeof path === 'string'
            ? path.split('.').pop()?.toLowerCase() || ''
            : path.getExtension();
        return this.ALLOWED_EXTENSIONS.includes(ext);
    }

    static validateImage(file: File | { name: string; size: number }): ValidationResult {
        const result = this.validateFile(file);
        if (!result.valid) return result;

        const ext = file.name.split('.').pop()?.toLowerCase() || '';
        if (!IMAGE_EXTENSIONS.includes(ext)) {
            return {
                valid: false,
                errors: [`Файл должен быть изображением. Получено: ${ext}`],
            };
        }
        return { valid: true, errors: [] };
    }

    static validateVideo(file: File | { name: string; size: number }): ValidationResult {
        const result = this.validateFile(file);
        if (!result.valid) return result;

        const ext = file.name.split('.').pop()?.toLowerCase() || '';
        if (!VIDEO_EXTENSIONS.includes(ext)) {
            return {
                valid: false,
                errors: [`Файл должен быть видео. Получено: ${ext}`],
            };
        }
        return { valid: true, errors: [] };
    }

    private static formatSize(bytes: number): string {
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        const size = (bytes / Math.pow(1024, i)).toFixed(1);
        return `${size} ${units[i]}`;
    }
}
