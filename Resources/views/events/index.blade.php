@extends('cnxevents::layouts.app')

@section('title', 'Events')

@section('content')
<div class="container">
    <h1>Events</h1>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Validation Error:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <button class="btn btn-primary" data-toggle="modal" data-target="#eventModal" data-backdrop="false">Add Event</button>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="request" {{ request('status') == 'request' ? 'selected' : '' }}>Request</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="venue_id" class="form-control">
                    <option value="">All Venues</option>
                    @foreach($venues as $venue)
                        <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>{{ $venue->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Events Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Client</th>
                <th>Venue</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
                <tr>
                    <td>{{ $event->title }}</td>
                    <td>{{ $event->client_name }}</td>
                    <td>{{ $event->venue->name }}</td>
                    <td>{{ $event->start_datetime->format('Y-m-d H:i') }}</td>
                    <td>{{ $event->end_datetime->format('Y-m-d H:i') }}</td>
                    <td>{{ ucfirst($event->status) }}</td>
                    <td>
                        <button class="btn btn-sm btn-info edit-event-btn" data-event-id="{{ $event->id }}">Edit</button>
                        <form method="POST" action="{{ route('cnxevents.events.destroy', $event->id) }}" style="display:inline;">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <input type="hidden" name="_method" value="DELETE" />
                            <button type="submit" class="btn btn-sm btn-danger delete-event-btn">Delete</button>
                        </form>
                        @if($event->status == 'request')
                            <form method="POST" action="{{ route('cnxevents.events.confirm', $event->id) }}" style="display:inline;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <button type="submit" class="btn btn-sm btn-success confirm-event-btn">Confirm</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $events->links() }}
</div>

@include('cnxevents::modals.event-modal')

@endsection
