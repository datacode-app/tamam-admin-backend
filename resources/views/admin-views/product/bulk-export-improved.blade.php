@extends('admin-views.partials.import-export-layout')

@section('title', translate('Items Bulk Export'))

@php
    $title = 'Items Bulk Export';
    $subtitle = 'Export your items data to Excel or CSV format for backup, analysis, or migration purposes';
    $icon = 'fas fa-download';
    
    $steps = [
        [
            'title' => 'Choose Export Type',
            'description' => 'Select the format and data scope for your export',
            'features' => [
                'Excel or CSV format',
                'All items or filtered selection',
                'Custom date ranges',
                'Store-specific exports'
            ]
        ],
        [
            'title' => 'Select Data Fields',
            'description' => 'Choose which information to include in your export',
            'features' => [
                'Basic item information',
                'Pricing & stock data',
                'Categories & variations',
                'Multi-language content'
            ]
        ],
        [
            'title' => 'Download Export',
            'description' => 'Generate and download your customized export file',
            'features' => [
                'Instant file generation',
                'Large dataset support',
                'UTF-8 encoding',
                'Ready for import elsewhere'
            ]
        ]
    ];
    
    $actionTitle = 'Ready to Export Your Items?';
    $actionSubtitle = 'Configure your export settings and generate your file';
    
    $stats = [
        ['value' => '1,234', 'label' => 'Total Items'],
        ['value' => '45', 'label' => 'Categories'],
        ['value' => '12', 'label' => 'Stores']
    ];
    
    $help = '
        <p><strong>Export Options:</strong></p>
        <ul>
            <li><strong>Excel Format:</strong> Best for data analysis and spreadsheet work</li>
            <li><strong>CSV Format:</strong> Universal format compatible with all systems</li>
            <li><strong>Multi-language:</strong> Includes translations when available</li>
            <li><strong>Filtered Export:</strong> Export only specific categories or stores</li>
        </ul>
        <p><strong>Large Exports:</strong> Files with 1000+ items may take a few minutes to generate</p>
    ';
@endphp

@section('import-export-content')
{{-- Export Configuration Form --}}
<form id="bulk-export-form" action="{{ route('admin.item.bulk-export') }}" method="POST">
    @csrf
    
    {{-- Export Format Selection --}}
    <div class="export-format-selection mb-4">
        <h4 style="color: #2d3748; margin-bottom: 1.5rem;">
            <i class="fas fa-file-export"></i> Export Format
        </h4>
        
        <div class="row">
            <div class="col-md-6">
                <div class="format-card active" data-format="excel">
                    <div class="format-icon">
                        <i class="fas fa-file-excel text-success"></i>
                    </div>
                    <h5>Excel Format</h5>
                    <p>Rich formatting, charts, formulas</p>
                    <span class="format-extension">.xlsx</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="format-card" data-format="csv">
                    <div class="format-icon">
                        <i class="fas fa-file-csv text-info"></i>
                    </div>
                    <h5>CSV Format</h5>
                    <p>Universal, lightweight, compatible</p>
                    <span class="format-extension">.csv</span>
                </div>
            </div>
        </div>
        <input type="hidden" name="export_format" value="excel" id="export-format">
    </div>
    
    {{-- Data Selection --}}
    <div class="data-selection mb-4">
        <h4 style="color: #2d3748; margin-bottom: 1.5rem;">
            <i class="fas fa-filter"></i> Data Selection
        </h4>
        
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-store"></i> Store Filter
                    </label>
                    <select name="store_id" class="form-control">
                        <option value="">All Stores</option>
                        {{-- Populate dynamically --}}
                    </select>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-tags"></i> Category Filter
                    </label>
                    <select name="category_id" class="form-control">
                        <option value="">All Categories</option>
                        {{-- Populate dynamically --}}
                    </select>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-toggle-on"></i> Item Status
                    </label>
                    <select name="status" class="form-control">
                        <option value="">All Items</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Field Selection --}}
    <div class="field-selection mb-4">
        <h4 style="color: #2d3748; margin-bottom: 1.5rem;">
            <i class="fas fa-list"></i> Include Fields
        </h4>
        
        <div class="row">
            <div class="col-md-6">
                <div class="field-group">
                    <h6><i class="fas fa-info-circle text-primary"></i> Basic Information</h6>
                    <div class="form-check-list">
                        <label class="form-check-item">
                            <input type="checkbox" name="fields[]" value="name" checked>
                            <span>Item Name</span>
                        </label>
                        <label class="form-check-item">
                            <input type="checkbox" name="fields[]" value="description" checked>
                            <span>Description</span>
                        </label>
                        <label class="form-check-item">
                            <input type="checkbox" name="fields[]" value="category">
                            <span>Category</span>
                        </label>
                        <label class="form-check-item">
                            <input type="checkbox" name="fields[]" value="images">
                            <span>Images</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="field-group">
                    <h6><i class="fas fa-dollar-sign text-success"></i> Pricing & Stock</h6>
                    <div class="form-check-list">
                        <label class="form-check-item">
                            <input type="checkbox" name="fields[]" value="price" checked>
                            <span>Price</span>
                        </label>
                        <label class="form-check-item">
                            <input type="checkbox" name="fields[]" value="discount">
                            <span>Discount</span>
                        </label>
                        <label class="form-check-item">
                            <input type="checkbox" name="fields[]" value="stock">
                            <span>Stock Quantity</span>
                        </label>
                        <label class="form-check-item">
                            <input type="checkbox" name="fields[]" value="variations">
                            <span>Variations</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Multi-language Option --}}
        <div class="multilang-option mt-3">
            <label class="form-check-item large">
                <input type="checkbox" name="include_translations" value="1">
                <span>
                    <i class="fas fa-globe"></i>
                    Include Multi-language Data (Arabic, Kurdish)
                </span>
            </label>
        </div>
    </div>
    
    {{-- Export Button --}}
    <div class="text-center" style="margin-top: 3rem;">
        <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 1rem 3rem; border-radius: 50px; font-weight: 600; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); transition: all 0.3s ease;" id="export-btn">
            <i class="fas fa-download"></i>
            Generate Export File
        </button>
    </div>
