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
		require_login(SITEID);
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

		$f->addElement('select', 'encoding', bayes::str('encoding'), [
			'CP932' => bayes::str('cp932'),
			'UTF-8' => bayes::str('utf8')
		]);

		$this->add_action_buttons(false, bayes::str('downloademptycsv'));
	}
}
