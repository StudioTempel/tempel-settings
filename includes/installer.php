<?php

namespace Tempel;

class Installer
{
    public static function setup()
    {
        $default = array(
            'enable_branding'                   => 'on',
            'disable_comments'                  => 'on',
            'disable_default_pt'                => 'on',
            'hide_dashboard_widgets'            => 'on',
            'enable_widgets'                    => 'on',
            'svg_support'                       => 'ofs',
        );
        update_option('tmpl_settings', $default);
        
        $widget_defaults = array(
            'conversion_selected_forms'                 => '',
            'status_safeupdate_day'                     => 'monday',
            'status_backup_interval'                    => '12:30',
            'status_last_checkup_date'                  => '06/2024',
            'status_show_service_contract_tier'         => 'on',
            'status_service_contract_upgradable'        => 'on',
            'status_service_contract_tier'              => 'Plus',
            'status_service_contract_upgrade_link'      => 'https://studiotempel.nl/servicecontract',
            'support_faq_link'                          => 'https://studiotempel.nl/veelgestelde-vragen',
            'support_ticket_link'                       => 'https://studiotempel.nl/contact',
        );
        update_option('tmpl_widget_settings', $widget_defaults);
    }
}
