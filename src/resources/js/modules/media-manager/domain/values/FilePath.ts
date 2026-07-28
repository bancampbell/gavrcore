export class FilePath {
    private readonly value: string;

    private constructor(path: string) {
        if (path.trim() === '') {
            this.value = '';
            return;
        }
        this.value = path.replace(/\/+/g, '/').replace(/^\/+|\/+$/g, '');
    }

    static create(path: string): FilePath {
        return new FilePath(path);
    }

    static fromStorageUrl(url: string): FilePath {
        const path = url.replace('/storage/uploads/', '');
        return new FilePath(path);
    }

    static root(): FilePath {
        return new FilePath('');
    }

    toString(): string {
        return this.value;
    }

    getParent(): FilePath {
        if (this.isRoot()) {
            return FilePath.root();
        }
        const parts = this.value.split('/');
        parts.pop();
        return new FilePath(parts.join('/'));
    }

    getName(): string {
        const parts = this.value.split('/');
        return parts[parts.length - 1] || '';
    }

    getExtension(): string {
        const name = this.getName();
        const parts = name.split('.');
        return parts.length > 1 ? parts[parts.length - 1].toLowerCase() : '';
    }

    getFullPath(): string {
        return this.value;
    }

    getStorageUrl(): string {
        return `/storage/uploads/${this.value}`;
    }

    isRoot(): boolean {
        return this.value === '';
    }

    equals(other: FilePath): boolean {
        return this.value === other.value;
    }

    isChildOf(parent: FilePath): boolean {
        if (parent.isRoot()) return true;
        return this.value.startsWith(parent.value + '/');
    }

    isValidExtension(extensions: string[]): boolean {
        const ext = this.getExtension();
        return extensions.includes(ext);
    }

    isValidMimeType(mimeTypes: string[]): boolean {
        return this.isValidExtension(mimeTypes.map(ext => ext.replace('image/', '').replace('video/', '')));
    }
}
