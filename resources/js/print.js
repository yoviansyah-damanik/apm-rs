// resources/js/print.js

// Deklarasi variabel modul (bukan implicit global)
let originalFontSize = '';
let originalDark = null;
let printStyleEl = null;

/**
 * Auto-print function with optional printer name support
 * @param {string} name - Area name to print
 * @param {Object} options - Print options
 * @param {boolean} options.auto - Auto-print without confirmation
 * @param {string} options.printerName - Printer name (for future use with native apps)
 */
function printArea(name, options = {}) {
    const selector = `[data-area='print'][data-printable='${name}']`;
    const target = document.querySelector(selector);
    if (!target) {
        console.warn('Area print tidak ditemukan:', selector);
        window.print();
        return;
    }

    const pageType = (target.getAttribute('data-page-type') || '').toLowerCase();
    const isAntrean = pageType === 'antrean';
    const isSurat = pageType === 'surat';

    // Matikan dark mode sementara & kecilkan font
    originalDark = (typeof $flux !== 'undefined') ? $flux.dark : null;
    if (typeof $flux !== 'undefined') $flux.dark = false;
    originalFontSize = document.documentElement.style.fontSize || getComputedStyle(document.documentElement).fontSize;
    document.documentElement.style.fontSize = '10pt';

    // Bersihkan style lama
    if (printStyleEl) printStyleEl.remove();

    // Kertas: antrean=80mm, surat=A4, lainnya=auto
    const paperCSS = isAntrean
        ? `
      @page { size: 80mm auto; margin: 0; }
      html, body { width: 80mm; margin: 0 !important; padding: 0 !important; }
    `
        : isSurat
            ? `
      @page { size: A4; margin: 12mm; }
      html, body { margin: 0 !important; padding: 0 !important; }
    `
            : `
      @page { size: auto; margin: 0; }
      html, body { margin: 0 !important; padding: 0 !important; }
    `;

    const css = `
    @media print {
      ${paperCSS}

      /* Sembunyikan semua */
      body * { visibility: hidden !important; }

      /* tampilkan hanya target + posisikan di atas */
      ${selector}, ${selector} * { visibility: visible !important; }

      ${selector} {
        position: fixed !important;
        top: .5rem !important;
        left: 0 !important;
        right: 0 !important;
        bottom: auto !important;
        margin: 0 auto !important;
        color: #000 !important;
        border-color: #000 !important;
        ${isAntrean ? 'width: 100% !important;' : 'width: auto !important;'}
        max-width: none !important;
        height: auto !important;
        display: block !important;
        justify-content: flex-start !important;
        align-items: center !important;
        padding-top: 0 !important;
        page-break-inside: avoid;
        break-inside: avoid;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      /* Sembunyikan elemen bertanda data-no-print */
      [data-no-print], [data-no-print] * {
        display: none !important;
        visibility: hidden !important;
      }
    }
  `;

    printStyleEl = document.createElement('style');
    printStyleEl.textContent = css;
    document.head.appendChild(printStyleEl);

    const afterPrint = () => {
        document.documentElement.style.fontSize = originalFontSize;
        if (typeof $flux !== 'undefined' && originalDark !== null) $flux.dark = originalDark;
        if (printStyleEl) {
            printStyleEl.remove();
            printStyleEl = null;
        }
        window.removeEventListener('afterprint', afterPrint);
    };
    window.addEventListener('afterprint', afterPrint);

    // beri sedikit jeda agar @page terpasang
    setTimeout(() => window.print(), 50);
}

/**
 * Auto-print handler untuk Livewire events
 * Mendengarkan event 'queue-taken' dan otomatis trigger print jika auto-print enabled
 */
function initAutoPrint() {
    // Listen untuk queue-taken event dari Livewire
    window.addEventListener('queue-taken', (event) => {
        const detail = event.detail?.[0] || event.detail;
        const queueNumber = detail?.queueNumber ?? '';

        // Detect apakah priority (A-xxx) atau regular (B-xxx / integer)
        const isPriority = typeof queueNumber === 'string' && queueNumber.startsWith('A-');
        const printAreaName = isPriority ? 'antrean-loket-prioritas' : 'antrean-loket';

        if (detail?.autoPrint === true) {
            const printerName = detail.printerName || 'ANTREAN';
            setTimeout(() => {
                printArea(printAreaName, { auto: true, printerName });
            }, 500);
        } else {
            // Manual print via browser dialog — delay agar Livewire selesai render area print
            setTimeout(() => {
                printArea(printAreaName);
            }, 300);
        }
    });

    console.log('Auto-print handler initialized');
}

// Initialize auto-print saat DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAutoPrint);
} else {
    initAutoPrint();
}

// >>> EKSPOS KE GLOBAL agar bisa dipanggil Alpine:
window.printArea = printArea;
window.initAutoPrint = initAutoPrint;

// (opsional) juga diexport jika nanti mau diimport langsung
export { printArea, initAutoPrint };
