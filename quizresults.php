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
		global $DB;

		echo $this->output->header();

		$quizid = required_param('quiz', PARAM_INT);
		$quiz = $DB->get_record('quiz', ['id' => $quizid], '*', MUST_EXIST);
		$cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course);
		$course = $DB->get_record('course', ['id' => $quiz->course], '*', MUST_EXIST);

// 		$options = new \quiz_overview_options('', $quiz, $cm, $course);

// 		$table = new \quiz_overview_table($quiz, \context_course::instance($quiz->course),
// 				'', $options, null, null, null, null);
// 		list($currentgroup, $students, $groupstudents, $allowed) =
// 		$this->init('responses', 'quiz_responses_settings_form', $quiz, $cm, $course);

// 		$table->out(30, true);

		$report = new \quiz_bayes_report();
		$report->display($cm, $course, $quiz);

		echo $this->output->footer();
	}
}

$page = new page_quiz_results('/blocks/bayes/quizresults.php');
$page->execute();
