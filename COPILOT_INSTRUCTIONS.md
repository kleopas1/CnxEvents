# GitHub Copilot Instructions for CnxEvents Module

## Project Context
CnxEvents is a FreeScout module for hotel event and banquet management. It provides scheduling, venue management, and event handling with a request/confirmation workflow.

## Architecture Overview

### Technology Stack
- **Framework**: Laravel 5.5+ (FreeScout's version)
- **Frontend**: Bootstrap 3 (strict requirement for FreeScout compatibility)
- **JavaScript**: jQuery + vanilla JS (CSP-compliant)
- **Database**: MySQL with Eloquent ORM
- **PDF Generation**: Dompdf for BEO exports

### Key Constraints
1. **Bootstrap 3 Only**: Never suggest Bootstrap 4/5 classes. Use Bootstrap 3 conventions.
2. **CSP Compliance**: All inline scripts must use `\Helper::cspNonceAttr()`
3. **FreeScout Integration**: Extend FreeScout layouts, use Eventy hooks for navigation
4. **No FullCalendar**: Custom calendar implementation with month/week/day views

## Database Schema

### Tables
- `cnx_venues`: id, name, description, capacity, features (json), color (varchar(7)), timestamps
- `cnx_events`: id, title, description, start_datetime, end_datetime, setup_datetime, venue_release_datetime, all_day (boolean), venue_id, status (enum: request/confirmed), user_id, client_name, client_email, client_phone, client_company, timestamps
- `cnx_departments`: id, name, description, timestamps
- `cnx_custom_fields`: id, name, type (enum: text/number/select/multiselect/date/textarea), options (json), is_required (boolean), timestamps
- `cnx_custom_field_department`: custom_field_id, department_id
- `cnx_event_custom_field_values`: id, event_id, custom_field_id, value (text), timestamps

### Key Relationships
- Event belongsTo Venue
- Event belongsTo User (FreeScout user)
- Event hasMany EventCustomFieldValue
- CustomField belongsToMany Department
- Venue color stored as hex (#RRGGBB)

## Code Patterns

### Controllers

#### EventController Patterns
```php
// All-day event handling
if ($request->filled('all_day') && $request->all_day) {
    $data['start_datetime'] = ($request->start_date ?: $request->start_datetime) . ' 00:01:00';
    $data['end_datetime'] = ($request->end_date ?: $request->end_datetime) . ' 23:59:00';
    $data['setup_datetime'] = null;
    $data['venue_release_datetime'] = null;
}

// Checkbox conversion
$request->merge(['all_day' => $request->has('all_day')]);

// JSON response for modals
if (request()->ajax() || request()->wantsJson()) {
    $eventData['start_datetime'] = $event->start_datetime->format('Y-m-d\TH:i');
    return response()->json($eventData);
}

// Validation with dual inputs
$request->validate([
    'start_datetime' => 'required_without:start_date|nullable|date',
    'start_date' => 'required_without:start_datetime|nullable|date',
]);
```

#### Custom Field Storage
```php
// Store custom field values
foreach ($customFields as $field) {
    $key = 'custom_field_' . $field->id;
    if ($request->has($key)) {
        $value = $request->input($key);
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        EventCustomFieldValue::create([
            'event_id' => $event->id,
            'custom_field_id' => $field->id,
            'value' => $value
        ]);
    }
}
```

### Views

#### Calendar Styling
```css
/* Sticky headers */
.week-sticky, .day-sticky {
    position: sticky;
    top: 0;
    z-index: 300;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,.15);
}

/* Table structure */
.week-row, .day-row {
    display: table;
    width: 100%;
    table-layout: fixed;
}

/* Venue colors with gradients */
background: linear-gradient(to bottom, 
    #venueColor40 0%, 
    #venueColor40 20%, 
    #venueColor 20%, 
    #venueColor 80%, 
    #venueColor40 80%);

/* Request stripes */
background: repeating-linear-gradient(45deg, 
    transparent, 
    transparent 10px, 
    rgba(255,255,255,0.3) 10px, 
    rgba(255,255,255,0.3) 20px), 
    #venueColor;
```

#### Modal Structure
```blade
{{-- Reusable modal in modals/event-modal.blade.php --}}
<div class="modal fade" id="eventModal">
    <div class="modal-dialog modal-lg">
        <form id="eventForm" method="POST">
            @csrf
            <input type="hidden" name="_method" value="POST">
            
            {{-- Dual inputs for all-day --}}
            <input type="datetime-local" name="start_datetime" class="datetime-field">
            <input type="date" name="start_date" class="date-field" style="display: none;">
        </form>
    </div>
</div>
```

#### CSP-Compliant JavaScript
```blade
<script {!! \Helper::cspNonceAttr() !!}>
    function editEvent(id) {
        fetch(`/cnxevents/events/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Populate form
            $('#eventModal').modal('show');
        });
    }
    
    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.view-event-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                editEvent(this.dataset.eventId);
            });
        });
    });
