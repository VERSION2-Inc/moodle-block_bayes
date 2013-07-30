<?php
defined('MOODLE_INTERNAL') || die();

class block_bayes extends block_list {

	public function init() {
		$this->title = get_string('pluginname', __CLASS__);
		$this->version = 2013072800;
	}

	public function has_config() {
		return true;
	}

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
		global $DB, $OUTPUT, $COURSE;

		if (!has_capability('block/bayes:classify', context_block::instance($this->instance->id))) {
			return null;
		}

		$courseid = $COURSE->id;

		if ($this->content !== null) {
			return $this->content;
		}

		$this->content = (object)[
			'icons' => null
		];

		if (!has_capability('moodle/grade:viewall',
				context_course::instance($this->page->course->id)))
			return $this->content = '';

		$editicon = $OUTPUT->pix_icon('i/edit', '', 'moodle', [
				'class' => 'icon'
		]);

		$this->content->items[] = 	$OUTPUT->action_link(
						new moodle_url('/blocks/bayes/editpriorprobability.php',
								[
										'id' => $this->page->course->id
								]), $editicon . get_string('editpriorprobability', __CLASS__));

		$quizzes = $DB->get_records_menu('quiz', ['course' => $courseid],
				'name', 'id, name');
		$this->content->items[] = $OUTPUT->single_select(
				new moodle_url('/blocks/bayes/likelihoods.php', ['course' => $courseid]), 'quiz', $quizzes);

		$this->content->items[] = $OUTPUT->action_link(
				new moodle_url('/blocks/bayes/uploadcsv.php', ['course' => $courseid]), 'uploadcsv');

// 		$this->page->requires->css('/blocks/bayes/styles.css');

		return $this->content;
// 		return $this->content = (object)[
// 				'text' => $html
// 		];
	}
}
