# Design: Update Model Route Keys

This document details the implementation plan for updating route keys for `AtkStockRequest` and `AtkStockUsage`.

## Database Changes
None required as columns already exist.

## Model Changes

### `App\Models\AtkStockRequest`
Add `getRouteKeyName()` method:
```php
public function getRouteKeyName(): string
{
    return 'request_number';
}
```

### `App\Models\AtkStockUsage`
Add `getRouteKeyName()` method:
```php
public function getRouteKeyName(): string
{
    return 'request_number';
}
```

## Logic Changes
None required as the column name remains `request_number`.

## Verification Plan

### Automated Tests
1.  Verify route model binding works as expected with the new keys.

### Manual Verification
1.  Access a stock request and stock usage in the Filament panel and verify the URL contains the respective number instead of the ID.
