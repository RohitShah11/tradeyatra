# TradeYatra India Product Blueprint

## Vision

TradeYatra should become a professional trading journal for SharkExchange users in India. The product should let Indian traders connect SharkExchange API keys, automatically import trades, review performance, tag behavior, inspect mistakes, and improve repeatable decision making.

The current Laravel app already has useful foundations:

- Manual trade journal views and analytics.
- SharkExchange API settings, market lookup, and sync pages.
- Encrypted storage for `api_secret`.
- Trade import mapping from SharkExchange payloads.

The next phase is turning the app from a single-user journal into a secure India-focused multi-user SaaS product.

## Target Users

- Active Indian SharkExchange futures traders who want automatic trade journaling.
- Semi-professional traders who need analytics, screenshots, notes, and strategy review.
- Trading communities or mentors who may later need team/account review features.
- Indian users who need IST timezone defaults, INR-first reporting, and simple regional formatting.

## MVP Product Scope

### User Account System

- Email/password registration and login.
- Email verification.
- Password reset.
- User profile with timezone, country, preferred currency, and display language.
- Default country: India.
- Default timezone: Asia/Kolkata.
- Default currency: INR.
- Every trade, Shark account, sync log, screenshot, and setting must belong to one user.

### SharkExchange Connection

- User-owned SharkExchange API credentials.
- Encrypted `api_key` and `api_secret`.
- Clear permissions guidance: users should create read-only API keys if SharkExchange supports it.
- Test connection button.
- Manual sync button.
- Automatic background sync job.
- Optional manual "Sync now" button for users who want an immediate refresh.
- Sync log history per user.
- Error handling that explains API failures without exposing secrets.

### Trade Journal

- Automatic imports from SharkExchange trade history via `/v1/user-data/trade-history`.
- PnL should use `realizedProfitInMarginAsset` as the primary realized-profit field.
- Manual trade entry for non-imported trades.
- Duplicate detection by SharkExchange trade/order IDs scoped per user.
- Trade screenshots and notes.
- Strategy, emotion, mistake tags, plan-followed, setup quality, and exit reason.
- Filters by date, symbol, status, broker, strategy, and imported/manual source.

### Analytics

- Net PnL, gross profit, gross loss, fees, win rate, average win, average loss.
- Equity curve.
- Strategy performance.
- Symbol performance.
- Day/time performance.
- Mistake tag performance.
- Imported vs manual trade comparison.

### India Scope

- Store all sync timestamps in UTC.
- Display dates/times in Asia/Kolkata by default.
- Store money amounts with INR currency context.
- Keep the system India-first because SharkExchange is India-focused.
- Prepare translation files only if Hindi or other Indian-language support becomes a product requirement.

## Technical Architecture

### Framework

Keep Laravel as the backend. It is already installed and fits the product well:

- Authentication, sessions, policies, queues, encrypted casts, mail, validation, and jobs are all first-class.
- The existing Blade UI can evolve quickly for the MVP.
- Later, the frontend can move to Inertia or a separate SPA if needed.

### Core Data Ownership

Add `user_id` to:

- `trades`
- `shark_accounts`
- `sync_logs`

Then update all controllers and model queries to scope by `auth()->id()`.

Recommended uniqueness:

- `trades`: unique `user_id + shark_trade_id` when `shark_trade_id` exists.
- `trades`: unique `user_id + shark_order_id` fallback when no trade ID exists.
- `shark_accounts`: allow multiple accounts per user, but only one active account per user at first.

### Background Jobs

Move SharkExchange sync out of the controller into jobs:

- `SyncSharkAccountJob`
- `ImportSharkTradesAction`
- `NormalizeSharkTradeAction`

The controller should only validate the request, dispatch the job, and show status.

For automatic sync:

- Schedule a command such as `shark:sync-active-accounts` every 5 to 15 minutes.
- The command should find Shark accounts with valid credentials and auto-sync enabled.
- Dispatch one queue job per account.
- Use a lock per account so two sync jobs cannot import duplicates at the same time.
- Keep manual sync as a fallback, not the main workflow.
- Start with trade-history sync first because `/v1/user-data/trade-history` includes realized PnL through `realizedProfitInMarginAsset`.

### API Client

Keep `App\Services\SharkExchangeClient`, but improve it for SaaS reliability:

- Add request timeout and retry rules.
- Add structured exception types.
- Never log API keys, secrets, signatures, or raw headers.
- Store raw payloads only where useful and consider redaction.
- Add tests around payload normalization.

### Security Requirements

- Use HTTPS everywhere.
- Encrypt both `api_key` and `api_secret`.
- Never show secrets after save.
- Mask API keys in UI.
- Add CSRF protection on all forms.
- Add rate limiting for auth, sync, and API-test endpoints.
- Add user authorization policies for trades and Shark accounts.
- Make uploaded screenshots private or user-scoped before public launch.
- Add terms, privacy policy, and risk disclaimer.

### Recommended Laravel Packages

- Laravel Breeze or Jetstream for authentication.
- Laravel Horizon if using Redis queues in production.
- Laravel Cashier later for subscriptions.
- Spatie Laravel Permission later for admin/team roles.

## Suggested Database Changes

Create a migration for:

- `trades.user_id`
- `shark_accounts.user_id`
- `sync_logs.user_id`
- `users.timezone`
- `users.country`
- `users.currency`
- `users.locale`

Update models:

- `User hasMany Trade`
- `User hasMany SharkAccount`
- `User hasMany SyncLog`
- `Trade belongsTo User`
- `SharkAccount belongsTo User`
- `SyncLog belongsTo User`

## MVP Roadmap

### Phase 1: SaaS Foundation

- Install authentication.
- Add user-owned data migrations.
- Scope all journal and SharkExchange queries to the logged-in user.
- Protect routes with auth middleware.
- Add basic policies.
- Replace global `SharkAccount::active()` with user-scoped lookup.

### Phase 2: Reliable Shark Sync

- Move sync into a queued job.
- Add scheduled automatic sync for active Shark accounts.
- Add connection test.
- Add per-user sync logs.
- Add duplicate-safe imports.
- Add normalized trade import tests.
- Add sync status UI.

### Phase 3: Professional Journal UX

- Improve dashboard information density.
- Add advanced filters.
- Add strategy/mistake analytics.
- Add private screenshot handling.
- Add export to CSV.

### Phase 4: India Launch Readiness

- Add timezone and currency preferences.
- Add translation-ready UI strings.
- Add legal pages.
- Add onboarding flow.
- Add admin dashboard for users, sync health, and failed jobs.
- Add monitoring, backups, and deployment pipeline.

### Phase 5: Monetization and Scale

- Add subscription plans.
- Add usage limits by plan.
- Add team/mentor review features.
- Add public shareable reports if traders want them.
- Add multiple exchange accounts per user.

## Immediate Next Build Step

The safest next implementation is Phase 1:

1. Add authentication.
2. Add `user_id` ownership to trades, Shark accounts, and sync logs.
3. Scope all existing pages to the authenticated user.
4. Move Shark sync into a reusable service/job.
5. Add automatic scheduled sync and keep manual sync as a backup.

This turns the current app into the base of a real public product without rewriting everything.
