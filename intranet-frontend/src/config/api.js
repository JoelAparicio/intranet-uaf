const API_BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000/api'

export default {
    baseURL: API_BASE_URL,
    endpoints: {
        // ==================== AUTENTICACIÓN ====================
        login: 'login',
        register: 'register',
        logout: 'logout',

        // ==================== USUARIO ====================
        user: 'user',
        userInfo: 'information_user',
        editUser: 'edit_information',
        uploadFirma: 'upload-firma',
        directorio: 'directorio_usuarios',
        listarUsuarios: 'listar_usuarios',

        // ==================== ROLES ====================
        rolesUsuario: 'roles_usuario',
        roles: 'roles',
        agregarRoles: 'agregar_roles',
        asignarRol: 'asignar_rol',
        eliminarRol: 'eliminar_rol',
        desasignarRol: 'desasignar_rol',

        // ==================== CUMPLEAÑOS ====================
        birthdays: 'birthdays',
        addBirthday: 'addBirthday',

        // ==================== SOLICITUDES ====================
        listarSolicitud: 'listar_solicitud',
        insertarSolicitud: 'insertar_solicitud',
        historialSolicitud: 'historial_solicitud',
        solicitud: 'solicitud',
        actualizarSolicitud: 'actualizar_solicitud',
        obtenerRutaPdf: 'obtener_ruta_pdf',

        // ==================== APROBACIONES ====================
        listarAprobaciones: 'listar_aprobaciones',
        aprobarSolicitud: 'aprobar-solicitud',
        rechazarSolicitud: 'rechazar-solicitud',
        solicitudesRechazadas: 'solicitudes-rechazadas',
        solicitudesAceptadas: 'solicitudes-aceptadas',

        // ==================== ADMINISTRACIÓN ====================
        administrarUsuarios: 'administrar_usuarios',
        borrarUsuario: 'borrar_usuario',
        actualizarUsuario: 'actualizar_usuario',
        statusUsuario: 'status_usuario'
    }
}