<?php
namespace bayesns;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';

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

class generate_csv_form extends \moodleform {
	protected function definition() {
		$f = $this->_form;

		$f->addElement('hidden', 'id', $this->_customdata->id);
		$f->setType('id', PARAM_INT);

		$f->addElement('header', 'generateemptycsv', bayes::str('generateemptycsv'));

		$opts = [
			'CP932' => bayes::str('cp932'),
			'UTF-8' => bayes::str('utf8')
		];
		$f->addElement('select', 'encoding', bayes::str('encoding'), $opts);

		$this->add_action_buttons(false, bayes::str('downloademptycsv'));
	}
}
