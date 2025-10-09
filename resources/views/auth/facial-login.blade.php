@extends('layouts.app')

@section('title', 'Facial Login – ReCircle')
@section('meta_description', 'Sign in to ReCircle using facial recognition for secure and convenient access.')

@push('head')
    <link rel="icon" href="{{ Vite::asset('resources/images/vite.svg') }}" />
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-wrapper">
            <div class="auth-info">
                <div class="auth-recycle" aria-hidden="true">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <h1>Facial Recognition Login</h1>
                <p>Secure and convenient access to your ReCircle account using facial recognition technology.</p>
                <ul class="auth-benefits">
                    <li><i class="fa-solid fa-shield-halved"></i> Advanced security with biometrics</li>
                    <li><i class="fa-solid fa-bolt"></i> Quick and seamless login</li>
                    <li><i class="fa-solid fa-eye"></i> Privacy-focused, data stays local</li>
                </ul>
            </div>
            <div class="auth-card">
                <div class="facial-login-container">
                    <div class="video-container" style="position: relative; display: none;">
                        <video id="video" width="320" height="240" autoplay muted playsinline style="border-radius: 8px;"></video>
                        <canvas id="overlay" width="320" height="240" style="position: absolute; top: 0; left: 0; border-radius: 8px;"></canvas>
                    </div>
                    
                    <div id="status-message" class="status-message">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <span>Initializing facial recognition...</span>
                    </div>
                    
                    <div class="facial-controls" style="display: none;">
                        <button id="authenticate-btn" class="btn btn-primary" disabled>
                            <i class="fa-solid fa-face-smile"></i>
                            Authenticate with Face
                        </button>
                        
                        @auth
                        <button id="enroll-btn" class="btn btn-secondary" style="display: none;">
                            <i class="fa-solid fa-user-plus"></i>
                            Enroll Your Face
                        </button>
                        @endauth
                    </div>
                    
                    <div class="fallback-options">
                        <p>Having trouble?</p>
                        <a href="{{ route('auth') }}" class="btn btn-outline">
                            <i class="fa-solid fa-arrow-left"></i>
                            Use Traditional Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.facial-login-container {
    text-align: center;
    padding: 2rem;
}

.video-container {
    margin: 0 auto 1.5rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: #f5f5f5;
    display: inline-block;
}

.status-message {
    margin: 1rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    color: #666;
}

.status-message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.facial-controls {
    margin: 1.5rem 0;
}

.facial-controls .btn {
    margin: 0.5rem;
    min-width: 200px;
}

.fallback-options {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e0e0e0;
}

.fallback-options p {
    margin-bottom: 1rem;
    color: #666;
}
</style>

<script>
class FacialAuth {
    constructor() {
        this.video = document.getElementById('video');
        this.canvas = document.getElementById('overlay');
        this.ctx = this.canvas.getContext('2d');
        this.statusElement = document.getElementById('status-message');
        this.isModelLoaded = false;
        this.isDetecting = false;
    }

    updateStatus(message, type = 'info') {
        this.statusElement.innerHTML = message;
        this.statusElement.className = `status-message ${type}`;
    }

    async loadModels() {
        try {
            this.updateStatus('<i class="fa-solid fa-download"></i> Loading AI models...');
            
            const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model';
            
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            
            this.isModelLoaded = true;
            this.updateStatus('<i class="fa-solid fa-camera"></i> Starting camera...', 'success');
            
            await this.startVideo();
            
        } catch (error) {
            console.error('Model loading error:', error);
            this.updateStatus('<i class="fa-solid fa-exclamation-triangle"></i> Failed to load facial recognition models. Please refresh the page.', 'error');
        }
    }

