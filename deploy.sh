#!/bin/bash

set -e

echo "Pulling latest images..."
docker-compose pull

echo "Restarting containers..."
docker-compose up -d

echo "Pruning unused images..."
docker image prune -f

echo "Deployment complete."
