# CnxEvents Module Implementation Plan

## Overview
CnxEvents is a hotel event and banquet management module for FreeScout. It provides built-in scheduling, venue management, and event handling with request/confirmation workflow. Key features include calendar views, paginated tables with filters, modal-based CRUD, settings for venues and custom fields, and analytics with KPIs.

## Models
- **Venue**: Represents physical venues (e.g., rooms, halls). Fields: name, description, capacity, features (JSON), color (VARCHAR(7) for hex color), custom_fields (JSON).
- **Event**: Represents events/banquets. Fields: title, description, start_datetime, end_datetime, setup_datetime, venue_release_datetime, all_day (boolean), venue_id, status (request/confirmed), user_id (requester), client_name, client_email, client_phone, client_company, timestamps. Custom fields stored in separate cnx_event_custom_field_values table. (Note: Events are not assigned to departments; custom fields are.)
- **Department**: Represents departments (e.g., Catering, AV). Fields: name, description, timestamps. Used for assigning custom fields and BEO generation.
- **CustomField**: Defines custom fields for events. Fields: name, type (text, number, select, multiselect, date, textarea), options (JSON for select/multiselect), is_required (boolean), timestamps. Assigned to multiple departments via pivot table. Values stored in EventCustomFieldValue model.
- **EventCustomFieldValue**: Stores custom field values for events. Fields: event_id, custom_field_id, value, timestamps.

## Database Migrations
- Create `departments` table: id, name, description, timestamps.
- Create `custom_fields` table: id, name, type (enum: text, number, select, multiselect, date, textarea), options (json), is_required (boolean, default false), timestamps.
- Create `custom_field_department` pivot table: custom_field_id, department_id.
- Create `venues` table: id, name, description, capacity, features (json), color (varchar(7)), custom_fields (json), timestamps.
- Create `events` table: id, title, description, start_datetime, end_datetime, setup_datetime, venue_release_datetime, all_day (boolean), venue_id (foreign), status (enum: request, confirmed), user_id (foreign), client_name, client_email, client_phone, client_company, timestamps.
- Create `event_custom_field_values` table: id, event_id (foreign), custom_field_id (foreign), value (text), timestamps.
- Add indexes on status, venue_id, user_id, department_id for performance.

## Controllers
- **VenueController**: CRUD for venues (index, create, store, edit, update, destroy). Includes color field management.
- **EventController**: CRUD for events (index, create, store, edit, update, destroy, show). Include confirm action to convert request to event. Load CustomField definitions for forms, enforce required fields validation, and store values in EventCustomFieldValue model. Supports all-day events with date-only inputs (automatically sets 00:01:00 and 23:59:00). Returns JSON for AJAX modal requests. Handles checkbox conversion for all_day field.
- **CalendarController**: Displays calendar views (month, week, day) with events and requests. Filters by status and venue. Provides customFields to views for modal support.
- **RequestController**: Similar to EventController but filtered for status='request'. Include confirm action.
- **DepartmentController**: CRUD for departments (index, create, store, edit, update, destroy).
- **CustomFieldController**: CRUD for custom fields (index, create, store, edit, update, destroy). Assign to multiple departments and set required status. Supports multiple field types (text, number, select, multiselect, date, textarea).
- **SettingsController**: Manage venues, venue features/fields, departments, and custom fields. Restrict access to admins using FreeScout's permission system.
- **AnalyticsController**: Generate reports and KPIs (e.g., total events, revenue, occupancy).
- **BeoController**: Generate and view Banquet Event Orders (BEO) for events, grouping custom fields by department.

## Views
- **Navigation**: Add dropdown in header with options: Calendar (confirmed, requests, both), Events, Requests, Settings, Analytics. Integrated into FreeScout's main navigation via Eventy hooks.
- **Calendar View**: Custom calendar with month/week/day views displaying events/requests with venue colors. Events show as boxes with gradient backgrounds for setup/release times. Request events display diagonal white stripes. All-day events shown in dedicated strip above hourly grid. Sticky headers for week/day views. Filter by status and venue. Modal-based event creation/editing. Uses CSP-compliant inline scripts.
- **Events Index**: Table view with pagination, filters (date range, venue, user, status). Modal for create/edit with dual datetime/date inputs for all-day toggle. Custom fields display in 2-column grid. Modern gradient header design. Validation error alerts.
- **Event Modal**: Reusable modal (modals/event-modal.blade.php) with modern styling, gradient header, organized sections (Event Details, Event Timing, Client Information, Additional Details). Dual input system for all-day events. JavaScript toggles between datetime-local and date inputs.
- **Requests Index**: Similar to Events but only status='request'. Add "Confirm" button to convert to event.
- **Settings**: Forms for adding/editing venues (with color picker), defining features/fields, custom fields per department (supports text, number, select, multiselect, date, textarea types).
- **Analytics**: Charts and tables for KPIs (e.g., events per month, venue utilization).

