# 📂 Intranet Corporativa – Gestión de Solicitudes y Aprobaciones

Sistema integral de gestión de solicitudes internas, diseñado para facilitar procesos de **permisos**, **vacaciones**, **misiones oficiales** y validaciones jerárquicas dentro de una organización.  
Desarrollado con **Laravel 10 (PHP 8.1)** en el backend y **Vue 3 + Composition API** en el frontend, siguiendo una arquitectura modular, segura y preparada para SQL Server.

---

## 🚀 Características Principales

- 📑 **Gestión de Solicitudes** (permisos, vacaciones, misiones oficiales)
- 🔗 **Flujos de Aprobación** basados en jerarquía de roles con **Spatie Laravel Permission**
- 👥 **Panel del colaborador, jefatura y Recursos Humanos**
- ✒️ **Firmas digitales por rol** integradas automáticamente en los PDF generados
- 🖨️ **Generación de documentos** con Blade + DomPDF
- 🔐 **Autenticación con Sanctum** y manejo granular de permisos
- 🖼️ **Carga y gestión de firma de usuario** mediante **Intervention Image**
- 📧 **Notificaciones inteligentes por correo** *(pendiente de implementación)*
- 📊 **Dashboard administrativo** con control de acceso y métricas clave
- 🗄️ **Soporte nativo para SQL Server**
- ⚙️ **Jobs & Queues** para generación y firma de PDFs en segundo plano

---

## 🛠️ Tecnologías

| Capa | Tecnologías |
|------|-------------|
| **Backend (Laravel 10 + PHP 8.1)** | Laravel Sanctum • Spatie Laravel Permission • DomPDF • Intervention Image • Jobs & Queues • API RESTful estructurada |
| **Frontend (Vue 3 + Composition API)** | Vuex • Vue Router • Axios (con interceptor Bearer) • Bootstrap 5 • FontAwesome • SweetAlert2 • Moment.js |

---

## 📁 Estructura del Proyecto

intranet-backend/ # Backend Laravel 10
│ ├── app/Http/Controllers/
│ ├── database/
│ └── resources/views/pdf/solicitud.blade.php
└─ intranet-frontend/ # Frontend Vue 3
├── src/
│ ├── router/index.js
│ ├── store/index.js
│ ├── views/
│ └── components/
└── public/

yaml
Copiar
Editar

---

## 🔙 Backend

### Controladores clave

| Controlador | Responsabilidad |
|-------------|-----------------|
| **AuthController** | Registro, login, logout. Valida datos y crea el token Sanctum. |
| **UserController** | Obtiene/edita datos del usuario, carga la firma digital y administra usuarios. |
| **SolicitudesController** | Registra solicitudes, calcula duración, ajusta tiempo extra y genera el PDF inicial. |
| **AprobacionesController** | Filtra y gestiona aprobaciones según rol; aprueba/rechaza y genera el PDF de aprobación. |
| **DocumentController** | Compila datos (incl. firmas) y crea el PDF final de la solicitud. |
| **BirthdayController** | Gestiona calendario de cumpleaños. |
| **RolesController** | Lista, crea y asigna roles (Spatie). |

### Seeds importantes

- **DepartamentoSeeder** – Catálogo de departamentos
- **TiposolicitudSeeder** – Tipos de solicitud
- **RolesAndPermissionsSeeder** – Roles iniciales
- **BirthdaySeeder** – Cumpleaños de colaboradores
- **UserSeeder** – Usuario administrador por defecto

### Principales migraciones

- **usuarios**, **solicitudes**, **aprobaciones** – Tablas básicas y relaciones
- **departamentos**, **tipos_solicitud** – Catálogos de referencia
- **documentos**, **notificaciones**, **eventos**, **auditorias** – PDFs generados, correos, calendarios y bitácoras

### Vistas Blade

- `resources/views/pdf/solicitud.blade.php` – Plantilla del documento PDF con campos, firmas y estilos

---

## 🔜 Frontend

### Núcleo de la aplicación

| Archivo | Función |
|---------|---------|
| `router/index.js` | Ruteo protegido y sub-rutas para colaborador, aprobaciones y admin |
| `store/index.js` | Gestión de autenticación (token), registro y logout con Axios |
| `main.js` | Bootstrap de la app, plugins (Bootstrap, SweetAlert2, FontAwesome) e interceptor Axios |

### Vistas principales

| Vista | Descripción |
|-------|-------------|
| **Login.vue / Register.vue** | Formularios con validaciones y animaciones |
| **MiEspacio.vue** | Datos del usuario, creación de solicitudes e historial |
| **PortalColaboradores.vue** | Calendario de cumpleaños y directorio; modales para agregar/exportar datos |
| **PortalAprobaciones.vue** | Tabs (pendientes, aceptadas, rechazadas); modales para ver PDF y aprobar/rechazar |
| **Administradores.vue** | Consolida **Roles.vue** y **Lista_Usuario_Rol.vue** para administración |

### Componentes destacados

| Componente | Funcionalidad |
|------------|---------------|
| **Solicitudes.vue** | Tarjetas por tipo de solicitud; formularios en modales (permiso, vacaciones, etc.) |
| **Lista_Aprobaciones.vue** | Tabla con aprobar/rechazar/ver PDF; modal de visualización |
| **Datos_Usuario.vue** | Información personal; edición y carga de firma digital mediante modales |

---