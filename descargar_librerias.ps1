# Script para descargar librerías CDN para modo offline
# Ejecutar con PowerShell como administrador

Write-Host "🚀 Descargando librerías para modo offline..." -ForegroundColor Green

# Crear estructura de carpetas
Write-Host "📁 Creando estructura de carpetas..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "assets\libs\bootstrap-5.3.0\css" | Out-Null
New-Item -ItemType Directory -Force -Path "assets\libs\bootstrap-5.3.0\js" | Out-Null
New-Item -ItemType Directory -Force -Path "assets\libs\font-awesome-6.4.0\css" | Out-Null
New-Item -ItemType Directory -Force -Path "assets\libs\chart.js-4.4.0" | Out-Null

# Descargar Bootstrap CSS
Write-Host "📦 Descargando Bootstrap CSS..." -ForegroundColor Cyan
try {
    Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" -OutFile "assets\libs\bootstrap-5.3.0\css\bootstrap.min.css"
    Write-Host "✅ Bootstrap CSS descargado" -ForegroundColor Green
} catch {
    Write-Host "❌ Error descargando Bootstrap CSS: $($_.Exception.Message)" -ForegroundColor Red
}

# Descargar Bootstrap JS
Write-Host "📦 Descargando Bootstrap JS..." -ForegroundColor Cyan
try {
    Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" -OutFile "assets\libs\bootstrap-5.3.0\js\bootstrap.bundle.min.js"
    Write-Host "✅ Bootstrap JS descargado" -ForegroundColor Green
} catch {
    Write-Host "❌ Error descargando Bootstrap JS: $($_.Exception.Message)" -ForegroundColor Red
}

# Descargar Font Awesome
Write-Host "📦 Descargando Font Awesome..." -ForegroundColor Cyan
try {
    Invoke-WebRequest -Uri "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" -OutFile "assets\libs\font-awesome-6.4.0\css\all.min.css"
    Write-Host "✅ Font Awesome descargado" -ForegroundColor Green
} catch {
    Write-Host "❌ Error descargando Font Awesome: $($_.Exception.Message)" -ForegroundColor Red
}

# Descargar Chart.js
Write-Host "📦 Descargando Chart.js..." -ForegroundColor Cyan
try {
    Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js" -OutFile "assets\libs\chart.js-4.4.0\chart.min.js"
    Write-Host "✅ Chart.js descargado" -ForegroundColor Green
} catch {
    Write-Host "❌ Error descargando Chart.js: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n🎉 ¡Descarga completada!" -ForegroundColor Green
Write-Host "📋 Próximos pasos:" -ForegroundColor Yellow
Write-Host "1. Abrir config_offline.php" -ForegroundColor White
Write-Host "2. Cambiar MODO_OFFLINE a true" -ForegroundColor White
Write-Host "3. Probar el sistema sin WiFi" -ForegroundColor White
Write-Host "`n💡 El sistema estará listo para presentación offline" -ForegroundColor Magenta






