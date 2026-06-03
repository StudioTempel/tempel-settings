<?php

namespace Tempel;

class GF_BAG_Address
{
    private string $ajax_action = 'tmpl_gf_bag_address_lookup';

    public function __construct()
    {
        add_action('gform_loaded', array($this, 'register_field'), 5);
        add_action('wp_ajax_' . $this->ajax_action, array($this, 'lookup'));
        add_action('wp_ajax_nopriv_' . $this->ajax_action, array($this, 'lookup'));
    }

    public function register_field(): void
    {
        if (!class_exists('GF_Fields') || !class_exists('GF_Field')) {
            return;
        }

        require_once TEMPEL_SETTINGS_DIR . 'src/includes/settings/fields/gf-field-nl-address.php';

        \GF_Fields::register(new \GF_Field_NL_Address());
    }

    public function lookup(): void
    {
        check_ajax_referer($this->ajax_action, 'nonce');

        $postcode = isset($_POST['postcode']) ? sanitize_text_field(wp_unslash($_POST['postcode'])) : '';
        $huisnummer = isset($_POST['huisnummer']) ? sanitize_text_field(wp_unslash($_POST['huisnummer'])) : '';
        $toevoeging = isset($_POST['toevoeging']) ? sanitize_text_field(wp_unslash($_POST['toevoeging'])) : '';

        if ($postcode === '' || $huisnummer === '') {
            wp_send_json_error(
                array(
                    'message' => __('Postcode en huisnummer zijn verplicht.', 'tempel-settings'),
                ),
                400
            );
        }

        $result = $this->bag_lookup($postcode, $huisnummer, $toevoeging);

        if (!$result['success']) {
            wp_send_json_error(
                array(
                    'message' => $result['message'] ?? __('Adres niet gevonden.', 'tempel-settings'),
                ),
                (int) ($result['status'] ?? 422)
            );
        }

        wp_send_json_success($result['data']);
    }

    public static function get_settings(): array
    {
        return array(
            'api_key' => (string) return_option('tmpl_settings', 'gf_bag_address_api_key'),
            'endpoint' => (string) (return_option('tmpl_settings', 'gf_bag_address_endpoint') ?: 'https://api.bag.kadaster.nl/lvbag/individuelebevragingen/v2/adressenuitgebreid'),
            'timeout' => max(1, (int) (return_option('tmpl_settings', 'gf_bag_address_timeout') ?: 8)),
        );
    }

    public static function get_ajax_config(int $field_id): array
    {
        return array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'action' => 'tmpl_gf_bag_address_lookup',
            'nonce' => wp_create_nonce('tmpl_gf_bag_address_lookup'),
            'fieldId' => $field_id,
            'messages' => array(
                'loading' => __('Adres wordt opgehaald...', 'tempel-settings'),
                'notFound' => __('Geen adres gevonden.', 'tempel-settings'),
            ),
        );
    }

    private function bag_lookup(string $postcode, string $huisnummer, string $toevoeging = ''): array
    {
        $settings = self::get_settings();
        $api_key = trim($settings['api_key']);
        $endpoint = rtrim($settings['endpoint'], '/');

        if ($api_key === '') {
            return array(
                'success' => false,
                'message' => __('BAG API key ontbreekt in de Tempel Settings plugininstellingen.', 'tempel-settings'),
            );
        }

        $query = array(
            'postcode' => $this->normalize_postcode($postcode),
            'huisnummer' => absint($huisnummer),
            'exacteMatch' => 'true',
            'page' => 1,
            'pageSize' => 10,
        );

        $suffix = $this->split_suffix($toevoeging);

        if ($suffix['huisletter'] !== '') {
            $query['huisletter'] = $suffix['huisletter'];
        }

        if ($suffix['huisnummertoevoeging'] !== '') {
            $query['huisnummertoevoeging'] = $suffix['huisnummertoevoeging'];
        }

        $response = wp_remote_get(
            add_query_arg($query, $endpoint),
            array(
                'timeout' => $settings['timeout'],
                'headers' => array(
                    'Accept' => 'application/hal+json',
                    'X-Api-Key' => $api_key,
                ),
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($status_code >= 400) {
            return array(
                'success' => false,
                'message' => $body['detail'] ?? __('De BAG API gaf een fout terug.', 'tempel-settings'),
                'status' => $status_code,
            );
        }

        $items = $body['_embedded']['adressen'] ?? array();

        if (!is_array($items) || $items === array()) {
            return array(
                'success' => false,
                'message' => __('Geen adres gevonden voor deze combinatie.', 'tempel-settings'),
                'status' => 404,
            );
        }

        $item = $items[0];

        return array(
            'success' => true,
            'data' => array(
                'postcode' => $item['postcode'] ?? $query['postcode'],
                'huisnummer' => isset($item['huisnummer']) ? (string) $item['huisnummer'] : (string) $query['huisnummer'],
                'toevoeging' => $this->compose_suffix(
                    $item['huisletter'] ?? '',
                    $item['huisnummertoevoeging'] ?? ''
                ),
                'straat' => $item['openbareRuimteNaam'] ?? '',
                'plaats' => $item['woonplaatsNaam'] ?? '',
                'oppervlakte' => isset($item['oppervlakte']) ? (string) $item['oppervlakte'] : '',
                'bouwjaar' => $this->extract_bouwjaar($item['oorspronkelijkBouwjaar'] ?? array()),
                'gebruiksdoelen' => isset($item['gebruiksdoelen']) && is_array($item['gebruiksdoelen']) ? implode(', ', $item['gebruiksdoelen']) : '',
            ),
        );
    }

    private function normalize_postcode(string $postcode): string
    {
        return strtoupper(str_replace(' ', '', trim($postcode)));
    }

    private function split_suffix(string $suffix): array
    {
        $suffix = strtoupper(trim($suffix));
        $suffix = preg_replace('/\s+/', '', $suffix);

        if ($suffix === '') {
            return array(
                'huisletter' => '',
                'huisnummertoevoeging' => '',
            );
        }

        if (preg_match('/^[A-Z]$/', $suffix)) {
            return array(
                'huisletter' => $suffix,
                'huisnummertoevoeging' => '',
            );
        }

        return array(
            'huisletter' => '',
            'huisnummertoevoeging' => $suffix,
        );
    }

    private function compose_suffix(string $huisletter, string $huisnummertoevoeging): string
    {
        $parts = array_filter(array(trim($huisletter), trim($huisnummertoevoeging)));

        return implode(' ', $parts);
    }

    private function extract_bouwjaar($value): string
    {
        if (is_array($value) && isset($value[0])) {
            return substr((string) $value[0], 0, 4);
        }

        if (is_string($value)) {
            return substr($value, 0, 4);
        }

        return '';
    }
}
