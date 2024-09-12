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
            'enable_branding'                   => 'on',
            'disable_comments'                  => 'on',
            'disable_default_pt'                => 'on',
            'hide_dashboard_widgets'            => 'on',
            'svg_support'                       => 'on',
        ];
        add_option('tmpl_settings', $option_defaults);
    }
    
    static function register_widget_options()
    {
        $widget_defaults = [
            'conversion_widget_enabled'                 => 'on',
            'conversion_selected_forms'                 => '',
            
            'status_widget_enabled'                     => 'on',
            'status_safeupdate_day'                     => 'monday',
            'status_backup_interval'                    => '12:30',
            'status_last_checkup_date'                  => '06/2024',
            'status_show_service_contract_tier'         => 'on',
            'status_service_contract_upgradable'        => 'on',
            'status_service_contract_tier'              => 'Plus',
            'status_service_contract_upgrade_link'      => 'https://studiotempel.nl/servicecontract',
            
            'support_widget_enabled'                    => 'on',
            'support_faq_link'                          => 'https://studiotempel.nl/veelgestelde-vragen',
            'support_ticket_link'                       => 'https://studiotempel.nl/contact',
        ];
        add_option('tmpl_widget_settings', $widget_defaults);
    }
}
