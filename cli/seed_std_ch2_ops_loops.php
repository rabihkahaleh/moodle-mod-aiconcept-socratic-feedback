<?php
// Seed — Chapter 2 (Operators, Decisions, Loops) transcript into mod_aiconcept tables (per install.xml).
// Usage (web): /mod/aiconcept/cli/seed_std_ch2_ops_loops.php?cmid=123&userid=45
// Usage (CLI): php mod/aiconcept/cli/seed_std_ch2_ops_loops.php --cmid=123 --userid=45
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
// Transcript turns — Chapter 2 (Operators, Decisions, Loops).
// Each row => one message in the dialogue. Role is 'student' or 'assistant'.
// Long text uses nowdoc (<<<'TXT') to avoid escaping issues.
// -----------------------------------------------------------------------------
$T = [];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
Hi Chat. How are u. i am a university student taking programming for engineer on python. i would like u to explain to me chapter to of this course. it is about operators, decision(if, elif) and loops.
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Got it — thanks for letting me know! Before we dive in, let me ask you a quick thing:
👉 How comfortable do you already feel with the basics of Python (like variables, input, and simple print statements)?
That will help me decide whether to start with a quick warm-up or jump right into operators, if/elif, and loops.
Also, do you want me to explain these topics mostly with examples (so you can try them out), or more like a conceptual overview first?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
jump right into it and use examples plz
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Perfect — let’s get into it. We’ll go step by step: operators → decisions → loops.
I’ll show you examples, then I’ll ask you to try a small one so it sticks.

________________________________________
1. Operators
Operators are symbols that let you do operations on values. Main categories:
• Arithmetic: + - * / % // **
a = 10
b = 3
print(a + b)   # 13
print(a / b)   # 3.333...
print(a // b)  # 3 (floor division)
print(a % b)   # 1 (remainder)
print(a ** b)  # 1000 (power)

• Comparison: == != > < >= <=
print(5 > 2)   # True
print(5 == 2)  # False

• Logical: and or not
x = 5
print(x > 0 and x < 10)   # True (both sides true)
print(x < 0 or x > 10)    # False
print(not (x > 0))        # False

________________________________________
2. Decision Making (if, elif, else)
This is where your program makes choices.
age = 20
if age < 13:
    print("Child")
elif age < 20:
    print("Teenager")
else:
    print("Adult")

• Python checks conditions from top to bottom.
• As soon as one condition is true, it runs that block and skips the rest.

________________________________________
3. Loops
Loops repeat code until a condition is met.

• for loop (used when you know how many times to repeat):
for i in range(5):     # range(5) → 0,1,2,3,4
    print("Hello", i)

• while loop (runs until condition becomes false):
count = 0
while count < 3:
    print("Looping...", count)
    count += 1

✅ Quick check for you:
Can you write a for loop that prints only the even numbers between 1 and 10?
(You don’t have to be perfect — I’ll help fix it if needed.)
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
can u explain about for loops more plz
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Of course 👍 let’s slow down and go deeper into for loops.

🔹 What is a for loop?
A for loop is used when you want to repeat a block of code a specific number of times or when you want to go through a sequence (like a list, string, or range of numbers).
In Python, it looks like this:
for variable in sequence:
    # do something
• variable → takes each value from the sequence one by one
• sequence → something iterable (like a list, string, or range)

🔹 Using range()
The most common way engineers use for loops is with range().
• range(stop) → goes from 0 up to stop-1
for i in range(5):  # 0,1,2,3,4
    print(i)

• range(start, stop) → goes from start up to stop-1
for i in range(2, 6):  # 2,3,4,5
    print(i)

• range(start, stop, step) → adds a step (can skip numbers)
for i in range(0, 10, 2):  # 0,2,4,6,8
    print(i)

🔹 Looping through lists
You can also loop through items in a list:
fruits = ["apple", "banana", "cherry"]
for fruit in fruits:
    print(fruit)

Output:
apple
banana
cherry

🔹 Looping through a string
Strings are sequences of characters, so you can loop through them too:
for char in "Hello":
    print(char)

Output:
H
e
l
l
o

🔹 Combining with if
Often we use for loops with if conditions:
for i in range(1, 11):
    if i % 2 == 0:
        print(i, "is even")

👉 Question for you:
Would you like me to also show you nested for loops (loop inside another loop)? They’re useful in engineering when working with matrices, tables, or grids.
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
yes
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Great — let’s dive into nested for loops 🚀

🔹 What is a Nested Loop?
A nested loop means putting one loop inside another loop.
This is very common when working with tables, matrices, or repeated patterns.

General structure:
for outer in sequence1:
    for inner in sequence2:
        # do something with outer and inner

Example 1: Multiplication Table
for i in range(1, 4):        # outer loop
    for j in range(1, 4):    # inner loop
        print(i, "*", j, "=", i * j)
    print("----")

Output:
1 * 1 = 1
1 * 2 = 2
1 * 3 = 3
----
2 * 1 = 2
2 * 2 = 4
2 * 3 = 6
----
3 * 1 = 3
3 * 2 = 6
3 * 3 = 9
----

Notice:
• The outer loop controls the row (the i).
• The inner loop runs fully for each i.

Example 2: Printing a Grid
for row in range(3):         # 3 rows
    for col in range(5):     # 5 columns
        print("*", end=" ")  # print star without newline
    print()                  # move to next line after finishing row

Output:
* * * * *
* * * * *
* * * * *

Example 3: Working with a Matrix (list of lists)
matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
]
for row in matrix:
    for value in row:
        print(value, end=" ")
    print()

