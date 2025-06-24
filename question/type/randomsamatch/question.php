<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Matching question definition class.
 *
 * @package   qtype_randomsamatch
 * @copyright 2013 Jean-Michel Vedrine
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/match/question.php');

use core_question\local\bank\question_version_status;

/**
 * Represents a randomsamatch question.
 *
 * @copyright 22013 Jean-Michel Vedrine
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_randomsamatch_question extends qtype_match_question {
    /** @var qtype_randomsamatch_question_loader helper for loading the shortanswer questions. */
    public $questionsloader;

    /** @var int the number of subquestions to randomly create. */
    public $choose;

    /** @var bool whether to include questions from subactegories when making the random selection. */
    public $subcats;

    public function start_attempt(question_attempt_step $step, $variant) {
        $saquestions = $this->questionsloader->load_questions();
        foreach ($saquestions as $wrappedquestion) {
            // Store and save stem text and format.
            $this->stems[$wrappedquestion->id] = $wrappedquestion->questiontext;
            $this->stemformat[$wrappedquestion->id] = $wrappedquestion->questiontextformat;
            $step->set_qt_var('_stem_' . $wrappedquestion->id, $this->stems[$wrappedquestion->id]);
            $step->set_qt_var('_stemformat_' . $wrappedquestion->id, $this->stemformat[$wrappedquestion->id]);

            // Find, store and save right choice id.
            $key = $this->find_right_answer($wrappedquestion);
            $this->right[$wrappedquestion->id] = $key;
            $step->set_qt_var('_right_' . $wrappedquestion->id, $key);
            // No need to save saquestions, it will be saved by parent class in _stemorder.
        }

        // Save all the choices.
        foreach ($this->choices as $key => $answer) {
            $step->set_qt_var('_choice_' . $key, $answer);
        }

        parent::start_attempt($step, $variant);
    }

    /**
     * Find the corresponding choice id of the first correct answer of a shortanswer question.
     * choice is added to the randomsamatch question if it doesn't already exist.
     * @param object $wrappedquestion short answer question.
     * @return int correct choice id.
     */
    public function find_right_answer($wrappedquestion) {
        // We only take into account *one* (the first) correct answer.
        while ($answer = array_shift($wrappedquestion->answers)) {
            if (!question_state::graded_state_for_fraction(
                    $answer->fraction)->is_incorrect()) {
                // Store this answer as a choice, only if this is a new one.
                $key = array_search($answer->answer, $this->choices);
                if ($key === false) {
                    $key = $answer->id;
                    $this->choices[$key] = $answer->answer;
                }
                return $key;
            }
        }
        // We should never get there.
        throw new coding_exception('shortanswerquestionwithoutrightanswer', $wrappedquestion->id);

    }

    public function apply_attempt_state(question_attempt_step $step) {
        $saquestions = explode(',', $step->get_qt_var('_stemorder'));
        foreach ($saquestions as $questionid) {
            $this->stems[$questionid] = $step->get_qt_var('_stem_' . $questionid);
            $this->stemformat[$questionid] = $step->get_qt_var('_stemformat_' . $questionid);
            $key = $step->get_qt_var('_right_' . $questionid);
            $this->right[$questionid] = $key;
            $this->choices[$key] = $step->get_qt_var('_choice_' . $key);
        }
        parent::apply_attempt_state($step);
    }

    /**
     * Get the latest version of a question that's ready for use.
     *
     * @param int $stemid Question ID.
     * @return question_definition The latest version of the question
     * corresponding to the specified ID, or null if there are no versions
     * available (none have status = "ready").
     */
    private function get_latest_stem_version(int $stemid): ?question_definition {
        $stemversions = question_bank::get_all_versions_of_question($stemid);

        foreach ($stemversions as $stemversion) {
            $stem = question_bank::load_question($stemversion->questionid);
            if ($stem->status == question_version_status::QUESTION_STATUS_READY) {
                return $stem;
            }
        }

        return null;
    }

    #[\Override]
    public function validate_can_regrade_with_other_version(question_definition $otherversion): ?string {
        global $DB;

        if ($this->choose !== $otherversion->choose) {
            return get_string('choosehaschanged', 'qtype_randomsamatch');
        }

        // Check if the short-answer questions used by this attempt are
        // available. Unfortunately we can't verify these prior to calling
        // update_attempt_state_data_for_new_version() because
        // $otherversion->stems is not populated prior to this. Consequently if
        // the check below fails the teacher gets a scary red error instead of
        // a yellow warning.
        foreach ($otherversion->stems as $stemid => $unused) {
            if (!$DB->record_exists('question', ['id' => $stemid])) {
                return get_string('questiondeleted', 'qtype_randomsamatch', $stemid);
            }

            if (!$this->get_latest_stem_version($stemid)) {
                return get_string('questionnotready', 'qtype_randomsamatch', $stemid);
            }
        }

        return null;
    }

    #[\Override]
    public function update_attempt_state_data_for_new_version(
            question_attempt_step $oldstep, question_definition $otherversion) {
        $message = $this->validate_can_regrade_with_other_version($otherversion);
        if ($message) {
            throw new moodle_exception('cannotregrade', 'qtype_randomsamatch', '', $message);
        }

        $startdata = $oldstep->get_qt_data();
        $stemids = explode(',', $oldstep->get_qt_var('_stemorder'));
        $stems = [];
        $choicesmap = [];

        foreach ($stemids as $oldstemid) {
            $stem = $this->get_latest_stem_version($oldstemid);

            // If the latest version of the short-answer question is different
            // to that in the attempt update the corresponding attempt values.
            if ($stem->id !== $oldstemid) {
                unset($startdata['_stem_' . $oldstemid]);
                $startdata['_stem_' . $stem->id] = $stem->questiontext;
                unset($startdata['_stemformat_' . $oldstemid]);
                $startdata['_stemformat_' . $stem->id] = $stem->questiontextformat;
                $rightchoice = $this->find_right_answer($stem);
                $choicesmap[$startdata['_right_' . $oldstemid]] = $rightchoice;
                unset($startdata['_right_' . $oldstemid]);
                $startdata['_right_' . $stem->id] = $rightchoice;
            }

            $stems[] = $stem->id;
        }

        $startdata['_stemorder'] = implode(',', $stems);
        $choiceids = explode(',', $oldstep->get_qt_var('_choiceorder'));
        $newchoiceids = [];

        // If there's a new version of the short-answer question update the
        // corresponding choice values.
        foreach ($choiceids as $key => $oldchoiceid) {
            if (isset($choicesmap[$oldchoiceid])) {
                $newchoiceid = $choicesmap[$oldchoiceid];
                unset($startdata['_choice_' . $oldchoiceid]);
                $startdata['_choice_' . $newchoiceid] = $this->choices[$newchoiceid];
                $choiceids[$key] = $newchoiceid;
            }
        }

        $startdata['_choiceorder'] = implode(',', $choiceids);

        return $startdata;
    }
}

/**
 * This class is responsible for loading the questions that a question needs from the database.
 *
 * @copyright  2013 Jean-Michel vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_randomsamatch_question_loader {
    /** @var array hold available shortanswers questionid to choose from. */
    protected $availablequestions;
    /** @var int how many questions to load. */
    protected $choose;

    /**
     * Constructor
     * @param array $availablequestions array of available question ids.
     * @param int $choose how many questions to load.
     */
    public function __construct($availablequestions, $choose) {
        $this->availablequestions = $availablequestions;
        $this->choose = $choose;
    }

    /**
     * Choose and load the desired number of questions.
     * @return array of short answer questions.
     */
    public function load_questions() {
        if ($this->choose > count($this->availablequestions)) {
            throw new coding_exception('notenoughtshortanswerquestions');
        }

        $questionids = draw_rand_array($this->availablequestions, $this->choose);
        $questions = array();
        foreach ($questionids as $questionid) {
            $questions[] = question_bank::load_question($questionid);
        }
        return $questions;
    }
}
