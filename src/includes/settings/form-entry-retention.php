<?php

namespace Tempel;

class Form_Entry_Retention
{
    public const CRON_HOOK = 'tempel_cleanup_form_entries';
    public const MANUAL_ACTION = 'tempel_cleanup_form_entries_now';
    private const LAST_RUN_OPTION = 'tempel_form_entry_retention_last_run';
    private const BATCH_SIZE = 500;

    public function __construct()
    {
        $this->schedule();
        add_action('admin_init', array($this, 'maybe_cleanup'));
        add_action(self::CRON_HOOK, array($this, 'cleanup'));
        add_action('admin_post_' . self::MANUAL_ACTION, array($this, 'manual_cleanup'));
    }

    public function schedule(): void
    {
        if (!self::is_enabled()) {
            if (wp_next_scheduled(self::CRON_HOOK)) {
                wp_clear_scheduled_hook(self::CRON_HOOK);
            }
            return;
        }

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', self::CRON_HOOK);
        }
    }

    public static function is_enabled(): bool
    {
        return return_option('tmpl_settings', 'form_entry_retention_enabled') === 'on';
    }

    public static function get_days(): int
    {
        return max(1, min(3650, absint(return_option('tmpl_settings', 'form_entry_retention_days') ?: 365)));
    }

    public function maybe_cleanup(): void
    {
        if (!self::is_enabled() || !class_exists('GFAPI')) {
            return;
        }

        $last_run = absint(get_option(self::LAST_RUN_OPTION, 0));
        if ($last_run === 0 || $last_run <= time() - HOUR_IN_SECONDS) {
            $this->cleanup();
        }
    }

    public function cleanup(): int
    {
        if (!self::is_enabled() || !class_exists('GFAPI')) {
            return 0;
        }

        // Gravity Forms treats end_date as inclusive. Subtract one second so an
        // entry exactly on the retention boundary is kept and only older
        // entries are permanently deleted.
        $cutoff_timestamp = current_time('timestamp', true) - (self::get_days() * DAY_IN_SECONDS) - 1;
        $cutoff = wp_date('Y-m-d H:i:s', $cutoff_timestamp, new \DateTimeZone('UTC'));
        $remaining = self::BATCH_SIZE;
        $deleted = 0;

        foreach (array('active', 'spam', 'trash') as $status) {
            if ($remaining <= 0) {
                break;
            }

            $entries = \GFAPI::get_entries(
                0,
                array('status' => $status, 'end_date' => $cutoff),
                array('key' => 'date_created', 'direction' => 'ASC'),
                array('offset' => 0, 'page_size' => $remaining)
            );

            if (is_wp_error($entries) || !is_array($entries)) {
                continue;
            }

            foreach ($entries as $entry) {
                $entry_id = absint($entry['id'] ?? 0);
                if ($entry_id) {
                    --$remaining;
                    if (\GFAPI::delete_entry($entry_id)) {
                        ++$deleted;
                    }
                }
            }
        }

        update_option(self::LAST_RUN_OPTION, time(), false);
        return $deleted;
    }

    public function manual_cleanup(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Je hebt geen rechten om formulierinzendingen op te schonen.', 'tempel-settings'));
        }

        check_admin_referer(self::MANUAL_ACTION, 'tempel_cleanup_nonce');
        $deleted = $this->cleanup();
        $url = add_query_arg(
            array(
                'page' => 'tempel-gform-address-settings',
                'tempel_cleanup_done' => '1',
                'tempel_cleanup_deleted' => $deleted,
            ),
            admin_url('admin.php')
        );
        wp_safe_redirect($url);
        exit;
    }
}
