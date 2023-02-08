// Default colors
var brandPrimary = '#20a8d8';
var brandSuccess = '#4dbd74';
var brandInfo = '#63c2de';
var brandWarning = '#f8cb00';
var brandDanger = '#f86c6b';
var grayDark = '#2a2c36';
var gray = '#55595c';
var grayLight = '#818a91';
var grayLighter = '#d1d4d7';
var grayLightest = '#f8f9fa';

angular
    .module('app', [
        'ui.router',
        'oc.lazyLoad',
        'ncy-angular-breadcrumb',
        'ngStorage',
        'ui.bootstrap',
        'ui.utils.masks',
        'ui.calendar',
        'toastr',
        'pascalprecht.translate',
        'ngFileUpload',
        'smart-table',
        'angularMoment',
        'ui.tab.scroll'
    ])

    .config(['stConfig', function (stConfig) {
        stConfig.pagination.itemsByPage = 10;
        // stConfig.pagination.template = 'my-custom-pagination-tmpl.html';
    }])
    .config(['$qProvider', function($qProvider) {
        $qProvider.errorOnUnhandledRejections(false);
    }])
        // loader
    /*.config(['cfpLoadingBarProvider', '$httpProvider', function (cfpLoadingBarProvider, $httpProvider) {
        cfpLoadingBarProvider.includeSpinner = false;
        cfpLoadingBarProvider.latencyThreshold = 1;
        $httpProvider.defaults.useXDomain = true;
        delete $httpProvider.defaults.headers.common['X-Requested-With'];
        //$httpProvider.interceptors.push('errorInterceptor');
    }])*/
    .config(['$httpProvider', function ( $httpProvider) {
        //cfpLoadingBarProvider.includeSpinner = false;
        //cfpLoadingBarProvider.latencyThreshold = 1;
        $httpProvider.defaults.useXDomain = true;
        delete $httpProvider.defaults.headers.common['X-Requested-With'];
        //$httpProvider.interceptors.push('errorInterceptor');
    }])
    //language config
    .config(['$translateProvider', function ($translateProvider) {
        $translateProvider.useStaticFilesLoader({
            prefix: 'language/lag_',
            suffix: '.json?ver=6.3.0'
        });
        $translateProvider.useSanitizeValueStrategy(null);
        $translateProvider.preferredLanguage('en'); //default language
    }])
    .run(['$rootScope', '$state','$filter', '$stateParams', '$timeout', 'AuthService','userService', 'toastr', '$location','$localStorage','$window','$http','$q', '$locale', function ($rootScope, $state, $filter,$stateParams, $timeout, AuthService,userService, toastr, $location,$localStorage, $window, $http,$q,$locale) {
        $locale.NUMBER_FORMATS.CURRENCY_SYM = "€";
        $locale.NUMBER_FORMATS.GROUP_SEP = ".";
        $locale.NUMBER_FORMATS.DECIMAL_SEP = ",";

        $rootScope.appVersion = "6.3.0"; // appVersion
        $rootScope.pagesNumber =[10,20,50,100];
        $rootScope.$on('$stateChangeStart', function (event, toState,toParams,fromState,fromParams) {
            /*userService.signUp().then(function(result){
                result = window.atob(result);
                result = JSON.parse(result);
                if(result.status){
                    AES_KEY = result.data.AES_KEY;
                    DATA_ENCRYPT = result.data.DATA_ENCRYPT;
                }
            })*/
            
        //    setTimeout(function(){ $('body').removeClass('expanded-menu-cls'); }, 500);
        //    setTimeout(function(){ 
        //         if($('.sidebar').hasClass('expanded-menu-cls')){
        //             $('body').addClass('expanded-menu-cls');
        //         } else if($('.sidebar').hasClass('expanded-menu')){
        //             $('body').removeClass('expanded-menu-cls'); 
        //         }
        //     }, 800);
            if(toState.name.indexOf('app.contract') == -1){
                if($localStorage.curUser)
                    $localStorage.curUser.data.filters={};
            }
            $timeout(function(){
                if($('.sidebar').hasClass('expanded-menu') == true){
                    console.log('stateChangeStart--');
                    $('.sidebar').removeClass('expanded-menu');
                    $('.sidebar').addClass('expanded-menu-cls');
                } 
            },300);
            if (AuthService.login() && toState.name == 'appSimple.login') {
                event.preventDefault();
                 $location.path('/');
            }
           if(toState.name != 'appSimple.login' && !AuthService.login()) {
               $rootScope.returnToState = toState.name;
               $rootScope.returnToStateParams = toParams;
           }
           $rootScope.currentUrl = $state.href(toState.name, toParams);
           $rootScope.checkPermission = function checkPermission($state, AuthService, $q, $rootScope) {
                var deferred = $q.defer();
                $rootScope.permission = [];
                AuthService.checkUrl($state.href(toState.name, toParams)).then(function (result) {
                    $rootScope.permission = result;
                    if ($rootScope.permission) {
                        deferred.resolve();
                    }else{
                        $state.go("app.404");
                        deferred.reject();
                    }
                });
                return deferred.promise;
           }
           //$rootScope.checkPermission($state, AuthService, $q, $rootScope);
            $rootScope.loader(toParams);
        })

        $rootScope.$on('loggedOut',function(){
            $rootScope.displayName ='';
            $rootScope.module = '';
            // $localStorage.curUser = undefined;                    
            // localStorage.clear();
            // $window.location.reload();

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

            
            /*$localStorage.curUser = undefined;
            setTimeout(function(){
                $location.path('/');
            },200);*/
            // $state.go('appSimple.login',{id:1});
        });
        $rootScope.$on('$stateChangeError', function(evt, to, toParams, from, fromParams, error) {
            $timeout(function(){
                $rootScope.pageLoading=false;
                $($window).resize();
            },300);

            $timeout(function(){
                $rootScope.pageLoaded=true;
                $rootScope.appInitialized=true;
            },400);
        });

        $rootScope.$on('$stateChangeSuccess', function (event, toState,toParams) {
            /*userService.signUp().then(function(result){
                result = window.atob(result);
                result = JSON.parse(result);
                if(result.status){
                    AES_KEY = result.data.AES_KEY;
                    DATA_ENCRYPT = result.data.DATA_ENCRYPT;
                }
            })*/
            document.body.scrollTop = document.documentElement.scrollTop = 0;
            $timeout(function(){
                $rootScope.hidePreloader=false;
                $rootScope.pageLoading=false;
                $($window).resize();
            },200);
            $timeout(function(){
                if($('.sidebar').hasClass('expanded-menu') == true){
                    $('.sidebar').removeClass('expanded-menu');
                    $('.sidebar').addClass('expanded-menu-cls');
                } 
            },1000);
            // $timeout(function(){
            //     $rootScope.pageLoaded=true;
            //     $rootScope.appInitialized=false;
            // },200);
            if(toState.name != 'appSimple.login' && toState.name !='appSimple.saml'){
                var params = {};
                params.action_name = 'view';
                params.action_description = 'view '+toState.activeLink;
                params.module_type = toState.activeLink;
                if(!angular.isUndefined(AuthService.getFields().access_token)){
                    var s = AuthService.getFields().access_token.split(' ');
                    params.access_token = s[1];
                }
                else params.access_token = '';
                params.action_url= $location.$$absUrl;
                if(!angular.isUndefined(AuthService.getFields().data.parent)){
                    params.user_id = AuthService.getFields().data.parent.id_user;
                    params.acting_user_id = AuthService.getFields().data.data.id_user;
                }
                else params.user_id = AuthService.getFields().data.data.id_user;
                $http.post(API_URL + 'User/accessLog', params).then(function(response){
                    return response.data;
                });
            }
            // $rootScope.hidePreloader=false;// app stall issue
        });
        $rootScope.loader = function(toParams){
            if(!toParams.hasOwnProperty('hidePreloader')){
                $rootScope.pageLoading=true;
                $rootScope.hidePreloader=true;// app stall issue
                $rootScope.pageLoaded=false;
            }
        }
        $rootScope.toast = function (title, message, type, allFields) {
            $timeout(function () {
                toastr.clear();
                toastr.options = {
                    showMethod: 'fadeIn',
                    preventDuplicates: false,
                    timeOut: 3000
                };
                switch (type) {
                    case 'error':
                        var errorCount=0;
                        $('.has-error').removeClass('has-error');
                        $('.error-message').remove();
                        jQuery.each(message,function(index,value){
                            if(message.hasOwnProperty(index)){
                                errorCount++;
                                jQuery('[name="'+index+'"]').addClass('has-error');
                                jQuery('[name="'+index+'"]').parent().append('<div class="error-message server-message" style="white-space: nowrap;">'+message[index]+'</div>');
                            }
                        })
                        if(errorCount<2){
                            jQuery.each(message,function(index,value){
                                toastr.error(value,$filter('translate')('general.error'));
                            });
                        }else{
                            toastr.error('Invalid Form Fields',title);
                        }
                        break;
                    case 'image-error':
                        toastr.error(message, title);
                        break;
                    case 'l-error':
                        toastr.error(message, title);
                        break;
                    case 'Success':
                        toastr.success(message, title);
                        break;
                    case 'warning':
                        toastr.warning(message, title);
                        break;
                    case 'warning_m':
                        toastr.options.positionClass = 'toast-top-center';
                        toastr.warning(message, title);
                        break;
                    case 'warning_l':
                        toastr.warning(message.message, title);
                        break;
                    case 'login':
                        toastr.clear();
                        toastr.options.positionClass = 'toast-top-center';
                        toastr.warning(message, title);
                        break;
                    default:
                        if (title == 'Success') {
                            toastr.success(message, $filter('translate')('general.success'));
                        } else if (title == 'Error') {
                            toastr.error(message,title);
                        } else if (title == 'customError') {
                            toastr.error(message, '');
                        }else {
                            toastr.info(message, title);
                        }
                        break;
                }
            }, 200);
        }
        $rootScope.displayName = '';
        $rootScope.module = '';
        $rootScope.confirmNavigation = function (obj){
            $http.post(API_URL + 'User/accessLog', obj).then(function(response){
                return response.data;
            });
        }
        $rootScope.confirmNavigationForSubmit = function (params){
            var data={};
            data.action_name = params.a_n;
            data.action_description = params.a_d;
            if(AuthService.getFields().data.parent){
                params.user_id = AuthService.getFields().data.parent.id_user;
                params.acting_user_id = AuthService.getFields().data.data.id_user;
            }
            else params.user_id = AuthService.getFields().data.data.id_user;
            if(AuthService.getFields().access_token != undefined){
                var s = AuthService.getFields().access_token.split(' ');
                params.access_token = s[1];
            }
            else params.access_token = '';
            $http.post(API_URL + 'User/accessLog', params).then(function(response){
                return response.data;
            });
        }
    }]);