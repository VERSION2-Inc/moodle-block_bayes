<?php
namespace bayesns;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';
require_once $CFG->libdir . '/csvlib.class.php';

class bayes {
	const COMPONENT = 'block_bayes';

	const TABLE_LEVELS = 'block_bayes_levels';
	const TABLE_LIKELIHOODS = 'block_bayes_likelihoods';

	/**
	 *
	 * @param string $identifier
	 * @param string|\stdClass $a
	 * @return string
	 */
	public static function str($identifier, $a = null) {
		return get_string($identifier, self::COMPONENT, $a);
	}

	/**
	 *
	 * @return array
	 */
	public static function get_encodings() {
		return [
			'CP932' => self::str('cp932'),
			'UTF-8' => self::str('utf8')
		];
	}

	/**
	 *
	 * @param string $name
	 * @return string
	 */
	public static function get_level_key($name) {
		$name = mb_convert_kana($name, 'asKV', 'UTF-8');
		$name = preg_replace('/\s/', '', $name);
		$name = mb_strtolower($name);
		return $name;
	}

	/**
	 *
	 * @param int $questionid
	 * @param int $levelid
	 * @param float $likelihood
	 */
	public static function set_likelihood($questionid, $levelid, $likelihood) {
		global $DB;

		if ($row = $DB->get_record(self::TABLE_LIKELIHOODS, compact('questionid', 'levelid'))) {
			$row->likelihood = $likelihood;
			$DB->update_record(self::TABLE_LIKELIHOODS, $row);
		} else {
			$DB->insert_record(self::TABLE_LIKELIHOODS, (object)compact('questionid', 'levelid', 'likelihood'));
		}
	}

	/**
	 *
	 * @return \stdClass[]
	 */
	public static function get_levels() {
		global $DB;
		return $DB->get_records(self::TABLE_LEVELS);
	}

	/**
	 *
	 * @param int $id
	 * @return int[]
	 */
	public static function get_quiz_question_ids($id) {
		global $DB;
		$quiz = $DB->get_record('quiz', compact('id'), 'id, questions', MUST_EXIST);
		return array_merge(array_filter(explode(',', $quiz->questions)));
	}

	/**
	 *
	 * @param int $questionid
	 * @param int $levelid
	 * @return float
	 */
	public static function get_likelihood($questionid, $levelid) {
		global $DB;
		return $DB->get_field(self::TABLE_LIKELIHOODS, 'likelihood', compact('questionid', 'levelid'));
	}

	/**
	 *
	 * @return float[][]
	 */
	public static function get_likelihoods() {
		global $DB;
		$rows = $DB->get_records(self::TABLE_LIKELIHOODS);
		$likelihoods = [];
		foreach ($rows as $row) {
			$likelihoods[$row->questionid][$row->levelid] = $row->likelihood;
		}
		return $likelihoods;
	}

	/**
	 *
	 * @param float $value
	 * @return string
	 */
	public static function format_float($value) {
		return sprintf('%.2f', $value);
	}
}

abstract class page {
	/**
	 *
	 * @var \core_renderer
	 */
	protected $output;
	/**
	 *
	 * @var int
	 */
	protected $courseid;
	/**
	 *
	 * @var string
	 */
	protected $url;

	/**
	 *
	 * @param string $url
	 */
	public function __construct($url) {
		global $OUTPUT, $PAGE;

		$this->url = $url;
		$this->output = $OUTPUT;

		if ($this->courseid = optional_param('course', 0, PARAM_INT)) {
			require_login($this->courseid);
		} else {
			require_login(SITEID);
		}

		$PAGE->set_url($url);
		$PAGE->set_title(bayes::str('pluginname'));
		$PAGE->set_heading(bayes::str('pluginname'));
		$this->add_navbar(bayes::str('pluginname'));
	}

	public abstract function execute();

	/**
	 *
	 * @param string $text
	 */
	protected function add_navbar($text) {
		global $PAGE;
		$PAGE->navbar->add($text);
	}
}

class encoded_csv_writer extends \csv_export_writer {
	/**
	 *
	 * @var string
	 */
	public $encoding;

	public function add_data($row) {
		if ($this->encoding && $this->encoding != 'UTF-8') {
			foreach ($row as &$item) {
				$item = iconv('UTF-8', $this->encoding, $item);
			}
		}
		parent::add_data($row);
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

		if (!empty($this->_customdata->quizid)) {
			$f->addElement('hidden', 'quiz', $this->_customdata->quizid);
			$f->setType('quiz', PARAM_INT);
		} else {
			$quizzes = $DB->get_records_menu('quiz', ['course' => $this->_customdata->courseid],
					'name', 'id, name');
			$f->addElement('select', 'quiz', get_string('modulename', 'quiz'), $quizzes);
		}

		$f->addElement('select', 'encoding', bayes::str('encoding'), bayes::get_encodings());

		$this->add_action_buttons(false, bayes::str('uploadcsv'));
	}
}
