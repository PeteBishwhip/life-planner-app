<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Appointment $appointment;

    public AppointmentReminder $reminder;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment, AppointmentReminder $reminder)
    {
        $this->appointment = $appointment;
        $this->reminder = $reminder;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($this->reminder->notification_type === 'email') {
            $channels[] = 'mail';
        }

        if ($this->reminder->notification_type === 'browser') {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $timeUntil = $this->formatTimeUntil($this->reminder->reminder_minutes_before);

        return (new MailMessage)
            ->subject('Reminder: '.$this->appointment->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('This is a reminder for your upcoming appointment.')
            ->line('**'.$this->appointment->title.'**')
            ->line('**When:** '.$this->appointment->start_datetime->format('l, F j, Y \a\t g:i A'))
            ->line('**Time until appointment:** '.$timeUntil)
            ->when($this->appointment->location, function ($mail) {
                return $mail->line('**Location:** '.$this->appointment->location);
            })
            ->when($this->appointment->description, function ($mail) {
                return $mail->line('**Details:** '.$this->appointment->description);
            })
            ->action('View Appointment', url('/dashboard'))
            ->line('Thank you for using Life Planner!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'title' => $this->appointment->title,
            'start_datetime' => $this->appointment->start_datetime->toISOString(),
            'location' => $this->appointment->location,
            'reminder_minutes_before' => $this->reminder->reminder_minutes_before,
        ];
    }

    /**
     * Format time until appointment
     */
    protected function formatTimeUntil(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes.' minutes';
        }

        if ($minutes < 1440) {
            $hours = round($minutes / 60, 1);

            return $hours == 1 ? '1 hour' : $hours.' hours';
        }

        $days = round($minutes / 1440, 1);

        return $days == 1 ? '1 day' : $days.' days';
    }
}
