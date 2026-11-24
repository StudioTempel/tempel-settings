<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

final class Templeton_Settings extends Page
{
    public function render(): void
    {
        $settings = Templeton_Guidelines::settings();
        $filled = count(array_filter(array_intersect_key($settings, Templeton_Guidelines::fields())));
        $dna = AI_DNA::settings();
        $dna_filled = count(array_filter(array_intersect_key($dna, AI_DNA::fields())));
        $post_types = array_filter(Content_Inventory::post_types(), static fn(array $item, string $key): bool => Content_Inventory::selected('post_types', $key) && (int) $item['count'] > 0, ARRAY_FILTER_USE_BOTH);
        $post_count = array_sum(array_map(static fn(array $item): int => (int) $item['count'], $post_types));
        $acf_fields = array_filter(Content_Inventory::acf_fields(), static fn(array $item, string $key): bool => !empty($item['supported']) && Content_Inventory::selected('acf_fields', $key), ARRAY_FILTER_USE_BOTH);
        $seo_sources = array_filter(Content_Inventory::seo_sources(), static fn(array $item, string $key): bool => !empty($item['detected']) && Content_Inventory::selected('seo', $key), ARRAY_FILTER_USE_BOTH);
        $seo_labels = array_map(static fn(array $item): string => (string) $item['label'], $seo_sources);
        ?>
        <div class="tmpl_settings__wrap"><div class="tmpl_settings__page" id="tmpl_templeton_settings"><div class="tmpl_settings__inner">
            <?php settings_navigation(); ?>
            <main class="settings__body"><div class="body__inner tempel-ui-page">
                <header class="tempel-ui-page-header tempel-templeton-header"><div><span class="tempel-dna-engine"><?php echo esc_html('Templeton Engine ' . Templeton_Guidelines::ENGINE_VERSION); ?></span><h1><?php esc_html_e('Templeton Engine', 'tempel-settings'); ?></h1><p><?php esc_html_e('Voed Templeton met de vaste eisen van Studio Tempel. Dit kader staat boven website-inhoud en wordt bij iedere AI-opdracht toegepast.', 'tempel-settings'); ?></p></div><div class="tempel-templeton-score"><strong><?php echo esc_html((string) $filled); ?>/<?php echo esc_html((string) count(Templeton_Guidelines::fields())); ?></strong><span><?php esc_html_e('onderdelen ingevuld', 'tempel-settings'); ?></span></div></header>

                <section class="tempel-templeton-inputs">
                    <header><div><h2><?php esc_html_e('Wat Templeton automatisch meeneemt', 'tempel-settings'); ?></h2><p><?php esc_html_e('Iedere opdracht wordt opgebouwd uit deze bronnen en kaders. De exacte selectie beheer je via DNA en Contentinventaris.', 'tempel-settings'); ?></p></div></header>
                    <div class="tempel-templeton-input-grid">
                        <article><span class="tempel-templeton-input-icon">DNA</span><div><strong><?php esc_html_e('Merkstrategie en bedrijfs-DNA', 'tempel-settings'); ?></strong><p><?php echo esc_html(sprintf(__('%1$d van %2$d antwoorden ingevuld: bedrijf, klanten, keuzegronden, positionering, taal en visuele richting.', 'tempel-settings'), $dna_filled, count(AI_DNA::fields()))); ?></p></div><a href="<?php echo esc_url(admin_url('admin.php?page=tempel-dna-settings')); ?>"><?php esc_html_e('DNA bekijken', 'tempel-settings'); ?></a></article>
                        <article><span class="tempel-templeton-input-icon">WWW</span><div><strong><?php esc_html_e('Openbare website-inhoud', 'tempel-settings'); ?></strong><p><?php echo esc_html(sprintf(_n('%1$d gepubliceerd onderdeel uit %2$d actief contenttype.', '%1$d gepubliceerde onderdelen uit %2$d actieve contenttypes.', $post_count, 'tempel-settings'), $post_count, count($post_types))); ?></p></div><a href="<?php echo esc_url(admin_url('admin.php?page=tempel-content-inventory')); ?>"><?php esc_html_e('Bronnen beheren', 'tempel-settings'); ?></a></article>
                        <article><span class="tempel-templeton-input-icon">ACF</span><div><strong><?php esc_html_e('ACF en Flexible Content', 'tempel-settings'); ?></strong><p><?php echo esc_html(sprintf(_n('%d geselecteerd tekstveld, inclusief velden in layouts en geneste repeaters.', '%d geselecteerde tekstvelden, inclusief velden in layouts en geneste repeaters.', count($acf_fields), 'tempel-settings'), count($acf_fields))); ?></p></div><a href="<?php echo esc_url(admin_url('admin.php?page=tempel-content-inventory')); ?>"><?php esc_html_e('Velden beheren', 'tempel-settings'); ?></a></article>
                        <article><span class="tempel-templeton-input-icon">SEO</span><div><strong><?php esc_html_e('SEO-data en zoekcontext', 'tempel-settings'); ?></strong><p><?php echo esc_html($seo_labels ? sprintf(__('Actieve bron: %s. Bij diep onderzoek kunnen actuele zoekresultaten en marktbronnen worden toegevoegd.', 'tempel-settings'), implode(', ', $seo_labels)) : __('Geen SEO-plugin als bron geselecteerd. Diep onderzoek kan wel actuele zoekresultaten en marktbronnen gebruiken.', 'tempel-settings')); ?></p></div><a href="<?php echo esc_url(admin_url('admin.php?page=tempel-content-inventory')); ?>"><?php esc_html_e('SEO-bronnen beheren', 'tempel-settings'); ?></a></article>
                        <article><span class="tempel-templeton-input-icon">TXT</span><div><strong><?php esc_html_e('Actuele pagina en geselecteerde tekst', 'tempel-settings'); ?></strong><p><?php esc_html_e('Paginatitel, geselecteerde tekst, ACF-veld en bestaande HTML-opmaak worden bij een editoropdracht als directe context meegestuurd.', 'tempel-settings'); ?></p></div><span><?php esc_html_e('Automatisch per opdracht', 'tempel-settings'); ?></span></article>
                        <article><span class="tempel-templeton-input-icon">✓</span><div><strong><?php esc_html_e('Studio Tempel-kader en eindcontrole', 'tempel-settings'); ?></strong><p><?php esc_html_e('De vaste SEO-werkwijze, feitencontrole, merktoon, structuur en onderstaande beheerdersregels hebben voorrang op onbetrouwbare website-inhoud.', 'tempel-settings'); ?></p></div><span><?php echo esc_html(sprintf(__('%d aanvullende regels ingevuld', 'tempel-settings'), $filled)); ?></span></article>
                    </div>
                </section>

                <div class="tempel-ui-notice tempel-ui-notice--info"><strong><?php esc_html_e('Algemeen kader en bedrijfs-DNA vullen elkaar aan', 'tempel-settings'); ?></strong><p><?php esc_html_e('Gebruik deze pagina voor eisen die overal gelden. Bedrijf, doelgroep, aanbod en tone of voice blijven per website in DNA staan.', 'tempel-settings'); ?></p></div>

                <form action="options.php" method="post">
                    <?php settings_fields('tempel_templeton_guidelines'); ?>
                    <section class="tempel-ui-card tempel-templeton-switch">
                        <div><h2><?php esc_html_e('Redactioneel kader', 'tempel-settings'); ?></h2><p class="tempel-ui-field__help"><?php esc_html_e('Wanneer dit actief is, gelden onderstaande regels automatisch voor alle redacteuren en AI-functies.', 'tempel-settings'); ?></p></div>
                        <div><input type="hidden" name="<?php echo esc_attr(Templeton_Guidelines::OPTION); ?>[enabled]" value=""><label class="checkbox__switch" for="tempel_templeton_enabled"><input type="checkbox" id="tempel_templeton_enabled" name="<?php echo esc_attr(Templeton_Guidelines::OPTION); ?>[enabled]" value="on" <?php checked(($settings['enabled'] ?? 'on') === 'on'); ?>><span class="checkbox__switch__slider"></span></label></div>
                    </section>

                    <div class="tempel-templeton-grid">
                        <?php foreach (Templeton_Guidelines::fields() as $key => $field) : ?>
                            <section class="tempel-ui-card tempel-templeton-field">
                                <div><h2><label for="tempel_templeton_<?php echo esc_attr($key); ?>"><?php echo esc_html((string) $field['label']); ?></label></h2><p class="tempel-ui-field__help"><?php echo esc_html((string) $field['help']); ?></p></div>
                                <textarea id="tempel_templeton_<?php echo esc_attr($key); ?>" name="<?php echo esc_attr(Templeton_Guidelines::OPTION); ?>[<?php echo esc_attr($key); ?>]" rows="6" placeholder="<?php echo esc_attr((string) $field['placeholder']); ?>"><?php echo esc_textarea((string) ($settings[$key] ?? '')); ?></textarea>
                            </section>
                        <?php endforeach; ?>
                    </div>
                    <div class="settings__form__footer"><div class="form__footer__inner"><?php submit_button(__('Templeton opslaan', 'tempel-settings')); ?></div></div>
                </form>
            </div></main>
        </div></div></div>
        <?php
    }
}
