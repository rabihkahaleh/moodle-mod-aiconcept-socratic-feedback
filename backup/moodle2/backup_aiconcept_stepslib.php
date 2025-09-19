
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

class backup_aiconcept_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $aiconcept = new backup_nested_element('aiconcept', ['id'], ['name', 'timecreated', 'timemodified']);
        $aiconcept->set_source_table('aiconcept', ['id' => backup::VAR_ACTIVITYID]);
        return $this->prepare_activity_structure($aiconcept);
    }
}
