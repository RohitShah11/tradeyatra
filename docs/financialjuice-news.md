# FinancialJuice news stream

FinancialJuice delivers news over a WebSocket and does not replay messages missed while disconnected. The application therefore stores incoming events in `financial_juice_news` and merges the latest seven days into the existing News page.

## Setup

1. Set `FINANCIAL_JUICE_API_KEY` in `.env`.
2. Apply the database migration:

   ```shell
   php artisan migrate
   ```

3. Verify the key and connection:

   ```shell
   php artisan news:stream-financial-juice --check
   ```

4. Run the listener continuously under Supervisor, systemd, or another process manager:

   ```shell
   php artisan news:stream-financial-juice
   ```

The listener reconnects with capped exponential backoff. Created and updated events are upserted, deleted events are removed, and affected news caches are cleared automatically.
