<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/widget.php';

require_once TEMPEL_SETTINGS_DIR . 'src/includes/widget-blog-helper-functions.php';

require_once 'partials/widget-header.php';
require_once 'partials/widget-footer.php';

class Blog_Widget extends Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->widget_id = 'widget-blog';
        $this->color = 'black-yellow';
        $this->title = 'Blogs';
        $this->type = 'blog';
    }
    
    public function render_widget()
    {
        $widget_id = $this->widget_id;
        $color = $this->color;
        $title = $this->title;
        $type = $this->type;
        
        echo $this->widget_markup();
    }
    
    function widget_markup()
    {
        $blog_items = get_blogs();
        ?>
        <?php widget_header($this->widget_id, $this->title, $this->type, $this->color); ?>
        <div class="widget__content">
            <div class="widget__content__inner">
                <?php if ($blog_items): ?>
                    <?php foreach ($blog_items as $blog_item):
                        $title = $blog_item['title'];
                        $permalink = $blog_item['permalink'];
                        ?>
                        <div class="widget__content__item">
                            <a href="<?= $permalink; ?>" class="item__link" target="_blank">
                                <div class="item__label has-arrow"><?= $title; ?></div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?php _e('No Blog items found', 'tempel-settings'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php widget_footer(); ?>
        <?php
    }
}