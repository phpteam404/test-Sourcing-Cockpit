angular.module('app',['localytics.directives'])
.controller('documentOverviewCtrl', function ($scope,$rootScope) {
   
})
.controller('documentListCtrl',function($scope,$rootScope,$uibModal,documentService,customerService,userService){
    $scope.customerName = {templateUrl: 'myPopoverCustomer.html'};
    $rootScope.module = '';
    $rootScope.displayName = '';
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
    $scope.status=1;
    $scope.getDocumentByAccess=function(val){
         $scope.resetPagination=true;
         $scope.status = val; 
        if($scope.tableStateRef.status){
             $scope.getTemplateList($scope.tableStateRef);  
         }else{
             delete $scope.tableStateRef.status;
             $scope.getTemplateList($scope.tableStateRef);  
        }
     }

    $scope.getTemplateList = function (tableState){
        setTimeout(function(){
            $scope.templateLoading = true;
            $scope.tableStateRef = tableState;
            var pagination = tableState.pagination;
            tableState.customer_id  = $scope.user1.customer_id;
            if($scope.status==0)tableState.status  = $scope.status;        
            documentService.getTemplateList(tableState).then (function(result){
                 $scope.templateInfo = result.data.data;
                 $scope.templateCount = result.data.total_records;
                $scope.emptyTemplateTable=false;
                $scope.displayCount = $rootScope.userPagination;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                
                $scope.templateLoading = false;
                if(result.data.total_records < 1)
                    $scope.emptyTemplateTable=true;
            })
        },700);
    }

    $scope.defaultPagesTemplate = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getTemplateList($scope.tableStateRef);
            }                
        });
    }

    $scope.createTemplate = function(row){
        var selectedRow= row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'templateForm.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';
                $scope.template ={};
                $scope.template.available_for_all_customers=1;
                $scope.disableField = true;
                $scope.getCustomerVyAccess = function(val){
                    if(val==0)  $scope.disableField = false;
                    else {
                        $scope.disableField = true;
                        $scope.template.customer_id='';
                    }   
                }
                var params={};
                params.status = 1;
                customerService.list(params).then(function(result){
                    $scope.customersList = result.data.data;
                })

                    
                if(row){
                    console.log("row is:",row);
                    $scope.bottom='general.update';
                    documentService.getTemplateList({'id_intelligence_template':row.id_intelligence_template,'status':row.status}).then (function(result){
                        $scope.template=result.data.data[0];
                        console.log($scope.template)
                        if($scope.template.available_for_all_customers==0){
                            $scope.disableField = false;
                        }
                        else{
                            $scope.disableField =true;
                        }

                        $scope.addTemplate =function(template){
                            var params={};
                            params =template;
                            if(template.customer_id)params.customer_id = template.customer_id.toString();
                            documentService.templateUpdate(params).then(function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $scope.getTemplateList($scope.tableStateRef);
                                   $rootScope.toast('Success', result.message);
                                }
                                else{
                                   $rootScope.toast('Error',result.error,'error');
                               }
                            })
                        }
                       
                    })
                }
                $scope.addTemplate =function(template){
                    //console.log(template);
                    var params={};
                    params =template;
                    if(template.customer_id)params.customer_id = template.customer_id.toString();
                    documentService.createTemplate(params).then(function(result){
                        if(result.status){
                            $scope.cancel();
                            $scope.getTemplateList($scope.tableStateRef);
                           $rootScope.toast('Success', result.message);
                        }
                        else{
                           $rootScope.toast('Error',result.error,'error');
                       }
                    })
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
})
.controller('documentIntelligenceTemplateCtrl',function($scope,$filter,$rootScope,$uibModal,$stateParams,$stateParams,documentService,encode,decode,userService){
    $rootScope.module = 'Document Intelligence Template';
    $rootScope.displayName = $stateParams.name;
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
    $scope.getTemplateQuestionList = function (tableState){
        setTimeout(function(){
            $scope.templateLoading = true;
            $scope.tableStateRef = tableState;
            var pagination = tableState.pagination;
            if($scope.filter_field_type && $scope.filter_field_type !=null){
                            tableState.filter_field_type = $scope.filter_field_type;
                        }else {
                            delete tableState.filter_field_type;
                            $scope.filter_field_type=null;
                        }
            tableState.customer_id  = $scope.user1.customer_id;
            tableState.id_intelligence_template = decode($stateParams.id);         
            documentService.templateQuestionsList(tableState).then (function(result){
                // console.log(result);
                $scope.templateQuestionInfo = result.data.data;
                $scope.templateQuestionCount = result.data.total_records;
                $scope.emptyTemplateQuestionTable=false;
                $scope.displayCount = $rootScope.userPagination;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.templateQuestionLoading = false;
                if(result.data.total_records < 1)
                    $scope.emptyTemplateQuestionTable=true;
            })
        },700);
    }

    $scope.defaultPagesTemplate = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getTemplateQuestionList($scope.tableStateRef);
            }                
        });
    }

    // $scope.getTemplateQuestionList = function (tableState){
    //     //setTimeout(function(){
    //         $scope.templateLoading = true;
    //         $scope.tableStateRef = tableState;
    //         var pagination = tableState.pagination;
    //         if($scope.filter_field_type && $scope.filter_field_type !=null){
    //             tableState.filter_field_type = $scope.filter_field_type;
    //         }else {
    //             delete tableState.filter_field_type;
    //             $scope.filter_field_type=null;
    //         }
            
    //         tableState.customer_id  = $scope.user1.customer_id;   
    //         tableState.id_intelligence_template = decode($stateParams.id);    
    //         documentService.templateQuestionsList(tableState).then (function(result){
    //              $scope.templateQuestionInfo = result.data.data;
    //              $scope.templateQuestionCount = result.data.total_records;
    //             $scope.emptyTemplateQuestionTable=false;
    //             $scope.displayCount = $rootScope.userPagination;
    //             tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
    //             $scope.templateQuestionLoading = false;
    //             if(result.data.total_records < 1)
    //                 $scope.emptyTemplateQuestionTable=true;
    //         })
    //     //},700);
    // }

    // $scope.defaultPagesTemplate = function(val){
    //     userService.userPageCount({'display_rec_count':val}).then(function (result){
    //         if(result.status){
    //             $rootScope.userPagination = val;
    //             $scope.getTemplateQuestionList($scope.tableStateRef);
    //         }                
    //     });
    // }
    $scope.documentIntelligenceQuestion = function(info){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'templateQuestionForm.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';
                if(info){
                    documentService.templateQuestionsList({'id_intelligence_template_fields':info.id_intelligence_template_fields}).then (function(result){
                        $scope.templateQuestion = result.data.data[0];
                   })
                   $scope.addTemplateQuestion=function(question){
                    var params={};
                    params =question;
                    documentService.updatetemplateQuestion(params).then(function(result){
                        if(result.status){
                            $scope.cancel();
                            $scope.getTemplateQuestionList($scope.tableStateRef);
                           $rootScope.toast('Success', result.message);
                        }
                        else{
                           $rootScope.toast('Error',result.error,'error');
                       }
                    })
                }
                }
                if(!info){
                $scope.addTemplateQuestion=function(question){
                    var params={};
                    params =question;
                    params.id_intelligence_template = decode($stateParams.id);
                    documentService.createQuestion(params).then(function(result){
                        if(result.status){
                            $scope.cancel();
                            $scope.getTemplateQuestionList($scope.tableStateRef);
                           $rootScope.toast('Success', result.message);
                        }
                        else{
                           $rootScope.toast('Error',result.error,'error');
                       }
                    })
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
    }
  
    $scope.deleteTemplateQuestion = function(id){
        var r = confirm($filter('translate')('general.alert_template_delete'));
        if(r==true){
            var params={};
            params.id_intelligence_template_fields =id.id_intelligence_template_fields;
            documentService.deleteTemplateQuestion(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    $scope.getTemplateQuestionList($scope.tableStateRef);
                }
                else{
                    $rootScope.toast('Error',result.error,'error');
                }
            })
    
        }
    }
})
