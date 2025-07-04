<template>
    <Header />
    <RouterView />
    <Footer />
</template>

<script>
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import { provide } from 'vue';
import { apiCall } from '@/utils/apiHelper';
import { mapGetters } from 'vuex';

export default {
  name: 'Home',
  components: {
    Header,
    Footer
  },
  computed: {
    ...mapGetters('auth', ['token'])
  },
  setup() {
    const fetchAprobacionesCount = async () => {
      try {
        // ✅ USANDO APIHELPER
        const response = await apiCall.get('listarAprobaciones');
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