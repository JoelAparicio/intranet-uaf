<template>
  <div class="container-fluid d-flex justify-content-center align-items-center">
    <div class="row">
      <div class="col-md-6">
        <Datos_Usuario ref="datosUsuario" />
      </div>
      <div class="col-md-6">
        <Solicitudes ref="solicitudes" :tiposSolicitudes="sharedTiposSolicitudes" />
      </div>
    </div>
  </div>
  <Historial_Solicitudes ref="historial" :tiposSolicitudes="sharedTiposSolicitudes" />
</template>

<script>
import Datos_Usuario from '@/components/Datos_Usuario.vue'
import Solicitudes from '@/components/Solicitudes.vue'
import Historial_Solicitudes from '@/components/Historial_Solicitudes.vue'
import { apiCall } from '@/utils/apiHelper'
import { mapGetters } from 'vuex'

export default {
  name: 'MiEspacio',
  components: {
    Solicitudes,
    Datos_Usuario,
    Historial_Solicitudes
  },
  computed: {
    ...mapGetters('auth', ['token', 'isAuthenticated'])
  },
  data() {
    return {
      sharedTiposSolicitudes: []
    }
  },
  async created() {
    if (!this.isAuthenticated) {
      this.$router.push({ name: 'Login' });
      return;
    }

    // OPTIMIZACIÓN 1: Cargar datos en paralelo
    await this.loadDataOptimized()
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
        this.$router.push({ name: 'Login' });
        return true;
      }
      return false;
    },

    async loadDataOptimized() {
      try {
        if (!this.token) {
          await this.$store.dispatch('auth/logout');
          this.$router.push({ name: 'Login' });
          return;
        }

        // ✅ USANDO APIHELPER Y AUTH STORE
        const tiposSolicitudesResponse = await apiCall.get('listarSolicitud', {
          headers: this.getAuthHeaders()
        });

        // Compartir datos entre componentes
        this.sharedTiposSolicitudes = tiposSolicitudesResponse.data

        // Notificar a los componentes que ya tienen los datos disponibles
        this.$nextTick(() => {
          if (this.$refs.solicitudes && this.$refs.solicitudes.setSolicitudes) {
            this.$refs.solicitudes.setSolicitudes(this.sharedTiposSolicitudes)
          }
          if (this.$refs.historial && this.$refs.historial.setTiposSolicitudes) {
            this.$refs.historial.setTiposSolicitudes(this.sharedTiposSolicitudes)
          }
        })

      } catch (error) {
        console.error('Error al cargar datos compartidos:', error)

        if (await this.handleAuthError(error)) return;

        // Los componentes individuales manejarán sus propios errores como fallback
      }
    }
  }
}
</script>

<style scoped>
.container {
  margin-top: 3em;
}
</style>