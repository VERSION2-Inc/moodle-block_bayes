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
}

class page {
	public function __construct() {
		if ($courseid = required_param('course', PARAM_INT)) {
			require_login($courseid);
		}
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

		$f->addElement('hidden', 'course', $this->_customdata->course);
		$f->setType('course', PARAM_INT);

		$f->addElement('header', 'generateemptycsv', bayes::str('generateemptycsv'));

		$opts = [
			'CP932' => bayes::str('cp932'),
			'UTF-8' => bayes::str('utf8')
		];
		$f->addElement('select', 'encoding', bayes::str('encoding'), $opts);

		$this->add_action_buttons(false, bayes::str('downloademptycsv'));
	}
}
