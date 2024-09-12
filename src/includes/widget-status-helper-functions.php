<?php
namespace Tempel;

require_once 'helper-functions.php';


function get_backup_interval(): string
{
    $option = return_option('tmpl_widget_settings', 'status_backup_interval');
    
    if (is_wp_error($option) || empty($option)) return '<span class="tmpl_widget__error">' . __('Could not retrieve the last backup date', 'tempel-settings') . '</span>';
    
    $time_now = new \DateTime("now", new \DateTimeZone('Europe/Amsterdam'));
    
    $time_now = strtotime($time_now->format('H:i'));
    $time_last_backup = strtotime($option);
    
    if ($time_last_backup > $time_now) {
        $option = __('Yesterday', 'tempel-settings');
    } else if ($time_last_backup == $time_now) {
        $option = __('Now', 'tempel-settings');
    } else {
        $option = __('Today', 'tempel-settings');
    }
    
    return $option;
}

function get_last_checkup(): array
{
    $lastCheckup = [
        'date' => '',
        'show_link' => false,
        'color' => 'green',
        'error' => ''
    ];
    $option = return_option('tmpl_widget_settings', 'status_last_checkup_date');
    
    if (is_wp_error($option) || empty($option)) return ['error' => '<span class="tmpl_widget__error">' . __('Could not retrieve the last checkup date', 'tempel-settings') . '</span>'];
    
    $lastCheckup['date'] = $option;
    
    $dateLastCheckup = \DateTime::createFromFormat('m/Y', $lastCheckup['date']);
    $now = new \DateTime("now", new \DateTimeZone('Europe/Amsterdam'));
    
    if ($dateLastCheckup->diff($now)->days > 90) {
        $lastCheckup['show_link'] = true;
        $lastCheckup['color'] = 'orange';
    }
    
    if ($dateLastCheckup->diff($now)->days > 180) {
        $lastCheckup['show_link'] = true;
        $lastCheckup['color'] = 'red';
    }
    
    return $lastCheckup;
}

function get_safeupdate_day(): string
{
    $option = return_option('tmpl_widget_settings', 'status_safeupdate_day');
    
    if (is_wp_error($option) || empty($option)) return '<span class="tmpl_widget__error">' . __('Could not retrieve last update date', 'tempel-settings') . '</span>';
    
    $now = new \DateTime("now", new \DateTimeZone('Europe/Amsterdam'));
    $lastUpdate = $now->modify('last ' . $option);
    return $lastUpdate->format('d/m');
}

function show_service_contract_tier(): bool
{
    $option = return_option('tmpl_widget_settings', 'status_show_service_contract_tier');
    
    if (is_wp_error($option) || empty($option)) return false;
    
    return true;
}

function service_contract_upgradable(): bool
{
    $option = return_option('tmpl_widget_settings', 'status_service_contract_upgradable');
    
    if (is_wp_error($option) || empty($option)) return false;
    
    return true;
}

function get_customer_package(): string
{
    $option = return_option('tmpl_widget_settings', 'status_service_contract_tier');
    
    if (is_wp_error($option) || empty($option)) return '<span class="tmpl_widget__error">' . __('Could not retrieve support tier', 'tempel-settings') . '</span>';
    
    $option = ucfirst($option);
    
    return $option;
}

function get_service_contract_upgrade_link(): string
{
    $option = return_option('tmpl_widget_settings', 'status_service_contract_upgrade_link');
    
    if (is_wp_error($option) || empty($option)) return '';
    
    return $option;
}