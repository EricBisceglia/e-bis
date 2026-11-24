<?php /***************************************************************************************************************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/
// File inclusions
include_once __DIR__.'/inc/includes.inc.php'; # Core
include_once __DIR__.'/lang/main.lang.php';   # Translations

// Page settings
$page_stylesheet = 'cv';




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
          <p class="header-subtitle"><?=__('home_tech')?></p>
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

        <p class="cv-introduction">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat
        </p>

        <nav class="cv-navigation">
          <a href="./"><?=__('cv_home')?></a>
          <span><?=__('cv_tech')?></span>
          <a href="./cybersecurity"><?=__('cv_security')?></a>
          <a href="./gamedesign"><?=__('cv_games')?></a>
          <a href="https://www.linkedin.com/in/eric-bisceglia" target="_blank">
            <?=__('cv_contact')?>
          </a>
        </nav>

        <section class="cv-section" id="career-summary">
          <h2><?=__('cv_summary')?></h2>
          <div class="career-summary">
            <a class="summary-entry" href="#some-job">
              <span>2006</span>
              <span>Company name · Role name</span>
              <span>One skill · Two skills · Three skills · Four skills · Five skills</span>
            </a>
            <a class="summary-entry" href="#other-job">
              <span>2026</span>
              <span>Long company name · Long role name</span>
              <span>Skill name  · Skill name  · Skill name</span>
            </a>
          </div>
        </section>

        <section class="cv-section" id="skills">
          <h2><?=__('cv_skills')?></h2>
          <dl class="skill-list">
            <dt>Skill name</dt>
            <dd>Skil description · Other things · More text · Lorem skillum</dd>
            <dt>Other skill</dt>
            <dd>Things · Things · Things · Things · Things · Things</dd>
          </dl>
        </section>

        <section class="cv-section" id="career">
          <h2><?=__('cv_career')?></h2>
          <article class="cv-entry" id="some-job">
            <h3>Some company name</h3>
            <div class="entry-details">
              <p>Job description</p>
              <p>2006 - Present</p>
            </div>
            <p>
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat
            </p>
            <ul>
              <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
              <li>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</li>
              <li>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo.</li>
            </ul>
          </article>

          <article class="cv-entry" id="other-job">
            <h3>Another company name</h3>
            <div class="entry-details">
              <p>Another job title</p>
              <p>2006 - 2026</p>
            </div>
            <p>
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat
            </p>
            <ul>
              <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
              <li>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</li>
              <li>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo.</li>
            </ul>
          </article>
        </section>

      </main>

    </div>
  </body>
</html>