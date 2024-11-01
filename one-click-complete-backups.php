<?php
/*
Plugin Name: One Click Complete Backups By Maida Themes
Plugin URI: https://en-gb.wordpress.org/plugins/techopialabs-backups
Description: One Click Complete Backups is a plugin Manually Create Complete Backup With Database easy for developers and novice users,no
waiting screen and one click backup start.
Version: 6.0.1
Author: Adnan Hyder Pervez
Author URI: https://profiles.wordpress.org/adnanhyder/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

*/
/*
One Click Complete Backups By Maida Themes is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.
 
One Click Complete Backups By Maida Themes is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with One Click Complete Backups. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
*/

define('ONECLICKBACKUPSVERSION', '1.0.0');


define('ONECLICKBACKUPSPLUGIN', __FILE__);


/**
 * Class ONECLICKBACKUPS_Zipper
 *
 *
 */
class ONECLICKBACKUPS_Zipper
{
  /**
   * Add files and sub-directories in a folder to zip file.
   *
   * @param string $folder
   *   Path to folder that should be zipped.
   *
   * @param ZipArchive $zipFile
   *   Zipfile where files end up.
   *
   * @param int $exclusiveLength
   *   Number of text to be exclusived from the file path.
   */
  private static function folderToZip($folder, &$zipFile, $exclusiveLength)
  {
    $handle = opendir($folder);

    while (FALSE !== $f = readdir($handle)) {
// Check for local/parent path or zipping file itself and skip.
      if ($f != '.' && $f != '..' && $f != basename(__FILE__)) {
        $filePath = "$folder/$f";
// Remove prefix from file path before add to zip.
        $localPath = substr($filePath, $exclusiveLength);

        if (is_file($filePath)) {
          $zipFile->addFile($filePath, $localPath);
        }
        elseif (is_dir($filePath)) {
// Add sub-directory.
          $zipFile->addEmptyDir($localPath);
          self::folderToZip($filePath, $zipFile, $exclusiveLength);
        }
      }
    }
    closedir($handle);
  }

  /**
   * Zip a folder (including itself).
   *
   * Usage:
   *   Zipper::zipDir('path/to/sourceDir', 'path/to/out.zip');
   *
   * @param string $sourcePath
   *   Relative path of directory to be zipped.
   *
   * @param string $outZipPath
   *   Relative path of the resulting output zip file.
   */
  public static function zipDir($sourcePath, $outZipPath)
  {
    $pathInfo = pathinfo($sourcePath);
    $parentPath = $pathInfo['dirname'];
    $dirName = $pathInfo['basename'];

    $z = new ZipArchive();
    $z->open($outZipPath, ZipArchive::CREATE);
    $z->addEmptyDir($dirName);
    if ($sourcePath == $dirName) {
      self::folderToZip($sourcePath, $z, 0);
    }
    else {
      self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
    }
    $z->close();

    return $outZipPath;
  }
}


function oneclickcompletebackups()
{
  add_menu_page(
    __('one click backups', 'textdomain'),
    'ONE CLICK Backups',
    'manage_options',
    'oneclickcompletebackups',
    'oneclickbackups_includes',
    'dashicons-welcome-widgets-menus',
    6
  );
  add_submenu_page(
    'oneclickcompletebackups',
    'ONE CLICK Restore',
    'ONE CLICK Restore',
    'manage_options',
    'sub-page',
    'oneclickbackups_includes_restore');
}

add_action('admin_menu', 'oneclickcompletebackups');

function oneclickbackups_includes()
{
  include('backupform.php');
}

function oneclickbackups_includes_restore()
{
  include('restoreform.php');
}

add_action('wp_ajax_oneclickbackups_backup_function', 'oneclickbackups_backup_function');

