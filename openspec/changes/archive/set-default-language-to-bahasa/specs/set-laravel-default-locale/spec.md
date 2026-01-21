## MODIFIED Requirements

### Requirement: Set Laravel Default Locale to Bahasa Indonesia
This requirement SHALL ensure that the Laravel application is configured to use Bahasa Indonesia as its default language.

#### Scenario: Application's default locale is set to Bahasa Indonesia

Given the Laravel application configuration,
When the application starts,
Then the default locale (`locale`) MUST be set to `'id'`,
And the fallback locale (`fallback_locale`) MUST also be set to `'id'`.