</script>
```

### Model Patterns

#### Event Model
```php
// Casts
protected $casts = [
    'start_datetime' => 'datetime',
    'end_datetime' => 'datetime',
    'setup_datetime' => 'datetime',
    'venue_release_datetime' => 'datetime',
    'all_day' => 'boolean',
];

// Validation in boot
protected static function boot() {
    parent::boot();
    
    static::saving(function ($event) {
        if (!$event->all_day) {
            // Validate time sequence
        }
    });
}

// Relationships
public function customFieldValues() {
    return $this->hasMany(EventCustomFieldValue::class);
}
```

## Common Tasks

### Adding a New Custom Field Type
1. Update `type` enum in migration
2. Add case in CustomFieldController validation
3. Add input type in settings view
4. Handle in EventController storage logic
5. Update event modal form rendering

### Calendar Event Rendering
- All-day events: Display in `.week-allday-cell` / `.day-allday-cell` strip
- Timed events: Position with `top` and `height` based on minutes from 6 AM
- Setup/release: Use `linear-gradient` with lighter venue color (append `40` for alpha)
- Requests: Layer `repeating-linear-gradient` stripes over venue color
- Overlap handling: Calculate column widths and left positions

### Form Validation Flow
1. Convert checkbox to boolean: `$request->merge(['all_day' => $request->has('all_day')])`
2. Validate with `required_without` for dual inputs
3. Check all-day flag and use appropriate date/datetime field
4. Append times for all-day: ` 00:01:00` and ` 23:59:00`
5. For normal events: Convert `T` to space and append `:00`

## Testing Checklist
- All-day events save with correct times (00:01 - 23:59)
- Non-all-day events respect time sequence validation
- Venue colors display correctly in all calendar views
- Request events show diagonal stripes
- Setup/release times show lighter gradient sections
- Calendar columns align in week view
- Sticky headers work on scroll
- Modals load data via AJAX
- Custom field values save to separate table
- Required custom fields enforce validation

## Debugging Tips
- Check `storage/logs/laravel-YYYY-MM-DD.log` for errors
- Clear view cache: `php artisan view:clear`
- Rebuild module: `php artisan freescout:module-build`
- Copy assets: `Copy-Item events.js to public/modules/cnxevents/js/`
- Check browser console for JavaScript errors
- Verify CSP nonce on inline scripts
- Test with `dd($request->all())` to see form data

## FreeScout Integration Points
- Navigation: Use Eventy hook `header.append_dropdown_item`
- Layouts: Extend `cnxevents::layouts.app` which extends FreeScout base
- Permissions: Check admin via FreeScout's permission system
- Users: Reference FreeScout's `\App\User` model
- Middleware: Use FreeScout's auth middleware

## Code Style
- Follow PSR-2 for PHP
- Use Blade directives (`@if`, `@foreach`) over PHP tags
- Prefix all table names with `cnx_`
- Use snake_case for database columns
- Use camelCase for JavaScript variables
- Use kebab-case for CSS classes
- Comment complex logic, especially calendar math
- Keep controllers thin, move logic to models/services when appropriate
