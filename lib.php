<?php
defined('MOODLE_INTERNAL') || die();

function aiconcept_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE: return MOD_ARCHETYPE_OTHER;
        case FEATURE_NO_VIEW_LINK:  return false;
        case FEATURE_BACKUP_MOODLE2: return true;
        default: return null;
    }
}

function aiconcept_add_instance($data, $mform = null) {
    global $DB;
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    return $DB->insert_record('aiconcept', $data);
}

function aiconcept_update_instance($data, $mform = null) {
    global $DB;
    $data->id = $data->instance;
    $data->timemodified = time();
    return $DB->update_record('aiconcept', $data);
}

function aiconcept_delete_instance($id) {
    global $DB;
    if (!$aiconcept = $DB->get_record('aiconcept', ['id'=>$id])) { return false; }
    // Clean up submissions and turns linked to this CM.
    $submissions = $DB->get_records('aiconcept_submissions', ['cmid' => $aiconcept->coursemodule ?? 0]);
    foreach ($submissions as $s) {
        $DB->delete_records('aiconcept_turns', ['submissionid' => $s->id]);
    }
    $DB->delete_records('aiconcept_submissions', ['cmid' => $aiconcept->coursemodule ?? 0]);
    $DB->delete_records('aiconcept', ['id'=>$id]);
    return true;
}

/** Add Instructor dashboard link in the activity's secondary nav. */
function aiconcept_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $aiconceptnode=null) {
    global $PAGE;
    if (!$PAGE->cm) { return; }
    $context = context_module::instance($PAGE->cm->id);
    if (!has_capability('moodle/course:manageactivities', $context)) { return; }
    $url = new moodle_url('/mod/aiconcept/instructor.php', ['id' => $PAGE->cm->id]);
    $node = navigation_node::create('Instructor dashboard', $url, navigation_node::TYPE_SETTING, null, 'aiconcept_instructor');
    $settingsnav->add_node($node);
}