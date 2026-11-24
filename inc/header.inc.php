<?php if(realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(403); exit; } /***************/
/*                                                                                                                   */
/*                                                       SETUP                                                       */
/*                                                                                                                   */
/*********************************************************************************************************************/

// Default the path to the website's root if none is provided
$path = $path ?? './';




/*********************************************************************************************************************/
/*                                                                                                                   */
/*                                                       HEADER                                                      */
/*                                                                                                                   */
/******************************************************************************************************************/ ?>

<!DOCTYPE html>
<html lang="<?=$lang_lower?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=__('site_name')?></title>
    <meta name="description" content="<?=__('site_name')?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?=$path?>favicon.ico">
    <link rel="stylesheet" href="<?=$path?>css/ebis.css">
    <link rel="stylesheet" href="<?=$path?>css/<?=$page_stylesheet?>.css">
  </head>