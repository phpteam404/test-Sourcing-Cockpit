angular.module('app',['ng-fusioncharts','localytics.directives'])
    .controller('customerCtrl',function ($localStorage,$window,$state, $rootScope, $uibModal, $scope,masterService, customerService) {
        if($localStorage.curUser.appVersion == undefined || $localStorage.curUser.appVersion !=$rootScope.appVersion){
            $localStorage.curUser.appVersion=$rootScope.appVersion;
            $window.location.reload();
        }
        $scope.countriesList = {};
        masterService.getCountiresList().then(function(result){
            if(result.status){
                $scope.countriesList = result.data;
            }
        })
        $scope.templatesList = {};
        customerService.getTemplates().then(function (result){
            $scope.templatesList = result.data;
        });
    })
    .controller('customerListCtrl', function ($state, $rootScope, $scope,$translate, $http,$uibModal,$localStorage, encode,userService, customerService) {
        $scope.displayed = {};
        $scope.data = [];
        $scope.ldapResult = {};
        $scope.displayCount = $rootScope.userPagination;
        // $translate.use($localStorage.curUser.language_iso_code);
        $scope.callServer = function callServer(tableState) {
            $rootScope.displayName = '';
            $rootScope.module = '';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.isLoading = true;
            $scope.tableStateRef=tableState;
            var pagination = tableState.pagination;
            //var start = pagination.start || 0;     // This is NOT the page number, but the index of item in the list that you want to use to display the table.
            //var number = pagination.number || 10;  // Number of entries showed per page.
            customerService.list(tableState).then(function (result){
                $scope.displayed = result.data.data;
                $scope.data = result.data.data;
                $scope.emptyTable = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if(result.data.total_records < 1)$scope.emptyTable = true;
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


        $scope.ldap = {
            'status': 0,
            'host': '',
            'dc': '',
            'port': '',
        };
        $scope.saml = {
            'status': 0,
        };
        $scope.UpdateLdap = function (row) {
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/customers/ldap-config.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.testFormSubmitted = false;
                    $scope.testEmailValidation = false;
                    $scope.testPasswordValidation = false;
                    
                    customerService.getLDAPCustomer({customer_id: $scope.selectedRow.id_customer}).then(function (result) {
                        if (result.data != '') {
                            $scope.ldap =  result.data;
                            $scope.title = 'general.update';
                            $scope.bottom = 'general.update';
                            $scope.action = 'contract.module_questions.change';
                        }
                        else{
                            $scope.title = 'general.add';
                            $scope.bottom = 'general.save';
                            $scope.action = 'general.add';
                        }
                    });
                    $scope.update = false;
                    $scope.isEdit = false;

                    customerService.getSAMLCustomer({customer_id: $scope.selectedRow.id_customer}).then(function (result) {
                        if (result.data != '') {
                            $scope.saml =  result.data;
                            $scope.title1 = 'general.update';
                            $scope.bottom1 = 'general.update';
                            $scope.action = 'contract.module_questions.change';
                        }
                        else{
                            $scope.title1 = 'general.add';
                            $scope.bottom1 = 'general.save';
                            $scope.action = 'general.add';
                            }
                        });
                    if (item) {
                        $scope.isEdit = true;
                        $scope.submitStatus = true;
                        $scope.classification = angular.copy(item);
                        $scope.update = true;
                    } else {
                        //$scope.bottom = 'general.save';
                    }
                    var params ={};
                    $scope.save=function(value, mainForm){
                        if($scope.ldap.email_id == '' || $scope.ldap.email_id == null)
                            $scope.ldap.email_id = 'admin@sourcingcockpit.com';

                        if($scope.ldap.password == '' || $scope.ldap.password == null)
                            $scope.ldap.password = 'password';
                        setTimeout(function(){
                            if (mainForm.$valid) {
                                value.customer_id=item.id_customer;
                                if (value.email_id)
                                    delete value.email_id;
                                customerService.saveLDAP(value).then(function (result) {
                                    if (result.status) {
                                        $rootScope.toast('Success', result.message);
                                        $scope.cancel();
                                        $scope.callServer($scope.tableStateRef);
                                    } else {
                                        $rootScope.toast('Error', result.error,'error');
                                    }
                                });
                            }
                        },100);
                        /* value.customer_id=item.id_customer;
                        if (value.email_id)
                            delete value.email_id;
                        customerService.saveLDAP(value).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        }); */
                    }
                    $scope.saveSaml=function(value, mainForm){
                        console.log("saml form is:",value);
                        if($scope.saml.email_id == '' || $scope.saml.email_id == null)
                            $scope.saml.email_id = 'admin@sourcingcockpit.com';

                        if($scope.saml.password == '' || $scope.saml.password == null)
                            $scope.saml.password = 'password';
                        setTimeout(function(){
                            if (mainForm.$valid) {
                                value.customer_id=item.id_customer;
                                if (value.email_id)
                                    delete value.email_id;
                                customerService.saveSAML(value).then(function (result) {
                                    if (result.status) {
                                        $rootScope.toast('Success', result.message);
                                        $scope.cancel();
                                        $scope.callServer($scope.tableStateRef);
                                    } else {
                                        $rootScope.toast('Error', result.error,'error');
                                    }
                                });
                            }
                        },100);
                    }
                    
                    $scope.testLDAP = function(data, valid) {
                        // console.log('data', data);
                        $scope.testFormSubmitted = true;
                        if(valid) {
                            var params = {};
                            params.email_id = data.email_id;
                            params.password = data.password;
                            params.port = data.port;
                            params.dc = data.dc;
                            params.host = data.host;
                            // console.log('params', params);
                            customerService.testLDAP(params).then(function(result){
                                $scope.ldapResult = result;
                                // console.log('$scope.ldapResult', $scope.ldapResult);
                            });
                        }
                        
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
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

        $scope.goToMFA=function(row){
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/customers/mfa-config.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.mfa = {
                        'is_email_verification_active': 0,
                        'is_mfa_active': 0,
                    };
                    $scope.mfa=$scope.selectedRow;
                    console.log("mfa",$scope.mfa);
                    if (item.updated_by != null) {
                        $scope.title = 'general.update';
                        $scope.bottom = 'general.update';
                    }
                    else{
                        $scope.title = 'general.add';
                        $scope.bottom = 'general.save';
                    }
                    $scope.getEnableMfa=function(id){
                        $scope.enableMfa=id;
                    }
                    $scope.saveMFA=function(value1){
                        var params={};
                        params.customer_id=item.id_customer;
                        params.is_mfa_active=value1.is_mfa_active;
                        params.is_email_verification_active=value1.is_email_verification_active;
                            customerService.saveMFA(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $scope.cancel();
                                    $scope.callServer($scope.tableStateRef);
                                } else {
                                    $rootScope.toast('Error', result.error,'error');
                                }
                            });
                        }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };


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

        }
        $scope.goto = function (row) {
            if (row){
                var Id = encode(row.id_customer);
                var name = row.company_name;
                $state.go('app.customer.edit-customer', {name:name,id:Id});
            }
            else
                $state.go('app.customer.create-customer');
        }
        $scope.gotoAdmin = function(row) {
            var company_name = row.company_name;
            var customer_id = encode(row.id_customer);
            $state.go('app.manage-admin.admin-list',{name:company_name,id:customer_id});
        }

        $scope.gotoUser = function(row){
            var company_name = row.company_name;
            var customer_id = encode(row.id_customer);
            $state.go('app.manage-user.user-list',{name:company_name,id:customer_id});
        }
    })
    .controller('addCustomerCtrl', function ($state, $rootScope, $scope, $stateParams,$window, encode, decode, customerService, templateService, Upload,$location) {
        $rootScope.module = 'Customer';
        $scope.customer = {};
        $scope.customer_id = decode($stateParams.id);
        $scope.company_name = $stateParams.name;
        $scope.trash=false;
        if(!$scope.customer_id){
        customerService.languageSelection().then(function(result){
            $scope.language = result.data;
            $scope.languageSelection = result.data;
        });    
    }

        $scope.uploadUserImage=function(file){
            if(file!=null&&file!=''){
                //$scope.userLogoRemove();
                setTimeout(function(){
                    $scope.customer.company_logo=file;
                    $scope.trash=true;
                    $scope.$apply();
                },100)
            }
        };
        $scope.userLogoRemove=function(){
            $scope.customer.company_logo='';
            $scope.trash=false;
        };
        $scope.logoRemove=function(){
             $scope.customer.companyLogo='';
              $scope.customer.company_logo_small ='';
              $scope.trash=false;
        };
        $scope.title="general.create";
        $scope.bottom="general.save";
        $scope.isEdit = false;
        if($scope.customer_id){
            $scope.title="general.edit";
            $scope.bottom="general.update";
            $scope.isEdit = true;
            customerService.getCustomer({'id_customer':$scope.customer_id}).then(function(result){
                $scope.customer = result.data[0];
                if($scope.customer.company_logo_small){
                    $scope.trash =true;
                }
                if($scope.customer.primary_language_id){
                    $scope.primary=true;
                }
                $scope.getCounts($scope.customer);
                $rootScope.displayName = $scope.customer.company_name;
            });

            customerService.languageSelection({'secondary_language':true,'customer_id':$scope.customer_id}).then(function(result){
                $scope.languageSelection = result.data;
            });
    
            customerService.languageSelection().then(function(result){
                $scope.language = result.data;
            });
        }
        $scope.gotoTemplate = function (customer){
            angular.forEach($scope.templatesList,function(item,key){
                if(item.id_template == customer.template_id){
                    //$state.go('app.templates.templates-view.module',{name:item.template_name,id:encode(item.id_template)});
                   var url = $state.href('app.templates.templates-view.module',{name:item.template_name,id:encode(item.id_template)});
                    $window.open(url);
                }
            });
        }
        $scope.getCounts = function (obj) {
            $scope.customer.template_name=obj.template_name;
            templateService.getCounts({'template_id':obj.id_template}).then(function(result){
                $scope.customer.counts =  result.data;
            });
        }

        $scope.primaryLanugae=function(id){
            $scope.secondaryLanguage=[];
            angular.forEach($scope.language,function(item,key){
                if(item.id_language != id){
                 $scope.secondaryLanguage.push(item);
                    }   
                });
             $scope.languageSelection=$scope.secondaryLanguage;
            
            }
          
        $scope.addCustomer = function (customer) {
            if(!customer.company_logo_small){
                customer.is_delete_logo = 1;
            }else customer.is_delete_logo = 0;
            if(typeof customer.id_customer!='undefined' && ((isNaN(customer.id_customer)===false && customer.id_customer > 0) || (isNaN(customer.id_customer)===true && customer.id_customer.length > 0))) {
                customer.updated_by = $scope.user.id_user;
                delete customer.business_unit;
                Upload.upload({
                    url: API_URL+'Customer/update',
                    data: {
                        'customer': customer
                    }
                }).then(function(resp){
                    if(resp.data.status){
                        $rootScope.toast('Success',resp.data.message);
                        var obj = {};
                        obj.action_name = 'update';
                        obj.action_description = 'update$$customer$$'+customer.company_name;
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                        $state.go('app.customer.customer-list');
                    }else{
                        $rootScope.toast('Error',resp.data.error,'error',$scope.customer);
                    }
                },function(resp){
                    $rootScope.toast('Error',resp.error);
                },function(evt){
                    var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                });
            } else {
                customer.created_by = $scope.user.id_user;
                Upload.upload({
                    url: API_URL+'Customer/add',
                    data: {
                        'customer': customer
                    }
                }).then(function(resp){
                    if(resp.data.status){
                        $rootScope.toast('Success',resp.data.message);
                        var obj = {};
                        obj.action_name = 'add';
                        obj.action_description = 'add$$customer$$'+customer.company_name;
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                        $state.go('app.customer.customer-list');
                    }else{
                        $rootScope.toast('Error',resp.data.error,'error',$scope.customer);
                    }
                },function(resp){
                    $rootScope.toast('Error',resp.error);
                },function(evt){
                    var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                });
            }
        }
        $scope.cancel = function () {
            //$window.history.back();
            $state.go('app.customer.customer-list');
        }
    })
    .controller('manageAdminCtrl',function($scope,$rootScope,$localStorage,$state,$stateParams, customerService){
    })
    .controller('customerAdminListCtrl', function($timeout, $scope, userService, $filter,$rootScope, $stateParams, $state, decode, encode, customerService, userService,$window,$location, $localStorage){
        $rootScope.module = 'Customer';
        $rootScope.displayName = $stateParams.name;
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.adminsList = {};
        $scope.id = decode($stateParams.id);
        $scope.displayCount = $rootScope.userPagination;
        $scope.showForm = function(row){
            if(row){
                $scope.user_id = encode(row.id_user);
                $scope.user_name = row.name;
                $state.go('app.manage-admin.edit-admin',{id:encode($scope.id),name:$stateParams.name,userId:$scope.user_id});
            }
            else
                $state.go('app.manage-admin.create-admin',{name:$stateParams.name,id:encode($scope.id)});
        }

        $scope.deleteAdminUser = function(data){
            var r=confirm($filter('translate')('general.alert_continue'));
            if(r==true){
                customerService.deleteUsers({'id_user':data.id_user}).then(function(result){
                    if(result.status){
                        $rootScope.toast('Success', result.message);
                        $scope.callServer($scope.tableStateRef);
                    }
                    else{
                        rootScope.toast('Error', result.error,'error');
                    }
                })
            }
          
        }
        $scope.callServer = function callServer(tableState) {
            $scope.isLoading = true;
            $scope.tableStateRef = tableState;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.id;
            console.log('typeof tableState---', typeof tableState);
            customerService.getAdminList(tableState).then(function (result){
                $scope.adminsList = result.data.data;
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
        $scope.delete = function(row){
            var params = {};
            params.id_user = row.id_user;
            customerService.deleteAdmin(params).then(function (result){
                if (result.status) {
                    $rootScope.toast('Success', result.message);
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
                    $rootScope.userPagination = result.data.data.display_rec_count;
                    //window.location.href = APP_DIR;
                    $timeout(function(){
                        window.location.href = APP_DIR;
                    },2000);
                    //$window.open('http://localhost:3000/#/dashboard', '_blank');
                    //$window.location.href = 'http://localhost:3000/#/dashboard';
                    //$window.location.reload();
                    //$location.path('/');
                }
            });
        }

        $scope.unblock = function (row) {
            var params ={};
            params.email = row.email;
            userService.unBlock(params).then(function(result){
                if(result.status){
                    $rootScope.toast('User unblocked', result.message);
                    $scope.callServer($scope.tableStateRef);
                } else {
                    $rootScope.toast('Error', result.error,'error');
                }
            });
        }
    })
    .controller('addAdminCtrl', function($scope, $rootScope, $state, $window, encode, decode, customerService ,$stateParams, $location){
        $rootScope.module = 'Customer';
        $rootScope.displayName = $stateParams.name;
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.compnay_name = $stateParams.name;
        $scope.customerId = decode($stateParams.id);
        $scope.user_id = decode($stateParams.userId);
        $scope.admin = {};
        $scope.title = "general.create";
        $scope.bottom="general.save";
        $scope.action="general.add";

        if(!$scope.user_id){
            customerService.languageSelection({'user_languages':true,'customer_id': $scope.customerId}).then(function(result){
                $scope.language = result.data;
            });
        }
    
        if($scope.user_id){
            $scope.title = "general.edit";
            $scope.bottom="general.update";
            $scope.action="general.update";
            var param ={};
            param.customer_id = $scope.customerId;
            param.user_id = $scope.user_id;
            customerService.getAdminById(param).then(function(result){
                $scope.admin = result.data;
            });

            customerService.languageSelection({'user_languages':true,'customer_id': $scope.customerId}).then(function(result){
                $scope.language = result.data;
            });

        }
        $scope.addAdmin =  function (admin){
            var params ={};
            params = admin;
            params.created_by = $scope.user.id_user;
            params.customer_id = $scope.customerId;
            if(admin.is_manual == 0){
                delete admin.password;
                admin.is_manual_password = 0;
            }else{
                admin.is_manual_password = 1;
            }

            customerService.postAdmin(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    var obj = {};
                    obj.action_name = $scope.action;
                    obj.action_description = $scope.action+'$$customer$$'+admin.first_name+'$$'+admin.last_name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $state.go('app.customer.customer-list');
                }else{
                    if(result.error.email)
                        $rootScope.toast('Error',result.error.email);
                    else
                        $rootScope.toast('Error',result.error,'error');
                }
            });
        }
        $scope.resetPassword = function(adminPwd,admin){
            var params ={};
            params.customer_id = $scope.customerId;
            params.user_id = $scope.user_id;
            params.password = adminPwd.npassword;
            params.cpassword = adminPwd.cpassword;
            customerService.resetPassword(params).then (function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    var obj = {};
                    obj.action_name = 'update';
                    obj.action_description = 'update$$customer admin$$password$$'+admin.first_name+'$$'+admin.last_name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $state.go('app.manage-admin.admin-list',{name:$stateParams.name,id:encode($scope.customerId)});
                }else{
                    $rootScope.toast('Error',result.error,'error',$scope.user);
                }
            });
        }
        $scope.cancel = function(){
            //$window.history.back();
            $state.go('app.manage-admin.admin-list',{name:$stateParams.name,id:encode($scope.customerId)});
        }
    })

    .controller('customerUserCtrl',function($scope,$rootScope,$localStorage,$state,$stateParams, customerService, masterService){
        $scope.userRoles = {};
        $scope.dynamicPopover = {templateUrl: 'myPopoverTemplate.html'};
        masterService.getUserRole().then(function(result){
            $scope.userRoles = result.data;
        });
    })
    .controller('customerUserListCtrl', function($timeout, $scope, $rootScope, $stateParams, $state, 
                                                 $filter,decode, encode, customerService,userService,$localStorage, $window,$location){
        $rootScope.module = 'Customer';
        $rootScope.displayName = $stateParams.name;
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.usersList = {};
        var id = decode($stateParams.id);
        $scope.displayCount = $rootScope.userPagination;
        $scope.showForm = function(row){
            if(row){
                var user_id = encode(row.id_user);
                var user_name = row.name;
                $state.go('app.manage-user.edit-user',{id:encode(id),name:$stateParams.name,userId:user_id});
            }
            else
                $state.go('app.manage-user.create-user',{name:$stateParams.name,id:encode(id)});
        }

        $scope.deleteUser = function(info){
            var r=confirm($filter('translate')('general.alert_continue'));
            if(r==true){
                customerService.deleteUsers({'id_user':info.id_user}).then(function(result){
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                        $scope.callServer($scope.tableStateRef);
                    } else {
                        $rootScope.toast('Error', result.error,'error');
                    }
                })
            }
            
        }
        $scope.callServer = function callServer(tableState) {
            $scope.tableStateRef = tableState;
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.customer_id = id;
            tableState.user_role_id = $scope.user1.user_role_id;
            tableState.id_user = $scope.user1.id_user;
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
        $scope.delete = function(row){
            var params = {};
            params.id_user = row.id_user;
            customerService.deleteUser(params).then(function (result){
                if (result.status) {
                    $rootScope.toast('Success', result.message);
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
                    //console.log('$localStorage.curUser',$localStorage.curUser);
                    //window.location.href = APP_DIR;
                    //location.href = APP_DIR;
                    $timeout(function(){
                        window.location.href = APP_DIR;
                    },2000);
                    //$location.path('/');
                }
            });
        }
        $scope.unblock = function (row) {
            var params ={};
            params.email = row.email;
            userService.unBlock(params).then(function(result){
                if(result.status){
                    $rootScope.toast('User unblocked', result.message);
                    $scope.callServer($scope.tableStateRef);
                } else {
                    $rootScope.toast('Error', result.error,'error');
                }
            });
        }
    })
    .controller('addUserCtrl', function($scope, $rootScope, $state, $window, encode, decode, customerService ,$stateParams,$location){
        $rootScope.module = 'Customer';
        $rootScope.displayName = $stateParams.name;
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.customer_id = decode($stateParams.id);
        $scope.customerId = decode($stateParams.id);
        $scope.user_id = decode($stateParams.userId);
        $scope.customUser = {};
        $scope.customUser.user_type = 'internal'; 
        $scope.title = "general.create";
        $scope.bottom="general.save";
        $scope.action="general.add";

        if(!$scope.user_id){
        customerService.languageSelection({'user_languages':true,'customer_id': $scope.customer_id}).then(function(result){
            $scope.language = result.data;
        });
    }

        $scope.userRoleType=function(id){
            console.log("role",id);
            $scope.roleType=id;        }

        if($scope.user_id){
            $scope.title = "general.edit";
            $scope.bottom="general.update";
            $scope.action="general.update";
            var param ={};
            param.customer_id = $scope.customerId;
            param.user_id = $scope.user_id;
            customerService.getUserById(param).then(function(result){
                $scope.customUser = result.data;
            });

            customerService.languageSelection({'user_languages':true,'customer_id': $scope.customer_id}).then(function(result){
                $scope.language = result.data;
            });
    
        }
        $scope.addUser =  function (customUser){
            
            var params ={};
            params = customUser;
            params.created_by = $scope.user.id_user;
            params.customer_id = $scope.customerId;
            if(customUser.is_manual == 0){
                delete customUser.password;
                customUser.is_manual_password = 0;
            }else{
                customUser.is_manual_password = 1;
            }
            delete params.business_unit;
            if($scope.user_id>0){params.user_type = $scope.customUser.user_type;}
            customerService.postUser(params).then(function(result){
                if(result.status){
                    var obj = {};
                    obj.action_name = $scope.action;
                    obj.action_description = $scope.action+'$$customer user$$'+customUser.first_name+'$$'+customUser.last_name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $rootScope.toast('Success',result.message);
                    $state.go('app.manage-user.user-list',{name:$stateParams.name,id:encode($scope.customerId)});
                }else{
                    $rootScope.toast('Error',result.error, 'error');
                }
            });
        }
        $scope.resetPassword = function(userPwd,customUser){
            var params ={};
            params.customer_id = $scope.customerId;
            params.user_id = $scope.user_id;
            params.password = userPwd.npassword;
            params.cpassword = userPwd.cpassword;
            params.user_type = 'internal';
            customerService.resetPassword(params).then (function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    var obj = {};
                    obj.action_name = 'update';
                    obj.action_description = 'update$$customer$$user$$password$$'+customUser.first_name+'$$'+customUser.last_name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $state.go('app.manage-user.user-list',{name:$stateParams.name,id:encode($scope.customerId)});
                }else{
                    $rootScope.toast('Error',result.error,'error',$scope.user);
                }
            });
        }
        $scope.cancel = function(){
            //$window.history.back();
            $state.go('app.manage-user.user-list',{name:$stateParams.name,id:encode($scope.customerId)});
        }
    })

    .controller('ManageTemplatesCtrl', function($scope,$rootScope, $state,$location,$stateParams,encode,decode,$window,customerService, userService, templateService,$uibModal){
        $rootScope.module = 'Customer';
        $rootScope.displayName = $stateParams.name;
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.customerId = decode($stateParams.id);
        $scope.user_id = decode($stateParams.userId);
        $scope.customer={};
        $scope.displayCount = $rootScope.userPagination;
      //  $scope.tableStateRef={};
        $scope.callServer = function callServer(tableState) {
            $scope.isLoading = true;
            $scope.tableStateRef=tableState;
            var pagination = tableState.pagination;
            tableState.customer_id=$scope.customerId;
            templateService.list(tableState).then(function (result){
                $scope.templateList = result.data.data;
                $scope.data = result.data.data;
                $scope.emptyTable = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if(result.data.total_records < 1)$scope.emptyTable = true;
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
        $scope.showForm = function (row){
            $scope.selectedRow = row;
            $scope.module.module_name = '';
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/customers/link-customer-template-modal.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.module = {};
                    $scope.title = 'general.add';
                    $scope.bottom = 'general.save';
                    $scope.action = 'general.add';
                    $scope.isEdit = false;
                    if (item) {
                        $scope.submitStatus = true;
                        $scope.module = angular.copy(item);
                        $scope.update = true;
                        $scope.title = 'Update';
                        $scope.isEdit = true;
                        $scope.title = 'general.edit';
                        $scope.bottom = 'general.update';
                        $scope.action = 'general.update';
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    var params ={};
                    $scope.save=function(customer){
                        var obj = {};
                        obj.action_name = $scope.action;
                        obj.action_description = $scope.action+'$$template-$$'+customer.tempalte_name;
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);                    
                        if(customer.template_id)params.template_id=customer.template_id.id_template;
                        else params.template_id='';
                        if(customer.new_template_name)params.new_template_name=customer.new_template_name;
                        else params.new_template_name=customer.template_id.template_name;
                        params.customer_id=$scope.customerId;
                        customerService.linkTemplate(params).then(function (result){  
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                              
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
        $scope.getCounts = function (obj) {
            console.log('******',obj);
            $scope.customer.template_name = obj.template_name;
            templateService.getCounts({'template_id':obj.id_template}).then(function(result){
                $scope.customer.counts =  result.data;
            });
        }
    })
    .run(function ($rootScope, $window) {
        $rootScope.myGlobalFunction = function () {
            //TODO : your custom code here.
            $window.alert($filter('translate')('general.alert_global'));
            console.log("I am global function. :)");
        };
    });