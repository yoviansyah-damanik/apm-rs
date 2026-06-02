import * as faceapi from '@vladmandic/face-api';

// Constants
const CONFIG = {
    MODEL_URL: 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/',
    DETECTION_INTERVAL: 250,
    CONFIDENCE_THRESHOLD: 0.6,
    INIT_DELAY: 100,
    CAMERA_STORAGE_KEY: 'selectedCamera'
};

const DEBUG = typeof import.meta !== 'undefined' && import.meta.env ? import.meta.env.DEV : false;

// Status template helpers
const StatusTemplates = {
    loading(message) {
        return `
            <div class="flex items-center justify-center space-x-2">
                <span class="text-white font-medium">${message}</span>
            </div>
        `;
    },

    success(message) {
        return `
            <div class="flex items-center justify-center space-x-2">
                <span class="text-green-300 font-medium">${message}</span>
            </div>
        `;
    },

    error(message) {
        return `
            <div class="flex items-center justify-center space-x-2">
                <span class="text-red-300 font-medium">${message}</span>
            </div>
        `;
    },

    searching(message) {
        return `
            <div class="flex items-center justify-center space-x-2">
                <span class="text-blue-300 font-medium">${message}</span>
            </div>
        `;
    },

    active(message) {
        return `
            <div class="flex items-center justify-center space-x-2">
                <div class="w-3 h-3 bg-green-400 rounded-full animate-ping"></div>
                <span class="text-green-300 font-medium">${message}</span>
            </div>
        `;
    },

    faceDetected(confidence) {
        return `
            <div class="space-y-2">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-6 h-6 text-yellow-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-yellow-300 font-semibold">😎 Wajah terdeteksi</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <span class="text-white text-sm">Confidence:</span>
                    <span class="text-blue-300 font-bold text-lg">${confidence}%</span>
                </div>
            </div>
        `;
    },

    verifySuccess(message, confidence = null) {
        return `
            <div class="space-y-2">
                <div class="flex items-center justify-center space-x-2">
                    <span class="text-green-300 font-bold text-lg">Verifikasi Berhasil!</span>
                </div>
                <p class="text-white text-center">${message}</p>
                ${confidence ? `<p class="text-blue-200 text-sm text-center">Confidence BPJS: ${confidence}</p>` : ''}
            </div>
        `;
    },

    verifyFailed(message) {
        return `
            <div class="space-y-2">
                <div class="flex items-center justify-center space-x-2">
                    <span class="text-red-300 font-bold text-lg">Verifikasi Gagal</span>
                </div>
                <p class="text-red-200 text-center text-sm">${message}</p>
            </div>
        `;
    }
};

// Logger utility
const Logger = {
    log(...args) {
        if (DEBUG) console.log('[Biometric]', ...args);
    },
    error(...args) {
        if (DEBUG) console.error('[Biometric]', ...args);
    },
    info(...args) {
        if (DEBUG) console.info('[Biometric]', ...args);
    }
};

class BiometricVerification {
    constructor() {
        this.reset();
        this.canvasContext = null;
    }

    reset() {
        this.video = null;
        this.overlay = null;
        this.statusBox = null;
        this.cameraSelect = null;
        this.NIK = null;
        this.verifyUrl = null;
        this.csrfToken = null;
        this.modelsLoaded = false;
        this.faceCaptured = false;
        this.currentStream = null;
        this.detectionLoop = null;
    }

    async init(config) {
        // Cleanup existing resources
        this.cleanup();

        // Get DOM elements
        this.video = document.getElementById(config.videoId || 'video');
        this.overlay = document.getElementById(config.overlayId || 'overlay');
        this.statusBox = document.getElementById(config.statusId || 'status');
        this.cameraSelect = document.getElementById(config.cameraSelectId || 'cameraSelect');

        // Store config
        this.NIK = config.nik;
        this.verifyUrl = config.verifyUrl;
        this.csrfToken = config.csrfToken;

        // Validate elements
        const missingElements = [];
        if (!this.video) missingElements.push('video');
        if (!this.overlay) missingElements.push('overlay');
        if (!this.statusBox) missingElements.push('statusBox');
        if (!this.cameraSelect) missingElements.push('cameraSelect');

        if (missingElements.length > 0) {
            Logger.error('Missing required elements:', missingElements);
            return;
        }

        // Cache canvas context
        this.canvasContext = this.overlay.getContext('2d');

        Logger.log('Elements validated successfully');
        await this.loadModels();
    }

