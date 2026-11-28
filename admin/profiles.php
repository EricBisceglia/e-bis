<?php /***************************************************************************************************************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/

// File inclusions
include_once __DIR__.'/../inc/includes.inc.php'; # Core
include_once __DIR__.'/../inc/profiles.inc.php'; # Profile storage
include_once __DIR__.'/../lang/main.lang.php';   # Shared translations
include_once __DIR__.'/../lang/admin.lang.php';  # Admin translations

// Page settings
$path            = '../';
$page_stylesheet = 'admin';
$admin_page      = 'profiles';




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     BACK END                                                      */
/*                                                                                                                   */
/*********************************************************************************************************************/

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Profile selection

// Check which profile is selected
$profile_current = $_GET['profile'] ?? 'tech';

// Ensure the selected profile is valid
if(!in_array($profile_current, array('tech', 'security', 'games'), true))
  $profile_current = 'tech';

// Prepare profile navigation links
$profile_links = array();
foreach(array('tech', 'security', 'games') as $profile)
{
  $profile_links[] = array(
    'label'   => __('home_'.$profile),
    'url'     => './profiles?profile='.$profile,
    'current' => $profile === $profile_current,
  );
}

// Prepare the profile heading
$profile_heading = __('home_'.$profile_current);




///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Profile editor

// Prepare default form values
$profile_values  = profile_defaults();
$profile_error   = '';
$profile_version = '';
$profile_storage = true;

// Prepare the submission URL
$profile_action = './profiles?profile='.$profile_current;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Session & form tokens

// Start a session if it is not already active
if(session_status() !== PHP_SESSION_ACTIVE)
{
  session_name('ebis_admin');
  session_start(array(
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
  ));
}

// Prepare a token for profile forms
$_SESSION['profile_token'] ??= bin2hex(random_bytes(32));
$profile_token = $_SESSION['profile_token'];


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Load the selected profile

// Fetch profiles
try
{
  $profiles        = profiles_get();
  $profile_values  = $profiles[$profile_current];
  $profile_version = hash('sha256', json_encode($profile_values, JSON_THROW_ON_ERROR));
}

