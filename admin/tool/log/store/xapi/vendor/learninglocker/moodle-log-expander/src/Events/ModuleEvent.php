<?php namespace LogExpander\Events;

class ModuleEvent extends Event {
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
        
        $module = $this->repo->readModule($opts['objectid'], $opts['objecttable']);
        if ($module == false){
            return null;
        }
        return array_merge(parent::read($opts), [
            'module' => $module,
        ]);
    }
}