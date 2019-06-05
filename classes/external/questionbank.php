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

namespace mod_millionaire\external;

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_value;
use mod_millionaire\external\exporter\gamesession_dto;
use mod_millionaire\external\exporter\mdl_answer_dto;
use mod_millionaire\external\exporter\mdl_question_dto;
use mod_millionaire\external\exporter\question_dto;
use mod_millionaire\model\game;
use mod_millionaire\model\gamesession;
use mod_millionaire\model\question;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Class questionbank
 *
 * @package    mod_millionaire\external
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class questionbank extends external_api {

    public static function get_mdl_question_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'mdlquestionid' => new external_value(PARAM_INT, 'the id of the moodle question')
        ]);
    }

    public static function get_mdl_question_returns() {
        return mdl_question_dto::get_read_structure();
    }

    /**
     * Gets data of a specific moodle question.
     *
     * @param int $coursemoduleid
     * @param int $mdlquestionid
     *
     * @return \stdClass
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \restricted_context_exception
     */
    public static function get_mdl_question($coursemoduleid, $mdlquestionid) {
        $params = ['coursemoduleid' => $coursemoduleid, 'mdlquestionid' => $mdlquestionid];
        self::validate_parameters(self::get_mdl_answers_parameters(), $params);

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'millionaire');
        self::validate_context($coursemodule->context);

        global $PAGE, $DB;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;

        // load the moodle question
        $mdl_question = \question_bank::load_question($mdlquestionid, false);

        $exporter = new mdl_question_dto($mdl_question, $ctx);
        return $exporter->export($renderer);
    }

    public static function get_mdl_answers_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'mdlquestionid' => new external_value(PARAM_INT, 'the id of the moodle question')
        ]);
    }

    public static function get_mdl_answers_returns() {
        return new external_multiple_structure(
            mdl_answer_dto::get_read_structure()
        );
    }

    /**
     * Gets all answers for a specific moodle question.
     *
     * @param int $coursemoduleid
     * @param int $mdlquestionid
     *
     * @return array
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \restricted_context_exception
     */
    public static function get_mdl_answers($coursemoduleid, $mdlquestionid) {
        $params = ['coursemoduleid' => $coursemoduleid, 'mdlquestionid' => $mdlquestionid];
        self::validate_parameters(self::get_mdl_answers_parameters(), $params);

        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'millionaire');
        self::validate_context($coursemodule->context);

        global $PAGE, $DB;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;

        // load the moodle question
        $mdl_question = \question_bank::load_question($mdlquestionid, false);

        // transform answers
        $result = [];
        if (property_exists($mdl_question, 'answers')) {
            foreach ($mdl_question->answers as $answer) {
                $exporter = new mdl_answer_dto($answer, $ctx);
                $result[] = $exporter->export($renderer);
            }
        }
        return $result;
    }
}