
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

class backup_aiconcept_activity_task extends backup_activity_task {
    protected function define_my_settings() {}
    protected function define_my_steps() {
        $this->add_step(new backup_aiconcept_activity_structure_step('aiconcept_structure', 'aiconcept.xml'));
    }
    static public function encode_content_links($content) { return $content; }
}
