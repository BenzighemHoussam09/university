/**
 * Alpine.js monitor component for the teacher's live exam monitor page.
 *
 * Responsibilities:
 * - Listen for `student-disconnected` and `student-violation` Livewire browser events.
 * - Play audio alerts via Web Audio API (lazy AudioContext for autoplay policy).
 * - Show dismissible banners and maintain an activity feed.
 */
export function monitor() {
    return {
        globalMinutes: 5,
        showEndConfirm: false,
        alertStudentName: null,
        alertViolationName: null,
        alertSubmittedName: null,
        activityFeed: [],
        audioCtx: null,

        init() {
            // Prime AudioContext on first user gesture so beeps work immediately.
            document.addEventListener('click', () => this._initAudio(), { once: true });
        },

        _initAudio() {
            if (this.audioCtx) return;
            try {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } catch (e) {}
        },

        _beep(frequency, duration, gain = 0.25) {
            try {
                if (!this.audioCtx) {
                    this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                const ctx = this.audioCtx;
                const play = () => {
                    const osc = ctx.createOscillator();
                    const vol = ctx.createGain();
                    osc.connect(vol);
                    vol.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.value = frequency;
                    vol.gain.setValueAtTime(gain, ctx.currentTime);
                    vol.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + duration);
                };
                if (ctx.state === 'suspended') {
                    ctx.resume().then(play).catch(() => {});
                } else {
                    play();
                }
            } catch (e) {}
        },

        _playDisconnectSound() {
            this._beep(440, 0.5);
            setTimeout(() => this._beep(330, 0.7), 350);
        },

        _playViolationSound() {
            this._beep(880, 0.15);
            setTimeout(() => this._beep(880, 0.15), 200);
            setTimeout(() => this._beep(1100, 0.2), 400);
        },

        _playSubmitSound() {
            this._beep(660, 0.15);
            setTimeout(() => this._beep(880, 0.15), 180);
            setTimeout(() => this._beep(1100, 0.25), 360);
        },

        onStudentDisconnected(detail) {
            const name = detail.studentName || `طالب #${detail.studentId}`;
            this.alertStudentName = name;
            this._playDisconnectSound();
            this.addFeedItem('disconnect', name, 'انقطع الاتصال. يحاول النظام الاسترداد...');
        },

        onStudentSubmitted(detail) {
            const name = detail.studentName || `طالب #${detail.studentId}`;
            this.alertSubmittedName = name;
            this._playSubmitSound();
            this.addFeedItem('submitted', name, 'سلّم إجاباته وأنهى الامتحان.');
        },

        onStudentViolation(detail) {
            const name = detail.studentName || `طالب #${detail.studentId}`;
            const kindLabel = this._violationLabel(detail.kind);
            this.alertViolationName = name;
            this._playViolationSound();
            this.addFeedItem(
                'violation',
                name,
                `مخالفة: ${kindLabel} (الإجمالي: ${detail.total ?? '?'})`,
            );
        },

        _violationLabel(kind) {
            const map = {
                visibility_hidden: 'تبديل التبويب',
                window_blur: 'مغادرة النافذة',
                navigation_attempt: 'محاولة التنقل',
            };
            return map[kind] ?? kind ?? 'غير معروف';
        },

        addFeedItem(type, studentName, message) {
            const time = new Date().toLocaleTimeString('ar-DZ', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
            this.activityFeed.unshift({ type, studentName, message, time });
            if (this.activityFeed.length > 50) {
                this.activityFeed.pop();
            }
        },
    };
}
