<?php
/**
 * Socratic Code Coach (mod_aiconcept)
 * Academic Evaluation Only — Non-Commercial, No Redistribution
 * This research prototype accompanies the manuscript:
 * “Design and Prototype Evaluation of an AI-Augmented Programming Education Tool.”
 *
 * @package   mod_aiconcept
 * @license   Academic Evaluation License v1.0 (see LICENSE_EVALUATION.txt)
 * @copyright 2025 Rabih Kahaleh
 */

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
