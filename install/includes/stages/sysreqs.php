<?php

/*
 * Enano - an open-source CMS capable of wiki functions, Drupal-like sidebar blocks, and everything in between
 * Version 1.1.6 (Caoineag beta 1)
 * Copyright (C) 2006-2008 Dan Fuhry
 * Installation package
 * sysreqs.php - Installer system-requirements page
 *
 * This program is Free Software; you can redistribute and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for details.
 */

if ( !defined('IN_ENANO_INSTALL') )
  die();

global $failed, $warned;

$failed = false;
$warned = false;

function run_test($code, $desc, $extended_desc, $warn = false)
{
  global $failed, $warned;
  static $cv = true;
  $cv = !$cv;
  $val = eval($code);
  if($val)
  {
    if($cv) $color='CCFFCC'; else $color='AAFFAA';
    echo "<tr><td style='background-color: #$color; width: 500px; padding: 5px;'>$desc</td><td style='padding-left: 10px;'><img alt='Test passed' src='../images/check.png' /></td></tr>";
  } elseif(!$val && $warn) {
    if($cv) $color='FFFFCC'; else $color='FFFFAA';
    echo "<tr><td style='background-color: #$color; width: 500px; padding: 5px;'>$desc<br /><b>$extended_desc</b></td><td style='padding-left: 10px;'><img alt='Test passed with warning' src='../images/checkunk.png' /></td></tr>";
    $warned = true;
  } else {
    if($cv) $color='FFCCCC'; else $color='FFAAAA';
    echo "<tr><td style='background-color: #$color; width: 500px; padding: 5px;'>$desc<br /><b>$extended_desc</b></td><td style='padding-left: 10px;'><img alt='Test failed' src='../images/checkbad.png' /></td></tr>";
    $failed = true;
  }
}
function is_apache()
{
  $r = strstr($_SERVER['SERVER_SOFTWARE'], 'Apache') ? true : false;
  return $r;
}

function write_test($filename)
{
  // We need to actually _open_ the file to make sure it can be written, because sometimes this fails even when is_writable() returns
  // true on Windows/IIS servers. Don't ask me why.
  
  $file = ENANO_ROOT . '/' . $filename;
  if ( is_dir($file) )
  {
    $file = rtrim($file, '/') . '/' . 'enanoinstalltest.txt';
    if ( file_exists($file) )
    {
      $fp = @fopen($file, 'a+');
      if ( !$fp )
        return false;
      fclose($fp);
      unlink($file);
      return true;
    }
    else
    {
      $fp = @fopen($file, 'w');
      if ( !$fp )
        return false;
      fclose($fp);
      unlink($file);
      return true;
    }
  }
  else
  {
    if ( file_exists($file) )
    {
      $fp = @fopen($file, 'a+');
      if ( !$fp )
        return false;
      fclose($fp);
      return true;
    }
    else
    {
      $fp = @fopen($file, 'w');
      if ( !$fp )
        return false;
      fclose($fp);
      return true;
    }
  }
}

$warnings = array();
$failed = false;
$have_dbms = false;

// Test: Apache
$req_apache = is_apache() ? 'good' : 'bad';

// Test: PHP
if ( version_compare(PHP_VERSION, '5.2.0', '>=') )
{
  $req_php = 'good';
}
else if ( version_compare(PHP_VERSION, '5.0.0', '>=') )
{
  $warnings[] = $lang->get('sysreqs_req_help_php', array('php_version' => PHP_VERSION));
  $req_php = 'warn';
}
else
{
  $failed = true;
  $req_php = 'bad';
}

$req_safemode = !intval(@ini_get('safe_mode'));
if ( !$req_safemode )
{
  $warnings[] = $lang->get('sysreqs_req_help_safemode');
  $failed = true;
}

// Test: MySQL
$req_mysql = function_exists('mysql_connect');
if ( $req_mysql )
  $have_dbms = true;

// Test: PostgreSQL
$req_pgsql = function_exists('pg_connect');
if ( $req_pgsql )
  $have_dbms = true;

if ( !$have_dbms )
  $failed = true;

// Test: File uploads
$req_uploads = intval(@ini_get('file_uploads'));

// Writability test: config
$req_config_w = write_test('config.new.php');

// Writability test: .htaccess
$req_htaccess_w = write_test('.htaccess.new');

