<?php
namespace bayesns;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';
require_once $CFG->libdir . '/csvlib.class.php';

class bayes {
	const COMPONENT = 'block_bayes';

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
}

class page {
	/**
	 *
	 * @var \core_renderer
	 */
	protected $output;
	protected $courseid;

	/**
	 *
	 * @param string $url
	 */
	public function __construct($url) {
		global $OUTPUT, $PAGE;

		$this->output = $OUTPUT;

		if ($this->courseid = optional_param('course', 0, PARAM_INT)) {
			require_login($this->courseid);
		} else {
			require_login(SITEID);
		}

		$PAGE->set_url($url);
		$PAGE->set_title(bayes::str('pluginname'));
		$PAGE->set_heading(bayes::str('pluginname'));
	}
}

class encoded_csv_writer extends \csv_export_writer {
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

class generate_csv_form extends \moodleform {
	protected function definition() {
		$f = $this->_form;

		$f->addElement('header', 'generateemptycsv', bayes::str('generateemptycsv'));

		$vals = range(10, 200, 10);
		$f->addElement('select', 'numquestions', bayes::str('numquestions'),
				array_combine($vals,
						array_map(
								function ($val) {
									return bayes::str('xquestions', $val);
								}, $vals)));
		$f->setDefault('numquestions', 100);

		$f->addElement('select', 'encoding', bayes::str('encoding'), bayes::get_encodings());

		$this->add_action_buttons(false, bayes::str('downloademptycsv'));
	}
}
