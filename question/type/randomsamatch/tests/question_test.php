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

namespace qtype_randomsamatch;

use question_attempt_step;
use question_state;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');


/**
 * Unit tests for the random shortanswer matching question definition class.
 *
 * @package   qtype_randomsamatch
 * @copyright 2013 Jean-Michel Vedrine
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \qtype_randomsamatch\question
 */
class question_test extends \advanced_testcase {

    public function test_get_expected_data(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $question->start_attempt(new question_attempt_step(), 1);

        $this->assertEquals(array('sub0' => PARAM_INT, 'sub1' => PARAM_INT,
                'sub2' => PARAM_INT, 'sub3' => PARAM_INT), $question->get_expected_data());
    }

    public function test_is_complete_response(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $question->start_attempt(new question_attempt_step(), 1);

        $this->assertFalse($question->is_complete_response(array()));
        $this->assertFalse($question->is_complete_response(
                array('sub0' => '1', 'sub1' => '1', 'sub2' => '1', 'sub3' => '0')));
        $this->assertFalse($question->is_complete_response(array('sub1' => '1')));
        $this->assertTrue($question->is_complete_response(
                array('sub0' => '1', 'sub1' => '1', 'sub2' => '1', 'sub3' => '1')));
    }

    public function test_is_gradable_response(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $question->start_attempt(new question_attempt_step(), 1);

        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertFalse($question->is_gradable_response(
                array('sub0' => '0', 'sub1' => '0', 'sub2' => '0', 'sub3' => '0')));
        $this->assertTrue($question->is_gradable_response(
                array('sub0' => '1', 'sub1' => '0', 'sub2' => '0', 'sub3' => '0')));
        $this->assertTrue($question->is_gradable_response(array('sub1' => '1')));
        $this->assertTrue($question->is_gradable_response(
                array('sub0' => '1', 'sub1' => '1', 'sub2' => '3', 'sub3' => '1')));
    }

    public function test_is_same_response(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $question->start_attempt(new question_attempt_step(), 1);

        $this->assertTrue($question->is_same_response(
                array(),
                array('sub0' => '0', 'sub1' => '0', 'sub2' => '0', 'sub3' => '0')));

        $this->assertTrue($question->is_same_response(
                array('sub0' => '0', 'sub1' => '0', 'sub2' => '0', 'sub3' => '0'),
                array('sub0' => '0', 'sub1' => '0', 'sub2' => '0', 'sub3' => '0')));

        $this->assertFalse($question->is_same_response(
                array('sub0' => '0', 'sub1' => '0', 'sub2' => '0', 'sub3' => '0'),
                array('sub0' => '1', 'sub1' => '2', 'sub2' => '3', 'sub3' => '1')));

        $this->assertTrue($question->is_same_response(
                array('sub0' => '1', 'sub1' => '2', 'sub2' => '3', 'sub3' => '1'),
                array('sub0' => '1', 'sub1' => '2', 'sub2' => '3', 'sub3' => '1')));

        $this->assertFalse($question->is_same_response(
                array('sub0' => '2', 'sub1' => '2', 'sub2' => '3', 'sub3' => '1'),
                array('sub0' => '1', 'sub1' => '2', 'sub2' => '3', 'sub3' => '1')));
    }

    public function test_grading(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $question->start_attempt(new question_attempt_step(), 1);

        $choiceorder = $question->get_choice_order();
        $orderforchoice = array_combine(array_values($choiceorder), array_keys($choiceorder));
        $this->assertEquals(array(1, question_state::$gradedright),
                $question->grade_response(array('sub0' => $orderforchoice[13],
                        'sub1' => $orderforchoice[16], 'sub2' => $orderforchoice[16],
                        'sub3' => $orderforchoice[13])));
        $this->assertEquals(array(0.25, question_state::$gradedpartial),
                $question->grade_response(array('sub0' => $orderforchoice[13])));
        $this->assertEquals(array(0, question_state::$gradedwrong),
                $question->grade_response(array('sub0' => $orderforchoice[16],
                        'sub1' => $orderforchoice[13], 'sub2' => $orderforchoice[13],
                        'sub3' => $orderforchoice[16])));
    }

    public function test_get_correct_response(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $question->start_attempt(new question_attempt_step(), 1);

        $choiceorder = $question->get_choice_order();
        $orderforchoice = array_combine(array_values($choiceorder), array_keys($choiceorder));

        $this->assertEquals(array('sub0' => $orderforchoice[13], 'sub1' => $orderforchoice[16],
                'sub2' => $orderforchoice[16], 'sub3' => $orderforchoice[13]),
                $question->get_correct_response());
    }

