//angular.module('app', ['ng-fusioncharts'])
    // .controller('providerCategoryCtrl', function ($state, $rootScope, $scope, $uibModal, relationCategoryService,userService, $location) {
    //     $scope.myDataSource = {};
    //     $scope.displayCount = $rootScope.userPagination;
    //     $scope.callServer = function callServer(tableState) {
    //         $rootScope.displayName = '';
    //         $rootScope.module = '';
    //         $scope.tableStateRef = tableState;
    //         $rootScope.bredCrumbLabel = '';
    //         $scope.isLoading = true;
    //         var pagination = tableState.pagination;
    //         tableState.customer_id = $scope.user1.customer_id;
    //         tableState.can_review = 1;
    //         relationCategoryService.providerCategoriesList(tableState).then(function (result) {
    //             $scope.displayed = result.data.data;
    //             $scope.data = result.data.data;
    //             $scope.displayCount = $rootScope.userPagination;
    //             $scope.totalRecords1 = result.data.total_records;
    //             tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
    //             $scope.isLoading = false;
    //             $scope.myDataSource = result.data.graph;
    //         });
    //     };
    //     $scope.defaultPages1 = function(val){
    //         userService.userPageCount({'display_rec_count':val}).then(function (result){
    //             if(result.status){
    //                 $rootScope.userPagination = val;
    //                 $scope.callServer($scope.tableStateRef);
    //             }                
    //         });
    //     }
    //     $scope.goToProviderClassificationStructure = function () {
    //         $state.go('app.customer-relationship_category.customer-provider_classification');
    //      }
    //     $scope.updateCategoryInfo = function (row) {
    //         $scope.selectedRow = row;
    //         var modalInstance = $uibModal.open({
    //             animation: true,
    //             backdrop: 'static',
    //             keyboard: false,
    //             scope: $scope,
    //             openedClass: 'right-panel-modal modal-open',
    //             templateUrl: 'views/Manage-Users/relationship-category/create-edit-provider-category.html',
    //             controller: function ($uibModalInstance, $scope, item) {
    //                 if (item) {
    //                     $scope.type = "category";
    //                     $scope.isEdit = true;
    //                     $scope.submitStatus = true;
    //                     $scope.category = angular.copy(item);
    //                     $scope.update = true;
    //                     $scope.title = 'general.edit';
    //                     $scope.bottom = 'general.update';
    //                 }
    //                 $scope.cancel = function () {
    //                     $uibModalInstance.close();
    //                 };
    //                 var params = {};
    //                 $scope.update = function (category) {
    //                     if (typeof category.id_relationship_category != 'undefined' && ((isNaN(category.id_relationship_category) === false && category.id_relationship_category > 0) || (isNaN(category.id_relationship_category) === true && category.id_relationship_category.length > 0))) {
    //                         params = category;
    //                         params.created_by = $scope.user.id_user;
    //                         params.updated_by = $scope.user.id_user;
    //                         params.customer_id = $scope.user1.customer_id;
    //                         relationCategoryService.update(params).then(function (result) {
    //                             if (result.status) {
    //                                 $rootScope.toast('Success', result.message);
    //                                 var obj = {};
    //                                 obj.action_name = 'update';
    //                                 obj.action_description = 'update$$relationship category$$(' + item.relationship_category_name + ')';
    //                                 obj.module_type = $state.current.activeLink;
    //                                 obj.action_url = $location.$$absUrl;
    //                                 $rootScope.confirmNavigationForSubmit(obj);
    //                                 $scope.cancel();
    //                                 $scope.callServer($scope.tableStateRef);
    //                             } else {
    //                                 $rootScope.toast('Error', result.error, 'error');
    //                             }
    //                         });
    //                     }
    //                 }
    //             },
    //             resolve: {
    //                 item: function () {
    //                     if ($scope.selectedRow) {
    //                         return $scope.selectedRow;
    //                     }
    //                 }
    //             }
    //         });
    //         modalInstance.result.then(function ($data) {
    //         }, function () {
    //         });
    //     };

    //     $scope.callServer2 = function callServer2(tableState2) {
    //         $scope.tableStateRef2 = tableState2;
    //         $scope.isLoading = true;
    //         var pagination = tableState2.pagination;
    //         tableState2.customer_id = $scope.user1.customer_id;
    //         relationCategoryService.subList(tableState2).then(function (result) {
    //             $scope.newList = result.data.data;
    //             $scope.displayCount = $rootScope.userPagination;
    //             $scope.totalRecords2 = result.data.total_records;
    //             tableState2.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
    //             $scope.isLoading = false;
    //         });
    //     };
    //     $scope.defaultPages2 = function(val){
    //         userService.userPageCount({'display_rec_count':val}).then(function (result){
    //             if(result.status){
    //                 $rootScope.userPagination = val;
    //                 $scope.callServer2($scope.tableStateRef2);
    //             }                
    //         });
    //     }
    //     $scope.updateNewCategory = function (row) {
    //         $scope.selectedRow = row;
    //         var modalInstance = $uibModal.open({
    //             animation: true,
    //             backdrop: 'static',
    //             keyboard: false,
    //             scope: $scope,
    //             openedClass: 'right-panel-modal modal-open',
    //             templateUrl: 'views/Manage-Users/relationship-category/create-edit-customer-category.html',
    //             controller: function ($uibModalInstance, $scope, item) {
    //                 $scope.type = "newCategory";
    //                 if (item) {
    //                     $scope.isEdit = true;
    //                     $scope.submitStatus = true;
    //                     $scope.category = angular.copy(item);
    //                     $scope.update = true;
    //                     $scope.title = 'general.edit';
    //                     $scope.bottom = 'general.update';
    //                 } else {
    //                     $scope.title = 'general.create';
    //                     $scope.bottom = 'general.save';
    //                 }
    //                 $scope.cancel = function () {
    //                     $uibModalInstance.close();
    //                 };
    //                 var params = {};
    //                 $scope.update = function (category) {
    //                     if (item) {
    //                         if (typeof category.id_relationship_category != 'undefined' && ((isNaN(category.id_relationship_category) === false && category.id_relationship_category > 0) || (isNaN(category.id_relationship_category) === true && category.id_relationship_category.length > 0))) {
    //                             params.created_by = $scope.user.id_user;
    //                             params.updated_by = $scope.user.id_user;
    //                             params.customer_id = $scope.user1.customer_id;
    //                             params.relationship_category_name = category.relationship_category_name;
    //                             params.id_relationship_category_language = category.id_relationship_category_language;
    //                             params.relationship_category_status = category.relationship_category_status;
    //                             params.id_relationship_category = category.id_relationship_category;
    //                             relationCategoryService.updateSub(params).then(function (result) {
    //                                 if (result.status) {
    //                                     $rootScope.toast('Success', result.message);
    //                                     var obj = {};
    //                                     obj.action_name = 'update';
    //                                     obj.action_description = 'update$$relationship category$$(' + item.relationship_category_name + ')';
    //                                     obj.module_type = $state.current.activeLink;
    //                                     obj.action_url = $location.$$absUrl;
    //                                     $rootScope.confirmNavigationForSubmit(obj);
    //                                     $scope.cancel();
    //                                     $scope.callServer2($scope.tableStateRef2);
    //                                 } else {
    //                                     $rootScope.toast('Error', result.error, 'error');
    //                                 }
    //                             });
    //                         }
    //                     } else {
    //                         params.created_by = $scope.user.id_user;
    //                         params.customer_id = $scope.user1.customer_id;
    //                         params.relationship_category_name = category.relationship_category_name;
    //                         relationCategoryService.addSub(params).then(function (result) {
    //                             if (result.status) {
    //                                 $rootScope.toast('Success', result.message);
    //                                 var obj = {};
    //                                 obj.action_name = 'create';
    //                                 obj.action_description = 'create$$relationship category$$(' + category.relationship_category_name + ')';
    //                                 obj.module_type = $state.current.activeLink;
    //                                 obj.action_url = $location.$$absUrl;
    //                                 $rootScope.confirmNavigationForSubmit(obj);
    //                                 $scope.cancel();
    //                                 $scope.callServer2($scope.tableStateRef2);
    //                             } else {
    //                                 $rootScope.toast('Error', result.error, 'error');
    //                             }
    //                         });
    //                     }
    //                 }
    //             },
    //             resolve: {
    //                 item: function () {
    //                     if ($scope.selectedRow) {
    //                         return $scope.selectedRow;
    //                     }
    //                 }
    //             }
    //         });
    //         modalInstance.result.then(function ($data) {
    //         }, function () {
    //         });
    //     };
    // })

    