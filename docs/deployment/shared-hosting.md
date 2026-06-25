# Shared Hosting Deployment Runbook

This runbook targets Laravel on shared hosting with cron-only queue workers.

## Prerequisites
- PHP 8.3+ with required Laravel extensions (`mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`).
- MySQL database and user provisioned.
- Cron access.
- Writable directories: `storage/` and `bootstrap/cache/`.

## Initial Deployment
1. Upload source code to the application path.
2. Install dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Create `.env` from `.env.production.example` and fill all production secrets.
4. Generate key if missing:
   ```bash
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate --force
   ```
6. Build/cache framework artifacts:
   ```bash
   php artisan optimize
   ```
7. Set cron for scheduler:
   ```cron
   * * * * * php /home/USER/app/artisan schedule:run >> /dev/null 2>&1
   ```
8. Verify scheduler-driven queue execution:
   ```bash
   php artisan schedule:run
   ```
9. Verify logs are written in `storage/logs` and that `failed_jobs` remains stable.

## Zero-Downtime-ish Release Steps
1. Enable maintenance mode (optional but recommended for schema-sensitive deploys):
   ```bash
   php artisan down --retry=60
   ```
2. Upload new code.
3. Run:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan optimize:clear
   php artisan optimize
   ```
4. Bring app up:
   ```bash
   php artisan up
   ```
5. Run smoke checks: login, vendor dashboard, checkout, Stripe webhook endpoint.

## Rollback Procedure
1. Put app in maintenance mode:
   ```bash
   php artisan down --retry=60
   ```
2. Revert to previous release files.
3. Restore database from backup if schema/data changes require it.
4. Rebuild caches:
   ```bash
   php artisan optimize:clear
   php artisan optimize
   ```
5. Bring app back:
   ```bash
   php artisan up
   ```

## Post-Deploy Monitoring
- Check `storage/logs/laravel-*.log` for spikes in `error` and `critical`.
- Track `failed_jobs` count.
- Confirm cron continues triggering every minute.
