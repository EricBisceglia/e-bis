<?php if(realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(403); exit; } /***************/
/*                                                                                                                   */
/*                                                LANGUAGE FUNCTIONS                                                 */
/*                                                                                                                   */
/*********************************************************************************************************************/

 /**
 * Returns a translation, ready for usage in HTML.
 *
 * @param   string  $string  The name of the language string to translate.
 *
 * @return  string           The translated text (or an empty string if it does not exist).
 */

function __( string $string ) : string
{
  // Fetch the requested translation
  $translation = $GLOBALS['translations'][$string] ?? '';

  // Return the translation
  return htmlspecialchars($translation, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}




 /**
 * Adds a translation to the current language's set of translations.
 *
 * @param   string  $name         The translation's name.
 * @param   string  $language     The translation's language.
 * @param   string  $translation  The translated text.
 *
 * @return  void
 */

function ___( string  $name         ,
              string  $language     ,
              string  $translation  ) : void
{
  // Only add translations to the current language
  if($GLOBALS['lang'] !== $language)
    return;

  // Add the translation
  $GLOBALS['translations'][$name] = $translation;
}




 /**
 * Determines which language to use.
 *
 * @return  string  The selected language code.
 */

function user_get_language() : string
{
  // Check for language preference in the URL & cookies
  $preferences = array($_GET['lang'] ?? '', $_COOKIE['ebis_language'] ?? '');

  // Make sure the preferred language is valid and in uppercase
  foreach($preferences as $preference)
  {
    if(!is_string($preference))
      continue;
    $preference = strtoupper($preference);
    if(in_array($preference, array('EN', 'FR'), true))
      return $preference;
  }

  // Prepare variables for browser language detection
  $selected_language = 'EN';
  $selected_quality  = 0;
  $browser_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');

  // Iterate through the browser's accepted languages to look for a match
  foreach($browser_languages as $preference)
  {
    // Make sure to match regional variants (eg. fr-FR, en-GB)
    $parts    = explode(';', trim($preference));
    $language = strtoupper(explode('-', trim($parts[0]))[0]);

    // Skip unsupported languages
    if(!in_array($language, array('EN', 'FR'), true))
      continue;

    // Sort the desired languages by priority
    $quality = 1;
    foreach(array_slice($parts, 1) as $parameter)
    {
      if(preg_match('/^q=([0-9.]+)$/i', trim($parameter), $match))
        $quality = (float)$match[1];
    }

    // Only keep languages with the highest quality (so far)
    if($quality > $selected_quality && $quality <= 1)
    {
      $selected_language = $language;
      $selected_quality  = $quality;
    }
  }

  // Return the selected language based on user preference + browser settings
  return $selected_language;
}