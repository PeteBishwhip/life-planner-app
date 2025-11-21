<?php

namespace App\Livewire;

use App\Models\Calendar;
use App\Models\ImportLog;
use App\Services\CsvExportService;
use App\Services\IcsExportService;
use App\Services\IcsImportService;
use App\Services\PdfExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportExportManager extends Component
{
    use WithFileUploads;

    // Import properties
    public $importFile;
    public $selectedCalendarForImport;
    public $importType = 'ics';
    public $showImportModal = false;
    public $importResult = null;

    // Export properties
    public $selectedCalendarForExport;
    public $selectedCalendarsForExport = [];
    public $exportFormat = 'ics';
    public $exportView = 'list';
    public $startDate;
    public $endDate;
    public $showExportModal = false;

    // Import logs
    public $importLogs = [];
    public $showImportHistory = false;

    protected $rules = [
        'importFile' => 'required|file|max:10240', // 10MB max
        'selectedCalendarForImport' => 'required|exists:calendars,id',
        'importType' => 'required|in:ics,csv',
    ];

    public function mount()
    {
        $this->loadImportLogs();
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->addMonths(2)->format('Y-m-d');

        // Set default calendar for import if available
        $firstCalendar = Auth::user()->calendars()->first();
        if ($firstCalendar) {
            $this->selectedCalendarForImport = $firstCalendar->id;
            $this->selectedCalendarForExport = $firstCalendar->id;
        }
    }

    public function openImportModal()
    {
        $this->showImportModal = true;
        $this->importResult = null;
        $this->importFile = null;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'importResult']);
    }

    public function openExportModal()
    {
        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
    }

    public function toggleImportHistory()
    {
        $this->showImportHistory = !$this->showImportHistory;
        if ($this->showImportHistory) {
            $this->loadImportLogs();
        }
    }

    public function import()
    {
        $this->validate();

        try {
            $calendar = Calendar::findOrFail($this->selectedCalendarForImport);

            // Store the uploaded file temporarily
            $path = $this->importFile->store('temp');
            $fullPath = Storage::path($path);
            $filename = $this->importFile->getClientOriginalName();

            // Import based on type
            if ($this->importType === 'ics') {
                $importService = new IcsImportService(Auth::user(), $calendar);
                $importLog = $importService->import($fullPath, $filename);
            } else {
                // CSV import would be implemented here
                throw new \Exception('CSV import not yet implemented');
            }

            // Clean up temporary file
            Storage::delete($path);

            // Set result
            $this->importResult = [
                'success' => $importLog->status !== 'failed',
                'message' => $this->getImportMessage($importLog),
                'log' => $importLog,
            ];

            // Reload import logs
            $this->loadImportLogs();

            // Emit event to refresh calendar
            $this->dispatch('appointments-updated');

        } catch (\Exception $e) {
            $this->importResult = [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'log' => null,
            ];
        }
    }

    public function exportIcs()
    {
        try {
            $exportService = new IcsExportService();

            if (!empty($this->selectedCalendarsForExport) && count($this->selectedCalendarsForExport) > 1) {
                // Export multiple calendars
                $calendars = Calendar::whereIn('id', $this->selectedCalendarsForExport)->get();
                $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
                $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;

                $content = $exportService->exportMultipleCalendars($calendars->all(), $startDate, $endDate);
                $filename = 'calendars-combined-' . Carbon::now()->format('Y-m-d') . '.ics';
            } else {
                // Export single calendar
                $calendarId = !empty($this->selectedCalendarsForExport)
                    ? $this->selectedCalendarsForExport[0]
                    : $this->selectedCalendarForExport;

                $calendar = Calendar::findOrFail($calendarId);
                $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
                $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;

                $content = $exportService->exportCalendar($calendar, $startDate, $endDate);
                $filename = $exportService->generateFilename($calendar);
            }

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, ['Content-Type' => $exportService->getMimeType()]);

        } catch (\Exception $e) {
            session()->flash('error', 'Export failed: ' . $e->getMessage());
            return null;
        }
    }

    public function exportCsv()
    {
        try {
            $exportService = new CsvExportService();

            if (!empty($this->selectedCalendarsForExport) && count($this->selectedCalendarsForExport) > 1) {
                // Export multiple calendars
                $calendars = Calendar::whereIn('id', $this->selectedCalendarsForExport)->get();
                $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
                $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;

                $content = $exportService->exportMultipleCalendars($calendars->all(), $startDate, $endDate);
                $filename = $exportService->generateCombinedFilename();
            } else {
                // Export single calendar
                $calendarId = !empty($this->selectedCalendarsForExport)
                    ? $this->selectedCalendarsForExport[0]
                    : $this->selectedCalendarForExport;

                $calendar = Calendar::findOrFail($calendarId);
                $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
                $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;

                $content = $exportService->exportCalendar($calendar, $startDate, $endDate);
                $filename = $exportService->generateFilename($calendar);
            }

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, ['Content-Type' => $exportService->getMimeType()]);

        } catch (\Exception $e) {
            session()->flash('error', 'Export failed: ' . $e->getMessage());
            return null;
        }
    }

    public function exportPdf()
    {
        try {
            $exportService = new PdfExportService();

            if (!empty($this->selectedCalendarsForExport) && count($this->selectedCalendarsForExport) > 1) {
                // Export multiple calendars
                $calendars = Calendar::whereIn('id', $this->selectedCalendarsForExport)->get();
                $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
                $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;

                $pdf = $exportService->exportMultipleCalendars($calendars->all(), $startDate, $endDate);
                $filename = $exportService->generateCombinedFilename();
            } else {
                // Export single calendar
                $calendarId = !empty($this->selectedCalendarsForExport)
                    ? $this->selectedCalendarsForExport[0]
                    : $this->selectedCalendarForExport;

                $calendar = Calendar::findOrFail($calendarId);
                $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
                $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;

                if ($this->exportView === 'month') {
                    $month = $startDate ?? Carbon::now();
                    $pdf = $exportService->exportMonthView($calendar, $month);
                    $filename = $exportService->generateFilename($calendar, 'month');
                } else {
                    $pdf = $exportService->exportListView($calendar, $startDate, $endDate);
                    $filename = $exportService->generateFilename($calendar, 'list');
                }
            }

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, ['Content-Type' => 'application/pdf']);

        } catch (\Exception $e) {
            session()->flash('error', 'Export failed: ' . $e->getMessage());
            return null;
        }
    }

    protected function loadImportLogs()
    {
        $this->importLogs = Auth::user()
            ->importLogs()
            ->latest()
            ->take(10)
            ->get();
    }

    protected function getImportMessage(ImportLog $log): string
    {
        if ($log->status === 'failed') {
            return 'Import failed. Please check the error log for details.';
        }

        if ($log->status === 'completed_with_errors') {
            return "Import completed with {$log->records_imported} successful and {$log->records_failed} failed records.";
        }

        return "Successfully imported {$log->records_imported} appointments!";
    }

    public function render()
    {
        $calendars = Auth::user()->calendars()->get();

        return view('livewire.import-export-manager', [
            'calendars' => $calendars,
        ]);
    }
}
