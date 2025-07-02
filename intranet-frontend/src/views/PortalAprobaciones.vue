<template>
  <div class="portal-aprobaciones">
    <h1 class="portal-title">Portal de Aprobaciones</h1>
    <div class="tab-navigation">
      <button 
        v-for="tab in tabs" 
        :key="tab.id" 
        @click="currentTab = tab.id"
        :class="['tab-button', { active: currentTab === tab.id }]"
      >
        {{ tab.name }}
      </button>
    </div>
    <div class="tab-content">
      <transition name="fade" mode="out-in">
        <keep-alive>
          <component :is="currentTabComponent"></component>
        </keep-alive>
      </transition>
    </div>
  </div>
</template>

<script>
import Lista_Aprobaciones from '@/components/Lista_Aprobaciones.vue';
import Solicitudes_Rechazadas from '@/components/Solicitudes_Rechazadas.vue';
import Solicitudes_Aceptadas from '@/components/Solicitudes_Aceptadas.vue';

export default {
  name: 'PortalAprobaciones',
  components: {
    Lista_Aprobaciones,
    Solicitudes_Rechazadas,
    Solicitudes_Aceptadas
  },
  data() {
    return {
      currentTab: 'pendientes',
      tabs: [
        { id: 'pendientes', name: 'Pendientes', component: Lista_Aprobaciones },
        { id: 'aceptadas', name: 'Aceptadas', component: Solicitudes_Aceptadas },
        { id: 'rechazadas', name: 'Rechazadas', component: Solicitudes_Rechazadas }
      ]
    }
  },
  computed: {
    currentTabComponent() {
      const tab = this.tabs.find(tab => tab.id === this.currentTab);
      return tab ? tab.component : null;
    }
  }
}
</script>

<style scoped>
.portal-aprobaciones {
  padding: 1rem 2rem;
  background-color: #f0f2f5;
  min-height: calc(85vh - 60px); /* Ajusta esto según el tamaño de tu footer */
  display: flex;
  flex-direction: column;
}

.portal-title {
  color: #1050a9;
  font-size: 1.5rem;
  margin-bottom: 1rem;
  text-align: center;
}

.tab-navigation {
  display: flex;
  justify-content: center;
  margin-bottom: 1rem;
}

.tab-button {
  background-color: #ffffff;
  border: none;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tab-button:first-child {
  border-radius: 8px 0 0 8px;
}

.tab-button:last-child {
  border-radius: 0 8px 8px 0;
}

.tab-button.active {
  background-color: #1050a9;
  color: #ffffff;
}

.tab-content {
  background-color: #ffffff;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  flex-grow: 1;
  overflow-y: auto;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .tab-navigation {
    flex-direction: column;
  }
  .tab-button {
    width: 100%;
    border-radius: 0;
  }
  .tab-button:first-child {
    border-radius: 8px 8px 0 0;
  }
  .tab-button:last-child {
    border-radius: 0 0 8px 8px;
  }
}
</style>