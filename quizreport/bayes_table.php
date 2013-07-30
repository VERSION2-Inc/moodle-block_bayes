<?php
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/mod/quiz/report/attemptsreport_table.php';

class quiz_bayes_table extends quiz_attempts_report_table {
	public function build_table() {
        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        parent::build_table();
	}

	public function other_cols($colname, $attempt) {
		if (preg_match('/^question(\d+)$/', $colname, $m)) {
			$step = $this->lateststeps[$attempt->usageid][$m[1]];
			return $step->fraction;
		} else {
			return null;
		}
	}

	protected function requires_latest_steps_loaded() {
		return true;
	}
}
