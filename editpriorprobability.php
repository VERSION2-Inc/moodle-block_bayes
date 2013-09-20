<?php
require_once __DIR__.'/../../config.php';
require_once $CFG->dirroot . '/blocks/bayes/locallib.php';

use bayesns\bayes;
use bayesns\generate_csv_form;

/* @var $DB moodle_database */
/* @var $USER object */
/* @var $PAGE moodle_page */
/* @var $OUTPUT core_renderer */

$blockid = required_param('id', PARAM_INT);
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

if (optional_param('update', null, PARAM_TEXT) and confirm_sesskey()) {
    $fullnames = optional_param_array('fullname', [], PARAM_TEXT);
    $shortnames = optional_param_array('shortname', [], PARAM_TEXT);
    $probabilities = optional_param_array('probability', [], PARAM_TEXT);
    foreach (array_intersect(array_keys($fullnames), array_keys($shortnames), array_keys($probabilities)) as $id) {
        list ($fullname, $name, $probability) = [ trim($fullnames[$id]), trim($shortnames[$id]), trim($probabilities[$id]) ];
        if (strlen($fullname) != 0 && strlen($name) != 0 && strlen($probability) != 0) {
        	$levels = bayes::get_levels();
        	foreach ($levels as $level) {
        		if ($level->id == $id) {
        			continue;
        		}
        		foreach ([$fullname, $name] as $testname) {
	        		if (bayes::get_level_key($level->fullname) == bayes::get_level_key($testname)
	        			|| bayes::get_level_key($level->name) == bayes::get_level_key($testname)) {
	        			redirect(new moodle_url($PAGE->url, ['error' => bayes::str('conflictinglevelexists', $testname)]));
	        		}
        		}
        	}

            if ($record = $DB->get_record('block_bayes_levels', [ 'id' => $id ])) {
                $record->name        = $name;
                $record->fullname    = $fullname;
                $record->probability = $probability;
                $DB->update_record('block_bayes_levels', $record);
            } else {
                $DB->insert_record('block_bayes_levels', compact('name', 'fullname', 'probability'));
            }
        }
    }
//     redirect($PAGE->url);
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}
if ($delete = optional_param('delete', null, PARAM_INT) and confirm_sesskey()) {
    $DB->delete_records('block_bayes_levels', [ 'id' => $delete ]);
    redirect($PAGE->url);
}

function html_input_tag($type, $name, $value = '', array $attrs = [])
{
    return html_writer::empty_tag('input', compact('type', 'name', 'value') + $attrs);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strpagetitle);

echo $OUTPUT->error_text(optional_param('error', '', PARAM_TEXT));

echo html_writer::start_tag('form', [ 'action' => $PAGE->url->out_omit_querystring(), 'method' => 'post' ]);
echo html_writer::start_tag('div', [ 'style' => 'display:none' ]);
echo html_writer::input_hidden_params($PAGE->url);
echo html_input_tag('hidden', 'sesskey', sesskey());
echo html_writer::end_tag('div');
$table = new html_table;
$table->head = [
    get_string('levelfullname', 'block_bayes'),
    get_string('levelshortname', 'block_bayes'),
    get_string('priorprobability', 'block_bayes'),
    '',
    ];
foreach ($DB->get_records('block_bayes_levels') as $level) {
    $table->data[] = [
        html_input_tag('text', "fullname[$level->id]", $level->fullname),
        html_input_tag('text', "shortname[$level->id]", $level->name),
        html_input_tag('text', "probability[$level->id]", bayes::format_float($level->probability)),
        $OUTPUT->action_icon(
            new moodle_url($PAGE->url, [ 'delete' => $level->id, 'sesskey' => sesskey() ]),
            new pix_icon('t/delete', '')
            ),
        ];
}
$table->data[] = [
    html_input_tag('text', 'fullname[0]'),
    html_input_tag('text', 'shortname[0]'),
    html_input_tag('text', 'probability[0]'),
    '',
    ];
echo html_writer::table($table);
echo html_writer::tag('div', html_input_tag('submit', 'update', get_string('savechanges')));
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
