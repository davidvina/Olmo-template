<?php
/**
 * @file
 * Theme settings file for Olmo.
 *
 */

// https://www.drupal.org/node/1862892
// #9
// needed for working submit and validate
$theme_settings_path = backdrop_get_path('theme', 'olmo') . '/theme-settings.php';

if (file_exists($theme_settings_path) && !in_array($theme__settings_path, $form_state['build_info']['files'])) {
  $form_state['build_info']['files'][] = $theme_settings_path;
}




/**
 * Implements hook_form_system_theme_settings_alter().
 */

function olmo_form_system_theme_settings_alter(&$form, &$form_state, $form_id = NULL) {


  if (isset($form_id)) {
     return;
    }

  $form['nocolor'] = array(
    '#markup' => '<p>' . t("This theme doesn't supports Color module. Please disable this module, for better performance.") . '</p>',
  );


  $form['theme_options'] = array(
    '#type' => 'fieldset',
    '#title' => t('Theme options'),
  );

  $form['theme_options']['theme_favicon'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use theme favicon'),
      '#default_value' => theme_get_setting('theme_favicon', 'olmo'),
      '#description' => t('Use favicons on images/favicons folder, replace with http://realfavicongenerator.net favicons'),
  );

  $form['theme_options']['toolbar_color_chrome'] = array(
      '#type' => 'textfield',
      '#title' => t('Toolbar color on chrome'),
      '#default_value' => theme_get_setting('toolbar_color_chrome', 'olmo'),
      '#description' => t('The favicon icon must be png format.'),
      '#states' => array(
        'visible' => array(   // action to take.
          ':input[name="theme_favicon"]' => array('checked' => TRUE),
        ),
      ),
  );

  $form['theme_options']['pin-tab-safari'] = array(
      '#type' => 'textfield',
      '#title' => t('Pin tab color on Safari'),
      '#default_value' => theme_get_setting('pin-tab-safari', 'olmo'),
      '#description' => t('The favicon icon must be png format.'),
      '#states' => array(
        'visible' => array(   // action to take.
          ':input[name="theme_favicon"]' => array('checked' => TRUE),
        ),
      ),
  );

  $form['theme_options']['meta_generator'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable meta generator'),
      '#default_value' => theme_get_setting('meta_generator', 'olmo'),
      '#description' => t("Disable de <em>meta name=\"Generator\"</em> tag on head"),
  );


  $form['theme_options']['rss_feeds'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable default rss feed'),
      '#default_value' => theme_get_setting('rss_feeds', 'olmo'),
      '#description' => t("Disable de <em>meta name=\"Generator\"</em> tag on head"),
  );

  /* Validate function */
  $form['#validate'][] = 'olmo_form_system_theme_settings_validate';

  /* Submit function */
  $form['#submit'][] = 'olmo_form_system_theme_settings_submit';


}



/**
 * Custom function, same as color module
 * Determines if a hexadecimal CSS color string is valid.
 *
 * @param $color
 *   The string to check.
 *
 * @return
 *   TRUE if the string is a valid hexadecimal CSS color string, or FALSE if it
 *   isn't.
 */
function olmo_color_valid_hexadecimal_string($color) {
  return preg_match('/^#([a-f0-9]{3}){1,2}$/iD', $color);
}


// validate form

function olmo_form_system_theme_settings_validate($form, &$form_state) {

    if (!olmo_color_valid_hexadecimal_string($form_state['values']['toolbar_color_chrome'])) {
      form_set_error('toolbar_color_chrome', t('Must be a valid hexadecimal CSS color value on "Toolbar color on Chrome" field.'));
    }

    if (!olmo_color_valid_hexadecimal_string($form_state['values']['pin-tab-safari'])) {
      form_set_error('pin-tab-safari', t('Must be a valid hexadecimal CSS color value on "Pin tab color on Safari" field.'));
    }

}


/**
 * Implements additional submit logic for responsive_favicons_settings_form().
 */

function olmo_form_system_theme_settings_submit(&$form, &$form_state) {


   // set favicon config
   config_set('olmo.settings', 'theme_favicon', $form_state['values']['theme_favicon']);
   config_set('system.core', 'site_favicon_theme', $form_state['values']['theme_favicon']);

   // set meta_generator
   config_set('olmo.settings', 'meta_generator', $form_state['values']['meta_generator']);

   // set meta_generator
   config_set('olmo.settings', 'rss_feeds', $form_state['values']['rss_feeds']);

   // set toolbar_color_chrome
   config_set('olmo.settings', 'toolbar_color_chrome', $form_state['values']['toolbar_color_chrome']);

   // set pin-tab-safari
   config_set('olmo.settings', 'pin-tab-safari', $form_state['values']['pin-tab-safari']);

}


