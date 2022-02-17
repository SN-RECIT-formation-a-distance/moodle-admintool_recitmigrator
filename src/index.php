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

if (isset($_GET['action'])){
    $migr = new RecitMigrator();
    if ($_GET['action'] == 'format'){
        $migr->migrateFormat();
    }
    if ($_GET['action'] == 'cc'){
        $migr->migrateCC();
    }
}

echo "<div class=\"alert alert-info alert-block fade in \">
La migration du format va migré les niveaux de section vers v2 sans forcer le format.
</div>";
echo "<form><input type='hidden' name='action' value='format'/><input type='submit' class='btn btn-primary' value='Migrer format vers v2'/></form><hr>";

echo "<div class=\"alert alert-info alert-block fade in \">
La migration du cahier de trace va migré le cahier ainsi que les données vers une nouvelle activité et va cacher l'ancienne activité.
</div>";
echo "<form><input type='hidden' name='action' value='cc'/><input type='submit' class='btn btn-primary' value='Migrer cahier trace vers v2'/></form>";

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
