/**
 * Alpine.js monitor component for the teacher's live exam monitor page.
 *
 * Responsibilities:
 * - Listen for the `student-disconnected` Livewire browser event.
 * - Show a dismissible disconnection banner with the student's name.
 * - Maintain a client-side activity feed of disconnection events.
 */
export function monitor() {
    return {
        globalMinutes: 5,
        showEndConfirm: false,
        alertStudentName: null,
        activityFeed: [],

        init() {},

        onStudentDisconnected(detail) {
            const name = detail.studentName || `طالب #${detail.studentId}`;
            this.alertStudentName = name;
            this.addFeedItem('disconnect', name, 'انقطع الاتصال. يحاول النظام الاسترداد...');
        },

        addFeedItem(type, studentName, message) {
            const time = new Date().toLocaleTimeString('ar-DZ', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
            this.activityFeed.unshift({ type, studentName, message, time });
            if (this.activityFeed.length > 20) {
                this.activityFeed.pop();
            }
        },
    };
}
