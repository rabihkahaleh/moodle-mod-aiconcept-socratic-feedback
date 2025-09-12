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

// Fetch most recent submission for this user in this CM.
$submission = $DB->get_records('aiconcept_submissions',
    ['cmid' => $cm->id, 'userid' => $USER->id], 'timemodified DESC', '*', 0, 1);
$submission = $submission ? reset($submission) : null;

$status        = $submission->status        ?? 'inprogress';
$reviewstatus  = $submission->reviewstatus  ?? 'none';
$kbtext        = (string)($submission->kb_text   ?? '');
$finalcode     = (string)($submission->finalcode ?? '');

$data = [
    'name'          => format_string($instance->name),
    'cmid'          => (int)$cm->id,
    'status'        => $status,
    'statusUC'      => ucfirst($status),
    'instructorurl' => has_capability('moodle/course:manageactivities', $context)
                        ? (new moodle_url('/mod/aiconcept/instructor.php', ['id' => $cm->id]))->out(false)
                        : null,
    'history'       => [] // AMD will load turns via WS.
];

// Initialise student chat AMD.
$PAGE->requires->js_call_amd('mod_aiconcept/student_chat', 'init', [[
    'cmid' => (int)$cm->id,
    'submissionid' => (int)($submission->id ?? 0)
]]);

// Small inline CSS (keeps things self-contained).
$inlinecss = <<<CSS
<style>
.aiconcept-card { background:#111827; border:1px solid #1f2937; border-radius:14px; padding:16px; margin:16px 0; }
.aiconcept-card h4 { margin:0 0 8px; color:#e5e7eb; }
.aiconcept-kb-actions .btn { margin-right:8px; }
.aiconcept-kb pre, .aiconcept-code pre {
  white-space:pre-wrap; background:#0f172a; color:#e5e7eb; border-radius:12px; padding:12px; overflow:auto;
}
.aiconcept-banner {
  background:#0b3d2e; border:1px solid #146c43; color:#d1fae5; border-radius:12px; padding:10px 12px; margin:8px 0;
}
.aiconcept-banner.pending { background:#2a1a00; border-color:#a16207; color:#fde68a; }
</style>
CSS;
echo $inlinecss;

echo $OUTPUT->header();

// Optional friendly banners.
if ($reviewstatus === 'pending') {
    echo html_writer::div('Your final code is submitted and pending instructor approval.', 'aiconcept-banner pending');
}
if ($reviewstatus === 'approved') {
    echo html_writer::div('✅ Instructor approved this submission. Your Knowledge Base is available below.', 'aiconcept-banner');
}

// Main dashboard (your Mustache template).
echo $OUTPUT->render_from_template('mod_aiconcept/student_dashboard', $data);

// Knowledge Base panel (only when approved and KB exists).
if ($kbtext !== '' && $reviewstatus === 'approved') {
    echo html_writer::start_div('aiconcept-card aiconcept-kb');
    echo html_writer::tag('h4', 'Knowledge Base');

    // Actions row.
    echo html_writer::start_div('aiconcept-kb-actions');
    echo html_writer::tag('button', 'Copy', ['id' => 'kb-copy-btn', 'class' => 'btn btn-secondary']);
    echo html_writer::tag('button', 'Download (.md)', ['id' => 'kb-dl-btn', 'class' => 'btn btn-link']);
    echo html_writer::end_div();

    // Content.
    echo html_writer::tag('pre', s($kbtext), ['id' => 'kb-pre']);
    echo html_writer::end_div();

    // Inline JS for copy & download (Blob).
    $copydljs = <<<JS
(function(){
  var pre = document.getElementById('kb-pre');
  var copyBtn = document.getElementById('kb-copy-btn');
  var dlBtn = document.getElementById('kb-dl-btn');
  if (copyBtn && pre) {
    copyBtn.addEventListener('click', function(){
      var txt = pre.innerText || '';
      navigator.clipboard.writeText(txt).then(()=>{
        var old = copyBtn.textContent; copyBtn.textContent='Copied!';
        setTimeout(()=>copyBtn.textContent=old, 1200);
      });
    });
  }
  if (dlBtn && pre) {
    dlBtn.addEventListener('click', function(){
      var txt = pre.innerText || '';
      var blob = new Blob([txt], {type:'text/markdown;charset=utf-8'});
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      a.download = 'KB_submission_' + (#{(int)($submission->id ?? 0)}) + '.md';
      document.body.appendChild(a);
      a.click();
      setTimeout(function(){ URL.revokeObjectURL(url); a.remove(); }, 100);
    });
  }
})();
JS;
    $PAGE->requires->js_init_code($copydljs);
}

// Final code preview (if present).
if ($finalcode !== '') {
    echo html_writer::start_div('aiconcept-card aiconcept-code');
    echo html_writer::tag('h4', 'Final code (your last submitted version)');
    echo html_writer::tag('pre', s($finalcode));
    echo html_writer::end_div();
}

echo $OUTPUT->footer();
