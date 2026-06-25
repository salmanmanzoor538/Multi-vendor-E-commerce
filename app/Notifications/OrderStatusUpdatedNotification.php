<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $orderId,
        private readonly string $orderNumber,
        private readonly OrderStatus $fromStatus,
        private readonly OrderStatus $toStatus,
        private readonly ?int $changedById,
        private readonly ?string $changedByName,
        private readonly ?string $note,
        private readonly string $occurredAtIso,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! config('notifications.order_updates.mail_enabled', true)) {
            return $channels;
        }

        $enabledStatuses = config('notifications.order_updates.mail_statuses', []);
        if (! is_array($enabledStatuses) || ! in_array($this->toStatus->value, $enabledStatuses, true)) {
            return $channels;
        }

        $channels[] = 'mail';

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $orderUrl = $this->resolveOrderUrl($notifiable);
        $changedBy = $this->changedByName ?: 'System';

        $mail = (new MailMessage())
            ->subject(sprintf('Order %s is now %s', $this->orderNumber, $this->toStatus->label()))
            ->greeting(sprintf('Hello %s,', $this->resolveRecipientName($notifiable)))
            ->line(sprintf(
                'Order %s status changed from %s to %s.',
                $this->orderNumber,
                $this->fromStatus->label(),
                $this->toStatus->label()
            ))
            ->line(sprintf('Updated by: %s', $changedBy))
            ->action('View Order', $orderUrl)
            ->line('Thank you for using our marketplace.');

        if ($this->note) {
            $mail->line(sprintf('Note: %s', $this->note));
        }

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'order.status_updated',
            'title' => sprintf('Order %s updated', $this->orderNumber),
            'message' => sprintf(
                'Order status changed from %s to %s.',
                $this->fromStatus->label(),
                $this->toStatus->label()
            ),
            'order_id' => $this->orderId,
            'order_number' => $this->orderNumber,
            'from_status' => $this->fromStatus->value,
            'from_status_label' => $this->fromStatus->label(),
            'to_status' => $this->toStatus->value,
            'to_status_label' => $this->toStatus->label(),
            'note' => $this->note,
            'changed_by_id' => $this->changedById,
            'changed_by_name' => $this->changedByName,
            'occurred_at' => $this->occurredAtIso,
            'url' => $this->resolveOrderUrl($notifiable),
        ];
    }

    private function resolveOrderUrl(object $notifiable): string
    {
        if (! $notifiable instanceof User) {
            return route('dashboard');
        }

        return match ($notifiable->role) {
            UserRole::CUSTOMER => route('customer.orders.show', $this->orderId),
            UserRole::VENDOR => route('vendor.orders.show', $this->orderId),
            default => route('dashboard'),
        };
    }

    private function resolveRecipientName(object $notifiable): string
    {
        if ($notifiable instanceof User && $notifiable->name !== '') {
            return $notifiable->name;
        }

        return 'there';
    }
}

