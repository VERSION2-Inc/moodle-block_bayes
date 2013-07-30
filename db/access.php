<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
		'block/bayes:addinstance' => [
				'captype' => 'write',
				'contextlevel' => CONTEXT_BLOCK,
				'archetypes' => [
						'editingteacher' => CAP_ALLOW,
						'manager' => CAP_ALLOW
				],
				'clonepermissionsfrom' => 'moodle/site:manageblocks'
		],

		'block/bayes:classify' => [
				'captype' => 'write',
				'contextlevel' => CONTEXT_BLOCK,
				'archetypes' => [
						'editingteacher' => CAP_ALLOW,
						'manager' => CAP_ALLOW
				]
		]
];
