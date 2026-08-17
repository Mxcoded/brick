import './bootstrap';
import 'bootstrap';
import Alpine from 'alpinejs';
import './restaurant';

// Livewire v4 ships its own Alpine and auto-injects it on pages that render a
// Livewire component. Booting a second copy here triggers the "Detected
// multiple instances of Alpine running" warning, so defer to Livewire's
// instance when one is already present.
if (!window.Alpine || !window.Alpine.__fromLivewire) {
    window.Alpine = Alpine;
    Alpine.start();
}
