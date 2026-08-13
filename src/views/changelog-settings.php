<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

class Changelog_Settings extends Page
{
    public function render(): void
    {
        $releases = $this->get_releases();
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_changelog_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner">
                            <div class="settings__category">
                                <div class="category__header">
                                    <div class="category__label__wrap">
                                        <div class="category__title">
                                            <?php esc_html_e('Changelog', 'tempel-settings'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__content">
                                    <?php if (empty($releases)) : ?>
                                        <div class="settings__field">
                                            <div class="settings__field__inner">
                                                <p><?php esc_html_e('De changelog kon niet worden geladen.', 'tempel-settings'); ?></p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <?php foreach ($releases as $index => $release) : ?>
                                            <section class="settings__field tempel-changelog-release">
                                                <div class="settings__field__inner">
                                                    <div class="settings__label__wrap">
                                                        <h2>
                                                            <?php echo esc_html($release['version']); ?>
                                                            <?php if ($index === 0) : ?>
                                                                <span class="tempel-changelog-current"><?php esc_html_e('Huidige versie', 'tempel-settings'); ?></span>
                                                            <?php endif; ?>
                                                        </h2>
                                                        <?php if ($release['date'] !== '') : ?>
                                                            <time datetime="<?php echo esc_attr($release['date']); ?>">
                                                                <?php echo esc_html(wp_date(get_option('date_format'), strtotime($release['date']))); ?>
                                                            </time>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="settings__input__wrap">
                                                        <ul>
                                                            <?php foreach ($release['changes'] as $change) : ?>
                                                                <li><?php echo esc_html($change); ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </section>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings__footer"></div>
                </div>
            </div>
        </div>
        <style>
            .tempel-changelog-release .settings__field__inner { align-items: flex-start; }
            .tempel-changelog-release h2 { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin: 0 0 6px; }
            .tempel-changelog-release time { color: #646970; }
            .tempel-changelog-release ul { margin: 0; padding-left: 20px; list-style: disc; }
            .tempel-changelog-release li + li { margin-top: 6px; }
            .tempel-changelog-current { padding: 4px 8px; border-radius: 999px; background: #f3f438; color: #000; font-size: 11px; line-height: 1; }
        </style>
        <?php
    }

    private function get_releases(): array
    {
        $path = TEMPEL_SETTINGS_DIR . 'CHANGELOG.md';

        if (!is_readable($path)) {
            return array();
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $releases = array();
        $current = null;

        foreach ($lines as $line) {
            if (preg_match('/^## \[([^]]+)\](?: - (\d{4}-\d{2}-\d{2}))?$/', trim($line), $matches)) {
                if ($current !== null) {
                    $releases[] = $current;
                }

                $current = array(
                    'version' => $matches[1],
                    'date' => $matches[2] ?? '',
                    'changes' => array(),
                );
                continue;
            }

            if ($current !== null && preg_match('/^- (.+)$/', trim($line), $matches)) {
                $current['changes'][] = $matches[1];
            }
        }

        if ($current !== null) {
            $releases[] = $current;
        }

        return array_values(array_filter($releases, static function (array $release): bool {
            return !empty($release['changes']);
        }));
    }
}
