<?php
require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/export/lib.php';
require_once($CFG->dirroot.'/mod/geogebra/archivelib.php');

// returns ONE dump or EOF or deletes the dump that dumps
$id                = required_param('id', PARAM_INT);  // course id
$count                = required_param('count', PARAM_INT);
$PAGE->set_url('/grade/export/extended/ggb_dumper.php', array('id'=>$id));

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

require_login($course);
$context = context_course::instance($id);
$groupid = groups_get_course_group($course, true);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/extended:view', $context);
// see if user want to see just this attempt
$fname = optional_param('fname',0,PARAM_TEXT); // course id
$dir=tempfolderggbs();
$dir = "$CFG->tempdir/".tempfolderggbs();
$applet="EOF";
if ($fname && ($fname === "CLEAN")) {
  if (is_dir($dir) && ($dh = opendir($dir)) ){
            while (($file = readdir($dh)) !== false) {
                $filename=$dir."/".$file;
                if ((!is_dir($filename)) && ($handle = fopen($filename, 'r'))) {
                     fclose($handle);
                    unlink($filename);
                }
            }
            closedir($dh);
  }
  global $CFG;
  $urlto = new moodle_url($CFG->wwwroot .'/grade/export/extended/index.php',
            array('id' => $id) );
  header("location: ".$urlto);
  return;
}
if ($fname && ($handle = fopen($fname, 'r'))) {
  $applet=fread($handle,filesize($fname));
  fclose($handle);
  echo $applet;
 return;}
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (!is_dir($filename)) {$count=$count-1;}
            $filename=$dir."/".$file;
            if ($count==0 &&(!is_dir($filename)) && ($handle = fopen($filename, 'r'))) {
                $applet=fread($handle,filesize($filename));
                fclose($handle);
                //unlink($filename);
                break;
            };
        }
        closedir($dh);
    }
}

echo $applet;