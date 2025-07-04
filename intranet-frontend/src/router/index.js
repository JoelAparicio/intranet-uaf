import { createRouter, createWebHistory } from 'vue-router'
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
    name: 'Login',
    component: Login
  },
  {
    path: '/login',
    redirect: { name: 'Login' }
  },
  {
    path: '/home',
    name: 'Home',
    component: Home,
    meta: { requiresAuth: true },
    children: [
      {
        path: '/home/inicio',
        name: 'HomeInicio',
        component: Inicio,
        meta: { requiresAuth: true }
      },
      {
        path: '/home/miespacio',
        name: 'MiEspacio',
        component: MiEspacio,
        meta: { requiresAuth: true }
      },
      {
        path: '/home/portalcolaboradores',
        name: 'PortalColaboradores',
        component: PortalColaboradores,
        meta: { requiresAuth: true }
      },
      {
        path: '/home/portalaprobaciones',
        name: 'PortalAprobaciones',
        component: PortalAprobaciones,
        meta: { requiresAuth: true }
      },
      {
        path: '/home/administradores',
        name: 'Administradores',
        component: Administradores,
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/no-acceso',
    name: 'NoAcceso',
    component: NoAcceso
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router