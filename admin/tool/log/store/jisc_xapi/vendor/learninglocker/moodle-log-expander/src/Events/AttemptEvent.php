<?php namespace LogExpander\Events;

class AttemptEvent extends Event {
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

        $attempt = $this->repo->readAttempt($opts['objectid']);
        if ($attempt === false) {
            return null;
        }

        $grade_items = $this->repo->readGradeItems($attempt->quiz, 'quiz');
        $attempt->questions = $this->repo->readQuestionAttempts($attempt->id);
        $questions = $this->repo->readQuestions($attempt->quiz);
        $module = $this->repo->readModule($attempt->quiz, 'quiz');
        if (
            ($attempt->questions === false) 
            || ($grade_items === false) 
            || ($questions === false) 
            || ($module === false) 
        ){
            return null;
        }
        
        return array_merge(parent::read($opts), [
            'attempt' => $attempt,
            'module' => $module,
            'grade_items' => $grade_items,
            'questions' => $questions
        ]);
    }
}