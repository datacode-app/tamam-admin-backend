{{-- 
    Unified Import/Export Interface Layout
    Beautiful, simple, and explanatory design for all import/export methods
--}}

@push('css_or_js')
<style>
    /* Modern Import/Export Interface Styles */
    .import-export-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .import-export-container::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
        animation: float 20s linear infinite;
        pointer-events: none;
    }
    
    @keyframes float {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
    
    .import-export-header {
        position: relative;
        z-index: 2;
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .import-export-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .import-export-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .process-steps {
        display: flex;
        justify-content: space-between;
        gap: 2rem;
        margin: 3rem 0;
        flex-wrap: wrap;
    }
    
    .process-step {
        flex: 1;
        min-width: 280px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        color: #333;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .process-step:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .process-step::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #FF6B6B, #4ECDC4, #45B7D1, #96CEB4);
    }
    
    .step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 50%;
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .step-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #2d3748;
    }
    
    .step-description {
        color: #718096;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }
    
    .step-features {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .step-features li {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        color: #4a5568;
    }
    
    .step-features li::before {
        content: '✓';
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        background: #48bb78;
        color: white;
        border-radius: 50%;
        font-size: 0.8rem;
        margin-right: 0.75rem;
        flex-shrink: 0;
    }
    
    .action-section {
        background: white;
        border-radius: 20px;
        padding: 3rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        position: relative;
    }
    
    .action-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .action-title {
        font-size: 2rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .action-subtitle {
        color: #718096;
        font-size: 1.1rem;
    }
    
    .upload-area {
        border: 3px dashed #e2e8f0;
        border-radius: 16px;
        padding: 3rem;
        text-align: center;
        background: #f7fafc;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .upload-area:hover {
        border-color: #667eea;
        background: #edf2f7;
    }
    
    .upload-area.dragover {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        transform: scale(1.02);
    }
    
    .upload-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: white;
        font-size: 2rem;
    }
    
    .upload-text {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .upload-hint {
        color: #718096;
        font-size: 0.95rem;
    }
    
    .template-downloads {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        margin: 2rem 0;
    }
    
    .template-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .template-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .template-btn i {
        font-size: 1.2rem;
    }
    
    .language-badges {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        margin: 1rem 0;
        flex-wrap: wrap;
    }
    
    .language-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .progress-container {
        display: none;
        margin: 2rem 0;
    }
    
    .progress-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 4px;
        transition: width 0.3s ease;
        width: 0%;
    }
    
    .status-message {
        margin-top: 2rem;
        padding: 1rem;
        border-radius: 8px;
        display: none;
    }
    
    .status-success {
        background: #f0fff4;
        color: #38a169;
        border-left: 4px solid #38a169;
    }
    
    .status-error {
        background: #fed7d7;
        color: #e53e3e;
        border-left: 4px solid #e53e3e;
    }
    
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin: 2rem 0;
    }
    
    .quick-stat {
        background: #f7fafc;
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
        border-left: 4px solid #667eea;
    }
    
    .quick-stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #2d3748;
    }
    
    .quick-stat-label {
        color: #718096;
        font-size: 0.9rem;
        margin-top: 0.25rem;
    }
    
    .help-section {
        background: #f7fafc;
        border-radius: 16px;
        padding: 2rem;
        margin-top: 3rem;
        border-left: 5px solid #667eea;
    }
    
    .help-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.3rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 1rem;
    }
    
    .help-content {
        color: #4a5568;
        line-height: 1.6;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .process-steps {
            flex-direction: column;
        }
        
        .import-export-title {
            font-size: 2rem;
        }
        
        .template-downloads {
            flex-direction: column;
            align-items: center;
        }
    }
    
    /* Animation for successful upload */
    @keyframes success-pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .upload-success {
        animation: success-pulse 0.6s ease;
        border-color: #48bb78;
        background: #f0fff4;
    }
</style>
@endpush