// Handle errors when fetching profiles
catch(Exception $error)
{
  error_log($error->getMessage());
  $profile_error   = 'admin_profile_error_storage';
  $profile_storage = false;
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Save the selected profile

if(($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')
{
  // Use the submitted profile version rather than the one loaded previously
  $profile_version = is_string($_POST['profile_version'] ?? null)
                   ? $_POST['profile_version']
                   : '';

  try
  {
    // Retrieve the submitted profile data
    $submitted = $_POST['profile'] ?? array();

    // Stop here if the submitted profile is not an array
    if(!is_array($submitted))
      throw new InvalidArgumentException("Invalid profile.");

    // Validate and normalize the submitted translations for each language
    foreach(array('en', 'fr') as $profile_language)
    {
      // Fetch the translation for the current language
      $translation = $submitted[$profile_language] ?? array();

      // Stop here if the translation is not an array
      if(!is_array($translation))
        throw new InvalidArgumentException("Invalid profile translation.");

      // Normalize the translation array for the current language
      foreach(array('introduction', 'skills') as $field)
      {
        // Fetch the value for the current field
        $value = $translation[$field] ?? '';

        // Stop here if the value is not a string
        if(!is_string($value))
          throw new InvalidArgumentException("Invalid profile field.");

        // Store the normalized value for the current field
        $profile_values[$profile_language][$field] = trim(str_replace(array("\r\n", "\r"), "\n", $value));
      }
    }

    // Fetch the form token
    $submitted_token = $_POST['profile_token'] ?? '';

    // Stop here if the form token is invalid
    if(!is_string($submitted_token) || !hash_equals($profile_token, $submitted_token))
      $profile_error = 'admin_profile_error_request';

    // Validate the skill lines for each language
    foreach(array('en', 'fr') as $profile_language)
    {
      // Split the skills into individual lines for validation
      foreach(explode("\n", $profile_values[$profile_language]['skills']) as $line)
      {
        // Skip empty lines
        if(trim($line) === '')
          continue;

        // Split the line into name and description parts
        $parts = explode('|', $line, 2);

        // Stop here if the line does not contain both a name and a description
        if(count($parts) !== 2 || trim($parts[0]) === '' || trim($parts[1]) === '')
        {
          if(!$profile_error)
            $profile_error = 'admin_profile_error_skills';
        }
      }
    }

    // Save the profile only if there are no errors
    if(!$profile_error && $profile_storage)
    {
      // Save the profile with the current values and version
      profile_save($profile_current, $profile_values, $profile_version);

      // Renew the form token after saving
      $_SESSION['profile_token'] = bin2hex(random_bytes(32));

      // Redirect to the same profile page after updating
      exit(header('Location: '.$profile_action.'&saved=1', true, 303));
    }
  }

  // Handle exceptions during profile processing
  catch(InvalidArgumentException $error)
  {
    $profile_error = 'admin_profile_error_invalid';
  }

  // Handle domain exceptions separately
  catch(DomainException $error)
  {
    $profile_error = 'admin_profile_error_changed';
  }

  // Handle general exceptions
  catch(Exception $error)
  {
    error_log($error->getMessage());
    $profile_error = 'admin_profile_error_storage';
  }
}

// Escape the profile action URL for HTML output
$profile_action = htmlspecialchars($profile_action, ENT_QUOTES, 'UTF-8');




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     FRONT END                                                     */
/*                                                                                                                   */
/*******************************************************************/ include_once __DIR__.'/../inc/header.inc.php'; ?>

  <body class="admin">
    <div class="page">

      <?php include_once __DIR__.'/../inc/admin_header.inc.php'; ?>

      <main>

        <?php if($profile_error): ?>
        <p class="admin-feedback"><?=__($profile_error)?></p>
        <?php elseif(($_GET['saved'] ?? '') === '1'): ?>
        <p class="admin-feedback"><?=__('admin_profile_saved')?></p>
        <?php endif; ?>

        <section class="admin-section">
          <h2><?=__('admin_profiles')?></h2>

          <ul class="admin-profiles">
            <?php foreach($profile_links as $profile_link): ?>
            <li>
              <?php if($profile_link['current']): ?>
              <span><?=$profile_link['label']?></span>
              <?php else: ?>
              <a href="<?=$profile_link['url']?>"><?=$profile_link['label']?></a>
              <?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>
        </section>

        <section class="admin-section">
          <h2><?=$profile_heading?></h2>

          <form class="entry-editor" method="post" action="<?=$profile_action?>">
            <input type="hidden" name="profile_token" value="<?=$profile_token?>">
            <input type="hidden" name="profile_version" value="<?=htmlspecialchars($profile_version, ENT_QUOTES, 'UTF-8')?>">
            <div class="entry-translations">
              <?php foreach(array('en', 'fr') as $profile_language): ?>
              <fieldset>
                <legend><?=__('admin_language_'.$profile_language)?></legend>

                <div class="admin-field">
                  <label for="profile_<?=$profile_language?>_introduction">
                    <?=__('admin_profile_introduction')?>
                  </label>

                  <textarea id="profile_<?=$profile_language?>_introduction" name="profile[<?=$profile_language?>][introduction]" rows="5"><?=htmlspecialchars($profile_values[$profile_language]['introduction'], ENT_QUOTES, 'UTF-8')?></textarea>
                </div>

                <div class="admin-field">
                  <label for="profile_<?=$profile_language?>_skills">
                    <?=__('admin_profile_skills')?>
                  </label>

                  <textarea id="profile_<?=$profile_language?>_skills" name="profile[<?=$profile_language?>][skills]" rows="8"><?=htmlspecialchars($profile_values[$profile_language]['skills'], ENT_QUOTES, 'UTF-8')?></textarea>

                  <p class="admin-help"><?=__('admin_profile_skills_help')?></p>
                </div>
              </fieldset>
              <?php endforeach; ?>
            </div>

            <div>
              <button type="submit"><?=__('admin_profile_save')?></button>
            </div>
          </form>
        </section>
      </main>

    </div>
  </body>
</html>