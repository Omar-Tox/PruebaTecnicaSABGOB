# PruebaTecnicaSABGOB
Sistema de Gestión de Tareas Jerárquicas y Lista de Verificación.

* **Backend:** Laravel 12
* **Frontend:** Vue.js 3 + Pinia + Bootstrap 5
* **Base de Datos:** MariaDB 10.6
* **Infraestructura:** Docker & Docker Compose

*  **Requisitos de instalación**
* Docker Deskop version 29.1.3
* WSL (Ubuntu 24.04.1 LTS)
* Git

1. Clonar el repositorio desde WSL (Ubuntu) y entrar a la carpeta del repositorio
2. **Levantar el contenedor:**
```bash
     docker-compose up -d --build
```
3.**Instalar dependencias de Backend (Laravel):**
  ```bash
    docker-compose exec laravel composer install
    docker-compose exec laravel php artisan key:generate
    docker-compose exec laravel php artisan migrate --seed
  ```

    *Nota: El comando `--seed` creará usuarios y datos de prueba automáticamente.*
  4. **Instalar dependencias de frontend (vue.js)**
  ```bash
    docker compose exec frontend npm install
  ```
  4.  **Acceder a la aplicación:**
    * **Frontend:** [http://localhost:5173](http://localhost:5173)
    * **Backend API:** [http://localhost:8000](http://localhost:8000)


