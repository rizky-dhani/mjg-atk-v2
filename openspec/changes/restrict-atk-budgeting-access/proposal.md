# Proposal: Restrict AtkBudgeting Access

## Goal
Restrict access to `AtkBudgeting` resource to users with the `Admin` role (or `Super Admin`) and ensure they can only manage budgets for divisions they belong to. Additionally, automate the `division_id` assignment during creation.

## Scope
-   **Authorization:** Implement `AtkBudgetingPolicy` to enforce role and division-based access control.
-   **Filament Resource:** Update `AtkBudgetingResource` (and its form) to automate `division_id` and respect the new policy.
-   **User Experience:** Ensure users only see and can only manage budgets relevant to their assigned divisions.

## Dependencies
-   `spatie/laravel-permission` (already installed)
-   `AtkBudgeting` model (already exists)
-   `UserDivision` model (already exists)

## Risks
-   Users belonging to multiple divisions: The automation logic must handle multi-division users gracefully (e.g., by allowing selection from their assigned divisions if more than one exists).
