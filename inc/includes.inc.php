<?php if(realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(403); exit; } /***************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/

// File inclusions
include_once __DIR__.'/../lang/common.lang.php';

// Determine the current language
$lang       = user_get_language();
$lang_lower = strtolower($lang);
$lang_other = ($lang === 'EN') ? 'fr' : 'en';

// Determine the current color theme
$theme = $_COOKIE['ebis_theme'] ?? '';
if(!in_array($theme, array('light', 'dark'), true))
  $theme = '';

// Determine which color theme preference to apply
$theme_media = '(prefers-color-scheme: dark)';
if($theme === 'light')
  $theme_media = 'not all';
else if($theme === 'dark')
  $theme_media = 'all';

// Prepare translations
$GLOBALS['translations'] = array();

// Make sure the cache updates between settings changes
header('Cache-Control: private, no-cache');
header('Vary: Accept-Language, Cookie');




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                    PREFERENCES                                                    */
/*                                                                                                                   */
/*********************************************************************************************************************/

// Prepare cookie settings
$cookie_options = array(
  'expires'  => time() + 60 * 60 * 24 * 365,
  'path'     => '/',
  'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
  'httponly' => true,
  'samesite' => 'Lax',
);

// Track whether a preference was changed
$preference_changed = false;

// Fetch the requested language
$requested_lang = $_GET['lang'] ?? '';
if(is_string($requested_lang))
  $requested_lang = strtoupper($requested_lang);

// Update the language preferences if they are a valid language
if(in_array($requested_lang, array('EN', 'FR'), true))
{
  setcookie('ebis_language', $requested_lang, $cookie_options);
  $preference_changed = true;
}

// Fetch the requested theme
$requested_theme = $_GET['theme'] ?? '';

// Update the theme preferences if they are a valid theme
if(in_array($requested_theme, array('light', 'dark'), true))
{
  setcookie('ebis_theme', $requested_theme, $cookie_options);
  $preference_changed = true;
}

// Return to the current page after saving preferences
if($preference_changed)
{
  // Remove preference selections from the URL
  $parameters = $_GET;
  unset($parameters['lang'], $parameters['theme']);

  // Rebuild the current page URL
  $page_url = explode('?', $_SERVER['REQUEST_URI'] ?? '/')[0];
  $page_url = '/'.ltrim($page_url, '/');
  if(!empty($parameters))
    $page_url .= '?'.http_build_query($parameters);

  // Redirect to the rebuilt URL with the new preferences
  exit(header('Location: '.$page_url, true, 302));
}




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                    THEME LINKS                                                    */
/*                                                                                                                   */
/*********************************************************************************************************************/

// Prepare the light mode link
$theme_parameters          = $_GET;
$theme_parameters['theme'] = 'light';
$theme_light_url           = '?'.http_build_query($theme_parameters);

// Prepare the dark mode link
$theme_parameters['theme'] = 'dark';
$theme_dark_url            = '?'.http_build_query($theme_parameters);