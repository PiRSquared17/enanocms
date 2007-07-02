<?php

/*
 * Enano - an open-source CMS capable of wiki functions, Drupal-like sidebar blocks, and everything in between
 * Version 1.0 (Banshee)
 * Copyright (C) 2006-2007 Dan Fuhry
 *
 * This program is Free Software; you can redistribute and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for details.
 */

/**
 * Fetch a value from the site configuration.
 * @param string The identifier of the value ("site_name" etc.)
 * @return string Configuration value, or bool(false) if the value is not set
 */

function getConfig($n)
{
  global $enano_config;
  if ( isset( $enano_config[ $n ] ) )
  {
    return $enano_config[$n];
  }
  else
  {
    return false;
  }
}

/**
 * Update or change a configuration value.
 * @param string The identifier of the value ("site_name" etc.)
 * @param string The new value
 * @return null
 */

function setConfig($n, $v)
{
  
  global $enano_config, $db;
  $enano_config[$n] = $v;
  $v = $db->escape($v);
  
  $e = $db->sql_query('DELETE FROM '.table_prefix.'config WHERE config_name=\''.$n.'\';');
  if ( !$e )
  {
    $db->_die('Error during generic setConfig() call row deletion.');
  }
  
  $e = $db->sql_query('INSERT INTO '.table_prefix.'config(config_name, config_value) VALUES(\''.$n.'\', \''.$v.'\')');
  if ( !$e )
  {
    $db->_die('Error during generic setConfig() call row insertion.');
  }
}

/**
 * Create a URI for an internal link.
 * @param string The full identifier of the page to link to (Special:Administration)
 * @param string The GET query string to append
 * @param bool   If true, perform htmlspecialchars() on the return value to make it HTML-safe
 * @return string
 */

function makeUrl($t, $query = false, $escape = false)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  $flags = '';
  $sep = urlSeparator;
  if ( isset($_GET['printable'] ) )
  {
    $flags .= $sep . 'printable=yes';
    $sep = '&';
  }
  if ( isset($_GET['theme'] ) )
  {
    $flags .= $sep . 'theme='.$session->theme;
    $sep = '&';
  }
  if ( isset($_GET['style'] ) ) {
    $flags .= $sep . 'style='.$session->style; 
    $sep = '&';
  }
  
  $url = $session->append_sid(contentPath.$t.$flags);
  if($query)
  {
    $sep = strstr($url, '?') ? '&' : '?';
    $url = $url . $sep . $query;
  }
  
  return ($escape) ? htmlspecialchars($url) : $url;
}

/**
 * Create a URI for an internal link, and be namespace-friendly. Watch out for this one because it's different from most other Enano functions, in that the namespace is the first parameter.
 * @param string The namespace ID
 * @param string The page ID
 * @param string The GET query string to append
 * @param bool   If true, perform htmlspecialchars() on the return value to make it HTML-safe
 * @return string
 */

function makeUrlNS($n, $t, $query = false, $escape = false)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  $flags = '';
  
  if(defined('ENANO_BASE_CLASSES_INITIALIZED'))
  {
    $sep = urlSeparator;
  }
  else
  {
    $sep = (strstr($_SERVER['REQUEST_URI'], '?')) ? '&' : '?';
  }
  if ( isset( $_GET['printable'] ) ) {
    $flags .= $sep . 'printable';
    $sep = '&';
  }
  if ( isset( $_GET['theme'] ) ) 
  {
    $flags .= $sep . 'theme='.$session->theme;
    $sep = '&';
  }
  if ( isset( $_GET['style'] ) )
  {
    $flags .= $sep . 'style='.$session->style;
    $sep = '&';
  }
  
  if(defined('ENANO_BASE_CLASSES_INITIALIZED'))
  {
    $url = contentPath . $paths->nslist[$n] . $t . $flags;
  }
  else
  {
    // If the path manager hasn't been initted yet, take an educated guess at what the URI should be
    $url = contentPath . $n . ':' . $t . $flags;
  }
  
  if($query)
  {
    if(strstr($url, '?')) 
    {
      $sep =  '&';
    }
    else
    {
      $sep = '?';
    }
    $url = $url . $sep . $query . $flags;
  }
  
  if(defined('ENANO_BASE_CLASSES_INITIALIZED'))
  {
    $url = $session->append_sid($url);
  }
  
  return ($escape) ? htmlspecialchars($url) : $url;
}

/**
 * Create a URI for an internal link, be namespace-friendly, and add http://hostname/scriptpath to the beginning if possible. Watch out for this one because it's different from most other Enano functions, in that the namespace is the first parameter.
 * @param string The namespace ID
 * @param string The page ID
 * @param string The GET query string to append
 * @param bool   If true, perform htmlspecialchars() on the return value to make it HTML-safe
 * @return string
 */

function makeUrlComplete($n, $t, $query = false, $escape = false)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  $flags = '';
  
  if(defined('ENANO_BASE_CLASSES_INITIALIZED'))
  {
    $sep = urlSeparator;
  }
  else
  {
    $sep = (strstr($_SERVER['REQUEST_URI'], '?')) ? '&' : '?';
  }
  if ( isset( $_GET['printable'] ) ) {
    $flags .= $sep . 'printable';
    $sep = '&';
  }
  if ( isset( $_GET['theme'] ) ) 
  {
    $flags .= $sep . 'theme='.$session->theme;
    $sep = '&';
  }
  if ( isset( $_GET['style'] ) )
  {
    $flags .= $sep . 'style='.$session->style;
    $sep = '&';
  }
  
  if(defined('ENANO_BASE_CLASSES_INITIALIZED'))
  {
    $url = $session->append_sid(contentPath . $paths->nslist[$n] . $t . $flags);
  }
  else
  {
    // If the path manager hasn't been initted yet, take an educated guess at what the URI should be
    $url = contentPath . $n . ':' . $t . $flags;
  }
  if($query)
  {
    if(strstr($url, '?')) $sep =  '&';
    else $sep = '?';
    $url = $url . $sep . $query . $flags;
  }
  
  $baseprot = 'http' . ( isset($_SERVER['HTTPS']) ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'];
  $url = $baseprot . $url;
  
  return ($escape) ? htmlspecialchars($url) : $url;
}

/**
 * Redirect the user to the specified URL.
 * @param string $url The URL, either relative or absolute.
 * @param string $title The title of the message
 * @param string $message A short message to show to the user
 * @param string $timeout Timeout, in seconds, to delay the redirect. Defaults to 3.
 */
 
