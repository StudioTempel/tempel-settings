<?php

namespace Tempel;

require_once TEMPEL_SETTINGS_DIR . 'src/includes/helper-functions.php';

function get_support_ticket_link()
{
    $support_ticket_link = return_option('tmpl_widget_settings', 'support_ticket_link');
    
    if (empty($support_ticket_link) || !filter_var($support_ticket_link, FILTER_VALIDATE_URL)) {
        return 'https://studiotempel.nl/contact';
    }
    
    return $support_ticket_link;
}
