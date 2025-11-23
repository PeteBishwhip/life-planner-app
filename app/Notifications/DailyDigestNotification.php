<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DailyDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Collection $appointments;

    protected \Carbon\Carbon $date;

    /**
     * Create a new notification instance.
     */
    public function __construct(Collection $appointments, \Carbon\Carbon $date)
    {
        $this->appointments = $appointments;
        $this->date = $date;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your Daily Agenda - '.$this->date->format('l, F j, Y'))
            ->greeting('Good morning, '.$notifiable->name.'!');

        if ($this->appointments->isEmpty()) {
            return $message
                ->line('You have no appointments scheduled for today.')
                ->line('Enjoy your free day!')
                ->line('Have a great day!');
        }

        $message->line('Here\'s your schedule for today:');

        foreach ($this->appointments as $appointment) {
            $time = $appointment->is_all_day
                ? 'All Day'
                : $appointment->start_datetime->format('g:i A').' - '.$appointment->end_datetime->format('g:i A');

            $message->line('');
            $message->line('**'.$appointment->title.'**');
            $message->line('⏰ '.$time);

            if ($appointment->location) {
                $message->line('📍 '.$appointment->location);
            }

            if ($appointment->calendar) {
                $message->line('📅 '.$appointment->calendar->name);
            }
        }

        return $message
            ->line('')
            ->line('Total appointments: '.$this->appointments->count())
            ->action('View Full Calendar', url('/dashboard'))
            ->line('Have a productive day!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'date' => $this->date->toDateString(),
            'appointment_count' => $this->appointments->count(),
            'appointments' => $this->appointments->map(fn ($apt) => [
                'id' => $apt->id,
                'title' => $apt->title,
                'start_time' => $apt->start_datetime->toISOString(),
                'end_time' => $apt->end_datetime->toISOString(),
            ])->toArray(),
        ];
    }
}
