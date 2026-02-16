@extends('layouts.app')

@section('title', $__env->yieldContent('title'))

@section('sidebar')
<button class="sidebar-menu-toggle" type="button">
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
</button>
<div class="sidebar-title">
    {{ __('Events') }}
</div>
<ul class="sidebar-menu">
    <li class="{{ request()->routeIs('cnxevents.events.*') ? 'active' : '' }}">
        <a href="{{ route('cnxevents.events.index') }}">
            <i class="glyphicon glyphicon-calendar"></i> {{ __('Events') }}
        </a>
    </li>
    <li class="{{ request()->routeIs('cnxevents.calendar') ? 'active' : '' }}">
        <a href="{{ route('cnxevents.calendar') }}">
            <i class="glyphicon glyphicon-th"></i> {{ __('Calendar') }}
        </a>
    </li>
    <li class="{{ request()->routeIs('cnxevents.analytics') ? 'active' : '' }}">
        <a href="{{ route('cnxevents.analytics') }}">
            <i class="glyphicon glyphicon-stats"></i> {{ __('Analytics') }}
        </a>
    </li>
    @if(Auth::user()->isAdmin())
        <li class="{{ request()->routeIs('cnxevents.settings.*') ? 'active' : '' }}">
            <a href="{{ route('cnxevents.settings.index') }}">
                <i class="glyphicon glyphicon-cog"></i> {{ __('Settings') }}
            </a>
        </li>
    @endif
</ul>
@endsection

@section('content')
<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @yield('content')
</div>
@endsection

@section('stylesheets')
<style>
.sidebar-menu li.active a {
    background-color: #f5f5f5;
    font-weight: bold;
}

/* Desktop sidebar toggle - show button on large screens */
@media (min-width: 992px) {
    .sidebar-menu-toggle {
        display: block !important;
    }
    
    /* Collapsed sidebar state */
    .sidebar-2col.sidebar-collapsed {
        width: 50px;
        padding-right: 0;
        transition: width 0.3s ease;
    }
    
    .sidebar-2col.sidebar-collapsed .sidebar-title {
        display: none;
    }
    
    .sidebar-2col.sidebar-collapsed .sidebar-menu {
        display: none;
    }
    
    .sidebar-2col.sidebar-collapsed .sidebar-menu-toggle {
        margin: 10px auto;
        float: none;
    }
    
    .content-2col {
        transition: margin-left 0.3s ease;
    }
}

/* Modal fixes for Bootstrap 3 compatibility */
.modal-backdrop {
    z-index: 1040 !important;
}
.modal {
    z-index: 10000 !important;
}
/* Push modal down below the header */
.modal-dialog {
    margin-top: 70px;
}
</style>
@yield('stylesheets')
@endsection

@section('scripts')
<script>
// Desktop sidebar collapse functionality
(function() {
    'use strict';
    
    var toggleBtn = document.querySelector('.sidebar-menu-toggle');
    var sidebar = document.querySelector('.sidebar-2col');
    
    if (toggleBtn && sidebar) {
        // Add desktop toggle functionality
        toggleBtn.addEventListener('click', function(e) {
            // Only handle desktop collapse (>= 992px)
            if (window.innerWidth >= 992) {
                e.stopPropagation();
                sidebar.classList.toggle('sidebar-collapsed');
                
                // Save state
                try {
                    if (sidebar.classList.contains('sidebar-collapsed')) {
                        localStorage.setItem('cnxevents_sidebar_collapsed', 'true');
                    } else {
                        localStorage.setItem('cnxevents_sidebar_collapsed', 'false');
                    }
                } catch (err) {
                    // localStorage not available
                }
            }
        });
        
        // Restore collapsed state on page load (desktop only)
        if (window.innerWidth >= 992) {
            try {
                if (localStorage.getItem('cnxevents_sidebar_collapsed') === 'true') {
                    sidebar.classList.add('sidebar-collapsed');
                }
            } catch (err) {
                // localStorage not available
            }
        }
    }
})();
</script>
@yield('scripts')
@stack('scripts')
@endsection