Output:
1 2 3
4 5 6
7 8 9

⚙️ Engineers often use nested loops in:
• Simulations (grid-based models)
• Processing images (pixel by pixel)
• Iterating through rows and columns of data

✅ Quick test for you:
Can you write a nested loop that prints a triangle of stars like this?
*
* *
* * *
* * * *
* * * * *
(Want me to guide you step by step, or do you want to try it fully on your own first?)
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
do it for me
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Got it ✅ Here’s how you can print that triangle of stars using a nested loop:

# Number of rows
rows = 5
for i in range(1, rows + 1):     # outer loop → rows
    for j in range(i):           # inner loop → stars per row
        print("*", end=" ")      # print star without new line
    print()                      # new line after each row

Output:
*
* *
* * *
* * * *
* * * * *

⚙️ Explanation:
• for i in range(1, rows + 1) → controls the row number (1 to 5).
• for j in range(i) → prints stars equal to the row number.
• print("*", end=" ") → prints stars on the same line.
• print() → moves to the next line after finishing a row.

👉 Do you want me to also show you how to make the reverse triangle (upside-down)?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
yes
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Perfect 👍 let’s build the reverse triangle (upside-down).

🔹 Code for Reverse Triangle
rows = 5
for i in range(rows, 0, -1):  # start from 5, down to 1
    for j in range(i):        # stars decrease each row
        print("*", end=" ")
    print()                   # move to next line

🔹 Output
* * * * *
* * * *
* * *
* *
*

⚙️ Explanation:
• range(rows, 0, -1) → starts at 5, goes down by 1, stops before 0.
• For each row i, we print i stars.

✨ Bonus: You can also combine both (triangle + reverse triangle) to make a diamond shape.
Want me to show you how to do the diamond pattern too?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
no thank u
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
All good 👍 Got it.
So far we’ve covered:
• Operators (arithmetic, comparison, logical)
• Decision making (if, elif, else)
• Loops (for, while, nested loops, star patterns)
That’s basically the whole Chapter 2 foundation 🎯
👉 Do you want me to make you a short summary sheet (like a cheat sheet) of everything we went over, so you can revise quickly before exams?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
no i'm good tnx
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Awesome! 😄 You’ve got a solid grip on Chapter 2 then.
If you want, next we could move on to practice problems that combine operators, decisions, and loops — that’s the best way to make it stick. Do you want me to do that?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
maybe later
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Perfect 😎 No rush.
For now, just reviewing what we covered and maybe trying a few small examples on your own will really help it stick.
If you want, I can also give you a few tips to avoid common mistakes in loops and if statements, so you don’t run into bugs later. Do you want me to do that?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
no tnx
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Got it! 😄
You’re all set with Chapter 2 then. Keep practicing a bit, and it’ll feel very natural.
If you want, we can move on to Chapter 3 or any other topic whenever you’re ready.
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
what are the common mistakes in loops and if statements
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Ah, perfect! Even if Student Said “no thanks” before, it’s super useful to know these now so you avoid headaches later. Here are the most common mistakes in loops and if statements in Python:

