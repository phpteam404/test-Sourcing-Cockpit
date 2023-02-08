angular.module('app')
.controller('ExternalUserCtrl', function($timeout, $scope, $rootScope, $stateParams, $state, $localStorage,encode,catalogueService, 
                                          decode, customerService, masterService,providerService, 
                                          $filter,businessUnitService, userService, $location,$window){
    $scope.dynamicPopover = {templateUrl: 'myPopoverTemplate.html'};
    $scope.customer_user = $localStorage.curUser.data.data;
    $scope.displayCount = $rootScope.userPagination;
    $scope.usersList = {};
    $scope.req={};
    $scope.req.status=0;
    businessUnitService.list({'user_role_id':$scope.user1.user_role_id,'customer_id': $scope.customer_user.customer_id, status: 1,id_user:$scope.user1.id_user}).then(function(result){
        $scope.bussinessUnit = result.data.data;
        if($stateParams.buId){
            $scope.business_unit_id = decode($stateParams.buId);
        }
    });

    
    $scope.deleteExternalUser = function(row){
        var r=confirm($filter('translate')('general.alert_continue'));
        if(r==true){
            customerService.deleteUsers({'id_user':row.id_user}).then(function(result){
                if (result.status) {
                    $rootScope.toast('Success', result.message);
                    $scope.callServer($scope.tableStateRef);
                } else {
                    $rootScope.toast('Error', result.error,'error');
                }
            })
        }
        
    }

   
    catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
        $scope.selectedInfoProvider = result.data;
        });

    $scope.showForm = function(row){
        if(row){
            var user_id = encode(row.id_user);
            $state.go('app.external-user.edit-ext-user',{id:encode($scope.customer_user.customer_id),userId:user_id});
        }
        else
            $state.go('app.external-user.create-ext-user',{id:encode($scope.customer_user.customer_id)});
    }
    $scope.callServer = function (tableState) {
        tableState.user_type = 'external';
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
        if(tableState.search && tableState.search.predicateObject && tableState.search.predicateObject.id_provider && $scope.id_provider!=''){
            tableState.id_provider = tableState.search.predicateObject.id_provider.split(":")[1];
        }
        else if($stateParams.buId){
            $scope.business_unit_id = decode($stateParams.buId);
            tableState.business_unit_id = decode($stateParams.buId);
        }
        else{
            delete tableState.business_unit_id;
            delete tableState.id_provider;
        }
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
                $scope.callServer($scope.tableStateRef);
            }                
        });
    }
    $scope.getExtUsersByStatus= function(val){
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
    $scope.getUserContributions = function(row){
        $state.go('app.external-user.user-contributions',{name:row.name,id:encode(row.id_user),extUser:encode(row.id_user)});        
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

})
.controller('manageUserCtrl', function($scope,$rootScope, $state, decode,masterService ){
    $scope.userRoles = {};
    var param = {};
    param.user_role_id = $scope.user1.user_role_id;
    masterService.getUserRole(param).then(function(result){
        $scope.userRoles = result.data;
    });
})
.controller('addExternalUserCtrl', function($scope, $rootScope, $state, $window, $localStorage,  $filter,encode, decode, customerService ,catalogueService,providerService, $stateParams, masterService,businessUnitService, $location){
        $scope.customer_user = $localStorage.curUser.data.data;
        $scope.customer_id = decode($stateParams.id);
        $scope.user_id = decode($stateParams.userId);
        $scope.customUser = {};
        $scope.customUser.is_manual=0;
        $scope.title = "general.create";
        $scope.bottom="general.save";
        
        catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
            $scope.selectedInfoProvider = result.data;
            });
        
        $scope.disableField = true;

        if($scope.user_id){
            $scope.title = "general.edit";
            $scope.bottom="general.update";
            var param ={};
            param.customer_id = $scope.customer_id;
            param.user_id = $scope.user_id;
            customerService.getUserById(param).then(function(result){
                $scope.customUser = result.data;
                console.log('$scope.customUser',$scope.customUser.contribution_type);
                if($scope.customUser.contribution_type==0){
                    $scope.hidefield =true;
                }
                else{
                    $scope.hidefield =false;
                    $scope.hideManual = true;
                }
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

            $scope.goToLink = function(link){
                if(link != undefined){
                    var r=confirm($filter('translate')('contract.alert_msg'));
                    if(r==true){
                        link = link.match(/^https?:/) ? link : '//' + link;
                        window.open(link,'_blank');
                    }
                }
            }
        }

        businessUnitService.list({'user_role_id':$scope.user1.user_role_id, 'customer_id': $scope.customer_user.customer_id, status: 1,id_user:$scope.user1.id_user}).then(function(result){
            $scope.bussinessUnit = result.data.data;
        });

        $scope.countriesList = {};
        masterService.getCountiresList().then(function(result){
            if(result.status){
                $scope.countriesList = result.data;
            }
        })

        $scope.getValue = function(val){
            if(val=='other') {
                $scope.disableField = false;
            }
       else{
        $scope.disableField = true;

            }
        }
        $scope.customUser={};
        $scope.hidefield = true;
        $scope.changeStatus = function(value){
            console.log('v',value);
            if(value==0){
              $scope.hidefield = true;
              $scope.hideManual = false;
              $scope.hidePasswordfield = false;
              $scope.customUser.is_manual ='';   
              $scope.customUser.user_status ='1'; 
            }
            else{
                $scope.hidefield = true; 
                $scope.hidePasswordfield = true;
            }
        }

        $scope.addUser =  function (customUser){
            var params ={};
            params = customUser;
            params.created_by = $scope.user.id_user;
            params.customer_id = $scope.customer_id;
            params.user_type = 'external';
            if(customUser.is_manual == 0){
                delete customUser.password;
                customUser.is_manual_password = 0;
            }else{
                customUser.is_manual_password = 1;
            }
           
            customerService.postUser(params).then(function(result){
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
                    $state.go('app.external-user.ext-list');
                }else{
                    $rootScope.toast('Error',result.error,'error',$scope.user);
                }

            });
        }
        $scope.resetPassword = function(userPwd){
            var params ={};
            params.customer_id = $scope.customer_id;
            params.user_id = $scope.user_id;
            params.password = userPwd.npassword;
            params.cpassword = userPwd.cpassword;
            params.user_type = 'external';
            customerService.resetPassword(params).then (function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    var obj = {};
                    obj.action_name = 'update';
                    obj.action_description = 'update$$user$$password$$('+$scope.customUser.first_name+' '+$scope.customUser.last_name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $state.go('app.external-user.ext-list');
                    // $state.go('app.manage-user.user-list',{name:$stateParams.name,id:encode($scope.customer_id)});
                }else{
                    $rootScope.toast('Error',result.error,'error',$scope.user);
                }
            });
        }
        $scope.cancel = function(){
            //$window.history.back();
            $state.go('app.external-user.ext-list');
        }
        $scope.changeBussinessUnit = function(){
            console.log('$scope.customUser.is_allow_all_bu',$scope.customUser.is_allow_all_bu);
        }
    })

    .controller('ExternalUserContributionsCtrl', function($scope,$rootScope,$stateParams,  $state, decode,customerService ){
        $rootScope.module = 'External User';
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
        if($stateParams.extUser) {
            $scope.showContracts = false;
            $scope.active = 1;
        }
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