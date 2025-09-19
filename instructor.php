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
require_capability('moodle/course:manageactivities', $context);

$PAGE->set_url('/mod/aiconcept/instructor.php', ['id' => $id]);
$PAGE->set_title(format_string($instance->name).' — Instructor dashboard');
$PAGE->set_heading(format_string($course->fullname));

$sql = "SELECT s.id, s.userid, s.status, s.timecreated, s.timemodified,
               (SELECT COUNT(1) FROM {aiconcept_turns} t WHERE t.submissionid = s.id) AS turns,
               (SELECT COUNT(1) FROM {aiconcept_turns} t WHERE t.submissionid = s.id AND t.role='assistant') AS assistant_turns
        FROM {aiconcept_submissions} s
        WHERE s.cmid = :cmid
        ORDER BY s.timemodified DESC";
$subs = $DB->get_records_sql($sql, ['cmid' => $cm->id]);

$rows = [];
foreach ($subs as $s) {
    $u = $DB->get_record('user', ['id' => $s->userid], 'id,firstname,lastname,email', MUST_EXIST);
    $rows[] = [
        'id'        => (int)$s->id,
        'name'      => fullname($u),
        'email'     => s($u->email),
        'turns'     => (int)$s->turns,
        'assistant' => (int)$s->assistant_turns,
        'status'    => s($s->status ?? 'inprogress'),
        'statusUC'  => ucfirst($s->status ?? 'inprogress'),
        'updated'   => userdate($s->timemodified),
        'viewurl'   => (new moodle_url('/mod/aiconcept/instructor_view.php', ['sid' => $s->id]))->out(false),
    ];
}

$data = ['name' => format_string($instance->name), 'rows' => $rows];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_aiconcept/instructor_dashboard', $data);
echo $OUTPUT->footer();
