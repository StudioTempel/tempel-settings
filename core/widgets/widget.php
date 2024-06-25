<?php

namespace Tempel\Core\Widgets;

abstract class Widget
{
    public $widget_id;

    public $color;

    public $title;

    public $type;

    public $widget;

    public function __construct()
    {
        $this->widget_id = '';
        $this->color = '';          // Either 'yellow', 'black' or 'white'
        $this->title = '';
        $this->type = '';           // Either 'conversions, 'status', or 'support'

        add_action('wp_dashboard_setup', array($this, 'add_widget'));
    }

    public function add_widget($widget)
    {
        wp_add_dashboard_widget(
            $this->widget_id,
            $this->title,
            array($this, 'render_widget')
        );
    }

    public function render_widget()
    {
        $widget_id = $this->widget_id;
        $color = $this->color;
        $title = $this->title;
        $type = $this->type;

        $this->widget = '';

        echo $this->widget;
    }
    
    public function get_widget_icon()
    {
        return include('assets/widget_icon.php');
    }

//    public function render_conversions_widget($widget_id, $color, $title)
//    {
//        $widget = '
//            <div class="widget widget--conversions widget--' . $color . '">
//                <div class="widget__inner">
//                    <div class="widget_icon">
//
//                    </div>
//                    <div class="widget__header">
//                        <div class="conversion-value"></div>
//                        <div class="conversion-title">Conversies</div>
//                        <div class="conversion-scope">Afgelopen 30 dagen</div>
//                    </div>
//                    <div class="widget__content">
//                        <div class="conversion">
//                            <div class="conversion__source">Contact</div>
//                            <div class="conversion__value">31</div>
//                        </div>
//                        <div class="conversion">
//                            <div class="conversion__source">Nieuwsbrief</div>
//                            <div class="conversion__value">12</div>
//                        </div>
//                        <div class="conversion">
//                            <div class="conversion__source">Solliciteren</div>
//                            <div class="conversion__value">6</div>
//                        </div>
//                    </div>
//                </div>
//            </div>
//        ';
//    }
}