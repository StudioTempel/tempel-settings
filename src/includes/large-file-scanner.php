<?php

namespace Tempel;

class Large_File_Scanner
{
    public const RESULT_OPTION = 'tempel_large_file_scan';
    private const DEFAULT_LIMIT = 50;
    private const DEFAULT_TIME_LIMIT = 20.0;

    public static function scan(string $root, int $limit = self::DEFAULT_LIMIT, float $time_limit = self::DEFAULT_TIME_LIMIT): array
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $limit = max(1, min(200, $limit));
        $time_limit = max(0.1, $time_limit);
        $started = microtime(true);
        $directories = array($root);
        $largest = array();
        $scanned = 0;
        $errors = 0;
        $incomplete = false;

        while ($directories) {
            if (microtime(true) - $started >= $time_limit) {
                $incomplete = true;
                break;
            }

            $directory = array_pop($directories);

            try {
                $iterator = new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS);
            } catch (\UnexpectedValueException $exception) {
                $errors++;
                continue;
            }

            foreach ($iterator as $item) {
                if (microtime(true) - $started >= $time_limit) {
                    $incomplete = true;
                    break 2;
                }

                if ($item->isLink()) {
                    continue;
                }

                if ($item->isDir()) {
                    $directories[] = $item->getPathname();
                    continue;
                }

                if (!$item->isFile()) {
                    continue;
                }

                try {
                    $path = str_replace('\\', '/', $item->getPathname());
                    $size = $item->getSize();
                    $modified = $item->getMTime();
                } catch (\RuntimeException $exception) {
                    $errors++;
                    continue;
                }

                $scanned++;
                $largest[] = array(
                    'path' => ltrim(substr($path, strlen($root)), '/'),
                    'size' => max(0, (int) $size),
                    'modified' => max(0, (int) $modified),
                );

                if (count($largest) > $limit * 2) {
                    self::sort_files($largest);
                    $largest = array_slice($largest, 0, $limit);
                }
            }
        }

        self::sort_files($largest);

        return array(
            'timestamp' => time(),
            'root' => $root,
            'files' => array_slice($largest, 0, $limit),
            'scanned' => $scanned,
            'errors' => $errors,
            'incomplete' => $incomplete,
            'duration' => round(microtime(true) - $started, 2),
        );
    }

    public static function run_and_store(): array
    {
        $result = self::scan(ABSPATH);
        update_option(self::RESULT_OPTION, $result, false);
        return $result;
    }

    public static function get_result(): array
    {
        $result = get_option(self::RESULT_OPTION, array());
        return is_array($result) ? $result : array();
    }

    public static function resolve_path(string $relative_path): string
    {
        $relative_path = str_replace('\\', '/', trim($relative_path));
        if ($relative_path === '' || str_starts_with($relative_path, '/') || str_contains($relative_path, "\0")) {
            return '';
        }

        $parts = array_values(array_filter(explode('/', $relative_path), static fn(string $part): bool => $part !== ''));
        if (in_array('..', $parts, true)) {
            return '';
        }

        $root = realpath(ABSPATH);
        $path = realpath(rtrim(ABSPATH, '/\\') . '/' . implode('/', $parts));
        if ($root === false || $path === false || !is_file($path) || is_link($path)) {
            return '';
        }

        $root = rtrim(str_replace('\\', '/', $root), '/');
        $path = str_replace('\\', '/', $path);

        return str_starts_with($path, $root . '/') ? $path : '';
    }

    public static function can_delete(string $relative_path): bool
    {
        $path = self::resolve_path($relative_path);
        if ($path === '') {
            return false;
        }

        $relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');
        $allowed_directories = array(
            'wp-content/uploads/',
            'wp-content/cache/',
        );

        foreach ($allowed_directories as $directory) {
            if (str_starts_with($relative_path, $directory)) {
                return true;
            }
        }

        // Tempel Verhuizen normally removes these private transfer artifacts in
        // its finally block. An interrupted or uncertain remote import can leave
        // them in the WordPress root for manual inspection and cleanup.
        if (preg_match('/^stager-transfer-[a-f0-9]{32}(?:-package|-status)?\.php$/D', $relative_path)) {
            return true;
        }

        return false;
    }

    public static function can_view(string $relative_path): bool
    {
        if (self::resolve_path($relative_path) === '') {
            return false;
        }

        $extension = strtolower((string) pathinfo($relative_path, PATHINFO_EXTENSION));
        return in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'pdf', 'txt', 'log', 'csv', 'json', 'zip'), true);
    }

    private static function sort_files(array &$files): void
    {
        usort($files, static function (array $left, array $right): int {
            return ($right['size'] ?? 0) <=> ($left['size'] ?? 0);
        });
    }
}
