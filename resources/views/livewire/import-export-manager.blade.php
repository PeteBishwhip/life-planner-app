<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Import & Export</h2>
            <p class="text-gray-600 mt-2">Import calendars from external sources or export your calendars to various formats</p>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Import Section -->
            <div class="border border-gray-200 rounded-lg p-6 hover:border-blue-500 transition">
                <div class="flex items-center mb-4">
                    <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-800">Import Calendar</h3>
                </div>
                <p class="text-gray-600 mb-4">Import appointments from ICS files, Google Calendar, or Outlook</p>
                <button
                    wire:click="openImportModal"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition"
                >
                    Import Calendar
                </button>
            </div>

            <!-- Export Section -->
            <div class="border border-gray-200 rounded-lg p-6 hover:border-green-500 transition">
                <div class="flex items-center mb-4">
                    <svg class="w-8 h-8 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-800">Export Calendar</h3>
                </div>
                <p class="text-gray-600 mb-4">Export your calendars to ICS, PDF, or CSV format</p>
                <button
                    wire:click="openExportModal"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition"
                >
                    Export Calendar
                </button>
            </div>
        </div>

        <!-- Import History -->
        <div class="p-6 border-t border-gray-200">
            <button
                wire:click="toggleImportHistory"
                class="flex items-center justify-between w-full text-left text-gray-700 hover:text-gray-900"
            >
                <span class="font-semibold">Import History</span>
                <svg
                    class="w-5 h-5 transform transition-transform {{ $showImportHistory ? 'rotate-180' : '' }}"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @if($showImportHistory)
                <div class="mt-4 space-y-2">
                    @forelse($importLogs as $log)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-800">{{ $log->filename }}</span>
                                    <span class="ml-2 px-2 py-1 text-xs rounded
                                        {{ $log->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $log->status === 'completed_with_errors' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $log->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $log->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                    ">
                                        {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Imported: {{ $log->records_imported }} | Failed: {{ $log->records_failed }}
                                    | {{ $log->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No import history found</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    <!-- Import Modal -->
    @if($showImportModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-800">Import Calendar</h3>
                        <button wire:click="closeImportModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    @if($importResult)
                        <div class="mb-4 p-4 rounded-lg {{ $importResult['success'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                            <p class="{{ $importResult['success'] ? 'text-green-800' : 'text-red-800' }}">
                                {{ $importResult['message'] }}
                            </p>
                        </div>
                    @endif

                    <form wire:submit.prevent="import">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Import Type</label>
                                <select wire:model="importType" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="ics">ICS File (.ics)</option>
                                    <option value="csv" disabled>CSV File (Coming Soon)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Target Calendar</label>
                                <select wire:model="selectedCalendarForImport" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @foreach($calendars as $calendar)
                                        <option value="{{ $calendar->id }}">{{ $calendar->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
                                <input
                                    type="file"
                                    wire:model="importFile"
                                    accept=".ics,.csv"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                @error('importFile')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            @if($importFile)
                                <div class="text-sm text-gray-600">
                                    Selected: {{ $importFile->getClientOriginalName() }}
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button
                                type="submit"
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="import">Import</span>
                                <span wire:loading wire:target="import">Importing...</span>
                            </button>
                            <button
                                type="button"
                                wire:click="closeImportModal"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Export Modal -->
    @if($showExportModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-800">Export Calendar</h3>
                        <button wire:click="closeExportModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Calendar(s)</label>
                            <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-2">
                                @foreach($calendars as $calendar)
                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded">
                                        <input
                                            type="checkbox"
                                            wire:model="selectedCalendarsForExport"
                                            value="{{ $calendar->id }}"
                                            class="mr-2 rounded border-gray-300"
                                        >
                                        <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ $calendar->color }}"></span>
                                        <span class="text-sm">{{ $calendar->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range (Optional)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input
                                    type="date"
                                    wire:model="startDate"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Start Date"
                                >
                                <input
                                    type="date"
                                    wire:model="endDate"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="End Date"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    type="button"
                                    wire:click="exportIcs"
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition text-sm"
                                >
                                    ICS
                                </button>
                                <button
                                    type="button"
                                    wire:click="exportCsv"
                                    class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition text-sm"
                                >
                                    CSV
                                </button>
                                <button
                                    type="button"
                                    wire:click="exportPdf"
                                    class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg transition text-sm"
                                >
                                    PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button
                            type="button"
                            wire:click="closeExportModal"
                            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
