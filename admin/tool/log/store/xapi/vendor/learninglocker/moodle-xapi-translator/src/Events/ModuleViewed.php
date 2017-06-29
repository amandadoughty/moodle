<?php namespace MXTranslator\Events;

class ModuleViewed extends CourseViewed {
    /**
     * Reads data for an event.
     * @param [String => Mixed] $opts
     * @return [String => Mixed]
     * @override CourseViewed
     */
    public function read(array $opts) {

        $jiscType = static::$xapi_type.$opts['module']->type;

        if ($opts['module']->type=='url'){
            $jiscType = 'http://xapi.jisc.ac.uk/externalURL';
        }

        if ($opts['module']->type=='resource'){
            $jiscType = static::$xapi_type.'content';
        }

        if ($opts['module']->type=='book'){
            $jiscType = static::$xapi_type.'content';
        }

        if ($opts['module']->type=='folder'){
            $jiscType = static::$xapi_type.'module';
        }

        return [array_merge(parent::read($opts)[0], [
            'recipe' => 'module_viewed',
            'module_url' => $opts['module']->url,
            'module_name' => $opts['module']->name,
            'module_description' => $opts['module']->intro ?: 'A module',
            'module_type' => $jiscType,
            'module_ext' => $opts['module'],
            'module_ext_key' => 'http://lrs.learninglocker.net/define/extensions/moodle_module'
        ])];
    }
}
