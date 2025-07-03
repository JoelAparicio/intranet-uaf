// src/store/modules/auth.js
import axios from 'axios';
import { checkTokenValidity, setupTokenTimer } from '@/utils/axiosSetup';

const state = {
    token: localStorage.getItem('token') || null,
    tokenExpiresAt: localStorage.getItem('token_expires_at') || null,
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    isAuthenticated: false,
    isLoading: false,
    lastTokenCheck: null
};

const mutations = {
    SET_TOKEN(state, token) {
        state.token = token;
        state.isAuthenticated = !!token;

        if (token) {
            localStorage.setItem('token', token);
        } else {
            localStorage.removeItem('token');
        }
    },

    SET_TOKEN_EXPIRY(state, expiresAt) {
        state.tokenExpiresAt = expiresAt;

        if (expiresAt) {
            localStorage.setItem('token_expires_at', expiresAt);
        } else {
            localStorage.removeItem('token_expires_at');
        }
    },

    SET_USER(state, user) {
        state.user = user;

        if (user) {
            localStorage.setItem('user', JSON.stringify(user));
        } else {
            localStorage.removeItem('user');
        }
    },

    SET_AUTHENTICATED(state, status) {
        state.isAuthenticated = status;
    },

    SET_LOADING(state, status) {
        state.isLoading = status;
    },

    SET_LAST_TOKEN_CHECK(state, timestamp) {
        state.lastTokenCheck = timestamp;
    }
};

const actions = {
    // ===== LOGIN MEJORADO =====
    async login({ commit, dispatch }, credentials) {
        try {
            commit('SET_LOADING', true);

            const response = await axios.post('/login', credentials);

            const { token, expires_at, user } = response.data;

            // Guardar datos de autenticación
            commit('SET_TOKEN', token);
            commit('SET_TOKEN_EXPIRY', expires_at);
            commit('SET_USER', user);
            commit('SET_AUTHENTICATED', true);

            // Configurar timer para auto-refresh
            setupTokenTimer();

            console.log('✅ Login exitoso:', { user: user.nombre, expiresAt: expires_at });

            return { success: true, user };

        } catch (error) {
            console.error('❌ Error en login:', error);

            commit('SET_LOADING', false);

            const errorMessage = error.response?.data?.message || 'Error en el login';
            return { success: false, error: errorMessage };

        } finally {
            commit('SET_LOADING', false);
        }
    },

    // ===== LOGOUT MEJORADO =====
    async logout({ commit }) {
        try {
            // Intentar logout en servidor (si hay token)
            if (state.token) {
                await axios.post('/logout');
            }
        } catch (error) {
            console.warn('⚠️ Error en logout del servidor:', error);
            // Continuar con logout local aunque falle el servidor
        } finally {
            // Limpiar estado local siempre
            commit('SET_TOKEN', null);
            commit('SET_TOKEN_EXPIRY', null);
            commit('SET_USER', null);
            commit('SET_AUTHENTICATED', false);

            console.log('👋 Logout completado');
        }
    },

    // ===== VERIFICAR TOKEN AL INICIAR APP =====
    async initAuth({ commit, dispatch }) {
        const token = localStorage.getItem('token');

        if (!token) {
            console.log('🚫 No hay token guardado');
            return false;
        }

        try {
            commit('SET_LOADING', true);

            // Verificar validez del token
            const isValid = await checkTokenValidity();

            if (isValid) {
                // Token válido, restaurar estado
                const user = JSON.parse(localStorage.getItem('user') || 'null');
                const expiresAt = localStorage.getItem('token_expires_at');

                commit('SET_TOKEN', token);
                commit('SET_TOKEN_EXPIRY', expiresAt);
                commit('SET_USER', user);
                commit('SET_AUTHENTICATED', true);

                // Configurar timer
                setupTokenTimer();

                console.log('✅ Autenticación restaurada:', user?.nombre);
                return true;
            } else {
                // Token inválido, limpiar
                await dispatch('logout');
                return false;
            }

        } catch (error) {
            console.error('❌ Error inicializando auth:', error);
            await dispatch('logout');
            return false;

        } finally {
            commit('SET_LOADING', false);
        }
    },

    // ===== REFRESCAR TOKEN MANUALMENTE =====
    async refreshToken({ commit }) {
        try {
            const response = await axios.post('/refresh-token');

            const { token, expires_at } = response.data;

            commit('SET_TOKEN', token);
            commit('SET_TOKEN_EXPIRY', expires_at);

            // Reconfigurar timer
            setupTokenTimer();

            console.log('🔄 Token refrescado manualmente');
            return true;

        } catch (error) {
            console.error('❌ Error refrescando token:', error);
            return false;
        }
    },

    // ===== VERIFICAR ESTADO PERIÓDICAMENTE =====
    async checkAuthStatus({ commit, dispatch, state }) {
        const now = Date.now();
        const lastCheck = state.lastTokenCheck;

        // Solo verificar cada 5 minutos
        if (lastCheck && (now - lastCheck) < 5 * 60 * 1000) {
            return state.isAuthenticated;
        }

        commit('SET_LAST_TOKEN_CHECK', now);

        if (!state.token) {
            return false;
        }

        try {
            const isValid = await checkTokenValidity();

            if (!isValid) {
                await dispatch('logout');
                return false;
            }

            return true;

        } catch (error) {
            console.error('❌ Error verificando estado auth:', error);
            return state.isAuthenticated; // Mantener estado actual si hay error
        }
    }
};

const getters = {
    isAuthenticated: state => state.isAuthenticated,
    user: state => state.user,
    token: state => state.token,
    isLoading: state => state.isLoading,

    // Getter para verificar si el token está próximo a expirar
    isTokenExpiringSoon: state => {
        if (!state.tokenExpiresAt) return false;

        const now = new Date();
        const expiry = new Date(state.tokenExpiresAt);
        const timeUntilExpiry = expiry.getTime() - now.getTime();

        // Considerar "próximo a expirar" si quedan menos de 10 minutos
        return timeUntilExpiry < (10 * 60 * 1000);
    },

    // Tiempo restante del token en minutos
    tokenTimeRemaining: state => {
        if (!state.tokenExpiresAt) return null;

        const now = new Date();
        const expiry = new Date(state.tokenExpiresAt);
        const timeUntilExpiry = expiry.getTime() - now.getTime();

        return Math.max(0, Math.floor(timeUntilExpiry / (60 * 1000)));
    }
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};