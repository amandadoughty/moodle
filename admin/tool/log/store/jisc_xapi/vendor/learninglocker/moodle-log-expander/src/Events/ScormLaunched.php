<?php namespace LogExpander\Events;

class ScormLaunched extends Event {
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
        
        $scorm_scoes = $this->repo->readObject($opts['objectid'], $opts['objecttable']);
        if ($scorm_scoes === false) {
            return null;
        }

        $module = $this->repo->readModule($scorm_scoes->scorm, 'scorm');
        if ($module === false) {
            return null;
        }

        return array_merge(parent::read($opts), [
            'module' => $module,
            'scorm_scoes' => $scorm_scoes
        ]);
    }
}