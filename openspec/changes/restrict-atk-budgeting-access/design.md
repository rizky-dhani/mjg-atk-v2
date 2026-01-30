# Design: Restrict AtkBudgeting Access

## Authorization Strategy
We will implement `AtkBudgetingPolicy` to manage access to the `AtkBudgeting` resource.

-   **viewAny / view:** Allowed for `Admin` and `Super Admin` roles.
-   **create:** Allowed for `Admin` and `Super Admin` roles.
-   **update / delete:**
    -   `Super Admin`: Always allowed.
    -   `Admin`: Allowed if the user belongs to the division assigned to the budget record (`$user->belongsToDivision($atkBudgeting->division_id)`).

## Automation Strategy (division_id)
In `AtkBudgetingForm`, we will modify the `division_id` component:
-   **Default Value:** Set to the user's first division (`auth()->user()->divisions()->first()?->id`).
-   **Options:** 
    -   `Super Admin`: See all divisions.
    -   `Admin`: Only see divisions they belong to.
-   **Visibility/State:**
    -   If the user is not a `Super Admin` and belongs to only one division, the field can be hidden (using `Hidden` component) to "automate" it.
    -   If the user belongs to multiple divisions, they must select which division the budget is for, but the options are restricted to their divisions.

## Filament Resource Changes
-   Update `AtkBudgetingResource::getPages()` to include `create` and `edit` routes.
-   Ensure the resource respects the policy by Filament's default mechanism.

## Data Consistency
The `AtkBudgetingObserver` will continue to handle `remaining_amount` calculations. We will NOT move `division_id` automation to the observer to avoid ambiguity for multi-division users; instead, we handle it at the UI layer (Filament Form) where the intent is clearer.
