<?php

namespace Tempel\Core;
// require PLUGIN_DIR . 'vendor/autoload.php';

use enshrined\svgSanitize\Sanitizer;

class TMPL_SVGSanitizer
{
    public function __construct() {
        add_filter('wp_check_filetype_and_ext', array($this, "add_mime_types"), 10, 4);
        add_filter('upload_mimes', array($this, 'cc_mime_types'));
        add_action('admin_head', array($this, 'fix_svg'));
        add_filter( 'wp_handle_upload_prefilter', array($this, 'tmpl_sanitize_svg'));
    }

    function tmpl_sanitize_svg($file)
    {   
        if($file['type'] == 'image/svg+xml') {
            $this->tmpl_sanitize($file['tmp_name']);
        } 

        return $file;
    }

    function tmpl_sanitize($file) {

        $sanitizer = new Sanitizer();

        $dirty = file_get_contents($file);

        $sanitizer->removeRemoteReferences(true);

        $cleanSVG = $sanitizer->sanitize($dirty);

        file_put_contents($file, $cleanSVG);

        return $file;
    }

    /**
     * Add SVG Support
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

    function cc_mime_types($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }


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