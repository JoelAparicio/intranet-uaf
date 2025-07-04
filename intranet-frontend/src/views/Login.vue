<template>
  <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row justify-content-center w-100">
      <div class="col-md-6 col-lg-4 custom-width">
        <div class="card shadow-lg">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <img src="../assets/user.png" alt="User Icon" class="img-fluid rounded-circle" style="width: 100px;">
            </div>
            <h2 class="card-title text-center mb-4 fs-3">Iniciar sesión</h2>
            <form @submit.prevent="submitForm">
              <div class="form-floating mb-3">
                <input type="email" class="form-control" id="correo_electronico" v-model="correo_electronico" placeholder="Correo Electrónico" required>
                <label for="correo_electronico">Correo Electrónico</label>
              </div>
              <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" v-model="password" placeholder="Contraseña" required>
                <label for="password">Contraseña</label>
              </div>
              <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg py-2" :disabled="isLoading">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                  {{ isLoading ? 'Iniciando sesión...' : 'Entrar' }}
                </button>
              </div>
              <div v-if="error" class="alert alert-danger mt-3" role="alert">
                {{ error }}
              </div>
              <div class="text-center mt-3">
                <a href="#" @click.prevent="redirectToRegister" class="underline-animation">¿No tienes una cuenta? Regístrate</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <Footer/>
</template>

<script>
import { mapGetters } from 'vuex'
import Footer from '@/components/Footer.vue'

export default {
  name: 'Login',
  components: {
    Footer
  },
  data() {
    return {
      correo_electronico: '',
      password: '',
      error: '',
      isLoading: false
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated'])
  },
  methods: {
    async submitForm() {
      // Prevenir doble submit
      if (this.isLoading) return;

      this.isLoading = true;
      this.error = '';

      try {
        console.log('🔐 Iniciando proceso de login...');

        const credentials = {
          correo_electronico: this.correo_electronico,
          password: this.password
        };

        // Usar el store de auth correctamente
        const result = await this.$store.dispatch('auth/login', credentials);

        console.log('📝 Resultado del login:', result);

        if (result.success) {
          console.log('✅ Login exitoso');

          // SweetAlert de éxito
          this.$swal.fire({
            icon: 'success',
            title: '¡Bienvenido!',
            text: `Hola ${result.user.nombre}, has iniciado sesión correctamente`,
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
          });

          // Navegar después de un breve delay
          setTimeout(() => {
            this.$router.push({ name: 'MiEspacio' });
          }, 2000);

        } else {
          // El login falló
          throw new Error(result.error || 'Error en el login');
        }

      } catch (error) {
        console.error('❌ Error en login:', error);

        let errorMessage = 'Error al iniciar sesión.';

        // Manejar diferentes tipos de errores
        if (error.response) {
          if (error.response.status === 401) {
            errorMessage = 'Correo electrónico o contraseña incorrectos.';
          } else if (error.response.status === 403) {
            errorMessage = 'Usuario inactivo. Contacte al administrador.';
          } else if (error.response.data?.message) {
            errorMessage = error.response.data.message;
          }
        } else if (error.message) {
          errorMessage = error.message;
        }

        // SweetAlert de error
        this.$swal.fire({
          icon: 'error',
          title: 'Error de autenticación',
          text: errorMessage,
          showConfirmButton: true
        });

        this.error = errorMessage;

      } finally {
        this.isLoading = false;
      }
    },

    redirectToRegister() {
      this.$router.push({ name: 'Register' });
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

/* Spinner animation */
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
}

input:-webkit-autofill {
  -webkit-text-fill-color: #000 !important;
}
</style>