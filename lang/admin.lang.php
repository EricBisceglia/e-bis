<?php if(realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(403); exit; } /***************/
/*                                                                                                                   */
/*                                                       ADMIN                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/

// Header
___('admin_title',  'EN', "Administration");
___('admin_title',  'FR', "Administration");

// Navigation
___('admin_entries',  'EN', "Projects");
___('admin_entries',  'FR', "Projets");
___('admin_profiles', 'EN', "Profiles");
___('admin_profiles', 'FR', "Profils");
___('admin_website',  'EN', "Website");
___('admin_website',  'FR', "Site");

// Entries
___('admin_entries_empty', 'EN', "No projects yet.");
___('admin_entries_empty', 'FR', "Aucun projet pour le moment.");

// Profiles
___('admin_profiles_description', 'EN', "Introductions & skills for each CV.");
___('admin_profiles_description', 'FR', "Présentations & compétences pour chaque CV.");





/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                      ENTRIES                                                      */
/*                                                                                                                   */
/*********************************************************************************************************************/

// General
___('admin_entry_heading',   'EN', "Entry");
___('admin_entry_heading',   'FR', "Projet");
___('admin_entry_profile',   'EN', "Profile");
___('admin_entry_profile',   'FR', "Profil");
___('admin_entry_status',    'EN', "Status");
___('admin_entry_status',    'FR', "Statut");
___('admin_entry_draft',     'EN', "Draft");
___('admin_entry_draft',     'FR', "Brouillon");
___('admin_entry_published', 'EN', "Published");
___('admin_entry_published', 'FR', "Publié");

// Dates
___('admin_entry_start',     'EN', "Start");
___('admin_entry_start',     'FR', "Début");
___('admin_entry_end',       'EN', "End");
___('admin_entry_end',       'FR', "Fin");
___('admin_entry_ongoing',   'EN', "Ongoing");
___('admin_entry_ongoing',   'FR', "En cours");

// Languages
___('admin_language_en',     'EN', "English");
___('admin_language_en',     'FR', "Anglais");
___('admin_language_fr',     'EN', "French");
___('admin_language_fr',     'FR', "Français");

// Entry list
___('admin_entry_dates',     'EN', "Dates");
___('admin_entry_dates',     'FR', "Dates");
___('admin_entry_languages', 'EN', "Languages");
___('admin_entry_languages', 'FR', "Langues");

// Entry actions
___('admin_entry_add',    'EN', "Add entry");
___('admin_entry_add',    'FR', "Ajouter un projet");
___('admin_entry_cancel', 'EN', "Cancel");
___('admin_entry_cancel', 'FR', "Annuler");

// Translated fields
___('admin_entry_title',     'EN', "Company / Project");
___('admin_entry_title',     'FR', "Entreprise / projet");
___('admin_entry_role',      'EN', "Role");
___('admin_entry_role',      'FR', "Poste");
___('admin_entry_summary',   'EN', "Summary");
___('admin_entry_summary',   'FR', "Récapitulatif");
___('admin_entry_body',      'EN', "Description");
___('admin_entry_body',      'FR', "Description");
___('admin_entry_bullets',   'EN', "Bullet points");
___('admin_entry_bullets',   'FR', "Bullet points");

// Saving entries
___('admin_entry_save',  'EN', "Save");
___('admin_entry_save',  'FR', "Enregistrer");
___('admin_entry_saved', 'EN', "Entry added.");
___('admin_entry_saved', 'FR', "Projet ajouté.");

// Default values
___('admin_entry_date_placeholder', 'EN', "YYYY or YYYY-MM");
___('admin_entry_date_placeholder', 'FR', "YYYY ou YYYY-MM");

// Validation errors
___('admin_entry_error_invalid',  'EN', "Some fields contain invalid values.");
___('admin_entry_error_invalid',  'FR', "Certains champs contiennent des valeurs invalides.");
___('admin_entry_error_title',    'EN', "Enter a company or project name in at least one language.");
___('admin_entry_error_title',    'FR', "Indiquez une entreprise ou un projet dans au moins une langue.");
___('admin_entry_error_dates',    'EN', "The end date cannot be before the start date.");
___('admin_entry_error_dates',    'FR', "La date de fin ne peut pas précéder la date de début.");
___('admin_entry_error_request',  'EN', "The form expired. Please try saving again.");
___('admin_entry_error_request',  'FR', "Le formulaire a expiré. Veuillez réessayer.");
___('admin_entry_error_storage',  'EN', "The entries could not be read or saved.");
___('admin_entry_error_storage',  'FR', "Impossible de lire ou d'enregistrer les projets.");