<?php

namespace App\Notifications\Concerns;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

/**
 * Shared by notifications that should also reach the user by email.
 *
 * - The in-app (database) notification is saved immediately ("sync").
 * - The email is handed to the queue worker, so a slow or misconfigured
 *   mail server never breaks the action that triggered it (e.g. approving
 *   a claim). Failed emails can be retried with `php artisan queue:retry`.
 *
 * The email reuses the same message and link as the in-app notification,
 * so the two can never say different things. Like the in-app version, it
 * never contains hidden details, emails, or phone numbers.
 */
trait AlsoSendsEmail
{
    use Queueable, SerializesModels;

    /** The email subject line, e.g. "Your claim was approved". */
    abstract protected function mailSubject(): string;

    public function via(User $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function viaConnections(): array
    {
        return ['database' => 'sync'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $data = $this->toArray($notifiable);

        return (new MailMessage)
            ->subject($this->mailSubject().' - LostMate AI')
            ->greeting('Hi '.$notifiable->name.',')
            ->line($data['message'])
            ->action('Open LostMate AI', $data['link'])
            ->line('AI matches are suggestions only. Always verify ownership before handing over an item.');
    }
}
