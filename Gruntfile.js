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
                colors: true,
                coverage: true
            }
        },
        phpcpd: {
            application: {
                dir: 'lib/'
            },
            options: {
                quiet: true
            }
        },
        phpmd: {
            application: {
                dir: 'lib/'
            },
            options: {
                rulesets: 'codesize,unusedcode,naming,design'
            }
        }
    });

    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-phplint');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-phpcpd');
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