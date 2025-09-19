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

$sid = required_param('sid', PARAM_INT);

$submission = $DB->get_record('aiconcept_submissions', ['id' => $sid], '*', MUST_EXIST);
$cm = get_coursemodule_from_id('aiconcept', $submission->cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Student can download their own; teachers/managers can download any.
if ($submission->userid != $USER->id && !has_capability('mod/aiconcept:viewlogs', $context) && !has_capability('moodle/course:manageactivities', $context)) {
    throw new required_capability_exception($context, 'mod/aiconcept:viewlogs', 'nopermissions', '');
}

$kb = (string)($submission->kb_text ?? '');
if ($kb === '') {
    print_error('Nothing to download.');
}

// Prepare a temp file and send it.
$filename = 'KB_submission_'.$submission->id.'.md';
$tempdir = make_temp_directory('aiconcept');
$tempfile = $tempdir . '/' . $filename;

file_put_contents($tempfile, $kb); // UTF-8 by default.

\send_temp_file($tempfile, $filename); // This exits.
