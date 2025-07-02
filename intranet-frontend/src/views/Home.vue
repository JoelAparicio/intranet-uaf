<template>
    <Header />
    <RouterView />
    <Footer />
</template>

<script>
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import { provide } from 'vue';
import axios from 'axios';
import apiConfig from '@/config/api'

export default {
  name: 'Home',
  components: {
    Header,
    Footer
  },
  setup() {
    const fetchAprobacionesCount = async () => {
      try {
        const response = await axios.get('listar_aprobaciones');
        return response.data.success ? response.data.data.length : 0;
      } catch (error) {
        console.error('Error fetching aprobaciones count:', error);
        return 0;
      }
    };

    provide('fetchAprobacionesCount', fetchAprobacionesCount);
  }
};
</script>