<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_aiconcept_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    // 2025091302: add review fields + kb_text to submissions.
    if ($oldversion < 2025091302) {
        $table = new xmldb_table('aiconcept_submissions');

        $reviewstatus = new xmldb_field('reviewstatus', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'none', 'status');
        if (!$dbman->field_exists($table, $reviewstatus)) {
            $dbman->add_field($table, $reviewstatus);
        }

        $reviewby = new xmldb_field('reviewby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'reviewstatus');
        if (!$dbman->field_exists($table, $reviewby)) {
            $dbman->add_field($table, $reviewby);
        }

        $reviewtime = new xmldb_field('reviewtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'reviewby');
        if (!$dbman->field_exists($table, $reviewtime)) {
            $dbman->add_field($table, $reviewtime);
        }

        $reviewnote = new xmldb_field('reviewnote', XMLDB_TYPE_TEXT, null, null, null, null, null, 'reviewtime');
        if (!$dbman->field_exists($table, $reviewnote)) {
            $dbman->add_field($table, $reviewnote);
        }

        $kbtext = new xmldb_field('kb_text', XMLDB_TYPE_TEXT, null, null, null, null, null, 'finalcode');
        if (!$dbman->field_exists($table, $kbtext)) {
            $dbman->add_field($table, $kbtext);
        }

        // Initialize existing rows.
        $DB->execute("UPDATE {aiconcept_submissions} SET reviewstatus = 'none', reviewby = 0, reviewtime = 0 WHERE reviewstatus IS NULL");

        upgrade_mod_savepoint(true, 2025091302, 'aiconcept');
    }

    return true;
}
