/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('select-version', ['ngStorage'])
    .controller('selectVersionController', ['$scope', '$http', '$localStorage', function ($scope, $http, $localStorage) {
        $scope.packages = [{
            name: '',
            version: ''
        }];
        $scope.upgradeReadyForNext = false;
        $scope.upgradeProcessed = false;
        $scope.upgradeProcessError = false;
        $scope.componentsReadyForNext = true;
        $scope.componentsProcessed = false;
        $scope.componentsProcessError = false;

        $scope.tryAgainEnabled = function() {
            return ($scope.upgradeProcessed || $scope.upgradeProcessError)
                && ($scope.updateComponents.no ||
                    ($scope.updateComponents.yes && ($scope.componentsProcessed || $scope.componentsProcessError))
                );
        };

        $http.get('index.php/select-version/systemPackage', {'responseType' : 'json'})
            .success(function (data) {
                if (data.responseType != 'error') {
                    $scope.versions = data.package.versions;
                    $scope.packages[0].name = data.package.package;
                    $scope.packages[0].version = $scope.versions[0].id;
                    $scope.selectedOption = $scope.versions[0].id;
                    $scope.upgradeReadyForNext = true;

                } else {
                    $scope.upgradeProcessError = true;
                }
                $scope.upgradeProcessed = true;
            })
            .error(function (data) {
                $scope.upgradeProcessError = true;
            });

        $scope.updateComponents = {
            yes: false,
            no: true
        };

        $scope.$watch('currentPage + rowLimit', function() {
            var begin = (($scope.currentPage - 1) * $scope.rowLimit);
            var end = parseInt(begin) + parseInt(($scope.rowLimit));
            $scope.numberOfPages = Math.ceil($scope.total/$scope.rowLimit);
            if ($scope.components !== undefined) {
                $scope.displayComponents = $scope.components.slice(begin, end);
            }
            if ($scope.currentPage > $scope.numberOfPages) {
                $scope.currentPage = $scope.numberOfPages;
            }
        });

        $scope.$watch('updateComponents.no', function() {
            if (angular.equals($scope.updateComponents.no, true)) {
                $scope.updateComponents.yes = false;
            }
        });

        $scope.$watch('updateComponents.yes', function() {
            if (angular.equals($scope.updateComponents.yes, true)) {
                $scope.updateComponents.no = false;
                if (!$scope.componentsProcessed && !$scope.componentsProcessError) {
                    $scope.componentsReadyForNext = false;
                    $http.get('index.php/other-components-grid/components', {'responseType': 'json'}).
                        success(function (data) {
                            if (data.responseType != 'error') {
                                $scope.components = data.components;
                                $scope.displayComponents = data.components;
                                $scope.totalForGrid = data.total;
                                $scope.total = data.total;
                                $scope.currentPage = 1;
                                $scope.rowLimit = 20;
                                $scope.numberOfPages = Math.ceil(data.total/$scope.rowLimit);
                                for (var i = 0; i < $scope.totalForGrid; i++) {
                                    $scope.packages.push({
                                        name: $scope.components[i].name,
                                        version: $scope.components[i].updates[0].id
                                    });
                                }
                                $scope.componentsReadyForNext = true;
                            } else {
                                $scope.componentsProcessError = true;
                            }
                            $scope.componentsProcessed = true;
                        })
                        .error(function (data) {
                            $scope.componentsProcessError = true;
                        });
                }
            }
        });

        $scope.setComponentVersion = function(name, $version) {
            for (var i = 0; i < $scope.totalForGrid; i++) {
                if ($scope.packages[i + 1].name === name) {
                    $scope.packages[i + 1].version = $version;
                }
            }
        };

        $scope.AddRemoveComponentOnSliderMove = function(component) {
            var found = false;
            for (var i = 0; i < $scope.packages.length; i++) {
                if ($scope.packages[i].name === component.name) {
                    $scope.packages.splice(i, 1);
                    $scope.totalForGrid = $scope.totalForGrid - 1;
                    found = true;
                }
            }
            if (!found) {
                $scope.packages.push({
                    name: component.name,
                    version: component.dropdownId
                });
                $scope.totalForGrid = $scope.totalForGrid + 1;
            }
        };

        $scope.isSelected = function(name) {
            for (var i = 0; i < $scope.packages.length; i++) {
                if ($scope.packages[i].name === name) {
                    return true;
                }
            }
            return false;
        };

        $scope.update = function() {
            $scope.packages[0].version = $scope.selectedOption;
            if (angular.equals($scope.updateComponents.no, true)) {
                if ($scope.totalForGrid > 0) {
                    $scope.packages.splice(1, $scope.totalForGrid);
                }
            }
            $localStorage.packages = $scope.packages;
            $scope.nextState();
        };
    }]);
