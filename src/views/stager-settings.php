<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

final class Stager_Settings extends Page
{
    public function render()
    {
        $issues = Stager::preflight_issues();
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_stager_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner tempel-stager tempel-ui-page">
                            <?php foreach ($issues as $issue) : ?>
                                <div class="notice notice-error inline"><p><?php echo esc_html($issue); ?></p></div>
                            <?php endforeach; ?>

                            <section class="tempel-stager-card">
                                <h2><?php esc_html_e('Verbind met nieuwe website', 'tempel-settings'); ?></h2>
                                <p><?php esc_html_e('Er moet al een werkende WordPress-installatie op de doelserver staan. De SFTP-gegevens worden niet opgeslagen.', 'tempel-settings'); ?></p>

                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" autocomplete="off">
                                    <input type="hidden" name="action" value="tempel_stager_deploy">
                                    <?php wp_nonce_field('tempel_stager_deploy'); ?>
                                    <div class="tempel-stager-fields">
                                        <div class="tempel-stager-field-row tempel-stager-field-row--host">
                                            <div>
                                                <label for="stager-host"><?php esc_html_e('SFTP-host', 'tempel-settings'); ?></label>
                                                <input id="stager-host" type="text" name="sftp[host]" placeholder="sftp.jouwhosting.nl" required>
                                            </div>
                                            <div>
                                                <label for="stager-port"><?php esc_html_e('SFTP-poort', 'tempel-settings'); ?></label>
                                                <input id="stager-port" type="number" name="sftp[port]" value="22" min="1" max="65535" required>
                                            </div>
                                        </div>
                                        <div class="tempel-stager-field-row">
                                            <div>
                                                <label for="stager-user"><?php esc_html_e('Gebruikersnaam', 'tempel-settings'); ?></label>
                                                <input id="stager-user" type="text" name="sftp[username]" required>
                                            </div>
                                            <div>
                                                <label for="stager-pass"><?php esc_html_e('SFTP-wachtwoord', 'tempel-settings'); ?></label>
                                                <input id="stager-pass" type="password" name="sftp[password]" autocomplete="new-password" required>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="stager-url"><?php esc_html_e('Online WordPress-adres (HTTPS)', 'tempel-settings'); ?></label>
                                            <input id="stager-url" type="url" name="sftp[url]" placeholder="https://jouwsite.nl" required>
                                        </div>
                                        <div>
                                            <label for="stager-root"><?php esc_html_e('Servermap waarin wp-load.php staat', 'tempel-settings'); ?></label>
                                            <div class="tempel-stager-folder-field">
                                                <input id="stager-root" type="text" name="sftp[root]" placeholder="/public_html" required>
                                                <button type="button" class="button" id="stager-browse" aria-haspopup="dialog" aria-controls="stager-folders"><?php esc_html_e('Bladeren', 'tempel-settings'); ?></button>
                                            </div>
                                            <dialog id="stager-folders" class="tempel-stager-folder-dialog" data-endpoint="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('tempel_stager_browse')); ?>">
                                                <div class="tempel-stager-folder-dialog__header">
                                                    <div>
                                                        <span class="tempel-stager-step"><?php esc_html_e('Serverbrowser', 'tempel-settings'); ?></span>
                                                        <h2><?php esc_html_e('Kies de WordPress-map', 'tempel-settings'); ?></h2>
                                                    </div>
                                                    <button type="button" class="tempel-stager-folder-close" id="stager-folder-close" aria-label="<?php esc_attr_e('Sluiten', 'tempel-settings'); ?>">&times;</button>
                                                </div>
                                                <p id="stager-folder-status" role="status" aria-live="polite"></p>
                                                <p id="stager-folder-path"></p>
                                                <div class="tempel-stager-folder-actions"><button type="button" class="button" id="stager-folder-home"><?php esc_html_e('Startmap', 'tempel-settings'); ?></button><button type="button" class="button" id="stager-folder-up"><?php esc_html_e('Map omhoog', 'tempel-settings'); ?></button></div>
                                                <ul id="stager-folder-list" aria-label="<?php esc_attr_e('Servermappen', 'tempel-settings'); ?>"></ul>
                                                <div class="tempel-stager-folder-dialog__footer"><button type="button" class="button button-primary" id="stager-folder-select" disabled><?php esc_html_e('Deze map kiezen', 'tempel-settings'); ?></button></div>
                                            </dialog>
                                        </div>
                                    </div>
                                    <div class="tempel-stager-warning">
                                        <strong><?php esc_html_e('De live database wordt volledig vervangen.', 'tempel-settings'); ?></strong>
                                        <p><?php esc_html_e('Nieuwe bestellingen, gebruikers en formulierinzendingen op de live site gaan verloren. Plan de verhuizing op een rustig moment.', 'tempel-settings'); ?></p>
                                    </div>
                                    <label class="tempel-stager-confirm"><input type="checkbox" name="backup" value="1" required> <?php esc_html_e('Ik heb een recente Dropbox- of hostingback-up en wil de live site vervangen.', 'tempel-settings'); ?></label>
                                    <button class="button button-primary" <?php disabled((bool) $issues); ?>><?php esc_html_e('Verbind en zet website live', 'tempel-settings'); ?></button>
                                </form>
                            </section>

                            <div class="tempel-stager-grid tempel-stager-grid--manual">
                                    <section class="tempel-stager-card">
                                        <h2><?php esc_html_e('Download verhuispakket', 'tempel-settings'); ?></h2>
                                        <p><?php esc_html_e('Het pakket bevat ook de database en gebruikersaccounts. Bewaar het privé.', 'tempel-settings'); ?></p>
                                        <form id="stager-export-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                            <input type="hidden" name="action" value="tempel_stager_prepare_export">
                                            <?php wp_nonce_field('tempel_stager_prepare_export'); ?>
                                            <button class="button button-primary" <?php disabled((bool) $issues); ?>><?php esc_html_e('Maak verhuispakket', 'tempel-settings'); ?></button>
                                            <div id="stager-export-progress" class="tempel-stager-export-progress" hidden aria-live="polite">
                                                <p id="stager-export-message"><?php esc_html_e('Voorbereiden…', 'tempel-settings'); ?></p>
                                                <progress id="stager-export-bar" max="100"></progress>
                                            </div>
                                        </form>
                                    </section>
                                    <section class="tempel-stager-card">
                                        <h2><?php esc_html_e('Importeer verhuispakket', 'tempel-settings'); ?></h2>
                                        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                            <input type="hidden" name="action" value="tempel_stager_import">
                                            <?php wp_nonce_field('tempel_stager_import'); ?>
                                            <label for="stager-package"><?php esc_html_e('Verhuispakket', 'tempel-settings'); ?></label>
                                            <input id="stager-package" type="file" name="package" accept=".zip">
                                            <p class="description"><?php echo esc_html(sprintf(__('De browserupload van deze hosting is momenteel beperkt tot %s. Gebruik voor grotere websites rechtstreeks publiceren of verhoog de PHP-uploadlimiet.', 'tempel-settings'), size_format(wp_max_upload_size()))); ?></p>
                                            <?php if (Dropbox_Backup::is_connected()) : ?>
                                                <input id="stager-dropbox-path" type="hidden" name="dropbox_path" value="">
                                                <button type="button" class="button" id="stager-dropbox-browse"><?php esc_html_e('Kies uit Dropbox', 'tempel-settings'); ?></button>
                                                <p id="stager-dropbox-selected" class="description"></p>
                                                <dialog id="stager-dropbox-dialog" class="tempel-stager-folder-dialog" data-endpoint="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('tempel_stager_browse')); ?>">
                                                    <div class="tempel-stager-folder-dialog__header">
                                                        <h2><?php esc_html_e('Kies een Tempel-sitepakket', 'tempel-settings'); ?></h2>
                                                        <button type="button" class="tempel-stager-folder-close" id="stager-dropbox-close" aria-label="<?php esc_attr_e('Sluiten', 'tempel-settings'); ?>">&times;</button>
                                                    </div>
                                                    <p id="stager-dropbox-status" role="status" aria-live="polite"></p>
                                                    <ul id="stager-dropbox-list" class="tempel-stager-package-list"></ul>
                                                </dialog>
                                            <?php endif; ?>
                                            <label class="tempel-stager-confirm"><input type="checkbox" name="backup" value="1" required> <?php esc_html_e('Ik heb een recente back-up.', 'tempel-settings'); ?></label>
                                            <button class="button button-primary" <?php disabled((bool) $issues); ?>><?php esc_html_e('Importeer website', 'tempel-settings'); ?></button>
                                        </form>
                                    </section>
                            </div>
                            <p class="description"><?php esc_html_e('Ondersteunt enkele WordPress-installaties, PHP 8.1+ en maximaal 20 GiB uitgepakt. Multisite wordt niet ondersteund.', 'tempel-settings'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
