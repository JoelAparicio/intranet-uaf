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
                <!-- ✅ AÑADIR: Mostrar estado de loading -->
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
  <Footer />
</template>

<script>
import { mapActions, mapState } from 'vuex'
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
      isLoading: false // ✅ AÑADIR: Estado de loading
    }
  },
  computed: {
    ...mapState(['authStatus'])
  },
  methods: {
    ...mapActions(['login']),
    async submitForm() {
      // ✅ PREVENIR: Doble submit
      if (this.isLoading) return;

      this.isLoading = true;
      this.error = '';

      try {
        console.log('🔐 Iniciando proceso de login...');

        const user = {
          correo_electronico: this.correo_electronico,
          password: this.password
        }

        // ✅ ESPERAR: A que el login complete totalmente
        const response = await this.login(user);

        console.log('✅ Login exitoso, respuesta:', response.data);

        // ✅ VERIFICAR: Que el token se guardó correctamente
        const savedToken = localStorage.getItem('auth_token');

        if (!savedToken) {
          throw new Error('Token no se guardó correctamente en localStorage');
        }

        console.log('✅ Token verificado en localStorage');

        // ✅ ESPERAR: Un momento antes de navegar para asegurar que todo se guarde
        await new Promise(resolve => setTimeout(resolve, 100));

        console.log('🚀 Navegando a miespacio...');

        // Navegar al dashboard
        this.$router.push({name: 'miespacio'});

      } catch (error) {
        console.error('❌ Error en login:', error);

        // Limpiar cualquier token parcial
        localStorage.removeItem('auth_token');

        let errorMessage = 'Error al iniciar sesión.';

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

        this.error = errorMessage;
      } finally {
        this.isLoading = false;
      }
    },
    redirectToRegister() {
      this.$router.push({name: 'register'})
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