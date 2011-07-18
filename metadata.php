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
require_once('lib.php');

// Check permissions.
require_login();
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
require_capability('report/cpd:superadminview', $systemcontext);

$id = optional_param('id', NULL);
$errors = null;
$edit = null;

if( $process = optional_param('process', NULL) )
{
	$errors = process_meta_form($process);
}
else if ( $table = optional_param('delete', NULL) )
{
	delete_meta_record($table, $id);
}
else if ( $table = optional_param('edit', NULL) )
{
	if ($result = get_meta_records($table, $id))
	{
		$edit[$table] = $result;
	}
}
else if ( $table = optional_param('moveup', NULL) )
{
	change_display_order($table, $id, 'up');
}
else if ( $table = optional_param('movedown', NULL) )
{
	change_display_order($table, $id, 'down');
}

$activity_types = get_records('cpd_activity_type', null, null, 'name asc');
$years = get_records('cpd_year', null, null, 'startdate asc, enddate asc');
$statuses = get_records('cpd_status', null, null, 'display_order asc');

// Print the header.
admin_externalpage_setup('cpdmetadata');
// Include styles
$CFG->stylesheets[] = $CFG->wwwroot.'/admin/report/cpd/css/style.css';
admin_externalpage_print_header();

global $CFG;
if (isset($errors))
{
	echo '<div class="box errorbox errorboxcontent">'. implode('<br />' , $errors) .'</div>';
}
?>
<table class="cpd_settings" cellpadding="8" border="0" />
	<tr>
		<th colspan="2"><?php print_string('activitytypes', 'report_cpd'); ?></th>
	</tr>
	<tr>
		<td class="itemlist">
			<table class="cpd_list" cellpadding="0" cellspacing="0" border="0">
				<?php
				if ($activity_types)
				{
					foreach ($activity_types as $activity_type)
					{
				?>
				<tr>
					<td><?php echo $activity_type->name ?></td>
					<td>
						<a href="<?php echo "$CFG->wwwroot/$CFG->admin/report/cpd/metadata.php?edit=activitytype&id=$activity_type->id" ?>"><img src="<?php echo $CFG->pixpath?>/t/edit.gif" alt="edit" /></a>
						<a onclick="return confirm('Are you sure you want to delete?');" href="<?php echo "$CFG->wwwroot/$CFG->admin/report/cpd/metadata.php?delete=activitytype&id=$activity_type->id" ?>"><img src="<?php echo $CFG->pixpath?>/t/delete.gif" alt="delete" /></a>
					</td>
				</tr>
				<?php
					}
				}
				?>
			</table>
		</td>
		<td class="itemform">
			<h3><?php echo (isset($edit['activitytype'])) ? get_string('update') : get_string('addnew', 'report_cpd').' '.get_string('activitytype', 'report_cpd'); ?></h3>
			<form action="" method="post" name="frmactivitytype">
			<?php
				$activity_name = '';
				if (isset($edit['activitytype']))
				{
					echo '<input type="hidden" name="frmid" value="'. $edit['activitytype']->id .'" />';
					$activity_name = $edit['activitytype']->name;
				}
			?>
				<input type="hidden" name="process" value="activitytype" />
				<input type="text" value="<?php echo $activity_name ?>" name="activitytype" />
				<input type="submit" value="<?php echo (isset($edit['activitytype'])) ? get_string('update') : get_string('add') ?>" />
			</form>
		</td>
	</tr>
