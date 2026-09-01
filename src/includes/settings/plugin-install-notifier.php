<?php

namespace Tempel;

class Plugin_Install_Notifier
{
    private const RECIPIENT = 'info@studiotempel.nl';
    private array $pending_administrators = array();
    private array $new_users = array();

    public function __construct()
    {
        add_action('upgrader_process_complete', array($this, 'notify'), 10, 2);
        add_action('user_register', array($this, 'queue_new_user'), 20, 1);
        add_action('set_user_role', array($this, 'queue_role_change'), 20, 3);
        add_action('add_user_role', array($this, 'queue_added_role'), 20, 2);
        add_action('shutdown', array($this, 'send_administrator_notifications'));
    }

    public function notify($upgrader, $options): void
    {
        if (($options['type'] ?? '') !== 'plugin' || ($options['action'] ?? '') !== 'install') {
            return;
        }

        $plugins = array_filter(array_map('sanitize_text_field', (array) ($options['plugins'] ?? array())));
        if (!$plugins && !empty($options['plugin'])) {
            $plugins[] = sanitize_text_field($options['plugin']);
        }
        if (!$plugins && is_object($upgrader) && method_exists($upgrader, 'plugin_info')) {
            $plugin = $upgrader->plugin_info();
            if (is_string($plugin) && $plugin !== '') {
                $plugins[] = sanitize_text_field($plugin);
            }
        }

        $user = wp_get_current_user();
        $subject = sprintf('[%s] Plugin geïnstalleerd', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
        $message = implode("\n", array(
            'Website: ' . home_url('/'),
            'Plugin: ' . ($plugins ? implode(', ', $plugins) : 'Onbekend'),
            'Gebruiker: ' . ($user->exists() ? $user->user_login . ' (ID ' . $user->ID . ')' : 'Onbekend/geautomatiseerd'),
            'Tijdstip: ' . wp_date('d-m-Y H:i:s'),
        ));

        wp_mail(self::RECIPIENT, $subject, $message);
    }

    public function queue_new_user(int $user_id): void
    {
        $this->new_users[$user_id] = true;
        $user = get_userdata($user_id);
        if ($user && in_array('administrator', (array) $user->roles, true)) {
            $this->pending_administrators[$user_id] = true;
        }
    }

    public function queue_role_change(int $user_id, string $role, array $old_roles): void
    {
        if ($role === 'administrator' && !in_array('administrator', $old_roles, true)) {
            $this->pending_administrators[$user_id] = true;
        }
    }

    public function queue_added_role(int $user_id, string $role): void
    {
        if ($role === 'administrator') {
            $this->pending_administrators[$user_id] = true;
        }
    }

    public function send_administrator_notifications(): void
    {
        foreach (array_keys($this->pending_administrators) as $user_id) {
            $user = get_userdata($user_id);
            if (!$user || !in_array('administrator', (array) $user->roles, true)) {
                continue;
            }

            $is_new = isset($this->new_users[$user_id]);
            $actor = wp_get_current_user();
            $action = $is_new ? 'Nieuwe beheerder aangemaakt' : 'Gebruiker gepromoveerd naar beheerder';
            $subject = sprintf('[%s] %s', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES), $action);
            $message = implode("\n", array(
                'Website: ' . home_url('/'),
                'Actie: ' . $action,
                'Gebruiker: ' . $user->user_login . ' (ID ' . $user->ID . ')',
                'E-mailadres: ' . $user->user_email,
                'Uitgevoerd door: ' . ($actor->exists() ? $actor->user_login . ' (ID ' . $actor->ID . ')' : 'Onbekend/geautomatiseerd'),
                'Tijdstip: ' . wp_date('d-m-Y H:i:s'),
            ));

            wp_mail(self::RECIPIENT, $subject, $message);
        }

        $this->pending_administrators = array();
        $this->new_users = array();
    }
}
