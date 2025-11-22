<?php

namespace App\Console\Commands;

use App\Services\DailyDigestService;
use Illuminate\Console\Command;

class SendDailyDigests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'digest:send-daily {--date= : The date to send digest for (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily agenda digest to all users';

    /**
     * Execute the console command.
     */
    public function handle(DailyDigestService $digestService): int
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : today();

        $this->info('Sending daily digests for '.$date->format('Y-m-d').'...');

        $results = $digestService->sendToAllUsers($date);

        $this->info('Total users: '.$results['total']);
        $this->info('Successfully sent: '.$results['sent']);

        if ($results['failed'] > 0) {
            $this->error('Failed: '.$results['failed']);
        }

        return Command::SUCCESS;
    }
}
