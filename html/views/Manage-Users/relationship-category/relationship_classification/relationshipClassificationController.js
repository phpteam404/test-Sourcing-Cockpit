angular.module('app')
    .controller('customerRelationshipClassificationCtrl', function ($state, $rootScope, userService,$scope, $uibModal, relationshipClassificationService, $location) {
        $scope.callServer = function callServer(tableState)
        {
            $rootScope.displayName ='';
            $rootScope.module = '';
            $scope.tableStateRef=tableState;
            $rootScope.bredCrumbLabel = '';
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            relationshipClassificationService.list(tableState).then(function (result){
                $scope.displayed = result.data.data;
                $scope.data = result.data.data;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
            });
        };
        $scope.defaultPages2 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServer($scope.tableStateRef);
                }                
            });
        }

        $scope.updateClassfication = function (row) {
            $scope.selectedRow = row;
            /*$scope.moduleId = $scope.moduleId;*/
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/relationship-category/relationship_classification/customer-create-edit-classification.html',
                controller: function ($uibModalInstance, $scope, item) {
                    if (item) {
                        $scope.isEdit = true;
                        $scope.submitStatus = true;
                        $scope.classification = angular.copy(item);
                        $scope.update = true;
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    var params ={};
                    $scope.update=function(classification){
                        if(typeof classification.id_relationship_classification!='undefined' && ((isNaN(classification.id_relationship_classification)===false && classification.id_relationship_classification > 0) || (isNaN(classification.id_relationship_classification)===true && classification.id_relationship_classification.length > 0))){
                            params = classification;
                            params.created_by = $scope.user.id_user;
                            params.updated_by = $scope.user.id_user;
                            params.customer_id = $scope.user1.customer_id;
                            relationshipClassificationService.update(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$relationship classification$$('+item.classification_name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error,'error');
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

        $scope.manageClassfication = function (row) {
            console.log(' relationship mange info',row);
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/relationship-category/relationship_classification/customer-manage-classification.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.classification = {};
                    $scope.classification = item;
                    var pa = {};
                    pa.parent_classification_id = item.relationship_classification_id;
                    pa.customer_id = $scope.user1.customer_id;
                    relationshipClassificationService.listChildClassification(pa).then(function (result){
                        if(result.data.length>0){
                            $scope.update = false;
                            $scope.title = 'general.edit';
                            $scope.bottom = 'general.update';
                            $scope.isEdit = true;

                            $scope.classification.classification_position = item.classification_position;

                            $scope.classification.parent_classification_id = item.relationship_classification_id;
                            $scope.classification.isEdit = true;

                            if(item.classification_position == 'x'){
                                $scope.classification.left1 = {};
                                $scope.classification.left = result.data[0].classification_name;
                                $scope.classification.left1.id_relationship_classification = result.data[0].id_relationship_classification;
                                $scope.classification.left1.id_relationship_classification_language = result.data[0].id_relationship_classification_language;
                                $scope.classification.right1 = {};
                                $scope.classification.right = result.data[1].classification_name;
                                $scope.classification.right1.id_relationship_classification = result.data[1].id_relationship_classification;
                                $scope.classification.right1.id_relationship_classification_language = result.data[1].id_relationship_classification_language;
                            } else if(item.classification_position == 'y') {
                                $scope.classification.low1 = {};
                                $scope.classification.low = result.data[0].classification_name;
                                $scope.classification.low1.id_relationship_classification = result.data[0].id_relationship_classification;
                                $scope.classification.low1.id_relationship_classification_language = result.data[0].id_relationship_classification_language;

                                $scope.classification.high1 = [];
                                $scope.classification.high = result.data[1].classification_name ;
                                $scope.classification.high1.id_relationship_classification = result.data[1].id_relationship_classification;
                                $scope.classification.high1.id_relationship_classification_language = result.data[1].id_relationship_classification_language;
                            }
                        }else{
                            $scope.update = false;
                            $scope.title = 'general.create';
                            $scope.bottom = 'general.save';
                            $scope.isEdit = false;

                            $scope.classification.parent_classification_id = item.id_relationship_classification;
                            $scope.classification.isEdit = false;

                            delete $scope.classification.id_relationship_classification;
                            delete $scope.classification.id_relationship_classification_language;
                        }
                    });

                    $scope.cancel = function () {
                        $uibModalInstance.close();
                        $scope.classification.parent_classification_id = 0;
                    };

                    var params ={};
                    $scope.save=function(classification){

                        params.updated_by = $scope.user.id_user;
                        params.created_by = $scope.user.id_user;
                        params.customer_id = $scope.user1.customer_id;
                        var position = {};
                        params.classification = [];

                        if(classification.classification_position=='x'){
                            position.classification_name = classification.left;
                            position.classification_position = 'left';
                            if(classification.isEdit) {
                                position.id_relationship_classification = classification.left1.id_relationship_classification;
                                position.id_relationship_classification_language = classification.left1.id_relationship_classification_language;
                            }
                            params.classification.push(position);
                            position = {};
                            position.classification_name = classification.right;
                            position.classification_position = 'right';
                            if(classification.isEdit){
                                position.id_relationship_classification = classification.right1.id_relationship_classification;
                                position.id_relationship_classification_language = classification.right1.id_relationship_classification_language;
                            }
                            params.classification.push(position);

                        } else if(classification.classification_position=='y'){
                            position.classification_name = classification.low;
                            position.classification_position = 'low';
                            if(classification.isEdit) {
                                position.id_relationship_classification = classification.low1.id_relationship_classification;
                                position.id_relationship_classification_language = classification.low1.id_relationship_classification_language;
                            }
                            params.classification.push(position);
                            position = {};
                            position.classification_name = classification.high;
                            position.classification_position = 'high';
                            if(classification.isEdit) {
                                position.id_relationship_classification = classification.high1.id_relationship_classification;
                                position.id_relationship_classification_language = classification.high1.id_relationship_classification_language;
                            }
                            params.classification.push(position);                        }
                            params.parent_classification_id = classification.parent_classification_id;
                        relationshipClassificationService.saveClassification(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'Manage Relationship Classification$$('+item.classification_name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.callServer($scope.tableStateRef);
                                $scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });
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
    .controller('providersRelationshipClassificationCtrl', function ($state, $rootScope, userService,$scope, $uibModal, relationshipClassificationService, $location) {
        $scope.callServer = function callServer(tableState)
        {
            $rootScope.displayName ='';
            $rootScope.module = '';
            $scope.tableStateRef=tableState;
            $rootScope.bredCrumbLabel = '';
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            relationshipClassificationService.providersClassificationList(tableState).then(function (result){
                console.log('in calssification',result);
                $scope.displayed = result.data.data;
                $scope.data = result.data.data;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
            });
        };
        $scope.defaultPages2 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServer($scope.tableStateRef);
                }                
            });
        }

        $scope.updateClassfication = function (row) {
            $scope.selectedRow = row;
            /*$scope.moduleId = $scope.moduleId;*/
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/relationship-category/relationship_classification/customer-create-edit-classification.html',
                controller: function ($uibModalInstance, $scope, item) {
                    if (item) {
                        $scope.isEdit = true;
                        $scope.submitStatus = true;
                        $scope.classification = angular.copy(item);
                        $scope.update = true;
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    var params ={};
                    $scope.update=function(classification){
                        if(typeof classification.id_provider_relationship_classification!='undefined' && ((isNaN(classification.id_provider_relationship_classification)===false && classification.id_provider_relationship_classification > 0) || (isNaN(classification.id_provider_relationship_classification)===true && classification.id_provider_relationship_classification.length > 0))){
                            params = classification;
                            params.created_by = $scope.user.id_user;
                            params.updated_by = $scope.user.id_user;
                            params.customer_id = $scope.user1.customer_id;
                            relationshipClassificationService.providerUpdate(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$provider classification$$('+item.classification_name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error,'error');
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

        $scope.manageClassfication = function (row) {
            console.log('mange info',row);
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/relationship-category/relationship_classification/customer-manage-classification.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.classification = {};
                    $scope.classification = item;
                    var pa = {};
                    pa.parent_classification_id = item.provider_relationship_classification_id;
                    pa.customer_id = $scope.user1.customer_id;
                    relationshipClassificationService.listProviderChildClassification(pa).then(function (result){
                        if(result.data.length>0){
                            $scope.update = false;
                            $scope.title = 'general.edit';
                            $scope.bottom = 'general.update';
                            $scope.isEdit = true;

                            $scope.classification.classification_position = item.classification_position;

                            $scope.classification.parent_classification_id = item.provider_relationship_classification_id;
                            $scope.classification.isEdit = true;

                            if(item.classification_position == 'x'){
                                $scope.classification.left1 = {};
                                $scope.classification.left = result.data[0].classification_name;
                                $scope.classification.left1.id_provider_relationship_classification = result.data[0].id_provider_relationship_classification;
                                $scope.classification.left1.id_provider_relationship_classification_language = result.data[0].id_provider_relationship_classification_language;
                                $scope.classification.right1 = {};
                                $scope.classification.right = result.data[1].classification_name;
                                $scope.classification.right1.id_provider_relationship_classification = result.data[1].id_provider_relationship_classification;
                                $scope.classification.right1.id_provider_relationship_classification_language = result.data[1].id_provider_relationship_classification_language;
                            } else if(item.classification_position == 'y') {
                                $scope.classification.low1 = {};
                                $scope.classification.low = result.data[0].classification_name;
                                $scope.classification.low1.id_provider_relationship_classification = result.data[0].id_provider_relationship_classification;
                                $scope.classification.low1.id_provider_relationship_classification_language = result.data[0].id_provider_relationship_classification_language;

                                $scope.classification.high1 = [];
                                $scope.classification.high = result.data[1].classification_name ;
                                $scope.classification.high1.id_provider_relationship_classification = result.data[1].id_provider_relationship_classification;
                                $scope.classification.high1.id_provider_relationship_classification_language = result.data[1].id_provider_relationship_classification_language;
                            }
                        }else{
                            $scope.update = false;
                            $scope.title = 'general.create';
                            $scope.bottom = 'general.save';
                            $scope.isEdit = false;

                            $scope.classification.parent_classification_id = item.id_provider_relationship_classification;
                            $scope.classification.isEdit = false;

                            delete $scope.classification.id_provider_relationship_classification;
                            delete $scope.classification.id_provider_relationship_classification_language;
                        }
                    });

                    $scope.cancel = function () {
                        $uibModalInstance.close();
                        $scope.classification.parent_classification_id = 0;
                    };

                    var params ={};
                    $scope.save=function(classification){

                        params.updated_by = $scope.user.id_user;
                        params.created_by = $scope.user.id_user;
                        params.customer_id = $scope.user1.customer_id;
                        var position = {};
                        params.classification = [];

                        if(classification.classification_position=='x'){
                            position.classification_name = classification.left;
                            position.classification_position = 'left';
                            if(classification.isEdit) {
                                position.id_provider_relationship_classification = classification.left1.id_provider_relationship_classification;
                                position.id_provider_relationship_classification_language = classification.left1.id_provider_relationship_classification_language;
                            }
                            params.classification.push(position);
                            position = {};
                            position.classification_name = classification.right;
                            position.classification_position = 'right';
                            if(classification.isEdit){
                                position.id_provider_relationship_classification = classification.right1.id_provider_relationship_classification;
                                position.id_provider_relationship_classification_language = classification.right1.id_provider_relationship_classification_language;
                            }
                            params.classification.push(position);

                        } else if(classification.classification_position=='y'){
                            position.classification_name = classification.low;
                            position.classification_position = 'low';
                            if(classification.isEdit) {
                                position.id_provider_relationship_classification = classification.low1.id_provider_relationship_classification;
                                position.id_provider_relationship_classification_language = classification.low1.id_provider_relationship_classification_language;
                            }
                            params.classification.push(position);
                            position = {};
                            position.classification_name = classification.high;
                            position.classification_position = 'high';
                            if(classification.isEdit) {
                                position.id_provider_relationship_classification = classification.high1.id_provider_relationship_classification;
                                position.id_provider_relationship_classification_language = classification.high1.id_provider_relationship_classification_language;
                            }
                            params.classification.push(position);                        }
                            params.parent_classification_id = classification.parent_classification_id;
                            //console.log('params info',params);
                            relationshipClassificationService.saveproviderClassification(params).then(function (result) {
                                //console.log('add calssification',result);
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'Manage Provider Classification$$('+item.classification_name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.callServer($scope.tableStateRef);
                                $scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });
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