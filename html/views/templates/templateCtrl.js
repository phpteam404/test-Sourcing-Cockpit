angular.module('app')
.controller('templatesCtrl',function($scope,$rootScope,$state,$sce,$localStorage,$translate){
    
    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }
    
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
})
.controller('templatesListCtrl', function($scope, $rootScope,$state, encode,$filter, decode, $uibModal, templateService, userService, $location,dateFilter){
    $scope.templateList = {};
    /*templateService.list().then(function(result){
        console.log('result',result.data);
    });*/
    $scope.displayCount = $rootScope.userPagination;
    $scope.isAdmin = false;
    if($scope.user1.user_role_id==1)$scope.isAdmin = true;
    $scope.dynamicPopover = {templateUrl: 'myPopoverTemplate.html'};
    $scope.callServer = function callServer(tableState) {
        $rootScope.displayName = '';
        $rootScope.module = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.isLoading = true;
        $scope.tableStateRef=tableState;
        var pagination = tableState.pagination;
        tableState.is_workflow=0;
        //var start = pagination.start || 0;     // This is NOT the page number, but the index of item in the list that you want to use to display the table.
        //var number = pagination.number || 10;  // Number of entries showed per page.
        templateService.list(tableState).then(function (result){
            $scope.templateList = result.data.data;
            $scope.data = result.data.data;
            $scope.emptyTable = false;
            $scope.import_access=result.data.import_subscription;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords = result.data.total_records;
            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
            $scope.isLoading = false;
            if(result.data.total_records < 1)$scope.emptyTable = true;
            angular.forEach( $scope.templateList, function(value, key) {
                if(value.import_status==null || value.import_status=='0' || value.import_status==0)
                    value.import_status=0;
                if(value.import_status=='1' || value.import_status==1)
                    value.import_status=1;
            });
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
    $scope.createTemplate = function (row,isImport){
        $scope.forImport=false;
        if(isImport==1)$scope.forImport=true;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/templates/create-edit-template-modal.html',
            resolve:{
              item : row
            },
            controller: function ($uibModalInstance, $scope, item) {
                $scope.template = {};
                $scope.getTemplatesToImport = function(tableState){
                    $scope.tableStateRef1=tableState;
                    var pagination = tableState.pagination;
                    if(isImport==1)tableState.import_status=1;
                    templateService.getImportTemplates(tableState).then(function (result){
                        $scope.templates = result.data.data;
                        //console.log('result.data.data----61', result.data.data);
                        $scope.data = result.data.data;
                        $scope.emptyTable = false;
                        $scope.displayCount = $rootScope.userPagination;
                        $scope.totalRecords1 = result.data.total_records;
                        tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                        $scope.isLoading = false;
                        if(result.data.total_records < 1)$scope.emptyTable = true;
                    });
                }
                $scope.defaultPages1 = function(val){
                    userService.userPageCount({'display_rec_count':val}).then(function (result){
                        if(result.status){
                            $rootScope.userPagination = val;
                            $scope.getTemplatesToImport($scope.tableStateRef1);
                        }                
                    });
                }
                
                if(item){
                    $scope.title = 'general.edit';
                    $scope.bottom = 'general.update';
                    $scope.action = 'general.update';
                    $scope.template = angular.copy(item);
                    //$scope.template = item;
                }else{
                    $scope.title = 'general.add';
                    if(isImport==1)$scope.title = 'general.import';
                    $scope.bottom = 'general.save';
                    $scope.action = 'general.add';
                }
                $scope.importTemplate = function(data){
                    // $scope.template.template_name=data.template_name;
                    // $scope.template.templateId=data.id_template;   
                    var params={};
                    params.template_id = data.id_template;
                    var date = dateFilter(new Date(),'yyyy-MM-dd');
                    var time = new Date().getHours()+':'+new Date().getMinutes()+':'+new Date().getSeconds();
                    params.new_template_name = data.template_name +'_' + date+" "+time;
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
                $scope.saveTemplate = function (template){
                    var obj = {};
                    obj.action_name = $scope.action;
                    obj.action_description = $scope.action+'$$template$$-'+template.template_name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    if(template.template_id != undefined) template.template_id = template.template_id.id_template;
                    if(typeof template.id_template!='undefined' && ((isNaN(template.id_template)===false && template.id_template > 0) || (isNaN(template.id_template)===true && template.id_template.length > 0))){
                        templateService.update(template).then(function(result){
                            if(result.status){
                                $scope.cancel();
                                $scope.callServer($scope.tableStateRef);
                                $rootScope.confirmNavigationForSubmit(obj);
                                $rootScope.toast('Success',result.message);
                            }else{
                                $rootScope.toast('Error',result.message);
                            }
                        });
                    }
                    else{
                        if($scope.forImport){
                            var params={};
                            params.template_id = template.templateId;
                            params.new_template_name = template.template_name;
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
                        }else{
                            templateService.add(template).then(function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $scope.callServer($scope.tableStateRef);
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $rootScope.toast('Success',result.message);
                                }else{
                                    $rootScope.toast('Error',result.message);
                                }
                            });
                        }
                    }
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
    $scope.updateTemplate = function(row){
        // delete row.template_name;
        templateService.update(row).then(function(result){
            if(result.status){
                var obj = {};
                obj.action_name = 'Update';
                obj.action_description = 'Update $$template$$-'+row.template_name;
                obj.module_type = $state.current.activeLink;
                obj.action_url= $location.$$absUrl;
                $scope.callServer($scope.tableStateRef);
                $rootScope.confirmNavigationForSubmit(obj);
                $rootScope.toast('Success',result.message);
            }else{
                $rootScope.toast('Error',result.message?result.message:result.error);
            }
        });
    }
    $scope.previewTemplate = function(row) {
        $state.go('app.templates.templates-preview',{name:row.template_name,id:encode(row.id_template)});
    }
    //parvathi code starts
    $scope.preview =function(row){
        $scope.selectedRow = row;
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
.controller('templatesView',function($scope,$rootScope,$state,$uibModal,$filter, $stateParams,templateService, decode){
    $rootScope.displayName =$stateParams.name;
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
    $rootScope.module = 'Template';
    var params={};
    params.template_id = decode($stateParams.id);
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

.controller('manageTemplateCtrl', function($scope, $rootScope,$filter,decode,templateService, $location){
    //$rootScope.template_id = decode($stateParams.id);
    //$rootScope.template_name = $stateParams.name;

    var arr= [];
    arr = $location.$$path.split('/');
    arr.reverse();
    $rootScope.template_id = decode(arr[0]);
    $scope.getCounts = function () {
        templateService.getCounts({'template_id':$rootScope.template_id}).then(function(result){
            $scope.counts = result.data;
        });
    }
    $scope.getCounts();
})
.controller('manageModuleTemplateCtrl', function($scope, $rootScope, $filter,$state, $stateParams, encode, decode, $uibModal, templateService, $location){
        $rootScope.template_id = decode($stateParams.id);
        $rootScope.template_name = $stateParams.name;
        $scope.moduleListTable = function(tableState){
            $rootScope.displayName =$stateParams.name;
            $rootScope.module = 'Template';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.isLoading = true;
            $scope.tableStateRef=tableState;
            //var pagination = tableState.pagination;
            var params = {};
            params.template_id = $rootScope.template_id;
            if($scope.tableStateRef.search){
                params.search = $scope.tableStateRef.search;
            }
            templateService.moduleList(params).then(function (result){
                $scope.moduleList = result.data.data;
                $scope.emptyTable = false;
                //tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / tableState.pagination.number);
                $scope.isLoading = false;
                if(result.data.total_records < 1)$scope.emptyTable = true;
            });
        }
        $scope.addModuleModal = function(id){
            templateService.getAllModules({'template_id':id}).then(function(result){
                $scope.allModules = result.data;
            });
            $scope.title = "general.add";
            $scope.bottom = "general.save";
            $scope.action = "general.add";
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
                        /*angular.forEach(modules, function(i,o){
                            if(i==1){
                                $scope.addedModulesList.push(o);
                            }
                        });*/
                        for(var a in modules){
                            if(modules[a]==1)
                                $scope.addedModulesList.push(a);

                        }
                        params.module_id= $scope.addedModulesList;
                        params.template_id = id;
                        templateService.addModule(params).then(function(result){
                            if(result.status){
                                $scope.cancel();
                                $scope.moduleListTable($scope.tableStateRef);
                                $scope.getCounts();
                                $rootScope.toast('Success',result.message);
                                var obj = {};
                                obj.action_name = $scope.action;
                                obj.action_description = $scope.action+'$$template modules$$-'+$stateParams.name;
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
        $scope.deleteTemplateModule =  function (row){
            templateService.deleteModule({'id_template_module':row.id_template_module}).then(function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    $scope.moduleListTable($scope.tableStateRef);
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$template module$$-'+row.module_name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.getCounts();
                }
                else $rootScope.toast('Error',reulst.error,'error',$scope.modules);
            });
        }
        $scope.sortableOptions={
        update: function(e,ui){
            var params = {};
            params.data = $scope.moduleList;
            templateService.sortModules(params).then(function(result){
                if(result.status){
                    var obj = {};
                    obj.action_name = 'update';
                    obj.action_description = 'sort$$template module-$$'+$stateParams.name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                }
            })
        },
        stop: function(e,ui){
        }
    };
    })
.controller('manageTopicTemplateCtrl', function($scope, $rootScope,$filter, $state, $stateParams, encode, decode, $uibModal, templateService, $location){
        $rootScope.template_id = decode($stateParams.id);
        $rootScope.template_name = $stateParams.name;
        $rootScope.displayName =$stateParams.name;
        $rootScope.module = 'Template';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.topicModules = {};
        $scope.topic = {};
        $scope.topic.template_module_id = 'all';
        templateService.getModule({'template_id':$rootScope.template_id}).then(function (result) {
            $scope.topicModules = result.data;
            var result = [];
            result.push({'id_template_module': 'all', 'module_name': 'All'});
            for(var a in $scope.topicModules){
                result.push($scope.topicModules[a]);
            }
            $scope.topicModules = result;
        });
        $scope.topicListTable =  function (params) {
            params.template_id = $rootScope.template_id;
            templateService.topicList(params).then(function (result){
                $scope.topicList = result.data.data;
                $scope.emptyTable = false;
                //tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / tableState.pagination.number);
                $scope.isLoading = false;
                if(result.data.total_records < 1)$scope.emptyTable = true;
            });
        };
        $scope.topicListTable($scope.topic);

        $scope.loadTopicModal= function(id){
            var param = {};
            param.template_module_id = id;
            param.template_id = $rootScope.template_id;
            templateService.getAllTopics(param).then(function(result){
                $scope.allTopics = result.data;
            });
            $scope.title = "general.add";
            $scope.bottom = "general.save";
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
                        /*angular.forEach(topics, function(i,o){
                            if(i){
                                console.log('o',o);
                                $scope.addedTopicsList.push(o);
                            }
                        });*/
                        for(var a in topics){
                            if(topics[a]==1){
                                $scope.addedTopicsList.push(a);
                                console.log(a);
                                console.log($scope.addedTopicsList);
                            }
                               

                        }
                        console.log($scope.addedTopicsList);
                        params.topic_id= $scope.addedTopicsList;
                        params.template_module_id= id;
                        console.log(params);
                        console.log("------------------------");
                        templateService.addTopic(params).then(function(result){
                            if(result.status){
                                $scope.cancel();
                                $scope.topicListTable($scope.topic);
                                $scope.getCounts();
                                $rootScope.toast('Success',result.message);
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
        $scope.deleteTemplateTopic =  function (row){
            templateService.deleteTopic({'id_template_module_topic':row.id_template_module_topic}).then(function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    $scope.topicListTable($scope.topic);
                    $scope.getCounts();
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$template topic-$$'+row.topic_name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url= $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                }
                else $rootScope.toast('Error',reulst.error,'error',$scope.modules);
            });
        }
        $scope.sortableOptions={
        update: function(e,ui){
            var params = {};
            params.data = $scope.topicList;
            templateService.sortTopics(params).then(function(result){
                if(result.status) {
                    var obj = {};
                    obj.action_name = 'update';
                    obj.action_description = 'sort$$template topics-$$' + $stateParams.name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                }
            })
        },
        stop: function(e,ui){
        }
    };
    })
.controller('manageQuestionsTemplateCtrl', function($scope, $rootScope,$filter, $state, $stateParams, encode, decode, $uibModal, templateService, $location){
        $rootScope.template_id = decode($stateParams.id);
        $rootScope.template_name = $stateParams.name;
        $rootScope.displayName =$stateParams.name;
        $rootScope.module = 'Template';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.questionTopicsList = {};
        $scope.question = {};
        $scope.question.template_module_topic_id = 'all';
        templateService.getTopic({'template_id':$rootScope.template_id}).then(function(result){
           $scope.questionTopicsList = result.data;
            var result = [];
            result.push({'id_template_module_topic': 'all', 'topic_name': 'All'});
            for(var a in $scope.questionTopicsList){
                result.push($scope.questionTopicsList[a]);
            }
            $scope.questionTopicsList = result;
        });
        $scope.questionsListTable =  function(params){
            params.template_id = $rootScope.template_id;
            /*console.log(params);*/
            templateService.questionList(params).then(function(result){
                $scope.questionsList = result.data.data;
                $scope.emptyTable = false;
                //tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / tableState.pagination.number);
                $scope.isLoading = false;
                if(result.data.total_records < 1)$scope.emptyTable = true;
            })
        }
        $scope.questionsListTable($scope.question);
        $scope.addQuestionModal = function(id){
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
                        /*angular.forEach(questions, function(i,o){
                            if(i==1){
                                $scope.addedQuestionsList.push(o);
                            }
                        });*/
                        for(var a in questions){
                            if(questions[a]==1)
                                $scope.addedQuestionsList.push(a);

                        }
                        params.question_id= $scope.addedQuestionsList;
                        params.template_module_topic_id= id;
                        templateService.postQuestion(params).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success',result.message);
                                var obj = {};
                                obj.action_name = 'add';
                                obj.action_description = 'add$$template questions-$$' + $stateParams.name;
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.cancel();
                                $scope.questionsListTable($scope.question);
                                $scope.getCounts();
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
        $scope.deleteQuestion = function(row){
            templateService.deleteQuestion({'id_template_module_topic_question':row.id_template_module_topic_question}).then(function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    $scope.questionsListTable($scope.question);
                    $scope.getCounts();
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
        $scope.sortableOptions={
        update: function(e,ui){
            var params = {};
            params.data = $scope.questionsList;
            templateService.sortQuestions(params).then(function(result){
                if(result.status) {
                    var obj = {};
                    obj.action_name = 'update';
                    obj.action_description = 'sort$$template questions-$$' + $stateParams.name;
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                }
            })
        },
        stop: function(e,ui){
        }
    };
    })
.controller('templatesTreeView',function($scope,$rootScope,$state,$uibModal,$filter, $stateParams,$location,templateService, topicService, questionsService, decode){
    $scope.moduleStatus = [];
    var arr= [];
    arr = $location.$$path.split('/');
    arr.reverse();
    $rootScope.template_id = decode(arr[0]);
    $rootScope.displayName =$stateParams.name;
    $rootScope.module = 'Template';
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
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
    /*$scope.loadAndShow = function(moduleIndx, questionIndx){
        var modIndex = 0;
        for(var mod in $scope.showData){
            if ($scope.showData[mod]['open']){
                // call topic
                $scope.showData[$scope.showData[mod]['data'].module_id]['open'] = false;
                $scope.topicList('', modIndex, $scope.showData[mod]['data'], questionIndx);
                for(var top in $scope.showData[mod]['topics']){
                    if ($scope.showData[mod]['topics'][top]['open'] && $scope.showData[mod]['topics'][top]['questions']){
                    }
                }
            }
            modIndex++;
        }
    }
    $scope.topicList = function($event='', indx, module, questionIndx=''){
        if($event != ''){
            $event.stopPropagation();
            $event.preventDefault();
        }       
    }
    $scope.questionList = function($event='', moduleIndex, indx, topic){
        if($event != ''){
            $event.stopPropagation();
            $event.preventDefault();
        }
        if (!$scope.showData[topic['template_module_id']]['topic'][topic.topic_id]['open']) {
            var params = {'template_id': $rootScope.template_id, 'template_module_topic_id': topic.id_template_module_topic};
            templateService.questionList(params).then(function(result){
                if(result.status){
                    if(!$scope.templateModules[moduleIndex]['topics'][indx]['questions']){
                        $scope.templateModules[moduleIndex]['topics'][indx]['questions'] = [];
                    }
                    $scope.templateModules[moduleIndex]['topics'][indx]['questions'] = result.data.data;
                    $scope.showData[topic['template_module_id']]['topic'][topic.topic_id]['open'] = true;
                }
            });
        } else {
            $scope.showData[topic['template_module_id']]['topic'][topic.topic_id]['open'] = false;
            $scope.templateModules[moduleIndex]['topics'][indx]['questions'] = [];
        }
    }*/
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
    /*$scope.enableQuestion = function(row){
        var params = {};
        params.created_by = $scope.user.id_user;
        params.id_question = row.id_question;
        if(row.question_status == 1){
            $scope.action = 'disable';
            params.question_status = 0;
        }
        if(row.question_status == 0){
            $scope.action = 'enable';
            params.question_status = 1;
        }
        var obj1 = {};
        obj1.action_name = 'update';
        obj1.action_description = $scope.action+'$$question$$'+row.question_text;
        obj1.module_type = $state.current.activeLink;
        obj1.action_url= $location.$$absUrl;
        questionsService.updateQuestionStatus(params).then(function(result){
            if(result.status){
                $rootScope.confirmNavigationForSubmit(obj1);
                $rootScope.toast('Success',result.message);
            }
            else
                $rootScope.toast('Error',result.error);
            $scope.getAllData();
        })
    }*/
    $scope.open = function(g){
        if(g.open)
          g.open = false;
        else
          g.open = true;
        return g.open;
    }
})