________________________________________
1️⃣ Common Mistakes in if, elif, else

1. Using a single = instead of ==
x = 5
if x = 5:      # ❌ wrong, this is assignment
    print("Yes")
✅ Correct:
if x == 5:     # ✅ comparison
    print("Yes")

2. Not covering all cases
age = 20
if age < 13:
    print("Child")
elif age < 20:
    print("Teenager")
# ❌ If age >= 20, nothing happens
✅ Add else if needed:
else:
    print("Adult")

3. Wrong indentation
if x > 0:
print("Positive")  # ❌ Python needs proper indentation
✅ Correct:
if x > 0:
    print("Positive")

4. Confusing and & or
x = 5
if x > 0 or x < 10:  # ❌ always True, logic may be wrong
    print("Hello")
✅ Be careful with logic:
if x > 0 and x < 10: # ✅ x must satisfy both conditions
    print("Hello")

________________________________________
2️⃣ Common Mistakes in Loops

1. Infinite loops in while
count = 0
while count < 5:
    print(count)
    # ❌ forgot count += 1 → loop never ends

✅ Fix:
count = 0
while count < 5:
    print(count)
    count += 1

2. Off-by-one errors
for i in range(5):  # i goes 0,1,2,3,4
    print(i)
• Sometimes students expect it to include 5 → remember range(stop) stops before stop.

3. Wrong indentation inside loops
for i in range(3):
print(i)            # ❌ needs indentation

4. Modifying the loop variable inside a for loop
for i in range(5):
    i += 1          # ❌ does not affect the loop sequence
    print(i)
• for loop automatically controls i, so changing it inside rarely does what you want.

5. Printing inside wrong place
for i in range(3):
    print(i)
print("Done")       # outside loop

• Sometimes students accidentally put the print inside/outside and get unexpected output.

If you want, I can make a tiny table of “common mistakes & fixes” that you can keep next to your code — it’s really handy while coding.
Do you want me to make that?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
no thank u so much for everything . goodbye
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
You’re very welcome! 😄 I’m glad I could help.
Good luck with your programming course — you’ve got this! 💪
Goodbye and take care! 👋
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
a = 6  # 110 in binary
b = 3  # 011 in binary
print(a & b)   # AND → 2 (010)
print(a | b)   # OR  → 7 (111)
print(a ^ b)   # XOR → 5 (101)
print(~a)      # NOT → -7
print(a << 1)  # Left shift → 12 (1100)
print(a >> 1)  # Right shift → 3 (011)
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Perfect example 👍 You’ve written a small program that shows bitwise operators in Python. Let me walk you through each line so you see why the results happen.

Given:
a = 6  # in binary → 110
b = 3  # in binary → 011

1) a & b (bitwise AND)
Compares each bit — 1 if both bits are 1.
  110   (6)
& 011   (3)
= 010   (2)
✅ Output → 2

2) a | b (bitwise OR)
Compares each bit — 1 if at least one bit is 1.
  110   (6)
| 011   (3)
= 111   (7)
✅ Output → 7

3) a ^ b (bitwise XOR, exclusive OR)
1 if the bits are different.
  110   (6)
^ 011   (3)
= 101   (5)
✅ Output → 5

4) ~a (bitwise NOT)
Flips every bit. In Python, numbers are stored in two’s complement, so:
a = 6 → 000...0110
~a    → 111...1001  (which equals -7)
✅ Output → -7

5) a << 1 (left shift)
Shifts all bits one place to the left (adds a zero at the end).
  110   (6)
<< 1
= 1100  (12)
✅ Output → 12

6) a >> 1 (right shift)
Shifts all bits one place to the right (drops the last bit).
  110   (6)
>> 1
= 011   (3)
✅ Output → 3

⚡ So the final program prints:
2
7
5
-7
12
3

👉 Do you want me to also show you a real-world engineering example where bitwise operators are actually used (like in electronics, sensors, or optimization), or is this binary breakdown enough?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
alright thanks bye
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
You’re very welcome! 😄 Glad I could help.
Good luck with your studies, and bye for now 👋
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
explain continue and break in loops
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Great question — break and continue are two special keywords that give you more control inside loops. 🚀

