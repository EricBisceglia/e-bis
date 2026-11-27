<?php /***************************************************************************************************************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/

// File inclusions
include_once __DIR__.'/../inc/includes.inc.php';  # Core
include_once __DIR__.'/../inc/entries.inc.php';   # Entry storage
include_once __DIR__.'/../lang/main.lang.php';    # Shared translations
include_once __DIR__.'/../lang/admin.lang.php';   # Admin translations

// Page settings
$path            = '../';
$page_stylesheet = 'admin';
$admin_page      = 'entries';




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     BACK END                                                      */
/*                                                                                                                   */
/*********************************************************************************************************************/

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Prepare the page & forms

// Show the editor when adding an entry
$entry_add = ($_GET['add'] ?? '') === '1';

// Determine the fields used for each entry
$entry_fields = array(
  'title'   => 0,
  'role'    => 0,
  'summary' => 0,
  'body'    => 5,
  'bullets' => 5,
);


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Handle the session & form tokens

// Start a session
if(session_status() !== PHP_SESSION_ACTIVE)
{
  session_name('ebis_admin');
  session_start(array(
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
  ));
}

// Prepare the form token
$_SESSION['entry_token'] ??= bin2hex(random_bytes(32));
$entry_token = $_SESSION['entry_token'];

// Prepare default form values
$entry_values = array(
  'profile' => 'tech',
  'status'  => 'draft',
  'start'   => '',
  'end'     => '',
  'ongoing' => false,
);

// Prepare translated form values
foreach(array('en', 'fr') as $entry_language)
{
  foreach($entry_fields as $field => $rows)
    $entry_values[$entry_language][$field] = '';
}

// Set up error messages
$entry_error = '';


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add/edit/delete entries

