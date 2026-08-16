import axios from 'axios';

export const galleryAxios = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});
