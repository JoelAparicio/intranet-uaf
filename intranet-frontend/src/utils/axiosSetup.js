// src/utils/axiosSetup.js
import axios from 'axios';
import store from '@/store';

// ===== CONFIGURACIÓN BASE DE AXIOS =====
axios.defaults.baseURL = process.env.VUE_APP_API_URL || 'http://172.19.115.44';
axios.defaults.timeout = 10000; // 10 segundos
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

// ===== INTERCEPTOR DE REQUEST =====
axios.interceptors.request.use(
    (config) => {
        // Agregar token si existe
        const token = store.getters['auth/token'];
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        // Log para desarrollo
        if (process.env.NODE_ENV === 'development') {
            console.log('🚀 Axios Request:', {
                method: config.method?.toUpperCase(),
                url: config.url,
                headers: config.headers,
                data: config.data
            });
        }

        return config;
    },
    (error) => {
        console.error('❌ Error en request interceptor:', error);
        return Promise.reject(error);
    }
);

// ===== INTERCEPTOR DE RESPONSE =====
axios.interceptors.response.use(
    (response) => {
        // Log para desarrollo
        if (process.env.NODE_ENV === 'development') {
            console.log('✅ Axios Response:', {
                status: response.status,
                url: response.config.url,
                data: response.data
            });
        }

        return response;
    },
    async (error) => {
        const originalRequest = error.config;

        // Log del error
        console.error('❌ Axios Error:', {
            status: error.response?.status,
            url: error.config?.url,
            message: error.message,
            data: error.response?.data
        });

        // ===== MANEJO DE ERRORES 401 (Token Expirado) =====
        if (error.response?.status === 401 && !originalRequest._retry) {
            originalRequest._retry = true;

            try {
                console.log('🔄 Token expirado - intentando refresh...');

                // Intentar refrescar token
                const refreshed = await store.dispatch('auth/refreshToken');

                if (refreshed) {
                    console.log('✅ Token refrescado exitosamente');

                    // Retry request original con nuevo token
                    const newToken = store.getters['auth/token'];
                    originalRequest.headers.Authorization = `Bearer ${newToken}`;

                    return axios(originalRequest);
                } else {
                    console.log('❌ No se pudo refrescar token - logout');
                    await store.dispatch('auth/logout');
                    window.location.href = '/login';
                }
            } catch (refreshError) {
                console.error('❌ Error al refrescar token:', refreshError);
                await store.dispatch('auth/logout');
                window.location.href = '/login';
            }
        }

        // ===== MANEJO DE ERRORES 403 (Sin permisos) =====
        if (error.response?.status === 403) {
            console.warn('⚠️ Sin permisos para esta acción');

            // Mostrar mensaje de error si SweetAlert está disponible
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'error',
                    title: 'Sin permisos',
                    text: 'No tienes permisos para realizar esta acción',
                    confirmButtonText: 'Entendido'
                });
            }
        }

        // ===== MANEJO DE ERRORES DE RED =====
        if (error.code === 'NETWORK_ERROR' || !error.response) {
            console.error('❌ Error de conexión');

            if (window.Swal) {
                window.Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor. Verifica tu conexión a internet.',
                    confirmButtonText: 'Reintentar'
                });
            }
        }

        return Promise.reject(error);
    }
);

// ===== EXPORTAR AXIOS CONFIGURADO =====
export default axios;

// ===== FUNCIONES HELPER =====

/**
 * Realiza una petición GET con manejo de errores
 * @param {string} url - URL del endpoint
 * @param {object} config - Configuración adicional
 * @returns {Promise} - Respuesta de la petición
 */
export const apiGet = async (url, config = {}) => {
    try {
        const response = await axios.get(url, config);
        return response.data;
    } catch (error) {
        console.error(`Error en GET ${url}:`, error);
        throw error;
    }
};

/**
 * Realiza una petición POST con manejo de errores
 * @param {string} url - URL del endpoint
 * @param {object} data - Datos a enviar
 * @param {object} config - Configuración adicional
 * @returns {Promise} - Respuesta de la petición
 */
export const apiPost = async (url, data = {}, config = {}) => {
    try {
        const response = await axios.post(url, data, config);
        return response.data;
    } catch (error) {
        console.error(`Error en POST ${url}:`, error);
        throw error;
    }
};

/**
 * Realiza una petición PUT con manejo de errores
 * @param {string} url - URL del endpoint
 * @param {object} data - Datos a enviar
 * @param {object} config - Configuración adicional
 * @returns {Promise} - Respuesta de la petición
 */
export const apiPut = async (url, data = {}, config = {}) => {
    try {
        const response = await axios.put(url, data, config);
        return response.data;
    } catch (error) {
        console.error(`Error en PUT ${url}:`, error);
        throw error;
    }
};

/**
 * Realiza una petición DELETE con manejo de errores
 * @param {string} url - URL del endpoint
 * @param {object} config - Configuración adicional
 * @returns {Promise} - Respuesta de la petición
 */
export const apiDelete = async (url, config = {}) => {
    try {
        const response = await axios.delete(url, config);
        return response.data;
    } catch (error) {
        console.error(`Error en DELETE ${url}:`, error);
        throw error;
    }
};

// ===== FUNCIONES PARA MANEJO DE TOKENS =====

/**
 * Verifica si el token actual es válido haciendo una petición al servidor
 * @returns {Promise<boolean>} - True si el token es válido
 */
export const checkTokenValidity = async () => {
    try {
        const response = await axios.get('/api/user');
        return response.status === 200;
    } catch (error) {
        console.warn('⚠️ Token inválido:', error.response?.status);
        return false;
    }
};

/**
 * Configura un timer para auto-refrescar el token antes de que expire
 */
export const setupTokenTimer = () => {
    // Limpiar timer anterior si existe
    if (window.tokenRefreshTimer) {
        clearTimeout(window.tokenRefreshTimer);
    }

    const expiresAt = localStorage.getItem('token_expires_at');
    if (!expiresAt) return;

    const now = new Date().getTime();
    const expiry = new Date(expiresAt).getTime();
    const timeUntilExpiry = expiry - now;

    // Programar refresh 5 minutos antes de que expire
    const refreshTime = Math.max(0, timeUntilExpiry - (5 * 60 * 1000));

    if (refreshTime > 0) {
        window.tokenRefreshTimer = setTimeout(async () => {
            console.log('🔄 Auto-refrescando token...');

            // Importación dinámica para evitar dependencia circular
            const { default: store } = await import('@/store');
            await store.dispatch('auth/refreshToken');
        }, refreshTime);

        console.log(`⏰ Token timer configurado - refresh en ${Math.floor(refreshTime / 60000)} minutos`);
    }
};

/**
 * Obtiene la URL completa para un endpoint
 * @param {string} endpoint - Endpoint de la API
 * @returns {string} - URL completa
 */
export const getApiUrl = (endpoint) => {
    const baseUrl = process.env.VUE_APP_API_URL || 'http://172.19.115.44';
    return `${baseUrl}/api/${endpoint}`;
};

