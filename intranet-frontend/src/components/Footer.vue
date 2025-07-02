<template>
  <footer class="footer mt-auto py-1 bg-light">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-4 mb-3 mb-md-0">
          <h5 class="text-primary fw-bold">Unidad de Análisis Financiero</h5>
          <p class="text-muted mb-0">Panamá</p>
        </div>
        <div class="col-md-4 text-center mb-3 mb-md-0">
          <img src="../assets/UAF_logo.png" alt="UAF Logo" class="img-fluid" style="max-width: 120px;">
        </div>
        <div class="col-md-4 text-md-end">
          <p class="mt-2 mb-0 text-muted">&copy; 2024 UAF. Todos los derechos reservados.</p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
// No additional script needed for this component
</script>

<style scoped>
.footer {
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
}

.text-primary {
  color: #0056b3 !important;
}

a:hover {
  opacity: 0.7;
  transition: opacity 0.3s ease;
}
</style>