## Routes
- Define in `Http/routes.php`:
  - GET /cnxevents/venues -> VenueController@index
  - Resource routes for venues, events, requests
  - GET /cnxevents/settings -> SettingsController@index
  - GET /cnxevents/analytics -> AnalyticsController@index
  - POST /cnxevents/requests/{id}/confirm -> RequestController@confirm

## Settings
- **Departments Setup**: Add/edit departments (e.g., Catering, AV, Decor).
- **Venues Setup**: Add/edit venues with features (e.g., AV equipment) and custom fields (e.g., setup time).
- **Custom Fields Setup**: Define event custom fields (text, select, date) assigned to multiple departments. Specify options for select fields and mark as required. All fields must have at least one department attached for BEO generation.
- **BEO Templates**: Configure templates for Banquet Event Orders, pulling data from event custom fields grouped by department.

## Analytics
- **KPIs**: Total events, confirmed vs requests, revenue (if pricing added), venue occupancy rate, BEO generation count.
- **Reports**: Events by date, venue, department; BEO summaries; department-wise custom field usage.
- Use charting library (e.g., Chart.js) for visualizations.

## Dependencies
Add to `Modules/CnxEvents/composer.json`:
- "fullcalendar/fullcalendar": "^3.10" for calendar view
- "chartjs/chart.js": "^2.9" for analytics charts
- Any other packages as needed (e.g., for PDF BEO generation: "dompdf/dompdf")

## Implementation Steps
1. **Setup Module Structure** ✅ COMPLETED:
   - All required folders (Entities, Http/Controllers, Database/Migrations, Resources/views, etc.) are created.
   - Updated composer.json with required packages (FullCalendar, Chart.js, Dompdf for BEO PDFs).

2. **Create Migrations** ✅ COMPLETED:
   - Created migration files for departments, custom_fields, custom_field_department (pivot), venues, and events tables.
   - Defined table schemas with proper data types, foreign keys, and indexes. Included validation constraints in comments.

3. **Define Models** ✅ COMPLETED:
   - Created Department.php, CustomField.php, Venue.php, Event.php, and EventCustomFieldValue.php in Entities/.
   - Added relationships: CustomField belongsToMany Department; Event belongsTo Venue, Event belongsTo User; Event hasMany EventCustomFieldValue.
   - Added scopes for status filtering (scopeRequests, scopeConfirmed) on Event.
   - Added validation rules in Event model: setup_datetime < start_datetime < end_datetime < venue_release_datetime when not all_day (via boot() method).
   - Event model casts datetimes to Carbon instances.

4. **Build Controllers** ✅ COMPLETED:
   - Implemented DepartmentController with full CRUD.
   - Implemented CustomFieldController with CRUD, department assignment via sync, options handling for select/multiselect types.
   - Implemented VenueController with CRUD, JSON support, and color field management.
   - Implemented EventController with CRUD, pagination, filters, confirm action, dynamic CustomField loading/validation, EventCustomFieldValue storage. Handles all-day events with automatic time appending (00:01:00 and 23:59:00). Returns JSON for AJAX modal requests with proper datetime formatting. Converts checkbox values to boolean. Validates with required_without for date/datetime fields.
   - Implemented CalendarController with month/week/day views, status/venue filtering, and customFields loading for modals.
   - Implemented RequestController with filtered index and confirm action.
   - Implemented SettingsController with admin-only middleware.
   - Implemented AnalyticsController with KPI queries and chart data.
   - Implemented BeoController for viewing and PDF download of BEOs, grouping custom fields by department.

5. **Create Views** ✅ COMPLETED:
   - All views extend FreeScout's base layout for consistent integration.
   - Created index.blade.php for events with pagination/filters and Bootstrap modals for forms. Includes dual datetime/date inputs with JavaScript toggle for all-day events. Custom fields in 2-column grid. Modern gradient header. Validation error alerts.
   - Created modals/event-modal.blade.php as reusable modal component with modern design, gradient header, organized sections, and dual input system.
   - Created calendar.blade.php with custom month/week/day views. Events display with venue colors, setup/release gradient backgrounds, diagonal stripes for requests, and all-day event strips. Sticky headers for week/day views. CSP-compliant JavaScript. Filter dropdowns for status/venue.
   - Created calendar/month.blade.php, week.blade.php, day.blade.php with responsive layouts, venue color styling, overlap handling for concurrent events, and modal integration.
   - Created settings.blade.php with tabs for departments, custom fields (with type selection), and venues (with color picker).
   - Created analytics.blade.php with charts using Chart.js.
   - Created beo.blade.php for displaying/printing BEOs.
   - Updated layouts/app.blade.php to extend FreeScout's layout and use sidebar navigation.
   - All JavaScript uses \Helper::cspNonceAttr() for CSP compliance.

6. **Add Routes** ✅ COMPLETED:
   - Registered comprehensive routes in routes.php: resources for departments, venues, events; custom routes for confirm, beo, analytics.
   - Grouped under 'cnxevents' prefix with proper middleware and admin restrictions.

