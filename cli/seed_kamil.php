<?php
// Seed the STD5 — Kamil conversation into mod_aiconcept tables (per install.xml).
// Usage (web): /mod/aiconcept/cli/seed_std5_kamil.php?cmid=123&userid=45
// Usage (CLI): php mod/aiconcept/cli/seed_std5_kamil.php --cmid=123 --userid=45
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
// Transcript turns (STD5 — Kamil). Each row => one message in the dialogue.
// Role must be 'student' or 'assistant'. Long content uses nowdoc to avoid escapes.
// -----------------------------------------------------------------------------
$T = [];

// 1) Student opens (identifiers request)
$T[] = ['role' => 'student', 'content' => <<<'TXT'
i would like to learn about python, can you give me the first chapter students learn about python. try to talk about the identifiers.
TXT];

// 2) Assistant: Chapter 1 intro + Identifiers
$T[] = ['role' => 'assistant', 'content' => <<<'TXT'
Perfect place to start 🚀 — Python begins with the basics of identifiers, keywords, variables, and simple syntax.
Let’s treat this like it’s Chapter 1 of a Python course.

📘 Chapter 1: Introduction to Python and Identifiers

🔹 What is Python?
• Python is a high-level, interpreted, general-purpose programming language.
• Known for being easy to read, write, and learn because its syntax is close to natural English.
• Used in web development, data science, AI, automation, and more.

When you install Python, you can run it in two main ways:
1) Interactive mode (REPL): you type commands one by one.
2) Script mode: you save code in a .py file and run it.

1.1 Identifiers
🔹 Definition
An identifier is the name used to identify variables, functions, classes, modules, or objects in Python.

Examples:
x = 10
name = "Alice"
total_sum = 500
Here x, name, and total_sum are identifiers.

🔹 Rules for Identifiers
1) Can contain letters (A–Z, a–z), digits (0–9), and underscore (_)
   Examples: student1, my_variable, _hiddenValue
2) Must not start with a digit
   ❌ 2name (invalid)   ✅ name2 (valid)
3) No special characters allowed (@, $, %, # are not allowed)
   ❌ user-name        ✅ user_name
4) Case-sensitive (value, Value, and VALUE are different)
5) Cannot use Python keywords (reserved words like if, while, for, class).
   ❌ if = 5           ✅ if_count = 5

🔹 Examples
# Valid identifiers
age = 21
student_name = "Kamil"
number123 = 999
_total = 50   # starts with underscore is okay

# Invalid identifiers
2ndPlace = "Ali"      # ❌ starts with digit
first-name = "Sara"   # ❌ contains hyphen
class = "Math"        # ❌ reserved keyword

1.2 Best Practices for Identifiers
• Use descriptive names → marks is better than m.
• Use snake_case for variables/functions → student_score, calculate_area()
• Use PascalCase for class names → BankAccount, CarModel
• Avoid single letters unless for short loops → for i in range(10):
TXT];

// 3) Student: continue with reserved words, indentation, quotations, comments
$T[] = ['role' => 'student', 'content' => <<<'TXT'
continue with the chapter but talk about reserved words, lines and indentation, quotations and comments.
TXT];

// 4) Assistant: reserved words, lines/indentation, quotations, comments + exercises
$T[] = ['role' => 'assistant', 'content' => <<<'TXT'
📘 Chapter 1 (continued): Python Basics

1.3 Reserved Words (Keywords)
Reserved words have predefined meanings; you cannot use them as identifiers.
Examples: if, else, while, for, class, try, except, def, return, import, True, False, None
(There are ~35 keywords; varies by version.)

Example:
class = "Math"        # ❌ Invalid (class is a keyword)
class_name = "Math"   # ✅ Valid

List them in your interpreter:
import keyword
print(keyword.kwlist)

1.4 Lines and Indentation
• Each line usually represents one statement.
• A statement can span multiple lines using a backslash (\).

# Single line
x = 10

# Multi-line using backslash
y = 1 + 2 + 3 + \
    4 + 5

• Python uses indentation (spaces or tabs) instead of braces { } to define blocks.

if True:
    print("This is indented")  # part of if-block
print("Outside the block")     # not indented → outside

⚠️ Incorrect indentation causes errors:
if True:
print("Hello")   # ❌ Error: expected an indented block

✅ Best practice: use 4 spaces (not tabs).

1.5 Quotations (Strings)
• Single quotes → 'hello'
• Double quotes → "hello"
• Triple quotes → '''hello''' or """hello"""

s1 = 'Hello'
s2 = "World"
s3 = '''This is a multi-line string.'''

1.6 Comments
• Single-line: use #
# This is a single-line comment
x = 10  # comment after code

• Multi-line: use triple quotes as a convention (unassigned string literal)
""" This is a multi-line comment-like block. """

✅ Quick Exercise
1) Which is NOT a valid string?
   a) 'Python'   b) "Python"   c) '''Python'''   d) ""Python""
