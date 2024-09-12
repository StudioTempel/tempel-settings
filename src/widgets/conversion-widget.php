<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/abstract/widget.php';

require_once 'partials/widget-header.php';
require_once 'partials/widget-footer.php';
require_once 'partials/widget-conversion-item.php';

require_once TEMPEL_SETTINGS_DIR . 'src/includes/widget-conversion-helper-functions.php';

class Conversion_Widget extends Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->widget_id = 'widget-conversion';
        $this->color = 'yellow';
        $this->title = 'Conversies';
        $this->type = 'conversion';
    }
    
    public function render_widget()
    {
        echo $this->widget_markup();
    }
    
    function widget_markup()
    {
        $forms_submissions = get_form_submissions_by_id();
        $total_submissions = get_total_submissions();
        
        ?>
        <?php widget_header($this->widget_id, $this->title, $this->type, $this->color); ?>
        <div class="widget__content">
            <div class="widget__content__inner">
                <?php if ($forms_submissions && count($forms_submissions) > 0) : ?>
                    <?php foreach ($forms_submissions as $submission):
                        $title = $submission['title'];
                        $link = $submission['link'];
                        $submissions = $submission['submissions'];
                        ?>

                            <?php widget_conversion_item($link, $title, $submissions); ?>
                    
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="widget__content__item">
                        <div class="item__label"><?php _e('No forms selected', 'tempel-settings'); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php widget_footer(); ?>
        <?php
    }
}

