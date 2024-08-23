<?php

namespace Tempel\Helpers;

class Helper
{
    public static function get_option($option_name)
    {
        return get_option($option_name);
    }
    
    /**
     * Returns the option value from the database by option name
     *
     * @param string $option_name
     * @since 1.0.0
     */
    public static function return_option($option_name)
    {
        $option = get_option('tmpl_settings');
        
        return $option[$option_name] ?? false;
    }
    
    /**
     * Sanitize checkbox value to not return 'on' but true
     *
     * @param string $val
     * @since 1.0.0
     */
    public static function sanitize_checkbox_value($val)
    {
        if ($val == 'on') {
            return true;
        } else {
            return false;
        }
    }
}