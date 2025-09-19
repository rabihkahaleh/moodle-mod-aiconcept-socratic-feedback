<?php
// Seed the STD1 — Ricardo transcript into mod_aiconcept tables (per install.xml).
// Usage (web): /mod/aiconcept/cli/seed_std1_ricardo.php?cmid=123&userid=45
// Usage (CLI): php mod/aiconcept/cli/seed_std1_ricardo.php --cmid=123 --userid=45
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

// -----------------------------------------------------------------------------
// Transcript turns (STD1 — Ricardo). Each row => one message in the dialogue.
// Role must be 'student' or 'assistant'. Content can include code fences.
// -----------------------------------------------------------------------------

$T = [];

// (From STD1: opening—student wants a Python path and topics.) 
$T[] = ['role' => 'student', 'content' =>
"I want to learn Python from the beginning—can you help me? Please include: identifiers, reserved words, lines and indentations, quotations and comments, variable types, numbers, string, list, tuple, dictionary, set, and data type conversion."
];

$T[] = ['role' => 'assistant', 'content' =>
"I can guide you step by step and include all those topics. We'll start from the basics and build up to data structures and type conversion. We will cover:\n\n- Identifiers & Reserved Words\n- Lines & Indentation\n- Quotations & Comments\n- Variable Types & Numbers\n- Strings\n- Lists\n- Tuples\n- Dictionaries\n- Sets\n- Data Type Conversion\n\nDo you want me to start with **Identifiers & Reserved Words** first?"
];

// Student mentions Java background.
$T[] = ['role' => 'student', 'content' =>
"I have taken Java before."
];

$T[] = ['role' => 'assistant', 'content' =>
"Great! Then you already know variables, loops, and conditionals. We'll connect Python ideas to your Java knowledge along the way."
];

// Ask for overview then proceed topic-by-topic.
$T[] = ['role' => 'student', 'content' => "Can you give me an overview first?"];
$T[] = ['role' => 'assistant', 'content' =>
"Overview for a Java learner:\n\n- Python is **interpreted**; you can run code line-by-line without compiling.\n- Python is **dynamically typed** (no explicit type declarations needed).\n- Blocks use **indentation** instead of `{}`.\n- Rich built-ins: **list, tuple, dict, set**.\n- OOP supported (like Java).\n- `if`, `for`, `while` are simpler syntactically.\n\nWe’ll go topic by topic, mapping to your Java knowledge."
];

$T[] = ['role' => 'student', 'content' => "Yes—start topic by topic."];

// Identifiers & reserved words.
$T[] = ['role' => 'assistant', 'content' =>
"**Identifiers & Reserved Words**\n- Start with a letter or underscore; can include letters, digits, underscores; case-sensitive.\n- Reserved words (e.g., `if`, `while`, `for`) cannot be identifiers."
];
$T[] = ['role' => 'student', 'content' => "main and student are safe."];
$T[] = ['role' => 'assistant', 'content' => "Correct—neither is reserved."];
// Lines & indentation.
$T[] = ['role' => 'assistant', 'content' =>
"**Lines & Indentation**\n```python\nif age >= 18:\n    print(\"Adult\")\n```\nIndentation defines the block; consistent spacing is mandatory."
];
$T[] = ['role' => 'student', 'content' =>
"```python\nif age >= 18:\nprint(\"Adult\")\n```"
];
$T[] = ['role' => 'assistant', 'content' =>
"Good conditional—remember the body must be indented."
];

// Quotations & comments.
$T[] = ['role' => 'assistant', 'content' =>
"**Quotations & Comments**\n```python\n# This is a comment\nmsg = '\"Python is fun!\"'\n```\nSingle, double, or triple quotes are fine."
];
$T[] = ['role' => 'student', 'content' => "yes"];

// Strings.
$T[] = ['role' => 'assistant', 'content' =>
"**Strings** support indexing/slicing/methods.\n```python\nmsg = \"Hello\"\nprint(msg[0])   # H\nprint(msg[-1])  # o\n```\nSlicing is start-inclusive, end-exclusive."
];
$T[] = ['role' => 'student', 'content' =>
"In slicing you split 0..4 and got 0..3—why?"
];
$T[] = ['role' => 'assistant', 'content' =>
"End index is exclusive:\n```python\ntext = \"Python\"\nprint(text[0:4])  # Pyth\n```"
];

// Lists.
$T[] = ['role' => 'assistant', 'content' =>
"**Lists** (mutable):\n```python\ncolors = [\"red\", \"green\", \"blue\"]\ncolors[1] = \"yellow\"\ncolors.append(\"purple\")\nprint(colors[1])\n```"
];
$T[] = ['role' => 'student', 'content' =>
"```python\ncolors.append(\"purple\")\nprint(colors[1])\n```"
];
$T[] = ['role' => 'assistant', 'content' => "Exactly—`append` adds at the end; index 1 is the second element."];

