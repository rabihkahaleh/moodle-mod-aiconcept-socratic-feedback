<?php
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
