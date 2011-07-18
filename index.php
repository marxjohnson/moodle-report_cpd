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
 * This page lists CPD Activities the belong to the current user
 *
 * @package   admin-report-cpd                                               
 * @copyright 2010 Kineo open Source                                         
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/tablelib.php');
require_once "$CFG->libdir/formslib.php";
require_once('cpd_filter_form.php');
require_once('lib.php');

global $CFG, $USER;

// Check permissions.
require_login();
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
require_capability('report/cpd:userview', $systemcontext);

// Log request
add_to_log(SITEID, "admin", "report capability", "report/cpd/index.php");

if ($delete_id = optional_param('delete', NULL))
{
	delete_cpd_record($delete_id);
}

$cpdyearid = optional_param('cpdyearid', NULL); // Current CPD year id
$download = optional_param('download', NULL);
$print = optional_param('print', NULL);

// CPD Report headers
$columns = array (
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

if (!empty($download) || !empty($print))
{
	// Filter object
	$filter_data = new stdClass;
	$filter_data->cpdyearid = $cpdyearid;
	$filter_data->filterbydate = optional_param('filterbydate', NULL);
	$filter_data->from = optional_param('from');
	$filter_data->to = optional_param('to');
	$filter_data->userid = $USER->id;
	
	if (($cpd_records = get_cpd_records($filter_data)) && !empty($download))
	{
		// Add disclaimer
		$cpd_records[] = array();
		$cpd_records[] = array(get_string('confirmstatement', 'report_cpd').':', '');
		$cpd_records[] = array(get_string('date').':', '');
		download_csv('cpd_record', $columns, $cpd_records);
		exit;
	}
}
else
{
	$columns['edit'] = get_string('edit');
	$columns['delete'] = get_string('delete');
}

$cpd_years = get_cpd_menu('years');
$userid = $USER->id;

$filter = new cpd_filter_form('index.php', compact('cpd_years', 'userid'), 'post', '', array('class' => 'cpdfilter'));
if (empty($cpd_records)) {
	$filter_data = $filter->get_data();
	if (empty($filter_data))
	{
		// Filter object
		$filter_data = new stdClass;
		$filter_data->userid = $USER->id;
		$cpdyearid = empty($cpdyearid) ? get_current_cpd_year() : $cpdyearid;
		$filter_data->cpdyearid = $cpdyearid; // Set cpd year id always needs to be set
		$filter_data->from = null;
		$filter_data->to = null;
	}
	if (! ($errors = validate_filter($filter_data)) )
	{
		$cpd_records = get_cpd_records($filter_data, true);
	}
	$filter->set_data(compact('cpdyearid'));
} else {
	$filter->set_data((array)$filter_data);
}

if (empty($print)) {
	// Print the header.
	admin_externalpage_setup('cpdrecord');
	// Include styles
	$CFG->stylesheets[] = $CFG->wwwroot.'/admin/report/cpd/css/style.css';
	admin_externalpage_print_header();
} else {
	$CFG->stylesheets[] = $CFG->wwwroot.'/admin/report/cpd/css/print.css';
	// Add JS needed for printing
	require_js(array('yui_dom-event', $CFG->wwwroot.'/admin/report/cpd/js/print.js'));
	print_header();
}

if (! empty($errors))
{
	echo '<div class="box errorbox errorboxcontent">'. implode('<br />' , $errors) .'</div>';
}
//$filter->set_data();
$filter->display();

// Add activity button
if ($cpd_years && $cpdyearid)
{
	echo '<form name="addcpd" method="get" action="edit_activity.php">';
	echo '<input type="hidden" name="cpdyearid" value="'.$cpdyearid.'">';
	echo '<input type="submit" value="Add Activity">';
	echo '</form>';
}

if ($cpd_records)
{
	if (!empty($cpd_years[$cpdyearid])) {
		print_heading("CPD Year: {$cpd_years[$cpdyearid]}", 'left', 4, 'printonly');
	}
	print_heading("$USER->firstname $USER->lastname", 'left', 3, 'printonly');
	$table = new flexible_table('cpd');
	$table->define_columns(array_keys($columns));
	$table->define_headers(array_values($columns));
	$table->column_style('edit', 'text-align', 'center');
	$table->column_style('delete', 'text-align', 'center');
	$table->column_class('edit', 'no_print_col');
	$table->column_class('delete', 'no_print_col');
	//$table->define_baseurl($reporturlwithdisplayoptions->out());
	
	$table->sortable(false);
	$table->collapsible(false);
	//$table->pageable(true);
	//$table->pagesize(3, count($data));
	$table->column_style_all('white-space', 'normal');
	$table->set_attribute('cellspacing', '0');
	$table->set_attribute('id', 'attempts');
	$table->set_attribute('class', 'generaltable boxalignleft cpd');
	
	$table->data = $cpd_records;
	
	$table->setup();
	$table->print_html();
	
	if (!empty($print)) {
		// Disclaimer
		echo '	<table class="disclaimer" cellpadding="0" cellspacing="5" border="0">
				<tr>
					<td class="name">'.get_string('confirmstatement', 'report_cpd').'</td>
					<td class="fillbox">&nbsp;</td>
					<td class="date">'.get_string('date').'</td>
					<td class="fillbox date">&nbsp;</td>
					</tr>
			</table>';
	}
	
	echo '<table class="boxalignleft"><tr>';
	echo '<td>';
	print_single_button('index.php', array('download' => 1) + ((array)$filter_data), get_string('exportcsv', 'report_cpd'), null, null, false);
	echo '</td><td>';
	print_print_button('index.php', $filter_data);
	echo '</td></tr></table>';
}

if (empty($print)) {
	admin_externalpage_print_footer();
} else {
	print_footer();
}

?>
