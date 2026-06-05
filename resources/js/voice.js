// resources/js/voice.js
// TTS menggunakan Web Speech API dengan antrian dan persistent settings

import aliasesData from '../../aliases.json';

const STORAGE_KEY = 'apm_voice_settings';
const DEBUG = import.meta.env.DEV;

const VOICE_PRIORITY = {
    name: 'Microsoft Gadis - Indonesian (Indonesia)',
    type: 'Microsoft Gadis Online (Natural) - Indonesian (Indonesia)',
    provide: 'Google Bahasa Indonesia',
};

const DEFAULT_SETTINGS = {
    voiceName: VOICE_PRIORITY['name'],
    lang: 'id-ID',
    rate: 1.0,
    pitch: 1.1,
    volume: 1.0,
};

// --- Debug helpers ---

function dbg(msg, ...args) {
    if (!DEBUG) return;
    console.log(`[Voice] ${msg}`, ...args);
}

function warn(msg, ...args) {
    console.warn(`[Voice] ${msg}`, ...args);
}

// --- Settings persistence (localStorage) ---

export function loadSettings() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return { ...DEFAULT_SETTINGS };
        return { ...DEFAULT_SETTINGS, ...JSON.parse(raw) };
    } catch {
        return { ...DEFAULT_SETTINGS };
    }
}

export function saveSettings(settings) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            voiceName: settings.voiceName ?? DEFAULT_SETTINGS.voiceName,
            lang: settings.lang ?? DEFAULT_SETTINGS.lang,
            rate: settings.rate ?? DEFAULT_SETTINGS.rate,
            pitch: settings.pitch ?? DEFAULT_SETTINGS.pitch,
            volume: settings.volume ?? DEFAULT_SETTINGS.volume,
        }));
        dbg('Pengaturan disimpan:', settings);
    } catch (e) {
        warn('Gagal menyimpan pengaturan:', e.message);
    }
}

export function resetSettings() {
    localStorage.removeItem(STORAGE_KEY);
    dbg('Pengaturan direset ke default');
}

// --- Voice detection ---

export function detectVoice(targetName = null, lang = 'id-ID') {
    if (!window.speechSynthesis) {
        warn('Web Speech API tidak didukung browser ini');
        return null;
    }

    const voices = window.speechSynthesis.getVoices();
    if (voices.length === 0) {
        dbg('Daftar voice belum tersedia (akan dicoba ulang via onvoiceschanged)');
        return null;
    }

    dbg(`Tersedia ${voices.length} voice`);

    // VOICE_PRIORITY selalu dicek lebih dulu
    for (const name of Object.values(VOICE_PRIORITY)) {
        const found = voices.find(v => v.name === name);
        if (found) {
            dbg('Voice prioritas ditemukan:', found.name);
            return found;
        }
    }

    // Fallback ke nama yang tersimpan di pengaturan
    if (targetName) {
        const saved = voices.find(v => v.name === targetName);
        if (saved) {
            dbg('Voice dari pengaturan (fallback):', saved.name);
            return saved;
        }
        warn(`Voice "${targetName}" tidak ditemukan`);
    }

    // Fallback ke bahasa Indonesia manapun
    const fallback = voices.find(v => v.lang === lang || v.lang?.startsWith(lang.split('-')[0]));
    if (fallback) {
        dbg('Voice fallback (lang match):', fallback.name, fallback.lang);
        return fallback;
    }

    warn('Tidak ada voice Indonesia tersedia');
    return null;
}

// --- Text normalization (alias ekspansi gelar/singkatan medis) ---

const MEDICAL_ALIASES = aliasesData.aliases
    .filter(entry => entry.pattern)
    .map(({ pattern, flags = 'gi', replacement }) => [new RegExp(pattern, flags), replacement]);

/** Normalisasi teks TTS: ekspansi singkatan medis */
function normalizeText(text) {
    let result = text;
    for (const [pattern, replacement] of MEDICAL_ALIASES) {
        result = result.replace(pattern, replacement);
    }
    return result;
}

// --- Alpine component factory ---

/**
 * Alpine component data untuk TTS.
 * Gunakan di x-data: voiceComponent()
 *
 * @param {Object} options
 * @param {boolean} options.isQueue - Jika true, teks baru masuk antrian.
 *                                    Jika false, langsung menghentikan yang sedang berjalan.
 */
