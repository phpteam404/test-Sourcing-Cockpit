angular.module('app')
    .controller('bussinessUnitCtrl', function ($state, $rootScope, $localStorage,$translate,$scope, $uibModal, encode, decode, businessUnitService, providerService, userService) {
        $scope.showAddBtn = 0; 
        console.log('bussinessUnitCtrl loaded');
        $scope.req={};
        $scope.req.status=0; 
        
        if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }

        if ($rootScope.access == 'wa' || $rootScope.access == 'ca') {
            $scope.showAddBtn = 1;
        }
        $scope.user_access = $rootScope.access;
       //console.log($scope.user_access);
        //console.log($rootScope);
        $scope.displayCount = $rootScope.userPagination;
        $scope.callServer = function callServer(tableState) {
            $rootScope.module = '';
            $rootScope.displayName = '';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.tableStateRef = tableState;
            $rootScope.bredCrumbLabel = '';
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            tableState.user_role_id = $rootScope.user_role_id;
            tableState.id_user = $rootScope.id_user;
            businessUnitService.list(tableState).then(function (result) {
                $scope.displayed = result.data.data;
                $scope.data = result.data.data;
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
        $scope.getBusByStatus= function(val){
            $scope.tableStateRef.status=val;
            $scope.tableStateRef.pagination.start='0';
            $scope.tableStateRef.pagination.number='10';
            $scope.callServer($scope.tableStateRef);
        }   
        $scope.editBusinessUnit = function (row) {
            $state.go('app.bussiness_unit.edit', { id: encode(row.id_business_unit) });
        }
        $scope.goToCustomerUser = function (row) {
            $state.go('app.customer-user.list', { buId: encode(row.id_business_unit) });
        }
    })
    .controller('bussinessUniListCtrl', function ($state, $rootScope, $scope, $uibModal, $localStorage, businessUnitService, providerService, masterService) {
        $scope.callServer = function callServer(tableState) {
            $scope.tableStateRef = tableState;
            $rootScope.bredCrumbLabel = '';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            tableState.user_role_id = $rootScope.user_role_id;
            tableState.id_user = $rootScope.id_user;
            businessUnitService.list(tableState).then(function (result) {
                $scope.displayed = result.data.data;
                $scope.data = result.data.data;
                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / tableState.pagination.number);
                $scope.isLoading = false;
            });
        };

        $scope.callServer2 = function callServer2(tableState1) {
            $scope.tableStateRef1 = tableState1;
            $rootScope.bredCrumbLabel = '';
            $scope.isLoading = true;
            var pagination = tableState1.pagination;
            tableState1.customer_id = $scope.user1.customer_id;
            providerService.list(tableState1).then(function (result) {
                $scope.providers = result.data.data;
                tableState1.pagination.numberOfPages = Math.ceil(result.data.total_records / tableState1.pagination.number);
                $scope.isLoading = false;
            });
        };
    })
    .controller('bussinessUnitCreateCtrl', function ($state, $rootScope, $scope, $uibModal, $localStorage, businessUnitService, masterService, $location) {
        /*$localStorage.curUser = $scope.userData;*/
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.title = 'general.create';
        $scope.bottom = 'general.save';
        masterService.getCountiresList().then(function (result) {
            if (result.status) {
                $scope.countriesList = result.data;
            }
        });

        $scope.cancel = function () {
            $state.go('app.bussiness_unit.list');
        };

        $scope.save = function (data) {
            var params = {};
            $scope.userData = $localStorage.curUser.data.data;
            /*data.customer_id = $scope.userData.customer_id;*/
            if (typeof data.id_business_unit != 'undefined' && ((isNaN(data.id_business_unit) === false && data.id_business_unit > 0) || (isNaN(data.id_business_unit) === true && data.id_business_unit.length > 0))) {
                params = data;
                params.customer_id = $scope.user1.customer_id;
                params.updated_by = $scope.userData.id_user;
                businessUnitService.update(params).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                        var obj = {};
                        obj.action_name = 'update';
                        obj.action_description = 'update$$business unit$$(' + data.bu_name + ')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                        $state.go('app.bussiness_unit.list');
                    } else {
                        $rootScope.toast('Error', result.error, 'error');
                    }
                });
            } else {
                params = data;
                params.created_by = $scope.userData.id_user;
                params.customer_id = $scope.user1.customer_id;
                businessUnitService.add(params).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                        var obj = {};
                        obj.action_name = 'add';
                        obj.action_description = 'add$$business unit$$(' + data.bu_name + ')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                        $state.go('app.bussiness_unit.list');
                    } else {
                        $rootScope.toast('Error', result.error, 'error');
                    }
                });
            }
        }
    })
    .controller('bussinessUnitEditCtrl', function ($state, $rootScope, $scope, $uibModal, $localStorage, businessUnitService, masterService, $stateParams, encode, decode, $location) {
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        if ($stateParams.id) {
            $scope.title = 'general.edit';
            $scope.bottom = 'general.update';
            $scope.isEdit = true;

            $scope.user = $localStorage.curUser.data.data;

            $scope.id = decode($stateParams.id);
            masterService.getCountiresList().then(function (result) {
                if (result.status) {
                    $scope.countriesList = result.data;
                }
            });

            $scope.cancel = function () {
                $state.go('app.bussiness_unit.list');
            };

            businessUnitService.get({ 'id_business_unit': $scope.id }).then(function (result) {
                if (result.status && result.data && result.data.length > 0) {
                    $scope.bussiness = result.data[0];
                } else {
                    $state.go('app.bussiness_unit.list');
                }
            });

            $scope.save = function (data) {
                var params = {};
                params.customer_id = $scope.user1.customer_id;
                data.customer_id = $scope.user.customer_id;
                if (typeof data.id_business_unit != 'undefined' && ((isNaN(data.id_business_unit) === false && data.id_business_unit > 0) || (isNaN(data.id_business_unit) === true && data.id_business_unit.length > 0))) {
                    params = data;
                    params.customer_id = $scope.user1.customer_id;
                    params.updated_by = $scope.user.id_user;
                    businessUnitService.update(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'update';
                            obj.action_description = 'update$$business unit$$(' + data.bu_name + ')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $state.go('app.bussiness_unit.list');
                        } else {
                            $rootScope.toast('Error', result.error, 'error');
                        }
                    });
                } else {
                    params = data;
                    params.customer_id = $scope.user1.customer_id;
                    params.created_by = $scope.user.id_user;
                    businessUnitService.add(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'add';
                            obj.action_description = 'add$$business unit$$(' + data.bu_name + ')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $state.go('app.bussiness_unit.list');
                        } else {
                            $rootScope.toast('Error', result.error, 'error');
                        }
                    });
                }
            }
        } else {
            $state.go('app.bussiness_unit.list');
        }
    })