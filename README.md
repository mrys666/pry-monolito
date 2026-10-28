# 📦 MiniStock SaaS

Un sistema web de gestión de inventarios multi-empresa (SaaS) de alto rendimiento. Construido bajo los principios de **Arquitectura de Monolito Modular** orientada a dominios (DDD), garantizando alta cohesión, bajo acoplamiento, concurrencia segura y actualizaciones en tiempo real.

---

## ✨ Características Principales

*   🏢 **SaaS Multi-Tenant:** Aislamiento total de datos por organización. Dos empresas pueden coexistir en la misma base de datos (con los mismos SKUs) sin visibilidad cruzada, gestionado nativamente mediante Global Scopes y la fachada `Context` de Laravel.
*   ⚡ **Actualizaciones en Tiempo Real:** Interfaz viva impulsada por **Laravel Reverb**. Los movimientos de inventario se reflejan instantáneamente en las pantallas de todos los usuarios de la misma organización mediante WebSockets y Livewire 4, sin recurrir a peticiones periódicas (`wire:poll`).
*   🔒 **Concurrencia Segura:** Prevención absoluta de existencias negativas gracias al uso de transacciones atómicas (`DB::transaction`) y bloqueos exclusivos a nivel de fila de base de datos (`lockForUpdate()`).
*   🛡️ **Autorización Estricta:** Control de acceso robusto basado en roles (`Admin`, `Operator`, `Viewer`) mediante un Enum nativo y evaluado estrictamente en el servidor a través de Policies de Laravel (`Gate::authorize`).
*   📊 **Reportes Optimizados:** Panel de indicadores ultrarrápido utilizando consultas escalares (`toBase()`), subconsultas correlacionadas, índices compuestos y caché flexible (*stale-while-revalidate*).

---

## 🛠️ Stack Tecnológico

*   **Backend:** [Laravel 13.x](https://laravel.com/) (PHP 8.3+)
*   **Frontend:** [Livewire 4.x](https://livewire.laravel.com/) + [Alpine.js](https://alpinejs.dev/)
*   **UI Components:** [Flux UI](https://fluxui.dev/) (Free Edition) + [Tailwind CSS 4.x](https://tailwindcss.com/)
*   **WebSockets:** [Laravel Reverb](https://reverb.laravel.com/) + Laravel Echo
*   **Base de Datos:** SQL Server / SQLite
*   **Testing:** [Pest PHP](https://pestphp.com/) (Unit, Feature & Architecture Tests)

---

## 🏗️ Arquitectura (Monolito Modular)

El proyecto abandona el patrón MVC tradicional de Laravel en favor de una estructura orientada a dominios, ubicada en `app/Domain/`. Los módulos están estrictamente aislados y su comunicación transversal ocurre únicamente mediante la emisión de Eventos.

*   📂 **`Access`**: Enum de Roles y Policies de autorización.
*   📂 **`Catalog`**: Gestión de Categorías y Productos.
*   📂 **`Inventory`**: `Actions` transaccionales de movimientos (Entradas, Salidas, Ajustes), `Jobs` para alertas de stock bajo y WebSockets.
*   📂 **`Organization`**: Modelos, Traits (`BelongsToOrganization`) y Scopes para el aislamiento SaaS.
*   📂 **`Reports`**: Objetos de consulta especializados (`Queries`) para cálculos pesados y solo-lectura.

Las reglas arquitectónicas están blindadas por pruebas automatizadas (`arch()`) que impiden, por ejemplo, que los dominios importen clases de la capa HTTP o de Livewire.

---

## 🚀 Requisitos Previos

*   PHP 8.3 o superior
*   Composer 2.x
*   Node.js 20+ y NPM
*   SQL Server u otro motor relacional (por defecto, configurado para aprovechar `sqlsrv`).

---

## ⚙️ Instalación y Configuración

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/mrys666/pry-monolito.git
   cd pry-monolito
