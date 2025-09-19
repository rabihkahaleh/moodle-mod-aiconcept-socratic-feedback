<?php
// Seed the STD2 — Charles transcript into mod_aiconcept tables (per install.xml).
// Web: /mod/aiconcept/cli/seed_std2_charles.php?cmid=123&userid=45
// CLI: php mod/aiconcept/cli/seed_std2_charles.php --cmid=123 --userid=45
//
// Requires: admin/teacher capability in that CM when run via web.

define('NO_OUTPUT_BUFFERING', true);
require_once(__DIR__ . '/../../../config.php');

$cmid   = optional_param('cmid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

// Accept CLI flags too.
if (PHP_SAPI === 'cli') {
    define('CLI_SCRIPT', true);
    $opts = getopt('', ['cmid:', 'userid:']);
    if (!empty($opts['cmid']))   { $cmid   = (int)$opts['cmid']; }
    if (!empty($opts['userid'])) { $userid = (int)$opts['userid']; }
}

if (!$cmid || !$userid) {
    echo "Missing cmid or userid. Provide ?cmid=XX&userid=YY or --cmid= --userid=\n";
    exit(1);
}

// Fetch CM / course / instance.
$cm       = get_coursemodule_from_id('aiconcept', $cmid, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('aiconcept', ['id' => $cm->instance], '*', MUST_EXIST);

// Web authz.
if (PHP_SAPI !== 'cli') {
    require_login($course, false, $cm);
    $context = context_module::instance($cm->id);
    require_capability('moodle/course:manageactivities', $context);
}

// ---------------- Transcript (STD2 — Charles) ----------------
$T = [];

// identifiers / safe names
$T[] = ['role'=>'student','content'=>'sorry i meant 2 and 4'];
$T[] = ['role'=>'assistant','content'=>'No problem! That still works. ✅'];
$T[] = ['role'=>'student','content'=>'main and student are safe'];
$T[] = ['role'=>'assistant','content'=>'Yes, both main and student are valid identifiers in Python.'];

// indentation
$T[] = ['role'=>'assistant','content'=>"Next is Lines and Indentation. Example:\n\nif age >= 18:\n    print(\"Adult\")"];
$T[] = ['role'=>'student','content'=>"if age >= 18:\nprint(\"Adult\")"];
$T[] = ['role'=>'assistant','content'=>'Exactly! Indent the body. It will print "Adult" if age >= 18.'];

// strings / slicing
$T[] = ['role'=>'assistant','content'=>"Strings & slicing demo:\nword = \"Python\"\nprint(word[0:4])  # Pyth"];
$T[] = ['role'=>'student','content'=>'in slicing you split it from 0 to 4 and got index 0 to 3 why'];
$T[] = ['role'=>'assistant','content'=>"Right—the end index is exclusive.\nword[1:4] -> 'yth'\nword[-3:] -> 'hon'"];

// lists
$T[] = ['role'=>'assistant','content'=>"Lists are ordered, mutable:\nfruits=[\"apple\",\"banana\",\"cherry\"]\nfruits[1]=\"blueberry\"\nfruits.append(\"orange\")"];
$T[] = ['role'=>'student','content'=>"colors=[\"red\",\"green\",\"blue\"]\ncolors[1]=\"yellow\""];
$T[] = ['role'=>'assistant','content'=>'Great—now do colors.append("purple") and print(colors[1]).'];
$T[] = ['role'=>'student','content'=>"colors.append(\"purple\")\nprint(colors[1])"];
$T[] = ['role'=>'assistant','content'=>'Perfect! List is now ["red","yellow","blue","purple"].'];

// tuples
$T[] = ['role'=>'student','content'=>"person[1] is 30.\nwe cannot change it to 31 because it is immutable"];
$T[] = ['role'=>'assistant','content'=>"Exactly—that's a tuple, immutable after creation."];

// dictionaries
$T[] = ['role'=>'assistant','content'=>"Dictionaries:\nstudent={\"name\":\"Bob\",\"grade\":90}\nstudent[\"grade\"]=95\nstudent[\"subject\"]=\"Math\"\nstudent.keys()"];
$T[] = ['role'=>'student','content'=>'yes'];
$T[] = ['role'=>'assistant','content'=>'Perfect. ✅'];

// sets
$T[] = ['role'=>'student','content'=>"colors={\"red\",\"blue\",\"green\"}\ncolors.add(\"yellow\")\ncolors.remove(\"blue\")\nprint(\"red\" in colors)"];
$T[] = ['role'=>'assistant','content'=>'Exactly! Correct usage of sets.'];

// casting
$T[] = ['role'=>'assistant','content'=>"Casting examples: float(\"45.6\"), set([1,2,2,3]), str(99).\nAlso int(), float(), str(), list(), tuple(), set()."];
$T[] = ['role'=>'student','content'=>"float(\"45.6\")\nset([1, 2, 2, 3])\nstring(99)"];
$T[] = ['role'=>'assistant','content'=>'Almost perfect—use str(99), not string(99).'];

// wrap-up
$T[] = ['role'=>'student','content'=>'Now I’ve gone through all topics'];
$T[] = ['role'=>'assistant','content'=>'Exactly! Covered identifiers, indentation, comments, strings, lists, tuples, dictionaries, sets, and casting.'];

// ------------ Insert (per install.xml) ------------
$now = time();

$submission = (object)[
    'course'       => $cm->course,
    'cmid'         => $cm->id,
    'userid'       => $userid,
    'assignmentid' => 0,
    'status'       => 'finalsubmitted',
    'finalcode'    => "student={\"name\":\"Bob\",\"grade\":90}\nstudent[\"grade\"]=95\nstudent[\"subject\"]=\"Math\"\nprint(student)",
    'timecreated'  => $now,
    'timemodified' => $now
];
$submissionid = $DB->insert_record('aiconcept_submissions', $submission);

// Write turns with spacing
$offset = 0;
$model  = get_config('mod_aiconcept', 'openai_model'); // optional
foreach ($T as $t) {
    $turn = (object)[
        'submissionid' => $submissionid,
        'role'         => ($t['role'] === 'assistant') ? 'assistant' : 'student',
        'content'      => (string)$t['content'],
        'model'        => empty($model) ? null : $model,
        'usage_json'   => null,
        'status'       => 'ok',
        'timecreated'  => $now + $offset
    ];
    $DB->insert_record('aiconcept_turns', $turn);
    $offset += 30;
}

echo "Seeded STD2 — Charles as submission #{$submissionid} for user {$userid} in cmid {$cmid}.\n";
