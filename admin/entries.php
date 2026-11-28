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

// Look for profile filters
$entry_filter = $_GET['profile'] ?? '';

// Validate profile filtering against allowed values
if(!in_array($entry_filter, array('', 'tech', 'security', 'games'), true))
  $entry_filter = '';

// Prepare the query string for the current profile filter
$entry_filter_query = ($entry_filter !== '')
                    ? 'profile='.rawurlencode($entry_filter).'&'
                    : '';

// Prepare the URL for the entry list
$entry_list_url = './entries';
if($entry_filter !== '')
  $entry_list_url .= '?profile='.rawurlencode($entry_filter);
$entry_list_link = htmlspecialchars($entry_list_url, ENT_QUOTES, 'UTF-8');
$entry_add_url = htmlspecialchars('./entries?'.$entry_filter_query.'add=1#entry-editor', ENT_QUOTES, 'UTF-8');

// Prepare the empty-list message
$entry_empty_text = ($entry_filter !== '')
                  ? __('admin_entries_empty_profile')
                  : __('admin_entries_empty');

// Show the editor when adding or editing an entry
$entry_add    = ($_GET['add'] ?? '') === '1';
$entry_edit   = array_key_exists('edit', $_GET);
$entry_delete = ($_POST['entry_action'] ?? '') === 'delete';
$entry_move   = ($_POST['entry_action'] ?? '') === 'move';
$entry_id     = is_string($_GET['edit'] ?? null) ? $_GET['edit'] : '';
$entry_open   = $entry_add || $entry_edit;

// Read the version submitted by the editor
$entry_version = is_string($_POST['entry_version'] ?? null)
               ? $_POST['entry_version']
               : '';

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
  'profile' => $entry_filter ?: 'tech',
  'status'  => 'draft',
  'start'   => '',
  'end'     => '',
  'ongoing' => false,
  'images'  => array(),
);

