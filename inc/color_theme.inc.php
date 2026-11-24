<?php if(realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(403); exit; } /***************/
/*                                                                                                                   */
/*                                                   THEME SWITCH                                                    */
/*                                                                                                                   */
/******************************************************************************************************************/ ?>

<a class="theme-switch theme-switch-light"
   href="<?=htmlspecialchars($theme_light_url, ENT_QUOTES, 'UTF-8')?>">
  <?=__('site_theme_light')?>
</a>

<a class="theme-switch theme-switch-dark"
   href="<?=htmlspecialchars($theme_dark_url, ENT_QUOTES, 'UTF-8')?>">
  <?=__('site_theme_dark')?>
</a>