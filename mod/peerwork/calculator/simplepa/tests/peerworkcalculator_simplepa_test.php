<?php
// This file is part of a 3rd party created module for Moodle - http://moodle.org/
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
 * simplepa testcase.
 *
 * @package    mod_peerwork
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * simplepa testcase.
 * @group mod_peerwork
 *
 * @package    mod_peerwork
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class peerworkcalculator_simplepa_calculator_testcase extends basic_testcase {

    /**
     * Test the simplepa result with no weighting or penalties.
     */
    public function test_simplepa_result_basic() {
        $peerwork = new \stdClass();
        $peerwork->id = 1;

        $grades = $this->get_sample();
        $calculator = new peerworkcalculator_simplepa\calculator($peerwork, 'simplepa');
        $result = $calculator->calculate($grades, 80);

        $fracs = $result->get_reduced_scores('ludwig');
        $this->assertEquals([
            'john' => 0,
            'jean' => 2.00,
            'soren' => 2.00,
            'rene' => 0.67,
            'george' => 0.67
        ], array_map(function($a) {
            return round($a, 2);  // We must round because the data we were given is rounded.
        }, $fracs));

        $this->assertTrue($result->has_submitted('ludwig'));
        $this->assertTrue($result->has_submitted('john'));
        $this->assertTrue($result->has_submitted('jean'));
        $this->assertTrue($result->has_submitted('soren'));
        $this->assertTrue($result->has_submitted('rene'));
        $this->assertTrue($result->has_submitted('george'));

        // Values are stlightly different from the source because of rounding issues.
        $this->assertEquals(0.6, round($result->get_score('ludwig'), 1));
        $this->assertEquals(-2.1, round($result->get_score('john'), 1));
        $this->assertEquals(2.6, round($result->get_score('jean'), 1));
        $this->assertEquals(2.6, round($result->get_score('soren'), 1));
        $this->assertEquals(-2.4, round($result->get_score('rene'), 1));
        $this->assertEquals(-1.1, round($result->get_score('george'), 1));

        $this->assertEquals(80.60, round($result->get_grade('ludwig'), 2));
        $this->assertEquals(77.90, round($result->get_grade('john'), 2));
        $this->assertEquals(82.60, round($result->get_grade('jean'), 2));
        $this->assertEquals(82.60, round($result->get_grade('soren'), 2));
        $this->assertEquals(77.60, round($result->get_grade('rene'), 2));
        $this->assertEquals(78.90, round($result->get_grade('george'), 2));
    }

    /**
     * Test the simplepa result with truncating.
     */
    public function test_simplepa_result_with_truncating() {
        $peerwork = new \stdClass();
        $peerwork->id = 1;
        $grades = $this->get_sample();
        $calculator = new peerworkcalculator_simplepa\calculator($peerwork, 'simplepa');
        $calculator->truncated = 1;
        $result = $calculator->calculate($grades, 80);

        // Values are stlightly different from the source because of rounding issues.
        $this->assertEquals(0, round($result->get_score('ludwig'), 1));
        $this->assertEquals(-2.1, round($result->get_score('john'), 1));
        $this->assertEquals(2.6, round($result->get_score('jean'), 1));
        $this->assertEquals(2.6, round($result->get_score('soren'), 1));
        $this->assertEquals(-2.4, round($result->get_score('rene'), 1));
        $this->assertEquals(0, round($result->get_score('george'), 1));

        $this->assertEquals(80, round($result->get_grade('ludwig'), 2));
        $this->assertEquals(77.90, round($result->get_grade('john'), 2));
        $this->assertEquals(82.60, round($result->get_grade('jean'), 2));
        $this->assertEquals(82.60, round($result->get_grade('soren'), 2));
        $this->assertEquals(77.60, round($result->get_grade('rene'), 2));
        $this->assertEquals(80, round($result->get_grade('george'), 2));
    }

    /**
     * Test the simplepa result with truncating and penalty.
     */
    public function test_simplepa_result_with_truncating_and_penalty() {
        $peerwork = new \stdClass();
        $peerwork->id = 1;
        $grades = $this->get_sample();
        $calculator = new peerworkcalculator_simplepa\calculator($peerwork, 'simplepa');
        $calculator->truncated = 1;
        $result = $calculator->calculate($grades, 80, .1, .5);

        // This does not affect the scores.
        $this->assertEquals(0, round($result->get_score('ludwig'), 1));
        $this->assertEquals(-2.1, round($result->get_score('john'), 1));
        $this->assertEquals(2.6, round($result->get_score('jean'), 1));
        $this->assertEquals(2.6, round($result->get_score('soren'), 1));
        $this->assertEquals(-2.4, round($result->get_score('rene'), 1));
        $this->assertEquals(0, round($result->get_score('george'), 1));

        // Values are slightly different from the source because of rounding issues.
        $this->assertEquals(80, round($result->get_grade('ludwig'), 2));
        $this->assertEquals(77.9, round($result->get_grade('john'), 2));
        $this->assertEquals(82.6, round($result->get_grade('jean'), 2));
        $this->assertEquals(82.6, round($result->get_grade('soren'), 2));
        $this->assertEquals(77.6, round($result->get_grade('rene'), 2));
        $this->assertEquals(80, round($result->get_grade('george'), 2));
    }

    /**
     * Data sample.
     *
     *
     * @return array
     */
    protected function get_sample() {
        return [
            'ludwig' => [
                'john' => [0, 0, 0],
                'jean' => [1, 1, 1],
                'soren' => [1, 1, 1],
                'rene' => [0, 1, 0],
                'george' => [0, 1, 0]
            ],
            'john' => [
                'ludwig' => [1, 1, 2],
                'jean' => [3, 3, 2],
                'soren' => [3, 2, 3],
                'rene' => [0, 3, 1],
                'george' => [2, 3, 2]
            ],
            'jean' => [
                'ludwig' => [2, 2, 2],
                'john' => [2, 2, 2],
                'soren' => [3, 3, 3],
                'rene' => [2, 3, 2],
                'george' => [2, 3, 2]
            ],
            'soren' => [
                'ludwig' => [2, 2, 2],
                'john' => [2, 2, 2],
                'jean' => [3, 3, 3],
                'rene' => [2, 3, 2],
                'george' => [2, 3, 2]
            ],
            'rene' => [
                'ludwig' => [2, 2, 2],
                'john' => [2, 2, 2],
                'jean' => [3, 3, 3],
                'soren' => [3, 3, 3],
                'george' => [2, 3, 2]
            ],
            'george' => [
                'ludwig' => [3, 3, 3],
                'john' => [3, 3, 3],
                'jean' => [3, 3, 3],
                'soren' => [3, 3, 3],
                'rene' => [3, 3, 3]
            ]
        ];
    }
}
