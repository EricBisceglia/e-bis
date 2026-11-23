<?php /***************************************************************************************************************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/

// File inclusions
include_once __DIR__.'/../lang/common.lang.php';

// Set a default language
$lang       = user_get_language();
$lang_lower = strtolower($lang);
$lang_other = ($lang === 'EN') ? 'fr' : 'en';

// Prepare the translations array
$GLOBALS['translations'] = array();

// Make sure the cache updates between language changes
header('Cache-Control: private, no-cache');
header('Vary: Accept-Language, Cookie');

// Change language if requested via URL
$requested_lang = $_GET['lang'] ?? '';
if(is_string($requested_lang))
  $requested_lang = strtoupper($requested_lang);

// Handle language change requests
if(in_array($requested_lang, array('EN', 'FR'), true))
{
  // Create the language cookie
  setcookie('ebis_language', $lang, array(
    'expires'  => time() + 60 * 60 * 24 * 365,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
  ));

  // Remove the language change request parameter from the URL
  $parameters = $_GET;
  unset($parameters['lang']);

  // Rebuild the correct page URL after removing the language parameter
  $page_url = explode('?', $_SERVER['REQUEST_URI'] ?? '/')[0];
  $page_url = '/'.ltrim($page_url, '/');
  if(!empty($parameters))
    $page_url .= '?'.http_build_query($parameters);

  // Redirect to the updated URL
  header('Location: '.$page_url, true, 302);
  exit;
}