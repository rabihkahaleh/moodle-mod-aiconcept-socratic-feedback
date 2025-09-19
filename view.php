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

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$cm       = get_coursemodule_from_id('aiconcept', $id, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('aiconcept', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);


// Optional flag to let teachers preview the student view.
$asstudent = optional_param('asstudent', 0, PARAM_BOOL);

// If the user can manage the activity (teacher/manager) OR has viewlogs,
// and they did not request to view as student, send them to the instructor dashboard.
if (!$asstudent && (
        has_capability('moodle/course:manageactivities', $context) ||
        has_capability('mod/aiconcept:viewlogs', $context)
    )) {
    redirect(new moodle_url('/mod/aiconcept/instructor.php', ['id' => $cm->id]));
}


$PAGE->set_url('/mod/aiconcept/view.php', ['id' => $id]);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));

$submission = $DB->get_records('aiconcept_submissions',
    ['cmid' => $cm->id, 'userid' => $USER->id], 'timemodified DESC', '*', 0, 1);
$submission = $submission ? reset($submission) : null;
$status = $submission->status ?? 'inprogress';

$data = [
    'name'          => format_string($instance->name),
    'cmid'          => (int)$cm->id,
    'status'        => $status,
    'statusUC'      => ucfirst($status),
    'instructorurl' => has_capability('moodle/course:manageactivities', $context)
                        ? (new moodle_url('/mod/aiconcept/instructor.php', ['id' => $cm->id]))->out(false)
                        : null,
    'history'       => [] // AMD will load turns via WS; leave empty initially.
];

$PAGE->requires->js_call_amd('mod_aiconcept/student_chat', 'init', [[
    'cmid' => (int)$cm->id,
    'submissionid' => (int)($submission->id ?? 0)
]]);

// Small icon:
$iconurl = $OUTPUT->image_url('icon', 'mod_aiconcept');

// Larger mark (monologo):
$logourl = $OUTPUT->image_url('monologo', 'mod_aiconcept');

echo html_writer::div(
    html_writer::empty_tag('img', ['src'=>$logourl, 'alt'=>'AI Concept', 'class'=>'ac-logo']) .
    html_writer::tag('span', format_string($instance->name), ['class'=>'ac-logo-title']),
    'ac-logo-wrap'
);



echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_aiconcept/student_dashboard', $data);
echo $OUTPUT->footer();
