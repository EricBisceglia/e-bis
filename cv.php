<?php /***************************************************************************************************************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/
// File inclusions
include_once __DIR__.'/inc/includes.inc.php'; # Core
include_once __DIR__.'/inc/profiles.inc.php'; # Profile storage
include_once __DIR__.'/inc/entries.inc.php';  # Entry storage
include_once __DIR__.'/lang/main.lang.php';   # Translations

// Page settings
$page_stylesheet = 'cv';




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     BACK END                                                      */
/*                                                                                                                   */
/*********************************************************************************************************************/

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Profile selection

// Map public page names to internal names
$cv_profiles = array(
  'tech'          => 'tech',
  'cybersecurity' => 'security',
  'gamedesign'    => 'games',
);

// Determine the requested CV route from the URL
$cv_route = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');

// Validate the requested CV route against the known profiles
if(!isset($cv_profiles[$cv_route]))
{
  http_response_code(404);
  exit;
}

// Determine the internal profile name based on the requested route
$cv_profile = $cv_profiles[$cv_route];


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Profile navigation

// Build the profile navigation array
$cv_navigation = array();
foreach($cv_profiles as $route => $profile_name)
{
  $cv_navigation[] = array(
    'url'     => './'.$route,
    'label'   => __('cv_'.$profile_name),
    'current' => $profile_name === $cv_profile,
  );
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Profile content

// Initialize variables
$cv_introduction = '';
$cv_skills       = array();
$cv_entries      = array();
$cv_error        = false;

// Fetch the selected profile translation
try
{
  // Fetch all profiles and match the right one
  $profiles = profiles_get();
  $profile  = $profiles[$cv_profile][$lang_lower];

  // Convert the introduction to HTML while preserving line breaks
  $cv_introduction = nl2br(htmlspecialchars($profile['introduction'], ENT_QUOTES, 'UTF-8'));

  // Split the skills into individual lines and parse their names and descriptions
  foreach(explode("\n", $profile['skills']) as $line)
  {
    // Skip empty lines
    if(trim($line) === '')
      continue;

    // Split the line into name and description parts
    $parts = explode('|', $line, 2);

    // Ensure the line has at least a name
    $cv_skills[] = array(
      'name'        => htmlspecialchars(trim($parts[0]), ENT_QUOTES, 'UTF-8'),
      'description' => htmlspecialchars(trim($parts[1] ?? ''), ENT_QUOTES, 'UTF-8'),
    );
  }
}

// Handle errors during profile fetching and parsing
catch(Exception $error)
{
  error_log($error->getMessage());
  $cv_error = true;
  http_response_code(503);
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Experience entries

// Fetch and prepare experience entries for the requested profile
try
{
  // Fetch all entries and initialize anchors
  $entries    = entries_get();
  $cv_anchors = array();

  // Iterate over entries in reverse order
  foreach(array_reverse($entries) as $entry)
  {
    // Skip entries that shouldn't appear here
    if($entry['profile'] !== $cv_profile || $entry['status'] !== 'published')
      continue;

    // Fetch the translation for the current language
    $translation = $entry[$lang_lower];

    // Skip entries without a translated title for the current language
    if(trim($translation['title']) === '')
      continue;

    // Generate a unique anchor for the entry based on its title
    $anchor = preg_replace('/[^\p{L}\p{N}]+/u', '-', strtolower($translation['title']));
    $anchor = 'experience-'.(trim($anchor, '-') ?: 'project');

    // Ensure the anchor is unique by appending a number if necessary
    $base_anchor    = $anchor;
    $anchor_number  = 2;

    // Check if the anchor already exists and modify it to be unique if necessary
    while(isset($cv_anchors[$anchor]))
      $anchor = $base_anchor.'-'.$anchor_number++;

    // Mark the anchor as used to prevent duplicates
    $cv_anchors[$anchor] = true;

    // Prepare the date range
    $dates = array();

    // Prepare the start and end dates for the entry
    if($entry['start'] !== '')
      $dates[] = htmlspecialchars($entry['start'], ENT_QUOTES, 'UTF-8');

    // Add a separator if both start and end dates are present
    if($entry['ongoing'])
      $dates[] = __('cv_ongoing');

    // Add the ongoing label if necessary
    else if($entry['end'] !== '')
      $dates[] = htmlspecialchars($entry['end'], ENT_QUOTES, 'UTF-8');

    // Split the body into paragraphs separated by blank lines
    $paragraphs = array();
    foreach(preg_split('/\n\s*\n/', trim($translation['body'])) as $paragraph)
    {
      if(trim($paragraph) !== '')
        $paragraphs[] = nl2br(htmlspecialchars(trim($paragraph), ENT_QUOTES, 'UTF-8'));
    }

    // Prepare bullet points for the entry
    $bullets = array();

    // Split the bullets into individual lines and escape them for HTML output
    foreach(explode("\n", $translation['bullets']) as $bullet)
    {
      if(trim($bullet) !== '')
        $bullets[] = htmlspecialchars(trim($bullet), ENT_QUOTES, 'UTF-8');
    }

    // Fetch associated images
    $images = array();
    foreach($entry['images'] ?? array() as $image)
    {
      if(!entry_image_exists($image))
        continue;

      $image_url = './images/projects/'.implode('/', array_map('rawurlencode', explode('/', $image)));
      $images[] = htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8');
    }

    // Prepare the title and role for the entry
    $title = htmlspecialchars($translation['title'], ENT_QUOTES, 'UTF-8');
    $role  = htmlspecialchars($translation['role'], ENT_QUOTES, 'UTF-8');

    // Add the prepared entry to the list of CV entries
    $cv_entries[] = array(
      'anchor'     => htmlspecialchars($anchor, ENT_QUOTES, 'UTF-8')                      ,
      'link'       => '#'.rawurlencode($anchor)                                           ,
      'year'       => htmlspecialchars(substr($entry['start'], 0, 4), ENT_QUOTES, 'UTF-8'),
      'title'      => $title                                                              ,
      'role'       => $role                                                               ,
      'heading'    => $title.($role !== '' ? ' · '.$role : '')                            ,
      'summary'    => htmlspecialchars($translation['summary'], ENT_QUOTES, 'UTF-8')      ,
      'dates'      => implode(' — ', $dates)                                              ,
      'paragraphs' => $paragraphs                                                         ,
      'bullets'    => $bullets                                                            ,
      'images'     => $images                                                             ,
    );
  }
}

// Handle any exceptions that occured during entry processing
catch(Exception $error)
{
  error_log($error->getMessage());
  $cv_error = true;
  http_response_code(503);
}




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     FRONT END                                                     */
/*                                                                                                                   */
/**********************************************************************/ include_once __DIR__.'/inc/header.inc.php'; ?>

  <body class="cv">
    <div class="page">

      <header class="header-name">
        <div>
          <h1><a href="./"><?=__('site_name')?></a></h1>
          <p class="header-subtitle"><?=__('home_'.$cv_profile)?></p>
        </div>

        <div class="header-details">
          <span><?=__('cv_location')?></span>
          <span><?=__('cv_age')?></span>
          <span><?=__('cv_in_tech')?></span>
          <div class="header-switches">
            <a class="language-switch" href="?lang=<?=$lang_other?>">
              <?=__('site_language')?>
            </a>
            <?php include_once __DIR__.'/inc/color_theme.inc.php'; ?>
          </div>
        </div>
      </header>

      <main>

        <?php if($cv_error): ?>
        <p class="cv-introduction"><?=__('cv_error_content')?></p>
        <?php endif; ?>

        <nav class="cv-navigation">
          <a href="./"><?=__('cv_home')?></a>
          <?php foreach($cv_navigation as $cv_link): ?>
          <?php if($cv_link['current']): ?>
          <span><?=$cv_link['label']?></span>
          <?php else: ?>
          <a href="<?=$cv_link['url']?>"><?=$cv_link['label']?></a>
          <?php endif; ?>
          <?php endforeach; ?>
          <a href="https://github.com/EricBisceglia/e-bis" target="_blank"><?=__('cv_source')?></a>
          <a href="https://www.linkedin.com/in/eric-bisceglia" target="_blank"><?=__('cv_contact')?></a>
        </nav>

        <?php if($cv_introduction !== ''): ?>
        <p class="cv-introduction"><?=$cv_introduction?></p>
        <?php endif; ?>

        <?php if($cv_entries): ?>
        <section class="cv-section" id="career-summary">
          <h2><?=__('cv_summary')?></h2>

          <div class="career-summary">
            <?php foreach($cv_entries as $entry): ?>
            <a class="summary-entry" href="<?=$entry['link']?>">
              <span><?=$entry['year']?></span>
              <span><?=$entry['heading']?></span>
              <span><?=$entry['summary']?></span>
            </a>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if($cv_skills): ?>
        <section class="cv-section" id="skills">
          <h2><?=__('cv_skills')?></h2>

          <dl class="skill-list">
            <?php foreach($cv_skills as $skill): ?>
            <dt><?=$skill['name']?></dt>
            <dd><?=$skill['description']?></dd>
            <?php endforeach; ?>
          </dl>
        </section>
        <?php endif; ?>

        <?php if($cv_entries): ?>
        <section class="cv-section" id="career">
          <h2><?=__('cv_career')?></h2>

          <?php foreach($cv_entries as $entry): ?>
          <article class="cv-entry" id="<?=$entry['anchor']?>">
            <h3><?=$entry['title']?></h3>

            <?php if($entry['role'] !== '' || $entry['dates'] !== ''): ?>
            <div class="entry-details">
              <?php if($entry['role'] !== ''): ?>
              <p><?=$entry['role']?></p>
              <?php endif; ?>

              <?php if($entry['dates'] !== ''): ?>
              <p><?=$entry['dates']?></p>
              <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php foreach($entry['paragraphs'] as $paragraph): ?>
            <p><?=$paragraph?></p>
            <?php endforeach; ?>

            <?php if($entry['bullets']): ?>
            <ul>
              <?php foreach($entry['bullets'] as $bullet): ?>
              <li><?=$bullet?></li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if($entry['images']): ?>
            <div class="entry-images">
              <?php foreach($entry['images'] as $image): ?>
              <img src="<?=$image?>" alt="" loading="lazy">
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

          </article>
          <?php endforeach; ?>
        </section>
        <?php endif; ?>

      </main>

    </div>
  </body>
</html>