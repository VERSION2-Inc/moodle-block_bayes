<?php
namespace bayesns;

require_once '../../config.php';
require_once $CFG->dirroot . '/blocks/bayes/locallib.php';

class page_likelihoods extends page {
	/**
	 *
	 * @var form_edit_likelihoods
	 */
	private $editform;

	public function execute() {
		$this->editform = new form_edit_likelihoods();

		if ($this->editform->is_submitted()) {
			$this->update_likelihoods();
		}
		$this->view();
	}

	private function view() {
		global $DB;

		$head = bayes::str('managelikelihoods');
		$this->add_navbar($head);

		echo $this->output->header();
		echo $this->output->heading($head);

		echo $this->output->box(get_string('modulename', 'quiz').': '
				.$DB->get_field('quiz', 'name', ['id' => required_param('quiz', PARAM_INT)], MUST_EXIST));

		$this->editform->display();

		echo $this->output->footer();
	}

	private function update_likelihoods() {
		global $DB;

		$likelihoods = bayes::get_likelihoods();

		$data = $this->editform->get_data();

		foreach ($data as $key => $group) {
			if (preg_match('/^q_(\d+)$/', $key, $m)) {
				$questionid = $m[1];
				foreach ($group as $levelid => $newlikelihood) {
					if (isset($likelihoods[$questionid][$levelid])
						&& $likelihoods[$questionid][$levelid] != $newlikelihood) {
// 						echo "$questionid:$levelid({$likelihoods[$questionid][$levelid]}) set to $newlikelihood<br>";
						bayes::set_likelihood($questionid, $levelid, $newlikelihood);
					}
				}
			}
		}

		redirect(new \moodle_url($this->url, ['quiz' => required_param('quiz', PARAM_INT)]));
	}
}

class form_edit_likelihoods extends \moodleform {
	protected function definition() {
		$f = $this->_form;

		$quizid = required_param('quiz', PARAM_INT);

		$f->addElement('hidden', 'quiz', $quizid);
		$f->setType('quiz', PARAM_INT);

		$f->addElement('header', 'questions', get_string('questions', 'quiz'));

		$this->add_question_groups($quizid);

		$this->add_action_buttons(false);
	}

	private function add_question_groups($quizid) {
		$questionids = bayes::get_quiz_question_ids($quizid);

		foreach ($questionids as $questionnum => $questionid) {
			$this->add_question_group($questionnum, $questionid);
		}
	}

	private function add_question_group($questionnum, $questionid) {
		global $DB;

		$f = $this->_form;

		$question = $DB->get_record('question', ['id' => $questionid], 'id, name', MUST_EXIST);

		$levels = bayes::get_levels();

		$group = [];

		$groupname = "q_$questionid";
		$fieldsize = strlen(bayes::format_float(0));
		foreach ($levels as $level) {
			$name = $level->id;
			$group[] = $f->createElement('text', $name, '', ['size' => $fieldsize]);
			$uniquename = "{$groupname}[$name]";
			$f->setType($uniquename, PARAM_TEXT);

			if (($value = bayes::get_likelihood($questionid, $level->id)) !== false) {
				$f->setDefault($uniquename, bayes::format_float($value));
			}
		}

		$f->addGroup($group, $groupname, ($questionnum + 1).": $question->name");
	}
}

$page = new page_likelihoods('/blocks/bayes/likelihoods.php');
$page->execute();
