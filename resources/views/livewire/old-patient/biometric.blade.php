<div>
    <livewire:biometric :$participantData lazy />
</div>

{{-- @script
    <script>
        console.log('Loading biometric script for component');
        (function() {
            'use strict';

            // Unique ID untuk komponen ini
            var componentId = 'biometric-{{ uniqid() }}';

            // Check if already initialized untuk prevent duplicate
            if (window.biometricInitialized && window.biometricInitialized[componentId]) {
                console.log('Biometric already initialized for this component');
                return;
            }

            // Track initialization
            window.biometricInitialized = window.biometricInitialized || {};
            window.biometricInitialized[componentId] = true;

            var biometricConfig = {
                videoId: 'video',
                overlayId: 'overlay',
                statusId: 'status',
                cameraSelectId: 'cameraSelect',
                nik: '{{ $participantData['peserta']['nik'] }}',
                verifyUrl: '{{ route('face.verify') }}',
                csrfToken: '{{ csrf_token() }}'
            };

            var initTimeout = null;
            var isInitialized = false;

            function initBiometric() {
                console.log('Initializing biometric verification');

                if (initTimeout) {
                    clearTimeout(initTimeout);
                }

                requestAnimationFrame(function() {
                    initTimeout = setTimeout(function() {
                        if (!window.BiometricVerification) {
                            console.error('BiometricVerification not loaded');
                            return;
                        }

                        window.BiometricVerification.init(biometricConfig);
                        isInitialized = true;
                    }, 100);
                });
            }

            function cleanupBiometric() {
                if (window.BiometricVerification && isInitialized) {
                    window.BiometricVerification.destroy();
                    isInitialized = false;
                }

                if (initTimeout) {
                    clearTimeout(initTimeout);
                    initTimeout = null;
                }
            }

            function refreshBiometric() {
                if (window.BiometricVerification && isInitialized) {
                    window.BiometricVerification.refresh();
                }
            }

            // Setup event listeners
            function setupListeners() {
                console.log('Setting up biometric listeners');

                // Listen for init-biometric event from Livewire
                Livewire.on('init-biometric', initBiometric);

                // Event: Modal opened
                window.addEventListener('modal-opened', function(event) {
                    if (event.detail && event.detail.name === 'frista-modal') {
                        initBiometric();
                    }
                });

                // Event: Modal closed
                window.addEventListener('modal-closed', function(event) {
                    if (event.detail && event.detail.name === 'frista-modal') {
                        cleanupBiometric();
                    }
                });

                // Event: Refresh button
                document.addEventListener('click', function(e) {
                    if (e.target.closest('#refreshBiometric')) {
                        e.preventDefault();
                        refreshBiometric();
                    }
                });

                // Cleanup on navigation away
                document.addEventListener('livewire:navigating', function() {
                    cleanupBiometric();
                    // Reset initialization flag
                    if (window.biometricInitialized) {
                        window.biometricInitialized[componentId] = false;
                    }
                });
            }

            // Initialize immediately and on navigation
            function initialize() {
                setupListeners();
                console.log('Biometric script loaded for component:', componentId);
            }

            // Run on initial load
            if (typeof Livewire !== 'undefined') {
                initialize();
            } else {
                document.addEventListener('livewire:init', initialize);
            }

            // Re-run on navigation to this page
            document.addEventListener('livewire:navigated', function() {
                console.log('Page navigated, re-initializing biometric');
                initialize();
            });

            // ============================================================
            // BIOMETRIC APP LAUNCHER AUTOMATION
            // ============================================================

            /**
             * Helper untuk copy text ke clipboard
             */
            async function copyToClipboard(text) {
                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(text);
                        console.log('Text copied to clipboard:', text);
                        return true;
                    } else {
                        // Fallback untuk browser yang tidak support clipboard API
                        const textarea = document.createElement('textarea');
                        textarea.value = text;
                        textarea.style.position = 'fixed';
                        textarea.style.opacity = '0';
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                        console.log('Text copied to clipboard (fallback):', text);
                        return true;
                    }
                } catch (error) {
                    console.error('Failed to copy to clipboard:', error);
                    return false;
                }
            }

            /**
             * Helper untuk delay/sleep
             */
            function sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            /**
             * Automation workflow untuk aplikasi biometric
             * Menangani clipboard automation dengan delays yang tepat
             */
            async function handleBiometricAppLaunch(event) {
                const {
                    app,
                    credentials,
                    participant_number,
                    delays
                } = event.detail;

                console.log(`Biometric app ${app} launched, starting automation...`);

                try {
                    // STEP 1: Delay setelah launch aplikasi
                    console.log(`Waiting ${delays.app_launch}ms after app launch...`);
                    await sleep(delays.app_launch);

                    // STEP 2: Copy USERNAME ke clipboard
                    console.log('Copying username to clipboard...');
                    await copyToClipboard(credentials.username);

                    // Notifikasi untuk user
                    showAutomationNotification(
                        'Username Disalin',
                        `Username "${credentials.username}" telah disalin ke clipboard. Paste (Ctrl+V) ke field username aplikasi ${app}.`,
                        'info'
                    );

                    // STEP 3: Tunggu aplikasi load sepenuhnya
                    console.log(`Waiting ${delays.app_load}ms for app to load...`);
                    await sleep(delays.app_load);

                    // STEP 4: Copy PASSWORD ke clipboard
                    console.log('Copying password to clipboard...');
                    await copyToClipboard(credentials.password);

                    showAutomationNotification(
                        'Password Disalin',
                        'Password telah disalin ke clipboard. Paste (Ctrl+V) ke field password.',
                        'info'
                    );

                    // STEP 5: Delay setelah login
                    console.log(`Waiting ${delays.after_login}ms after login...`);
                    await sleep(delays.after_login);

                    // STEP 6: Copy NOMOR PESERTA ke clipboard (jika ada)
                    if (participant_number) {
                        console.log('Copying participant number to clipboard...');
                        await copyToClipboard(participant_number);

                        showAutomationNotification(
                            'Nomor Peserta Disalin',
                            `Nomor peserta "${participant_number}" telah disalin ke clipboard. Paste (Ctrl+V) ke field nomor peserta.`,
                            'success'
                        );
                    }

                    console.log('Biometric app automation completed successfully');

                } catch (error) {
                    console.error('Error during biometric app automation:', error);
                    showAutomationNotification(
                        'Automation Error',
                        'Terjadi kesalahan saat automation. Silakan isi form manual.',
                        'error'
                    );
                }
            }

            /**
             * Tampilkan notifikasi automation (menggunakan browser notification API)
             */
            function showAutomationNotification(title, message, type = 'info') {
                // Coba gunakan browser notification jika allowed
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification(title, {
                        body: message,
                        icon: type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ',
                        badge: '/favicon.ico',
                        requireInteraction: false,
                        silent: false
                    });
                }

                // Fallback: log ke console
                console.log(`[${type.toUpperCase()}] ${title}: ${message}`);

                // Optional: Tambahkan toast notification jika ada library
                // Misalnya menggunakan Livewire Alert atau custom toast
            }

            /**
             * Request notification permission saat page load
             */
            function requestNotificationPermission() {
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission().then(function(permission) {
                        console.log('Notification permission:', permission);
                    });
                }
            }

            // Listen untuk event biometric-app-launched dari Livewire
            if (typeof Livewire !== 'undefined') {
                Livewire.on('biometric-app-launched', handleBiometricAppLaunch);

                // Request notification permission
                requestNotificationPermission();
            } else {
                document.addEventListener('livewire:init', function() {
                    Livewire.on('biometric-app-launched', handleBiometricAppLaunch);
                    requestNotificationPermission();
                });
            }

            // ============================================================
            // END BIOMETRIC APP LAUNCHER AUTOMATION
            // ============================================================

        })();
    </script>
@endscript --}}
