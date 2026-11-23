<?php /***************************************************************************************************************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/
// File inclusions
include_once __DIR__.'/inc/includes.inc.php'; # Core
include_once __DIR__.'/lang/main.lang.php';   # Translations
?>

<!DOCTYPE html>
<html lang="<?=$lang_lower?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=__('site_name')?></title>
    <meta name="description" content="<?=__('site_name')?>">
    <link rel="icon" type="image/png" sizes="32x32" href="./favicon.ico">
    <link rel="stylesheet" href="./css/ebis.css">
    <link rel="stylesheet" href="./css/homepage.css">
  </head>

  <body>
    <div class="page">

      <header class="header-name">
        <h1><?=__('site_name')?></h1>
        <div class="header-details">
          <a class="language-switch" href="?lang=<?=$lang_other?>">
            <?=__('site_language')?>
          </a>
        </div>
      </header>

      <main>
        <section class="introduction">
          <p>
            <?=__('home_summary')?>
          </p>
        </section>

        <nav class="home-links">
          <a class="home-banner" href="./tech">
            <h2>
              <?=__('home_tech')?>
            </h2>
          </a>

          <a class="home-banner" href="./cybersecurity">
            <h2>
              <?=__('home_security')?>
            </h2>
          </a>

          <a class="home-banner" href="./gamedesign">
            <h2>
              <?=__('home_games')?>
            </h2>
          </a>
        </nav>

        <p class="contact">
          <a href="https://www.linkedin.com/in/eric-bisceglia/">
            <?=__('home_contact')?>
          </a>
        </p>
      </main>

    </div>
  </body>
</html>