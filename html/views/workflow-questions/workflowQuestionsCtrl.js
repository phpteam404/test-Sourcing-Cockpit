angular.module('app')
    .controller('workflowQuestionsCtrl', function ($state, $rootScope, $scope, masterService,$localStorage,$translate) {
        if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }
    })
    .controller('workflowQuestionsListCtrl', function($state, $rootScope, $scope, $stateParams, $uibModal, questionsService, userService){
        $scope.questionsTableList = {};
        $scope.displayCount = $rootScope.userPagination;
        $scope.callServer = function(tableState){
            $rootScope.displayName = '';
            $rootScope.module = '';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.isLoading = true;
            $scope.tableStateRef=tableState;
            var pagination = tableState.pagination;
            tableState.is_workflow=true;
            questionsService.list(tableState).then(function (result){
                $scope.questionsTableList = result.data.data;
                $scope.emptyTable = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if(result.data.total_records < 1)$scope.emptyTable = true;
            });
        }
        $scope.defaultPages = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServer($scope.tableStateRef);
                }                
            });
        }
    })
    .controller('workflowQuestionsView',function($scope, $rootScope, $state, $stateParams, decode, $uibModal, questionsService,$location){
        $rootScope.displayName = $stateParams.mName+" - "+ $stateParams.name;
        $scope.topic_id = decode($stateParams.id);
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $rootScope.module = "Task Questions";
        $scope.question = {};
        $scope.req = {};
        $scope.req.status=0;
        $scope.topicQuestions = {};
        $scope.choices =[];
        $scope.q_options =[
            {label: 0, value: '0'},
            {label: 0.1, value: '0.1'},
            {label: 1, value: '1'},
            {label: 'NA', value: 'NA'},
        ];
        $scope.getQuestionsList = function(){
            questionsService.getTopicQuestions({'id_topic':$scope.topic_id,'is_workflow':true}).then (function(result){
                if(result.status){
                    $scope.topicQuestions = result.data;
                }
            });
        }
        $scope.getQuestionsByStatus = function(val){
            var params={};
            params.id_topic = $scope.topic_id;
            params.status = val;
            params.is_workflow = true;
            questionsService.getTopicQuestions(params).then (function(result){
                if(result.status){
                    $scope.topicQuestions = result.data;
                }
            });
        }
        $scope.updateRelationships = function(item,row){
            var params = {};
            params.id_question = row.id_question;
            params.updated_by = $scope.user.id_user;
            if(item.id_relationship_category_question){

            }
            else{
                item.id_relationship_category_question = '';
            }
            params.id_relationship_category_question = item.id_relationship_category_question;
            params.id_relationship_category = item.id_relationship_category;
            params.status= item.status;
            questionsService.updateRelationship(params).then(function(result){
                if(result.status){
                    $scope.req.status=0;
                    $scope.getQuestionsByStatus(0);
                    $rootScope.toast('Success',result.message);
                }
                else $rootScope.toast('Error',result.error,'error',$scope.topicQuestions);
            });
        }
        $scope.updateProvider = function(row){
            var params = {};
            params.id_question = row.id_question;
            params.updated_by = $scope.user.id_user;
            params.provider_visibility = row.provider_visibility;
            params.is_workflow = true;
            questionsService.updateRelationship(params).then(function(result){
                if(result.status){
                    $scope.req.status=0;
                    $scope.getQuestionsByStatus(0);
                    $rootScope.toast('Success',result.message);
                }
                else {
                    $rootScope.toast('Error',result.error,'error',$scope.topicQuestions);
                    $scope.req.status=0;
                    $scope.getQuestionsByStatus(0);
                }
            });
        }
        $scope.getQuestionsList();
        $scope.loadModal = function(type,row){
            $scope.question = {};
            $scope.question.option_name = [];
            $scope.option_delete = [];
            $scope.option_added = [];
            $scope.categories= {};
            $scope.question_type = type;
            $scope.title ='general.add';
            $scope.bottom ='general.save';
            $scope.action ='general.add';
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/workflow-questions/questions-modal.html',
                resolve : {
                    item : row
                },
                controller: function ($uibModalInstance, $scope , item) {
                    if(item){
                        $scope.title ='general.edit';
                        $scope.bottom ='general.update';
                        $scope.action ='general.update';
                        questionsService.getQuestionInfo({'id_question':item.id_question,'id_topic':$scope.topic_id}).then (function(result){
                            if(result.status){
                                $scope.question = result.data;
                                $scope.question_type = result.data.question_type;
                                $scope.question.provider_visibility=parseInt(result.data.provider_visibility);
                                //$scope.choices = result.data.option_name;
                                for(obj in  result.data.option_names ){
                                    var opt = {"id" : "choice"+obj,'type':'update','id_question_option':result.data.option_names[obj].id_question_option,
                                        'question_option':result.data.option_names[obj].option_name, 'question_value':result.data.option_names[obj].option_value,
                                        'id_question_option_language': result.data.id_question_option_language[obj]};
                                    $scope.choices[obj]=opt;
                                }
                                $scope.options = [];
                                $scope.question.option_name = [];
                                angular.forEach(result.data.option_names,function(i,o){
                                    var obj = {};
                                    obj.option =i.option_name;
                                    obj.value =i.option_value;
                                    obj.id_question_option =i.id_question_option;
                                    obj.id_question_option_language =i.id_question_option_language;
                                    $scope.question.option_name[o] =obj;
                                    var optObj ={};
                                    optObj.id_question_option = result.data.option_names[o].id_question_option;
                                    optObj.id_question_option_language = result.data.option_names[o].id_question_option_language;
                                    optObj.option_name = result.data.option_names[o].option_name;
                                    optObj.option_value = result.data.option_names[o].option_value;
                                    $scope.options[o] = optObj;
                                });
                            }
                        });
                    } else {
                        $scope.question.question_required = 1;
                        $scope.question.provider_visibility=0;
                    }
                    if(type == 'input')$scope.question.option_name[0] = '';
                    if(type == 'dropdown'){
                        questionsService.getQuestionOptions({'question_type':type}).then(function(result){
                            $scope.choices = result.data;
                        });
                    }
                    questionsService.getQuestionOptions({'question_type':type}).then(function(result){
                        if(type == 'dropdown'){$scope.choices = result.data;}
                        if(type == 'radio'){
                            angular.forEach(result.data, function(item,key){
                                var obj = {};
                                obj.option =item.question_option;
                                obj.value =item.question_value;
                                obj.id_question_option =item.id_question_option;
                                obj.id_question_option_language =item.id_question_option_language;
                                $scope.question.option_name[key] = obj ;
                            })
                        }
                        if(type == 'rag'){
                            angular.forEach(result.data, function(item,key){
                                var obj = {};
                                obj.option =item.question_option;
                                obj.value =item.question_value;
                                obj.id_question_option =item.id_question_option;
                                obj.id_question_option_language =item.id_question_option_language;
                                $scope.question.option_name[key] = obj ;
                            })
                        }
                    });
                    $scope.addQuestion =  function (question){
                        var obj1 = {};
                        obj1.action_name = $scope.action;
                        obj1.action_description = $scope.action+'$$workflow-question$$'+question.question_text;
                        obj1.module_type = $state.current.activeLink;
                        obj1.action_url= $location.$$absUrl;
                        //delete question.option_name ;
                        delete question.option_names;
                        question.id_topic = $scope.topic_id;
                        question.question_type = $scope.question_type;
                        question.option_delete = $scope.option_delete;
                        question.categories = $scope.categories;
                        question.categories = $scope.categories;
                        if($scope.question_type == 'dropdown'){
                            delete question.option_name;
                            question.option_name = $scope.choices;
                        }
                        else question.option_name = $scope.question.option_name;
                        // console.log('question',question);
                        if(question.id_question){
                            question.updated_by = $scope.user.id_user;
                            question.is_workflow=true;
                            questionsService.updateQuestion(question).then(function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $rootScope.toast('Success',result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.req.status=0;
                                    $scope.getQuestionsByStatus(0);
                                }else{
                                    if(result.data) 
                                        $scope.question.provider_visibility=parseInt(result.data.provider_visibility);
                                    $rootScope.toast('Error',result.error,'error',$scope.question);
                                }
                            })
                        }else{
                            question.created_by = $scope.user.id_user;
                            question.is_workflow=true;
                            questionsService.postQuestions(question).then (function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $rootScope.toast('Success',result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.req.status=0;
                                    $scope.getQuestionsByStatus(0);
                                }else{
                                    $rootScope.toast('Error',result.error,'error',$scope.question);
                                }
                            })
                        }
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.choices = [{id: 'choice1','type':'new'}];
                    $scope.addNewChoice = function(question,index) {
                        var newItemNo = $scope.choices.length+1;
                        $scope.choices.push({'id':'choice'+newItemNo,'type':'new'});
                    };
                    $scope.removeChoice = function(index,choice) {
                        if(choice.type=='new'){
                            $scope.choices.splice(index,1);
                        }else{
                            choice.type = 'delete';
                            $scope.choices.splice(index,1);
                            angular.forEach($scope.options, function(i,o){
                                if(i.id_question_option == choice.id_question_option){
                                    var obj = {};
                                    obj.id_question_option =$scope.options[o].id_question_option;
                                    obj.id_question_option_language =$scope.options[o].id_question_option_language;
                                    $scope.option_delete.push(obj);
                                }
                            })
                        }
                    };
                    var params = {};
                    if(item)params.question_id = item.id_question;
                    else params.question_id = 0 ;
                    questionsService.questionCategory(params).then(function(result) {
                        if (result.status) {
                            $scope.categories = result.data;
                        }
                    });
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        }
        $scope.enableQuestion = function(row){
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
            obj1.action_description = $scope.action+'$$workflow-question$$'+row.question_text;
            obj1.module_type = $state.current.activeLink;
            obj1.action_url= $location.$$absUrl;
            params.is_workflow=true;
            questionsService.updateQuestionStatus(params).then(function(result){
                if(result.status){
                    $rootScope.confirmNavigationForSubmit(obj1);
                    $rootScope.toast('Success',result.message);
                }
                else
                    $rootScope.toast('Error',result.error);
                $scope.req.status=0;
                $scope.getQuestionsByStatus(0);
            })
        }
        $scope.sortableOptions={
            update: function(e,ui){
                var params={};
                params.data=$scope.topicQuestions;
                params.is_workflow = true;
                questionsService.sortQuestions(params).then(function(result){
                    if(result.status){
                        var obj1 = {};
                        obj1.action_name = 'update';
                        obj1.action_description = 'sort$$questions';
                        obj1.module_type = $state.current.activeLink;
                        obj1.action_url= $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj1);
                    }
                });
            },
            stop: function(e,ui){
            }
        };
    })
