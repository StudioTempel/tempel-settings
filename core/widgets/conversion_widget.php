<?php

namespace Tempel\Core\Widgets;

require_once "widget.php";

class ConversionWidget extends Widget
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
        $color = $this->color;
        $title = $this->title;
        $type = $this->type;
        
        echo $this->widget_markup();
    }
    
    function widget_markup()
    {
        $forms_submissions = $this->get_form_submissions_by_id();
        $total_submissions = $this->get_total_submissions();
        
        ?>
        <div class="tmpl_widget widget--<?= $this->type; ?> widget--<?= $this->color; ?>">
            <div class="widget__inner">
                <div class="widget__icon">
                    <?php $this->get_widget_icon(); ?>
                </div>
                <div class="widget__total_conversion_value"><?= $total_submissions; ?></div>
                <div class="widget__header">
                    <div class="widget__title">
                        <?php if($total_submissions > 1) {
                            echo 'Conversies';
                        } else {
                            echo 'Conversie';
                        } ?>
                    </div>
                    <div class="widget__scope">Afgelopen 30 dagen</div>
                </div>
                <div class="widget__content">
                    <div class="widget__content__inner">
                        <?php if ($forms_submissions && count($forms_submissions) > 0) : ?>
                            <?php foreach ($forms_submissions as $submission):
                                $title = $submission['title'];
                                $link = $submission['link'];
                                $submissions = $submission['submissions'];
                                ?>

                                <div class="widget__content__item">
                                    <a href="<?= $link; ?>" class="item__link">
                                        <div class="item__label"><?= $title; ?></div>
                                        <div class="item__value"><?= $submissions; ?></div>
                                    </a>
                                </div>
                            
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="widget__content__item">
                                <div class="item__label">Geen formulieren gevonden</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function get_forms_submissions(): array
    {
        $forms = \GFAPI::get_forms();
        $forms_submissions = [];
        
        foreach ($forms as $form) {
            $form_id = $form['id'];
            $form_title = $form['title'];
            $form_link = admin_url('admin.php?page=gf_entries&view=entries&id=' . $form_id);
            $form_submissions = \GFAPI::count_entries($form_id);
            
            $forms_submissions[] = [
                'title' => $form_title,
                'link' => $form_link,
                'submissions' => $form_submissions
            ];
        }
        
        return $forms_submissions;
    }
    
    function get_form_submissions_by_id() : array
    {
        $form_ids = $this->get_selected_forms();
        
        if (is_wp_error($form_ids)) {
            return [];
        }
        
        $forms_submissions = [];
        
        foreach ($form_ids as $form_id) {
            $form = \GFAPI::get_form($form_id);
            $form_title = $form['title'];
            $form_link = admin_url('admin.php?page=gf_entries&view=entries&id=' . $form_id);
            $form_submissions = \GFAPI::count_entries($form_id);
            
            $forms_submissions[] = [
                'title' => $form_title,
                'link' => $form_link,
                'submissions' => $form_submissions
            ];
        }
        
        return $forms_submissions;
    }
    
    public function get_total_submissions(): int
    {
        if (!class_exists('GFAPI')) {
            return new WP_Error('gfapi_not_found', 'GFAPI class not found.');
        }
        
        // Get the current date and the date 30 days ago
        $end_date = date('Y-m-d H:i:s');
        $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        // Set up search criteria
        $search_criteria = array(
            'status'        => 'active',
            'field_filters' => array(
                array(
                    'key'   => 'date_created',
                    'value' => $start_date,
                    'operator' => '>=',
                ),
                array(
                    'key'   => 'date_created',
                    'value' => $end_date,
                    'operator' => '<=',
                ),
            ),
        );
        
        $entries = \GFAPI::get_entries(null, $search_criteria);
        
        if(is_wp_error($entries)) {
            return $entries;
        }
        
        return count($entries);
    }
    
    public function get_selected_forms()
    {
        $forms = $this->get_settings('gf_form_select_field');
        
        if (is_wp_error($forms)) {
            return null;
        }
        
        if (!is_array($forms)) {
            $forms = [$forms];
        }
        
        return $forms;
    }
    public function get_settings($option)
    {
        $settings = get_option('tempel-widget-settings-data');
        return $settings[$option];
    }
}

