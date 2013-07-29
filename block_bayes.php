<?php

defined('MOODLE_INTERNAL') || die;

class block_bayes extends block_base
{
    public function init()
    {
        $this->title   = get_string('pluginname', __CLASS__);
        $this->version = 2013072800;
    }

    public function has_config()
    {
        return true;
    }

    public function applicable_formats()
    {
        return [ 'course' => true, 'course-category' => false, 'mod' => false, 'my' => false ];
    }

    /**
     * Returns the block content
     *
     * @global core_renderer $OUTPUT
     * @return stdClass|string
     */
    public function get_content()
    {
        global $OUTPUT;

        if ($this->content !== null)
            return $this->content;

        if (!has_capability('moodle/grade:viewall', context_course::instance($this->page->course->id)))
            return $this->content = '';

        $editicon = $OUTPUT->pix_icon('i/edit', '', 'moodle', [ 'class' => 'icon' ]);
        $html = html_writer::start_tag('ul');
        $html .= html_writer::tag('li',
            $OUTPUT->action_link(
                new moodle_url('/blocks/bayes/editpriorprobability.php', [ 'id' => $this->page->course->id ]),
                $editicon . get_string('editpriorprobability', __CLASS__)
                )
            );
        $html .= html_writer::end_tag('ul');

        $this->page->requires->css('/blocks/bayes/styles.css');

        return $this->content = (object)[ 'text' => $html ];
    }
}
