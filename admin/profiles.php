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
$admin_page      = 'profiles';




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     FRONT END                                                     */
/*                                                                                                                   */
/*******************************************************************/ include_once __DIR__.'/../inc/header.inc.php'; ?>

  <body class="admin">
    <div class="page">

      <?php include_once __DIR__.'/../inc/admin_header.inc.php'; ?>

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