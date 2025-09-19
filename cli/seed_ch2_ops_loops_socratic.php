<?php
// Seed a Socratic, gated (Understand → Apply → Analyze → Create) Chapter 2 transcript
// into mod_aiconcept tables (per install.xml).
//
// Usage (web): /mod/aiconcept/cli/seed_ch2_ops_loops_socratic.php?cmid=123&userid=45
// Usage (CLI): php mod/aiconcept/cli/seed_ch2_ops_loops_socratic.php --cmid=123 --userid=45
//
// Tables per install.xml:
//   aiconcept_submissions(id, course, cmid, userid, assignmentid, status, finalcode, timecreated, timemodified)
//   aiconcept_turns(id, submissionid, role, content, model, usage_json, status, timecreated)
//
// IMPORTANT: Every message body is a NOWDOC (<<<'Txx') and the label (Txx) appears
//            ALONE on a line to close the block—no spaces, no text after it.

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
// Conversation turns (Socratic, gated). Role: 'student' | 'assistant'
// -----------------------------------------------------------------------------
$T = [];

// Opening
$T[] = ['role'=>'student', 'content'=> <<<'T01'
Hi Chat. How are u. i am a university student taking programming for engineer on python. i would like u to explain to me chapter two of this course. it is about operators, decision(if, elif) and loops.
T01];

$T[] = ['role'=>'assistant', 'content'=> <<<'T02'
We’ll use a structured, gated flow: **Understand → Apply → Analyze → Create**.
⚠️ You must complete each stage to progress. Topics: Operators → Decisions → Loops.
T02];

// -------------------- OPERATORS (U) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T03'
**Operators — Understand (U)**
Predict outputs and name each operator category:

```python
a = 10
b = 3
print(a + b)     # ?
print(a // b)    # ?
print(a % b)     # ?
print(a ** b)    # ?
print(5 > 2)     # ?
print(5 == 2)    # ?
x = 5
print(x > 0 and x < 10)  # ?
```

Answer in words (not just numbers): addition, floor division, remainder, power, comparisons, logical AND.
T03];

$T[] = ['role'=>'student', 'content'=> <<<'T04'
Addition → 13, floor division → 3, remainder → 1, power → 1000, 5>2 → True, 5==2 → False, last → True (x is between 0 and 10).
T04];

// -------------------- OPERATORS (A) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T05'
**Operators — Apply (A)**
Write a snippet that:
1) Assigns two integers `p` and `q`
2) Prints `p ** q`
3) Prints whether `p` is divisible by `q`
4) Prints whether `0 < p < 100` using a chained comparison
T05];

$T[] = ['role'=>'student', 'content'=> <<<'T06'
```python
p = 12
q = 5
print(p ** q)
print(p % q == 0)
print(0 < p < 100)
```
T06];

// -------------------- OPERATORS (An) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T07'
**Operators — Analyze (An)**
Why is `p % q == 0` a correct divisibility check? Why is `0 < p < 100` clearer than `(p > 0 and p < 100)`?
T07];

$T[] = ['role'=>'student', 'content'=> <<<'T08'
`p % q` is the remainder; divisibility means remainder 0. Chained comparisons are shorter and less error-prone than two comparisons with `and`.
T08];

// -------------------- OPERATORS (C) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T09'
**Operators — Create (C)**
Create a bitwise demo (AND, OR, XOR, NOT, <<, >>) with comments.
T09];

$T[] = ['role'=>'student', 'content'=> <<<'T10'
```python
a = 6   # 110
b = 3   # 011
print(a & b)  # 2
print(a | b)  # 7
print(a ^ b)  # 5
print(~a)     # -7
print(a << 1) # 12
print(a >> 1) # 3
```
T10];

$T[] = ['role'=>'assistant', 'content'=> <<<'T11'
✅ Operators complete (U → A → An → C). Next: Decisions.
T11];

// -------------------- DECISIONS (U) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T12'
**Decisions — Understand (U)**
Given:

```python
age = 20
if age < 13:
    print("Child")
elif age < 20:
    print("Teenager")
else:
    print("Adult")
```

Which branch runs and why?
T12];

$T[] = ['role'=>'student', 'content'=> <<<'T13'
`else` runs and prints "Adult" because age is not <13 and not <20.
T13];

// -------------------- DECISIONS (A) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T14'
**Decisions — Apply (A)**
Grading rules:
- 90–100 → "A"
- 80–89 → "B"
- 70–79 → "C"
- else → "Needs improvement"
Use chained comparisons.
T14];

