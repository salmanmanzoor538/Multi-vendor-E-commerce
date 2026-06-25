<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('subscriptions:expire', function () {
    $subscriptionService = app(\App\Services\SubscriptionService::class);
    $expired = $subscriptionService->expireDueSubscriptions();

    $this->info("Expired subscriptions: {$expired->count()}");
})->purpose('Expire overdue active subscriptions.');

Artisan::command('ops:queue:retry-all', function () {
    $this->warn('This command retries all failed jobs and may re-trigger side effects.');

    if (! $this->confirm('Proceed with retrying all failed jobs?')) {
        $this->info('Cancelled.');

        return self::SUCCESS;
    }

    $this->call('queue:retry', ['id' => 'all']);

    return self::SUCCESS;
})->purpose('Manually retry all failed queue jobs.');

$queueCommand = sprintf(
    'queue:work %s --queue=%s --sleep=%d --tries=%d --max-time=%d --stop-when-empty',
    env('QUEUE_WORKER_CONNECTION', config('queue.default', 'database')),
    env('QUEUE_WORKER_QUEUES', 'high,default'),
    (int) env('QUEUE_WORKER_SLEEP', 3),
    (int) env('QUEUE_WORKER_TRIES', 3),
    (int) env('QUEUE_WORKER_MAX_TIME', 55),
);

Schedule::command($queueCommand)
    ->name('queue-work-cron')
    ->everyMinute()
    ->withoutOverlapping(10);

Schedule::command('subscriptions:expire')
    ->name('subscriptions-expire')
    ->dailyAt(env('SUBSCRIPTIONS_EXPIRE_AT', '01:00'))
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();

Schedule::command(sprintf(
    'queue:prune-failed --hours=%d',
    (int) env('QUEUE_FAILED_RETENTION_HOURS', 168),
))
    ->name('queue-prune-failed')
    ->dailyAt(env('QUEUE_FAILED_PRUNE_AT', '02:00'))
    ->withoutOverlapping();
