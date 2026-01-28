# Design: User Multi-Division

## Database Changes

### New Pivot Table: `division_user`
- `user_id`: unsignedBigInteger, foreign key to `users.id`.
- `division_id`: unsignedBigInteger, foreign key to `user_divisions.id`.
- Primary key: `(user_id, division_id)`.

### Migration Plan
1. Create `division_user` table.
2. Iterate through all users with a non-null `division_id`.
3. Insert a record into `division_user` for each such user.
4. Keep `users.division_id` temporarily for backward compatibility during the transition, but mark it as deprecated.
5. In a subsequent task, remove `users.division_id`.

## Model Updates

### `App\Models\User`
- Add `divisions(): BelongsToMany` relationship.
- Update `isGA()`:
  ```php
  public function isGA(): bool
  {
      return $this->divisions()->where('initial', 'GA')->exists() || $this->isSuperAdmin();
  }
  ```
- Deprecate `division()` relationship and `division_id` attribute.

### `App\Models\UserDivision`
- Change `users(): HasMany` to `users(): BelongsToMany`.

## Filament Updates

### `App\Filament\Resources\Users\UserResource`
- Update the `divisions` select component:
  ```php
  Select::make('divisions')
      ->multiple()
      ->relationship('divisions', 'name')
      ->preload()
  ```

## Business Logic Adjustments

### Stock Requests and Usages
- Currently, many forms default the `division_id` to `auth()->user()->division_id`.
- This needs to change. If a user has only one division, it can still default. If they have multiple, the user MUST select one, or we default to the first one with a warning/indicator.
- Updated `AtkStockRequestForm`:
  ```php
  Select::make('division_id')
      ->options(fn () => auth()->user()->divisions->pluck('name', 'id'))
      ->default(fn () => auth()->user()->divisions->first()?->id)
  ```
- Note: Many Filament components currently use `auth()->user()->division_id` in `afterStateUpdated` and `helperText`. These need to be updated to use the `division_id` selected in the form (`$get('division_id')`).

## Testing Strategy
- Unit test for `User` model relationships.
- Feature test for `UserResource` saving multiple divisions.
- Feature test for `AtkStockRequest` creation by a user with multiple divisions.
- Verification of `isGA()` logic with multiple divisions.
