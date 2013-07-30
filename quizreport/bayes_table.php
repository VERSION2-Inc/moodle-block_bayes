<?php
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/mod/quiz/report/attemptsreport_table.php';

use bayesns\bayes;

class quiz_bayes_table extends quiz_attempts_report_table {
	public function build_table() {
// 		var_dump($this->lateststeps);

        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        parent::build_table();
	}

	public function other_cols($colname, $attempt) {
		if (preg_match('/^question(\d+)$/', $colname, $m)) {
			$step = $this->lateststeps[$attempt->usageid][$m[1]];
			return bayes::format_float($step->fraction);
		} else {
			return null;
		}
	}

	public function col_classify($attempt) {
		$levels = bayes::get_levels();
		$likelihoods = bayes::get_likelihoods();

		$levelids = [];
		foreach ($levels as $level) {
			$levelids[] = $level->id;
		}

		$probrates = [];
		foreach ($levelids as $levelid) {
			$probrates[$levelid] = 1;
		}

		$steps = $this->lateststeps[$attempt->usageid];

		$debug = [];

		foreach ($steps as $questionnum => $step) {
			$debugrow = [];
			foreach ($levels as $level) {
				if ($step->fraction == 1) {
					$probability = $level->probability;
					$likelihood = $likelihoods[$step->questionid][$level->id];
				} else {
					$probability = 1 - $level->probability;
					$likelihood = 1 - $likelihoods[$step->questionid][$level->id];
				}
				$probrates[$level->id] *= $probability * $likelihood;
				$debugrow[] = $probability * $likelihood;
			}
			$debug[] = $debugrow;
		}

		$debugrow = [];
		foreach ($levels as $level) {
			$debugrow[] = $probrates[$level->id];
			$debugtitlerow[] = $level->fullname;
		}
		$debug[] = $debugrow;
		$debug[] = $debugtitlerow;

		arsort($probrates);

		$level = $levels[key($probrates)];

		$table = new html_table();
		$table->data = $debug;
		echo html_writer::table($table);

		return $level->fullname;
	}

	protected function requires_latest_steps_loaded() {
		return true;
	}
}
