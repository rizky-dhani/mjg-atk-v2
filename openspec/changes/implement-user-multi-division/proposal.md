# Proposal: Implement User Multi-Division

This proposal addresses the requirement for users to belong to multiple divisions instead of a single division. This change is necessary to support scenarios where a user may have roles or responsibilities across different organizational units, such as managers overseeing multiple departments or GA staff supporting multiple divisions.

## Motivation

The current implementation restricts a user to a single division through a `division_id` foreign key on the `users` table. As the organization grows, users often need to be associated with multiple divisions for approvals, stock requests, and other activities. Moving to a many-to-many relationship provides the necessary flexibility and accurately reflects the organizational reality.

## High-Level Design

The change involves:
- Creating a `division_user` pivot table to store the many-to-many relationship between `users` and `user_divisions`.
- Updating the `User` model to replace the `belongsTo` relationship with a `belongsToMany` relationship named `divisions`.
- Updating the `UserDivision` model to replace the `hasMany` relationship with a `belongsToMany` relationship named `users`.
- Migrating existing data from the `users.division_id` column to the `division_user` pivot table.
- Updating business logic that depends on a single division (e.g., `User::isGA()`, `AtkStockRequest` creation, etc.) to handle multiple divisions.
- Updating the Filament `UserResource` to allow selecting multiple divisions for a user using a `multiple()` select component.

## Security Considerations

Access control logic that previously relied on a single division must be updated to check against the collection of divisions a user belongs to. For example, a user should be able to see stock requests for any division they are a member of. This change will require careful updates to existing Policies and Gates.

## Impact

- **Increased Flexibility**: Users can now represent and act on behalf of multiple divisions.
- **Accurate Representation**: Better reflects the actual organizational structure and user responsibilities.
- **Database Schema**: Transition from a 1:N relationship to a M:N relationship, involving a new pivot table and eventual removal of the `division_id` column from the `users` table.
