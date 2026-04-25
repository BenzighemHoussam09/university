/**
 * Alpine.js exam session component.
 *
 * Responsibilities:
 * - Fullscreen lockdown: enter fullscreen on init, show blocking overlay on exit
 * - Keyboard Lock API: makes ESC require 2s hold (Chrome/Edge)
 * - Prevent page navigation via beforeunload
 * - Offline buffer: queue saveDraft calls in localStorage, retry on reconnect
 * - Countdown: display server-authoritative deadline driven by deadlineIso
 * - Answer state: tracked locally so Livewire re-renders are never needed
 */
export function examSession({ sessionId, deadlineIso, wireId, initialSelections, totalQuestions, initialIncidentCount }) {
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
        showTimeUpModal: false,
        timeExpired: false,
        autoSubmitCountdown: 3,
        autoSubmitTimer: null,

        // Answer & incident state — managed here; Livewire methods are #[Renderless]
        answers: initialSelections,
        totalQuestions,
        incidentCount: initialIncidentCount,

        get answeredCount() {
            return Object.keys(this.answers).length;
        },
        get unansweredCount() {
            return this.totalQuestions - this.answeredCount;
        },
        get progressPct() {
            return this.totalQuestions > 0
                ? Math.round(this.answeredCount / this.totalQuestions * 100)
                : 0;
        },

        isAnswered(questionId) {
            return this.answers[questionId] !== undefined;
        },

        isSelected(questionId, choiceId) {
            return this.answers[questionId] === choiceId;
        },

        init() {
            this.deadlineMs = new Date(deadlineIso).getTime();

            // Watch Livewire deadlineIso for time extensions from teacher
            // Also listen for server-side session-expired event (cron finalized before client detected)
            this.$nextTick(() => {
                this.$wire?.$watch('deadlineIso', (iso) => {
                    this.deadlineMs = new Date(iso).getTime();
                });
                this.$wire?.on('session-expired', () => this.handleTimeExpired());
            });

            // Countdown tick — auto-triggers time-up when deadline reached
            this.countdownInterval = setInterval(() => {
                if (this.deadlineMs > 0 && Date.now() >= this.deadlineMs) {
                    this.handleTimeExpired();
                }
                this.$nextTick(() => {});  // trigger reactivity each second
            }, 1000);

            // Sync pending count from storage
            this.pendingCount = this.getPending().length;

            // Feature-detect fullscreen support (iOS Safari has none at all)
            const el = document.documentElement;
            const canFullscreen = !!(el.requestFullscreen || el.webkitRequestFullscreen);
            // Android/Samsung browsers exit fullscreen on every tap interaction, causing
            // the gate overlay to appear after answering the first question. Bypass the
            // fullscreen gate on mobile — lockdown is still enforced via visibilitychange/blur.
            const isMobile = /Mobi|Android/i.test(navigator.userAgent);

            if (!canFullscreen || isMobile) {
                // iOS Safari / Android / unsupported — bypass gate, lockdown active via events
                this.isFullscreen = true;
            } else {
                this.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);

                const onFullscreenChange = () => {
                    const wasFullscreen = this.isFullscreen;
                    this.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
                    if (wasFullscreen && !this.isFullscreen) {
                        this.incidentCount++;
                        Livewire.find(this.wireId)?.call('recordIncident', 'visibility_hidden');
                    }
                };
                document.addEventListener('fullscreenchange', onFullscreenChange);
                document.addEventListener('webkitfullscreenchange', onFullscreenChange);

                // Attempt initial fullscreen entry
                this.requestFullscreen();
            }

            // Tab switching detection
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.incidentCount++;
                    Livewire.find(this.wireId)?.call('recordIncident', 'visibility_hidden');
                }
            });

            // App/window switching detection — debounced to avoid false positives from
            // mobile touch interactions (radio taps briefly blur the window on Android).
            let _blurTimer = null;
            window.addEventListener('blur', () => {
                clearTimeout(_blurTimer);
                _blurTimer = setTimeout(() => {
                    if (!document.hasFocus()) {
                        this.incidentCount++;
                        Livewire.find(this.wireId)?.call('recordIncident', 'window_blur');
                    }
                }, 300);
            });
            window.addEventListener('focus', () => clearTimeout(_blurTimer));

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

            // Diagnostic — remove after identifying root cause
            this.$watch('isSaving', v => console.log('[exam] isSaving:', v));
            this.$watch('incidentCount', v => console.log('[exam] incidentCount:', v));
            this.$watch('showSubmitModal', v => console.log('[exam] showSubmitModal:', v));
            this.$watch('showTimeUpModal', v => console.log('[exam] showTimeUpModal:', v));
            this.$watch('isFullscreen', v => console.log('[exam] isFullscreen:', v));
        },

        handleTimeExpired() {
            if (this.timeExpired) return;
            this.timeExpired = true;
            clearInterval(this.countdownInterval);
            this.showSubmitModal = false;
            this.showTimeUpModal = true;
            this.autoSubmitCountdown = 3;

            this.autoSubmitTimer = setInterval(() => {
                this.autoSubmitCountdown--;
                if (this.autoSubmitCountdown <= 0) {
                    clearInterval(this.autoSubmitTimer);
                    Livewire.find(this.wireId)?.call('submitFinal');
                }
            }, 1000);
        },

        destroy() {
            clearInterval(this.countdownInterval);
            clearInterval(this.retryInterval);
            clearInterval(this.autoSubmitTimer);
            navigator.keyboard?.unlock();
        },

        requestFullscreen() {
            const el = document.documentElement;
            if (el.requestFullscreen) {
                el.requestFullscreen({ navigationUI: 'hide' })
                    .then(() => {
                        this.isFullscreen = true;
                        navigator.keyboard?.lock(['Escape']).catch(() => {});
                    })
                    .catch(() => {
                        this.isFullscreen = false;
                    });
            } else if (el.webkitRequestFullscreen) {
                // Desktop Safari — synchronous, no promise
                try {
                    el.webkitRequestFullscreen();
                    this.isFullscreen = true;
                } catch {
                    this.isFullscreen = false;
                }
            }
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
            // Optimistic update — visual state changes immediately, no re-render needed
            this.answers[questionId] = choiceId;

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
