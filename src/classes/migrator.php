<?php 
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . "/mod/recitcahiercanada/classes/PersistCtrl.php");
require_once($CFG->dirroot . "/mod/recitcahiertraces/classes/PersistCtrl.php");

class RecitMigrator {

    public function migrateFormat(){
        global $DB;
        
        $recitopts = $DB->get_records('course_format_options', array('format' => 'treetopics', 'name' => 'ttsectiondisplay'));
        $num = 0;
        if (!empty($recitopts)){
            foreach($recitopts as $data){
                $DB->execute("insert into {format_recit_options} (courseid, sectionid, name, value)
                values(?, ?, 'sectionlevel' ?)
                ON DUPLICATE KEY UPDATE value = value", [$data->courseid, $data->sectionid, $data->value]);
                $num++;
            }
        }

        $result = "<div class=\"alert alert-warning alert-block fade in \">$num données de Treetopics ont été migré vers v2</div>";

        return $result;
    }
    
    public function migrateCC(){
        global $DB, $USER;
        
        $recitcc = $DB->get_records_sql("SELECT t1.id as id, t2.id as mid, t2.course as course, t2.section as section FROM {course_modules} t2
        INNER JOIN {recitcahiercanada} t1 ON t1.id = t2.instance
        WHERE t2.visible = 1 AND t2.module = (SELECT id FROM {modules} WHERE name='recitcahiercanada');");
        $mod = $DB->get_record_sql("SELECT id FROM {modules} WHERE name='recitcahiertraces'");

        $result = "";
        foreach($recitcc as $cc){
            try{
                list ($course, $oldcm) = get_course_and_cm_from_cmId($cc->mid);
                list($oldcm, $oldcontext, $oldmodule, $olddata, $oldcw) = get_moduleinfo_data($oldcm, $course);
                $newcm = duplicate_module($course, $oldcm);
                $cId = $DB->insert_record('recitcahiertraces', ['course' => $cc->course, 'name' => $oldcm->name, 'intro' => '', 'introformat' => 1, 'display' => 0, 'timemodified' => 0]);
                $DB->update_record('course_modules', array('id' => $cc->mid, 'module' => $mod->id, 'instance' => $cId));
                $data = \recitcahiercanada\PersistCtrl::getInstance($DB, $USER)->getCmSuggestedNotes($cc->mid);
    
                \recitcahiertraces\PersistCtrl::getInstance($DB, $USER)->importCahierCanada($newcm->id, $data);
                set_coursemodule_visible($cc->mid, 0);
                $result .= "<div class=\"alert alert-warning alert-block fade in \">Migrated ".$oldcm->name." from course ".$course->shortname. "</div>";
            }
            catch(Exception $ex){
                $result .= "<div class=\"alert alert-danger alert-block fade in \">".$ex->GetMessage()."</div>";
            }
        }

        return $result;
    }
}