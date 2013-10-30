<?php
defined('MOODLE_INTERNAL') || die();

use bayesns\bayes;

class block_bayes extends block_list {

	public function init() {
		$this->title = get_string('pluginname', __CLASS__);
		$this->version = 2013072800;
	}

	/**
	 *
	 * @return boolean
	 */
	public function has_config() {
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public function applicable_formats() {
		return [
				'course' => true,
				'course-category' => false,
				'mod' => false,
				'my' => false
		];
	}

	/**
	 * Returns the block content
	 *
	 * @return stdClass string
	 */
	public function get_content() {
		global $CFG, $DB, $OUTPUT, $COURSE;

		if (!has_capability('block/bayes:classify', context_block::instance($this->instance->id))) {
			return null;
		}

		require_once $CFG->dirroot . '/blocks/bayes/locallib.php';

		$courseid = $COURSE->id;

		if ($this->content !== null) {
			return $this->content;
		}

		$this->content = (object)[
			'icons' => null
		];

		$editicon = $OUTPUT->pix_icon('i/edit', '', 'moodle', [
				'class' => 'icon'
		]);

		$this->content->items[] = $OUTPUT->action_link(
						new moodle_url('/blocks/bayes/editpriorprobability.php',
								[
										'id' => $this->page->course->id
								]), $editicon . get_string('editpriorprobability', __CLASS__));
		$this->content->icons[] = '';

		$this->content->items[] = bayes::str('managelikelihoods');
		$this->content->icons[] = $OUTPUT->pix_icon('i/db', '');
		$quizzes = $DB->get_records_menu('quiz', ['course' => $courseid],
				'name', 'id, name');
		$this->content->items[] = $OUTPUT->single_select(
				new moodle_url('/blocks/bayes/likelihoods.php', ['course' => $courseid]), 'quiz', $quizzes);
		$this->content->icons[] = '';

		$this->content->items[] = bayes::str('classify');
		$this->content->icons[] = $OUTPUT->pix_icon('i/group', '');
		$quizzes = $DB->get_records_menu('quiz', ['course' => $courseid],
				'name', 'id, name');
		$this->content->items[] = $OUTPUT->single_select(
				new moodle_url('/blocks/bayes/quizresults.php', ['course' => $courseid]), 'quiz', $quizzes);

		return $this->content;
	}
}