</table>
<table class="cpd_settings" cellpadding="8" border="0" />
	<tr>
		<th colspan="2"><?php print_string('cpdyears', 'report_cpd'); ?></th>
	</tr>
	<tr>
		<td class="itemlist">
			<table class="cpd_list" cellpadding="0" cellspacing="0" border="0">
				<?php
				if ($years)
				{
					foreach ($years as $year)
					{
				?>
				<tr>
					<td><?php echo date("d/m/Y", $year->startdate) . " - " . date("d/m/Y", $year->enddate) ?></td>
					<td>
						<a href="<?php echo "$CFG->wwwroot/$CFG->admin/report/cpd/metadata.php?edit=year&id=$year->id" ?>"><img src="<?php echo $CFG->pixpath?>/t/edit.gif" alt="edit" /></a>
						<a onclick="return confirm(get_string('confirmdelete', 'report_cpd'));" href="<?php echo "$CFG->wwwroot/$CFG->admin/report/cpd/metadata.php?delete=year&id=$year->id" ?>"><img src="<?php echo $CFG->pixpath?>/t/delete.gif" alt="delete" /></a>
					</td>
				</tr>
				<?php
					}
				}
				?>
			</table>
		</td>
		<td class="itemform">
			<h3><?php echo (isset($edit['year'])) ? get_string('update') : get_string('add').' '.get_string('cpdyears', 'report_cpd'); ?></h3>
			<form action="" method="post" name="frmcpdyears">
			<?php
				$year_startdate = null;
				$year_enddate = null;
				if (isset($edit['year']))
				{
					echo '<input type="hidden" name="frmid" value="'. $edit['year']->id .'" />';
					$year_startdate = $edit['year']->startdate;
					$year_enddate = $edit['year']->enddate;
				}
			?>
				<input type="hidden" name="process" value="cpdyears" />
				<label for="menustartday">Start:</label>
				<?php print_date_selector('startday', 'startmonth', 'startyear', $year_startdate); ?><br/>
				<label for="menuendday">End:</label>
				<?php print_date_selector('endday', 'endmonth', 'endyear', $year_enddate); ?>
				<input type="submit" value="<?php echo (isset($edit['year'])) ? get_string('update') : get_string('add') ?>" />
			</form>
		</td>
	</tr>
</table>
<table class="cpd_settings" cellpadding="8" border="0" />
	<tr>
		<th colspan="2"><?php print_string('status', 'report_cpd'); ?></th>
	</tr>
	<tr>
		<td class="itemlist">
			<table class="cpd_list" cellpadding="0" cellspacing="0" border="0">
				<?php
				if ($statuses)
				{
					foreach ($statuses as $status)
					{
				?>
				<tr>
					<td><?php echo $status->name ?></td>
					<td>
						<a href="<?php echo "$CFG->wwwroot/$CFG->admin/report/cpd/metadata.php?moveup=status&id=$status->id" ?>">
							<img src="<?php echo $CFG->pixpath?>/t/up.gif" alt="up" /></a>
						<a href="<?php echo "$CFG->wwwroot/$CFG->admin/report/cpd/metadata.php?movedown=status&id=$status->id" ?>">
							<img src="<?php echo $CFG->pixpath?>/t/down.gif" alt="down" /></a>
					<?php 
						if (! in_array( strtoupper($status->name), array('OBJECTIVE MET')) )
						{
					?>
						<a href="<?php echo "$CFG->wwwroot/$CFG->admin/report/cpd/metadata.php?edit=status&id=$status->id" ?>">
									<img src="<?php echo $CFG->pixpath?>/t/edit.gif" alt="edit" /></a>
						<a onclick="return confirm(<?php get_string('confirmdelete', 'report_cpd') ?>);" href="<?php echo "$CFG->wwwroot/$CFG->admin/report/cpd/metadata.php?delete=status&id=$status->id" ?>">
									<img src="<?php echo $CFG->pixpath?>/t/delete.gif" alt="delete" /></a>
					<?php 
						}
					?>
					</td>
				</tr>
				<?php
					}
				}
				?>
			</table>
		</td>
		<td class="itemform">
			<h3><?php echo (isset($edit['status'])) ? get_string('update') : get_string('addnew', 'report_cpd').' '.get_string('status', 'report_cpd'); ?></h3>
			<form action="" method="post" name="frmstatus">
			<?php
				$status_name = '';
				if (isset($edit['status']))
				{
					echo '<input type="hidden" name="frmid" value="'. $edit['status']->id .'" />';
					$status_name = $edit['status']->name;
				}
			?>
				<input type="hidden" name="process" value="status" />
				<input type="text" value="<?php echo $status_name ?>" name="status" />
				<input type="submit" value="<?php echo (isset($edit['status'])) ? get_string('update') : get_string('add') ?>" />
			</form>
		</td>
	</tr>
</table>
<?php

admin_externalpage_print_footer();
?>
