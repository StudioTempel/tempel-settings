<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

final class Content_Inventory_Settings extends Page
{
    public function render(): void
    {
        $post_types = Content_Inventory::post_types();
        $acf_fields = Content_Inventory::acf_fields();
        $seo_sources = Content_Inventory::seo_sources();
        $supported_acf = array_filter($acf_fields, static fn(array $field): bool => $field['supported']);
        ?>
        <div class="tmpl_settings__wrap"><div class="tmpl_settings__page" id="tmpl_content_inventory"><div class="tmpl_settings__inner">
            <?php settings_navigation(); ?>
            <main class="settings__body"><div class="body__inner tempel-ui-page">
                <header class="tempel-ui-page-header"><div><h1><?php esc_html_e('Contentinventaris', 'tempel-settings'); ?></h1><p><?php esc_html_e('Bepaal welke openbare content en metadata Templeton mag gebruiken voor analyses en teksten.', 'tempel-settings'); ?></p></div></header>

                <div class="tempel-content-summary">
                    <div><strong><?php echo esc_html((string) count($post_types)); ?></strong><span><?php esc_html_e('contenttypes', 'tempel-settings'); ?></span></div>
                    <div><strong><?php echo esc_html((string) count($acf_fields)); ?></strong><span><?php esc_html_e('ACF-velden', 'tempel-settings'); ?></span></div>
                    <div><strong><?php echo esc_html((string) count($supported_acf)); ?></strong><span><?php esc_html_e('bruikbare tekstvelden', 'tempel-settings'); ?></span></div>
                    <div><strong><?php echo esc_html((string) count(array_filter($seo_sources, static fn(array $source): bool => $source['detected']))); ?></strong><span><?php esc_html_e('SEO-bronnen gevonden', 'tempel-settings'); ?></span></div>
                </div>

                <form action="options.php" method="post">
                    <?php settings_fields('tempel_content_inventory'); ?>
                    <section class="tempel-ui-card tempel-inventory-section">
                        <div><h2><?php esc_html_e('Contenttypes', 'tempel-settings'); ?></h2><p class="tempel-ui-field__help"><?php esc_html_e('Alleen gepubliceerde inhoud wordt gelezen.', 'tempel-settings'); ?></p></div>
                        <div class="tempel-inventory-grid">
                            <?php foreach ($post_types as $slug => $type) : ?>
                                <?php $this->toggle('post_types', $slug, $type['label'], sprintf(_n('%d gepubliceerd onderdeel', '%d gepubliceerde onderdelen', $type['count'], 'tempel-settings'), $type['count']), true); ?>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="tempel-ui-card tempel-inventory-section">
                        <div><h2><?php esc_html_e('ACF-velden', 'tempel-settings'); ?></h2><p class="tempel-ui-field__help"><?php esc_html_e('Tekstvelden uit gewone groepen, repeaters en Flexible Content. Gevoelige velden blijven altijd uitgesloten.', 'tempel-settings'); ?></p></div>
                        <?php if (!$acf_fields) : ?><p class="tempel-ui-empty"><?php esc_html_e('Geen ACF-velden gevonden.', 'tempel-settings'); ?></p><?php endif; ?>
                        <?php foreach ($this->acf_groups($acf_fields) as $group => $fields) : ?>
                            <details class="tempel-inventory-group" open><summary><strong><?php echo esc_html($group); ?></strong><span><?php echo esc_html(sprintf(_n('%d veld', '%d velden', count($fields), 'tempel-settings'), count($fields))); ?></span></summary><div class="tempel-inventory-grid">
                                <?php foreach ($fields as $key => $field) : ?>
                                    <?php
                                    $detail = $field['sensitive']
                                        ? __('Altijd uitgesloten vanwege mogelijk gevoelige inhoud', 'tempel-settings')
                                        : (!$field['supported'] ? sprintf(__('Veldtype %s wordt niet als tekst gebruikt', 'tempel-settings'), $field['type']) : sprintf(__('Veldtype: %s', 'tempel-settings'), $field['type']));
                                    $this->toggle('acf_fields', $key, $field['label'], $detail, (bool) $field['supported']);
                                    ?>
                                <?php endforeach; ?>
                            </div></details>
                        <?php endforeach; ?>
                    </section>

                    <section class="tempel-ui-card tempel-inventory-section">
                        <div><h2><?php esc_html_e('SEO-data', 'tempel-settings'); ?></h2><p class="tempel-ui-field__help"><?php esc_html_e('SEO-titels, metabeschrijvingen en focuszoekwoorden kunnen extra context geven.', 'tempel-settings'); ?></p></div>
                        <div class="tempel-inventory-grid">
                            <?php foreach ($seo_sources as $key => $source) : ?>
                                <?php $this->toggle('seo', $key, $source['label'], $source['detected'] ? __('Plugin gevonden', 'tempel-settings') : __('Niet gevonden op deze website', 'tempel-settings'), (bool) $source['detected']); ?>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <div class="settings__form__footer"><div class="form__footer__inner"><?php submit_button(__('Inventaris opslaan', 'tempel-settings')); ?></div></div>
                </form>
            </div></main>
        </div></div></div>
        <?php
    }

    private function toggle(string $section, string $key, string $label, string $detail, bool $available): void
    {
        $id = 'tempel_inventory_' . sanitize_html_class($section . '_' . $key);
        ?>
        <div class="tempel-inventory-item<?php echo !$available ? ' is-disabled' : ''; ?>">
            <div><strong><?php echo esc_html($label); ?></strong><span><?php echo esc_html($detail); ?></span></div>
            <input type="hidden" name="<?php echo esc_attr(Content_Inventory::OPTION); ?>[<?php echo esc_attr($section); ?>][<?php echo esc_attr($key); ?>]" value="">
            <label class="checkbox__switch" for="<?php echo esc_attr($id); ?>"><input type="checkbox" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr(Content_Inventory::OPTION); ?>[<?php echo esc_attr($section); ?>][<?php echo esc_attr($key); ?>]" value="on" <?php checked(Content_Inventory::selected($section, $key, $available)); ?> <?php disabled(!$available); ?>><span class="checkbox__switch__slider"></span></label>
        </div>
        <?php
    }

    private function acf_groups(array $fields): array
    {
        $groups = array();
        foreach ($fields as $key => $field) $groups[$field['group']][$key] = $field;
        return $groups;
    }
}
