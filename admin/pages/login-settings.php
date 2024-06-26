<?php

namespace Tempel\Admin\Pages;

require_once TMPL_PLUGIN_DIR . 'admin/abstract/Page.php';

use Tempel\Admin\Abstract\Page;

class LoginPageSettings extends Page
{
    public function render()
    {
        ?>
        <div class="tmpl_settings__wrap">
            <div class="tmpl_settings__page" id="tmpl_widget_settings">
                <div class="tmpl_settings__inner">
                    <div class="settings__header">
                        <div class="header__inner">
                            <div class="header__title">
                                <?php _e('Widget Settings', 'tempel-settings'); ?>
                            </div>
                            <div class="header__nav">
                                <div class="nav__inner">
                                    <a href="/wp-admin/admin.php?page=tempel-settings" class="nav__item">
                                        <?php _e('General Settings', 'tempel-settings'); ?>
                                    </a>
                                    <a href="/wp-admin/admin.php?page=tempel-widget-settings" class="nav__item">
                                        <?php _e('Widget Settings', 'tempel-settings'); ?>
                                    </a>
                                    <a href="/wp-admin/admin.php?page=tempel-login-settings" class="nav__item active">
                                        <?php _e('Login Settings', 'tempel-settings'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings__body">
                        <div class="body__inner">
                            <form id="login-settings-form" action="options.php" method="post">
                                <?php settings_fields('tempel-login-page-settings'); ?>

                                <div class="settings__category">
                                    <div class="category__header">
                                        <div class="category__title">
                                            <?php _e('Content', 'tempel-settings'); ?>
                                        </div>
                                        <div class="category__description">
                                            <?php //_e('A collection of general settings to disable unused features of Wordpress.', 'tempel'); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="category__content">

                                        <!-- Settings Field | Use Image from Hub -->
                                        <div id="login-bg-image-src-hub" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Get Login BG Image from Studiotempel.nl', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <label class="checkbox__switch" for="login_bg_img_src">
                                                        <input
                                                                type="checkbox"
                                                                name="tempel-settings-data[login_bg_img_src]"
                                                                id="login_bg_img_src"
                                                            <?php echo $this->is_checked('login_bg_img_src'); ?>
                                                        >
                                                        <span class="checkbox__switch__slider"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Use Image from Hub -->

                                        <!-- Settings Field | Login Background Image Upload -->
                                        <div id="login-bg-image" class="settings__field">
                                            <div class="settings__field__inner">
                                                <div class="settings__label__wrap">
                                                    <label for="reset-checkup-field">
                                                        <?php _e('Login Background Image', 'tempel-settings'); ?>
                                                    </label>
                                                </div>
                                                <div class="settings__input__wrap">
                                                    <div class="input__wrap__inner">
                                                        <input
                                                                type="file"
                                                                name="login_bg_image"
                                                                id="login_bg_image"
                                                        >
                                                        <button class="button button-secondary" id="upload_login_bg_image">
                                                            <?php _e('Upload', 'tempel-settings'); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Settings Field | Login Background Image Upload -->
                                        
                                    </div>
                                </div>
                                

                                
                                <!-- Settings Form Footer -->
                                <div class="settings__form__footer">
                                    <div class="form__footer__inner">
                                        <?php submit_button(); ?>
                                    </div>
                                </div>
                                <!-- Settings Form Footer -->
                            </form>
                        </div>
                    </div>
                    <div class="settings__footer">
                    
                    </div>
                </div>
            </div>
        </div>
        <script>
            let pondFile,
                formData;
            
            jQuery(document).ready(function ($) {
                
                let imagePond = FilePond.create(
                    document.querySelector("input#login_bg_image"), {
                        allowMultiple: false,
                        allowRevert: false,
                        allowReplace: false,
                        allowProcess: false,
                        instantUpload: false,
                        acceptedFileTypes: ['image/*'],
                    }
                )
                
                $("#upload_login_bg_image").click(function (e) {
                    e.preventDefault();
                    
                    pondFile = imagePond.getFile();
                    formData = new FormData();
                    
                    if(pondFile) {
                        formData.append('pondFile', pondFile.file);
                        formData.append('action', 'save_login_image_to_plugin_upload_folder');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            processData: false,
                            contentType: false,
                            data: formData,
                            success: function (response) {
                                console.log(response);
                            },
                            error: function (error) {
                                console.log(error);
                            }
                        });
                    }
                });

            });
        </script>
        <?php
    }
    
    public function enqueue_scripts(): void
    {
        if(isset($_GET['page']) && $_GET['page'] === 'tempel-login-settings') {
            // Vendor assets
            // Filepond
            wp_enqueue_style('tmpl-settings-filepond', plugin_dir_url(__FILE__) . '../../dist/vendor/filepond.min.css');
            wp_enqueue_script('tmpl-settings-filepond', plugin_dir_url(__FILE__) . '../../dist/vendor/filepond.min.js');
        }
    }
    
    public function is_checked($args)
    {
        $option = get_option('tempel-settings-data');
        if ($option) {
            $checkbox_value = $option[$args] ?? false;
        } else {
            $checkbox_value = false;
        }
        
        return checked("on", $checkbox_value, false);
    }
}