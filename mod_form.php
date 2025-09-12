<?php
defined('MOODLE_INTERNAL') || die();

// This is the correct include for activity module forms.
require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_aiconcept_mod_form extends moodleform_mod {
    public function definition() {
        $mform = $this->_form;

        // Activity name.
        $mform->addElement('text', 'name', get_string('modulename', 'mod_aiconcept'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Standard course module settings: availability, groups, grade, etc.
        $this->standard_coursemodule_elements();

        // Action buttons.
        $this->add_action_buttons();
    }
}
