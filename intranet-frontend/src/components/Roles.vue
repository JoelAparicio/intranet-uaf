<template>
  <div class="container mt-4">
    <!-- Card principal con animación de skeleton loader -->
    <div class="card shadow-sm no-animation mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h2>Administración de Roles</h2>
          <p class="text-muted mb-0">Gestiona los roles y permisos del sistema</p>
        </div>
        <div>
          <button class="btn btn-add-role me-2" @click="showAddRoleModal">
            <font-awesome-icon :icon="['fas', 'plus']" class="me-2" />
            Agregar rol
          </button>
          <button class="btn btn-add-role btn-delete-role" @click="showDeleteRoleModal">
            <font-awesome-icon :icon="['fas', 'trash']" class="me-2" />
            Eliminar rol
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- Loading skeleton -->
        <div v-if="loading" class="row roles-container">
          <div v-for="i in 6" :key="i" class="col-md-4 mb-4">
            <div class="skeleton-card">
              <div class="skeleton-line skeleton-title"></div>
              <div class="skeleton-line skeleton-link"></div>
              <div class="skeleton-line skeleton-users"></div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="roles.length === 0" class="empty-state">
          <font-awesome-icon :icon="['fas', 'shield-alt']" class="empty-icon" />
          <h4>No hay roles creados</h4>
          <p class="text-muted">Comienza creando tu primer rol</p>
          <button class="btn btn-primary" @click="showAddRoleModal">
            <font-awesome-icon :icon="['fas', 'plus']" class="me-2" />
            Crear primer rol
          </button>
        </div>

        <!-- Roles grid -->
        <div v-else class="row roles-container">
          <div
              v-for="(role, index) in paginatedRoles"
              :key="index"
              class="col-md-4 mb-4"
          >
            <div class="card h-100 shadow-sm role-card with-animation">
              <div class="card-body position-relative">
                <!-- Badge de permisos -->
                <div class="permissions-badge">
                  <font-awesome-icon :icon="['fas', 'key']" />
                  <span>{{ role.permissions_count || 0 }}</span>
                </div>

                <div class="role-icon-container">
                  <font-awesome-icon :icon="getIcon(role.name)" class="role-main-icon" />
                </div>

                <h5 class="card-title mt-3">{{ role.name }}</h5>

                <!-- Mini gráfico circular -->
                <div class="users-chart">
                  <svg width="50" height="50" viewBox="0 0 50 50">
                    <circle
                        cx="25"
                        cy="25"
                        r="20"
                        fill="none"
                        stroke="#e9ecef"
                        stroke-width="4"
                    />
                    <circle
                        cx="25"
                        cy="25"
                        r="20"
                        fill="none"
                        :stroke="getColorForRole(role.name)"
                        stroke-width="4"
                        :stroke-dasharray="`${(role.usuarios_count / totalUsersCount) * 126} 126`"
                        stroke-dashoffset="0"
                        transform="rotate(-90 25 25)"
                    />
                  </svg>
                  <div class="chart-label">{{ ((role.usuarios_count / totalUsersCount) * 100).toFixed(0) }}%</div>
                </div>

                <div class="role-actions">
                  <a href="#" class="action-link" @click="showAssignUserModal(role)">
                    <font-awesome-icon :icon="['fas', 'user-plus']" />
                    Agregar usuario
                  </a>
                </div>

                <a href="#" class="users-count-link" @click="showUsersModal(role)">
                  <span class="users-count">{{ role.usuarios_count }}</span>
                  <span class="users-label">usuarios</span>
                  <font-awesome-icon :icon="['fas', 'chevron-right']" class="ms-1" />
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginación mejorada -->
        <nav aria-label="Page navigation" class="mt-4" v-if="totalPages > 1">
          <ul class="pagination justify-content-center">
            <li v-if="currentPage > 1" class="page-item">
              <a class="page-link" href="#" @click.prevent="previousPage">
                <font-awesome-icon :icon="['fas', 'chevron-left']" />
              </a>
            </li>
            <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: currentPage === page }">
              <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
            </li>
            <li v-if="currentPage < totalPages" class="page-item">
              <a class="page-link" href="#" @click.prevent="nextPage">
                <font-awesome-icon :icon="['fas', 'chevron-right']" />
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Modal para agregar rol - Mejorado -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title">Agregar nuevo rol</h5>
              <p class="text-muted mb-0 small">Define un nuevo rol para el sistema</p>
            </div>
            <button type="button" class="btn-close" @click="hideAddRoleModal"></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label class="form-label">Nombre del rol</label>
              <input
                  type="text"
                  class="form-control"
                  v-model="newRole"
                  placeholder="Ej: Supervisor, Analista, etc."
                  @keyup.enter="createRole"
              >
              <small class="text-muted">Este nombre será usado para identificar el rol en el sistema</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="hideAddRoleModal">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="createRole" :disabled="!newRole.trim() || isCreatingRole">
              <span v-if="isCreatingRole" class="spinner-border spinner-border-sm me-2"></span>
              <font-awesome-icon v-else :icon="['fas', 'save']" class="me-2" />
              {{ isCreatingRole ? 'Guardando...' : 'Guardar rol' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para asignar usuario a rol - Mejorado -->
    <div v-if="showAssignModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title">Asignar usuario a {{ selectedRole?.name }}</h5>
              <p class="text-muted mb-0 small">Busca y selecciona un usuario</p>
            </div>
            <button type="button" class="btn-close" @click="hideAssignUserModal"></button>
          </div>
          <div class="modal-body">
            <div class="search-input-container">
              <font-awesome-icon :icon="['fas', 'search']" class="search-icon" />
              <input
                  type="text"
                  class="form-control ps-5"
                  v-model="userSearchQuery"
                  @input="updateSearchResults"
                  placeholder="Buscar usuario por correo electrónico"
              >
            </div>

            <div class="search-results" v-if="!selectedUser && userSearchQuery">
              <div v-if="filteredUsers.length === 0" class="no-results">
                <font-awesome-icon :icon="['fas', 'user-slash']" />
                <p>No se encontraron usuarios</p>
              </div>
              <div
                  v-else
                  v-for="user in filteredUsers"
                  :key="user.correo_electronico"
                  class="user-result-item"
                  @click="selectUser(user)"
              >
                <div class="user-info">
                  <div class="user-initial">{{ user.correo_electronico.charAt(0).toUpperCase() }}</div>
                  <div>
                    <p class="user-email">{{ user.correo_electronico }}</p>
                    <p class="user-name">{{ user.nombre || 'Sin nombre' }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="selectedUser" class="selected-user-card">
              <div class="user-info">
                <div class="user-initial selected">{{ selectedUser.correo_electronico.charAt(0).toUpperCase() }}</div>
                <div>
                  <p class="user-email">{{ selectedUser.correo_electronico }}</p>
                  <p class="user-name">Usuario seleccionado</p>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger" @click="clearSelectedUser">
                <font-awesome-icon :icon="['fas', 'times']" />
              </button>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="hideAssignUserModal">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="confirmAssignRoleToUser" :disabled="!selectedUser || isAssigningRole">
              <span v-if="isAssigningRole" class="spinner-border spinner-border-sm me-2"></span>
              <font-awesome-icon v-else :icon="['fas', 'user-check']" class="me-2" />
              {{ isAssigningRole ? 'Asignando...' : 'Asignar rol' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para mostrar usuarios asignados - Mejorado -->
    <div v-if="isUsersModalVisible" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title">Usuarios en {{ selectedRole?.name }}</h5>
              <p class="text-muted mb-0 small">{{ selectedRole?.usuarios?.length || 0 }} usuarios asignados</p>
            </div>
            <button type="button" class="btn-close" @click="hideUsersModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedRole?.usuarios?.length === 0" class="empty-state small">
              <font-awesome-icon :icon="['fas', 'users-slash']" class="empty-icon" />
              <p>No hay usuarios asignados a este rol</p>
            </div>
            <div v-else class="users-list">
              <div v-for="user in selectedRole.usuarios" :key="user.id" class="user-list-item">
                <div class="user-info">
                  <div class="user-initial">{{ user.correo_electronico.charAt(0).toUpperCase() }}</div>
                  <div>
                    <p class="user-email">{{ user.correo_electronico }}</p>
                    <p class="user-name">{{ user.nombre || 'Usuario' }}</p>
                  </div>
                </div>
                <button
                    class="btn btn-sm btn-outline-danger"
                    @click="desasignarRol(user.id, selectedRole.name)"
                    data-bs-toggle="tooltip"
                    title="Remover rol"
                    :disabled="isUnassigningRole"
                >
                  <span v-if="isUnassigningRole" class="spinner-border spinner-border-sm"></span>
                  <font-awesome-icon v-else :icon="['fas', 'user-minus']" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para eliminar rol - Mejorado -->
    <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <div>
              <h5 class="modal-title">
                <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="me-2" />
                Eliminar rol
              </h5>
              <p class="mb-0 small">Esta acción no se puede deshacer</p>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="hideDeleteRoleModal"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning d-flex align-items-center">
              <font-awesome-icon :icon="['fas', 'info-circle']" class="me-2" />
              <small>Solo puedes eliminar roles que no tengan usuarios asignados</small>
            </div>
            <label class="form-label">Selecciona el rol a eliminar</label>
            <select class="form-select" v-model="selectedRoleToDelete">
              <option disabled value="">-- Seleccione un rol --</option>
              <option
                  v-for="role in deletableRoles"
                  :key="role.name"
                  :value="role.name"
              >
                {{ role.name }} ({{ role.usuarios_count }} usuarios)
              </option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="hideDeleteRoleModal">Cancelar</button>
            <button
                type="button"
                class="btn btn-danger"
                @click="confirmDeleteRole"
                :disabled="!selectedRoleToDelete || isDeletingRole"
            >
              <span v-if="isDeletingRole" class="spinner-border spinner-border-sm me-2"></span>
              <font-awesome-icon v-else :icon="['fas', 'trash']" class="me-2" />
              {{ isDeletingRole ? 'Eliminando...' : 'Eliminar rol' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { apiCall } from '@/utils/apiHelper';
import { mapGetters } from 'vuex';

export default {
  name: 'Roles',
  computed: {
    ...mapGetters('auth', ['token', 'isAuthenticated', 'user']),

    totalPages() {
      return Math.ceil(this.roles.length / this.rolesPerPage);
    },
    paginatedRoles() {
      const start = (this.currentPage - 1) * this.rolesPerPage;
      const end = start + this.rolesPerPage;
      return this.roles.slice(start, end);
    },
    filteredUsers() {
      if (!this.userSearchQuery) {
        return [];
      }
      const query = this.userSearchQuery.toLowerCase();
      return this.users.filter(user =>
          user.correo_electronico.toLowerCase().includes(query)
      ).slice(0, 5);
    },
    totalUsersCount() {
      return this.roles.reduce((sum, role) => sum + role.usuarios_count, 0) || 1;
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
    },
    deletableRoles() {
      return this.roles.filter(role => role.usuarios_count === 0);
    }
  },
  data() {
    return {
      roles: [],
      users: [],
      currentPage: 1,
      rolesPerPage: 6,
      showModal: false,
      showAssignModal: false,
      showDeleteModal: false,
      isUsersModalVisible: false,
      newRole: '',
      userSearchQuery: '',
      selectedUser: null,
      selectedRole: null,
      selectedRoleToDelete: '',
      loading: true,
      isCreatingRole: false,
      isAssigningRole: false,
      isUnassigningRole: false,
      isDeletingRole: false
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

    async listar_roles() {
      try {
        this.loading = true;

        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        const [rolesResponse, usersResponse] = await Promise.all([
          apiCall.get('roles', { headers: this.getAuthHeaders() }),
          apiCall.get('listarUsuarios', { headers: this.getAuthHeaders() })
        ]);

        this.roles = rolesResponse.data;
        this.users = Array.isArray(usersResponse.data.data) ? usersResponse.data.data : [];

        this.$emit('update-stats', {
          totalRoles: this.roles.length,
          totalUsers: this.users.length
        });
      } catch (error) {
        console.error('Error fetching data:', error);

        if (await this.handleAuthError(error)) return;

        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar los roles',
          timer: 2000,
          showConfirmButton: false
        });
      } finally {
        this.loading = false;
      }
    },

    async createRole() {
      if (!this.newRole.trim()) return;

      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        this.isCreatingRole = true;

        const response = await apiCall.post('agregarRoles', {
          name: this.newRole
        }, {
          headers: this.getAuthHeaders()
        });

        this.roles.push(response.data.data);
        this.hideAddRoleModal();
        this.newRole = '';

        this.$swal.fire({
          icon: 'success',
          title: 'Rol creado',
          text: 'El rol ha sido creado correctamente',
          timer: 1000,
          showConfirmButton: false
        });

        await this.listar_roles();
      } catch (error) {
        console.error('Error creating role:', error);

        if (await this.handleAuthError(error)) return;

        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un error al crear el rol',
          timer: 2000,
          showConfirmButton: false
        });
      } finally {
        this.isCreatingRole = false;
      }
    },

    async assignRoleToUser() {
      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        this.isAssigningRole = true;

        await apiCall.post('asignarRol', {
          user_id: this.selectedUser.id,
          role: this.selectedRole.name
        }, {
          headers: this.getAuthHeaders()
        });

        this.hideAssignUserModal();
        this.selectedUser = null;
        this.userSearchQuery = '';

        this.$swal.fire({
          icon: 'success',
          title: 'Rol asignado',
          text: 'El rol ha sido asignado correctamente',
          timer: 1500,
          showConfirmButton: false
        });

        await this.listar_roles();
      } catch (error) {
        console.error('Error assigning role to user:', error);

        if (await this.handleAuthError(error)) return;

        if (error.response && error.response.status === 422) {
          this.$swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se ha seleccionado ningún usuario válido',
            timer: 2000,
            showConfirmButton: false
          });
        } else {
          this.$swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error al asignar el rol',
            timer: 2000,
            showConfirmButton: false
          });
        }
      } finally {
        this.isAssigningRole = false;
      }
    },

    async desasignarRol(userId, roleName) {
      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        this.isUnassigningRole = true;

        await apiCall.put('desasignarRol', {
          user_id: userId,
          role: roleName
        }, {
          headers: this.getAuthHeaders()
        });

        this.$swal.fire({
          icon: 'success',
          title: 'Usuario desasignado',
          text: 'El usuario ha sido desasignado del rol correctamente',
          timer: 1500,
          showConfirmButton: false
        });

        this.hideUsersModal();
        await this.listar_roles();
      } catch (error) {
        console.error('Error unassigning role from user:', error);

        if (await this.handleAuthError(error)) return;

        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un error al desasignar el rol del usuario',
          timer: 2000,
          showConfirmButton: false
        });
      } finally {
        this.isUnassigningRole = false;
      }
    },

    async deleteRole() {
      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push('/login');
          return;
        }

        this.isDeletingRole = true;

        await apiCall.putWithId('eliminarRol', this.selectedRoleToDelete, {}, {
          headers: this.getAuthHeaders()
        });

        this.roles = this.roles.filter(role => role.name !== this.selectedRoleToDelete);
        this.hideDeleteRoleModal();
        this.selectedRoleToDelete = '';

        this.$swal.fire({
          icon: 'success',
          title: 'Rol eliminado',
          text: 'El rol ha sido eliminado correctamente',
          timer: 1000,
          showConfirmButton: false
        });

        await this.listar_roles();
      } catch (error) {
        console.error('Error deleting role:', error);

        if (await this.handleAuthError(error)) return;

        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un error al eliminar el rol',
          timer: 2000,
          showConfirmButton: false
        });
      } finally {
        this.isDeletingRole = false;
      }
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
    },

    getIcon(roleName) {
      const lowerRoleName = roleName.toLowerCase();
      if (lowerRoleName.includes('jefe') || lowerRoleName.includes('director') || lowerRoleName.includes('subdirector')) {
        return ['fas', 'user-tie'];
      } else if (lowerRoleName.includes('administrador')) {
        return ['fas', 'user-secret'];
      } else if (lowerRoleName.includes('analista')) {
        return ['fas', 'user-graduate'];
      } else if (lowerRoleName.includes('aseso')) {
        return ['fas', 'user-shield'];
      } else {
        return ['fas', 'user'];
      }
    },

    getColorForRole(roleName) {
      const lowerRoleName = roleName.toLowerCase();
      if (lowerRoleName.includes('administrador')) return '#dc3545';
      if (lowerRoleName.includes('jefe') || lowerRoleName.includes('director')) return '#007bff';
      if (lowerRoleName.includes('analista')) return '#28a745';
      if (lowerRoleName.includes('aseso')) return '#6f42c1';
      return '#6c757d';
    },

    showAddRoleModal() {
      this.showModal = true;
    },
    hideAddRoleModal() {
      this.showModal = false;
      this.newRole = '';
      this.isCreatingRole = false;
    },

    showAssignUserModal(role) {
      this.selectedRole = role;
      this.showAssignModal = true;
    },
    hideAssignUserModal() {
      this.showAssignModal = false;
      this.selectedUser = null;
      this.userSearchQuery = '';
      this.isAssigningRole = false;
    },

    showUsersModal(role) {
      this.selectedRole = role;
      this.isUsersModalVisible = true;
    },
    hideUsersModal() {
      this.isUsersModalVisible = false;
      this.isUnassigningRole = false;
    },

    showDeleteRoleModal() {
      this.showDeleteModal = true;
    },
    hideDeleteRoleModal() {
      this.showDeleteModal = false;
      this.selectedRoleToDelete = '';
      this.isDeletingRole = false;
    },

    updateSearchResults() {
      this.selectedUser = null;
    },

    selectUser(user) {
      this.selectedUser = user;
      this.userSearchQuery = user.correo_electronico;
    },

    clearSelectedUser() {
      this.selectedUser = null;
      this.userSearchQuery = '';
    },

    async confirmAssignRoleToUser() {
      if (!this.selectedUser) {
        console.error('No user selected');
        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se ha seleccionado ningún usuario',
          timer: 2000,
          showConfirmButton: false
        });
        return;
      }

      if (this.selectedRole.usuarios.some(user => user.correo_electronico === this.selectedUser.correo_electronico)) {
        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'El usuario ya está asignado a este rol',
          timer: 2000,
          showConfirmButton: false
        });
        return;
      }

      this.assignRoleToUser();
    },

    async confirmDeleteRole() {
      const roleToDelete = this.roles.find(role => role.name === this.selectedRoleToDelete);
      if (roleToDelete && roleToDelete.usuarios_count > 0) {
        this.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se puede eliminar un rol con usuarios asignados',
          timer: 2000,
          showConfirmButton: false
        });
        return;
      }

      this.$swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          this.deleteRole();
        }
      });
    }
  },

  async created() {
    if (!this.isAuthenticated) {
      this.$router.push('/login');
      return;
    }

    await this.listar_roles();
  }
};
</script>

