angular.module('app')
    .controller('workflowCtrl', function ($state, $rootScope, $scope, $localStorage) {
        $scope.dynamicPopover = {
            content: '',
            templateUrl: 'myPopoverTemplate10.html',
            title: 'Title'
        };
        $scope.placement = {
            options: [
                'top'
            ],
            selected: 'top'
        };
    })
    .controller('workflowListCtrl',function($scope, $rootScope, $state, $filter,$localStorage,templateService,$uibModal, encode, dateFilter, userService, moduleService, $location){
        $scope.workflowList = {};
        $scope.req = {};
        $scope.req.status = 0;
        $scope.displayCount = $rootScope.userPagination;
        $scope.callServer = function callServer(tableState){
            $rootScope.module = 'Task';
            $rootScope.displayName = '';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.tableStateRef=tableState;
            $rootScope.bredCrumbLabel = '';
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.is_workflow=true;
            moduleService.list(tableState).then(function (result){
                $scope.workflowList = result.data.data;
                $scope.data = result.data.data;
                $scope.import_access=result.data.import_subscription;
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
        $scope.updateWorkflow = function (row)
        {
            $scope.selectedRow = row;
            $scope.module.module_name = '';
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'workflow-modal.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.module = {};
                    $scope.title = 'general.create';
                    $scope.title = 'general.create';
                    $scope.bottom = 'general.save';
                    $scope.action = 'general.add';
                    $scope.isEdit = false;                   
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
                        obj.action_description = $scope.action+'$$workflow-$$'+module.module_name;
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        if(typeof module.id_module!='undefined' && ((isNaN(module.id_module)===false && module.id_module > 0) || (isNaN(module.id_module)===true && module.id_module.length > 0))){
                            params = module ;
                            params.updated_by = $scope.user.id_user;
                            params.static = parseInt(module.static);
                            params.is_workflow=true;
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
                            params.is_workflow=true;
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
        $scope.importWorkflows = function (row)
        {
            $scope.selectedRow = row;
            $scope.module.module_name = '';
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'import-workflow-modal.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.module = {};
                    $scope.title = 'general.create';
                    $scope.title = 'general.import';
                    $scope.bottom = 'general.save';
                    $scope.action = 'general.add';
                    $scope.isEdit = false;                   
                    
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.workflow = {};
                    $scope.getTemplatesToImport = function(tableState){
                        $scope.tableStateRef1=tableState;
                        var pagination = tableState.pagination;
                        tableState.import_status=1;
                        tableState.is_workflow=true;
                        templateService.getImportTemplates(tableState).then(function (result){
                            $scope.workflows = result.data.data;
                            $scope.data = result.data.data;
                            $scope.emptyTable = false;
                            $scope.displayCount = $rootScope.userPagination;
                            $scope.totalRecords2 = result.data.total_records;
                            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                            $scope.isLoading = false;
                            if(result.data.total_records < 1)$scope.emptyTable = true;
                        });
                    }
                    $scope.defaultPages2 = function(val){
                        userService.userPageCount({'display_rec_count':val}).then(function (result){
                            if(result.status){
                                $rootScope.userPagination = val;
                                $scope.getTemplatesToImport($scope.tableStateRef1);
                            }                
                        });
                    }
                    $scope.importWorkflow = function(data){ 
                        var params={};
                        params.template_id = data.id_template;
                        var date = dateFilter(new Date(),'yyyy-MM-dd');
                        var time = new Date().getHours()+':'+new Date().getMinutes()+':'+new Date().getSeconds();
                        params.new_template_name = data.template_name +'_' + date+" "+time;
                        params.is_workflow=true;
                        templateService.linkCustomerTemplate(params).then(function(result){
                            if(result.status){
                                $scope.cancel();
                                $scope.callServer($scope.tableStateRef);
                                $rootScope.confirmNavigationForSubmit(obj);
                                $rootScope.toast('Success',result.message);
                            }else{
                                $rootScope.toast('Error',result.error);
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
        $scope.manageTopics = function(row)
        {
            var module_name = row.module_name;
            var module_id = encode(row.id_module);
            $state.go('app.workflows.workflow-topic-list',{name:module_name,id:module_id});
        }
        $scope.manageWorkflow = function(row)
        {
            var module_name = row.module_name;
            var module_id = encode(row.to_avail_template);
            $state.go('app.workflows.templates-view',{name:module_name,id:module_id});
        }
        $scope.previewWorkflow = function(row)
        {
            var module_name = row.module_name;
            var module_id = encode(row.to_avail_template);
            $state.go('app.workflows.workflow-preview',{name:module_name,id:module_id});
        }
        $scope.updateModuleStatic= function(row,flag){
            var params={};
            params=row;
            params.updated_by = $scope.user.id_user;
            if(flag)params.static = parseInt(row.static);
            else params.import_status = parseInt(row.import_status);
            params.is_workflow=true;
            moduleService.update(params).then(function (result) {
                if (result.status) {
                    $rootScope.toast('Success', result.message);
                    var obj = {};
                    obj.action_name = 'Update';
                    obj.action_description = 'Update $$workflow-$$ '+row.module_name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.callServer($scope.tableStateRef);
                } else {
                    $rootScope.toast('Error', result.error,'error');
                }
            });
        }
        //parvathi code starts
        $scope.preview =function(row){
            $scope.selectedRow =row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                size:'lg',
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/workflows/template-workflow-preview-modal.html',
                controller: function ($uibModalInstance, $scope,item) {
                    $scope.template_name=item.template_name;
                    $scope.cancel = function () {
                        $uibModalInstance.close();  
                    };
                    var params={};
                    params.template_id=item.id_template;
                    params.is_workflow=1;
                    templateService.previewTemplate(params).then(function (result){
                        if(result.status){
                            $scope.templateModules = result.data.modules;
                        }
                    })
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
        $scope.open = function(g){
            if(g.open)
              g.open = false;
            else
              g.open = true;
            return g.open;
        }
        //parvathi code ends
    })
    .controller('workfowTopicController',function($scope, $rootScope, $state,$filter, $localStorage,$uibModal, $stateParams, encode, decode, topicService, userService, $location){
        var name = $stateParams.name;
        $rootScope.module = 'Task Topics';
        $rootScope.displayName =  $stateParams.name;
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
            tableState.is_workflow=true;
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
                templateUrl: 'create-workflow-topics.html',
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
                        obj.action_description = $scope.action+'$$workflow topic-$$'+topic.topic_name;
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        if(typeof topic.id_topic!='undefined' && ((isNaN(topic.id_topic)===false && topic.id_topic > 0) || (isNaN(topic.id_topic)===true && topic.id_topic.length > 0))){
                            params = topic ;
                            params.id_module = $scope.moduleId ;
                            params.is_workflow=true;
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
                            params.is_workflow=true;
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
            $state.go('app.workflow-questions.questions-view',{'mName':$stateParams.name,'name':row.topic_name,'id':encode(row.id_topic)});
        }
    }) 
    .controller('workflowTemplateView',function($scope,$rootScope,$state,$uibModal,$filter, $stateParams,templateService, decode){
        $rootScope.displayName =$stateParams.name;
        $rootScope.module = 'Task';
        var params={};
        params.template_id = decode($stateParams.id);
        params.is_workflow = 1;
        $scope.templateModules={};
        templateService.previewTemplate(params).then(function(result){
            if(result.status){
                $scope.templateModules = result.data.modules;
            }
        });
        $scope.showTemplateTopicQuestions = function(data,topic) {
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'modal-open questions-modal',
                templateUrl: 'view-template-topic-questions.html',
                size: 'lg',
                controller: function ($uibModalInstance, $scope) {
                    $scope.load = true;
                    $scope.title = topic.topic_name;
                    $scope.questions = topic.questions;
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        }
        $scope.open = function(g){
            if(g.open)
              g.open = false;
            else
              g.open = true;
            return g.open;
        }
    })
    .controller('workflowTemplateTreeView',function($scope,$rootScope,$state,$uibModal, $filter,$stateParams,$location,templateService, topicService, questionsService, decode){
        $scope.moduleStatus = [];
        var arr= [];
        arr = $location.$$path.split('/');
        arr.reverse();
        $rootScope.template_id = decode(arr[0]);
        $rootScope.displayName =$stateParams.name;
        $rootScope.module = 'Task';
        $scope.showData={};
        var params={};
        $scope.isAction=false;
        params.template_id = $rootScope.template_id;
        $scope.getCompleteModuleData = function(params) {
            templateService.getModulesData(params).then(function(result){
                if(result.status){
                    $scope.templateModules = result.data;                         
                    $scope.unAssignedModules = result.module_details;                         
                }
            });
        }
        $scope.getCompleteModuleData(params);       
        $scope.addModuleModal = function(id,type){
            templateService.getAllModules({'template_id':id}).then(function(result){            
                $scope.modulesListToAdd = result.data;
            });
            $scope.title = "general.add";
            $scope.bottom = "general.save";
            $scope.action = "general.add";
            $scope.info = true;
            if(type=='view'){
                $scope.info = false;
            }
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'templates-module-modal.html',
                controller: function ($uibModalInstance, $scope, $rootScope) {
                    $scope.modules = [];
                    $scope.addedModulesList = [];
                    $scope.addModules = function (modules){
                        var params = {};
                        for(var a in modules){
                            if(modules[a]==1)
                                $scope.addedModulesList.push(a);
                        }
                        params.module_id= $scope.addedModulesList;
                        params.template_id = id;
                        templateService.addModule(params).then(function(result){
                            if(result.status){
                                $scope.getCompleteModuleData({'template_id':$rootScope.template_id});
                                $scope.cancel();
                                $rootScope.toast('Success',result.message);
                                 var obj = {};
                                obj.action_name = $scope.action;
                                obj.action_description = $scope.action+' $$ template module$$- '+$stateParams.name;
                                obj.module_type = $state.current.activeLink;
                                obj.action_url= $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj); 
                            }else{
                                $rootScope.toast('Error',result.error,'error',$scope.modules);
                            }
                        });
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        }
        $scope.deleteTemplateModule =  function ($event, row){
            $event.stopPropagation();
            $event.preventDefault();
            var r=confirm($filter('translate')('general.alert_Delete_module'));
            if(r==true){
                templateService.deleteModule({'id_template_module':row.id_template_module}).then(function(result){
                    if(result.status){
                        $rootScope.toast('Success',result.message);
                        $scope.getCompleteModuleData({'template_id':$rootScope.template_id});  
                       // $scope.getAllData();
                    }
                    else $rootScope.toast('Error',reulst.error,'error',$scope.modules);
                });   
            }
        }
        $scope.loadTopicModal= function($event, id, index,type){
            $event.stopPropagation();
            $event.preventDefault();
            var param = {};
            param.template_module_id = id;
            param.template_id = $rootScope.template_id;
            templateService.getAllTopics(param).then(function(result){
                $scope.allTopics = result.data;
            });
            $scope.title = "general.add";
            $scope.bottom = "general.save";
            $scope.topicInfo = true;
            if(type=='view'){
                $scope.topicInfo = false;
            }
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'templates-topic-modal.html',
                controller: function ($uibModalInstance, $scope, $rootScope) {
                    $scope.topics = [];
                    $scope.addedTopicsList = [];
                    $scope.addTopics = function (topics){
                        var params = {};
                        for(var a in topics){
                            if(topics[a]==1){
                                $scope.addedTopicsList.push(a);
                            }
                               
                        }
                        params.topic_id= $scope.addedTopicsList;
                        params.template_module_id= id;
                        templateService.addTopic(params).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success',result.message);
                                $scope.getCompleteModuleData({'template_id':$rootScope.template_id});                            
                                $scope.cancel();
                                var obj = {};
                                obj.action_name = 'add';
                                obj.action_description = 'add$$template topics-$$'+$stateParams.name;
                                obj.module_type = $state.current.activeLink;
                                obj.action_url= $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj); 
                            }else{
                                $rootScope.toast('Error',result.error,'error',$scope.modules);
                            }
                        });
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        }
        $scope.deleteTemplateTopic =  function ($event, row, index){
            $event.stopPropagation();
            $event.preventDefault();
            var r=confirm($filter('translate')('general.alert_delete_topic'));
            if(r==true){
                templateService.deleteTopic({'id_template_module_topic':row.id_template_module_topic}).then(function(result){
                    if(result.status){
                        $rootScope.toast('Success',result.message);       
                        $scope.getCompleteModuleData({'template_id':$rootScope.template_id});            
                        var obj = {};
                        obj.action_name = 'delete';
                        obj.action_description = 'delete$$template topic-$$'+$stateParams.name;
                        obj.module_type = $state.current.activeLink;
                        obj.action_url= $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                    }
                    else $rootScope.toast('Error',reulst.error,'error',$scope.modules);
                });
            }
        }
        $scope.addQuestionModal = function($event, id, modIndex, topicIndex,type){
            $event.stopPropagation();
            $event.preventDefault();
            var param = {};
            param.template_module_topic_id = id;
            param.template_id = $rootScope.template_id;
            templateService.getAllQuestions(param).then(function(result){
                if(result.status)
                    $scope.allQuestions = result.data;
                else $rootScope.toast('Error',result.error,'error',$scope.user);
            });
            $scope.title = "general.add";
            $scope.bottom = "general.save";
            $scope.questionInfo = true;
            if(type=='view'){
                $scope.questionInfo = false;
            }
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'templates-question-modal.html',
                controller: function ($uibModalInstance, $scope, $rootScope) {
                    $scope.questions = [];
                    $scope.addedQuestionsList = [];
                    $scope.addQuestions = function (questions){
                        var params = {};
                        for(var a in questions){
                            if(questions[a]==1)
                                $scope.addedQuestionsList.push(a);
                        }
                        params.question_id= $scope.addedQuestionsList;
                        params.template_module_topic_id= id;
                        templateService.postQuestion(params).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success',result.message);
                                $scope.getCompleteModuleData({'template_id':$rootScope.template_id});
                                $scope.cancel();
                                var obj = {};
                                obj.action_name = 'add';
                                obj.action_description = 'add$$template questions-$$' + $stateParams.name;
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                            }else{
                                $rootScope.toast('Error',result.error,'error',$scope.modules);
                            }
                        });
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
    
        }
        $scope.deleteQuestion = function($event, row, modIndex, topicIndx, index){
            $event.stopPropagation();
            $event.preventDefault();
            var r=confirm($filter('translate')('general.alert_Delete_question'));
            if(r==true){
                templateService.deleteQuestion({'id_template_module_topic_question':row.id_template_module_topic_question}).then(function(result){
                    if(result.status){
                        $rootScope.toast('Success',result.message);
                        $scope.getCompleteModuleData({'template_id':$rootScope.template_id});                    
                        var obj = {};
                        obj.action_name = 'delete';
                        obj.action_description = 'delete$$template question-$$' + row.question_text;
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                    }
                    else $rootScope.toast('Error',result.error,'error',$scope.modules);
                });
            }
        }
        $scope.sortableOptions1={
            handle: '> .handle',
            start: function(e,ui){
              //  console.log('start', e,ui);            
            },
            update: function(e,ui){
                //console.log('update', e,ui);
                //console.log($scope.templateModules[ ui.item.index()]['opened']);
                var params = {};
                params.data = $scope.templateModules;
                templateService.sortModules(params).then(function(result){
                    if(result.status){
                        $rootScope.toast('Success',result.message);
                      // $scope.getCompleteModuleData({'template_id':$rootScope.template_id});
                    }
                })
            },
            stop: function(e,ui){
                //console.log('stop', e);         
               // console.log('module **--', $scope.templateModules[ ui.item.index()]['opened']);
               // $scope.templateModules[ ui.item.index()]['opened']=$scope.templateModules[ ui.item.index()]['opened']?true:false;
            },
            axis: 'y',
            cursor: 'move',
            forceHelperSize: true,
            forcePlaceholderSize: true,
        };
        $scope.sortableOptions2={
            handle: '> .topic',
            start: function(e,ui){
               // console.log('start', e,ui);
            },
            update: function(e,ui){
                // console.log('id',ui);
                var id = ui.item.context.attributes['data-array-id'].value;            
                params.data = $scope.templateModules[id]['topics'];
                templateService.sortTopics(params).then(function(result){
                    if(result.status) {
                        $rootScope.toast('Success',result.message);
                     //   $scope.getCompleteModuleData({'template_id':$rootScope.template_id});
                    }
                })
            },
            stop: function(e,ui){
             //   console.log('stop', e,ui);
            },
            axis: 'y',
            cursor: 'move',
            forceHelperSize: true,
            forcePlaceholderSize: true,
        };
        $scope.sortableOptions3={
            handle: '> .question',
            start: function(e,ui){
               // console.log('start', e,ui);
            },
            update: function(e,ui){
                var $list=ui.item.parent();
                var params = {};
                var id = ui.item.context.attributes['data-array-id'].value;
                var parentId = ui.item.context.attributes['data-parent-id'].value;
                params.data = $scope.templateModules[parentId]['topics'][id]['questions'];
                templateService.sortQuestions(params).then(function(result){
                    if(result.status) {
                        $rootScope.toast('Success',result.message);
                      //  $scope.getCompleteModuleData({'template_id':$rootScope.template_id});
                    }
                })
            },
            stop: function(e,ui){
               // console.log('stop', e,ui);
            },
            axis: 'y',
            cursor: 'move',
            forceHelperSize: true,
            forcePlaceholderSize: true,
        };
        
        $scope.open = function(g){
            if(g.open)
              g.open = false;
            else
              g.open = true;
            return g.open;
        }
    })
    