// Handle submitted entries
if(($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')
{
  // Default to adding exclusively for now
  $entry_add = true;

  // Add a new entry
  try
  {
    // Check the submitted structure
    $submitted = $_POST['entry'] ?? array();

    // Stop here if the submitted entry is not an array
    if(!is_array($submitted))
      throw new InvalidArgumentException("Invalid entry.");

    // Read the fields
    foreach(array('profile', 'status', 'start', 'end') as $field)
      $entry_values[$field] = entry_input($submitted[$field] ?? '');

    // Read the checkbox
    if(isset($submitted['ongoing']) && $submitted['ongoing'] !== '1')
      throw new InvalidArgumentException("Invalid ongoing value.");
    $entry_values['ongoing'] = isset($submitted['ongoing']);

    // Read translated fields
    foreach(array('en', 'fr') as $entry_language)
    {
      $translation = $submitted[$entry_language] ?? array();
      if(!is_array($translation))
        throw new InvalidArgumentException("Invalid translation.");
      foreach($entry_fields as $field => $rows)
        $entry_values[$entry_language][$field] = entry_input($translation[$field] ?? '');
    }

    // Ongoing entries should not have an end date
    if($entry_values['ongoing'])
      $entry_values['end'] = '';

    // Validate the format of years / months
    $valid_dates = true;
    foreach(array('start', 'end') as $field)
    {
      if($entry_values[$field] !== ''
      && !preg_match('/^(?!0000)[0-9]{4}(?:-(0[1-9]|1[0-2]))?$/D', $entry_values[$field]))
        $valid_dates = false;
    }

    // Treat year-only dates as covering the entire year
    $start_date = $entry_values['start'];
    $end_date   = $entry_values['end'];
    if(strlen($start_date) === 4)
      $start_date .= '-01';
    if(strlen($end_date) === 4)
      $end_date .= '-12';

    // Check the submitted form token
    $submitted_token = $_POST['entry_token'] ?? '';

    // Stop here if the form token is invalid
    if(!is_string($submitted_token) || !hash_equals($entry_token, $submitted_token))
      $entry_error = 'admin_entry_error_request';

    // Stop here if key fields are invalid
    else if(!in_array($entry_values['profile'], array('tech', 'security', 'games'), true)
         || !in_array($entry_values['status'], array('draft', 'published'), true)
         || !$valid_dates)
      $entry_error = 'admin_entry_error_invalid';

    // Stop here if no title is provided in any language
    else if($entry_values['en']['title'] === '' && $entry_values['fr']['title'] === '')
      $entry_error = 'admin_entry_error_title';

    // Stop here if the dates are not in chronological order
    else if($start_date !== '' && $end_date !== '' && $end_date < $start_date)
      $entry_error = 'admin_entry_error_dates';

    // Save the entry if no errors occurred
    if(!$entry_error)
    {
      // Add the entry to JSON storage
      entries_add($entry_values);

      // Replace the form token after a successful save
      $_SESSION['entry_token'] = bin2hex(random_bytes(32));

      // Redirect to the entries page
      exit(header('Location: ./entries?saved=1', true, 303));
    }
  }

  // Handle any issues during entry processing
  catch(InvalidArgumentException $error)
  {
    $entry_error = 'admin_entry_error_invalid';
  }

  // Handle any other unexpected issues
  catch(Exception $error)
  {
    error_log($error->getMessage());
    $entry_error = 'admin_entry_error_storage';
  }
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Fetch entries

// Prepare entries for display
$entries = array();
try
{
  $entries = entries_get();
}

// Handle issues when fetching entries
catch(Exception $error)
{
  error_log($error->getMessage());

  if(!$entry_error)
    $entry_error = 'admin_entry_error_storage';
}




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     FRONT END                                                     */
/*                                                                                                                   */
/*******************************************************************/ include_once __DIR__.'/../inc/header.inc.php'; ?>

  <body class="admin">
    <div class="page">

      <?php include_once __DIR__.'/../inc/admin_header.inc.php'; ?>

      <main>

        <?php if($entry_error): ?>
        <p class="admin-feedback"><?=__($entry_error)?></p>
        <?php elseif(($_GET['saved'] ?? '') === '1'): ?>
        <p class="admin-feedback"><?=__('admin_entry_saved')?></p>
        <?php endif; ?>

        <?php if($entry_add): ?>
        <section class="admin-section" id="entry-editor">
          <div class="admin-section-heading">
            <h2><?=__('admin_entry_add')?></h2>
            <a href="./entries"><?=__('admin_entry_cancel')?></a>
          </div>

          <form class="entry-editor" method="post" action="./entries?add=1#entry-editor">
            <input type="hidden" name="entry_token" value="<?=$entry_token?>">

            <div class="entry-settings">
              <div class="admin-field">
                <label for="entry_profile"><?=__('admin_entry_profile')?></label>

                <select id="entry_profile" name="entry[profile]">
                  <option value="tech"<?=$entry_values['profile'] === 'tech' ? ' selected' : ''?>><?=__('home_tech')?></option>
                  <option value="security"<?=$entry_values['profile'] === 'security' ? ' selected' : ''?>><?=__('home_security')?></option>
                  <option value="games"<?=$entry_values['profile'] === 'games' ? ' selected' : ''?>><?=__('home_games')?></option>
                </select>
              </div>

              <div class="admin-field">
                <label for="entry_status"><?=__('admin_entry_status')?></label>

                <select id="entry_status" name="entry[status]">
                  <option value="draft"<?=$entry_values['status'] === 'draft' ? ' selected' : ''?>><?=__('admin_entry_draft')?></option>
                  <option value="published"<?=$entry_values['status'] === 'published' ? ' selected' : ''?>><?=__('admin_entry_published')?></option>
                </select>
              </div>
            </div>

            <div class="entry-dates">
              <div class="admin-field">
                <label for="entry_start"><?=__('admin_entry_start')?></label>
                <input type="text" id="entry_start" name="entry[start]" value="<?=htmlspecialchars($entry_values['start'], ENT_QUOTES, 'UTF-8')?>" placeholder="<?=__('admin_entry_date_placeholder')?>">
              </div>

              <div class="admin-field">
                <label for="entry_end"><?=__('admin_entry_end')?></label>
                <input type="text" id="entry_end" name="entry[end]" value="<?=htmlspecialchars($entry_values['end'], ENT_QUOTES, 'UTF-8')?>" placeholder="<?=__('admin_entry_date_placeholder')?>">
              </div>

              <label class="admin-checkbox" for="entry_ongoing">
                <input type="checkbox" id="entry_ongoing" name="entry[ongoing]" value="1" <?=$entry_values['ongoing'] ? 'checked' : ''?>>
                <span><?=__('admin_entry_ongoing')?></span>
              </label>
            </div>

            <div class="entry-translations">
              <?php foreach(array('en', 'fr') as $entry_language): ?>
              <fieldset>
                <legend><?=__('admin_language_'.$entry_language)?></legend>

                <?php foreach($entry_fields as $field => $rows): ?>
                <div class="admin-field">
                  <label for="entry_<?=$entry_language?>_<?=$field?>">
                    <?=__('admin_entry_'.$field)?>
                  </label>

                  <?php if($rows): ?>
                  <textarea id="entry_<?=$entry_language?>_<?=$field?>"
                            name="entry[<?=$entry_language?>][<?=$field?>]"
                            rows="<?=$rows?>"><?=htmlspecialchars($entry_values[$entry_language][$field], ENT_QUOTES, 'UTF-8')?></textarea>
                  <?php else: ?>
                  <input type="text"
                        id="entry_<?=$entry_language?>_<?=$field?>"
                        name="entry[<?=$entry_language?>][<?=$field?>]"
                        value="<?=htmlspecialchars($entry_values[$entry_language][$field], ENT_QUOTES, 'UTF-8')?>">
                  <?php endif; ?>

                </div>
                <?php endforeach; ?>

              </fieldset>
              <?php endforeach; ?>
            </div>

            <div>
              <button type="submit"><?=__('admin_entry_save')?></button>
            </div>
          </form>
        </section>
        <?php endif; ?>

        <section class="admin-section">
          <div class="admin-section-heading">
            <h2><?=__('admin_entries')?></h2>

            <?php if(!$entry_add): ?>
            <a href="?add=1#entry-editor"><?=__('admin_entry_add')?></a>
            <?php endif; ?>
          </div>

          <?php if(empty($entries)): ?>
          <p class="admin-empty">
            <?=__('admin_entries_empty')?>
          </p>
          <?php else: ?>
          <ul class="admin-entry-list">
            <?php foreach(array_reverse($entries) as $saved_entry): ?>
            <li>
              <span>
                <?=htmlspecialchars(
                  $saved_entry['en']['title'] !== ''
                  ? $saved_entry['en']['title']
                  : $saved_entry['fr']['title'],
                  ENT_QUOTES,
                  'UTF-8'
                )?>
              </span>

              <span><?=__('home_'.$saved_entry['profile'])?></span>
              <span><?=__('admin_entry_'.$saved_entry['status'])?></span>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

        </section>

      </main>

    </div>
  </body>
</html>