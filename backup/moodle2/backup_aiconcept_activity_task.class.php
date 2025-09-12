<?php
class backup_aiconcept_activity_task extends backup_activity_task {
    protected function define_my_settings() {}
    protected function define_my_steps() {
        $this->add_step(new backup_aiconcept_activity_structure_step('aiconcept_structure', 'aiconcept.xml'));
    }
    static public function encode_content_links($content) { return $content; }
}
