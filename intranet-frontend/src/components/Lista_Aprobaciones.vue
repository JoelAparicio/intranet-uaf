<template>
  <div class="lista-aprobaciones">
    <div class="card">
      <div class="card-header">
        <h4>Solicitudes pendientes</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="loading-spinner">
          <font-awesome-icon :icon="['fas', 'spinner']" spin size="3x" />
          <p>Cargando solicitudes...</p>
        </div>
        <div v-else-if="aprobaciones.length === 0" class="no-solicitudes">
          <font-awesome-icon :icon="['far', 'face-smile-wink']" size="3x" />
          <p>No hay solicitudes pendientes</p>
        </div>
        <div v-else class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>ID Solicitud</th>
                <th>Colaborador</th>
                <th>Tipo de Solicitud</th>
                <th>Fecha de Creación</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="aprobacion in aprobaciones" :key="aprobacion.id_solicitud">
                <td>{{ aprobacion.id_solicitud }}</td>
                <td>{{ aprobacion.colaborador }}</td>
                <td>
                  <span class="badge" :class="getBadgeClass(aprobacion.tipo_solicitud)">
                    {{ aprobacion.tipo_solicitud }}
                  </span>
                </td>
                <td>{{ formatFecha(aprobacion.fecha_creacion) }}</td>
                <td>
                  <div class="action-buttons">
                    <button
                      class="btn btn-action btn-approve"
                      @click="aprobarSolicitud(aprobacion.id_solicitud, aprobacion.id_usuario_solicitud)"
                      :disabled="isSelfSolicitante(aprobacion.id_usuario_solicitud)"
                      title="Aprobar"
                    >
                      <font-awesome-icon :icon="['fas', 'check']" />
                    </button>
                    <button
                      class="btn btn-action btn-reject"
                      @click="rechazarSolicitud(aprobacion.id_solicitud, aprobacion.id_usuario_solicitud)"
                      :disabled="isSelfSolicitante(aprobacion.id_usuario_solicitud)"
                      title="Rechazar"
                    >
                      <font-awesome-icon :icon="['fas', 'xmark']" />
                    </button>
                    <button 
                      class="btn btn-action btn-view"
                      @click="verPDF(aprobacion.id_solicitud)"
                      title="Visualizar"
                    >
                      <font-awesome-icon :icon="['fas', 'eye']" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal para mostrar el PDF -->
    <transition name="modal-fade">
      <div v-if="showModal" class="modal-overlay" @click="cerrarModal">
        <div class="modal-container" @click.stop>
          <div class="modal-body">
            <iframe v-if="pdfPath" :src="pdfPath" class="pdf-iframe" title="Documento PDF"></iframe>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarModal">Cerrar</button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';
import 'moment/locale/es';
import Swal from 'sweetalert2';
import { ref, onMounted, markRaw } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import apiConfig from '@/config/api'



axios.defaults.withCredentials = true;

