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

require_once($CFG->dirroot.'/user/filters/lib.php');

/**
 * Select filter for custom user profile field with type drop-down menu.
 */
class user_filter_selectprofile extends user_filter_select {
    public function __construct($name, $label, $advanced, $field, $options, $default=null) {
        parent::__construct($name, $label, $advanced, $field, $options, $default);
        $this->_field   = $field;
        $this->_options = $options;
        $this->_default = $default;
    }

    /**
     * Returns the condition to be used with SQL "WHERE" clause
     * @param array $data filter settings
     * @return array sql string and $params
     */
    public function get_sql_filter($data) {
        static $counter = 0;
        $name = 'ex_selectprofile'.$counter++;
        $value = $this->_options[$data['value']];
        $field = $this->_field; // Custom profile field's shortname
        $operator = $data['operator'];

        switch($operator) {
            case 1: // Equal to.
                $op = " IN ";
                break;
            case 2: // Not equal to.
                $op = " NOT IN ";
                break;
            default:
                $op = " IN ";   // Shouldn't happen?
        }

        $params = array();
        $where = " WHERE data = :$name AND mdl_user_info_field.shortname = '$field'"; // FIXME $field should probably be a param (somehow)
        $params[$name] = $value;

        return array("id $op (SELECT userid FROM {user_info_data} INNER JOIN mdl_user_info_field ON mdl_user_info_data.fieldid = mdl_user_info_field.id $where)", $params);
    }
}