function redirect($url, $title = 'Redirecting...', $message = 'Please wait while you are redirected.', $timeout = 3)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  
  if ( $timeout == 0 )
  {
    header('Location: ' . $url);
    header('HTTP/1.1 307 Temporary Redirect');
  }
  
  $template->add_header('<meta http-equiv="refresh" content="' . $timeout . '; url=' . str_replace('"', '\\"', $url) . '" />');
  $template->add_header('<script type="text/javascript">
      function __r() {
        // FUNCTION AUTOMATICALLY GENERATED
        window.location="' . str_replace('"', '\\"', $url) . '";
      }
      setTimeout(\'__r();\', ' . $timeout . '000);
    </script>
    ');
  
  $template->tpl_strings['PAGE_NAME'] = $title;
  $template->header(true);
  echo '<p>' . $message . '</p><p>If you are not redirected within ' . ( $timeout + 1 ) . ' seconds, <a href="' . str_replace('"', '\\"', $url) . '">please click here</a>.</p>';
  $template->footer(true);
  
  $db->close();
  exit(0);
  
}

// Removed wikiFormat() from here, replaced with RenderMan::render

/**
 * Tell me if the page exists or not.
 * @param string the full page ID (Special:Administration) of the page to check for
 * @return bool True if the page exists, false otherwise
 */

function isPage($p) {
  global $db, $session, $paths, $template, $plugins; // Common objects
  
  // Try the easy way first ;-)
  if ( isset( $paths->pages[ $p ] ) )
  {
    return true;
  }
  
  // Special case for Special, Template, and Admin pages that can't have slashes in their URIs
  $ns_test = RenderMan::strToPageID( $p );
  
  if($ns_test[1] != 'Special' && $ns_test[1] != 'Template' && $ns_test[1] != 'Admin')
  {
    return false;
  }
  
  $particles = explode('/', $p);
  if ( isset ( $paths->pages[ $particles[ 0 ] ] ) )
  {
    return true;
  }
  else
  {
    return false;
  }
}

function arrayItemUp($arr, $keyname) {
  $keylist = array_keys($arr);
  $keyflop = array_flip($keylist);
  $idx = $keyflop[$keyname];
  $idxm = $idx - 1;
  $temp = $arr[$keylist[$idxm]];
  if($arr[$keylist[0]] == $arr[$keyname]) return $arr;
  $arr[$keylist[$idxm]] = $arr[$keylist[$idx]];
  $arr[$keylist[$idx]] = $temp;
  return $arr;
}

function arrayItemDown($arr, $keyname) {
  $keylist = array_keys($arr);
  $keyflop = array_flip($keylist);
  $idx = $keyflop[$keyname];
  $idxm = $idx + 1;
  $temp = $arr[$keylist[$idxm]];
  $sz = sizeof($arr); $sz--;
  if($arr[$keylist[$sz]] == $arr[$keyname]) return $arr;
  $arr[$keylist[$idxm]]  =  $arr[$keylist[$idx]];
  $arr[$keylist[$idx]]   =  $temp;
  return $arr;
}

function arrayItemTop($arr, $keyname) {
  $keylist = array_keys($arr);
  $keyflop = array_flip($keylist);
  $idx = $keyflop[$keyname];
  while( $orig != $arr[$keylist[0]] ) {
    // echo 'Keyname: '.$keylist[$idx] . '<br />'; flush(); ob_flush(); // Debugger
    if($idx < 0) return $arr;
    if($keylist[$idx] == '' || $keylist[$idx] < 0 || !$keylist[$idx]) {
      /* echo 'Infinite loop caught in arrayItemTop(<br /><pre>';
      print_r($arr);
      echo '</pre><br />, '.$keyname.');<br /><br />EnanoCMS: Critical error during function call, exiting to prevent excessive server load.';
      exit; */
      return $arr;
    }
    $arr = arrayItemUp($arr, $keylist[$idx]);
    $idx--;
  }
  return $arr;
}

function arrayItemBottom($arr, $keyname) {
  $keylist = array_keys($arr);
  $keyflop = array_flip($keylist);
  $idx = $keyflop[$keyname];
  $sz = sizeof($arr); $sz--;
  while( $orig != $arr[$keylist[$sz]] ) {
    // echo 'Keyname: '.$keylist[$idx] . '<br />'; flush(); ob_flush(); // Debugger
    if($idx > $sz) return $arr;
    if($keylist[$idx] == '' || $keylist[$idx] < 0 || !$keylist[$idx]) {
      echo 'Infinite loop caught in arrayItemBottom(<br /><pre>';
      print_r($arr);
      echo '</pre><br />, '.$keyname.');<br /><br />EnanoCMS: Critical error during function call, exiting to prevent excessive server load.';
      exit;
    }
    $arr = arrayItemDown($arr, $keylist[$idx]);
    $idx++;
  }
  return $arr;
}

// Convert IP address to hex string
// Input:  127.0.0.1  (string)
// Output: 0x7f000001 (string)
// Updated 12/8/06 to work with PHP4 and not use eval() (blech)
function ip2hex($ip) {
  if ( preg_match('/^([0-9a-f:]+)$/', $ip) )
  {
    // this is an ipv6 address
    return str_replace(':', '', $ip);
  }
  $nums = explode('.', $ip);
  if(sizeof($nums) != 4) return false;
  $str = '0x';
  foreach($nums as $n)
  {
    $str .= (string)dechex($n);
  }
  return $str;
}

// Convert DWord to IP address
// Input:  0x7f000001
// Output: 127.0.0.1
// Updated 12/8/06 to work with PHP4 and not use eval() (blech)
function hex2ip($in) {
  if(substr($in, 0, 2) == '0x') $ip = substr($in, 2, 8);
  else $ip = substr($in, 0, 8);
  $octets = enano_str_split($ip, 2);
  $str = '';
  $newoct = Array();
  foreach($octets as $o)
  {
    $o = (int)hexdec($o);
    $newoct[] = $o;
  }
  return implode('.', $newoct);
}

// Function strip_php moved to RenderMan class

function die_semicritical($t, $p)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  $db->close();
  
  if ( ob_get_status() )
    ob_end_clean();
  
  dc_here('functions: <span style="color: red">calling die_semicritical</span>');
  
  $tpl = new template_nodb();
  $tpl->load_theme('oxygen', 'bleu');
  $tpl->tpl_strings['SITE_NAME'] = getConfig('site_name');
  $tpl->tpl_strings['SITE_DESC'] = getConfig('site_desc');
  $tpl->tpl_strings['COPYRIGHT'] = getConfig('copyright_notice');
  $tpl->tpl_strings['PAGE_NAME'] = $t;
  $tpl->header();
  echo $p;
  $tpl->footer();
  
  exit;
}

function die_friendly($t, $p)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  
  if ( ob_get_status() )
    ob_end_clean();
  
  dc_here('functions: <span style="color: red">calling die_friendly</span>');
  $paths->cpage['name'] = $t;
  $template->tpl_strings['PAGE_NAME'] = $t;
  $template->header();
  echo $p;
  $template->footer();
  $db->close();
  
  exit;
}

function grinding_halt($t, $p)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  
  $db->close();
  
  if ( ob_get_status() )
    ob_end_clean();
  
  dc_here('functions: <span style="color: red">calling grinding_halt</span>');
  $tpl = new template_nodb();
  $tpl->load_theme('oxygen', 'bleu');
  $tpl->tpl_strings['SITE_NAME'] = 'Critical error';
  $tpl->tpl_strings['SITE_DESC'] = 'This website is experiencing a serious error and cannot load.';
  $tpl->tpl_strings['COPYRIGHT'] = 'Unable to retrieve copyright information';
  $tpl->tpl_strings['PAGE_NAME'] = $t;
  $tpl->header();
  echo $p;
  $tpl->footer();
  exit;
}

function show_category_info() {
  global $db, $session, $paths, $template, $plugins; // Common objects
  dc_here('functions: showing category info');
  if($template->no_headers && !strpos($_SERVER['REQUEST_URI'], 'ajax.php')) return '';
  if($paths->namespace=='Category')
  {
    $q = $db->sql_query('SELECT page_id,namespace FROM '.table_prefix.'categories WHERE category_id=\''.$paths->cpage['urlname_nons'].'\' AND namespace=\'Category\' ORDER BY page_id;');
    if(!$q) $db->_die('The category information could not be selected.');
    $ticker = -1;
    echo '<h3>Subcategories</h3>';
    if($db->numrows() < 1) echo '<p>There are no subcategories in this category.</p>';
    echo '<table border="0" cellspacing="1" cellpadding="4">';
    while($row = $db->fetchrow())
    {
      $ticker++;if($ticker==3) $ticker=0;
      if($ticker==0) echo '<tr>';
      echo '<td style="width: 200px;"><a href="'.makeUrlNS($row['namespace'], $row['page_id']).'">'.$paths->pages[$paths->nslist[$row['namespace']].$row['page_id']]['name'].'</a></td>';
      if($ticker==2) echo '</tr>';
    }
    $db->free_result();
    if($ticker) echo '</tr>';
    echo '</table>';
    
    $q = $db->sql_query('SELECT page_id,namespace FROM '.table_prefix.'categories WHERE category_id=\''.$paths->cpage['urlname_nons'].'\' AND namespace!=\'Category\' ORDER BY page_id;');
    if(!$q) $db->_die('The category information could not be selected.');
    $ticker = -1;
    echo '<h3>Pages</h3>';
    if($db->numrows() < 1) echo '<p>There are no pages in this category.</p>';
    echo '<table border="0" cellspacing="1" cellpadding="4">';
    while($row = $db->fetchrow())
    {
      $ticker++;if($ticker==3) $ticker=0;
      if($ticker==0) echo '<tr>';
      echo '<td style="width: 200px;"><a href="'.makeUrlNS($row['namespace'], $row['page_id']).'">'.$paths->pages[$paths->nslist[$row['namespace']].$row['page_id']]['name'].'</a></td>';
      if($ticker==2) echo '</tr>';
    }
    $db->free_result();
    if($ticker) echo '</tr>';
    echo '</table><br /><br />';
  }
  $q = $db->sql_query('SELECT category_id FROM '.table_prefix.'categories WHERE page_id=\''.$paths->cpage['urlname_nons'].'\' AND namespace=\''.$paths->namespace.'\'');
  if(!$q) $db->_die('The error seems to have occurred during selection of category data.');
  if($db->numrows() > 0) {
    echo '<div class="mdg-comment" style="margin-left: 0;">Categories: ';
    $i=0;
    while($r = $db->fetchrow())
    {
      if($i>0) echo ', ';
      $i++;
      echo '<a href="'.makeUrlNS('Category', $r['category_id']).'">'.$paths->pages[$paths->nslist['Category'].$r['category_id']]['name'].'</a>';
    }
    if( ( $paths->wiki_mode && !$paths->page_protected ) || ( $session->get_permissions('edit_cat') && $session->get_permissions('even_when_protected') ) ) echo ' [ <a href="'.makeUrl($paths->page, 'do=catedit', true).'" onclick="ajaxCatEdit(); return false;">edit categorization</a> ]</div>';
  } else {
    echo '<div class="mdg-comment" style="margin-left: 0;">Categories: ';
    echo '(Uncategorized)';
    if( ( $paths->wiki_mode && !$paths->page_protected ) || ( $session->get_permissions('edit_cat') && $session->get_permissions('even_when_protected') ) ) echo ' [ <a href="'.makeUrl($paths->page, 'do=catedit', true).'" onclick="ajaxCatEdit(); return false;">edit categorization</a> ]</div>';
    else echo '</div>';
  }
  $db->free_result();
}

