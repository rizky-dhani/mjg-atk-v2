# Project Context

## Purpose
The MJG ATK Management System is a comprehensive inventory management solution for office stationery (Alat Tulis Kantor/ATK). It is designed to manage office supplies across multiple divisions with a robust multi-step approval workflow, ensuring transparency, accountability, and operational efficiency.

## Tech Stack
- **Framework:** Laravel 12
- **UI Framework:** Filament v4 (built on Livewire v3, Alpine.js, and Tailwind CSS)
- **Database:** MariaDB
- **PHP Version:** 8.4
- **Key Packages:**
    - `spatie/laravel-permission`: Role-based access control (RBAC).
    - `maatwebsite/excel`: Excel imports and exports.
    - `pestphp/pest`: Testing framework.
    - `laravel/pint`: PHP code style and formatting.

## Project Conventions

### Code Style
- **PHP 8.4+ Standards:** Use constructor property promotion, explicit return types, and typed properties.
- **Formatting:** Adhere to Laravel Pint's default style. Always run `vendor/bin/pint --dirty` before committing.
- **Naming Conventions:** 
    - Variables/Methods: camelCase (e.g., `isRegisteredForDiscounts`).
    - Classes/Models: PascalCase.
    - Database Tables/Columns: snake_case.
- **Documentation:** Use PHPDoc blocks for complex methods and define array shapes where appropriate.

### Architecture Patterns
- **Service Pattern:** Extract complex business logic into classes within `app/Services`.
- **Filament Resources:** Use Filament Resources for all administrative CRUD operations.
- **Model Observers:** Use `app/Observers` for decoupled model events (e.g., calculating totals or syncing stock).
- **Traits:** Use `app/Traits` for shared logic across multiple models (e.g., `StockRequestModelTrait`).
- **Policy-Based Authorization:** Use `app/Policies` for fine-grained access control, integrated with Spatie Permissions.
- **Mailables:** Use `app/Mail` for all email notifications.

### Testing Strategy
- **Framework:** Pest 4.
- **Type:** Primarily Feature tests for business logic and Filament components.
- **State Management:** Use factories for model creation and `RefreshDatabase` when needed.
- **Assertion Style:** Use descriptive Pest assertions (e.g., `assertForbidden()`, `assertNotified()`).

### Git Workflow
- **Commit Messages:** Clear and concise, focusing on "why" rather than "what". Follow the project's established commit style.
- **Verification:** Ensure all tests pass (`php artisan test`) and linting is clean before pushing.

## Domain Context
- **ATK (Alat Tulis Kantor):** Indonesian term for office stationery.
- **Floating Stock:** Centralized inventory pool managed by GA (General Affairs) before being distributed to divisions.
- **Division Stock:** Inventory held and consumed by a specific organizational unit.
- **Approval Flow:** A configurable sequence of steps (e.g., Manager -> Head -> GA) required for stock requests or usage.

## Important Constraints
- **Filament v4:** Adhere to v4 conventions (e.g., `Schemas/Components` instead of `Forms/Components` for layout).
- **Drafting:** Stock requests/usages should remain in "Draft" status until explicitly submitted for approval.
- **Audit Trail:** Every inventory movement must be tracked with a user, division, and timestamp.

## External Dependencies
- **Database:** MariaDB 10.2+.
- **Mail Server:** SMTP or similar for sending approval notifications.