// Writability test: files
$req_files_w = write_test('files');

// Writability test: cache
$req_cache_w = write_test('cache');

if ( !$req_config_w || !$req_htaccess_w || !$req_files_w || !$req_cache_w )
  $warnings[] = $lang->get('sysreqs_req_help_writable');

if ( !$req_config_w )
  $failed = true;

// Extension test: GD
$req_gd = function_exists('imagecreatefrompng') && function_exists('getimagesize') && function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled');
if ( !$req_gd )
  $warnings[] = $lang->get('sysreqs_req_help_gd2');

// FS test: ImageMagick
$req_imagick = which('convert');
if ( !$req_imagick )
  $warnings[] = $lang->get('sysreqs_req_help_imagemagick');

// Extension test: GMP
$req_gmp = function_exists('gmp_init');
if ( !$req_gmp )
  $warnings[] = $lang->get('sysreqs_req_help_gmp');

// Extension test: Big_Int
$req_bigint = function_exists('bi_from_str');
if ( !$req_bigint && !$req_gmp )
  $warnings[] = $lang->get('sysreqs_req_help_bigint');

// Extension test: BCMath
$req_bcmath = function_exists('bcadd');
if ( !$req_bcmath && !$req_bigint && !$req_gmp )
  $warnings[] = $lang->get('sysreqs_req_help_bcmath');

?>
<h3><?php echo $lang->get('sysreqs_heading'); ?></h3>
 <p><?php echo $lang->get('sysreqs_blurb'); ?></p>
 
<?php
if ( !empty($warnings) ):
?>
  <div class="sysreqs_warning">
    <h3><?php echo $lang->get('sysreqs_summary_warn_title'); ?></h3>
    <p><?php echo $lang->get('sysreqs_summary_warn_body'); ?></p>
    <ul>
      <li><?php echo implode("</li>\n      <li>", $warnings); ?></li>
    </ul>
  </div>
<?php
endif;

if ( !$have_dbms ):
?>
  <div class="sysreqs_error">
    <h3><?php echo $lang->get('sysreqs_err_no_dbms_title'); ?></h3>
    <p><?php echo $lang->get('sysreqs_err_no_dbms_body'); ?></p>
  </div>
<?php
endif;

if ( $failed ):
?>
  <div class="sysreqs_error">
    <h3><?php echo $lang->get('sysreqs_summary_fail_title'); ?></h3>
    <p><?php echo $lang->get('sysreqs_summary_fail_body'); ?></p>
  </div>
<?php
endif;        
?>
 
<table border="0" cellspacing="0" cellpadding="0" class="sysreqs">

<?php
/*
  
  </div>
<?php
}
else
{
  if ( $failed )
  {
    echo '<div class="pagenav"><table border="0" cellspacing="0" cellpadding="0">';
    run_test('return false;', $lang->get('sysreqs_summary_fail_title'), $lang->get('sysreqs_summary_fail_body'));
    echo '</table></div>';
  }
}
*/
?>

<tr>
  <th colspan="2"><?php echo $lang->get('sysreqs_heading_serverenv'); ?></th>
</tr>

<tr>
  <td><?php echo $lang->get('sysreqs_req_apache'); ?></td>
  <?php
  if ( $req_apache ):
    echo '<td class="good">' . $lang->get('sysreqs_req_found') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_notfound') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <td><?php echo $lang->get('sysreqs_req_php'); ?></td>
  <td class="<?php echo $req_php; ?>">v<?php echo PHP_VERSION; ?></td>
</tr>

<tr>
  <td><?php echo $lang->get('sysreqs_req_safemode'); ?></td>
  <?php
  if ( $req_safemode ):
    echo '<td class="good">' . $lang->get('sysreqs_req_disabled') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_enabled') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <td><?php echo $lang->get('sysreqs_req_uploads'); ?></td>
  <?php
  if ( $req_uploads ):
    echo '<td class="good">' . $lang->get('sysreqs_req_enabled') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_disabled') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <th colspan="2"><?php echo $lang->get('sysreqs_heading_dbms'); ?></th>
</tr>

<tr>
  <td><?php echo $lang->get('sysreqs_req_mysql'); ?></td>
  <?php
  if ( $req_mysql ):
    echo '<td class="good">' . $lang->get('sysreqs_req_supported') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_notfound') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <td><?php echo $lang->get('sysreqs_req_postgresql'); ?></td>
  <?php
  if ( $req_pgsql ):
    echo '<td class="good">' . $lang->get('sysreqs_req_supported') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_notfound') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <th colspan="2"><?php echo $lang->get('sysreqs_heading_files'); ?></th>