// Prepare translated form values
foreach(array('en', 'fr') as $entry_language)
{
  foreach($entry_fields as $field => $rows)
    $entry_values[$entry_language][$field] = '';
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Fetch existing entries

// Set up error messages
$entry_error = '';

// Fetch entries
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


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Load the entry being edited

if($entry_edit)
{
  // Look for the entry being edited in the existing entries
  $entry_found = false;
  if(!$entry_error && preg_match('/^[a-f0-9]{32}$/D', $entry_id))
  {
    foreach($entries as $saved_entry)
    {
      // Skip entries that don't match the requested ID
      if($saved_entry['id'] !== $entry_id)
        continue;

      // Found the entry, no need to continue searching
      $entry_found = true;

      // Populate the entry editor (or display a deletion error)
      if(($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || $entry_delete)
        $entry_values = $saved_entry;

      // Store the version hash for the entry being edited
      if(($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST')
        $entry_version = hash('sha256', json_encode($saved_entry, JSON_THROW_ON_ERROR));

      // Stop searching after finding the entry
      break;
    }
  }

  // Never turn a missing edit target into a new entry
  if(!$entry_error && !$entry_found)
  {
    $entry_error = 'admin_entry_error_missing';
    http_response_code(404);
  }

  if($entry_error)
    $entry_open = false;
}

// Prepare the image text for the editor
$entry_images_text = implode("\n", $entry_values['images'] ?? array());


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add/edit/move/delete entries

// Move an entry
if(($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $entry_move && !$entry_error)
{
  // Fetch the submitted form values for the move
  $submitted_token = $_POST['entry_token'] ?? '';
  $move_id         = $_POST['entry_move_id'] ?? '';
  $move_direction  = $_POST['entry_direction'] ?? '';
  $move_version    = $_POST['entry_order_version'] ?? '';

  // Validate the form token first
  if(!is_string($submitted_token) || !hash_equals($entry_token, $submitted_token))
    $entry_error = 'admin_entry_error_request';

  // Validate the movement request
  else if(!is_string($move_id) || !preg_match('/^[a-f0-9]{32}$/D', $move_id)
       || !in_array($move_direction, array('up', 'down'), true)
       || !is_string($move_version))
    $entry_error = 'admin_entry_error_invalid';

  // Only proceed if there are no errors
  else
  {
    try
    {
      // Perform the move operation for the specified entry within its profile
      entries_save(array(), $move_id, $move_version, move: $move_direction);

      // Renew the form token after a successful move
      $_SESSION['entry_token'] = bin2hex(random_bytes(32));

      // Redirect back to the entry list
      exit(header('Location: '.$entry_list_url.'#entry-'.rawurlencode($move_id), true, 303));
    }

    // Handle errors during the move
    catch(DomainException $error)
    {
      $entry_error = 'admin_entry_error_order';
    }

    // Handle the case where the entry to move is missing
    catch(OutOfBoundsException $error)
    {
      $entry_error = 'admin_entry_error_missing';
    }

    // Handle any other unexpected errors
    catch(Exception $error)
    {
      error_log($error->getMessage());
      $entry_error = 'admin_entry_error_storage';
    }
  }
}

// Delete an entry
if(($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $entry_delete && !$entry_error)
{
  // Fetch the submitted form token for validation
  $submitted_token = $_POST['entry_token'] ?? '';

  // Must delete an existing entry
  if(!$entry_edit)
    $entry_error = 'admin_entry_error_invalid';

  // Validate the form token
  else if(!is_string($submitted_token) || !hash_equals($entry_token, $submitted_token))
    $entry_error = 'admin_entry_error_request';

  // Only proceed if there are no errors
  else
  {
    try
    {
      // Delete the entry
      entries_save(array(), $entry_id, $entry_version, delete: true);

      // Renew the form token
      $_SESSION['entry_token'] = bin2hex(random_bytes(32));

      // Return to the list
      exit(header('Location: ./entries?'.$entry_filter_query.'deleted=1', true, 303));
    }

    // Handle errors during deletion
    catch(DomainException $error)
    {
      $entry_error = 'admin_entry_error_changed';
    }

    // Handle the case where the entry to delete is missing
    catch(OutOfBoundsException $error)
    {
      $entry_error = 'admin_entry_error_missing';
    }

    // Handle any other unexpected errors
    catch(Exception $error)
    {
      error_log($error->getMessage());
      $entry_error = 'admin_entry_error_storage';
    }
  }
}

// Add or edit an entry
if(($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !$entry_delete && !$entry_move && !$entry_error)
{
  // Keep the entry editor open
  $entry_open = true;

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

    // Read and process the image list
    $entry_images_text = entry_input($submitted['images'] ?? '');
    $entry_values['images'] = array();

    // Split the image list into individual filenames and format them
    foreach(explode("\n", $entry_images_text) as $image)
    {
      $image = trim($image);
      if($image !== '')
        $entry_values['images'][] = $image;
    }

    // Validate that each image exists
    $valid_images = true;
    foreach($entry_values['images'] as $image)
    {
      if(!entry_image_exists($image))
        $valid_images = false;
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

    // Stop here if an image path is invalid (or its file is missing)
    else if(!$valid_images)
      $entry_error = 'admin_entry_error_images';

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
      entries_save($entry_values, $entry_id, $entry_version);

      // Replace the form token after a successful save
      $_SESSION['entry_token'] = bin2hex(random_bytes(32));

      // Redirect to the entries page
      exit(header('Location: ./entries?'.$entry_filter_query.'saved=1', true, 303));
    }
  }

  // Handle any issues during entry processing
  catch(InvalidArgumentException $error)
  {
    $entry_error = 'admin_entry_error_invalid';
  }

  // Handle an entry changed since the editor was opened
  catch(DomainException $error)
  {
    $entry_error = 'admin_entry_error_changed';
  }

  // Handle an entry removed before saving
  catch(OutOfBoundsException $error)
  {
    $entry_error = 'admin_entry_error_missing';
  }

  // Handle any other unexpected issues
  catch(Exception $error)
  {
    error_log($error->getMessage());
    $entry_error = 'admin_entry_error_storage';
  }
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Prepare the entry list

// Initialize entries
$entry_rows = array();

// Prepare the order of entries for each profile
$entry_order_ids      = array();
$entry_order_versions = array();

// Collect the entry IDs for each profile in reverse order
foreach(array_reverse($entries) as $saved_entry)
  $entry_order_ids[$saved_entry['profile']][] = $saved_entry['id'];

// Determine the order versions for each profile
foreach($entry_order_ids as $profile => $identifiers)
  $entry_order_versions[$profile] = entries_order_version($entries, $profile);

// Process each saved entry for display
foreach(array_reverse($entries) as $saved_entry)
{
  // Skip entries outside the selected profile
  if($entry_filter !== '' && $saved_entry['profile'] !== $entry_filter)
    continue;

  // Prefer the current interface language when available
  $display_language = $lang_lower;

  // Fallback to the other language if needed
  if($saved_entry[$display_language]['title'] === '')
    $display_language = ($display_language === 'en') ? 'fr' : 'en';

  // Prepare the entry dates for display
  $entry_dates = array();

  // Add the start date if available
  if($saved_entry['start'] !== '')
    $entry_dates[] = htmlspecialchars($saved_entry['start'], ENT_QUOTES, 'UTF-8');

  // Add the end date, or indicate if still ongoing
  if($saved_entry['ongoing'])
    $entry_dates[] = __('admin_entry_ongoing');
  else if($saved_entry['end'] !== '')
    $entry_dates[] = htmlspecialchars($saved_entry['end'], ENT_QUOTES, 'UTF-8');

  // Prepare the list of languages that have content for this entry
  $entry_languages = array();
  foreach(array('en', 'fr') as $available_language)
  {
    if($saved_entry[$available_language]['title'] !== '')
      $entry_languages[] = __('admin_language_'.$available_language);
  }

  // Determine which movements are available
  $profile_order = $entry_order_ids[$saved_entry['profile']];
  $entry_position = array_search($saved_entry['id'], $profile_order, true);

  // Prepare the row for HTML output
  $entry_rows[] = array(
    'title'         => htmlspecialchars($saved_entry[$display_language]['title'], ENT_QUOTES, 'UTF-8'),
    'role'          => htmlspecialchars($saved_entry[$display_language]['role'], ENT_QUOTES, 'UTF-8'),
    'dates'         => implode(' — ', $entry_dates) ?: '—',
    'profile'       => __('home_'.$saved_entry['profile']),
    'status'        => __('admin_entry_'.$saved_entry['status']),
    'languages'     => implode(' / ', $entry_languages),
    'edit_url' => htmlspecialchars('./entries?'.$entry_filter_query.'edit='.rawurlencode($saved_entry['id']).'#entry-editor', ENT_QUOTES, 'UTF-8'),
    'id'            => htmlspecialchars($saved_entry['id'], ENT_QUOTES, 'UTF-8'),
    'order_version' => $entry_order_versions[$saved_entry['profile']],
    'can_up'        => $entry_position > 0,
    'can_down'      => $entry_position < count($profile_order) - 1,
  );
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Prepare image previews

// Generate the preview URLs for each valid image
$entry_image_previews = array();
foreach($entry_values['images'] ?? array() as $image)
{
  // Skip images that don't exist
  if(!entry_image_exists($image))
    continue;

  // Generate the URL for the image preview
  $image_url = '../images/projects/'.implode('/', array_map('rawurlencode', explode('/', $image)));

  // Add the image preview to the list
  $entry_image_previews[] = array(
    'url'  => htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'),
    'name' => htmlspecialchars($image, ENT_QUOTES, 'UTF-8'),
  );
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Entry editor details

// Determine the entry editor's header
$entry_heading = $entry_edit ? __('admin_entry_edit') : __('admin_entry_add');

// Determine the entry editor's submission URL
$entry_action = './entries?'.$entry_filter_query.($entry_edit ? 'edit='.rawurlencode($entry_id) : 'add=1').'#entry-editor';

// Escape the submission URL for HTML output
$entry_action = htmlspecialchars($entry_action, ENT_QUOTES, 'UTF-8');




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
        <?php elseif(($_GET['deleted'] ?? '') === '1'): ?>
        <p class="admin-feedback"><?=__('admin_entry_deleted')?></p>
        <?php endif; ?>

        <?php if($entry_open): ?>
        <section class="admin-section" id="entry-editor">
          <div class="admin-section-heading">
            <h2><?=$entry_heading?></h2>
            <a href="<?=$entry_list_link?>"><?=__('admin_entry_cancel')?></a>
          </div>

          <form class="entry-editor" method="post" action="<?=$entry_action?>">
            <input type="hidden" name="entry_token" value="<?=$entry_token?>">
            <input type="hidden" name="entry_version" value="<?=htmlspecialchars($entry_version, ENT_QUOTES, 'UTF-8')?>">

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

            <div class="admin-field">
              <label for="entry_images"><?=__('admin_entry_images')?></label>
              <textarea id="entry_images" name="entry[images]" rows="5"><?=htmlspecialchars($entry_images_text, ENT_QUOTES, 'UTF-8')?></textarea>
              <p class="admin-help"><?=__('admin_entry_images_help')?></p>
            </div>

            <?php if($entry_image_previews): ?>
            <div class="entry-images">
              <?php foreach($entry_image_previews as $image): ?>
              <figure>
                <img src="<?=$image['url']?>" alt="" loading="lazy">
                <figcaption><?=$image['name']?></figcaption>
              </figure>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div>
              <button type="submit"><?=__('admin_entry_save')?></button>
            </div>
          </form>

          <?php if($entry_edit): ?>
          <details class="entry-delete">
            <summary><?=__('admin_entry_delete')?></summary>

            <p><?=__('admin_entry_delete_confirmation')?></p>

            <form method="post" action="<?=$entry_action?>">
              <input type="hidden" name="entry_action" value="delete">
              <input type="hidden" name="entry_token" value="<?=$entry_token?>">
              <input type="hidden" name="entry_version"
                    value="<?=htmlspecialchars($entry_version, ENT_QUOTES, 'UTF-8')?>">

              <button type="submit"><?=__('admin_entry_delete_confirm')?></button>
            </form>
          </details>
          <?php endif; ?>

        </section>
        <?php endif; ?>

        <section class="admin-section">
          <div class="admin-section-heading">
            <h2><?=__('admin_entries')?></h2>

            <?php if(!$entry_open): ?>
            <a href="<?=$entry_add_url?>"><?=__('admin_entry_add')?></a>
            <?php endif; ?>
          </div>

          <form class="entry-filters" method="get" action="./entries">
            <div class="admin-field">
              <label for="entry_filter_profile"><?=__('admin_entry_profile')?></label>

              <select id="entry_filter_profile" name="profile">
                <option value=""<?=$entry_filter === '' ? ' selected' : ''?>>
                  <?=__('admin_entries_all_profiles')?>
                </option>

                <option value="tech"<?=$entry_filter === 'tech' ? ' selected' : ''?>>
                  <?=__('home_tech')?>
                </option>

                <option value="security"<?=$entry_filter === 'security' ? ' selected' : ''?>>
                  <?=__('home_security')?>
                </option>

                <option value="games"<?=$entry_filter === 'games' ? ' selected' : ''?>>
                  <?=__('home_games')?>
                </option>
              </select>
            </div>

            <button type="submit"><?=__('admin_entries_filter')?></button>
          </form>

          <?php if(empty($entry_rows)): ?>
            <p class="admin-empty">
              <?=$entry_empty_text?>
            </p>
            <?php else: ?>

            <table class="admin-entry-table">
              <thead>
                <tr>
                  <th scope="col"><?=__('admin_entry_title')?></th>
                  <th scope="col"><?=__('admin_entry_dates')?></th>
                  <th scope="col"><?=__('admin_entry_profile')?></th>
                  <th scope="col"><?=__('admin_entry_status')?></th>
                  <th scope="col"><?=__('admin_entry_languages')?></th>
                  <th scope="col"><?=__('admin_entry_order')?></th>
                </tr>
              </thead>

              <tbody>
              <?php foreach($entry_rows as $entry_row): ?>
              <tr id="entry-<?=$entry_row['id']?>">
                <td data-label="<?=__('admin_entry_title')?>">
                  <div>
                    <a href="<?=$entry_row['edit_url']?>"><?=$entry_row['title']?></a>

                    <?php if($entry_row['role'] !== ''): ?>
                    <span class="admin-entry-role"><?=$entry_row['role']?></span>
                    <?php endif; ?>
                  </div>
                </td>

                <td data-label="<?=__('admin_entry_dates')?>">
                  <?=$entry_row['dates']?>
                </td>

                <td data-label="<?=__('admin_entry_profile')?>">
                  <?=$entry_row['profile']?>
                </td>

                <td data-label="<?=__('admin_entry_status')?>">
                  <?=$entry_row['status']?>
                </td>

                <td data-label="<?=__('admin_entry_languages')?>">
                  <?=$entry_row['languages']?>
                </td>

                <td data-label="<?=__('admin_entry_order')?>">
                  <form class="entry-order" method="post" action="<?=$entry_list_link?>">
                    <input type="hidden" name="entry_action" value="move">
                    <input type="hidden" name="entry_token" value="<?=$entry_token?>">
                    <input type="hidden" name="entry_move_id" value="<?=$entry_row['id']?>">
                    <input type="hidden" name="entry_order_version" value="<?=$entry_row['order_version']?>">
                    <button type="submit" name="entry_direction" value="up" title="<?=__('admin_entry_up')?>" <?=$entry_row['can_up'] ? '' : 'disabled'?>>↑</button>
                    <button type="submit" name="entry_direction" value="down" title="<?=__('admin_entry_down')?>" <?=$entry_row['can_down'] ? '' : 'disabled'?>>↓</button>
                  </form>
                </td>

              </tr>
              <?php endforeach; ?>
            </tbody>
            </table>

            <?php endif; ?>

        </section>

      </main>

    </div>
  </body>
</html>