<?php

namespace Tempel;

abstract class Widget
{
    public string $widget_id;
    
    public string $color;
    
    public string $title;
    
    public string $type;
    
    public mixed $widget;
    
    public function __construct()
    {
        $this->widget_id = '';
        $this->color = '';          // Either 'yellow', 'black' or 'white'
        $this->title = '';
        $this->type = '';           // Either 'conversions, 'status', or 'support'
        
        add_action('wp_dashboard_setup', array($this, 'add_widget'));
    }
    
    public function add_widget()
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
    
    public static function get_widget_icon()
    {
        return include(TEMPEL_SETTINGS_DIR . 'src/widgets/partials/widget-icon.php');
    }
}