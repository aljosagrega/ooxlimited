<?php
if (empty($theme)) {
    return;
}

if (!function_exists($theme . '_convert_to_slug')) {
    eval("
        function {$theme}_convert_to_slug(\$string) {
            return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', \$string)));
        }
    ");
}
