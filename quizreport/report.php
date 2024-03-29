<?php
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/blocks/bayes/locallib.php';
require_once $CFG->dirroot . '/mod/quiz/report/reportlib.php';
require_once $CFG->dirroot . '/mod/quiz/report/default.php';
require_once $CFG->dirroot . '/mod/quiz/report/attemptsreport.php';
require_once $CFG->dirroot . '/blocks/bayes/quizreport/bayes_form.php';
require_once $CFG->dirroot . '/mod/quiz/report/attemptsreport_options.php';
require_once $CFG->dirroot . '/blocks/bayes/quizreport/bayes_table.php';

class quiz_bayes_report extends quiz_attempts_report {
	public function display($cm, $course, $quiz) {
		global $OUTPUT;

		list($currentgroup, $students, $groupstudents, $allowed) =
			$this->init('bayes', 'quiz_bayes_settings_form', $quiz, $cm, $course);

		if (empty($students)) {
			echo $OUTPUT->notification(get_string('nostudentsyet'));
			return;
		}

		$options = new mod_quiz_attempts_report_options('bayes', $quiz, $cm, $course);
		$options->process_settings_from_params();

		$questions = quiz_report_get_significant_questions($quiz);

		$courseshortname = format_string($course->shortname, true, [
				'context' => context_course::instance($course->id)
		]);
		$table = new quiz_bayes_table('quiz-bayes-report', $quiz, $this->context, $this->qmsubselect,
				$options, $groupstudents, $students, $questions, $options->get_url());
		$filename = quiz_report_download_filename(get_string('classify', 'block_bayes'),
				$courseshortname, $quiz->name);
		$table->is_downloading($options->download, $filename, $courseshortname.' '.format_string($quiz->name, true));
		$view = !$table->is_downloading();
		if (!$view) {
			raise_memory_limit(MEMORY_EXTRA);
		}

		if ($view) {
			$this->print_header_and_tabs($cm, $course, $quiz, $this->mode);
		}

		$table->define_baseurl(new moodle_url('/blocks/bayes/quizresults.php', [
				'course' => $course->id,
				'quiz' => $quiz->id
		]));

		list($fields, $from, $where, $params) = $table->base_sql($allowed);
		$table->set_sql($fields, $from, $where, $params);

		$columns = [];
		$headers = [];

		$columns[] = 'classify';
		$headers[] = get_string('classify', 'block_bayes');

		$this->add_user_columns($table, $columns, $headers);
		$this->add_state_column($columns, $headers);
		$this->add_time_columns($columns, $headers);
		$this->add_grade_columns($quiz, true, $columns, $headers);
		foreach ($questions as $id => $question) {
			$columns[] = "question$id";
			$headers[] = "question$id";
		}
		$table->define_columns($columns);
		$table->define_headers($headers);
		$this->configure_user_columns($table);

		$table->out(30, true);

		if ($view) {
			echo $OUTPUT->footer();
		}
	}
}
