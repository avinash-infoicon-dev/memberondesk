<?php

return [
    // Empty disables the mobile app's key prompt. Rotate for a new release.
    'app_lock_key' => (string) env('APP_LOCK_KEY', ''),
];