2) Rewrite with proper indentation:
   if 5 > 2: print("Five is greater than two!")
TXT];

// 5) Student: anything else about previous topics?
$T[] = ['role' => 'student', 'content' => <<<'TXT'
if there is anything else i need to know about the previous topics state it now.
TXT];

// 6) Assistant: extra notes & best practices
$T[] = ['role' => 'assistant', 'content' => <<<'TXT'
🔎 Extra Notes (Identifiers, Keywords, Indentation, Quotes, Comments)

Identifiers
• Unicode allowed (e.g., π = 3.14159), but prefer English + snake_case.
• Conventions: _var (protected), __var (name-mangled), __var__ (special “dunder” names).

Keywords
• Most are lowercase; True, False, None are capitalized.
• Avoid shadowing built-ins (e.g., list = [1,2,3]) — you lose list().

Indentation
• Use a colon (:) after statements that open a block: if/for/while/def/class:
• Don’t mix tabs and spaces; configure your editor for spaces.

Quotations
• Escape sequences: \n (newline), \t (tab), \" (quote), \\ (backslash).
quote = "She said, \"Python is fun!\""
path  = "C:\\Users\\Kamil"
• Raw strings to ignore escapes: r"C:\Users\Kamil"

Comments & Docstrings
def greet(name):
    """This function greets the user by name."""
    return f"Hello, {name}!"

help(greet)  # Reads docstring

✅ Summary so far
• Naming correctly, recognizing reserved words, writing/indenting code blocks,
  composing strings, and documenting with comments/docstrings.
TXT];

// 7) Student: jump to variable types
$T[] = ['role' => 'student', 'content' => <<<'TXT'
yes jump to variable types now
TXT];

// 8) Assistant: variables & data types
$T[] = ['role' => 'assistant', 'content' => <<<'TXT'
📘 Chapter 1 (continued): Variables and Data Types

1.7 Variables
• Names bound to values in memory; Python is dynamically typed.

x = 10         # int
name = "Ali"   # str
pi = 3.14      # float

• Reassignment can change type:
x = 5
x = "Hello"    # now str

• Multiple assignment:
a, b, c = 10, 20, 30
x = y = z = 100

1.8 Data Types (basics)
• int, float, complex
• bool (True/False)
• str (strings)
• NoneType (None)

1.9 Type Conversion
# implicit
x = 5; y = 2.0
z = x + y        # 7.0 (float)

# explicit
a = int(3.7)     # 3
b = float(5)     # 5.0
c = str(10)      # "10"

1.10 Checking Types
type(10)                 # <class 'int'>
isinstance(3.14, (int, float))  # True

✅ Exercise
Determine types for:
a = 10
b = 3.14
c = "42"
d = True
e = None
TXT];

// 9) Student: continue with numbers & string
$T[] = ['role' => 'student', 'content' => <<<'TXT'
Can you continue and talk about numbers and string
TXT];

