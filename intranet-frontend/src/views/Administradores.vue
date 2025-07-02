<!-- Administradores.vue -->
<template>
  <div class="admin-container">
    <!-- Dashboard de Métricas -->
    <div class="dashboard-stats mb-4">
      <div class="stat-card">
        <div class="stat-icon">
          <font-awesome-icon :icon="['fas', 'users']" />
        </div>
        <div class="stat-content">
          <h3>{{ totalUsers }}</h3>
          <p>Usuarios Totales</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <font-awesome-icon :icon="['fas', 'shield-alt']" />
        </div>
        <div class="stat-content">
          <h3>{{ totalRoles }}</h3>
          <p>Roles Activos</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <font-awesome-icon :icon="['fas', 'user-check']" />
        </div>
        <div class="stat-content">
          <h3>{{ activeUsers }}</h3>
          <p>Usuarios Activos</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <font-awesome-icon :icon="['fas', 'clock']" />
        </div>
        <div class="stat-content">
          <h3>{{ recentActivity }}</h3>
          <p>Cambios Hoy</p>
        </div>
      </div>
    </div>

    <!-- Navegación por Tabs -->
    <div class="tab-navigation mb-4">
      <button
          class="tab-button"
          :class="{ active: activeTab === 'roles' }"
          @click="activeTab = 'roles'"
      >
        <font-awesome-icon :icon="['fas', 'shield-alt']" class="me-2" />
        Gestión de Roles
      </button>
      <button
          class="tab-button"
          :class="{ active: activeTab === 'users' }"
          @click="activeTab = 'users'"
      >
        <font-awesome-icon :icon="['fas', 'users']" class="me-2" />
        Gestión de Usuarios
      </button>
      <button
          class="tab-button"
          :class="{ active: activeTab === 'activity' }"
          @click="activeTab = 'activity'"
      >
        <font-awesome-icon :icon="['fas', 'history']" class="me-2" />
        Actividad Reciente
      </button>
    </div>

    <!-- Contenido de Tabs -->
    <transition name="fade" mode="out-in">
      <div v-if="activeTab === 'roles'" key="roles">
        <Roles @update-stats="updateStats" />
      </div>
      <div v-else-if="activeTab === 'users'" key="users">
        <Lista_Usuario_Rol @update-stats="updateStats" />
      </div>
      <div v-else-if="activeTab === 'activity'" key="activity">
        <!-- Widget de Actividad Reciente como Tab -->
        <div class="activity-widget">
          <h4 class="widget-title">
            <font-awesome-icon :icon="['fas', 'history']" class="me-2" />
            Historial de Actividades
          </h4>
          <div class="activity-list">
            <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
              <div class="activity-icon" :class="activity.type">
                <font-awesome-icon :icon="activity.icon" />
              </div>
              <div class="activity-content">
                <p class="activity-text">{{ activity.description }}</p>
                <small class="activity-time">{{ activity.time }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import Roles from '@/components/Roles.vue';
import Lista_Usuario_Rol from '@/components/Lista_Usuario_Rol.vue';

export default {
  components: {
    Roles,
    Lista_Usuario_Rol,
  },
  data() {
    return {
      activeTab: 'roles',
      totalUsers: 0,
      totalRoles: 0,
      activeUsers: 0,
      recentActivity: 0,
      recentActivities: [
        {
          id: 1,
          type: 'user-add',
          icon: ['fas', 'user-plus'],
          description: 'Nuevo usuario agregado al sistema',
          time: 'Hace 5 minutos'
        },
        {
          id: 2,
          type: 'role-assign',
          icon: ['fas', 'user-tag'],
          description: 'Rol asignado a usuario',
          time: 'Hace 15 minutos'
        },
        {
          id: 3,
          type: 'user-update',
          icon: ['fas', 'user-edit'],
          description: 'Información de usuario actualizada',
          time: 'Hace 1 hora'
        }
      ]
    };
  },
  methods: {
    updateStats(stats) {
      if (stats) {
        this.totalUsers = stats.totalUsers || this.totalUsers;
        this.totalRoles = stats.totalRoles || this.totalRoles;
        this.activeUsers = stats.activeUsers || this.activeUsers;
        this.recentActivity = stats.recentActivity || this.recentActivity;
      }
    }
  },
  mounted() {
    // Aquí podrías cargar estadísticas reales desde el backend
  }
};
</script>

<style scoped>
.admin-container {
  padding: 20px 0;
}

/* Dashboard Stats */
.dashboard-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
  border: 1px solid #e9ecef;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
  font-size: 24px;
}

.stat-card:nth-child(1) .stat-icon {
  background: #e3f2fd;
  color: #1976d2;
}

.stat-card:nth-child(2) .stat-icon {
  background: #f3e5f5;
  color: #7b1fa2;
}

.stat-card:nth-child(3) .stat-icon {
  background: #e8f5e9;
  color: #388e3c;
}

.stat-card:nth-child(4) .stat-icon {
  background: #fff3e0;
  color: #f57c00;
}

.stat-content h3 {
  margin: 0;
  font-size: 28px;
  font-weight: 600;
  color: #2c3e50;
}

.stat-content p {
  margin: 5px 0 0;
  color: #6c757d;
  font-size: 14px;
}

/* Tab Navigation */
.tab-navigation {
  display: flex;
  gap: 10px;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 0;
}

.tab-button {
  background: none;
  border: none;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: 500;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
}

.tab-button:hover {
  color: #007bff;
  background: #f8f9fa;
}

.tab-button.active {
  color: #007bff;
  border-bottom-color: #007bff;
  background: transparent;
}

/* Activity Widget */
.activity-widget {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e9ecef;
}

.widget-title {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 20px;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.activity-item {
  display: flex;
  align-items: center;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.activity-item:hover {
  background: #e9ecef;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  font-size: 16px;
}

.activity-icon.user-add {
  background: #e8f5e9;
  color: #388e3c;
}

.activity-icon.role-assign {
  background: #e3f2fd;
  color: #1976d2;
}

.activity-icon.user-update {
  background: #fff3e0;
  color: #f57c00;
}

.activity-content {
  flex: 1;
}

.activity-text {
  margin: 0;
  color: #2c3e50;
  font-size: 14px;
}

.activity-time {
  color: #6c757d;
  font-size: 12px;
}

/* Fade Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>