function show_file_info()
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  if($paths->namespace != 'File') return null; // Prevent unnecessary work
  $selfn = $paths->cpage['urlname_nons']; // substr($paths->page, strlen($paths->nslist['File']), strlen($paths->cpage));
  if(substr($paths->cpage['name'], 0, strlen($paths->nslist['File']))==$paths->nslist['File']) $selfn = substr($paths->cpage['urlname_nons'], strlen($paths->nslist['File']), strlen($paths->cpage['urlname_nons']));
  $q = $db->sql_query('SELECT mimetype,time_id,size FROM '.table_prefix.'files WHERE page_id=\''.$selfn.'\' ORDER BY time_id DESC;');
  if(!$q) $db->_die('The file type could not be fetched.');
  if($db->numrows() < 1) { echo '<div class="mdg-comment" style="margin-left: 0;"><h3>Uploaded file</h3><p>There are no files uploaded with this name yet. <a href="'.makeUrlNS('Special', 'UploadFile/'.$paths->cpage['urlname_nons']).'">Upload a file...</a></p></div><br />'; return; }
  $r = $db->fetchrow();
  $mimetype = $r['mimetype'];
  $datestring = date('F d, Y h:i a', (int)$r['time_id']);
  echo '<div class="mdg-comment" style="margin-left: 0;"><p><h3>Uploaded file</h3></p><p>Type: '.$r['mimetype'].'<br />Size: ';
  $fs = $r['size'];
  echo $fs.' bytes';
  $fs = (int)$fs;
  if($fs >= 1048576)
  {
    $fs = round($fs / 1048576, 1);
    echo ' ('.$fs.' MB)';
  } elseif($fs >= 1024) {
    $fs = round($fs / 1024, 1);
    echo ' ('.$fs.' KB)';
  }
  echo '<br />Uploaded: '.$datestring.'</p>';
  if(substr($mimetype, 0, 6)!='image/' && ( substr($mimetype, 0, 5) != 'text/' || $mimetype == 'text/html' || $mimetype == 'text/javascript' ))
  {
    echo '<div class="warning-box">This file type may contain viruses or other code that could harm your computer. You should exercise caution if you download it.</div>';
  }
  if(substr($mimetype, 0, 6)=='image/')
  {
    echo '<p><a href="'.makeUrlNS('Special', 'DownloadFile'.'/'.$selfn).'"><img style="border: 0;" alt="'.$paths->page.'" src="'.makeUrlNS('Special', 'DownloadFile'.'/'.$selfn.htmlspecialchars(urlSeparator).'preview').'" /></a></p>';
  }
  echo '<p><a href="'.makeUrlNS('Special', 'DownloadFile'.'/'.$selfn.'/'.$r['time_id'].htmlspecialchars(urlSeparator).'download').'">Download this file</a>';
  if(!$paths->page_protected && ( $paths->wiki_mode || $session->get_permissions('upload_new_version') ))
  {
    echo '  |  <a href="'.makeUrlNS('Special', 'UploadFile'.'/'.$selfn).'">Upload new version</a>';
  }
  echo '</p>';
  if($db->numrows() > 1)
  {
    echo '<h3>File history</h3><p>';
    while($r = $db->fetchrow())
    {
      echo '(<a href="'.makeUrlNS('Special', 'DownloadFile'.'/'.$selfn.'/'.$r['time_id'].htmlspecialchars(urlSeparator).'download').'">this ver</a>) ';
      if($session->get_permissions('history_rollback'))
        echo ' (<a href="#" onclick="ajaxRollback(\''.$r['time_id'].'\'); return false;">revert</a>) ';
      $mimetype = $r['mimetype'];
      $datestring = date('F d, Y h:i a', (int)$r['time_id']);
      echo $datestring.': '.$r['mimetype'].', ';
      $fs = $r['size'];
      $fs = (int)$fs;
      if($fs >= 1048576)
      {
        $fs = round($fs / 1048576, 1);
        echo ' '.$fs.' MB';
      } elseif($fs >= 1024) {
        $fs = round($fs / 1024, 1);
        echo ' '.$fs.' KB';
      } else {
        echo ' '.$fs.' bytes';
      }
      echo '<br />';
    }
    echo '</p>';
  }
  $db->free_result();
  echo '</div><br />';
}

function display_page_headers()
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  if($session->get_permissions('vote_reset') && $paths->cpage['delvotes'] > 0)
  {
    $hr = implode(', ', explode('|', $paths->cpage['delvote_ips']));
    $is = 'is';
    $s = '';
    $s2 = 's';
    if ( $paths->cpage['delvotes'] > 1)
    {
      $is = 'are';
      $s = 's';
      $s2 = '';
    }
    echo '<div class="info-box" style="margin-left: 0; margin-top: 5px;" id="mdgDeleteVoteNoticeBox">
            <b>Notice:</b> There '.$is.' '.$paths->cpage['delvotes'].' user'.$s.' that think'.$s2.' this page should be deleted.<br />
            <b>Users that voted:</b> ' . $hr . '<br />
            <a href="'.makeUrl($paths->page, 'do=deletepage').'" onclick="ajaxDeletePage(); return false;">Delete page</a>  |  <a href="'.makeUrl($paths->page, 'do=resetvotes').'" onclick="ajaxResetDelVotes(); return false;">Reset votes</a>
          </div>';
  }
}

function display_page_footers()
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  if(isset($_GET['nofooters'])) return;
  $code = $plugins->setHook('send_page_footers');
  foreach ( $code as $cmd )
  {
    eval($cmd);
  }
  show_file_info();
  show_category_info();
}

function password_prompt($id = false)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  if(!$id) $id = $paths->page;
  if(isset($paths->pages[$id]['password']) && strlen($paths->pages[$id]['password']) == 40 && !isset($_REQUEST['pagepass']))
  {
    die_friendly('Password required', '<p>You must supply a password to access this page.</p><form action="'.makeUrl($paths->pages[$id]['urlname']).'" method="post"><p>Password: <input name="pagepass" type="password" /></p><p><input type="submit" value="Submit" /></p>');
  } elseif(isset($_REQUEST['pagepass'])) {
    $p = (preg_match('#^([a-f0-9]*){40}$#', $_REQUEST['pagepass'])) ? $_REQUEST['pagepass'] : sha1($_REQUEST['pagepass']);
    if($p != $paths->pages[$id]['password']) die_friendly('Password required', '<p style="color: red;">The password you entered is incorrect.</p><form action="'.makeUrl($paths->page).'" method="post"><p>Password: <input name="pagepass" type="password" /></p><p><input type="submit" value="Submit" /></p>');
  }
}

function str_hex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= ' '.dechex(ord($string[$i]));
    }
    return substr($hex, 1, strlen($hex));
}

// Function pulled from phpBB's smtp.php
function smtp_get_response($socket, $response, $line = __LINE__) 
{
	$server_response = '';
	while (substr($server_response, 3, 1) != ' ') 
	{
		if (!($server_response = fgets($socket, 256))) 
		{
      die_friendly('SMTP Error', "<p>Couldn't get mail server response codes</p>");
		}
	} 

	if (!(substr($server_response, 0, 3) == $response)) 
	{ 
    die_friendly('SMTP Error', "<p>Ran into problems sending mail. Response: $server_response</p>");
	} 
}

function smtp_send_email($to, $subject, $message, $from)
{
  return smtp_send_email_core($to, $subject, $message, "From: <$from>\n");
}

// Replacement or substitute for PHP's mail command
// Ported from phpBB - copyright (C) phpBB group, GPL.
function smtp_send_email_core($mail_to, $subject, $message, $headers = '')
{
	global $board_config;

	// Fix any bare linefeeds in the message to make it RFC821 Compliant.
	$message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);

	if ($headers != '')
	{
		if (is_array($headers))
		{
			if (sizeof($headers) > 1)
			{
				$headers = join("\n", $headers);
			}
			else
			{
				$headers = $headers[0];
			}
		}
		$headers = chop($headers);

		// Make sure there are no bare linefeeds in the headers
		$headers = preg_replace('#(?<!\r)\n#si', "\r\n", $headers);

		// Ok this is rather confusing all things considered,
		// but we have to grab bcc and cc headers and treat them differently
		// Something we really didn't take into consideration originally
		$header_array = explode("\r\n", $headers);
		@reset($header_array);

		$headers = '';
		while(list(, $header) = each($header_array))
		{
			if (preg_match('#^cc:#si', $header))
			{
				$cc = preg_replace('#^cc:(.*)#si', '\1', $header);
			}
			else if (preg_match('#^bcc:#si', $header))
			{
				$bcc = preg_replace('#^bcc:(.*)#si', '\1', $header);
				$header = '';
			}
			$headers .= ($header != '') ? $header . "\r\n" : '';
		}

		$headers = chop($headers);
		$cc = explode(', ', $cc);
		$bcc = explode(', ', $bcc);
	}

	if (trim($subject) == '')
	{
		die_friendly(GENERAL_ERROR, "No email Subject specified");
	}

	if (trim($message) == '')
	{
		die_friendly(GENERAL_ERROR, "Email message was blank");
	}
  
  // setup SMTP
  $host = getConfig('smtp_server');
  if ( empty($host) )
    return 'No smtp_host in config';
  if ( strstr($host, ':' ) )
  {
    $n = explode(':', $host);
    $smtp_host = $n[0];
    $port = intval($n[1]);
  }
  else
  {
    $smtp_host = $host;
    $port = 25;
  }
  
  $smtp_user = getConfig('smtp_user');
  $smtp_pass = getConfig('smtp_password');
  
	// Ok we have error checked as much as we can to this point let's get on
	// it already.
	if( !$socket = @fsockopen($smtp_host, $port, $errno, $errstr, 20) )
	{
		die_friendly(GENERAL_ERROR, "Could not connect to smtp host : $errno : $errstr");
	}

	// Wait for reply
	smtp_get_response($socket, "220", __LINE__);

	// Do we want to use AUTH?, send RFC2554 EHLO, else send RFC821 HELO
	// This improved as provided by SirSir to accomodate
	if( !empty($smtp_user) && !empty($smtp_pass) )
	{ 
		enano_fputs($socket, "EHLO " . $smtp_host . "\r\n");
		smtp_get_response($socket, "250", __LINE__);

		enano_fputs($socket, "AUTH LOGIN\r\n");
		smtp_get_response($socket, "334", __LINE__);

		enano_fputs($socket, base64_encode($smtp_user) . "\r\n");
		smtp_get_response($socket, "334", __LINE__);

		enano_fputs($socket, base64_encode($smtp_pass) . "\r\n");
		smtp_get_response($socket, "235", __LINE__);
	}
	else
	{
		enano_fputs($socket, "HELO " . $smtp_host . "\r\n");
		smtp_get_response($socket, "250", __LINE__);
	}

	// From this point onward most server response codes should be 250
	// Specify who the mail is from....
	enano_fputs($socket, "MAIL FROM: <" . getConfig('contact_email') . ">\r\n");
	smtp_get_response($socket, "250", __LINE__);

	// Specify each user to send to and build to header.
	$to_header = '';

	// Add an additional bit of error checking to the To field.
	$mail_to = (trim($mail_to) == '') ? 'Undisclosed-recipients:;' : trim($mail_to);
	if (preg_match('#[^ ]+\@[^ ]+#', $mail_to))
	{
		enano_fputs($socket, "RCPT TO: <$mail_to>\r\n");
		smtp_get_response($socket, "250", __LINE__);
	}

	// Ok now do the CC and BCC fields...
	@reset($bcc);
	while(list(, $bcc_address) = each($bcc))
	{
		// Add an additional bit of error checking to bcc header...
		$bcc_address = trim($bcc_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $bcc_address))
		{
			enano_fputs($socket, "RCPT TO: <$bcc_address>\r\n");
			smtp_get_response($socket, "250", __LINE__);
		}
	}

	@reset($cc);
	while(list(, $cc_address) = each($cc))
	{
		// Add an additional bit of error checking to cc header
		$cc_address = trim($cc_address);
		if (preg_match('#[^ ]+\@[^ ]+#', $cc_address))
		{
			enano_fputs($socket, "RCPT TO: <$cc_address>\r\n");
			smtp_get_response($socket, "250", __LINE__);
		}
	}

	// Ok now we tell the server we are ready to start sending data
	enano_fputs($socket, "DATA\r\n");

	// This is the last response code we look for until the end of the message.
	smtp_get_response($socket, "354", __LINE__);

	// Send the Subject Line...
	enano_fputs($socket, "Subject: $subject\r\n");

	// Now the To Header.
	enano_fputs($socket, "To: $mail_to\r\n");

	// Now any custom headers....
	enano_fputs($socket, "$headers\r\n\r\n");

	// Ok now we are ready for the message...
	enano_fputs($socket, "$message\r\n");

	// Ok the all the ingredients are mixed in let's cook this puppy...
	enano_fputs($socket, ".\r\n");
	smtp_get_response($socket, "250", __LINE__);

	// Now tell the server we are done and close the socket...
	enano_fputs($socket, "QUIT\r\n");
	fclose($socket);

	return TRUE;
}

