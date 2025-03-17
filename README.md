# Turnos - CAP

Este es un sistema de gestión de turnos para una pista de patinaje, desarrollado con **Laravel** y **Filament** como panel de administración. Se ejecuta con **Laravel Sail** para un entorno basado en Docker y proporciona una **API REST** consumida por un proyecto frontend independiente.

![image](https://github.com/user-attachments/assets/3327316b-57be-49ab-acc1-6619677ee485)


## Tecnologías utilizadas

- Laravel 11
- Filament Admin Panel
- Laravel Sail (Docker)
- MySQL
- API REST
- Autenticación con Laravel Sanctum

## Requisitos previos

Asegúrate de tener instalado:

- Docker y Docker Compose
- Make (opcional, para facilitar comandos)

## Instalación

Clona el repositorio y accede a la carpeta del proyecto:

```sh
git clone https://github.com/tu-usuario/turnos-cap.git
cd turnos-cap
```

### 1. Iniciar Laravel Sail

Si usas **Makefile** (opcional):
```sh
make up
```

O manualmente con Docker:
```sh
./vendor/bin/sail up -d
```

### 2. Instalar dependencias

```sh
./vendor/bin/sail composer install
```

### 3. Configurar entorno

Copia el archivo de configuración:
```sh
cp .env.example .env
```
Genera la clave de la aplicación:
```sh
./vendor/bin/sail artisan key:generate
```

### 4. Migrar y poblar la base de datos

```sh
./vendor/bin/sail artisan migrate --seed
```

### 5. Crear usuario administrador para Filament

```sh
./vendor/bin/sail artisan make:filament-user
```

### 6. Acceder a Filament Admin Panel

Visita [http://localhost/admin](http://localhost/admin) e inicia sesión con las credenciales creadas.

---

## API REST

La aplicación expone una API REST para gestionar turnos, la cual es consumida por el frontend.

### Endpoints principales

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | /api/locations | Listar ubicaciones |
| GET | /api/locations/{id} | Obtener detalles de una ubicación |
| POST | /api/locations | Crear una nueva ubicación |
| PUT | /api/locations/{id} | Actualizar una ubicación |
| DELETE | /api/locations/{id} | Eliminar una ubicación |

Para más detalles, consulta la documentación de la API.

---

## Proyecto frontend

Este backend es consumido por un proyecto App-pista (expo app) que puedes encontrar en:

[https://github.com/tu-usuario/turnos-cap-frontend]([https://github.com/tu-usuario/turnos-cap-frontend](https://github.com/PabloGabrielDonato/app-pista))

---

## Comandos útiles

- **Subir contenedores**:
  ```sh
  ./vendor/bin/sail up -d
  ```
- **Bajar contenedores**:
  ```sh
  ./vendor/bin/sail down
  ```
- **Ejecutar migraciones**:
  ```sh
  ./vendor/bin/sail artisan migrate --seed
  ```
- **Acceder a la base de datos MySQL**:
  ```sh
  ./vendor/bin/sail mysql
  ```

---

## Licencia

Este proyecto está bajo la licencia MIT. Consulta el archivo `LICENSE` para más información.

