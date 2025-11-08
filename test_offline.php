<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Modo Offline - Sistema ecosocialismo</title>
    <?php require_once 'config_offline.php'; ?>
    <?php incluirCSS(BOOTSTRAP_CSS); ?>
    <?php incluirCSS(FONT_AWESOME_CSS); ?>
    <style>
        body {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .test-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            margin: 2rem 0;
        }
        .status-ok { color: #10b981; }
        .status-error { color: #ef4444; }
        .status-warning { color: #f59e0b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="test-card text-center">
                    <h1 class="mb-4">
                        <i class="fas fa-wifi-slash"></i>
                        Test de Modo Offline
                    </h1>
                    
                    <div class="alert alert-info">
                        <h4>🎯 Sistema ecosocialismo - Modo Offline</h4>
                        <p class="mb-0">Verificando que todas las librerías funcionen sin conexión a internet</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5><i class="fas fa-cog"></i> Configuración</h5>
                                    <p><strong>Modo Offline:</strong> 
                                        <?php if (MODO_OFFLINE): ?>
                                            <span class="status-ok">✅ ACTIVADO</span>
                                        <?php else: ?>
                                            <span class="status-error">❌ DESACTIVADO</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5><i class="fas fa-file-code"></i> Librerías</h5>
                                    <p><strong>Bootstrap:</strong> 
                                        <?php if (file_exists(BOOTSTRAP_CSS)): ?>
                                            <span class="status-ok">✅ Disponible</span>
                                        <?php else: ?>
                                            <span class="status-error">❌ No encontrado</span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Font Awesome:</strong> 
                                        <?php if (file_exists(FONT_AWESOME_CSS)): ?>
                                            <span class="status-ok">✅ Disponible</span>
                                        <?php else: ?>
                                            <span class="status-error">❌ No encontrado</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Test de Bootstrap</h5>
                        <p>Si ves este botón funcionando, Bootstrap está cargado correctamente:</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#testModal">
                            <i class="fas fa-play"></i> Probar Modal
                        </button>
                    </div>

                    <div class="alert alert-warning">
                        <h5><i class="fas fa-info-circle"></i> Instrucciones</h5>
                        <ol class="text-start">
                            <li>Desconecta el WiFi</li>
                            <li>Recarga esta página</li>
                            <li>Verifica que todo funcione</li>
                            <li>Si todo está ✅, el sistema está listo para presentación</li>
                        </ol>
                    </div>

                    <div class="mt-4">
                        <a href="login.php" class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Ir al Sistema
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de prueba -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✅ Test Exitoso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¡Perfecto! Bootstrap está funcionando correctamente en modo offline.</p>
                    <p><i class="fas fa-check-circle text-success"></i> El sistema está listo para la presentación.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php incluirJS(BOOTSTRAP_JS); ?>
</body>
</html>






