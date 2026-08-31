import axios from 'axios';

export const formApi = {
    getForms(params?: Record<string, any>) {
        return axios.get('/admin/forms', { params });
    },
    createForm(data: Record<string, any>) {
        return axios.post('/admin/forms', data);
    },
    updateForm(id: number, data: Record<string, any>) {
        return axios.put(`/admin/forms/${id}`, data);
    },
    updateFormStatus(id: number, status: boolean) {
        return axios.put(`/admin/forms/${id}/status`, { status });
    },
    deleteForm(id: number) {
        return axios.delete(`/admin/forms/${id}`);
    },
    getFormList() {
        return axios.get('/admin/forms/list');
    },
    updateFormFields(id: number, fields: any[]) {
        return axios.put(`/admin/forms/${id}/fields`, { fields });
    },
};