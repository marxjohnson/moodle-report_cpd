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
 * This page is a report of all CPD Activities.
 *
 * The report can be filtered by CPD Year, Date and User.
 *
 * @package   admin-report-cpd
 * @copyright 2010 Kineo open Source
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/tablelib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/admin/report/cpd/cpd_filter_form.php');
require_once($CFG->dirroot.'/admin/report/cpd/lib.php');

// Check permissions.
require_login($SITE);
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
require_capability('report/cpd:adminview', $systemcontext);
$PAGE->set_url('/'.$CFG->admin.'/report/cpd/adminview.php');
$output = $PAGE->get_renderer('report_cpd');

// Log request
add_to_log(SITEID, "admin", "report capability", "report/cpd/adminview.php");

$print = optional_param('print', null, PARAM_BOOL);
$download = optional_param('download', null, PARAM_BOOL);
$returned = null;

// Extra columns
$extra_columns['user_name'] = true;

// CPD Report headers
$columns = array(
    'name' => get_string('name', 'report_cpd'),
    'objective' => get_string('objective', 'report_cpd'),
    'development_need' => get_string('developmentneed', 'report_cpd'),
    'activity_type' => get_string('activitytype', 'report_cpd'),
    'activity' => get_string('activity', 'report_cpd'),
    'due_date' => get_string('datedue', 'report_cpd'),
    'start_date' => get_string('datestart', 'report_cpd'),
    'end_date' => get_string('dateend', 'report_cpd'),
    'status' => get_string('status', 'report_cpd'),
    'timetaken' => get_string('timetaken', 'report_cpd')
);

if (!empty($download) || !empty($print)) {
    // Filter object
    $filter_data = new stdClass;
    $filter_data->cpdyearid = optional_param('cpdyearid', null, PARAM_INT);
    $filter_data->filterbydate = optional_param('filterbydate', null, PARAM_BOOL);
    $filter_data->from = optional_param('from', null, PARAM_INT);
    $filter_data->to = optional_param('to', null, PARAM_INT);
    $filter_data->activitytypeid = optional_param('activitytypeid', null, PARAM_INT);
    $filter_data->userid = optional_param('userid', null, PARAM_INT);

    $cpd_records = get_cpd_records($filter_data, false, $extra_columns, $returned);
    if ($cpd_records && !empty($download)) {
        download_csv('cpd_record', $columns, $cpd_records);
        exit;
    }
}

$cpd_years = get_cpd_menu('years');
$activity_types = get_cpd_menu('activity_types');
$users = get_users_by_capability(
    $systemcontext,
    'report/cpd:userview',
    'u.id, u.firstname, u.lastname',
    'lastname ASC'
);

$filter = new cpd_filter_form(
    'adminview.php',
    compact('cpd_years', 'activity_types', 'users'),
    'post',
    '',
    array('class' => 'cpdfilter')
);

if (empty($cpd_records)) {
    if ($filter_data = $filter->get_data()) {
        if (!($errors = validate_filter($filter_data))) {
            $cpd_records = get_cpd_records($filter_data, false, $extra_columns, $returned);
        }
    }
}

// Print the header.
if (has_capability('report/cpd:superadminview', $systemcontext) && empty($print)) {
    admin_externalpage_setup('cpdadminview');
    // Include styles
    $CFG->stylesheets[] = $CFG->wwwroot.'/admin/report/cpd/css/style.css';
} else {
    if (empty($print)) {
        $navlinks = array();
        $navlinks[] = array('name' => get_string('reports', 'report_cpd'), 'link' => null, 'type' => 'misc');
        $navlinks[] = array('name' => get_string('cpddevreport', 'report_cpd'), 'link' => null, 'type' => 'misc');
        $navigation = build_navigation($navlinks);
        // Include styles
        echo $OUTPUT->header(
            get_string('cpddevreport', 'report_cpd'),
            get_string('cpddevreport', 'report_cpd'),
            $navigation
        );
        $printparams = (array)$filter_data + array('print' => 1);
        $printlink = new moodle_url('/admin/report/cpd/index.php', $printparams);
        $PAGE->requires->string_for_js('printlandscape', 'report_cpd');
        $PAGE->requires->js_init_call('M.report_cpd.init', array(false, $printlink->out(false)));
    } else {
        $PAGE->requires->css('/admin/report/cpd/css/print.css');
        $PAGE->requires->js_init_call('M.report_cpd.init', array(true, null));
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('cpddevreport', 'report_cpd'));

if (isset($errors)) {
    echo '<div class="box errorbox errorboxcontent">'. implode('<br />' , $errors) .'</div>';
}

//$filter->set_data();
$filter->display();

if (!empty($cpd_records)) {
    $table = new flexible_table('cpd');
    $table->define_columns(array_keys($columns));
    $table->define_headers(array_values($columns));
    $table->define_baseurl($PAGE->url->out());

    $table->sortable(false);
    $table->collapsible(false);
    //$table->pageable(true);
    //$table->pagesize(3, count($data));
    $table->column_style_all('white-space', 'normal');
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'attempts');
    $table->set_attribute('class', 'generaltable boxalignleft cpd');

    $table->setup();
    foreach ($cpd_records as $cpd_record) {
        $table->add_data($cpd_record);
    }

    $table->finish_output();
    if ($table->started_output) {
        if (!empty($print)) {
            // Disclaimer
            echo $output->disclaimer();
        }

        echo $output->export_controls($PAGE, $filter_data);
    }

} else if (!empty($filter_data)) {
    echo '<h4 class="noresults">'. get_string('noresults', 'report_cpd') .'</h4>';
}

echo $OUTPUT->footer();
