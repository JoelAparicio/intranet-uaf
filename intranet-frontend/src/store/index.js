// src/store/index.js
import { createStore } from 'vuex';
import auth from './modules/auth';

const store = createStore({
    modules: {
        auth
    },

    // Estado global (si necesitas)
    state: {
        loading: false,
        notifications: []
    },

    // Mutaciones globales
    mutations: {
        SET_LOADING(state, status) {
            state.loading = status;
        },

        ADD_NOTIFICATION(state, notification) {
            state.notifications.push({
                id: Date.now(),
                ...notification
            });
        },

        REMOVE_NOTIFICATION(state, id) {
            state.notifications = state.notifications.filter(n => n.id !== id);
        }
    },

    // Acciones globales
    actions: {
        showNotification({ commit }, notification) {
            commit('ADD_NOTIFICATION', notification);

            // Auto-remove después de 5 segundos
            setTimeout(() => {
                commit('REMOVE_NOTIFICATION', notification.id);
            }, 5000);
        }
    },

    // Getters globales
    getters: {
        isLoading: state => state.loading,
        notifications: state => state.notifications
    }
});

export default store;