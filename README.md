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

## Screenshots

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:43:43" src="https://github.com/user-attachments/assets/399d1e2a-e2e4-4921-8bba-1344d10dd4d1" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:43:27" src="https://github.com/user-attachments/assets/056835f8-76c6-44e3-864e-b845b8d61029" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:43:51" src="https://github.com/user-attachments/assets/0e60a2f0-77b9-4ca5-a22a-0857ea78394c" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:43:59" src="https://github.com/user-attachments/assets/4dac5bce-07fc-4b3a-9f24-61a0db559b51" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:44:17" src="https://github.com/user-attachments/assets/9a1f47b9-03f0-4aa0-842f-daf46e1a4c8b" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:45:11" src="https://github.com/user-attachments/assets/5f2afaf2-deca-4017-8240-45119663a726" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:45:23" src="https://github.com/user-attachments/assets/5bace5db-0b49-4a1f-93df-7f6c2b1109bf" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:45:31" src="https://github.com/user-attachments/assets/5da2854f-3231-4d19-a50e-de60635794d9" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:46:19" src="https://github.com/user-attachments/assets/e2f03435-78ed-4ff5-b84f-751c84e95db2" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:46:52" src="https://github.com/user-attachments/assets/65894a94-a256-4b53-876d-a85d57ee5333" />

<img width="1920" height="1026" alt="Screenshot_2026,01,28_23:47:29" src="https://github.com/user-attachments/assets/9bc73962-b135-45a9-a8d8-4e05f2700671" />

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
