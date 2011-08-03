<?php
// This file is part of CPD Report for Moodle
//
// CPD Report for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// CPD Report for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with CPD Report for Moodle.  If not, see <http://www.gnu.org/licenses/>.

function xmldb_report_cpd_install() {
    global $DB;
    $statusrows = array(
        (object)array('name' => 'Started', 'display_order' => 2),
        (object)array('name' => 'Objective Met', 'display_order' => 3),
        (object)array('name' => 'Not Started', 'display_order' => 1)
    );

    foreach ($statusrows as $row) {
        $DB->insert_record('cpd_status', $row);
    }

    $yearrows = array(
        (object)array('startdate' => 1262304000, 'enddate' => 1293839999),
        (object)array('startdate' => 1293840000, 'enddate' => 1325375999),
        (object)array('startdate' => 1325376000, 'enddate' => 1356998399)
    );

    foreach ($yearrows as $row) {
        $DB->insert_record('cpd_year', $row);
    }

    $activitytyperows = array(
        (object)array('name' => 'Attendence in college/university'),
        (object)array('name' => 'Computer based training'),
        (object)array('name' => 'Conferences'),
        (object)array('name' => 'Discussions'),
        (object)array('name' => 'Examination'),
        (object)array('name' => 'Individual informal study'),
        (object)array('name' => 'Mentoring'),
        (object)array('name' => 'On-the-job training'),
        (object)array('name' => 'Professional Institute'),
        (object)array('name' => 'Reading'),
        (object)array('name' => 'Self-managed learning'),
        (object)array('name' => 'Seminars'),
        (object)array('name' => 'Structured discussions'),
        (object)array('name' => 'Training course')
    );

    foreach ($activitytyperows as $row) {
        $DB->insert_record('cpd_activity_type', $row);
    }
}
?>