/**
 * Tell which version of Enano we're running.
 * @param bool $long if true, uses English version names (e.g. alpha, beta, release candidate). If false (default) uses abbreviations (1.0a1, 1.0b3, 1.0RC2, etc.)
 * @return string
 */

function enano_version($long = false, $no_nightly = false)
{
  $r = getConfig('enano_version');
  $rc = ( $long ) ? ' release candidate ' : 'RC';
  $b = ( $long ) ? ' beta ' : 'b';
  $a = ( $long ) ? ' alpha ' : 'a';
  if($v = getConfig('enano_rc_version')) $r .= $rc.$v;
  if($v = getConfig('enano_beta_version')) $r .= $b.$v;
  if($v = getConfig('enano_alpha_version')) $r .= $a.$v;
  if ( defined('ENANO_NIGHTLY') && !$no_nightly )
  {
    $nightlytag  = ENANO_NIGHTLY_MONTH . '-' . ENANO_NIGHTLY_DAY . '-' . ENANO_NIGHTLY_YEAR;
    $nightlylong = ' nightly; build date: ' . ENANO_NIGHTLY_MONTH . '-' . ENANO_NIGHTLY_DAY . '-' . ENANO_NIGHTLY_YEAR;
    $r = ( $long ) ? $r . $nightlylong : $r . '-nightly-' . $nightlytag;
  }
  return $r;
}

function _dualurlenc($t) {
  return rawurlencode(rawurlencode($t));
}
  
function _die($t) {
  $_ob = 'document.getElementById("ajaxEditContainer").innerHTML = unescape(\'' . rawurlencode('' . $t . '') . '\')';
  die($_ob);
}

function jsdie($text) {
  global $db, $session, $paths, $template, $plugins; // Common objects
  $text = rawurlencode($text . "\n\nSQL Backtrace:\n" . $db->sql_backtrace());
  echo 'document.getElementById("ajaxEditContainer").innerHTML = unescape(\''.$text.'\');';
}

// HTML sanitizing function - written by Kallahar
// Original function at: http://quickwired.com/kallahar/smallprojects/php_xss_filter_function.php

// UNUSED - todo: remove this in gold or put it to use

function RemoveXSS($val) {
  // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
  // this prevents some character re-spacing such as <java\0script>
  // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
  $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
  
  // straight replacements, the user should never need these since they're normal characters
  // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
  $search  = 'abcdefghijklmnopqrstuvwxyz';
  $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $search .= '1234567890!@#$%^&*()';
  $search .= '~`";:?+/={}[]-_|\'\\';
  for ($i = 0; $i < strlen($search); $i++) {
    // ;? matches the ;, which is optional
    // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
  
    // &#x0040 @ search for the hex values
    $val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
    // &#00064 @ 0{0,7} matches '0' zero to seven times
    $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
  }
  
  // now the only remaining whitespace attacks are \t, \n, and \r
  $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
  $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
  $ra = array_merge($ra1, $ra2);
  
  $found = true; // keep replacing as long as the previous round replaced something
  while ($found == true) {
    $val_before = $val;
    for ($i = 0; $i < sizeof($ra); $i++) {
      $pattern = '/';
      for ($j = 0; $j < strlen($ra[$i]); $j++) {
        if ($j > 0) {
          $pattern .= '(';
          $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
          $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
          $pattern .= ')?';
        }
        $pattern .= $ra[$i][$j];
      }
      $pattern .= '/i';
      $replacement = substr($ra[$i], 0, 2).'<b></b>'.substr($ra[$i], 2); // add in <> to nerf the tag
      $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
      if ($val_before == $val) {
        // no replacements were made, so exit the loop
        $found = false;
      }
    }
  }
  return $val;
}

/**
 * Capitalizes the first letter of a string
 * @param $text string the text to be transformed
 * @return string
 */
 
function capitalize_first_letter($text)
{
  return strtoupper(substr($text, 0, 1)) . substr($text, 1);
}

/**
 * Checks if a value in a bitfield is on or off
 * @param $bitfield int the bit-field value
 * @param $value int the value to switch off
 * @return bool
 */
 
function is_bit($bitfield, $value)
{
  return ( $bitfield & $value ) ? true : false;
}

/**
 * Trims spaces/newlines from the beginning and end of a string
 * @param $text the text to process
 * @return string
 */
 
function trim_spaces($text)
{
  $d = true;
  while($d)
  {
    $c = substr($text, 0, 1);
    $a = substr($text, strlen($text)-1, strlen($text));
    if($c == "\n" || $c == "\r" || $c == "\t" || $c == ' ') $text = substr($text, 1, strlen($text));
    elseif($a == "\n" || $a == "\r" || $a == "\t" || $a == ' ') $text = substr($text, 0, strlen($text)-1);
    else $d = false;
  }
  return $text;
}

/**
 * Enano-ese equivalent of str_split() which is only found in PHP5
 * @param $text string the text to split
 * @param $inc int size of each block
 * @return array
 */
 
function enano_str_split($text, $inc = 1)
{
  if($inc < 1) 
  {
    return false;
  }
  if($inc >= strlen($text))
  {
    return Array($text);
  }
  $len = ceil(strlen($text) / $inc);
  $ret = Array();
  for ( $i = 0; $i < strlen($text); $i = $i + $inc )
  {
    $ret[] = substr($text, $i, $inc);
  }
  return $ret;
}

/**
 * Converts a hexadecimal number to a binary string.
 * @param text string hexadecimal number
 * @return string
 */
function hex2bin($text)
{
  $arr = enano_str_split($text, 2);
  $ret = '';
  for ($i=0; $i<sizeof($arr); $i++)
  {
    $ret .= chr(hexdec($arr[$i]));
  }
  return $ret;
}

/**
 * Generates and/or prints a human-readable backtrace
 * @param bool $return - if true, this function returns a string, otherwise returns null
 * @return mixed
 */
 
function enano_debug_print_backtrace($return = false)
{
  ob_start();
  echo '<pre>';
  if ( function_exists('debug_print_backtrace') )
  {
    debug_print_backtrace();
  }
  else
  {
    echo '<b>Warning:</b> No debug_print_backtrace() support!';
  }
  echo '</pre>';
  $c = ob_get_contents();
  ob_end_clean();
  if($return) return $c;
  else echo $c;
  return null;
}

/**
 * Like rawurlencode(), but encodes all characters
 * @param string $text the text to encode
 * @param optional string $prefix text before each hex character
 * @param optional string $suffix text after each hex character
 * @return string
 */
 
function hexencode($text, $prefix = '%', $suffix = '')
{
  $arr = enano_str_split($text);
  $r = '';
  foreach($arr as $a)
  {
    $nibble = (string)dechex(ord($a));
    if(strlen($nibble) == 1) $nibble = '0' . $nibble;
    $r .= $prefix . $nibble . $suffix;
  }
  return $r;
}

/**
 * Enano-ese equivalent of get_magic_quotes_gpc()
 * @return bool
 */
 
