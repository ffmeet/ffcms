# Changelog

All notable changes to this project will be documented in this file.

## v1.0.0 - 2026-05-05

### Added

- Public developer documentation center with sidebar, on-page TOC, search, code copy, and anchor links.
- Dedicated homepage settings system tied to theme-specific slot positions.
- Homepage builder architecture for `xiaofang`, moving heavy data composition out of Blade.
- Secure source package workflow with `.releaseignore` and `composer package:source`.
- First-admin creation command: `php artisan ffmeet:create-admin`.
- Release and deployment documentation for external developers.

### Changed

- Default branding unified to `FFMeet`.
- Public docs path rendering now rewrites Markdown relative links to docs-center routes.
- Homepage content structure and slot mapping aligned with current `xiaofang` layout.
- Authentication flow and password-reset flow refined for frontend consistency.
- Member center styling and link behavior refined across posts, comments, orders, subscriptions, and activities.

### Security

- Production environment no longer allows simulated payment completion.
- Payment webhook handling now enforces a safer production baseline.
- Authentication endpoints include baseline rate limiting.
- Demo accounts and demo content no longer seed automatically in production-style installs.

### Notes

- `v1.0.0` is a production-observation release.
- Real third-party payment gateway integration is still a follow-up task and should be enabled carefully per provider.