    async startVideo() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: 320, height: 240, facingMode: 'user' } 
            });
            
            this.video.srcObject = stream;
            
            this.video.addEventListener('loadeddata', () => {
                document.querySelector('.video-container').style.display = 'block';
                document.querySelector('.facial-controls').style.display = 'block';
                document.getElementById('authenticate-btn').disabled = false;
                
                @auth
                document.getElementById('enroll-btn').style.display = 'inline-block';
                @endauth
                
                this.updateStatus('<i class="fa-solid fa-eye"></i> Camera ready. Position your face in the frame.', 'success');
                this.startDetection();
            });
            
        } catch (error) {
            console.error('Camera error:', error);
            this.updateStatus('<i class="fa-solid fa-camera-slash"></i> Camera access denied. Please allow camera permissions and refresh.', 'error');
        }
    }

    startDetection() {
        if (this.isDetecting) return;
        this.isDetecting = true;
        
        const detect = async () => {
            if (!this.isModelLoaded || !this.video.videoWidth) {
                setTimeout(detect, 100);
                return;
            }

            const detection = await faceapi
                .detectSingleFace(this.video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks();

            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            if (detection) {
                const resizedDetections = faceapi.resizeResults(detection, {
                    width: this.canvas.width,
                    height: this.canvas.height
                });
                faceapi.draw.drawDetections(this.canvas, resizedDetections);
                faceapi.draw.drawFaceLandmarks(this.canvas, resizedDetections);
            }

            setTimeout(detect, 100);
        };
        
        detect();
    }

    async detectFaceDescriptor() {
        if (!this.isModelLoaded) {
            throw new Error('Models not loaded');
        }

        const detection = await faceapi
            .detectSingleFace(this.video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (detection) {
            return Array.from(detection.descriptor);
        }
        
        throw new Error('No face detected');
    }

    async enrollFace() {
        try {
            this.updateStatus('<i class="fa-solid fa-user-plus"></i> Enrolling face...');
            
            const descriptor = await this.detectFaceDescriptor();
            
            const response = await fetch('/api/face/enroll', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    userId: {{ auth()->id() ?? 'null' }},
                    descriptor: descriptor
                })
            });
            
            if (response.ok) {
                this.updateStatus('<i class="fa-solid fa-check-circle"></i> Face enrolled successfully! You can now use facial login.', 'success');
            } else {
                throw new Error('Enrollment failed');
            }
            
        } catch (error) {
            console.error('Enrollment error:', error);
            this.updateStatus('<i class="fa-solid fa-exclamation-triangle"></i> ' + error.message, 'error');
        }
    }

    async authenticateFace() {
        try {
            this.updateStatus('<i class="fa-solid fa-search"></i> Authenticating...');
            
            const descriptor = await this.detectFaceDescriptor();
            
            const response = await fetch('/api/face/authenticate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    descriptor: descriptor
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateStatus('<i class="fa-solid fa-check-circle"></i> Authentication successful! Redirecting...', 'success');
                
                // Check if onboarding is needed
                if (result.show_onboarding) {
                    // Load onboarding script and show modal
                    window.currentUserId = result.user_id;
                    
                    // Load Face-api.js for onboarding if not already loaded
                    if (typeof faceapi === 'undefined') {
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js';
                        document.head.appendChild(script);
                    }
                    
                    setTimeout(() => {
                        // Redirect to main auth page with onboarding trigger
                        const url = new URL('/auth', window.location.origin);
                        url.searchParams.set('onboarding', '1');
                        url.searchParams.set('user_id', result.user_id);
                        window.location.href = url.toString();
                    }, 1000);
                } else {
                    setTimeout(() => {
                        window.location.href = result.redirect || '/dashboard';
                    }, 1000);
                }
            } else {
                this.updateStatus('<i class="fa-solid fa-times-circle"></i> Face not recognized. Please try again or use traditional login.', 'error');
            }
            
        } catch (error) {
            console.error('Authentication error:', error);
            this.updateStatus('<i class="fa-solid fa-exclamation-triangle"></i> ' + error.message, 'error');
        }
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', async () => {
    const facialAuth = new FacialAuth();
    
    // Load models and start camera
    await facialAuth.loadModels();
    
    // Event listeners
    document.getElementById('authenticate-btn').addEventListener('click', () => {
        facialAuth.authenticateFace();
    });
    
    @auth
    document.getElementById('enroll-btn').addEventListener('click', () => {
        facialAuth.enrollFace();
    });
    @endauth
});
</script>
@endsection