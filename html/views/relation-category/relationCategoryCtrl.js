angular.module('app')
    .controller('relationCategoryCtrl', function ($state, $rootScope, $localStorage,$translate,$scope, userService, $uibModal, relationCategoryService, $location) {
       
       
        if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }
       
       
        $scope.displayCount = $rootScope.userPagination;
        $scope.callServer = function callServer(tableState) {
            $rootScope.displayName = '';
            $rootScope.module = '';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.tableStateRef = tableState;
            $rootScope.bredCrumbLabel = '';
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.can_review = 1;
            relationCategoryService.list(tableState).then(function (result) {
                $scope.displayed = result.data.data;
                $scope.data = result.data.data;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
            });
        };
        $scope.defaultPages = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServer($scope.tableStateRef);
                }                
            });
        }
        $scope.goToClassifications = function () {
            $state.go('app.relationship_category.relationship_classification');
        }
        $scope.updateCategory = function (row) {
            $scope.selectedRow = row;
            /*$scope.moduleId = $scope.moduleId;*/
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/relation-category/create-edit-category.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.title = 'general.create';
                    $scope.bottom = 'general.save';
                    $scope.action = 'general.add';
                    $scope.isEdit = false;
                    if (item) {
                        $scope.isEdit = true;
                        $scope.submitStatus = true;
                        $scope.category = angular.copy(item);
                        $scope.update = true;
                        $scope.title = 'general.edit';
                        $scope.bottom = 'general.update';
                        $scope.action = 'general.update';
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    var params = {};
                    $scope.save = function (category) {
                        var obj1 = {};
                        obj1.action_name = $scope.action;
                        obj1.action_description = $scope.action + '$$relationship category$$' + category.relationship_category_name;
                        obj1.module_type = $state.current.activeLink;
                        obj1.action_url = $location.$$absUrl;

                        if (typeof category.id_relationship_category != 'undefined' && ((isNaN(category.id_relationship_category) === false && category.id_relationship_category > 0) || (isNaN(category.id_relationship_category) === true && category.id_relationship_category.length > 0))) {
                            params = category;
                            params.updated_by = $scope.user.id_user;
                            relationCategoryService.update(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error, 'error');
                                }
                            });
                        } else {
                            params.relationship_category_quadrant = category.relationship_category_quadrant;
                            params.relationship_category_name = category.relationship_category_name;
                            params.created_by = $scope.user.id_user;
                            relationCategoryService.add(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error, 'error');
                                }
                            });
                        }
                    }
                },
                resolve: {
                    item: function () {
                        if ($scope.selectedRow) {
                            return $scope.selectedRow;
                        }
                    }
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        };
    })
    .controller('relationAdminProviderCategoryCtrl', function ($state, $rootScope, $scope, userService, $uibModal, relationCategoryService, $location) {
        $scope.displayCount = $rootScope.userPagination;
        $scope.callServer = function callServer(tableState) {
            $rootScope.displayName = '';
            $rootScope.module = '';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.tableStateRef = tableState;
            $rootScope.bredCrumbLabel = '';
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.can_review = 1;
            relationCategoryService.providerCategoriesList(tableState).then(function (result) {
                $scope.displayed = result.data.data;
                $scope.data = result.data.data;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
            });
        };
        $scope.defaultPages = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServer($scope.tableStateRef);
                }                
            });
        }
        $scope.goToClassifications = function () {
            $state.go('app.relationship_category.provider_admin_classification');
        }
        $scope.updateCategory = function (row) {
            $scope.selectedRow = row;
            /*$scope.moduleId = $scope.moduleId;*/
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/relation-category/create-edit-provider-category.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.title = 'general.create';
                    $scope.bottom = 'general.save';
                    $scope.action = 'general.add';
                    $scope.isEdit = false;
                    if (item) {
                        $scope.isEdit = true;
                        $scope.submitStatus = true;
                        $scope.category = angular.copy(item);
                        $scope.update = true;
                        $scope.title = 'general.edit';
                        $scope.bottom = 'general.update';
                        $scope.action = 'general.update';
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    var params = {};
                    $scope.save = function (category) {
                        var obj1 = {};
                        obj1.action_name = $scope.action;
                        obj1.action_description = $scope.action + '$$relationship category$$' + category.relationship_category_name;
                        obj1.module_type = $state.current.activeLink;
                        obj1.action_url = $location.$$absUrl;

                        if (typeof category.id_provider_relationship_category != 'undefined' && ((isNaN(category.id_provider_relationship_category) === false && category.id_provider_relationship_category > 0) || (isNaN(category.id_provider_relationship_category) === true && category.id_provider_relationship_category.length > 0))) {
                            params = category;
                            params.updated_by = $scope.user.id_user;
                            relationCategoryService.providerupdate(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error, 'error');
                                }
                            });
                        } else {
                            params.provider_relationship_category_quadrant = category.provider_relationship_category_quadrant;
                            params.relationship_category_name = category.relationship_category_name;
                            params.created_by = $scope.user.id_user;
                            relationCategoryService.addprovidercategories(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error, 'error');
                                }
                            });
                        }
                    }
                },
                resolve: {
                    item: function () {
                        if ($scope.selectedRow) {
                            return $scope.selectedRow;
                        }
                    }
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        };
    })