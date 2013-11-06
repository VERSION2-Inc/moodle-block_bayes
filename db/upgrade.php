<?php
defined('MOODLE_INTERNAL') || die();

/**
 *
 * @param int $oldversion
 * @return boolean
 */
function xmldb_block_bayes_upgrade($oldversion) {
	global $DB;

	$dbman = $DB->get_manager();

	if ($oldversion < 2013110501) {

		// Define table block_bayes_classification to be created
		$table = new xmldb_table('block_bayes_classification');

		// Adding fields to table block_bayes_classification
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
		$table->add_field('attempt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
		$table->add_field('levelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
		$table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

		// Adding keys to table block_bayes_classification
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('levelid', XMLDB_KEY_FOREIGN, array('levelid'), 'block_bayes_levels', array('id'));

		// Adding indexes to table block_bayes_classification
		$table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));
		$table->add_index('attempt', XMLDB_INDEX_NOTUNIQUE, array('attempt'));

		// Conditionally launch create table for block_bayes_classification
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}

		// bayes savepoint reached
		upgrade_block_savepoint(true, 2013110501, 'bayes');
	}

	return true;
}
