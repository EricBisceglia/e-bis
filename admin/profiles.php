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




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     FRONT END                                                     */
/*                                                                                                                   */
/*******************************************************************/ include_once __DIR__.'/../inc/header.inc.php'; ?>

  <body class="admin">
    <div class="page">

      <header class="header-name">
        <div>
          <h1><?=__('admin_title')?></h1>
          <p class="admin-identity"><?=__('site_name')?></p>
        </div>

        <div class="header-details">
          <a class="language-switch" href="?lang=<?=$lang_other?>">
            <?=__('site_language')?>
          </a>
        </div>
      </header>

      <nav class="admin-navigation">
        <span><?=__('admin_profiles')?></span>
        <a href="./entries"><?=__('admin_entries')?></a>
        <a href="<?=$path?>"><?=__('admin_website')?></a>
      </nav>

      <main>
        <section class="admin-section">
          <h2><?=__('admin_profiles')?></h2>

          <p>
            <?=__('admin_profiles_description')?>
          </p>

          <ul class="admin-profiles">
            <li><?=__('home_tech')?></li>
            <li><?=__('home_security')?></li>
            <li><?=__('home_games')?></li>
          </ul>
        </section>
      </main>

    </div>
  </body>
</html>