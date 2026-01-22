# Design: AtkFulfillment Resource

## Architecture

The `AtkFulfillment` resource will be a standard Filament resource, leveraging Livewire components for its interactive features. It will interact with existing `AtkStockRequest`, `AtkStockRequestItem`, `Approval`, and `UserDivision` models.

### Data Flow

1.  **User Access**: A user attempts to access the `AtkFulfillment` resource.
2.  **Policy Enforcement**: The `AtkFulfillmentPolicy` intercepts the request, checking if the user's division has the "IPC initial" attribute and if the `AtkStockRequest` is fully approved.
3.  **Resource Display**: If authorized, the resource displays a table of relevant `AtkStockRequest` records.
4.  **Fulfillment Management**: Upon selecting an `AtkStockRequest`, a detailed view allows modification of `AtkStockRequestItem.received_quantity`.
5.  **Status Update**: Changes to `received_quantity` trigger updates to `AtkStockRequest.fulfillment_status`.

## Components

-   **`AtkFulfillmentResource.php`**: The main Filament resource definition.
    -   `getEloquentQuery()`: Will be overridden to filter `AtkStockRequest`s based on approval status and division initial.
    -   `table()`: Defines the columns for listing `AtkStockRequest`s.
    -   `form()`: Defines the form for viewing/editing `AtkStockRequest` details (e.g., items).
    -   `getPages()`: Defines the list and edit pages.
-   **`AtkFulfillmentPolicy.php`**: Policy to control access to the resource.
    -   `viewAny()`: Checks for "IPC initial" division and relevant permissions.
    -   `view()`: Checks for "IPC initial" division and relevant permissions.
    -   `update()`: Checks for "IPC initial" division and relevant permissions, and ensures the request is approved.
-   **Livewire Components**: Potentially custom Livewire components for complex fulfillment logic if standard Filament forms are insufficient for updating `received_quantity` efficiently.

## Relationships

-   **`AtkFulfillment` (Resource) <-> `AtkStockRequest` (Model)**: The resource operates on `AtkStockRequest` instances.
-   **`AtkStockRequest` <-> `AtkStockRequestItem`**: One-to-many relationship, where items are fulfilled.
-   **`AtkStockRequest` <-> `Approval`**: One-to-one relationship to check approval status.
-   **`AtkStockRequest` <-> `UserDivision`**: Many-to-one relationship to check the division's `initial` attribute.

## Open Questions / Considerations

-   How will "IPC initial" divisions be identified? Will there be a specific `initial` value (e.g., 'IPC') on the `UserDivision` model, or a more dynamic check? (Assuming `UserDivision.initial` is sufficient for now).
-   What specific permissions will be required for a user to access this resource? (e.g., `manage-atk-fulfillment`).
