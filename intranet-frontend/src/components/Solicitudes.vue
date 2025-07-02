<template>
  <div class="container">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-3">Solicitudes</h5>
        </div>
        <div class="card-body">
          <p class="card-text">Aquí podrás realizar solicitudes</p>
          <div class="row">
            <div class="col-md-4 mb-3" v-for="solicitud in solicitudes" :key="solicitud.id_tipo_solicitud">
              <div
                  class="card h-100 shadow-sm clickable-card"
                  @click="handleCardClick(solicitud.id_tipo_solicitud)"
                  data-bs-toggle="modal"
                  data-bs-target="#solicitudModal"
              >
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title">{{ solicitud.tipo_solicitud }}</h5>
                  <p class="card-text">{{ solicitud.descripcion }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Vue -->
    <div class="modal fade" id="solicitudModal" tabindex="-1" aria-labelledby="solicitudModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="solicitudModalLabel">{{ selectedSolicitudTitle }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Formulario dinámico basado en selectedSolicitud -->
            <form v-if="selectedSolicitud === 1" @submit.prevent="enviarSolicitud">
              <div class="mb-3">
                <label class="form-label">Motivos descontables</label>
                <div class="form-check" v-for="(motivo, index) in motivosDescontables" :key="'desc-' + index">
                  <input class="form-check-input" type="radio" :id="'desc-' + index" name="motivo" :value="motivo" v-model="solicitud_permiso.motivo">
                  <label class="form-check-label" :for="'desc-' + index">
                    {{ motivo }}
                  </label>
                </div>
                <hr>
                <label class="form-label">Motivos no descontables</label>
                <div class="form-check" v-for="(motivo, index) in motivosNoDescontables" :key="'no-desc-' + index">
                  <input class="form-check-input" type="radio" :id="'no-desc-' + index" name="motivo" :value="motivo" v-model="solicitud_permiso.motivo">
                  <label class="form-check-label" :for="'no-desc-' + index">
                    {{ motivo }}
                  </label>
                </div>
              </div>
              <div class="mb-3">
                <label for="observacion" class="form-label">Observación</label>
                <textarea
                    class="form-control"
                    id="observacion"
                    v-model="solicitud_permiso.observacion"
                    rows="3"
                    placeholder="Ingresa tu observación"
                    maxlength="210">
                </textarea>
              </div>
              <div class="mb-3">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                <input type="datetime-local" class="form-control" id="fecha_inicio" v-model="solicitud_permiso.fecha_inicio">
              </div>
              <div class="mb-3">
                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                <input type="datetime-local" class="form-control" id="fecha_fin" v-model="solicitud_permiso.fecha_fin">
              </div>
              <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            </form>
            <!-- Formulario para selectedSolicitud === 2 -->
            <form v-if="selectedSolicitud === 2" @submit.prevent="enviarSolicitud">
              <div class="mb-3">
                <label class="form-label">Motivo</label>
                <div class="form-check" v-for="(motivo, index) in motivosReincorporacion" :key="'desc-' + index">
                  <input class="form-check-input" type="radio" :id="'desc-' + index" name="motivo" :value="motivo" v-model="solicitud_reincorporacion.motivo">
                  <label class="form-check-label" :for="'desc-' + index">
                    {{ motivo }}
                  </label>
                </div>
                <hr>
              </div>
              <div class="mb-3">
                <label for="observacion" class="form-label">Observación</label>
                <textarea
                    class="form-control"
                    id="observacion"
                    v-model="solicitud_reincorporacion.observacion"
                    rows="3"
                    placeholder="Ingresa tu observación"
                    maxlength="210">
                </textarea>
              </div>
              <div class="mb-3">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                <input type="datetime-local" class="form-control" id="fecha_inicio" v-model="solicitud_reincorporacion.fecha_inicio">
              </div>
              <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            </form>
            <!-- Formulario para selectedSolicitud === 3 -->
            <form v-if="selectedSolicitud === 3" @submit.prevent="enviarSolicitud">
              <div class="mb-3">
                <label for="duracion" class="form-label">Tiempo a utilizar</label>
                <div class="input-group">
                  <input type="number" class="form-control" id="duracion" v-model="solicitud_uso_tiempo.duracion" min="1" placeholder="Solicito el uso de">
                  <select class="form-select" v-model="solicitud_uso_tiempo.unidad">
                    <option value="horas">Horas</option>
                    <option value="días">Días</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label for="observacion" class="form-label">Observación</label>
                <textarea
                    class="form-control"
                    id="observacion"
                    v-model="solicitud_uso_tiempo.observacion"
                    rows="3"
                    placeholder="Ingresa tu observación"
                    maxlength="210">
                </textarea>
              </div>
              <div class="mb-3">
                <label for="fecha_inicio" class="form-label">Inicio del tiempo compensatorio</label>
                <input type="datetime-local" class="form-control" id="fecha_inicio" v-model="solicitud_uso_tiempo.fecha_inicio">
              </div>
              <div class="mb-3">
                <label for="fecha_fin" class="form-label">Finalización del tiempo compensatorio</label>
                <input type="datetime-local" class="form-control" id="fecha_fin" v-model="solicitud_uso_tiempo.fecha_fin">
              </div>

              <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            </form>
            <!-- Formulario para selectedSolicitud === 4 -->
            <form v-if="selectedSolicitud === 4" @submit.prevent="enviarSolicitud">
              <div class="mb-3">
                <label for="fecha_inicio_vacaciones" class="form-label">Fecha de Inicio</label>
                <input type="date" class="form-control" id="fecha_inicio_vacaciones" v-model="solicitud_vacaciones.fecha_inicio">
              </div>
              <div class="mb-3">
                <label for="salario_vacaciones" class="form-label">Salario</label>
                <input type="number" class="form-control" id="salario_vacaciones" v-model="solicitud_vacaciones.salario" min="0" step="0.01" placeholder="Ingrese su salario mensual">
              </div>
              <div class="mb-3">
                <label for="dias_vacaciones" class="form-label">Días</label>
                <input type="number" class="form-control" id="dias_vacaciones" v-model="solicitud_vacaciones.dias" min="1" placeholder="Ingrese los días de vacaciones">
              </div>
              <div class="mb-3">
                <label for="fecha_fin_vacaciones" class="form-label">Fecha de Fin</label>
                <input type="date" class="form-control" id="fecha_fin_vacaciones" v-model="solicitud_vacaciones.fecha_fin">
              </div>
              <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            </form>
            <!-- Formulario para selectedSolicitud === 5 -->
            <form v-if="selectedSolicitud === 5" @submit.prevent="enviarSolicitud">
              <div class="mb-3">
                <label for="fecha_inicio_horas_extras" class="form-label">Fecha de Inicio</label>
                <input type="datetime-local" class="form-control" id="fecha_inicio_horas_extras" v-model="solicitud_horas_extras.fecha_inicio">
              </div>
              <div class="mb-3">
                <label for="fecha_fin_horas_extras" class="form-label">Fecha de Fin</label>
                <input type="datetime-local" class="form-control" id="fecha_fin_horas_extras" v-model="solicitud_horas_extras.fecha_fin">
              </div>
              <div class="mb-3">
                <label for="trabajo_realizado" class="form-label">Trabajo Realizado</label>
                <textarea
                    class="form-control"
                    id="trabajo_realizado"
                    v-model="solicitud_horas_extras.trabajo_realizado"
                    rows="3"
                    placeholder="Descripción del trabajo que realizó durante este tiempo"
                    maxlength="210">
                </textarea>
              </div>
              <div class="mb-3">
                <label for="justificacion" class="form-label">Justificación</label>
                <textarea
                    class="form-control"
                    id="justificacion"
                    v-model="solicitud_horas_extras.justificacion"
                    rows="3"
                    placeholder="Causas que le impidieron realizar las labores antes descritas durante la jornada regular de trabajo."
                    maxlength="210">
                </textarea>
              </div>
              <div class="mb-3">
                <label for="observacion_horas_extras" class="form-label">Observación</label>
                <textarea
                    class="form-control"
                    id="observacion_horas_extras"
                    v-model="solicitud_horas_extras.observacion"
                    rows="3"
                    placeholder="Ingresa tu observación"
                    maxlength="210">
                </textarea>
              </div>
              <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { Modal } from 'bootstrap'
import Swal from 'sweetalert2'
import moment from 'moment'
import apiConfig from '@/config/api'
export default {
  name: 'Solicitudes',
  props: {
    tiposSolicitudes: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      solicitudes: [],
      showModal: false,
      selectedSolicitud: null,
      selectedSolicitudTitle: '',
      solicitud_permiso: {
        motivo: '',
        observacion: '',
        fecha_inicio: '',
        fecha_fin: '',
      },
      solicitud_reincorporacion: {
        motivo: '',
        observacion: '',
        fecha_inicio: ''
      },
      solicitud_uso_tiempo: {
        duracion: '',
        unidad: 'horas',
        observacion: '',
        fecha_inicio: '',
        fecha_fin: ''
      },
      solicitud_vacaciones: {
        fecha_inicio: '',
        salario: '',
        dias: '',
        fecha_fin: '',
      },
      solicitud_horas_extras: {
        fecha_inicio: '',
        fecha_fin: '',
        trabajo_realizado: '',
        justificacion: '',
        observacion: ''
      },
      motivosDescontables: [
        'Enfermedad', 'Duelo', 'Matrimonio', 'Nacimiento de hijos',
        'Enfermedades de parientes cercanos', 'Eventos academicos puntuales', 'Permisos personales'
      ],
      motivosNoDescontables: [
        'Mision oficial', 'Seminarios', 'Otros, especifique'
      ],
      motivosReincorporacion:[
        'Licencia con sueldo', 'Licencia sin sueldo', 'Licencia especial', 'Vacaciones'
      ],
      solicitudModalInstance: null
    }
  },
  created() {
    // MODIFICAR este método:
    if (this.tiposSolicitudes.length > 0) {
      // Si ya tenemos datos del padre, usarlos
      this.solicitudes = this.tiposSolicitudes
    } else {
      // Fallback: cargar como antes si no hay datos del padre
      this.fetchSolicitudes()
    }

    this.$nextTick(() => {
      this.solicitudModalInstance = new Modal(document.getElementById('solicitudModal'))
    })
  },
  methods: {
    async fetchSolicitudes() {
      try {
        const response = await axios.get(apiConfig.endpoints.listarSolicitud);
        this.solicitudes = response.data;
      } catch (error) {
        console.error('Error fetching solicitudes', error);
      }
    },
    handleCardClick(id) {
      this.selectedSolicitud = id;
      this.selectedSolicitudTitle = this.getSolicitudTitle(id);
      this.showModal = true;
      this.solicitudModalInstance.show();
    },
    closeModal() {
      this.showModal = false;
      this.solicitudModalInstance.hide();
    },
    getSolicitudTitle(id) {
      const solicitud = this.solicitudes.find(s => s.id_tipo_solicitud === id);
      return solicitud ? solicitud.tipo_solicitud : '';
    },
    validarFechas(fecha_inicio, fecha_fin) {
      if (new Date(fecha_fin) < new Date(fecha_inicio)) {
        Swal.fire({
          icon: 'error',
          title: 'Error en las fechas',
          text: 'La fecha de fin no puede ser antes de la fecha de inicio.',
          timer: 2000,
          showConfirmButton: false
        });
        return false;
      }
      return true;
    },
    validarTiempoCompensatorio(fecha_inicio, fecha_fin, duracion, unidad) {
      const inicio = moment(fecha_inicio, 'YYYY-MM-DD HH:mm');
      const fin = moment(fecha_fin, 'YYYY-MM-DD HH:mm');

      // Validar que las fechas sean válidas
      if (!inicio.isValid() || !fin.isValid()) {
        Swal.fire({
          icon: 'error',
          title: 'Error en las fechas',
          text: 'Las fechas ingresadas no son válidas.',
          timer: 2000,
          showConfirmButton: false
        });
        return false;
      }

      // Validar duración según la unidad
      if (unidad === 'horas') {
        const diffHoras = fin.diff(inicio, 'hours');

        if (diffHoras !== parseInt(duracion)) {
          Swal.fire({
            icon: 'error',
            title: 'Error en la duración',
            text: `La duración debe ser exactamente ${duracion} horas.`,
            timer: 2000,
            showConfirmButton: false
          });
          return false;
        }
      } else if (unidad === 'días') {
        const diffDias = fin.diff(inicio, 'days');
        if (diffDias !== parseInt(duracion)) {
          Swal.fire({
            icon: 'error',
            title: 'Error en la duración',
            text: `La duración debe ser exactamente ${duracion} días.`,
            timer: 2000,
            showConfirmButton: false
          });
          return false;
        }
      }

      return true;
    },

// También actualiza el método validarPermiso para que use correctamente los días:
    validarPermiso(fecha_inicio, fecha_fin) {
      const inicio = moment(fecha_inicio, 'YYYY-MM-DD HH:mm');
      const fin = moment(fecha_fin, 'YYYY-MM-DD HH:mm');

      // Validar que las fechas sean válidas
      if (!inicio.isValid() || !fin.isValid()) {
        Swal.fire({
          icon: 'error',
          title: 'Error en las fechas',
          text: 'Las fechas ingresadas no son válidas.',
          timer: 2000,
          showConfirmButton: false
        });
        return false;
      }

      const diasSemana = [1, 2, 3, 4, 5]; // Lunes a viernes
      const horaInicioPermitida = moment('08:30', 'HH:mm');
      const horaFinPermitida = moment('16:00', 'HH:mm');

      // Obtener el día de la semana (0 = domingo, 1 = lunes, ..., 6 = sábado)
      const diaInicio = inicio.day();
      const diaFin = fin.day();

      // Validar que las fechas no sean fines de semana
      if (!diasSemana.includes(diaInicio) || !diasSemana.includes(diaFin)) {
        console.log('Día inicio:', diaInicio, 'Día fin:', diaFin); // Para debugging
        Swal.fire({
          icon: 'error',
          title: 'Fecha no válida',
          text: 'No se puede seleccionar sábado o domingo.',
          timer: 2000,
          showConfirmButton: false
        });
        return false;
      }

      // Validar que las horas estén entre 08:30 y 16:00
      const horaInicio = moment(inicio.format('HH:mm'), 'HH:mm');
      const horaFin = moment(fin.format('HH:mm'), 'HH:mm');

      if (horaInicio.isBefore(horaInicioPermitida) || horaInicio.isAfter(horaFinPermitida) ||
          horaFin.isBefore(horaInicioPermitida) || horaFin.isAfter(horaFinPermitida)) {
        Swal.fire({
          icon: 'error',
          title: 'Hora no válida',
          text: 'La hora debe estar entre las 08:30 y las 16:00.',
          timer: 2000,
          showConfirmButton: false
        });
        return false;
      }

      return true;
    },
    formatFecha(fecha) {
      // Si la fecha ya viene en formato datetime-local (YYYY-MM-DDTHH:mm)
      if (fecha.includes('T')) {
        return fecha.replace('T', ' ');
      }

      // Si viene en otro formato, intentar parsearlo con moment
      const momentDate = moment(fecha);

      if (!momentDate.isValid()) {
        console.error('Fecha inválida:', fecha);
        return null;
      }

      return momentDate.format('YYYY-MM-DD HH:mm');
    },
    formatFechaVacaciones(fecha) {
      return moment(fecha).format('YYYY-MM-DD 00:00');
    },
    validarHorario(fecha_inicio, fecha_fin){
      const hora_inicio = moment(fecha_inicio, 'YYYY-MM-DD HH:mm').format('HH:mm');
      const hora_fin = moment(fecha_fin, 'YYYY-MM-DD HH:mm').format('HH:mm');

      if (hora_inicio === hora_fin) {
        Swal.fire({
          icon: 'warning',
          title: 'Advertencia',
          text: 'La hora no puede ser la misma.',
          timer: 2000,
          showConfirmButton: false
        });
        return false;
      }

      return true;
    },
    validarVacaciones(fecha_inicio, fecha_fin, duracion)
    {
      const inicio = moment(fecha_inicio, 'YYYY-MM-DD HH:mm');
      const fin = moment(fecha_fin, 'YYYY-MM-DD HH:mm');

      // Validar que las fechas coincidan con la duración
      const diffDias = fin.diff(inicio, 'days'); // +1 para incluir el día de inicio
      if (diffDias !== duracion) {
        console.log(diffDias, duracion)
        Swal.fire({
          icon: 'error',
          title: 'Duración no válida',
          text: `La duración debe ser exactamente ${duracion} días.`,
          timer: 2000,
          showConfirmButton: false
        });
        return false;
      }

      // Validar que la duración sea 15 o 30 días
      if (duracion !== 15 && duracion !== 30) {
        Swal.fire({
          icon: 'error',
          title: 'Duración no válida',
          text: 'La duración de las vacaciones debe ser de 15 o 30 días.',
          timer: 2000,
          showConfirmButton: false
        });
        return false;
      }

      return true;
    },
    validarHorasExtras(fecha_inicio, fecha_fin)
    {
      const inicio = moment(fecha_inicio, 'YYYY-MM-DD HH:mm');
      const fin = moment(fecha_fin, 'YYYY-MM-DD HH:mm');

      // Verificar si las fechas son el mismo día
      if (!inicio.isSame(fin, 'day')) {
        Swal.fire({
          icon: 'warning',
          title: 'Advertencia',
          text: 'Las horas extras deben ser solicitadas para el mismo día.',
          timer: 3000,
          showConfirmButton: false
        });
        return false;
      }

      // Obtener el día de la semana
      const diaSemana = inicio.day();

      // Si el día es sábado (6), no aplicar la validación del horario laboral
      if (diaSemana === 6) {
        return true;
      }

      // Definir horarios laborales permitidos
      const horaInicioLaboral = moment('08:30', 'HH:mm');
      const horaFinLaboral = moment('15:59', 'HH:mm');

      // Obtener las horas y minutos de las fechas de inicio y fin
      const horaInicio = moment(inicio.format('HH:mm'), 'HH:mm');
      const horaFin = moment(fin.format('HH:mm'), 'HH:mm');

      // Verificar si las horas están dentro del horario laboral
      if ((horaInicio.isBetween(horaInicioLaboral, horaFinLaboral, null, '[]')) ||
          (horaFin.isBetween(horaInicioLaboral, horaFinLaboral, null, '[]'))) {
        Swal.fire({
          icon: 'warning',
          title: 'Advertencia',
          text: 'No se puede solicitar horas extras dentro de horario laboral.',
          timer: 3000,
          showConfirmButton: false
        });
        return false;
      }

      return true;
    },

    async enviarSolicitud() {
      try {
        let solicitudData;

        if (this.selectedSolicitud === 1) {
          // Formatear fechas al formato yyyy-dd-mm HH:mm
          const fecha_inicio = this.formatFecha(this.solicitud_permiso.fecha_inicio);
          const fecha_fin = this.formatFecha(this.solicitud_permiso.fecha_fin);

          // Validar fechas
          if (!this.validarFechas(fecha_inicio, fecha_fin)) {
            return;
          }

          if (!this.validarPermiso(fecha_inicio, fecha_fin)) {
            return;
          }

          solicitudData = {

            motivo: this.solicitud_permiso.motivo,
            observacion: this.solicitud_permiso.observacion,
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            tipo_solicitud: this.selectedSolicitud
          };
        } else if (this.selectedSolicitud === 2) {
          const fecha_inicio = this.formatFecha(this.solicitud_reincorporacion.fecha_inicio);

          solicitudData = {

            motivo: this.solicitud_reincorporacion.motivo,
            observacion: this.solicitud_reincorporacion.observacion,
            fecha_inicio: fecha_inicio,
            tipo_solicitud: this.selectedSolicitud
          };
        } else if (this.selectedSolicitud === 3) {
          // Verificar que las fechas no estén vacías
          if (!this.solicitud_uso_tiempo.fecha_inicio || !this.solicitud_uso_tiempo.fecha_fin) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Por favor complete todas las fechas',
              timer: 2000,
              showConfirmButton: false
            });
            return;
          }

          // Log para debugging
          console.log('Fechas originales:', {
            inicio: this.solicitud_uso_tiempo.fecha_inicio,
            fin: this.solicitud_uso_tiempo.fecha_fin
          });

          // Formatear fechas al formato yyyy-MM-dd HH:mm
          const fecha_inicio = this.formatFecha(this.solicitud_uso_tiempo.fecha_inicio);
          const fecha_fin = this.formatFecha(this.solicitud_uso_tiempo.fecha_fin);

          console.log('Fechas formateadas:', {
            inicio: fecha_inicio,
            fin: fecha_fin
          });

          // Validar tiempo compensatorio
          if (!this.validarTiempoCompensatorio(fecha_inicio, fecha_fin, parseInt(this.solicitud_uso_tiempo.duracion), this.solicitud_uso_tiempo.unidad)) {
            return;
          }

          // Validar que no sea fin de semana
          if (!this.validarPermiso(fecha_inicio, fecha_fin)) {
            return;
          }

          solicitudData = {
            observacion: this.solicitud_uso_tiempo.observacion,
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            tiempo_utilizado: `${this.solicitud_uso_tiempo.duracion} ${this.solicitud_uso_tiempo.unidad}`,
            tipo_solicitud: this.selectedSolicitud
          };
        } else if (this.selectedSolicitud === 4) {
          // Validar que todos los campos estén llenos
          if (!this.solicitud_vacaciones.fecha_inicio || !this.solicitud_vacaciones.fecha_fin ||
              !this.solicitud_vacaciones.salario || !this.solicitud_vacaciones.dias) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Por favor complete todos los campos',
              timer: 2000,
              showConfirmButton: false
            });
            return;
          }

          const fecha_inicio = this.formatFechaVacaciones(this.solicitud_vacaciones.fecha_inicio);
          const fecha_fin = this.formatFechaVacaciones(this.solicitud_vacaciones.fecha_fin);

          // Validar fechas
          if (!this.validarFechas(fecha_inicio, fecha_fin)) {
            return;
          }

          // Convertir días a número
          const dias = parseInt(this.solicitud_vacaciones.dias);

          if (!this.validarVacaciones(fecha_inicio, fecha_fin, dias)) {
            return;
          }

          solicitudData = {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            salario: parseFloat(this.solicitud_vacaciones.salario),
            dias: dias,
            tipo_solicitud: this.selectedSolicitud
          };

          console.log('Datos de vacaciones a enviar:', solicitudData);
        } else if (this.selectedSolicitud === 5) {
          const fecha_inicio = this.formatFecha(this.solicitud_horas_extras.fecha_inicio);
          const fecha_fin = this.formatFecha(this.solicitud_horas_extras.fecha_fin);

          // Validar fechas
          if (!this.validarFechas(fecha_inicio, fecha_fin)) {
            return;
          }

          if (!this.validarHorasExtras(fecha_inicio, fecha_fin)) {
            return;
          }

          if (!this.validarHorario(fecha_inicio, fecha_fin)) {
            return;
          }

          solicitudData = {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            trabajo_realizado: this.solicitud_horas_extras.trabajo_realizado,
            justificacion: this.solicitud_horas_extras.justificacion,
            observacion: this.solicitud_horas_extras.observacion,
            tipo_solicitud: this.selectedSolicitud
          };
        }

        const response = await axios.post(apiConfig.endpoints.insertarSolicitud, solicitudData);
        if (response.data.status) {
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: response.data.message,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            this.closeModal();
            // this.descargarDocumento(solicitudData);
            setTimeout(() => {
              location.reload();
            }, 700); // Retraso de 3 segundos
          });
        }
      } catch (error) {
        console.error('Error al enviar la solicitud', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un problema al enviar la solicitud'
        });
      }
    },
  }
}
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}
.card-header {
  background-color: #f8f9fa;
}
.card {
  width: 100%;
}
.card-body {
  padding: 1rem;
}
.clickable-card {
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}
.clickable-card:hover {
  transform: scale(1.02);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}
.modal-container {
  background: white;
  padding: 20px;
  border-radius: 8px;
  max-width: 500px;
  width: 100%;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-title {
  margin: 0;
}
.btn-close {
  border: none;
  font-size: 1rem;
  cursor: pointer;
  color: #000; /* Asegúrate de que el botón de cierre sea visible */
  opacity: 1; /* Asegurarse de que el botón sea completamente visible */
}
</style>