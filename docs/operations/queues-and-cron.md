# Queue and Cron Operations

## Scheduler Model
- Primary cron entry:
  ```cron
  * * * * * php /home/USER/app/artisan schedule:run >> /dev/null 2>&1
  ```
- Queue workers are triggered by Laravel scheduler, not a persistent daemon.

## Scheduled Queue Commands
- Queue worker command (every minute):
  - `queue:work <connection> --queue=high,default --sleep=3 --tries=3 --max-time=55 --stop-when-empty`
- Failed jobs pruning (daily):
  - `queue:prune-failed --hours=<QUEUE_FAILED_RETENTION_HOURS>`
- Subscription expiration command:
  - `subscriptions:expire` at `SUBSCRIPTIONS_EXPIRE_AT`.

## Fallback Cron (Emergency Only)
Use only if scheduler is down:
```cron
* * * * * php /home/USER/app/artisan queue:work database --queue=high,default --sleep=3 --tries=3 --max-time=55 --stop-when-empty >> /dev/null 2>&1
```

## Failed Job Recovery
- List failed jobs:
  ```bash
  php artisan queue:failed
  ```
- Retry one failed job:
  ```bash
  php artisan queue:retry <id>
  ```
- Retry all failed jobs (guarded helper):
  ```bash
  php artisan ops:queue:retry-all
  ```
- Forget one failed job:
  ```bash
  php artisan queue:forget <id>
  ```

## Queue Health Checks
- Ensure queue tables exist (`jobs`, `failed_jobs`, `job_batches`).
- Confirm jobs are draining each minute.
- If backlog grows, increase cron worker throughput by:
  - widening `QUEUE_WORKER_QUEUES` priorities,
  - reducing long-running jobs by splitting workload,
  - tuning `QUEUE_WORKER_TRIES` and timeout behavior.

## Logging Paths
- Application logs: `storage/logs/laravel-YYYY-MM-DD.log`.
- Request correlation: each request includes `request_id` in logs and `X-Request-Id` response header.