<style scoped>
/* Container y Cards Base */
.roles-container {
  min-height: 400px;
}

.card.no-animation {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card.with-animation {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.card.with-animation:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.card-body {
  padding: 1.5rem;
  position: relative;
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

/* Role Card Specific */
.role-card {
  height: 280px;
}

.role-card .card-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.permissions-badge {
  position: absolute;
  top: 15px;
  right: 15px;
  background: #f8f9fa;
  border-radius: 20px;
  padding: 5px 12px;
  font-size: 12px;
  color: #6c757d;
  display: flex;
  align-items: center;
  gap: 5px;
}

.role-icon-container {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  margin-bottom: 15px;
}

.role-main-icon {
  font-size: 36px;
  color: #6c757d;
}

.role-card .card-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 20px;
}

/* Users Chart */
.users-chart {
  position: relative;
  width: 50px;
  height: 50px;
  margin: 15px auto;
}

.chart-label {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 12px;
  font-weight: 600;
  color: #2c3e50;
}

/* Role Actions */
.role-actions {
  margin-top: auto;
  margin-bottom: 15px;
}

.action-link {
  color: #007bff;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: color 0.2s;
}

.action-link:hover {
  color: #0056b3;
}

.users-count-link {
  position: absolute;
  bottom: 15px;
  right: 15px;
  text-decoration: none;
  color: #6c757d;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: all 0.2s;
}

.users-count-link:hover {
  color: #495057;
}

.users-count {
  font-weight: 600;
  color: #2c3e50;
  font-size: 16px;
}

/* Skeleton Loader */
.skeleton-card {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 1.5rem;
  height: 280px;
}

.skeleton-line {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 4px;
}

.skeleton-title {
  height: 24px;
  width: 60%;
  margin: 20px auto;
}

.skeleton-link {
  height: 16px;
  width: 40%;
  margin: 40px auto 20px;
}

.skeleton-users {
  height: 16px;
  width: 30%;
  margin: auto auto 20px;
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

.empty-state.small {
  padding: 30px 20px;
}

.empty-icon {
  font-size: 48px;
  color: #dee2e6;
  margin-bottom: 20px;
}

.empty-state h4 {
  color: #6c757d;
  margin-bottom: 10px;
}

/* Buttons */
.btn-add-role {
  background-color: #007bff;
  border: none;
  color: white;
  padding: 0.5rem 1.25rem;
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
}

.btn-add-role:hover {
  background-color: #0056b3;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
  color: #fff;
}

.btn-delete-role {
  background-color: #dc3545;
  box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
}

.btn-delete-role:hover {
  background-color: #c82333;
  box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

/* Pagination */
.pagination {
  margin-top: 30px;
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

.pagination .page-item .page-link:hover {
  background-color: #f8f9fa;
  color: #0056b3;
  border-color: #007bff;
}

/* Modal Improvements */
.modal-content {
  border-radius: 12px;
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
  border-bottom: 1px solid #e9ecef;
  padding: 1.25rem 1.5rem;
}

.modal-body {
  padding: 1.5rem;
}

.form-label {
  font-weight: 500;
  color: #495057;
  margin-bottom: 0.5rem;
}

.form-control, .form-select {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 0.625rem 0.875rem;
  transition: all 0.2s;
}

.form-control:focus, .form-select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
}

/* Search Input */
.search-input-container {
  position: relative;
  margin-bottom: 1rem;
}

.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
}

/* Search Results */
.search-results {
  max-height: 300px;
  overflow-y: auto;
  margin-top: 1rem;
}

.user-result-item {
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.user-result-item:hover {
  background-color: #f8f9fa;
  border-color: #007bff;
}

.no-results {
  text-align: center;
  padding: 30px;
  color: #6c757d;
}

.no-results svg {
  font-size: 36px;
  margin-bottom: 10px;
  color: #dee2e6;
}

/* User Info Display */
.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.user-initial {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #e9ecef;
  color: #6c757d;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
}

.user-initial.selected {
  background-color: #007bff;
  color: white;
}

.user-email {
  margin: 0;
  font-weight: 500;
  color: #2c3e50;
  font-size: 14px;
}

.user-name {
  margin: 0;
  font-size: 12px;
  color: #6c757d;
}

/* Selected User Card */
.selected-user-card {
  background-color: #e7f3ff;
  border: 1px solid #007bff;
  border-radius: 8px;
  padding: 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 1rem;
}

/* Users List in Modal */
.users-list {
  max-height: 400px;
  overflow-y: auto;
}

.user-list-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  margin-bottom: 8px;
  transition: all 0.2s;
}

.user-list-item:hover {
  background-color: #f8f9fa;
}

/* Alert Styling */
.alert {
  border-radius: 8px;
  border: none;
}

.alert-warning {
  background-color: #fff3cd;
  color: #856404;
}

/* Spinner */
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Button States */
.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-primary:disabled {
  background-color: #6c757d;
  border-color: #6c757d;
}

.btn-danger:disabled {
  background-color: #6c757d;
  border-color: #6c757d;
}
</style>