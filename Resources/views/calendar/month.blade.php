<div class="calendar-grid">
    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
        <div class="calendar-day-header">{{ $day }}</div>
    @endforeach

    @foreach($calendarDays as $day)
        <div class="calendar-day {{ $day['isOtherMonth'] ? 'other-month' : '' }} {{ $day['isToday'] ? 'today' : '' }}">
            <div class="calendar-day-number">{{ $day['date']->format('j') }}</div>
            
            @foreach($day['events'] as $event)
                @php
                    $venueColor = $event->venue ? $event->venue->color : '#3c8dbc';
                    $statusClass = strtolower($event->status) === 'request' ? 'event-request' : '';
                @endphp
                <a href="#" 
                   class="calendar-event event-venue-color {{ $statusClass }} view-event-btn"
                   data-event-id="{{ $event->id }}"
                   style="background-color: {{ $venueColor }};"
                   title="{{ $event->title }} - {{ $event->start_datetime->format('H:i') }} - {{ $event->venue ? $event->venue->name : 'No Venue' }}">
                    {{ $event->start_datetime->format('H:i') }} - {{ $event->title }}
                </a>
            @endforeach
        </div>
    @endforeach
</div>