</tr>

<tr>
  <td>
    <?php echo $lang->get('sysreqs_req_config_writable'); ?>
  </td>
  <?php
  if ( $req_config_w ):
    echo '<td class="good">' . $lang->get('sysreqs_req_writable') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_unwritable') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <td>
    <?php echo $lang->get('sysreqs_req_htaccess_writable'); ?><br />
    <small><?php echo $lang->get('sysreqs_req_hint_htaccess_writable'); ?></small>
  </td>
  <?php
  if ( $req_htaccess_w ):
    echo '<td class="good">' . $lang->get('sysreqs_req_writable') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_unwritable') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <td>
    <?php echo $lang->get('sysreqs_req_files_writable'); ?>
  </td>
  <?php
  if ( $req_files_w ):
    echo '<td class="good">' . $lang->get('sysreqs_req_writable') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_unwritable') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <td>
    <?php echo $lang->get('sysreqs_req_cache_writable'); ?>
  </td>
  <?php
  if ( $req_cache_w ):
    echo '<td class="good">' . $lang->get('sysreqs_req_writable') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_unwritable') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <th colspan="2"><?php echo $lang->get('sysreqs_heading_images'); ?></th>
</tr>

<tr>
  <td>
    <?php echo $lang->get('sysreqs_req_gd2'); ?><br />
    <small><?php echo $lang->get('sysreqs_req_hint_gd2'); ?></small>
  </td>
  <?php
  if ( $req_gd ):
    echo '<td class="good">' . $lang->get('sysreqs_req_supported') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_notfound') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <td>
    <?php echo $lang->get('sysreqs_req_imagemagick'); ?><br />
    <small><?php echo $lang->get('sysreqs_req_hint_imagemagick'); ?></small>
  </td>
  <?php
  if ( $req_imagick ):
    echo '<td class="good">' . $lang->get('sysreqs_req_found') . ' <small>(' . htmlspecialchars($req_imagick) . ')</small></td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_notfound') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <th colspan="2"><?php echo $lang->get('sysreqs_heading_crypto'); ?></th>
</tr>

<tr>
  <td>
    <?php echo $lang->get('sysreqs_req_gmp'); ?><br />
    <small><?php echo $lang->get('sysreqs_req_hint_gmp'); ?></small>
  </td>
  <?php
  if ( $req_gmp ):
    echo '<td class="good">' . $lang->get('sysreqs_req_supported') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_notfound') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <td>
    <?php echo $lang->get('sysreqs_req_bigint'); ?><br />
    <small><?php echo $lang->get('sysreqs_req_hint_bigint'); ?></small>
  </td>
  <?php
  if ( $req_bigint ):
    echo '<td class="good">' . $lang->get('sysreqs_req_supported') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_notfound') . '</td>';
  endif;
  ?>
</tr>

<tr>
  <td>
    <?php echo $lang->get('sysreqs_req_bcmath'); ?><br />
    <small><?php echo $lang->get('sysreqs_req_hint_bcmath'); ?></small>
  </td>
  <?php
  if ( $req_bcmath ):
    echo '<td class="good">' . $lang->get('sysreqs_req_supported') . '</td>';
  else:
    echo '<td class="bad">' . $lang->get('sysreqs_req_notfound') . '</td>';
  endif;
  ?>
</tr>

</table>

<?php
if ( !$failed ):
?>
  <form action="install.php?stage=database" method="post">
    <?php
      echo '<input type="hidden" name="language" value="' . $lang_id . '" />';
    ?>
    <table border="0">
    <tr>
      <td>
        <input type="submit" value="<?php echo $lang->get('meta_btn_continue'); ?>" />
      </td>
      <td>
        <p>
          <span style="font-weight: bold;"><?php echo $lang->get('meta_lbl_before_continue'); ?></span><br />
          &bull; <?php echo $lang->get('sysreqs_objective_scalebacks'); ?><br />
          &bull; <?php echo $lang->get('license_objective_have_db_info'); ?>
        </p>
      </td>
    </tr>
    </table>
  </form>
<?php
endif;
?>
