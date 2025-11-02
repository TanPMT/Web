#!/bin/bash

echo "🚀 Starting Notes App in Production Mode..."

# Build and start containers
podman-compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
podman exec notes_php composer install

echo "✅ Production environment is ready!"
echo ""
echo "📝 Access the app at: http://localhost"
echo "🔐 Default login: admin / admin"
echo ""
echo "⚠️  IMPORTANT: Change JWT_SECRET and database passwords for production!"
echo ""
echo "📊 Container status:"
podman ps | grep notes

echo ""
echo "To view logs: podman-compose logs -f"
echo "To stop: podman-compose down"
