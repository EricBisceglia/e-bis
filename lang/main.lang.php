<?php if(realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(403); exit; } /***************/
/*                                                                                                                   */
/*                                                COMMON TRANSLATIONS                                                */
/*                                                                                                                   */
/*********************************************************************************************************************/

// Identity
___('site_name',  'EN', "Éric Bisceglia");
___('site_name',  'FR', "Éric Bisceglia");

// Language
___('site_language', 'EN', "Français");
___('site_language', 'FR', "English");

// Appearance
___('site_theme_light', 'EN', "Light");
___('site_theme_light', 'FR', "Clair");
___('site_theme_dark',  'EN', "Dark");
___('site_theme_dark',  'FR', "Sombre");




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     HOMEPAGE                                                      */
/*                                                                                                                   */
/*********************************************************************************************************************/

// CV links
___('home_tech',     'EN', "Tech management");
___('home_tech',     'FR', "Management technique");
___('home_security', 'EN', "Cybersecurity consulting");
___('home_security', 'FR', "Conseil en cybersécurité");
___('home_games',    'EN', "Game design");
___('home_games',    'FR', "Game design");

// Summary
___('home_summary', 'EN', <<<EOT
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat
EOT
);
___('home_summary', 'FR', <<<EOT
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat
EOT
);

// Contact
___('home_contact',  'EN', "Contact / LinkedIn");
___('home_contact',  'FR', "Contact / LinkedIn");

// Source code
___('home_source',  'EN', "Source code");
___('home_source',  'FR', "Code source");




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                     CV PAGES                                                      */
/*                                                                                                                   */
/*********************************************************************************************************************/

// Personal information
___('cv_location',  'EN', "Lives in Paris, France");
___('cv_location',  'FR', "Vit à Paris");
___('cv_age',       'EN', "Born in August 1988");
___('cv_age',       'FR', "Né en août 1988");
___('cv_in_tech',   'EN', "In tech since 2006");
___('cv_in_tech',   'FR', "Dans la tech depuis 2006");

// Navigation
___('cv_home',      'EN', "Home");
___('cv_home',      'FR', "Accueil");
___('cv_tech',      'EN', "Tech");
___('cv_tech',      'FR', "Tech");
___('cv_security',  'EN', "Cybersecurity");
___('cv_security',  'FR', "Cybersécurité");
___('cv_games',     'EN', "Game design");
___('cv_games',     'FR', "Game design");
___('cv_source',    'EN', "Source");
___('cv_source',    'FR', "Source");
___('cv_contact',   'EN', "Contact / LinkedIn");
___('cv_contact',   'FR', "Contact / LinkedIn");

// Section titles
___('cv_summary',   'EN', "Career summary");
___('cv_summary',   'FR', "Parcours professionnel");
___('cv_skills',    'EN', "Skills");
___('cv_skills',    'FR', "Compétences");
___('cv_career',    'EN', "Detailed career");
___('cv_career',    'FR', "Parcours détaillé");

// Dynamic CV content
___('cv_ongoing',       'EN', "Present");
___('cv_ongoing',       'FR', "Aujourd'hui");
___('cv_error_content', 'EN', "This section is temporarily unavailable.");
___('cv_error_content', 'FR', "Cette section est temporairement indisponible.");