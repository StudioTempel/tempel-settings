<?php

namespace Tempel;

//function send_sitescan_email() : void
//{
//    $email = 'job@studiotempel.nl';
//    $site = 'https://www.studiotempel.nl';
//    $name = 'test';
//
//    $subject = 'SiteScan aanvraag';
//    $message = 'Beste ' . $name . ',<br><br>';
//    $message .= 'Sitescan aanvraag van ' . $site . '.<br><br>';
//
//    $headers = array('Content-Type: text/html; charset=UTF-8');
//
//    wp_mail($email, $subject, $message, $headers);
//
//    echo json_encode(
//        array(
//            'status' => 'success',
//            'email' => $email,
//            'site' => $site,
//            'name' => $name
//        )
//    );
//}
//add_action('wp_ajax_send_sitescan_email', 'send_sitescan_email');

function reset_checkup(): void
{
    check_ajax_referer('tmpl_widget_settings_action', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You are not allowed to reset the checkup date.', 'tempel-settings')), 403);
    }

    $option = get_option('tmpl_widget_settings');
    $option = is_array($option) ? $option : array();
    $option['status_last_checkup_date'] = current_time('m/Y');
    
    if (get_option('tmpl_widget_settings') !== false) {
        update_option('tmpl_widget_settings', $option);
    } else {
        add_option('tmpl_widget_settings', $option);
    }
    
    wp_send_json_success(
        array(
            'status_last_checkup_date' => $option['status_last_checkup_date'],
        )
    );
}
add_action('wp_ajax_reset_checkup', __NAMESPACE__ . '\reset_checkup');

function reset_update(): void
{
    check_ajax_referer('tmpl_widget_settings_action', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You are not allowed to reset the update date.', 'tempel-settings')), 403);
    }

    $option = get_option('tempel-widget-settings-data');
    $option = is_array($option) ? $option : array();
    $option['last-update-date'] = current_time('d/m');
    
    if (get_option('tempel-widget-settings-data') !== false) {
        update_option('tempel-widget-settings-data', $option);
    } else {
        add_option('tempel-widget-settings-data', $option);
    }
    
    wp_send_json_success(
        array(
            'last-update-date' => $option['last-update-date'],
        )
    );
}
add_action('wp_ajax_reset_update', __NAMESPACE__ . '\reset_update');

function clear_site_cache(): void
{
    check_ajax_referer('tempel_support_actions', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You are not allowed to clear the site cache.', 'tempel-settings')), 403);
    }

    $cleared = array();

    if (is_breeze_cache_plugin_active()) {
        clear_breeze_cache();
        $cleared[] = 'Breeze';
    }

    if (is_hummingbird_cache_plugin_active()) {
        clear_hummingbird_cache();
        $cleared[] = 'Hummingbird';
    }

    if (empty($cleared)) {
        wp_send_json_error(array('message' => __('No supported cache plugin is active.', 'tempel-settings')), 404);
    }

    wp_send_json_success(
        array(
            'message' => sprintf(
                /* translators: %s: cache plugin names */
                __('Cache cleared for: %s.', 'tempel-settings'),
                implode(', ', $cleared)
            ),
        )
    );
}
add_action('wp_ajax_tempel_clear_site_cache', __NAMESPACE__ . '\clear_site_cache');

function send_test_mail(): void
{
    check_ajax_referer('tempel_support_actions', 'nonce');

    if (!is_user_logged_in() || !current_user_can('read')) {
        wp_send_json_error(array('message' => __('You are not allowed to send a test email.', 'tempel-settings')), 403);
    }

    $user = wp_get_current_user();

    if (!$user || empty($user->user_email) || !is_email($user->user_email)) {
        wp_send_json_error(array('message' => __('Your account has no valid email address.', 'tempel-settings')), 400);
    }

    $subject = sprintf(
        /* translators: %s: site name */
        __('[%s] Test email', 'tempel-settings'),
        wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)
    );
    $message = sprintf(
        /* translators: %s: site URL */
        __('This is a test email from %s. If you received this message, WordPress email sending works.', 'tempel-settings'),
        home_url()
    );

    $sent = wp_mail($user->user_email, $subject, $message);

    if (!$sent) {
        wp_send_json_error(array('message' => __('WordPress could not send the test email.', 'tempel-settings')), 500);
    }

    wp_send_json_success(
        array(
            'message' => sprintf(
                /* translators: %s: email address */
                __('Test email sent to %s.', 'tempel-settings'),
                $user->user_email
            ),
        )
    );
}
add_action('wp_ajax_tempel_send_test_mail', __NAMESPACE__ . '\send_test_mail');

function has_supported_cache_plugin(): bool
{
    return is_breeze_cache_plugin_active() || is_hummingbird_cache_plugin_active();
}

function is_breeze_cache_plugin_active(): bool
{
    return class_exists('Breeze_Admin') || class_exists('Breeze_PurgeCache') || has_action('breeze_clear_all_cache');
}

function clear_breeze_cache(): void
{
    try {
        if (class_exists('Breeze_PurgeCache') && method_exists('Breeze_PurgeCache', 'breeze_cache_flush')) {
            $method = new \ReflectionMethod('Breeze_PurgeCache', 'breeze_cache_flush');

            if ($method->isStatic()) {
                \Breeze_PurgeCache::breeze_cache_flush();
            }
        }

        do_action('breeze_clear_all_cache');
    } catch (\Throwable $exception) {
        do_action('breeze_clear_all_cache');
    }
}

function is_hummingbird_cache_plugin_active(): bool
{
    return defined('WPHB_VERSION') || class_exists('Hummingbird\\WP_Hummingbird') || class_exists('Hummingbird\\Core\\Utils');
}

function clear_hummingbird_cache(): void
{
    try {
        do_action('wphb_clear_page_cache');
        do_action('wphb_clear_cache');

        if (class_exists('Hummingbird\\Core\\Utils') && method_exists('Hummingbird\\Core\\Utils', 'get_module')) {
            $page_cache = \Hummingbird\Core\Utils::get_module('page_cache');

            if (is_object($page_cache) && method_exists($page_cache, 'clear_cache')) {
                $page_cache->clear_cache();
            }
        }
    } catch (\Throwable $exception) {
        do_action('wphb_clear_page_cache');
        do_action('wphb_clear_cache');
    }
}