function enano_get_magic_quotes_gpc()
{
  if(function_exists('get_magic_quotes_gpc'))
  {
    return ( get_magic_quotes_gpc() == 1 );
  }
  else
  {
    return ( strtolower(@ini_get('magic_quotes_gpc')) == '1' );
  }
}

/**
 * Recursive stripslashes()
 * @param array
 * @return array
 */
 
function stripslashes_recurse($arr)
{
  foreach($arr as $k => $xxxx)
  {
    $val =& $arr[$k];
    if(is_string($val))
      $val = stripslashes($val);
    elseif(is_array($val))
      $val = stripslashes_recurse($val);
  }
  return $arr;
}

/**
 * Recursive function to remove all NUL bytes from a string
 * @param array
 * @return array
 */
 
function strip_nul_chars($arr)
{
  foreach($arr as $k => $xxxx_unused)
  {
    $val =& $arr[$k];
    if(is_string($val))
      $val = str_replace("\000", '', $val);
    elseif(is_array($val))
      $val = strip_nul_chars($val);
  }
  return $arr;
}

/**
 * If magic_quotes_gpc is on, calls stripslashes() on everything in $_GET/$_POST/$_COOKIE
 * @ignore - this doesn't work too well in my tests
 * @todo port version from the PHP manual
 * @return void
 */
function strip_magic_quotes_gpc()
{
  if(enano_get_magic_quotes_gpc())
  {
    $_POST   = stripslashes_recurse($_POST);
    $_GET    = stripslashes_recurse($_GET);
    $_COOKIE = stripslashes_recurse($_COOKIE);
  }
  $_POST   = strip_nul_chars($_POST);
  $_GET    = strip_nul_chars($_GET);
  $_COOKIE = strip_nul_chars($_COOKIE);
}

/**
 * A very basic single-character compression algorithm for binary strings/bitfields
 * @param string $bits the text to compress
 * @return string
 */
 
function compress_bitfield($bits)
{
  $crc32 = crc32($bits);
  $bits .= '0';
  $start_pos = 0;
  $current = substr($bits, 1, 1);
  $last    = substr($bits, 0, 1);
  $chunk_size = 1;
  $len = strlen($bits);
  $crc = $len;
  $crcval = 0;
  for ( $i = 1; $i < $len; $i++ )
  {
    $current = substr($bits, $i, 1);
    $last    = substr($bits, $i - 1, 1);
    $next    = substr($bits, $i + 1, 1);
    // Are we on the last character?
    if($current == $last && $i+1 < $len)
      $chunk_size++;
    else
    {
      if($i+1 == $len && $current == $next)
      {
        // This character completes a chunk
        $chunk_size++;
        $i++;
        $chunk = substr($bits, $start_pos, $chunk_size);
        $chunklen = strlen($chunk);
        $newchunk = $last . '[' . $chunklen . ']';
        $newlen   = strlen($newchunk);
        $bits = substr($bits, 0, $start_pos) . $newchunk . substr($bits, $i, $len);
        $chunk_size = 1;
        $i = $start_pos + $newlen;
        $start_pos = $i;
        $len = strlen($bits);
        $crcval = $crcval + $chunklen;
      }
      else
      {
        // Last character completed a chunk
        $chunk = substr($bits, $start_pos, $chunk_size);
        $chunklen = strlen($chunk);
        $newchunk = $last . '[' . $chunklen . '],';
        $newlen   = strlen($newchunk);
        $bits = substr($bits, 0, $start_pos) . $newchunk . substr($bits, $i, $len);
        $chunk_size = 1;
        $i = $start_pos + $newlen;
        $start_pos = $i;
        $len = strlen($bits);
        $crcval = $crcval + $chunklen;
      }
    }
  }
  if($crc != $crcval)
  {
    echo __FUNCTION__.'(): ERROR: length check failed, this is a bug in the algorithm<br />Debug info: aiming for a CRC val of '.$crc.', got '.$crcval;
    return false;
  }
  $compressed = 'cbf:len='.$crc.';crc='.dechex($crc32).';data='.$bits.'|end';
  return $compressed;
}

/**
 * Uncompresses a bitfield compressed with compress_bitfield()
 * @param string $bits the compressed bitfield
 * @return string the uncompressed, original (we hope) bitfield OR bool false on error
 */
 
function uncompress_bitfield($bits)
{
  if(substr($bits, 0, 4) != 'cbf:')
  {
    echo __FUNCTION__.'(): ERROR: Invalid stream';
    return false;
  }
  $len = intval(substr($bits, strpos($bits, 'len=')+4, strpos($bits, ';')-strpos($bits, 'len=')-4));
  $crc = substr($bits, strpos($bits, 'crc=')+4, 8);
  $data = substr($bits, strpos($bits, 'data=')+5, strpos($bits, '|end')-strpos($bits, 'data=')-5);
  $data = explode(',', $data);
  foreach($data as $a => $b)
  {
    $d =& $data[$a];
    $char = substr($d, 0, 1);
    $dlen = intval(substr($d, 2, strlen($d)-1));
    $s = '';
    for($i=0;$i<$dlen;$i++,$s.=$char);
    $d = $s;
    unset($s, $dlen, $char);
  }
  $decompressed = implode('', $data);
  $decompressed = substr($decompressed, 0, -1);
  $dcrc = (string)dechex(crc32($decompressed));
  if($dcrc != $crc)
  {
    echo __FUNCTION__.'(): ERROR: CRC check failed<br />debug info:<br />original crc: '.$crc.'<br />decomp\'ed crc: '.$dcrc.'<br />';
    return false;
  }
  return $decompressed;
}

/**
 * Exports a MySQL table into a SQL string.
 * @param string $table The name of the table to export
 * @param bool $structure If true, include a CREATE TABLE command
 * @param bool $data If true, include the contents of the table
 * @param bool $compact If true, omits newlines between parts of SQL statements, use in Enano database exporter
 * @return string
 */

function export_table($table, $structure = true, $data = true, $compact = false)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  $struct_keys = '';
  $divider   = (!$compact) ? "\n" : "\n";
  $spacer1   = (!$compact) ? "\n" : " ";
  $spacer2   = (!$compact) ? "  " : " ";
  $rowspacer = (!$compact) ? "\n  " : " ";
  $index_list = Array();
  $cols = $db->sql_query('SHOW COLUMNS IN '.$table.';');
  if(!$cols)
  {
    echo 'export_table(): Error getting column list: '.$db->get_error_text().'<br />';
    return false;
  }
  $col = Array();
  $sqlcol = Array();
  $collist = Array();
  $pri_keys = Array();
  // Using fetchrow_num() here to compensate for MySQL l10n
  while( $row = $db->fetchrow_num() )
  {
    $field =& $row[0];
    $type  =& $row[1];
    $null  =& $row[2];
    $key   =& $row[3];
    $def   =& $row[4];
    $extra =& $row[5];
    $col[] = Array(
      'name'=>$field,
      'type'=>$type,
      'null'=>$null,
      'key'=>$key,
      'default'=>$def,
      'extra'=>$extra,
      );
    $collist[] = $field;
  }
  
  if ( $structure )
  {
    $db->sql_query('SET SQL_QUOTE_SHOW_CREATE = 0;');
    $struct = $db->sql_query('SHOW CREATE TABLE '.$table.';');
    if ( !$struct )
      $db->_die();
    $row = $db->fetchrow_num();
    $db->free_result();
    $struct = $row[1];
    $struct = preg_replace("/\n\) ENGINE=(.+)$/", "\n);", $struct);
    unset($row);
    if ( $compact )
    {
      $struct_arr = explode("\n", $struct);
      foreach ( $struct_arr as $i => $leg )
      {
        if ( $i == 0 )
          continue;
        $test = trim($leg);
        if ( empty($test) )
        {
          unset($struct_arr[$i]);
          continue;
        }
        $struct_arr[$i] = preg_replace('/^([\s]*)/', ' ', $leg);
      }
      $struct = implode("", $struct_arr);
    }
  }
  
  // Structuring complete
  if($data)
  {
    $datq = $db->sql_query('SELECT * FROM '.$table.';');
    if(!$datq)
    {
      echo 'export_table(): Error getting column list: '.$db->get_error_text().'<br />';
      return false;
    }
    if($db->numrows() < 1)
    {
      if($structure) return $struct;
      else return '';
    }
    $rowdata = Array();
    $dataqs = Array();
    $insert_strings = Array();
    $z = false;
    while($row = $db->fetchrow_num())
    {
      $z = false;
      foreach($row as $i => $cell)
      {
        $str = mysql_encode_column($cell, $col[$i]['type']);
        $rowdata[] = $str;
      }
      $dataqs2 = implode(",$rowspacer", $dataqs) . ",$rowspacer" . '( ' . implode(', ', $rowdata) . ' )';
      $ins = 'INSERT INTO '.$table.'( '.implode(',', $collist).' ) VALUES' . $dataqs2 . ";";
      if ( strlen( $ins ) > MYSQL_MAX_PACKET_SIZE )
      {
        // We've exceeded the maximum allowed packet size for MySQL - separate this into a different query
        $insert_strings[] = 'INSERT INTO '.$table.'( '.implode(',', $collist).' ) VALUES' . implode(",$rowspacer", $dataqs) . ";";;
        $dataqs = Array('( ' . implode(', ', $rowdata) . ' )');
        $z = true;
      }
      else
      {
        $dataqs[] = '( ' . implode(', ', $rowdata) . ' )';
      }
      $rowdata = Array();
    }
    if ( !$z )
    {
      $insert_strings[] = 'INSERT INTO '.$table.'( '.implode(',', $collist).' ) VALUES' . implode(",$rowspacer", $dataqs) . ";";;
      $dataqs = Array();
    }
    $datstring = implode($divider, $insert_strings);
  }
  if($structure && !$data) return $struct;
  elseif(!$structure && $data) return $datstring;
  elseif($structure && $data) return $struct . $divider . $datstring;
  elseif(!$structure && !$data) return '';
}

