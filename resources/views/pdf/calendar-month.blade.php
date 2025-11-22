<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $calendar->name }} - {{ $month->format('F Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #666;
        }
        .calendar-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .calendar-grid th {
            background-color: {{ $calendar->color }};
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }
        .calendar-grid td {
            border: 1px solid #ddd;
            padding: 5px;
            vertical-align: top;
            height: 80px;
            width: 14.28%;
        }
        .day-number {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .other-month {
            background-color: #f5f5f5;
            color: #999;
        }
        .appointment {
            background-color: #e3f2fd;
            border-left: 3px solid {{ $calendar->color }};
            padding: 2px 4px;
            margin: 2px 0;
            font-size: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .appointment-time {
            font-weight: bold;
            color: #333;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $calendar->name }}</h1>
        <h2>{{ $month->format('F Y') }}</h2>
    </div>

    <table class="calendar-grid">
        <thead>
            <tr>
                <th>Sunday</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Saturday</th>
            </tr>
        </thead>
        <tbody>
            @foreach($weeks as $week)
                <tr>
                    @foreach($week as $day)
                        <td class="{{ !$day['isCurrentMonth'] ? 'other-month' : '' }}">
                            <div class="day-number">{{ $day['date']->format('j') }}</div>
                            @php
                                $dateKey = $day['date']->format('Y-m-d');
                                $dayAppointments = $appointmentsByDay->get($dateKey, collect());
                            @endphp
                            @foreach($dayAppointments->take(3) as $appointment)
                                <div class="appointment">
                                    @if(!$appointment->is_all_day)
                                        <span class="appointment-time">{{ $appointment->start_datetime->format('g:i A') }}</span>
                                    @endif
                                    {{ Str::limit($appointment->title, 20) }}
                                </div>
                            @endforeach
                            @if($dayAppointments->count() > 3)
                                <div class="appointment">+{{ $dayAppointments->count() - 3 }} more</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('F j, Y g:i A') }} | Life Planner App
    </div>
</body>
</html>
