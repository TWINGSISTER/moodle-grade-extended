<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once($CFG->dirroot.'/grade/export/lib.php');
require_once($CFG->dirroot.'/lib/pagelib.php'); 
require_once($CFG->dirroot.'/lib/outputcomponents.php');
//require_once($CFG->dirroot.'/grade/export/extended/repeteable_moodle_page.php');
require_once($CFG->libdir.'/filelib.php');
//$MYPAGE= new repeteable_moodle_page();
function pagekey(){
    $reflectionProperty = new \ReflectionProperty(renderer_base::class, 'page');
    $reflectionProperty->setAccessible(true);
    return $reflectionProperty;        
}

global $BASEPAGE,$BASEOUTPUTPG,$WHITEPAGE,$WHITEOUTPUTPG;
function getWhitePaper(){
    global $pgkey,$PAGE,$OUTPUT,$WHITEPAGE,$WHITEOUTPUTPG;
    $WHITEPAGE = clone $PAGE;
    $WHITEOUTPUTPG=clone $pgkey->getValue($OUTPUT);
}

function setWhitePaper(){
    global $pgkey,$PAGE,$OUTPUT,$WHITEPAGE,$WHITEOUTPUTPG;
    $PAGE = clone $WHITEPAGE;
    $pgkey->setValue($OUTPUT, clone $WHITEOUTPUTPG);
}
function storePaper(){
    global $pgkey,$PAGE,$OUTPUT,$BASEPAGE,$BASEOUTPUTPG;
    $BASEPAGE = clone $PAGE;
    $BASEOUTPUTPG=clone $pgkey->getValue($OUTPUT);
}
function restorePaper(){
    global $pgkey,$PAGE,$OUTPUT,$BASEPAGE,$BASEOUTPUTPG;
    $PAGE = clone $BASEPAGE;
    $pgkey->setValue($OUTPUT, clone $BASEOUTPUTPG);
}
function filterDOM($s){
    preg_match("/<body[^>]*>(.*?)<\/body>/is", $s, $matches);
   return $matches[1];
}
function filterDOMsec($s){
    preg_match("/<section id=\"region-main\"[^>]*>(.*?)<\/section>/is", $s, $matches);
    return "<div id='page-content' class='row pb-3'>".
    "<div id='region-main-box' class='col-12'>".
    "<section id='region-main' aria-label='Content'>".
    $matches[1].
    "</section></div></div>";
}
/*function filterDOMReg($s){
    $dom = new DomDocument();
    $dom->loadHTML($s);
    $body = $dom->getElementsByTagName('body');
    //$body = $body->item(0);
    //return  $dom->saveHTML();
    return $matches[1];
}*/

