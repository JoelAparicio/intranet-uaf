<template>
  <div class="container-fluid p-0">
    <div class="main-content p-4">
      <!-- Loading State -->
      <div v-if="isLoading" class="calendar-skeleton">
        <div class="skeleton-header mb-4">
          <div class="skeleton-box" style="width: 200px; height: 40px;"></div>
          <div class="skeleton-box" style="width: 250px; height: 36px;"></div>
          <div class="skeleton-box" style="width: 300px; height: 36px;"></div>
        </div>
        <div class="skeleton-calendar">
          <div v-for="i in 35" :key="i" class="skeleton-day">
            <div class="skeleton-box" style="width: 100%; height: 80px;"></div>
          </div>
        </div>
      </div>

      <!-- Calendar Component -->
      <div v-else class="calendar-wrapper">
        <FullCalendar
            ref="calendar"
            :options="calendarOptions"
        >
          <template v-slot:eventContent="arg">
            <div class="custom-event" :title="`${arg.event.title} - ${arg.event.extendedProps.departamento}`">
              <div class="event-content">
                <span class="event-initials">{{ getInitials(arg.event.title) }}</span>
                <span class="event-name">{{ getShortName(arg.event.title) }}</span>
              </div>
            </div>
          </template>
        </FullCalendar>
      </div>

      <!-- Empty State -->
      <div v-if="!isLoading && showEmptyState" class="empty-state mt-3">
        <h6>No hay cumpleaños este mes</h6>
        <p class="text-muted small mb-2">¡Sé el primero en agregar un cumpleaños!</p>
        <button class="btn btn-primary btn-sm" @click="showModal = true">
          <font-awesome-icon :icon="['fas', 'plus']" class="me-2" />
          Agregar Cumpleaños
        </button>
      </div>

      <!-- Modal para agregar cumpleaños -->
      <div v-if="showModal" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <font-awesome-icon :icon="['fas', 'birthday-cake']" class="me-2 text-primary" />
                Agregar Cumpleaños
              </h5>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="addBirthday">
                <div class="mb-3">
                  <label for="nombre" class="form-label">
                    <font-awesome-icon :icon="['fas', 'user']" class="me-2 text-muted" />
                    Nombre
                  </label>
                  <input
                      type="text"
                      class="form-control"
                      id="nombre"
                      v-model="newBirthday.nombre"
                      placeholder="Ingrese el nombre completo"
                      required
                  >
                </div>

                <div class="mb-3">
                  <label for="departamento" class="form-label">
                    <font-awesome-icon :icon="['fas', 'building']" class="me-2 text-muted" />
                    Departamento
                  </label>
                  <div class="position-relative">
                    <select
                        name="departamento"
                        v-model="newBirthday.departamento"
                        id="departamento"
                        class="form-control"
                        required
                    >
                      <option value="">Selecciona un departamento</option>
                      <option
                          v-for="dept in departamentos"
                          :key="dept.id"
                          :value="dept.id"
                      >
                        {{ dept.nombre }}
                      </option>
                    </select>
                    <div
                        v-if="newBirthday.departamento"
                        class="color-indicator"
                        :style="`background-color: ${getDepartmentColor(newBirthday.departamento)}`"
                    ></div>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="birthday" class="form-label">
                    <font-awesome-icon :icon="['fas', 'calendar-alt']" class="me-2 text-muted" />
                    Fecha de Cumpleaños
                  </label>
                  <input
                      type="date"
                      class="form-control"
                      id="birthday"
                      v-model="newBirthday.birthday"
                      required
                  >
                </div>

                <div class="d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-secondary" @click="closeModal">
                    Cancelar
                  </button>
                  <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                    <font-awesome-icon v-else :icon="['fas', 'check']" class="me-2" />
                    {{ isSubmitting ? 'Guardando...' : 'Guardar' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div v-if="showModal" class="modal-backdrop fade show"></div>

      <!-- Modal para directorio de colaboradores -->
      <div v-if="showDirectorioModal" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <font-awesome-icon :icon="['fas', 'users']" class="me-2 text-primary" />
                Directorio de Colaboradores
              </h5>
              <button type="button" class="btn-close" @click="closeDirectorio"></button>
            </div>
            <div class="modal-body">
              <!-- Controls -->
              <div class="mb-4">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="position-relative">
                      <font-awesome-icon :icon="['fas', 'search']" class="search-icon" />
                      <input
                          type="text"
                          class="form-control ps-5"
                          v-model="searchTerm"
                          placeholder="Buscar por nombre o correo..."
                      >
                      <span v-if="searchTerm" class="search-results">
                        {{ filteredDirectorio.length }} resultados
                      </span>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <select class="form-control" v-model="filterDepartamento">
                      <option value="">Todos los Departamentos</option>
                      <option v-for="dept in departamentos" :key="dept.id" :value="dept.nombre">
                        {{ dept.nombre }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <div class="btn-group w-100" role="group">
                      <button
                          type="button"
                          class="btn btn-outline-secondary"
                          :class="{ active: viewMode === 'cards' }"
                          @click="viewMode = 'cards'"
                          title="Vista de tarjetas"
                      >
                        <font-awesome-icon :icon="['fas', 'th-large']" />
                      </button>
                      <button
                          type="button"
                          class="btn btn-outline-secondary"
                          :class="{ active: viewMode === 'table' }"
                          @click="viewMode = 'table'"
                          title="Vista de tabla"
                      >
                        <font-awesome-icon :icon="['fas', 'table']" />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Department Filters -->
                <div v-if="filterDepartamento" class="mt-3">
                  <span class="badge bg-primary me-2">
                    {{ filterDepartamento }}
                    <button @click="filterDepartamento = ''" class="btn-close btn-close-white ms-2" style="font-size: 0.7rem;"></button>
                  </span>
                </div>
              </div>

              <!-- Directory Content -->
              <div class="directory-content">
                <!-- Loading State -->
                <div v-if="isLoadingDirectorio" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando directorio...</span>
                  </div>
                  <p class="mt-2 text-muted">Cargando directorio de colaboradores...</p>
                </div>

                <!-- Cards View -->
                <div v-else-if="viewMode === 'cards'" class="row g-3">
                  <div
                      v-for="usuario in filteredDirectorio"
                      :key="usuario.correo_electronico"
                      class="col-md-6 col-lg-4"
                  >
                    <div class="card h-100 border-0 shadow-sm">
                      <div class="card-body d-flex align-items-center">
                        <div class="avatar-circle me-3">
                          {{ getInitials(usuario.nombre) }}
                        </div>
                        <div class="flex-grow-1">
                          <h6 class="mb-1">{{ usuario.nombre }}</h6>
                          <p class="text-muted small mb-1">
                            <font-awesome-icon :icon="['fas', 'envelope']" class="me-1" />
                            {{ usuario.correo_electronico }}
                          </p>
                          <div class="d-flex align-items-center gap-2">
                            <span class="badge" :style="`background-color: ${getDepartmentColorByName(usuario.departamento)}20; color: ${getDepartmentColorByName(usuario.departamento)}`">
                              <font-awesome-icon :icon="['fas', 'building']" class="me-1" />
                              {{ usuario.departamento }}
                            </span>
                            <small class="text-muted">
                              <font-awesome-icon :icon="['fas', 'phone']" class="me-1" />
                              Ext. {{ usuario.extension }}
                            </small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Table View -->
                <div v-else-if="!isLoadingDirectorio" class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                    <tr>
                      <th>Colaborador</th>
                      <th>Correo Electrónico</th>
                      <th>Departamento</th>
                      <th>Extensión</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="usuario in filteredDirectorio" :key="usuario.correo_electronico">
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-circle-small me-2">
                            {{ getInitials(usuario.nombre) }}
                          </div>
                          {{ usuario.nombre }}
                        </div>
                      </td>
                      <td>{{ usuario.correo_electronico }}</td>
                      <td>
                          <span class="badge" :style="`background-color: ${getDepartmentColorByName(usuario.departamento)}20; color: ${getDepartmentColorByName(usuario.departamento)}`">
                            {{ usuario.departamento }}
                          </span>
                      </td>
                      <td>{{ usuario.extension }}</td>
                    </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Empty State -->
                <div v-if="!isLoadingDirectorio && filteredDirectorio.length === 0" class="text-center py-5">
                  <font-awesome-icon :icon="['fas', 'search']" class="text-muted mb-3" style="font-size: 3rem;" />
                  <h5>No se encontraron colaboradores</h5>
                  <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="showDirectorioModal" class="modal-backdrop fade show"></div>

      <!-- Toast Notifications -->
      <transition name="toast">
        <div v-if="showToast" class="toast-container position-fixed bottom-0 end-0 p-3">
          <div class="toast show" :class="`border-${toastType === 'success' ? 'success' : 'danger'}`">
            <div class="toast-body">
              <font-awesome-icon :icon="['fas', toastIcon]" :class="`text-${toastType === 'success' ? 'success' : 'danger'} me-2`" />
              {{ toastMessage }}
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import esLocale from '@fullcalendar/core/locales/es'
import axios from 'axios'
import Swal from 'sweetalert2'
import moment from 'moment'
import * as XLSX from 'xlsx'
import { mapGetters } from 'vuex'

export default defineComponent({
  components: {
    FullCalendar,
  },
  setup() {
    const calendarRef = ref(null);

    const getCalendarTitle = () => {
      if (calendarRef.value) {
        const calendarApi = calendarRef.value.getApi();
        return calendarApi.view.title;
      }
      return '';
    };

    onMounted(() => {
      getCalendarTitle();
    });

    return { calendarRef, getCalendarTitle };
  },

  // ✅ CORREGIDO: Añadir mapGetters para acceder al store
  computed: {
    ...mapGetters('auth', ['token', 'isAuthenticated']),

    filteredDirectorio() {
      return this.directorio.filter(usuario => {
        const matchesSearchTerm = usuario.nombre.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
            usuario.correo_electronico.toLowerCase().includes(this.searchTerm.toLowerCase());
        const matchesDepartamento = this.filterDepartamento ? usuario.departamento === this.filterDepartamento : true;
        return matchesSearchTerm && matchesDepartamento;
      });
    }
  },

  data() {
    return {
      isLoading: true,
      isSubmitting: false,
      isLoadingDirectorio: false,
      eventGuid: 0,
      showModal: false,
      showDirectorioModal: false,
      viewMode: 'cards',
      showToast: false,
      toastMessage: '',
      toastType: 'success',
      toastIcon: 'check-circle',
      showEmptyState: false,
      newBirthday: {
        nombre: '',
        departamento: '',
        birthday: ''
      },
      directorio: [],
      searchTerm: '',
      filterDepartamento: '',
      calendarTitle: '',
      departamentos: [
        { id: '1', nombre: 'Despacho superior', color: '#6366f1' },
        { id: '2', nombre: 'Secretaría', color: '#f59e0b' },
        { id: '3', nombre: 'Relaciones públicas', color: '#10b981' },
        { id: '4', nombre: 'Administración', color: '#06b6d4' },
        { id: '5', nombre: 'Recursos humanos', color: '#3b82f6' },
        { id: '6', nombre: 'Análisis operativo', color: '#8b5cf6' },
        { id: '7', nombre: 'Análisis estratégico', color: '#ec4899' },
        { id: '8', nombre: 'Asesoría legal', color: '#f97316' },
        { id: '9', nombre: 'Contact Center', color: '#14b8a6' },
        { id: '10', nombre: 'Cooperación nacional e internacional', color: '#ef4444' },
        { id: '11', nombre: 'Tecnología', color: '#64748b' }
      ],
      calendarOptions: {
        plugins: [
          dayGridPlugin,
          interactionPlugin
        ],
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: ''
        },
        views: {
          dayGridMonth: {
            dayHeaderContent: (arg) => {
              return arg.text.charAt(0).toUpperCase() + arg.text.slice(1);
            }
          }
        },
        initialView: 'dayGridMonth',
        locale: esLocale,
        editable: false,
        selectable: false,
        selectMirror: true,
        dayMaxEvents: false,
        eventDisplay: 'block',
        eventTimeFormat: {
          hour: 'numeric',
          minute: '2-digit',
          meridiem: false
        },
        datesSet: (dateInfo) => {
          this.checkEmptyState();
        },
        weekends: true,
        eventClick: this.handleEventClick,
        eventsSet: this.handleEvents,
        events: this.fetchEvents,
        eventDisplay: 'block',
        eventClassNames: 'custom-event-wrapper',
        dayCellClassNames: (arg) => {
          if (arg.isToday) {
            return ['today-cell'];
          }
          return [];
        },
        height: 'auto',
        aspectRatio: 1.8,
        expandRows: true,
      },
      currentEvents: [],
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    }
  },
  mounted() {
    // Verificar autenticación
    if (!this.isAuthenticated) {
      this.$router.push('/login');
      return;
    }

    // Simulate loading
    setTimeout(() => {
      this.isLoading = false;
      // Wait for next tick to ensure calendar is rendered
      this.$nextTick(() => {
        setTimeout(() => {
          this.addCustomButtons();
        }, 100);
      });
    }, 500);
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

    closeModal() {
      this.showModal = false;
      this.newBirthday = { nombre: '', departamento: '', birthday: '' };
    },
    closeDirectorio() {
      this.showDirectorioModal = false;
      this.searchTerm = '';
      this.filterDepartamento = '';
    },
    addCustomButtons() {
      if (!this.$refs.calendar) {
        console.warn('Calendar ref not available');
        return;
      }

      const calendarEl = this.$refs.calendar.$el;
      if (!calendarEl) {
        console.warn('Calendar element not available');
        return;
      }

      const toolbar = calendarEl.querySelector('.fc-toolbar-chunk:last-child');

      if (toolbar) {
        toolbar.innerHTML = `
          <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary btn-sm" id="addBirthdayBtn">
              <i class="fas fa-plus me-2"></i>Agregar Cumpleaños
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="directoryBtn">
              <i class="fas fa-users me-2"></i>Directorio
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadBtn">
              <i class="fas fa-download me-2"></i>Descargar Excel
            </button>
          </div>
        `;

        document.getElementById('addBirthdayBtn').addEventListener('click', () => {
          this.showModal = true;
        });

        document.getElementById('directoryBtn').addEventListener('click', () => {
          this.showDirectorio();
        });

        document.getElementById('downloadBtn').addEventListener('click', () => {
          this.downloadExcel();
        });
      }
    },
    getInitials(name) {
      const names = name.split(' ');
      if (names.length >= 2) {
        return names[0].charAt(0).toUpperCase() + names[1].charAt(0).toUpperCase();
      }
      return name.substring(0, 2).toUpperCase();
    },
    getShortName(name) {
      const names = name.split(' ');
      if (names.length >= 2) {
        return names[0] + ' ' + names[1].charAt(0) + '.';
      }
      return name;
    },
    getDepartmentColor(departmentId) {
      const dept = this.departamentos.find(d => d.id === departmentId);
      return dept ? dept.color : '#94a3b8';
    },
    getDepartmentColorByName(departmentName) {
      const dept = this.departamentos.find(d => d.nombre === departmentName);
      return dept ? dept.color : '#94a3b8';
    },
    showToastNotification(message, type = 'success') {
      this.toastMessage = message;
      this.toastType = type;
      this.toastIcon = type === 'success' ? 'check-circle' : 'exclamation-circle';
      this.showToast = true;

      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    },
    async handleEventClick(clickInfo) {
      const eventEl = clickInfo.el;
      eventEl.style.transform = 'scale(0.95)';
      setTimeout(() => {
        eventEl.style.transform = 'scale(1)';
      }, 100);

      const result = await Swal.fire({
        title: '¿Eliminar cumpleaños?',
        html: `
          <div class="text-center">
            <div class="mb-3">
              <div class="user-avatar-large mx-auto mb-2">
                ${this.getInitials(clickInfo.event.title)}
              </div>
              <h5>${clickInfo.event.title}</h5>
              <p class="text-muted">${clickInfo.event.extendedProps.departamento}</p>
            </div>
          </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
          popup: 'modern-swal'
        }
      });

      if (result.isConfirmed) {
        try {
          // ✅ CORREGIDO: URL y headers correctos
          await axios.put(`/birthdays/${clickInfo.event.id}`,
              { deleted: true },
              { headers: this.getAuthHeaders() }
          );

          clickInfo.event.remove();
          this.showToastNotification('Cumpleaños eliminado correctamente');

          setTimeout(() => {
            this.checkEmptyState();
          }, 100);
        } catch (error) {
          console.error('Error al eliminar cumpleaños:', error);

          if (await this.handleAuthError(error)) return;

          this.showToastNotification('Error al eliminar el cumpleaños', 'error');
        }
      }
    },
    handleEvents(events) {
      this.currentEvents = events;
      this.calendarTitle = this.getCalendarTitle();
      this.checkEmptyState();
    },
    checkEmptyState() {
      // Obtener el mes actual del calendario
      const calendarApi = this.$refs.calendar?.getApi();
      if (calendarApi) {
        const currentDate = calendarApi.getDate();
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();

        // Filtrar eventos del mes actual
        const eventsInCurrentMonth = this.currentEvents.filter(event => {
          const eventDate = new Date(event.start);
          return eventDate.getMonth() === currentMonth && eventDate.getFullYear() === currentYear;
        });

        this.showEmptyState = eventsInCurrentMonth.length === 0;
      }
    },
    async fetchEvents(fetchInfo, successCallback, failureCallback) {
      try {
        // ✅ CORREGIDO: URL y headers correctos
        const response = await axios.get('/birthdays', {
          headers: this.getAuthHeaders()
        });

        const events = response.data.map(event => ({
          id: event.id,
          title: event.nombre,
          start: event.birthday,
          allDay: true,
          backgroundColor: this.getDepartmentColorByName(event.departamento),
          borderColor: this.getDepartmentColorByName(event.departamento),
          extendedProps: {
            departamento: event.departamento
          }
        }));

        successCallback(events);

        // Verificar empty state después de cargar eventos
        this.$nextTick(() => {
          this.checkEmptyState();
        });
      } catch (error) {
        console.error('Error fetching events:', error);

        if (await this.handleAuthError(error)) return;

        if (typeof failureCallback === 'function') {
          failureCallback(error);
        }
      }
    },
    createEventId() {
      return String(this.eventGuid++)
    },
    async addBirthday() {
      if (this.isSubmitting) return;

      this.isSubmitting = true;

      try {
        const formattedBirthday = moment(this.newBirthday.birthday).format('YYYY-MM-DD');
        const birthdayData = {
          ...this.newBirthday,
          birthday: formattedBirthday
        };

        // ✅ CORREGIDO: URL y headers correctos
        await axios.post('/addBirthday', birthdayData, {
          headers: this.getAuthHeaders()
        });

        this.closeModal();
        this.$refs.calendar.getApi().refetchEvents();
        this.showToastNotification('Cumpleaños agregado correctamente');
      } catch (error) {
        console.error('Error al agregar cumpleaños:', error);

        if (await this.handleAuthError(error)) return;

        this.showToastNotification('Error al agregar el cumpleaños', 'error');
      } finally {
        this.isSubmitting = false;
      }
    },
    downloadExcel() {
      const events = this.currentEvents;
      const currentDate = this.$refs.calendar.getApi().getDate();
      const month = moment(currentDate).format('M');
      const monthName = this.monthNames[month - 1];

      const filteredEvents = events.filter(event => moment(event.start).format('M') === month);

      if (filteredEvents.length === 0) {
        this.showToastNotification('No hay cumpleaños para exportar este mes', 'error');
        return;
      }

      const worksheet = XLSX.utils.json_to_sheet(filteredEvents.map(event => ({
        Nombre: event.title,
        Departamento: event.extendedProps.departamento,
        Cumpleaños: moment(event.start).format('DD/MM/YYYY')
      })));

      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, 'Cumpleaños');
      XLSX.writeFile(workbook, `Cumpleaños_${monthName}_2025.xlsx`);

      this.showToastNotification('Excel descargado correctamente');
    },
    async showDirectorio() {
      this.showDirectorioModal = true;
      this.isLoadingDirectorio = true;

      try {
        // ✅ CORREGIDO: URL y headers correctos
        const response = await axios.get('/directorio_usuarios', {
          headers: this.getAuthHeaders()
        });

        this.directorio = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching directorio:', error);

        if (await this.handleAuthError(error)) return;

        this.showToastNotification('Error al cargar el directorio', 'error');
        this.closeDirectorio();
      } finally {
        this.isLoadingDirectorio = false;
      }
    }
  }
})
</script>

<style scoped>
/* General Styles */
.container-fluid {
  background-color: #f8fafc;
  min-height: 0;
  height: auto;
}

.main-content {
  max-width: 1600px;
  margin: 0 auto;
  min-height: calc(100vh - 200px);
  padding: 2rem !important;
}

.calendar-wrapper {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 24px 32px;
  margin-bottom: 2rem;
  width: 100%;
}

/* Calendar Customization */
:deep(.fc) {
  font-family: inherit;
  max-height: 650px;
}

:deep(.fc-scrollgrid) {
  border: none !important;
}

:deep(.fc-daygrid-body) {
  width: 100% !important;
}

:deep(.fc-scrollgrid-sync-table) {
  height: auto !important;
}

:deep(.fc-toolbar) {
  margin-bottom: 28px;
  flex-wrap: wrap;
  gap: 12px;
}

:deep(.fc-toolbar-title) {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
}

:deep(.fc-button) {
  background-color: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-weight: 500;
  padding: 6px 16px;
  transition: all 0.2s;
}

:deep(.fc-button:hover) {
  background-color: #e2e8f0;
  border-color: #cbd5e1;
}

:deep(.fc-button-active) {
  background-color: #3b82f6 !important;
  border-color: #3b82f6 !important;
  color: white !important;
}

:deep(.fc-today-button) {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

:deep(.fc-today-button:hover) {
  background-color: #2563eb;
  border-color: #2563eb;
}

/* Calendar Grid - Fixed heights */
:deep(.fc-daygrid) {
  overflow: hidden;
}

:deep(.fc-daygrid-day-frame) {
  min-height: 70px;
  max-height: 90px;
}

:deep(.fc-daygrid-day-events) {
  margin-top: 2px;
}

:deep(.fc-daygrid-event-harness) {
  margin-bottom: 1px;
}
:deep(.fc-daygrid-day) {
  transition: background-color 0.2s;
}

:deep(.fc-daygrid-day:hover) {
  background-color: #f8fafc;
}

:deep(.fc-daygrid-day-number) {
  font-weight: 500;
  color: #64748b;
  padding: 8px;
}

:deep(.today-cell) {
  background-color: #eff6ff !important;
}

:deep(.today-cell .fc-daygrid-day-number) {
  color: #3b82f6;
  font-weight: 700;
}

:deep(.fc-day-today) {
  background-color: #eff6ff !important;
}

/* Custom Event Styles */
:deep(.custom-event-wrapper) {
  margin: 2px !important;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

:deep(.custom-event-wrapper:hover) {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.custom-event {
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.7rem;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  line-height: 1.2;
}

.event-content {
  display: flex;
  align-items: center;
  gap: 4px;
}

.event-initials {
  background-color: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  width: 16px;
  height: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.5rem;
  font-weight: 600;
  flex-shrink: 0;
}

.event-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.7rem;
}

/* Loading Skeleton */
.calendar-skeleton {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.skeleton-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.skeleton-calendar {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  margin-top: 24px;
}

.skeleton-day {
  aspect-ratio: 1;
}

.skeleton-box {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 8px;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Empty State - Smaller */
.empty-state {
  text-align: center;
  padding: 25px 20px;
  background: #f8fafc;
  border-radius: 12px;
  border: 2px dashed #e2e8f0;
}

.empty-state h6 {
  color: #475569;
  margin-bottom: 8px;
  font-weight: 600;
}

.empty-state .small {
  font-size: 0.875rem;
}

/* Avatar Styles */
.avatar-circle {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.125rem;
  flex-shrink: 0;
}

.avatar-circle-small {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.75rem;
}

.user-avatar-large {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.5rem;
}

/* Form Controls */
.color-indicator {
  position: absolute;
  right: 40px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  border-radius: 4px;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

/* Search Styles */
.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-results {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.875rem;
  color: #64748b;
}

/* Toast Styles */
.toast-container {
  z-index: 1055;
}

.toast {
  background-color: white;
  border-radius: 8px;
  border-left-width: 4px;
  min-width: 300px;
}

/* Animations */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.toast-leave-to {
  opacity: 0;
}

/* Custom Button Group */
.btn-group .btn {
  border-radius: 0;
}

.btn-group .btn:first-child {
  border-radius: 6px 0 0 6px;
}

.btn-group .btn:last-child {
  border-radius: 0 6px 6px 0;
}

.btn-outline-primary {
  color: #3b82f6;
  border-color: #3b82f6;
}

.btn-outline-primary:hover {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.btn-outline-secondary.active {
  background-color: #64748b;
  border-color: #64748b;
  color: white;
}

/* Modal Overrides for Bootstrap Compatibility */
.modal.show {
  display: block !important;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1040;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1050;
  width: 100%;
  height: 100%;
  overflow-x: hidden;
  overflow-y: auto;
  outline: 0;
}

/* SweetAlert Customization */
:deep(.modern-swal) {
  border-radius: 16px !important;
  padding: 32px !important;
}

:deep(.swal2-title) {
  font-size: 1.25rem !important;
  font-weight: 600 !important;
}

:deep(.swal2-html-container) {
  font-size: 0.95rem !important;
}

/* Directory Cards */
.directory-content {
  max-height: 60vh;
  overflow-y: auto;
}

/* Spinner styles */
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .calendar-wrapper {
    padding: 16px;
  }

  :deep(.fc-toolbar) {
    flex-direction: column;
    align-items: stretch;
  }

  :deep(.fc-toolbar-chunk) {
    display: flex;
    justify-content: center;
    margin: 4px 0;
  }

  .modal-dialog {
    margin: 0.5rem;
  }

  .btn-group {
    flex-direction: column;
    width: 100%;
  }

  .btn-group .btn {
    border-radius: 6px !important;
    margin-bottom: 8px;
  }
}

/* Print Styles */
@media print {
  .btn-group,
  .fc-button-group,
  .modal,
  .modal-backdrop {
    display: none !important;
  }

  .calendar-wrapper {
    box-shadow: none;
    padding: 0;
  }
}
</style>