<?php

/**
 * Enables support for SVG file upload and sanitizes uploaded SVG files
 *
 * @since 1.0.0
 */


namespace Tempel;

use enshrined\svgSanitize\Sanitizer;

class SVG_Support
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
        add_filter('wp_check_filetype_and_ext', array($this, "add_mime_types"), 10, 4);
        add_filter('upload_mimes', array($this, 'cc_mime_types'));
        add_action('admin_head', array($this, 'fix_svg'));
        add_filter('wp_handle_upload_prefilter', array($this, 'sanitize_svg_on_upload'));
    }
    
    /**
     *  Sanitize SVG files on upload
     *
     * @since 1.0.0
     * @param array $file
     */
    function sanitize_svg_on_upload($file)
    {
        $extension = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (($file['type'] ?? '') === 'image/svg+xml' || $extension === 'svg') {
            if (!$this->sanitize_svg((string) ($file['tmp_name'] ?? ''))) {
                $file['error'] = __('Dit SVG-bestand kon niet veilig worden verwerkt en is geweigerd.', 'tempel-settings');
            }
        }
        
        return $file;
    }
    
    /**
     * Sanitize SVG files
     *
     * @since 1.0.0
     * @param string $file
     */
    function sanitize_svg($file): bool
    {
        if ($file === '' || !is_file($file) || !is_readable($file) || !is_writable($file)) {
            return false;
        }

        $sanitizer = new Sanitizer();
        $dirty = file_get_contents($file);
        if (!is_string($dirty) || $dirty === '') {
            return false;
        }

        $sanitizer->removeRemoteReferences(true);
        $cleanSVG = $sanitizer->sanitize($dirty);
        if (!is_string($cleanSVG) || $cleanSVG === '') {
            return false;
        }

        return file_put_contents($file, $cleanSVG, LOCK_EX) === strlen($cleanSVG);
    }
    
    /**
     * Add SVG mime types to the allowed file types
     *
     * @since 1.0.0
     * @param array $data
     * @param string $file
     * @param string $filename
     * @param array $mimes
     */
    function add_mime_types($data, $file, $filename, $mimes)
    {
        global $wp_version;
        if ($wp_version !== '4.7.1') {
            return $data;
        }
        
        $filetype = wp_check_filetype($filename, $mimes);
        
        return [
            'ext'             => $filetype['ext'],
            'type'            => $filetype['type'],
            'proper_filename' => $data['proper_filename']
        ];
    }
    
    /**
     * Add SVG mime types
     *
     * @since 1.0.0
     * @param array $mimes
     */
    function cc_mime_types($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }
    
    /**
     * Fix SVG display in the media library
     *
     * @since 1.0.0
     */
    function fix_svg()
    {
        echo '<style type="text/css">
          .attachment-266x266, .thumbnail img {
               width: 100% !important;
               height: auto !important;
          }
          </style>';
    }
}
