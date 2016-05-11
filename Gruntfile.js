/* jshint: node:true */
module.exports = function (grunt) {
    'use strict';

    require('time-grunt')(grunt);

    grunt.initConfig({
        phplint: {
            application: ["lib/*.php", "tests/**/*.php"]
        },
        phpcs: {
            application: {
                src: 'lib/*.php'
            },
            options: {
                bin: 'vendor/bin/phpcs',
                standard: 'PSR1',
                showSniffCodes: true,
                verbose: true
            }
        },
        phpunit: {
            application: {
                dir: 'tests/'
            },
            options: {
                bin: 'vendor/bin/phpunit',
                colors: true,
                coverage: true
            }
        },
        phpcpd: {
            application: {
                dir: 'lib/'
            },
            options: {
                bin: 'vendor/bin/phpcpd',
                quiet: true
            }
        },
        phpmd: {
            application: {
                dir: 'lib/'
            },
            options: {
                bin: 'vendor/bin/phpmd',
                reportFormat: 'text',
                rulesets: 'codesize,unusedcode,naming,design'
            }
        },
        phpdcd: {
            application: {
                dir: 'lib/'
            },
            options: {
                bin: 'vendor/bin/phpdcd',
                verbose: 2
            }
        // },
        // phpdocumentor: {
        //     dist: {
        //         options: {
        //             command: 'run',
        //             directory: './lib/'
        //         }
        //     }
        }
    });

    require('load-grunt-tasks')(grunt);

    grunt.registerTask("default", [
        "phplint",
        "phpunit",
        "phpcpd",
        "phpmd"
    ]);

    grunt.registerTask("lint", ["phplint", "phpcpd", "phpcs"]);
    
    grunt.registerTask('travis', [
        "phplint",
        "phpunit",
        "phpcpd",
        "phpmd"
    ]);
};