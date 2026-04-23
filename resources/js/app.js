import './bootstrap';
import { examSession } from './alpine/examSession';
import { monitor } from './alpine/monitor';

// Register Alpine components globally so they can be used in Livewire blades.
// Alpine is shipped with Livewire 4; we hook into its lifecycle.
document.addEventListener('alpine:init', () => {
    Alpine.data('examSession', examSession);
    Alpine.data('monitor', monitor);
});
