# Changelog

All notable changes to `northbees/codeweavers-api` will be documented in this file.

## Unreleased

- Initial release: JSON transport over Laravel's HTTP client with retries, typed DTOs, and resources for finance defaults, calculate for display, quote terms and conditions, and v3 JSON bulk calculations.
- Per-instance credentials, environment and organisation via `Codeweavers::withCredentials()`, `withEnvironment()` and `withOrganisation()`.
- `Codeweavers::fake()` and `CodeweaversResponse` testing helpers.
