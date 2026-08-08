# Implementation Summary

Date: 2026-08-08

This document briefly summarizes security and UX improvements applied to the project.

## Rate limiting

- Added named rate limiters in `app/Providers/RouteServiceProvider.php`:
    - `api` (default): 60 requests per minute (existing)
    - `currency`: 30 requests per minute (for public currency views/endpoints)
    - `sensitive`: 10 requests per minute (for write/convert/compare endpoints)
- Applied throttling middleware to routes:
    - Web currency route group uses `throttle:currency`.
    - POST/write routes (convert, compare, historical data, trend data) use `throttle:sensitive`.
    - Example API routes in `routes/api.php` also demonstrate `throttle:currency` and `throttle:sensitive` usage.

## 429 handling / JSON responses

- Implemented JSON 429 responses for API clients in `app/Exceptions/Handler.php` so API consumers receive a consistent error payload: `{ message, retry_after }`.

## Auto-refresh UX change

- Removed automatic dashboard polling in `resources/views/currency/dashboard.blade.php` to reduce background API calls and respect rate limits.
- Kept a manual `Refresh Dashboard` button that triggers a single fetch. This avoids unexpected data churn and reduces third-party API usage.

## Cache / Redis

- Attempted to enable `CACHE_DRIVER=redis` in `.env` but reverted to `file` because the PHP Redis extension (`phpredis`) is not present in the current environment and would cause runtime errors.
- Redis configuration variables remain in `.env` as optional settings. To enable Redis safely:
    - Option A: Install the PHP `phpredis` extension on your system.
    - Option B: Run `composer require predis/predis` and set `REDIS_CLIENT=predis` and `CACHE_DRIVER=redis`.

## Documentation

- Added a Rate Limiting section to the project README explaining the new limiters and Redis recommendation.

## Files changed (high level)

- `app/Providers/RouteServiceProvider.php` — added named limiters
- `routes/web.php` — applied `throttle:currency` and `throttle:sensitive` middleware
- `routes/api.php` — added example endpoints demonstrating throttles
- `app/Exceptions/Handler.php` — JSON 429 responses
- `resources/views/currency/dashboard.blade.php` — removed auto-refresh polling, left manual refresh
- `.env` — updated (brief Redis settings added then reverted `CACHE_DRIVER` to `file`)
- `README.md` — documented rate limits

## Recommended next steps

- If you want distributed, reliable throttling in production, enable Redis (see above). I can add `predis` to composer.json and configure it for you.
- Optionally add a user-controlled auto-refresh toggle (localStorage) if you want the feature back but user-controllable.
- Add monitoring/alerts for repeated 429s to detect abuse.

If you'd like, I can now:

- Enable Redis via `composer require predis/predis` and update config, or
- Add an Auto-refresh toggle UI that respects rate limits, or
- Add an automated test for the dashboard refresh route.
