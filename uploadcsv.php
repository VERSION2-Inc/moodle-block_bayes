<?php
namespace bayesns;

require_once '../../config.php';
require_once $CFG->dirroot . '/blocks/bayes/locallib.php';
require_once $CFG->libdir . '/csvlib.class.php';

class page_upload_csv extends page {
	/**
	 *
	 * @var form_upload_csv
	 */
	private $uploadform;

	public function execute() {
		$this->uploadform = new form_upload_csv(null, (object)[
				'courseid' => $this->courseid
		]);

		if ($this->uploadform->is_submitted()) {
			$this->read_csv();
			exit();
		}
		$this->view();
	}

	private function view() {
		$head = bayes::str('uploadcsv');
		$this->add_navbar($head);

		echo $this->output->header();
		echo $this->output->heading($head);

		$this->uploadform->display();

		echo $this->output->footer();
	}

	private function read_csv() {
		global $DB;

		$data = $this->uploadform->get_data();

		ini_set('auto_detect_line_endings', true);

		$content = $this->uploadform->get_file_content('file');
		$reader = new \csv_import_reader(\csv_import_reader::get_new_iid('block_bayes'), 'block_bayes');
		if (!$reader->load_csv_content($content, $data->encoding, 'comma')) {
			print_error('csvhaserror', bayes::COMPONENT, '', null, $reader->get_error());
		}

		$levelids = [];
		$levels = $DB->get_records('block_bayes_levels');
		foreach ($levels as $level) {
			$levelids[bayes::get_level_key($level->name)] = $level->id;
			$levelids[bayes::get_level_key($level->fullname)] = $level->id;
		}

		$columnmap = [];
		$columns = $reader->get_columns();
		for ($i = 1; $i < count($columns); $i++) {
			$pattern = bayes::get_level_key($columns[$i]);
			if (isset($levelids[$pattern])) {
				$columnmap[$i] = $levelids[$pattern];
			}
		}

		$questions = bayes::get_quiz_question_ids($data->quiz);

		$reader->init();
		while ($row = $reader->next()) {
			$row = array_map('trim', $row);
			$questionnum = $row[0] - 1;
			if (!isset($questions[$questionnum])) {
				continue;
			}

			foreach ($columnmap as $i => $levelid) {
				bayes::set_likelihood($questions[$questionnum], $levelid, $row[$i]);
			}
		}

		redirect(new \moodle_url('/blocks/bayes/likelihoods.php', ['course' => $this->courseid, 'quiz' => $data->quiz]));
	}
}

$page = new page_upload_csv('/blocks/bayes/uploadcsv.php');
$page->execute();
