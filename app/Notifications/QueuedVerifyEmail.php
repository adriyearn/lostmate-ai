<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Laravel's standard "verify your email" message, but sent by the queue
 * worker. If the mail server is slow or misconfigured, registration still
 * succeeds instead of crashing; the email is retried in the background.
 */
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;
}
