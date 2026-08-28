<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/page.php';
require_once TEMPEL_SETTINGS_DIR . 'src/views/partials/settings-navigation.php';

class Status_Settings extends Page
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
        add_action('admin_post_tempel_clear_status_log', array($this, 'clear_log'));
        add_action('admin_post_tempel_save_status_monitor', array($this, 'save_monitor'));
        add_action('admin_post_tempel_run_status_monitor', array($this, 'run_monitor'));
    }

    public function render(): void
    {
        $level = isset($_GET['log_level']) ? sanitize_key(wp_unslash($_GET['log_level'])) : '';
        $source = isset($_GET['log_source']) ? sanitize_key(wp_unslash($_GET['log_source'])) : '';
        $logs = Status_Log::get_entries(array('level' => $level, 'source' => $source));
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_status_settings">
                <div class="tmpl_settings__inner">
                    <?php settings_navigation(); ?>
                    <div class="settings__body">
                        <div class="body__inner">
                            <div class="settings__category">
                                <div class="category__header"><div class="category__label__wrap"><div class="category__title"><?php esc_html_e('Website-status', 'tempel-settings'); ?></div></div></div>
                                <div class="category__content tempel-status-grid">
                                    <?php foreach ($this->get_status_cards() as $card) : ?>
                                        <article class="tempel-status-card tempel-status-card--<?php echo esc_attr($card['status']); ?>">
                                            <div class="tempel-status-card__heading"><span class="tempel-status-card__dot" aria-hidden="true"></span><strong><?php echo esc_html($card['label']); ?></strong></div>
                                            <p><?php echo esc_html($card['message']); ?></p>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="settings__category">
                                <div class="category__header"><div class="category__label__wrap"><div class="category__title"><?php esc_html_e('Periodieke controles', 'tempel-settings'); ?></div><p><?php esc_html_e('Tempel controleert tweemaal per dag de systeemstatus en maximaal vijf veilige HTTPS-monitor-URL’s.', 'tempel-settings'); ?></p></div></div>
                                <div class="category__content">
                                    <?php if (isset($_GET['monitor_saved'])) : ?><div class="notice notice-success inline"><p><?php esc_html_e('De monitor-URL’s zijn opgeslagen.', 'tempel-settings'); ?></p></div><?php endif; ?>
                                    <?php if (isset($_GET['monitor_ran'])) : ?><div class="notice notice-success inline"><p><?php esc_html_e('De gezondheidscontrole is uitgevoerd.', 'tempel-settings'); ?></p></div><?php endif; ?>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="tempel-monitor-form">
                                        <input type="hidden" name="action" value="tempel_save_status_monitor">
                                        <?php wp_nonce_field('tempel_save_status_monitor'); ?>
                                        <label for="tempel_monitor_endpoints"><strong><?php esc_html_e('Monitor-URL’s', 'tempel-settings'); ?></strong><span><?php esc_html_e('Eén HTTPS healthcheck-URL per regel. Er wordt alleen een veilige HEAD-aanvraag uitgevoerd.', 'tempel-settings'); ?></span></label>
                                        <textarea id="tempel_monitor_endpoints" name="monitor_endpoints" rows="5" placeholder="https://voorbeeld.nl/health"><?php echo esc_textarea(implode("\n", Status_Monitor::get_endpoints())); ?></textarea>
                                        <button class="button button-primary"><?php esc_html_e('Monitor-URL’s opslaan', 'tempel-settings'); ?></button>
                                    </form>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="tempel-monitor-run">
                                        <input type="hidden" name="action" value="tempel_run_status_monitor">
                                        <?php wp_nonce_field('tempel_run_status_monitor'); ?>
                                        <button class="button button-secondary"><?php esc_html_e('Nu controleren', 'tempel-settings'); ?></button>
                                    </form>
                                </div>
                            </div>

                            <div class="settings__category">
                                <div class="category__header">
                                    <div class="category__label__wrap">
                                        <div class="category__title"><?php esc_html_e('Technisch logboek', 'tempel-settings'); ?></div>
                                        <p><?php esc_html_e('Maximaal 500 regels, automatisch verwijderd na 30 dagen. Persoonsgegevens en geheime sleutels worden niet opgeslagen.', 'tempel-settings'); ?></p>
                                    </div>
                                </div>
                                <div class="category__content">
                                    <form method="get" class="tempel-log-filters">
                                        <input type="hidden" name="page" value="tempel-status-settings">
                                        <label><span class="screen-reader-text"><?php esc_html_e('Status', 'tempel-settings'); ?></span>
                                            <select name="log_level">
                                                <option value=""><?php esc_html_e('Alle statussen', 'tempel-settings'); ?></option>
                                                <?php foreach ($this->level_options() as $value => $label) : ?>
                                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($level, $value); ?>><?php echo esc_html($label); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label><span class="screen-reader-text"><?php esc_html_e('Onderdeel', 'tempel-settings'); ?></span>
                                            <select name="log_source">
                                                <option value=""><?php esc_html_e('Alle onderdelen', 'tempel-settings'); ?></option>
                                                <option value="mail" <?php selected($source, 'mail'); ?>><?php esc_html_e('E-mail', 'tempel-settings'); ?></option>
                                                <option value="api" <?php selected($source, 'api'); ?>><?php esc_html_e('API', 'tempel-settings'); ?></option>
                                                <option value="gravity_forms" <?php selected($source, 'gravity_forms'); ?>>Gravity Forms</option>
                                                <option value="webhook" <?php selected($source, 'webhook'); ?>><?php esc_html_e('Webhook', 'tempel-settings'); ?></option>
                                                <option value="system" <?php selected($source, 'system'); ?>><?php esc_html_e('Systeem', 'tempel-settings'); ?></option>
                                            </select>
                                        </label>
                                        <button class="button"><?php esc_html_e('Filteren', 'tempel-settings'); ?></button>
                                        <a class="button button-link" href="<?php echo esc_url(admin_url('admin.php?page=tempel-status-settings')); ?>"><?php esc_html_e('Reset', 'tempel-settings'); ?></a>
                                    </form>

                                    <?php if (isset($_GET['log_cleared'])) : ?><div class="notice notice-success inline"><p><?php esc_html_e('Het logboek is geleegd.', 'tempel-settings'); ?></p></div><?php endif; ?>

                                    <div class="tempel-log-table-wrap">
                                        <table class="widefat striped tempel-log-table">
                                            <thead><tr><th><?php esc_html_e('Tijd', 'tempel-settings'); ?></th><th><?php esc_html_e('Onderdeel', 'tempel-settings'); ?></th><th><?php esc_html_e('Status', 'tempel-settings'); ?></th><th><?php esc_html_e('Melding', 'tempel-settings'); ?></th></tr></thead>
                                            <tbody>
                                            <?php if (empty($logs)) : ?>
                                                <tr><td colspan="4"><?php esc_html_e('Nog geen logregels voor deze selectie.', 'tempel-settings'); ?></td></tr>
                                            <?php else : foreach (array_slice($logs, 0, 100) as $entry) : ?>
                                                <tr>
                                                    <td><?php echo esc_html(wp_date('d-m-Y H:i:s', (int) $entry['timestamp'])); ?></td>
                                                    <td><?php echo esc_html($this->source_label((string) $entry['source'])); ?></td>
                                                    <td><span class="tempel-log-level tempel-log-level--<?php echo esc_attr($entry['level']); ?>"><?php echo esc_html($this->level_label((string) $entry['level'])); ?></span></td>
                                                    <td>
                                                        <?php echo esc_html($entry['message']); ?>
                                                        <?php if (!empty($entry['context'])) : ?><details><summary><?php esc_html_e('Details', 'tempel-settings'); ?></summary><code><?php echo esc_html(wp_json_encode($entry['context'], JSON_UNESCAPED_SLASHES)); ?></code></details><?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="tempel-log-clear" onsubmit="return confirm('<?php echo esc_js(__('Weet je zeker dat je het logboek wilt leegmaken?', 'tempel-settings')); ?>');">
                                        <input type="hidden" name="action" value="tempel_clear_status_log">
                                        <?php wp_nonce_field('tempel_clear_status_log'); ?>
                                        <button class="button button-secondary"><?php esc_html_e('Logboek leegmaken', 'tempel-settings'); ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings__footer"></div>
                </div>
            </div>
        </div>
        <style>
            .tempel-status-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;padding:20px;background:#171717}.tempel-status-card{background:#242424;color:#fff;border:1px solid #3d3d3d;border-left:4px solid #72aee6;border-radius:4px;padding:16px}.tempel-status-card--success{border-left-color:#46b450}.tempel-status-card--warning{border-left-color:#f0b849}.tempel-status-card--error{border-left-color:#e65054}.tempel-status-card__heading{display:flex;align-items:center;gap:8px}.tempel-status-card__dot{width:9px;height:9px;border-radius:50%;background:#72aee6}.tempel-status-card--success .tempel-status-card__dot{background:#46b450}.tempel-status-card--warning .tempel-status-card__dot{background:#f0b849}.tempel-status-card--error .tempel-status-card__dot{background:#e65054}.tempel-status-card p{margin:10px 0 0;color:#c3c4c7}.tempel-monitor-form{display:grid;gap:10px;padding:20px 20px 8px}.tempel-monitor-form label{display:grid;gap:4px}.tempel-monitor-form label span{color:#646970}.tempel-monitor-form textarea{width:100%;max-width:800px;font-family:monospace}.tempel-monitor-form .button{width:max-content}.tempel-monitor-run{padding:0 20px 20px}.tempel-log-filters{display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:20px 20px 12px}.tempel-log-table-wrap{overflow-x:auto;padding:0 20px}.tempel-log-level{display:inline-block;border-radius:999px;padding:3px 8px;background:#f0f0f1}.tempel-log-level--success{color:#006b1b;background:#edfaef}.tempel-log-level--warning{color:#6e4b00;background:#fcf3cf}.tempel-log-level--error{color:#8a2424;background:#fbeaea}.tempel-log-table details{margin-top:5px}.tempel-log-table code{display:inline-block;margin-top:5px;white-space:normal}.tempel-log-clear{padding:16px 20px 20px;text-align:right}
        </style>
        <?php
    }

    public function clear_log(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Je hebt geen rechten om het logboek te wissen.', 'tempel-settings'));
        }

        check_admin_referer('tempel_clear_status_log');
        Status_Log::clear();
        wp_safe_redirect(admin_url('admin.php?page=tempel-status-settings&log_cleared=1'));
        exit;
    }

    public function save_monitor(): void
    {
        $this->guard_monitor_action('tempel_save_status_monitor');
        $raw = isset($_POST['monitor_endpoints']) ? sanitize_textarea_field(wp_unslash($_POST['monitor_endpoints'])) : '';
        Status_Monitor::save_endpoints(preg_split('/\r\n|\r|\n/', $raw) ?: array());
        wp_safe_redirect(admin_url('admin.php?page=tempel-status-settings&monitor_saved=1'));
        exit;
    }

    public function run_monitor(): void
    {
        $this->guard_monitor_action('tempel_run_status_monitor');
        Status_Monitor::run();
        wp_safe_redirect(admin_url('admin.php?page=tempel-status-settings&monitor_ran=1'));
        exit;
    }

    private function get_status_cards(): array
    {
        $mail = Status_Log::get_mail_status();
        $api = Status_Log::get_api_status();
        $gravity_forms = Status_Monitor::get_gravity_forms_status();
        $monitor = Status_Monitor::get_status();
        $memory = wp_convert_hr_to_bytes((string) ini_get('memory_limit'));
        $memory_mb = $memory > 0 ? (int) round($memory / MB_IN_BYTES) : 0;
        $cron = $this->get_cron_status();

        return array(
            array('label' => 'WordPress', 'status' => version_compare(get_bloginfo('version'), '7.1', '>=') ? 'success' : 'info', 'message' => sprintf(__('Versie %s', 'tempel-settings'), get_bloginfo('version'))),
            array('label' => 'PHP', 'status' => version_compare(PHP_VERSION, '8.2', '>=') ? 'success' : 'warning', 'message' => sprintf(__('Versie %s', 'tempel-settings'), PHP_VERSION)),
            array('label' => __('Geheugen', 'tempel-settings'), 'status' => $memory_mb >= 128 || $memory_mb === 0 ? 'success' : 'warning', 'message' => $memory_mb > 0 ? sprintf(__('%d MB beschikbaar', 'tempel-settings'), $memory_mb) : __('Geen vaste limiet gevonden', 'tempel-settings')),
            array('label' => 'WP-Cron', 'status' => $cron['status'], 'message' => $cron['message']),
            $this->activity_card(__('E-mail', 'tempel-settings'), $mail, __('Nog geen e-mailresultaat geregistreerd.', 'tempel-settings')),
            $this->activity_card(__('Postcode-API', 'tempel-settings'), $api, __('Nog geen API-resultaat geregistreerd.', 'tempel-settings')),
            $this->activity_card('Gravity Forms', $gravity_forms, class_exists('GFForms') ? __('Nog geen formulierresultaat geregistreerd.', 'tempel-settings') : __('Gravity Forms is niet actief.', 'tempel-settings')),
            $this->activity_card(__('Periodieke controle', 'tempel-settings'), $monitor, __('Nog niet uitgevoerd.', 'tempel-settings')),
            array('label' => 'HTTPS', 'status' => is_ssl() ? 'success' : 'warning', 'message' => is_ssl() ? __('Beveiligde verbinding actief', 'tempel-settings') : __('WordPress-beheer draait niet via HTTPS', 'tempel-settings')),
            array('label' => __('Zoekmachines', 'tempel-settings'), 'status' => get_option('blog_public') ? 'success' : 'warning', 'message' => get_option('blog_public') ? __('Indexering toegestaan', 'tempel-settings') : __('Indexering wordt ontmoedigd', 'tempel-settings')),
        );
    }

    private function get_cron_status(): array
    {
        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            return array('status' => 'warning', 'message' => __('WP-Cron is uitgeschakeld; controleer de servercron.', 'tempel-settings'));
        }

        $crons = _get_cron_array();
        $overdue = 0;
        foreach ((array) $crons as $timestamp => $events) {
            if ((int) $timestamp < time() - (15 * MINUTE_IN_SECONDS)) {
                $overdue += count((array) $events);
            }
        }

        return $overdue > 0
            ? array('status' => 'warning', 'message' => sprintf(_n('%d taak lijkt vertraagd.', '%d taken lijken vertraagd.', $overdue, 'tempel-settings'), $overdue))
            : array('status' => 'success', 'message' => __('Geen vertraagde taken gevonden', 'tempel-settings'));
    }

    private function activity_card(string $label, array $activity, string $empty): array
    {
        if (empty($activity['timestamp'])) {
            return array('label' => $label, 'status' => 'info', 'message' => $empty);
        }

        $activity_status = (string) ($activity['status'] ?? 'success');
        if (!in_array($activity_status, array('success', 'warning', 'error', 'info'), true)) {
            $activity_status = 'info';
        }

        return array(
            'label' => $label,
            'status' => $activity_status,
            'message' => sprintf(__('%1$s — %2$s', 'tempel-settings'), $activity['message'] ?? '', human_time_diff((int) $activity['timestamp'], time()) . ' ' . __('geleden', 'tempel-settings')),
        );
    }

    private function source_label(string $source): string
    {
        return array(
            'mail' => __('E-mail', 'tempel-settings'),
            'api' => __('API', 'tempel-settings'),
            'gravity_forms' => 'Gravity Forms',
            'webhook' => __('Webhook', 'tempel-settings'),
            'system' => __('Systeem', 'tempel-settings'),
        )[$source] ?? ucfirst($source);
    }

    private function level_label(string $level): string
    {
        return array('success' => __('Geslaagd', 'tempel-settings'), 'warning' => __('Waarschuwing', 'tempel-settings'), 'error' => __('Fout', 'tempel-settings'), 'info' => __('Informatie', 'tempel-settings'))[$level] ?? ucfirst($level);
    }

    private function level_options(): array
    {
        return array(
            'error' => __('Fouten', 'tempel-settings'),
            'warning' => __('Waarschuwingen', 'tempel-settings'),
            'success' => __('Geslaagd', 'tempel-settings'),
            'info' => __('Informatie', 'tempel-settings'),
        );
    }

    private function guard_monitor_action(string $nonce_action): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Je hebt geen rechten om de statusmonitor te beheren.', 'tempel-settings'));
        }

        check_admin_referer($nonce_action);
    }
}
