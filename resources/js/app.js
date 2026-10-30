import './bootstrap';

// Instant transaction alerts over Pusher when it is configured (VITE_PUSHER_APP_KEY).
// The header also checks every 10 seconds, so alerts still arrive without Pusher.
// Sound, voice and toast are handled by public/newassets/js/mm-alerts.js.
if (window.Echo) {
    window.Echo.channel('my-channel').listen('.form-submitted', () => {
        window.dispatchEvent(new Event('mm-realtime-ping'));
    });
}
