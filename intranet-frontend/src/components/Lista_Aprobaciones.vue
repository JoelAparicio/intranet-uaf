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
                      :disabled="isSelfSolicitante(aprobacion.id_usuario_solicitud) || isProcessing"
                      title="Aprobar"
                  >
                    <font-awesome-icon v-if="isProcessing === aprobacion.id_solicitud" :icon="['fas', 'spinner']" spin />
                    <font-awesome-icon v-else :icon="['fas', 'check']" />
                  </button>
                  <button
                      class="btn btn-action btn-reject"
                      @click="rechazarSolicitud(aprobacion.id_solicitud, aprobacion.id_usuario_solicitud)"
                      :disabled="isSelfSolicitante(aprobacion.id_usuario_solicitud) || isProcessing"
                      title="Rechazar"
                  >
                    <font-awesome-icon v-if="isProcessing === aprobacion.id_solicitud" :icon="['fas', 'spinner']" spin />
                    <font-awesome-icon v-else :icon="['fas', 'xmark']" />
                  </button>
                  <button
                      class="btn btn-action btn-view"
                      @click="verPDF(aprobacion.id_solicitud)"
                      :disabled="isLoadingPDF === aprobacion.id_solicitud"
                      title="Visualizar"
                  >
                    <font-awesome-icon v-if="isLoadingPDF === aprobacion.id_solicitud" :icon="['fas', 'spinner']" spin />
                    <font-awesome-icon v-else :icon="['fas', 'eye']" />
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
          <div class="modal-header">
            <h5 class="modal-title">
              <font-awesome-icon :icon="['fas', 'file-pdf']" class="me-2" />
              Solicitud #{{ solicitudEnVisualizacion }} - Vista previa
            </h5>
            <button type="button" class="btn-close" @click="cerrarModal">
              <font-awesome-icon :icon="['fas', 'times']" />
            </button>
          </div>
          <div class="modal-body">
            <div v-if="isLoadingPDF" class="loading-pdf">
              <font-awesome-icon :icon="['fas', 'spinner']" spin size="2x" />
              <p>Cargando documento...</p>
            </div>
            <iframe v-else-if="pdfPath" :src="pdfPath" class="pdf-iframe" title="Documento PDF"></iframe>
            <div v-else class="error-pdf">
              <font-awesome-icon :icon="['fas', 'exclamation-triangle']" size="2x" />
              <p>Error al cargar el documento</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarModal">
              <font-awesome-icon :icon="['fas', 'times']" class="me-1" />
              Cerrar
            </button>
            <a v-if="pdfPath" :href="pdfPath" target="_blank" class="btn btn-primary">
              <font-awesome-icon :icon="['fas', 'external-link-alt']" class="me-1" />
              Abrir en nueva pestaña
            </a>
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
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { mapGetters } from 'vuex';

