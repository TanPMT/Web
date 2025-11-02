# 🚀 Starting Notes App in Production Mode...

Write-Host "🚀 Starting Notes App in Production Mode..." -ForegroundColor Green

# Build and start containers
podman-compose up -d

# Wait for MySQL to be ready
Write-Host "⏳ Waiting for MySQL to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Install PHP dependencies
Write-Host "📦 Installing PHP dependencies..." -ForegroundColor Cyan
podman exec notes_php composer install

Write-Host ""
Write-Host "✅ Production environment is ready!" -ForegroundColor Green
Write-Host ""
Write-Host "📝 Access the app at: http://localhost" -ForegroundColor White
Write-Host "🔐 Default login: admin / admin" -ForegroundColor White
Write-Host ""
Write-Host "⚠️  IMPORTANT: Change JWT_SECRET and database passwords for production!" -ForegroundColor Red
Write-Host ""
Write-Host "📊 Container status:" -ForegroundColor Cyan
podman ps | Select-String "notes"

Write-Host ""
Write-Host "To view logs: podman-compose logs -f" -ForegroundColor Gray
Write-Host "To stop: podman-compose down" -ForegroundColor Gray
