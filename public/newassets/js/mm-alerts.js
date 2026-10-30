/*
 * Real-time transaction alerts: loud chime + spoken announcement + toast.
 *
 * The header Livewire component (layouts.header) dispatches "mm-new-transactions"
 * when new transaction notifications arrive: every 10s via wire:poll, or instantly
 * when Pusher/Echo delivers a broadcast (resources/js/app.js calls checkNew()).
 *
 * Browsers only allow sound after the user has interacted with the page, so audio
 * is unlocked on the first click/tap/key press. The speaker button in the top bar
 * turns alerts on/off (saved in localStorage).
 */
(function () {
    var STORAGE_KEY = 'mm-sound-alerts';
    function t(text) { return (window.mmI18n && window.mmI18n[text]) || text; }
    var audioCtx = null;
    var unlocked = false;
    var queue = [];
    var speaking = false;

    function soundEnabled() {
        try { return localStorage.getItem(STORAGE_KEY) !== 'off'; } catch (e) { return true; }
    }
    function setSoundEnabled(on) {
        try { localStorage.setItem(STORAGE_KEY, on ? 'on' : 'off'); } catch (e) { /* private mode */ }
        renderToggle();
    }

    function renderToggle() {
        var btn = document.getElementById('mmSoundToggle');
        if (!btn) return;
        var on = soundEnabled();
        btn.classList.toggle('is-off', !on);
        btn.classList.toggle('needs-unlock', on && !unlocked);
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        btn.title = !on ? t('Sound alerts off - click to turn on')
            : (unlocked ? t('Sound alerts on - click to mute') : t('Click anywhere to enable sound alerts'));
        btn.innerHTML = '<i class="mdi ' + (on ? 'mdi-volume-high' : 'mdi-volume-off') + '"></i>';
    }

    function unlock() {
        if (unlocked) return;
        try {
            audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
            unlocked = true;
        } catch (e) { /* no Web Audio support */ }
        renderToggle();
    }

    // loud three-note "cash register" chime, generated (no audio file needed)
    function chime() {
        if (!audioCtx) return Promise.resolve();
        var now = audioCtx.currentTime;
        var master = audioCtx.createGain();
        var comp = audioCtx.createDynamicsCompressor();
        master.gain.value = 1;
        master.connect(comp); comp.connect(audioCtx.destination);
        [[880, 0], [1175, 0.16], [1568, 0.32]].forEach(function (n) {
            var osc = audioCtx.createOscillator();
            var g = audioCtx.createGain();
            osc.type = 'triangle';
            osc.frequency.value = n[0];
            g.gain.setValueAtTime(0.0001, now + n[1]);
            g.gain.exponentialRampToValueAtTime(1.6, now + n[1] + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, now + n[1] + 0.45);
            osc.connect(g); g.connect(master);
            osc.start(now + n[1]); osc.stop(now + n[1] + 0.5);
        });
        return new Promise(function (r) { setTimeout(r, 850); });
    }

    var CURRENCY_WORDS = { USD: 'US dollars', KHR: 'riel', THB: 'baht', EUR: 'euro', INR: 'rupees' };

    function sentence(a) {
        var text = (String(a.type) + ' ' + String(a.msg)).toLowerCase();
        var kind = text.indexOf('withdraw') !== -1 ? 'withdrawal' : 'deposit';
        var isNew = text.indexOf('new') !== -1 || text.indexOf('created') !== -1;
        var head = isNew ? 'New ' + kind + ' received.' : kind.charAt(0).toUpperCase() + kind.slice(1) + ' status updated.';
        var amount = parseFloat(a.amount);
        var money = isNaN(amount) ? '' : ' ' + (Math.round(amount * 100) / 100) + ' ' + (CURRENCY_WORDS[a.currency] || a.currency || '') + '.';
        var status = a.status ? ' Status: ' + a.status + '.' : '';
        return head + money + status;
    }

    // clear female English voice; best match first (macOS/iOS, Windows/Edge, Chrome, Android)
    var PREFERRED_VOICES = [
        'Samantha', 'Ava', 'Allison', 'Susan', 'Karen', 'Moira', 'Tessa', 'Victoria', 'Serena',
        'Microsoft Aria', 'Microsoft Jenny', 'Microsoft Zira', 'Google US English', 'Google UK English Female',
    ];
    var voice = null;
    function pickVoice() {
        if (!('speechSynthesis' in window)) return;
        var voices = window.speechSynthesis.getVoices().filter(function (v) { return /^en(-|_|$)/i.test(v.lang); });
        if (!voices.length) return;
        for (var i = 0; i < PREFERRED_VOICES.length && !voice; i++) {
            voice = voices.find(function (v) { return v.name.indexOf(PREFERRED_VOICES[i]) === 0; }) || null;
        }
        voice = voice || voices.find(function (v) { return /female|woman|girl/i.test(v.name); })
                      || voices.find(function (v) { return /en-US/i.test(v.lang); }) || voices[0];
    }
    if ('speechSynthesis' in window) {
        pickVoice();
        window.speechSynthesis.addEventListener('voiceschanged', function () { voice = null; pickVoice(); });
    }

    function speak(text) {
        return new Promise(function (resolve) {
            if (!('speechSynthesis' in window)) return resolve();
            if (!voice) pickVoice();
            var u = new SpeechSynthesisUtterance(text);
            if (voice) { u.voice = voice; u.lang = voice.lang; } else { u.lang = 'en-US'; }
            // slightly slower and brighter for a clear announcement, full volume
            u.rate = 0.9; u.pitch = 1.15; u.volume = 1;
            u.onend = u.onerror = function () { resolve(); };
            window.speechSynthesis.speak(u);
            setTimeout(resolve, 12000); // safety: never block the queue
        });
    }

    function playNext() {
        if (speaking || !queue.length) return;
        speaking = true;
        var text = queue.shift();
        chime().then(function () { return speak(text); }).then(function () { speaking = false; playNext(); });
    }

    function shakeBell() {
        var bell = document.querySelector('.mm-bell');
        if (!bell) return;
        bell.classList.remove('mm-ring'); void bell.offsetWidth; bell.classList.add('mm-ring');
    }

    function toast(a) {
        if (typeof toastr === 'undefined') return;
        var amount = parseFloat(a.amount);
        toastr.info(
            (a.transaction_id ? a.transaction_id + '<br>' : '') +
            (isNaN(amount) ? '' : amount.toFixed(2) + ' ' + (a.currency || '')) +
            (a.status ? ' &middot; ' + a.status : ''),
            a.msg || t('New transaction'),
            { timeOut: 8000, progressBar: true, closeButton: true }
        );
    }

    function handle(alerts) {
        if (!alerts || !alerts.length) return;
        shakeBell();
        alerts.slice(0, 5).forEach(toast);
        if (!soundEnabled()) return;
        if (!unlocked) {
            if (typeof toastr !== 'undefined') toastr.warning(t('Click anywhere on the page to enable sound alerts.'), t('Sound is blocked by the browser'));
            return;
        }
        alerts.slice(0, 3).forEach(function (a) { queue.push(sentence(a)); });
        if (alerts.length > 3) queue.push('And ' + (alerts.length - 3) + ' more transactions.');
        playNext();
    }

    ['pointerdown', 'keydown', 'touchstart'].forEach(function (ev) {
        document.addEventListener(ev, unlock, { once: true, passive: true, capture: true });
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#mmSoundToggle')) return;
        var turningOn = !soundEnabled();
        setSoundEnabled(turningOn);
        if (turningOn) { unlock(); queue.push('Sound alerts are on.'); playNext(); }
        else if ('speechSynthesis' in window) { window.speechSynthesis.cancel(); queue = []; }
    });

    document.addEventListener('livewire:init', function () {
        Livewire.on('mm-new-transactions', function (event) { handle(event.alerts); });
    });

    // Pusher/Echo ping (resources/js/app.js): check right away instead of waiting for the next poll
    window.addEventListener('mm-realtime-ping', function () {
        if (!window.Livewire || !Livewire.getByName) return;
        Livewire.getByName('layouts.header').forEach(function (c) { c.call('checkNew'); });
    });

    document.addEventListener('DOMContentLoaded', renderToggle);
    document.addEventListener('livewire:navigated', renderToggle);
    window.mmTransactionAlerts = { voice: function () { return voice && voice.name; }, test: function () { unlock(); handle([{ type: 'Deposit', msg: 'New Deposit Transaction Created!', transaction_id: 'TEST123', amount: 100, currency: 'USD', status: 'pending' }]); } };
})();