// 10) Assistant: numbers & strings
$T[] = ['role' => 'assistant', 'content' => <<<'TXT'
📘 Chapter 1 (continued): Numbers and Strings

Numbers
• int, float, complex
z = 3 + 4j
print(z.real, z.imag)

Common ops:
a = 10; b = 3
a + b, a - b, a * b, a / b, a // b, a % b, a ** b

Built-ins:
abs(-10), pow(2,5), round(3.14159,2), max(...), min(...)
Advanced: import math

Strings
s1 = 'Hello'
s2 = "World"
s3 = '''This is a multi-line string.'''

Ops:
a = "Hello"; b = "World"
a + " " + b
a * 3
len(a)

Indexing/Slicing:
name = "Python"
name[0], name[-1], name[0:3]

Methods:
text = " hello world "
text.upper(); text.lower(); text.strip()
text.replace("world","Python")
text.split()

Formatting:
name="Kamil"; age=21
f"My name is {name} and I am {age}"
"My name is {} and I am {}".format(name, age)
"My name is %s and I am %d" % (name, age)

Escapes:
"Line1\nLine2"
"Hello\tWorld"
"She said: \"Yes!\""

✅ Exercises
1) Area of circle from radius (use math.pi)
2) Given your first name string:
   - print first char
   - last two chars
   - uppercase
   - repeat 5 times
TXT];

// 11) Student: learn lists deeply
$T[] = ['role' => 'student', 'content' => <<<'TXT'
I now would like to learn more about list and go into it well enough to understand it fully
TXT];

// 12) Assistant: Lists (Chapter 2)
$T[] = ['role' => 'assistant', 'content' => <<<'TXT'
📘 Chapter 2: Python Lists

2.1 Lists are ordered, mutable sequences.
fruits = ["apple", "banana", "cherry"]
numbers = [1, 2, 3, 4, 5]
mixed = [1, "hello", 3.14, True]

2.2 Creating
list1 = [10, 20, 30]
list2 = []
list3 = list((1, 2, 3))

2.3 Accessing
fruits[0], fruits[-1]
numbers[1:4], numbers[:3], numbers[3:], numbers[::2]

2.4 Modifying
fruits[1] = "mango"
fruits.append("orange")
fruits.insert(1, "grape")
a = [1,2,3]; b = [4,5]; a.extend(b)

2.5 Removing
fruits.remove("banana")
p = fruits.pop(1)
fruits.clear()

2.6 Helpers
len(nums), min(nums), max(nums), sum(nums)
nums.reverse(); nums.sort()
nums.sort(reverse=True)

2.7 Iteration
for fruit in fruits: ...
for i in range(len(fruits)): ...

2.8 Nested
matrix = [[1,2,3],[4,5,6],[7,8,9]]
matrix[0][1]  # 2

2.9 List comprehensions
[x**2 for x in range(6)]
[x for x in range(10) if x % 2 == 0]

2.10 Copying (important!)
a = [1,2,3]
b = a       # alias (same list)
b[0] = 99   # also changes a
b = a.copy()  # real copy

✅ Exercises
• 5 favorite movies: replace 3rd, append new, remove first
• Numbers 1–10: print evens, reverse, squares via comprehension
TXT];

// 13) Student: what else with lists
$T[] = ['role' => 'student', 'content' => <<<'TXT'
What other things are used woth list
TXT];

// 14) Assistant: Lists continued (membership, enumerate, sorting, slicing tricks, unpacking, zip, filtering)
$T[] = ['role' => 'assistant', 'content' => <<<'TXT'
📘 Chapter 2 (continued): More About Lists

2.11 Membership
"apple" in fruits; "orange" not in fruits

2.12 + and *
[1,2,3] + [4,5]
[1,2,3] * 3

2.13 Built-ins
len, min, max, sum

2.14 enumerate
for i, v in enumerate(fruits): ...

2.15 Sorting
nums.sort()
nums.sort(reverse=True)
new_nums = sorted(nums)
words.sort(key=len)

2.16 Slicing tricks
numbers[::-1]      # reverse
numbers[1:5:2]     # step

2.17 Safe copies
a.copy(), list(a), a[:]

2.18 2D lists
matrix[1][2]

2.19 Comprehensions
squares = [x**2 for x in range(6)]
evens   = [x for x in range(10) if x % 2 == 0]
matrix  = [[r*c for c in range(3)] for r in range(3)]

2.20 Advanced
a, b, c = [1,2,3]
a, *mid, b = [1,2,3,4,5]
for n, s in zip(["Alice","Bob","Charlie"], [85,90,95]):
    print(f"{n} scored {s}")
positive = [x for x in [10,-5,20,-3,7] if x > 0]

✅ Exercises
• For [5,3,8,6,7,2]: sort ascending, >5, reverse via slicing
• Zip students & grades and print "Name got X"
TXT];

