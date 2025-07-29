import axios from 'axios';
import { APP_CONFIG } from './config';

window.axios = axios;

// Configurar la URL base para todas las peticiones
window.axios.defaults.baseURL = APP_CONFIG.baseURL;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Interceptor para manejar las respuestas
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Redirigir al login si no está autenticado
            window.location.href = APP_CONFIG.baseURL + '/login';
        }
        return Promise.reject(error);
    }
);