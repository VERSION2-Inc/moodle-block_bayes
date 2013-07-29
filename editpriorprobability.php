<?php

require_once __DIR__.'/../../config.php';

/* @var $DB moodle_database */
/* @var $USER object */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$course = $DB->get_record('course', [ 'id' => required_param('id', PARAM_INT) ], '*', MUST_EXIST);
require_login($course);
require_capability('moodle/grade:viewall', context_course::instance($course->id));

$strpluginname = get_string('pluginname', 'block_bayes');
$strpagetitle = get_string('editpriorprobability', 'block_bayes');

$PAGE->set_url('/blocks/bayes/editpriorprobability.php', [ 'id' => $course->id ]);
$PAGE->set_title($strpluginname . ': ' . $strpagetitle);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strpluginname);
$PAGE->navbar->add($strpagetitle);

echo $OUTPUT->header();
echo $OUTPUT->heading($strpagetitle);



echo $OUTPUT->footer();
