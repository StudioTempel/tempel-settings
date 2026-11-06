<?php

namespace Tempel\Core;

/**
 * Single access point for persisted plugin settings.
 *
 * Legacy option names remain supported so existing installations keep all
 * values while the internal architecture moves to the Tempel Settings 3 API.
 */
final class SettingsRepository
{
    public const GENERAL = 'tmpl_settings';
    public const WIDGETS = 'tmpl_widget_settings';
    public const MAIL = 'tmpl_mail_settings';

    public function all(string $group): array
    {
        $value = get_option($group, array());
        return is_array($value) ? $value : array();
    }

    public function get(string $group, string $key, $default = false)
    {
        $settings = $this->all($group);
        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public function enabled(string $group, string $key): bool
    {
        return $this->get($group, $key, '') === 'on';
    }

    public function update(string $group, array $settings): bool
    {
        return update_option($group, $settings, false);
    }

    public function merge(string $group, array $changes): bool
    {
        return $this->update($group, array_replace($this->all($group), $changes));
    }
}
