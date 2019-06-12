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

namespace mod_millionaire\model;

defined('MOODLE_INTERNAL') || die();

/**
 * Class level
 *
 * @package    mod_millionaire\model
 * @copyright  2019 Benedikt Kulmann <b@kulmann.biz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class level extends abstract_model {

    const STATE_PRIVATE = 'private';
    const STATE_ACTIVE = 'active';
    const STATE_DELETED = 'deleted';

    /**
     * @var int The id of the millionaire instance this level belongs to.
     */
    protected $game;
    /**
     * @var string The state of the level, out of [private, active, deleted].
     */
    protected $state;
    /**
     * @var string The name of the level.
     */
    protected $name;
    /**
     * @var int Position for ordering levels.
     */
    protected $position;
    /**
     * @var int The score the user reaches when answering the question of this level correctly.
     */
    protected $score;
    /**
     * @var bool Whether or not this level is a safe spot.
     */
    protected $safe_spot;

    /**
     * level constructor.
     */
    function __construct() {
        parent::__construct('millionaire_levels', 0);
        $this->game = 0;
        $this->state = 'private';
        $this->name = '';
        $this->position = 0;
        $this->score = 0;
        $this->safe_spot = false;
    }

    /**
     * Apply data to this object from an associative array or an object.
     *
     * @param mixed $data
     *
     * @return void
     */
    public function apply($data): void {
        if (\is_object($data)) {
            $data = get_object_vars($data);
        }
        $this->id = isset($data['id']) ? $data['id'] : 0;
        $this->game = $data['game'];
        $this->state = isset($data['state']) ? $data['state'] : self::STATE_PRIVATE;
        $this->name =  isset($data['name']) ? $data['name'] : '';
        $this->position = isset($data['position']) ? $data['position'] : 0;
        $this->score = isset($data['score']) ? $data['score'] : 0;
        $this->safe_spot = isset($data['safe_spot']) ? ($data['safe_spot'] == 1) : false;
    }

    /**
     * Returns one random question out of the categories that are assigned to this level.
     *
     * @return \question_definition
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public function get_random_question(): \question_definition {
        $category = $this->get_random_category();
        return $category->get_random_question();
    }

    /**
     * Returns one random of those categories that are assigned to this level. Throws an exception
     * if this level has no categories.
     *
     * @return category
     * @throws \dml_exception
     */
    private function get_random_category(): category {
        global $DB;
        $sql = "
            SELECT *
              FROM {millionaire_categories}
             WHERE level = :level 
          ORDER BY RAND()
             LIMIT 0,1
        ";
        $result = $DB->get_record_sql($sql, ['level' => $this->get_id()]);
        if ($result) {
            $category = new category();
            $category->apply($result);
            return $category;
        } else {
            throw new \dml_exception('not found');
        }
    }

    /**
     * @return int
     */
    public function get_game(): int {
        return $this->game;
    }

    /**
     * @param int $game
     */
    public function set_game(int $game): void {
        $this->game = $game;
    }

    /**
     * @return string
     */
    public function get_state(): string {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function set_state(string $state): void {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function set_name(string $name): void {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function get_position(): int {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function set_position(int $position): void {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function get_score(): int {
        return $this->score;
    }

    /**
     * @param int $score
     */
    public function set_score(int $score): void {
        $this->score = $score;
    }

    /**
     * @return bool
     */
    public function is_safe_spot(): bool {
        return $this->safe_spot;
    }

    /**
     * @param bool $safe_spot
     */
    public function set_safe_spot(bool $safe_spot): void {
        $this->safe_spot = $safe_spot;
    }
}
