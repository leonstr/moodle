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
 * Upgrade code for install
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * upgrade this assignment instance - this function could be skipped but it will be needed later
 * @param int $oldversion The old version of the assign module
 * @return bool
 */
function xmldb_assign_upgrade($oldversion) {
    global $DB;

    // Automatically generated Moodle v4.1.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.2.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.3.0 release upgrade line.
    // Put any upgrade step following this.

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2023103000) {
        // Define field activity to be added to assign.
        $table = new xmldb_table('assign');
        $field = new xmldb_field(
            'markinganonymous',
            XMLDB_TYPE_INTEGER,
            '2',
            null,
            XMLDB_NOTNULL,
            null,
            '0',
            'markingallocation'
        );
        // Conditionally launch add field activity.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2023103000, 'assign');
    }

    // Automatically generated Moodle v4.4.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2024042201) {
        // The 'Never' ('none') option for the additional attempts (attemptreopenmethod) setting is no longer supported
        // and needs to be updated in all relevant instances.

        // The default value for the 'attemptreopenmethod' field in the 'assign' database table is currently set to 'none',
        // This needs to be updated to 'untilpass' to ensure the system functions correctly. Additionally, the default
        // value for the 'maxattempts' field needs to be changed to '1' to prevent multiple attempts and maintain the
        // original behavior.
        $table = new xmldb_table('assign');
        $attemptreopenmethodfield = new xmldb_field('attemptreopenmethod', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL,
            null, 'untilpass');
        $maxattemptsfield = new xmldb_field('maxattempts', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL,
            null, '1');
        $dbman->change_field_default($table, $attemptreopenmethodfield);
        $dbman->change_field_default($table, $maxattemptsfield);

        // If the current value for the 'attemptreopenmethod' global configuration in the assignment is set to 'none'.
        if (get_config('assign', 'attemptreopenmethod') == 'none') {
            // Reset the value to 'untilpass'.
            set_config('attemptreopenmethod', 'untilpass', 'assign');
            // Also, setting the value for the 'maxattempts' global config in the assignment to '1' ensures that the
            // original behaviour is preserved by disallowing any additional attempts by default.
            set_config('maxattempts', 1, 'assign');
        }

        // Update all the current assignment instances that have their 'attemptreopenmethod' set to 'none'.
        // By setting 'maxattempts' to 1, additional attempts are disallowed, preserving the original behavior.
        $DB->execute(
            'UPDATE {assign}
                    SET attemptreopenmethod = :newattemptreopenmethod,
                        maxattempts = :maxattempts
                  WHERE attemptreopenmethod = :oldattemptreopenmethod',
            [
                'newattemptreopenmethod' => 'untilpass',
                'maxattempts' => 1,
                'oldattemptreopenmethod' => 'none',
            ]
        );

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024042201, 'assign');
    }

    if ($oldversion < 2024100700.06) {

        // Define field markercount to be added to assign.
        $table = new xmldb_table('assign');
        $field = new xmldb_field('markercount', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, '1', 'markingallocation');
        // Conditionally launch add field markercount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field multimarkmethod to be added to assign.
        $table = new xmldb_table('assign');
        $field = new xmldb_field('multimarkmethod', XMLDB_TYPE_CHAR, '10', null, false, false, null, 'markercount');
        // Conditionally launch add field multimarkmethod.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table assign_mark to be created.
        $table = new xmldb_table('assign_mark');

        // Adding fields to table assign_mark.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('gradeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('marker', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mark', XMLDB_TYPE_FLOAT, '10,5', null, XMLDB_NOTNULL, null, null);
        $table->add_field('workflowstate', XMLDB_TYPE_CHAR, '20', null, null, null, null);

        // Adding keys to table assign_grades_mark.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('gradeid', XMLDB_KEY_FOREIGN, ['gradeid'], 'assign_grades', ['id']);
        $table->add_key('marker', XMLDB_KEY_FOREIGN, ['marker'], 'user', ['id']);

        // Conditionally launch create table for assign_mark.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024100700.06, 'assign');
    }

    if ($oldversion < 2024100700.07) {
        // Define field allocatedmarkers to be added to assign_user_flags.
        $table = new xmldb_table('assign_user_flags');
        $field = new xmldb_field('allocatedmarkers', XMLDB_TYPE_CHAR, '255', null, null, null, '');

        // Conditionally launch add field allocatedmarkers.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Populate new field allocatedmarkers.
        $DB->execute('UPDATE {assign_user_flags}
                         SET allocatedmarkers = allocatedmarker');

        // Define field allocatedmarker to be dropped from assign_user_flags.
        $table = new xmldb_table('assign_user_flags');
        $field = new xmldb_field('allocatedmarker');

        // Conditionally launch drop field allocatedmarker.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024100700.07, 'assign');
    }

    if ($oldversion < 2024100700.08) {
        // Define table assign_allocated_marker to be created.
        $table = new xmldb_table('assign_allocated_marker');

        // Adding fields to table assign_allocated_marker.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assignid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table assign_allocated_marker.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for assign_allocated_marker.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Populate assign_allocated_marker.
        $sql = "SELECT userid, assignment, allocatedmarkers
                  FROM {assign_user_flags}
                 WHERE allocatedmarkers <> ''";
        $rs = $DB->get_recordset_sql($sql);

        foreach ($rs as $record) {
            foreach(explode(',', $record->allocatedmarkers) as $markerid) {
                $newrecord = new stdClass();
                $newrecord->studentid = $record->userid;
                $newrecord->assignid = $record->assignment;
                $newrecord->markerid = $markerid;
                $DB->insert_record('assign_allocated_marker', $newrecord);
            }
        }

        // Define field allocatedmarker to be dropped from assign_user_flags.
        $table = new xmldb_table('assign_user_flags');
        $field = new xmldb_field('allocatedmarkers');

        // Conditionally launch drop field allocatedmarker.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024100700.08, 'assign');
    }

    if ($oldversion < 2024100700.09) {
        // Define table assign_allocated_marker to be modified.
        $table = new xmldb_table('assign_allocated_marker');

        $field = new xmldb_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $dbman->rename_field($table, $field, 'student');

        $field = new xmldb_field('assignid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $dbman->rename_field($table, $field, 'assignment');

        $field = new xmldb_field('markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $dbman->rename_field($table, $field, 'marker');

        // Define table assign_mark to be modified.
        $table = new xmldb_table('assign_mark');

        // Add field assignment.  This field is just so that rows are
        // associated with the corresponding assignment during backup/restore.
        $field = new xmldb_field('assignment', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id');

        // Conditionally launch add field assignment.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute("UPDATE {assign_mark} am
                        JOIN {assign_grades} ag ON am.gradeid = ag.id
                         SET am.assignment = ag.assignment");

        $field = new xmldb_field('assignment', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_notnull($table, $field);

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024100700.09, 'assign');
    }

    return true;
}
