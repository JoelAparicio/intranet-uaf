// src/store/modules/auth.js
import axios from 'axios';
import { checkTokenValidity, setupTokenTimer } from '@/utils/axiosSetup';

const state = {
    token: localStorage.getItem('token') || null,
    tokenExpiresAt: localStorage.getItem('token_expires_at') || null,
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    isAuthenticated: false,
    isLoading: false,
    lastTokenCheck: null,
    refreshTokenTimer: null,
    autoRefreshEnabled: true
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
    },

    SET_REFRESH_TOKEN_TIMER(state, timer) {
        state.refreshTokenTimer = timer;
    },

    SET_AUTO_REFRESH_ENABLED(state, enabled) {
        state.autoRefreshEnabled = enabled;
    }
};

const actions = {
    // ===== LOGIN MEJORADO =====
    async login({ commit, dispatch, state }, credentials) {
        try {
            commit('SET_LOADING', true);

            const response = await axios.post('/login', credentials);

            const { token, expires_at, user } = response.data;

            if (!token || !expires_at || !user) {
                throw new Error('Respuesta de login incompleta del servidor');
            }

            // Guardar datos de autenticación
            commit('SET_TOKEN', token);
            commit('SET_TOKEN_EXPIRY', expires_at);
            commit('SET_USER', user);
            commit('SET_AUTHENTICATED', true);

            // Configurar refresh automático
            await dispatch('setupAutoRefresh');

            console.log('✅ Login exitoso:', {
                user: user.nombre,
                expiresAt: expires_at,
                autoRefresh: state.autoRefreshEnabled
            });

            return { success: true, user };

        } catch (error) {
            console.error('❌ Error en login:', error);

            const errorMessage = error.response?.data?.message || 'Error en el login';
            return { success: false, error: errorMessage };

        } finally {
            commit('SET_LOADING', false);
        }
    },

    // ===== LOGOUT MEJORADO =====
    async logout({ commit, dispatch }) {
        try {
            // Limpiar timers antes de logout
            await dispatch('clearAutoRefresh');

            // Intentar logout en servidor (si hay token)
            if (state.token) {
                try {
                    await axios.post('/logout');
                } catch (logoutError) {
                    console.warn('⚠️ Error en logout del servidor:', logoutError);
                    // Continuar con logout local aunque falle el servidor
                }
            }
        } catch (error) {
            console.warn('⚠️ Error durante logout:', error);
        } finally {
            // Limpiar estado local siempre
            commit('SET_TOKEN', null);
            commit('SET_TOKEN_EXPIRY', null);
            commit('SET_USER', null);
            commit('SET_AUTHENTICATED', false);
            commit('SET_LAST_TOKEN_CHECK', null);

            console.log('👋 Logout completado');
        }
    },

    // ===== CONFIGURAR AUTO-REFRESH =====
    async setupAutoRefresh({ commit, dispatch, state }) {
        if (!state.autoRefreshEnabled || !state.tokenExpiresAt) {
            console.log('🔄 Auto-refresh deshabilitado o sin fecha de expiración');
            return;
        }

        // Limpiar timer anterior
        await dispatch('clearAutoRefresh');

        const now = new Date().getTime();
        const expiry = new Date(state.tokenExpiresAt).getTime();
        const timeUntilExpiry = expiry - now;

        // Programar refresh 5 minutos antes de que expire
        const refreshTime = Math.max(0, timeUntilExpiry - (5 * 60 * 1000));

        if (refreshTime > 0 && refreshTime < 24 * 60 * 60 * 1000) { // No más de 24 horas
            const timer = setTimeout(async () => {
                console.log('⏰ Ejecutando auto-refresh programado...');
                const success = await dispatch('refreshToken');

                if (success) {
                    // Programar el siguiente refresh
                    await dispatch('setupAutoRefresh');
                } else {
                    console.error('❌ Auto-refresh falló, forzando logout');
                    await dispatch('logout');
                }
            }, refreshTime);

            commit('SET_REFRESH_TOKEN_TIMER', timer);

            console.log(`⏰ Auto-refresh programado en ${Math.floor(refreshTime / 60000)} minutos`);
        } else {
            console.warn('⚠️ Tiempo de refresh inválido o token próximo a expirar');
        }

        // Configurar verificación periódica adicional
        dispatch('startPeriodicCheck');
    },

    // ===== LIMPIAR AUTO-REFRESH =====
    async clearAutoRefresh({ commit, state }) {
        if (state.refreshTokenTimer) {
            clearTimeout(state.refreshTokenTimer);
            commit('SET_REFRESH_TOKEN_TIMER', null);
            console.log('🧹 Timer de auto-refresh limpiado');
        }
    },

    // ===== VERIFICACIÓN PERIÓDICA =====
    startPeriodicCheck({ dispatch, state }) {
        // Verificar cada 2 minutos si el token necesita refresh
        const periodicCheck = setInterval(async () => {
            if (!state.isAuthenticated || !state.autoRefreshEnabled) {
                clearInterval(periodicCheck);
                return;
            }

            const needsRefresh = await dispatch('shouldRefreshToken');
            if (needsRefresh) {
                console.log('🔄 Verificación periódica: iniciando refresh...');
                await dispatch('refreshToken');
            }
        }, 2 * 60 * 1000); // 2 minutos

        // Guardar referencia para poder limpiarlo
        if (!window.authPeriodicChecks) {
            window.authPeriodicChecks = [];
        }
        window.authPeriodicChecks.push(periodicCheck);
    },

    // ===== VERIFICAR SI NECESITA REFRESH =====
    shouldRefreshToken({ state }) {
        if (!state.tokenExpiresAt) return false;

        const now = new Date().getTime();
        const expiry = new Date(state.tokenExpiresAt).getTime();
        const timeUntilExpiry = expiry - now;

        // Refrescar si quedan menos de 10 minutos
        return timeUntilExpiry < (10 * 60 * 1000) && timeUntilExpiry > 0;
    },

    // ===== REFRESCAR TOKEN MANUALMENTE =====
    async refreshToken({ commit, dispatch, state }) {
        if (!state.token) {
            console.warn('⚠️ No hay token para refrescar');
            return false;
        }

        try {
            console.log('🔄 Iniciando refresh de token...');

            const response = await axios.post('/refresh-token', {}, {
                headers: {
                    'Authorization': `Bearer ${state.token}`
                }
            });

            const { token, expires_at } = response.data;

            if (!token || !expires_at) {
                throw new Error('Respuesta de refresh incompleta');
            }

            commit('SET_TOKEN', token);
            commit('SET_TOKEN_EXPIRY', expires_at);

            // Reconfigurar auto-refresh con nuevo token
            await dispatch('setupAutoRefresh');

            console.log('✅ Token refrescado exitosamente:', {
                newExpiry: expires_at,
                timeRemaining: Math.floor((new Date(expires_at) - new Date()) / 60000) + ' min'
            });

            return true;

        } catch (error) {
            console.error('❌ Error refrescando token:', error);

            // Si el refresh falla, hacer logout
            if (error.response?.status === 401 || error.response?.status === 403) {
                console.error('🔒 Token de refresh inválido, forzando logout');
                await dispatch('logout');
            }

            return false;
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

                // Verificar si necesita refresh inmediato
                const needsRefresh = await dispatch('shouldRefreshToken');
                if (needsRefresh) {
                    console.log('🔄 Token próximo a expirar, refrescando inmediatamente...');
                    await dispatch('refreshToken');
                } else {
                    // Configurar auto-refresh normal
                    await dispatch('setupAutoRefresh');
                }

                console.log('✅ Autenticación restaurada:', user?.nombre);
                return true;
            } else {
                // Token inválido, limpiar
                console.log('❌ Token inválido al inicializar');
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

    // ===== VERIFICAR ESTADO PERIÓDICAMENTE =====
    async checkAuthStatus({ commit, dispatch, state }) {
        const now = Date.now();
        const lastCheck = state.lastTokenCheck;

        // Solo verificar cada 5 minutos para no sobrecargar
        if (lastCheck && (now - lastCheck) < 5 * 60 * 1000) {
            return state.isAuthenticated;
        }

        commit('SET_LAST_TOKEN_CHECK', now);

        if (!state.token) {
            commit('SET_AUTHENTICATED', false);
            return false;
        }

        try {
            // Verificar si necesita refresh automático
            const needsRefresh = await dispatch('shouldRefreshToken');
            if (needsRefresh) {
                const refreshed = await dispatch('refreshToken');
                if (!refreshed) {
                    await dispatch('logout');
                    return false;
                }
            }

            // Verificar validez con el servidor
            const isValid = await checkTokenValidity();
            if (!isValid) {
                console.log('❌ Token inválido en verificación periódica');
                await dispatch('logout');
                return false;
            }

            return true;

        } catch (error) {
            console.error('❌ Error verificando estado auth:', error);
            return state.isAuthenticated; // Mantener estado actual si hay error de red
        }
    },

    // ===== HABILITAR/DESHABILITAR AUTO-REFRESH =====
    setAutoRefresh({ commit, dispatch }, enabled) {
        commit('SET_AUTO_REFRESH_ENABLED', enabled);

        if (enabled && state.isAuthenticated) {
            dispatch('setupAutoRefresh');
        } else {
            dispatch('clearAutoRefresh');
        }

        console.log(`🔄 Auto-refresh ${enabled ? 'habilitado' : 'deshabilitado'}`);
    },

    // ===== FORZAR VERIFICACIÓN INMEDIATA =====
    async forceTokenCheck({ dispatch }) {
        console.log('🔍 Forzando verificación inmediata de token...');
        return await dispatch('checkAuthStatus');
    }
};

const getters = {
    isAuthenticated: state => state.isAuthenticated,
    user: state => state.user,
    token: state => state.token,
    isLoading: state => state.isLoading,
    autoRefreshEnabled: state => state.autoRefreshEnabled,

    // Getter para verificar si el token está próximo a expirar
    isTokenExpiringSoon: state => {
        if (!state.tokenExpiresAt) return false;

        const now = new Date();
        const expiry = new Date(state.tokenExpiresAt);
        const timeUntilExpiry = expiry.getTime() - now.getTime();

        // Considerar "próximo a expirar" si quedan menos de 10 minutos
        return timeUntilExpiry < (10 * 60 * 1000) && timeUntilExpiry > 0;
    },

    // Tiempo restante del token en minutos
    tokenTimeRemaining: state => {
        if (!state.tokenExpiresAt) return null;

        const now = new Date();
        const expiry = new Date(state.tokenExpiresAt);
        const timeUntilExpiry = expiry.getTime() - now.getTime();

        return Math.max(0, Math.floor(timeUntilExpiry / (60 * 1000)));
    },

    // Estado del token (válido, expirando, expirado)
    tokenStatus: (state, getters) => {
        if (!state.token) return 'no-token';
        if (!state.tokenExpiresAt) return 'no-expiry';

        const remaining = getters.tokenTimeRemaining;

        if (remaining <= 0) return 'expired';
        if (remaining <= 10) return 'expiring';
        if (remaining <= 60) return 'warning';
        return 'valid';
    },

    // Información completa del estado de autenticación
    authInfo: (state, getters) => ({
        isAuthenticated: getters.isAuthenticated,
        user: state.user,
        tokenStatus: getters.tokenStatus,
        timeRemaining: getters.tokenTimeRemaining,
        autoRefreshEnabled: state.autoRefreshEnabled,
        isLoading: state.isLoading,
        lastCheck: state.lastTokenCheck
    })
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};