// Tuples.
$T[] = ['role' => 'assistant', 'content' =>
"**Tuples** (immutable):\n```python\nnumbers = (1,2,3)\ninfo = (\"Alice\", 25, \"Engineer\")\n```\nWhy tuples? Protect data; usable as dict keys.\nWhat is `person[1]` and can you set it?"
];
$T[] = ['role' => 'student', 'content' =>
"`person[1]` is 30. We cannot set it—tuples are immutable."
];
$T[] = ['role' => 'assistant', 'content' => "Exactly."];

// Dictionaries (this is where the misconception occurs in STD1).
$T[] = ['role' => 'assistant', 'content' =>
"**Dictionaries** (key → value):\n```python\nperson = {\"name\":\"Alice\",\"age\":25}\nperson[\"age\"] = 26\nperson[\"job\"] = \"Engineer\"\nprint(person.keys())\n```"
];
$T[] = ['role' => 'student', 'content' =>
"```python\nstudent = {\n  \"name\": \"Bob\",\n  \"grade\": 90\n}\nperson[\"grade\"] = 95\nperson[\"subject\"] = \"Math\"\nperson.keys()\n```"
];

// Compiler-style detection + Socratic repair.
$T[] = ['role' => 'assistant', 'content' =>
"[System Compiler Output]\n----------------------------------------\nTraceback (most recent call last):\n  File \"submission.py\", line 4, in <module>\n    person[\"grade\"] = 95\nNameError: name 'person' is not defined\n\n[Detection & Tagging]\n- Misconception tag: DICT-ALIAS-MISMATCH\n- Evidence: variable `person` not declared; mismatch with `student`.\n- Confidence: high\n\n**Understand**: Why did `person[\"grade\"] = 95` fail?"
];
$T[] = ['role' => 'student', 'content' =>
"Because I created `student` but then used `person`."
];
$T[] = ['role' => 'assistant', 'content' =>
"Fix it by using the correct dictionary name."
];
$T[] = ['role' => 'student', 'content' =>
"```python\nstudent = {\"name\":\"Bob\",\"grade\":90}\nstudent[\"grade\"] = 95\nstudent[\"subject\"] = \"Math\"\nprint(student.keys())\n```"
];
$T[] = ['role' => 'assistant', 'content' =>
"[System Compiler Output]\n----------------------------------------\ndict_keys(['name','grade','subject'])\n\n**Analyze**: What changed compared to the first attempt?"
];
$T[] = ['role' => 'student', 'content' => "I used `student` consistently."];

// Sets.
$T[] = ['role' => 'assistant', 'content' =>
"**ALM: Sets — Uniqueness**\nPredict the output:\n```python\ns = set()\ns.add(1); s.add(2); s.add(2)\nprint(s)\n```"
];
$T[] = ['role' => 'student', 'content' =>
"{1, 2} because sets only keep unique elements."
];
$T[] = ['role' => 'assistant', 'content' =>
"Correct. Apply: remove duplicates with a set.\n```python\nnums = [1,1,2,3,3]\nunique_nums = set(nums)\nprint(unique_nums)\n```"
];

// Type conversion.
$T[] = ['role' => 'assistant', 'content' =>
"**Casting / Constructors**\nConvert values:\n- `float(\"45.6\")`\n- `set([1,2,2,3])`\n- `str(99)` (not `string(99)`)."
];
$T[] = ['role' => 'student', 'content' =>
"Got it: `float(\"45.6\")`, `set([1,2,2,3])`, `str(99)`."
];

// Closeout
$T[] = ['role' => 'assistant', 'content' =>
"Nice work—topics covered: identifiers, indentation, comments, strings, lists, tuples, dictionaries (fixed alias mismatch), sets (uniqueness), and type conversion. ✅"
];

// -----------------------------------------------------------------------------
// Insert into DB per install.xml (aiconcept_submissions / aiconcept_turns).
// -----------------------------------------------------------------------------

$now = time();

$submission = (object)[
    'course'       => $cm->course,
    'cmid'         => $cm->id,
    'userid'       => $userid,
    'assignmentid' => 0,
    'status'       => 'Approved', // you can show this as "complete" in UI
    'finalcode'    => "student = {\"name\":\"Bob\",\"grade\":90}\nstudent[\"grade\"] = 95\nstudent[\"subject\"] = \"Math\"\nprint(student)",
    'timecreated'  => $now,
    'timemodified'  => $now
];

$submissionid = $DB->insert_record('aiconcept_submissions', $submission);

// Write turns with 30-second spacing to preserve order.
$offset = 0;
$model  = get_config('mod_aiconcept', 'openai_model'); // if you use it
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

echo "Seeded STD1 — Ricardo as submission #{$submissionid} for user {$userid} in cmid {$cmid}.\n";
