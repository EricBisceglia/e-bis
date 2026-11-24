<?php /***************************************************************************************************************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/

// File inclusions
include_once __DIR__.'/../inc/includes.inc.php'; # Core
include_once __DIR__.'/../lang/main.lang.php';   # Shared translations
include_once __DIR__.'/../lang/admin.lang.php';  # Admin translations

// Page settings
$path            = '../';
$page_stylesheet = 'admin';
$admin_page      = 'entries';




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     BACK END                                                      */
/*                                                                                                                   */
/*********************************************************************************************************************/

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




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     FRONT END                                                     */
/*                                                                                                                   */
/*******************************************************************/ include_once __DIR__.'/../inc/header.inc.php'; ?>

  <body class="admin">
    <div class="page">

      <?php include_once __DIR__.'/../inc/admin_header.inc.php'; ?>

      <main>

        <?php if($entry_add): ?>
        <section class="admin-section" id="entry-editor">
          <div class="admin-section-heading">
            <h2><?=__('admin_entry_add')?></h2>
            <a href="./entries"><?=__('admin_entry_cancel')?></a>
          </div>

          <div class="entry-editor">

            <div class="entry-settings">
              <div class="admin-field">
                <label for="entry_profile"><?=__('admin_entry_profile')?></label>

                <select id="entry_profile" name="entry[profile]">
                  <option value="tech"><?=__('home_tech')?></option>
                  <option value="security"><?=__('home_security')?></option>
                  <option value="games"><?=__('home_games')?></option>
                </select>
              </div>

              <div class="admin-field">
                <label for="entry_status"><?=__('admin_entry_status')?></label>

                <select id="entry_status" name="entry[status]">
                  <option value="draft"><?=__('admin_entry_draft')?></option>
                  <option value="published"><?=__('admin_entry_published')?></option>
                </select>
              </div>
            </div>

            <div class="entry-dates">
              <div class="admin-field">
                <label for="entry_start"><?=__('admin_entry_start')?></label>
                <input type="month" id="entry_start" name="entry[start]">
              </div>

              <div class="admin-field">
                <label for="entry_end"><?=__('admin_entry_end')?></label>
                <input type="month" id="entry_end" name="entry[end]">
              </div>

              <label class="admin-checkbox" for="entry_ongoing">
                <input type="checkbox" id="entry_ongoing" name="entry[ongoing]" value="1">
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
                            rows="<?=$rows?>"></textarea>
                  <?php else: ?>
                  <input type="text"
                         id="entry_<?=$entry_language?>_<?=$field?>"
                         name="entry[<?=$entry_language?>][<?=$field?>]">
                  <?php endif; ?>

                </div>
                <?php endforeach; ?>

              </fieldset>
              <?php endforeach; ?>
            </div>

          </div>
        </section>
        <?php endif; ?>

        <section class="admin-section">
          <div class="admin-section-heading">
            <h2><?=__('admin_entries')?></h2>

            <?php if(!$entry_add): ?>
            <a href="?add=1#entry-editor"><?=__('admin_entry_add')?></a>
            <?php endif; ?>
          </div>

          <p class="admin-empty">
            <?=__('admin_entries_empty')?>
          </p>
        </section>

      </main>

    </div>
  </body>
</html>