export default {
  components: {
    FontAwesomeIcon
  },

  // ✅ CORREGIDO: Usar mapGetters para acceder al store
  computed: {
    ...mapGetters('auth', ['token', 'isAuthenticated', 'user'])
  },

  data() {
    return {
      aprobaciones: [],
      loading: true,
      isProcessing: null,
      isLoadingPDF: null,
      userRoles: [],
      userId: null,
      userFirma: null,
      pdfPath: null,
      showModal: false,
      solicitudVisualizada: null,
      solicitudEnVisualizacion: null
    };
  },

  async created() {
    // ✅ CORREGIDO: Verificar autenticación antes de cargar datos
    if (!this.isAuthenticated) {
      this.$router.push('/login');
      return;
    }

    await this.fetchUserRoles();
  },

  methods: {
    // ✅ CORREGIDO: Método para obtener headers de autorización
    getAuthHeaders() {
      if (!this.token) {
        throw new Error('No token available');
      }
      return {
        'Authorization': `Bearer ${this.token}`
      };
    },

    // ✅ CORREGIDO: Método para manejar errores de autenticación
    async handleAuthError(error) {
      if (error.response?.status === 401) {
        await this.$store.dispatch('auth/logout');
        this.$router.push('/login');
        return true;
      }
      return false;
    },

    async fetchUserRoles() {
      try {
        // ✅ CORREGIDO: URLs y headers correctos
        const response = await axios.get('/roles_usuario', {
          headers: this.getAuthHeaders()
        });

        this.userRoles = response.data.roles || [];
        this.userId = response.data.id_usuario;

        // Cargar info del usuario para saber si tiene firma
        const userResponse = await axios.get('/user', {
          headers: this.getAuthHeaders()
        });

        this.userFirma = userResponse.data?.data?.firma_path;

        await this.fetchAprobaciones();
      } catch (error) {
        console.error('Error fetching user roles:', error);

        if (await this.handleAuthError(error)) return;

        this.showErrorAlert('Error al obtener los roles del usuario');
      }
    },

    async fetchAprobaciones() {
      this.loading = true;
      try {
        // ✅ CORREGIDO: URL y headers correctos
        const response = await axios.get('/listar_aprobaciones', {
          headers: this.getAuthHeaders()
        });

        if (response.data.success) {
          this.aprobaciones = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching aprobaciones:', error);

        if (await this.handleAuthError(error)) return;

        this.showErrorAlert('Error al cargar las solicitudes');
      } finally {
        this.loading = false;
        this.solicitudVisualizada = null;
      }
    },

    async aprobarSolicitud(id_solicitud, id_usuario_solicitud) {
      if (this.isSelfSolicitante(id_usuario_solicitud)) {
        this.showWarningAlert('No puedes aprobar tu propia solicitud.');
        return;
      }

      if (!this.userFirma) {
        this.showWarningAlert('Debes subir tu firma antes de aprobar solicitudes.');
        return;
      }

      if (this.solicitudVisualizada !== id_solicitud) {
        this.showWarningAlert('Debe visualizar la solicitud antes de aprobarla.');
        return;
      }

      // Confirmación antes de aprobar
      const result = await Swal.fire({
        title: '¿Confirmar aprobación?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar'
      });

      if (!result.isConfirmed) return;

      this.isProcessing = id_solicitud;

      try {
        // ✅ CORREGIDO: URL y headers correctos
        const response = await axios.put(`/aprobar-solicitud/${id_solicitud}`, {}, {
          headers: this.getAuthHeaders()
        });

        if (response.data.success) {
          this.showSuccessAlert('La solicitud ha sido aprobada exitosamente.');
          await this.fetchAprobaciones();
        }
      } catch (error) {
        console.error('Error al aprobar solicitud:', error);

        if (await this.handleAuthError(error)) return;

        this.showErrorAlert(`Error al aprobar la solicitud: ${error.response?.data?.message || error.message}`);
      } finally {
        this.isProcessing = null;
      }
    },

    async rechazarSolicitud(id_solicitud, id_usuario_solicitud) {
      if (this.isSelfSolicitante(id_usuario_solicitud)) {
        this.showWarningAlert('No puedes rechazar tu propia solicitud.');
        return;
      }

      if (this.solicitudVisualizada !== id_solicitud) {
        this.showWarningAlert('Debe visualizar la solicitud antes de rechazarla.');
        return;
      }

      const { value: comentarios } = await Swal.fire({
        title: 'Ingrese el motivo para el rechazo',
        input: 'textarea',
        inputPlaceholder: 'Describa el motivo del rechazo...',
        inputAttributes: {
          'aria-label': 'Ingrese el motivo para el rechazo'
        },
        showCancelButton: true,
        confirmButtonText: 'Rechazar',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancelar',
        cancelButtonColor: '#6c757d',
        inputValidator: (value) => {
          if (!value || value.trim().length === 0) {
            return 'Debe ingresar un motivo para el rechazo';
          }
          if (value.trim().length < 10) {
            return 'El motivo debe tener al menos 10 caracteres';
          }
        }
      });

      if (comentarios) {
        this.isProcessing = id_solicitud;

        try {
          // ✅ CORREGIDO: URL y headers correctos
          const response = await axios.put(`/rechazar-solicitud/${id_solicitud}`,
              { comentarios: comentarios.trim() },
              { headers: this.getAuthHeaders() }
          );

          if (response.data.success) {
            this.showSuccessAlert('La solicitud ha sido rechazada.');
            await this.fetchAprobaciones();
          }
        } catch (error) {
          console.error('Error al rechazar solicitud:', error);

          if (await this.handleAuthError(error)) return;

          this.showErrorAlert(`Error al rechazar la solicitud: ${error.response?.data?.message || error.message}`);
        } finally {
          this.isProcessing = null;
        }
      }
    },

    async verPDF(id_solicitud) {
      this.isLoadingPDF = id_solicitud;

      try {
        // ✅ CORREGIDO: URL y headers correctos
        const response = await axios.post('/obtener_ruta_pdf',
            { id_solicitud },
            { headers: this.getAuthHeaders() }
        );

        if (response.data.status) {
          // ✅ CORREGIDO: Construir URL completa correctamente
          this.pdfPath = axios.defaults.baseURL.replace('/api', '') + response.data.pdf_file_path;
          this.solicitudEnVisualizacion = id_solicitud;
          this.showModal = true;
        } else {
          this.showErrorAlert(response.data.message || 'No se pudo obtener el PDF');
        }
      } catch (error) {
        console.error('Error al obtener la ruta del PDF:', error);

        if (await this.handleAuthError(error)) return;

        this.showErrorAlert('Error al obtener la ruta del PDF');
      } finally {
        this.isLoadingPDF = null;
      }
    },

    cerrarModal() {
      this.showModal = false;
      this.pdfPath = null;
      this.solicitudVisualizada = this.solicitudEnVisualizacion;
      this.solicitudEnVisualizacion = null;
    },

    formatFecha(fecha) {
      if (!fecha) return '';
      return moment(fecha).format('D [de] MMMM [de] YYYY, h:mm a');
    },

    isSelfSolicitante(id_usuario_solicitud) {
      return this.userId === id_usuario_solicitud;
    },

    getBadgeClass(tipoSolicitud) {
      const classes = {
        'Vacaciones': 'badge-success',
        'Permiso': 'badge-warning',
        'Licencia': 'badge-info',
        'Reincorporación': 'badge-secondary',
        'Tiempo Compensatorio': 'badge-primary',
        'Horas Extraordinarias': 'badge-info'
      };
      return classes[tipoSolicitud] || 'badge-primary';
    },

    showSuccessAlert(message) {
      Swal.fire({
        title: '¡Éxito!',
        text: message,
        icon: 'success',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      });
    },

    showErrorAlert(message) {
      Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#dc3545'
      });
    },

    showWarningAlert(message) {
      Swal.fire({
        title: 'Atención',
        text: message,
        icon: 'warning',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#ffc107'
      });
    }
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
  border: none;
}

