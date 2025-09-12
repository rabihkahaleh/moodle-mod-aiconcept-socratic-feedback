<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$cm       = get_coursemodule_from_id('aiconcept', $id, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('aiconcept', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

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

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_aiconcept/student_dashboard', $data);
echo $OUTPUT->footer();
