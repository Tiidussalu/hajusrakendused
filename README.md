# Hajusrakendus

> Laravel 13 · Vue 3 · Inertia.js · Tailwind CSS · MySQL

Täielik hajusrakendus 5 iseseisvast moodulist, mis on ühendatud üheks terviklikuks veebirakenduseks.

---

## 📋 Ülesehitus

```
hajusrakendus/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── WeatherController.php    # Ülesanne 1 – Ilm
│   │   │   ├── MapController.php        # Ülesanne 2 – Kaart
│   │   │   ├── BlogController.php       # Ülesanne 3 – Blogi
│   │   │   ├── ShopController.php       # Ülesanne 4 – E-pood
│   │   │   ├── FilmController.php       # Ülesanne 5 – Filmide API
│   │   │   ├── HomeController.php
│   │   │   └── AuthController.php
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Marker.php        # markers tabel
│   │   ├── Post.php          # posts tabel
│   │   ├── Comment.php       # comments tabel
│   │   ├── Order.php         # orders tabel
│   │   └── Film.php          # my_favorite_subject tabel
│   └── Services/
│       └── WeatherService.php   # OpenWeatherMap + vahemälu
├── resources/js/
│   ├── Layouts/
│   │   └── AppLayout.vue       # Ühine navigatsioon
│   ├── Pages/
│   │   ├── Home.vue
│   │   ├── Weather/Index.vue   # Ülesanne 1
│   │   ├── Map/Index.vue       # Ülesanne 2
│   │   ├── Blog/               # Ülesanne 3
│   │   ├── Shop/               # Ülesanne 4
│   │   ├── Api/                # Ülesanne 5
│   │   └── Auth/
│   └── Components/
├── database/migrations/
├── routes/
│   ├── web.php
│   └── api.php                 # JSON API /api/v1/...
└── docs/
```

---

## 🚀 Kasutatud tehnoloogiad

| Kiht | Tehnoloogia |
|------|------------|
| Backend | Laravel 13, PHP 8.3+ |
| Frontend | Vue 3 (Composition API) |
| SPA sild | Inertia.js v2 |
| CSS | Tailwind CSS v3 |
| Ehitamine | Vite 6 |
| Andmebaas | MySQL 8 |
| Autentimine | Laravel Session Auth |
| Kaart | Radar Maps API |
| Ilm | OpenWeatherMap API |
| Maksed | Stripe |
| Vahemälu | Laravel Cache (file/redis) |

---

## ⚙️ Paigaldamine

### 1. Klooni repo

```bash
git clone https://github.com/kasutajanimi/hajusrakendus.git
cd hajusrakendus
```

### 2. PHP sõltuvused

```bash
composer install
```

### 3. Node sõltuvused

```bash
npm install
```

### 4. Keskkonna seadistamine

```bash
cp .env.example .env
php artisan key:generate
```

Muuda `.env` failis:

```env
DB_DATABASE=hajusrakendus
DB_USERNAME=root
DB_PASSWORD=sinu_parool

OPENWEATHER_API_KEY=sinu_openweathermap_võti
RADAR_MAPS_API_KEY=sinu_radar_maps_võti

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

### 5. Andmebaas

```bash
php artisan migrate
php artisan db:seed        # Demo andmed
```

### 6. Käivita arendusserverid

```bash
# Terminalis 1 – Laravel
php artisan serve

# Terminalis 2 – Vite (Vue + Tailwind)
npm run dev
```

Ava brauser: **http://localhost:8000**

---

## 🌐 Tootmiskeskkond (Zone vms)

```bash
# Ehita frontend
npm run build

# Optimeeri
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`.htaccess` fail peaks suunama kõik päringud `public/index.php`-le.

---

## 📡 JSON API dokumentatsioon

Kõik API vastused on JSON formaadis koos `Access-Control-Allow-Origin: *` päisega.

### Filmide API

| Meetod | URL | Kirjeldus |
|--------|-----|-----------|
| GET | `/api/v1/films` | Filmide nimekiri |
| GET | `/api/v1/films/{id}` | Üksiku filmi andmed |

**Päringu parameetrid:**

| Parameeter | Tüüp | Näide | Kirjeldus |
|------------|------|-------|-----------|
| `search` | string | `nolan` | Otsi pealkirja/režissööri järgi |
| `genre` | string | `Sci-Fi` | Filtreeri žanri järgi |
| `sort` | string | `-rating` | Sordi (eesliide `-` = laskuv) |
| `limit` | integer | `10` | Max kirjete arv (1–100) |