🔹 1. break
break stops the loop completely — it jumps out of the loop immediately.
Example:
for i in range(1, 10):
    if i == 5:
        break   # exit the loop when i = 5
    print(i)

✅ Output:
1
2
3
4
• When i becomes 5 → loop ends.
• Anything after 5 is skipped.

🔹 2. continue
continue skips the current iteration and jumps back to the next loop cycle.
Example:
for i in range(1, 6):
    if i == 3:
        continue  # skip printing 3
    print(i)

✅ Output:
1
2
4
5
• When i = 3, the loop skips print(i) and moves to the next number.

🔹 3. Combining them
for i in range(1, 10):
    if i % 2 == 0:
        continue   # skip even numbers
    if i > 7:
        break      # stop if number is greater than 7
    print(i)

✅ Output:
1
3
5
7

⚙️ Summary:
• break → exits the loop completely.
• continue → skips only the current iteration.

👉 Do you want me to also show you how break and continue work inside a while loop, or are the for loop examples enough?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
is there any more operators or loop types or any other kind of condition (like if elif...)
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Excellent question 👌 You already know the basics (operators, if/elif/else, for/while loops) — but yes, Python has more operators, variations of loops, and condition tricks. Let me expand step by step:

🔹 1. More Operators
Besides arithmetic, comparison, and logical, Python also has:

Assignment operators (shorthand updates)
x = 5
x += 3   # same as x = x + 3 → 8
x -= 2   # same as x = x - 2 → 6
x *= 4   # same as x = x * 4 → 24

Membership operators
• in → checks if a value is inside a sequence (list, string, etc.)
• not in
print("a" in "cat")        # True
print(3 in [1, 2, 3])      # True
print(4 not in [1, 2, 3])  # True

Identity operators
• is and is not → check if two variables refer to the same object in memory (not just equal value).
a = [1,2,3]; b = [1,2,3]
print(a == b)  # True (values equal)
print(a is b)  # False (different objects)

Bitwise operators (useful in engineering, electronics, low-level work)
a = 6  # 110 in binary
b = 3  # 011 in binary
print(a & b)   # AND → 2 (010)
print(a | b)   # OR  → 7 (111)
print(a ^ b)   # XOR → 5 (101)
print(~a)      # NOT → -7
print(a << 1)  # Left shift → 12 (1100)
print(a >> 1)  # Right shift → 3 (011)

🔹 2. More Loop Features
Python only has two main loop types:
• for
• while
But you can add extra control:

else with loops
Yes! Loops can have an else clause that runs only if the loop finishes normally (without a break).
for i in range(5):
    print(i)
else:
    print("Loop finished without break")

With break:
for i in range(5):
    if i == 3:
        break
    print(i)
else:
    print("Loop finished without break")  # ❌ won’t run

🔹 3. Other Condition Tricks
• Ternary operator (one-line if-else)
age = 20
status = "Adult" if age >= 18 else "Minor"
print(status)

• Chained comparisons
x = 10
print(5 < x < 20)  # True (same as 5 < x and x < 20)

• Match-case (like switch in other languages, Python 3.10+)
day = 3
match day:
    case 1: print("Monday")
    case 2: print("Tuesday")
    case 3: print("Wednesday")
    case _: print("Other day")

✅ So, in summary:
• Operators → also include assignment, membership (in), identity (is), bitwise.
• Loops → only for and while, but you can add else, break, continue.
• Conditions → if/elif/else, one-line if, chained comparisons, and match-case.
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
is there any more?
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Nice follow-up 👌 You’re thinking like a programmer now: “Have I covered everything, or is there still more hidden?”
Here’s the full picture:

🔹 Operators
You now know all categories Python has:
1. Arithmetic → + - * / // % **
2. Comparison → == != > < >= <=
3. Logical → and or not
4. Assignment (shorthand) → +=, -=, *=, /=, //=, %=, **=
5. Membership → in, not in
6. Identity → is, is not
7. Bitwise → & | ^ ~ << >>
✅ That’s it — there are no “hidden” operator types beyond these.

🔹 Conditions
Main tools:
• if, elif, else
• Nested if (if inside another if)
• One-line conditional (ternary):
result = "Yes" if x > 0 else "No"
• Chained comparisons:
if 0 < x < 10:
    print("In range")
