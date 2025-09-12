<?php
require_once(__DIR__ . '/../../config.php');

use mod_aiconcept\local\openai\client as openai_client;

$sid = required_param('sid', PARAM_INT); // submission id.
$action = optional_param('action', '', PARAM_ALPHA);
$note = optional_param('note', '', PARAM_RAW);

$submission = $DB->get_record('aiconcept_submissions', ['id' => $sid], '*', MUST_EXIST);
$cm = get_coursemodule_from_id('aiconcept', $submission->cmid, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('aiconcept', ['id' => $cm->instance], '*', MUST_EXIST);
$user     = $DB->get_record('user', ['id' => $submission->userid], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('moodle/course:manageactivities', $context);

$PAGE->set_url('/mod/aiconcept/instructor_view.php', ['sid' => $sid]);
$PAGE->set_title('Transcript — '.fullname($user));
$PAGE->set_heading(format_string($course->fullname));

// Handle instructor action.
if ($action) {
    require_sesskey();
    $now = time();

    if ($action === 'approve') {
        // Generate KB from the transcript.
        $turns = $DB->get_records('aiconcept_turns', ['submissionid' => $sid], 'id ASC');
        $hist = [];
        foreach ($turns as $t) { $hist[] = ['role' => $t->role, 'content' => $t->content]; }

        $client = new openai_client();
        $kbprompt = "INSTRUCTOR_KB_REQUEST:\nSummarize the dialogue into a student-facing Knowledge Base.\nInclude:\n- List of issues encountered (in order) with a one-line concept for each.\n- Minimal example for each concept (3-5 lines, if useful).\n- The final general rules stated clearly.\n- Transfer tips: where this rule applies elsewhere.\nReturn a concise markdown checklist.";
        $kbresp = $client->respond($kbprompt, $hist);
        $kbtext = trim($kbresp['text'] ?? '');

        // Update submission.
        $submission->reviewstatus = 'approved';
        $submission->status       = 'approved';
        $submission->reviewby     = $USER->id;
        $submission->reviewtime   = $now;
        $submission->reviewnote   = $note;
        $submission->kb_text      = $kbtext;
        $submission->timemodified = $now;
        $DB->update_record('aiconcept_submissions', $submission);

        // Send KB back as an assistant turn (student will see it in history).
        if ($kbtext !== '') {
            $DB->insert_record('aiconcept_turns', (object)[
                'submissionid' => $sid,
                'role'         => 'assistant',
                'content'      => "✅ Instructor approved.\n\n**Knowledge Base**\n\n".$kbtext,
                'status'       => 'ok',
                'timecreated'  => $now
            ]);
        } else {
            $DB->insert_record('aiconcept_turns', (object)[
                'submissionid' => $sid,
                'role'         => 'assistant',
                'content'      => "✅ Instructor approved.\n\nA summary will follow.",
                'status'       => 'ok',
                'timecreated'  => $now
            ]);
        }

        redirect(new moodle_url('/mod/aiconcept/instructor_view.php', ['sid' => $sid]), 'Approved & KB sent.', 2);

    } else if ($action === 'reject' || $action === 'revise') {
        $submission->reviewstatus = ($action === 'reject') ? 'rejected' : 'revise';
        $submission->status       = ($action === 'reject') ? 'rejected' : 'inprogress';
        $submission->reviewby     = $USER->id;
        $submission->reviewtime   = $now;
        $submission->reviewnote   = $note;
        $submission->timemodified = $now;
        $DB->update_record('aiconcept_submissions', $submission);

        // Notify student in the thread.
        $msg = ($action === 'reject')
            ? "❌ Instructor rejected this submission." 
            : "📝 Instructor requests a revision.";
        if (!empty($note)) { $msg .= "\n\nInstructor note:\n".$note; }

        $DB->insert_record('aiconcept_turns', (object)[
            'submissionid' => $sid,
            'role'         => 'assistant',
            'content'      => $msg,
            'status'       => 'ok',
            'timecreated'  => $now
        ]);

        redirect(new moodle_url('/mod/aiconcept/instructor_view.php', ['sid' => $sid]), 'Decision saved.', 2);
    }
}

// Render page.
echo $OUTPUT->header();

// Header.
echo html_writer::div(
    html_writer::span('Transcript — '.fullname($user), 'title') .
    html_writer::empty_tag('br') .
    html_writer::span('Submission status: ' . s($submission->status) . ' / Review: ' . s($submission->reviewstatus), 'meta'),
    'aiconcept-hero'
);

// Action form.
echo html_writer::start_div('aiconcept-card');
echo html_writer::tag('h4', 'Moderation');

$url = new moodle_url('/mod/aiconcept/instructor_view.php', ['sid' => $sid]);
echo html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false)]);
echo html_writer::input_hidden_params($url);
echo html_writer::input_hidden_params(new moodle_url('', ['sesskey' => sesskey()]));
echo html_writer::tag('textarea', s($submission->reviewnote ?? ''), ['name' => 'note', 'rows' => 4, 'style' => 'width:100%;border-radius:8px;']);
echo html_writer::div(
    html_writer::empty_tag('input', ['type'=>'submit','class'=>'btn btn-success','name'=>'action','value'=>'approve']) . ' ' .
    html_writer::empty_tag('input', ['type'=>'submit','class'=>'btn btn-warning','name'=>'action','value'=>'revise']) . ' ' .
    html_writer::empty_tag('input', ['type'=>'submit','class'=>'btn btn-danger','name'=>'action','value'=>'reject'])
, 'aiconcept-actions');
echo html_writer::end_tag('form');
echo html_writer::end_div();

// Conversation.
echo html_writer::start_div('aiconcept-card');
echo html_writer::tag('h4', 'Conversation');

echo html_writer::start_div('aiconcept-history');
$turns = $DB->get_records('aiconcept_turns', ['submissionid' => $sid], 'id ASC');
foreach ($turns as $t) {
    $role = ($t->role === 'assistant') ? 'assistant' : 'student';
    echo html_writer::start_div('aiconcept-msg '.$role);
    echo html_writer::div(
        html_writer::div(($role==='assistant'?'Assistant':'Student'), 'role') .
        s($t->content),
        'bubble'
    );
    echo html_writer::end_div();
}
echo html_writer::end_div();

// Final code preview.
if (!empty($submission->finalcode)) {
    echo html_writer::tag('h4', 'Final code');
    echo html_writer::tag('pre', s($submission->finalcode), ['style' => 'background:#0f172a;color:#e5e7eb;border-radius:12px;padding:12px;overflow:auto;']);
}
echo html_writer::end_div();

echo $OUTPUT->footer();
