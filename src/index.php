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

/**
 * PHPUnit info
 *
 * @package    tool_phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require(__DIR__ . '/classes/migrator.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('toolrecitmigrator');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_recitmigrator'));
echo $OUTPUT->box_start();

$resultFormat = "";
$resultCC = "";
if (isset($_GET['action'])){
    $migr = new RecitMigrator();
    if ($_GET['action'] == 'format'){
        $resultFormat = $migr->migrateFormat();
    }
    if ($_GET['action'] == 'cc'){
        $resultCC = $migr->migrateCC();
    }
}

echo "<h3>Migration Tree Topics pour Format RÉCIT</h3>";
echo "<div class=\"text-muted \">
La migration du format va migré les données Treetopics (modèle menu, niveau de menu, affichage par section, cacher section restreint) vers format RÉCIT et changera aussi le format de cours.
Le format RÉCIT est dépendant du theme RÉCIT v2.
</div>";
echo "<form><input type='hidden' name='action' value='format'/><input type='submit' class='m-3 btn btn-primary' value='Démarrer la migration'/></form><hr>";
if(!empty($resultFormat)){
    echo "<h4>Résultat</h4>";
    echo $resultFormat;
}

echo "<h3>Migration Cahier de traces v1 au Cahier de traces v2</h3>";
echo "<div class=\"text-muted \">
La migration du cahier de trace va migré le cahier ainsi que les données vers une nouvelle activité et va cacher l'ancienne activité.
</div>";
echo "<form><input type='hidden' name='action' value='cc'/><input type='submit' class='m-3 btn btn-primary' value='Démarrer la migration'/></form><hr>";
if(!empty($resultCC)){
    echo "<h4>Résultat</h4>";
    echo $resultCC;
}


echo $OUTPUT->box_end();
echo $OUTPUT->footer();
