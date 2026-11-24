<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

final class Optimization_Settings extends Page
{
    public function render(): void
    {
        $pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => array('publish', 'draft', 'pending', 'private'),
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_optimization_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <main class="settings__body">
                        <div class="body__inner tempel-ui-page">
                            <header class="tempel-ui-page-header">
                                <div><h1><?php esc_html_e('Optimalisatie', 'tempel-settings'); ?></h1><p><?php esc_html_e('Scan een bestaande pagina en ontvang een controleerbaar verbeterplan op basis van inhoud, SEO-data en het bedrijfs-DNA.', 'tempel-settings'); ?></p></div>
                            </header>

                            <section class="tempel-ui-card tempel-optimization-start">
                                <div class="tempel-ui-field">
                                    <label for="tempel-optimization-page"><?php esc_html_e('Pagina', 'tempel-settings'); ?></label>
                                    <select id="tempel-optimization-page">
                                        <option value=""><?php esc_html_e('Kies een pagina', 'tempel-settings'); ?></option>
                                        <?php foreach ($pages as $page) : ?>
                                            <?php if (!current_user_can('edit_post', $page->ID)) continue; ?>
                                            <?php $status = get_post_status_object($page->post_status); ?>
                                            <option value="<?php echo esc_attr((string) $page->ID); ?>"><?php echo esc_html($page->post_title !== '' ? $page->post_title : __('(Zonder titel)', 'tempel-settings')); ?><?php echo $page->post_status !== 'publish' && $status ? esc_html(' — ' . $status->label) : ''; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="tempel-ui-field">
                                    <label for="tempel-optimization-focus"><?php esc_html_e('Extra aandachtspunt', 'tempel-settings'); ?></label>
                                    <textarea id="tempel-optimization-focus" rows="2" placeholder="<?php esc_attr_e('Optioneel, bijvoorbeeld: richt de pagina sterker op zakelijke beslissers.', 'tempel-settings'); ?>"></textarea>
                                </div>
                                <div><button type="button" class="button button-primary" id="tempel-optimization-start"><?php esc_html_e('Pagina scannen', 'tempel-settings'); ?></button></div>
                                <p id="tempel-optimization-message" class="tempel-ui-field__help" aria-live="polite"></p>
                                <div id="tempel-optimization-progress" class="tempel-ui-progress" hidden>
                                    <div class="tempel-ui-progress__header"><strong id="tempel-optimization-phase"><?php esc_html_e('Scan voorbereiden', 'tempel-settings'); ?></strong><span id="tempel-optimization-percent">5%</span></div>
                                    <progress max="100" value="5"></progress>
                                    <p><?php esc_html_e('De pagina, ACF-velden, SEO-data en het bedrijfs-DNA worden gecontroleerd.', 'tempel-settings'); ?></p>
                                </div>
                            </section>

                            <section id="tempel-optimization-result" class="tempel-optimization-result" hidden>
                                <header class="tempel-optimization-result__header"><div><h2 id="tempel-optimization-title"><?php esc_html_e('Verbeterplan', 'tempel-settings'); ?></h2><p id="tempel-optimization-verdict"></p></div><a id="tempel-optimization-edit" class="button button-secondary" href="#" hidden><?php esc_html_e('Pagina bewerken', 'tempel-settings'); ?></a></header>
                                <div class="tempel-optimization-grid">
                                    <article class="tempel-ui-card"><h3><?php esc_html_e('Sterke punten', 'tempel-settings'); ?></h3><ul id="tempel-optimization-strengths"></ul></article>
                                    <article class="tempel-ui-card"><h3><?php esc_html_e('Belangrijkste knelpunten', 'tempel-settings'); ?></h3><div id="tempel-optimization-issues" class="tempel-optimization-list"></div></article>
                                </div>
                                <article class="tempel-ui-card"><h3><?php esc_html_e('Concrete verbeteringen', 'tempel-settings'); ?></h3><div id="tempel-optimization-recommendations" class="tempel-optimization-list"></div></article>
                                <article class="tempel-ui-card"><h3><?php esc_html_e('Voorstellen per onderdeel', 'tempel-settings'); ?></h3><div id="tempel-optimization-rewrites" class="tempel-optimization-list"></div></article>
                                <article class="tempel-ui-card tempel-optimization-seo"><h3><?php esc_html_e('SEO-voorstel', 'tempel-settings'); ?></h3><dl id="tempel-optimization-seo"></dl></article>
                            </section>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
    }
}
