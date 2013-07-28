<?php

require_once __DIR__.'/../../config.php';

/* @var $DB moodle_database */
/* @var $USER object */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$course = $DB->get_record('course', [ 'id' => required_param('id', PARAM_INT) ], '*', MUST_EXIST);
require_login($course);
require_capability('moodle/grade:viewall', context_course::instance($course->id));

$courseshortname = format_string($course->shortname, true, [ 'context' => context_course::instance($course->id) ]);
$strtitle = get_string('pluginname', 'block_bayes');

$PAGE->set_url('/blocks/bayes/view.php', [ 'id' => $course->id ]);
$PAGE->set_title($courseshortname . ': ' . $strtitle);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strtitle);

echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);

echo $OUTPUT->footer();
