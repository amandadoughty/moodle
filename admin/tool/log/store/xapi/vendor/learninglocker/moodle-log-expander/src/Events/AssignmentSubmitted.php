<?php namespace LogExpander\Events;

class AssignmentSubmitted extends Event {
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
        
        $submission = $this->repo->readObject($opts['objectid'], $opts['objecttable']);
        if ($submission === false) {
            return null;
        }

        $module = $this->repo->readModule($submission->assignment, 'assign');
        if ($module  === false) {
            return null;
        }
        return array_merge(parent::read($opts), [
            'submission' => $submission,
            'module' => $module,
        ]);
    }
}