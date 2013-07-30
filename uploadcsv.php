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
		}
		$this->view();
	}

	private function view() {
		echo $this->output->header();

		$this->uploadform->display();

		echo $this->output->footer();
	}

	private function read_csv() {

	}
}

class form_upload_csv extends \moodleform {
	protected function definition() {
		global $DB;

		$f = $this->_form;

		$f->addElement('header', 'upload', bayes::str('upload'));

		$f->addElement('filepicker', 'file', get_string('file'));
		$f->addRule('file', null, 'required', null, 'client');

		$quizzes = $DB->get_records_menu('quiz', ['course' => $this->_customdata->courseid],
				'name', 'id, name');
		$f->addElement('select', 'quiz', get_string('modulename', 'quiz'), $quizzes);
		$f->addElement('select', 'encoding', bayes::str('encoding'), bayes::get_encodings());

		$this->add_action_buttons();
	}
}

$page = new page_upload_csv('/blocks/bayes/uploadcsv.php');
$page->execute();
