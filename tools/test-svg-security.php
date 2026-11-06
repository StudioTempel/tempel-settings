<?php

if (PHP_SAPI !== 'cli') { exit; }

require dirname(__DIR__) . '/vendor/autoload.php';

function check_svg($condition, string $label): void {
    if (!$condition) { throw new RuntimeException($label); }
    echo "PASS: {$label}\n";
}

$sanitizer = new enshrined\svgSanitize\Sanitizer();
$sanitizer->removeRemoteReferences(true);
$unsafe = '<svg xmlns="http://www.w3.org/2000/svg"><style>@import url(https://evil.test/a.css);</style><a href="javascript:alert(1)"><text>x</text></a></svg>';
$clean = $sanitizer->sanitize($unsafe);

check_svg(is_string($clean), 'SVG sanitizer accepts valid XML');
check_svg(stripos($clean, 'javascript:') === false, 'SVG sanitizer removes script URLs');
check_svg(stripos($clean, 'evil.test') === false, 'SVG sanitizer removes remote CSS references');
check_svg($sanitizer->sanitize('<svg') === false, 'Malformed SVG fails closed');
