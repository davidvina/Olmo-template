<?php
/**
 * @file
 * olmo preprocess functions and theme function overrides.
 */


/*
 * Initialize theme settings
 */

if (is_null(theme_get_setting('meta_generator'))) {
  // Save default theme settings.
  config_set('olmo.settings', 'meta_generator', true);
  config_set('olmo.settings', 'rss_feeds', false);
  config_set('olmo.settings', 'theme_favicon', false);
  config_set('olmo.settings', 'toolbar_color_chrome', '#ffffff');
  config_set('olmo.settings', 'pin-tab-safari', '#ffffff');
  // Force refresh of Backdrop internals.
  theme_get_setting('', TRUE);
}



function addFavicons(){
    // output from
    //  http://realfavicongenerator.net


    // url to favicons
    global $base_url;
    global $theme_path;

    $url_favicons = $base_url .'/'. $theme_path.'/images/favicons/';


    // add favicons
    $html_favicons = array();


    $html_favicons['favicon_apple-touch-icon'] = array(
        '#type' => 'markup',
        '#markup' => "    <link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"" . $url_favicons ."apple-touch-icon.png\">\n",
        '#weight' => '100',
    );

    $html_favicons['favicon-32x32'] = array(
        '#type' => 'markup',
        '#markup' => "    <link rel=\"icon\" type=\"image/png\" href=\"" . $url_favicons ."favicon-32x32.png\" sizes=\"32x32\">\n",
        '#weight' => '110',
    );


    $html_favicons['favicon-16x16'] = array(
        '#type' => 'markup',
        '#markup' => "    <link rel=\"icon\" type=\"image/png\" href=\"" . $url_favicons ."favicon-16x16.png\" sizes=\"16x16\">\n",
        '#weight' => '120',
    );

    $html_favicons['favicon-192x192'] = array(
        '#type' => 'markup',
        '#markup' => "    <link rel=\"icon\" type=\"image/png\" href=\"" . $url_favicons ."android-chrome-192x192.png\" sizes=\"192x192\">\n",
        '#weight' => '125',
    );
    
    $html_favicons['manifes.json'] = array(
        '#type' => 'markup',
        '#markup' => "    <link rel=\"manifest\" href=\"" . $url_favicons ."manifes.json\">\n",
        '#weight' => '130',
    );


    $html_favicons['safari-pinned-tab'] = array(
        '#type' => 'markup',
        '#markup' => "    <link rel=\"mask-icon\" href=\"" . $url_favicons ."safari-pinned-tab.svg\" color=\"".theme_get_setting('pin-tab-safari')."\">\n",
        '#weight' => '140',
    );

    // toolbar color on chrome
    $html_favicons['theme-color'] = array(
        '#type' => 'markup',
        '#markup' => "    <meta name=\"theme-color\" content=\"".theme_get_setting('toolbar_color_chrome')."\">\n",
        '#weight' => '150',
    );

    // add favicos to head
    foreach ($html_favicons as $key => $value) {
      backdrop_add_html_head($value, $key); 
    }
}



/**
 * Implements hook_html_head_alter 
 *
 *  https://api.backdropcms.org/api/backdrop/core%21modules%21system%21system.api.php/function/hook_html_head_alter/1
 *
 * @see maintenance_page.tpl.php
 */


function olmo_html_head_alter(&$head_elements){

  foreach ($head_elements as $key => $element) {
  
    if(preg_match('/backdrop_add_html_head_link:alternate:.*/', $key)) {

      if(theme_get_setting('rss_feeds')){
          // Remove rss link
          unset($head_elements[$key]);

      } else {
          // repair self closure on charset and viewport tag
          $newRSSLink = "<link";
          
          foreach ($head_elements[$key]['#attributes'] as $atributeKey => $atributeValue) {
            $newRSSLink .= ' '.$atributeKey.'="'.$atributeValue.'"';
          }
          $newRSSLink .= ">";

          // overwrite rss link
          $head_elements[$key] = array(
            '#type' => 'markup',
            '#markup' => "    ".$newRSSLink."\n",
            '#weight' => '-1000',
          ); 
      }
    }
    
    if(theme_get_setting('theme_favicon')){
      // Remove existing favicon location
      if (preg_match('/backdrop_add_html_head_link:shortcut icon:.*/', $key)) {
        unset($head_elements[$key]);
      }
    }

    if(theme_get_setting('meta_generator')){
      // disable meta_generator backdrop
      if (preg_match('/system_meta_generator.*/', $key)) {
        unset($head_elements[$key]);
      }
    }

  }  

  // repair self closure on charset and viewport tag
  $head_elements['system_meta_content_type'] = array(
      '#type' => 'markup',
      '#markup' => "<meta charset=\"utf-8\">\n",
      '#weight' => '-1000',
  );

  $head_elements['viewport'] = array(
      '#type' => 'markup',
      '#markup' => "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n",
      '#weight' => '200',
  );
} 


/**
 * Implements hook_css_alter().
 *
 * @see maintenance_page.tpl.php
 */
function olmo_css_alter(&$css) {
//  dsm($css);
}


/**
 * Implements hook_preprocess_page().
 *
 * @see maintenance_page.tpl.php
 */
function olmo_preprocess_page(&$variables) {
  $node = menu_get_object();

  // call to function of add favicons
  if(theme_get_setting('theme_favicon')){
    addFavicons();
  }

  // To add a class 'page-node-[nid]' to each page.
  if ($node) {
    $variables['classes'][] = 'page-node-' . $node->nid;
  }

  // To add a class 'view-name-[name]' to each page.
  $view = views_get_page_view();
  if ($view) {
    $variables['classes'][] = 'view-name-' . $view->name;
  }
}







/**
 * Implements template_preprocess_page().
 *
 * @see layout.tpl.php
 */
function olmo_preprocess_layout(&$variables) {
  if ($variables['is_front']) {
    $variables['classes'][] = 'layout-front';
  }
}

/**
 * Implements template_preprocess_header().
 *
 * @see header.tpl.php
 */
function olmo_preprocess_header(&$variables) {

  $logo = $variables['logo'];

  // Add classes and height/width to logo.
  if ($logo) {
    $logo_attributes = array();
    $logo_wrapper_classes = array();
    $logo_wrapper_classes[] = 'header-logo-wrapper';
    $logo_size = getimagesize($logo);
    if (!empty($logo_size)) {
      if ($logo_size[0] < $logo_size[1]) {
        $logo_wrapper_classes[] = 'header-logo-tall';
      }
      $logo_attributes['width'] = $logo_size[0];
      $logo_attributes['height'] = $logo_size[1];
    }

    $variables['logo_wrapper_classes'] = $logo_wrapper_classes;
    $variables['logo_attributes'] = $logo_attributes;
  }

}

/**
 * Overrides theme_breadcrumb().
 *
 * Removes &raquo; from markup.
 *
 * @see theme_breadcrumb().
 */
function olmo_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $output = '';
  if (!empty($breadcrumb)) {
    $output .= '<nav role="navigation" class="breadcrumb">';
    // Provide a navigational heading to give context for breadcrumb links to
    // screen-reader users. Make the heading invisible with .element-invisible.
    $output .= '<h2 class="element-invisible">' . t('You are here') . '</h2>';
    $output .= '<ol><li>' . implode('</li><li>', $breadcrumb) . '</li></ol>';
    $output .= '</nav>';
  }
  return $output;
}


