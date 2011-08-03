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
 * Defines the CPD Activity form
 *
 * @package   admin-report-cpd
 * @copyright 2010 Kineo open Source
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class edit_activity_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'addactivity', get_string('addactivity', 'report_cpd'));
        $mform->addElement('hidden', 'cpdyearid', $this->_customdata['cpdyearid']);
        $mform->addElement('hidden', 'process', '1');
        if ($this->_customdata['cpdid']) {
            // This updates a CPD Report
            $mform->addElement('hidden', 'id', $this->_customdata['cpdid']);
        }

        $mform->addElement(
            'textarea',
            'objective',
            get_string('objective', 'report_cpd'),
            array('rows'=>'2', 'cols'=>'40')
        );
        $mform->addElement(
            'textarea',
            'development_need',
            get_string('developmentneed', 'report_cpd'),
            array('rows'=>'2', 'cols'=>'40')
        );

        if ($this->_customdata['activity_types']) {
            $mform->addElement(
                'select',
                'activitytypeid',
                get_string('activitytype', 'report_cpd'),
                $this->_customdata['activity_types']
            );
        }
        $mform->addElement(
            'textarea',
            'activity',
            get_string('activityplanned', 'report_cpd'),
            array('rows'=>'2', 'cols'=>'40')
        );

        // Get CPD start and end years
        $startyear = date('Y') - 5;
        $endyear = date('Y') + 5;
        if ($this->_customdata['cpdyear']) {
            $startyear = date('Y', $this->_customdata['cpdyear']->startdate);
            $endyear = date('Y', $this->_customdata['cpdyear']->enddate);
        }

        for ($i=1; $i<=31; $i++) {
            $days[$i] = $i;
        }
        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 15, 2000), "%B");
        }
        for ($i=$startyear; $i<=$endyear; $i++) {
            $years[$i] = $i;
        }

        $startdate[] = $mform->createElement('select', 'startdate[d]', '', $days);
        $startdate[] = $mform->createElement('select', 'startdate[m]', '', $months);
        $startdate[] = $mform->createElement('select', 'startdate[Y]', '', $years);
        $mform->addGroup(
            $startdate,
            'startdate',
            get_string('datestart', 'report_cpd'),
            array(' '),
            false
        );

        $duedate[] = $mform->createElement('select', 'duedate[d]', '', $days);
        $duedate[] = $mform->createElement('select', 'duedate[m]', '', $months);
        $duedate[] = $mform->createElement('select', 'duedate[Y]', '', $years);
        $mform->addGroup(
            $duedate,
            'duedate',
            get_string('dateend', 'report_cpd'),
            array(' '),
            false
        );

        if ($this->_customdata['statuses']) {
            $mform->addElement(
                'select',
                'statusid',
                get_string('status', 'report_cpd'),
                $this->_customdata['statuses']
            );
        }

        $hours = null;
        $minutes = null;
        for ($i=1; $i<=20; $i++) {
            $hours[$i] = $i;
        }
        for ($i=15; $i<=45; $i+=15) {
            $minutes[$i] = $i;
        }
        $timetaken[] = $mform->createElement('select', 'timetaken[hours]', '', array('' => '--') + $hours);
        $timetaken[] = $mform->createElement('select', 'timetaken[minutes]', '', array('' => '--') + $minutes);
        $mform->addGroup($timetaken, 'timetaken', get_string('timetaken', 'report_cpd'), ':', false);

        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('update'));
        $buttonarray[] = $mform->createElement('reset', '', get_string('clear', 'report_cpd'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        //$mform->addRule('objective', 'Please enter an objective.', 'required', null, 'client');
    }

}
