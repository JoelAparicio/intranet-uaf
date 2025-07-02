<template>
  <div class="lista-aprobaciones">
    <div class="card">
      <div class="card-header">
        <h4 class="text-center">Solicitudes Rechazadas</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="loading-spinner">
          <font-awesome-icon :icon="['fas', 'spinner']" spin size="3x" />
          <p>Cargando solicitudes...</p>
        </div>
        <div v-else-if="solicitudesRechazadas.length === 0" class="no-solicitudes">
          <font-awesome-icon :icon="['far', 'face-smile-wink']" size="3x" />
          <p>No has rechazado solicitudes</p>
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
              <tr v-for="solicitud in solicitudesRechazadas" :key="solicitud.id_solicitud">
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
import axios from 'axios';
import moment from 'moment';
import 'moment/locale/es';
import { markRaw } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

export default markRaw({
  components: {
    FontAwesomeIcon
  },
  data() {
    return {
      solicitudesRechazadas: [],
      loading: true
    };
  },
  methods: {
    async fetchSolicitudesRechazadas() {
      this.loading = true;
      try {
        const response = await axios.get('solicitudes-rechazadas');
        if (response.data.success) {
          this.solicitudesRechazadas = response.data.data;
        }
        this.loading = false;
      } catch (error) {
        console.error('Error fetching solicitudes rechazadas:', error);
        this.loading = false;
      }
    },
    formatFecha(fecha) {
      if (!fecha) return '';
      return moment(fecha).format('D [de] MMMM [de] YYYY, h:mm a');
    },
    getBadgeClass(tipoSolicitud) {
      const classes = {
        'Vacaciones': 'badge-success',
        'Permiso': 'badge-warning',
        'Licencia': 'badge-info',
        'Otros': 'badge-secondary'
      };
      return classes[tipoSolicitud] || 'badge-primary';
    }
  },
  mounted() {
    this.fetchSolicitudesRechazadas();
  }
});
</script>

<style scoped>
.lista-aprobaciones {
  margin-top: 0.5rem;
}

.card {
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
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
}

.table td {
  vertical-align: middle;
  padding: 0.75rem 1rem;
  background-color: #ffffff;
  border-bottom: 1px solid #e9ecef;
}

.table tr:last-child td {
  border-bottom: none;
}

.badge {
  padding: 0.5em 0.75em;
  font-size: 0.85em;
  font-weight: 500;
  border-radius: 30px;
}

.badge-success { background-color: #28a745; color: white; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-info { background-color: #17a2b8; color: white; }
.badge-secondary { background-color: #6c757d; color: white; }
.badge-primary { background-color: #1050a9; color: white; }

/* Estilizar la barra de desplazamiento */
.table-container::-webkit-scrollbar {
  width: 6px;
}

.table-container::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.table-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.table-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}

@media (max-width: 768px) {
  .table th, .table td {
    padding: 0.5rem;
  }
}
</style>
