angular.module('app')
    .controller('fullLayoutCtrl', function ($timeout,$window, $stateParams,$state,$rootScope, $scope,$localStorage,encode, userService,notificationService, $http,$location,AuthService){
        //$scope.user={};
        $scope.menu={};
        $scope.menu = $localStorage.curUser.data.menu;
        $scope.saml=$localStorage.curUser.data.isSamlLogin;
        $scope.user1 = $localStorage.curUser.data.data;
        $scope.parent_user = $localStorage.curUser.data.parent;
        $scope.year = new Date().getFullYear();
        //$location.path($scope.menu[0].module_url);
        $rootScope.getProfile = function () {
            $scope.user = {};
            userService.getUserProfile({'user_id': $scope.user1.id_user}).then(function (result) {
                //console.log('r',result);
                $scope.user = result.data;
            });
        }
        $rootScope.getProfile();
        $rootScope.getCompany = function () {
            userService.companyDetails({'id_customer':$scope.user1.customer_id}).then(function (result) {
                $scope.customer = result.data;
            });
        }
        $rootScope.getCompany();
        $scope.logout = function () {
            $rootScope.displayName ='';
            $rootScope.module = '';
            var param = {};
            userService.logout(param).then(function(result){
            setTimeout(function(){
                if(result.status){
                    if($localStorage.curUser.data.isSamlLogin == true){
                        var samlLogoutUrl=$localStorage.curUser.data.SamlLogOutUrl;
                        localStorage.clear();
                        $localStorage.curUser = undefined;
                        $window.location.href=samlLogoutUrl;
                    }
                    else{
                        localStorage.clear();
                        $localStorage.curUser = undefined;
                        $window.location.reload();
                    }
                }
            },400);
        });
        }

        $scope.goToParent = function(){
            userService.loginAs({'id_user':$localStorage.curUser.data.parent.id_user}).then(function(result){
                if(result.status){
                    $localStorage.curUser.data.parent = undefined;
                    $localStorage.curUser.data.data = result.data.data;
                    $localStorage.curUser.data.menu = result.data.menu;
                    $localStorage.curUser.data.filters={};
                    $timeout(function(){
                        window.location.href = APP_DIR;
                    },2000);
                }
            });
        }

        $scope.goToNotifications = function(){
            $state.go('app.notifiationList');
        }
        $rootScope.getNotificationsCount = function(){
            notificationService.getCount({'id_user':$scope.user1.id_user,'is_opened':'0'}).then(function(result){
                $scope.notificationsCount = result.data;
            });
        }
        $rootScope.getNotificationsCount();
    })
    .controller('profileCtrl',function($scope, $rootScope, $http, $state, $localStorage, $timeout,decode, $stateParams, $location, userService, customerService,Upload ){
        //console.log('$scope.user1.access',$scope.user1.access)
        console.log('($scope.user1',$scope.user1);
        $scope.id_user=$rootScope.id_user;

        if($localStorage.curUser.data.parent && $state.current.name=="app.changePassword"){
            $rootScope.toast('Access Denied','Access Denied for Change password.','warning');
            $timeout(function(){
                if($scope.user1.access == 'wa')
                    $state.go('app.customer.customer-list');
                if($scope.user1.access != 'wa')
                    $state.go('app.dashboard');
            },2000);
        }else{
            $rootScope.displayName ='';
            $rootScope.module = '';
            $scope.disableField = true;   
            $scope.userProfile ={};
            var id = $stateParams.id;
            $scope.userProfileDetails = {};

            customerService.languageSelection({'user_languages':true,'id_user': $scope.id_user}).then(function(result){
                $scope.language = result.data;
            });
    
            userService.getUserProfile({'user_id':$scope.user1.id_user}).then(function(result){
                $scope.user = result.data;
                // console.log('$scope.user ',$scope.user);
                if($scope.user.gender=='other'){
                    console.log("234");
                    $scope.disableField = false;
                }
            });
            $scope.uploadUserImage=function(file){
                if(file!=null&&file!=''){
                    $scope.userLogoRemove();
                    setTimeout(function(){
                        $scope.userImage=file;
                        $scope.trash=true;
                        $scope.$apply();
                    },100)
                }
            };

            // $scope.disableField = true;
            $scope.getValue = function(val){
                console.log("123user",val);
                if(val=='other') {
                    $scope.disableField = false;
                }
                else{
                    $scope.disableField = true;
                        }
            }

            $scope.userLogoRemove=function(){
                $scope.userImage='';
                $scope.trash=false;
            };
            $scope.cancel=function(){
                $state.go('app.dashboard');
            }
            $scope.updateProfile = function(user){
                console.log("89");
                //$localStorage.curUser.data.data.profile_image = $scope.userImage;
                $rootScope.first_name = $scope.user.first_name;
                $rootScope.last_name = $scope.user.last_name;
                $rootScope.email = $scope.user.email;
                $rootScope.profile_image = $scope.userImage;
                $rootScope.gender = $scope.user.gender;
                $rootScope.language_id = $scope.user.language_id;
                $rootScope.contribution_type=$scope.user.contribution_type;
                user.id_user = $scope.user1.id_user;
                // console.log($scope.user1.id_user);
                Upload.upload({
                    url: API_URL+'User/update',
                    data: {
                        file: {'profile_image': $scope.userImage},
                        'user': user
                    }
                }).then(function(resp){
                    if(resp.data.status){
                    //    console.log("ki",resp.data);
                        $scope.getprofile=function(){
                            userService.getUserProfile({'user_id': $scope.user1.id_user}).then(function (result) {
                            $scope.user = result.data;
                            // console.log("io",$scope.user)
                            // if($localStorage.curUser.data.data.language_iso_code!=$scope.user.language_iso_code){
                            //     window.location.href = APP_DIR;
                            // }
                            $localStorage.curUser.data.data.language_iso_code=$scope.user.language_iso_code;
                            $localStorage.curUser.data.data.language_id=$scope.user.language_id;
                            $localStorage.curUser.data.menu=resp.data.menu;
                            window.location.href = APP_DIR;
                            });
                        }
                        $scope.getprofile();
                        var obj = {};
                        obj.action_name = 'update';
                        obj.action_description = 'update$$myProfile';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                        // $rootScope.toast('Success',result.message);
                        if($scope.user1.access == 'wa')
                            $state.go('app.customer.customer-list');
                        if($scope.user1.access != 'wa')
                            $state.go('app.dashboard');
                    }else{
                        $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                    }
                },function(resp){
                    $rootScope.toast('Error',resp.error);
                },function(evt){
                    var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                });
            }
            $scope.changePassword = function(userProfile){
                var  params={};
                /*params.user_id = $scope.user1.id_user;*/
                params.oldpassword = userProfile.oldPassword;
                params.password = userProfile.newPassword;
                params.cpassword = userProfile.confirmPassword;
                userService.changePassword(params).then(function(result){
                    if(result.status){
                        $rootScope.toast('Success',result.message);
                        var obj = {};
                        obj.action_name = 'update';
                        obj.action_description = 'update$$password';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                        if($scope.user1.access == 'wa')
                            $state.go('app.customer.customer-list');
                        if($scope.user1.access != 'wa')
                            $state.go('app.dashboard');
                    }else{
                        $rootScope.toast('Error',result.error,'error',$scope.user);
                    }
                })
            }
        }
    })
    .controller('companySetupCtrl', function($scope, $rootScope, $stateParams, $state, decode, userService, masterService,Upload,$location){
        $rootScope.displayName ='';
        $rootScope.module = '';
        $scope.countriesList = {};
        masterService.getCountiresList().then(function(result){
            if(result.status){
                $scope.countriesList = result.data;
            }
        })
        $scope.bottom="general.update";
        var params= {};
        params.id_customer = $scope.user1.customer_id;
        userService.companyDetails(params).then(function (result) {
            $scope.customer = result.data;
        });
        $scope.trash=true;
        $scope.uploadCompanyLogo=function(file){
            if(file!=null&&file!=''){
                $scope.companyLogoRemove();
                setTimeout(function(){
                    $scope.companyLogo=file;
                    $scope.trash=true;
                    $scope.$apply();
                },100)
            }
        };
        $scope.companyLogoRemove=function(){
            $scope.companyLogo='';
            $scope.trash=false;
        };
        $scope.LogoRemove = function() {
            $scope.companyLogo='';
            $scope.customer.company_logo_medium ='';
            $scope.trash=false;
        }
        $scope.updateComppany = function (customer){
            customer.id_customer = $scope.user1.customer_id;
            customer.created_by = $scope.user1.id_user;
            customer.company_logo_small= $scope.companyLogo;
            customer.company_logo_medium = $scope.companyLogo;
            customer.company_logo = $scope.companyLogo;
            if(!customer.company_logo_medium){
                customer.is_delete_logo = 1;
            }else customer.is_delete_logo = 0;
           Upload.upload({
                url: API_URL+'Customer/update',
                data: {
                    'customer': customer
                }
           }).then(function(resp){
                if(resp.data.status){
                    $rootScope.getCompany();
                    $rootScope.toast('Success',resp.data.message);
                    var obj = {};
                    obj.action_name = 'update';
                    obj.action_description = 'update$$company setup';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                }else{
                    $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                }
           },function(resp){
                $rootScope.toast('Error',resp.error);
           },function(evt){
                var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
           });
        }
    })
    .controller('manageUserCtrl',function($scope,$rootScope,$localStorage,$state,$http,$window){
        $scope.showUserForm = false;
        $scope.formTitle = 'Add';
    })
    .controller('templatesView',function($scope,$rootScope,$state,$uibModal){
        $scope.loadModal = function()
        {
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'templates-modal.html',
                controller: function ($uibModalInstance, $scope) {

                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        }
    })
    .controller('templatesList',function($scope,$rootScope,$state,$sce){
        $scope.dynamicPopover = {
            content: '',
            templateUrl: 'myPopoverTemplate.html',
            title: 'Title'
        };

        $scope.placement = {
            options: [
                'top'
            ],
            selected: 'top'
        };

        $scope.htmlPopover = $sce.trustAsHtml('<div class="label label-success">' +
            '<ul>' +
            '<li><a href="javascript">Customer-3</a></li>' +
            '<li><a href="javascript">Customer-4</a></li>' +
            '<li><a href="javascript">Customer-5</a></li>' +
            '<li><a href="javascript">Customer-6</a></li>' +
            '</ul></div>');
    })
    .controller('calendar',function($scope,$uibModal){
        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();
        $scope.eventSource = {
            url: "http://www.google.com/calendar/feeds/usa__en%40holiday.calendar.google.com/public/basic",
            className: 'gcal-event',           // an option!
            currentTimezone: 'America/Chicago' // an option!
        };
        $scope.alertOnEventClick = function(ev){
            /*modal.open();*/
        }
        $scope.setCalDate = function(ev){
            $scope.loadModal = function()
            {
                var modalInstance = $uibModal.open({
                    animation: true,
                    backdrop: 'static',
                    keyboard: false,
                    scope: $scope,
                    openedClass: 'right-panel-modal modal-open',
                    templateUrl: 'calendar-event-modal.html',
                    controller: function ($uibModalInstance, $scope) {

                        $scope.cancel = function () {
                            $uibModalInstance.close();
                        };

                    }
                });
                modalInstance.result.then(function ($data) {
                }, function () {
                });
            }
        }
        $scope.uiConfig = {
            calendar:{
                height: 450,
                editable: true,
                header:{
                    left: 'prev,today,next',
                    center: 'title',
                    right: 'month,basicWeek,basicDay'
                },
                dayClick : $scope.setCalDate
            }
        };
        $scope.calEventsExt = {
            color: '#f00',
            textColor: 'yellow',
            events: [

            ]
        };
        $scope.events = [

        ];
        $scope.eventsF = function (start, end, timezone, callback) {
            var s = new Date(start).getTime() / 1000;
            var e = new Date(end).getTime() / 1000;
            var m = new Date(start).getMonth();
            var events = [{title: 'Feed Me ' + m,start: s + (50000),end: s + (100000),allDay: false, className: ['customFeed']}];
            callback(events);
        };
        $scope.eventSources = [$scope.events];

    })
/* .controller('dashboardTabs',function($scope,$rootScope,$localStorage,$state,$http,$window){
 $scope.tabs = [
 { title:'Dynamic Title 1', content:'Dynamic content 1' },
 { title:'Dynamic Title 2', content:'Dynamic content 2', disabled: true }
 ];
 $scope.alertMe = function() {
 setTimeout(function() {
 $window.alert('You\'ve selected the alert tab!');
 });
 };
 $scope.model = {
 name: 'Tabs'
 };
 })*/