.card-header {
  background-color: #1050a9;
  color: white;
  padding: 1rem;
}

.card-header h4 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.card-body {
  padding: 1rem;
}

.loading-spinner,
.no-solicitudes {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #6c757d;
}

.loading-spinner p,
.no-solicitudes p {
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
  font-size: 0.9rem;
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

.table tbody tr:hover {
  background-color: #f8f9fa;
}

.badge {
  padding: 0.5em 0.75em;
  font-size: 0.85em;
  font-weight: 500;
  border-radius: 30px;
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
  cursor: pointer;
}

.btn-approve {
  background-color: #28a745;
}
.btn-reject {
  background-color: #dc3545;
}
.btn-view {
  background-color: #1050a9;
}

.btn-action:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.btn-action:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

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
  backdrop-filter: blur(2px);
}

.modal-container {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 1200px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #e9ecef;
  background-color: #f8f9fa;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1050a9;
}

.btn-close {
  background: none;
  border: none;
  color: #6c757d;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.btn-close:hover {
  background-color: #e9ecef;
  color: #343a40;
}

.modal-body {
  flex-grow: 1;
  overflow: hidden;
  padding: 0;
  position: relative;
}

.loading-pdf,
.error-pdf {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 400px;
  color: #6c757d;
}

.loading-pdf p,
.error-pdf p {
  margin-top: 1rem;
  font-size: 1rem;
}

.pdf-iframe {
  width: 100%;
  height: calc(90vh - 120px);
  border: none;
  display: block;
}

.modal-footer {
  padding: 1rem;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  background-color: #f8f9fa;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.15s ease-in-out;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
}

.btn-secondary:hover {
  background-color: #5a6268;
  color: white;
  text-decoration: none;
}

.btn-primary {
  background-color: #1050a9;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.15s ease-in-out;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
}

.btn-primary:hover {
  background-color: #0a3f87;
  color: white;
  text-decoration: none;
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

.modal-fade-enter-active .modal-container,
.modal-fade-leave-active .modal-container {
  transition: transform 0.3s ease;
}

.modal-fade-enter-from .modal-container,
.modal-fade-leave-to .modal-container {
  transform: scale(0.9);
}

/* Utilidades */
.me-1 {
  margin-right: 0.25rem;
}

.me-2 {
  margin-right: 0.5rem;
}

/* Estilos responsivos */
@media (max-width: 768px) {
  .table th,
  .table td {
    padding: 0.5rem;
    font-size: 0.875rem;
  }

  .action-buttons {
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
  }

  .btn-action {
    width: 28px;
    height: 28px;
  }

  .modal-container {
    width: 95%;
    margin: 0.5rem;
  }

  .pdf-iframe {
    height: calc(90vh - 100px);
  }

  .modal-header {
    padding: 0.75rem;
  }

  .modal-footer {
    padding: 0.75rem;
    flex-direction: column;
  }

  .btn-secondary,
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 576px) {
  .table-container {
    font-size: 0.8rem;
  }

  .badge {
    font-size: 0.7rem;
    padding: 0.25em 0.5em;
  }
}
</style>