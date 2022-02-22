<?php 
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . "/mod/recitcahiercanada/classes/PersistCtrl.php");
require_once($CFG->dirroot . "/mod/recitcahiertraces/classes/PersistCtrl.php");

class RecitMigrator {

    public function migrateFormat(){
        ini_set('max_execution_time', 60*60);
        global $DB;
        $result = "";
        
        try {
            $recitopts = $DB->get_records('course_format_options', array('format' => 'treetopics', 'name' => 'ttsectiondisplay'));
            $num = 0;
            if (!empty($recitopts)){
                foreach($recitopts as $data){
                    $DB->execute("insert into {format_recit_options} (courseid, sectionid, name, value)
                    values(?, ?, 'sectionlevel', ?)
                    ON DUPLICATE KEY UPDATE value = value", [$data->courseid, $data->sectionid, $data->value]);
                    $num++;
                }
            }
        
            $recitopts = $DB->get_records('course_format_options', array('format' => 'treetopics'));
            foreach($recitopts as $data){
                if ($data->name == 'tttabsmodel'){
                    $mapping = array(
                        1 => 1,
                        2 => 3,
                        3 => 2,
                        5 => 4,
                    );
                    $model = 5;
                    if (isset($mapping[$data->value])){
                        $model = $mapping[$data->value];
                    }
                    $this->setCustomFieldData($data->courseid, 'menumodel', $model);
                }elseif ($data->name == 'ttshownavsection'){
                    $this->setCustomFieldData($data->courseid, 'show_section_bottom_nav', $data->value);
                }elseif ($data->name == 'ttcustompath'){
                    $this->setCustomFieldData($data->courseid, 'hide_restricted_section', $data->value);
                }/*elseif ($data->name == 'tthascontract'){
                    unset($data->id);
                    $data->format = 'recit';
                    $DB->insert_record('course_format_options', $data);
                }*/
                $num++;
            }

            $num2 = 0;
            $courses = $DB->get_records('course', array('format'=>'treetopics'));
            foreach ($courses as $course){
                $c = $course->id;
                $num2++;
                $data = new stdClass();
                $data->id = $c;
                $data->format = 'recit';
                $DB->update_record('course', $data);
                // make sure the modinfo cache is reset
                rebuild_course_cache($c);
                $course = $DB->get_record('course', array('id'=>$c));
                // Trigger a course updated event.
                $event = \core\event\course_updated::create(array(
                    'objectid' => $course->id,
                    'context' => context_course::instance($course->id),
                    'other' => array('shortname' => $course->shortname,
                                    'fullname' => $course->fullname,
                                    'updatedfields' => [])
                ));
            
                $event->set_legacy_logdata(array($course->id, 'course', 'update', 'edit.php?id=' . $course->id, $course->id));
                $event->trigger();

                //Migrate contract signatures
                $signatures = $DB->get_records('format_treetopics_contract', array('courseid'=>$course->id));
                foreach($signatures as $s){
                    unset($s->id);
                    $DB->insert_record('format_recit_contract', $s);
                }
            }

            $result .= "<div class=\"alert alert-warning alert-block fade in \">$num données ont été migrées vers Format RÉCIT v2.</div>";
            $result .= "<div class=\"alert alert-warning alert-block fade in \">$num2 cours avec Format RÉCIT ont été migrés vers Format RÉCIT v2</div>";
        }
        catch(Exception $ex){
            $result .= "<div class=\"alert alert-danger alert-block fade in \">".$ex->GetMessage()."</div>";
        }

        return $result;
    }

    public function setCustomFieldData($courseid, $name, $val){
        $handler = core_course\customfield\course_handler::create();
        $k = 'customfield_'.$name;
        $data = new stdClass();
        $data->id = $courseid;
        $data->$k = $val;
        $handler->instance_form_save($data);
    }
    
    public function migrateCC(){
        global $DB, $USER;
        ini_set('max_execution_time', 60*60);
        
        $recitcc = $DB->get_records_sql("SELECT t1.id as id, t2.id as mid, t2.course as course, t2.section as section FROM {course_modules} t2
        INNER JOIN {recitcahiercanada} t1 ON t1.id = t2.instance
        WHERE t2.visible = 1 AND t2.module = (SELECT id FROM {modules} WHERE name='recitcahiercanada');");
        $mod = $DB->get_record_sql("SELECT id FROM {modules} WHERE name='recitcahiertraces'");

        $result = "";
        $num = 0;
        foreach($recitcc as $cc){
            try{
                list ($course, $oldcm) = get_course_and_cm_from_cmId($cc->mid);
                list($oldcm, $oldcontext, $oldmodule, $olddata, $oldcw) = get_moduleinfo_data($oldcm, $course);
                $newcm = duplicate_module($course, $oldcm);
                $cId = $DB->insert_record('recitcahiertraces', ['course' => $cc->course, 'name' => $oldcm->name, 'intro' => '', 'introformat' => 1, 'display' => 0, 'timemodified' => 0]);
                $DB->update_record('course_modules', array('id' => $newcm->id, 'module' => $mod->id, 'instance' => $cId));
                $data = \recitcahiercanada\PersistCtrl::getInstance($DB, $USER)->getCmSuggestedNotes($cc->mid);
    
                \recitcahiertraces\PersistCtrl::getInstance($DB, $USER)->importCahierCanada($newcm->id, $data);
                set_coursemodule_visible($cc->mid, 0);
                $num++;
                $result .= "<div class=\"alert alert-warning alert-block fade in \">Le cahier de traces v1 \"".$oldcm->name."\" a été migré du cours ".$course->shortname. ".</div>";
            }
            catch(Exception $ex){
                $result .= "<div class=\"alert alert-danger alert-block fade in \">".$ex->GetMessage()."</div>";
            }
        }

        if ($num == 0){
            $result .= "<div class=\"alert alert-warning alert-block fade in \">Aucune donnée à migrer.</div>";
        }

        return $result;
    }

    public function migrateTheme(){
        global $DB, $USER;
        ini_set('max_execution_time', 60*60);
        

        $result = "";
        $num = 0;
        $courses = $DB->get_records('course', array('theme'=>'recit'));
        foreach ($courses as $course){
            try{
                $DB->update_record('course', array('id' => $course->id, 'theme' => 'recit2'));
                $num++;
                $result .= "<div class=\"alert alert-warning alert-block fade in \"> Le cours ".$course->shortname. " a été migré vers le theme RÉCIT v2</div>";
            }
            catch(Exception $ex){
                $result .= "<div class=\"alert alert-danger alert-block fade in \">".$ex->GetMessage()."</div>";
            }
        }

        if ($num == 0){
            $result .= "<div class=\"alert alert-danger alert-block fade in \">Aucune donnée à migrer</div>";
        }else{
            purge_caches();
        }

        return $result;
    }
}