export default markRaw({
  components: {
    FontAwesomeIcon
  },
  setup() {
    const aprobaciones = ref([]);
    const loading = ref(true);
    const userRoles = ref([]);
    const userId = ref(null);
    const userFirma = ref(null);
    const pdfPath = ref(null);
    const showModal = ref(false);
    const solicitudVisualizada = ref(null);
    const solicitudEnVisualizacion = ref(null);

    const fetchUserRoles = async () => {
      try {
        const response = await axios.get(apiConfig.endpoints.rolesUsuario);
        userRoles.value = response.data.roles || [];
        userId.value = response.data.id_usuario;

        // Cargar info del usuario para saber si tiene firma
        const userResponse = await axios.get(apiConfig.endpoints.user);
        userFirma.value = userResponse.data?.data?.firma_path;

        await fetchAprobaciones();
      } catch (error) {
        console.error('Error fetching user roles:', error);
        showErrorAlert('Error al obtener los roles del usuario');
      }
    };

    const fetchAprobaciones = async () => {
      loading.value = true;
      try {
        const response = await axios.get(apiConfig.endpoints.listarAprobaciones);
        if (response.data.success) {
          aprobaciones.value = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching aprobaciones:', error);
        showErrorAlert('Error al cargar las solicitudes');
      } finally {
        loading.value = false;
        solicitudVisualizada.value = null;
      }
    };

    const aprobarSolicitud = async (id_solicitud, id_usuario_solicitud) => {
      if (isSelfSolicitante(id_usuario_solicitud)) {
        showWarningAlert('No puedes aprobar tu propia solicitud.');
        return;
      }

      if (!userFirma.value) {
        showWarningAlert('Debes subir tu firma antes de aprobar solicitudes.');
        return;
      }

      if (solicitudVisualizada.value !== id_solicitud) {
        showWarningAlert('Debe visualizar la solicitud antes de aprobarla.');
        return;
      }

      try {
        const response = await axios.put(`${apiConfig.endpoints.aprobarSolicitud}/${id_solicitud}`);
        if (response.data.success) {
          showSuccessAlert('La solicitud ha sido aprobada exitosamente.');
          await fetchAprobaciones();
        }
      } catch (error) {
        showErrorAlert(`Error al aprobar la solicitud: ${error.response?.data?.message || error.message}`);
      }
    };

    const rechazarSolicitud = async (id_solicitud, id_usuario_solicitud) => {
      if (isSelfSolicitante(id_usuario_solicitud)) {
        showWarningAlert('No puedes rechazar tu propia solicitud.');
        return;
      }

      if (solicitudVisualizada.value !== id_solicitud) {
        showWarningAlert('Debe visualizar la solicitud antes de rechazarla.');
        return;
      }

      const { value: comentarios } = await Swal.fire({
        title: 'Ingrese el motivo para el rechazo',
        input: 'textarea',
        inputAttributes: {
          'aria-label': 'Ingrese el motivo para el rechazo'
        },
        showCancelButton: true,
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
          if (!value) {
            return 'Debe ingresar un motivo para el rechazo';
          }
        }
      });

      if (comentarios) {
        try {
          const response = await axios.put(`${apiConfig.endpoints.rechazarSolicitud}/${id_solicitud}`, { comentarios });
          if (response.data.success) {
            showSuccessAlert('La solicitud ha sido rechazada.');
            await fetchAprobaciones();
          }
        } catch (error) {
          showErrorAlert(`Error al rechazar la solicitud: ${error.response?.data?.message || error.message}`);
        }
      }
    };

    const verPDF = async (id_solicitud) => {
      try {
        const response = await axios.post(apiConfig.endpoints.obtenerRutaPdf, { id_solicitud });
        if (response.data.status) {
          pdfPath.value = apiConfig.baseURL.replace('/api', '') + response.data.pdf_file_path;
          showModal.value = true;
          solicitudEnVisualizacion.value = id_solicitud;
        } else {
          showErrorAlert(response.data.message);
        }
      } catch (error) {
        console.error('Error al obtener la ruta del PDF:', error);
        showErrorAlert('Error al obtener la ruta del PDF');
      }
    };

    const cerrarModal = () => {
      showModal.value = false;
      pdfPath.value = null;
      solicitudVisualizada.value = solicitudEnVisualizacion.value;
      solicitudEnVisualizacion.value = null;
    };

    const formatFecha = (fecha) => {
      if (!fecha) return '';
      return moment(fecha).format('D [de] MMMM [de] YYYY, h:mm a');
    };

    const isSelfSolicitante = (id_usuario_solicitud) => {
      return userId.value === id_usuario_solicitud;
    };

    const getBadgeClass = (tipoSolicitud) => {
      const classes = {
        'Vacaciones': 'badge-success',
        'Permiso': 'badge-warning',
        'Licencia': 'badge-info',
        'Otros': 'badge-secondary'
      };
      return classes[tipoSolicitud] || 'badge-primary';
    };

    const showSuccessAlert = (message) => {
      Swal.fire({
        title: '¡Éxito!',
        text: message,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      });
    };

    const showErrorAlert = (message) => {
      Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonText: 'Ok'
      });
    };

    const showWarningAlert = (message) => {
      Swal.fire({
        title: 'Atención',
        text: message,
        icon: 'warning',
        confirmButtonText: 'Ok'
      });
    };

    onMounted(() => {
      fetchUserRoles();
    });

    return {
      aprobaciones,
      loading,
      aprobarSolicitud,
      rechazarSolicitud,
      verPDF,
      cerrarModal,
      formatFecha,
      isSelfSolicitante,
      getBadgeClass,
      showModal,
      pdfPath
    };
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

.card-header h4 {
  margin: 0;
  font-size: 1.25rem;
}

.card-body {
  padding: 1rem;
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
  max-height: 485px;
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

.action-buttons {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}

.btn-action {
  width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease-in-out;
  border: none;
  color: white;
}

.btn-approve { background-color: #28a745; }
.btn-reject { background-color: #dc3545; }
.btn-view { background-color: #1050a9; }

.btn-action:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.btn-action:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

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

/* Estilos para el modal de visualización de PDF */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-container {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
  width: 90%;
  max-width: 1200px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-body {
  flex-grow: 1;
  overflow: hidden;
  padding: 1rem;
}

.pdf-iframe {
  width: 100%;
  height: calc(90vh - 80px);
  border: none;
  border-radius: 4px;
}

.modal-footer {
  padding: 1rem;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: flex-end;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.15s ease-in-out;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

/* Animaciones para el modal */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* Estilos responsivos */
@media (max-width: 768px) {
  .table th, .table td {
    padding: 0.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
    align-items: center;
  }
  
  .btn-action {
    margin-bottom: 0.25rem;
  }

  .modal-container {
    width: 95%;
  }

  .pdf-iframe {
    height: calc(90vh - 60px);
  }
}
</style>