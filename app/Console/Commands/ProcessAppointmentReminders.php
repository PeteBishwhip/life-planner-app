<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class ProcessAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'appointments:process-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Process and send due appointment reminders';

    /**
     * Execute the console command.
     */
    public function handle(ReminderService $reminderService): int
    {
        $this->info('Processing appointment reminders...');

        $results = $reminderService->processDueReminders();

        $this->info("Total reminders processed: {$results['total']}");
        $this->info("Successfully sent: {$results['sent']}");

        if ($results['failed'] > 0) {
            $this->warn("Failed to send: {$results['failed']}");
        }

        $this->info('Reminder processing completed.');

        return Command::SUCCESS;
    }
}
