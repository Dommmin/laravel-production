#!/bin/bash

set -e

# Definicja kolorów dla lepszej czytelności
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Rozpoczynam proces deploymentu...${NC}"

# Tworzenie wymaganych katalogów
echo -e "${GREEN}Tworzenie wymaganych katalogów...${NC}"
mkdir -p ./storage
mkdir -p ./docker/php
mkdir -p ./docker/nginx/conf.d
mkdir -p ./docker/nginx/ssl

# Logowanie do GitHub Container Registry
if [ -n "$GITHUB_PAT" ] && [ -n "$GITHUB_USER" ]; then
  echo -e "${GREEN}Logowanie do GitHub Container Registry...${NC}"
  echo "$GITHUB_PAT" | docker login ghcr.io -u "$GITHUB_USER" --password-stdin
else
  echo -e "${YELLOW}Zmienne GITHUB_PAT lub GITHUB_USER nie są ustawione. Pomijam logowanie do rejestru.${NC}"
  echo -e "${YELLOW}Jeśli obraz jest prywatny, logowanie może być wymagane.${NC}"
fi

# Pobieranie najnowszych obrazów
echo -e "${GREEN}Pobieranie najnowszych obrazów...${NC}"
docker compose pull || {
  echo -e "${RED}Błąd podczas pobierania obrazów!${NC}"
  echo -e "${YELLOW}Sprawdzam szczegóły obrazu...${NC}"

  # Pobierz wartości zmiennych środowiskowych
  source .env
  REGISTRY=${REGISTRY:-ghcr.io}
  IMAGE_NAME=${IMAGE_NAME:-dommmin/laravel-production}
  TAG=${TAG:-latest}
  TAG_BRANCH=${TAG_BRANCH:-main}

  # Najpierw próbujemy pobrać obraz z tagiem "latest"
  FULL_IMAGE_NAME="${REGISTRY}/${IMAGE_NAME}:${TAG}"
  echo -e "${YELLOW}Próba pobrania obrazu: ${FULL_IMAGE_NAME}${NC}"

  if docker pull "${FULL_IMAGE_NAME}"; then
    echo -e "${GREEN}Obraz został pomyślnie pobrany: ${FULL_IMAGE_NAME}${NC}"
  else
    echo -e "${YELLOW}Nie można pobrać obrazu: ${FULL_IMAGE_NAME}${NC}"
    echo -e "${YELLOW}Próbuję pobrać obraz z tagiem gałęzi: ${TAG_BRANCH}${NC}"

    # Jeśli nie udało się pobrać z tagiem "latest", próbujemy z tagiem gałęzi
    BRANCH_IMAGE_NAME="${REGISTRY}/${IMAGE_NAME}:${TAG_BRANCH}"

    if docker pull "${BRANCH_IMAGE_NAME}"; then
      echo -e "${GREEN}Obraz został pomyślnie pobrany: ${BRANCH_IMAGE_NAME}${NC}"

      echo -e "${YELLOW}Tagowanie obrazu jako ${FULL_IMAGE_NAME}${NC}"
      docker tag "${BRANCH_IMAGE_NAME}" "${FULL_IMAGE_NAME}"
    else
      echo -e "${RED}Nie można pobrać obrazu ani z tagiem ${TAG}, ani z ${TAG_BRANCH}${NC}"
      echo -e "${YELLOW}Sprawdź, czy obraz istnieje w rejestrze i czy masz odpowiednie uprawnienia.${NC}"

      # Wyświetl dostępne tagi dla obrazu
      echo -e "${YELLOW}Dostępne tagi dla ${REGISTRY}/${IMAGE_NAME}:${NC}"
      curl -s -H "Authorization: Bearer ${GITHUB_PAT}" https://api.github.com/user/packages/container/${IMAGE_NAME}/versions | grep '"tags":'

      exit 1
    fi
  fi
}

echo -e "${GREEN}Uruchamianie kontenerów...${NC}"
docker compose up -d

if docker compose ps | grep -q "Up"; then
  echo -e "${GREEN}Kontenery uruchomione pomyślnie.${NC}"
else
  echo -e "${RED}Niektóre kontenery mogą nie działać poprawnie. Sprawdź logi.${NC}"
  docker compose logs
fi

# Wykonanie zadań po deploymencie (migracje, cache, itd.)
echo -e "${GREEN}Wykonywanie zadań po deploymencie...${NC}"
PROJECT_NAME=$(grep COMPOSE_PROJECT_NAME .env | cut -d= -f2)
APP_CONTAINER="${PROJECT_NAME}_app"

echo -e "${YELLOW}Uruchamianie migracji bazy danych...${NC}"
docker exec -i $APP_CONTAINER php artisan migrate --force || echo -e "${RED}Migracja nie powiodła się.${NC}"

echo -e "${YELLOW}Czyszczenie cache...${NC}"
docker exec -i $APP_CONTAINER php artisan config:cache || echo -e "${RED}Czyszczenie cache konfiguracji nie powiodło się.${NC}"
docker exec -i $APP_CONTAINER php artisan route:cache || echo -e "${RED}Czyszczenie cache tras nie powiodło się.${NC}"
docker exec -i $APP_CONTAINER php artisan view:cache || echo -e "${RED}Czyszczenie cache widoków nie powiodło się.${NC}"

# Czyszczenie nieużywanych obrazów
echo -e "${GREEN}Usuwanie nieużywanych obrazów...${NC}"
docker image prune -f

echo -e "${GREEN}Deployment zakończony pomyślnie!${NC}"
