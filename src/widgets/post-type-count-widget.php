<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/widget.php';
require_once TEMPEL_SETTINGS_DIR . 'src/includes/helper-functions.php';

require_once 'partials/widget-header.php';
require_once 'partials/widget-footer.php';

class Post_Type_Count_Widget extends Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->widget_id = 'widget-post-type-count';
        $this->color = 'white';
        $this->title = $this->get_widget_title();
        $this->type = 'post-type-count';
    }

    public function render_widget()
    {
        echo $this->widget_markup();
    }

    function widget_markup()
    {
        $post_type = $this->get_post_type();
        $post_type_object = $post_type ? get_post_type_object($post_type) : null;
        $count = $this->get_count($post_type);
        $total_value = is_wp_error($count) ? '-' : $count;
        $label = $post_type_object ? $post_type_object->labels->name : __('Post type', 'tempel-settings');

        if ($post_type === 'shop_order' && function_exists('wc_get_orders')) {
            $label = __('Orders', 'tempel-settings');
        }

        ?>
        <?php widget_header($this->widget_id, $this->title, $this->type, $this->color, $total_value); ?>
        <div class="widget__content">
            <div class="widget__content__inner">
                <div class="widget__content__item">
                    <div class="item__label"><?php _e('Post type', 'tempel-settings'); ?></div>
                    <div class="item__value"><?= esc_html($label); ?></div>
                </div>
                <?php if (is_wp_error($count)): ?>
                    <div class="widget__content__item">
                        <div class="item__label"><?php _e('Status', 'tempel-settings'); ?></div>
                        <div class="item__value"><?= wp_kses_post($count->get_error_message()); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php widget_footer(); ?>
        <?php
    }

    public function get_widget_title(): string
    {
        $title = return_option('tmpl_widget_settings', 'post_type_count_widget_title');

        if (!$title) {
            return __('Items', 'tempel-settings');
        }

        return sanitize_text_field($title);
    }

    public function get_post_type(): string
    {
        $post_type = return_option('tmpl_widget_settings', 'post_type_count_post_type');

        return $post_type ? sanitize_key($post_type) : '';
    }

    public function get_statuses(): array
    {
        $statuses = return_option('tmpl_widget_settings', 'post_type_count_statuses');

        if (!$statuses) {
            return array();
        }

        $statuses = explode(',', $statuses);
        $statuses = array_map('trim', $statuses);
        $statuses = array_map('sanitize_key', $statuses);

        return array_filter($statuses);
    }

    public function get_count(string $post_type)
    {
        if ($post_type === 'shop_order' && function_exists('wc_get_orders')) {
            return $this->get_woocommerce_order_count();
        }

        if (!$post_type || !post_type_exists($post_type)) {
            return new \WP_Error('missing_post_type', __('Select a valid post type.', 'tempel-settings'));
        }

        $counts = wp_count_posts($post_type);

        if (!$counts) {
            return 0;
        }

        $statuses = $this->get_statuses();

        if (!$statuses) {
            $statuses = array_diff(array_keys(get_object_vars($counts)), array('trash', 'auto-draft'));
        }

        $total = 0;

        foreach ($statuses as $status) {
            $total += isset($counts->{$status}) ? (int) $counts->{$status} : 0;
        }

        return $total;
    }

    public function get_woocommerce_order_count(): int
    {
        $statuses = $this->get_statuses();

        if (!$statuses && function_exists('wc_get_order_statuses')) {
            $statuses = array_keys(wc_get_order_statuses());
        }

        $statuses = array_map(function ($status) {
            return preg_replace('/^wc-/', '', $status);
        }, $statuses);

        $orders = wc_get_orders(array(
            'limit' => 1,
            'paginate' => true,
            'return' => 'ids',
            'status' => $statuses,
        ));

        return isset($orders->total) ? (int) $orders->total : 0;
    }
}
