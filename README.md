# Laravel Project

## Środowisko deweloperskie

### Wymagania
- Docker
- Docker Compose
- Make (opcjonalnie)

### Pierwsze uruchomienie

1. Sklonuj repozytorium:
```bash
git clone https://github.com/your-username/laravel-project.git
cd laravel-project
```

2. Konfiguracja środowiska (jednorazowo):
```bash
make setup
```

LUB jeśli nie używasz Make:

```bash
cp .env.local .env
docker compose build
docker compose up -d
docker compose exec app composer install
docker compose exec app npm install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### Codzienne użytkowanie

- Uruchomienie środowiska:
```bash
make up
```

- Zatrzymanie środowiska:
```bash
make down
```

- Dostęp do powłoki kontenera:
```bash
make shell
```

- Uruchomienie serwera Vite (live reload):
```bash
make vite
```

- Uruchomienie testów:
```bash
make test
```

- Wyczyszczenie cache:
```bash
make clear
```

## Środowisko produkcyjne

### Architektura produkcyjna

W środowisku produkcyjnym używamy zoptymalizowanych kontenerów Docker z obrazami Alpine dla mniejszego rozmiaru i poprawy bezpieczeństwa:

- **PHP-FPM Alpine**: Kontener aplikacji Laravel z zainstalowanym Supervisord do zarządzania procesami
- **Nginx Alpine**: Serwer WWW z konfiguracją SSL
- **Redis Alpine**: Cache i kolejki

**Baza danych**: Zalecamy używanie zewnętrznej usługi bazodanowej (np. Amazon RDS, DigitalOcean Managed Databases) zamiast kontenera dla lepszej wydajności, niezawodności i łatwości zarządzania.

### Wdrażanie na produkcję

#### Metoda 1: GitHub Actions CI/CD (zalecana)

Ten projekt zawiera gotowy pipeline CI/CD w GitHub Actions:

1. Ustaw następujące sekrety w repozytorium GitHub:
   - `PRODUCTION_HOST`: Adres IP/hostname serwera produkcyjnego
   - `PRODUCTION_USERNAME`: Nazwa użytkownika na serwerze
   - `PRODUCTION_SSH_KEY`: Klucz SSH do logowania na serwer
   - `ENV_PRODUCTION`: Zawartość pliku .env dla produkcji

2. Po każdym push do gałęzi `main`, aplikacja zostanie automatycznie:
   - Przetestowana
   - Zbudowana jako obraz Docker
   - Wdrożona na serwer produkcyjny

#### Metoda 2: Ręczne wdrażanie

1. Zbuduj lokalnie obraz produkcyjny:
```bash
docker build -t yourregistry/laravel-app:latest -f docker/php/Dockerfile.production .
```

2. Prześlij obraz do rejestru (Docker Hub, GitHub Container Registry itp.):
```bash
docker push yourregistry/laravel-app:latest
```

3. Na serwerze produkcyjnym:
```bash
# Przygotuj katalog projektu
mkdir -p ~/app
cd ~/app

# Skopiuj pliki konfiguracyjne
scp user@local-machine:/path/to/docker-compose.production.yml docker-compose.yml
scp -r user@local-machine:/path/to/docker/nginx ./docker/

# Utwórz plik .env
nano .env
# Uzupełnij zmienne środowiskowe

