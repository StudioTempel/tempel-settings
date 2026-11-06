<?php
// Standalone regression checks: php tools/test-large-file-scanner.php
if (PHP_SAPI !== 'cli') {
    exit;
}

require dirname(__DIR__) . '/src/includes/large-file-scanner.php';

function check($condition, $label) {
    if (!$condition) {
        throw new RuntimeException($label);
    }
    echo "PASS: $label\n";
}

$root = sys_get_temp_dir() . '/tempel-large-files-' . bin2hex(random_bytes(4));
define('ABSPATH', $root . '/');
mkdir($root . '/wp-content/uploads/nested', 0777, true);
file_put_contents($root . '/small.txt', str_repeat('a', 10));
file_put_contents($root . '/wp-content/uploads/medium.jpg', str_repeat('b', 100));
file_put_contents($root . '/wp-content/uploads/nested/large.zip', str_repeat('c', 1000));
file_put_contents($root . '/stager-transfer-5d696227911771931697e8c7d52c66f9-package.php', str_repeat('d', 50));
file_put_contents($root . '/stager-transfer-5d696227911771931697e8c7d52c66f9.php', 'runner');
file_put_contents($root . '/stager-transfer-5d696227911771931697e8c7d52c66f9-status.php', 'status');

$result = Tempel\Large_File_Scanner::scan($root, 2, 5.0);
check($result['scanned'] === 6, 'All regular files are scanned');
check(count($result['files']) === 2, 'Result is limited to requested number of files');
check($result['files'][0]['path'] === 'wp-content/uploads/nested/large.zip', 'Largest file is listed first');
check($result['files'][0]['size'] === 1000, 'File size is recorded');
check($result['files'][1]['path'] === 'wp-content/uploads/medium.jpg', 'Second-largest file is retained');
check(!$result['incomplete'], 'Small scan completes within the time limit');
check(strpos($result['files'][0]['path'], $root) === false, 'Stored paths are relative to WordPress root');
check(Tempel\Large_File_Scanner::can_delete('wp-content/uploads/nested/large.zip'), 'Upload files may be selected for deletion');
check(!Tempel\Large_File_Scanner::can_delete('small.txt'), 'Files outside safe directories cannot be deleted');
check(Tempel\Large_File_Scanner::can_delete('stager-transfer-5d696227911771931697e8c7d52c66f9-package.php'), 'Orphaned Tempel transfer packages may be deleted');
check(Tempel\Large_File_Scanner::can_delete('stager-transfer-5d696227911771931697e8c7d52c66f9.php'), 'Orphaned Tempel transfer runners may be deleted');
check(Tempel\Large_File_Scanner::can_delete('stager-transfer-5d696227911771931697e8c7d52c66f9-status.php'), 'Orphaned Tempel transfer status files may be deleted');
check(!Tempel\Large_File_Scanner::can_delete('stager-transfer-not-a-token-package.php'), 'Unrecognized root PHP files remain protected');
check(Tempel\Large_File_Scanner::resolve_path('../outside.txt') === '', 'Directory traversal is rejected');
check(Tempel\Large_File_Scanner::can_view('wp-content/uploads/medium.jpg'), 'Safe images can be viewed');
file_put_contents($root . '/wp-content/uploads/dangerous.svg', '<svg onload="alert(1)"></svg>');
check(!Tempel\Large_File_Scanner::can_view('wp-content/uploads/dangerous.svg'), 'Active SVG content is not served by the viewer');

unlink($root . '/small.txt');
unlink($root . '/wp-content/uploads/medium.jpg');
unlink($root . '/wp-content/uploads/nested/large.zip');
unlink($root . '/wp-content/uploads/dangerous.svg');
unlink($root . '/stager-transfer-5d696227911771931697e8c7d52c66f9-package.php');
unlink($root . '/stager-transfer-5d696227911771931697e8c7d52c66f9.php');
unlink($root . '/stager-transfer-5d696227911771931697e8c7d52c66f9-status.php');
rmdir($root . '/wp-content/uploads/nested');
rmdir($root . '/wp-content/uploads');
rmdir($root . '/wp-content');
rmdir($root);
