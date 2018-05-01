
    var ModulenameNAME = 'this_is_a_module_name';
    var MODULENAME = function() {
        MODULENAME.superclass.constructor.apply(this, arguments);
    };
    Y.extend(MODULENAME, Y.Base, {
        initializer : function(config) { // 'config' contains the parameter values
            window.alert('I am in initializer');
        }
    }, {
        NAME : ModulenameNAME, //module name is something mandatory. 
                                // It should be in lower case without space 
                                // as YUI use it for name space sometimes.
        ATTRS : {
                 aparam : {}
        } // Attributes are the parameters sent when the $PAGE->requires->yui_module calls the module. 
          // Here you can declare default values or run functions on the parameter. 
          // The param names must be the same as the ones declared 
          // in the $PAGE->requires->yui_module call.
    });
    M.format_culcourse = M.format_culcourse || {}; // This line use existing name path if it exists, otherwise create a new one. 
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.format_culcourse.init_dragdrop = function(config) { // 'config' contains the parameter values
        window.alert('I am in the javascript module, Yeah!');
        return new MODULENAME(config); // 'config' contains the parameter values
    };
