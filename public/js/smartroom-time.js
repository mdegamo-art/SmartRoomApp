/**
 * Smart Room — live clock and relative timestamps (web admin).
 * Set window.SMARTROOM_TIMEZONE before loading (from Blade).
 */
(function () {
    const tz = window.SMARTROOM_TIMEZONE || 'UTC';

    function formatClock(date) {
        return date.toLocaleString('en-PH', {
            timeZone: tz,
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
    }

    function formatTimeOnly(date) {
        return date.toLocaleTimeString('en-PH', {
            timeZone: tz,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
    }

    function formatDateTime(unixSeconds) {
        return new Date(unixSeconds * 1000).toLocaleString('en-PH', {
            timeZone: tz,
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
    }

    function timeAgo(unixSeconds) {
        const diff = Math.floor(Date.now() / 1000) - unixSeconds;
        if (diff < 0) return 'just now';
        if (diff < 60) return diff + 's ago';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    function tick() {
        const now = new Date();

        document.querySelectorAll('[data-live-clock]').forEach(function (el) {
            el.textContent = formatClock(now);
        });

        document.querySelectorAll('[data-live-clock-short]').forEach(function (el) {
            el.textContent = formatTimeOnly(now);
        });

        document.querySelectorAll('[data-timestamp]').forEach(function (el) {
            const ts = parseInt(el.getAttribute('data-timestamp'), 10);
            if (isNaN(ts)) return;

            const mode = el.getAttribute('data-live-mode') || 'ago';
            if (mode === 'ago') {
                el.textContent = timeAgo(ts);
            } else if (mode === 'absolute') {
                el.textContent = formatDateTime(ts);
            }
        });
    }

    tick();
    setInterval(tick, 1000);

    window.SmartRoomTime = {
        timezone: tz,
        formatClock: formatClock,
        formatTimeOnly: formatTimeOnly,
        formatDateTime: formatDateTime,
        timeAgo: timeAgo,
        tick: tick,
    };
})();
