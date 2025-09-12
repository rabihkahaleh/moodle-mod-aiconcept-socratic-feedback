<?php
class backup_aiconcept_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $aiconcept = new backup_nested_element('aiconcept', ['id'], ['name', 'timecreated', 'timemodified']);
        $aiconcept->set_source_table('aiconcept', ['id' => backup::VAR_ACTIVITYID]);
        return $this->prepare_activity_structure($aiconcept);
    }
}