</form>

{{-- Recent Exports --}}
<div class="recent-exports" style="margin-top: 4rem;">
    <h4 style="color: #2d3748; margin-bottom: 1.5rem;">
        <i class="fas fa-history"></i> Recent Exports
    </h4>
    
    <div class="export-history">
        <div class="export-item">
            <div class="export-info">
                <i class="fas fa-file-excel text-success"></i>
                <div>
                    <strong>items_export_2024_08_07.xlsx</strong>
                    <small class="text-muted d-block">1,234 items • 2 hours ago</small>
                </div>
            </div>
            <a href="#" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
        
        <div class="export-item">
            <div class="export-info">
                <i class="fas fa-file-csv text-info"></i>
                <div>
                    <strong>categories_export.csv</strong>
                    <small class="text-muted d-block">45 categories • Yesterday</small>
                </div>
            </div>
            <a href="#" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format selection
    document.querySelectorAll('.format-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.format-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('export-format').value = this.dataset.format;
        });
    });
    
    // Form submission
    document.getElementById('bulk-export-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const format = document.getElementById('export-format').value;
        const checkedFields = document.querySelectorAll('input[name="fields[]"]:checked');
        
        if (checkedFields.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Fields Selected',
                text: 'Please select at least one field to export',
                confirmButtonColor: '#667eea'
            });
            return;
        }
        
        Swal.fire({
            title: 'Generate Export File?',
            html: `
                <div style="text-align: left;">
                    <p><strong>Export Configuration:</strong></p>
                    <ul style="margin: 1rem 0; padding-left: 1.5rem;">
                        <li>📊 Format: ${format.toUpperCase()}</li>
                        <li>📋 Fields: ${checkedFields.length} selected</li>
                        <li>🌐 Multi-language: ${document.querySelector('input[name="include_translations"]').checked ? 'Yes' : 'No'}</li>
                        <li>⏱️ Processing time: ~30 seconds</li>
                    </ul>
                    <div style="background: #d4edda; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                        <strong>💡 Tip:</strong> Large exports may take a few minutes to generate
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Generate File!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show progress
                showProgress();
                updateProgress(0, 'Starting export...');
                
                // Simulate progress
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += 10;
                    updateProgress(progress, `Exporting data... ${progress}%`);
                    
                    if (progress >= 100) {
                        clearInterval(progressInterval);
                        updateProgress(100, 'Export complete!');
                        setTimeout(() => {
                            hideProgress();
                            showStatus('Export file generated successfully!', 'success');
                        }, 1000);
                    }
                }, 500);
                
                // Submit form
                e.target.submit();
            }
        });
    });
    
    // Select all/none helpers
    function addSelectAllButtons() {
        document.querySelectorAll('.field-group').forEach(group => {
            const selectAll = document.createElement('button');
            selectAll.type = 'button';
            selectAll.className = 'btn btn-sm btn-outline-secondary ms-2';
            selectAll.innerHTML = '<i class="fas fa-check-double"></i> All';
            selectAll.onclick = function(e) {
                e.preventDefault();
                const checkboxes = group.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(cb => cb.checked = true);
            };
            
            const selectNone = document.createElement('button');
            selectNone.type = 'button';
            selectNone.className = 'btn btn-sm btn-outline-secondary ms-1';
            selectNone.innerHTML = '<i class="fas fa-times"></i> None';
            selectNone.onclick = function(e) {
                e.preventDefault();
                const checkboxes = group.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(cb => cb.checked = false);
            };
            
            const h6 = group.querySelector('h6');
            h6.appendChild(selectAll);
            h6.appendChild(selectNone);
        });
    }
    
    addSelectAllButtons();
});
</script>

<style>
.export-format-selection, .data-selection, .field-selection {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
}

.format-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
}

.format-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
}

.format-card.active {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.format-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.format-card h5 {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.format-card p {
    color: #718096;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

.format-extension {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #667eea;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.field-group {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    border-left: 4px solid #667eea;
}

.field-group h6 {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-check-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.form-check-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.form-check-item:hover {
    background: #f8f9fa;
}

.form-check-item.large {
    background: white;
    border: 2px solid #e2e8f0;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    color: #2d3748;
}

.form-check-item.large:hover {
    border-color: #667eea;
}

.form-check-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #667eea;
}

.multilang-option {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
}

.recent-exports {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 16px;
    border-left: 5px solid #667eea;
}

.export-history {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.export-item {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: between;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.export-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.export-info i {
    font-size: 1.5rem;
}

#export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
}
</style>
@endpush