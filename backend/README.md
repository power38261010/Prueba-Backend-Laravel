# Prueba Técnica Backend — Laravel

API RESTful desarrollada con Laravel que gestiona productos, su información en divisas y los precios de los productos en diferentes divisas. Respuestas en JSON, persistencia con Eloquent y base de datos SQLite.

## Tabla de contenidos

1. [Objetivo](#objetivo)
2. [Tecnologías y versiones verificadas](#tecnologías-y-versiones-verificadas)
3. [Requisitos previos](#requisitos-previos)
4. [Instalación](#instalación)
5. [Configuración](#configuración)
6. [Base de datos: migraciones y seeders](#base-de-datos-migraciones-y-seeders)
7. [Ejecución de la aplicación](#ejecución-de-la-aplicación)
8. [Ejecución de tests](#ejecución-de-tests)
9. [Modelo de datos](#modelo-de-datos)
10. [Arquitectura y decisiones relevantes](#arquitectura-y-decisiones-relevantes)
11. [Convención de tasas de cambio](#convención-de-tasas-de-cambio)
12. [Endpoints](#endpoints)
13. [Ejemplos de solicitudes y respuestas JSON](#ejemplos-de-solicitudes-y-respuestas-json)
14. [Códigos de error](#códigos-de-error)
15. [Seguridad](#seguridad)
16. [Limitaciones conocidas](#limitaciones-conocidas)
17. [Documentación de la API](#documentación-de-la-api)

## Objetivo

Implementar desde cero una API RESTful en Laravel que cumpla la consigna de `Prueba Backend.pdf`:

- CRUD de productos.
- Gestión de la información de divisas.
- Registro y consulta de precios de productos en distintas divisas.
- Respuestas JSON y persistencia mediante Eloquent.
- Documentación clara de la API y medidas de seguridad apropiadas.

## Tecnologías y versiones verificadas

| Componente | Versión |
|------------|---------|
| PHP | 8.2.35 (CLI, ZTS, extensiones: openssl, pdo_sqlite, sqlite3, mbstring, fileinfo, xml, ctype, zip) |
| Laravel Framework | 12.70.2 |
| Composer | 2.10.3 |
| Base de datos | SQLite (archivo `database/database.sqlite`) |
| Pruebas | PHPUnit (incluido en Laravel) |
| Entorno de pruebas | SQLite en memoria (`:memory:`), no afecta datos reales |

## Requisitos previos

- PHP 8.2 o superior con las extensiones: `openssl`, `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, `xml`, `ctype`.
- Composer 2.x.

No es necesario instalar un servidor de base de datos (se usa SQLite) ni Docker.

## Instalación

Desde la carpeta del proyecto (`backend/`):

```bash
# 1. Instalar dependencias de PHP
composer install

# 2. Copiar el archivo de entorno
cp .env.example .env

# 3. Generar la clave de la aplicación (si no se hizo al crear el proyecto)
php artisan key:generate

# 4. Crear la base de datos SQLite y ejecutar migraciones
php artisan migrate

# 5. Cargar datos de ejemplo (reproducible, no genera duplicados al reejecutar)
php artisan db:seed --force
```

El archivo `.env` ya está configurado para usar SQLite (`DB_CONNECTION=sqlite`) con claves foráneas habilitadas (`DB_FOREIGN_KEYS=true`).

## Configuración

Edita `backend/.env` según tu entorno. Claves relevantes:

```ini
APP_NAME="Prueba Backend Laravel"
APP_ENV=local
APP_DEBUG=false              # En producción mantener false (no exponer stack traces)
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_FOREIGN_KEYS=true         # Integridad referencial habilitada en SQLite
```

- `.env.example` no contiene credenciales reales.
- `APP_DEBUG=false` es la configuración adecuada para producción; los errores se devuelven como JSON genérico sin stack traces ni datos internos.

## Base de datos: migraciones y seeders

```bash
# Ejecutar migraciones (crea tablas currencies, products, product_prices)
php artisan migrate

# Cargar datos de ejemplo (divisas, productos, precios)
php artisan db:seed --force

# Refrescar base de datos (migraciones + seeders desde cero)
php artisan migrate:fresh --seed
```

Los seeders usan `firstOrCreate` / `updateOrCreate`, por lo que son **reproducibles**: se pueden ejecutar varias veces sin generar duplicados inesperados.

## Ejecución de la aplicación

```bash
php artisan serve
```

La API quedará disponible en `http://localhost:8000`. Los endpoints están en la raíz (ej. `GET /products`), sin prefijo `/api`.

## Ejecución de tests

La suite de pruebas usa PHPUnit con SQLite en memoria (`:memory:`), por lo que no afecta datos reales.

```bash
php artisan test
```

Resultado esperado: 14 pruebas pasadas, 74 aserciones.

## Modelo de datos

### Tabla `products`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | biginteger (PK) | Identificador único |
| name | string | Nombre del producto |
| description | text | Descripción del producto |
| price | decimal(12,2) | Precio del producto en su divisa base |
| currency_id | biginteger (FK) | Divisa base del producto → `currencies.id` |
| tax_cost | decimal(12,2) | Costo de impuestos |
| manufacturing_cost | decimal(12,2) | Costo de fabricación |

### Tabla `currencies`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | biginteger (PK) | Identificador único |
| name | string (unique) | Nombre de la divisa |
| symbol | string (unique) | Símbolo de la divisa |
| exchange_rate | decimal(10,6) | Tasa de cambio (ver convención abajo) |

### Tabla `product_prices`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | biginteger (PK) | Identificador único |
| product_id | biginteger (FK) | Producto → `products.id` (borrado en cascada) |
| currency_id | biginteger (FK) | Divisa → `currencies.id` (borrado restringido) |
| price | decimal(12,2) | Precio del producto en la divisa especificada |

### Relaciones e integridad

- `products.currency_id → currencies.id` (`onDelete restrict`).
- `product_prices.product_id → products.id` (`onDelete cascade`).
- `product_prices.currency_id → currencies.id` (`onDelete restrict`).
- Índice único en `product_prices(product_id, currency_id)`: un precio vigente por combinación producto/divisa.
- Índices en `currency_id` (products), `product_id` y `currency_id` (product_prices).
- Los importes monetarios usan `decimal` (no `float`) con cast `decimal:2` en los modelos para evitar problemas de punto flotante.

### Relación precio base vs. precios en otras divisas

El campo `products.price` es el precio del producto en su **divisa base** (`currency_id`). La tabla `product_prices` almacena precios adicionales en otras divisas, registrados explícitamente por el cliente. El precio base **no** se autocrea como fila de `product_prices` (ambas estructuras son independientes por diseño de la consigna).

## Arquitectura y decisiones relevantes

Arquitectura Laravel idiomática, proporcionada a la complejidad de la prueba, sin abstracciones innecesarias (no se usó CQRS, DDD completo, hexagonal, microservicios ni event sourcing).

Capas:

- **Modelos Eloquent** (`app/Models`): `Product`, `Currency`, `ProductPrice` con relaciones y casts decimal.
- **Form Requests** (`app/Http/Requests`): validación de entrada (`StoreProductRequest`, `UpdateProductRequest`, `StoreProductPriceRequest`, `StoreCurrencyRequest`, `UpdateCurrencyRequest`).
- **API Resources** (`app/Http/Resources`): representación JSON estable (`ProductResource`, `CurrencyResource`, `ProductPriceResource`).
- **Controladores delgados** (`app/Http/Controllers/Api`): coordinan la solicitud y devuelven JSON.
- **Servicios de aplicación** (`app/Services`): `ProductService`, `CurrencyService`, `ProductPriceService` encapsulan la lógica de negocio y usan transacciones (`DB::transaction`) para operaciones atómicas.
- **Excepciones** (`app/Exceptions/BusinessException`, renderizado en `bootstrap/app.php`): errores consistentes en JSON, sin stack traces ni información interna.

Principios aplicados:

- **SRP**: cada clase tiene una responsabilidad clara.
- **O**: los Form Requests están abiertos a extensión para nuevas reglas sin tocar los controladores.
- **LSP / ISP / DIP**: no se forzó interfaces con una sola implementación; solo se usaron donde hay desacoplamiento real.

Validaciones: campos obligatorios, longitudes, importes no negativos, existencia de productos/divisas relacionadas, unicidad de precio por combinación producto/divisa, rechazo de relaciones inexistentes. El `PUT` respeta la semántica de actualización completa (todos los campos requeridos).

## Convención de tasas de cambio

La consigna no define la dirección de `exchange_rate` ni la fórmula de conversión, por lo que se adoptó una convención **expresa y documentada** (no se inventó una interpretación silenciosa):

- `exchange_rate`: **valor de 1 unidad de la divisa expresado en la divisa base**.
- La divisa base tiene `exchange_rate = 1.000000`.
- Conversión base → divisa: `precio_divisa = precio_base / exchange_rate`.
- Conversión divisa → base: `precio_base = precio_divisa * exchange_rate`.

Ejemplo: si la divisa base es USD (`1.000000`) y EUR tiene `exchange_rate = 0.900000`, entonces 1 EUR = 0.90 USD.

Los precios en divisas distintas a la base se **registran explícitamente** por el cliente (el API no autoconvierte), evitando así comportamientos implícitos no especificados.

## Endpoints

| Método | Endpoint | Descripción | Código OK |
|--------|----------|-------------|-----------|
| GET | `/products` | Listar productos | 200 |
| POST | `/products` | Crear un producto | 201 |
| GET | `/products/{id}` | Obtener un producto (con divisa y precios) | 200 |
| PUT | `/products/{id}` | Actualizar un producto (completo) | 200 |
| DELETE | `/products/{id}` | Eliminar un producto (sin contenido) | 204 |
| GET | `/products/{id}/prices` | Listar precios de un producto | 200 |
| POST | `/products/{id}/prices` | Crear un precio para un producto | 201 |
| GET | `/currencies` | Listar divisas | 200 |
| POST | `/currencies` | Crear una divisa | 201 |
| GET | `/currencies/{id}` | Obtener una divisa | 200 |
| PUT | `/currencies/{id}` | Actualizar una divisa (completo) | 200 |
| DELETE | `/currencies/{id}` | Eliminar una divisa (sin contenido) | 204 |

Los endpoints de divisas cubren el requerimiento de "gestión de la información de divisas" de la consigna.

## Ejemplos de solicitudes y respuestas JSON

### Crear producto — POST /products

Solicitud:

```json
{
  "name": "Laptop",
  "description": "Computadora portatil de 14 pulgadas",
  "price": 1200.00,
  "currency_id": 1,
  "tax_cost": 216.00,
  "manufacturing_cost": 700.00
}
```

Respuesta (201):

```json
{
  "success": true,
  "message": "Producto creado.",
  "data": {
    "id": 1,
    "name": "Laptop",
    "description": "Computadora portatil de 14 pulgadas",
    "price": "1200.00",
    "currency_id": 1,
    "currency": {
      "id": 1,
      "name": "Dolar estadounidense",
      "symbol": "USD",
      "exchange_rate": "1.000000"
    },
    "tax_cost": "216.00",
    "manufacturing_cost": "700.00",
    "prices": []
  }
}
```

### Obtener producto — GET /products/{id}

Respuesta (200):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Laptop",
    "description": "Computadora portatil de 14 pulgadas",
    "price": "1200.00",
    "currency_id": 1,
    "currency": { "id": 1, "name": "Dolar estadounidense", "symbol": "USD", "exchange_rate": "1.000000" },
    "tax_cost": "216.00",
    "manufacturing_cost": "700.00",
    "prices": [
      { "id": 1, "product_id": 1, "currency_id": 1, "price": "1200.00", "currency": {...} },
      { "id": 2, "product_id": 1, "currency_id": 2, "price": "1333.33", "currency": {...} }
    ]
  }
}
```

### Crear precio — POST /products/{id}/prices

Solicitud:

```json
{
  "product_id": 1,
  "currency_id": 2,
  "price": 1333.33
}
```

Respuesta (201): `{"success": true, "message": "Precio creado.", "data": {...}}`

### Duplicado rechazado — POST /products/{id}/prices

Respuesta (422):

```json
{
  "success": false,
  "message": "Ya existe un precio para este producto en la divisa indicada.",
  "errors": null
}
```

### Recurso no encontrado — GET /products/99999

Respuesta (404):

```json
{
  "success": false,
  "message": "Recurso no encontrado.",
  "errors": null
}
```

### Validación fallida — POST /products

Respuesta (422):

```json
{
  "success": false,
  "message": "Error de validacion.",
  "errors": {
    "price": ["El precio no puede ser negativo."],
    "currency_id": ["La divisa base seleccionada no existe."]
  }
}
```

## Códigos de error

| Código | Significado | Cuándo se usa |
|--------|-------------|---------------|
| 200 | OK | Operación exitosa (GET, PUT) |
| 201 | Created | Recurso creado (POST) |
| 204 | No Content | Eliminación exitosa sin contenido (DELETE) |
| 404 | Not Found | Recurso o endpoint inexistente |
| 409 | Conflict | No se puede eliminar (referenciado por FK) |
| 422 | Unprocessable Entity | Validación fallida o precio duplicado |
| 500 | Internal Server Error | Error inesperado (sin stack trace) |

El sobre-envelope JSON es consistente en todos los endpoints:

- Éxito: `{"success": true, "data": ...}` (y `message` en mutaciones).
- Error: `{"success": false, "message": "...", "errors": ...}`.

## Seguridad

Medidas aplicadas en el alcance declarado:

- Validación de entrada mediante Form Requests (tipos, longitudes, importes no negativos, existencia de claves foráneas).
- Protección contra mass assignment: solo `$fillable` es asignable en los modelos.
- Integridad referencial: claves foráneas y restricciones de unicidad en la base de datos; FKs habilitadas en SQLite.
- Consultas parametrizadas: Eloquent/Query Builder (no SQL concatenado).
- Manejo seguro de errores: respuestas JSON genéricas, sin stack traces, sin SQL ni credenciales (`APP_DEBUG=false`).
- Secretos únicamente en variables de entorno (`.env`); `.env.example` sin credenciales.
- Atributos internos no autorizados no son modificables desde el cliente (no hay campos de auditoría expuestos).

**Alcance y limitaciones:** La consigna **no especifica autenticación ni autorización**, por lo que **la API no implementa autenticación** (no se instaló Sanctum/Passport). Está expuesta sin control de acceso; en un entorno real se debería añadir autenticación (p. ej. Sanctum) y autorización según el dominio.

## Limitaciones conocidas

1. **Sin autenticación:** no se implementó login/tokens (fuera del alcance de la consigna). Se documentó explícitamente.
2. **SQLite:** adecuado para reproducir la prueba sin infraestructura, pero no es equivalente a MySQL/PostgreSQL en comportamiento (tipos de datos, concurrencia, funciones de fecha). Se documentó la elección.
3. **Precisión monetaria:** los cast `decimal` devuelven strings en JSON (comportamiento estándar de Laravel para preservar precisión).
4. **No autoconsversión de precios:** los precios en otras divisas se registran explícitamente; no se calculan automáticamente desde `exchange_rate` (evita interpretar silenciosamente la consigna).

## Documentación de la API

La documentación completa está en este archivo (`README.md`).

### Swagger / OpenAPI

La API cuenta con documentación OpenAPI 3.0 generada a partir de anotaciones en el código (`darkaonline/l5-swagger` + `zircote/swagger-php`).

- Interfaz Swagger UI: `GET /api/documentation` (documentación interactiva).
- Especificación OpenAPI generada: `backend/storage/api-docs/api-docs.json` (JSON, OpenAPI 3.0.0).

Los paths documentados coinciden exactamente con las rutas de la API:

| Path | Métodos |
|------|---------|
| `/products` | GET, POST |
| `/products/{id}` | GET, PUT, DELETE |
| `/products/{id}/prices` | GET, POST |
| `/currencies` | GET, POST |
| `/currencies/{id}` | GET, PUT, DELETE |

Para regenerar la documentación tras cambios en los controladores:

```bash
php artisan l5-swagger:generate
```

También se incluye una colección importable de Postman en `backend/postman_collection.json` (uno de los formatos aceptados por la consigna: Swagger, Json Postman, Json Insomnia o Documento).