{{-- Main Container --}}
<div class="content container-fluid">
    
    {{-- Beautiful Header Section --}}
    <div class="import-export-container">
        <div class="import-export-header">
            <h1 class="import-export-title">
                <i class="{{ $icon ?? 'fas fa-exchange-alt' }}"></i>
                {{ $title }}
            </h1>
            <p class="import-export-subtitle">{{ $subtitle }}</p>
        </div>
        
        {{-- Process Steps --}}
        <div class="process-steps">
            @foreach($steps as $index => $step)
            <div class="process-step">
                <div class="step-number">{{ $index + 1 }}</div>
                <h3 class="step-title">{{ $step['title'] }}</h3>
                <p class="step-description">{{ $step['description'] }}</p>
                @if(isset($step['features']))
                <ul class="step-features">
                    @foreach($step['features'] as $feature)
                    <li>{{ $feature }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    
    {{-- Action Section --}}
    <div class="action-section">
        <div class="action-header">
            <h2 class="action-title">{{ $actionTitle ?? 'Get Started' }}</h2>
            <p class="action-subtitle">{{ $actionSubtitle ?? 'Choose your action below' }}</p>
        </div>
        
        {{-- Template Downloads (if provided) --}}
        @if(isset($templates))
        <div class="template-downloads">
            @foreach($templates as $template)
            <a href="{{ $template['url'] }}" class="template-btn" download>
                <i class="{{ $template['icon'] ?? 'fas fa-download' }}"></i>
                {{ $template['name'] }}
            </a>
            @endforeach
        </div>
        @endif
        
        {{-- Language Support (if multilingual) --}}
        @if(isset($languages))
        <div class="language-badges">
            @foreach($languages as $lang)
            <span class="language-badge">
                <span class="flag">{{ $lang['flag'] }}</span>
                {{ $lang['name'] }}
            </span>
            @endforeach
        </div>
        @endif
        
        {{-- Main Content Slot --}}
        <div class="main-content">
            @yield('import-export-content')
        </div>
        
        {{-- Progress Indicator --}}
        <div class="progress-container" id="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <p class="text-center mt-2" id="progress-text">Processing...</p>
        </div>
        
        {{-- Status Messages --}}
        <div class="status-message" id="status-message"></div>
        
        {{-- Quick Stats (if provided) --}}
        @if(isset($stats))
        <div class="quick-stats">
            @foreach($stats as $stat)
            <div class="quick-stat">
                <div class="quick-stat-number">{{ $stat['value'] }}</div>
                <div class="quick-stat-label">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
        @endif
        
        {{-- Help Section --}}
        @if(isset($help))
        <div class="help-section">
            <h3 class="help-title">
                <i class="fas fa-question-circle"></i>
                Need Help?
            </h3>
            <div class="help-content">
                {!! $help !!}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- JavaScript for Enhanced UX --}}
@push('script_2')
<script>
// Enhanced drag and drop functionality
function initializeDragDrop(uploadAreaId, fileInputId) {
    const uploadArea = document.getElementById(uploadAreaId);
    const fileInput = document.getElementById(fileInputId);
    
    if (!uploadArea || !fileInput) return;
    
    // Prevent default behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Highlight drop area when dragging over it
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });
    
    // Handle dropped files
    uploadArea.addEventListener('drop', handleDrop, false);
    
    // Handle click to open file dialog
    uploadArea.addEventListener('click', () => fileInput.click());
    
    // Handle file input change
    fileInput.addEventListener('change', function(e) {
        handleFiles(this.files);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        uploadArea.classList.add('dragover');
    }
    
    function unhighlight() {
        uploadArea.classList.remove('dragover');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
    
    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            showFileName(file.name);
            // You can add file validation here
            validateFile(file);
        }
    }
    
    function showFileName(fileName) {
        uploadArea.innerHTML = `
            <div class="upload-icon">
                <i class="fas fa-file-check"></i>
            </div>
            <div class="upload-text">File Selected: ${fileName}</div>
            <div class="upload-hint">Click to change file or drag another one</div>
        `;
        uploadArea.classList.add('upload-success');
    }
    
    function validateFile(file) {
        const allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!allowedTypes.includes(file.type)) {
            showStatus('Please select a valid CSV or Excel file.', 'error');
            return false;
        }
        
        if (file.size > maxSize) {
            showStatus('File size must be less than 10MB.', 'error');
            return false;
        }
        
        return true;
    }
}

// Progress bar functionality
function showProgress() {
    document.getElementById('progress-container').style.display = 'block';
}

function updateProgress(percent, text = 'Processing...') {
    document.getElementById('progress-fill').style.width = percent + '%';
    document.getElementById('progress-text').textContent = text;
}

function hideProgress() {
    document.getElementById('progress-container').style.display = 'none';
}

// Status message functionality
function showStatus(message, type = 'success') {
    const statusEl = document.getElementById('status-message');
    statusEl.className = `status-message status-${type}`;
    statusEl.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
    `;
    statusEl.style.display = 'block';
    
    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            statusEl.style.display = 'none';
        }, 5000);
    }
}

// Form submission with progress
function submitWithProgress(formId, successMessage = 'Operation completed successfully!') {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        showProgress();
        updateProgress(10, 'Starting process...');
        
        // Simulate progress updates (replace with real progress tracking)
        const progressSteps = [
            { percent: 25, text: 'Validating data...' },
            { percent: 50, text: 'Processing records...' },
            { percent: 75, text: 'Saving to database...' },
            { percent: 90, text: 'Finalizing...' }
        ];
        
        let stepIndex = 0;
        const updateInterval = setInterval(() => {
            if (stepIndex < progressSteps.length) {
                const step = progressSteps[stepIndex];
                updateProgress(step.percent, step.text);
                stepIndex++;
            } else {
                clearInterval(updateInterval);
                updateProgress(100, 'Complete!');
                setTimeout(() => {
                    hideProgress();
                    showStatus(successMessage, 'success');
                }, 1000);
            }
        }, 1000);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // You can call initializeDragDrop from your specific pages
    // initializeDragDrop('upload-area', 'file-input');
});
</script>
@endpush