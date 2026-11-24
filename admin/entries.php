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
        <a href="./profiles"><?=__('admin_profiles')?></a>
        <span><?=__('admin_entries')?></span>
        <a href="<?=$path?>"><?=__('admin_website')?></a>
      </nav>

      <main>
        <section class="admin-section">
          <h2><?=__('admin_entries')?></h2>

          <p class="admin-empty">
            <?=__('admin_entries_empty')?>
          </p>
        </section>
      </main>

    </div>
  </body>
</html>