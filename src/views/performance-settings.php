<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

class Performance_Settings extends Page
{
    public function render()
    {
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_performance_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner">
                            <form action="options.php" method="post">
                                <?php settings_fields('tempel_settings'); ?>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__label__wrap">
                                            <div class="category__title">
                                                <?php esc_html_e('Performance', 'tempel-settings'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category__content">
                                        <div class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label>
                                                        <?php esc_html_e('Preset', 'tempel-settings'); ?>
                                                        <span class="label__desc"><?php esc_html_e('Vult de velden met een startpunt. Je kunt daarna alles handmatig aanpassen.', 'tempel-settings'); ?></span>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <div class="input__wrap__inner performance-presets">
                                                        <?php foreach ($this->get_presets() as $preset_key => $preset) : ?>
                                                            <button
                                                                type="button"
                                                                class="button performance-preset"
                                                                data-preset="<?php echo esc_attr($preset_key); ?>"
                                                                data-values="<?php echo esc_attr(wp_json_encode($preset['values'])); ?>"
                                                            >
                                                                <?php echo esc_html($preset['label']); ?>
                                                            </button>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="performance_enabled_setting" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="performance_enabled">
                                                        <?php esc_html_e('Performance-maatregelen inschakelen', 'tempel-settings'); ?>
                                                        <span class="label__desc"><?php esc_html_e('Activeert limieten en optimalisaties die CPU- en geheugendruk verlagen.', 'tempel-settings'); ?></span>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label for="performance_enabled" class="checkbox__switch">
                                                        <input
                                                            type="checkbox"
                                                            name="tmpl_settings[performance_enabled]"
                                                            id="performance_enabled"
                                                            <?php echo $this->is_checked('performance_enabled'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <?php $this->render_number_field('performance_frontend_memory_limit', __('Frontend memory limit (MB)', 'tempel-settings'), '64', '1024', $this->get_value('performance_frontend_memory_limit', '128')); ?>
                                        <?php $this->render_number_field('performance_admin_memory_limit', __('Backend memory limit (MB)', 'tempel-settings'), '64', '1024', $this->get_value('performance_admin_memory_limit', '256')); ?>
                                        <?php $this->render_number_field('performance_revision_limit', __('Maximaal aantal revisies per bericht', 'tempel-settings'), '1', '50', $this->get_value('performance_revision_limit', '5')); ?>
                                        <?php $this->render_number_field('performance_heartbeat_interval', __('Heartbeat interval (seconden)', 'tempel-settings'), '15', '120', $this->get_value('performance_heartbeat_interval', '60'), __('Hoger is rustiger voor de server; 60 seconden is meestal prima.', 'tempel-settings')); ?>

                                        <?php $this->render_checkbox_field('performance_disable_heartbeat', __('Heartbeat volledig uitschakelen', 'tempel-settings'), __('Alleen gebruiken op sites waar autosave en realtime locks niet belangrijk zijn.', 'tempel-settings')); ?>
                                        <?php $this->render_checkbox_field('performance_disable_emojis', __('Emoji scripts en styles uitschakelen', 'tempel-settings')); ?>
                                        <?php $this->render_checkbox_field('performance_disable_embeds', __('WordPress embeds uitschakelen', 'tempel-settings')); ?>
                                        <?php $this->render_checkbox_field('performance_disable_xmlrpc', __('XML-RPC en pingbacks uitschakelen', 'tempel-settings')); ?>
                                    </div>
                                </div>

                                <div class="settings__form__footer">
                                    <div class="form__footer__inner">
                                        <?php submit_button(__('Instellingen opslaan', 'tempel-settings')); ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="settings__footer"></div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var buttons = document.querySelectorAll('.performance-preset');

                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        var values = JSON.parse(button.getAttribute('data-values') || '{}');

                        Object.keys(values).forEach(function (key) {
                            var field = document.getElementById(key);

                            if (!field) {
                                return;
                            }

                            if (field.type === 'checkbox') {
                                field.checked = values[key] === 'on';
                                return;
                            }

                            field.value = values[key];
                        });
                    });
                });
            }());
        </script>
        <?php
    }

    private function render_number_field(string $key, string $label, string $min, string $max, string $value, string $description = ''): void
    {
        ?>
        <div id="<?php echo esc_attr($key); ?>_setting" class="settings__field">
            <div class="settings__field__inner">
                <div class="settings__label__wrap">
                    <label for="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($label); ?>
                        <?php if ($description !== '') : ?>
                            <span class="label__desc"><?php echo esc_html($description); ?></span>
                        <?php endif; ?>
                    </label>
                </div>
                <div class="settings__input__wrap">
                    <input
                        type="number"
                        min="<?php echo esc_attr($min); ?>"
                        max="<?php echo esc_attr($max); ?>"
                        name="tmpl_settings[<?php echo esc_attr($key); ?>]"
                        id="<?php echo esc_attr($key); ?>"
                        value="<?php echo esc_attr($value); ?>"
                    >
                </div>
            </div>
        </div>
        <?php
    }

    private function render_checkbox_field(string $key, string $label, string $description = ''): void
    {
        ?>
        <div id="<?php echo esc_attr($key); ?>_setting" class="settings__field">
            <div class="settings__field__inner">
                <div class="settings__label__wrap">
                    <label for="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($label); ?>
                        <?php if ($description !== '') : ?>
                            <span class="label__desc"><?php echo esc_html($description); ?></span>
                        <?php endif; ?>
                    </label>
                </div>
                <div class="settings__input__wrap">
                    <label for="<?php echo esc_attr($key); ?>" class="checkbox__switch">
                        <input
                            type="checkbox"
                            name="tmpl_settings[<?php echo esc_attr($key); ?>]"
                            id="<?php echo esc_attr($key); ?>"
                            <?php echo $this->is_checked($key); ?>
                        >
                        <span class="checkbox__switch__slider"></span>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_presets(): array
    {
        return array(
            'normal' => array(
                'label' => __('Normale site', 'tempel-settings'),
                'values' => array(
                    'performance_enabled' => 'on',
                    'performance_frontend_memory_limit' => '128',
                    'performance_admin_memory_limit' => '256',
                    'performance_revision_limit' => '5',
                    'performance_heartbeat_interval' => '60',
                    'performance_disable_heartbeat' => '',
                    'performance_disable_emojis' => 'on',
                    'performance_disable_embeds' => 'on',
                    'performance_disable_xmlrpc' => 'on',
                ),
            ),
            'webshop' => array(
                'label' => __('Webshop', 'tempel-settings'),
                'values' => array(
                    'performance_enabled' => 'on',
                    'performance_frontend_memory_limit' => '256',
                    'performance_admin_memory_limit' => '512',
                    'performance_revision_limit' => '10',
                    'performance_heartbeat_interval' => '60',
                    'performance_disable_heartbeat' => '',
                    'performance_disable_emojis' => 'on',
                    'performance_disable_embeds' => 'on',
                    'performance_disable_xmlrpc' => 'on',
                ),
            ),
            'heavy' => array(
                'label' => __('Zware website', 'tempel-settings'),
                'values' => array(
                    'performance_enabled' => 'on',
                    'performance_frontend_memory_limit' => '512',
                    'performance_admin_memory_limit' => '768',
                    'performance_revision_limit' => '10',
                    'performance_heartbeat_interval' => '90',
                    'performance_disable_heartbeat' => '',
                    'performance_disable_emojis' => 'on',
                    'performance_disable_embeds' => 'on',
                    'performance_disable_xmlrpc' => 'on',
                ),
            ),
        );
    }

    public function is_checked($args)
    {
        $option = get_option('tmpl_settings');
        $checkbox_value = $option && is_array($option) ? ($option[$args] ?? false) : false;

        return checked('on', $checkbox_value, false);
    }

    public function get_value($key, $default = '')
    {
        $option = get_option('tmpl_settings');

        if (!$option || !is_array($option)) {
            return $default;
        }

        return $option[$key] ?? $default;
    }
}
