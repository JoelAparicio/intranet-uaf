// src/utils/apiHelper.js
import api from '@/config/api';
import axios from 'axios';

/**
 * Helper para hacer llamadas a la API usando endpoints de api.js
 */
export const apiCall = {
    get: (endpoint, config = {}) => {
        const url = `/${api.endpoints[endpoint]}`;
        return axios.get(url, config);
    },

    post: (endpoint, data = {}, config = {}) => {
        const url = `/${api.endpoints[endpoint]}`;
        return axios.post(url, data, config);
    },

    put: (endpoint, data = {}, config = {}) => {
        const url = `/${api.endpoints[endpoint]}`;
        return axios.put(url, data, config);
    },

    delete: (endpoint, config = {}) => {
        const url = `/${api.endpoints[endpoint]}`;
        return axios.delete(url, config);
    },

    // Para endpoints que requieren parámetros en la URL
    putWithId: (endpoint, id, data = {}, config = {}) => {
        const url = `/${api.endpoints[endpoint]}/${id}`;
        return axios.put(url, data, config);
    },

    deleteWithId: (endpoint, id, config = {}) => {
        const url = `/${api.endpoints[endpoint]}/${id}`;
        return axios.delete(url, config);
    }
};

/**
 * Obtener URL completa de un endpoint
 */
export const getEndpointUrl = (endpoint) => {
    return `${api.baseURL}/${api.endpoints[endpoint]}`;
};

/**
 * Obtener solo el path del endpoint
 */
export const getEndpointPath = (endpoint) => {
    return `/${api.endpoints[endpoint]}`;
};