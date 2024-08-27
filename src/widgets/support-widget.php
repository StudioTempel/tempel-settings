<?php

namespace Tempel\Admin\Widgets;

require_once TMPL_PLUGIN_DIR . 'src/abstract/widget.php';
use Tempel\Abstracts\Widget;

class Support_Widget extends Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->widget_id = 'widget-support';
        $this->color = 'white';
        $this->title = 'Support';
        $this->type = 'support';
    }
    
    public function render_widget()
    {
        $widget = $this->widget_markup();
        if (is_wp_error($widget)) {
            echo $widget->get_error_message();
        }
    }
    
    public function widget_markup()
    {
        
        $faq_items = $this->get_faq_items();
        ?>
        <script>
            jQuery(document).ready(function ($) {
                $('.widget__content__dropdown').on('click', function () {
                    $(this).toggleClass('active');
                    $(this).find('.item__dropdown__value').slideToggle();
                });
            });
        </script>
        <div class="tmpl_widget widget--<?= $this->type; ?> widget--<?= $this->color; ?>">
            <div class="widget__inner">
                <div class="widget__icon">
                    <?php $this->get_widget_icon(); ?>
                </div>
                <div class="widget__header">
                    <div class="widget__title"><?= $this->title; ?></div>
                </div>
                <div class="widget__content">
                    <div class="widget__content_inner">
                        <?php if ($faq_items): ?>
                            <?php foreach ($faq_items as $faq_item):
                                $title = $faq_item['title'];
                                $content = $faq_item['content'];
                                ?>
                                <div class="widget__content__item widget__content__dropdown">
                                    <div class="item__dropdown__label"><?= $title; ?></div>
                                    <div class="item__dropdown__value"><?= $content; ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p><?php _e('No FAQ items found', 'tempel-settings'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="widget__footer">
                    <a href="<?= $this->get_faq_link(); ?>" rel="nofollow" target="_blank"><?php _e('View all', 'tempel-settings'); ?></a>
                    <a href="https://studiotempel.nl/contact" target="_blank" rel="nofollow"
                       class="widget__button widget__button__yellow"><?php _e('Ask a question', 'tempel-settings'); ?></a>
                </div>
            </div>
        </div>
        <?php
    }
    
    function get_faq_link()
    {
        $faq_link = $this->get_settings('support_faq_link');
        
        if (empty($faq_link) || !filter_var($faq_link, FILTER_VALIDATE_URL)) {
            return 'https://studiotempel.nl/faq';
        }
        
        return $faq_link;
    }
    
    function get_contact_link()
    {
        $contact_link = $this->get_settings('support_ticket_link');
        
        if (empty($contact_link) || !filter_var($contact_link, FILTER_VALIDATE_URL)) {
            return 'https://studiotempel.nl/contact';
        }
        
        return $contact_link;
    }
    
    /**
     * Get faq items
     * Returns faq items from cache or fetches new items if cache is older than 1 day
     *
     * @return array
     */
    function get_faq_items(): array
    {
        $cache_file = TMPL_PLUGIN_CACHE_PATH . 'faq_items_cache.json';
        
        if (file_exists($cache_file)) {
            $cache_time = filemtime($cache_file);
            $interval = 60 * 60 * 24; // 1 day
            
            if ($cache_time > time() - $interval) {
                $faq_items = file_get_contents($cache_file);
                $faq_items = json_decode($faq_items, true);
                
                return $faq_items;
            }
        }
        
        return $this->fetch_new_faq_items();
    }
    
    function fetch_new_faq_items()
    {
        $response = wp_remote_get('https://studiotempel.nl/wp-json/tmpl/v1/faq?show_in_widget_value=1');
        
        if (!is_array($response) || is_wp_error($response)) {
            return [];
        }
        
        $body = wp_remote_retrieve_body($response);
        
        $response = json_decode($body, true);
        
        $this->cache_faq_items_to_file($response);
        
        return $response;
    }
    
    function cache_faq_items_to_file($faq_items)
    {
        $faq_items = json_encode($faq_items);
        $cache_file = TMPL_PLUGIN_CACHE_PATH . 'faq_items_cache.json';
        
        file_put_contents($cache_file, $faq_items);
    }
    
    public function get_settings($option)
    {
        $settings = get_option('tmpl_widget_settings');
        return $settings[$option];
    }
}