    public function test_get_question_summary(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $question->start_attempt(new question_attempt_step(), 1);
        $qsummary = $question->get_question_summary();
        $this->assertMatchesRegularExpression('/' . preg_quote($question->questiontext, '/') . '/', $qsummary);
        foreach ($question->stems as $stem) {
            $this->assertMatchesRegularExpression('/' . preg_quote($stem, '/') . '/', $qsummary);
        }
        foreach ($question->choices as $choice) {
            $this->assertMatchesRegularExpression('/' . preg_quote($choice, '/') . '/', $qsummary);
        }
    }

    public function test_summarise_response(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $question->shufflestems = false;
        $question->start_attempt(new question_attempt_step(), 1);

        $summary = $question->summarise_response(array('sub0' => 2, 'sub1' => 1));

        $this->assertMatchesRegularExpression('/Dog -> \w+; Frog -> \w+/', $summary);
    }

    public function test_validate_can_regrade_with_other_version_ok(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $newquestion = clone($question);
        $result = $newquestion->validate_can_regrade_with_other_version($question);
        $this->assertNull($result, 'Regrade should be possible when stems and choices have not changed.');
    }

    public function test_validate_can_regrade_with_other_version_bad_stems(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        // Ensure stems are populated for the test.
        $question->stems = [1 => 'Stem 1', 2 => 'Stem 2'];
        $this->assertGreaterThan(1, count($question->stems), 'The stems array should contain more than one element.');
        $newquestion = clone($question);
        unset($newquestion->stems[array_key_first($newquestion->stems)]);
        $this->assertCount(count($question->stems) - 1, $newquestion->stems, 'The number of stems should be reduced by 1.');
        $result = $newquestion->validate_can_regrade_with_other_version($question);
        $this->assertEquals(get_string('regradeissuenumstemschanged', 'qtype_randomsamatch'), $result);
    }

    public function test_validate_can_regrade_with_other_version_bad_choices(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        // Ensure choices are populated for the test.
        $question->choices = [1 => 'Choice 1', 2 => 'Choice 2'];
        $this->assertGreaterThan(1, count($question->choices), 'The choices array should contain more than one element.');
        $newquestion = clone($question);
        unset($newquestion->choices[array_key_first($newquestion->choices)]);
        $this->assertCount(count($question->choices) - 1, $newquestion->choices, 'The number of choices should be reduced by 1.');
        $result = $newquestion->validate_can_regrade_with_other_version($question);
        $this->assertEquals(get_string('regradeissuenumchoiceschanged', 'qtype_randomsamatch'), $result);
    }

    public function test_update_attempt_state_data_for_new_version_ok(): void {
        $question = \test_question_maker::make_question('randomsamatch');
        $question->stems = [1 => 'Stem 1', 2 => 'Stem 2', 3 => 'Stem 3'];
        $question->choices = [1 => 'Choice 1', 2 => 'Choice 2', 3 => 'Choice 3'];
        $question->right = [1 => 1, 2 => 2, 3 => 3];
        $newquestion = clone($question);
        $oldstep = new \question_attempt_step();
        $oldstep->set_qt_var('_stemorder', implode(',', array_keys($question->stems)));
        foreach ($question->stems as $key => $value) {
            $oldstep->set_qt_var('_stem_' . $key, $value);
            $oldstep->set_qt_var('_stemformat_' . $key, FORMAT_HTML);
            $oldstep->set_qt_var('_right_' . $key, $question->right[$key]);
            $oldstep->set_qt_var('_choice_' . $question->right[$key], $question->choices[$question->right[$key]]);
        }
        $oldstep->set_qt_var('_choiceorder', implode(',', array_keys($question->choices)));
        $startdata = $newquestion->update_attempt_state_data_for_new_version($oldstep, $question);
        $this->assertEquals($oldstep->get_qt_var('_stemorder'), $startdata['_stemorder']);
        $this->assertEquals($oldstep->get_qt_var('_choiceorder'), $startdata['_choiceorder']);
        foreach ($question->stems as $key => $value) {
            $this->assertEquals($value, $startdata['_stem_' . $key]);
            $this->assertEquals(FORMAT_HTML, $startdata['_stemformat_' . $key]);
            $this->assertEquals($question->right[$key], $startdata['_right_' . $key]);
            $this->assertEquals($question->choices[$question->right[$key]], $startdata['_choice_' . $question->right[$key]]);
        }
    }

}
