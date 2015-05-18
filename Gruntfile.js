/* jshint: node:true */
module.exports = function (grunt) {
    'use strict';

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
        }
    });

    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-phplint');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-phpcpd');
    grunt.loadNpmTasks('grunt-phpdcd');
    grunt.loadNpmTasks('grunt-phpmd');

    grunt.registerTask("default", [
        "phplint",
        "phpunit",
        "phpcpd"
    ]);
    
    grunt.registerTask('travis', [
        "phplint",
        "phpunit",
        "phpcpd"
    ]);
};