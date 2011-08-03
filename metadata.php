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


/**
 * This page is used to add, modify or delete CPD Years, Activity types, or Statuses.
 *
 * You can also change the display order of the Statuses.
 * You cannot delete or modify status 'Objective Met', because End Date of a CPD Activity is set when
 * the status is changed to this.
 *
 * @package   admin-report-cpd
 * @copyright 2010 Kineo open Source
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/report/cpd/lib.php');

// Check permissions.
require_login($SITE);
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
require_capability('report/cpd:superadminview', $systemcontext);
$PAGE->set_url($CFG->dirroot.'/'.$CFG->admin.'/report/cpd/metadata.php');
$output = $PAGE->get_renderer('report_cpd');

$id = optional_param('id', null, PARAM_INT);
$errors = null;
$edit = null;


if ($process = optional_param('process', null, PARAM_RAW)) {
    $errors = process_meta_form($process);
} else if ($table = optional_param('delete', null, PARAM_RAW)) {
    delete_meta_record($table, $id);
} else if ($table = optional_param('edit', null, PARAM_RAW)) {
    if ($result = get_meta_records($table, $id)) {
        $edit[$table] = $result;
    }
} else if ($table = optional_param('moveup', null, PARAM_RAW)) {
    change_display_order($table, $id, 'up');
} else if ($table = optional_param('movedown', null, PARAM_RAW)) {
    change_display_order($table, $id, 'down');
}

$activity_types = $DB->get_records('cpd_activity_type', null, 'name asc');
$years = $DB->get_records('cpd_year', null, 'startdate asc, enddate asc');
$statuses = $DB->get_records('cpd_status', null, 'display_order asc');

// Print the header.
admin_externalpage_setup('cpdmetadata');
echo $OUTPUT->header();

global $CFG;
if (isset($errors)) {
    echo $output->error_box($errors);
}
$editactivitytype = isset($edit['activitytype']) ? $edit['activitytype'] : null;
echo $output->settings_form('activitytype', $activity_types, $editactivitytype);

$edityear = isset($edit['cpdyears']) ? $edit['cpdyears'] : null;
echo $output->settings_form('cpdyears', $years, $edityear, true);

$editstatus = isset($edit['status']) ? $edit['status'] : null;
echo $output->settings_form('status', $statuses, $editstatus, false, true);

echo $OUTPUT->footer();
