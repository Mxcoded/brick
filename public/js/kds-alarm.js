/**
 * Kitchen display alarm.
 *
 * The kitchen works with its back to the screen, so an order that lands
 * silently is an order that sits until someone walks over. The tone has to
 * repeat for as long as a ticket is waiting to be accepted, and it has to stop
 * the moment the last one is, or the kitchen mutes the whole thing to get any
 * work done.
 *
 * A generated tone is used rather than an audio file: the file that shipped
 * with this screen was a 183-byte 8 kHz clip decoding to roughly 23
 * milliseconds of noise, and browsers refuse to start audio before the user has
 * interacted with the page anyway. An oscillator sidesteps both, and the
 * context is resumed on the first interaction so the alarm is audible by the
 * time a real ticket arrives.
 *
 * Usage:
 *   const alarm = window.createKdsAlarm({ intervalMs: 10000 });
 *   alarm.setPendingCount(3);   // repeats until this reaches 0
 */
const createKdsAlarm = ({ intervalMs = 10000, storageKey = 'kdsAlarmMuted' } = {}) => {
    let audioContext = null;
    let repeatTimer = null;
    let pendingCount = 0;
    let muted = false;

    try {
        muted = window.localStorage.getItem(storageKey) === 'true';
    } catch (error) {
        // Private mode, or storage disabled. Not being able to remember the
        // preference is not a reason to lose the alarm.
        muted = false;
    }

    const persistMute = () => {
        try {
            window.localStorage.setItem(storageKey, muted ? 'true' : 'false');
        } catch (error) {
            // As above: the preference is a convenience, not a requirement.
        }
    };

    const context = () => {
        if (audioContext) {
            return audioContext;
        }

        const Context = window.AudioContext || window.webkitAudioContext;
        if (!Context) {
            return null;
        }

        try {
            audioContext = new Context();
        } catch (error) {
            audioContext = null;
        }

        return audioContext;
    };

    /**
     * A short two-tone chime. Two oscillators rather than one so it reads as a
     * signal instead of a click, and square waves because they cut through a
     * noisy kitchen.
     */
    const playTone = () => {
        const ctx = context();
        if (!ctx) {
            return;
        }

        if (ctx.state === 'suspended') {
            ctx.resume().catch(() => {});
        }

        [0, 0.18].forEach((offset, index) => {
            const oscillator = ctx.createOscillator();
            const gain = ctx.createGain();
            const at = ctx.currentTime + offset;

            oscillator.type = 'square';
            oscillator.frequency.value = index === 0 ? 880 : 660;

            // Ramp instead of a hard start/stop, so the tone does not click.
            gain.gain.setValueAtTime(0.0001, at);
            gain.gain.exponentialRampToValueAtTime(0.25, at + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, at + 0.16);

            oscillator.connect(gain);
            gain.connect(ctx.destination);
            oscillator.start(at);
            oscillator.stop(at + 0.18);
        });
    };

    const stopRepeat = () => {
        if (repeatTimer) {
            window.clearInterval(repeatTimer);
            repeatTimer = null;
        }
    };

    const startRepeat = () => {
        if (repeatTimer) {
            return;
        }

        playTone();
        repeatTimer = window.setInterval(playTone, intervalMs);
    };

    /**
     * Reconcile the alarm with the number of tickets still waiting. Anything
     * above zero keeps it ringing; zero silences it at once.
     */
    const setPendingCount = (count) => {
        pendingCount = Number(count) || 0;

        if (pendingCount > 0 && !muted) {
            startRepeat();
        } else {
            stopRepeat();
        }
    };

    const toggleMute = () => {
        muted = !muted;
        persistMute();

        if (muted) {
            stopRepeat();
        } else if (pendingCount > 0) {
            startRepeat();
        }

        return muted;
    };

    /**
     * Browsers keep an audio context suspended until the page has been
     * interacted with. Wiring this to the first click or keypress means the
     * context is already running when the first ticket arrives, rather than the
     * alarm being silently dropped.
     */
    const unlock = () => {
        const ctx = context();
        if (ctx && ctx.state === 'suspended') {
            ctx.resume().catch(() => {});
        }
    };

    ['click', 'keydown', 'touchstart'].forEach((eventName) => {
        window.addEventListener(eventName, unlock, { once: true, passive: true });
    });

    return {
        setPendingCount,
        toggleMute,
        isMuted: () => muted,
        unlock,
    };
};

window.createKdsAlarm = createKdsAlarm;
