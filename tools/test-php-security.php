<?php
if (PHP_SAPI !== 'cli') { exit; }
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/src/includes/settings/php-security.php';
function check_php_security($condition, $label) { if (!$condition) { throw new RuntimeException($label); } echo "PASS: {$label}\n"; }
check_php_security(!Tempel\PHP_Security::is_patched('8.1.34'), 'End-of-life PHP remains unsafe');
check_php_security(!Tempel\PHP_Security::is_patched('8.4.26'), 'PHP 8.4 before critical fix is unsafe');
check_php_security(Tempel\PHP_Security::is_patched('8.4.27'), 'PHP 8.4 critical fix is accepted');
check_php_security(Tempel\PHP_Security::is_patched('8.3.36'), 'PHP 8.3 critical fix is accepted');
check_php_security(Tempel\PHP_Security::is_patched('8.2.35'), 'PHP 8.2 critical fix is accepted');
check_php_security(Tempel\PHP_Security::is_patched('8.5.12'), 'PHP 8.5 critical fix is accepted');
