<?php namespace LogExpander\Events;

class AssignmentGraded extends Event {
    /**
     * Reads data for an event.
     * @param [String => Mixed] $opts
     * @return [String => Mixed]
     * @override Event
     */
    public function read(array $opts) {
        $parent = parent::read($opts);
        if (is_null($parent)) {
            return null;
        }

        $grade = $this->repo->readObject($opts['objectid'], $opts['objecttable']);
        if ($grade === false) {
            return null;
        }

        $grade_comment = $this->repo->readGradeComment($grade->id, $grade->assignment)->commenttext;
        $grade_items = $this->repo->readGradeItems($grade->assignment, 'assign');
        $graded_user = $this->repo->readUser($grade->userid);
        $module = $this->repo->readModule($grade->assignment, 'assign');
        if (
            ($grade_comment === false) 
            || ($grade_items === false) 
            || ($graded_user === false) 
            || ($module === false) 
        ){
            return null;
        }
        return array_merge(parent::read($opts), [
            'grade' => $grade,
            'grade_comment' => $grade_comment,
            'grade_items' => $grade_items,
            'graded_user' => $graded_user,
            'module' => $module,
        ]);
    }
}