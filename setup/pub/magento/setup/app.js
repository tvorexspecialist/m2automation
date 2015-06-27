/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
var app = angular.module(
    'magentoSetup',
    [
        'ui.router',
        'ui.bootstrap',
        'main',
        'landing-installer',
        'readiness-check-installer',
        'add-database',
        'web-configuration',
        'customize-your-store',
        'create-admin-account',
        'install',
        'success',
        'landing-updater',
        'readiness-check-updater',
        'create-backup',
        'complete-backup',
        'component-upgrade',
        'component-upgrade-success'
    ]);

app.config(function ($stateProvider) {
    app.stateProvider = $stateProvider;
})
.config(function($provide) {
    $provide.decorator('$state', function($delegate, $stateParams) {
        $delegate.forceReload = function() {
            return $delegate.go($delegate.current, $stateParams, {
                reload: true,
                inherit: false,
                notify: true
            });
        };
        return $delegate;
    });
}).run(function ($rootScope, $state) {
    $rootScope.$state = $state;
});
