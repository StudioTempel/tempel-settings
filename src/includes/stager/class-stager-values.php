<?php
/** Byte-safe URL replacement without instantiating serialized PHP objects. */
final class Tempel_Stager_Values {
    public static function replace($value, array $map, $depth = 0) {
        if (!is_string($value) || $depth > 64) { return $value; }
        if (preg_match('/^(?:a|O|C|s|i|d|b|R|r):|^N;/', $value)) {
            $offset = 0;
            try {
                $result = self::token($value, $offset, $map, $depth + 1);
                if ($offset === strlen($value)) { return $result; }
            } catch (RuntimeException $e) {
                // Never corrupt an unsupported serialized representation.
                if (preg_match('/^(?:a|O|C|s):/', $value)) { throw $e; }
            }
        }
        return strtr($value, $map);
    }

    private static function token($input, &$offset, array $map, $depth) {
        if ($depth > 64) { throw new RuntimeException('Te diep geneste geserialiseerde gegevens.'); }
        $tail = substr($input, $offset);
        if (preg_match('/^(N;|[bidRr]:[^;]*;)/', $tail, $match)) {
            $offset += strlen($match[0]); return $match[0];
        }
        if (preg_match('/^s:(\d+):"/', $tail, $match)) {
            $offset += strlen($match[0]);
            $length = (int) $match[1];
            $value = substr($input, $offset, $length);
            $offset += $length;
            if (substr($input, $offset, 2) !== '";') { throw new RuntimeException('Ongeldige geserialiseerde tekst.'); }
            $offset += 2;
            $value = self::replace($value, $map, $depth + 1);
            return 's:' . strlen($value) . ':"' . $value . '";';
        }
        if (preg_match('/^a:(\d+):\{/', $tail, $match)) {
            $header = $match[0]; $count = (int) $match[1] * 2;
        } elseif (preg_match('/^O:(\d+):"([^"\r\n]*)":(\d+):\{/', $tail, $match) && strlen($match[2]) === (int) $match[1]) {
            $header = $match[0]; $count = (int) $match[3] * 2;
        } else { throw new RuntimeException('Niet-ondersteunde geserialiseerde gegevens (bijvoorbeeld een custom Serializable-object).'); }
        $offset += strlen($header); $output = $header;
        for ($i = 0; $i < $count; $i++) { $output .= self::token($input, $offset, $map, $depth + 1); }
        if (substr($input, $offset++, 1) !== '}') { throw new RuntimeException('Ongeldige geserialiseerde structuur.'); }
        return $output . '}';
    }

    public static function excluded_file($path) {
        $path = ltrim(str_replace('\\', '/', (string) $path), '/');
        if (preg_match('~(^|/)(\.git|\.svn|\.env[^/]*|node_modules|\.DS_Store)(/|$)~i', $path)) { return true; }
        foreach (['wp-content/cache', 'wp-content/upgrade', 'wp-content/ai1wm-backups', 'wp-content/updraft', 'wp-content/backup-db', 'wp-content/uploads/cache'] as $directory) {
            if ($path === $directory || str_starts_with($path, $directory . '/')) { return true; }
        }
        // Backup directory names can also be executable plugin/theme modules.
        // Only omit backup data under uploads, never matching plugin source names.
        return (str_starts_with($path, 'uploads/') || str_starts_with($path, 'wp-content/uploads/')) && (bool) preg_match('~(^|/)(updraft|ai1wm-backups|backup[^/]*)(/|$)~i', $path);
    }

    public static function safe_path($path) {
        return is_string($path) && strlen($path) < 2048 &&
            !preg_match('/[\\\\\x00-\x1f:]/', $path) &&
            !preg_match('~(^|/)\.{1,2}(/|$)~', $path) &&
            preg_match('~^(?:content/(?:plugins|themes|uploads)|site)/[^/].*~', $path) &&
            strpos($path, '//') === false;
    }
}
