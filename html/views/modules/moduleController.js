angular.module('app')
    .controller('moduleCtrl', function ($state, $rootScope, $scope, $localStorage) {})
    .controller('moduleListCtrl',function($scope, $rootScope, $state, $localStorage,templateService,$uibModal, userService, encode, moduleService, $location){
        $scope.moduleList = {};
        $scope.req = {};
        $scope.req.status = 0;
        $scope.displayCount = $rootScope.userPagination;
        $scope.callServer = function callServer(tableState)
        {
            $rootScope.module = 'Module';
            $rootScope.displayName = '';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.tableStateRef=tableState;
            $rootScope.bredCrumbLabel = '';
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            moduleService.list(tableState).then(function (result){
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
        $scope.getModulesByStatus = function (val){
            $scope.tableStateRef.status=val;
            $scope.tableStateRef.pagination.start='0';
            $scope.tableStateRef.pagination.number='10';
            $scope.callServer($scope.tableStateRef);
        }
        $scope.updateModule = function (row)
        {
            $scope.selectedRow = row;
            $scope.module.module_name = '';
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/modules/create-edit-module.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.module = {};
                    $scope.title = 'general.create';
                    $scope.title = 'general.create';
                    $scope.bottom = 'general.save';
                    $scope.action = 'general.add';
                    $scope.isEdit = false;
                    $scope.getTemplates = function(){
                        templateService.list().then(function(result){
                            $scope.templates = result.data.data;
                         });
                    }
                    if (item) {
                        $scope.submitStatus = true;
                        $scope.module = angular.copy(item);
                        $scope.update = true;
                        $scope.title = 'general.update';
                        $scope.isEdit = true;
                        $scope.title = 'general.edit';
                        $scope.bottom = 'general.update';
                        $scope.action = 'general.update';
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    var params ={};
                    $scope.save=function(module){
                        var obj = {};
                        obj.action_name = $scope.action;
                        obj.action_description = $scope.action+'$$module-$$'+module.module_name;
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        if(typeof module.id_module!='undefined' && ((isNaN(module.id_module)===false && module.id_module > 0) || (isNaN(module.id_module)===true && module.id_module.length > 0))){
                            params = module ;
                            params.updated_by = $scope.user.id_user;
                            params.static = parseInt(module.static);
                            moduleService.update(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.req.status = 0;
                                    $scope.tableStateRef.status=0;
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error,'error');
                                }
                            });
                        }else{
                            params = module ;
                            params.created_by = $scope.user.id_user;
                            params.static = 0;
                            if(params.template_id == undefined) params.template_id="";
                            moduleService.add(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.req.status = 0;
                                    $scope.tableStateRef.status=0;
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

        $scope.manageTopics = function(row)
        {
            var module_name = row.module_name;
            var module_id = encode(row.id_module);
            $state.go('app.module.module-topic-list',{name:module_name,id:module_id});
        }
        $scope.updateModuleStatic= function(row){
            var params={};
            params=row;
            params.updated_by = $scope.user.id_user;
            params.static = parseInt(row.static);
            moduleService.update(params).then(function (result) {
                if (result.status) {
                    $rootScope.toast('Success', result.message);
                    var obj = {};
                    obj.action_name = 'Update';
                    obj.action_description = 'Update $$module-$$ '+row.module_name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.callServer($scope.tableStateRef);
                } else {
                    $rootScope.toast('Error', result.error,'error');
                }
            });
        }
    })
    .controller('moduleTopicController',function($scope, $rootScope, $state, $localStorage,$uibModal, $stateParams, encode, decode, topicService, userService, $location){
        var name = $stateParams.name;
        $rootScope.module = 'Module';
        $rootScope.displayName =  $stateParams.name;
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.moduleId = decode($stateParams.id);
        $scope.req={};
        $scope.req.status=0;
        $scope.displayCount = $rootScope.userPagination;
        $scope.callServer = function callServer(tableState)
        {
            $rootScope.bredCrumbLabel = '';
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.module_id = $scope.moduleId;
            $scope.tableStateRef=tableState;
            topicService.list(tableState).then(function (result){
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
        $scope.topicTypes=[];
        topicService.getTopicTypes().then(function(result){
            if(result.status){
                $scope.topicTypes = result.data.data;
            }
        });
        $scope.getTopicsByStatus = function(val){
            $scope.tableStateRef.status=val;
            $scope.tableStateRef.pagination.start='0';
            $scope.tableStateRef.pagination.number='10';
            $scope.callServer($scope.tableStateRef);
        }
        $scope.updateTopic = function (row) {
            $scope.selectedRow = row;
            $scope.moduleId = $scope.moduleId;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/modules/create-edit-topic.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.title = 'general.create';
                    $scope.bottom = 'general.save';
                    $scope.action = 'general.add';
                    $scope.isEdit = false;
                    if (item) {
                        $scope.isEdit = true;
                        $scope.submitStatus = true;
                        $scope.topic = angular.copy(item);
                        $scope.topic.topic_type = item.type;
                        $scope.update = true;
                        $scope.title = 'general.edit';
                        $scope.bottom = 'general.update';
                        $scope.action = 'general.update';
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    var params ={};
                    $scope.save=function(topic){
                        var obj = {};
                        obj.action_name = $scope.action;
                        obj.action_description = $scope.action+'$$topic-$$'+topic.topic_name;
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        if(typeof topic.id_topic!='undefined' && ((isNaN(topic.id_topic)===false && topic.id_topic > 0) || (isNaN(topic.id_topic)===true && topic.id_topic.length > 0))){
                            params = topic ;
                            params.id_module = $scope.moduleId ;
                            params.updated_by = $scope.user.id_user;topicService.update(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.tableStateRef.status=0;
                                    $scope.req.status=0;
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error,'error');
                                }
                            });
                        }else{
                            params = topic ;
                            params.id_module = $scope.moduleId ;
                            params.created_by = $scope.user.id_user;
                            topicService.add(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.tableStateRef.status=0;
                                    $scope.req.status=0;
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
        $scope.gotoQuestions = function (row) {
            $state.go('app.questions.questions-view',{'mName':$stateParams.name,'name':row.topic_name,'id':encode(row.id_topic)});
        }
    })