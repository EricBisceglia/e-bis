<?php if(realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(403); exit; } /***************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/

// Preserve page parameters when switching languages
$language_parameters         = $_GET;
$language_parameters['lang'] = $lang_other;
$language_url                = '?'.http_build_query($language_parameters);




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                   ADMIN HEADER                                                    */
/*                                                                                                                   */
/******************************************************************************************************************/ ?>

<header class="header-name">
  <div>
    <h1><?=__('admin_title')?></h1>
    <p class="admin-identity"><?=__('site_name')?></p>
  </div>

  <div class="header-details">
    <a class="language-switch" href="<?=htmlspecialchars($language_url, ENT_QUOTES, 'UTF-8')?>">
      <?=__('site_language')?>
    </a>
  </div>
</header>

<nav class="admin-navigation">
  <?php if($admin_page === 'profiles'): ?>
  <span><?=__('admin_profiles')?></span>
  <?php else: ?>
  <a href="<?=$path?>admin/profiles"><?=__('admin_profiles')?></a>
  <?php endif; ?>

  <?php if($admin_page === 'entries'): ?>
  <span><?=__('admin_entries')?></span>
  <?php else: ?>
  <a href="<?=$path?>admin/entries"><?=__('admin_entries')?></a>
  <?php endif; ?>

  <a href="<?=$path?>"><?=__('admin_website')?></a>
</nav>