    updateStatus(html) {
        if (this.statusBox) {
            this.statusBox.innerHTML = html;
        }
    }

    async loadModels() {
        // Check if models already loaded globally
        if (this.modelsLoaded || window.__faceapiModelsLoaded) {
            Logger.log('Models already loaded, skipping...');
            Logger.log('Nomor Peserta: ', this.NIK);
            this.modelsLoaded = true;
            await this.listCameras();
            return;
        }

        try {
            this.updateStatus(StatusTemplates.loading('⏳ Memuat model AI...'));
            Logger.log('Loading face-api models...');

            // Load models in parallel for better performance
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(CONFIG.MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(CONFIG.MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(CONFIG.MODEL_URL)
            ]);

            this.modelsLoaded = true;
            window.__faceapiModelsLoaded = true;

            Logger.log('All models loaded successfully');
            this.updateStatus(StatusTemplates.success('✅ Model siap, mencari kamera...'));

            await this.listCameras();
        } catch (err) {
            Logger.error('Failed to load models:', err);
            this.updateStatus(StatusTemplates.error(`❌ Gagal memuat model: ${err.message}`));
        }
    }

    async listCameras() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const cameras = devices.filter(d => d.kind === 'videoinput');

            this.cameraSelect.innerHTML = '';

            if (cameras.length === 0) {
                this.updateStatus(StatusTemplates.error('❌ Tidak ada kamera terdeteksi'));
                return;
            }

            // Populate camera select
            cameras.forEach((cam, i) => {
                const option = document.createElement('option');
                option.value = cam.deviceId;
                option.text = cam.label || `Kamera ${i + 1}`;
                this.cameraSelect.appendChild(option);
            });

            // Restore saved camera or use first
            const savedCam = localStorage.getItem(CONFIG.CAMERA_STORAGE_KEY);
            const selectedCamera = (savedCam && cameras.some(c => c.deviceId === savedCam))
                ? savedCam
                : cameras[0].deviceId;

            this.cameraSelect.value = selectedCamera;

            // Camera change handler
            this.cameraSelect.onchange = () => {
                localStorage.setItem(CONFIG.CAMERA_STORAGE_KEY, this.cameraSelect.value);
                this.startCamera(this.cameraSelect.value);
            };

