<?php namespace LogExpander\Events;

class FeedbackSubmitted extends ModuleEvent {
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

        $attempt = $this->repo->readFeedbackAttempt($opts['objectid']); 
        if ($attempt === false) {
            return null;
        }
        $questions = $this->repo->readFeedbackQuestions($attempt->feedback);
        $module = $this->repo->readModule($attempt->feedback, 'feedback');
        if (
            ($questions === false) 
            || ($module === false) 
        ){
            return null;
        }

        return array_merge(parent::read($opts), [
            'questions' => $questions,
            'attempt' => $attempt,
            'module' => $module
        ]);
    }
}