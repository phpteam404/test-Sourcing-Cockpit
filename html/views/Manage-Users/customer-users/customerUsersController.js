angular.module('app', ['localytics.directives'])
.controller('UserCtrl', function($timeout, $scope, $rootScope,$localStorage, $translate,$stateParams, $state, 
                                 $filter,
                                 $localStorage,encode, decode, customerService, masterService, businessUnitService, userService, $location,$window){
    $scope.dynamicPopover = { templateUrl: 'myPopoverTemplate.html' };
    
    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }

    $scope.customer_user = $localStorage.curUser.data.data;
    $scope.usersList = {};
    $scope.req={};
    $scope.req.status=0;
    businessUnitService.list({'user_role_id':$scope.user1.user_role_id,'customer_id': $scope.customer_user.customer_id, status: 1,id_user:$scope.user1.id_user}).then(function(result){
        $scope.bussinessUnit = result.data.data;
        if($stateParams.buId){
            $scope.business_unit_id = decode($stateParams.buId);
        }
    });
    $scope.showForm = function(row){
        if(row){
            var user_id = encode(row.id_user);
            $state.go('app.customer-user.edit-customer-user',{id:encode($scope.customer_user.customer_id),userId:user_id});
        }
        else
            $state.go('app.customer-user.create-customer-user',{id:encode($scope.customer_user.customer_id)});
    }

    $scope.deleteInternalUser = function(row){
        var r=confirm($filter('translate')('general.alert_continue'));
        if(r==true){
            customerService.deleteUsers({'id_user':row.id_user}).then(function(result){
                if (result.status) {
                    $rootScope.toast('Success', result.message);
                    $scope.callServer($scope.tableRef);
                } else {
                    $rootScope.toast('Error', result.error,'error');
                }
            })
        }
       
    }
    $scope.displayCount = $rootScope.userPagination;
    $scope.callServer = function (tableState) {
        tableState.user_type = 'internal';
        $rootScope.module = '';
        $rootScope.displayName = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.isLoading = true;
        $scope.emptyTable=false;
        var pagination = tableState.pagination;
        tableState.customer_id = $scope.customer_user.customer_id;
        tableState.user_role_id = $scope.user1.user_role_id;
        tableState.id_user = $scope.user1.id_user;
        $scope.tableStateRef = tableState;
        if(tableState.search && tableState.search.predicateObject && tableState.search.predicateObject.business_unit_id && $scope.business_unit_id!=''){
            tableState.business_unit_id = tableState.search.predicateObject.business_unit_id.split(":")[1];
        }
        else if($stateParams.buId){
            $scope.business_unit_id = decode($stateParams.buId);
            tableState.business_unit_id = decode($stateParams.buId);
        }
        else{
            delete tableState.business_unit_id;
        }
        $scope.tableRef=tableState;
        customerService.getUserList(tableState).then(function (result){
            $scope.usersList = result.data.data;
            $scope.emptyTable=false;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords = result.data.total_records;
            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
            $scope.isLoading = false;
            if(result.data.total_records < 1)
                $scope.emptyTable=true;
        });
    };
    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.callServer($scope.tableRef);
            }                
        });
    }
    $scope.getIntUsersByStatus= function(val){
        $scope.tableStateRef.status=val;
        $scope.tableStateRef.pagination.start='0';
        $scope.tableStateRef.pagination.number='10';
        $scope.callServer($scope.tableStateRef);
    }
    $scope.delete = function(row){
        var params = {};
        params.id_user = row.id_user;
        customerService.deleteUser(params).then(function (result){
            if (result.status) {
                $rootScope.toast('Success', result.message);
                $scope.tableStateRef.status=0;
                $scope.callServer($scope.tableStateRef);
            } else {
                $rootScope.toast('Error', result.error,'error');
            }
        });
    }

    $scope.loginAsAdmin = function (row) {
        userService.loginAs({'id_user':row.id_user}).then(function(result){
            if(result.status){
                $localStorage.curUser.data.parent = $localStorage.curUser.data.data;
                $localStorage.curUser.data.data = result.data.data;
                $localStorage.curUser.data.menu = result.data.menu;
                //$window.location.href = APP_DIR;
                $timeout(function(){
                    window.location.href = APP_DIR;
                },2000);
            }
        });
    }
    $scope.unblock = function (row) {
        var params ={};
        params.email = row.email;
        userService.unBlock(params).then(function(result){
            if(result.status){
                $rootScope.toast('User unblocked', result.message);
                $scope.tableStateRef.status=0;
                $scope.callServer($scope.tableStateRef);
            } else {
                $rootScope.toast('Error', result.error,'error');
            }
        });
    }

    $scope.getUserContributions = function(row){
        $state.go('app.customer-user.user-contract-contributions',{name:row.name,id:encode(row.id_user)});        
    }

})
.controller('manageUserCtrl', function($scope,$rootScope, $state, decode,masterService ){
    $scope.userRoles = {};
    var param = {};
    param.user_role_id = $scope.user1.user_role_id;
    masterService.getUserRole(param).then(function(result){
        $scope.userRoles = result.data;
    });
})
.controller('addCustomUserCtrl', function($scope, $rootScope, $state, $window, $localStorage, customerService, encode, decode, masterService, customerService ,$stateParams, businessUnitService, $location){
        $scope.customer_user = $localStorage.curUser.data.data;
        $scope.customer_id = decode($stateParams.id);
        //$scope.customerId = decode($stateParams.id);
        $scope.user_id = decode($stateParams.userId);
        $scope.customUser = {};
        $scope.title = "general.create";
        $scope.bottom="general.save";
        $scope.disableField = true;

        if($scope.user_id){
            $scope.title = "general.edit";
            $scope.bottom="general.update";
            var param ={};
            param.customer_id = $scope.customer_id;
            param.user_id = $scope.user_id;
            param.user_type = 'internal';
            customerService.getUserById(param).then(function(result){
                $scope.customUser = result.data;
                console.log("ki",$scope.customUser)
                if($scope.customUser.gender=='other'){
                    $scope.disableField = false;
                }
                if(result.data.user_role_id){
                    for(var a in $scope.userRoles){
                        if($scope.userRoles[a].id_user_role == result.data.user_role_id)
                            $scope.customUser.user_role_id = $scope.userRoles[a];
                    }
                }
                $rootScope.module = 'User';
                $rootScope.displayName = $scope.customUser.first_name+" "+ $scope.customUser.last_name;
                var bussiness_unit = [];
                for(var a in $scope.customUser.business_unit){
                    bussiness_unit.push($scope.customUser.business_unit[a].business_unit_id);
                }
                $scope.customUser.business_unit = bussiness_unit;
            });
        }

        masterService.getUserRole({'user_role_id' : $scope.user1.user_role_id}).then(function(result){
            $scope.userRoles = result.data;
        });
        businessUnitService.list({'user_role_id':$scope.user1.user_role_id, 'customer_id': $scope.customer_user.customer_id, status: 1,id_user:$scope.user1.id_user}).then(function(result){
            $scope.bussinessUnit = result.data.data;
        });

        customerService.languageSelection({'user_languages':true,'customer_id': $scope.customer_user.customer_id}).then(function(result){
            $scope.language = result.data;
        });


        $scope.countriesList = {};
        masterService.getCountiresList().then(function(result){
            if(result.status){
                $scope.countriesList = result.data;
            }
        })

        $scope.getValue = function(val){
            console.log("user",val);
            if(val=='other') {
                $scope.disableField = false;
            }
            else{
                $scope.disableField = true;
                    }
                }
        
        $scope.addUser =  function (customUser){
            console.log("oi",customUser);
            var params ={};
            params = angular.copy(customUser);
            customUser.is_manual_password = 0;
            params.user_role_id = params.user_role_id.id_user_role;
            params.created_by = $scope.user.id_user;
            params.customer_id = $scope.customer_id;
            if(customUser.is_manual == 0){
                delete customUser.password;
                customUser.is_manual_password = 0;
            }else{
                customUser.is_manual_password = 1;
            }
            params.user_type = 'internal';
            customerService.postUser(params).then(function(result){
                // console.log('result', result);
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    var obj = {};
                    if(customUser.id_user>0)$scope.action = 'update';
                    else $scope.action = 'create';
                    obj.action_name = $scope.action;
                    obj.action_description = $scope.action+'$$user$$('+customUser.first_name+' '+customUser.last_name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $state.go('app.customer-user.list');
                }else{
                    $rootScope.toast('Error',result.error,'error',$scope.user);
                    if(result.success) $state.go('app.customer-user.list');
                }

            });
        }
        $scope.resetPassword = function(userPwd){
            var params ={};
            params.customer_id = $scope.customer_id;
            params.user_id = $scope.user_id;
            params.password = userPwd.npassword;
            params.cpassword = userPwd.cpassword;
            params.user_type = 'internal';
            customerService.resetPassword(params).then (function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    var obj = {};
                    obj.action_name = 'update';
                    obj.action_description = 'update$$user$$password$$('+$scope.customUser.first_name+' '+$scope.customUser.last_name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $state.go('app.customer-user.list');
                    // $state.go('app.manage-user.user-list',{name:$stateParams.name,id:encode($scope.customer_id)});
                }else{
                    $rootScope.toast('Error',result.error,'error',$scope.user);
                }
            });
        }
        $scope.cancel = function(){
            //$window.history.back();
            $state.go('app.customer-user.list');
        }
        $scope.changeBussinessUnit = function(){
            console.log('$scope.customUser.is_allow_all_bu',$scope.customUser.is_allow_all_bu);
        }
})
.controller('CustomUserContributionsCtrl', function($scope,$rootScope,$stateParams,  $state, decode,customerService ){
    $rootScope.module = 'Internal User';
    $rootScope.displayName = $stateParams.name;
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
    $scope.contractsList =[];
    $scope.contributionsList =[];
    $scope.contractsCount =0;
    $scope.contributionsCount =0;
    $scope.active =0;
    $scope.showContracts =true;
    $scope.userName = $stateParams.name;
    customerService.getUserContributions({'id_user':decode($stateParams.id)}).then(function (result){
        if(result.status){
            $scope.contractsList=result.data.contracts.data;
            $scope.contractsCount=result.data.contracts.total_records;
            $scope.contributionsList=result.data.contributions.data;
            $scope.contributionsCount=result.data.contributions.total_records;
            console.log('$scope.contractsList',$scope.contractsList);
            console.log('$scope.contributionsList',$scope.contributionsList);
        }
    });
})