• match-case (Python 3.10+): acts like switch in other languages.
✅ That’s the complete set of condition styles in Python.

🔹 Loops
Python is actually simple here — only two kinds of loops:
1. for (usually with range, lists, strings, etc.)
2. while (runs until condition is false)

Extra features:
• break → stop loop early
• continue → skip iteration
• else with loops → runs if loop wasn’t broken
• Nested loops

✅ No do...while like in C/C++/Java. If you need that, you simulate it with while True and a break.

🔹 Bonus Tools That Behave Like Loops/Conditions
These aren’t “new loop types,” but they’re advanced Python ways to loop/decide:
• List comprehensions (compact for + if)
squares = [x**2 for x in range(5) if x % 2 == 0]
print(squares)  # [0, 4, 16]
• Generator expressions (like comprehensions but lazy)
gen = (x**2 for x in range(5))
for value in gen:
    print(value)
• Any / All (conditional checks over collections)
nums = [2, 4, 6]
print(all(x % 2 == 0 for x in nums))  # True (all even)
print(any(x > 5 for x in nums))       # True (some > 5)

✅ So to answer your question:
• You’ve now seen all the operators Python provides.
• You’ve seen all loop types (for, while) + extras (break, continue, else).
• You’ve seen all condition forms (if/elif/else, ternary, chained, match-case).
• Advanced: list comprehensions, generator expressions, any/all.
That’s the complete toolbox. 🎯

👉 Do you want me to highlight which of these are most important for your engineering course right now, and which are extra/advanced that you might meet later?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
yes plz
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Perfect 👍 Let’s separate the essentials (what your engineering Python course will almost surely test you on) from the extras (good to know later).

✅ Most Important for Now (Core Chapter 2 stuff)
You should master these first — they’ll appear in homework, quizzes, and exams.

Operators
• Arithmetic → + - * / // % **
• Comparison → == != > < >= <=
• Logical → and or not
• Assignment (shorthand) → +=, -=, *=, /= (at least these four)

Conditions
• if, elif, else
• Nested if
• Chained comparisons (like 0 < x < 10)

Loops
• for loop (with range)
• while loop
• Nested loops (especially for grids/matrices)
• break and continue

👉 These are the bread-and-butter. Practice them until they feel automatic.

📘 Good to Know (Might Appear Later in Course)
• Membership operators → in, not in
• Identity → is, is not
• for loop with lists/strings (not just range)
• else with loops (less common, but professors love tricky questions 😅)
• Ternary operator → x if cond else y

🚀 Advanced / Optional (Useful Later, Not Critical for Now)
• Bitwise operators → & | ^ ~ << >> (common in electronics/digital logic courses)
• match-case (Python 3.10+, acts like switch)
• List comprehensions → [x**2 for x in range(10) if x % 2 == 0]
• Generator expressions → (x**2 for x in range(10))
• any() / all() built-in functions

⚡ Study strategy for Chapter 2:
1) Focus 90% of your time on the Core set.
2) Be aware of the “Good to Know” ones (might appear in tricky questions).
3) Skip advanced ones for now — unless you’re curious or your professor explicitly covers them.

👉 Do you want me to create a set of short practice problems (easy → medium → tricky) only on the core set, so you can test yourself like in an exam?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
no thanks
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Got it 👍 No worries. You’ve now got the full map of operators, conditions, and loops in Python — and you know which ones really matter for your course right now.
Good luck with your studying 🚀 You’re in a strong position for Chapter 2 already.
Would you like me to be ready to explain Chapter 3 when you start it, or do you prefer to come back when you reach it?
TXT];

$T[] = ['role'=>'student', 'content'=> <<<'TXT'
later
TXT];

$T[] = ['role'=>'assistant', 'content'=> <<<'TXT'
Sounds good 👍 Just drop by whenever you’re ready for Chapter 3.
Good luck with your course, and see you later 👋
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
    'status'       => 'finalsubmitted',
    'finalcode'    => "print('Chapter 2 seed (Operators, Decisions, Loops) loaded')",
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

echo "Seeded Chapter 2 (Operators/Decisions/Loops) as submission #{$submissionid} for user {$userid} in cmid {$cmid}.\n";
