import axios from 'axios';
import router from '@/router';

const api = axios.create({
    withCredentials: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status;

        if (status === 401) {
            router.push({
                name: 'login',
                query: {
                    redirect: router.currentRoute.value.fullPath,
                },
            });
        }

        if (status === 403) {
            router.push({
                name: 'accessDenied',
            });
        }

        if (status >= 500) {
            router.push({
                name: 'error',
            });
        }

        return Promise.reject(error);
    }
);

export default api;