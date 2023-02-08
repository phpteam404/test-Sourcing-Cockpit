angular.module('app',['smart-table','localytics.directives'])
.controller('actionItemCtrl' , function($scope,$sce, $rootScope,$state,$filter, $localStorage,$translate,$stateParams,dateFilter,$timeout, contractService, userService, actionItemsService, encode, decode, 
    $uibModal, $location,projectService,providerService){
    $rootScope.module = '';
    $rootScope.displayName = '';
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
    $scope.ActionItemsList = [];
    $scope.tableStateRef = {};
    $scope.filters = {};
    $scope.providersList = [];
    $scope.emptyTable = false;
    $scope.resetPagination=false;
    $scope.loadFiltersTable=false;
    $scope.displayCount = $rootScope.userPagination;

    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }


    $scope.advancedFilterActionItem = function(){
        $scope.filterCreate = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/advancedFilterContract.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';

                $scope.filterListActionItem=function(){
                var params ={};
                // params.user_id=$scope.user.id_user;
                params.module='action_items';
                $scope.filterLoading=true;
                contractService.getContractList(params).then(function(result){
                    $scope.filterList=result.data;
                    angular.forEach($scope.filterList,function(obj){
                        obj.value_names_string = $sce.trustAsHtml(obj.value_names_string);
                    });

                    $scope.filterLoading=false;

                    $scope.filterContracts=false;
                    if($scope.filterList.length<1){
                        $scope.filterContracts=true;
                    }
                    });
                }
                $scope.filterListActionItem();


            $scope.flterDelete=function(rowdata){
                var r = confirm($filter('translate')('general.alert_Delete_filter'));
                if(r==true){
                    contractService.deleteContractFlter({'id_master_filter':rowdata.id_master_filter}).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success', result.message);
                            $scope.filterActionitemList();
                            $scope.filterListActionItem();
                            $scope.tableStateRef.pagination.start =0;
                            $scope.tableStateRef.pagination.totalItemCount =10;
                            $scope.tableStateRef.pagination.number =10;
                            $scope.callServer($scope.tableStateRef);
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
        $scope.filterCreate.result.then(function ($data) {
        }, function () {
        });
    }


    $scope.createFilter = function(row){
        if($scope.filterCreate){
            $scope.filterCreate.close();
          }
        var selectedRow= row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/action-items/create-action-filter.html',
            controller: function ($uibModalInstance,$scope,item) {
            $scope.bottom ='general.save';
             $scope.title='controller.add_filter_criteria'

                contractService.getContractDomain({'domain_module': 'action_items'}).then(function(result){
                    $scope.contractFilter = result.data;                   
                    });
                    
            
                $scope.getContractDomainFieldList=function(id){
                    $scope.idDomain=id; 
                    var domainId= $scope.contractFilter.filter(item => { return item.id_master_domain == id; });
                    $scope.domainType= domainId[0].domain;
                    contractService.getContractField({'id_master_domain': $scope.idDomain}).then(function(result){
                        $scope.contractField = result.data;
                    });
                }
                $scope.getContractCondition=function(domainFieldId){
                    $scope.filterCreate.value='';
                    var domainFieldData = $scope.contractField.filter(item => { return item.id_master_domain_fields == domainFieldId; });
                    $scope.fieldType=domainFieldData[0].field_type;
                    $scope.feldName=domainFieldData[0].field_name;

                    if($scope.feldName=='Relation'){
                    var params={};
                    params.customer_id = $scope.user1.customer_id;
                    // params.id_user  = $scope.user1.id_user;
                    // params.user_role_id  = $scope.user1.user_role_id;
                    params.status  = 1;
                    providerService.list(params).then(function(result){
                    $scope.providerList = result.data.data;
                    });
                }
                if($scope.feldName=='Responsible user'){
                contractService.getResponsibleUserFilter().then(function(result){
                    $scope.userList = result.data;
                });
            }

                    
                }
                $scope.disable=false;

                if(row){
                    $scope.bottom='general.update';
                    $scope.title='controller.edit_filter_criteria';
                    $scope.disable=true;
                    $scope.edittag=1;
                    $scope.master_filter=row.id_master_filter;
                    var params ={};
                    // params.user_id=$scope.user.id_user;
                    params.module='action_items';
                    params.id_master_filter=row.id_master_filter;
                    contractService.getContractList(params).then(function(result){
                        $scope.filterCreate=result.data[0];
                        $scope.fieldType=result.data[0].field_type;
                        $scope.feldName=result.data[0].field;
                        $scope.domainType= result.data[0].domain;
                        if($scope.filterCreate.field_type=='numeric_text' || $scope.filterCreate.field_type=='free_text' || $scope.filterCreate.field_type=='drop_down' ||  $scope.filterCreate.field_type=='date'){  
                            contractService.getContractField({'id_master_domain': row.master_domain_id}).then(function(result){
                                    $scope.contractField = result.data;
                                });
                        }
                        if($scope.filterCreate.field_type=='date'){
                            if($scope.filterCreate.value){
                                $scope.filterCreate.value = moment($scope.filterCreate.value).utcOffset(0, false).toDate();
                            }
                        $scope.options = {
                            minDate: moment().utcOffset(0, false).toDate(),
                            showWeeks: false
                            };
                        }
                
                    if($scope.feldName=='Relation'){
                        var params={};
                        params.customer_id = $scope.user1.customer_id;

                        // params.id_user  = $scope.user1.id_user;
                        // params.user_role_id  = $scope.user1.user_role_id;
                        params.status  = 1;
                        providerService.list(params).then(function(result){
                        $scope.providerList = result.data.data;
                        });
                    }

                    if($scope.feldName=='Responsible user'){
                        contractService.getResponsibleUserFilter().then(function(result){
                            $scope.userList = result.data;
                        });
                    }
                });


                    $scope.addContractFilter=function(fields){
                        var para = angular.copy(fields);
                        // para.user_id=$scope.user.id_user;
                        para.field=$scope.feldName;
                        // para.field_type=$scope.fieldType;
                        if(para.value!=null && $scope.fieldType=='date'){
                            para.value = dateFilter(fields.value,'yyyy-MM-dd');
                        }
                        else if(para.value!=null && $scope.fieldType=='drop_down'){
                            para.value = fields.value.toString();
                        }
                        else{
                            para.value=fields.value;
                        }
    
                        contractService.filterCreate(para).then(function(result){
                            if(result.status){
                                $scope.cancel();
                                $scope.filterActionitemList();
                                $scope.tableStateRef.pagination.start =0;
                                $scope.tableStateRef.pagination.totalItemCount =10;
                                $scope.tableStateRef.pagination.number =10;
                                $scope.callServer($scope.tableStateRef);
                                $rootScope.toast('Success', result.message);
                            }else{
                                $rootScope.toast('Error',result.error);
                            }
    
                        })
                    }
                
                }


                if(!row){
                $scope.addContractFilter=function(fields){
                    var params={};
                    params.master_domain_id=fields.master_domain_id;
                    params.master_domain_field_id=fields.master_domain_field_id;
                    params.condition=fields.condition;
                    params.value=fields.value;
                    // params.user_id=$scope.user.id_user;
                    params.field=$scope.feldName;
                    // params.field_type=$scope.fieldType;  

                    if(params.value!=null && $scope.fieldType=='date'){
                        params.value = dateFilter(fields.value,'yyyy-MM-dd');
                    }
                    else if(params.value!=null && $scope.fieldType=='drop_down'){
                        params.value = fields.value.toString();
                    }
                    else{
                        params.value=fields.value;
                    }

                    contractService.filterCreate(params).then(function(result){
                        if(result.status){
                            $scope.cancel();
                            $scope.filterActionitemList();
                            $scope.tableStateRef.pagination.start =0;
                            $scope.tableStateRef.pagination.totalItemCount =10;
                            $scope.tableStateRef.pagination.number =10;
                            $scope.callServer($scope.tableStateRef);
                            $rootScope.toast('Success', result.message);
                        }else{
                            $rootScope.toast('Error',result.error);
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


    $scope.currentFlterDelete=function(rowdata){
        var r = confirm($filter('translate')('general.alert_Delete_filter'));
        if(r==true){
            contractService.deleteContractFlter({'id_master_filter':rowdata.id_master_filter}).then(function(result){
                if(result.status){
                    $scope.filterActionitemList();
                    $scope.tableStateRef.pagination.start =0;
                    $scope.tableStateRef.pagination.totalItemCount =10;
                    $scope.tableStateRef.pagination.number =10;
                    $scope.callServer($scope.tableStateRef);
                    $rootScope.toast('Success', result.message);
                }
                else{
                    $rootScope.toast('Error',result.error,'error');
                }
            })
        }
    }
    
        $scope.filterActionitemList=function(){
        var params ={};
        // params.user_id = $scope.user1.id_user;
        params.module = 'action_items';
        contractService.getContractList(params).then(function(result){
            $scope.filterList=result.data;
            angular.forEach($scope.filterList,function(obj){
                obj.value_names_string = $sce.trustAsHtml(obj.value_names_string);
            });            
            $scope.filterCross=false;
            if($scope.filterList.length>0){
                $scope.filterCross=true;
                }
            });
        }
        $scope.filterActionitemList();

    // $scope.getFiltersData = function (provider) {
    //     $scope.contracts=[];
    //     var params = {};
    //     if($scope.business_unit_id) params.business_unit_id = $scope.business_unit_id;        
    //     if(provider) params.provider_name = provider;
    //     params.customer_id = $scope.user1.customer_id;
    //     params.id_user  = $scope.user1.id_user;
    //     params.user_role_id  = $scope.user1.user_role_id;
    //     actionItemsService.getActionItemFilters(params).then(function(result){
    //         if(result.status){
    //            result.data.providers.unshift({'provider_name':'All'});
    //            result.data.contracts.unshift({'contract_id' : 'all', 'contract_name' : 'All'});
    //             $scope.providers = result.data.providers;
    //             $scope.contracts = result.data.contracts;
    //         }
    //     });
    // }
    // $scope.getFiltersData('');

    $scope.goToQuestion = function(row) {
        var obj = {};
        obj.action_name = 'view';
        obj.action_description = 'view$$module$$questions$$('+row.module_name+')';
        obj.module_type = $state.current.activeLink;
        obj.action_url = $location.$$absUrl;
       $rootScope.confirmNavigationForSubmit(obj);
       if(row.is_workflow=='1' && row.type=='project' && $rootScope.access !='eu'){
        $state.go('app.projects.project-module-task',
        {name:row.contract_name,id:encode(row.contract_id),rId:encode(row.contract_review_id),mName:row.module_name,
            moduleId:encode(row.module_id),tName:row.topic_name,tId:encode(row.topic_id),qId:encode(row.question_id),
            wId:encode(row.contract_workflow_id),type:'workflow'},{ reload: true, inherit: false });
       }
       if(row.is_workflow=='1' && row.type=='contract' && $rootScope.access !='eu'){
            $state.go('app.contract.contract-module-workflow',
            {name:row.contract_name,id:encode(row.contract_id),rId:encode(row.contract_review_id),mName:row.module_name,
                moduleId:encode(row.module_id),tName:row.topic_name,tId:encode(row.topic_id),qId:encode(row.question_id),
                wId:encode(row.contract_workflow_id),type:'workflow'},{ reload: true, inherit: false });
        }
         if(row.is_workflow=='0' && row.type=='contract' && $rootScope.access !='eu'){                        
            $state.go('app.contract.contract-module-review',
            {name:row.contract_name,id:encode(row.contract_id),rId:encode(row.contract_review_id),mName:row.module_name,
                moduleId:encode(row.module_id),tName:row.topic_name,tId:encode(row.topic_id),
                qId:encode(row.question_id),type:'review'},
                { reload: true, inherit: false });
        }

        if(row.is_workflow=='1' && row.type=='project' && $rootScope.access =='eu'){
            $state.go('app.projects.project-module-task11',
            {name:row.contract_name,id:encode(row.contract_id),rId:encode(row.contract_review_id),mName:row.module_name,
                moduleId:encode(row.module_id),tName:row.topic_name,tId:encode(row.topic_id),qId:encode(row.question_id),
                wId:encode(row.contract_workflow_id),type:'workflow'},{ reload: true, inherit: false });
           }

           if(row.is_workflow=='1' && row.type=='contract' && $rootScope.access =='eu'){
            $state.go('app.contract.contract-module-workflow11',
            {name:row.contract_name,id:encode(row.contract_id),rId:encode(row.contract_review_id),mName:row.module_name,
                moduleId:encode(row.module_id),tName:row.topic_name,tId:encode(row.topic_id),qId:encode(row.question_id),
                wId:encode(row.contract_workflow_id),type:'workflow'},{ reload: true, inherit: false });
        }
         if(row.is_workflow=='0' && row.type=='contract' && $rootScope.access =='eu'){                        
            $state.go('app.contract.contract-module-review11',
            {name:row.contract_name,id:encode(row.contract_id),rId:encode(row.contract_review_id),mName:row.module_name,
                moduleId:encode(row.module_id),tName:row.topic_name,tId:encode(row.topic_id),
                qId:encode(row.question_id),type:'review'},
                { reload: true, inherit: false });
        }

    }   
    $scope.providersListTable = function(tableState1){
        $scope.isLoading1 = true;
        var pagination = tableState1.pagination;
        $scope.tableStateRef1 = tableState1;
        tableState1.id_user  = $scope.user1.id_user;
        tableState1.user_role_id  = $scope.user1.user_role_id;
        tableState1.customer_id = $scope.user1.customer_id;
        contractService.contractProviders(tableState1).then(function(result){
            $scope.providersList = result.data;
            $scope.emptyTable1=false;
            tableState1.pagination.numberOfPages =  Math.ceil(result.data.total_records / tableState1.pagination.number);
            $scope.isLoading1 = false;
            if(result.data.length < 1)
                $scope.emptyTable1=true;
            $scope.isLoading1 = false;
        });
    }
    $scope.updateContractReview = function (row, type) {
        //console.log('row info',row);
        $scope.review_access = true;
        $scope.type = type;
        $scope.isActionItem = true;
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/create-edit-contract-review.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.update = false;
                //$scope.bottom = 'general.save';
                $scope.isEdit = false;
                if (item) {
                    $scope.isEdit = true;
                    $scope.submitStatus = true;
                    $scope.data = angular.copy(item);
                    $scope.data.due_date = moment($scope.data.due_date).utcOffset(0, false).toDate();
                    $scope.title = '';
                    $scope.update = true;
                    $scope.bottom = 'general.update';
                }
                if($scope.type == 'view'){
                    $scope.bottom = 'contract.finish';
                }
                if($scope.type == 'add'){
                    $scope.bottom = 'general.update';
                }
                var param ={};
                param.provider_id = row.provider_id;
                param.contract_id = row.contract_id;
                param.customer_id = $scope.user1.customer_id;
                param.user_role_id = $scope.user1.user_role_id;
                param.contract_review_id = row.contract_review_id;
                contractService.getActionItemResponsibleUsers(param).then(function(result){
                    $scope.userList = result.data;
                });
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                $scope.goToEdit = function(data){
                    $scope.data.due_date = moment(data.due_date).utcOffset(0, false).toDate();
                }
                var params ={};
                $scope.getActionItemById = function(id){
                    contractService.getActionItemDetails({'id_contract_review_action_item':id}).then(function(result){
                        $scope.data = result.data[0];
                    });
                }
                $scope.addReviewActionItem=function(data){
                    console.log('--------1', data);
                    $scope.due_date = angular.copy(data.due_date);
                    $scope.due_date = dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                    if($scope.type == 'view'){
                        params.external_users = data.external_users;
                        params.id_contract_review_action_item = data.id_contract_review_action_item;
                        params.comments = data.comments;
                        params.is_finish = data.is_finish;
                        params.updated_by = $scope.user.id_user;
                        params.contract_id  = row.contract_id;
                        params.due_date  = $scope.due_date;
                        params.reference_type= row.type;
                        if(params.is_finish == 1){
                            var r=confirm($filter('translate')('general.alert_action_finish'));
                            $scope.deleConfirm = r;
                            if(r==true){
                                contractService.reviewActionItemUpdate(params).then(function (result) {
                                    if (result.status) {
                                        var obj = {};
                                        obj.action_name = 'update';
                                        obj.action_description = 'finish$$action item$$('+data.action_item+')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);
                                        $rootScope.toast('Success', result.message);
                                        console.log('$scope.callServer($scope.tableStateRef)----2');
                                        $scope.callServer($scope.tableStateRef);
                                        $scope.cancel();
                                    } else {
                                        $rootScope.toast('Error', result.error,'error');
                                    }
                                });
                            }
                        }else{
                            contractService.reviewActionItemUpdate(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    var obj = {};
                                    obj.action_name = 'save';
                                    obj.action_description = 'save$$action$$item$$('+data.action_item+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.getActionItemById(data.id_contract_review_action_item);
                                    console.log('$scope.callServer($scope.tableStateRef)----3');
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error,'error');
                                }
                            });
                        }
                    }
                    else if(data != 0 && data.hasOwnProperty('id_contract_review_action_item')){
                        delete data.comments;
                        params = angular.copy(data);
                        params.updated_by = $scope.user.id_user;
                        params.contract_id = params.id_contract =  row.contract_id;
                        params.due_date  = $scope.due_date;
                        params.reference_type= row.type;
                        contractService.addReviewActionItemList(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'update$$action$$item$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.getActionItemById(data.id_contract_review_action_item);
                                console.log('$scope.callServer($scope.tableStateRef)----4');
                                $scope.callServer($scope.tableStateRef);
                                $scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
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
    }
    if($stateParams.id){
        var param ={};
        param.user_role_id  = $scope.user1.user_role_id;
        param.customer_id = $scope.user1.customer_id;
        param.id_user = $scope.user1.id_user;
        param.id_contract_review_action_item = decode($stateParams.id);
        contractService.getAllActionItems(param).then(function(result){
            $scope.data = result.data.data[0];
            $scope.updateContractReview($scope.data,'view');
        });
    }
    $scope.deleteContractActionItem = function(row){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            var params ={};
            params.id_contract_review_action_item  = row.id_contract_review_action_item ;
            params.updated_by  = $scope.user1.id_user;
            contractService.deleteActionItem(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$action$$item$$('+row.action_item+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    console.log('$scope.callServer($scope.tableStateRef)----5');
                    $scope.callServer($scope.tableStateRef);
                    $scope.cancel();
                }else $rootScope.toast('Error', result.error, 'error',$scope.user);
            });
        }
    }


    $scope.createActionItem = function () {
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'create-action-items.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.bottom = 'general.save';
               $scope.getSelectedValue= function(id){
                   //console.log('k',id);
                  $scope.idInfo =id;
                  $scope.userList='';
                  $scope.action.contract_id ='';
                   if(id=='1'){
                       var params={};
                       params.customer_id = $scope.user1.customer_id;
                       params.id_user  = $scope.user1.id_user;
                       params.user_role_id  = $scope.user1.user_role_id;
                       params.type='contract';
                       params.business_unit_id='All';
                       params.can_access=1;
                       projectService.getAllContractsAndProjects(params).then(function(result){
                           $scope.contractNames = result.data.data;
                       })
                   }

                   if(id=='2'){
                       var params ={};
                       params.customer_id = $scope.user1.customer_id;
                       params.id_user  = $scope.user1.id_user;
                       params.user_role_id  = $scope.user1.user_role_id;
                       params.type='project';
                       params.business_unit_id='All';
                       params.can_access=1;
                       projectService.getAllContractsAndProjects(params).then(function(result){
                           $scope.projectNames = result.data.data;
                       })
                   }
                   if(id=='3'){
                    providerService.list({'customer_id': $scope.user1.customer_id,'status':1,'all_providers':true}).then(function(result){
                        $scope.providers = result.data.data;
                    });
                   }
               }
                
              
               function arr_diff (a1, a2) {

                var a = [], diff = [];
            
                for (var i = 0; i < a1.length; i++) {
                    a[a1[i]] = true;
                }
            
                for (var i = 0; i < a2.length; i++) {
                    if (a[a2[i]]) {
                        delete a[a2[i]];
                    } else {
                        a[a2[i]] = true;
                    }
                }
            
                for (var k in a) {
                    diff.push(k);
                }
            
                return diff;
            }

            function intersection(data) {
                //console.log('data',data);
                //console.log('len',data.length);
                var result = [];
                var lists;
                
                if (data.length === 1) {
                  lists = data[0];
                } else {
                    lists = data;
                }
                //console.log('lists',lists);
                    for (var i = 0; i < lists.length; i++) {
                  var currentList = lists[i];
                  for (var y = 0; y < currentList.length; y++) {
                    var currentUser = currentList[y];
                    var currentValue = currentList[y].id_user;
                    if (result.findIndex(item => item.id_user == currentValue) === -1) {
                      var existsInAll = true;
                      for (var x = 0; x < lists.length; x++) {
                        if (lists[x].findIndex(item => item.id_user == currentValue) === -1) {
                          existsInAll = false;
                          break;
                        }
                      }
                      if (existsInAll) {
                          result.push(currentUser);
                        }
                    }
                }
            }
            return result;
        }
        
        
        
        var curData=[]; var resultarray=[]; var action='added';  var result_contract_id=''; 
        $scope.getSelectedContract = function(key){
            var params1={};
            var undefinedId = resultarray[0];
            if($scope.action.contract_id !=undefined){
                resultarray.push($scope.action.contract_id);
            }
            console.log('resultarray',resultarray);
            console.log('ng scope',$scope.action.contract_id);
            if($scope.action.contract_id==undefined){
               // console.log('in 145',resultarray);
               action='deleted';
                resultarray =[];
                console.log('in 148',resultarray);
            }
            if($scope.action.contract_id!=undefined && $scope.action.contract_id.length==1){
                action='added';
                result_contract_id =$scope.action.contract_id;
            }
            if(resultarray[resultarray.length-2]!=undefined && resultarray[resultarray.length-1]!=undefined){
                //console.log('entered if');
                 result_contract_id = arr_diff(resultarray[resultarray.length-2], resultarray[resultarray.length-1]);
            }
           
          
            console.log('ids',result_contract_id[0]);
            if(key=='contract_id' && $scope.action.contract_id!=undefined){
                params1.contract_id = result_contract_id[0];
            }
            if(key=='project_id' && $scope.action.contract_id!=undefined){
                params1.type='project';
                params1.contract_id =result_contract_id[0];
                params1.project_id = result_contract_id[0];
           }
           if(key=='provider_id' && $scope.action.contract_id!=undefined){
                params1.id_provider  = result_contract_id[0];
           }
            if(resultarray[resultarray.length-2]!=undefined && resultarray[resultarray.length-1]!=undefined){
                if(resultarray[resultarray.length-2].length  > resultarray[resultarray.length-1].length ){
                    action ='deleted';
                }
                else{
                    action ='added';
                }
           }
           //console.log('action',action);
           if($scope.action.contract_id ==undefined){
               $scope.userList='';
           }
            contractService.getActionItemResponsibleUsers(params1).then(function(result){
                    if(action=='added' || $scope.action.contract_id!=undefined){
                        //console.log('enter add');
                    curData.push({data:result.data,key:result_contract_id[0]});
                }
                if(action=='deleted'){
                    console.log('enter del');
                    const currentKey = result_contract_id[0];
                    curData = curData.filter((item) => {return item.key != currentKey});
                }
                if($scope.action.contract_id==undefined){
                    curData=[];
                    console.log('in final',$scope.action.contract_id);
                }
                 console.log('final data',curData);
                    var usersData=[];
                    angular.forEach(curData,function(i,o){
                        usersData.push(i.data);
                    })
                    res =intersection([usersData]);
                    $scope.userList =res;
                    console.log('res',res);
               });
             
                $scope.addActionItem = function(data){
                    console.log('data',data.contract_id);
                          $scope.due_date=angular.copy(data.due_date);
                          $scope.due_date=dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                          console.log($scope.due_date);
                          params = angular.copy(data);
                        if(data.type_id=='1') params.reference_type='contract';
                         if(data.type_id=='2') params.reference_type='project';
                         if(data.type_id=='3') params.reference_type='provider';
                         params.created_by = $scope.user1.id_user;
                       
                         params.id_user  = $scope.user1.id_user;
                         params.user_role_id  = $scope.user1.user_role_id;
                         params.due_date = $scope.due_date;
                         if(key!='provider_id'){
                            params.contract_id= data.contract_id.toString();
                        }
                         if(key=='provider_id') {
                             params.provider_id = data.contract_id.toString();
                             delete params.contract_id=='';
                         }
                       
                        //console.log(params);
                        projectService.multipleActionItemsAdding(params).then(function(result){
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.cancel();
                            }
                            else {
                                $rootScope.toast('Error', result.error,'error');
                           }
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
    }
    /*$scope.getActionItems = function (row) {
        $scope.filters.contract_id = '';
        // $scope.ActionItemsTable($scope.tableStateRef,row.contract_id);
        $scope.callServer($scope.tableStateRef);
    }
    $scope.getAllActionItems = function () {
        console.log('$scope.callServer($scope.tableStateRef)----7');
        $scope.callServer($scope.tableStateRef);
    }*/

    // $scope.callServer = function (tableState){
    //     $rootScope.module = '';
    //     $rootScope.displayName = '';
        
    //     $scope.isLoading = true;
    //     var pagination = tableState.pagination;

    //     tableState.customer_id = $scope.user1.customer_id;
    //     tableState.user_role_id  = $scope.user1.user_role_id;
    //     tableState.customer_id = $scope.user1.customer_id;
    //     tableState.id_user  = $scope.user1.id_user;

    //     if($stateParams.id==undefined &&
    //         $stateParams.cId==undefined &&
    //         $stateParams.priority==undefined &&
    //         $stateParams.due==undefined &&
    //         $stateParams.status==undefined){
    //     }else{
    //         if($stateParams.cId){
    //             if($scope.filters.contract_id!=undefined){}
    //             else{
    //                 $scope.filters.contract_id = decode($stateParams.cId);               
    //                 $scope.resetPagination=true;
    //             }
    //         }
    //         if($stateParams.status){
    //                 if($scope.filters.item_status !=undefined){}
    //                 else{
    //                     $scope.filters.item_status = decode($stateParams.status)+"";
    //                     $scope.resetPagination=true;
    //                 }
    //         }
    //         if($stateParams.due){
    //             $scope.filters.overdue = decode($stateParams.due);
    //             $scope.filters.item_status='open';
    //             $scope.resetPagination=true;
    //             //$scope.loadFiltersTable=true;
    //         }
    //         if($stateParams.priority){
    //             if($scope.filters.priority !=undefined){}
    //             else{
    //                 $scope.filters.priority = $stateParams.priority;
    //                 $scope.resetPagination=true;
    //                 $scope.filters.item_status=($scope.filters.item_status)?$scope.filters.item_status:'open';
    //                 //$scope.loadFiltersTable=true;
    //             }
    //         }
    //     }
    //     // console.log("filters**----",$scope.filters);
    //     // console.log("$stateParams**----",decode($stateParams.due));
    //     if($scope.filters.item_status) 
    //         tableState.contract_review_action_item_status = $scope.filters.item_status;
    //     else tableState.contract_review_action_item_status = 'all';

    //     if($scope.filters.priority && $scope.filters.priority != null) 
    //         tableState.priority = $scope.filters.priority;
    //     else delete tableState.priority;
        
    //     if($scope.filters.overdue){
    //         tableState.overdue  = $scope.filters.overdue;
    //     }else tableState.overdue=false;

    //     if($scope.filters.provider_name && $scope.filters.provider_name != null){
    //         tableState.provider_name  = $scope.filters.provider_name;
    //     }else if($scope.filters.provider_name == 'All') {
    //     }else{
    //         delete tableState.provider_name;
    //         $scope.filters.provider_name = '';
    //     }
    //     if($scope.filters.contract_id && $scope.filters.contract_id != null ){
    //         tableState.contract_id  = $scope.filters.contract_id;
    //     }else if($scope.filters.contract_id == 'all') {
    //     }else{
    //         delete tableState.contract_id;
    //         $scope.filters.contract_id = '';
    //     }
    //     if($scope.filters.show_my_action_items && $scope.filters.show_my_action_items != null){
    //         tableState.show_my_action_items  = $scope.filters.show_my_action_items;
    //     }else{
    //         delete tableState.show_my_action_items;
    //         $scope.filters.show_my_action_items = '';
    //     }
    //     if($scope.filters.item_status ==''){
    //         tableState.contract_review_action_item_status='all';
    //         $scope.filters.item_status='all';
    //         tableState.contract_review_action_item_status  =$scope.filters.item_status ?$scope.filters.item_status : 'all';
    //     }
    //     if($scope.filters.priority && $scope.filters.priority != null){
    //         tableState.priority  = $scope.filters.priority;
    //     }else{
    //         delete tableState.priority;
    //         $scope.filters.priority = '';
    //     }
    //     if($scope.resetPagination){            
    //         tableState.pagination={};
    //         tableState.pagination.start='0';
    //         tableState.pagination.number='10';
    //     }
    //     $scope.tableStateRef = tableState;
    //     contractService.getAllActionItems(tableState).then (function(result){
    //         $scope.ActionItemsList = result.data.data;           
    //         $scope.emptyTable=false;
    //         $scope.displayCount = $rootScope.userPagination;
    //         $scope.totalRecords = result.data.total_records;            
    //         tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
    //         $scope.pagesCount = Math.ceil(result.data.total_records / $rootScope.userPagination);
    //         console.log("pagesCount*--",$scope.pagesCount);
    //         $scope.isLoading = false;
    //         $scope.resetPagination=false;
    //         if(result.data.total_records < 1)
    //             $scope.emptyTable=true;
    //     })
    // }

    $scope.callServer = function (tableState){
        $scope.filtersData = {};
        $rootScope.module = '';
        $rootScope.displayName = '';     
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';   
        $scope.isLoading = true;
        $scope.isLoadingPagination = true;
        var pagination = tableState.pagination;
        tableState.customer_id = $scope.user1.customer_id;
        tableState.user_role_id  = $scope.user1.user_role_id;
        tableState.customer_id = $scope.user1.customer_id;
        tableState.id_user  = $scope.user1.id_user;
        tableState.is_advance_filter=1;
        
        if($scope.resetPagination){
            tableState.pagination={};
            tableState.pagination.start='0';
            tableState.pagination.number='10';
        }
        if($stateParams.id==undefined &&
            $stateParams.cId==undefined &&
            $stateParams.priority==undefined &&
            $stateParams.due==undefined &&
            $stateParams.status==undefined){}
        else{
            if($stateParams.cId){
                if($scope.filters.contract_id!=undefined){}
                else{
                    $scope.filters.contract_id = decode($stateParams.cId);               
                    $scope.resetPagination=true;
                }              
                tableState.sort={};
            }
            if($stateParams.status){
                if($scope.filters.item_status != $stateParams.status){}
                else {
                    $$scope.filters.item_status = $stateParams.status;
                }
            }
            if($stateParams.due){
                $scope.filters.overdue = decode($stateParams.due);  
                $scope.filters.item_status='open';            
            }
            if($stateParams.priority) {
                if($scope.filters.priority !=undefined){}
                else{
                    $scope.filters.priority = $stateParams.priority;
                    $scope.filters.item_status=($scope.filters.item_status)?$scope.filters.item_status:'open';
                }
              
               
            }
        }
      
        if($scope.filters.item_status) 
            tableState.contract_review_action_item_status = $scope.filters.item_status;
        else tableState.contract_review_action_item_status = 'all';


        if($scope.filters.priority && $scope.filters.priority != null) 
            tableState.priority = $scope.filters.priority;
        else delete tableState.priority;

        if($scope.filters.overdue){
                tableState.overdue  = $scope.filters.overdue;
        }else tableState.overdue=false;


           if($scope.filters.provider_name && $scope.filters.provider_name != null){
            tableState.provider_name  = $scope.filters.provider_name;
        }else if($scope.filters.provider_name == 'All') {
        }else{
            delete tableState.provider_name;
            $scope.filters.provider_name = '';
        }
        if($scope.filters.contract_id && $scope.filters.contract_id != null ){
            tableState.contract_id  = $scope.filters.contract_id;
        }else if($scope.filters.contract_id == 'all') {
        }else{
            delete tableState.contract_id;
            $scope.filters.contract_id = '';
        }
        if($scope.filters.show_my_action_items && $scope.filters.show_my_action_items != null){
            tableState.show_my_action_items  = $scope.filters.show_my_action_items;
        }else{
            delete tableState.show_my_action_items;
            $scope.filters.show_my_action_items = '';
        }
        if($scope.filters.item_status ==''){
            tableState.contract_review_action_item_status='all';
            $scope.filters.item_status='all';
            tableState.contract_review_action_item_status  =$scope.filters.item_status ?$scope.filters.item_status : 'all';
        }
        if($scope.filters.priority && $scope.filters.priority != null){
            tableState.priority  = $scope.filters.priority;
        }else{
            delete tableState.priority;
            $scope.filters.priority = '';
        }
        $scope.totalRecords = 0;
        $scope.tableStateRef = tableState;
        contractService.getAllActionItems(tableState).then (function(result){
            $scope.ActionItemsList = result.data.data;           
            $scope.emptyTable=false;
            $scope.totalRecords = 0;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords = result.data.total_records;
            var pages = 0;
            pages = Math.ceil(result.data.total_records / $rootScope.userPagination);
            tableState.pagination.numberOfPages =  pages; 
            $scope.isLoading = false;
            $scope.resetPagination=false;
            if(result.data.total_records < 1){
            $scope.emptyTable=true;
            }
           
        });
    }

    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $scope.resetPagination=true;
                $rootScope.userPagination = val;
                console.log('$scope.callServer($scope.tableStateRef)----22');
                $scope.callServer($scope.tableStateRef);
            }                
        });
    }

    // $scope.defaultPages = function(val){
    //     userService.userPageCount({'display_rec_count':val}).then(function (result){
    //         if(result.status){
    //             $rootScope.userPagination = val;
    //             console.log('$scope.callServer($scope.tableStateRef)----22');
    //             $scope.callServer($scope.tableStateRef);
    //         }                
    //     });
    // }

    $scope.getByPriority = function(val){
        if(val){
            $scope.tableStateRef.priority=val;
            $scope.filters.priority=val;
        }else{
            delete $scope.tableStateRef.priority;
        }
        if($scope.filters.overdue) {
            delete $scope.tableStateRef.overdue;
        }
        $scope.filters.overdue=false;
        $stateParams.due = undefined;
        console.log('$scope.callServer($scope.tableStateRef)----8');
        $scope.loadFiltersTable=true;
        $scope.resetPagination=true;
        $scope.callServer($scope.tableStateRef);
    }

    $scope.getByProvider = function(val){

        delete $scope.tableStateRef.priority;
        $scope.filters.priority='';

        delete $scope.tableStateRef.show_my_action_items;
        $scope.filters.show_my_action_items='';
        
        delete $scope.tableStateRef.contract_id;
        $scope.filters.contract_id='';

        $scope.tableStateRef.contract_review_action_item_status='all';
        $scope.filters.item_status='all';

        if(val){
            $scope.tableStateRef.provider_name=val;
            $scope.getFiltersData(val);
        }else{
            delete $scope.tableStateRef.provider_name;
        }
        if($scope.filters.overdue){
            delete $scope.tableStateRef.overdue;
        }
        $scope.filters.overdue=false;
        $stateParams.due = undefined;
        console.log('$scope.callServer($scope.tableStateRef)----9');
        $scope.resetPagination=true;
        $scope.loadFiltersTable=true;
        $scope.callServer($scope.tableStateRef);
    }

    $scope.getByContracts = function(val){
        $stateParams.cId=undefined
        delete $scope.tableStateRef.priority;
        $scope.filters.priority='';

        delete $scope.tableStateRef.show_my_action_items;
        $scope.filters.show_my_action_items='';

        $scope.tableStateRef.contract_review_action_item_status='all';
        $scope.filters.item_status='all';

        if(val){
            $scope.tableStateRef.contract_id = val;
            $scope.filters.contract_id=val;
        }else{
            delete $scope.tableStateRef.contract_id;
        }
        if($scope.filters.overdue){
            delete $scope.tableStateRef.overdue;
        }
        $scope.filters.overdue=false;
        $stateParams.due = undefined;
        console.log('$scope.callServer($scope.tableStateRef)----10');
        $scope.resetPagination=true;
        $scope.loadFiltersTable=true;
        $scope.callServer($scope.tableStateRef);
    }

    $scope.getByStatus = function(val){
        if(val){
            $scope.tableStateRef.contract_review_action_item_status = val;
            $scope.filters.item_status=val;
        }else{
            $scope.tableStateRef.contract_review_action_item_status = 'all';
            $scope.filters.item_status='all';
        }
        if($scope.filters.overdue) delete $scope.tableStateRef.overdue;
        $scope.filters.overdue=false;
        $stateParams.due = undefined;
        $scope.resetPagination=true;
        console.log('$scope.callServer($scope.tableStateRef)----11');
        $scope.loadFiltersTable=true;
        $scope.callServer($scope.tableStateRef);
    }

    $scope.getByAssignedType=function(val){
        if(val){
            $scope.tableStateRef.show_my_action_items=val;
            $scope.filters.show_my_action_items=val;
        }else{
            delete $scope.tableStateRef.show_my_action_items;
            $scope.filters.show_my_action_items='';
        }
        if($scope.filters.overdue){
            delete $scope.tableStateRef.overdue;
        }
        $scope.filters.overdue=false;
        $stateParams.due = undefined;
        $scope.resetPagination=true;
        console.log('$scope.callServer($scope.tableStateRef)----12');
        $scope.loadFiltersTable=true;
        $scope.callServer($scope.tableStateRef);
    }
    $scope.searchActionItems=function(){
        $scope.tableStateRef = {};
        $scope.tableStateRef.sort = {};
        $scope.tableStateRef.search = {};
        $scope.tableStateRef.search.predicateObject = {};
        $scope.resetPagination=true;
        $scope.tableStateRef.search.predicateObject.search_key=$scope.search_key;
        
        if($scope.filters.priority) $scope.tableStateRef.priority = $scope.filters.priority;
        if($scope.filters.show_my_action_items) $scope.tableStateRef.show_my_action_items = $scope.filters.show_my_action_items;
        if($scope.filters.item_status) $scope.tableStateRef.contract_review_action_item_status = $scope.filters.item_status;
        if($scope.filters.contract_id) $scope.tableStateRef.contract_id = $scope.filters.contract_id;
        if($scope.filters.provider_name) $scope.tableStateRef.provider_name = $scope.filters.provider_name;
        
        console.log('$scope.callServer($scope.tableStateRef)----17');
        $scope.callServer($scope.tableStateRef);
    }
})
