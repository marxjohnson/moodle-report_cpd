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
 * Defines the settings of this CPD Report
 *
 * @package   admin-report-cpd                                               
 * @copyright 2010 Kineo open Source                                         
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
$ADMIN->add('root', new admin_externalpage('cpdrecord', get_string('cpd', 'report_cpd'), "$CFG->wwwroot/$CFG->admin/report/cpd/index.php", 'report/cpd:userview'));

// The capability checks are required to avoid a debug error for non-admins
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
if (has_capability('report/cpd:superadminview', $systemcontext)) {
	$ADMIN->add('modsettings', new admin_externalpage('cpdmetadata', get_string('cpdreportadmin', 'report_cpd'), "$CFG->wwwroot/$CFG->admin/report/cpd/metadata.php", 'report/cpd:superadminview'));
}
if (has_capability('report/cpd:adminview', $systemcontext)) {
	$ADMIN->add('reports', new admin_externalpage('cpdadminview', get_string('cpddevreport', 'report_cpd'), "$CFG->wwwroot/$CFG->admin/report/cpd/adminview.php", 'report/cpd:adminview'));
}

?>
