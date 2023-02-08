angular
    .module('app')
    .config(['$stateProvider','$translateProvider', '$urlRouterProvider','$ocLazyLoadProvider', '$breadcrumbProvider', '$httpProvider', '$locationProvider', 
            function ($stateProvider,$translateProvider, $urlRouterProvider, $ocLazyLoadProvider, $breadcrumbProvider, $httpProvider, $locationProvider) {
        $locationProvider.hashPrefix('');
        //$urlRouterProvider.otherwise('/404');
        $translateProvider.useStaticFilesLoader({
            prefix: 'language/lag_',
            suffix: '.json?ver=6.3.0'
        });
        $translateProvider.useSanitizeValueStrategy(null);
        $translateProvider.preferredLanguage('en'); 
        $urlRouterProvider.otherwise(function ($injector, $location) {
            $injector.invoke(['$state', function ($state) {
                $state.go('app.404', null, {location: false});
            }]);
        });
        $urlRouterProvider.when('', '/');
        // $urlRouterProvider.when('/', '/dashboard');
        //if null or '/' empty url there ,redirect to first menu url
        $urlRouterProvider.when('/', ['$state', '$localStorage','$location', function ($state, $localStorage, $location) {
            console.log('inroutes',$localStorage);
            // if($localStorage.curUser.data.data.language_iso_code){
            //     $translateProvider.use($localStorage.curUser.data.data.language_iso_code)
            // }
            // else{
            //     $translateProvider.use('en');
            // }

            if($localStorage.curUser && !angular.equals({}, $localStorage.curUser)){
               if($localStorage.curUser.data.data.language_iso_code)
                {
                 $translateProvider.use($localStorage.curUser.data.data.language_iso_code)
                }
                else{
                $translateProvider.use('en');
                 }
                }
               else{
                 $translateProvider.use('en');
                }
                

                
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
            
        }]);
        
        $ocLazyLoadProvider.config({
            // Set to true if you want to see what and when is dynamically loaded
            debug: false
        });
    
        //console.log(' $breadcrumbProvider.get()', $breadcrumbProvider);
        $breadcrumbProvider.setOptions({
            prefixStateName: 'app',
            includeAbstract: true,
            template: '<li class="breadcrumb-item" ng-repeat="step in steps" ng-class="{active: $last}" ng-switch="$last || !!step.abstract"><a class="f16"  ng-switch-when="false" href="{{step.ncyBreadcrumbLink}}">{{step.ncyBreadcrumbLabel}}</a class="f16"><span ng-switch-when="true" >{{step.ncyBreadcrumbLabel}}</span></li>'
        });
        $stateProvider
            .state('appSimple', {
                abstract: false,
                templateUrl: 'views/common/layouts/simple.html?ver="6.3.0"',
                resolve: {
                    loadPlugin: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            serie: true,
                            name: 'Font Awesome',
                            files: ['css/font-awesome.min.css']
                        }, {
                            serie: true,
                            name: 'Simple Line Icons',
                            files: ['css/simple-line-icons.css']
                        }, {
                            serie: true,
                            name: 'Login Styles',
                            files: ['views/pages/login.css']
                        }]);
                    }],
                }
            })
            .state('appSimple.login', {
                url: '/login?:id',
                controller: 'loginCtrl',
                templateUrl: 'views/pages/login.html?ver="6.3.0"'
            })

            .state('appSimple.saml', {
                url: '/saml?:token',
                controller: 'samlCtrl',
                templateUrl: 'views/pages/saml-login.html?ver="6.3.0"',
            })


            .state('appSimple.forgotPassword', {
                url: '/forgot-password',
                controller: 'loginCtrl',
                templateUrl: 'views/user/forgot-password.html?ver="6.3.0"',
            })
            .state('appSimple.invitation-expire', {
                url: '/invitation-expire',
                templateUrl: 'views/user/invitation-expire.html?ver="6.3.0"',
            })
            .state('appSimple.welcome-user', {
                url: '/welcome-user',
                templateUrl: 'views/user/welcome-user.html?ver="6.3.0"',
            })
            .state('appSimple.error-messages', {
                url: '/error-messages',
                templateUrl: 'views/user/error-messages.html?ver="6.3.0"',
            })
            .state('appSimple.logout', {
                url: '/logout',
                controller: 'logoutCtrl',
                templateUrl: 'views/pages/login.html?ver="6.3.0"'
            })

            .state('app', {
                abstract: true,
                templateUrl: 'views/common/layouts/full.html?ver="6.3.0"',
                ncyBreadcrumb: {
                    label: 'Home',
                    skip: true
                },
                controller: 'fullLayoutCtrl',
                resolve: {
                    authenticate: ['AuthService', '$state', '$q', '$timeout', '$rootScope', function (AuthService, $state, $q, $timeout, $rootScope) {
                        var deferred = $q.defer();
                        if (AuthService.login()) {
                            var temp = angular.fromJson(AuthService.getFields());
                            $rootScope.id_user = temp.data.data.id_user;
                            $rootScope.user_name = temp.data.data.first_name +" "+temp.data.data.last_name;
                            $rootScope.first_name = temp.data.data.first_name;
                            $rootScope.last_name = temp.data.data.last_name;
                            $rootScope.email = temp.data.data.email;
                            $rootScope.userPagination = (temp.data.data.display_rec_count)?parseInt(temp.data.data.display_rec_count):10;
                            $rootScope.user_role_id = temp.data.data.user_role_id;
                            $rootScope.user_role_name = temp.data.data.user_role_name;
                            $rootScope.user_type = temp.data.data.user_type;
                            $rootScope.profile_image = temp.data.data.profile_image;
                            $rootScope.profile_image_medium = temp.data.data.profile_image_medium;
                            $rootScope.profile_image_small = temp.data.data.profile_image_small;
                            $rootScope.access = temp.data.data.access;
                            deferred.resolve();
                        } else {
                            $timeout(function() {
                                $state.go('appSimple.login');
                            },0)
                            return $q.reject();
                        }
                        return deferred.promise;
                    }],
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            serie: true,
                            name: 'Font Awesome',
                            files: ['css/font-awesome.min.css']
                        }, {
                            serie: true,
                            name: 'Simple Line Icons',
                            files: ['css/simple-line-icons.css']
                        }, {
                            serie: true,
                            name: 'Icomoon Icons',
                            files: ['css/icomoon-icons.css']
                        }]);
                    }]
                }
            })
            .state('app.404', {
                url: '/404',
                templateUrl: 'views/pages/404.html?ver="6.3.0"',
                ncyBreadcrumb: {
                    label: '404'
                }
            })

            .state('app.403', {
                url: '/403',
                templateUrl: 'views/pages/403.html?ver="6.3.0"',
                ncyBreadcrumb: {
                    label: '403'
                }
            })

            .state('app.notifiation',{
                url: '/notifications/:date',
                templateUrl :'views/user/notifications-details.html?ver="6.3.0"',
                controller: 'notificationCtrl',
                activeLink: 'Notifications',
                ncyBreadcrumb: {
                    parent: 'app.notifiationList',
                    label: 'Notifications Details'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            name: 'Notifications JS',
                            files: ['views/user/notificationController.js?ver="6.3.0"']
                        });
                    }]
                }
            })
            .state('app.notifiationList',{
                url: '/notification/list',
                templateUrl :'views/user/notifications-list.html?ver="6.3.0"',
                controller: 'notificationListCtrl',
                activeLink: 'Notifications',
                ncyBreadcrumb: {
                    // label: 'Notifications'
                    label: '{{"user.breadcrumb.notifications" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            name: 'Notifications JS',
                            files: ['views/user/notificationController.js?ver="6.3.0"']
                        });
                    }]
                }
            })
            .state('app.dashboard', {
                url: '/dashboard',
                // templateUrl:function(para){
                //     console.log("o",para);
                //     if(istranslate){
                //         return 'views/dashboard/user-dashboard.html?ver="6.3.0"';
                //     }
                //     else{
                //         return '"views/pages/403.html?ver="6.3.0"';
                //     }
                // },
                templateUrl: 'views/dashboard/user-dashboard.html?ver="6.3.0"',
                controller: 'dashboardCtrl',
                activeLink: 'dashboard',
                ncyBreadcrumb: {
                    //label: 'Activity Dashboard'
                    label: '{{"user.breadcrumb.activity_dashboard" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    // loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    //     return $ocLazyLoad.load({
                    //         name: 'Dashboard JS',
                    //         files: ['views/dashboard/dashboardController.js?ver="6.3.0"']
                    //     });
                    // },'ng-fusioncharts']
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Dashboard JS',
                                files: ['views/dashboard/dashboardController.js?ver="6.3.0"']
                            },'ng-fusioncharts']);
                    }
                }
            })
            .state('app.dashboard2', {
                url: '/dashboard',
                templateUrl: 'views/dashboard/user-dashboard.html?ver="6.3.0"',
                controller: 'dashboardCtrl',
                activeLink: 'dashboard',
                ncyBreadcrumb: {
                    // label: 'Dashboard'
                    label: '{{"user.breadcrumb.dashboard" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            name: 'Dashboard JS',
                            files: ['views/dashboard/dashboardController.js?ver="6.3.0"']
                        });
                    }]
                }
            })
            .state('app.myProfile', {
                url: '/my-account/my-profile?:id',
                controller: 'profileCtrl',
                activeLink: 'My Profile',
                ncyBreadcrumb: {
                    // label: 'My Profile'
                    label: '{{"user.breadcrumb.myprofile" | translate}}'
                },
                templateUrl: 'views/user/user-profile.html?ver="6.3.0"',
                resolve: {
                    checkPermission: checkPermission,
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load controllers
                        return $ocLazyLoad.load({
                            files: ['js/controllers.js?ver="6.3.0"']
                        });
                    }]
                }
            })
            .state('app.changePassword', {
                url: '/my-account/change-password?:id',
                controller: 'profileCtrl',
                activeLink: 'Change Password',
                ncyBreadcrumb: {
                    // label: 'Change Password'
                    label: '{{"user.breadcrumb.change_password" | translate}}'
                },
                templateUrl: 'views/user/change-password.html?ver="6.3.0"',
                resolve: {
                    checkPermission: checkPermission,
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load controllers
                        return $ocLazyLoad.load({
                            files: ['js/controllers/main.js?ver="6.3.0"']
                        });
                    }]
                }
            })
            .state('app.companySetup',{
                url: '/my-account/company-setup',
                controller: 'companySetupCtrl',
                templateUrl: 'views/user/change-company-details.html?ver="6.3.0"',
                activeLink: 'Company Setup',
                ncyBreadcrumb: {
                    // label: 'Company Setup'
                    label: '{{"user.breadcrumb.company_setup" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load controllers
                        return $ocLazyLoad.load({
                            files: ['js/controllers/main.js?ver="6.3.0"']
                        });
                    }]
                }
            })
            .state('app.contractDeleted',{
                template: '<ui-view></ui-view>',
                controller: 'deletedContractListCtrl',
                activeLink : 'Deleted Contract List',
                ncyBreadcrumb: {
                    // label: 'deleted Contracts',
                    label: '{{"user.breadcrumb.delete_contracts" | translate}}',
                    skip: true
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Contract JS',
                            files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['attachment']);
                    }
                }
            })
            .state('app.contractDeleted.deleted-contracts', {
                url: '/deleted-contracts',
                templateUrl: 'views/Manage-Users/contracts/deleted-contracts.html?ver="6.3.0"',
                activeLink: 'Deleted Contract List',
                ncyBreadcrumb: {
                    // label: 'Deleted Contracts and projects'
                    label: '{{"user.breadcrumb.delete_contracts_projects" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Contract JS',
                            files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['attachment']);
                    }
                }
            })


             /*  Projects route open*/
            .state('app.projects',{
                template:'<ui-view></ui-view>',
                controller:'projectOverviewCtrl',
                activeLink : 'projects',
                ncyBreadcrumb: {
                    // label: 'Projects',
                    label: '{{"user.breadcrumb.projects" | translate}}',
                    skip: true
                },
                resolve: {
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }

            })

            .state('app.projects.all-projects',{
                url: '/all-projects?:this_month?:end_month?:end_date?:end_date_180',
                templateUrl: 'views/Manage-Users/contracts/all-projects-list.html?ver="6.3.0"',
                controller:'allProjectsListCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'All Projects'
                    label: '{{"user.breadcrumb.all_projects" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.create-project',{
                url: '/all-projects/create-project',
                templateUrl: 'views/Manage-Users/contracts/create-edit-project.html?ver="6.3.0"',
                controller:'createProjectCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Create Project',
                    label: '{{"user.breadcrumb.create_project" | translate}}',
                    parent: 'app.projects.all-projects'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.view', {
                url: '/all-projects/view/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/project-details.html?ver="6.3.0"',
                controller:'projectViewCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Project Details',
                    label: '{{"user.breadcrumb.project_details" | translate}}',
                    parent: 'app.projects.all-projects'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.project-task', {
                url: '/all-projects/project-task/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/project-review.html?ver="6.3.0"',
                controller : 'projectReviewCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Task Execution',
                    label: '{{"user.breadcrumb.task_execution" | translate}}',
                    parent: 'app.projects.all-projects'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.project-module-task', {
                url: '/all-projects/project-module-task/:name/:id?:rId/:mName/:moduleId/:tName?:pname/:tId?:qId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/project-module-review.html?ver="6.3.0"',
                controller: 'projectModuleReviewCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Task Module',    
                    label: '{{"user.breadcrumb.task_module" | translate}}',
                    parent: 'app.projects.project-task'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.project-log',{
                url: '/all-projects/project-logs/:name/:id?:type',
                templateUrl: 'views/Manage-Users/contracts/project-log.html?ver="6.3.0"',
                controller : 'projectLogCtrl',
                activeLink: 'Projects',
                
                ncyBreadcrumb: {
                    // label: 'Project Logs',
                    label: '{{"user.breadcrumb.project_logs" | translate}}',
                    parent: 'app.projects.view'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.project-dashboard1', {
                url: '/all-projects/project-dashboard/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/project-dashboard.html?ver="6.3.0"',
                controller: 'projectDashboardCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Task Dashboard',
                    label: '{{"user.breadcrumb.task_dashboard" | translate}}',
                    parent: 'app.projects.project-task'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            /*For  Project Provider breadcrumb disable open*/

            .state('app.projects.project-task11', {
                url: '/all-project/project-task/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/project-review.html?ver="6.3.0"',
                controller : 'projectReviewCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Task Execution',
                    label: '{{"user.breadcrumb.task_execution" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })
            .state('app.projects.project-dashboard11', {
                url: '/all-project/project-dashboard/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/project-dashboard.html?ver="6.3.0"',
                controller: 'projectDashboardCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Task Dashboard',
                    label: '{{"user.breadcrumb.task_dashboard" | translate}}',
                    parent: 'app.projects.project-task11'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })
            /* For Project provider breadcrumb disable close */

            

            .state('app.projects.task-design', {
                url: '/all-projects/project-task-design/:name/:id?:rId?:mId?:tId?:qId?:wId?:type',
                controller:'projectReviewDesign',
                templateUrl: 'views/Manage-Users/contracts/project-discussion.html?ver="6.3.0"',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Task Discussion',
                    label: '{{"user.breadcrumb.task_discussion" | translate}}',
                    parent: 'app.projects.project-task'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.project-task1', {
                url: '/all-project/project-task/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/project-review.html?ver="6.3.0"',
                controller : 'projectReviewCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                //    label: 'Task Execution',
                label: '{{"user.breadcrumb.task_execution" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.project-module-task11', {
                url: '/all-project/project-module-task/:name/:id?:rId/:mName/:moduleId/:tName/:tId?:qId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/project-module-review.html?ver="6.3.0"',
                controller: 'projectModuleReviewCtrl',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Project Module',
                    label: '{{"user.breadcrumb.project_module" | translate}}',
                    parent: 'app.projects.project-task11'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.task-design1', {
                url: '/all-project/project-task-design/:name/:id?:rId?:mId?:tId?:qId?:wId?:type',
                controller:'projectReviewDesign',
                templateUrl: 'views/Manage-Users/contracts/project-discussion.html?ver="6.3.0"',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Task Discussion',
                    label: '{{"user.breadcrumb.task_discussion" | translate}}',
                    parent: 'app.projects.project-task1'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })

            .state('app.projects.task-change-log', {
                url: '/all-projects/project-task-change-log/:name/:id?:rId:wId?:type',
                controller:'projectChangeLogCtrl',
                templateUrl: 'views/Manage-Users/contracts/project-change-log.html?ver="6.3.0"',
                activeLink: 'Projects',
                ncyBreadcrumb: {
                    // label: 'Change Log',
                    label: '{{"user.breadcrumb.change_log" | translate}}',
                    parent: 'app.projects.project-task'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Project JS',
                                files: ['views/Manage-Users/contracts/projectsController.js?ver="6.3.0"']
                            },'attachment']);
                    }            
                }
            })


            .state('app.contract',{
                template: '<ui-view></ui-view>',
                controller: 'contractOverviewCtrl',
                activeLink : 'contracts',
                ncyBreadcrumb: {
                    // label: 'Contracts',
                    label: '{{"user.breadcrumb.contracts" | translate}}',
                    skip: true
                },
                resolve: {
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            /*  Projects route close*/
            .state('app.contract.contract-overview', {
                // url: '/all-activities?:pname?:status?:end_date',
                url: '/all-activities?:pname?:activity_filter?:status?:end_date?:status1',
                templateUrl: 'views/Manage-Users/contracts/all-activities-list.html?ver="6.3.0"',
                controller:'allActivitiesListCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'All Activities'
                    label: '{{"user.breadcrumb.all_activities" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
          
          
            
            .state('app.contract.view1', {
                url: '/all-activities/view/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-details.html?ver="6.3.0"',
                controller:'contractViewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Details',
                    label: '{{"user.breadcrumb.contract_details" | translate}}',
                    parent: 'app.contract.contract-overview'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            //For Refrenece Parvathi
         
            .state('app.contract.all-contracts', {
                // url: '/all-contracts?:pname?:status?:end_date',
                url: '/all-contracts?:pname?:status?:end_date?:this_month?:end_month?:automatic_prolongation',
                templateUrl: 'views/Manage-Users/contracts/all-contracts-list.html?ver="6.3.0"',
                controller:'allContractListCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'All Contracts'
                    label: '{{"user.breadcrumb.all_contracts" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            
            .state('app.contract.view', {
                url: '/all-contracts/view/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-details.html?ver="6.3.0"',
                controller:'contractViewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Details',
                    label: '{{"user.breadcrumb.contract_details" | translate}}',
                    parent: 'app.contract.all-contracts'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.view2', {
                url: '/all-contracts/view/:name/:id?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-details.html?ver="6.3.0"',
                controller:'contractViewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Details',
                    label: '{{"user.breadcrumb.contract_details" | translate}}',
                    parent: 'app.contract.all-contracts'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }

            })
            .state('app.contract.create-contract', {
                url: '/all-contracts/create-contract',
                templateUrl: 'views/Manage-Users/contracts/create-edit-contract.html?ver="6.3.0"',
                controller:'createContractCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Create Contract',
                    label: '{{"user.breadcrumb.create_contract" | translate}}',
                    parent: 'app.contract.all-contracts'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.edit-contract', {
                url: '/all-contracts/edit/:name/:id',
                templateUrl: 'views/Manage-Users/contracts/create-edit-contract.html?ver="6.3.0"',
                controller:'createContractCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Edit Contract',
                    label: '{{"user.breadcrumb.edit_contract" | translate}}',
                    parent: 'app.contract.all-contracts'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.create-sub-contract', {
                url: '/all-contracts/sub-create/:name/:id?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/create-sub-contract.html?ver="6.3.0"',
                controller:'subContractCreateCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Create Sub-Contract',
                    label: '{{"user.breadcrumb.create_sub_contract" | translate}}',
                    parent: 'app.contract.all-contracts'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.review-action-item', {
                url: '/all-activities/review-action-item/:name/:id',
                templateUrl: 'views/Manage-Users/contracts/contract-review-list.html?ver="6.3.0"',
                controller:'contractReviewActionItemCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Review Action Item',
                    label: '{{"user.breadcrumb.review_action_item" | translate}}',
                    parent: 'app.contract.contract-overview'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.contract-review1', {
                url: '/all-contracts/contract-review/:name/:id/:rId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-review.html?ver="6.3.0"',
                controller : 'contractReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Review',
                    label: '{{"user.breadcrumb.contract_review" | translate}}',
                    parent: 'app.contract.all-contracts'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })

            /* for  provider screen contract review condition open*/
            .state('app.contract.contract-review11', {
                url: '/all-contract/contract-review/:name/:id/:rId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-review.html?ver="6.3.0"',
                controller : 'contractReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Review',
                    label: '{{"user.breadcrumb.contract_review" | translate}}'

                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })

            .state('app.contract.contract-workflow11', {
                url: '/all-contract/contract-workflow/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-review.html?ver="6.3.0"',
                controller : 'contractReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                //    label: 'Task Execution',
                label: '{{"user.breadcrumb.task_execution" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })

            /* for module review screen*/
            .state('app.contract.contract-module-review11', {
                url: '/all-contract/contract-module-review/:name/:id/:rId/:mName/:moduleId/:tName/:tId?:qId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-module-review.html?ver="6.3.0"',
                controller: 'contractModuleReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Module',
                    label: '{{"user.breadcrumb.contract_module" | translate}}',
                    parent: 'app.contract.contract-review11'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })

            .state('app.contract.contract-module-workflow11', {
                url: '/all-contract/contract-module-workflow/:name/:id?:rId/:mName/:moduleId/:tName/:tId?:qId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-module-review.html?ver="6.3.0"',
                controller: 'contractModuleReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Module',
                    label: '{{"user.breadcrumb.task_module" | translate}}',
                    parent: 'app.contract.contract-workflow11'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            /* for module review screen close*/
             /* for  provider screen contract review condition close*/
            .state('app.contract.contract-review', {
                url: '/all-activities/contract-review/:name/:id/:rId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-review.html?ver="6.3.0"',
                controller : 'contractReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Review',
                    label: '{{"user.breadcrumb.contract_review" | translate}}',
                    parent: 'app.contract.contract-overview'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.review-change-log', {
                url: '/all-activities/contract-review-change-log/:name/:id/:rId?:type',
                controller:'ReviewChangeLogCtrl',
                templateUrl: 'views/Manage-Users/contracts/contract-review-change-log.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Change Log',
                    label: '{{"user.breadcrumb.change_log" | translate}}',
                    parent: 'app.contract.contract-review'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.review-change-log1', {
                url: '/all-contracts/contract-review-change-log/:name/:id/:rId?:type',
                controller:'ReviewChangeLogCtrl',
                templateUrl: 'views/Manage-Users/contracts/contract-review-change-log.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Change Log',
                    label: '{{"user.breadcrumb.change_log" | translate}}',
                    parent: 'app.contract.contract-review1'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })

            .state('app.contract.review-change-log12345', {
                url: '/all-contract/contract-review-change-log/:name/:id/:rId?:type',
                controller:'ReviewChangeLogCtrl',
                templateUrl: 'views/Manage-Users/contracts/contract-review-change-log.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Change Log',
                    label: '{{"user.breadcrumb.change_log" | translate}}',
                    parent: 'app.contract.contract-review11'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.review-design', {
                url: '/all-activities/contract-review-design/:name/:id/:rId?:mId?:tId?:qId?:type',
                controller:'ReviewDesign',
                templateUrl: 'views/Manage-Users/contracts/contract-review-discussion.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Review Discussion',
                    label: '{{"user.breadcrumb.review_discussion" | translate}}',
                    parent: 'app.contract.contract-review'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.review-design1', {
                url: '/all-contracts/contract-review-design/:name/:id/:rId?:mId?:tId?:qId?:type',
                controller:'ReviewDesign',
                templateUrl: 'views/Manage-Users/contracts/contract-review-discussion.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Review Discussion',
                    label: '{{"user.breadcrumb.review_discussion" | translate}}',
                    parent: 'app.contract.contract-review1'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.contract-module-review', {
                url: '/all-activities/contract-module-review/:name/:id/:rId/:mName/:moduleId/:tName/:tId?:qId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-module-review.html?ver="6.3.0"',
                controller: 'contractModuleReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Module',
                    label: '{{"user.breadcrumb.contract_module" | translate}}',
                    parent: 'app.contract.contract-review'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
                })
            .state('app.contract.contract-module-review1', {
                url: '/all-contracts/contract-module-review/:name/:id/:rId/:mName/:moduleId/:tName/:tId?:qId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-module-review.html?ver="6.3.0"',
                controller: 'contractModuleReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Module',
                    label: '{{"user.breadcrumb.contract_module" | translate}}',
                    parent: 'app.contract.contract-review1'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.contract-dashboard1', {
                url: '/all-contracts/contract-dashboard/:name/:id?:rId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-dashboard.html?ver="6.3.0"',
                controller: 'contractDashboardCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Dashboard',
                    label: '{{"user.breadcrumb.contract_dashboard" | translate}}',
                    parent: 'app.contract.contract-review1'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.contract-dashboard', {
                url: '/all-activities/contract-dashboard/:name/:id?:rId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-dashboard.html?ver="6.3.0"',
                controller: 'contractDashboardCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Dashboard',
                    label: '{{"user.breadcrumb.contract_dashboard" | translate}}',
                    parent: 'app.contract.contract-review'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.review-trends', {
                url: '/all-activities/review-trends/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-trends-view.html?ver="6.3.0"',
                controller: 'contractTrendsCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Trends',
                    label: '{{"user.breadcrumb.contract_trends" | translate}}',
                    parent: 'app.contract.contract-review'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.review-trends1', {
                url: '/all-contracts/review-trends/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-trends-view.html?ver="6.3.0"',
                controller: 'contractTrendsCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Trends',
                    label: '{{"user.breadcrumb.contract_trends" | translate}}',
                    parent: 'app.contract.contract-review1'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })


            .state('app.contract.review-trends11122', {
                url: '/all-contract/review-trends/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-trends-view.html?ver="6.3.0"',
                controller: 'contractTrendsCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Trends',
                    label: '{{"user.breadcrumb.contract_trends" | translate}}',
                    parent: 'app.contract.contract-review11'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            /*.state('app.contract.workflow-trends', {
                url: '/all-activities/workflow-trends/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-trends-view.html?ver="6.3.0"',
                controller: 'contractTrendsCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    label: 'Contract Trends',
                    parent: 'app.contract.contract-workflow'
                }
            })*/
            .state('app.contract.contract-log',{
                url: '/all-activities/contract-logs/:name/:id?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-logs.html?ver="6.3.0"',
                controller : 'contractLogCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Logs',
                    label: '{{"user.breadcrumb.contract_logs" | translate}}',
                    parent: 'app.contract.view1'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.contract-log1',{
                url: '/all-contracts/contract-logs/:name/:id?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-logs.html?ver="6.3.0"',
                controller : 'contractLogCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Logs',
                    label: '{{"user.breadcrumb.contract_logs" | translate}}',
                    parent: 'app.contract.view'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.contract-workflow1', {
                url: '/all-contracts/contract-workflow/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-review.html?ver="6.3.0"',
                controller : 'contractReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Execution',
                    label: '{{"user.breadcrumb.task_execution" | translate}}',
                    parent: 'app.contract.all-contracts'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.contract-workflow', {
                url: '/all-activities/contract-workflow/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-review.html?ver="6.3.0"',
                controller : 'contractReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Execution',
                    label: '{{"user.breadcrumb.task_execution" | translate}}',
                    parent: 'app.contract.contract-overview'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.contract-module-workflow', {
                url: '/all-activities/contract-module-workflow/:name/:id?:rId/:mName/:moduleId/:tName/:tId?:qId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-module-review.html?ver="6.3.0"',
                controller: 'contractModuleReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Module',
                    label: '{{"user.breadcrumb.task_module" | translate}}',
                    parent: 'app.contract.contract-workflow'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.contract-module-workflow1', {
                url: '/all-contracts/contract-module-workflow/:name/:id?:rId/:mName/:moduleId/:tName/:tId?:qId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-module-review.html?ver="6.3.0"',
                controller: 'contractModuleReviewCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Module',
                    label: '{{"user.breadcrumb.task_module" | translate}}',
                    parent: 'app.contract.contract-workflow1'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.workflow-dashboard', {
                url: '/all-activities/workflow-dashboard/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-dashboard.html?ver="6.3.0"',
                controller: 'contractDashboardCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Dashboard',
                    label: '{{"user.breadcrumb.task_dashboard" | translate}}',
                    parent: 'app.contract.contract-workflow'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })

            /* For provider  disable open*/
            .state('app.contract.workflow-dashboard11', {
                url: '/all-activity/workflow-dashboard/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-dashboard.html?ver="6.3.0"',
                controller : 'contractDashboardCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Dashboard',
                    label: '{{"user.breadcrumb.task_dashboard" | translate}}',
                    parent:'app.contract.contract-workflow11'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })

            .state('app.contract.contract-dashboard11', {
                url: '/all-contract/contract-dashboard/:name/:id?:rId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-dashboard.html?ver="6.3.0"',
                controller: 'contractDashboardCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Dashboard',
                    label: '{{"user.breadcrumb.contract_dashboard" | translate}}',
                    parent: 'app.contract.contract-review11'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            /* For Provider disable close*/

            .state('app.contract.workflow-dashboard1', {
                url: '/all-contracts/workflow-dashboard/:name/:id?:rId?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-dashboard.html?ver="6.3.0"',
                controller: 'contractDashboardCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Dashboard',
                    label: '{{"user.breadcrumb.task_dashboard" | translate}}',
                    parent: 'app.contract.contract-workflow1'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.workflow-log1',{
                url: '/all-contracts/contract-workflow-logs/:name/:id?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-logs.html?ver="6.3.0"',
                controller : 'contractLogCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Logs',
                    label: '{{"user.breadcrumb.contract_logs" | translate}}',
                    parent: 'app.contract.view'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.workflow-log',{
                url: '/all-activities/contract-workflow-logs/:name/:id?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-logs.html?ver="6.3.0"',
                controller : 'contractLogCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Contract Logs',
                    label: '{{"user.breadcrumb.contract_logs" | translate}}',
                    parent: 'app.contract.view1'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })
            .state('app.contract.workflow-change-log', {
                url: '/all-activities/contract-workflow-change-log/:name/:id?:rId:wId?:type',
                controller:'ReviewChangeLogCtrl',
                templateUrl: 'views/Manage-Users/contracts/contract-review-change-log.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Change Log',
                    label: '{{"user.breadcrumb.change_log" | translate}}',
                    parent: 'app.contract.contract-workflow'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.workflow-change-log1', {
                url: '/all-contracts/contract-workflow-change-log/:name/:id?:rId:wId?:type',
                controller:'ReviewChangeLogCtrl',
                templateUrl: 'views/Manage-Users/contracts/contract-review-change-log.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Change Log',
                    label: '{{"user.breadcrumb.change_log" | translate}}',
                    parent: 'app.contract.contract-workflow1'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })

            .state('app.contract.workflow-change-log11234', {
                url: '/all-contract/contract-workflow-change-log/:name/:id?:rId:wId?:type',
                controller:'ReviewChangeLogCtrl',
                templateUrl: 'views/Manage-Users/contracts/contract-review-change-log.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Change Log',
                    label: '{{"user.breadcrumb.change_log" | translate}}',
                    parent: 'app.contract.contract-workflow11'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.workflow-design', {
                url: '/all-activities/contract-workflow-design/:name/:id?:rId?:mId?:tId?:qId?:wId?:type',
                controller:'ReviewDesign',
                templateUrl: 'views/Manage-Users/contracts/contract-review-discussion.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Discussion',
                    label: '{{"user.breadcrumb.task_discussion" | translate}}',
                    parent: 'app.contract.contract-workflow'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            .state('app.contract.workflow-design1', {
                url: '/all-contracts/contract-workflow-design/:name/:id?:rId?:mId?:tId?:qId?:wId?:type',
                controller:'ReviewDesign',
                templateUrl: 'views/Manage-Users/contracts/contract-review-discussion.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Discussion',
                    label: '{{"user.breadcrumb.task_discussion" | translate}}',
                    parent: 'app.contract.contract-workflow1'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            //for provider breadcrumb disable open//
            .state('app.contract.workflow-design11233', {
                url: '/all-contract/contract-workflow-design/:name/:id?:rId?:mId?:tId?:qId?:wId?:type',
                controller:'ReviewDesign',
                templateUrl: 'views/Manage-Users/contracts/contract-review-discussion.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Discussion',
                    label: '{{"user.breadcrumb.task_discussion" | translate}}',
                    parent:'app.contract.contract-workflow11'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })

            .state('app.contract.review-design12334', {
                url: '/all-contract/contract-review-design/:name/:id/:rId?:mId?:tId?:qId?:type',
                controller:'ReviewDesign',
                templateUrl: 'views/Manage-Users/contracts/contract-review-discussion.html?ver="6.3.0"',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Review Discussion',
                    label: '{{"user.breadcrumb.review_discussion" | translate}}',
                    parent: 'app.contract.contract-review11'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }    
            })
            //for provider breadcrumb disable close//
            .state('app.contract.workflow-action-item', {
                url: '/all-activities/workflow-action-item/:name/:id?:wId?:type',
                templateUrl: 'views/Manage-Users/contracts/contract-review-list.html?ver="6.3.0"',
                controller:'contractReviewActionItemCtrl',
                activeLink: 'Contracts',
                ncyBreadcrumb: {
                    // label: 'Task Action Item',
                    label: '{{"user.breadcrumb.task_action_item" | translate}}',
                    parent: 'app.contract.contract-overview'
                    },
                    resolve: {
                        checkPermission: checkPermission,
                        loadPlugin: function($ocLazyLoad){
                            return $ocLazyLoad.load([
                                {
                                    name: 'Contract JS',
                                    files: ['views/Manage-Users/contracts/contractsController.js?ver="6.3.0"']
                                },'ng-fusioncharts','attachment']);
                        }
                    }
    
            })
            .state('app.archive', {
                url: '/archive?:pname',
                templateUrl: 'views/Manage-Users/archive/archive-list.html?ver="6.3.0"',
                controller:'archiveListCtrl',
                activeLink : 'Archive',
                ncyBreadcrumb: {
                    // label: 'Activity Archive'
                    label: '{{"user.breadcrumb.activitiy_archive" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Archive JS',
                            files: ['views/Manage-Users/archive/archiveController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.actionItems', {
                url: '/action-items?:id?:cId?:status?:priority?:due',
                templateUrl: 'views/Manage-Users/action-items/action-items-overview.html?ver="6.3.0"',
                controller:'actionItemCtrl',
                activeLink : 'Action Items',
                ncyBreadcrumb: {
                    // label: 'Action Items'
                    label: '{{"user.breadcrumb.action_items" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Contract JS',
                            files: ['views/Manage-Users/action-items/actionItemController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            .state('app.documents',{
                template: '<ui-view></ui-view>',
                controller: 'documentOverviewCtrl',
                activeLink : 'Documents',
                ncyBreadcrumb: {
                    label: 'Documents',
                    skip: true
                },
                resolve: {
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/documents/documentCtrl.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }
            })

            .state('app.documents.all-documents', {
                url: '/document-intelligence',
                templateUrl: 'views/documents/documents-list.html?ver="6.3.0"',
                controller:'documentListCtrl',
                activeLink: 'Documents',
                ncyBreadcrumb: {
                    // label: 'Document Intelligence'
                    label: '{{"user.breadcrumb.document_intelligence" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/documents/documentCtrl.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }
            })

            // url: '/action-items?:id?:cId?:status?:priority?:due',
            
            .state('app.documents.documents-intelligence-template',{
                url:'/document-intelligence/template/:name/:id',
                templateUrl: 'views/documents/document-intelligence-template-list.html?ver="6.3.0"',
                controller:'documentIntelligenceTemplateCtrl',
                activeLink: 'Documents',
                ncyBreadcrumb: {
                    label: 'Manage Templates',
                    parent:'app.documents.all-documents'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS',
                                files: ['views/documents/documentCtrl.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }

            })


            /* For customer admin screens starts */
            .state('app.customer-documents',{
                template: '<ui-view></ui-view>',
                controller: 'documentCustomerOverviewCtrl',
                activeLink : 'Documents',
                ncyBreadcrumb: {
                    label: 'Documents',
                    skip: true
                },
                resolve: {
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS', 
                                files: ['views/Manage-Users/customer-documents/customerdocumentCtrl.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }
            })

            .state('app.customer-documents.all-documents', {
                url: '/customer-document-intelligence',
                templateUrl: 'views/Manage-Users/customer-documents/customer-documents-list.html?ver="6.3.0"',
                controller:'customerdocumentListCtrl',
                activeLink: 'Documents',
                ncyBreadcrumb: {
                    // label: 'Document Intelligence'
                    label: '{{"user.breadcrumb.document_intelligence" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS', 
                                files: ['views/Manage-Users/customer-documents/customerdocumentCtrl.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }
            })

            .state('app.customer-documents.side-by-side',{
                url:'/customer-document-intelligence/side-by-side-pdfs/:name/:id/:documentName?:statusValidate', 
                templateUrl: 'views/Manage-Users/customer-documents/side-by-side-ocr.html?ver="6.3.0"',
                controller:'documentCustomerOverviewCtrl',
                activeLink: 'Documents',
                ncyBreadcrumb: {
                    label: ' ',
                    parent:'app.customer-documents.all-documents'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Contract JS', 
                                files: ['views/Manage-Users/customer-documents/customerdocumentCtrl.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }
            })
           

            .state('app.builder',{
                template: '<ui-view></ui-view>',
                controller: 'builderOverviewCtrl',
                activeLink : 'Contract Builder',
                ncyBreadcrumb: {
                    label: '{{"normal.contract_builder" | translate}}',
                    skip: true
                },
                resolve: {
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Builder JS',
                                files: ['views/contract-builder/builderCtrl.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }
            })

            .state('app.builder.builder-list', {
                url: '/contract-builder',
                templateUrl: 'views/contract-builder/builder-list.html?ver="6.3.0"',
                controller:'builderListCtrl',
                activeLink: 'Contract Builder',
                ncyBreadcrumb: {
                    label: '{{"normal.contract_builder" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Builder JS',
                                files: ['views/contract-builder/builderCtrl.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }

            })


            .state('app.builder.view', {
                url: '/contract-builder/preview/:tname/:lang/:strucutreId',
                templateUrl: 'views/contract-builder/builder-templates-list.html?ver="6.3.0"',
                controller:'builderDetailsCtrl',
                activeLink: 'Contract Builder',
                ncyBreadcrumb: {
                    label: '{{"user.breadcrumb.template_preview" | translate}}',
                    parent: 'app.builder.builder-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Builder JS',
                                files: ['views/contract-builder/builderCtrl.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }

            })

            // .state('app.customer-user.list',{
            //     url: '/users?:buId',
            //     templateUrl: 'views/Manage-Users/customer-users/customer-users-list.html?ver="6.3.0"',
            //     activeLink : 'users',
            //     controller:"UserCtrl",
            //     ncyBreadcrumb: {
            //         // label: 'Internal Users'
            //         label: '{{"user.breadcrumb.internal_user" | translate}}',
            //     },
            //     resolve : {
            //         checkPermission: checkPermission,
            //         loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
            //             // you can lazy load CSS files
            //             return $ocLazyLoad.load([{
            //                 name: 'Manage Customer User JS',
            //                 files: ['views/Manage-Users/customer-users/customerUsersController.js?ver="6.3.0"']
            //             }]);
            //         }]
            //     }
            // })



            /*customer-contract-builder-start*/
            .state('app.customer-builder',{
                template: '<ui-view></ui-view>',
                controller: 'customerBuilderOverviewCtrl',
                activeLink : 'Contract Builder',
                ncyBreadcrumb: {
                    label: '{{"normal.contract_builder" | translate}}',
                    skip: true
                },
                resolve: {
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Builder JS',
                                files: ['views/Manage-Users/customer-contract-builder/customerBuilderCtrl.js?ver="6.3.0"']
                            },'attachment','ckeditor']);
                    }
                }
            })

            .state('app.customer-builder.builder-list', {
                url: '/customer-contract-builder',
                templateUrl: 'views/Manage-Users/customer-contract-builder/contract-builder-list.html?ver="6.3.0"',
                controller:'customerBuilderListCtrl',
                activeLink: 'Contract Builder',
                ncyBreadcrumb: {
                    label: '{{"normal.contract_builder" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Builder JS',
                                files: ['views/Manage-Users/customer-contract-builder/customerBuilderCtrl.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })

            .state('app.customer-builder.template-by-side',{
                url:'/customer-contract-builder/template-by-side/:contract_build_id', 
                templateUrl: 'views/Manage-Users/customer-contract-builder/template-side.html?ver="6.3.0"',
                controller:'customerBuilderOverviewCtrl',
                activeLink: 'Contract Builder',
                ncyBreadcrumb: {
                    label: ' ',
                    parent:'app.customer-builder.builder-list'
                },
                    resolve: {
                        checkPermission: checkPermission,
                        loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                            // you can lazy load CSS files
                            return $ocLazyLoad.load([{
                                name: 'Builder JS',
                                files: ['views/Manage-Users/customer-contract-builder/customerBuilderCtrl.js?ver="6.3.0"']
                            },'attachment']);
                        }],
                        loadPlugin: function ($ocLazyLoad) {
                            return $ocLazyLoad.load(['ui.sortable','ckeditor']);
                        }
                    }
                })

            /*customer-contract-builder-ends*/

            /*For Catalogue screen starts*/

            .state('app.catalogue',{
                template: '<ui-view></ui-view>',
                controller: 'catalogueOverviewCtrl',
                activeLink : 'Catalogue',
                ncyBreadcrumb: {
                    label: '{{"normal.all_catalogue_items" | translate}}',
                    skip: true
                },
                resolve: {
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'catalogue JS',
                                files: ['views/Manage-Users/catalogue/catalogueController.js?ver="6.3.0"']
                            },'attachment']);
                    }
                }
            })


            .state('app.catalogue.catalogue-list', {
                url: '/catalogue-list',
                templateUrl: 'views/Manage-Users/catalogue/catalogue-list.html?ver="6.3.0"',
                controller:'catalogueListCtrl',
                activeLink: 'Catalogue',
                ncyBreadcrumb: {
                    label: '{{"normal.all_catalogue_items" | translate}}'
                },
                resolve: {
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'catalogue JS',
                                files: ['views/Manage-Users/catalogue/catalogueController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })


            .state('app.catalogue.create-catalogue', {
                url: '/catalogue-list/create-catalogue',
                templateUrl: 'views/Manage-Users/catalogue/create-catalogue.html?ver="6.3.0"',
                controller:'createCatalogueCtrl',
                activeLink: 'Catalogues',
                ncyBreadcrumb: {
                    label: '{{"normal.create_catalogue_item" | translate}}',
                    parent: 'app.catalogue.catalogue-list'
                },
                resolve: {
                    loadPlugin: function($ocLazyLoad){
                        return $ocLazyLoad.load([
                            {
                                name: 'Catalogue JS',
                                files: ['views/Manage-Users/catalogue/catalogueController.js?ver="6.3.0"']
                            },'ng-fusioncharts','attachment']);
                    }
                }
            })


            /*For Catalogue screen ends*/

            /* For customer admin screens ends */

            .state('app.customer', {
                template: '<ui-view></ui-view>',
                controller: 'customerCtrl',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Customer',
                    label: '{{"user.breadcrumb.customer" | translate}}',
                    skip: true
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.customer.customer-list', {
                url: '/customers',
                controller: 'customerListCtrl',
                templateUrl: 'views/customers/customer-list.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Customers',
                    label: '{{"user.breadcrumb.customers" | translate}}',
                    parent:'app.customer'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }

            })
            .state('app.customer.edit-customer', {
                url: '/customers/edit/:name/:id',
                controller: 'addCustomerCtrl',
                templateUrl: 'views/customers/create-edit-customer.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Edit Customer',
                    label: '{{"user.breadcrumb.edit_customer" | translate}}',
                    parent:'app.customer.customer-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }

            })
            .state('app.customer.create-customer', {
                url: '/customers/add',
                controller: 'addCustomerCtrl',
                templateUrl: 'views/customers/create-edit-customer.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Create Customer',
                    label: '{{"user.breadcrumb.create_customer" | translate}}',
                    parent:'app.customer.customer-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }

            })
            .state('app.customer.manage-templates', {
                url: '/customers/manage-templates/:name/:id',
                controller: 'ManageTemplatesCtrl',
                templateUrl: 'views/customers/customer-manage-templates.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Manage Template',
                    label: '{{"user.breadcrumb.manage_template" | translate}}',
                    parent:'app.customer.customer-list'
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            .state('app.manage-admin', {
                template: '<ui-view></ui-view>',
                controller: 'customerCtrl',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Admin',
                    label: '{{"user.breadcrumb.admin" | translate}}',
                    parent:'app.customer.customer-list',
                    skip : true
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.manage-admin.admin-list', {
                url: '/customers/admin/list/:name/:id',
                controller: 'customerAdminListCtrl',
                templateUrl: 'views/customers/manage-admin-list.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Admin',
                    label: '{{"user.breadcrumb.admin" | translate}}',
                    parent:'app.customer.customer-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.manage-admin.edit-admin', {
                url: '/customers/admin/edit/:id/:name/:userId',
                controller: 'addAdminCtrl',
                templateUrl: 'views/customers/create-edit-customer-admin.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Edit Admin',
                    label: '{{"user.breadcrumb.edit_admin" | translate}}',
                    parent:'app.manage-admin.admin-list'
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.manage-admin.create-admin', {
                url: '/customers/admin/add/:name/:id',
                controller: 'addAdminCtrl',
                templateUrl: 'views/customers/create-edit-customer-admin.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Create Admin',
                    label: '{{"user.breadcrumb.create_admin" | translate}}',
                    parent:'app.manage-admin.admin-list'
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            .state('app.manage-user', {
                template: '<ui-view></ui-view>',
                controller: 'customerUserCtrl',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'User',
                    label: '{{"user.breadcrumb.user" | translate}}',
                    parent:'app.customer.customer-list',
                    skip : true
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.manage-user.user-list', {
                url: '/customers/user/list/:name/:id',
                controller: 'customerUserListCtrl',
                templateUrl: 'views/customers/manage-user-list.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'User',
                    label: '{{"user.breadcrumb.user" | translate}}',
                    parent:'app.customer.customer-list'
                },
                resolve:{
                    checkPermission: checkPermission,
                }

            })
            .state('app.manage-user.edit-user', {
                url: '/customers/user/edit/:id/:name/:userId',
                controller: 'addUserCtrl',
                templateUrl: 'views/customers/create-edit-customer-user.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Edit User',
                    label: '{{"user.breadcrumb.edit_user" | translate}}',
                    parent:'app.manage-user.user-list'
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.manage-user.create-user', {
                url: '/customers/user/add/:name/:id',
                controller: 'addUserCtrl',
                templateUrl: 'views/customers/create-edit-customer-user.html?ver="6.3.0"',
                activeLink : 'customers',
                ncyBreadcrumb: {
                    // label: 'Create User',
                    label: '{{"user.breadcrumb.create_user" | translate}}',
                    parent:'app.manage-user.user-list'
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer JS',
                            files: ['views/customers/customerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            .state('app.module', {
                template: '<ui-view></ui-view>',
                controller: 'moduleCtrl',
                activeLink : 'modules',
                ncyBreadcrumb: {
                    // label: 'Module',
                    label: '{{"user.breadcrumb.module" | translate}}',
                    skip : true
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Module JS',
                            files: ['views/modules/moduleController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.module.module-list', {
                url: '/modules',
                controller: 'moduleListCtrl',
                templateUrl: 'views/modules/module-list.html?ver="6.3.0"',
                activeLink : 'manage_review',
                ncyBreadcrumb: {
                    // label: 'Manage Review Modules'
                    label: '{{"user.breadcrumb.manage_review_module" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Module JS',
                            files: ['views/modules/moduleController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.module.module-topic-list', {
                url: '/modules/topics/:name/:id',
                controller: 'moduleTopicController',
                templateUrl: 'views/modules/manage-topic.html?ver="6.3.0"',
                activeLink : 'manage_review',
                ncyBreadcrumb: {
                    // label: 'Topics',
                    label: '{{"user.breadcrumb.topics" | translate}}',
                    parent:'app.module.module-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Module JS',
                            files: ['views/modules/moduleController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            /**/
            .state('app.settings', {
                url: '/settings',
                controller: 'settingsCtrl',
                templateUrl: 'views/settings/settings-list.html?ver="6.3.0"',
                activeLink : 'settings',
                ncyBreadcrumb: {
                    // label: 'Settings'
                    label: '{{"user.breadcrumb.settings" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Module JS',
                            files: ['views/settings/settingsCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.relationship_category', {
                template: '<ui-view></ui-view>',
                controller: 'relationCategoryCtrl',
                activeLink : 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Relationship Categories',
                    label: '{{"user.breadcrumb.relationship_catageory" | translate}}',
                    skip: true
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Category relation JS',
                            files: ['views/relation-category/relationCategoryCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.relationship_category.list', {
                url: '/relationship_category',
                controller: 'relationCategoryCtrl',
                templateUrl: 'views/relation-category/relation-category-list.html?ver="6.3.0"',
                activeLink : 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Contract Classification'
                    label: '{{"user.breadcrumb.contract_classification" | translate}}',

                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Category relation JS',
                            files: ['views/relation-category/relationCategoryCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.relationship_category.relationship_classification', {
                url: '/relationship_category/relationship_classification/list',
                controller: 'relationshipClassificationCtrl',
                templateUrl: 'views/relationship_classification/relationship_classification_list.html?ver="6.3.0"',
                activeLink : 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Relationship Classification'
                    label: '{{"user.breadcrumb.relationship_classification" | translate}}'
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Relationship ClassificationJS',
                            files: ['views/relationship_classification/relationshipClassificationCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            /* admin relationship categories open*/
            .state('app.relationship_category.list1', {
                url: '/relationship_category/admin_provider_relationship_category',
                controller: 'relationAdminProviderCategoryCtrl',
                templateUrl: 'views/relation-category/admin-provider-relation-category-list.html?ver="6.3.0"',
                activeLink : 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Relation Categories'
                    label: '{{"user.breadcrumb.relation_catageory" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Category relation JS',
                            files: ['views/relation-category/relationCategoryCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            /* admin relationship categories close*/

            /*admin provider category classification open*/
            .state('app.relationship_category.provider_admin_classification', {
                url: '/relationship_category/admin_provider_relationship_classification/list',
                controller: 'relationshipadminProviderClassificationCtrl',
                templateUrl: 'views/relationship_classification/admin_provider_relationship_classification.html?ver="6.3.0"',
                activeLink : 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Relation Classification'
                    label: '{{"user.breadcrumb.relation_classification" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Relationship ClassificationJS',
                            files: ['views/relationship_classification/relationshipClassificationCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            /*admin provider category classification close*/


            /* Currency Routing open*/
            .state('app.currency', {
                template: '<ui-view></ui-view>',
                controller: 'currenciesCtrl',
                activeLink: 'Company Currency',
                ncyBreadcrumb: {
                    // label: 'Company Currencies',
                    label: '{{"user.breadcrumb.company_currencies" | translate}}',
                    skip: true
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Caompany Currency Js',
                            files: ['views/company-currency/currenciesCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

           

            .state('app.currency.currency-list', {
                url: '/currency',
                templateUrl: 'views/company-currency/currencies-list.html?ver="6.3.0"',
                controller:'currenciesCtrl',
                activeLink: 'Company Currency',
                ncyBreadcrumb: {
                    // label: 'Company Currencies'
                    label: '{{"user.breadcrumb.company_currencies" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Caompany Currency Js',
                            files: ['views/company-currency/currenciesCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            

            /* Currency  Routing close*/
            .state('app.calender',{
                template: '<ui-view></ui-view>',
                controller: 'calenderCtrl',
                ncyBreadcrumb: {
                    // label: 'Calendar',
                    label: '{{"user.breadcrumb.calendar" | translate}}',
                    skip: true
                },
                activeLink : 'Calendar',
                resolve : {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Calender JS',
                            files: ['views/calender/calenderCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.calender.view', {
                url: '/calendar',
                templateUrl: 'views/calender/calender.html?ver="6.3.0"',
                activeLink : 'calendar',
                ncyBreadcrumb: {
                    // label: 'Calendar'
                    label: '{{"user.breadcrumb.calendar" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Calender JS',
                            files: ['views/calender/calenderCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            /*.state('app.calender.year', {
                url: '/calendar/full-calendar/:year',
                controller: 'fullcalenderCtrl',
                templateUrl: 'views/calender/fullCalender.html?ver="6.3.0"',
                activeLink : 'calendar',
                ncyBreadcrumb: {
                    label: 'Year Calendar',
                    parent: 'app.calender.view'
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Calender JS',
                            files: ['views/calender/calenderCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })*/
           /* .state('app.fullcalender', {
                url: '/full-calendar/:year',
                controller: 'fullcalenderCtrl',
                templateUrl: 'views/calender/fullCalender.html?ver="6.3.0"',
                activeLink : 'calendar',
                ncyBreadcrumb: {
                    label: 'Calendar'
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Calender JS',
                            files: ['views/calender/calenderCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })*/
            .state('app.email-templates',{
                template: '<ui-view></ui-view>',
                controller: 'emailTempaltesCtrl',
                ncyBreadcrumb: {
                    // label: 'Email Templates',
                    label: '{{"user.breadcrumb.email_templates" | translate}}',
                    skip: true
                },
                activeLink : 'Email Templates',
                resolve : {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Email Tempaltes JS',
                            files: ['views/email-templates/emailTemplatesCtrl.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ckeditor']);
                    }
                }
            })
            .state('app.email-templates.list',{
                url: '/email-templates',
                controller:"emailTempaltesCtrl",
                templateUrl: 'views/email-templates/email-templates.html?ver="6.3.0"',
                activeLink : 'Email Templates',
                ncyBreadcrumb: {
                    // label: 'Email Templates'
                    label: '{{"user.breadcrumb.email_templates" | translate}}'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Email Tempaltes JS',
                            files: ['views/email-templates/emailTemplatesCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.email-templates.edit',{
                url: '/email-templates/edit/:name/:id',
                controller:"emailTempaltesCtrl",
                templateUrl: 'views/email-templates/edit-email-templates.html?ver="6.3.0"',
                activeLink : 'Email Templates',
                ncyBreadcrumb: {
                    // label: 'Edit Email Template',
                    label: '{{"user.breadcrumb.edit_email_templates" | translate}}',
                    parent: 'app.email-templates.list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Email Tempaltes JS',
                            files: ['views/email-templates/emailTemplatesCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }

            })

            .state('app.questions',{
                template: '<ui-view></ui-view>',
                controller: 'questionsCtrl',
                activeLink : 'manage_review',
                ncyBreadcrumb: {
                    // label: 'Questions',
                    label: '{{"user.breadcrumb.Questions" | translate}}',
                    skip: true
                },
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })
            .state('app.questions.questions-list',{
                url: '/questions',
                controller: 'questionsListCtrl',
                templateUrl: 'views/questions/questions-talbe-list.html?ver="6.3.0"',
                activeLink : 'questions',
                ncyBreadcrumb: {
                    // label: 'Manage Review Questions',
                    label: '{{"user.breadcrumb.manage_review_questions" | translate}}',
                    parent:'app.questions'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Question JS',
                            files: ['views/questions/questionsCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            /*.state('app.questions.topic-questions',{
                url : '/question/topic/:name/:id',
                templateUrl : '',
                controller:'',
                ncyBreadcrumb: {
                    label: 'Topic Questions',
                    parent:'app.questions.questions-list'
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Question JS',
                            files: ['views/questions/questionsCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })*/
            .state('app.questions.questions-view', {
                url: '/questions/view/:mName/:name/:id',
                templateUrl: 'views/questions/questions-view.html?ver="6.3.0"',
                controller:"questionsView",
                activeLink : 'questions',
                ncyBreadcrumb: {
                    // label: 'Topic Questions',
                    label: '{{"user.breadcrumb.topic_questions" | translate}}',
                    parent:'app.questions.questions-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Question JS',
                            files: ['views/questions/questionsCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            .state('app.co-workers', {
                url: '/co-workers',
                templateUrl: 'views/co-workers.html?ver="6.3.0"',
                ncyBreadcrumb: {
                    // label: 'Co Workers'
                    label: '{{"user.breadcrumb.coworkers" | translate}}',
                }
            })
            .state('app.contract-review2', {
                url: '/contract-review2',
                templateUrl: 'views/contract-module-review.html?ver="6.3.0"',
                ncyBreadcrumb: {
                    // label: 'Contract Review2'
                    label: '{{"user.breadcrumb.contract_review2" | translate}}',
                }
            })

            .state('app.templates', {
                template: '<ui-view></ui-view>',
                controller: 'templatesCtrl',
                activeLink : 'templates',
                ncyBreadcrumb: {
                    // label: 'Templates',
                    label: '{{"user.breadcrumb.templates" | translate}}',
                    skip: true
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Templates JS',
                            files: ['views/templates/templateCtrl.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })
            .state('app.templates.templates-list', {
                url: '/templates',
                templateUrl: 'views/templates/templates-list.html?ver="6.3.0"',
                controller:"templatesListCtrl",
                activeLink : 'templates',
                ncyBreadcrumb: {
                    // label: 'Manage Review Templates',
                    label: '{{"user.breadcrumb.manage_review_templates" | translate}}',
                    parent : 'app.templates'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Templates JS',
                            files: ['views/templates/templateCtrl.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })
            .state('app.templates.templates-preview', {
                url: '/templates/preview/:name/:id',
                templateUrl: 'views/templates/templates-preview.html?ver="6.3.0"',
                controller:"templatesView",
                activeLink : 'templates',
                ncyBreadcrumb: {
                    // label: 'Template Preview',
                    label: '{{"user.breadcrumb.template_preview" | translate}}',
                    parent : 'app.templates.templates-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Templates JS',
                            files: ['views/templates/templateCtrl.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })
            .state('app.templates.templates-view', {
                url: '/templates/view/:name/:id',
                templateUrl: 'views/templates/template-tree-view.html?ver="6.3.0"',
                controller:"templatesTreeView",
                activeLink : 'templates',
                ncyBreadcrumb: {
                    // label: 'Manage Template',
                    label: '{{"user.breadcrumb.manage_template" | translate}}',
                    parent : 'app.templates.templates-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Templates JS',
                            files: ['views/templates/templateCtrl.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }

            })
             /*.state('app.templates.templates-view', {
                templateUrl: 'views/templates/tempalte-view.html?ver="6.3.0"',
                controller:"manageTemplateCtrl",
                activeLink : 'templates',
                ncyBreadcrumb: {
                    label: 'Templates',
                    parent : 'app.templates'
                }
            })
            .state('app.templates.templates-view.module', {
                url: '/templates/module/:name/:id',
                templateUrl: 'views/templates/templates-view-module.html?ver="6.3.0"',
                controller:"manageModuleTemplateCtrl",
                activeLink : 'Templates',
                ncyBreadcrumb: {
                    label: 'Modules',
                    parent : 'app.templates.templates-list'
                }
            })
            .state('app.templates.templates-view.topic', {
                url: '/templates/topic/:name/:id',
                templateUrl: 'views/templates/template-topic-view.html?ver="6.3.0"',
                activeLink : 'Templates',
                controller:"manageTopicTemplateCtrl",
                ncyBreadcrumb: {
                    label: 'Topics',
                    parent : 'app.templates.templates-list'
                }
            })
            .state('app.templates.templates-view.questions', {
                url: '/templates/question/:name/:id',
                templateUrl: 'views/templates/template-view-questions.html?ver="6.3.0"',
                controller:"manageQuestionsTemplateCtrl",
                activeLink : 'Templates',
                ncyBreadcrumb: {
                    label : 'Questions',
                    parent: 'app.templates.templates-list'
                }
            })*/

            .state('app.bussiness_unit', {
                template: '<ui-view></ui-view>',
                controller: 'bussinessUnitCtrl',
                activeLink: 'business unit',
                ncyBreadcrumb: {
                    // label: 'Business Unit',
                    label: '{{"user.breadcrumb.buinessunit" | translate}}',
                    skip: true
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Bussiness Unit JS',
                            files: ['views/bussiness_unit/bussinessCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.bussiness_unit.list', {
                url: '/business-unit',
                controller: 'bussinessUnitCtrl',
                templateUrl: 'views/bussiness_unit/bussiness_unit_list.html?ver="6.3.0"',
                activeLink: 'business unit',
                ncyBreadcrumb: {
                    // label: 'Business Unit',
                    label: '{{"user.breadcrumb.buinessunit" | translate}}',
                    parent : 'app.bussiness_unit'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Bussiness Unit JS',
                            files: ['views/bussiness_unit/bussinessCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.bussiness_unit.create', {
                url: '/business-unit/create',
                controller: 'bussinessUnitCreateCtrl',
                templateUrl: 'views/bussiness_unit/create-edit-bussiness-unit.html?ver="6.3.0"',
                activeLink: 'business unit',
                ncyBreadcrumb: {
                    // label: 'Add Business Unit',
                    label: '{{"user.breadcrumb.add_buinessunit" | translate}}',
                    parent : 'app.bussiness_unit.list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Bussiness Unit JS',
                            files: ['views/bussiness_unit/bussinessCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }

            })
            .state('app.bussiness_unit.edit', {
                url: '/business-unit/edit?:id',
                controller: 'bussinessUnitEditCtrl',
                templateUrl: 'views/bussiness_unit/create-edit-bussiness-unit.html?ver="6.3.0"',
                activeLink: 'business unit',
                ncyBreadcrumb: {
                    // label: 'Edit Business Unit',
                    label: '{{"user.breadcrumb.edit_buinessunit" | translate}}',
                    parent : 'app.bussiness_unit.list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Bussiness Unit JS',
                            files: ['views/bussiness_unit/bussinessCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            .state('app.provider', {
                template: '<ui-view></ui-view>',
                controller: 'providerCtrl',
                activeLink: 'business unit',
                ncyBreadcrumb: {
                    // label: 'Providers',
                    label: '{{"user.breadcrumb.providers" | translate}}',
                    skip: true
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Provider JS',
                            files: ['views/bussiness_unit/providerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            /*  Changed and present using*/

            .state('app.provider.all-providers', {
                url: '/provider?:approval_status?:risk_profile',
                templateUrl: 'views/bussiness_unit/provider-list.html?ver="6.3.0"',
                controller:'providerCtrl',
                activeLink: 'Provider',
                ncyBreadcrumb: {
                    // label: 'All Relations'
                    label: '{{"user.breadcrumb.all_relation" | translate}}'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Provider JS',
                            files: ['views/bussiness_unit/providerCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }

            })
            

            .state('app.provider.view', {
                url: '/provider/view/:name/:id',
                templateUrl:'views/bussiness_unit/provider-details.html?ver="6.3.0"',
                controller:'providerViewCtrl',
                activeLink: 'Provider',
                ncyBreadcrumb: {
                    // label: 'Relation Details',
                    label: '{{"user.breadcrumb.relation_details" | translate}}',
                    parent : 'app.provider.all-providers'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['attachment']);
                    }
                }
            })
            
            .state('app.provider.prvcreate',{
                url: '/provider/create',
                controller: 'providerCreateCtrl',
                templateUrl: 'views/bussiness_unit/create-edit-provider.html?ver="6.3.0"',
                activeLink: 'Provider',
                ncyBreadcrumb: {
                    // label: 'Add Relation',
                    label: '{{"user.breadcrumb.add_relation" | translate}}',
                    parent : 'app.provider.all-providers'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['attachment']);
                    }
                }
            })
            .state('app.provider.prvedit', {
                url: '/provider/edit?:id',
                controller: 'providerEditCtrl',
                templateUrl: 'views/bussiness_unit/create-edit-provider.html?ver="6.3.0"',
                activeLink: 'Provider',
                ncyBreadcrumb: {
                    // label: 'Edit Relation',
                    label: '{{"user.breadcrumb.edit_relation" | translate}}',
                    parent : 'app.provider.all-providers'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['attachment']);
                    }
                }

            })
            .state('app.provider.provider-log1',{
                url: '/provider/provider-logs/:name/:id',
                templateUrl: 'views/bussiness_unit/provider-logs.html?ver="6.3.0"',
                controller : 'providerLogCtrl',
                activeLink: 'Provider',
                ncyBreadcrumb: {
                    // label: 'Relation Logs',
                    label: '{{"user.breadcrumb.relation_logs" | translate}}',
                    parent: 'app.provider.view'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['attachment']);
                    }
                }
            })

           
            .state('app.customer-user', {
                template: '<ui-view></ui-view>',
                controller: 'manageUserCtrl',
                activeLink : 'users',
                ncyBreadcrumb: {
                    // label: 'User',
                    label: '{{"user.breadcrumb.user" | translate}}',
                    skip : true
                },
                resolve : {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/customerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            
            .state('app.customer-user.list',{
                url: '/users?:buId',
                templateUrl: 'views/Manage-Users/customer-users/customer-users-list.html?ver="6.3.0"',
                activeLink : 'users',
                controller:"UserCtrl",
                ncyBreadcrumb: {
                    // label: 'Internal Users'
                    label: '{{"user.breadcrumb.internal_user" | translate}}',
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/customerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })           

            .state('app.customer-user.create-customer-user',{
                url : '/users/create/:id',
                templateUrl:'views/Manage-Users/customer-users/create-edit-customer-user.html?ver="6.3.0"',
                controller:'addCustomUserCtrl',
                activeLink : 'users',
                ncyBreadcrumb:{
                    // label: 'Create Internal User',
                    label: '{{"user.breadcrumb.create_internal_user" | translate}}',
                    parent:'app.customer-user.list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/customerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.customer-user.edit-customer-user',{
                url : '/users/edit/:id/:userId',
                templateUrl:'views/Manage-Users/customer-users/create-edit-customer-user.html?ver="6.3.0"',
                controller:'addCustomUserCtrl',
                activeLink : 'users',
                ncyBreadcrumb:{
                    // label: 'Edit Internal User',
                    label: '{{"user.breadcrumb.edit_internal_user" | translate}}',
                    parent:'app.customer-user.list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/customerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.customer-user.user-contract-contributions',{
                url : '/users/contract-contributions/:name/:id',
                templateUrl:'views/Manage-Users/customer-users/customer-user-contributions.html?ver="6.3.0"',
                controller:'CustomUserContributionsCtrl',
                activeLink : 'users',
                ncyBreadcrumb:{
                    // label: 'Contributions',
                    label: '{{"user.breadcrumb.contributions" | translate}}',
                    parent:'app.customer-user.list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/customerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })            
            .state('app.external-user', {
                template: '<ui-view></ui-view>',
                controller: 'manageUserCtrl',
                activeLink : 'users',
                ncyBreadcrumb: {
                    // label: 'User',
                    label: '{{"user.breadcrumb.user" | translate}}',
                    skip : true
                },
                resolve : {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/extcustomerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.external-user.ext-list',{
                url: '/ext-users?:buId',
                templateUrl: 'views/Manage-Users/customer-users/customer-ext-users-list.html?ver="6.3.0"',
                activeLink : 'users',
                controller:"ExternalUserCtrl",
                ncyBreadcrumb: {
                    // label: 'External Users'
                    label: '{{"user.breadcrumb.external_users" | translate}}',
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/extcustomerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.external-user.create-ext-user',{
                url : '/ext-users/create/:id',
                templateUrl:'views/Manage-Users/customer-users/create-edit-ext-user.html?ver="6.3.0"',
                controller:'addExternalUserCtrl',
                activeLink : 'users',
                ncyBreadcrumb:{
                    // label: 'Create External User',
                    label: '{{"user.breadcrumb.create_external_user" | translate}}',
                    parent:'app.external-user.ext-list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/extcustomerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.external-user.edit-ext-user',{
                url : '/ext-users/edit/:id/:userId',
                templateUrl:'views/Manage-Users/customer-users/create-edit-ext-user.html?ver="6.3.0"',
                controller:'addExternalUserCtrl',
                activeLink : 'users',
                ncyBreadcrumb:{
                    // label: 'Edit External User',
                    label: '{{"user.breadcrumb.edit_external_user" | translate}}',
                    parent:'app.external-user.ext-list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/extcustomerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.external-user.user-contributions',{
                url : '/users/contributions/:name/:id?:extUser',
                templateUrl:'views/Manage-Users/customer-users/customer-user-contributions.html?ver="6.3.0"',
                controller:'ExternalUserContributionsCtrl',
                activeLink : 'users',
                ncyBreadcrumb:{
                    // label: 'Contributions',
                    label: '{{"user.breadcrumb.contributions" | translate}}',
                    parent:'app.external-user.ext-list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Manage Customer User JS',
                            files: ['views/Manage-Users/customer-users/extcustomerUsersController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            .state('app.customer-relationship_category', {
                template: '<ui-view></ui-view>',
                controller: 'customerRelationCategoryCtrl',
                activeLink: 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Relationship Categories',
                    label: '{{"user.breadcrumb.relationship_catageory" | translate}}',
                    skip: true
                },
                resolve: {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer Category relation JS',
                            files: ['views/Manage-Users/relationship-category/relationshipCategoryController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.customer-relationship_category.list', {
                url: '/customer_relationship_category',
                controller: 'customerRelationCategoryCtrl',
                templateUrl: 'views/Manage-Users/relationship-category/customer-relation-category-list.html?ver="6.3.0"',
                activeLink: 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Contract Classifications'
                    label: '{{"user.breadcrumb.contract_classification" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer Category relation JS',
                            files: ['views/Manage-Users/relationship-category/relationshipCategoryController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            /* provider categories open*/
            .state('app.customer-relationship_category.list1',{ 
                url:'/customer_relationship_category/providers-categories',
                controller:'providerRelationCategoryCtrl',
                templateUrl:'views/Manage-Users/relationship-category/provider-categories-tab-view.html',
                activeLink: 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Relation Categories'
                    label: '{{"user.breadcrumb.relationship_catageory" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer Category relation JS',
                            files: [' views/Manage-Users/relationship-category/relationshipCategoryController.js?ver="6.3.0"']
                        }]);
                    }]
                }
                
            })

            .state('app.customer-relationship_category.customer-relationship_classification', {
                url: '/customer_relationship_category/relationship_classification/list',
                controller: 'customerRelationshipClassificationCtrl',
                templateUrl: 'views/Manage-Users/relationship-category/relationship_classification/customer-relationship_classification_list.html?ver="6.3.0"',
                activeLink: 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Relationship Classification'
                    label: '{{"user.breadcrumb.relationship_classification" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Customer Relationship Classification JS',
                            files: ['views/Manage-Users/relationship-category/relationship_classification/relationshipClassificationController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            /* provider categories table structure open*/
            .state('app.customer-relationship_category.customer-provider_classification', {
                url: '/customer_relationship_category/provider_relationship_classification/list',
                controller: 'providersRelationshipClassificationCtrl',
                templateUrl: 'views/Manage-Users/relationship-category/relationship_classification/provider-classifiction-list.html?ver="6.3.0"',
                activeLink: 'relationship categories',
                ncyBreadcrumb: {
                    // label: 'Relation Classification'
                    label: '{{"user.breadcrumb.relation_classification" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'providers Relationship Classification JS',
                            files: ['views/Manage-Users/relationship-category/relationship_classification/relationshipClassificationController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            /* provider categories table structure close*/

            .state('app.reports',{
                template: '<ui-view></ui-view>',
                ncyBreadcrumb: {
                    label: 'Reporting',
                    skip : true
                },
                resolve : {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Reporting',
                            files: ['views/reports/reportsController.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })
            .state('app.reports.reporting',{
                url: '/reports',
                templateUrl: 'views/reports/reporting.html?ver="6.3.0"',
                controller:"reportsCtrl",
                activeLink: 'Reports',
                ncyBreadcrumb: {
                    // label: 'Reports'
                    label: '{{"user.breadcrumb.reports" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                }
            })
            .state('app.reports.create-report',{
                url: '/reports/create-report?:bu?:cl?:st?:desc?:con?:id?:name',
                templateUrl: 'views/reports/create-report.html?ver="6.3.0"',
                controller:"createReportsCtrl",
                activeLink: 'Reports',
                ncyBreadcrumb: {
                    // label: 'Create Report',
                    label: '{{"user.breadcrumb.create_report" | translate}}',
                    parent : 'app.reports.reporting'
                },
                resolve: {
                    checkPermission: checkPermission
                }
            })
            .state('app.reports.report-view',{
                url: '/reports/report-view/:bu/:cl/:st?:desc?:con?:id?:old',
                templateUrl: 'views/reports/report-view.html?ver="6.3.0"',
                controller:"generateReportsCtrl",
                activeLink: 'Reports',
                ncyBreadcrumb: {
                    // label: 'View Report',
                    label: '{{"user.breadcrumb.view_report" | translate}}',
                    parent : 'app.reports.reporting'
                },
                resolve: {
                    checkPermission: checkPermission
                }
            })
            .state('app.reports.report-edit',{
                url: '/reports/report-edit?:bu?:cl?:st?:desc?:con?:pro?:id?:old?:name',
                templateUrl: 'views/reports/report-edit.html?ver="6.3.0"',
                controller:"generateReportsCtrl",
                activeLink: 'Reports',
                ncyBreadcrumb: {
                    // label: 'Edit Report',
                    label: '{{"user.breadcrumb.edit_report" | translate}}',
                    parent : 'app.reports.reporting'
                },
                resolve: {
                    checkPermission: checkPermission
                }
            })

            .state('app.admin-logs', {
                template: '<ui-view></ui-view>',
                activeLink : 'Customer Usage',
                ncyBreadcrumb: {
                    // label: 'Customers',
                    label: '{{"user.breadcrumb.customers" | translate}}',
                    skip:true
                },
                resolve : {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Customers',
                            files: ['views/History/admin/historyController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.admin-logs.customers-list', {
                url:'/customer-usage',
                templateUrl: 'views/History/admin/log-customers-list.html?ver="6.3.0"',
                controller:'withAdminHistoryCtrl',
                activeLink: 'Customer Usage',
                ncyBreadcrumb: {
                    // label: 'Customers'
                    label: '{{"user.breadcrumb.customers" | translate}}',
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Customers',
                            files: ['views/History/admin/historyController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.admin-logs.customer-user-list', {
                url:'/customer-usage/users/:cName/:cId',
                templateUrl: 'views/History/admin/log-customer-users-list.html?ver="6.3.0"',
                controller:'withAdminUsersCtrl',
                activeLink: 'Customer Usage',
                ncyBreadcrumb: {
                    // label: 'Users',
                    label: '{{"user.breadcrumb.users" | translate}}',
                    parent : 'app.admin-logs.customers-list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Customers',
                            files: ['views/History/admin/historyController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.admin-logs.customer-user-logs', {
                url:'/customer-usage/user/logs/:cName/:cId/:uName/:id?:from?:to',
                templateUrl: 'views/History/admin/logs-users-list.html?ver="6.3.0"',
                controller:'withAdminUserLogsCtrl',
                activeLink: 'Customer Usage',
                ncyBreadcrumb: {
                    // label: 'Login History',
                    label: '{{"user.breadcrumb.login_history" | translate}}',
                    parent : 'app.admin-logs.customer-user-list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Customers',
                            files: ['views/History/admin/historyController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.admin-logs.customer-user-actions', {
                url:'/customer-usage/user/actions/:cName/:cId/:uName/:id/:from/:to/:token',
                templateUrl: 'views/History/admin/log-customer-user-actions.html?ver="6.3.0"',
                controller:'withAdminUserActionsCtrl',
                activeLink: 'Customer Usage',
                ncyBreadcrumb: {
                    // label: 'Actions',
                    label: '{{"user.breadcrumb.actions" | translate}}',
                    parent : 'app.admin-logs.customer-user-logs'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Customers',
                            files: ['views/History/admin/historyController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })

            .state('app.customerAdmin-logs', {
                template: '<ui-view></ui-view>',
                activeLink : 'User Usage',
                ncyBreadcrumb: {
                    // label: 'Users',
                    label: '{{"user.breadcrumb.users" | translate}}',
                    skip:true
                },
                resolve : {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Users',
                            files: ['views/History/user/userHistoryController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.customerAdmin-logs.list', {
                url:'/user-usage',
                templateUrl: 'views/History/user/logs-users-list.html?ver="6.3.0"',
                controller:'adminHistoryCtrl',
                activeLink: 'User Usage',
                ncyBreadcrumb: {
                    // label: 'Users'
                    label: '{{"user.breadcrumb.users" | translate}}'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Users',
                            files: ['views/History/user/userHistoryController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.customerAdmin-logs.user-logs', {
                url:'/user-usage/logs/:name/:id?:from?:to',
                templateUrl: 'views/History/user/user-log-history.html?ver="6.3.0"',
                controller:'userLogsCtrl',
                activeLink: 'User Usage',
                ncyBreadcrumb: {
                    // label: 'Login History',
                    label: '{{"user.breadcrumb.login_history" | translate}}',
                    parent : 'app.customerAdmin-logs.list'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Users',
                            files: ['views/History/user/userHistoryController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.customerAdmin-logs.actions', {
                url:'/user-usage/actions/:name/:id/:from/:to/:token',
                templateUrl: 'views/History/user/user-actions-log.html?ver="6.3.0"',
                controller:'userActionsCtrl',
                activeLink: 'User Usage',
                ncyBreadcrumb: {
                    // label: 'Actions',
                    label: '{{"user.breadcrumb.actions" | translate}}',
                    parent : 'app.customerAdmin-logs.user-logs'
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Users',
                            files: ['views/History/user/userHistoryController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.tags', {
                url: '/tags',
                templateUrl: 'views/tags/tags-list.html?ver="6.3.0"',
                controller: 'tagsCtrl',
                activeLink: 'tags',
                ncyBreadcrumb: {
                    // label: ' Contract Tags'
                    label: '{{"user.breadcrumb.contract_tags" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            name: 'Tags JS',
                            files: ['views/tags/tagController.js?ver="6.3.0"']
                        });
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })

            .state('app.catalogue-tags', {
                url: '/catalogue-tags',
                templateUrl: 'views/tags/catalogue-tags.html?ver="6.3.0"',
                controller: 'cataloguetagsCtrl',
                activeLink: 'tags',
                ncyBreadcrumb: {
                    // label: ' Relation Tags'
                    label: '{{"normal.catalogue_tags" | translate}}',
                },
                resolve: {
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            name: 'Tags JS',
                            files: ['views/tags/tagController.js?ver="6.3.0"']
                        });
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })


            //for reference

            .state('app.provider-tags', {
                url: '/provider-tags',
                templateUrl: 'views/tags/provider-tags.html?ver="6.3.0"',
                controller: 'providertagsCtrl',
                activeLink: 'tags',
                ncyBreadcrumb: {
                    // label: ' Relation Tags'
                    label: '{{"user.breadcrumb.relation_tags" | translate}}',
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadMyCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load({
                            name: 'Tags JS',
                            files: ['views/tags/tagController.js?ver="6.3.0"']
                        });
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })

            .state('app.contributors', {
                template: '<ui-view></ui-view>',
                activeLink : 'Contributors',
                ncyBreadcrumb: {
                    // label: 'Contributors',
                    label: '{{"user.breadcrumb.contibutors" | translate}}',
                    skip:true
                },
                resolve : {
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Contributors Js',
                            files: ['views/contributors/contributorController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.contributors.list', {
                url:'/contributors?:contribution_type',
                templateUrl: 'views/contributors/contributors-list.html?ver="6.3.0"',
                controller:'contributorsCtrl',
                activeLink: 'Contributors',
                ncyBreadcrumb: {
                    // label: 'Contributors'
                    label: '{{"user.breadcrumb.contibutors" | translate}}',
                },
                resolve : {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        return $ocLazyLoad.load([{
                            name: 'Contributors Js',
                            files: ['views/contributors/contributorController.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.workflows', {
                template: '<ui-view></ui-view>',
                controller: 'workflowCtrl',
                activeLink : 'workflows',
                ncyBreadcrumb: {
                    // label: 'Tasks',
                    label: '{{"user.breadcrumb.tasks" | translate}}',
                    skip: true
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Task JS',
                            files: ['views/workflows/workflowCtrl.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })
            .state('app.workflows.workflows-list', {
                url: '/workflows',
                templateUrl: 'views/workflows/workflow-list.html?ver="6.3.0"',
                controller:"workflowListCtrl",
                activeLink : 'workflows',
                ncyBreadcrumb: {
                    // label: 'Manage Tasks',
                    label: '{{"user.breadcrumb.manage_tasks" | translate}}',
                    parent : 'app.workflows'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Task JS',
                            files: ['views/workflows/workflowCtrl.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })
            .state('app.workflows.workflow-topic-list', {
                url: '/workflows/topics/:name/:id',
                controller: 'workfowTopicController',
                templateUrl: 'views/workflows/workflow-topics.html?ver="6.3.0"',
                activeLink : 'workflows',
                ncyBreadcrumb: {
                    // label: 'Topics',
                    label: '{{"user.breadcrumb.topics" | translate}}',
                    parent:'app.workflows.workflows-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Task JS',
                            files: ['views/workflows/workflowCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.workflow-questions',{
                template: '<ui-view></ui-view>',
                controller: 'workflowQuestionsCtrl',
                activeLink : 'questions',
                ncyBreadcrumb: {
                    // label: 'Manage Task Questions',
                    label: '{{"user.breadcrumb.manage_task_questions" | translate}}',
                    skip: true
                },
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })
            .state('app.workflow-questions.questions-list',{
                url: '/workflow-questions',
                controller: 'workflowQuestionsListCtrl',
                templateUrl: 'views/workflow-questions/questions-talbe-list.html?ver="6.3.0"',
                activeLink : 'questions',
                ncyBreadcrumb: {
                    // label: 'Manage Task Questions',
                    label: '{{"user.breadcrumb.manage_task_questions" | translate}}',
                    parent:'app.workflow-questions'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Question JS',
                            files: ['views/workflow-questions/workflowQuestionsCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.workflow-questions.questions-view', {
                url: '/workflow-questions/view/:mName/:name/:id',
                templateUrl: 'views/workflow-questions/questions-view.html?ver="6.3.0"',
                controller:"workflowQuestionsView",
                activeLink : 'questions',
                ncyBreadcrumb: {
                    // label: 'Task Topic Questions',
                    label: '{{"user.breadcrumb.task_topic_questions" | translate}}',
                    parent:'app.workflow-questions.questions-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Question JS',
                            files: ['views/workflow-questions/workflowQuestionsCtrl.js?ver="6.3.0"']
                        }]);
                    }]
                }
            })
            .state('app.workflows.workflow-preview', {
                url: '/workflows/preview/:name/:id',
                templateUrl: 'views/workflows/workflow-preview.html?ver="6.3.0"',
                controller:"workflowTemplateView",
                activeLink : 'workflows',
                ncyBreadcrumb: {
                    // label: 'Task Preview',
                    label: '{{"user.breadcrumb.task_preview" | translate}}',
                    parent : 'app.workflows.workflows-list'
                },
                resolve: {
                    checkPermission: checkPermission,
                    loadCSS: ['$ocLazyLoad', function ($ocLazyLoad) {
                        // you can lazy load CSS files
                        return $ocLazyLoad.load([{
                            name: 'Task JS',
                            files: ['views/workflows/workflowCtrl.js?ver="6.3.0"']
                        }]);
                    }],
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load(['ui.sortable']);
                    }
                }
            })
            .state('app.workflows.templates-view', {
                url: '/workflows/view/:name/:id',
                templateUrl: 'views/workflows/workflow-template-tree-view.html?ver="6.3.0"',
                controller:"workflowTemplateTreeView",
                activeLink : 'workflows',
                ncyBreadcrumb: {
                    // label: 'Manage Template',
                    label: '{{"user.breadcrumb.manage_template" | translate}}',
                    parent : 'app.workflows.workflows-list'
                }
            });
        function checkPermission($state, AuthService, $q, $rootScope,$location,userService) {
            var deferred = $q.defer();
            $rootScope.permission = [];
          //  userService.signUp().then(function(result){
          //      result = window.atob(result);
           //     result = JSON.parse(result);
           //     if(result.status){
                  //  AES_KEY = result.data.AES_KEY;
                  //  DATA_ENCRYPT = result.data.DATA_ENCRYPT;
                    AuthService.checkUrl($rootScope.currentUrl).then(function (result1) {
                        $rootScope.permission = result1;
                        if ($rootScope.permission) {
                            if($rootScope.permission['list']==true || $rootScope.permission['view']==true){
                                deferred.resolve();
                            }else{
                                $state.go('app.403');
                            }

                            //  if($rootScope.permission['list']==false || $rootScope.permission['view']==false){
                            //     console.log("permission false");
                            //     $state.go('app.403')
                            // }
                            // deferred.resolve();
                        }
                        else{
                            $state.go("app.404");
                            deferred.reject();
                        }
                    });
                    return deferred.promise;
             //   }
            //})
        }
    }]);
