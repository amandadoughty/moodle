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
    var path = require('path'),
        cwd = process.env.PWD || process.cwd();

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

    var files = null;
    if (grunt.option('files')) {
        // Accept a comma separated list of files to process.
        files = grunt.option('files').split(',');
    }

    var inAMD = path.basename(cwd) == 'amd';

    // Globbing pattern for matching all AMD JS source files.
    var amdSrc = [];
    if (inAMD) {
        amdSrc.push(cwd + "/src/*.js");
        amdSrc.push(cwd + "/src/**/*.js");
    } else {
        amdSrc.push("/amd/src/*.js");
        amdSrc.push("/amd/src/**/*.js");
    }

    /**
     * Function to generate the destination for the uglify task
     * (e.g. build/file.min.js). This function will be passed to
     * the rename property of files array when building dynamically:
     * http://gruntjs.com/configuring-tasks#building-the-files-object-dynamically
     *
     * @param {String} destPath the current destination
     * @param {String} srcPath the  matched src path
     * @return {String} The rewritten destination path.
     */
    var babelRename = function(destPath, srcPath) {
        destPath = srcPath.replace('src', 'build');
        destPath = destPath.replace('.js', '.min.js');
        return destPath;
    };

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
                files: ["amd/src/*.js"],
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
            files: ['/amd/src/*.js']
        },
        exec: {
            postcss: {
                command: 'npm run postcss'
            },
            deletesourcemap: {
                command: 'rm -rf style/*.map'            }
        },
        babel: {
            options: {
                sourceMaps: true,
                comments: false,
                plugins: [
                    'transform-es2015-modules-amd-lazy',
                    'system-import-transformer',
                    // This plugin modifies the Babel transpiling for "export default"
                    // so that if it's used then only the exported value is returned
                    // by the generated AMD module.
                    //
                    // It also adds the Moodle plugin name to the AMD module definition
                    // so that it can be imported as expected in other modules.
                    path.resolve(moodleroot + '/babel-plugin-add-module-to-define.js'),
                    '@babel/plugin-syntax-dynamic-import',
                    '@babel/plugin-syntax-import-meta',
                    ['@babel/plugin-proposal-class-properties', {'loose': false}],
                    '@babel/plugin-proposal-json-strings'
                ],
                presets: [
                    ['minify', {
                        // This minification plugin needs to be disabled because it breaks the
                        // source map generation and causes invalid source maps to be output.
                        simplify: false,
                        builtIns: false
                    }],
                    ['@babel/preset-env', {
                        targets: {
                            browsers: [
                                ">0.25%",
                                "last 2 versions",
                                "not ie <= 10",
                                "not op_mini all",
                                "not Opera > 0",
                                "not dead"
                            ]
                        },
                        modules: false,
                        useBuiltIns: false
                    }]
                ]
            },
            dist: {
                files: [{
                    expand: true,
                    src: files ? files : amdSrc,
                    rename: babelRename
                }]
            }
        }
    });

    // Load contrib tasks.
    grunt.loadNpmTasks("grunt-exec");
    grunt.loadNpmTasks("grunt-text-replace");
    grunt.loadNpmTasks('grunt-contrib-jshint');

    // Load core tasks.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-eslint');
    grunt.loadNpmTasks('grunt-stylelint');
    grunt.loadNpmTasks('grunt-babel');
    

    // Register tasks.
    grunt.registerTask("default", ["watch"]);

    grunt.registerTask("compile", [
        "sass",
        "replace:font_fix",
        "exec:postcss",
        "exec:deletesourcemap"
    ]);

    grunt.registerTask("amd", ["babel"]);
};