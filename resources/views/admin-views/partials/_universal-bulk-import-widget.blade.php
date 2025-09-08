{{-- Universal Bulk Import Widget --}}
{{--
    Parameters:
    - $type: Type of import (e.g., 'items', 'stores', 'categories')
    - $route: Route for the import action
    - $title: Display title for the widget
--}}

<div class="card bulk-import-widget">
    <div class="card-header">
        <h5 class="card-title d-flex align-items-center">
            <i class="fas fa-upload mr-2"></i>
            {{ $title ?? translate('Bulk Import') }}
        </h5>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="text-primary">{{ translate('Quick') }} {{ $title ?? translate('Import') }}</h6>
                <p class="text-muted mb-2">
                    {{ translate('Import multiple') }} {{ $type ?? 'items' }} {{ translate('at once using Excel or CSV files') }}
                </p>
                <div class="d-flex align-items-center">
                    <span class="badge badge-soft-info mr-2">
                        <i class="fas fa-file-excel mr-1"></i>
                        Excel
                    </span>
                    <span class="badge badge-soft-success mr-2">
                        <i class="fas fa-file-csv mr-1"></i>
                        CSV
                    </span>
                    <span class="badge badge-soft-warning">
                        <i class="fas fa-language mr-1"></i>
                        {{ translate('Multilingual') }}
                    </span>
                </div>
            </div>
            <div class="col-md-4 text-right">
                @if(isset($route) && $route)
                    <a href="{{ route($route) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload mr-1"></i>
                        {{ translate('Start Import') }}
                    </a>
                @else
                    <button class="btn btn-secondary btn-sm" disabled>
                        <i class="fas fa-upload mr-1"></i>
                        {{ translate('Coming Soon') }}
                    </button>
                @endif
            </div>
        </div>
        
        {{-- Quick features list --}}
        <div class="mt-3">
            <small class="text-muted">
                <i class="fas fa-check text-success mr-1"></i>
                {{ translate('Bulk upload with data validation') }}
                &nbsp;|&nbsp;
                <i class="fas fa-check text-success mr-1"></i>
                {{ translate('Error reporting and preview') }}
                &nbsp;|&nbsp;
                <i class="fas fa-check text-success mr-1"></i>
                {{ translate('Template download available') }}
            </small>
        </div>
    </div>
</div>

{{-- Optional: Add custom styles --}}
@push('css_or_js')
<style>
.bulk-import-widget {
    border: 2px solid #f1f3f4;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.bulk-import-widget:hover {
    border-color: #e3f2fd;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.bulk-import-widget .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0;
}

.bulk-import-widget .badge {
    font-size: 0.75rem;
}
</style>
@endpush