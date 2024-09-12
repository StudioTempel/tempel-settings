<?php

namespace Tempel;

function widget_conversion_item($link, $title, $submissions) {
    ?>
    
    <div class="widget__content__item">
        <a href="<?= $link; ?>" class="item__link">
            <div class="item__label"><?= $title; ?></div>
            <div class="item__value"><?= $submissions; ?></div>
        </a>
    </div>
    
    <?php
}