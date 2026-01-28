@extends('layouts.app')

@section('title', 'Request Access')

@section('styles')
    @vite(['resources/css/pages/auth.css'])
@endsection

@section('content')
<div class="auth-page container">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Apply for Access</h1>
                <p class="auth-subtitle text-secondary">Join the research infrastructure network</p>
            </div>

            <div class="step-indicator">
                <div class="step-dot active" data-step="1"></div>
                <div class="step-dot" data-step="2"></div>
            </div>

            <form id="registration-form" action="/apply" method="POST">
                @csrf
                
                <!-- Step 1: Identity -->
                <div class="step-container active" id="step-1">
                    <x-ui.input 
                        label="Full Name" 
                        name="name" 
                        placeholder="Dr. Jane Doe" 
                        required 
                    />

                    <x-ui.input 
                        label="Email Address" 
                        name="email" 
                        type="email" 
                        placeholder="jane.doe@research.org" 
                        required 
                    />

                    <x-ui.input 
                        label="Password" 
                        name="password" 
                        type="password" 
                        placeholder="••••••••" 
                        required 
                    />

                    <x-ui.input 
                        label="Profession" 
                        name="profession" 
                        placeholder="e.g., AI Researcher, PhD Student" 
                        required 
                    />

                    <div class="auth-actions">
                        <x-ui.button type="button" class="btn-lg" onclick="nextStep()">Next: Professional Details</x-ui.button>
                    </div>
                </div>

                <!-- Step 2: Justification -->
                <div class="step-container" id="step-2">
                    <x-ui.textarea 
                        label="Justification for Access" 
                        name="user_justification" 
                        placeholder="Please describe your research goals and why you require access to the data center resources..." 
                        required
                        id="justification-textarea"
                    ></x-ui.textarea>
                    
                    <div id="char-counter" style="text-align: right; font-size: 0.75rem; color: var(--text-muted); margin-top: -0.5rem; margin-bottom: 1rem;">
                        0 characters
                    </div>

                    <div class="auth-actions-row">
                        <x-ui.button type="button" variant="secondary" onclick="prevStep()">Back</x-ui.button>
                        <x-ui.button type="submit">Submit Application</x-ui.button>
                    </div>
                </div>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="/login">Sign In</a>
            </div>
        </div>
    </div>

    <div class="auth-bg-glow"></div>
</div>
@endsection

@section('scripts')
<script>
    // Initialize current step: if there are errors on step 2 fields, start at step 2
    let currentStep = {{ $errors->has('user_justification') ? 2 : 1 }};
    
    // Initial call to set the correct step on page load
    document.addEventListener('DOMContentLoaded', () => {
        updateSteps();
    });

    function updateSteps() {
        // Toggle step containers
        document.querySelectorAll('.step-container').forEach(step => {
            step.classList.remove('active');
        });
        document.getElementById(`step-${currentStep}`).classList.add('active');

        // Update dots
        document.querySelectorAll('.step-dot').forEach(dot => {
            const step = parseInt(dot.getAttribute('data-step'));
            if (step <= currentStep) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    function nextStep() {
        // Simple validation for step 1
        const inputs = document.querySelectorAll('#step-1 input');
        let valid = true;
        inputs.forEach(input => {
            if (!input.checkValidity()) {
                input.reportValidity();
                valid = false;
            }
        });

        if (valid) {
            currentStep = 2;
            updateSteps();
        }
    }

    function prevStep() {
        currentStep = 1;
        updateSteps();
    }

    // Character counter for justification
    const textarea = document.getElementById('justification-textarea');
    const counter = document.getElementById('char-counter');

    if (textarea && counter) {
        const updateCounter = () => {
            const length = textarea.value.length;
            counter.textContent = `${length} characters`;
            
            if (length < 50) {
                counter.style.color = 'var(--warning)';
            } else {
                counter.style.color = 'var(--text-muted)';
            }
        };

        textarea.addEventListener('input', updateCounter);
        // Initial call to set correct counter on page load
        updateCounter();
    }
</script>
@endsection