/**
 * Encodes a string value for use in an INSERT statement for given column type $type.
 * @access private
 */
 
function mysql_encode_column($input, $type)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  // Decide whether to quote the string or not
  if(substr($type, 0, 7) == 'varchar' || $type == 'datetime' || $type == 'text' || $type == 'tinytext' || $type == 'smalltext' || $type == 'longtext' || substr($type, 0, 4) == 'char')
  {
    $str = "'" . $db->escape($input) . "'";
  }
  elseif(in_array($type, Array('blob', 'longblob', 'mediumblob', 'smallblob')) || substr($type, 0, 6) == 'binary' || substr($type, 0, 9) == 'varbinary')
  {
    $str = '0x' . hexencode($input, '', '');
  }
  elseif(is_null($input))
  {
    $str = 'NULL';
  }
  else
  {
    $str = (string)$input;
  }
  return $str;
}

/**
 * Creates an associative array defining which file extensions are allowed and which ones aren't
 * @return array keyname will be a file extension, value will be true or false
 */

function fetch_allowed_extensions()
{
  global $mime_types;
  $bits = getConfig('allowed_mime_types');
  if(!$bits) return Array(false);
  $bits = uncompress_bitfield($bits);
  if(!$bits) return Array(false);
  $bits = enano_str_split($bits, 1);
  $ret = Array();
  $mt = array_keys($mime_types);
  foreach($bits as $i => $b)
  {
    $ret[$mt[$i]] = ( $b == '1' ) ? true : false;
  }
  return $ret;
}

/**
 * Generates a random key suitable for encryption
 * @param int $len the length of the key
 * @return string a BINARY key
 */

function randkey($len = 32)
{
  $key = '';
  for($i=0;$i<$len;$i++)
  {
    $key .= chr(mt_rand(0, 255));
  }
  return $key;
}

/**
 * Decodes a hex string.
 * @param string $hex The hex code to decode
 * @return string
 */

function hexdecode($hex)
{
  $hex = enano_str_split($hex, 2);
  $bin_key = '';
  foreach($hex as $nibble)
  {
    $byte = chr(hexdec($nibble));
    $bin_key .= $byte;
  }
  return $bin_key;
}

/**
 * Enano's own (almost) bulletproof HTML sanitizer.
 * @param string $html The input HTML
 * @return string cleaned HTML
 */

