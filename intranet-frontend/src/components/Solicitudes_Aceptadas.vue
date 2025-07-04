<template>
  <div class="lista-aprobaciones">
    <div class="card">
      <div class="card-header">
        <h4 class="text-center">Solicitudes Aceptadas</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="loading-spinner">
          <font-awesome-icon :icon="['fas', 'spinner']" spin size="3x" />
          <p>Cargando solicitudes...</p>
        </div>
        <div v-else-if="solicitudesAceptadas.length === 0" class="no-solicitudes">
          <font-awesome-icon :icon="['far', 'face-smile-wink']" size="3x" />
          <p>No has aceptado solicitudes</p>
        </div>
        <div v-else class="table-container">
          <table class="table">
            <thead>
            <tr>
              <th>ID Solicitud</th>
              <th>Colaborador</th>
              <th>Tipo de Solicitud</th>
              <th>Fecha de Creación</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="solicitud in solicitudesAceptadas" :key="solicitud.id_solicitud">
              <td>{{ solicitud.id_solicitud }}</td>
              <td>{{ solicitud.colaborador }}</td>
              <td>
                  <span class="badge" :class="getBadgeClass(solicitud.tipo_solicitud)">
                    {{ solicitud.tipo_solicitud }}
                  </span>
              </td>
              <td>{{ formatFecha(solicitud.fecha_creacion) }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { apiCall } from '@/utils/apiHelper';
import moment from 'moment';
import 'moment/locale/es';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { mapGetters } from 'vuex';

export default {
  name: 'SolicitudesAceptadas',
  components: {
    FontAwesomeIcon
  },
  computed: {
    ...mapGetters('auth', ['token', 'isAuthenticated', 'user'])
  },
  data() {
    return {
      solicitudesAceptadas: [],
      loading: true
    };
  },
  methods: {
    getAuthHeaders() {
      if (!this.token) {
        throw new Error('No token available');
      }
      return {
        'Authorization': `Bearer ${this.token}`
      };
    },

    async handleAuthError(error) {
      if (error.response?.status === 401) {
        await this.$store.dispatch('auth/logout');
        this.$router.push('/login');
        return true;
      }
      return false;
    },

    async fetchSolicitudesAceptadas() {
      try {
        this.loading = true;

        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        const response = await apiCall.get('solicitudesAceptadas', {
          headers: this.getAuthHeaders()
        });

        if (response.data.success) {
          this.solicitudesAceptadas = response.data.data;
        } else {
          console.warn('La respuesta no fue exitosa:', response.data);
          this.solicitudesAceptadas = [];
        }
      } catch (error) {
        console.error('Error fetching solicitudes aceptadas:', error);

        if (await this.handleAuthError(error)) return;

        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar las solicitudes aceptadas',
          timer: 2000,
          showConfirmButton: false
        });

        this.solicitudesAceptadas = [];
      } finally {
        this.loading = false;
      }
    },

    formatFecha(fecha) {
      if (!fecha) return '';

      try {
        return moment(fecha).format('D [de] MMMM [de] YYYY, h:mm a');
      } catch (error) {
        console.error('Error formatting date:', error);
        return fecha;
      }
    },

    getBadgeClass(tipoSolicitud) {
      const classes = {
        'Vacaciones': 'badge-success',
        'Permiso': 'badge-warning',
        'Licencia': 'badge-info',
        'Reincorporación': 'badge-primary',
        'Tiempo Compensatorio': 'badge-secondary',
        'Horas Extraordinarias': 'badge-dark',
        'Otros': 'badge-secondary'
      };
      return classes[tipoSolicitud] || 'badge-primary';
    }
  },

  async created() {
    if (!this.isAuthenticated) {
      this.$router.push('/login');
      return;
    }

    await this.fetchSolicitudesAceptadas();
  }
};
</script>

<style scoped>
.lista-aprobaciones {
  margin-top: 0.5rem;
}

.card {
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  border: 1px solid #e9ecef;
}

.card-header {
  background-color: #1050a9;
  color: white;
  padding: 1rem;
}

.card-body {
  padding: 1rem;
}

h4 {
  margin-bottom: 0;
  font-weight: 600;
}

.loading-spinner, .no-solicitudes {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #6c757d;
}

.loading-spinner p, .no-solicitudes p {
  margin-top: 1rem;
  font-size: 1.1rem;
  text-align: center;
}

.table-container {
  max-height: 485px; /* Altura para 5 filas aproximadamente */
  overflow-y: auto;
  border: 1px solid #e9ecef;
  border-radius: 4px;
}

.table {
  margin-bottom: 0;
  border-collapse: separate;
  border-spacing: 0;
  width: 100%;
}

.table th {
  position: sticky;
  top: 0;
  background-color: #f8f9fa;
  z-index: 10;
  font-weight: 600;
  border-top: none;
  border-bottom: 2px solid #1050a9;
  padding: 0.75rem 1rem;
  text-align: left;
}

.table td {
  vertical-align: middle;
  padding: 0.75rem 1rem;
  background-color: #ffffff;
  border-bottom: 1px solid #e9ecef;
}

.table tbody tr:hover {
  background-color: #f8f9fa;
}

.table tr:last-child td {
  border-bottom: none;
}

.badge {
  padding: 0.5em 0.75em;
  font-size: 0.85em;
  font-weight: 500;
  border-radius: 30px;
  text-align: center;
  white-space: nowrap;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.badge-warning {
  background-color: #ffc107;
  color: #212529;
}

.badge-info {
  background-color: #17a2b8;
  color: white;
}

.badge-secondary {
  background-color: #6c757d;
  color: white;
}

.badge-primary {
  background-color: #1050a9;
  color: white;
}

.badge-dark {
  background-color: #343a40;
  color: white;
}

/* Estilizar la barra de desplazamiento */
.table-container::-webkit-scrollbar {
  width: 6px;
}

.table-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.table-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.table-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Responsive Design */
@media (max-width: 768px) {
  .table th, .table td {
    padding: 0.5rem;
    font-size: 0.875rem;
  }

  .card-body {
    padding: 0.75rem;
  }

  .table-container {
    max-height: 400px;
  }

  .badge {
    font-size: 0.75em;
    padding: 0.375em 0.625em;
  }
}

@media (max-width: 576px) {
  .table th, .table td {
    padding: 0.375rem;
    font-size: 0.8rem;
  }

  h4 {
    font-size: 1.1rem;
  }

  .loading-spinner p, .no-solicitudes p {
    font-size: 1rem;
  }
}

/* Mejoras de accesibilidad */
.table th {
  color: #495057;
}

.table tbody tr {
  transition: background-color 0.15s ease-in-out;
}

/* Loading animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.table tbody tr {
  animation: fadeIn 0.3s ease-in-out;
}

/* Focus states for accessibility */
.table tbody tr:focus-within {
  background-color: #e3f2fd;
  outline: 2px solid #1050a9;
  outline-offset: -2px;
}
</style>