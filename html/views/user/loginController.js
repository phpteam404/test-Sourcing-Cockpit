angular.module('app')
    .controller('loginCtrl',function($window,$state,$rootScope,$stateParams,$scope,$localStorage,AuthService,$http,encode,userService,$location){
        if($stateParams.id){
            $state.go('appSimple.login',{},{reload: true, inherit: false });
        }
        $scope.menu ={};
        $scope.showLoadingSubmit=false;
        $scope.submitMfa=true;
        $scope.verifyCode=false;
        $scope.showSsoAd=false;
        $scope.mfaLoading=false;
        $scope.showBtnSubmit=false;
        $scope.mfaActive=false;
          $scope.submitMfa=function(verification_method,email_id){
            if(verification_method && email_id){
                $scope.verifyCode=true;
            var param={};
            param.email_id=email_id;
            param.verification_method=verification_method;
            userService.sendVerificationCode(param).then(function(result){
                $scope.verifyCode=false;
                if (result.status) {
                    $rootScope.toast('Success',result.message);
                    $rootScope.success();
                    }
                else{
                    $rootScope.toast('Error', result.error, 'error');

                    }
                });
            }
            }      
            $scope.mfaForm=function(form,user){
                $scope.mfaLoading=true;
                var param={};
                    param.email_id=user.email_id;
                    param.is_trust=user.is_trust;
                    param.verification_code=user.verification_code;
                    param.verification_method=user.verification_method;
                    userService.sendMfa(param).then(function(result){
                        if (result.status) {
                            $scope.isADLoginPage=false;
                            $scope.isSSOLoginPage=false;
                            $scope.isLoginWithMfa=false;
                            $scope.isEmailPage=true;
                            $scope.mfaLoading=false;
                            $scope.mfaActive=true;
                            $rootScope.access = result;
                            $scope.userData= result;
                            if($scope.userData.trust_device.length!=0){
                                $localStorage.trustDevice = $scope.userData;
                            }
                            $scope.checkmail();
                            }
                        else{
                            $rootScope.toast('Error', result.error, 'error');
                            $scope.mfaLoading=false;
                            }
                        });
            }


            $scope.submitLogin=function(loginForm,user,type){
                user.verification_method='email';
                if(user.email_id && user.password==null){
                    $scope.showLoadingSubmit=true;
                    $scope.showBtnSubmit=true;
                    $scope.checkmail=function(){
                        if($localStorage.trustDevice!=undefined){
                            if (angular.equals(user.email_id, $localStorage.trustDevice.trust_device.email_id)){
                                $scope.device_id =true;
                            }
                            else{
                                $scope.device_id =false;
                            }
                         }
                    var param={};
                    param.email_id=user.email_id;
                    if($scope.device_id) param.trusted_device_id=$localStorage.trustDevice.trust_device.device_id;
                    userService.signUpCheckmail(param).then(function(result){
                        $scope.showBtnSubmit=false;
                        $scope.showLoadingSubmit=false;
                        if (result.status) {
                            $scope.isEmailPage=true;
                            $scope.checkMailDetails=result.data;
                            }
                        else{
                            $rootScope.toast('Error', result.error, 'error');
                            }
                        });
                    }
                    $scope.checkmail();
    
                }
                else if(user.email_id && user.password!=null){
                    //console.log("entered else");
                  if (loginForm.$valid) {
                    $scope.showLoadingSubmit=true;
                    $scope.showBtnSubmit=true;
                    $scope.showSsoAd=true;
                    var param = {};
                    param.password = encode(user.password);
                    param.email_id = user.email_id;
                    param.session_exceed = 0;
                    param.login_with_ldap = type;
                    if($scope.mfaActive){
                        param.imiv = 'imiv';
                    }
                    if($localStorage.trustDevice){
                    if(user.email_id == $localStorage.trustDevice.trust_device.email_id){
                            
                        param.device_id=$localStorage.trustDevice.trust_device.device_id;    
                        }
                    }
                    if($scope.checkMailDetails.imiv=='imiv'){
                        param.imiv = 'imiv';
                    }
                    $scope.userData = {};
                    userService.post(param).then(function(result){
                        // $scope.showBtnSubmit=false;
                        // $scope.showLoadingSubmit=false;    
                        if (result.status) {
                            $rootScope.access = result.data.data.access;
                            $scope.userData = result;
                            $localStorage.curUser = $scope.userData;
                            $localStorage.curUser.data.filters={};
                            $scope.showSsoAd=false;                            
                                if ($localStorage.curUser && !angular.equals({}, $localStorage.curUser)) {                
                                    var menuObj = $localStorage.curUser.data.menu;
                                    if (Array.isArray(menuObj) && menuObj.length > 0 && menuObj[0].module_url) {
                                       $location.path(menuObj[0].module_url);
                                    } else {
                                        $state.go('app.404', null, {location: false});
                                    }
                                } else {
                                    $state.go('appSimple.login');
                                }
                           
                        }else {
                            $scope.showLoadingSubmit=false;
                            if(result.error.message)$rootScope.toast('Error', result.error.message, 'l-error');
                            else{$rootScope.toast('Error', result.error, 'error');}
                            $scope.showSsoAd=false;
                        }
                    });
                  }
                }
            }  

            $scope.ssoUrl=function(){
                window.location = $scope.checkMailDetails.sso_login_url;
            }

            
        $scope.forgotPassword = function(user){
            var param = {};
            param = user;
            userService.forgotPassword(param).then(function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    $state.go('appSimple.login');
                }else{
                    $rootScope.toast('Error',result.error.email);
                }
            });
        }
    })

    .controller('samlCtrl',function($window,$state,$rootScope,$stateParams,$scope,$localStorage,AuthService,$http,encode,userService,$location){

        var params={};
        params.login_with_saml=1;
        params.token=$stateParams.token;
         userService.post(params).then(function(result){
            // if (result.status) {
            //     console.log("success");
            //     $rootScope.toast('Success',result.message);
            //     }
            // else{
            //     $rootScope.toast('Error', result.error, 'error');
            //     }

            if (result.status) {
                $rootScope.access = result.data.data.access;
                $scope.userData = result;
                $localStorage.curUser = $scope.userData;
                $localStorage.curUser.data.filters={};
                    if ($localStorage.curUser && !angular.equals({}, $localStorage.curUser)) {                
                        var menuObj = $localStorage.curUser.data.menu;
                        if (Array.isArray(menuObj) && menuObj.length > 0 && menuObj[0].module_url) {
                           $location.path(menuObj[0].module_url);
                           window.location.href = APP_DIR;
                        } else {
                            $state.go('app.404', null, {location: false});
                        }
                    } else {
                        $state.go('appSimple.login');
                    }
               
            }else {
                if(result.error.message)$rootScope.toast('Error', result.error.message, 'l-error');
                else{$rootScope.toast('Error', result.error, 'error');}
                if(result.logout_url != undefined)
                    {
                    $window.location.href=result.logout_url;
                    }
                    else{

                    $state.go('appSimple.login');
                    }


            }
            });
         })

    .controller('logoutCtrl',function($state,$rootScope,$scope,$localStorage,AuthService,$http,encode,userService,$location){
        $rootScope.displayName ='';
        $rootScope.module = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        var param = {};
        userService.logout(param).then(function(result){
            console.log('==========*****=');
            setTimeout(function(){
                if($localStorage.curUser.data.isSamlLogin == true){
                    var samlLogoutUrl=$localStorage.curUser.data.SamlLogOutUrl;
                    localStorage.clear();
                    $localStorage.curUser = undefined;
                    console.log("samllogout","samllogout2");
                    $window.location.href=samlLogoutUrl;
                }
                else{
                    localStorage.clear();
                    $localStorage.curUser = undefined;
                    $window.location.reload();
                }
                
                $state.go('appSimple.login');
            },400);
        });
    })