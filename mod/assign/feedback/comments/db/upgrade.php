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
 * Upgrade code for the feedback_comments module.
 *
 * @package   assignfeedback_comments
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stub for upgrade code
 * @param int $oldversion
 * @return bool
 */
function xmldb_assignfeedback_comments_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Automatically generated Moodle v4.1.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.2.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.3.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.4.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2024042200.01) {
        // Define field marker to be added.
        $table = new xmldb_table('assignfeedback_comments');
        $field = new xmldb_field('marker', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Conditionally launch add field activity.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Comments savepoint reached.
        upgrade_plugin_savepoint(true, 2024042200.01, 'assignfeedback', 'comments');
    }

    return true;
}
