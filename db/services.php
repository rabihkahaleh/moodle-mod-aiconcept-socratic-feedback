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

$functions = [
    'mod_aiconcept_submit_and_respond' => [
        'classname'   => 'mod_aiconcept\external\submit',
        'methodname'  => 'submit_and_respond',
        'classpath'   => '',
        'description' => 'Submit code, get GPT feedback, and log turn.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'mod/aiconcept:submit'
    ],
    'mod_aiconcept_fetch_turns' => [
        'classname'   => 'mod_aiconcept\external\submit',
        'methodname'  => 'fetch_turns',
        'classpath'   => '',
        'description' => 'Fetch conversation turns for a user in an activity.',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> 'mod/aiconcept:submit'
    ]
];
$services = [];
