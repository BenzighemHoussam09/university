<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Heartbeat Interval (seconds)
    |--------------------------------------------------------------------------
    | How often the student exam page sends a heartbeat to the server.
    */

    'heartbeat_interval_seconds' => 10,

    /*
    |--------------------------------------------------------------------------
    | Heartbeat Window (seconds)
    |--------------------------------------------------------------------------
    | A student is considered disconnected when last_heartbeat_at is older
    | than now() minus this window.
    */

    'heartbeat_window_seconds' => 25,

    /*
    |--------------------------------------------------------------------------
    | Monitor Poll Interval (seconds)
    |--------------------------------------------------------------------------
    | How often the teacher monitor page refreshes student status.
    */

    'monitor_poll_interval_seconds' => 5,

    /*
    |--------------------------------------------------------------------------
    | Reminder Lead Time (minutes)
    |--------------------------------------------------------------------------
    | How many minutes before an exam the reminder notification is dispatched.
    */

    'reminder_lead_minutes' => 30,

    /*
    |--------------------------------------------------------------------------
    | Absence Threshold
    |--------------------------------------------------------------------------
    | Number of absences at which a student is blocked from logging in.
    */

    'absence_threshold' => 5,

    /*
    |--------------------------------------------------------------------------
    | Finalize Overdue Cadence (seconds)
    |--------------------------------------------------------------------------
    | How often the FinalizeOverdueSessionsCommand runs (scheduler cadence).
    */

    'finalize_overdue_cadence_seconds' => 15,

];
