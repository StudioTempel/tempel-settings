<?php

namespace Tempel;

class Security_Lock_Auto_Reenable
{
    public const EVENT = 'tempel_security_lock_auto_reenable';
    public const DISABLED_UNTIL_OPTION = 'tempel_security_lock_disabled_until';

    public function __construct()
    {
        add_action('update_option_tmpl_settings', array($this, 'settings_updated'), 10, 3);
        add_action(self::EVENT, array($this, 'reenable'));

        $this->check_expiry();
    }

    public function settings_updated($old_value, $value, $option = ''): void
    {
        $old_value = is_array($old_value) ? $old_value : array();
        $value = is_array($value) ? $value : array();
        $auto_reenable = ($value['security_lock_auto_reenable'] ?? '') === 'on';
        $locked = ($value['security_lock'] ?? '') === 'on';

        if (!$auto_reenable || $locked) {
            $this->cancel();
            return;
        }

        $was_locked = ($old_value['security_lock'] ?? '') === 'on';
        $existing_expiry = (int) get_option(self::DISABLED_UNTIL_OPTION, 0);

        // Start a fresh hour only when the lock was just disabled or no timer exists.
        if ($was_locked || $existing_expiry <= time()) {
            $this->schedule(time() + HOUR_IN_SECONDS);
        }
    }

    public function check_expiry(): void
    {
        $settings = get_option('tmpl_settings', array());
        $settings = is_array($settings) ? $settings : array();

        if (($settings['security_lock_auto_reenable'] ?? '') !== 'on' || ($settings['security_lock'] ?? '') === 'on') {
            $this->cancel();
            return;
        }

        $expiry = (int) get_option(self::DISABLED_UNTIL_OPTION, 0);
        if ($expiry > 0 && $expiry <= time()) {
            $this->reenable();
            return;
        }

        if ($expiry <= 0) {
            $expiry = time() + HOUR_IN_SECONDS;
            $this->schedule($expiry);
            return;
        }

        $this->ensure_event($expiry);
    }

    public function reenable(): void
    {
        $settings = get_option('tmpl_settings', array());
        $settings = is_array($settings) ? $settings : array();

        if (($settings['security_lock_auto_reenable'] ?? '') === 'on' && ($settings['security_lock'] ?? '') !== 'on') {
            $settings['security_lock'] = 'on';
            update_option('tmpl_settings', $settings);
        }

        $this->cancel();
    }

    private function schedule(int $timestamp): void
    {
        update_option(self::DISABLED_UNTIL_OPTION, $timestamp, false);
        $this->clear_event();
        wp_schedule_single_event($timestamp, self::EVENT);
    }

    private function ensure_event(int $timestamp): void
    {
        if (!wp_next_scheduled(self::EVENT)) {
            wp_schedule_single_event($timestamp, self::EVENT);
        }
    }

    private function cancel(): void
    {
        delete_option(self::DISABLED_UNTIL_OPTION);
        $this->clear_event();
    }

    private function clear_event(): void
    {
        $timestamp = wp_next_scheduled(self::EVENT);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::EVENT);
        }
    }
}

class Security_Lock
{
    public function __construct()
    {
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }

        add_filter('map_meta_cap', array($this, 'restrict_plugin_installation'), 100, 2);
        add_filter('editable_roles', array($this, 'filter_editable_roles'));
        add_action('user_profile_update_errors', array($this, 'validate_user'), 10, 3);
        add_filter('rest_pre_insert_user', array($this, 'validate_rest_user'), 10, 2);
        // Also cover programmatic role assignment and default administrator roles.
        add_filter('add_user_metadata', array($this, 'prevent_administrator_assignment'), 10, 5);
        add_filter('update_user_metadata', array($this, 'prevent_administrator_assignment'), 10, 5);
    }

    public function restrict_plugin_installation($caps, $cap): array
    {
        if (in_array($cap, array('install_plugins', 'upload_plugins'), true)) {
            return array('do_not_allow');
        }

        return $caps;
    }

    private function is_administrator($user_id): bool
    {
        $user = $user_id ? get_userdata($user_id) : false;
        return $user && in_array('administrator', $user->roles, true);
    }

    public function filter_editable_roles($roles): array
    {
        global $pagenow;

        // Keep the current role selectable when editing an existing administrator.
        $user_id = 0;
        if ($pagenow === 'profile.php') {
            $user_id = get_current_user_id();
        } elseif ($pagenow === 'user-edit.php') {
            $user_id = isset($_REQUEST['user_id']) ? absint($_REQUEST['user_id']) : 0;
        }

        if (!$this->is_administrator($user_id)) {
            unset($roles['administrator']);
        }

        return $roles;
    }

    private function error_message(): string
    {
        return __('Het aanmaken of promoveren van beheerders is geblokkeerd via Tempel.', 'tempel-settings');
    }

    public function validate_user($errors, $update, $user): void
    {
        $role = $user->role ?? ($update ? '' : get_option('default_role', 'subscriber'));
        if ($role === 'administrator' && (!$update || !$this->is_administrator($user->ID ?? 0))) {
            $errors->add('tempel_administrator_locked', $this->error_message());
        }
    }

    public function validate_rest_user($prepared_user, $request)
    {
        if (is_wp_error($prepared_user)) {
            return $prepared_user;
        }

        $user_id = (int) ($request['id'] ?? 0);
        $roles = $request['roles'] ?? ($user_id ? array() : array(get_option('default_role', 'subscriber')));
        if (in_array('administrator', (array) $roles, true) && !$this->is_administrator($user_id)) {
            return new \WP_Error('tempel_administrator_locked', $this->error_message(), array('status' => 403));
        }

        return $prepared_user;
    }

    public function prevent_administrator_assignment($check, $user_id, $meta_key, $meta_value, $previous)
    {
        global $wpdb;

        // Only protect the current site's roles; never change existing administrators.
        if ($meta_key !== $wpdb->get_blog_prefix() . 'capabilities' || !is_array($meta_value)) {
            return $check;
        }

        $current = get_user_meta($user_id, $meta_key, true);
        if (!empty($meta_value['administrator']) && empty($current['administrator'])) {
            return false;
        }

        return $check;
    }
}
