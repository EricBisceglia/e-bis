<?php if(realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(403); exit; } /***************/
/*                                                                                                                   */
/*                                                  PROFILE STORAGE                                                  */
/*                                                                                                                   */
/*********************************************************************************************************************/

/**
 * Returns the default content of a profile.
 *
 * @return  array  The empty translated fields.
 */

function profile_defaults() : array
{
  // Initialize the profile with empty translations
  $profile = array();
  foreach(array('en', 'fr') as $language)
  {
    $profile[$language] = array(
      'introduction' => '',
      'skills'       => '',
    );
  }

  // Return the empty profile
  return $profile;
}




/**
 * Fetches saved profiles.
 *
 * @return  array  The saved profiles.
 */

function profiles_get() : array
{
  // Read the profile JSON storage
  $content = @file_get_contents(__DIR__.'/../data/profiles.json');

  // Stop here if the file cannot be read
  if($content === false)
    throw new RuntimeException("Could not read profiles.json.");

  // Decode the JSON content into an array
  $profiles = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

  // Stop here if the decoded content feels wrong
  if(!is_array($profiles) || !str_starts_with(ltrim($content), '{'))
    throw new RuntimeException("Invalid profile storage.");

  // Validate each saved profile's structure
  foreach($profiles as $name => $profile)
  {
    // Validate the profile name and structure
    if(!in_array($name, array('tech', 'security', 'games'), true) || !is_array($profile))
      throw new RuntimeException("Invalid profile.");

    // Validate each language translation within the profile
    foreach(array('en', 'fr') as $language)
    {
      // Ensure the language translation exists and is an array
      if(!isset($profile[$language]) || !is_array($profile[$language]))
        throw new RuntimeException("Invalid profile translation.");

      // Validate each required field within the language translation
      foreach(array('introduction', 'skills') as $field)
      {
        if(!isset($profile[$language][$field]) || !is_string($profile[$language][$field]))
          throw new RuntimeException("Invalid profile field.");
      }
    }
  }

  // Initialize missing profiles with default content
  foreach(array('tech', 'security', 'games') as $name)
    $profiles[$name] ??= profile_defaults();

  // Return the profiles
  return $profiles;
}




/**
 * Updates a profile.
 *
 * @param   string  $name     The profile to update.
 * @param   array   $profile  The translated profile content.
 * @param   string  $version  The version opened in the editor.
 *
 * @return  void
 */

function profile_save( string  $name    ,
                       array   $profile ,
                       string  $version ) : void
{
  // Validate the profile name
  if(!in_array($name, array('tech', 'security', 'games'), true))
    throw new InvalidArgumentException("Invalid profile name.");

  // Validate the translated fields for each language
  foreach(array('en', 'fr') as $language)
  {
    foreach(array('introduction', 'skills') as $field)
    {
      if(!isset($profile[$language][$field]) || !is_string($profile[$language][$field]))
        throw new InvalidArgumentException("Invalid profile field.");
    }
  }

  // Open the storage lock to ensure exclusive access
  $lock = @fopen(__DIR__.'/../data/profiles.lock', 'c');

  // Stop here if the lock can't be used
  if($lock === false)
    throw new RuntimeException("Could not open the profile lock.");

  // Keep track of the temporary file for cleanup
  $temporary_file = false;

  // Update the profile
  try
  {
    // Lock the storage to ensure exclusive access during the update
    if(!flock($lock, LOCK_EX))
      throw new RuntimeException("Could not lock profile storage.");

    // Fetch the latest profiles from storage
    $profiles = profiles_get();

    // Compute the current version of the profile to detect changes
    $saved_version = hash('sha256', json_encode($profiles[$name], JSON_THROW_ON_ERROR));

    // Stop here if the profile does not exist in storage
    if(!hash_equals($saved_version, $version))
      throw new DomainException("The profile has changed.");

    // Update the selected profile
    $profiles[$name] = array_replace_recursive($profiles[$name], $profile);
    $content = json_encode($profiles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";

    // Create a temporary file with the updated profile
    $temporary_file = @tempnam(__DIR__.'/../data', 'profiles-');

    // Stop here if the temporary file could not be created
    if($temporary_file === false)
      throw new RuntimeException("Could not create temporary profile storage.");

    // Stop here if writing to the temporary file fails
    if(@file_put_contents($temporary_file, $content) !== strlen($content))
      throw new RuntimeException("Could not write profile storage.");

    // Replace the original profile storage with the new one
    if(!@rename($temporary_file, __DIR__.'/../data/profiles.json'))
      throw new RuntimeException("Could not replace profile storage.");

    // Stop tracking the temporary file
    $temporary_file = false;
  }

  // Clean up resources and release the lock, regardless of success or failure
  finally
  {
    // Remove the temporary file if it still exists
    if($temporary_file !== false)
      @unlink($temporary_file);

    // Release the storage lock and close the lock file
    flock($lock, LOCK_UN);
    fclose($lock);
  }
}