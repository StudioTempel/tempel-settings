<?php

namespace Tempel;

class TempelPluginInstaller
{
    public function __construct()
    {
    
    }
    
    private function addDefaultSettings()
    {
        $default = array(
            'enable_branding'                   => 'on',
            'disable_comments'                  => 'on',
            'disable_default_pt'                => 'off',
            'hide_dashboard_widgets'            => 'on',
            'enable_widgets'                    => 'off',
            'svg_support'                       => 'of',
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
            'support_faq_link'                          => TMPL_DEFAULT_FAQ_LINK,
            'support_ticket_link'                       => TMPL_DEFAULT_CONTACT_LINK,
        );
        update_option('tmpl_widget_settings', $widget_defaults);
        
        register_activation_hook(__FILE__, array("Tempel", 'setup'));
    }
}
