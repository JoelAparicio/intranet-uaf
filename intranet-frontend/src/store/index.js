import { createStore } from 'vuex'
import axios from 'axios'
import apiConfig from '@/config/api'

export default createStore({
    state: {
        authToken: localStorage.getItem('auth_token') || '', //Token de autenticación
        authStatus: '', //Estado de autenticación
        registerStatus: ''  //Estado de registro
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
                const response = await axios.post(apiConfig.endpoints.login, user)
                const token = response.data.token
                localStorage.setItem('auth_token', token)
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
                commit('auth_success', token)
                return response
            } catch (error) {
                commit('auth_error')
                localStorage.removeItem('auth_token')
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
                commit('logout')
                localStorage.removeItem('auth_token')
                delete axios.defaults.headers.common['Authorization']
                resolve()
            })
        }
    },
    getters: {
        isAuthenticated: state => !!state.authToken,
        authStatus: state => state.authStatus,
        registerStatus: state => state.registerStatus
    }
})