class grade_export_extended extends grade_export {
    //$grade_items; // list of all course grade items
    public $plugin = 'extended';
    public $updatedgradesonly = false; // default to export ALL grades

    
    /**
     * To be implemented by child classes
     * @param boolean $feedback
     * @param boolean $publish Whether to output directly, or send as a file
     * @return string
     */
    public function print_grades($feedback = false) {
        global $CFG,$pgkey,$PAGE,$OUTPUT,$COURSE;
        require_once($CFG->libdir.'/filelib.php');
        require_once($CFG->dirroot.'/grade/lib.php');
        require_once($CFG->dirroot.'/lib/modinfolib.php');
       $export_tracking = $this->track_exports();
       $pgkey=pagekey();getWhitePaper();
        $strgrades = get_string('grades');
        //echo '<form id="geogebra_form" method="POST" action="attempt.php">';
        
        $timedump=date('d-m-Y G:i:s', time());
        // Calculate file name
        $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));
        //here is the filename
        $archid=$shortname." ".$timedump;
        $PAGE->set_title($archid);
        $PAGE->requires->js(new moodle_url($CFG->wwwroot .'/grade/export/extended/extended_view.js'));
        
        //$PAGE->set_heading($archid);
        
        //make_temp_directory($archid);
        //$tempfilename = $CFG->tempdir .'/gradeexport/'. md5(sesskey().microtime().$downloadfilename);
        //$tempfilename = "$CFG->tempdir/$archid/$archid.html";
        //if (!$handle = fopen($tempfilename, 'w+b')) {
        //    print_error('cannotcreatetempdir');
        //}
        
        // $handle a file to go
        /// time stamp to ensure uniqueness of batch export
        //fwrite($handle,  '<results batch="extended_export_'.time().'">'."\n"); 

        //$export_buffer = array();
        // get all the items
        //$alltheitems = $this->grade_items;// all the exercises with grades in this course
      
        //global $CFG, $COURSE,$PAGE,$OUTPUT;
        //$switch = grade_get_setting($COURSE->id, 'aggregationposition', $CFG->grade_aggregationposition);
        //$gseq = new grade_seq($COURSE->id, $switch);
        //$gtree = new grade_tree($COURSE->id);
        //$gstruct = new grade_structure();
        $insta=get_fast_modinfo($COURSE->id)->get_instances();
        $quiz=$insta['quiz'];
        echo $OUTPUT->header();
        echo $OUTPUT->heading($archid);
        $prefooter = $OUTPUT->footer();
        //echo '<form id="geogebra_form" method="POST" action="attempt.php">';
        //$BASEOUTPUT = serialize($OUTPUT);
        //$BASEOUTPUT->page = clone $OUTPUT->page;
        foreach ($quiz as $key => $props) {
          // echo $itemidgrd, 
                  //  $gradeitmdata->courseid,
                 //   $gradeitmdata->id,
                 //   $gradeitmdata->itemmodule,//geogebra or quiz
                 //   $gradeitmdata->itemname;
                    //if($gradeitmdata->itemmodule==="quiz"){ 
                       //$_SERVER['QUERY_STRING'] = 
                        //$reflectionProperty = new \ReflectionProperty(moodle_page::class, '_state');
                        //$reflectionProperty->setAccessible(true);
                        //$reflectionProperty->setValue($PAGE, 0);
                        storePaper();
                        setWhitePaper();
                        //$PAGE = clone $BASEPAGE;
                        //$pgkey->setValue($OUTPUT, clone $BASEOUTPUTPG);
                            //= unserialize($BASEOUTPUT);
                        //$OUTPUT->page = clone $BASEOUTPUT->page;
                        $itemidgrd = $props->id;
                        $_GET['id']=$itemidgrd;
                        $_POST['id']=$itemidgrd;// to trick Moodle function optional_param
                        $_GET['mode']="archive";
                        $_POST['mode']="archive";
                        global $DB;
                        $_SERVER['DOCUMENT_ROOT'] =$CFG->dirroot.'/mod/quiz/';
                        chdir($CFG->dirroot.'/mod/quiz/');
                        ob_start();
                        include($CFG->dirroot.'/mod/quiz/report.php');
                        $s = ob_get_contents();
                        ob_end_clean();
                        //$downloadfilename = clean_filename("$shortname$strgrades.html");
                        restorePaper();
                        //fwrite($handle, 
                        //echo "<p id='$archid$props->name'></p>";
                        //echo "<script>addPageQuiz('$archid$props->name','FOO');</script>";
                        //);
                        echo filterDOMsec($s);
                        // $PAGE->_state = 0;
                        /*$PAGE-> __set=
                        Closure::bind(function ($name, $value) {
                            global $PAGE;
                            if ($name =='state') {$PAGE->_state = $value;return;}
                            if (method_exists($this, 'set_' . $name)) {
                                throw new $PAGE->coding_exception('Invalid attempt to modify page object', "Use \$PAGE->set_$name() instead.");
                            } else {
                                throw new $PAGE->coding_exception('Invalid attempt to modify page object', "Unknown property $name");
                            }
                        }, $PAGE);
                        $PAGE->set_state = Closure::bind(function ($s) {
                            // here the object scope was gone...
                            global $PAGE;
                            $PAGE->_state = $s;
                        }, $PAGE);
                         $PAGE->set_state($PAGE->STATE_BEFORE_HEADER);
                         $PAGE->_state=($PAGE->STATE_BEFORE_HEADER);
                        */
                        //http://localhost/moodle/mod/quiz/report.php?id=13&mode=archive
                        
                        /* $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $CFG->dirroot. "/mod/quiz/report.php?id=$itemidgrd&mode=archive");
                        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                        session_write_close();
                        $result = curl_exec ($ch);
                        curl_close ($ch);
                        session_start();*/
                    //}
                    
                  
                    
        }
        /*
        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->init();
        while ($userdata = $gui->next_user()) {
            // match one user that must have a idnumber set in the profile
            $user = $userdata->user;

            if (empty($user->idnumber)) {
                //id number must exist otherwise we cant match up students when importing
                continue;
            }

            // studentgrades[] index should match with corresponding $index
            foreach ($userdata->grades as $itemid => $grade) {
                $grade_item = $this->grade_items[$itemid];
                $grade->grade_item =& $grade_item;

                // MDL-11669, skip exported grades or bad grades (if setting says so)
                if ($export_tracking) {
                    $status = $geub->track($grade);
                    if ($this->updatedgradesonly && ($status == 'nochange' || $status == 'unknown')) {
                        continue;
                    }
                }

               // fwrite($handle,  "\t<result>\n");

                if ($export_tracking) {
                    fwrite($handle,  "\t\t<state>$status</state>\n");
                }

                // only need id number
                fwrite($handle,  "\t\t<assignment>{$grade_item->idnumber}</assignment>\n");
                // this column should be customizable to use either student id, idnumber, uesrname or email.
                fwrite($handle,  "\t\t<student>{$user->idnumber}</student>\n");
                // Format and display the grade in the selected display type (real, letter, percentage).
                if (is_array($this->displaytype)) {
                    // Grades display type came from the return of export_bulk_export_data() on grade publishing.
                    foreach ($this->displaytype as $gradedisplayconst) {
                        $gradestr = $this->format_grade($grade, $gradedisplayconst);
                        fwrite($handle,  "\t\t<score>$gradestr</score>\n");
                    }
                } else {
                    // Grade display type submitted directly from the grade export form.
                    $gradestr = $this->format_grade($grade, $this->displaytype);
                    fwrite($handle,  "\t\t<score>$gradestr</score>\n");
                }

                if ($this->export_feedback) {
                    $feedbackstr = $this->format_feedback($userdata->feedbacks[$itemid], $grade);
                    fwrite($handle,  "\t\t<feedback>$feedbackstr</feedback>\n");
                }
                //fwrite($handle,  "\t</result>\n");
            }
        }
        //fwrite($handle,  "</results>");
        fclose($handle);
        $gui->close();
        $geub->close();

        if (defined('BEHAT_SITE_RUNNING')) {
            // If behat is running, we cannot test the output if we force a file download.
            include($tempfilename);
        } else {
            @header("Content-type: text/xml; charset=UTF-8");
            send_temp_file($tempfilename, $downloadfilename, false);
        }*/
        //$PAGE = clone $BASEPAGE;
        //$pgkey->setValue($OUTPUT, clone $BASEOUTPUTPG);
        //$PAGE->requires->js('/grade/export/extended/extended_view.js');
        //fclose($handle);
        //include($tempfilename);
        //$output = $PAGE->get_renderer('tool_demo');
        
        
        //$renderable = new \tool_demo\output\index_page('Some text');
        //echo $output->render($renderable);
        
        echo $prefooter; 
    }
}


