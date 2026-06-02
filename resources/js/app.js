import.meta.glob([
    '../images/**',
]);

import Swal from 'sweetalert2'
window.Swal = Swal

// import './echo';
import './print';
import './voice';

// Import and initialize biometric
// import biometric from './biometric';
// Make it available globally (biometric.js already sets window.BiometricVerification)
// but we ensure it's loaded by referencing it here
// if (biometric) {
//     console.info('Biometric module loaded successfully');
// }

