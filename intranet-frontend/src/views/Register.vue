<template>
  <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row justify-content-center w-100">
      <div class="col-md-7 col-lg-5 custom-width">
        <div class="card shadow-lg">
          <div class="card-body p-4">
            <h2 class="card-title text-center mb-4 fs-3">Formulario de registro</h2>
            <form @submit.prevent="submitForm">
              <div class="form-floating mb-2">
                <input type="text" class="form-control" id="nombre" v-model="nombre" placeholder="Nombre" required>
                <label for="nombre">Nombre</label>
              </div>
              <div class="form-floating mb-2">
                <input type="email" class="form-control" id="correo_electronico" v-model="correo_electronico" placeholder="Correo Electrónico" required>
                <label for="correo_electronico">Correo Electrónico</label>
              </div>
              <div class="form-floating mb-2">
                <input type="text" class="form-control" id="cargo" v-model="cargo" placeholder="Cargo" required>
                <label for="cargo">Cargo</label>
              </div>
              <div class="form-floating mb-2">
                <input type="text" class="form-control" id="posicion" v-model="posicion" placeholder="Posición" required>
                <label for="posicion">Posición</label>
              </div>
              <div class="form-floating mb-2">
                <input type="text" class="form-control" id="cedula" v-model="cedula" placeholder="Cédula" required>
                <label for="cedula">Cédula</label>
              </div>
              <div class="form-floating mb-2">
                <input type="text" class="form-control" id="extension" v-model="extension" placeholder="Extensión" required>
                <label for="extension">Extensión</label>
              </div>
              <div class="form-floating mb-2">
                <select class="form-select" id="departamento" v-model="departamento" required>
                  <option value=""></option>
                  <option v-for="(dept, index) in departamentos" :key="index" :value="dept.value">
                    {{ dept.label }}
                  </option>
                </select>
                <label for="departamento">Departamento</label>
              </div>
              <div class="form-floating mb-2">
                <input type="password" class="form-control" id="password" v-model="password" placeholder="Contraseña" required>
                <label for="password">Contraseña</label>
              </div>
              <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password_confirmation" v-model="password_confirmation" placeholder="Confirmar Contraseña" required>
                <label for="password_confirmation">Confirmar Contraseña</label>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg py-2" :disabled="isLoading">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                  {{ isLoading ? 'Registrando...' : 'Registrarse' }}
                </button>
              </div>
            </form>
            <div v-if="errorMessage" class="alert alert-danger mt-3" role="alert">
              {{ errorMessage }}
            </div>
            <div class="text-center mt-3">
              <a href="#" @click.prevent="redirectToLogin" class="underline-animation">¿Ya tienes una cuenta? Inicia sesión</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Register',
  data() {
    return {
      nombre: '',
      correo_electronico: '',
      password: '',
      password_confirmation: '',
      cargo: '',
      posicion: '',
      cedula: '',
      extension: '',
      departamento: '',
      errorMessage: '',
      isLoading: false,
      departamentos: [
        { value: '1', label: 'Despacho superior' },
        { value: '2', label: 'Secretaría' },
        { value: '3', label: 'Relaciones públicas' },
        { value: '4', label: 'Administración' },
        { value: '5', label: 'Recursos humanos' },
        { value: '6', label: 'Análisis operativo' },
        { value: '7', label: 'Análisis estratégico' },
        { value: '8', label: 'Asesoría legal' },
        { value: '9', label: 'Contact Center' },
        { value: '10', label: 'Cooperación nacional e internacional' },
        { value: '11', label: 'Tecnología' },
      ]
    }
  },
  methods: {
    async submitForm() {
      if (this.isLoading) return;

      this.isLoading = true;
      this.errorMessage = '';

      try {
        const userData = {
          nombre: this.nombre,
          correo_electronico: this.correo_electronico,
          password: this.password,
          password_confirmation: this.password_confirmation,
          cargo: this.cargo,
          posicion: this.posicion,
          cedula: this.cedula,
          extension: this.extension,
          departamento: this.departamento
        };

        const response = await axios.post('/register', userData);

        console.log('✅ Registro exitoso:', response.data);

        this.$swal.fire({
          icon: 'success',
          title: 'Registro exitoso',
          text: 'El registro se ha completado con éxito!',
          timer: 2000,
          timerProgressBar: true,
          showConfirmButton: false
        });

        setTimeout(() => {
          this.$router.push({ name: 'Login' });
        }, 2000);

      } catch (error) {
        console.error('❌ Error en registro:', error);

        let errorMsg = 'Ha ocurrido un error. Por favor, inténtalo de nuevo.';

        if (error.response?.data?.errors) {
          errorMsg = this.parseErrorMessages(error.response.data.errors);
        } else if (error.response?.data?.message) {
          errorMsg = error.response.data.message;
        }

        this.$swal.fire({
          icon: 'error',
          title: 'Error de registro',
          text: errorMsg,
          showConfirmButton: true
        });

        this.errorMessage = errorMsg;

      } finally {
        this.isLoading = false;
      }
    },

    parseErrorMessages(errors) {
      if (errors['correo_electronico'] || errors['cedula'] || errors['extension']) {
        return 'El usuario ya está registrado en el sistema.';
      }
      return 'Ha ocurrido un error. Por favor, inténtalo de nuevo.';
    },

    redirectToLogin() {
      this.$router.push({ name: 'Login' });
    }
  }
}
</script>

<style scoped>
.container-fluid {
  background-color: #f8f9fa;
}
.card {
  border-radius: 1rem;
}
.custom-width {
  max-width: 500px;
}
.btn-primary {
  background-color: #2b79c2;
  border-color: #2b79c2;
}
.btn-primary:hover:not(:disabled) {
  background-color: #39ace7;
  border-color: #39ace7;
}
.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
.form-floating > .form-control,
.form-floating > .form-select {
  height: calc(3.25rem + 2px);
  padding: 0.75rem 0.75rem;
}
.form-floating > label {
  padding: 1rem 0.75rem;
}
.underline-animation {
  position: relative;
  color: #92badd;
  text-decoration: none !important;
  transition: color 0.3s ease;
}
.underline-animation::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 2px;
  bottom: -2px;
  left: 0;
  background-color: #92badd;
  transform: scaleX(0);
  transform-origin: bottom right;
  transition: transform 0.3s ease-out;
}
.underline-animation:hover {
  color: #000000;
}
.underline-animation:hover::after {
  transform: scaleX(1);
  transform-origin: bottom left;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Para manejar autocompletado */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 30px #f8f9fa inset !important;
  -webkit-text-fill-color: #000 !important;
}

select:-webkit-autofill,
select:-webkit-autofill:hover,
select:-webkit-autofill:focus,
select:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 30px #f8f9fa inset !important;
  -webkit-text-fill-color: #000 !important;
}
</style>