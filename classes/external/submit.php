<?php
namespace mod_aiconcept\external;

defined('MOODLE_INTERNAL') || die();

// Needed for external_api and parameter/return types.
require_once($CFG->libdir . '/externallib.php');

use context_module;
use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use mod_aiconcept\local\openai\client as openai_client;

class submit extends external_api {

    // -------- Submit & respond --------

    public static function submit_and_respond_parameters() {
        return new external_function_parameters([
            'cmid'         => new external_value(PARAM_INT, 'Course module id'),
            'submissionid' => new external_value(PARAM_INT, 'Existing submission id (0 to create new)', VALUE_DEFAULT, 0),
            'studentcode'  => new external_value(PARAM_RAW, 'Student code or message (use "FINAL_SUBMISSION:\\n..." to submit final code)'),
        ]);
    }

   public static function submit_and_respond($cmid, $submissionid, $studentcode) {
        global $DB, $USER;

        $cm = get_coursemodule_from_id('aiconcept', $cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/aiconcept:submit', $context);

        $time = time();
        // Ensure a submission row.
        if (empty($submissionid)) {
            $submission = (object)[
                'course'        => $cm->course,
                'cmid'          => $cmid,
                'userid'        => $USER->id,
                'assignmentid'  => 0,
                'status'        => 'inprogress',
                'reviewstatus'  => 'none',
                'finalcode'     => null,
                'timecreated'   => $time,
                'timemodified'  => $time
            ];
            $submissionid = $DB->insert_record('aiconcept_submissions', $submission);
        }

        // Build minimal history.
        $turns = $DB->get_records('aiconcept_turns', ['submissionid' => $submissionid], 'id ASC');
        $history = [];
        foreach ($turns as $t) {
            $history[] = ['role' => $t->role, 'content' => $t->content];
        }

        $isfinal = false;
        $finalcode = null;
        $studentmsg = $studentcode;

        // Treat "FINAL_SUBMISSION:" specially.
        if (stripos($studentcode, 'FINAL_SUBMISSION:') === 0) {
            $isfinal = true;
            $finalcode = trim(substr($studentcode, strlen('FINAL_SUBMISSION:')));
            $studentmsg = "[Final submission uploaded]\n" . $finalcode;
        }

        // Log student turn.
        $DB->insert_record('aiconcept_turns', (object)[
            'submissionid' => $submissionid,
            'role'         => 'student',
            'content'      => $studentmsg,
            'status'       => 'ok',
            'timecreated'  => $time
        ]);

        // Call OpenAI.
        $client = new openai_client();
        $resp = $client->respond($studentcode, $history);
        $text = $resp['text'] ?? '';

        // If final, parse model stance and flip to pending review.
        $append = '';
        if ($isfinal) {
            // Look for "FINAL_STATUS: OK" from your prompt; else treat as issues.
            $ok = (bool)preg_match('/FINAL_STATUS\s*:\s*OK/i', $text);
            // Persist final code and set statuses.
            $rec = $DB->get_record('aiconcept_submissions', ['id' => $submissionid], '*', MUST_EXIST);
            $rec->finalcode     = $finalcode ?: $rec->finalcode;
            $rec->status        = $ok ? 'submitted' : 'inprogress'; // student-facing pill.
            $rec->reviewstatus  = $ok ? 'pending'   : 'none';       // instructor queue.
            $rec->timemodified  = time();
            $DB->update_record('aiconcept_submissions', $rec);

            if ($ok) {
                $append = "\n\n---\n✅ Your code looks consistent.\nI've forwarded it to your instructor for approval. You'll receive full credit once they approve.";
            } else {
                $append = "\n\n---\nI see remaining issues above. Please revise and resubmit your final when ready.";
            }
        }

        // Log assistant turn.
        $DB->insert_record('aiconcept_turns', (object)[
            'submissionid' => $submissionid,
            'role'         => 'assistant',
            'content'      => $text . $append,
            'model'        => get_config('mod_aiconcept', 'openai_model'),
            'usage_json'   => json_encode($resp['usage'] ?? null),
            'status'       => 'ok',
            'timecreated'  => time()
        ]);

        return [
            'submissionid' => $submissionid,
            'assistant'    => $text . $append
        ];
    }

    public static function submit_and_respond_returns() {
        return new external_single_structure([
            'submissionid' => new external_value(PARAM_INT, 'Submission id'),
            'assistant'    => new external_value(PARAM_RAW, 'Assistant reply text'),
        ]);
    }

    // -------- Fetch turns (for transcript reloads) --------

    public static function fetch_turns_parameters() {
        return new external_function_parameters([
            'submissionid' => new external_value(PARAM_INT, 'Submission id'),
        ]);
    }

    public static function fetch_turns($submissionid) {
        global $DB, $USER;

        ['submissionid' => $submissionid]
            = self::validate_parameters(self::fetch_turns_parameters(), compact('submissionid'));

        $submission = $DB->get_record('aiconcept_submissions', ['id' => $submissionid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_id('aiconcept', $submission->cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/aiconcept:submit', $context);

        // Students can only see their own transcript; teachers with viewlogs can see all.
        if ((int)$submission->userid !== (int)$USER->id && !has_capability('mod/aiconcept:viewlogs', $context)) {
            throw new \required_capability_exception($context, 'mod/aiconcept:viewlogs', 'nopermissions', '');
        }

        $turns = $DB->get_records('aiconcept_turns', ['submissionid' => $submissionid], 'id ASC');
        $out = [];
        foreach ($turns as $t) {
            $out[] = [
                'role'        => (string)$t->role,
                'content'     => (string)$t->content,
                'timecreated' => (int)$t->timecreated,
            ];
        }
        return $out;
    }

    public static function fetch_turns_returns() {
        return new external_multiple_structure(new external_single_structure([
            'role'        => new external_value(PARAM_TEXT, 'Role (student|assistant)'),
            'content'     => new external_value(PARAM_RAW, 'Message content'),
            'timecreated' => new external_value(PARAM_INT, 'Unix time'),
        ]));
    }
}
