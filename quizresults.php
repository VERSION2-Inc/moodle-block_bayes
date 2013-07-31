<?php
namespace bayesns;

require_once '../../config.php';
require_once $CFG->dirroot . '/blocks/bayes/locallib.php';

require_once $CFG->dirroot . '/blocks/bayes/quizreport/report.php';

class page_quiz_results extends page {
	public function execute() {
		$this->view();
	}

	private function view() {
		global $DB, $PAGE;

		$PAGE->requires->js_init_call('M.block_bayes.quiz_results_init');

		$head = bayes::str('quizresults');
		$this->add_navbar($head);

		echo $this->output->header();
		echo $this->output->heading($head);

		$quizid = required_param('quiz', PARAM_INT);
		$quiz = $DB->get_record('quiz', ['id' => $quizid], '*', MUST_EXIST);
		$cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course);
		$course = $DB->get_record('course', ['id' => $quiz->course], '*', MUST_EXIST);

		echo $this->output->box(get_string('modulename', 'quiz').': '.$quiz->name);

		$report = new \quiz_bayes_report();
		$report->display($cm, $course, $quiz);

		echo $this->output->footer();
	}
}

$page = new page_quiz_results('/blocks/bayes/quizresults.php');
$page->execute();
