<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Combined Calendars - Appointment List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header .subtitle {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .calendar-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .calendar-header {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .date-group {
            margin-bottom: 15px;
        }
        .date-header {
            background-color: #e0e0e0;
            color: #333;
            padding: 6px 12px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .appointment {
            border-left: 4px solid #4CAF50;
            padding: 8px 12px;
            margin-bottom: 8px;
            background-color: #f9f9f9;
        }
        .appointment-title {
            font-weight: bold;
            font-size: 12px;
            color: #333;
            margin-bottom: 3px;
        }
        .appointment-time {
            color: #555;
            font-size: 10px;
        }
        .appointment-location {
            color: #777;
            font-style: italic;
            font-size: 10px;
        }
        .no-appointments {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Combined Calendars</h1>
        <div class="subtitle">{{ $startDate->format('F j, Y') }} - {{ $endDate->format('F j, Y') }}</div>
    </div>

    @foreach($calendars as $calendarData)
        <div class="calendar-section">
            <div class="calendar-header">
                {{ $calendarData['calendar']->name }}
            </div>

            @if($calendarData['appointments']->isEmpty())
                <div class="no-appointments">
                    No appointments in this calendar for the selected date range.
                </div>
            @else
                @php
                    $groupedByDate = $calendarData['appointments']->groupBy(function($appointment) {
                        return $appointment->start_datetime->format('Y-m-d');
                    });
                @endphp

                @foreach($groupedByDate as $date => $dateAppointments)
                    <div class="date-group">
                        <div class="date-header">
                            {{ Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                        </div>

                        @foreach($dateAppointments as $appointment)
                            <div class="appointment">
                                <div class="appointment-title">{{ $appointment->title }}</div>
                                <div class="appointment-time">
                                    @if($appointment->is_all_day)
                                        All Day Event
                                    @else
                                        {{ $appointment->start_datetime->format('g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}
                                    @endif
                                </div>
                                @if($appointment->location)
                                    <div class="appointment-location">
                                        📍 {{ $appointment->location }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach

    <div class="footer">
        Generated on {{ now()->format('F j, Y g:i A') }} | Life Planner App
    </div>
</body>
</html>
