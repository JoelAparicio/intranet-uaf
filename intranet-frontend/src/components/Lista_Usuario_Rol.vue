<template>
  <div class="container mt-4 mb-4">
    <div class="card shadow-sm no-animation">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h2>Administración de usuarios</h2>
          <p class="text-muted mb-0">Gestiona los usuarios y sus permisos en el sistema</p>
        </div>
        <div class="view-toggle">
          <button
              class="toggle-btn"
              :class="{ active: viewMode === 'table' }"
              @click="viewMode = 'table'"
              title="Vista de tabla"
          >
            <font-awesome-icon :icon="['fas', 'table']" />
          </button>
          <button
              class="toggle-btn"
              :class="{ active: viewMode === 'cards' }"
              @click="viewMode = 'cards'"
              title="Vista de tarjetas"
          >
            <font-awesome-icon :icon="['fas', 'th-large']" />
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- Filtros mejorados -->
        <div class="filters-section mb-4">
          <div class="search-container">
            <font-awesome-icon :icon="['fas', 'search']" class="search-icon" />
            <input
                type="text"
                class="form-control search-input"
                placeholder="Buscar por nombre o correo electrónico"
                v-model="searchTerm"
                style="padding-left: 40px;"
            />
          </div>
          <div class="filter-chips">
            <select
                class="form-select filter-select"
                v-model="selectedRole"
                style="padding-left: 20px;"
            >
              <option value="">Todos los roles</option>
              <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
            </select>
            <select
                class="form-select filter-select"
                v-model="selectedStatus"
                style="padding-left: 20px !important; padding-right: 40px !important;"
            >
              <option value="">Todos los estados</option>
              <option value="Activo">Activos</option>
              <option value="Inactivo">Inactivos</option>
            </select>
          </div>
        </div>

        <!-- Resumen de filtros activos -->
        <div v-if="hasActiveFilters" class="active-filters mb-3">
          <span class="filter-label">Filtros activos:</span>
          <span v-if="searchTerm" class="filter-chip">
            <font-awesome-icon :icon="['fas', 'search']" class="me-1" />
            {{ searchTerm }}
            <button @click="searchTerm = ''" class="chip-close">
              <font-awesome-icon :icon="['fas', 'times']" />
            </button>
          </span>
          <span v-if="selectedRole" class="filter-chip">
            <font-awesome-icon :icon="['fas', 'shield-alt']" class="me-1" />
            {{ selectedRole }}
            <button @click="selectedRole = ''" class="chip-close">
              <font-awesome-icon :icon="['fas', 'times']" />
            </button>
          </span>
          <span v-if="selectedStatus" class="filter-chip">
            <font-awesome-icon :icon="['fas', 'toggle-on']" class="me-1" />
            {{ selectedStatus }}
            <button @click="selectedStatus = ''" class="chip-close">
              <font-awesome-icon :icon="['fas', 'times']" />
            </button>
          </span>
          <button @click="clearFilters" class="btn btn-sm btn-link text-danger">
            Limpiar todo
          </button>
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading">
          <div v-if="viewMode === 'table'" class="skeleton-table">
            <div class="skeleton-row skeleton-header"></div>
            <div v-for="i in 5" :key="i" class="skeleton-row"></div>
          </div>
          <div v-else class="skeleton-cards">
            <div v-for="i in 6" :key="i" class="skeleton-card"></div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="filteredUsers.length === 0" class="empty-state">
          <font-awesome-icon :icon="['fas', 'users-slash']" class="empty-icon" />
          <h4>No se encontraron usuarios</h4>
          <p class="text-muted">
            {{ hasActiveFilters ? 'Intenta ajustar los filtros de búsqueda' : 'No hay usuarios registrados en el sistema' }}
          </p>
        </div>

        <!-- Vista de tabla -->
        <div v-else-if="viewMode === 'table'" class="table-container">
          <table class="table table-hover">
            <thead>
            <tr>
              <th>Usuario</th>
              <th>Rol</th>
              <th>Departamento</th>
              <th>Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in paginatedUsers" :key="user.id">
              <td>
                <div class="user-cell">
                  <div class="user-initial">{{ user.nombre.charAt(0).toUpperCase() }}</div>
                  <div>
                    <div class="user-name">{{ user.nombre }}</div>
                    <div class="user-email">{{ user.correo_electronico }}</div>
                  </div>
                </div>
              </td>
              <td>
                  <span class="role-badge" :style="{ backgroundColor: getRoleColor(user.roles[0]) + '20', color: getRoleColor(user.roles[0]) }">
                    {{ user.roles.join(', ') }}
                  </span>
              </td>
              <td>
                <span class="department-text">{{ user.departamento || 'Sin asignar' }}</span>
              </td>
              <td>
                  <span :class="user.estado === 'Activo' ? 'status-badge status-active' : 'status-badge status-inactive'">
                    <span class="status-dot"></span>
                    {{ user.estado }}
                  </span>
              </td>
              <td>
                <div class="actions-container">
                  <div class="custom-dropdown">
                    <button
                        class="btn btn-sm btn-light"
                        @click="toggleDropdown(user.id)"
                    >
                      <font-awesome-icon :icon="['fas', 'ellipsis-v']" />
                    </button>
                    <div v-if="openDropdownId === user.id" class="custom-dropdown-menu" :style="getDropdownPosition(user.id)">
                      <a class="dropdown-item" href="#" @click.prevent="handleEditUser(user)">
                        <font-awesome-icon :icon="['fas', 'user-pen']" class="me-2 text-primary" />
                        Editar información
                      </a>
                      <a class="dropdown-item" href="#" @click.prevent="handleToggleStatus(user)">
                        <font-awesome-icon :icon="user.estado === 'Activo' ? ['fas', 'toggle-off'] : ['fas', 'toggle-on']" class="me-2" :class="user.estado === 'Activo' ? 'text-warning' : 'text-success'" />
                        {{ user.estado === 'Activo' ? 'Desactivar' : 'Activar' }} usuario
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item text-danger" href="#" @click.prevent="handleDeleteUser(user)">
                        <font-awesome-icon :icon="['fas', 'trash']" class="me-2" />
                        Eliminar usuario
                      </a>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Vista de tarjetas -->
        <div v-else class="cards-container">
          <div v-for="user in paginatedUsers" :key="user.id" class="user-card">
            <div class="card-header-section">
              <div class="user-initial-large">{{ user.nombre.charAt(0).toUpperCase() }}</div>
              <div class="user-info-card">
                <h5 class="user-name-card">{{ user.nombre }}</h5>
                <p class="user-email-card">{{ user.correo_electronico }}</p>
              </div>
              <div class="custom-dropdown ms-auto">
                <button
                    class="btn btn-sm btn-light"
                    @click="toggleDropdown(user.id)"
                >
                  <font-awesome-icon :icon="['fas', 'ellipsis-v']" />
                </button>
                <div v-if="openDropdownId === user.id" class="custom-dropdown-menu" :style="getDropdownPosition(user.id)">
                  <a class="dropdown-item" href="#" @click.prevent="handleEditUser(user)">
                    <font-awesome-icon :icon="['fas', 'user-pen']" class="me-2 text-primary" />
                    Editar información
                  </a>
                  <a class="dropdown-item" href="#" @click.prevent="handleToggleStatus(user)">
                    <font-awesome-icon :icon="user.estado === 'Activo' ? ['fas', 'toggle-off'] : ['fas', 'toggle-on']" class="me-2" :class="user.estado === 'Activo' ? 'text-warning' : 'text-success'" />
                    {{ user.estado === 'Activo' ? 'Desactivar' : 'Activar' }} usuario
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="#" @click.prevent="handleDeleteUser(user)">
                    <font-awesome-icon :icon="['fas', 'trash']" class="me-2" />
                    Eliminar usuario
                  </a>
                </div>
              </div>
            </div>
            <div class="card-details">
              <div class="detail-item">
                <span class="detail-label">Rol:</span>
                <span class="role-badge" :style="{ backgroundColor: getRoleColor(user.roles[0]) + '20', color: getRoleColor(user.roles[0]) }">
                  {{ user.roles.join(', ') }}
                </span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Departamento:</span>
                <span>{{ user.departamento || 'Sin asignar' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Estado:</span>
                <span :class="user.estado === 'Activo' ? 'status-badge status-active' : 'status-badge status-inactive'">
                  <span class="status-dot"></span>
                  {{ user.estado }}
                </span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Cargo:</span>
                <span>{{ user.cargo || 'No especificado' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginación mejorada -->
        <nav v-if="totalPages > 1" aria-label="Page navigation" class="mt-4">
          <div class="pagination-info">
            Mostrando {{ startIndex + 1 }} - {{ endIndex }} de {{ filteredUsers.length }} usuarios
          </div>
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="previousPage">
                <font-awesome-icon :icon="['fas', 'chevron-left']" />
              </a>
            </li>
            <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: currentPage === page }">
              <a v-if="page !== '...'" class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              <span v-else class="page-link">...</span>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="nextPage">
                <font-awesome-icon :icon="['fas', 'chevron-right']" />
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Modal para editar usuario - Mejorado -->
    <div v-if="showEditUserModal" class="modal-overlay" @click.self="closeEditUserModal">
      <div class="modal-container">
        <div class="modal-header">
          <div>
            <h5 class="modal-title">Editar Usuario</h5>
            <p class="text-muted mb-0 small">Actualiza la información del usuario</p>
          </div>
          <button type="button" class="btn-close" @click="closeEditUserModal"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="updateUser">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="nombre" class="form-label">
                  <font-awesome-icon :icon="['fas', 'user']" class="me-1 text-muted" />
                  Nombre completo
                </label>
                <input type="text" class="form-control" id="nombre" v-model="editUserData.nombre" required />
              </div>
              <div class="col-md-6 mb-3">
                <label for="correo_electronico" class="form-label">
                  <font-awesome-icon :icon="['fas', 'envelope']" class="me-1 text-muted" />
                  Correo electrónico
                </label>
                <input type="email" class="form-control" id="correo_electronico" v-model="editUserData.correo_electronico" required />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="cargo" class="form-label">
                  <font-awesome-icon :icon="['fas', 'briefcase']" class="me-1 text-muted" />
                  Cargo
                </label>
                <input type="text" class="form-control" id="cargo" v-model="editUserData.cargo" required />
              </div>
              <div class="col-md-6 mb-3">
                <label for="posicion" class="form-label">
                  <font-awesome-icon :icon="['fas', 'sitemap']" class="me-1 text-muted" />
                  Posición
                </label>
                <input type="text" class="form-control" id="posicion" v-model="editUserData.posicion" required />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="cedula" class="form-label">
                  <font-awesome-icon :icon="['fas', 'id-card']" class="me-1 text-muted" />
                  Cédula
                </label>
                <input type="text" class="form-control" id="cedula" v-model="editUserData.cedula" required />
              </div>
              <div class="col-md-6 mb-3">
                <label for="extension" class="form-label">
                  <font-awesome-icon :icon="['fas', 'phone']" class="me-1 text-muted" />
                  Extensión
                </label>
                <input type="text" class="form-control" id="extension" v-model="editUserData.extension" required />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="departamento" class="form-label">
                  <font-awesome-icon :icon="['fas', 'building']" class="me-1 text-muted" />
                  Departamento
                </label>
                <input type="text" class="form-control" id="departamento" v-model="editUserData.departamento" />
              </div>
              <div class="col-md-6 mb-3">
                <label for="tiempo_extra" class="form-label">
                  <font-awesome-icon :icon="['fas', 'clock']" class="me-1 text-muted" />
                  Tiempo extra (horas)
                </label>
                <input type="number" class="form-control" id="tiempo_extra" v-model="editUserData.tiempo_extra" required min="0" />
              </div>
            </div>
            <div class="modal-footer px-0">
              <button type="button" class="btn btn-secondary" @click="closeEditUserModal">
                Cancelar
              </button>
              <button type="submit" class="btn btn-primary" :disabled="isUpdatingUser">
                <span v-if="isUpdatingUser" class="spinner-border spinner-border-sm me-2"></span>
                <font-awesome-icon v-else :icon="['fas', 'save']" class="me-2" />
                {{ isUpdatingUser ? 'Guardando...' : 'Guardar cambios' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { apiCall } from '@/utils/apiHelper';
import moment from 'moment';
import 'moment/locale/es';
import Swal from 'sweetalert2';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { mapGetters } from 'vuex';

export default {
  components: {
    FontAwesomeIcon
  },

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
    if (!this.isAuthenticated) {
      this.$router.push('/login');
      return;
    }

    await this.fetchUserRoles();
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

    async fetchUserRoles() {
      try {
        // ✅ USANDO APIHELPER
        const response = await apiCall.get('rolesUsuario', {
          headers: this.getAuthHeaders()
        });

        this.userRoles = response.data.roles || [];
        this.userId = response.data.id_usuario;

        // ✅ USANDO APIHELPER
        const userResponse = await apiCall.get('user', {
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
        // ✅ USANDO APIHELPER
        const response = await apiCall.get('listarAprobaciones', {
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
        // ✅ USANDO APIHELPER
        const response = await apiCall.putWithId('aprobarSolicitud', id_solicitud, {}, {
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
          // ✅ USANDO APIHELPER
          const response = await apiCall.putWithId('rechazarSolicitud', id_solicitud, {
            comentarios: comentarios.trim()
          }, {
            headers: this.getAuthHeaders()
          });

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
        // ✅ USANDO APIHELPER
        const response = await apiCall.post('obtenerRutaPdf', {
          id_solicitud
        }, {
          headers: this.getAuthHeaders()
        });

        if (response.data.status) {
          // Construir URL completa correctamente
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

/* Custom Dropdown */
.custom-dropdown {
  position: relative;
}

.custom-dropdown-menu {
  position: absolute;
  right: 0;
  z-index: 1000;
  min-width: 200px;
  padding: 0.5rem 0;
  background-color: #fff;
  border: 1px solid rgba(0, 0, 0, 0.15);
  border-radius: 0.375rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
}

.custom-dropdown-menu .dropdown-item {
  display: block;
  width: 100%;
  padding: 0.5rem 1rem;
  color: #212529;
  text-align: inherit;
  text-decoration: none;
  white-space: nowrap;
  background-color: transparent;
  border: 0;
  transition: all 0.2s;
}

.custom-dropdown-menu .dropdown-item:hover {
  background-color: #f8f9fa;
}

.custom-dropdown-menu .dropdown-divider {
  height: 0;
  margin: 0.5rem 0;
  overflow: hidden;
  border-top: 1px solid #e9ecef;
}

/* Card Base */
.card.no-animation {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card-header {
  background-color: #ffffff;
  border-bottom: 1px solid #e9ecef;
  padding: 1.5rem;
}

.card-header h2 {
  margin-bottom: 0.25rem;
  font-size: 1.5rem;
  font-weight: 600;
  color: #2c3e50;
}

.card-body {
  padding: 1.5rem;
}

/* View Toggle */
.view-toggle {
  display: flex;
  gap: 5px;
  background: #f8f9fa;
  padding: 4px;
  border-radius: 8px;
}

.toggle-btn {
  background: transparent;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  color: #6c757d;
  transition: all 0.2s;
  cursor: pointer;
}

.toggle-btn:hover {
  background: #e9ecef;
}

.toggle-btn.active {
  background: #fff;
  color: #007bff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Filters Section */
.filters-section {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
  align-items: center;
}

.search-container {
  position: relative;
  flex: 1;
  min-width: 300px;
}

.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
}

.search-input {
  padding-left: 40px;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  transition: all 0.2s;
}

.search-input:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
}

.filter-chips {
  display: flex;
  gap: 10px;
}

.filter-select {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  padding: 0.625rem 0.875rem;
  min-width: 150px;
  transition: all 0.2s;
}

.filter-select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
}

/* Active Filters */
.active-filters {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  padding: 10px;
  background: #f8f9fa;
  border-radius: 8px;
}

.filter-label {
  font-weight: 500;
  color: #6c757d;
  font-size: 14px;
}

.filter-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  background: #007bff;
  color: white;
  border-radius: 20px;
  font-size: 13px;
}

.chip-close {
  background: none;
  border: none;
  color: white;
  padding: 0;
  margin-left: 5px;
  cursor: pointer;
  font-size: 12px;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.chip-close:hover {
  opacity: 1;
}

/* Table Styles */
.table-container {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.table {
  margin: 0;
  border: 1px solid #e9ecef;
}

.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  padding: 1rem;
  font-weight: 600;
  color: #495057;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.5px;
}

.table tbody tr {
  transition: all 0.2s;
}

.table tbody tr:hover {
  background-color: #f8f9fa;
}

.table td {
  padding: 1rem;
  vertical-align: middle;
  border-bottom: 1px solid #e9ecef;
}

/* User Cell */
.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-initial, .user-initial-large {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
  flex-shrink: 0;
}

.user-initial-large {
  width: 60px;
  height: 60px;
  font-size: 24px;
}

.user-name, .user-name-card {
  font-weight: 500;
  color: #2c3e50;
  margin-bottom: 2px;
}

.user-email, .user-email-card {
  font-size: 13px;
  color: #6c757d;
  margin: 0;
}

/* Role Badge */
.role-badge {
  display: inline-block;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
}

/* Status Badge */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
}

.status-active {
  background-color: #d4edda;
  color: #155724;
}

.status-inactive {
  background-color: #f8d7da;
  color: #721c24;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: currentColor;
}

/* Actions */
.actions-container {
  display: flex;
  justify-content: center;
}

.dropdown-toggle {
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 6px 12px;
  transition: all 0.2s;
}

.dropdown-toggle:hover {
  background-color: #f8f9fa;
  border-color: #007bff;
}

.dropdown-menu {
  border: 1px solid #e9ecef;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 0.5rem 0;
}

.dropdown-item {
  padding: 0.5rem 1rem;
  font-size: 14px;
  transition: all 0.2s;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}

/* Cards View */
.cards-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.user-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.user-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.card-header-section {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e9ecef;
}

.user-info-card {
  flex: 1;
}

.user-name-card {
  margin: 0 0 5px 0;
  font-size: 18px;
}

.card-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
}

.detail-label {
  font-weight: 500;
  color: #6c757d;
  min-width: 100px;
}

/* Skeleton Loading */
.skeleton-table {
  background: white;
  border-radius: 8px;
  overflow: hidden;
}

.skeleton-row {
  height: 60px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  margin-bottom: 1px;
}

.skeleton-header {
  height: 50px;
  background-color: #f8f9fa;
}

.skeleton-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.skeleton-card {
  height: 200px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 12px;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  font-size: 64px;
  color: #dee2e6;
  margin-bottom: 20px;
}

.empty-state h4 {
  color: #6c757d;
  margin-bottom: 10px;
}

/* Pagination */
.pagination-info {
  text-align: center;
  color: #6c757d;
  font-size: 14px;
  margin-bottom: 15px;
}

.pagination {
  margin-top: 20px;
}

.pagination .page-item {
  margin: 0 3px;
}

.pagination .page-link {
  color: #007bff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 8px 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
}

.pagination .page-item.disabled .page-link {
  color: #6c757d;
  background-color: #f8f9fa;
  border-color: #dee2e6;
  cursor: not-allowed;
}

.pagination .page-item .page-link:hover:not(.active):not(.disabled) {
  background-color: #f8f9fa;
  color: #0056b3;
  border-color: #007bff;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-container {
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 650px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    transform: translateY(-30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #2c3e50;
}

.btn-close {
  border: none;
  background: none;
  font-size: 1.5rem;
  color: #6c757d;
  cursor: pointer;
  transition: color 0.2s;
}

.btn-close:hover {
  color: #000;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 0 0;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Form Styles in Modal */
.form-label {
  font-weight: 500;
  color: #495057;
  margin-bottom: 0.5rem;
  font-size: 14px;
}

.form-control {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 0.625rem 0.875rem;
  transition: all 0.2s;
  font-size: 14px;
}

.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
}

/* Button Styles */
.btn {
  border-radius: 8px;
  padding: 0.5rem 1.25rem;
  font-weight: 500;
  transition: all 0.2s;
  font-size: 14px;
}

.btn-primary {
  background-color: #007bff;
  border: none;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.btn-primary:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-secondary {
  background-color: #6c757d;
  border: none;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.btn-light {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  color: #495057;
}

.btn-light:hover {
  background-color: #e2e6ea;
  border-color: #dae0e5;
}

.btn-link {
  color: #007bff;
  text-decoration: none;
  padding: 0;
}

.btn-link:hover {
  color: #0056b3;
  text-decoration: underline;
}

/* Spinner */
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Department Text */
.department-text {
  color: #495057;
  font-size: 14px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .filters-section {
    flex-direction: column;
  }

  .search-container {
    min-width: 100%;
  }

  .filter-chips {
    width: 100%;
    justify-content: space-between;
  }

  .filter-select {
    flex: 1;
  }

  .cards-container {
    grid-template-columns: 1fr;
  }

  .table-container {
    overflow-x: auto;
  }

  .view-toggle {
    display: none;
  }

  .modal-container {
    width: 95%;
    margin: 20px;
  }
}

/* Transition Effects */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Icon animations */
.fa-lg {
  font-size: 1.5em;
  transition: all 0.2s;
}

.btn-action {
  transition: transform 0.2s;
}

.btn-action:hover {
  transform: scale(1.1);
}

/* Text utilities */
.text-primary {
  color: #007bff !important;
}

.text-danger {
  color: #dc3545 !important;
}

.text-success {
  color: #28a745 !important;
}

.text-warning {
  color: #ffc107 !important;
}

.text-muted {
  color: #6c757d !important;
}
</style>