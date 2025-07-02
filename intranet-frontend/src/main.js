import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import '@fortawesome/fontawesome-free/css/all.min.css'
import Swal from 'sweetalert2'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import moment from 'moment'

import axios from 'axios'
import apiConfig from './config/api'

// Configurar axios base URL
axios.defaults.baseURL = process.env.VUE_APP_API_URL || 'http://localhost:8000/api'

// Configurar el interceptor de solicitudes
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

// Importar FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core'
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
} from '@fortawesome/free-solid-svg-icons'
import { faFaceSmileWink, faBell } from '@fortawesome/free-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

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
)

const app = createApp(App);

// Registrar el componente globalmente
app.component('font-awesome-icon', FontAwesomeIcon);

app.use(store);
app.use(router);

// Agregar axios al contexto global
app.config.globalProperties.$axios = axios;

// Agregar SweetAlert2 al contexto global
app.config.globalProperties.$swal = Swal;

// Agregar moment al contexto global
app.config.globalProperties.$moment = moment;

app.mount('#app');