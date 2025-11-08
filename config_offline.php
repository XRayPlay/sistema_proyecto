<?php
// Configuración para modo offline
// Cambiar a true cuando no haya conexión a internet
define('MODO_OFFLINE', false); // DESACTIVADO - USAR CDN

// URLs locales para las librerías
if (MODO_OFFLINE) {
    // Bootstrap CSS
    define('BOOTSTRAP_CSS', 'assets/libs/bootstrap-5.3.0/css/bootstrap.min.css');
    
    // Bootstrap JS
    define('BOOTSTRAP_JS', 'assets/libs/bootstrap-5.3.0/js/bootstrap.bundle.min.js');
    
    // Font Awesome
    define('FONT_AWESOME_CSS', 'assets/libs/font-awesome-6.4.0/css/all.min.css');
    
    // Google Fonts (usar fuente del sistema)
    define('GOOGLE_FONTS', '');
    
    // Chart.js
    define('CHART_JS', 'assets/libs/chart.js-4.4.0/chart.min.js');
} else {
    // URLs CDN (modo online)
    define('BOOTSTRAP_CSS', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    define('BOOTSTRAP_JS', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js');
    define('FONT_AWESOME_CSS', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
    define('GOOGLE_FONTS', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    define('CHART_JS', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js');
}

// Función para incluir CSS
function incluirCSS($url) {
    if (!empty($url)) {
        echo '<link href="' . $url . '" rel="stylesheet">' . "\n";
    }
}

// Función para incluir JS
function incluirJS($url) {
    if (!empty($url)) {
        echo '<script src="' . $url . '"></script>' . "\n";
    }
}
?>