export function voiceComponent(options = {}) {
    return {
        isQueue: options.isQueue ?? false,
        queue: [],
        speaking: false,
        selectedVoice: null,
        settings: loadSettings(),

        init() {
            if (!window.speechSynthesis) {
                warn('Web Speech API tidak didukung. TTS dinonaktifkan.');
                return;
            }

            this.loadVoice();
            window.speechSynthesis.onvoiceschanged = () => {
                dbg('onvoiceschanged fired');
                this.loadVoice();
            };

            document.addEventListener('livewire:navigating', () => this.stop());
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    dbg('Tab tersembunyi, menghentikan TTS');
                    this.stop();
                }
            });
            window.addEventListener('beforeunload', () => this.stop());

            this.observeSweetAlert();
            dbg('Voice component initialized. Settings:', this.settings);
        },

        loadVoice() {
            const voice = detectVoice(this.settings.voiceName, this.settings.lang);
            if (voice) {
                this.selectedVoice = voice;
                const isPriority = Object.values(VOICE_PRIORITY).includes(voice.name);
                if (isPriority && this.settings.voiceName !== voice.name) {
                    this.settings.voiceName = voice.name;
                    saveSettings(this.settings);
                }
            } else {
                this.selectedVoice = null;
            }
        },

        observeSweetAlert() {
            const observer = new MutationObserver((mutations) => {
                for (const mutation of mutations) {
                    for (const node of mutation.addedNodes) {
                        if (!(node instanceof HTMLElement)) continue;
                        if (!node.classList?.contains('swal2-container')) continue;
                        const title = node.querySelector('.swal2-title')?.textContent?.trim() || '';
                        const text = node.querySelector('.swal2-html-container')?.textContent?.trim() || '';
                        const message = [title, text].filter(Boolean).join('. ');
                        if (message) {
                            dbg('SweetAlert terdeteksi, memperdengarkan:', message);
                            this.speak(message);
                        }
                    }
                }
            });
            observer.observe(document.body, { childList: true });
        },

        speak(text) {
            if (!window.speechSynthesis) return;
            if (!text?.trim()) {
                dbg('Teks kosong, diabaikan');
                return;
            }

            if (!this.isQueue) {
                window.speechSynthesis.cancel();
                this.queue = [];
                this.speaking = false;
            }

            this.queue.push(text);
            dbg(`speak() queue length: ${this.queue.length}`);

            if (!this.speaking) this.processQueue();
        },

        processQueue() {
            if (this.queue.length === 0) {
                this.speaking = false;
                this.$dispatch('speak-ended');
                dbg('Queue selesai, speak-ended dispatched');
                return;
            }

            this.speaking = true;
            const text = normalizeText(this.queue.shift());
            const utterance = new SpeechSynthesisUtterance(text);

            utterance.lang = this.settings.lang;
            utterance.rate = this.settings.rate;
            utterance.pitch = this.settings.pitch;
            utterance.volume = this.settings.volume;

            if (this.selectedVoice) {
                utterance.voice = this.selectedVoice;
            } else {
                dbg('Tidak ada voice yang dipilih, menggunakan default browser');
            }

            utterance.onstart = () => dbg('Mulai berbicara:', text.substring(0, 50));
            utterance.onend = () => {
                dbg('Selesai berbicara');
                this.processQueue();
            };
            utterance.onerror = (e) => {
                if (e.error !== 'interrupted') {
                    warn(`utterance error: ${e.error}`);
                } else {
                    dbg('utterance interrupted (normal)');
                }
                this.processQueue();
            };

            try {
                window.speechSynthesis.speak(utterance);
            } catch (e) {
                warn('Gagal menjalankan speechSynthesis.speak():', e.message);
                this.processQueue();
            }
        },

        stop() {
            if (!window.speechSynthesis) return;
            this.queue = [];
            this.speaking = false;
            window.speechSynthesis.cancel();
            dbg('TTS dihentikan');
        },

        /**
         * Update pengaturan dan simpan ke localStorage.
         * @param {Object} newSettings - { voiceName?, lang?, rate?, pitch?, volume? }
         */
        updateSettings(newSettings) {
            this.settings = { ...this.settings, ...newSettings };
            saveSettings(this.settings);
            this.loadVoice();
            dbg('Pengaturan diupdate:', this.settings);
        },

        /** Daftar voice yang tersedia (id dan en) */
        get availableVoices() {
            if (!window.speechSynthesis) return [];
            return window.speechSynthesis.getVoices().filter(
                v => v.lang?.startsWith('id') || v.lang?.startsWith('en')
            );
        },
    };
}

// Ekspos ke global untuk Alpine x-data tanpa import
window.voiceComponent = voiceComponent;
