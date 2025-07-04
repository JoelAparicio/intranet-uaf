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
import { apiCall } from '@/utils/apiHelper';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { mapGetters } from 'vuex';

export default {
  name: 'ListaUsuarioRol',
  components: {
    FontAwesomeIcon
  },
  computed: {
    ...mapGetters('auth', ['token', 'isAuthenticated', 'user']),

    filteredUsers() {
      return this.users.filter(user => {
        const matchesSearch = this.searchTerm === '' ||
            user.nombre.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
            user.correo_electronico.toLowerCase().includes(this.searchTerm.toLowerCase());
        const matchesRole = this.selectedRole === '' || user.roles.includes(this.selectedRole);
        const matchesStatus = this.selectedStatus === '' || user.estado === this.selectedStatus;

        return matchesSearch && matchesRole && matchesStatus;
      });
    },
    hasActiveFilters() {
      return this.searchTerm || this.selectedRole || this.selectedStatus;
    },
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.itemsPerPage);
    },
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredUsers.slice(start, end);
    },
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage;
    },
    endIndex() {
      return Math.min(this.startIndex + this.itemsPerPage, this.filteredUsers.length);
    },
    visiblePages() {
      const delta = 2;
      const range = [];
      const rangeWithDots = [];
      let l;

      for (let i = 1; i <= this.totalPages; i++) {
        if (i === 1 || i === this.totalPages || (i >= this.currentPage - delta && i <= this.currentPage + delta)) {
          range.push(i);
        }
      }

      range.forEach((i) => {
        if (l) {
          if (i - l === 2) {
            rangeWithDots.push(l + 1);
          } else if (i - l !== 1) {
            rangeWithDots.push('...');
          }
        }
        rangeWithDots.push(i);
        l = i;
      });

      return rangeWithDots;
    }
  },
  data() {
    return {
      openDropdownId: null,
      searchTerm: '',
      selectedRole: '',
      selectedStatus: '',
      roles: [],
      users: [],
      currentUser: {},
      showEditUserModal: false,
      editUserData: {
        id: null,
        nombre: '',
        correo_electronico: '',
        cargo: '',
        posicion: '',
        cedula: '',
        extension: '',
        departamento: '',
        tiempo_extra: 0
      },
      viewMode: 'table',
      currentPage: 1,
      itemsPerPage: 10,
      loading: true,
      isUpdatingUser: false
    };
  },
  watch: {
    filteredUsers() {
      this.currentPage = 1;
    }
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

    getDropdownPosition(userId) {
      const userIndex = this.paginatedUsers.findIndex(u => u.id === userId);
      const isLastRows = userIndex >= this.paginatedUsers.length - 2;

      if (isLastRows || this.viewMode === 'cards') {
        return {
          bottom: '100%',
          top: 'auto',
          marginBottom: '0.125rem',
          marginTop: '0'
        };
      }

      return {
        top: '100%',
        bottom: 'auto',
        marginTop: '0.125rem',
        marginBottom: '0'
      };
    },

    toggleDropdown(userId) {
      if (this.openDropdownId === userId) {
        this.openDropdownId = null;
      } else {
        this.openDropdownId = userId;
      }
    },

    handleEditUser(user) {
      this.openDropdownId = null;
      this.openEditUserModal(user);
    },

    handleToggleStatus(user) {
      this.openDropdownId = null;
      this.toggleUserStatus(user);
    },

    handleDeleteUser(user) {
      this.openDropdownId = null;
      this.confirmDeleteUser(user);
    },

    // ===== API CALLS CON APIHELPER =====
    async fetchUsers() {
      try {
        this.loading = true;

        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        // ✅ USANDO APIHELPER
        const response = await apiCall.get('administrarUsuarios', {
          headers: this.getAuthHeaders()
        });

        if (response.data.status) {
          this.users = response.data.data;
          this.roles = [...new Set(this.users.flatMap(user => user.roles))];

          const activeUsers = this.users.filter(u => u.estado === 'Activo').length;
          this.$emit('update-stats', {
            totalUsers: this.users.length,
            activeUsers: activeUsers
          });
        }
      } catch (error) {
        console.error('Error fetching users:', error);

        if (await this.handleAuthError(error)) return;

        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar los usuarios',
          timer: 2000,
          showConfirmButton: false
        });
      } finally {
        this.loading = false;
      }
    },

    async fetchCurrentUser() {
      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        // ✅ USANDO APIHELPER
        const response = await apiCall.get('user', {
          headers: this.getAuthHeaders()
        });

        this.currentUser = response.data.data;
      } catch (error) {
        console.error('Error fetching current user:', error);
        await this.handleAuthError(error);
      }
    },

    async deleteUser(userId, actionUserId) {
      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        // ✅ USANDO APIHELPER
        await apiCall.putWithId('borrarUsuario', userId, {
          action_user_id: actionUserId
        }, {
          headers: this.getAuthHeaders()
        });

        await this.fetchUsers();

        this.$swal.fire({
          icon: 'success',
          title: 'Eliminado!',
          text: 'El usuario ha sido eliminado.',
          timer: 1500,
          showConfirmButton: false
        });
      } catch (error) {
        console.error('Error deleting user:', error);

        if (await this.handleAuthError(error)) return;

        this.$swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'No se pudo eliminar el usuario.'
        });
      }
    },

    async updateUser() {
      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        this.isUpdatingUser = true;

        // ✅ USANDO APIHELPER
        await apiCall.putWithId('actualizarUsuario', this.editUserData.id, this.editUserData, {
          headers: this.getAuthHeaders()
        });

        await this.fetchUsers();
        this.closeEditUserModal();

        this.$swal.fire({
          title: 'Actualizado!',
          text: 'El usuario ha sido actualizado.',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false
        });
      } catch (error) {
        console.error('Error updating user:', error);

        if (await this.handleAuthError(error)) return;

        this.$swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'No se pudo actualizar el usuario.'
        });
      } finally {
        this.isUpdatingUser = false;
      }
    },

    async toggleUserStatus(user) {
      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        const newState = user.estado === 'Activo' ? 'inactivo' : 'activo';

        // ✅ USANDO APIHELPER
        await apiCall.putWithId('statusUsuario', user.id, {
          estado: newState
        }, {
          headers: this.getAuthHeaders()
        });

        await this.fetchUsers();

        this.$swal.fire({
          title: 'Status actualizado!',
          text: `El usuario ahora está ${newState}.`,
          icon: 'success',
          timer: 1500,
          showConfirmButton: false
        });
      } catch (error) {
        console.error('Error toggling user status:', error);

        if (await this.handleAuthError(error)) return;

        this.$swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'No se pudo cambiar el estado del usuario.'
        });
      }
    },

    confirmDeleteUser(user) {
      if (user.id === this.currentUser.id) {
        this.$swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'No puedes borrarte a ti mismo.'
        });
        return;
      }

      this.$swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción será permanente y no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          this.deleteUser(user.id, this.currentUser.id);
        }
      });
    },

    openEditUserModal(user) {
      this.editUserData = { ...user };
      this.showEditUserModal = true;
    },

    closeEditUserModal() {
      this.showEditUserModal = false;
      this.isUpdatingUser = false;
    },

    getRoleColor(role) {
      if (!role) return '#6c757d';
      const lowerRole = role.toLowerCase();
      if (lowerRole.includes('administrador')) return '#dc3545';
      if (lowerRole.includes('jefe') || lowerRole.includes('director')) return '#007bff';
      if (lowerRole.includes('analista')) return '#28a745';
      if (lowerRole.includes('aseso')) return '#6f42c1';
      return '#6c757d';
    },

    clearFilters() {
      this.searchTerm = '';
      this.selectedRole = '';
      this.selectedStatus = '';
    },

    changePage(page) {
      if (page !== '...' && page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },

    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },

    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    }
  },

  async created() {
    if (!this.isAuthenticated) {
      this.$router.push('/login');
      return;
    }

    await this.fetchUsers();
    await this.fetchCurrentUser();
  },

  mounted() {
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.custom-dropdown')) {
        this.openDropdownId = null;
      }
    });
  },

  beforeUnmount() {
    document.removeEventListener('click', () => {});
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