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

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('modsettingaiconcept', get_string('pluginname', 'mod_aiconcept'));
    $ADMIN->add('modsettings', $settings);

    // API key (masked).
    $settings->add(new admin_setting_configpasswordunmask(
        'mod_aiconcept/openai_api_key',
        get_string('openai_api_key', 'mod_aiconcept'),
        get_string('openai_api_key_desc', 'mod_aiconcept'),
        ''
    ));

    // Model (default to your allowed model).
    $settings->add(new admin_setting_configtext(
        'mod_aiconcept/openai_model',
        get_string('openai_model', 'mod_aiconcept'),
        get_string('openai_model_desc', 'mod_aiconcept'),
        'gpt-3.5-turbo-0125',
        PARAM_TEXT
    ));

    // Streaming toggle (not used unless you implement SSE).
    $settings->add(new admin_setting_configcheckbox(
        'mod_aiconcept/enable_streaming',
        get_string('enable_streaming', 'mod_aiconcept'),
        get_string('enable_streaming_desc', 'mod_aiconcept'),
        0
    ));

    // Default Socratic/system prompt.
    $defaultprompt = <<<PROMPT
You are a pedagogy-aware Python tutor for novices.

Goals: foster conceptual change and independent problem solving (not just fix code).

Dialogue rules (Socratic):
1) State ONE-sentence hypothesis of the misconception (e.g., list alias vs copy, iterator vs index, off-by-one, missing recursion base case, Boolean logic, scope).
2) Ask ONE probing question that requires prediction, comparison, or explanation.
3) Offer ONE tiny, testable nudge (no full solution). Prefer a minimal runnable snippet.
4) After the learner replies, reference their output/trace and prompt reflection.
5) Gate progress with the ALM cycle: UNDERSTAND → APPLY → ANALYZE → CREATE.
6) At CREATE, ask them to state the general rule in their own words and a transfer case.

Constraints: be concise (≤6 lines), supportive, and avoid full solutions unless they say “final check”.
PROMPT;

    $settings->add(new admin_setting_configtextarea(
        'mod_aiconcept/system_prompt',
        get_string('system_prompt', 'mod_aiconcept'),
        get_string('system_prompt_desc', 'mod_aiconcept'),
        $defaultprompt,
        PARAM_RAW,
        60,
        12
    ));
}

// settings.php (at the bottom)
$settings->add(new admin_setting_heading(
    'mod_aiconcept/eval_notice',
    get_string('pluginname', 'mod_aiconcept'),
    html_writer::div(
        'This research prototype is licensed for academic evaluation only. ' .
        'Commercial use and redistribution are prohibited. See LICENSE_EVALUATION.txt.',
        'alert alert-info'
    )
));

