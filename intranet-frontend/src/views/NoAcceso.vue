<template>
    <div class="no-access-wrapper">
      <h1 class="no-access-title">No estás autorizado</h1>
      <button @click="redirectToLogin" class="no-access-button">Regresar al login</button>
      <Footer />
    </div>
  </template>

<script>
import Footer from '@/components/Footer.vue'

export default {
  name: 'NoAccess',
  components: {
    Footer
  },
  methods: {
    redirectToLogin() {
      // ✅ CORREGIDO: Usar nombre de ruta estandarizado
      this.$router.push({ name: 'Login' });
    }
  }
}
</script>
  
  <style scoped>
  .no-access-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    text-align: center;
    background-color: #f8f9fa;
  }
  
  .no-access-title {
    color: red;
    font-size: 2em;
    margin-bottom: 20px;
  }
  
  .no-access-button {
    background-color: #2b79c2;
    border: none;
    color: white;
    padding: 15px 30px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  
  .no-access-button:hover {
    background-color: #1e5d91;
  }
  </style>
  