# Specification: Update Model Route Keys

## Overview
The goal is to use `request_number` as the route key for both `AtkStockRequest` and `AtkStockUsage`.

## Detailed Specifications

### 1. Route Key Binding
*   `AtkStockRequest`: Routes bound to this model must resolve using the `request_number` column.
*   `AtkStockUsage`: Routes bound to this model must resolve using the `request_number` column.

### 2. Model Updates
*   `AtkStockRequest`: Implement `getRouteKeyName()`.
*   `AtkStockUsage`: Implement `getRouteKeyName()`.
*   `MarketingMediaStockUsage`: Implement `getRouteKeyName()`.
