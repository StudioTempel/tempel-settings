<?php

namespace Tempel;

class Performance
{
    public function __construct()
    {
        add_action('init', array($this, 'apply_memory_limit'), 1);
        add_filter('admin_memory_limit', array($this, 'filter_admin_memory_limit'));
        add_filter('wp_revisions_to_keep', array($this, 'limit_revisions'), 10, 2);
        add_filter('heartbeat_settings', array($this, 'filter_heartbeat_settings'));

        if (sanitize_checkbox_value(return_option('tmpl_settings', 'performance_disable_heartbeat'))) {
            add_action('admin_enqueue_scripts', array($this, 'disable_heartbeat'), 1);
            add_action('wp_enqueue_scripts', array($this, 'disable_heartbeat'), 1);
        }

        if (sanitize_checkbox_value(return_option('tmpl_settings', 'performance_disable_emojis'))) {
            add_action('init', array($this, 'disable_emojis'));
        }

        if (sanitize_checkbox_value(return_option('tmpl_settings', 'performance_disable_embeds'))) {
            add_action('init', array($this, 'disable_embeds'), 9999);
        }

        if (sanitize_checkbox_value(return_option('tmpl_settings', 'performance_disable_xmlrpc'))) {
            add_filter('xmlrpc_enabled', '__return_false');
            add_filter('wp_headers', array($this, 'remove_pingback_header'));
            add_filter('pings_open', '__return_false', 20, 2);
        }
    }

    public function apply_memory_limit(): void
    {
        $limit = is_admin()
            ? $this->get_memory_limit('performance_admin_memory_limit', '256')
            : $this->get_memory_limit('performance_frontend_memory_limit', '128');

        if ($limit) {
            @ini_set('memory_limit', $limit . 'M');
        }
    }

    public function filter_admin_memory_limit($limit)
    {
        $admin_limit = $this->get_memory_limit('performance_admin_memory_limit', '');

        return $admin_limit ? $admin_limit . 'M' : $limit;
    }

    public function limit_revisions($num, $post)
    {
        $limit = absint(return_option('tmpl_settings', 'performance_revision_limit'));

        return $limit > 0 ? $limit : $num;
    }

    public function filter_heartbeat_settings($settings): array
    {
        $interval = absint(return_option('tmpl_settings', 'performance_heartbeat_interval'));

        if ($interval > 0) {
            $settings['interval'] = max(15, min(120, $interval));
        }

        return $settings;
    }

    public function disable_heartbeat(): void
    {
        wp_deregister_script('heartbeat');
    }

    public function disable_emojis(): void
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    }

    public function disable_embeds(): void
    {
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
        remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
        wp_deregister_script('wp-embed');
    }

    public function remove_pingback_header($headers): array
    {
        if (isset($headers['X-Pingback'])) {
            unset($headers['X-Pingback']);
        }

        return $headers;
    }

    private function get_memory_limit(string $key, string $default): string
    {
        $limit = absint(return_option('tmpl_settings', $key));

        if (!$limit && $default !== '') {
            $limit = absint($default);
        }

        if (!$limit) {
            return '';
        }

        return (string) max(64, min(1024, $limit));
    }
}
