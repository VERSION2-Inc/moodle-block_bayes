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
		echo $this->output->header();

		$this->uploadform->display();

		echo $this->output->footer();
	}

	private function read_csv() {
		global $DB;

		$data = $this->uploadform->get_data();

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
			$questionnum = $row[0] - 1;
			if (!isset($questions[$questionnum])) {
				continue;
			}

			foreach ($columnmap as $i => $levelid) {
				bayes::set_likelihood($questions[$questionnum], $levelid, $row[$i]);
			}
		}
	}
}

class form_upload_csv extends \moodleform {
	protected function definition() {
		global $DB;

		$f = $this->_form;
		$courseid = $this->_customdata->courseid;

		$f->addElement('hidden', 'course', $courseid);
		$f->setType('course', PARAM_INT);

		$f->addElement('header', 'upload', bayes::str('upload'));

		$f->addElement('filepicker', 'file', get_string('file'));
		$f->addRule('file', null, 'required', null, 'client');

		$quizzes = $DB->get_records_menu('quiz', ['course' => $this->_customdata->courseid],
				'name', 'id, name');
		$f->addElement('select', 'quiz', get_string('modulename', 'quiz'), $quizzes);
		$f->addElement('select', 'encoding', bayes::str('encoding'), bayes::get_encodings());

		$this->add_action_buttons(false, bayes::str('uploadcsv'));
	}
}

$page = new page_upload_csv('/blocks/bayes/uploadcsv.php');
$page->execute();
