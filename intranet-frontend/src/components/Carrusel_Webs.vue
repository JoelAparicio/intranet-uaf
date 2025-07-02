<template>
  <div id="carouselExampleCaptions" class="carousel slide mx-auto" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <button
        type="button"
        data-bs-target="#carouselExampleCaptions"
        data-bs-slide-to="0"
        class="active"
        aria-current="true"
        aria-label="Slide 1"
      ></button>
      <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
      <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="../assets/paginaadso.png" class="d-block w-100" alt="...">
        <div class="carousel-caption d-none d-md-block">
          <div class="caption-box">
            <h5 contenteditable="true">First slide label</h5>
            <p>Some representative placeholder content for the first slide.</p>
          </div>
        </div>
      </div>
      <div class="carousel-item">
        <img src="../assets/paginauafenlinea.png" class="d-block w-100" alt="...">
        <div class="carousel-caption d-none d-md-block">
          <div class="caption-box">
            <h5 contenteditable="true">Second slide label</h5>
            <p>Some representative placeholder content for the second slide.</p>
          </div>
        </div>
      </div>
      <div class="carousel-item">
        <img src="../assets/paginaprincipal.png" class="d-block w-100" alt="...">
        <div class="carousel-caption d-none d-md-block">
          <div class="caption-box">
            <h5 contenteditable="true">Third slide label</h5>
            <p>Some representative placeholder content for the third slide.</p>
          </div>
        </div>
      </div>
    </div>
    <button
      class="carousel-control-prev"
      type="button"
      data-bs-target="#carouselExampleCaptions"
      data-bs-slide="prev"
    >
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button
      class="carousel-control-next"
      type="button"
      data-bs-target="#carouselExampleCaptions"
      data-bs-slide="next"
    >
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
</template>

<script>
export default {
  name: 'Carrusel_Webs'
}
</script>

<style scoped>
.carousel-item img {
  width: 100%;
  height: auto; /* Ajusta la altura de la imagen automáticamente */
  max-height: 500px; /* Establece la altura máxima según tus necesidades */
  object-fit: cover; /* Escala y recorta la imagen para llenar el contenedor */
  object-position: center; /* Centra la imagen */
}

.carousel {
  max-width: 800px; /* Ajusta el ancho máximo del carrusel */
  margin: 20px auto; /* Centra el carrusel horizontalmente y añade margen superior */
}

/* Cambia el color de las letras a negro */
.carousel-caption h5,
.carousel-caption p {
  color: #000;
}

/* Cambia el color de los botones de navegación a negro */
.carousel-control-prev-icon,
.carousel-control-next-icon {
  background-color: #000;
}

/* Estilo para el recuadro de la leyenda */
.caption-box {
  background: rgba(255, 255, 255, 0.7); /* Fondo blanco semitransparente */
  padding: 10px; /* Espaciado interno */
  border-radius: 5px; /* Bordes redondeados */
}
</style>
