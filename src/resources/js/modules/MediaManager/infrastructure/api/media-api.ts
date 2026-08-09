const API_BASE = '/admin/media';

function getCsrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta?.getAttribute('content') || '';
}

const getHeaders = (): Record<string, string> => ({
    'X-CSRF-TOKEN': getCsrfToken(),
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
});

async function handleResponse(response: Response) {
    if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        throw new Error(data.message || `HTTP ${response.status}`);
    }
    return response.json();
}

export const mediaApi = {
    async loadContents(path: string) {
        const response = await fetch(`${API_BASE}/contents?path=${encodeURIComponent(path)}`, {
            headers: getHeaders(),
        });
        return handleResponse(response);
    },

    async loadPaginatedContents(
        path: string,
        page: number = 1,
        perPage: number = 20,
        sort: string = 'name_asc',
        search: string | null = null,
    ) {
        const params = new URLSearchParams({
            path,
            page: String(page),
            per_page: String(perPage),
            sort,
        });
        if (search) {
            params.append('search', search);
        }
        const response = await fetch(`${API_BASE}/contents/paginated?${params.toString()}`, {
            headers: getHeaders(),
        });
        return handleResponse(response);
    },

    async loadFolders() {
        const response = await fetch(`${API_BASE}/folders`, {
            headers: getHeaders(),
        });
        return handleResponse(response);
    },

    async createFolder(name: string, path: string) {
        const response = await fetch(`${API_BASE}/folder`, {
            method: 'POST',
            headers: { ...getHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, path }),
        });
        return handleResponse(response);
    },

    async renameItem(oldPath: string, newName: string) {
        const response = await fetch(`${API_BASE}/rename`, {
            method: 'POST',
            headers: { ...getHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify({ old_path: oldPath, new_name: newName }),
        });
        return handleResponse(response);
    },

    async deleteItem(path: string) {
        const response = await fetch(`${API_BASE}/item`, {
            method: 'DELETE',
            headers: { ...getHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify({ path }),
        });
        return handleResponse(response);
    },

    async deleteItems(paths: string[]) {
        const response = await fetch(`${API_BASE}/items`, {
            method: 'DELETE',
            headers: { ...getHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify({ paths }),
        });
        return handleResponse(response);
    },

    async copyItem(path: string) {
        const response = await fetch(`${API_BASE}/copy`, {
            method: 'POST',
            headers: { ...getHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify({ path }),
        });
        return handleResponse(response);
    },

    async uploadFile(files: FileList, path: string) {
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }
        formData.append('path', path);

        const response = await fetch(`${API_BASE}/upload`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: formData,
        });
        return handleResponse(response);
    },
};
