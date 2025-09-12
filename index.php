<?php
require('../../config.php');
$id = required_param('id', PARAM_INT);
$course = get_course($id);
require_login($course);
$PAGE->set_url('/mod/aiconcept/index.php', ['id' => $id]);
$PAGE->set_title(get_string('modulenameplural', 'mod_aiconcept'));
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_aiconcept'));
// Simple list of modules.
$modules = get_all_instances_in_course('aiconcept', $course);
if (empty($modules)) {
    echo $OUTPUT->notification('No instances found.');
} else {
    echo html_writer::start_tag('ul');
    foreach ($modules as $m) {
        $url = new moodle_url('/mod/aiconcept/view.php', ['id' => $m->coursemodule]);
        echo html_writer::tag('li', html_writer::link($url, format_string($m->name)));
    }
    echo html_writer::end_tag('ul');
}
echo $OUTPUT->footer();