function oneclickbackups_backup_function()
{

  $path = get_home_path();
  if (current_user_can('administrator')) {
    if (isset($_POST['dozip'])) {
      $currenttime = time();


      $options = array(
        'db_host' => DB_HOST,  //mysql host
        'db_uname' => DB_USER,  //user
        'db_password' => DB_PASSWORD, //pass
        'db_to_backup' => DB_NAME, //database name
        'db_backup_path' => $path, //where to backup
        'db_exclude_tables' => array() //tables to exclude
      );
      $filename = oneclickbackups_backup_mysql_database($options);


      $zippath = $path;
      $first = md5(uniqid());
      $second = $first . md5(uniqid());
      $zipfile = $path . 'one-click-complete-backups-' . date("Y-m-d--H-i") . $currenttime . "_" . $second . '.zip';
      $response = ONECLICKBACKUPS_Zipper::zipDir($zippath, $zipfile);
      $url = home_url();
      $zipfiledown = $url . '/one-click-complete-backups-' . date("Y-m-d--H-i") . $currenttime . "_" . $second . '.zip';
      $path2 = $path . $filename;
      unlink($path2);
      print_r($zipfiledown);


      exit();
    }


    if (isset($_POST['dellbackup'])) {
      $old_value = sanitize_text_field($_POST['oldval']);

      $path = $path . $old_value;
      print_r($path);
      unlink($path);
      exit();
    }
    exit();
  }
  else {
    return 0;
  }
}

function oneclickbackups_backup_mysql_database($options)
{
  $mtables = array();
  $contents = "-- Database: `" . $options['db_to_backup'] . "` --\n";

  $mysqli = new mysqli($options['db_host'], $options['db_uname'], $options['db_password'], $options['db_to_backup']);
  if ($mysqli->connect_error) {
    die('Error : (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
  }

  $results = $mysqli->query("SHOW TABLES");

  while ($row = $results->fetch_array()) {
    if (!in_array($row[0], $options['db_exclude_tables'])) {
      $mtables[] = $row[0];
    }
  }

  foreach ($mtables as $table) {
    $contents .= "-- Table `" . $table . "` --\n";

    $results = $mysqli->query("SHOW CREATE TABLE " . $table);
    while ($row = $results->fetch_array()) {
      $contents .= $row[1] . ";\n\n";
    }

    $results = $mysqli->query("SELECT * FROM " . $table);
    $row_count = $results->num_rows;
    $fields = $results->fetch_fields();
    $fields_count = count($fields);

    $insert_head = "INSERT INTO `" . $table . "` (";
    for ($i = 0; $i < $fields_count; $i++) {
      $insert_head .= "`" . $fields[$i]->name . "`";
      if ($i < $fields_count - 1) {
        $insert_head .= ', ';
      }
    }
    $insert_head .= ")";
    $insert_head .= " VALUES\n";

    if ($row_count > 0) {
      $r = 0;
      while ($row = $results->fetch_array()) {
        if (($r % 400) == 0) {
          $contents .= $insert_head;
        }
        $contents .= "(";
        for ($i = 0; $i < $fields_count; $i++) {
          $row_content = str_replace("\n", "\\n", $mysqli->real_escape_string($row[$i]));

          switch ($fields[$i]->type) {
            case 8:
            case 3:
              $contents .= $row_content;
              break;
            default:
              $contents .= "'" . $row_content . "'";
          }
          if ($i < $fields_count - 1) {
            $contents .= ', ';
          }
        }
        if (($r + 1) == $row_count || ($r % 400) == 399) {
          $contents .= ");\n\n";
        }
        else {
          $contents .= "),\n";
        }
        $r++;
      }
    }
  }

  if (!is_dir($options['db_backup_path'])) {
    mkdir($options['db_backup_path'], 0777, true);
  }

  $backup_file_name = $options['db_to_backup'] . "sql-backup-" . date("d-m-Y--h-i-s") . time() . ".sql";

  $fp = fopen($options['db_backup_path'] . '/' . $backup_file_name, 'w+');
  if (($result = fwrite($fp, $contents))) {
    //yes
  }
  fclose($fp);
  return $backup_file_name;
}