            await this.startCamera(selectedCamera);
        } catch (err) {
            Logger.error('Failed to list cameras:', err);
            this.updateStatus(StatusTemplates.error(`❌ Error: ${err.message}`));
        }
    }

    async startCamera(deviceId) {
        try {
            // Stop existing stream
            if (this.currentStream) {
                this.currentStream.getTracks().forEach(track => track.stop());
            }

            // Request camera stream
            const constraints = {
                video: deviceId ? { deviceId: { exact: deviceId } } : { facingMode: 'user' }
            };

            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.currentStream = stream;
            this.video.srcObject = stream;

            // Wait for video metadata
            await new Promise(resolve => {
                this.video.onloadedmetadata = resolve;
            });

            // Set canvas dimensions
            this.overlay.width = this.video.videoWidth;
            this.overlay.height = this.video.videoHeight;

            Logger.log('Camera started successfully');
            this.updateStatus(StatusTemplates.active('📷 Kamera aktif. Deteksi wajah dimulai...'));

            this.detectAndVerify();
        } catch (err) {
            Logger.error('Camera error:', err);
            this.updateStatus(StatusTemplates.error(`❌ Error kamera: ${err.message}`));
        }
    }

    async detectAndVerify() {
        const size = { width: this.overlay.width, height: this.overlay.height };
        faceapi.matchDimensions(this.overlay, size);

        // Clear existing loop
        if (this.detectionLoop) {
            clearInterval(this.detectionLoop);
        }

        // Detection loop
        this.detectionLoop = setInterval(async () => {
            if (this.faceCaptured) {
                clearInterval(this.detectionLoop);
                return;
            }

            try {
                const detection = await faceapi
                    .detectSingleFace(this.video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                // Clear canvas
                this.canvasContext.clearRect(0, 0, this.overlay.width, this.overlay.height);

                if (detection) {
                    // Draw detection results
                    const resized = faceapi.resizeResults(detection, size);
                    faceapi.draw.drawDetections(this.overlay, resized);
                    faceapi.draw.drawFaceLandmarks(this.overlay, resized);

                    const confidence = detection.detection.score;
                    const confidencePercent = (confidence * 100).toFixed(0);

                    this.updateStatus(StatusTemplates.faceDetected(confidencePercent));

                    // Verify if confidence is high enough
                    if (confidence > CONFIG.CONFIDENCE_THRESHOLD) {
                        this.faceCaptured = true;
                        this.updateStatus(StatusTemplates.loading('🔄 Mengirim data ke BPJS...'));
                        await this.verifyBPJS(Array.from(detection.descriptor));
                    }
                } else {
                    this.updateStatus(StatusTemplates.searching('🔍 Mencari wajah...'));
                }
            } catch (err) {
                Logger.error('Detection error:', err);
            }
        }, CONFIG.DETECTION_INTERVAL);
    }

    async verifyBPJS(encoding) {
        try {
            const response = await fetch(this.verifyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    nik: this.NIK,
                    encoding
                })
            });

            const data = await response.json();

            if (data.success) {
                Logger.log('Verification successful');
                this.updateStatus(StatusTemplates.verifySuccess(data.message, data.confidence));
            } else {
                Logger.log('Verification failed');
                this.updateStatus(StatusTemplates.verifyFailed(data.message));
                // Allow retry after 2 seconds
                setTimeout(() => {
                    this.faceCaptured = false;
                }, 2000);
            }
        } catch (err) {
            Logger.error('Verification error:', err);
            this.updateStatus(StatusTemplates.error(`❌ Error: ${err.message}`));
            // Allow retry
            setTimeout(() => {
                this.faceCaptured = false;
            }, 2000);
        }
    }

    refresh() {
        Logger.log('Refresh triggered');

        // Stop detection
        if (this.detectionLoop) {
            clearInterval(this.detectionLoop);
            this.detectionLoop = null;
        }

        // Reset state
        this.faceCaptured = false;

        // Clear canvas
        if (this.canvasContext) {
            this.canvasContext.clearRect(0, 0, this.overlay.width, this.overlay.height);
        }

        this.updateStatus(StatusTemplates.loading('🔄 Memulai ulang...'));

        // Restart camera
        const selectedDeviceId = this.cameraSelect?.value;
        if (selectedDeviceId && selectedDeviceId !== 'Loading cameras...') {
            Logger.log('Restarting camera with device:', selectedDeviceId);
            this.startCamera(selectedDeviceId);
        } else {
            Logger.log('Reloading cameras...');
            this.listCameras();
        }
    }

    cleanup() {
        Logger.log('Cleaning up resources');

        // Stop detection loop
        if (this.detectionLoop) {
            clearInterval(this.detectionLoop);
            this.detectionLoop = null;
        }

        // Stop video stream
        if (this.currentStream) {
            this.currentStream.getTracks().forEach(track => track.stop());
            this.currentStream = null;
        }

        // Clear canvas
        if (this.canvasContext) {
            this.canvasContext.clearRect(0, 0, this.overlay?.width || 0, this.overlay?.height || 0);
        }

        // Reset state
        this.faceCaptured = false;
    }

    destroy() {
        this.cleanup();
        this.reset();
    }
}

// Export singleton instance
const biometric = new BiometricVerification();
window.BiometricVerification = biometric;

export default biometric;
