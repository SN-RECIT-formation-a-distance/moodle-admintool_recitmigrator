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
    if ($_GET['action'] == 'theme'){
        $resultTheme = $migr->migrateTheme();
    }
    if ($_GET['action'] == 'editor'){
        $resultEditor = $migr->migrateEditor();
    }
}

echo "<hr>";
echo "<h3>Migration thème RÉCIT v1 vers RÉCIT legacy</h3>";
echo "<div class=\"text-muted \">
<p>Cette migration va migrer tout les cours ayant forcé le thème RÉCIT v1 vers RÉCIT legacy.</p>
<p>Le thème RÉCIT legacy contient toutes les améliorations du thème RÉCIT v2 et assure la compatibilité des contenus créés avec le thème RÉCIT v1.</p>
</div>";
echo "<form><input type='hidden' name='action' value='theme'/><input type='submit' class='m-3 btn btn-primary' value='Démarrer la migration'/></form>";
if(!empty($resultTheme)){
    echo "<h4>Résultat</h4>";
    echo $resultTheme;
}

echo "<hr>";
echo "<h3>Migration Format RÉCIT vers Format RÉCIT v2</h3>";
echo "<div class=\"text-muted \">
<p>Cette migration transforme le Format de cours RÉCIT (modèle de menu, niveau de section, afficher la navigation par section, activer le cheminement personnalisé par groupe) vers le Format de cours RÉCIT v2 et migre aussi les options du format de cours.</p>
<p>Le format RÉCIT est dépendant du thème RÉCIT v2.</p>
</div>";
echo "<form><input type='hidden' name='action' value='format'/><input type='submit' class='m-3 btn btn-primary' value='Démarrer la migration'/></form>";
if(!empty($resultFormat)){
    echo "<h4>Résultat</h4>";
    echo $resultFormat;
}

echo "<hr>";
echo "<h3>Migration Cahier de traces v1 vers Cahier de traces v2</h3>";
echo "<div class=\"text-muted \">
<p>Cette migration cache l'activité Cahier de trace v1 et génère une nouvelle activité Cahier de trace v2.</p>
<p>Veuillez patienter, car le temps de traitement de la requête dépend de la quantité de données utilisateur.</p>
</div>
<div class=\"alert alert-primary alert-block fade in \">Après la migration, il faut désactiver le filtre Cahier de traces et activer le filtre Cahier de traces v2 sur ".$CFG->wwwroot."/admin/filters.php</div>";
$disabled = "disabled";
if(file_exists("{$CFG->dirroot}/mod/recitcahiercanada/")){
    $version = get_config('mod_recitcahiercanada')->version;
    if ($version >= 2022020900) $disabled = "";
}
echo "<form><input type='hidden' name='action' value='cc'/><input type='submit' ".$disabled." class='m-3 btn btn-primary' value='Démarrer la migration'/></form>";
if (!empty($disabled)){
    echo "<div class=\"alert alert-primary alert-block fade in \">Cette opération requiert le plugin mod_recitcahiercanada v1.15.0</div>";
}
if(!empty($resultCC)){
    echo "<h4>Résultat</h4>";
    echo $resultCC;
}

echo "<hr>";
echo "<h3>Migration des gabarits vvvebjs vers html bootstrap editor</h3>";
echo "<div class=\"text-muted \">
<p>L'opération crée de nouveaux gabarits pour tous les utilisateurs. Lors de l'opération, le gabarit généré par l'utilisateur à l'aide de VVVEB est migré vers l'éditeur HTML Bootstrap. Ne pas dupliquer la requête (clic) car celle-ci crée une copie des gabarits HTML Bootstrap à chaque fois que la commande est lancée. </p>
<p>Veuillez patienter, car le temps de traitement de la requête dépend de la quantité de données utilisateur.</p>
</div>";

echo "<form><input type='hidden' name='action' value='editor'/><input type='submit' class='m-3 btn btn-primary' value='Démarrer la migration'/></form>";
if(!empty($resultEditor)){
    echo "<h4>Résultat</h4>";
    echo $resultEditor;
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
