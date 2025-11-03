#!/bin/bash

echo "🚀 Starting Notes App in Production Mode..."
echo ""

# Detect if Podman or Docker is available
USE_PODMAN=false
USE_DOCKER=false

if command -v podman &> /dev/null; then
    USE_PODMAN=true
    echo "✓ Detected Podman"
elif command -v docker &> /dev/null; then
    USE_DOCKER=true
    echo "✓ Detected Docker"
else
    echo "❌ Neither Podman nor Docker found. Please install one of them."
    exit 1
fi

echo ""

# Choose the right compose command and file
if [ "$USE_PODMAN" = true ]; then
    COMPOSE_CMD="podman-compose"
    COMPOSE_FILE="podman-compose.yml"
    EXEC_CMD="podman"
    echo "Using Podman Compose..."
else
    COMPOSE_CMD="docker-compose"
    COMPOSE_FILE="docker-compose.yml"
    EXEC_CMD="docker"
    echo "Using Docker Compose..."
fi

# Build and start containers
echo "Building and starting containers..."
$COMPOSE_CMD -f $COMPOSE_FILE up -d --build

# Wait for MySQL to be ready
echo ""
echo "⏳ Waiting for MySQL to be ready..."
sleep 15

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
$EXEC_CMD exec notes_php composer install

echo ""
echo "✅ Production environment is ready!"
echo ""
echo "📝 Access the app at: http://localhost"
echo "🔐 Default login: admin / admin"
echo ""
echo "⚠️  IMPORTANT: Change JWT_SECRET and database passwords for production!"
echo ""
echo "📊 Container status:"
$EXEC_CMD ps | grep notes

echo ""
echo "Useful commands:"
echo "  View logs: $COMPOSE_CMD -f $COMPOSE_FILE logs -f"
echo "  Stop: $COMPOSE_CMD -f $COMPOSE_FILE down"
echo "  Restart: $COMPOSE_CMD -f $COMPOSE_FILE restart"
