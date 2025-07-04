// src/main.js
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';

// ===== IMPORTAR CONFIGURACIÓN DE AXIOS =====
import './utils/axiosSetup';

// ===== OTRAS DEPENDENCIAS =====
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import '@fortawesome/fontawesome-free/css/all.min.css';

// ===== SWEETALERT2 =====
import Swal from 'sweetalert2';
window.Swal = Swal; // Hacer disponible globalmente

// ===== MOMENT.JS =====
import moment from 'moment';
import 'moment/locale/es'; // Configurar idioma español

// ===== FONTAWESOME ICONOS =====
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faCheck,
    faEye,
    faXmark,
    faUserSecret,
    faUserPlus,
    faUsers,
    faUserCheck,
    faUserShield,
    faUserTie,
    faUser,
    faUserClock,
    faBan,
    faUserPen,
    faToggleOn,
    faToggleOff,
    faPersonCircleCheck,
    faRightFromBracket,
    faSpinner,
    faKey,
    faChevronRight,
    faChevronLeft,
    faUserMinus,
    faShieldAlt,
    faUserGraduate,
    faPlus,
    faTrash,
    faSearch,
    faUserSlash,
    faTimes,
    faExclamationTriangle,
    faInfoCircle,
    faSave,
    faEllipsisV,
    faThLarge,
    faTable,
    faHistory,
    faUserTag,
    faUserEdit,
    faEnvelope,
    faBriefcase,
    faSitemap,
    faIdCard,
    faPhone,
    faBuilding,
    faClock,
    faUsersSlash,
    faBirthdayCake
} from '@fortawesome/free-solid-svg-icons';

import {
    faFaceSmileWink,
    faBell
} from '@fortawesome/free-regular-svg-icons';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// Agregar los iconos a la librería
library.add(
    faCheck,
    faXmark,
    faEye,
    faFaceSmileWink,
    faUserSecret,
    faBell,
    faUserPlus,
    faUsers,
    faUserCheck,
    faUserShield,
    faUserTie,
    faUser,
    faUserClock,
    faBan,
    faUserPen,
    faToggleOn,
    faToggleOff,
    faPersonCircleCheck,
    faRightFromBracket,
    faSpinner,
    faKey,
    faChevronRight,
    faChevronLeft,
    faUserMinus,
    faShieldAlt,
    faUserGraduate,
    faPlus,
    faTrash,
    faSearch,
    faUserSlash,
    faTimes,
    faExclamationTriangle,
    faInfoCircle,
    faSave,
    faEllipsisV,
    faThLarge,
    faTable,
    faHistory,
    faUserTag,
    faUserEdit,
    faEnvelope,
    faBriefcase,
    faSitemap,
    faIdCard,
    faPhone,
    faBuilding,
    faClock,
    faUsersSlash,
    faBirthdayCake
);

// Crear aplicación
const app = createApp(App);

// ===== REGISTRO DE COMPONENTES GLOBALES =====
app.component('font-awesome-icon', FontAwesomeIcon);

// ===== CONFIGURACIÓN GLOBAL =====
app.config.globalProperties.$apiUrl = process.env.VUE_APP_API_URL || 'http://172.19.115.44/api';
app.config.globalProperties.$swal = Swal; // Configurar SweetAlert2 globalmente
app.config.globalProperties.$moment = moment; // Configurar Moment.js globalmente

// ===== FUNCIÓN DE INICIALIZACIÓN ASYNC =====
async function initializeApp() {
    try {
        console.log('🚀 Inicializando aplicación...');

        // Inicializar autenticación
        await store.dispatch('auth/initAuth');

        console.log('✅ Aplicación inicializada correctamente');

    } catch (error) {
        console.error('❌ Error inicializando aplicación:', error);

        // En caso de error, limpiar autenticación
        await store.dispatch('auth/logout');
    }
}

/ ===== NAVIGATION GUARD GLOBAL =====
router.beforeEach(async (to, from, next) => {
    const isAuthenticated = store.getters['auth/isAuthenticated'];
    const isLoading = store.getters['auth/isLoading'];

    // ✅ CORREGIDO: Rutas que NO requieren autenticación (nombres estandarizados)
    const publicRoutes = ['Login', 'Register', 'NoAcceso'];
    const requiresAuth = !publicRoutes.includes(to.name);

    console.log('🧭 Navigation guard:', {
        to: to.name,
        requiresAuth,
        isAuthenticated,
        isLoading
    });

    // Si la ruta requiere autenticación
    if (requiresAuth) {
        // Si no está autenticado, redirigir a login
        if (!isAuthenticated && !isLoading) {
            console.log('🔒 Redirigiendo a login - no autenticado');
            return next({ name: 'Login' });
        }

        // Si está cargando, esperar un poco
        if (isLoading) {
            console.log('⏳ Esperando verificación de auth...');

            // Esperar máximo 3 segundos
            let attempts = 0;
            const checkAuth = setInterval(() => {
                attempts++;

                if (!store.getters['auth/isLoading']) {
                    clearInterval(checkAuth);

                    if (store.getters['auth/isAuthenticated']) {
                        console.log('✅ Auth verificado - continuando');
                        next();
                    } else {
                        console.log('🔒 Auth falló - redirigiendo a login');
                        next({ name: 'Login' });
                    }
                } else if (attempts > 30) { // 3 segundos
                    clearInterval(checkAuth);
                    console.log('⏱️ Timeout en verificación auth');
                    next({ name: 'Login' });
                }
            }, 100);

            return;
        }

        // Verificar estado de autenticación periódicamente
        const authValid = await store.dispatch('auth/checkAuthStatus');
        if (!authValid) {
            console.log('🔒 Token inválido - redirigiendo a login');
            return next({ name: 'Login' });
        }
    }

    // ✅ CORREGIDO: Si está autenticado y trata de ir a login/register, redirigir a dashboard
    if (isAuthenticated && publicRoutes.includes(to.name)) {
        console.log('↩️ Usuario autenticado - redirigiendo a dashboard');
        return next({ name: 'MiEspacio' });
    }

    next();
});

// ===== MONTAR APLICACIÓN =====
async function mountApp() {
    // Inicializar aplicación
    await initializeApp();

    // Montar Vue app con plugins
    app.use(store)
        .use(router)
        .mount('#app');

    console.log('🎉 Aplicación montada exitosamente');
}

// ===== VERIFICACIÓN PERIÓDICA DE TOKEN =====
setInterval(async () => {
    const isAuthenticated = store.getters['auth/isAuthenticated'];
    const isTokenExpiringSoon = store.getters['auth/isTokenExpiringSoon'];

    if (isAuthenticated && isTokenExpiringSoon) {
        console.log('⚠️ Token próximo a expirar - refrescando...');
        await store.dispatch('auth/refreshToken');
    }
}, 2 * 60 * 1000); // Verificar cada 2 minutos

// ===== INICIAR APLICACIÓN =====
mountApp().catch(error => {
    console.error('💥 Error crítico al montar aplicación:', error);
});