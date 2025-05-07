# Elasticsearch, Kibana, Filebeat – Laravel Production Setup

## 1. Zastosowania Elasticsearch
- Wyszukiwarka pełnotekstowa (szybciej i lepiej niż SQL LIKE)
- Wyszukiwanie po relacjach (np. artykuły + tagi + autorzy)
- Wyszukiwanie geolokalizacyjne (np. najbliższe artykuły)
- Agregacje/statystyki (np. liczba artykułów wg tagu)
- Logi i monitoring (aplikacja, nginx, php, system)
- Wektory/AI (semantyczne wyszukiwanie, rekomendacje)
- Alerty, automatyzacja, dashboardy

## 2. Docker Compose – produkcja i lokalnie

- **elasticsearch** – silnik wyszukiwania
- **kibana** – dashboardy, eksploracja, monitoring
- **filebeat** – zbieranie logów z aplikacji, nginx, php
- **app/nginx/redis/mysql** – Twoja aplikacja

**Wolumeny i ścieżki logów**:
- Laravel: `storage/logs/*.log` → montowane do `/logs` w Filebeat
- Nginx: `/var/log/nginx/*.log` → montowane do `/nginx-logs`
- PHP: `/var/log/php8/*.log` → montowane do `/php-logs`

**Filebeat** automatycznie zbiera logi z tych ścieżek i wysyła do Elasticsearcha.

## 3. Konfiguracja Filebeat (docker/local/filebeat/filebeat.yml)

```yaml
filebeat.inputs:
  - type: log
    enabled: true
    paths:
      - /logs/*.log
    fields:
      service: laravel
  - type: log
    enabled: true
    paths:
      - /nginx-logs/*.log
    fields:
      service: nginx
  - type: log
    enabled: true
    paths:
      - /php-logs/*.log
    fields:
      service: php

output.elasticsearch:
  hosts: ["elasticsearch:9200"]
  index: "logs-%{[fields.service]}-%{+yyyy.MM.dd}"

setup.kibana:
  host: "kibana:5601"
```

## 4. Seedery, modele, relacje, geolokalizacja

- **UserSeeder** – przykładowi użytkownicy
- **TagSeeder** – przykładowe tagi
- **ArticleSeeder** – 1000 artykułów z relacjami do userów, tagów, lokalizacją (Warszawa, Kraków, Wrocław)
- **Relacje**: Article → User (autor), Article → Tag (wiele do wielu)
- **Geolokalizacja**: pole `location` (lat/lon) w artykule

## 5. Wyszukiwanie w aplikacji

- **/search** – endpoint, który pozwala wyszukiwać po:
  - Tekście (pełnotekstowo, fuzzy, ranking)
  - Tagu
  - Lokalizacji (najbliższe artykuły)
  - Autorze
  - Kombinacji powyższych
  - Możesz dodać kolejne pola (np. daty, statusy, kategorie, wektory AI)

### Przykład zapytania po geolokalizacji (w aplikacji lub Dev Tools Kibany):
```json
{
  "query": {
    "bool": {
      "must": [
        { "match": { "title": "php" } }
      ],
      "filter": [
        {
          "geo_distance": {
            "distance": "50km",
            "location": { "lat": 52.2297, "lon": 21.0122 }
          }
        }
      ]
    }
  }
}
```

## 6. Logi – co i jak jest zbierane?

- **Automatycznie zbierane**:
  - Logi aplikacji Laravel (`storage/logs/*.log`)
  - Logi nginx (`/var/log/nginx/*.log`)
  - Logi PHP (`/var/log/php8/*.log`)
- **Filebeat** wysyła je do Elasticsearcha, gdzie każdy typ logu trafia do osobnego indeksu (`logs-laravel-*`, `logs-nginx-*`, `logs-php-*`).

## 7. Kibana – jak korzystać?

- **Dostęp**:  
  - Lokalnie: http://localhost:5601  
  - Produkcja: port 5601 (wystaw przez reverse proxy lub VPN)
- **Discover**: przeglądanie logów i danych (np. `logs-laravel-*`)
- **Dev Tools**: testowanie zapytań do Elasticsearcha
- **Dashboardy**: wykresy, heatmapy, statystyki
- **Alerty**: powiadomienia o błędach, anomaliach

## 8. Uruchomienie środowiska (lokalnie/produkcyjnie)

```bash
# Uruchom wszystkie serwisy (lokalnie)
docker compose up -d

# Lub na produkcji
docker compose -f docker-compose.production.yml up -d

# Sprawdź status
docker compose ps

# Zainicjuj bazę i dane testowe (w kontenerze app!)
docker compose exec app php artisan migrate:fresh --seed
```

## 9. Najczęstsze problemy i rozwiązania

- **NoNodeAvailableException** podczas seedowania:  
  Elasticsearch może nie być jeszcze gotowy – uruchom seedowanie po kilku minutach od startu kontenerów.
- **Logi nie pojawiają się w Kibanie**:  
  Sprawdź, czy Filebeat działa i czy ścieżki logów są poprawnie zamontowane.
- **Kibana na produkcji**:  
  Najlepiej wystawić przez reverse proxy (np. nginx z autoryzacją) lub dostęp przez VPN.

## 10. Rozszerzanie i best practices

- **Mappingi**: ustaw mapping `geo_point` dla lokalizacji przez Dev Tools w Kibanie:
  ```json
  PUT articles/_mapping
  {
    "properties": {
      "location": { "type": "geo_point" }
    }
  }
  ```
- **Wektory/AI**: możesz dodać pole wektorowe i korzystać z semantycznego wyszukiwania.
- **Alerty**: ustaw alerty w Kibanie na określone zdarzenia (np. dużo błędów).
- **Backupy**: snapshoty indeksów Elasticsearch.
- **Monitoring**: Elastic APM, dashboardy, alerty.

## 11. Przykładowe pytania rekrutacyjne

- Kiedy użyć Elasticsearch zamiast SQL?
- Jakie są typowe przypadki użycia?
- Jak monitorować środowisko produkcyjne?
- Jakie są różnice między SQL a Elasticsearch?

## 12. Przykładowe komendy

```bash
# Wyszukiwanie artykułów po tekście, tagu i lokalizacji
curl 'http://localhost/search?q=php&tag=laravel&lat=52.2297&lon=21.0122'

# Sprawdzanie logów w Kibanie
# Otwórz http://localhost:5601 → Discover → wybierz index np. logs-laravel-*

# Sprawdzanie statusu Elasticsearch
curl http://localhost:9200
```

---

**Masz gotowe środowisko do testów, rozwoju i prezentacji na rozmowie rekrutacyjnej!**
W razie pytań lub chęci rozbudowy – pytaj śmiało! 