7. **Implement Settings** ✅ COMPLETED:
   - ✅ Settings view with tabs for departments, custom fields, and venues
   - ✅ Tables display existing data with edit/delete actions
   - ✅ Bootstrap modals for CRUD operations
   - ✅ JavaScript for AJAX form handling
   - ✅ CRUD operations handled by separate controllers (DepartmentController, CustomFieldController, VenueController)

8. **Develop BEO Generation** ✅ COMPLETED:
   - ✅ BeoController with show() and pdf() methods
   - ✅ BEO view displays event details grouped by department
   - ✅ PDF generation using Dompdf with proper department grouping
   - ✅ Custom fields properly grouped by department for BEO display

9. **Develop Analytics** ✅ COMPLETED:
   - ✅ Analytics view with Chart.js integration
   - ✅ Basic KPI calculations in AnalyticsController index()
   - ✅ AJAX data endpoints (statusData, venueData, monthlyData) implemented for chart rendering

10. **Add Navigation** ✅ COMPLETED:
    - Updated FreeScout's header to include CnxEvents menu item using Eventy hooks, ensuring seamless integration with existing navigation.
    - Added menu selection logic for proper highlighting.
    - Ensure menu items link to module routes and respect permissions.

11. **Testing** 🔄 READY TO START:
    - Write unit tests for models and relationships
    - Write feature tests for controllers and CRUD operations
    - Test AJAX modals, filters, and confirm workflow
    - Validate form validation and error handling
    - Test UI responsiveness and FreeScout integration

12. **Refinement** ✅ COMPLETED:
    - ✅ Added comprehensive validation rules for forms, including time constraints, required field checking, and all-day event handling
    - ✅ Implemented checkbox to boolean conversion for all_day field
    - ✅ Added required_without validation for dual datetime/date inputs
    - ✅ Created dual input system with JavaScript toggle for all-day events
    - ✅ Implemented venue color system with color picker and calendar integration
    - ✅ Added setup/release time visualization with gradient backgrounds
    - ✅ Implemented diagonal stripe pattern for request events
    - ✅ Added all-day event strip above hourly grid in week/day views
    - ✅ Implemented sticky headers for week/day calendar views with proper table display structure
    - ✅ Fixed column alignment in week view with explicit width constraints
    - ✅ Moved all CSS to centralized calendar.blade.php stylesheet
    - ✅ Added validation error alerts in events index view
    - ✅ Implemented modal-based CRUD with AJAX data loading
    - ✅ Added CSP-compliant JavaScript throughout
    - ⏳ TODO: Implement authorization/policies for access control using FreeScout's system
    - ⏳ TODO: Optimize queries for performance with eager loading
    - ⏳ TODO: Add logging for actions like confirm and BEO generation

## Additional Features to Consider
- **Time Slots**: Add start_time and end_time to events for precise scheduling.
- **Event Overlap Validation**: Prevent booking same venue at overlapping times.
- **Notifications**: Email alerts for new requests, confirmations, and reminders.
- **User Permissions**: Role-based access (e.g., managers can confirm, staff can view).
- **Advanced Search/Export**: Full-text search, CSV export for tables and reports.
- **Audit Logs**: Track changes to events, confirmations, and settings.
- **Recurring Events**: Support for repeating bookings.
- **Integrations**: Sync with external calendars (Google Calendar), payment gateways for deposits.

## Notes
- Use Laravel's pagination and query builders for filters.
- Modals use AJAX for create/edit to avoid page reloads. Event data loaded via JSON endpoint.
- Custom calendar implementation with month/week/day views instead of FullCalendar. Supports venue colors, setup/release gradients, request stripes, all-day events, sticky headers, and overlap handling.
- All-day events: Use date-only inputs in form, automatically set to 00:01:00 - 23:59:00 in backend. Displayed in dedicated strip above hourly grid.
- Venue colors: Stored as hex values (VARCHAR(7)), displayed throughout calendar with HTML5 color picker for selection.
- Custom fields: Defined in CustomField model with many-to-many department assignment and required flag. Values stored in EventCustomFieldValue model as separate records. Load fields dynamically in forms and views, with validation for required fields. Support for text, number, select, multiselect, date, and textarea types.
- Time slots: Enforce setup_datetime < start_datetime < end_datetime < venue_release_datetime via Event model boot(). For all_day events, setup/release times are nullified.
- Form validation: Uses required_without for dual datetime/date inputs. Checkbox values converted to boolean before validation.
- Calendar styling: Linear gradients for setup/release sections (lighter venue color), repeating-linear-gradient for request stripes, CSS table display for week/day views with sticky positioning.
- Permissions: Settings access restricted to admins via FreeScout's permission system.
- Confirm action updates status and logs activity.
- All views extend FreeScout's layout for integration.
- CSP compliance: All inline JavaScript uses \Helper::cspNonceAttr() for Content Security Policy.