# Uruchom aplikację
docker compose pull
docker compose up -d
```

### Konfiguracja SSL

1. Uzyskaj certyfikaty SSL (np. Let's Encrypt):
```bash
certbot certonly --standalone -d example.com -d www.example.com
```

2. Skopiuj certyfikaty do katalogu Nginx:
```bash
mkdir -p ~/app/docker/nginx/production/ssl
cp /etc/letsencrypt/live/example.com/fullchain.pem ~/app/docker/nginx/production/ssl/
cp /etc/letsencrypt/live/example.com/privkey.pem ~/app/docker/nginx/production/ssl/
```

3. Zaktualizuj konfigurację Nginx, zastępując `example.com` swoją domeną.

### Monitorowanie i utrzymanie

- **Logi**: Dostępne w `/var/log/nginx` i poprzez `docker compose logs`
- **Aktualizacje**: Regularnie aktualizuj obrazy i zależności
- **Kopie zapasowe**: Ustaw regularne kopie zapasowe bazy danych i wolumenów
- **Monitorowanie**: Rozważ użycie Prometheus/Grafana dla monitorowania kontenerów

### Bezpieczeństwo

- Regularnie aktualizuj obrazy bazowe
- Używaj HTTPS z nowoczesnymi ustawieniami SSL
- Ogranicz dostęp SSH tylko do zaufanych adresów IP
- Używaj silnych haseł i rozważ konfigurację 2FA 


### Omówienie

1. Podejście wieloetapowe (multi-stage build)
Środowisko deweloperskie:
Dockerfile.local: Oparty na standardowym php:8.4-fpm, zawiera wszystkie narzędzia deweloperskie.
Używa apt-get dla pakietów, co ułatwia debugowanie i rozwiązywanie problemów.
Ustawia użytkownika www-data z pełnymi uprawnieniami sudo, aby uniknąć problemów z własnością plików.
Środowisko produkcyjne:
Dockerfile.production: Wykorzystuje podejście multi-stage:
Etap budowania (builder): Instaluje wszystkie zależności, kompiluje zasoby (npm build)
Etap finalny: Minimalistyczny obraz alpine z tylko niezbędnymi komponentami
Optymalizacja rozmiaru obrazu (~50-70% mniejszy niż wersja deweloperska)
Zwiększone bezpieczeństwo dzięki mniejszej powierzchni ataku
2. Strategia repozytorium Docker
Masz dwie opcje przechowywania obrazów:
Pojedyncze repozytorium dla wielu projektów:
Zalety: Łatwiejsze zarządzanie, jeden pipeline CI/CD
Wady: Mniej elastyczne, trudniejsze dostosowanie do specyficznych projektów
Dedykowane obrazy per projekt:
Zalety: Ściślejsza integracja z projektem, lepsze dostosowanie
Wady: Więcej zarządzania, duplikacja kodu
Rekomendacja: Dla MySQL i innych usług bazodanowych zdecydowanie zalecam korzystanie z zewnętrznych, zarządzanych usług (AWS RDS, DigitalOcean Managed Databases) zamiast kontenerów. Powody:
Lepsze zarządzanie danymi i kopie zapasowe
Automatyczne aktualizacje i patche bezpieczeństwa
Skalowalność i wysoka dostępność
Mniejsze ryzyko utraty danych przy awarii kontenera
3. Workflow CI/CD
Przygotowany pipeline GitHub Actions obejmuje:
Testowanie:
Uruchamia testy jednostkowe i integracyjne
Używa SQLite w pamięci dla szybkości
Budowanie:
Kompiluje wieloetapowy obraz produkcyjny
Publikuje do GitHub Container Registry
Wykorzystuje cache dla szybszych buildów
Wdrażanie:
Kopiuje pliki konfiguracyjne na serwer produkcyjny
Uruchamia docker compose pull i up
Czyści stare obrazy dla oszczędności miejsca
4. Konfiguracja produkcyjna
Optymalizacja PHP i FPM: Dostosowane ustawienia dla lepszej wydajności
Bezpieczeństwo Nginx: Nowoczesne nagłówki bezpieczeństwa, ustawienia SSL
Supervisord: Zarządzanie procesami Laravel (Horizon, kolejki, harmonogram)
Wolumeny: Trwałe przechowywanie dla danych Redis i plików aplikacji
Dobre praktyki produkcyjne:
Zewnętrzne bazy danych: Używaj zarządzanych usług dla MySQL
Skalowanie: Skaluj kontenery aplikacji w poziomie, dodając instancje
Monitoring: Dodaj Prometheus/Grafana dla monitorowania kontenerów
Logowanie: Używaj centralnego systemu logowania (np. ELK, Loki)
Backup: Regularne kopie zapasowe bazy danych i plików aplikacji
CDN: Rozważ użycie CDN dla statycznych zasobów
CI/CD: Automatyzacja testów i wdrożeń
Wdrożenie jest gotowe do użycia na produkcji i zgodne z najlepszymi praktykami DevOps. Czy masz jakieś pytania dotyczące konkretnych aspektów tej konfiguracji
