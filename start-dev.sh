#!/bin/bash

echo "🚀 Starting Notes App in Development Mode..."

# Build and start containers
podman-compose -f docker-compose.dev.yml up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
podman exec notes_php_dev composer install

echo "✅ Development environment is ready!"
echo ""
echo "📝 Access the app at: http://localhost:8080"
echo "🔐 Default login: admin / admin"
echo ""
echo "📊 Container status:"
podman ps | grep notes

echo ""
echo "To view logs: podman-compose -f docker-compose.dev.yml logs -f"
echo "To stop: podman-compose -f docker-compose.dev.yml down"
