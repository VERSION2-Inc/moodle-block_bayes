<?php
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/mod/quiz/report/attemptsreport_table.php';

use bayesns\bayes;

class quiz_bayes_table extends quiz_attempts_report_table {
	public function build_table() {
        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        parent::build_table();
	}

	/**
	 *
	 * @param string $colname
	 * @param \stdClass $attempt
	 * @return string
	 */
	public function other_cols($colname, $attempt) {
		if (preg_match('/^question(\d+)$/', $colname, $m)) {
			$step = $this->lateststeps[$attempt->usageid][$m[1]];
			return bayes::format_float($step->fraction);
		} else {
			return null;
		}
	}

	/**
	 *
	 * @param \stdClass $attempt
	 * @return string
	 */
	public function col_classify($attempt) {
		global $OUTPUT;

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

		$debugrow = ['レベル'];
		foreach ($levels as $level) {
			$debugrow[] = $level->fullname;
		}
		$debug[] = $debugrow;

		foreach ($steps as $questionnum => $step) {
			$debugrow = [$questionnum];
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

		$debugrow = ['積'];

		foreach ($levels as $level) {
			$debugrow[] = $probrates[$level->id];
		}
		$debug[] = $debugrow;

		arsort($probrates);

		$level = $levels[key($probrates)];

		if ($this->is_downloading()) {
			return $level->fullname;
		} else {
			$table = new html_table();
			$table->id = "debug_$attempt->usageid";
			$table->attributes = ['style' => 'display: none'];
			$table->data = $debug;

			$togglebutton = $OUTPUT->action_icon('#', new pix_icon('t/switch', '詳細'), null, [
					'class' => 'action-icon debug-toggle',
					'data-usageid' => $attempt->usageid
			], true);

			return $level->fullname.'<br>'.$togglebutton.html_writer::table($table);
		}
	}

	/**
	 *
	 * @return boolean
	 */
	protected function requires_latest_steps_loaded() {
		return true;
	}
}