// 15) Student: continue tuples, sets, dicts
$T[] = ['role' => 'student', 'content' => <<<'TXT'
Now continue about tuples sets and dictionary also in detail to fully understand them
TXT];

// 16) Assistant: Chapter 3 — tuples, sets, dictionaries
$T[] = ['role' => 'assistant', 'content' => <<<'TXT'
📘 Chapter 3: Tuples, Sets, and Dictionaries

3.1 Tuples (ordered, immutable)
numbers = (1,2,3)
fruits = ("apple","banana","cherry")
t1 = (7,)          # single element
t2 = tuple([4,5,6])
t[0], t[-1], t[1:3]
point = (3,4); x,y = point

3.2 Sets (unordered, unique)
s = {1,2,3,3,2}    # {1,2,3}
s1 = {1,2,3}
s2 = set([3,4,5])
empty = set()      # {} is dict
s.add(4); s.remove(2); s.discard(10)
a={1,2,3,4}; b={3,4,5,6}
a|b, a&b, a-b, a^b
list(set([1,2,2,3,4,4,5]))   # dedupe
2 in s, 5 in s

3.3 Dictionaries (key → value)
student = {"name":"Ali","age":21,"gpa":3.7}
student["name"]
student.get("grade","N/A")
student["age"] = 22
student["major"] = "CS"
student.pop("gpa"); del student["major"]
person = {"name":"Sara","age":20,"city":"Beirut"}
for k,v in person.items(): print(k,":",v)
squares = {x: x**2 for x in range(5)}

✅ Summary
• Tuples → ordered, immutable
• Sets   → unique, unordered
• Dicts  → key–value, fast lookup

✅ Exercises
• Tuple (name, age, GPA) and unpack it
• Sets: common hobbies between two sets
• Dict grades: read/update/add/print items
TXT];

// 17) Student: quick differences/common functions
$T[] = ['role' => 'student', 'content' => <<<'TXT'
Quickly give me the differences and the common functions for all
TXT];

// 18) Assistant: comparison table (text)
$T[] = ['role' => 'assistant', 'content' => <<<'TXT'
📝 Python Collections: Comparison & Functions (summary)

Ordered?            List: Yes   Tuple: Yes   Set: No     Dict: Yes (3.7+)
Mutable?            Yes         No          Yes         Yes
Duplicates allowed? Yes         Yes         No          Keys: No, Values: Yes
Index/Slice?        Yes         Yes         No          Key lookup only
Creation            [..]        (..)        {..} / set() {"a":1}
Add items           append/insert/extend    —           add/update      d[k]=v
Remove items        pop/remove/clear        —           remove/discard  pop/del/clear
Iteration           for x in list           for x in tup for x in set   for k or k,v in d.items()
Reverse/Sort        reverse/sort/sorted     —           —               —
Length/Membership   len(x), item in seq     len(x), in   len(x), in     len(d), key in d
Other helpers       min/max/sum             min/max/sum  union/inter/diff  keys/values/items/get

Key Takeaways:
• Lists → most flexible, ordered, mutable
• Tuples → immutable, lightweight, fast
• Sets → unique elements, math ops, fast membership
• Dicts → key–value mapping, very fast lookups

Want a printable cheat sheet next?
TXT];

// -----------------------------------------------------------------------------
// Insert into DB per install.xml (aiconcept_submissions / aiconcept_turns).
// -----------------------------------------------------------------------------
$now = time();

$submission = (object)[
    'course'       => $cm->course,
    'cmid'         => $cm->id,
    'userid'       => $userid,
    'assignmentid' => 0,
    'status'       => 'finalsubmitted', // consistent with earlier seeds
    'finalcode'    => "print('Kamil seed: Chapter 1–3 walkthrough loaded')",
    'timecreated'  => $now,
    'timemodified' => $now
];

$submissionid = $DB->insert_record('aiconcept_submissions', $submission);

// Write turns with 30-second spacing to preserve order.
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

echo "Seeded STD5 — Kamil as submission #{$submissionid} for user {$userid} in cmid {$cmid}.\n";
