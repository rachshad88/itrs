<?php
// Redirect root requests to the `app/` folder so users don't have to select it.
// If the request is already targeting `frontend/pages`, do nothing to avoid redirect loops.
$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
if (strpos($uri, '/frontend/pages') !== false || strpos($uri, '/frontend/pages/') === 0) {
    // Serve normally when `frontend/pages` is already in the URI.
    return;
}

// Preserve query string when redirecting.
$qs = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: frontend/pages.' . $qs);
exit;
