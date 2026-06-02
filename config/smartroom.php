<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Device online / offline threshold
    |--------------------------------------------------------------------------
    |
    | ESP32 posts sensor data about every 5 seconds. If the latest reading for a
    | device_id is older than this many seconds, the room is treated as offline
    | (no hardware connected or wrong device ID in firmware).
    |
    */
    'device_stale_seconds' => (int) env('SMARTROOM_DEVICE_STALE_SECONDS', 25),

    /*
    |--------------------------------------------------------------------------
    | Web admin inactivity auto-logout timeout
    |--------------------------------------------------------------------------
    |
    | If there is no keyboard/mouse/touch activity in the dashboard for this
    | many seconds, the browser will auto-submit the logout form.
    |
    */
    'web_inactivity_logout_seconds' => (int) env('SMARTROOM_WEB_IDLE_LOGOUT_SECONDS', 900),

];
