<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\ImportLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Kigkonsult\Icalcreator\Vcalendar;

class IcsImportService
{
    protected User $user;

    protected Calendar $targetCalendar;

    protected array $errors = [];

    protected int $successCount = 0;

    protected int $failCount = 0;

    public function __construct(User $user, Calendar $targetCalendar)
    {
        $this->user = $user;
        $this->targetCalendar = $targetCalendar;
    }

    /**
     * Import appointments from an ICS file
     *
     * @param  string  $filePath  Path to the ICS file
     * @param  string  $filename  Original filename
     */
    public function import(string $filePath, string $filename): ImportLog
    {
        $importLog = ImportLog::create([
            'user_id' => $this->user->id,
            'filename' => $filename,
            'import_type' => 'ics',
            'status' => 'processing',
            'records_imported' => 0,
            'records_failed' => 0,
            'error_log' => [],
        ]);

        try {
            $calendar = Vcalendar::factory([
                Vcalendar::UNIQUE_ID => 'life-planner-app',
            ]);

            // Parse the ICS file - read content and split into lines
            $content = file_get_contents($filePath);
            $lines = explode(PHP_EOL, $content);
            $calendar->parse($lines);

            // Process each event in the calendar
            while ($vevent = $calendar->getComponent(Vcalendar::VEVENT)) {
                try {
                    $this->processEvent($vevent);
                    $this->successCount++;
                } catch (\Exception $e) {
                    $this->failCount++;
                    $this->errors[] = [
                        'event' => $vevent->getSummary() ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                    Log::error('ICS Import Error', [
                        'event' => $vevent->getSummary() ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update import log
            $importLog->update([
                'status' => 'completed',
                'records_imported' => $this->successCount,
                'records_failed' => $this->failCount,
                'error_log' => $this->errors,
            ]);

        } catch (\Exception $e) {
            $importLog->update([
                'status' => 'failed',
                'records_imported' => $this->successCount,
                'records_failed' => $this->failCount,
                'error_log' => array_merge($this->errors, [
                    ['error' => 'Critical error: '.$e->getMessage()],
                ]),
            ]);

            Log::error('ICS Import Critical Error', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
        }

        return $importLog;
    }

    /**
     * Process a single VEVENT component
     *
     * @param  \Kigkonsult\Icalcreator\Vevent  $vevent
     */
    protected function processEvent($vevent): void
    {
        $summary = $vevent->getSummary();
        $description = $vevent->getDescription();
        $location = $vevent->getLocation();

        // Get start and end dates
        $dtstart = $vevent->getDtstart();
        $dtend = $vevent->getDtend();

        if (! $dtstart) {
            throw new \Exception('Event must have a start date');
        }

        $startDateTime = $this->convertToCarbon($dtstart);
        $endDateTime = $dtend ? $this->convertToCarbon($dtend) : $startDateTime->copy()->addHour();

        // Check if it's an all-day event
        $isAllDay = $this->isAllDayEvent($vevent);

        // Get recurrence rule if any
        $rrule = $vevent->getRrule();
        $recurrenceRule = $rrule ? $this->parseRecurrenceRule($rrule) : null;

        // Create the appointment
        Appointment::create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->targetCalendar->id,
            'title' => $summary ?? 'Untitled Event',
            'description' => $description,
            'location' => $location,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'is_all_day' => $isAllDay,
            'color' => $this->targetCalendar->color,
            'recurrence_rule' => $recurrenceRule,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Convert ICS datetime to Carbon instance
     *
     * @param  mixed  $dt
     */
    protected function convertToCarbon($dt): Carbon
    {
        if (is_array($dt)) {
            $dt = $dt[1] ?? $dt[0];
        }

        if ($dt instanceof \DateTime) {
            return Carbon::instance($dt);
        }

        return Carbon::parse($dt);
    }

    /**
     * Check if event is all-day
     *
     * @param  \Kigkonsult\Icalcreator\Vevent  $vevent
     */
    protected function isAllDayEvent($vevent): bool
    {
        // Get dtstart with parameters
        $dtstartPc = $vevent->getDtstart(true);

        if (! $dtstartPc) {
            return false;
        }

        // Check if params contain VALUE=DATE
        if (isset($dtstartPc->params['VALUE'])) {
            $value = $dtstartPc->params['VALUE'];

            // Handle if it's an array
            if (is_array($value)) {
                return in_array('DATE', $value);
            }

            // Handle if it's a string
            return $value === 'DATE';
        }

        return false;
    }

    /**
     * Parse recurrence rule from ICS format to our format
     *
     * @param  mixed  $rrule
     */
    protected function parseRecurrenceRule($rrule): ?array
    {
        if (is_array($rrule)) {
            $rrule = $rrule[1] ?? $rrule[0];
        }

        if (empty($rrule)) {
            return null;
        }

        $recurrence = [
            'freq' => null,
            'interval' => 1,
            'count' => null,
            'until' => null,
            'byDay' => [],
            'byMonthDay' => null,
        ];

        if (isset($rrule['FREQ'])) {
            $recurrence['freq'] = strtolower($rrule['FREQ']);
        }

        if (isset($rrule['INTERVAL'])) {
            $recurrence['interval'] = (int) $rrule['INTERVAL'];
        }

        if (isset($rrule['COUNT'])) {
            $recurrence['count'] = (int) $rrule['COUNT'];
        }

        if (isset($rrule['UNTIL'])) {
            $recurrence['until'] = $this->convertToCarbon($rrule['UNTIL'])->toDateString();
        }

        if (isset($rrule['BYDAY'])) {
            $recurrence['byDay'] = is_array($rrule['BYDAY'])
                ? $rrule['BYDAY']
                : explode(',', $rrule['BYDAY']);
        }

        if (isset($rrule['BYMONTHDAY'])) {
            $recurrence['byMonthDay'] = (int) $rrule['BYMONTHDAY'];
        }

        return $recurrence;
    }

    /**
     * Get import statistics
     */
    public function getStatistics(): array
    {
        return [
            'success' => $this->successCount,
            'failed' => $this->failCount,
            'errors' => $this->errors,
        ];
    }
}
