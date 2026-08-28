<?php

namespace Tempel;

class Activator
{
    public static function activate()
    {
        self::register_options();
        self::register_widget_options();
    }
    
    static function register_options() {
        $option_defaults = [
            'security_lock'                     => 'on',
            'enable_branding'                   => 'on',
            'disable_comments'                  => 'on',
            'disable_default_pt'                => 'on',
            'hide_dashboard_widgets'            => 'on',
            'skip_bundled_themes'               => 'on',
            'svg_support'                       => 'on',
            'taxonomy_order'                    => 'on',
            'duplicate_content'                 => 'on',
            'user_switching'                    => 'on',
            'gf_bag_address_enabled'            => '',
            'gf_bag_address_api_key'            => '',
            'gf_bag_address_endpoint'           => 'https://api.postcodeapi.nu/v3/lookup',
            'gf_bag_address_timeout'            => '8',
            'gf_bag_address_monthly_limit'      => '1000',
            'gf_bag_address_cache_days'         => '30',
            'gf_bag_address_rate_limit'         => '20',
            'performance_enabled'               => '',
            'performance_frontend_memory_limit' => '128',
            'performance_admin_memory_limit'    => '256',
            'performance_revision_limit'        => '5',
            'performance_heartbeat_interval'    => '60',
            'performance_disable_heartbeat'     => '',
            'performance_disable_emojis'        => 'on',
            'performance_disable_embeds'        => 'on',
            'performance_disable_xmlrpc'        => 'on',
        ];
        add_option('tmpl_settings', $option_defaults);
    }
    
    static function register_widget_options()
    {
        $widget_defaults = [
            'conversion_widget_enabled'                 => 'on',
            'conversion_selected_forms'                 => '',
            'conversion_include_woocommerce_orders'     => '',
            'conversion_include_post_type'              => '',
            'post_type_count_post_type'                 => '',
            'post_type_count_statuses'                  => '',
            
            'status_widget_enabled'                     => 'on',
            'status_safeupdate_day'                     => 'monday',
            'status_backup_interval'                    => '12:30',
            'status_last_checkup_date'                  => '06/2024',
            
            'support_widget_enabled'                    => 'on',
            'support_ticket_link'                       => 'https://studiotempel.nl/contact',
        ];
        add_option('tmpl_widget_settings', $widget_defaults);
    }
}
