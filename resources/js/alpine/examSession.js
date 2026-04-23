/**
 * Alpine.js exam session component.
 *
 * Responsibilities:
 * - Fullscreen lockdown: enter fullscreen on init, show blocking overlay on exit
 * - Keyboard Lock API: makes ESC require 2s hold (Chrome/Edge)
 * - Prevent page navigation via beforeunload
 * - Offline buffer: queue saveDraft calls in localStorage, retry on reconnect
 * - Countdown: display server-authoritative deadline driven by deadlineIso
 */
export function examSession({ sessionId, deadlineIso, wireId }) {
    return {
        sessionId,
        deadlineIso,
        wireId,
        deadlineMs: 0,
        countdownInterval: null,
        retryInterval: null,
        storageKey: `examSession:${sessionId}:pending`,

        // UI state
        isSaving: false,
        lastSavedAt: null,
        pendingCount: 0,
        showSubmitModal: false,
        isFullscreen: false,

        init() {
            this.deadlineMs = new Date(deadlineIso).getTime();

            // Watch Livewire deadlineIso for time extensions from teacher
            this.$nextTick(() => {
                this.$wire?.$watch('deadlineIso', (iso) => {
                    this.deadlineMs = new Date(iso).getTime();
                });
            });

            // Countdown tick
            this.countdownInterval = setInterval(() => {
                this.$nextTick(() => {});  // trigger reactivity each second
            }, 1000);

            // Sync pending count from storage
            this.pendingCount = this.getPending().length;

            // Track fullscreen state — show overlay whenever not fullscreen
            this.isFullscreen = !!document.fullscreenElement;
            document.addEventListener('fullscreenchange', () => {
                const wasFullscreen = this.isFullscreen;
                this.isFullscreen = !!document.fullscreenElement;
                // Do NOT auto-re-request here: it causes a blink loop.
                // The overlay forces the student to click the button (user gesture).
                if (wasFullscreen && !this.isFullscreen) {
                    Livewire.find(this.wireId)?.call('recordIncident', 'visibility_hidden');
                }
            });

            // Tab switching detection
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    Livewire.find(this.wireId)?.call('recordIncident', 'visibility_hidden');
                }
            });

            // App/window switching detection
            window.addEventListener('blur', () => {
                Livewire.find(this.wireId)?.call('recordIncident', 'window_blur');
            });

            // Attempt initial fullscreen entry
            this.requestFullscreen();

            // Prevent navigation away from exam page
            window.addEventListener('beforeunload', (e) => {
                e.preventDefault();
                e.returnValue = '';
            });

            // Offline retry loop — flush buffered answers every 3s when online
            this.retryInterval = setInterval(() => {
                if (navigator.onLine) {
                    this.flushPending();
                }
            }, 3000);
        },

        destroy() {
            clearInterval(this.countdownInterval);
            clearInterval(this.retryInterval);
            navigator.keyboard?.unlock();
        },

        requestFullscreen() {
            document.documentElement.requestFullscreen({ navigationUI: 'hide' })
                .then(() => {
                    this.isFullscreen = true;
                    // Keyboard Lock API: requires ESC to be held ~2s before exiting.
                    // Silently ignored on unsupported browsers (Firefox, Safari).
                    navigator.keyboard?.lock(['Escape']).catch(() => {});
                })
                .catch(() => {
                    this.isFullscreen = false;
                });
        },

        formatCountdown() {
            const remaining = Math.max(0, this.deadlineMs - Date.now());
            const h = Math.floor(remaining / 3600000);
            const m = Math.floor((remaining % 3600000) / 60000);
            const s = Math.floor((remaining % 60000) / 1000);
            return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        },

        isTimeLow() {
            return (this.deadlineMs - Date.now()) < 300000; // < 5 minutes
        },

        saveDraft(questionId, choiceId) {
            const payload = { questionId, choiceId, ts: Date.now() };

            if (navigator.onLine) {
                this.isSaving = true;
                Livewire.find(this.wireId)?.call('saveDraft', questionId, choiceId)
                    .then(() => {
                        this.isSaving = false;
                        this.lastSavedAt = new Date().toLocaleTimeString('ar-DZ', {
                            hour: '2-digit',
                            minute: '2-digit',
                        });
                    })
                    .catch(() => {
                        this.isSaving = false;
                        this.bufferPending(payload);
                    });
            } else {
                this.bufferPending(payload);
            }
        },

        bufferPending(payload) {
            const pending = this.getPending();
            const filtered = pending.filter(p => p.questionId !== payload.questionId);
            filtered.push(payload);
            localStorage.setItem(this.storageKey, JSON.stringify(filtered));
            this.pendingCount = filtered.length;
        },

        getPending() {
            try {
                return JSON.parse(localStorage.getItem(this.storageKey) || '[]');
            } catch {
                return [];
            }
        },

        flushPending() {
            const pending = this.getPending();
            if (pending.length === 0) return;

            const wire = Livewire.find(this.wireId);
            if (!wire) return;

            pending.forEach(({ questionId, choiceId }) => {
                wire.call('saveDraft', questionId, choiceId)
                    .then(() => {
                        const remaining = this.getPending().filter(p => p.questionId !== questionId);
                        localStorage.setItem(this.storageKey, JSON.stringify(remaining));
                        this.pendingCount = remaining.length;
                        if (this.pendingCount === 0) {
                            this.lastSavedAt = new Date().toLocaleTimeString('ar-DZ', {
                                hour: '2-digit',
                                minute: '2-digit',
                            });
                        }
                    })
                    .catch(() => {});
            });
        },
    };
}
