angular.module('app')
    .controller('settingsCtrl', function ($state, $rootScope, $scope, $uibModal, settingsService, userService) {
        $scope.settings = {};
        $scope.savedSettings = {};
        $scope.displayCount = $rootScope.userPagination;
        $scope.callServer = function callServer(tableState) {
            $scope.tableStateRef=tableState;
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            settingsService.list(tableState).then(function (result){
                $scope.displayed = result.data.data;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
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
        $scope.update = function (row)
        {
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/settings/edit-settings.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.title = 'general.edit';
                    $scope.bottom = 'general.update';
                    $scope.isEdit = false;
                    if (item) {
                        $scope.submitStatus = true;
                        $scope.settings = angular.copy(item);
                        $scope.update = true;
                        $scope.title = 'general.update';
                        $scope.isEdit = true;
                        $scope.title = 'general.edit';
                        $scope.bottom = 'general.update';
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    var params ={};
                    $scope.save=function(savedData){
                        if(typeof savedData.id_app_config!='undefined' && ((isNaN(savedData.id_app_config)===false && savedData.id_app_config > 0) || (isNaN(savedData.id_app_config)===true && savedData.id_app_config.length > 0))){
                            params = savedData;
                            params.created_by = $scope.user.id_user;
                            params.updated_by = $scope.user.id_user;
                            settingsService.update(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
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
    });