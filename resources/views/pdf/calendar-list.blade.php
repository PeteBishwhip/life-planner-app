<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $calendar->name }} - Appointment List</title>
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
            border-bottom: 2px solid {{ $calendar->color }};
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
        .date-group {
            margin-bottom: 20px;
        }
        .date-header {
            background-color: {{ $calendar->color }};
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .appointment {
            border-left: 4px solid {{ $calendar->color }};
            padding: 10px 15px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }
        .appointment-title {
            font-weight: bold;
            font-size: 13px;
            color: #333;
            margin-bottom: 5px;
        }
        .appointment-time {
            color: #555;
            margin-bottom: 3px;
        }
        .appointment-location {
            color: #777;
            font-style: italic;
        }
        .appointment-description {
            color: #666;
            margin-top: 5px;
            font-size: 10px;
        }
        .no-appointments {
            text-align: center;
            padding: 40px;
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
        <h1>{{ $calendar->name }}</h1>
        <div class="subtitle">{{ $startDate->format('F j, Y') }} - {{ $endDate->format('F j, Y') }}</div>
    </div>

    @if($appointments->isEmpty())
        <div class="no-appointments">
            No appointments found in this date range.
        </div>
    @else
        @php
            $groupedByDate = $appointments->groupBy(function($appointment) {
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
                        @if($appointment->description)
                            <div class="appointment-description">
                                {{ Str::limit($appointment->description, 200) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

    <div class="footer">
        Generated on {{ now()->format('F j, Y g:i A') }} | Life Planner App
    </div>
</body>
</html>