function sanitize_html($html, $filter_php = true)
{
  
  $html = preg_replace('#<([a-z]+)([\s]+)([^>]+?)'.htmlalternatives('javascript:').'(.+?)>(.*?)</\\1>#is', '&lt;\\1\\2\\3javascript:\\59&gt;\\60&lt;/\\1&gt;', $html);
  $html = preg_replace('#<([a-z]+)([\s]+)([^>]+?)'.htmlalternatives('javascript:').'(.+?)>#is', '&lt;\\1\\2\\3javascript:\\59&gt;', $html);
  
  if($filter_php)
    $html = str_replace(
      Array('<?php',    '<?',    '<%',    '?>',    '%>'),
      Array('&lt;?php', '&lt;?', '&lt;%', '?&gt;', '%&gt;'),
      $html);
  
  $tag_whitelist = array_keys ( setupAttributeWhitelist() );
  if ( !$filter_php )
    $tag_whitelist[] = '?php';
  $len = strlen($html);
  $in_quote = false;
  $quote_char = '';
  $tag_start = 0;
  $tag_name = '';
  $in_tag = false;
  $trk_name = false;
  for ( $i = 0; $i < $len; $i++ )
  {
    $chr = $html{$i};
    $prev = ( $i == 0 ) ? '' : $html{ $i - 1 };
    $next = ( ( $i + 1 ) == $len ) ? '' : $html { $i + 1 };
    if ( $in_quote && $in_tag )
    {
      if ( $quote_char == $chr && $prev != '\\' )
        $in_quote = false;
    }
    elseif ( ( $chr == '"' || $chr == "'" ) && $prev != '\\' && $in_tag )
    {
      $in_quote = true;
      $quote_char = $chr;
    }
    if ( $chr == '<' && !$in_tag && $next != '/' )
    {                                          
      // start of a tag
      $tag_start = $i;
      $in_tag = true;
      $trk_name = true;
    }
    elseif ( !$in_quote && $in_tag && $chr == '>' )
    {
      $full_tag = substr($html, $tag_start, ( $i - $tag_start ) + 1 );
      $l = strlen($tag_name) + 2;
      $attribs_only = trim( substr($full_tag, $l, ( strlen($full_tag) - $l - 1 ) ) );
      
      // Debugging message
      // echo htmlspecialchars($full_tag) . '<br />';
      
      if ( !in_array($tag_name, $tag_whitelist) )
      {
        // Illegal tag
        //echo $tag_name . ' ';
        
        $s = ( empty($attribs_only) ) ? '' : ' ';
        
        $sanitized = '&lt;' . $tag_name . $s . $attribs_only . '&gt;';
        
        $html = substr($html, 0, $tag_start) . $sanitized . substr($html, $i + 1);
        $html = str_replace('</' . $tag_name . '>', '&lt;/' . $tag_name . '&gt;', $html);
        $new_i = $tag_start + strlen($sanitized);
        
        $len = strlen($html);
        $i = $new_i;
        
        $in_tag = false;
        $tag_name = '';
        continue;
      }
      else
      {
        if ( $tag_name == '?php' && !$filter_php )
          continue;
        $f = fixTagAttributes( $attribs_only, $tag_name );
        $s = ( empty($f) ) ? '' : ' ';
        
        $sanitized = '<' . $tag_name . $f . '>';
        $new_i = $tag_start + strlen($sanitized);
        
        $html = substr($html, 0, $tag_start) . $sanitized . substr($html, $i + 1);
        $len = strlen($html);
        $i = $new_i;
        
        $in_tag = false;
        $tag_name = '';
        continue;
      }
    }
    elseif ( $in_tag && $trk_name )
    {
      $is_alphabetical = ( strtolower($chr) != strtoupper($chr) || in_array($chr, array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9')) || $chr == '?' || $chr == '!' || $chr == '-' );
      if ( $is_alphabetical )
        $tag_name .= $chr;
      else
      {
        $trk_name = false;
      }
    }
    
  }
  
  // Vulnerability from ha.ckers.org/xss.html:
  // <script src="http://foo.com/xss.js"
  // <
  // The rule is so specific because everything else will have been filtered by now
  $html = preg_replace('/<(script|iframe)(.+?)src=([^>]*)</i', '&lt;\\1\\2src=\\3&lt;', $html);
  
  // Unstrip comments
  $html = preg_replace('/&lt;!--([^>]*?)--&gt;/i', '', $html);
  
  return $html;
  
}

/**
 * Using the same parsing code as sanitize_html(), this function adds <litewiki> tags around certain block-level elements
 * @param string $html The input HTML
 * @return string formatted HTML
 */

function wikiformat_process_block($html)
{
  
  $tok1 = "<litewiki>";
  $tok2 = "</litewiki>";
  
  $block_tags = array('div', 'p', 'table', 'blockquote', 'pre');
  
  $len = strlen($html);
  $in_quote = false;
  $quote_char = '';
  $tag_start = 0;
  $tag_name = '';
  $in_tag = false;
  $trk_name = false;
  
  $diag = 0;
  
  $block_tagname = '';
  $in_blocksec = 0;
  $block_start = 0;
  
  for ( $i = 0; $i < $len; $i++ )
  {
    $chr = $html{$i};
    $prev = ( $i == 0 ) ? '' : $html{ $i - 1 };
    $next = ( ( $i + 1 ) == $len ) ? '' : $html { $i + 1 };
    
    // Are we inside of a quoted section?
    if ( $in_quote && $in_tag )
    {
      if ( $quote_char == $chr && $prev != '\\' )
        $in_quote = false;
    }
    elseif ( ( $chr == '"' || $chr == "'" ) && $prev != '\\' && $in_tag )
    {
      $in_quote = true;
      $quote_char = $chr;
    }
    
    if ( $chr == '<' && !$in_tag && $next == '/' )
    {
      // Iterate through until we've got a tag name
      $tag_name = '';
      $i++;
      while(true)
      {
        $i++;
        // echo $i . ' ';
        $chr = $html{$i};
        $prev = ( $i == 0 ) ? '' : $html{ $i - 1 };
        $next = ( ( $i + 1 ) == $len ) ? '' : $html { $i + 1 };
        $tag_name .= $chr;
        if ( $next == '>' )
          break;
      }
      // echo '<br />';
      if ( in_array($tag_name, $block_tags) )
      {
        if ( $block_tagname == $tag_name )
        {
          $in_blocksec -= 1;
          if ( $in_blocksec == 0 )
          {
            $block_tagname = '';
            $i += 2;
            // echo 'Finished wiki litewiki wraparound calc at pos: ' . $i;
            $full_litewiki = substr($html, $block_start, ( $i - $block_start ));
            $new_text = "{$tok1}{$full_litewiki}{$tok2}";
            $html = substr($html, 0, $block_start) . $new_text . substr($html, $i);
            
            $i += ( strlen($tok1) + strlen($tok2) ) - 1;
            $len = strlen($html);
            
            //die('<pre>' . htmlspecialchars($html) . '</pre>');
          }
        }
      }
      
      $in_tag = false;
      $in_quote = false;
      $tag_name = '';
      
      continue;
    }
    else if ( $chr == '<' && !$in_tag && $next != '/' )
    {
      // start of a tag
      $tag_start = $i;
      $in_tag = true;
      $trk_name = true;
    }
    else if ( !$in_quote && $in_tag && $chr == '>' )
    {
      if ( !in_array($tag_name, $block_tags) )
      {
        // Inline tag - reset and go to the next one
        // echo '&lt;inline ' . $tag_name . '&gt; ';
        
        $in_tag = false;
        $tag_name = '';
        continue;
      }
      else
      {
        // echo '&lt;block: ' . $tag_name . ' @ ' . $i . '&gt;<br/>';
        if ( $in_blocksec == 0 )
        {
          //die('Found a starting tag for a block element: ' . $tag_name . ' at pos ' . $tag_start);
          $block_tagname = $tag_name;
          $block_start = $tag_start;
          $in_blocksec++;
        }
        else if ( $block_tagname == $tag_name )
        {
          $in_blocksec++;
        }
        
        $in_tag = false;
        $tag_name = '';
        continue;
      }
    }
    elseif ( $in_tag && $trk_name )
    {
      $is_alphabetical = ( strtolower($chr) != strtoupper($chr) || in_array($chr, array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9')) || $chr == '?' || $chr == '!' || $chr == '-' );
      if ( $is_alphabetical )
        $tag_name .= $chr;
      else
      {
        $trk_name = false;
      }
    }
    
    // Tokenization complete
    
  }
  
  $regex = '/' . str_replace('/', '\\/', preg_quote($tok2)) . '([\s]*)' . preg_quote($tok1) . '/is';
  // die(htmlspecialchars($regex));
  $html = preg_replace($regex, '\\1', $html);
  
  return $html;
  
}

function htmlalternatives($string)
{
  $ret = '';
  for ( $i = 0; $i < strlen($string); $i++ )
  {
    $chr = $string{$i};
    $ch1 = ord($chr);
    $ch2 = dechex($ch1);
    $byte = '(&\\#([0]*){0,7}' . $ch1 . ';|\\\\([0]*){0,7}' . $ch1 . ';|\\\\([0]*){0,7}' . $ch2 . ';|&\\#x([0]*){0,7}' . $ch2 . ';|%([0]*){0,7}' . $ch2 . '|' . preg_quote($chr) . ')';
    $ret .= $byte;
    $ret .= '([\s]){0,2}';
  }
  return $ret;
}

/**
 * Paginates (breaks into multiple pages) a MySQL result resource, which is treated as unbuffered.
 * @param resource The MySQL result resource. This should preferably be an unbuffered query.
 * @param string A template, with variables being named after the column name
 * @param int The number of total results. This should be determined by a second query.
 * @param string sprintf-style formatting string for URLs for result pages. First parameter will be start offset.
 * @param int Optional. Start offset in individual results. Defaults to 0.
 * @param int Optional. The number of results per page. Defualts to 10.
 * @param int Optional. An associative array of functions to call, with key names being column names, and values being function names. Values can also be an array with key 0 being either an object or a string(class name) and key 1 being a [static] method.
 * @param string Optional. The text to be sent before the result list, only if there are any results. Possibly the start of a table.
 * @param string Optional. The text to be sent after the result list, only if there are any results. Possibly the end of a table.
 * @return string
 */

function paginate($q, $tpl_text, $num_results, $result_url, $start = 0, $perpage = 10, $callers = Array(), $header = '', $footer = '')
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  $parser = $template->makeParserText($tpl_text);
  $num_pages = ceil ( $num_results / $perpage );
  $out = '';
  $i = 0;
  $this_page = ceil ( $start / $perpage );
  
  // Build paginator
  $begin = '<div class="tblholder" style="display: table; margin: 10px 0 0 auto;">
    <table border="0" cellspacing="1" cellpadding="4">
      <tr><th>Page:</th>';
  $block = '<td class="row1" style="text-align: center;">{LINK}</td>';
  $end = '</tr></table></div>';
  $blk = $template->makeParserText($block);
  $inner = '';
  $cls = 'row2';
  if ( $num_pages < 5 )
  {
    for ( $i = 0; $i < $num_pages; $i++ )
    {
      $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
      $offset = strval($i * $perpage);
      $url = sprintf($result_url, $offset);
      $j = $i + 1;
      $link = ( $offset == strval($start) ) ? "<b>$j</b>" : "<a href=".'"'."$url".'"'." style='text-decoration: none;'>$j</a>";
      $blk->assign_vars(array(
        'CLASS'=>$cls,
        'LINK'=>$link
        ));
      $inner .= $blk->run();
    }
  }
  else
  {
    if ( $this_page + 5 > $num_pages )
    {
      $list = Array();
      $tp = $this_page;
      if ( $this_page + 0 == $num_pages ) $tp = $tp - 3;
      if ( $this_page + 1 == $num_pages ) $tp = $tp - 2;
      if ( $this_page + 2 == $num_pages ) $tp = $tp - 1;
      for ( $i = $tp - 1; $i <= $tp + 1; $i++ )
      {
        $list[] = $i;
      }
    }
    else
    {
      $list = Array();
      $current = $this_page;
      $lower = ( $current < 3 ) ? 1 : $current - 1;
      for ( $i = 0; $i < 3; $i++ )
      {
        $list[] = $lower + $i;
      }
    }
    $url = sprintf($result_url, '0');
    $link = ( 0 == $start ) ? "<b>First</b>" : "<a href=".'"'."$url".'"'." style='text-decoration: none;'>&laquo; First</a>";
    $blk->assign_vars(array(
      'CLASS'=>$cls,
      'LINK'=>$link
      ));
    $inner .= $blk->run();
    
    // if ( !in_array(1, $list) )
    // {
    //   $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
    //   $blk->assign_vars(array('CLASS'=>$cls,'LINK'=>'...'));
    //   $inner .= $blk->run();
    // }
    
    foreach ( $list as $i )
    {
      if ( $i == $num_pages )
        break;
      $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
      $offset = strval($i * $perpage);
      $url = sprintf($result_url, $offset);
      $j = $i + 1;
      $link = ( $offset == strval($start) ) ? "<b>$j</b>" : "<a href=".'"'."$url".'"'." style='text-decoration: none;'>$j</a>";
      $blk->assign_vars(array(
        'CLASS'=>$cls,
        'LINK'=>$link
        ));
      $inner .= $blk->run();
    }
    
    $total = $num_pages * $perpage - $perpage;
    
    if ( $this_page < $num_pages )
    {
      // $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
      // $blk->assign_vars(array('CLASS'=>$cls,'LINK'=>'...'));
      // $inner .= $blk->run();
    
      $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
      $offset = strval($total);
      $url = sprintf($result_url, $offset);
      $j = $i + 1;
      $link = ( $offset == strval($start) ) ? "<b>Last</b>" : "<a href=".'"'."$url".'"'." style='text-decoration: none;'>Last &raquo;</a>";
      $blk->assign_vars(array(
        'CLASS'=>$cls,
        'LINK'=>$link
        ));
      $inner .= $blk->run();
    }
    
  }
  
  $inner .= '<td class="row2" style="cursor: pointer;" onclick="paginator_goto(this, '.$this_page.', '.$num_pages.', '.$perpage.', unescape(\'' . rawurlencode($result_url) . '\'));">&darr;</td>';
  
  $paginator = "\n$begin$inner$end\n";
  $out .= $paginator;
  
  $cls = 'row2';
  
  if ( $row = $db->fetchrow($q) )
  {
    $i = 0;
    $out .= $header;
    do {
      $i++;
      if ( $i <= $start )
      {
        continue;
      }
      if ( ( $i - $start ) > $perpage )
      {
        break;
      }
      $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
      foreach ( $row as $j => $val )
      {
        if ( isset($callers[$j]) )
        {
          $tmp = ( is_callable($callers[$j]) ) ? @call_user_func($callers[$j], $val, $row) : $v;
          
          if ( $tmp )
          {
            $row[$j] = $tmp;
          }
        }
      }
      $parser->assign_vars($row);
      $parser->assign_vars(array('_css_class' => $cls));
      $out .= $parser->run();
    } while ( $row = $db->fetchrow($q) );
    $out .= $footer;
  }
  
  $out .= $paginator;
  
  return $out;
}

/**
 * This is the same as paginate(), but it processes an array instead of a MySQL result resource.
 * @param array The results. Each value is simply echoed.
 * @param int The number of total results. This should be determined by a second query.
 * @param string sprintf-style formatting string for URLs for result pages. First parameter will be start offset.
 * @param int Optional. Start offset in individual results. Defaults to 0.
 * @param int Optional. The number of results per page. Defualts to 10.
 * @param string Optional. The text to be sent before the result list, only if there are any results. Possibly the start of a table.
 * @param string Optional. The text to be sent after the result list, only if there are any results. Possibly the end of a table.
 * @return string
 */

function paginate_array($q, $num_results, $result_url, $start = 0, $perpage = 10, $header = '', $footer = '')
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  $parser = $template->makeParserText($tpl_text);
  $num_pages = ceil ( $num_results / $perpage );
  $out = '';
  $i = 0;
  $this_page = ceil ( $start / $perpage );
  
  // Build paginator
  $begin = '<div class="tblholder" style="display: table; margin: 10px 0 0 auto;">
    <table border="0" cellspacing="1" cellpadding="4">
      <tr><th>Page:</th>';
  $block = '<td class="row1" style="text-align: center;">{LINK}</td>';
  $end = '</tr></table></div>';
  $blk = $template->makeParserText($block);
  $inner = '';
  $cls = 'row2';
  if ( $start > 0 )
  {
    $url = sprintf($result_url, abs($start - $perpage));
    $link = "<a href=".'"'."$url".'"'." style='text-decoration: none;'>&laquo; Prev</a>";
    $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
    $blk->assign_vars(array(
      'CLASS'=>$cls,
      'LINK'=>$link
      ));
    $inner .= $blk->run();
  }
  if ( $num_pages < 5 )
  {
    for ( $i = 0; $i < $num_pages; $i++ )
    {
      $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
      $offset = strval($i * $perpage);
      $url = sprintf($result_url, $offset);
      $j = $i + 1;
      $link = ( $offset == strval($start) ) ? "<b>$j</b>" : "<a href=".'"'."$url".'"'." style='text-decoration: none;'>$j</a>";
      $blk->assign_vars(array(
        'CLASS'=>$cls,
        'LINK'=>$link
        ));
      $inner .= $blk->run();
    }
  }
  else
  {
    if ( $this_page + 5 > $num_pages )
    {
      $list = Array();
      $tp = $this_page;
      if ( $this_page + 0 == $num_pages ) $tp = $tp - 3;
      if ( $this_page + 1 == $num_pages ) $tp = $tp - 2;
      if ( $this_page + 2 == $num_pages ) $tp = $tp - 1;
      for ( $i = $tp - 1; $i <= $tp + 1; $i++ )
      {
        $list[] = $i;
      }
    }
    else
    {
      $list = Array();
      $current = $this_page;
      $lower = ( $current < 3 ) ? 1 : $current - 1;
      for ( $i = 0; $i < 3; $i++ )
      {
        $list[] = $lower + $i;
      }
    }
    $url = sprintf($result_url, '0');
    $link = ( 0 == $start ) ? "<b>First</b>" : "<a href=".'"'."$url".'"'." style='text-decoration: none;'>&laquo; First</a>";
    $blk->assign_vars(array(
      'CLASS'=>$cls,
      'LINK'=>$link
      ));
    $inner .= $blk->run();
    
    // if ( !in_array(1, $list) )
    // {
    //   $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
    //   $blk->assign_vars(array('CLASS'=>$cls,'LINK'=>'...'));
    //   $inner .= $blk->run();
    // }
    
    foreach ( $list as $i )
    {
      if ( $i == $num_pages )
        break;
      $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
      $offset = strval($i * $perpage);
      $url = sprintf($result_url, $offset);
      $j = $i + 1;
      $link = ( $offset == strval($start) ) ? "<b>$j</b>" : "<a href=".'"'."$url".'"'." style='text-decoration: none;'>$j</a>";
      $blk->assign_vars(array(
        'CLASS'=>$cls,
        'LINK'=>$link
        ));
      $inner .= $blk->run();
    }
    
    $total = $num_pages * $perpage - $perpage;
    
    if ( $this_page < $num_pages )
    {
      // $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
      // $blk->assign_vars(array('CLASS'=>$cls,'LINK'=>'...'));
      // $inner .= $blk->run();
    
      $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
      $offset = strval($total);
      $url = sprintf($result_url, $offset);
      $j = $i + 1;
      $link = ( $offset == strval($start) ) ? "<b>Last</b>" : "<a href=".'"'."$url".'"'." style='text-decoration: none;'>Last &raquo;</a>";
      $blk->assign_vars(array(
        'CLASS'=>$cls,
        'LINK'=>$link
        ));
      $inner .= $blk->run();
    }
    
  }
  
  if ( $start < $total )
  {
    $url = sprintf($result_url, abs($start + $perpage));
    $link = "<a href=".'"'."$url".'"'." style='text-decoration: none;'>Next &raquo;</a>";
    $cls = ( $cls == 'row1' ) ? 'row2' : 'row1';
    $blk->assign_vars(array(
      'CLASS'=>$cls,
      'LINK'=>$link
      ));
    $inner .= $blk->run();
  }
  
  $inner .= '<td class="row2" style="cursor: pointer;" onclick="paginator_goto(this, '.$this_page.', '.$num_pages.', '.$perpage.', unescape(\'' . rawurlencode($result_url) . '\'));">&darr;</td>';
  
  $paginator = "\n$begin$inner$end\n";
  if ( $total > 1 )
    $out .= $paginator;
  
  $cls = 'row2';
  
  if ( sizeof($q) > 0 )
  {
    $i = 0;
    $out .= $header;
    foreach ( $q as $val ) {
      $i++;
      if ( $i <= $start )
      {
        continue;
      }
      if ( ( $i - $start ) > $perpage )
      {
        break;
      }
      $out .= $val;
    }
    $out .= $footer;
  }
  
  if ( $total > 1 )
    $out .= $paginator;
  
  return $out;
}

/** 
 * Enano version of fputs for debugging
 */

function enano_fputs($socket, $data)
{
  // echo '<pre>' . htmlspecialchars($data) . '</pre>';
  // flush();
  // ob_flush();
  // ob_end_flush();
  return fputs($socket, $data);
}

/**
 * Sanitizes a page URL string so that it can safely be stored in the database.
 * @param string Page ID to sanitize
 * @return string Cleaned text
 */

function sanitize_page_id($page_id)
{
  
  // Remove character escapes
  $page_id = dirtify_page_id($page_id);
  
  $pid_clean = preg_replace('/[\w\.\/:;\(\)@\[\]_-]/', 'X', $page_id);
  $pid_dirty = enano_str_split($pid_clean, 1);
  
  foreach ( $pid_dirty as $id => $char )
  {
    if ( $char == 'X' )
      continue;
    $cid = ord($char);
    $cid = dechex($cid);
    $cid = strval($cid);
    if ( strlen($cid) < 2 )
    {
      $cid = strtoupper("0$cid");
    }
    $pid_dirty[$id] = ".$cid";
  }
  
  $pid_chars = enano_str_split($page_id, 1);
  $page_id_cleaned = '';
  
  foreach ( $pid_chars as $id => $char )
  {
    if ( $pid_dirty[$id] == 'X' )
      $page_id_cleaned .= $char;
    else
      $page_id_cleaned .= $pid_dirty[$id];
  }
  
  // global $mime_types;
          
  // $exts = array_keys($mime_types);
  // $exts = '(' . implode('|', $exts) . ')';
  
  // $page_id_cleaned = preg_replace('/\.2e' . $exts . '$/', '.\\1', $page_id_cleaned);
  
  return $page_id_cleaned;
}

/**
 * Removes character escapes in a page ID string
 * @param string Page ID string to dirty up
 * @return string
 */

function dirtify_page_id($page_id)
{
  global $db, $session, $paths, $template, $plugins; // Common objects
  // First, replace spaces with underscores  
  $page_id = str_replace(' ', '_', $page_id);
  
  // Exception for userpages for IP addresses
  if ( preg_match('/^' . preg_quote($paths->nslist['User']) . '/', $page_id) )
  {
    $ip = preg_replace('/^' . preg_quote($paths->nslist['User']) . '/', '', $page_id);
    if ( is_valid_ip($ip) )
      return $page_id;
  }
  
  preg_match_all('/\.[A-Fa-f0-9][A-Fa-f0-9]/', $page_id, $matches);
  
  foreach ( $matches[0] as $id => $char )
  {
    $char = substr($char, 1);
    $char = strtolower($char);
    $char = intval(hexdec($char));
    $char = chr($char);
    $page_id = str_replace($matches[0][$id], $char, $page_id);
  }
  
  return $page_id;
}

/**
 * Inserts commas into a number to make it more human-readable. Floating point-safe.
 * @param int The number to process
 * @return string Input number with commas added
 */

function commatize($num)
{
  $num = (string)$num;
  if ( strpos($num, '.') )
  {
    $whole = explode('.', $num);
    $num = $whole[0];
    $dec = $whole[1];
  }
  else
  {
    $whole = $num;
  }
  $offset = ( strlen($num) ) % 3;
  $len = strlen($num);
  $offset = ( $offset == 0 )
    ? 3
    : $offset;
  for ( $i = $offset; $i < $len; $i=$i+3 )
  {
    $num = substr($num, 0, $i) . ',' . substr($num, $i, $len);
    $len = strlen($num);
    $i++;
  }
  if ( isset($dec) )
  {
    return $num . '.' . $dec;
  }
  else
  {
    return $num;
  }
}

/**
 * Injects a string into another string at the specified position.
 * @param string The haystack
 * @param string The needle
 * @param int    Position at which to insert the needle
 */

function inject_substr($haystack, $needle, $pos)
{
  $str1 = substr($haystack, 0, $pos);
  $pos++;
  $str2 = substr($haystack, $pos);
  return "{$str1}{$needle}{$str2}";
}

/**
 * Tells if a given IP address is valid.
 * @param string suspected IP address
 * @return bool true if valid, false otherwise
 */
 
function is_valid_ip($ip)
{
  // These came from phpBB3.
  $ipv4 = '(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])';
  $ipv6 = '(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){5}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:))';
  
  if ( preg_match("/^{$ipv4}$/", $ip) || preg_match("/^{$ipv6}$/", $ip) )
    return true;
  else
    return false;
}

//die('<pre>Original:  01010101010100101010100101010101011010'."\nProcessed: ".uncompress_bitfield(compress_bitfield('01010101010100101010100101010101011010')).'</pre>');

?>
