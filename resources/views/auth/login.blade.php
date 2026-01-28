@extends('layouts.app')

@section('title', 'Login')

@section('styles')
    @vite(['resources/css/pages/auth.css'])
@endsection

@section('content')
<div class="auth-page container">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle text-secondary">Login to manage your research resources</p>
            </div>

            <form action="/login" method="POST">
                @csrf
                
                <x-ui.input 
                    label="Email Address" 
                    name="email" 
                    type="email" 
                    placeholder="name@institution.edu" 
                    required 
                    autocomplete="email"
                />

                <x-ui.input 
                    label="Password" 
                    name="password" 
                    type="password" 
                    placeholder="••••••••" 
                    required 
                    autocomplete="current-password"
                />

                <div class="auth-actions">
                    <x-ui.button type="submit" class="btn-lg">Sign In</x-ui.button>
                </div>
            </form>

            <div class="auth-footer">
                Don't have access? <a href="/apply">Request an account</a>
            </div>
        </div>
    </div>

    <div class="auth-bg-glow"></div>
</div>
@endsection
