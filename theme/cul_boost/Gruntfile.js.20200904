/**
 * Gruntfile for compiling theme_cul_boost .sass files.

 * Requirements:
 * -------------
 * nodejs, npm, grunt-cli.
 *
 * Installation:
 * -------------
 * node and npm: instructions at http://nodejs.org/
 *
 * grunt-cli: `[sudo] npm install -g grunt-cli`
 *
 * node dependencies: run `npm install` in the root directory.
 *
 *
 * Usage:
 * ------
 * Call tasks from the theme root directory. Default behaviour
 * (calling only `grunt`) is to run the watch task detailed below.
 *
 *
 * Porcelain tasks:
 * ----------------
 * The nice user interface intended for everyday use. Provide a
 * high level of automation and convenience for specific use-cases.
 *
 * grunt watch   Watch the less directory (and all subdirectories)
 *               for changes to *.less files then on detection
 *               run 'grunt compile'
 *
 *               Options:
 *
 *               --dirroot=<path>  Optional. Explicitly define the
 *                                 path to your Moodle root directory
 *                                 when your theme is not in the
 *                                 standard location.
 * grunt compile Run the .less files through the compiler, create the
 *               RTL version of the output, then run decache so that
 *               the results can be seen on the next page load.
 *
 *               Options:
 *
 *               --dirroot=<path>  Optional. Explicitly define the
 *                                 path to your Moodle root directory
 *                                 when your theme is not in the
 *                                 standard location.
 *
 * grunt amd     Create the Asynchronous Module Definition JavaScript files.  See: MDL-49046.
 *               Done here as core Gruntfile.js currently *nix only.
 *
 * @package theme
 * @subpackage cul_boost
 * @author Stephen Sharpe
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

module.exports = function(grunt) {
var DEBUG = !!grunt.option('dbug');
    require('time-grunt')(grunt);

    // Import modules.
    var path = require('path');

    // Theme Bootstrap constants.
    var SCSSDIR         = 'scss',
        THEMEDIR        = path.basename(path.resolve('.'));

    // PHP strings for exec task.
    var moodleroot = path.dirname(path.dirname(__dirname)), // jshint ignore:line
        configfile = '',
        dirrootopt = grunt.option('dirroot') || process.env.MOODLE_DIR || ''; // jshint ignore:line

    // Allow user to explicitly define Moodle root dir.
    if ('' !== dirrootopt) {
        moodleroot = path.resolve(dirrootopt);
    }
    var PWD = process.cwd(); // jshint ignore:line
    var moodleroot = path.dirname(path.dirname(PWD));
    var moodlename = path.basename(moodleroot);
    var projectype = path.basename(path.dirname(moodleroot));
    var vagrantmoodleroot = path.join('/var/www/html/', projectype, moodlename);

    grunt.initConfig({
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
        watch: {
            options: {
                spawn: false,
                livereload: true
            },
            scss: {
                files: ["scss/**/*.scss"],
                tasks: ["compile"]
            },
            amd: {
                files: ["amd/src/**/*.js"],
                tasks: ["amd"]
            },
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
        jshint: {
            options: {jshintrc: moodleroot + '/.jshintrc'},
            files: ['**/amd/src/*.js']
        },
        uglify: {
            dynamic_mappings: {
                files: grunt.file.expandMapping(
                    ['**/src/*.js', '!**/node_modules/**'],
                    '',
                    {
                        cwd: PWD,
                        rename: function(destBase, destPath) {
                            destPath = destPath.replace('src', 'build');
                            destPath = destPath.replace('.js', '.min.js');
                            destPath = path.resolve(PWD, destPath);
                            return destPath;
                        }
                    }
                )
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

    // Load contrib tasks.
    grunt.loadNpmTasks("grunt-sass");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-exec");
    grunt.loadNpmTasks("grunt-text-replace");

    // Load core tasks.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');

    // Register tasks.
    grunt.registerTask("default", ["watch"]);
    grunt.registerTask("decache", ["exec:decache"]);

    grunt.registerTask("compile", [
        "sass",
        "replace:font_fix",
        "exec:postcss",
        "exec:deletesourcemap",
        // "decache"
        ]);
    grunt.registerTask("amd", [
        "uglify", 
        // "decache"
        ]);
};
