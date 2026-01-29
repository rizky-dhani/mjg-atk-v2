# Tasks: Enforce Password Change

- [x] Create migration to add `has_changed_password` to `users` table.
- [x] Update `User` model to include `has_changed_password` in `$fillable` and `$casts`.
- [x] Create `CheckPasswordChanged` middleware.
- [x] Create custom `EditProfile` page extending `Filament\Auth\Pages\EditProfile`.
- [x] Update `DashboardPanelProvider` to:
    - [x] Register the new middleware.
    - [x] Enable and register the custom `EditProfile` page.
- [x] Add notice/alert to the custom `EditProfile` page.
- [x] Implement logic in `EditProfile` to set `has_changed_password = true` on password change.
- [x] Verify the redirect logic with a test user.
- [x] Run `vendor/bin/pint --dirty` to ensure code style consistency.
- [x] Run affected tests to ensure no regressions.