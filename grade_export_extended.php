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

require_once($CFG->dirroot.'/mod/geogebra/archivelib.php');
require_once($CFG->dirroot.'/grade/export/lib.php');
require_once($CFG->dirroot.'/lib/pagelib.php'); 
require_once($CFG->dirroot.'/lib/weblib.php'); 
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
function filterDOMsec($s){
    preg_match("/<section id=\"region-main\"[^>]*>(.*?)<\/section>/is", $s, $matches);
    return "<div id='page-content' class='row pb-3'>".
    "<div id='region-main-box' class='col-12'>".
    "<section id='region-main' aria-label='Content'>".
    $matches[1].
    "</section></div></div>";
}

function mustDump($idnum,$gtree,$itemsel){
    $i=0;
    foreach ($gtree->items as $key => $props) {
        if($props->idnumber==$idnum){$i=$key;}
    }
    if($i && $itemsel[$i]) { // skip even members
        return true;
    }
    return false;
}

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
        global $CFG,$pgkey,$PAGE,$OUTPUT,$COURSE,$dumped;
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
        //global $CFG;
        //$switch = grade_get_setting($COURSE->id, 'aggregationposition', $CFG->grade_aggregationposition);
        //$gseq = new grade_seq($COURSE->id, $switch);
        $gtree = new grade_tree($COURSE->id);
        //$gstruct = new grade_structure();
        $PAGE->set_title($archid);
        $PAGE->requires->js(new moodle_url($CFG->wwwroot .'/grade/export/extended/extended_view.js'));
        $insta=get_fast_modinfo($COURSE->id)->get_instances();
        $itemsel = required_param_array('itemids', PARAM_INT); // course id
        $quiz=$insta['quiz'];
        
        $head1=$OUTPUT->header();
        echo $head1;
        $head2= $OUTPUT->heading($archid);
        echo $head2;
        $indexpage=$head1.$head2;
        $prefooter = $OUTPUT->footer();
        //$quiz=$insta['quiz'];
        $indexpage="";
        foreach ($quiz as $key => $props) {
         $itemidgrd = $props->id;
         $idnum =$props->idnumber;
         if(!mustDump($idnum,$gtree,$itemsel)){continue;}
         storePaper();
         setWhitePaper();
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
         restorePaper();
         $thispage= filterDOMsec($s);
         echo $thispage;
         $indexpage=$indexpage.$thispage;        
         //echo filterDOMsec($s);
        }
        $ggb=$insta['geogebra'];
        remove_dir($CFG->tempdir."/".tempfolderggbs());
        $dumped=0;
        
        foreach ($ggb as $key => $props) {
            $itemidgrd = $props->id;
            $idnum =$props->idnumber;
            if(!mustDump($idnum,$gtree,$itemsel)){continue;}
            storePaper();
            setWhitePaper();
            $_GET['id']=$itemidgrd;
            $_POST['id']=$itemidgrd;// to trick Moodle function optional_param
            //$_GET['cid']=$COURSE->id;
            //$_POST['cid']=$COURSE->id;
            $_POST['timedump']=$timedump;
            $_POST['courseshortname']=$shortname;
            $_POST['testname']=$props->name; 
            global $DB;
            $_SERVER['DOCUMENT_ROOT'] =$CFG->dirroot.'/mod/geogebra/';
            chdir($CFG->dirroot.'/mod/geogebra/');
            ob_start();
            include($CFG->dirroot.'/mod/geogebra/archive.php');
            $s = ob_get_contents();
            ob_end_clean();
            restorePaper();
            $thispage= filterDOMsec($s);
            echo $thispage;
            $indexpage=$indexpage.$thispage;
        }
        //$url=new moodle_url($CFG->wwwroot .'/grade/export/extended/ggb_dumper.php',
       //     array('id' => $this->course->id,'count'=> $dumped));
        
//        echo '<script>window.onload = function() {dump_screenshots("'.tempfolderggbs().'");} </script>';
        // now dumps all the screenshots for this
        $dumps='<script>window.onload = function() {debugger;RT_dump_screenshots(';
        for ($i = 1; $i <= $dumped; $i++) {
            $url = new moodle_url($CFG->wwwroot .'/grade/export/extended/ggb_dumper.php',
                array('id' => $this->course->id,'count'=> $i));
                $dumps= $dumps.'"'.$url->out(false).'",';
                 
        }
 //       $urlclean = new moodle_url($CFG->wwwroot .'/grade/export/extended/ggb_dumper.php',
 //           array('fname' => "CLEAN",'id' => $this->course->id,'count'=> $i) );
 //       $fetchcode='fetch(".$urlclean.");';    
        echo $dumps.strval($dumped).');'.
 //       'window.focus();alert("cleaner");debugger;'.
 //       'let clean = confirm("Clean dumps?");'.
 //       'if (clean){'.$fetchcode.'}'.
        '}</script>';
        
            
            //.$url.'",'.strval($dumped).');} </script>';
        
        echo $prefooter; 
        //$indexpage=$indexpage.$prefooter;
        //header('Content-Disposition: attachment; filename="sample.txt"');
        //header('Content-Type: text/plain'); # Don't use application/force-download - it's not a real MIME type, and the Content-Disposition header is sufficient
        //header('Content-Length: ' . strlen($indexpage));
        //header('Connection: close');
        //echo $indexpage;
        
       
    }
}


