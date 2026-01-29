# Tasks: Pin Specific Approver to Approval Step

## Database
- [x] Create migration to add `user_id` to `approval_flow_steps` table. <!-- id: 0 -->

## Model
- [x] Add `user_id` to `$fillable` in `ApprovalFlowStep`. <!-- id: 1 -->
- [x] Add `user()` relationship to `ApprovalFlowStep`. <!-- id: 2 -->
- [x] Update `getPotentialApprovers` in `ApprovalFlowStep` to prioritize `user_id`. <!-- id: 3 -->

## Services
- [x] Update `ApprovalValidationService` to check `user_id` before role/division matching. <!-- id: 4 -->
- [x] Update `ApprovalProcessingService`'s `getNextApprovers` to prioritize `user_id`. <!-- id: 5 -->

## UI (Filament)
- [x] Add `user_id` Select field to `ApprovalFlowStepsRelationManager`. <!-- id: 6 -->
- [x] Implement reactive filtering for `user_id` Select based on `role_id` and `division_id`. <!-- id: 7 -->
- [x] Add "User" column to `ApprovalFlowStepsRelationManager` table. <!-- id: 8 -->

## Verification
- [x] Create test to verify only pinned user can approve. <!-- id: 9 -->
- [x] Create test to verify fallback to role/division when no user is pinned. <!-- id: 10 -->
- [x] Run `vendor/bin/pint --dirty` to ensure code style compliance. <!-- id: 11 -->