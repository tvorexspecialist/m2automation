/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('component-grid', ['ngStorage'])
    .controller('componentGridController', ['$scope', '$http', function ($scope, $http) {
      $http.get('index.php/componentGrid/components').success(function(data) {
        $scope.components = data.components;
        $scope.total = data.total;
      });

      $scope.selectedComponent = null;
      $scope.isActiveActionsCell = function(component) {
        return $scope.selectedComponent === component;
      };
      $scope.toggleActiveActionsCell = function(component) {
        $scope.selectedComponent = $scope.selectedComponent == component ? null : component;
      };
    }]);
