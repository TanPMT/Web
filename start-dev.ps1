# 🚀 Starting Notes App in Development Mode...

Write-Host "🚀 Starting Notes App in Development Mode..." -ForegroundColor Green

# Build and start containers
podman-compose -f docker-compose.dev.yml up -d

# Wait for MySQL to be ready
Write-Host "⏳ Waiting for MySQL to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Install PHP dependencies
Write-Host "📦 Installing PHP dependencies..." -ForegroundColor Cyan
podman exec notes_php_dev composer install

Write-Host ""
Write-Host "✅ Development environment is ready!" -ForegroundColor Green
Write-Host ""
Write-Host "📝 Access the app at: http://localhost:8080" -ForegroundColor White
Write-Host "🔐 Default login: admin / admin" -ForegroundColor White
Write-Host ""
Write-Host "📊 Container status:" -ForegroundColor Cyan
podman ps | Select-String "notes"

Write-Host ""
Write-Host "To view logs: podman-compose -f docker-compose.dev.yml logs -f" -ForegroundColor Gray
Write-Host "To stop: podman-compose -f docker-compose.dev.yml down" -ForegroundColor Gray
