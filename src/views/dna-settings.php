<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

final class DNA_Settings extends Page
{
    public function render(): void
    {
        $settings = AI_DNA::settings();
        $proposal = AI_DNA::proposal();
        $inventory_post_types = array_filter(Content_Inventory::post_types(), static fn(array $item, string $key): bool => Content_Inventory::selected('post_types', $key) && (int) $item['count'] > 0, ARRAY_FILTER_USE_BOTH);
        $inventory_acf = array_filter(Content_Inventory::acf_fields(), static fn(array $item, string $key): bool => !empty($item['supported']) && Content_Inventory::selected('acf_fields', $key), ARRAY_FILTER_USE_BOTH);
        $inventory_seo = array_filter(Content_Inventory::seo_sources(), static fn(array $item, string $key): bool => !empty($item['detected']) && Content_Inventory::selected('seo', $key), ARRAY_FILTER_USE_BOTH);
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_dna_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <main class="settings__body">
                        <div class="body__inner tempel-ui-page">
                            <header class="tempel-ui-page-header">
                                <div>
                                    <h1><?php esc_html_e('DNA', 'tempel-settings'); ?></h1>
                                    <p><?php esc_html_e('Leg vast hoe dit bedrijf denkt, klinkt en zich onderscheidt. Tempel AI gebruikt dit profiel bij iedere opdracht.', 'tempel-settings'); ?></p>
                                </div>
                            </header>

                            <details class="tempel-ui-card tempel-dna-generator">
                                <summary class="tempel-dna-generator__summary"><span><strong><?php esc_html_e('DNA-voorstel maken', 'tempel-settings'); ?></strong><small><?php esc_html_e('Analyseer de website en maak een voorstel voor alle hoofdstukken.', 'tempel-settings'); ?></small></span></summary>
                                <div class="tempel-dna-generator__content">
                                <div class="tempel-dna-inventory-summary">
                                    <span><?php echo esc_html(sprintf(__('%1$d contenttypes · %2$d websitevelden · %3$d SEO-bronnen', 'tempel-settings'), count($inventory_post_types), count($inventory_acf), count($inventory_seo))); ?></span>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=tempel-content-inventory')); ?>"><?php esc_html_e('Bronnen beheren', 'tempel-settings'); ?></a>
                                </div>
                                <div class="tempel-ui-field">
                                    <label for="tempel-dna-direction"><?php esc_html_e('Extra richting', 'tempel-settings'); ?></label>
                                    <textarea id="tempel-dna-direction" rows="3" placeholder="<?php esc_attr_e('Bijvoorbeeld: richt je vooral op zakelijke beslissers in Noord-Holland.', 'tempel-settings'); ?>"></textarea>
                                    <span class="tempel-ui-field__help"><?php esc_html_e('Optioneel. Gebruik dit wanneer de gewenste doelgroep nog niet duidelijk op de website staat.', 'tempel-settings'); ?></span>
                                </div>
                                <div class="tempel-dna-generator__options">
                                    <label><span><?php esc_html_e('Bestaand DNA', 'tempel-settings'); ?></span><select id="tempel-dna-merge-mode"><option value="augment"><?php esc_html_e('Aanvullen', 'tempel-settings'); ?></option><option value="overwrite"><?php esc_html_e('Nieuw voorstel maken', 'tempel-settings'); ?></option></select></label>
                                    <label class="tempel-dna-trends"><input type="checkbox" id="tempel-dna-use-trends" checked> <?php esc_html_e('Actuele trends meenemen', 'tempel-settings'); ?></label>
                                </div>
                                <div><button type="button" class="button button-primary" id="tempel-dna-generate"><?php esc_html_e('Voorstel maken', 'tempel-settings'); ?></button></div>
                                <p id="tempel-dna-status" class="tempel-ui-field__help" aria-live="polite"></p>
                                <div id="tempel-dna-progress" class="tempel-ui-progress tempel-dna-progress" hidden>
                                    <span class="tempel-dna-engine"><?php echo esc_html('Templeton Engine ' . Templeton_Guidelines::ENGINE_VERSION); ?></span>
                                    <div class="tempel-ui-progress__header"><strong class="tempel-ui-progress__phase"><?php esc_html_e('Onderzoek voorbereiden', 'tempel-settings'); ?></strong><span class="tempel-ui-progress__percent"></span></div>
                                    <progress max="100" value="5"></progress>
                                    <p class="tempel-ui-progress__message"><?php esc_html_e('Diep onderzoek kan enkele minuten duren. Je kunt deze pagina open laten staan.', 'tempel-settings'); ?></p>
                                    <ol class="tempel-dna-activity" aria-label="<?php esc_attr_e('Onderdelen van het DNA-onderzoek', 'tempel-settings'); ?>">
                                        <li data-step="profile"><?php esc_html_e('Websiteprofiel en basisgegevens', 'tempel-settings'); ?></li>
                                        <li data-step="content"><?php esc_html_e('Pagina’s, berichten en publieke contenttypes', 'tempel-settings'); ?></li>
                                        <li data-step="acf"><?php esc_html_e('ACF-velden en Flexible Content', 'tempel-settings'); ?></li>
                                        <li data-step="topics"><?php esc_html_e('Categorieën, tags en terugkerende thema’s', 'tempel-settings'); ?></li>
                                        <li data-step="audience"><?php esc_html_e('Doelgroep- en klantbehoeftesignalen', 'tempel-settings'); ?></li>
                                        <li data-step="market"><?php esc_html_e('Marktontwikkelingen en actuele bronnen', 'tempel-settings'); ?></li>
                                        <li data-step="positioning"><?php esc_html_e('Positionering, toon en onderscheidend vermogen', 'tempel-settings'); ?></li>
                                        <li data-step="compose"><?php esc_html_e('DNA-voorstel en tags samenstellen', 'tempel-settings'); ?></li>
                                    </ol>
                                </div>
                                <div id="tempel-dna-sources" class="tempel-dna-sources" hidden></div>
                                </div>
                            </details>

                            <form action="options.php" method="post">
                                <?php settings_fields('tempel_dna_settings'); ?>
                                <div id="tempel-dna-proposal-values" hidden>
                                    <?php foreach (AI_DNA::output_keys() as $proposal_key) : ?>
                                        <input type="hidden" id="tempel_dna_proposal_<?php echo esc_attr($proposal_key); ?>" name="<?php echo esc_attr(AI_DNA::PROPOSAL_OPTION); ?>[<?php echo esc_attr($proposal_key); ?>]" value="<?php echo esc_attr((string) ($proposal[$proposal_key] ?? '')); ?>">
                                    <?php endforeach; ?>
                                </div>
                                <section id="tempel-dna-proposal" class="tempel-ui-card tempel-dna-proposal" hidden>
                                    <header class="tempel-dna-proposal__header"><div><span class="tempel-dna-engine"><?php esc_html_e('Voorstel', 'tempel-settings'); ?></span><h2><?php esc_html_e('Controleer voordat je iets overneemt', 'tempel-settings'); ?></h2><p><?php esc_html_e('Het huidige DNA blijft ongewijzigd totdat je een onderdeel bewust overneemt. Zo kun je dit veilig voor bestaande klanten uitrollen.', 'tempel-settings'); ?></p></div><span id="tempel-dna-proposal-count" class="tempel-dna-proposal__count"></span></header>
                                    <div class="tempel-dna-proposal__actions">
                                        <button type="button" class="button button-primary" id="tempel-dna-accept-empty"><?php esc_html_e('Alleen lege velden vullen', 'tempel-settings'); ?></button>
                                        <button type="button" class="button button-secondary" id="tempel-dna-accept-all"><?php esc_html_e('Alles overnemen', 'tempel-settings'); ?></button>
                                        <button type="button" class="button button-link-delete" id="tempel-dna-discard-all"><?php esc_html_e('Voorstel verwijderen', 'tempel-settings'); ?></button>
                                    </div>
                                    <div id="tempel-dna-proposal-list" class="tempel-dna-proposal__list"></div>
                                </section>
                                <header class="tempel-dna-profile-header">
                                    <div><h2><?php esc_html_e('Bedrijfs-DNA', 'tempel-settings'); ?></h2></div>
                                </header>
                                <?php foreach (AI_DNA::groups() as $group_key => $group) : ?>
                                <?php
                                $group_fields = array_filter(AI_DNA::fields(), static fn(array $field): bool => ($field['group'] ?? '') === $group_key);
                                $group_filled = count(array_filter(array_keys($group_fields), static fn(string $key): bool => trim((string) ($settings[$key] ?? '')) !== ''));
                                $group_reviewed = count(array_filter(array_keys($group_fields), static fn(string $key): bool => trim((string) ($settings[$key] ?? '')) !== '' && !empty($settings[AI_DNA::reviewed_key($key)])));
                                $group_ai_only = max(0, $group_filled - $group_reviewed);
                                ?>
                                <details class="tempel-ui-card tempel-dna-fields tempel-dna-group" data-dna-group="<?php echo esc_attr($group_key); ?>">
                                    <summary class="tempel-dna-group__summary">
                                        <span class="tempel-dna-group__number"><?php echo esc_html((string) $group['number']); ?></span>
                                        <span class="tempel-dna-group__title"><strong><?php echo esc_html((string) $group['label']); ?></strong><small><?php echo esc_html((string) $group['help']); ?></small></span>
                                        <span class="tempel-dna-group__progress"><span data-dna-filled><?php echo esc_html(sprintf(__('%1$d van %2$d ingevuld', 'tempel-settings'), $group_filled, count($group_fields))); ?></span><small data-dna-review-status><?php echo esc_html(sprintf(__('%1$d gecontroleerd · %2$d AI', 'tempel-settings'), $group_reviewed, $group_ai_only)); ?></small></span>
                                    </summary>
                                    <div class="tempel-dna-group__content">
                                    <?php foreach (AI_DNA::fields() as $key => $field) : if (($field['group'] ?? '') !== $group_key) continue; ?>
                                        <div class="settings__field tempel-ai-dna-field" data-dna-field="<?php echo esc_attr($key); ?>"><div class="settings__field__inner">
                                            <div class="settings__label__wrap tempel-dna-field-heading"><label for="tempel_dna_<?php echo esc_attr($key); ?>"><?php echo esc_html((string) $field['label']); ?><span class="label__desc"><?php echo esc_html((string) $field['help']); ?></span></label><div class="tempel-dna-field-actions"><label class="tempel-dna-reviewed"><input type="checkbox" name="<?php echo esc_attr(AI_DNA::OPTION); ?>[<?php echo esc_attr(AI_DNA::reviewed_key($key)); ?>]" value="1" <?php checked(!empty($settings[AI_DNA::reviewed_key($key)])); ?>><span><?php esc_html_e('Gecontroleerd', 'tempel-settings'); ?></span></label><button type="button" class="button-link tempel-dna-update-toggle" data-dna-key="<?php echo esc_attr($key); ?>"><?php esc_html_e('Bijwerken', 'tempel-settings'); ?></button></div></div>
                                            <div class="tempel-dna-update-panel" data-dna-panel="<?php echo esc_attr($key); ?>" hidden>
                                                <label for="tempel_dna_change_<?php echo esc_attr($key); ?>"><?php esc_html_e('Wat is er gewijzigd?', 'tempel-settings'); ?></label>
                                                <textarea id="tempel_dna_change_<?php echo esc_attr($key); ?>" rows="2" placeholder="<?php esc_attr_e('Bijvoorbeeld: we richten ons voortaan ook op zorgorganisaties.', 'tempel-settings'); ?>"></textarea>
                                                <button type="button" class="button button-primary tempel-dna-update-run" data-dna-key="<?php echo esc_attr($key); ?>"><?php esc_html_e('Bijwerken uitvoeren', 'tempel-settings'); ?></button>
                                            </div>
                                            <div class="settings__input__wrap tempel-dna-field-controls">
                                                <?php $tags_key = AI_DNA::tags_key($key); ?>
                                                <label class="tempel-dna-tags" for="tempel_dna_<?php echo esc_attr($tags_key); ?>">
                                                    <span><?php esc_html_e('Tags', 'tempel-settings'); ?></span>
                                                    <input type="text" id="tempel_dna_<?php echo esc_attr($tags_key); ?>" name="<?php echo esc_attr(AI_DNA::OPTION); ?>[<?php echo esc_attr($tags_key); ?>]" value="<?php echo esc_attr((string) ($settings[$tags_key] ?? '')); ?>" placeholder="<?php esc_attr_e('bijvoorbeeld: nuchter, mkb, zekerheid, Noord-Holland', 'tempel-settings'); ?>">
                                                </label>
                                                <div class="tempel-dna-text"><span><?php esc_html_e('Tekst', 'tempel-settings'); ?></span><textarea id="tempel_dna_<?php echo esc_attr($key); ?>" name="<?php echo esc_attr(AI_DNA::OPTION); ?>[<?php echo esc_attr($key); ?>]" rows="<?php echo esc_attr((string) $field['rows']); ?>"><?php echo esc_textarea((string) ($settings[$key] ?? '')); ?></textarea></div>
                                            </div>
                                        </div></div>
                                    <?php endforeach; ?>
                                    </div>
                                </details>
                                <?php endforeach; ?>
                                <div class="settings__form__footer"><div class="form__footer__inner"><?php submit_button(__('DNA opslaan', 'tempel-settings')); ?></div></div>
                            </form>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
    }
}
