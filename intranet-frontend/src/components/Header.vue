<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-lg">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold text-white">
        <span class="brand-text">UAF INTRANET</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#" @click="paginaMiEspacio">
              <font-awesome-icon :icon="['fas', 'user']" class="me-1" /> Mi espacio
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" @click="paginaPortalColaboradores">
              <font-awesome-icon :icon="['fas', 'users']" class="me-1" /> Portal de colaboradores
            </a>
          </li>
          <li class="nav-item" v-if="canSeePortalAprobaciones">
            <a class="nav-link position-relative" href="#" @click="paginaPortalAprobaciones">
              <font-awesome-icon :icon="['fas', 'person-circle-check']" class="me-1" /> Portal de aprobaciones
              <span v-if="pendingCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ pendingCount }}
                <span class="visually-hidden">Pendientes</span>
              </span>
            </a>
          </li>
        </ul>
        <div class="d-flex">
          <button v-if="isAdmin" class="btn btn-light me-2" type="button" @click="paginaAdministradores">
            <font-awesome-icon :icon="['fas', 'user-shield']" class="me-1" /> Administradores
          </button>
          <button class="btn btn-outline-light" type="button" @click="cerrarSesion">
            <font-awesome-icon :icon="['fas', 'right-from-bracket']" class="me-1"/> Desconectarse
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import { apiCall } from '@/utils/apiHelper';
import Swal from 'sweetalert2';
import { ref, onMounted, watch, computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useStore } from 'vuex';

export default {
  components: {
    FontAwesomeIcon
  },
  setup() {
    const store = useStore();
    const pendingCount = ref(0);
    const userRoles = ref([]);
    const isAdmin = ref(false);
    const canSeePortalAprobaciones = ref(false);

    const token = computed(() => store.getters['auth/token']);
    const isAuthenticated = computed(() => store.getters['auth/isAuthenticated']);

    const fetchUserRoles = async () => {
      try {
        if (!token.value) {
          console.error('No se encontró el token en el store');
          return;
        }

        const response = await apiCall.get('rolesUsuario', {
          headers: {
            'Authorization': `Bearer ${token.value}`
          }
        });

        userRoles.value = response.data.roles || [];
        fetchAprobacionesCount();
      } catch (error) {
        console.error('Error fetching user roles:', error);
      }
    };

    const fetchAprobacionesCount = async () => {
      try {
        const response = await apiCall.get('listarAprobaciones');
        if (response.data.success) {
          pendingCount.value = response.data.data.length;
        }
      } catch (error) {
        console.error('Error fetching aprobaciones count:', error);
      }
    };

    onMounted(() => {
      if (isAuthenticated.value) {
        fetchUserRoles();
      }
    });

    watch(userRoles, (newRoles) => {
      isAdmin.value = newRoles.includes('Administrador');
      canSeePortalAprobaciones.value = newRoles.some(role => [
        'Jefe de Tecnología',
        'Jefe de Relaciones Públicas',
        'Jefe de Administración',
        'Jefe de Análisis Estratégico',
        'Jefe de Análisis Operativo',
        'Jefe de Asesoría Legal',
        'Jefe de Contact Center',
        'Jefe de Cooperación Nacional e Internacional',
        'Jefe de Recursos Humanos',
        'Recursos Humanos',
        'Director',
        'Subdirector',
      ].includes(role));
    });

    return {
      pendingCount,
      isAdmin,
      canSeePortalAprobaciones,
      fetchAprobacionesCount,
      store
    };
  },
  methods: {
    paginaPrincipal() {
      this.$router.push({ name: 'HomeInicio' });
    },

    paginaMiEspacio() {
      this.$router.push({ name: 'MiEspacio' });
    },

    paginaPortalColaboradores() {
      this.$router.push({ name: 'PortalColaboradores' });
    },

    paginaPortalAprobaciones() {
      this.$router.push({ name: 'PortalAprobaciones' });
    },

    paginaAdministradores() {
      this.$router.push({ name: 'Administradores' });
    },

    enConstruccion() {
      Swal.fire({
        icon: 'warning',
        title: 'En construcción',
        text: 'Esta sección está en construcción. Por favor, vuelve más tarde.',
      });
    },

    async cerrarSesion() {
      try {
        await this.store.dispatch('auth/logout');
        this.$router.push({ name: 'Login' });

        Swal.fire({
          icon: 'success',
          title: 'Sesión cerrada',
          text: 'Has cerrado sesión correctamente',
          timer: 1500,
          timerProgressBar: true,
          showConfirmButton: false
        });

      } catch (error) {
        console.error('Error al cerrar sesión:', error);
        await this.store.dispatch('auth/logout');
        this.$router.push({ name: 'Login' });
      }
    }
  }
}
</script>

<style scoped>
.navbar {
  background-color: #1050a9 !important;
  transition: all 0.3s ease;
}

.navbar-brand .brand-text {
  font-size: 1.5rem;
  letter-spacing: 1px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.nav-link {
  color: rgba(255,255,255,0.8) !important;
  transition: color 0.3s ease, transform 0.3s ease;
}

.nav-link:hover, .nav-link:focus {
  color: #ffffff !important;
  transform: translateY(-2px);
}

.btn {
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.badge {
  transition: all 0.3s ease;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.badge.bg-danger {
  animation: pulse 2s infinite;
}
</style>