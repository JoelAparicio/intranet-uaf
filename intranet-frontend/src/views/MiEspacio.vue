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
import axios from 'axios'

export default {
  name: 'MiEspacio',
  components: {
    Solicitudes,
    Datos_Usuario,
    Historial_Solicitudes
  },
  data() {
    return {
      sharedTiposSolicitudes: []
    }
  },
  async created() {
    // OPTIMIZACIÓN 1: Cargar datos en paralelo
    await this.loadDataOptimized()
  },
  methods: {
    async loadDataOptimized() {
      try {
        const token = localStorage.getItem('auth_token')

        // OPTIMIZACIÓN 2: Una sola llamada a listar_solicitud para ambos componentes
        const tiposSolicitudesPromise = axios.get('listar_solicitud', {
          headers: { 'Authorization': `Bearer ${token}` }
        })

        // OPTIMIZACIÓN 3: Ejecutar todas las llamadas en paralelo
        const [tiposSolicitudesResponse] = await Promise.all([
          tiposSolicitudesPromise,
          // Los otros componentes manejan sus propias llamadas, pero ahora pueden usar datos compartidos
        ])

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