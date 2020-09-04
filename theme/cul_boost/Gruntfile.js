"use strict";
 
module.exports = function (grunt) {
 
    // We need to include the core Moodle grunt file too, otherwise we can't run tasks like "amd".
    require("grunt-load-gruntfile")(grunt);
    grunt.loadGruntfile("../../Gruntfile.js");
 
    // Load contrib tasks.
    grunt.loadNpmTasks("grunt-sass");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-exec");
    grunt.loadNpmTasks("grunt-text-replace");

    // Load core tasks.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint'); 
 
    grunt.initConfig({
        watch: {
            options: {
                spawn: false,
                livereload: true
            },
            scss: {
                files: ["scss/**/*.scss"],
                tasks: ["compile"]
            },
        },
        sass: {
            // Compile moodle styles.
            moodle: {
                options: {
                    compress: true,
                    sourceMap: false,
                },
                files: [{
                    src: 'scss/moodle.scss',
                    dest: 'style/moodle.css'
                }]
            },
            // Compile editor styles.
            editor: {
                options: {
                    compress: true,
                    sourcemap: false,
                },
                files: [{
                    src: 'scss/editor.scss',
                    dest: 'style/editor.css'
                }]
            }
        },
        replace: {
            font_fix: {
                src: 'style/moodle.css',
                    overwrite: true,
                    replacements: [{
                        from: '../fonts/',
                        to: '[[fontsdir]]',
                    }]
            }
        },
        exec: {
            postcss: {
                command: 'npm run postcss'
            },
            deletesourcemap: {
                command: 'rm -rf style/*.map'
            },
            // decache: 'vagrant ssh -c "cd ' + vagrantmoodleroot + ' && sudo -u www-data /usr/bin/php admin/cli/purge_caches.php"'
        }
    });
    // The default task (running "grunt" in console).
    // Register tasks.
    grunt.registerTask("default", ["watch"]);

    grunt.registerTask("compile", [
        "sass",
        "replace:font_fix",
        "exec:postcss",
        // "exec:deletesourcemap",
    ]);
};