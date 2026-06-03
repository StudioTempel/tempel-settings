<?php

namespace Tempel;

function widget_conversion_item($link, $title, $submissions) {
    ?>
    
    <div class="widget__content__item">
        <a href="<?= esc_url($link); ?>" class="item__link">
            <div class="item__label"><?= esc_html($title); ?></div>
            <div class="item__value"><?= esc_html($submissions); ?></div>
        </a>
    </div>
    
    <?php
}
