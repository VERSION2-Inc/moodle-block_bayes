<?php
namespace bayesns;

require_once '../../config.php';
require_once $CFG->dirroot . '/blocks/bayes/locallib.php';

class page_generate_empty_csv extends page {
	public function execute() {
		$this->output_csv();
	}

	private function output_csv() {
		global $DB;

		$form = new form_generate_csv();
		$data = $form->get_data();

		$levels = $DB->get_records('block_bayes_levels');

		$csv = new encoded_csv_writer();
		$csv->set_filename('bayes');
		$csv->encoding = $data->encoding;

		$row = ['Q'];
		foreach ($levels as $level) {
			$row[] = $level->name;
		}
		$csv->add_data($row);

		for ($q = 1; $q <= $data->numquestions; $q++) {
			$row = [$q];
			foreach ($levels as $level) {
				$row[] = '';
			}
			$csv->add_data($row);
		}

		$csv->download_file();
	}
}

$page = new page_generate_empty_csv('/blocks/bayes/generateemptycsv.php');
$page->execute();
