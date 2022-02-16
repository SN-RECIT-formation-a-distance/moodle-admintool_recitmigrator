<?php 

class RecitMigrator {

    public function migrateFormat(){
        global $DB;
        
        $recitopts = $DB->get_records('course_format_options', array('format' => 'treetopics', 'name' => 'ttsectiondisplay'));
        if (!empty($recitopts)){
            foreach($recitopts as $data){
                $DB->execute("insert into {format_recit_options} (courseid, sectionid, name, value)
                values(?, ?, 'sectionlevel' ?)
                ON DUPLICATE KEY UPDATE value = value", [$data->courseid, $data->sectionid, $data->value]);
            }
        }

        echo "Les données de Treetopics ont été migré vers v2";
    }
}