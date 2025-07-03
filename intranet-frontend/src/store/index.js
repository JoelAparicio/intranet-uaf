import { createStore } from 'vuex'
import axios from 'axios'
import apiConfig from '@/config/api'

export default createStore({
    state: {
        authToken: localStorage.getItem('auth_token') || '',
        authStatus: '',
        registerStatus: ''
    },
    mutations: {
        auth_request(state) {
            state.authStatus = 'loading'
        },
        auth_success(state, token) {
            state.authStatus = 'success'
            state.authToken = token
        },
        auth_error(state) {
            state.authStatus = 'error'
        },
        logout(state) {
            state.authStatus = ''
            state.authToken = ''
        },
        register_request(state) {
            state.registerStatus = 'loading'
        },
        register_success(state) {
            state.registerStatus = 'success'
        },
        register_error(state) {
            state.registerStatus = 'error'
        }
    },
    actions: {
        async login({ commit }, user) {
            commit('auth_request')

            try {
                console.log('🔐 Vuex: Iniciando login para:', user.correo_electronico);

                // ✅ LIMPIAR: Cualquier token anterior
                localStorage.removeItem('auth_token');
                delete axios.defaults.headers.common['Authorization'];
                commit('logout');

                // ✅ ESPERAR: Un momento para asegurar limpieza
                await new Promise(resolve => setTimeout(resolve, 50));

                const response = await axios.post(apiConfig.endpoints.login, user)

                console.log('✅ Vuex: Login response recibida:', {
                    status: response.status,
                    hasToken: !!response.data.token,
                    tokenLength: response.data.token?.length
                });

                const token = response.data.token
                if (!token) {
                    throw new Error('No se recibió token del servidor');
                }

                // ✅ GUARDAR TOKEN: De manera síncrona y verificada
                try {
                    localStorage.setItem('auth_token', token);

                    // ✅ VERIFICAR: Que se guardó correctamente
                    const savedToken = localStorage.getItem('auth_token');
                    if (savedToken !== token) {
                        throw new Error('Error al guardar token en localStorage');
                    }

                    // ✅ CONFIGURAR: Headers de axios
                    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

                    console.log('✅ Vuex: Token guardado y configurado exitosamente');

                } catch (storageError) {
                    console.error('❌ Vuex: Error guardando token:', storageError);
                    throw new Error('Error al guardar token: ' + storageError.message);
                }

                commit('auth_success', token)

                return response

            } catch (error) {
                console.error('❌ Vuex: Error en login:', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });

                commit('auth_error')
                localStorage.removeItem('auth_token')
                delete axios.defaults.headers.common['Authorization']

                throw error
            }
        },

        async register({ commit }, user) {
            commit('register_request')
            try {
                const response = await axios.post(apiConfig.endpoints.register, user)
                commit('register_success')
                return response
            } catch (error) {
                commit('register_error')
                throw error
            }
        },

        logout({ commit }) {
            return new Promise((resolve) => {
                console.log('🚪 Vuex: Cerrando sesión...');

                commit('logout')
                localStorage.removeItem('auth_token')
                delete axios.defaults.headers.common['Authorization']

                // ✅ VERIFICAR: Limpieza completa
                setTimeout(() => {
                    const remainingToken = localStorage.getItem('auth_token');
                    if (remainingToken) {
                        console.warn('⚠️ Token no se limpió completamente, forzando limpieza');
                        localStorage.clear();
                    }
                    resolve()
                }, 100);
            })
        }
    },
    getters: {
        isAuthenticated: state => {
            // ✅ VERIFICAR: Tanto state como localStorage
            const stateToken = !!state.authToken;
            const localToken = !!localStorage.getItem('auth_token');
            return stateToken && localToken;
        },
        authStatus: state => state.authStatus,
        registerStatus: state => state.registerStatus
    }
})