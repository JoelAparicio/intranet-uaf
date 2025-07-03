<template>
  <div class="container">
    <div class="col-md-12">
      <div class="card">
        <!-- Header del Card -->
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Datos personales</h5>
          <div class="btn-group">
            <button
                type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#editModal"
                :disabled="isLoading"
            >
              <i class="fas fa-edit me-1"></i>Editar
            </button>
            <button
                v-if="!user.firma_path"
                type="button"
                class="btn btn-outline-success ms-2"
                data-bs-toggle="modal"
                data-bs-target="#firmaModal"
                :disabled="isLoading"
            >
              <i class="fas fa-signature me-1"></i>Registrar firma
            </button>
            <button
                v-else
                type="button"
                class="btn btn-outline-warning ms-2"
                data-bs-toggle="modal"
                data-bs-target="#firmaModal"
                :disabled="isLoading"
            >
              <i class="fas fa-sync-alt me-1"></i>Actualizar firma
            </button>
          </div>
        </div>

        <!-- Body del Card -->
        <div class="card-body">
          <p class="card-text text-muted mb-3">
            Aquí podrás ver y editar tus datos personales como colaborador de la UAF
          </p>

          <!-- Indicador de firma registrada -->
          <div v-if="user.firma_path" class="alert alert-success d-flex align-items-center mb-4">
            <i class="fas fa-check-circle me-2"></i>
            <span>Firma digital registrada correctamente</span>
            <a
                v-if="user.firma_url"
                :href="user.firma_url"
                target="_blank"
                class="btn btn-sm btn-outline-success ms-auto"
            >
              <i class="fas fa-eye me-1"></i>Ver firma
            </a>
          </div>

          <!-- Loading indicator -->
          <div v-if="isLoading" class="text-center my-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando datos del usuario...</p>
          </div>

          <!-- Formulario de datos -->
          <form v-else class="pb-4 pt-2">
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="nombre" class="form-label fw-semibold">Nombre completo</label>
                <input
                    type="text"
                    class="form-control"
                    id="nombre"
                    v-model="user.nombre"
                    disabled
                    placeholder="Nombre no disponible"
                >
              </div>

              <div class="col-md-4 mb-3">
                <label for="correo_electronico" class="form-label fw-semibold">Correo Electrónico</label>
                <input
                    type="email"
                    class="form-control"
                    id="correo_electronico"
                    v-model="user.correo_electronico"
                    disabled
                    placeholder="Correo no disponible"
                >
              </div>

              <div class="col-md-4 mb-3">
                <label for="cargo" class="form-label fw-semibold">Cargo</label>
                <input
                    type="text"
                    class="form-control"
                    id="cargo"
                    v-model="user.cargo"
                    disabled
                    placeholder="Cargo no asignado"
                >
              </div>

              <div class="col-md-4 mb-3">
                <label for="posicion" class="form-label fw-semibold">Posición</label>
                <input
                    type="text"
                    class="form-control"
                    id="posicion"
                    v-model="user.posicion"
                    disabled
                    placeholder="Posición no asignada"
                >
              </div>

              <div class="col-md-4 mb-3">
                <label for="cedula" class="form-label fw-semibold">Cédula</label>
                <input
                    type="text"
                    class="form-control"
                    id="cedula"
                    v-model="user.cedula"
                    disabled
                    placeholder="Cédula no registrada"
                >
              </div>

              <div class="col-md-4 mb-3">
                <label for="extension" class="form-label fw-semibold">Extensión telefónica</label>
                <input
                    type="text"
                    class="form-control"
                    id="extension"
                    v-model="user.extension"
                    disabled
                    placeholder="Sin extensión"
                >
              </div>

              <div class="col-md-4 mb-3">
                <label for="estado" class="form-label fw-semibold">Estado</label>
                <input
                    type="text"
                    class="form-control"
                    id="estado"
                    v-model="user.estado"
                    disabled
                    :class="{'text-success': user.estado === 'activo', 'text-danger': user.estado === 'inactivo'}"
                >
              </div>

              <div class="col-md-4 mb-3">
                <label for="departamento" class="form-label fw-semibold">Departamento</label>
                <input
                    type="text"
                    class="form-control"
                    id="departamento"
                    v-model="user.departamento"
                    disabled
                    placeholder="Sin departamento asignado"
                >
              </div>

              <div class="col-md-4 mb-3">
                <label for="horas_extra" class="form-label fw-semibold">Tiempo extra (horas)</label>
                <input
                    type="text"
                    class="form-control"
                    id="horas_extra"
                    v-model="user.tiempo_extra"
                    disabled
                    placeholder="0 horas"
                >
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal editar usuario -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">
              <i class="fas fa-user-edit me-2"></i>Editar Datos Personales
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <form @submit.prevent="updateUserData">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editNombre" class="form-label fw-semibold">
                    Nombre completo <span class="text-danger">*</span>
                  </label>
                  <input
                      type="text"
                      class="form-control"
                      id="editNombre"
                      v-model="editUser.nombre"
                      :class="{'is-invalid': editErrors.nombre}"
                      required
                  >
                  <div v-if="editErrors.nombre" class="invalid-feedback">
                    {{ editErrors.nombre[0] }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="editCargo" class="form-label fw-semibold">
                    Cargo <span class="text-danger">*</span>
                  </label>
                  <input
                      type="text"
                      class="form-control"
                      id="editCargo"
                      v-model="editUser.cargo"
                      :class="{'is-invalid': editErrors.cargo}"
                      required
                  >
                  <div v-if="editErrors.cargo" class="invalid-feedback">
                    {{ editErrors.cargo[0] }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="editPosicion" class="form-label fw-semibold">
                    Posición <span class="text-danger">*</span>
                  </label>
                  <input
                      type="text"
                      class="form-control"
                      id="editPosicion"
                      v-model="editUser.posicion"
                      :class="{'is-invalid': editErrors.posicion}"
                      required
                  >
                  <div v-if="editErrors.posicion" class="invalid-feedback">
                    {{ editErrors.posicion[0] }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="editExtension" class="form-label fw-semibold">
                    Extensión telefónica <span class="text-danger">*</span>
                  </label>
                  <input
                      type="text"
                      class="form-control"
                      id="editExtension"
                      v-model="editUser.extension"
                      :class="{'is-invalid': editErrors.extension}"
                      required
                  >
                  <div v-if="editErrors.extension" class="invalid-feedback">
                    {{ editErrors.extension[0] }}
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="editFirma" class="form-label fw-semibold">
                  Actualizar firma digital (opcional)
                </label>
                <input
                    type="file"
                    class="form-control"
                    id="editFirma"
                    accept="image/png,image/jpeg,image/jpg"
                    @change="handleEditFirmaFile"
                />
                <small class="form-text text-muted">
                  Selecciona un archivo solo si deseas cambiar tu firma actual. Formatos: PNG, JPG, JPEG (máx. 2MB)
                </small>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="submit" class="btn btn-primary" :disabled="isUpdating">
                  <span v-if="isUpdating" class="spinner-border spinner-border-sm me-2" role="status"></span>
                  <i v-else class="fas fa-save me-1"></i>
                  {{ isUpdating ? 'Guardando...' : 'Guardar cambios' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal subir/actualizar firma -->
    <div class="modal fade" id="firmaModal" tabindex="-1" aria-labelledby="firmaModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form @submit.prevent="uploadFirma">
            <div class="modal-header">
              <h5 class="modal-title" id="firmaModalLabel">
                <i class="fas fa-signature me-2"></i>
                {{ user.firma_path ? 'Actualizar Firma Digital' : 'Registrar Firma Digital' }}
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <div class="mb-3">
                <label for="firmaInput" class="form-label fw-semibold">
                  Seleccionar archivo de imagen <span class="text-danger">*</span>
                </label>
                <input
                    type="file"
                    id="firmaInput"
                    class="form-control"
                    accept="image/png,image/jpeg,image/jpg"
                    @change="handleFirmaFile"
                    required
                    :disabled="isUploading"
                />
                <small class="form-text text-muted">
                  Formatos soportados: PNG, JPG, JPEG. Tamaño máximo: 2MB
                </small>
              </div>

              <!-- Preview de la imagen seleccionada -->
              <div v-if="firmaPreview" class="mb-3">
                <label class="form-label fw-semibold">Vista previa:</label>
                <div class="border rounded p-3 text-center bg-light">
                  <img
                      :src="firmaPreview"
                      alt="Preview de firma"
                      class="img-fluid shadow-sm"
                      style="max-height: 200px; border-radius: 8px;"
                  >
                </div>
              </div>

              <!-- Información adicional -->
              <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <small>
                  La firma será procesada automáticamente para eliminar fondos blancos y optimizar su uso en documentos.
                </small>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" :disabled="isUploading">
                <i class="fas fa-times me-1"></i>Cancelar
              </button>
              <button type="submit" class="btn btn-success" :disabled="!firmaFile || isUploading">
                <span v-if="isUploading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                <i v-else class="fas fa-upload me-1"></i>
                {{ isUploading ? 'Subiendo...' : (user.firma_path ? 'Actualizar Firma' : 'Subir Firma') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { Modal } from 'bootstrap'
import Swal from 'sweetalert2'
import { mapGetters } from 'vuex'

export default {
  name: 'DatosUsuario',
  data() {
    return {
      // Datos del usuario
      user: {
        id: null,
        nombre: '',
        correo_electronico: '',
        cargo: '',
        posicion: '',
        cedula: '',
        extension: '',
        estado: '',
        departamento: '',
        tiempo_extra: '',
        firma_path: null,
        firma_url: null
      },

      // Datos para edición
      editUser: {
        nombre: '',
        cargo: '',
        posicion: '',
        extension: ''
      },

      // Archivos de firma
      firmaFile: null,
      editFirmaFile: null,
      firmaPreview: null,

      // Instancias de modales
      editModalInstance: null,
      firmaModalInstance: null,

      // Estados de carga
      isLoading: false,
      isUpdating: false,
      isUploading: false,

      // Errores de validación
      editErrors: {}
    }
  },

  computed: {
    ...mapGetters('auth', ['token', 'isAuthenticated'])
  },

  created() {
    this.fetchUserData()
  },

  methods: {
    /**
     * Obtener datos del usuario autenticado
     */
    async fetchUserData() {
      this.isLoading = true
      try {
        // ✅ CORREGIDO: Usar token del store de Vuex
        if (!this.token) {
          this.$router.push('/login')
          return
        }

        // ✅ CORREGIDO: URL correcta de la API
        const response = await axios.get('/information_user', {
          headers: {
            'Authorization': `Bearer ${this.token}`
          }
        })

        if (response.data.status) {
          this.user = response.data.data
          this.editUser = {
            nombre: this.user.nombre || '',
            cargo: this.user.cargo || '',
            posicion: this.user.posicion || '',
            extension: this.user.extension || ''
          }
        } else {
          throw new Error(response.data.message || 'Error al obtener datos')
        }

        this.$nextTick(() => {
          this.editModalInstance = new Modal(document.getElementById('editModal'))
          this.firmaModalInstance = new Modal(document.getElementById('firmaModal'))
        })

      } catch (error) {
        console.error('Error al cargar datos del usuario:', error)

        if (error.response?.status === 401) {
          await this.$store.dispatch('auth/logout')
          this.$router.push('/login')
          return
        }

        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Error al cargar sus datos'
        })
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Actualizar datos básicos del usuario
     */
    async updateUserData() {
      this.isUpdating = true
      this.editErrors = {}

      try {
        // ✅ CORREGIDO: Usar token del store
        const updateResponse = await axios.put('/edit_information', this.editUser, {
          headers: {
            'Authorization': `Bearer ${this.token}`
          }
        })

        if (!updateResponse.data.status) {
          throw new Error(updateResponse.data.message || 'Error al actualizar datos')
        }

        // Si hay una nueva firma, subirla
        if (this.editFirmaFile) {
          const formData = new FormData()
          formData.append('firma', this.editFirmaFile)

          const firmaResponse = await axios.post('/upload-firma', formData, {
            headers: {
              'Authorization': `Bearer ${this.token}`,
              'Content-Type': 'multipart/form-data'
            }
          })

          if (firmaResponse.data.success && firmaResponse.data.data?.user) {
            Object.assign(this.user, firmaResponse.data.data.user)
          }
        }

        Object.assign(this.user, this.editUser)

        this.closeModal('editModal')
        this.resetEditForm()

        await Swal.fire({
          icon: 'success',
          title: 'Datos actualizados',
          text: 'Los datos se han actualizado correctamente',
          timer: 2000,
          showConfirmButton: false,
          allowOutsideClick: true,
          allowEscapeKey: true
        })

        await this.fetchUserData()

      } catch (error) {
        console.error('Error al actualizar datos:', error)

        if (error.response?.status === 422) {
          this.editErrors = error.response.data.errors || {}
        }

        await Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Ha ocurrido un error al actualizar los datos',
          allowOutsideClick: true,
          allowEscapeKey: true
        })
      } finally {
        this.isUpdating = false
        this.removeModalBackdrops()
      }
    },

    /**
     * Manejar selección de archivo de firma (modal principal)
     */
    handleFirmaFile(e) {
      const file = e.target.files[0]
      if (file) {
        // Validar tamaño (2MB)
        if (file.size > 2 * 1024 * 1024) {
          Swal.fire({
            icon: 'warning',
            title: 'Archivo muy grande',
            text: 'El archivo debe ser menor a 2MB'
          })
          e.target.value = ''
          return
        }

        this.firmaFile = file

        // Crear preview de la imagen
        const reader = new FileReader()
        reader.onload = (event) => {
          this.firmaPreview = event.target.result
        }
        reader.readAsDataURL(file)
      } else {
        this.firmaFile = null
        this.firmaPreview = null
      }
    },

    /**
     * Manejar selección de archivo de firma (modal de edición)
     */
    handleEditFirmaFile(e) {
      const file = e.target.files[0]
      if (file && file.size > 2 * 1024 * 1024) {
        Swal.fire({
          icon: 'warning',
          title: 'Archivo muy grande',
          text: 'El archivo debe ser menor a 2MB'
        })
        e.target.value = ''
        return
      }
      this.editFirmaFile = file
    },

    /**
     * Subir firma digital
     */
    async uploadFirma() {
      if (!this.firmaFile) {
        Swal.fire({
          icon: 'warning',
          title: 'Atención',
          text: 'Por favor selecciona un archivo de imagen'
        })
        return
      }

      this.isUploading = true
      const formData = new FormData()
      formData.append('firma', this.firmaFile)

      try {
        // ✅ CORREGIDO: URL correcta y token del store
        const response = await axios.post('/upload-firma', formData, {
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'multipart/form-data'
          }
        })

        console.log('Respuesta del servidor:', response.data)

        // Actualizar el usuario con la nueva firma
        if (response.data.success && response.data.data) {
          if (response.data.data.user) {
            Object.assign(this.user, response.data.data.user)
          } else if (response.data.data.firma_path) {
            this.user.firma_path = response.data.data.firma_path
            this.user.firma_url = response.data.data.firma_url
          }
        }

        this.closeModal('firmaModal')
        this.resetFirmaForm()

        await Swal.fire({
          icon: 'success',
          title: this.user.firma_path ? 'Firma actualizada' : 'Firma registrada',
          text: 'Tu firma se ha guardado correctamente',
          timer: 2000,
          showConfirmButton: false,
          allowOutsideClick: true,
          allowEscapeKey: true
        })

        await this.fetchUserData()

      } catch (error) {
        console.error('Error al subir firma:', error)

        let errorMessage = 'Error al subir la firma'

        if (error.response?.data?.message) {
          errorMessage = error.response.data.message
        } else if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          errorMessage = errors.join(', ')
        }

        await Swal.fire({
          icon: 'error',
          title: 'Error',
          text: errorMessage,
          allowOutsideClick: true,
          allowEscapeKey: true
        })
      } finally {
        this.isUploading = false
        this.removeModalBackdrops()
      }
    },

    /**
     * Resetear formulario de firma
     */
    resetFirmaForm() {
      this.firmaFile = null
      this.firmaPreview = null
      const input = document.getElementById('firmaInput')
      if (input) input.value = ''
    },

    /**
     * Resetear formulario de edición
     */
    resetEditForm() {
      this.editFirmaFile = null
      this.editErrors = {}
      const editFirmaInput = document.getElementById('editFirma')
      if (editFirmaInput) editFirmaInput.value = ''
    },

    /**
     * Cerrar modal de forma más robusta
     */
    closeModal(modalId) {
      try {
        const modalElement = document.getElementById(modalId)
        if (modalElement) {
          const modalInstance = Modal.getInstance(modalElement)
          if (modalInstance) {
            modalInstance.hide()
          }

          modalElement.classList.remove('show')
          modalElement.style.display = 'none'
          modalElement.setAttribute('aria-hidden', 'true')
          modalElement.removeAttribute('aria-modal')
          modalElement.removeAttribute('role')
        }

        this.removeModalBackdrops()

        document.body.classList.remove('modal-open')
        document.body.style.overflow = ''
        document.body.style.paddingRight = ''

      } catch (error) {
        console.error('Error al cerrar modal:', error)
        this.forceCloseAllModals()
      }
    },

    /**
     * Remover todos los backdrops de modales
     */
    removeModalBackdrops() {
      const backdrops = document.querySelectorAll('.modal-backdrop')
      backdrops.forEach(backdrop => {
        backdrop.remove()
      })
    },

    /**
     * Método de emergencia para cerrar todos los modales
     */
    forceCloseAllModals() {
      document.body.classList.remove('modal-open')
      document.body.style.overflow = ''
      document.body.style.paddingRight = ''

      const modals = document.querySelectorAll('.modal')
      modals.forEach(modal => {
        modal.classList.remove('show')
        modal.style.display = 'none'
        modal.setAttribute('aria-hidden', 'true')
        modal.removeAttribute('aria-modal')
        modal.removeAttribute('role')
      })

      this.removeModalBackdrops()
    }
  }
}
</script>

<style scoped>
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.alert {
  border-radius: 0.5rem;
}

.img-fluid {
  border-radius: 0.375rem;
}

.card {
  border: none;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.form-label {
  color: #495057;
  margin-bottom: 0.5rem;
}

.form-control:disabled {
  background-color: #f8f9fa;
  border-color: #e9ecef;
}

.btn-group .btn {
  border-radius: 0.375rem;
}

.btn-group .btn:not(:last-child) {
  margin-right: 0.5rem;
}

.modal-content {
  border: none;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
}

.modal-footer {
  border-top: 1px solid #dee2e6;
  background-color: #f8f9fa;
}

.text-success {
  color: #198754 !important;
}

.text-danger {
  color: #dc3545 !important;
}

.fw-semibold {
  font-weight: 600;
}

.gap-2 {
  gap: 0.5rem;
}
</style>