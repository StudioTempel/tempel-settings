<?php

namespace Tempel;

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
        return __('Het aanmaken of promoveren van beheerders is geblokkeerd via Tempel Settings.', 'tempel-settings');
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
