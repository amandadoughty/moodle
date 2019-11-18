<?php namespace LogExpander\Events;

class DiscussionEvent extends Event {
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
        
        $discussion = $this->repo->readDiscussion($opts['objectid']);
        if ($discussion === false) {
            return null;
        }

        $module = $this->repo->readModule($discussion->forum, 'forum');
        if ($module === false) {
            return null;
        }

        return array_merge(parent::read($opts), [
            'discussion' => $discussion,
            'module' => $module,
        ]);

    }
}