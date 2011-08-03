<?php

class report_cpd_renderer extends plugin_renderer_base {

    public function disclaimer() {

        $name = new html_table_cell(get_string('confirmstatement', 'report_cpd'));
        $name->attributes['class'] = 'name';
        $fill1 = new html_table_cell('');
        $fill1->attributes['class'] = 'fillbox';
        $date = new html_table_cell(get_string('date'));
        $date->attributes['class'] = 'date';
        $fill2 = new html_table_cell('');
        $fill2->attributes['class'] = 'fillbox date';

        $row = new html_table_row(array($name, $fill1, $date, $fill2));

        $table = new html_table();
        $table->attributes = array(
            'class' => 'disclaimer',
            'border' => 0,
            'cellspacing' => 5,
            'cellpadding' => 0
        );
        $table->data[] = $row;

        return html_writer::table($table);

    }


    /**
     * Prints a button used to print a CPD Report
     *
     * @param string $page Page name
     * @param object $filter_data Current filter data
     */
    public function print_button($link) {
        $attrs = array('class' => 'cpd_print_button');
        return $this->output->single_button($link, get_string('print', 'report_cpd'), 'post', $attrs);
    }


    public function export_controls($page, $filter_data) {

        $downloadparams = (array)$filter_data + array('download' => 1);
        $printparams = (array)$filter_data + array('print' => 1);
        $downloadlink = new moodle_url($page->url->out_omit_querystring(), $downloadparams);
        $printlink = new moodle_url($page->url->out_omit_querystring(), $printparams);

        $strexport = get_string('exportcsv', 'report_cpd');
        $output = $this->output->single_button($downloadlink, $strexport, null, null, false);
        $output .= $this->print_button($printlink, $filter_data);

        return $output;
    }

    public function error_box($messages) {

        $output = implode(html_writer::empty_tag('br'), $messages);
        $output = $this->output->box($output, 'box errorbox errorboxcontent');

        return $output;
    }

    public function settings_form($type, $items, $edit = '', $range = false, $sort = false) {
        global $PAGE;
        $output = '';
        $output .= $this->output->heading(get_string($type, 'report_cpd'), 3, 'cpd_settingshead');
        $url = $PAGE->url;

        $list = '';
        if ($items) {
            foreach ($items as $item) {
                if ($range) {
                    $strrange = date("d/m/Y", $item->startdate)." - ".date("d/m/Y", $item->enddate);
                    $listitem = html_writer::tag('span', $strrange);
                } else {
                    $listitem = html_writer::tag('span', $item->name);
                }
                if ($sort) {
                    $icon = $this->output->pix_icon('t/up', get_string('up'));
                    $urlparams = array('moveup' => $type, 'id' => $item->id);
                    $listitem .= html_writer::link($url->out(false, $urlparams), $icon);
                    $icon = $this->output->pix_icon('t/down', get_string('down'));
                    $urlparams = array('movedown' => $type, 'id' => $item->id);
                    $listitem .= html_writer::link($url->out(false, $urlparams), $icon);
                }
                $icon = $this->output->pix_icon('t/edit', get_string('edit'));
                $urlparams = array('edit' => $type, 'id' => $item->id);
                $listitem .= html_writer::link($url->out(false, $urlparams), $icon);
                $icon = $this->output->pix_icon('t/delete', get_string('delete'));
                $urlparams = array(
                    'delete' => $type,
                    'id' => $item->id,
                );
                $linkparams = array(
                    'class' => 'cpd_delete',
                    'onclick' => 'return confirm("'.get_string('confirmdelete', 'report_cpd').'");'
                );
                $listitem .= html_writer::link($url->out(false, $urlparams), $icon, $linkparams);
                $list .= html_writer::tag('li', $listitem);
            }
        }
        $output .= html_writer::tag('ul', $list, array('class' => 'cpd_itemlist'));

        $addorupdate = isset($edit) ? get_string('update') : get_string('addnew', 'report_cpd');
        $form = $this->output->heading($addorupdate.' '.get_string($type, 'report_cpd'), 4);

        $value = '';
        if (!empty($edit)) {
            if ($range) {
                $params = array('type' => 'hidden', 'name' => 'frmid', 'value' => $edit->id);
                $form .= html_writer::empty_tag('input', $params);
                $value = array('start' => $edit->startdate, 'end' => $edit->enddate);
            } else {
                $params = array('type' => 'hidden', 'name' => 'frmid', 'value' => $edit->id);
                $form .= html_writer::empty_tag('input', $params);
                $value = $edit->name;
            }
        } else if ($range) {
            $value = array('start' => time(), 'end' => time());
        }

        $params = array('type' => 'hidden', 'name' => 'process', 'value' => $type);
        $form .= html_writer::empty_tag('input', $params);

        if ($range) {
            $startselector = html_writer::label(get_string('start', 'report_cpd'), 'startday', true).':';
            $startselector .= html_writer::select_time('days', 'startday', $value['start']);
            $startselector .= html_writer::select_time('months', 'startmonth', $value['start']);
            $startselector .= html_writer::select_time('years', 'startyear', $value['start']);
            $form .= $this->output->container($startselector);

            $endselector = html_writer::label(get_string('end', 'report_cpd'), 'endday', true).':';
            $endselector .= html_writer::select_time('days', 'endday', $value['end']);
            $endselector .= html_writer::select_time('months', 'endmonth', $value['end']);
            $endselector .= html_writer::select_time('years', 'endyear', $value['end']);
            $form .= $this->output->container($endselector);
        } else {
            $params = array('type' => 'text', 'name' => $type, 'value' => $value);
            $form .= html_writer::empty_tag('input', $params);
        }

        $form .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => $addorupdate));

        $params = array('class' => 'cpd_itemform', 'method' => 'post', 'name' => 'frmactivitytype');
        $output .= html_writer::tag('form', $form, $params);
        return $output;
    }
}

?>
