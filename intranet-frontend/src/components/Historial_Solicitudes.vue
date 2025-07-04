<template>
  <div class="container mt-4">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <select class="form-select" v-model="filtroTipo">
            <option value="">Todos los Formularios</option>
            <option v-for="tipo in tiposSolicitudes" :key="tipo.id_tipo_solicitud" :value="tipo.id_tipo_solicitud">
              {{ tipo.tipo_solicitud }}
            </option>
          </select>
        </div>
        <div class="d-flex align-items-center">
          <label class="form-label me-2 text-center" style="width: 50px;">
            Desde
          </label>
          <input
              type="date"
              class="form-control me-2"
              v-model="filtroFechaInicio"
              placeholder="Fecha de Inicio"
          />
          <label class="form-label me-2 text-center" style="width: 50px;">
            Hasta
          </label>
          <input
              type="date"
              class="form-control"
              v-model="filtroFechaFin"
              placeholder="Fecha de Fin"
          />
        </div>
      </div>

      <div class="card-body">
        <!-- Loading indicator -->
        <div v-if="isLoading" class="text-center my-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          <p class="mt-2 text-muted">Cargando historial de solicitudes...</p>
        </div>

        <!-- Tabla de solicitudes -->
        <div v-else>
          <table class="table table-hover">
            <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Tipo de Solicitud</th>
              <th scope="col">Fecha inicial solicitada</th>
              <th scope="col">Fecha final solicitada</th>
              <th scope="col">Estado</th>
              <th scope="col">Creación de solicitud</th>
              <th scope="col">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="solicitudesFiltradas.length === 0">
              <td colspan="7" class="text-center text-muted py-4">
                <i class="fas fa-inbox me-2"></i>
                No se encontraron solicitudes
              </td>
            </tr>
            <tr v-for="solicitud in solicitudesFiltradas" :key="solicitud.id_solicitud" v-else>
              <td>{{ solicitud.id_solicitud }}</td>
              <td>{{ solicitud.tipo_solicitud.tipo_solicitud }}</td>
              <td>{{ formatFecha(solicitud.fecha_inicio) }}</td>
              <td>{{ formatFecha(solicitud.fecha_fin) }}</td>
              <td>
                  <span
                      class="badge"
                      :class="getBadgeClass(solicitud.estado)"
                  >
                    {{ getEstadoText(solicitud.estado) }}
                  </span>
              </td>
              <td>{{ formatFecha(solicitud.fecha_creacion) }}</td>
              <td>
                <div class="btn-group btn-group-sm" role="group">
                  <button
                      type="button"
                      class="btn btn-outline-primary"
                      @click="verPDF(solicitud)"
                      :disabled="isGeneratingPDF === solicitud.id_solicitud"
                      :title="'Ver PDF de solicitud ' + solicitud.id_solicitud"
                  >
                    <span v-if="isGeneratingPDF === solicitud.id_solicitud" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="fas fa-eye me-1"></i>
                    Ver
                  </button>
                  <button
                      type="button"
                      class="btn btn-outline-secondary"
                      @click="editarSolicitud(solicitud)"
                      :disabled="solicitud.estado !== 'pendiente'"
                      :title="solicitud.estado !== 'pendiente' ? 'Solo se pueden editar solicitudes pendientes' : 'Editar solicitud'"
                  >
                    <i class="fas fa-edit me-1"></i>
                    Editar
                  </button>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal para editar solicitud -->
    <div class="modal fade" id="editSolicitudModal" tabindex="-1" aria-labelledby="editSolicitudModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editSolicitudModalLabel">
              <i class="fas fa-edit me-2"></i>
              Editar Solicitud #{{ solicitudEditando?.id_solicitud }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <form @submit.prevent="guardarCambios" v-if="solicitudEditando">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editTipoSolicitud" class="form-label fw-semibold">
                    Tipo de Solicitud
                  </label>
                  <select
                      class="form-select"
                      id="editTipoSolicitud"
                      v-model="solicitudEditando.tipo_solicitud_id"
                      disabled
                  >
                    <option v-for="tipo in tiposSolicitudes" :key="tipo.id_tipo_solicitud" :value="tipo.id_tipo_solicitud">
                      {{ tipo.tipo_solicitud }}
                    </option>
                  </select>
                  <small class="form-text text-muted">El tipo de solicitud no se puede cambiar</small>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="editEstado" class="form-label fw-semibold">
                    Estado
                  </label>
                  <input
                      type="text"
                      class="form-control"
                      id="editEstado"
                      :value="getEstadoText(solicitudEditando.estado)"
                      disabled
                  >
                  <small class="form-text text-muted">El estado es controlado por el sistema</small>
                </div>
              </div>

              <!-- Motivo (para permisos y reincorporación) -->
              <div class="mb-3" v-if="esFormularioPermiso">
                <label for="editMotivo" class="form-label fw-semibold">
                  Motivo <span class="text-danger">*</span>
                </label>
                <div class="row">
                  <div class="col-md-6">
                    <label class="fw-semibold text-success mb-2">Motivos descontables</label>
                    <div class="form-check" v-for="motivo in motivosDescontables" :key="motivo.valor">
                      <input
                          class="form-check-input"
                          type="radio"
                          :id="'motivo_' + motivo.valor"
                          :value="motivo.valor"
                          v-model="solicitudEditando.motivo"
                      >
                      <label class="form-check-label" :for="'motivo_' + motivo.valor">
                        {{ motivo.nombre }}
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="fw-semibold text-warning mb-2">Motivos no descontables</label>
                    <div class="form-check" v-for="motivo in motivosNoDescontables" :key="motivo.valor">
                      <input
                          class="form-check-input"
                          type="radio"
                          :id="'motivo_' + motivo.valor"
                          :value="motivo.valor"
                          v-model="solicitudEditando.motivo"
                      >
                      <label class="form-check-label" :for="'motivo_' + motivo.valor">
                        {{ motivo.nombre }}
                      </label>
                    </div>
                  </div>
                </div>
                <div v-if="editErrors.motivo" class="text-danger mt-1">
                  {{ editErrors.motivo[0] }}
                </div>
              </div>

              <!-- Motivo para reincorporación -->
              <div class="mb-3" v-if="esFormularioReincorporacion">
                <label for="editMotivoReincorporacion" class="form-label fw-semibold">
                  Motivo <span class="text-danger">*</span>
                </label>
                <div class="form-check" v-for="motivo in motivosReincorporacion" :key="motivo.valor">
                  <input
                      class="form-check-input"
                      type="radio"
                      :id="'motivo_reinc_' + motivo.valor"
                      :value="motivo.valor"
                      v-model="solicitudEditando.motivo"
                  >
                  <label class="form-check-label" :for="'motivo_reinc_' + motivo.valor">
                    {{ motivo.nombre }}
                  </label>
                </div>
                <div v-if="editErrors.motivo" class="text-danger mt-1">
                  {{ editErrors.motivo[0] }}
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editFechaInicio" class="form-label fw-semibold">
                    Fecha inicial solicitada <span class="text-danger">*</span>
                  </label>
                  <input
                      type="date"
                      class="form-control"
                      id="editFechaInicio"
                      v-model="solicitudEditando.fecha_inicio"
                      :class="{'is-invalid': editErrors.fecha_inicio}"
                      required
                  >
                  <div v-if="editErrors.fecha_inicio" class="invalid-feedback">
                    {{ editErrors.fecha_inicio[0] }}
                  </div>
                </div>

                <div class="col-md-6 mb-3" v-if="!esFormularioReincorporacion">
                  <label for="editFechaFin" class="form-label fw-semibold">
                    Fecha final solicitada <span class="text-danger">*</span>
                  </label>
                  <input
                      type="date"
                      class="form-control"
                      id="editFechaFin"
                      v-model="solicitudEditando.fecha_fin"
                      :class="{'is-invalid': editErrors.fecha_fin}"
                      :required="!esFormularioReincorporacion"
                  >
                  <div v-if="editErrors.fecha_fin" class="invalid-feedback">
                    {{ editErrors.fecha_fin[0] }}
                  </div>
                </div>
              </div>

              <!-- Campos específicos para vacaciones -->
              <div v-if="esFormularioVacaciones" class="row">
                <div class="col-md-6 mb-3">
                  <label for="editSalario" class="form-label fw-semibold">Salario</label>
                  <input
                      type="number"
                      class="form-control"
                      id="editSalario"
                      v-model="solicitudEditando.salario"
                      min="0"
                      step="0.01"
                      placeholder="Salario mensual"
                  >
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editDias" class="form-label fw-semibold">Días de vacaciones</label>
                  <input
                      type="number"
                      class="form-control"
                      id="editDias"
                      v-model="solicitudEditando.dias"
                      min="1"
                      placeholder="Número de días"
                  >
                </div>
              </div>

              <!-- Campos específicos para horas extraordinarias -->
              <div v-if="esFormularioHorasExtras">
                <div class="mb-3">
                  <label for="editTrabajoRealizado" class="form-label fw-semibold">Trabajo Realizado</label>
                  <textarea
                      class="form-control"
                      id="editTrabajoRealizado"
                      rows="3"
                      v-model="solicitudEditando.trabajo_realizado"
                      placeholder="Descripción del trabajo realizado"
                  ></textarea>
                </div>
                <div class="mb-3">
                  <label for="editJustificacion" class="form-label fw-semibold">Justificación</label>
                  <textarea
                      class="form-control"
                      id="editJustificacion"
                      rows="3"
                      v-model="solicitudEditando.justificacion"
                      placeholder="Causas que impidieron realizar las labores durante la jornada regular"
                  ></textarea>
                </div>
              </div>

              <div class="mb-3">
                <label for="editObservacion" class="form-label fw-semibold">
                  Observación
                </label>
                <textarea
                    class="form-control"
                    id="editObservacion"
                    rows="3"
                    v-model="solicitudEditando.observacion"
                    :class="{'is-invalid': editErrors.observacion}"
                    placeholder="Observaciones adicionales (opcional)"
                ></textarea>
                <div v-if="editErrors.observacion" class="invalid-feedback">
                  {{ editErrors.observacion[0] }}
                </div>
              </div>

              <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Nota:</strong> Solo puedes editar solicitudes que estén en estado "pendiente".
                Los cambios serán revisados nuevamente por tu supervisor.
              </div>
            </form>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button
                type="button"
                class="btn btn-primary"
                @click="guardarCambios"
                :disabled="isUpdating"
            >
              <span v-if="isUpdating" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="fas fa-save me-1"></i>
              {{ isUpdating ? 'Guardando...' : 'Guardar cambios' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para mostrar PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="pdfModalLabel">
              <i class="fas fa-file-pdf me-2"></i>
              Solicitud #{{ solicitudViendoPDF?.id_solicitud }} - PDF
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body p-0">
            <div v-if="isGeneratingPDF" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Generando PDF...</span>
              </div>
              <p class="mt-3 text-muted">Generando documento PDF...</p>
            </div>

            <iframe
                v-else-if="pdfUrl"
                :src="pdfUrl"
                style="width: 100%; height: 70vh; border: none;"
                title="Vista previa del PDF"
            ></iframe>

            <div v-else class="text-center py-5">
              <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
              <p class="text-muted">No se pudo cargar el PDF</p>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times me-1"></i>Cerrar
            </button>
            <a
                v-if="pdfUrl"
                :href="getFullPdfUrl(pdfUrl)"
                target="_blank"
                class="btn btn-primary"
            >
              <i class="fas fa-external-link-alt me-1"></i>Abrir en nueva pestaña
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { apiCall } from '@/utils/apiHelper';
import moment from 'moment';
import 'moment/locale/es';
import { Modal } from 'bootstrap';
import Swal from 'sweetalert2';
import { mapGetters } from 'vuex';

export default {
  name: 'HistorialSolicitudes',
  props: {
    tiposSolicitudes: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      solicitudes: [],
      tiposSolicitudes: [],
      filtroTipo: '',
      filtroFechaInicio: '',
      filtroFechaFin: '',

      isLoading: false,
      isUpdating: false,
      isGeneratingPDF: null,

      solicitudEditando: null,
      editModalInstance: null,
      editErrors: {},

      pdfModalInstance: null,
      solicitudViendoPDF: null,
      pdfUrl: null,

      motivosDescontables: [
        { valor: 'Enfermedad', nombre: 'Enfermedad' },
        { valor: 'Duelo', nombre: 'Duelo' },
        { valor: 'Matrimonio', nombre: 'Matrimonio' },
        { valor: 'Nacimiento de hijos', nombre: 'Nacimiento de hijos' },
        { valor: 'Enfermedades de parientes cercanos', nombre: 'Enfermedades de parientes cercanos' },
        { valor: 'Eventos académicos puntuales', nombre: 'Eventos académicos puntuales' },
        { valor: 'Permisos personales', nombre: 'Permisos personales' }
      ],
      motivosNoDescontables: [
        { valor: 'Misión oficial', nombre: 'Misión oficial' },
        { valor: 'Seminarios', nombre: 'Seminarios' },
        { valor: 'Otros, especifique', nombre: 'Otros, especifique' }
      ],
      motivosReincorporacion: [
        { valor: 'Licencia con sueldo', nombre: 'Licencia con sueldo' },
        { valor: 'Licencia sin sueldo', nombre: 'Licencia sin sueldo' },
        { valor: 'Licencia especial', nombre: 'Licencia especial' },
        { valor: 'Vacaciones', nombre: 'Vacaciones' }
      ]
    };
  },

  computed: {
    ...mapGetters('auth', ['token', 'isAuthenticated']),

    solicitudesFiltradas() {
      return this.solicitudes.filter(solicitud => {
        const cumpleTipo = this.filtroTipo ? solicitud.tipo_solicitud.id_tipo_solicitud === parseInt(this.filtroTipo) : true;
        const cumpleFechaInicio = this.filtroFechaInicio ? new Date(solicitud.fecha_creacion) >= new Date(this.filtroFechaInicio) : true;
        const cumpleFechaFin = this.filtroFechaFin ? new Date(solicitud.fecha_creacion) <= new Date(this.filtroFechaFin) : true;
        return cumpleTipo && cumpleFechaInicio && cumpleFechaFin;
      });
    },

    esFormularioPermiso() {
      return this.solicitudEditando && this.solicitudEditando.tipo_solicitud_id === 1;
    },

    esFormularioReincorporacion() {
      return this.solicitudEditando && this.solicitudEditando.tipo_solicitud_id === 2;
    },

    esFormularioVacaciones() {
      return this.solicitudEditando && this.solicitudEditando.tipo_solicitud_id === 4;
    },

    esFormularioHorasExtras() {
      return this.solicitudEditando && this.solicitudEditando.tipo_solicitud_id === 5;
    }
  },

  created() {
    this.fetchSolicitudes();
    this.fetchTiposSolicitudes();
  },

  mounted() {
    this.$nextTick(() => {
      this.editModalInstance = new Modal(document.getElementById('editSolicitudModal'));
      this.pdfModalInstance = new Modal(document.getElementById('pdfModal'));
    });
  },

  beforeUnmount() {
    if (this.editModalInstance) {
      this.editModalInstance.dispose();
    }
    if (this.pdfModalInstance) {
      this.pdfModalInstance.dispose();
    }
  },

  methods: {
    async fetchSolicitudes() {
      this.isLoading = true;
      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        const response = await apiCall.get('historialSolicitud', {
          headers: {
            'Authorization': `Bearer ${this.token}`
          }
        });
        this.solicitudes = response.data;
      } catch (error) {
        console.error('Error fetching solicitudes', error);

        if (error.response?.status === 401) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error al cargar el historial de solicitudes'
        });
      } finally {
        this.isLoading = false;
      }
    },

    async fetchTiposSolicitudes() {
      try {
        if (!this.token) return;

        const response = await apiCall.get('listarSolicitud', {
          headers: {
            'Authorization': `Bearer ${this.token}`
          }
        });
        this.tiposSolicitudes = response.data;
      } catch (error) {
        if (error.response?.status !== 401 && error.response?.status !== 403) {
          console.error('Error fetching tipos de solicitudes', error);
        }
      }
    },

    async verPDF(solicitud) {
      this.isGeneratingPDF = solicitud.id_solicitud;
      this.solicitudViendoPDF = solicitud;
      this.pdfUrl = null;

      try {
        const response = await apiCall.post('obtenerRutaPdf', {
          id_solicitud: solicitud.id_solicitud
        }, {
          headers: {
            'Authorization': `Bearer ${this.token}`
          }
        });

        if (response.data.status && response.data.pdf_file_path) {
          this.pdfUrl = axios.defaults.baseURL.replace('/api', '') + response.data.pdf_file_path;
          this.pdfModalInstance.show();
        } else {
          throw new Error(response.data.message || 'No se pudo obtener el PDF');
        }

      } catch (error) {
        console.error('Error al obtener PDF:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'No se pudo obtener el PDF'
        });
      } finally {
        this.isGeneratingPDF = null;
      }
    },

    editarSolicitud(solicitud) {
      if (solicitud.estado !== 'pendiente') {
        Swal.fire({
          icon: 'warning',
          title: 'No se puede editar',
          text: 'Solo se pueden editar solicitudes en estado pendiente'
        });
        return;
      }

      this.solicitudEditando = {
        ...solicitud,
        tipo_solicitud_id: solicitud.tipo_solicitud.id_tipo_solicitud,
        fecha_inicio: this.formatDateForInput(solicitud.fecha_inicio),
        fecha_fin: this.formatDateForInput(solicitud.fecha_fin)
      };

      this.editErrors = {};
      this.editModalInstance.show();
    },

    async guardarCambios() {
      if (!this.solicitudEditando) return;

      this.isUpdating = true;
      this.editErrors = {};

      try {
        const datosActualizacion = {
          fecha_inicio: this.solicitudEditando.fecha_inicio,
          fecha_fin: this.solicitudEditando.fecha_fin,
          motivo: this.solicitudEditando.motivo || '',
          observacion: this.solicitudEditando.observacion || ''
        };

        if (this.esFormularioVacaciones) {
          datosActualizacion.salario = this.solicitudEditando.salario;
          datosActualizacion.dias = this.solicitudEditando.dias;
        } else if (this.esFormularioHorasExtras) {
          datosActualizacion.trabajo_realizado = this.solicitudEditando.trabajo_realizado;
          datosActualizacion.justificacion = this.solicitudEditando.justificacion;
        }

        const response = await apiCall.putWithId('actualizarSolicitud', this.solicitudEditando.id_solicitud, datosActualizacion, {
          headers: {
            'Authorization': `Bearer ${this.token}`
          }
        });

        console.log('Respuesta del servidor:', response.data);

        if (response.data && response.data.status === true) {
          const index = this.solicitudes.findIndex(s => s.id_solicitud === this.solicitudEditando.id_solicitud);
          if (index !== -1 && response.data.data) {
            this.solicitudes[index] = { ...this.solicitudes[index], ...response.data.data };
          }

          this.editModalInstance.hide();
          this.solicitudEditando = null;

          Swal.fire({
            icon: 'success',
            title: 'Solicitud actualizada',
            text: 'Los cambios se han guardado correctamente',
            timer: 2000,
            showConfirmButton: false
          });

          this.fetchSolicitudes();

        } else {
          throw new Error(response.data?.message || 'Error al actualizar la solicitud');
        }

      } catch (error) {
        console.error('Error al actualizar solicitud:', error);

        if (error.response?.status === 422) {
          this.editErrors = error.response.data.errors || {};
        }

        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Error al actualizar la solicitud'
        });
      } finally {
        this.isUpdating = false;
      }
    },

    getFullPdfUrl(relativePath) {
      if (!relativePath) return '';

      if (relativePath.startsWith('http')) {
        return relativePath;
      }

      return `${window.location.origin}${relativePath}`;
    },

    getBadgeClass(estado) {
      const classes = {
        'pendiente': 'bg-warning text-dark',
        'revision': 'bg-info text-white',
        'aprobada': 'bg-success text-white',
        'rechazada': 'bg-danger text-white'
      };
      return classes[estado] || 'bg-secondary text-white';
    },

    getEstadoText(estado) {
      const textos = {
        'pendiente': 'Pendiente',
        'revision': 'En Revisión',
        'aprobada': 'Aprobada',
        'rechazada': 'Rechazada'
      };
      return textos[estado] || estado;
    },

    formatFecha(fecha) {
      if (!fecha) return '';
      return moment(fecha).format('MMMM D YYYY, h:mm a');
    },

    formatDateForInput(fecha) {
      if (!fecha) return '';
      return moment(fecha).format('YYYY-MM-DD');
    }
  },
};
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

.card {
  width: 100%;
}

.card-header {
  background-color: #f8f9fa;
}

.form-label {
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-body {
  height: 400px;
  overflow-y: scroll;
}

.btn-group-sm .btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.badge {
  font-size: 0.75em;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.modal-xl {
  max-width: 90%;
}

.fw-semibold {
  font-weight: 600;
}

.text-muted {
  color: #6c757d !important;
}
</style>