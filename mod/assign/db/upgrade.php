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

    // FIXME This needs rewriting to migrate upstream mod_assign tables to new
    // tables.  Currently it migrates between development table structures.
    if ($oldversion < 2024042200.01) {
        // Define field activity to be added to assign.
        $table = new xmldb_table('assign');
        $field = new xmldb_field(
            'markercount',
            XMLDB_TYPE_INTEGER,
            '6',
            null,
            XMLDB_NOTNULL,
            null,
            '1',
            'markingallocation'
        );
        // Conditionally launch add field activity.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024042200.01, 'assign');
    }

    if ($oldversion < 2024042200.06) {
        // Define table assign_allocated_marker to be created.
        $table = new xmldb_table('assign_allocated_marker');

        // Adding fields to table assign_allocated_marker.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assignment', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table assign_allocated_marker.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for assign_allocated_marker.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Populate assign_allocated_marker.
        $sql = 'SELECT userid, assignment, allocatedmarker AS markerid
                  FROM {assign_user_flags}
                 WHERE allocatedmarker <> 0';
        $rs = $DB->get_recordset_sql($sql);

        foreach ($rs as $record) {
            $DB->insert_record('assign_allocated_marker', $record);
        }

        // Define field allocatedmarker to be dropped from assign_user_flags.
        $table = new xmldb_table('assign_user_flags');
        $field = new xmldb_field('allocatedmarker');

        // Conditionally launch drop field allocatedmarker.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024042200.06, 'assign');
    }

    if ($oldversion < 2024042200.07) {
        // Define field allocatedmarkers to be added to assign_user_flags.
        $table = new xmldb_table('assign_user_flags');
        $field = new xmldb_field('allocatedmarkers', XMLDB_TYPE_CHAR, '255', null, null, null, '');

        // Conditionally launch add field allocatedmarkers.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Populate new field allocatedmarkers.
        $sql = 'SELECT assignment, userid, markerid
                  FROM {assign_allocated_marker}
              GROUP BY assignment, userid';

        $rs = $DB->get_recordset_sql($sql);

        $userid = -1;
        $assignment = -1;

        foreach ($rs as $record) {
            if (($record->userid != $userid) && ($record->assignment != $assignment)) {
                if (($userid != -1) && ($assignment != -1)) {
                    $params = ['userid' => $userid, 'assignment' => $assignment];

                    if ($record = $DB->get_record('assign_user_flags', $params)) {
                        $record->allocatedmarkers = implode(',', $markers);
                        $DB->update_record('assign_user_flags', $record);
                    } else {
                        $record = new stdClass();
                        $record->userid = $userid;
                        $record->assignment = $assignment;
                        $record->allocatedmarkers = implode(',', $markers);
                        $DB->update_record('assign_user_flags', $record);
                    }
                }

                $userid = $record->userid;
                $assignment = $record->assignment;
                $markers = [$record->markerid];
            }

            $markers[] = $record->markerid;
        }

        // Drop assign_allocated_marker_table
        $table = new xmldb_table('assign_allocated_marker');
        $dbman->drop_table($table).

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024042200.07, 'assign');
    }

    if ($oldversion < 2024042200.08) {

        // Remove index on assign_grades because we want to modifiy this.
        $table = new xmldb_table('assign_grades');
        $fields = ['assignment', 'userid', 'attemptnumber'];
        $index = new xmldb_index('mdl_assigrad_assuseatt_uix', XMLDB_INDEX_UNIQUE, $fields);

        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        $fields[] = 'grader';
        $index = new xmldb_index('mdl_assigrad_assuseatt_uix', XMLDB_INDEX_UNIQUE, $fields);
        $dbman->add_index($table, $index);

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024042200.08, 'assign');
    }

    if ($oldversion < 2024042200.09) {

        // Define table assign_grades_mark to be created.
        $table = new xmldb_table('assign_grades_mark');

        // Adding fields to table assign_allocated_mark.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('gradeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('marker', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('grade', XMLDB_TYPE_FLOAT, '10,5', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table assign_grades_mark.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('gradeid', XMLDB_KEY_FOREIGN, ['gradeid'], 'assign_grades', ['id']);
        $table->add_key('marker', XMLDB_KEY_FOREIGN, ['marker'], 'user', ['id']);

        // Conditionally launch create table for assign_grades_mark.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2024042200.09, 'assign');
    }

    // Automatically generated Moodle v4.4.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
