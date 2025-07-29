// resources/js/composables/useNotifications.js
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

export function useNotifications() {
    const notificaciones = ref([])
    const totalNotificaciones = ref(0)
    const ultimaConsulta = ref(null)
    const pollingInterval = ref(null)
    const isPolling = ref(false)

    // Configuración
    const POLLING_INTERVAL = 10000 // 10 segundos
    const MAX_NOTIFICACIONES_MOSTRAR = 5

    /**
     * Iniciar el polling de notificaciones
     */
    const iniciarPolling = () => {
        if (isPolling.value) return

        isPolling.value = true
        console.log('🔔 Iniciando polling de notificaciones...')

        // Primera consulta inmediata
        obtenerNotificaciones()

        // Configurar intervalo
        pollingInterval.value = setInterval(() => {
            obtenerNotificaciones()
        }, POLLING_INTERVAL)
    }

    /**
     * Detener el polling
     */
    const detenerPolling = () => {
        if (pollingInterval.value) {
            clearInterval(pollingInterval.value)
            pollingInterval.value = null
        }
        isPolling.value = false
        console.log('🔔 Polling de notificaciones detenido')
    }

    /**
     * Obtener notificaciones del servidor
     */
    const obtenerNotificaciones = async () => {
        try {
            const params = {}
            if (ultimaConsulta.value) {
                params.ultima_consulta = ultimaConsulta.value
            }

            const response = await axios.get('/api/notificaciones/recientes', { params })
            
            if (response.data.notificaciones.length > 0) {
                // Agregar nuevas notificaciones
                const nuevasNotificaciones = response.data.notificaciones
                
                // Mostrar notificaciones nuevas
                nuevasNotificaciones.forEach(notificacion => {
                    mostrarNotificacion(notificacion)
                })

                // Actualizar lista (mantener solo las más recientes)
                notificaciones.value = [
                    ...nuevasNotificaciones,
                    ...notificaciones.value
                ].slice(0, MAX_NOTIFICACIONES_MOSTRAR)

                console.log(`🔔 ${nuevasNotificaciones.length} nuevas notificaciones recibidas`)
            }

            // Actualizar timestamp
            ultimaConsulta.value = response.data.timestamp
            totalNotificaciones.value = response.data.total

        } catch (error) {
            console.error('Error obteniendo notificaciones:', error)
            
            // Si hay error de autenticación, detener polling
            if (error.response?.status === 401) {
                detenerPolling()
            }
        }
    }

    /**
     * Mostrar notificación visual
     */
    const mostrarNotificacion = (notificacion) => {
        // 1. Toast notification con SweetAlert
        if (window.Swal) {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                icon: getIconForType(notificacion.tipo),
                title: notificacion.icono + ' ' + getTitleForType(notificacion.tipo),
                text: notificacion.mensaje,
                background: getBackgroundForType(notificacion.tipo),
                color: '#fff',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', window.Swal.stopTimer)
                    toast.addEventListener('mouseleave', window.Swal.resumeTimer)
                }
            })
        }

        // 2. Notificación del navegador (si está permitido)
        mostrarNotificacionNavegador(notificacion)

        // 3. Log para debugging
        console.log('🔔 Notificación mostrada:', {
            tipo: notificacion.tipo,
            mensaje: notificacion.mensaje,
            tiempo: notificacion.tiempo_transcurrido
        })
    }

    /**
     * Mostrar notificación del navegador
     */
    const mostrarNotificacionNavegador = (notificacion) => {
        if ("Notification" in window && Notification.permission === "granted") {
            try {
                new Notification(notificacion.icono + " " + getTitleForType(notificacion.tipo), {
                    body: notificacion.mensaje,
                    icon: '/favicon.ico',
                    tag: `pago-${notificacion.id}`, // Evita duplicados
                    requireInteraction: notificacion.tipo === 'pago_exitoso' // Mantener visible si es pago exitoso
                    // Removemos 'actions' que causa el error
                })
            } catch (error) {
                console.warn('Error mostrando notificación del navegador:', error)
                // La notificación Toast seguirá funcionando
            }
        }
    }

    /**
     * Solicitar permiso para notificaciones del navegador
     */
    const solicitarPermisoNotificaciones = async () => {
        if (!("Notification" in window)) {
            console.log("Este navegador no soporta notificaciones")
            return false
        }

        if (Notification.permission === "granted") {
            return true
        }

        if (Notification.permission !== "denied") {
            const permission = await Notification.requestPermission()
            return permission === "granted"
        }

        return false
    }

    /**
     * Obtener ícono para SweetAlert según tipo
     */
    const getIconForType = (tipo) => {
        const iconMap = {
            'pago_exitoso': 'success',
            'pago_fallido': 'error',
            'pago_pendiente': 'info',
            'contrato_vencido': 'warning',
            'mantenimiento': 'info',
            'sistema': 'info'
        }
        return iconMap[tipo] || 'info'
    }

    /**
     * Obtener título según tipo
     */
    const getTitleForType = (tipo) => {
        const titleMap = {
            'pago_exitoso': 'Pago Exitoso',
            'pago_fallido': 'Pago Fallido',
            'pago_pendiente': 'Pago Pendiente',
            'contrato_vencido': 'Contrato Vencido',
            'mantenimiento': 'Mantenimiento',
            'sistema': 'Notificación del Sistema'
        }
        return titleMap[tipo] || 'Notificación'
    }

    /**
     * Obtener color de fondo según tipo
     */
    const getBackgroundForType = (tipo) => {
        const colorMap = {
            'pago_exitoso': '#10b981',
            'pago_fallido': '#ef4444',
            'pago_pendiente': '#f59e0b',
            'contrato_vencido': '#f97316',
            'mantenimiento': '#3b82f6',
            'sistema': '#6b7280'
        }
        return colorMap[tipo] || '#3b82f6'
    }

    /**
     * Método para testing - crear notificación de prueba
     */
    const crearNotificacionPrueba = async () => {
        try {
            const response = await axios.post('/api/notificaciones/prueba')
            console.log('Notificación de prueba creada:', response.data)
            return response.data
        } catch (error) {
            console.error('Error creando notificación de prueba:', error)
            throw error
        }
    }

    // Cleanup automático
    onUnmounted(() => {
        detenerPolling()
    })

    return {
        // State
        notificaciones,
        totalNotificaciones,
        isPolling,
        
        // Methods
        iniciarPolling,
        detenerPolling,
        obtenerNotificaciones,
        solicitarPermisoNotificaciones,
        crearNotificacionPrueba
    }
}