$T[] = ['role'=>'student', 'content'=> <<<'T15'
```python
score = 84
if 90 <= score <= 100:
    print("A")
elif 80 <= score <= 89:
    print("B")
elif 70 <= score <= 79:
    print("C")
else:
    print("Needs improvement")
```
T15];

// -------------------- DECISIONS (An) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T16'
**Decisions — Analyze (An)**
Why do chained comparisons reduce mistakes versus `score >= 80 and score <= 89`?
T16];

$T[] = ['role'=>'student', 'content'=> <<<'T17'
They remove duplication and are harder to mistype; it reads as one continuous range.
T17];

// -------------------- DECISIONS (C) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T18'
**Decisions — Create (C)**
Create a `status` classifier:
- temp < 0 → "Freezing"
- 0–25 → "Cool"
- 26–35 → "Warm"
- >35 → "Hot"
T18];

$T[] = ['role'=>'student', 'content'=> <<<'T19'
```python
temp = 28
if temp < 0:
    print("Freezing")
elif temp <= 25:
    print("Cool")
elif temp <= 35:
    print("Warm")
else:
    print("Hot")
```
T19];

$T[] = ['role'=>'assistant', 'content'=> <<<'T20'
✅ Decisions complete (U → A → An → C). Next: Loops.
T20];

// -------------------- LOOPS (U) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T21'
**Loops — Understand (U)**
What does each loop output?

```python
for i in range(1, 6):
    if i % 2 == 0:
        print("even", i)

count = 0
while count < 3:
    print("loop", count)
    count += 1
```
T21];

$T[] = ['role'=>'student', 'content'=> <<<'T22'
First: "even 2", "even 4".
Second: "loop 0", "loop 1", "loop 2".
T22];

// -------------------- LOOPS (A) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T23'
**Loops — Apply (A)**
Write a `for` loop that prints only even numbers between 1 and 10.
T23];

$T[] = ['role'=>'student', 'content'=> <<<'T24'
```python
for i in range(1, 11):
    if i % 2 == 0:
        print(i)
```
T24];

// -------------------- LOOPS (An) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T25'
**Loops — Analyze (An)**
Why is `range(1, 11)` the correct bound to include 10? Explain the off-by-one risk with `range`.
T25];

$T[] = ['role'=>'student', 'content'=> <<<'T26'
`range` stops before the upper bound, so `range(1, 11)` includes 10. Off-by-one happens when you forget the stop is exclusive.
T26];

// -------------------- LOOPS (C) --------------------
$T[] = ['role'=>'assistant', 'content'=> <<<'T27'
**Loops — Create (C)**
Print a right triangle of stars, then the reverse triangle, using nested loops.
T27];

$T[] = ['role'=>'student', 'content'=> <<<'T28'
```python
rows = 5
for i in range(1, rows+1):
    for j in range(i):
        print("*", end=" ")
    print()

for i in range(rows, 0, -1):
    for j in range(i):
        print("*", end=" ")
    print()
```
T28];

$T[] = ['role'=>'assistant', 'content'=> <<<'T29'
✅ Loops complete (U → A → An → C). You finished all gated stages.
T29];

// -----------------------------------------------------------------------------
// Insert records
// -----------------------------------------------------------------------------
$now = time();

$finalcode = <<<'FCODE'
# Quick demo collected from the session:

# Operators (bitwise)
a = 6; b = 3
print(a & b, a | b, a ^ b, ~a, a << 1, a >> 1)

# Decisions
score = 84
if 90 <= score <= 100:
    print("A")
elif 80 <= score <= 89:
    print("B")
elif 70 <= score <= 79:
    print("C")
else:
    print("Needs improvement")

# Loops (evens 1..10)
for i in range(1, 11):
    if i % 2 == 0:
        print(i)
FCODE;

$submission = (object)[
    'course'       => $cm->course,
    'cmid'         => $cm->id,
    'userid'       => $userid,
    'assignmentid' => 0,
    'status'       => 'finalsubmitted',
    'finalcode'    => $finalcode,
    'timecreated'  => $now,
    'timemodified' => $now
];

$submissionid = $DB->insert_record('aiconcept_submissions', $submission);

// Write turns with 25-second spacing to preserve order.
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
    $offset += 25;
}

echo "Seeded Chapter 2 (Socratic gated) as submission #{$submissionid} for user {$userid} in cmid {$cmid}.\n";
