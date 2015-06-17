/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var combo  = require('./combo'),
    themes = require('./themes'),
    _      = require('underscore');

var themeOptions = {};

_.each(themes, function(theme, name) {
    themeOptions[name] = {
        files: combo.lessFiles(name)
    };
});

var lessOptions = {
    options: {
        sourceMap: true,
        strictImports: false,
        sourceMapRootpath: '/',
        dumpLineNumbers: false, // use 'comments' instead false to output line comments for source
        ieCompat: false
    },
    backend: {
        files: combo.lessFiles('backend')
    },
    blank: {
        files: combo.lessFiles('blank')
    },
    luma: {
        files: combo.lessFiles('luma')
    },
    setup: {
        files: {
            '<%= path.css.setup %>/setup.css': '<%= path.less.setup %>/setup.less'
        }
    },
    upgrade: {
        files: {
            '<%= path.css.upgrade %>/upgrade.css': '<%= path.less.upgrade %>/upgrade.less'
        }
    },
    documentation: {
        files: {
            '<%= path.doc %>/docs.css': '<%= path.doc %>/source/docs.less'
        }
    }
};

/**
 * Compiles Less to CSS and generates necessary files if requested.
 */
module.exports = _.extend(themeOptions, lessOptions);
