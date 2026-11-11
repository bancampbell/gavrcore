import axios from 'axios';

export const submissionApi = {
    showSubmission(id: number) {
        return axios.get(`/admin/submissions/${id}`);
    },
    deleteSubmission(id: number) {
        return axios.delete(`/admin/submissions/${id}`);
    },
    markAsRead(ids: number[]) {
        return axios.post('/admin/submissions/mark-read', { ids });
    },
    deleteBulk(ids: number[]) {
        return axios.post('/admin/submissions/destroy-bulk', { ids });
    },
};
