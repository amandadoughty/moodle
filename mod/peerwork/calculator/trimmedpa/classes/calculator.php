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
 * trimmedpa calculator.
 *
 * @package    peerwork_calculator_trimmedpa
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace peerworkcalculator_trimmedpa;

defined('MOODLE_INTERNAL') || die();

/**
 * Cass Simple calculator.
 *
 * @package    peerwork_calculator_trimmedpa
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calculator extends \mod_peerwork\peerworkcalculator_plugin {
    // Adding property so that it can be set in testsuites.
    // TODO find a better way.
    public $truncated = null;

    /**
     * Get the name of the simple calculator plugin
     * @return string
     */
    public function get_name() {
        return get_string('trimmedpa', 'peerworkcalculator_trimmedpa');
    }

    /**
     * Get the value of truncated setting
     * @return string
     */
    public function get_truncated() {
        if (is_null($this->truncated)) {
            $this->truncated =
                $this->get_config('trimmedpa_truncated') ?
                $this->get_config('trimmedpa_truncated') :
                get_config('peerworkcalculator_trimmedpa', 'trimmedpa_truncated');
        }

        return $this->truncated;
    }

    /**
     * Calculate.
     *
     * Each member of the group must have an associated key in the $grades,
     * under which an array of the grades they gave to other members indexed
     * by member ID.
     *
     * In the example below, Alice rated Bob 4, and Elaine did not submit any marks..
     *
     * $grades =
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
     *   ];
     *
     * @param array $grades The list of marks given.
     * @param int $groupmark The mark given to the group.
     */
    public function calculate($grades, $groupmark, $noncompletionpenalty = 0, $paweighting = 1) {
        $memberids = array_keys($grades);
        $truncated = $this->get_truncated();
        $upperboundary =
            $this->get_config('trimmedpa_upperboundary') ?
            $this->get_config('trimmedpa_upperboundary') :
            get_config('peerworkcalculator_trimmedpa', 'trimmedpa_upperboundary');
        $lowerboundary =
            $this->get_config('trimmedpa_lowerboundary') ?
            $this->get_config('trimmedpa_lowerboundary') :
            get_config('peerworkcalculator_trimmedpa', 'trimmedpa_lowerboundary');

        // Translate the scales to scores.
        $grades = $this->translate_scales_to_scores($grades);

        // Calculate the reduced scores, and record whether scores were submitted.
        $meanscores = [];

        foreach ($memberids as $memberid) {
            foreach ($grades as $graderid => $gradesgiven) {
                if (!isset($gradesgiven[$memberid])) {
                    $gradesgiven[$graderid] = [];
                    continue;
                }

                $sum = array_reduce($gradesgiven[$memberid], function($carry, $item) {
                    $carry += $item;
                    return $carry;
                });

                $average = count($gradesgiven[$memberid]) > 0 ? $sum / count($gradesgiven[$memberid]) : 0;
                $meanscores[$graderid][$memberid] = $average;
            }
        }

        // Initialise everyone's array of scores at 0.
        $paallscores = array_reduce($memberids, function($carry, $memberid) {
            $carry[$memberid] = [];
            return $carry;
        }, []);

        // Initialise everyone's score at 0.
        $pascores = array_reduce($memberids, function($carry, $memberid) {
            $carry[$memberid] = 0;
            return $carry;
        }, []);

        // Initialise everyone's count at 0.
        $count = array_reduce($memberids, function($carry, $memberid) {
            $carry[$memberid] = 0;
            return $carry;
        }, []);

        // Walk through the individual scores given, and sum them up.
        foreach ($meanscores as $gradesgiven) {
            foreach ($gradesgiven as $memberid => $meanscore) {
                $paallscores[$memberid][] = $meanscore;
                $pascores[$memberid] += $meanscore;
                $count[$memberid] += 1;
            }
        }

        // Mean value.
        // (Rating(B) = ScoreSum(B) - 0.5 *
        // (MaxScore(B) + MinScore(B))) / (ScoreCount(B) - 1).
        array_walk($pascores, function(&$score, $memberid) use($count, $paallscores) {
            $max = max($paallscores[$memberid]);
            $min = min($paallscores[$memberid]);
            $score = $score > 0 ?
            ($score - 0.5 * ($max + $min)) / ($count[$memberid] - 1) :
            0;
        });

        // Average of the mean scores.
        $overallaverating = array_sum($pascores) > 0 ? array_sum($pascores) / count($pascores) : 0;
        $multfactor = 5;

        // Adjustment to final mark.
        // Adjustment(B) = MultiplicativeFactor * (Rating(B) - OverallAverageRating).
        $pascores = array_map(
            function($score) use ($overallaverating, $multfactor) {
                return round($multfactor * ($score - $overallaverating), 1);
            },
            $pascores
        );

        // Truncated adjustment.
        // If Adjustment(B) < UpperZoneBoundary and Adjustment(B) > LowerZoneBoundary then Adjustment(B) = 0.
        if ($truncated) {
            $pascores = array_map(
                function($score) use ($upperboundary, $lowerboundary) {
                    if ($score < $upperboundary && $score > $lowerboundary) {
                        return 0;
                    }

                    return $score;
                },
                $pascores
            );
        }

        // Calculate the students' preliminary grade (excludes penalties).
        $prelimgrades = array_map(function($score) use ($groupmark) {
            return $score + $groupmark;
        }, $pascores);

        // Calculate penalties.
        $noncompletionpenalties = array_reduce($memberids, function($carry, $memberid) use ($meanscores, $noncompletionpenalty) {
            $ispenalised = empty($meanscores[$memberid]);
            $carry[$memberid] = $ispenalised ? $noncompletionpenalty : 0;
            return $carry;
        });

        // Calculate the students' final grade.
        $grades = array_reduce($memberids, function($carry, $memberid) use ($pascores, $noncompletionpenalties, $groupmark) {
            $score = $pascores[$memberid];

            $grade = $score + $groupmark;
            $penaltyamount = $noncompletionpenalties[$memberid];

            if ($penaltyamount > 0) {
                $grade *= (1 - $penaltyamount);
            }

            $carry[$memberid] = $grade;

            return $carry;
        }, []);

        return new \mod_peerwork\pa_result($meanscores, $pascores, $prelimgrades, $grades, $noncompletionpenalties);
    }

    /**
     * Function to return if calculation uses paweighting.
     *
     * @return bool
     */
    public static function usespaweighting() {
        return false;
    }

    /**
     * Function to return the scales that can be used.
     *
     * @return array/bool false if no resriction on scales.
     */
    public static function get_scales_menu($courseid = 0) {
        $availablescales = false;

        if ($config = get_config('peerworkcalculator_simplepa', 'availablescales')) {
            $scales = get_scales_menu($courseid);
            $available = explode(',', $config);
            $availablescales = array_intersect_key($scales, array_flip($available));
        }

        return $availablescales;
    }

    /**
     * Function to translate scale into score.
     *
     * @param array $grades The list of marks given.
     * @return array $grades.
     */
    public function translate_scales_to_scores($prelimgrades) {
        $translator = [
            0 => 0,
            1 => 2,
            2 => 4,
            3 => 5
        ];

        $grades = [];

        foreach ($prelimgrades as $criteriaid => $gradedby) {
            foreach ($gradedby as $gradedbyid => $gradefor) {
                foreach ($gradefor as $gradeforid => $grade) {
                    if ($grade > 3) {
                        $newgrade = $translator[3];
                    } else {
                        $newgrade = $translator[$grade];
                    }

                    $grades[$criteriaid][$gradedbyid][$gradeforid] = $newgrade;
                }
            }
        }

        return $grades;
    }

    /**
     * Get the settings for trimmedpa calculator plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(\MoodleQuickForm $mform) {
        global $CFG, $COURSE;

        $default = $this->get_config('trimmedpa_truncated');
        if ($default === false) {
            // Apply the admin default if we don't have a value yet.
            $default = get_config('peerworkcalculator_trimmedpa', 'trimmedpa_truncated');
        }
        $name = get_string('truncated', 'peerworkcalculator_trimmedpa');
        $mform->addElement('selectyesno', 'trimmedpa_truncated', $name);
        $mform->addHelpButton('trimmedpa_truncated', 'truncated', 'peerworkcalculator_trimmedpa');
        $mform->setDefault('trimmedpa_truncated', $default);
        $mform->setType('trimmedpa_truncated', PARAM_INT);
        $mform->hideIf('trimmedpa_truncated', 'calculator', 'noteq', $this->get_type());

        $default = $this->get_config('trimmedpa_upperboundary');
        if ($default === false) {
            // Apply the admin default if we don't have a value yet.
            $default = get_config('peerworkcalculator_trimmedpa', 'trimmedpa_upperboundary');
        }
        $mform->addElement('hidden', 'trimmedpa_upperboundary', $default);
        $mform->setType('trimmedpa_upperboundary', PARAM_FLOAT);

        $default = $this->get_config('trimmedpa_lowerboundary');
        if ($default === false) {
            // Apply the admin default if we don't have a value yet.
            $default = get_config('peerworkcalculator_trimmedpa', 'trimmedpa_lowerboundary');
        }
        $mform->addElement('hidden', 'trimmedpa_lowerboundary', $default);
        $mform->setType('trimmedpa_lowerboundary', PARAM_FLOAT);
    }

    /**
     * Allows the plugin to update the defaultvalues passed in to
     * the settings form (needed to set up draft areas for editor
     * and filemanager elements)
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        return;
    }

    /**
     * Save the settings for simple calculator plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(\stdClass $data) {
        if (empty($data->trimmedpa_truncated)) {
            $truncated = get_config('peerworkcalculator_trimmedpa', 'trimmedpa_truncated');
        } else {
            $truncated = $data->trimmedpa_truncated;
        }
        if (empty($data->trimmedpa_upperboundary)) {
            $upperboundary = get_config('peerworkcalculator_trimmedpa', 'trimmedpa_upperboundary');
        } else {
            $upperboundary = $data->trimmedpa_upperboundary;
        }
        if (empty($data->trimmedpa_lowerboundary)) {
            $lowerboundary = get_config('peerworkcalculator_trimmedpa', 'trimmedpa_lowerboundary');
        } else {
            $lowerboundary = $data->trimmedpa_lowerboundary;
        }
        $this->set_config('trimmedpa_truncated', $truncated);
        $this->set_config('trimmedpa_upperboundary', $upperboundary);
        $this->set_config('trimmedpa_lowerboundary', $lowerboundary);

        return true;
    }
}
