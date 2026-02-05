# Proposal: Update Model Route Keys for AtkStockRequest and AtkStockUsage

This proposal aims to improve URL readability and consistency by using descriptive identifiers (`request_number`) as route keys for the `AtkStockRequest` and `AtkStockUsage` models.

## Motivation

Currently, these models use their primary keys (`id`) for route model binding by default. Using human-readable identifiers like `request_number` in URLs makes them more informative and consistent with the application's domain language.

## High-Level Design

1.  **AtkStockRequest**:
    *   Update the model to use `request_number` as its route key.
2.  **AtkStockUsage**:
    *   Update the model to use `request_number` as its route key.

## Impact

*   **Improved URLs**: More descriptive and user-friendly URLs (e.g., `/atk-stock-requests/ATK-DIV-REQ-00000001` instead of `/atk-stock-requests/1`).
*   **Consistency**: Aligns model identification across different parts of the system.
