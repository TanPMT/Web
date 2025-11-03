# 🚀 Starting Notes App in Production Mode...

Write-Host "🚀 Starting Notes App in Production Mode..." -ForegroundColor Green
Write-Host ""

# Detect if Podman or Docker is available
$usePodman = $false
$useDocker = $false

try {
    podman --version | Out-Null
    $usePodman = $true
    Write-Host "✓ Detected Podman" -ForegroundColor Cyan
} catch {
    Write-Host "ℹ Podman not found, checking Docker..." -ForegroundColor Yellow
}

if (-not $usePodman) {
    try {
        docker --version | Out-Null
        $useDocker = $true
        Write-Host "✓ Detected Docker" -ForegroundColor Cyan
    } catch {
        Write-Host "❌ Neither Podman nor Docker found. Please install one of them." -ForegroundColor Red
        exit 1
    }
}

Write-Host ""

# Choose the right compose command and file
if ($usePodman) {
    $composeCmd = "podman-compose"
    $composeFile = "podman-compose.yml"
    $execCmd = "podman"
    Write-Host "Using Podman Compose..." -ForegroundColor Cyan
} else {
    $composeCmd = "docker-compose"
    $composeFile = "docker-compose.yml"
    $execCmd = "docker"
    Write-Host "Using Docker Compose..." -ForegroundColor Cyan
}

# Build and start containers
Write-Host "Building and starting containers..." -ForegroundColor Yellow
& $composeCmd -f $composeFile up -d --build

# Wait for MySQL to be ready
Write-Host ""
Write-Host "⏳ Waiting for MySQL to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 15

# Install PHP dependencies
Write-Host "📦 Installing PHP dependencies..." -ForegroundColor Cyan
& $execCmd exec notes_php composer install

Write-Host ""
Write-Host "✅ Production environment is ready!" -ForegroundColor Green
Write-Host ""
Write-Host "📝 Access the app at: http://localhost" -ForegroundColor White
Write-Host "🔐 Default login: admin / admin" -ForegroundColor White
Write-Host ""
Write-Host "⚠️  IMPORTANT: Change JWT_SECRET and database passwords for production!" -ForegroundColor Red
Write-Host ""
Write-Host "📊 Container status:" -ForegroundColor Cyan
& $execCmd ps | Select-String "notes"

Write-Host ""
Write-Host "Useful commands:" -ForegroundColor Gray
Write-Host "  View logs: $composeCmd -f $composeFile logs -f" -ForegroundColor Gray
Write-Host "  Stop: $composeCmd -f $composeFile down" -ForegroundColor Gray
Write-Host "  Restart: $composeCmd -f $composeFile restart" -ForegroundColor Gray
