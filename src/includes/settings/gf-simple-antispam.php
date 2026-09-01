<?php

namespace Tempel;

class GF_Simple_Antispam
{
    private const START_FIELD = 'tempel_gf_started';
    private const PROOF_FIELD = 'tempel_gf_proof';
    private const JS_FIELD = 'tempel_gf_js';

    public function __construct()
    {
        add_filter('gform_form_post_get_meta', array($this, 'enable_honeypot'));
        add_filter('gform_form_tag', array($this, 'add_invisible_fields'), 10, 2);
        add_filter('gform_entry_is_spam', array($this, 'is_spam'), 10, 3);
    }

    public function enable_honeypot($form)
    {
        if (is_array($form)) {
            $form['enableHoneypot'] = true;
            $form['honeypotAction'] = 'spam';
        }
        return $form;
    }

    public function add_invisible_fields(string $form_tag, array $form): string
    {
        $form_id = absint($form['id'] ?? 0);
        if (!$form_id) {
            return $form_tag;
        }

        $started = time();
        $proof = wp_hash($form_id . '|' . $started, 'nonce');
        $fields = sprintf(
            '<input type="hidden" name="%1$s" value="%2$d"><input type="hidden" name="%3$s" value="%4$s"><input type="hidden" name="%5$s" value="" class="tempel-gf-js-proof"><script>document.currentScript.previousElementSibling.value="1";</script>',
            esc_attr(self::START_FIELD),
            $started,
            esc_attr(self::PROOF_FIELD),
            esc_attr($proof),
            esc_attr(self::JS_FIELD)
        );

        return $form_tag . $fields;
    }

    public function is_spam($is_spam, $form, $entry): bool
    {
        if ($is_spam || !isset($_POST['gform_submit'])) {
            return (bool) $is_spam;
        }

        $form_id = absint($form['id'] ?? 0);
        $started = isset($_POST[self::START_FIELD]) ? absint($_POST[self::START_FIELD]) : 0;
        $proof = isset($_POST[self::PROOF_FIELD]) ? sanitize_text_field(wp_unslash($_POST[self::PROOF_FIELD])) : '';
        $has_js = isset($_POST[self::JS_FIELD]) && wp_unslash($_POST[self::JS_FIELD]) === '1';
        $expected = $form_id && $started ? wp_hash($form_id . '|' . $started, 'nonce') : '';
        $minimum_seconds = max(1, min(30, absint(return_option('tmpl_settings', 'gf_antispam_min_seconds') ?: 3)));

        if (!$has_js || $expected === '' || !hash_equals($expected, $proof)) {
            return true;
        }

        return time() - $started < $minimum_seconds;
    }
}
