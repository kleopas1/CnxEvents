<style>
.event-modal .modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}
.event-modal .modal-header {
    background: linear-gradient(135deg, #3c8dbc 0%, #2e6da4 100%);
    color: white;
    border-radius: 8px 8px 0 0;
    padding: 15px 0;
    border: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}
.event-modal .modal-title {
    font-size: 20px;
    font-weight: 600;
    color: white;
    margin: 0;
    padding-left: 25px;
    flex: 1;
}
.event-modal .close {
    color: white;
    opacity: 0.9;
    text-shadow: none;
    margin: 0;
    padding: 0 25px 0 0;
    font-size: 28px;
    line-height: 1;
    flex-shrink: 0;
}
.event-modal .close:hover {
    opacity: 1;
    color: white;
}
.event-modal .modal-body {
    padding: 25px;
    background: #f8f9fa;
}
.event-modal .section-header {
    background: #fff;
    padding: 12px 15px;
    margin: -10px -10px 20px -10px;
    border-left: 4px solid #3c8dbc;
    font-weight: 600;
    color: #2e6da4;
    font-size: 16px;
}
.event-modal .form-section {
    background: white;
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.event-modal .form-group label {
    font-weight: 600;
    color: #555;
    margin-bottom: 8px;
}
.event-modal .form-control {
    border-radius: 4px;
    border: 1px solid #ddd;
    padding: 10px 12px;
    transition: border-color 0.2s;
    height: auto;
    line-height: 1.5;
}
.event-modal select.form-control {
    padding: 8px 12px;
    height: 40px;
}
.event-modal textarea.form-control {
    min-height: 80px;
}
.event-modal .form-control:focus {
    border-color: #3c8dbc;
    box-shadow: 0 0 0 0.2rem rgba(60, 141, 188, 0.15);
}
.event-modal .custom-fields-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}
.event-modal .custom-fields-grid .form-group {
    margin-bottom: 0;
}
@media (max-width: 768px) {
    .event-modal .custom-fields-grid {
        grid-template-columns: 1fr;
    }
}
.event-modal .modal-footer {
    background: #f8f9fa;
    border-top: 2px solid #e9ecef;
    padding: 15px 25px;
    border-radius: 0 0 8px 8px;
}
.event-modal .btn {
    padding: 10px 25px;
    font-weight: 600;
    border-radius: 4px;
    transition: all 0.2s;
}
.event-modal .btn-secondary {
    background: #6c757d;
    border: none;
    color: white;
}
.event-modal .btn-secondary:hover {
    background: #5a6268;
    color: white;
}
.event-modal .btn-primary {
    background: linear-gradient(135deg, #3c8dbc 0%, #2e6da4 100%);
    border: none;
    color: white;
}
.event-modal .btn-primary:hover {
    background: linear-gradient(135deg, #2e6da4 0%, #1f5a8a 100%);
    color: white;
}
</style>

<div class="modal fade event-modal" id="eventModal" tabindex="-1" role="dialog" data-backdrop="false" style="z-index: 100000;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="eventForm" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="modal-header">
                    <h4 class="modal-title" id="modalTitle">
                        <i class="glyphicon glyphicon-calendar"></i> Add Event
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Event Details Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="glyphicon glyphicon-info-sign"></i> Event Details
                        </div>
                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Event name" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Event description"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Venue <span class="text-danger">*</span></label>
                                    <select name="venue_id" class="form-control" required>
                                        <option value="">Select venue...</option>
                                        @foreach($venues ?? [] as $venue)
                                            <option value="{{ $venue->id }}">{{ $venue->name }}{{ $venue->capacity ? ' (' . $venue->capacity . ' guests)' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="form-check" style="margin-top: 10px;">
                                        <input type="checkbox" name="all_day" class="form-check-input" id="all_day">
                                        <label class="form-check-label" for="all_day" style="font-weight: normal;">
                                            <i class="glyphicon glyphicon-time"></i> All Day Event
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timing Section -->
                    <div class="form-section" id="datetimeFields">
                        <div class="section-header">
                            <i class="glyphicon glyphicon-time"></i> Event Timing
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date/Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="start_datetime" class="form-control datetime-field">
                                    <input type="date" name="start_date" class="form-control date-field" style="display: none;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date/Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="end_datetime" class="form-control datetime-field">
                                    <input type="date" name="end_date" class="form-control date-field" style="display: none;">
                                </div>
                            </div>
                        </div>
                        <div class="row setup-release-fields">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Setup Datetime</label>
                                    <input type="datetime-local" name="setup_datetime" class="form-control">
                                    <small class="form-text text-muted">Time to start setting up venue</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Venue Release Datetime</label>
                                    <input type="datetime-local" name="venue_release_datetime" class="form-control">
                                    <small class="form-text text-muted">Time when venue is released</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="glyphicon glyphicon-user"></i> Client Information
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Client Name</label>
                                    <input type="text" name="client_name" class="form-control" placeholder="Contact person">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Client Company</label>
                                    <input type="text" name="client_company" class="form-control" placeholder="Company name">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Client Email</label>
                                    <input type="email" name="client_email" class="form-control" placeholder="email@example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Client Phone</label>
                                    <input type="text" name="client_phone" class="form-control" placeholder="+1 (555) 123-4567">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Fields Section -->
                    @if(count($customFields ?? []) > 0)
                    <div class="form-section">
                        <div class="section-header">
                            <i class="glyphicon glyphicon-list-alt"></i> Additional Details
                        </div>
                        <div class="custom-fields-grid">
                            @foreach($customFields ?? [] as $field)
                                <div class="form-group">
                                    <label>{{ $field->name }} @if($field->is_required)<span class="text-danger">*</span>@endif</label>
                                    @if($field->type == 'text')
                                        <input type="text" name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                                    @elseif($field->type == 'select')
                                        <select name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                                            <option value="">Select...</option>
                                            @foreach($field->options ?? [] as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($field->type == 'multiselect')
                                        <select name="custom_field_{{ $field->id }}[]" class="form-control" multiple style="height: auto; min-height: 100px;" @if($field->is_required) required @endif>
                                            @foreach($field->options ?? [] as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple</small>
                                    @elseif($field->type == 'date')
                                        <input type="date" name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                                    @elseif($field->type == 'integer')
                                        <input type="number" step="1" name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                                    @elseif($field->type == 'decimal')
                                        <input type="number" step="0.01" name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="glyphicon glyphicon-remove"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="glyphicon glyphicon-ok"></i> Save Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script{!! \Helper::cspNonceAttr() !!} src="{{ \Module::asset('cnxevents:js/events.js') }}"></script>
