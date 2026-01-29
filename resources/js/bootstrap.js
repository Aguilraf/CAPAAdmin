import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Interceptor global para manejar errores 419 (CSRF Token Expired)
 * Detecta automáticamente cuando el token expira y recarga la página
 */
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            // Token CSRF expirado - mostrar mensaje y recargar
            alert(
                '⚠️ Tu sesión ha expirado por seguridad.\n\n' +
                'La página se recargará automáticamente.\n\n' +
                'Por favor, vuelve a intentar tu acción después de la recarga.'
            );

            // Recargar la página para obtener un nuevo token CSRF
            setTimeout(() => {
                window.location.reload();
            }, 500);

            // Retornar una promesa que nunca se resuelve para evitar que el código continúe
            return new Promise(() => { });
        }

        // Para otros errores, rechazar normalmente
        return Promise.reject(error);
    }
);
