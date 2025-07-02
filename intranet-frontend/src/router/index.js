import { createRouter, createWebHistory } from 'vue-router'
import store from '../store/index';
import Login from '../views/Login.vue';
import Home from '../views/Home.vue';
import Register from '../views/Register.vue';
import NoAcceso from '../views/NoAcceso.vue';
import MiEspacio from '../views/MiEspacio.vue';
import Inicio from '../views/Inicio.vue';
import PortalColaboradores from '../views/PortalColaboradores.vue';
import PortalAprobaciones from '../views/PortalAprobaciones.vue';
import Administradores from '../views/Administradores.vue';

const routes = [
  {
    path: '/',
    name: 'inicio',
    component: Login
  },
  {
    path: '/login',
    name: 'login',
    component: Login
  },
  {
    path: '/home',
    name: 'home',
    component: Home,
    meta: { requiresAuth: true }, // Protección para la ruta principal 'home'
    children: [
      {
        path: '/home/inicio', // Esta es la subruta de 'home'
        name: 'homeinicio',
        component: Inicio,
      },
      {
        path: '/home/miespacio', // Esta es la subruta de 'home'
        name: 'miespacio',
        component: MiEspacio,
      },
      {
        path: '/home/portalcolaboradores',
        name: 'portalcolaboradores',
        component: PortalColaboradores,
      },
      {
        path: '/home/portalaprobaciones',
        name: 'portalaprobaciones',
        component: PortalAprobaciones,
      },
      {
        path: '/home/administradores',
        name: 'administradores',
        component: Administradores,
      },
    ]
  },
  {
    path: '/register',
    name: 'register',
    component: Register
  },
  {
    path: '/no-acceso', // Corregido el nombre de la ruta
    name: 'no-acceso',
    component: NoAcceso
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// Guard de navegación global
router.beforeEach((to, from, next) => {
  // Si la ruta requiere autenticación y el usuario no está autenticado
  if (to.matched.some(record => record.meta.requiresAuth) && !store.getters.isAuthenticated) {
    // Redirigir a la página de inicio de sesión
    next({ name: 'no-acceso' });
  } else {
    // De lo contrario, continuar navegando
    next();
  }
});

export default router
