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
    name: 'Login', // CAMBIADO: Usar nombre consistente
    component: Login
  },
  {
    path: '/login',
    name: 'login', // Ruta alternativa
    component: Login
  },
  {
    path: '/home',
    name: 'home',
    component: Home,
    meta: { requiresAuth: true },
    children: [
      {
        path: '/home/inicio',
        name: 'homeinicio',
        component: Inicio,
        meta: { requiresAuth: true }
      },
      {
        path: '/home/miespacio',
        name: 'MiEspacio', // CAMBIADO: Usar nombre consistente con main.js
        component: MiEspacio,
        meta: { requiresAuth: true }
      },
      {
        path: '/home/portalcolaboradores',
        name: 'portalcolaboradores',
        component: PortalColaboradores,
        meta: { requiresAuth: true }
      },
      {
        path: '/home/portalaprobaciones',
        name: 'portalaprobaciones',
        component: PortalAprobaciones,
        meta: { requiresAuth: true }
      },
      {
        path: '/home/administradores',
        name: 'administradores',
        component: Administradores,
        meta: { requiresAuth: true }
      },
    ]
  },
  {
    path: '/register',
    name: 'Register', // CAMBIADO: Usar nombre consistente
    component: Register
  },
  {
    path: '/no-acceso',
    name: 'no-acceso',
    component: NoAcceso
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// ELIMINAR: Este guard ya está en main.js, no duplicar
// Solo usar el guard del main.js que es más completo

export default router