**Näide:**
```
GET /api/v1/films?search=nolan&genre=Sci-Fi&sort=-rating&limit=5
```

**Vastus:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Inception",
      "director": "Christopher Nolan",
      "year": 2010,
      "genre": "Sci-Fi",
      "rating": 8.8,
      "description": "...",
      "image": "https://...",
      "user": { "id": 1, "name": "Admin" }
    }
  ],
  "total": 6,
  "limit": 5
}
```

### Markerite API

```
GET /api/v1/markers
```

### Ilmaandmete API

```
GET /api/v1/weather?city=Tallinn
```

---

## 🗄️ Andmebaasi struktuur

### `users`
| Väli | Tüüp | |
|------|------|-|
| id | bigint | PK |
| name | varchar | |
| email | varchar | unique |
| password | varchar | |
| is_admin | boolean | default false |

### `markers`
| Väli | Tüüp | |
|------|------|-|
| id | bigint | PK |
| name | varchar | |
| latitude | decimal(10,7) | |
| longitude | decimal(10,7) | |
| description | text | nullable |
| added | timestamp | |
| edited | timestamp | nullable |

### `posts`
| Väli | Tüüp | |
|------|------|-|
| id | bigint | PK |
| user_id | bigint | FK |
| title | varchar | |
| slug | varchar | unique |
| description | text | |
| image | varchar | nullable |
| published | boolean | |
| created_at | timestamp | |
| updated_at | timestamp | |

### `comments`
| Väli | Tüüp | |
|------|------|-|
| id | bigint | PK |
| post_id | bigint | FK |
| user_id | bigint | FK nullable |
| author_name | varchar | |
| author_email | varchar | |
| body | text | |
| approved | boolean | |

### `orders`
| Väli | Tüüp | |
|------|------|-|
| id | bigint | PK |
| first_name | varchar | |
| last_name | varchar | |
| email | varchar | |
| phone | varchar | nullable |
| items | json | |
| total | decimal | |
| status | varchar | pending/paid/failed |
| stripe_payment_intent | varchar | |

### `my_favorite_subject` (filmid)
| Väli | Tüüp | |
|------|------|-|
| id | bigint | PK |
| title | varchar | |
| image | varchar | nullable |
| description | text | |
| director | varchar | teemaspetsiifiline |
| year | smallint | teemaspetsiifiline |
| genre | varchar | nullable |
| rating | decimal(3,1) | nullable |
| user_id | bigint | FK nullable |

---

## 🔑 Vahemälu (Cache)

| Moodul | Vahemälu võti | TTL |
|--------|--------------|-----|
| Ilm (praegune) | `weather_current_{linn}_{ühik}` | 30 min |
| Ilm (prognoos) | `weather_forecast_{linn}_{ühik}` | 30 min |
| Filmide API | `films_api_{query_hash}` | 5 min |

Vahemälu tühjendamine:
```bash
php artisan cache:clear
```

---

## 👤 Demo kasutajad (seeder)

| Nimi | E-post | Parool | Roll |
|------|--------|--------|------|
| Admin | admin@hajusrakendus.ee | password | Admin |
| Jaan Tamm | jaan@hajusrakendus.ee | password | Kasutaja |

---

## 💳 Stripe testimine

Kasuta test-kaardinumbreid:

| Kaart | Number | Tulemus |
|-------|--------|---------|
| Visa (õnnestub) | 4242 4242 4242 4242 | ✅ Makse õnnestub |
| Visa (ebaõnnestub) | 4000 0000 0000 0002 | ❌ Makse ebaõnnestub |

Kõik testkaardid: kehtivuskuupäev suvaline tulevik, CVC mis tahes 3 numbrit.

---

## 🌤️ API võtmete hankimine

1. **OpenWeatherMap**: https://openweathermap.org/api → tasuta plaan
2. **Radar Maps**: https://radar.com → tasuta arendajaplaan
3. **Stripe**: https://stripe.com → test-võtmed dashboard'ist

---

## 📦 GitHub

```bash
git init
git add .
git commit -m "feat: hajusrakendus - Laravel 13 + Vue 3 + Inertia.js"
git remote add origin https://github.com/kasutajanimi/hajusrakendus.git
git push -u origin main
```
