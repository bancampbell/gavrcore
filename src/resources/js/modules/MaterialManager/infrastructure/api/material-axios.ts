import axios from 'axios';

const materialAxios = axios.create({
    baseURL: '/admin/materials',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

materialAxios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const requestUrl = error.config?.url || '';
            const isAdminRequest = requestUrl.startsWith('/admin') || window.location.pathname.startsWith('/admin');
            window.location.href = isAdminRequest ? '/admin/login' : '/login';
            return Promise.reject(error);
        }
        if (error.response?.status === 403) {
            console.error('Доступ запрещен');
        }
        return Promise.reject(error);
    }
);

export default materialAxios;
