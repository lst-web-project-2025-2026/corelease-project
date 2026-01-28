# Corelease

Internal Infrastructure Orchestration and Resource Allocation Platform.

Corelease is a centralized management system designed for the governance of data center assets within a research-oriented environment. The platform facilitates the systematic discovery, allocation, and maintenance of heterogeneous technical resources through a deterministic, role-based authorization hierarchy.

---

## Technical Overview

The system is engineered to provide a persistent "Single Source of Truth" (SSoT) for facility state management, ensuring operational transparency and resource occupancy exclusivity. It addresses the complexities of shared hardware environments through a decoupled architectural approach and automated state synchronization.

### Primary Functional Components

- **Resource Inventory Catalog**: A comprehensive repository of hardware and virtual assets, utilizing JSON-backed schemas to manage diverse technical specifications.
- **Transactional Allocation Engine**: A multi-stage reservation system that enforces project-based justification and administrative vetting.
- **Conflict Prevention Algorithm**: A temporal validation engine localized in the backend services that prevents overlapping resource occupancy across leases and downtime windows.
- **Maintenance Lifecycle Management**: A system for scheduling and tracking asset downtime, allowing for facility upgrades without disrupting approved research operations.
- **Forensic Audit System**: An immutable, state-differential logging mechanism that captures all administrative state changes for compliance and facility auditing.
- **Stateful Communication Hub**: A persistent notification engine for approval workflows and system-wide broadcast alerts.

### Architectural Stack

Corelease utilizes a containerized stack optimized for stability and environmental parity:

- **Backend Logic**: Laravel 12 (PHP 8.3)
- **Data Layer**: MySQL 8.4
- **Frontend Layer**: Atomic Blade Components and Token-based Vanilla CSS
- **Asset Orchestration**: Vite 6
- **Infrastructure**: Docker and Docker Compose

---

## Developer Setup and Environment Configuration

The Following instructions outline the procedure for establishing a bit-identical development environment using the provided Docker orchestration and VS Code Dev Containers configuration.

### System Requirements

1.  **Virtualization**: Hardware virtualization (VT-x or AMD-V) must be enabled in the host BIOS/UEFI.
2.  **Container Runtime**: Docker Desktop or Docker Engine is required. Windows users must ensure WSL2 integration is active for the target distribution.
3.  **IDE Integration**: Visual Studio Code with the **Dev Containers** and **WSL** (if applicable) extensions installed.

### Initial Environment Setup

1.  **Project Retrieval**: Clone the repository into the target file system.
    ```bash
    git clone https://github.com/lst-web-project-2025-2026/corelease-project.git
    cd corelease-project
    ```

2.  **Configuration Initialization**: Generate the local environment configuration from the provided template.
    ```bash
    cp .env.example .env
    ```

3.  **Container Initialization**: Open the project directory in VS Code.
    ```bash
    code .
    ```
    Select **"Reopen in Container"** when prompted by the Dev Containers extension.

### Internal Application Initialization

Within the containerized terminal environment, execute the following commands in sequence:

1.  **Dependency Installation**:
    ```bash
    composer install
    ```

2.  **Database Configuration**:
    Verify that the `.env` file contains the correct internal Docker networking parameters:
    ```dotenv
    DB_HOST=db
    DB_DATABASE=corelease_db
    DB_USERNAME=dev_user
    DB_PASSWORD=dev_password
    ```

3.  **Environment Finalization**:
    ```bash
    php artisan key:generate
    php artisan migrate:fresh --seed
    ```

### Verified Access Endpoints

- **Management Interface**: [http://localhost:8000](http://localhost:8000)
- **Database Administration (phpMyAdmin)**: [http://localhost:8080](http://localhost:8080)
  - Server: `db`
  - Credentials: `root` / `root_password`

---

## Operational Standards

- **Business Logic Isolation**: Domain logic should be localized within `app/Services`. Controllers are restricted to request/response orchestration.
- **Design Consistency**: User interface development must utilize the Atomic Blade components located in `resources/views/components/ui/`.
- **Theme Orchestration**: Global branding changes are managed via CSS tokens in `resources/css/global.css`.
- **Data Fidelity**: Seeding logic in `DatabaseSeeder.php` must be maintained to reflect current model relationships and operational states.
