<?php

/**
 * Tempel Support Widget
 * 
 * Adds a support widget to the dashboard with a link to the contact page, SEO handbook, form entries and statistics
 * 
 * @since 1.0.0
 */

namespace Tempel\Core;

class TMPLSupportWidget
{

    /**
     * Constructor
     * 
     * @since 1.0.0
     */
    function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'support_widget_setup'));
    }

    /**
     * Add the dashboard widget
     * 
     * @since 1.0.0
     */
    function support_widget_setup()
    {
        wp_add_dashboard_widget(
            'tempel-support-widget',
            'Studio Tempel',
            array($this, 'support_widget_render')
        );
    }

    /**
     * Render callback for dashboard widget
     * 
     * @since 1.0.0
     */
    function support_widget_render()
    {
?>
        <div class='tempel-support-widget'>
            <img src='<?php echo plugin_dir_url(__DIR__) . 'assets/images/studiotempel-logo.svg' ?>' alt='Studio Tempel' />
            <h1>Kunnen <span>we</span>
                helpen?</h1>
            <a href='https://studiotempel.nl/contact/' rel="nofollow" target="_blank" class='button black'>Stel je vraag</a>
            <a target="_blank" rel="nofollow" href='https://studiotempel.nl/handleidingen/handleiding-seo.pdf' class='button'>SEO handboek</a>
            <?php
            // Check if gravity forms is installed
            if (class_exists('GFForms')) {
                echo "<a href='admin.php?page=gf_entries' class='button'>Formulier inzendingen</a>";
            }

            // Check if ExactMetrics plugin is active
            if (class_exists('ExactMetrics')) {
                echo "<a href='admin.php?page=exactmetrics_reports' class='button'>Statistieken</a>";
            }
            ?>
        </div>
<?php
    }
}
