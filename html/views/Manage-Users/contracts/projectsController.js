angular.module('app',['localytics.directives'])
.controller('projectOverviewCtrl', function($scope, $rootScope,$localStorage, $state, $translate,$filter, encode, businessUnitService, contractService, AuthService,userService){
    $scope.bussinessUnit = {};
    $scope.displayCount = $rootScope.userPagination;
    var param ={};
    $scope.del=0;
    if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
        $scope.del=1;
    }

    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }

    param.user_role_id=$rootScope.user_role_id;
    param.id_user=$rootScope.id_user;
    param.customer_id = $scope.user1.customer_id;
    param.status = 1;
    businessUnitService.list(param).then(function(result){
        $scope.bussinessUnit = result.data.data;
        console.log("asd",$scope.bussinessUnit)
    });

   
    $scope.getDownloadUrl = function(objData){
        var fileName = objData.document_source;
        var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
        var d = {};
        d.id_document = objData.id_document;
        var encryptedPath= objData.encryptedPath;
        var filePath =API_URL+'Cron/preview?file='+encryptedPath;
        encodePath =encode(filePath);
        if(fileExtension=='pdf' || fileExtension=='PDF' ){
           // console.log('kasi');
            window.open(window.origin+'/Document/web/preview.html?file='+encodePath+'#page=1');
        }
        else{
            contractService.getUrl(d).then(function (result) {
                if(result.status){
                    var obj = {};
                    obj.action_name = 'download';
                    obj.action_description = 'download$$attachment$$('+ objData.document_name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = location.href;
                    if(AuthService.getFields().data.parent){
                        obj.user_id = AuthService.getFields().data.parent.id_user;
                        obj.acting_user_id = AuthService.getFields().data.data.id_user;
                    }
                    else obj.user_id = AuthService.getFields().data.data.id_user;
                    if(AuthService.getFields().access_token != undefined){
                        var s = AuthService.getFields().access_token.split(' ');
                        obj.access_token = s[1];
                    }
                    else obj.access_token = '';
                    $rootScope.toast('Success',result.message);
                    userService.accessEntry(obj).then(function(result1){
                        if(result1.status){
                            if(DATA_ENCRYPT){
                                result.data.url =  GibberishAES.enc(result.data.url, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                result.data.file =  GibberishAES.enc(result.data.file, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                            }
                            window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+$rootScope.id_user+'&access_token='+obj.access_token;
                        }
                    });
                }
                else{
                    $rootScope.toast('Error',result.error);
                }
            });
        }
      
    };
    $scope.redirectUrl = function(url){
        if(url != undefined){
            var r=confirm($filter('translate')('contract.alert_msg'));
            if(r==true){
                url = url.match(/^https?:/) ? url : '//' + url;
                window.open(url,'_blank');
            }
        }
    };    
})
.controller('createProjectCtrl', function($scope, $rootScope,$localStorage,$filter, $state,$stateParams,$location, decode,projectService,templateService, providerService, contractService, masterService,Upload, dateFilter){
    $scope.currencyList = [];
    $scope.templateList = [];
    $scope.contract = {};
    $scope.file={};
    $scope.links_delete = [];
    $rootScope.module = '';
    $rootScope.displayName = '';
    $rootScope.breadcrumbcolor='' ;
    $rootScope.class='';
    $rootScope.icon='';
    $scope.del=0;
    $scope.projectId=0;
    $scope.isEditContract=false;
    $scope.disabled =false;
    $localStorage.curUser.data.filters = {};

    if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
        $scope.del=1;
    }
    
    masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
        $scope.currencyList = result.data;
    });
   
    $scope.title = 'general.create';
    $scope.bottom = 'general.save';
    $scope.enableTemplate = true;

    projectService.generateprojectId({'customer_id':$scope.user1.customer_id}).then(function(result){
        if(result.status){
            $scope.contract = result.data;
        }
    })
    $scope.getContractDelegates = function (id,projectId){
        contractService.getDelegates({'id_business_unit': id}).then(function(result){
            $scope.delegates = result.data;
        });
        var params = {};
        params.business_unit_id = id;
        params.contract_id = projectId;
        params.type = "buowner";
        contractService.getbuOwnerUsers(params).then(function(result){
            $scope.buOwnerUsers = result.data;
        });
    }
    $scope.contractLinks=[];
    $scope.contractLink={};
    $scope.verifyLink = function(data){
        if(data !={}){
            $scope.contractLinks.push(data);
            $scope.contractLink={};
        }
    }
    $scope.removeLink = function(index){
        var r=confirm($filter('translate')('general.alert_continue'));
        if(r==true){
            $scope.contractLinks.splice(index, 1);
        }                    
    }
    $scope.deleteFile = function(index,row){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            $scope.contract.attachment.links.splice(index,1);
            var obj={}; obj.id_document=row.id_document;
            $scope.links_delete.push(obj) ;
        }
    }
    
    $scope.addContract = function (data1){
        //console.log('data info',data1);
        $scope.formDataObj= angular.copy(data1);
        var contract={};
        contract= $scope.formDataObj;
        contract.customer_id = $scope.user1.customer_id;
        contract.created_by = $scope.user.id_user;
        contract.type='project';
        contract.project_end_date = dateFilter(contract.project_end_date,'yyyy-MM-dd');
        contract.project_start_date = dateFilter(contract.project_start_date,'yyyy-MM-dd');
        if($scope.user.access =='bo')
            contract.contract_owner_id = $scope.user.id_user;
        else contract.contract_owner_id = contract.contract_owner_id;
        contract.attachment_delete = [];
        if($scope.file.delete){
            angular.forEach($scope.file.delete, function(i,o){
                var obj = {};
                obj.id_document = i.id_document;
                contract.attachment_delete.push(obj) ;
            });
        }
        contract.links_delete=$scope.links_delete;
        contract.links=$scope.contractLinks;
        var params = {};
        angular.copy(contract,params);
        params.updated_by = $scope.user.id_user;
        if(moment( params.project_end_date).utcOffset(0, false).toDate() <= moment( params.project_start_date).utcOffset(0, false).toDate()){
            alert($filter('translate')('general.alert_start_date_less_end'));
        }else{
            if(contract.id_contract){
                if(moment($scope.end_date).utcOffset(0, false).toDate() > moment( params.contract_end_date).utcOffset(0, false).toDate()){
                    var r = confirm($filter('translate')('general.alert_contract_end_update'));
                    if (r == true) {
                        Upload.upload({
                            url: API_URL+'Project/createProject',
                            data: {
                                'file' : $scope.file.attachment,
                                'contract': params
                            }
                        }).then(function(resp){
                            if(resp.data.status){
                                if(contract.is_workflow=='1')
                                    $state.go('app.project.view',{name:contract.contract_name,id:$stateParams.id,wId:encode(contract.id_contract_workflow),type:'workflow'});
                                else
                                    $state.go('app.project.view',{name:contract.contract_name,id:$stateParams.id,type:'review'});
                                // $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id});
                                $rootScope.toast('Success',resp.data.message);
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'update$$contract$$'+contract.contract_name;
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                            }else{
                                $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                            }
                        },function(resp){
                            $rootScope.toast('Error',resp.error);
                        },function(evt){
                            var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                        });
                    }
                }else {
                    Upload.upload({
                        url: API_URL+'Contract/update',
                        data: {
                            'file' : $scope.file.attachment,
                            'contract': params
                        }
                    }).then(function(resp){
                        if(resp.data.status){
                            if(contract.is_workflow=='1')
                                $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id,wId:encode(contract.id_contract_workflow),type:'workflow'});
                            else
                                $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id,type:'review'});
                            $rootScope.toast('Success',resp.data.message);
                            var obj = {};
                            obj.action_name = 'update';
                            obj.action_description = 'update$$contract$$'+contract.contract_name;
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                        }else{
                            $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                        }
                    },function(resp){
                        $rootScope.toast('Error',resp.error);
                    },function(evt){
                        var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                    });
                }                
            }
            else{
                 Upload.upload({
                    url: API_URL+'Project/createProject',
                     data: {
                        'file' : $scope.file.attachment,
                        'contract': contract
                     }
                 }).then(function(resp){
                 if(resp.data.status){
                    $state.go('app.projects.all-projects');
                    $rootScope.toast('Success',resp.data.message);
                     var obj = {};
                     obj.action_name = 'add';
                     obj.action_description = 'add$$contract$$'+contract.contract_name;
                     obj.module_type = $state.current.activeLink;
                     obj.action_url = $location.$$absUrl;
                     $rootScope.confirmNavigationForSubmit(obj);
                 }else{
                    $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                 }
                 },function(resp){
                    $rootScope.toast('Error',resp.error);
                 },function(evt){
                    var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                 });
            }
        }
    }
    $scope.cancel = function(){
        //$window.history.back();
       if($stateParams.id){
            $rootScope.module = 'Project Details';
            $rootScope.displayName = $stateParams.name;
            if($scope.contract.is_workflow=='1')
                $state.go('app.contract.view',{name:$scope.contract.contract_name,id:$stateParams.id,wId:encode($scope.contract.id_contract_workflow),type:'workflow'});
            else
                $state.go('app.contract.view',{name:$scope.contract.contract_name,id:$stateParams.id,type:'review'});
            // $state.go('app.contract.view',{name:$stateParams.name,id:$stateParams.id});
        }
       else $state.go('app.contract.all-contracts');
    }
})
.controller('allProjectsListCtrl', function($scope, $rootScope, $state,$filter, $stateParams, $localStorage, dateFilter, $timeout,$uibModal, 
            projectService, contractService, masterService,businessUnitService, encode, AuthService,userService,calenderService,$sce,moduleService){
    $scope.del=0;
    $scope.contract_status='';
    $scope.can_access=1;
    $scope.date_field='';
    $scope.date_period='';
    $scope.searchFields = {};
    $scope.business_unit_id='All';
    $scope.relationship_category_id='';
    $scope.automatic_prolongation=null;
    $scope.provider_name='';
    $scope.displayCount = $rootScope.userPagination;
    $localStorage.curUser.data.filters.allActivities = undefined;
    $scope.resetPagination=false;
    $scope.showBU = true;
    if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
        $scope.del=1;
    }

    $scope.advancedFilterProject = function(){
        $scope.filterCreate = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/advancedFilterContract.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';


                $scope.filterListProjects=function(){
                var params ={};
                // params.user_id=$scope.user.id_user;
                params.module='all_projects_list';
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
                $scope.filterListProjects();


            $scope.flterDelete=function(rowdata){
                var r = confirm($filter('translate')('general.alert_Delete_filter'));
                if(r==true){
                    contractService.deleteContractFlter({'id_master_filter':rowdata.id_master_filter}).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success', result.message);
                            $scope.filterListProjects();
                            $scope.filterProjectsList();
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
            templateUrl: 'views/Manage-Users/contracts/create-project-filter.html',  
            controller: function ($uibModalInstance,$scope,item) {
            $scope.bottom ='general.save';
             $scope.title='controller.add_filter_criteria'

                contractService.getContractDomain({'domain_module': 'all_projects_list'}).then(function(result){
                    $scope.projectFilter = result.data;                   
                    });
            
            
                $scope.getProjectDomainFieldList=function(id){
                    $scope.idDomain=id; 
                    contractService.getContractField({'id_master_domain': $scope.idDomain}).then(function(result){
                        $scope.projectField = result.data;
                    });
                    var domainId= $scope.projectFilter.filter(item => { return item.id_master_domain == id; });
                    $scope.domainType= domainId[0].domain;
                }
                $scope.getProjectCondition=function(domainFieldId){
                    $scope.filterCreate.value='';
                    var domainFieldData = $scope.projectField.filter(item => { return item.id_master_domain_fields == domainFieldId; });
                    $scope.fieldType=domainFieldData[0].field_type;
                    $scope.feldName=domainFieldData[0].field_name;

                    if($scope.feldName=='Currency'){
                    masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                        $scope.currencyList = result.data;
                    });
                }
                if($scope.feldName=='Responsible User'){
                projectService.eventResponsibleUsers().then (function(result){
                    $scope.eventResponsibleUsers=result.data;
                });
            }


                    if($scope.feldName=='Business Unit'){
                    var param ={};
                    param.user_role_id=$rootScope.user_role_id;
                    param.id_user=$rootScope.id_user;
                    param.customer_id = $scope.user1.customer_id;
                    param.status = 1;
                    businessUnitService.list(param).then(function(result){
                        $scope.bussinessUnit = result.data.data;
                        });
                    }

                    if($scope.feldName=='Owner'){
                contractService.responsibleUserList({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(),'forAdvacedFilter':'1', 'type': 'buowner','forDocumentIntelligence':'1' }).then(function (result) {
                    $scope.buOwnerUsers = result.data;
                    })
                     }

                    if($scope.feldName=='Delegate'){
                    contractService.getDelegates({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(),'forAdvacedFilter':'1','forDocumentIntelligence':'1' }).then(function (result) {
                        $scope.delegates = result.data;
                    })
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
                    params.module='all_projects_list';
                    params.id_master_filter=row.id_master_filter;
                    console.log("id enter",params.id_master_filter);
                    contractService.getContractList(params).then(function(result){
                        $scope.filterCreate=result.data[0];
                        $scope.fieldType=result.data[0].field_type;
                        $scope.feldName=result.data[0].field;
                        $scope.domainType= result.data[0].domain;
                        if($scope.filterCreate.field_type=='numeric_text' || $scope.filterCreate.field_type=='free_text' || $scope.filterCreate.field_type=='drop_down' ||  $scope.filterCreate.field_type=='date'){  
                            contractService.getContractField({'id_master_domain': row.master_domain_id}).then(function(result){
                                    $scope.projectField = result.data;
                                });
                        }
                        if($scope.filterCreate.field_type=='date'){
                            if($scope.filterCreate.value){
                                $scope.filterCreate.value = moment( $scope.filterCreate.value).utcOffset(0, false).toDate();
                            }
                        $scope.options = {
                            minDate: moment().utcOffset(0, false).toDate(),
                            showWeeks: false
                            };
                        }

                    if($scope.feldName=='Currency'){
                        masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                            $scope.currencyList = result.data;
                        });
                    }

                    if($scope.feldName=='Business Unit'){
                        var param ={};
                        param.user_role_id=$rootScope.user_role_id;
                        param.id_user=$rootScope.id_user;
                        param.customer_id = $scope.user1.customer_id;
                        param.status = 1;
                        businessUnitService.list(param).then(function(result){
                            $scope.bussinessUnit = result.data.data;
                        });
                    }

                    if($scope.feldName=='Responsible User'){
                    projectService.eventResponsibleUsers().then (function(result){
                        $scope.eventResponsibleUsers=result.data;
                    });
                }
    
                    if($scope.feldName=='Owner'){
                    contractService.responsibleUserList({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(), 'type': 'buowner','forAdvacedFilter':'1','forDocumentIntelligence':'1' }).then(function (result) {
                        $scope.buOwnerUsers = result.data;
                    })
                    }

                    if($scope.feldName=='Delegate'){
                    contractService.getDelegates({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(),'forAdvacedFilter':'1','forDocumentIntelligence':'1' }).then(function (result) {
                        $scope.delegates = result.data;
                        })
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
                                $scope.filterProjectsList();
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
                            $scope.filterProjectsList();
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
                    $scope.filterProjectsList();
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

    $scope.filterProjectsList=function(){
        var params ={};
        // params.user_id = $scope.user1.id_user;
        params.module = 'all_projects_list';
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
        $scope.filterProjectsList();




    $scope.createProject = function(){
        $state.go('app.projects.create-project');
    }
   
    $scope.getBUList = function(){
        var param ={};
        param.user_role_id=$rootScope.user_role_id;
        param.id_user=$rootScope.id_user;
        param.customer_id = $scope.user1.customer_id;
        param.status = 1;
        businessUnitService.list(param).then(function(result){
            result.data.data.unshift({'id_business_unit':'All', 'bu_name':'All'});
            $scope.bussinessUnit = result.data.data;
        });
    }
    // $scope.getBUList();

    $scope.goToProjectDetails = function(row){      
        $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract),type:'workflow'});
     }

 
    $scope.addReviewForCalendar = function (title, flag, row) {
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'add-review-calendar.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.customOptions = {};
                $scope.customOptions.bussiness_unit_id =[];
                $scope.customOptions.contract_id =[];
                $scope.bottom = 'general.save';
                $scope.action = 'general.add';
                $scope.update = false;
                $scope.isEdit = false;
                $scope.addType = flag;
                $scope.validateRecurrence = function () {
                    $scope.options1 = {};
                    var dt = angular.copy(($scope.customOptions.date) ? $scope.customOptions.date : moment().utcOffset(0, false).toDate());
                    if ($scope.customOptions.recurrence == '1') dt.setMonth(dt.getMonth() + 1);
                    if ($scope.customOptions.recurrence == '2') dt.setMonth(dt.getMonth() + 3);
                    if ($scope.customOptions.recurrence == '3') dt.setFullYear(dt.getFullYear() + 1);
                    if ($scope.addType) $scope.customOptions.recurrence_till = null;
                    $scope.options1 = {
                        minDate: dt,
                        showWeeks: false
                    };
                }
                $scope.head = 'calender.' + title;

                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                 $scope.options = {
                    minDate: moment().utcOffset(0, false).toDate(),
                    showWeeks: false
                };
                $scope.options1 = angular.copy($scope.options);
                if (!flag) {
                    $scope.workflowsList = [];
                    var obj = {};
                    obj.is_workflow = true;
                    obj.status = 1;
                    moduleService.list(obj).then(function (result) {
                        if (result.status) {
                            $scope.workflowsList = result.data.data;
                        }
                    });
                }
                var params={};
                if (!flag) params.is_workflow = true;
                $scope.getFilters = function () {
                    params.customer_id = $scope.user1.customer_id;
                    params.business_ids =item.business_unit_id;
                    params.type='project';
                    calenderService.smartFilter(params).then(function (result) {
                        if (result.status) {
                            $scope.relationCategory = result.data.relationship_list;
                            $scope.business_units = result.data.business_unit;
                            $scope.providers = result.data.provider;
                            $scope.contracts = result.data.contract;
                            $scope.completed_contracts = result.data.completed_contracts;
                            $scope.customOptions.bussiness_unit_id.push(item.business_unit_id);
                            $scope.customOptions.contract_id.push(item.id_contract);
                        }
                       
                    });
                }
                $scope.getFilters();
               
               
                var params1=[];
                $scope.getSmartFilters = function (key) {
                    //console.log('key info',key);
                    if (!flag) params1.is_workflow = true;
                    if (key == "bussiness_unit_id") {
                        $scope.customOptions.relationship_category_id = [];
                        $scope.customOptions.contract_id = [];
                        $scope.customOptions.provider_id = [];
                    }
                    if (key == "relationship_category_id" && !$scope.isEdit) {
                        $scope.customOptions.contract_id = [];
                        $scope.customOptions.provider_id = [];
                    }
                    if (key == "provider_id")
                        $scope.customOptions.contract_id = [];
                    

                    if ($scope.customOptions.bussiness_unit_id)
                        params1["business_ids"] = $scope.customOptions.bussiness_unit_id.toString();
                    //params1["business_ids"] =item.business_unit_id;
                    if (params1['business_ids'] == '') delete params1['business_ids'];
                    //console.log('params1---', params1);
                    calenderService.smartFilter(params1).then(function (result) {
                        if (result.status) {
                            $scope.relationCategory = result.data.relationship_list;
                            $scope.business_units = result.data.business_unit;
                            $scope.providers = result.data.provider;
                            $scope.contracts = result.data.contract;
                        }
                    });
                }
                $scope.addReview = function (formData) {

                    var data = angular.copy(formData);
                    data.customer_id = $scope.user1.customer_id;
                    data.created_by = $scope.user.id_user;
                    data.type='project';
                    if ($scope.customOptions.relationship_category_id)
                        data.relationship_category_id = $scope.customOptions.relationship_category_id.toString();
                    if ($scope.customOptions.bussiness_unit_id) {
                        data.business_unit_id = $scope.customOptions.bussiness_unit_id.toString();
                        delete data.bussiness_unit_id;
                    }
                    if ($scope.customOptions.provider_id)
                        data.provider_id = $scope.customOptions.provider_id.toString();
                    if ($scope.customOptions.contract_id)
                        data.contract_id = $scope.customOptions.contract_id.toString();
                    data.date = dateFilter($scope.customOptions.date, 'yyyy-MM-dd');
                    if ($scope.customOptions.recurrence_till)
                        data.recurrence_till = dateFilter($scope.customOptions.recurrence_till, 'yyyy-MM-dd');
                    if (!flag) data.is_workflow = true;

                    if (!data.provider_id) delete data.provider_id;
                    if (!data.contract_id) delete data.contract_id;

                    if (flag) {
                        data.workflow_name = data.review_name;
                        delete data.review_name;
                    }
                    if(data.auto_initiate==1){
                        // if(flag) var str = '<span style="font-style: normal;">Are you sure you want to automatically initiate ALL reviews in this calendar planning?</span> <br><br><span><b>NOTE :</b>&nbsp; Reviews will be initiated after 10 minutes of successful planning.</span>';
                        // else var str = '<span style="font-style: normal;">Are you sure you want to automatically initiate ALL tasks in this calendar planning?</span><br><br><span><b>NOTE :</b>&nbsp; Tasks will be initiated after 10 minutes of successful planning.</span>';
                        if(flag){
                            var alert1 = ($filter('translate')('normal.alert_review'))
                            var alert2 = ($filter('translate')('normal.alert_review_initiate'))
                            var str = '<span style="font-style: normal;">'+alert1+'</span> <br><br><span><b>NOTE :</b>&nbsp;'+alert2+'</span>'
                        }
                        else{
                            var alert1 = ($filter('translate')('normal.alert_task'))
                            var alert2 = ($filter('translate')('normal.alert_task_initiate'))
                            var str = '<span style="font-style: normal;">'+alert1+'</span> <br><br><span><b>NOTE :</b>&nbsp;'+alert2+'</span>'
                        }


                        var modalInstance = $uibModal.open({
                            animation: true,
                            backdrop: 'static',
                            keyboard: false,
                            scope: $scope,
                            openedClass: 'right-panel-modal modal-open adv-search-model',
                            templateUrl: 'confirm-dialog.html',
                            controller: function ($uibModalInstance, $scope) {
                                $scope.val_data = $sce.trustAsHtml(str);
                                 console.log($scope.val_data);
                                $scope.saidOk = function(){
                                    $scope.serviceCall(data);
                                    $scope.cancel();
                                }
                                $scope.cancel = function () {
                                    $uibModalInstance.close();
                                };
                            }
                        });
                       
                    }else {
                        $scope.serviceCall(data);
                    }                        
                }
                $scope.serviceCall = function(data){
                    calenderService.addReview(data).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            $scope.cancel();
                            $scope.callServer($scope.tableStateRef);
                        } else $rootScope.toast('Error', result.error.message);
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
    }
   
   
    $scope.displayChart = function () {
        angular.element('#chart').removeClass('hide');
        angular.element('#chart').removeAttr('ng-hide');
        $scope.showChart = true;
        return true;
    }
    $scope.hideChart = function (flag) {
        if(flag) $rootScope.chatLables = $scope.myDataSource.chart;
        $timeout(function () {
            angular.element('#chart').addClass('hide');
        });
        $scope.showChart = false;
    }
   
    $scope.projectsList = [];
    $scope.itemsByPage = 10;
  
    // $scope.callServer = function (tableState){
    //     $scope.filtersData = {};
    //     $rootScope.module = '';
    //     $rootScope.displayName = '';   
    //     $rootScope.icon = "Projects";
    //     $rootScope.class ="project-logo"; 
    //     $rootScope.breadcrumbcolor='project-breadcrumb-color' ;      
    //     $scope.isLoading = true;
    //     var pagination = tableState.pagination;
    //     tableState.customer_id = $scope.user1.customer_id;
    //     tableState.id_user  = $scope.user1.id_user;
    //     tableState.user_role_id  = $scope.user1.user_role_id;
    //     tableState.can_access  = $scope.can_access;
       
    //     // setTimeout(function(){
    //         tableState.date_period  = $scope.date_period;
    //         tableState.date_field  = $scope.date_field;
    //         tableState.business_unit_id = angular.copy($scope.business_unit_id); 
            
           
           
    //         if($scope.created_this_month && $scope.created_this_month !=null){
    //             tableState.created_this_month = $scope.created_this_month;
    //         }else {
    //             delete tableState.created_this_month;
    //             $scope.created_this_month=null;
    //         }
    //         if($scope.ending_this_month && $scope.ending_this_month !=null){
    //             tableState.ending_this_month = $scope.ending_this_month;
    //         }else {
    //             delete tableState.ending_this_month;
    //             $scope.ending_this_month=null;
    //         }
    //         if($scope.automatic_prolongation && $scope.automatic_prolongation !=null){
    //             tableState.automatic_prolongation = $scope.automatic_prolongation;
    //         }else{
    //             delete tableState.automatic_prolongation;
    //             $scope.automatic_prolongation = null;
    //         }
    //         tableState.overview=true;
    //         if($scope.resetPagination){
    //             tableState.pagination={};
    //             tableState.pagination.start='0';
    //             tableState.pagination.number='10';
    //         }
    //         if($scope.created_date ==null || $scope.created_date == undefined || $scope.created_date==''){
    //             delete tableState.date_period;
    //             delete tableState.date_field;
    //             $scope.date_period = '';
    //             $scope.date_field = '';
    //         } else{
    //             tableState.created_date = dateFilter($scope.created_date,'yyyy-MM-dd');
    //         }
    //         $scope.tableStateRef = tableState;
    //         projectService.projectList(tableState).then (function(result){
    //             $scope.projectsList =[];
    //             $scope.projectsList = result.data.data;
    //             $scope.emptyTable=false;
    //             $scope.displayCount = $rootScope.userPagination;
    //             $scope.totalRecords = result.data.total_records;
    //             tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
    //             $scope.isLoading = false;
    //             $scope.getBUList();
    //             $scope.resetPagination=false;
    //             $scope.memorizeFilters(tableState);
    //             if(result.data.total_records < 1)
    //                 $scope.emptyTable=true;
    //         });
    //     // },2000);
    // }
    $scope.callServer = function (tableState){
        $scope.filtersData = {};
        $rootScope.module = '';
        $rootScope.displayName = '';
        $rootScope.icon = "Projects";
        $rootScope.class ="project-logo"; 
        $rootScope.breadcrumbcolor='project-breadcrumb-color';       
        $scope.isLoading = true;
        var pagination = tableState.pagination;
        tableState.customer_id = $scope.user1.customer_id;
        tableState.id_user  = $scope.user1.id_user;
        tableState.user_role_id  = $scope.user1.user_role_id;
        tableState.can_access  = $scope.can_access;
        tableState.is_advance_filter=1;
        
        if( $stateParams.status==undefined && 
            $stateParams.end_date==undefined &&
            $stateParams.this_month==undefined &&
            $stateParams.end_month==undefined &&
            $stateParams.end_date_180==undefined){
        }else {
            
            if($stateParams.end_date){
                if($scope.created_date ==null || 
                        $scope.created_date == undefined || 
                        $scope.created_date=='') {
                    $scope.end_date_lessthan_90 = $stateParams.end_date;
                    $scope.date_field = "contract_end_date";
                    $scope.date_period = "<=";
                    $scope.created_date = moment().utcOffset(0, false).toDate();
                    $scope.created_date.setDate( $scope.created_date.getDate() + 90 );
                }                
            }
            if($stateParams.this_month){
                if($scope.created_date ==null || 
                        $scope.created_date == undefined || 
                        $scope.created_date=='') {
                    $scope.created_this_month = $stateParams.this_month;
                    $scope.date_field = "created_on";
                    $scope.date_period = ">=";
                    $scope.created_date = moment().utcOffset(0, false).toDate();
                    var date1= moment().utcOffset(0, false).toDate();
                    var firstDay = moment(date1.getFullYear(), date1.getMonth(), 1).utcOffset(0, false).toDate();
                    $scope.created_date = moment(firstDay).utcOffset(0, false).toDate();
                }                
            }        
            if($stateParams.end_month){
                if($scope.created_date ==null || 
                        $scope.created_date == undefined || 
                        $scope.created_date=='') {
                    $scope.ending_this_month = $stateParams.end_month;
                    $scope.date_field = 'contract_end_date';
                    $scope.date_period = '<=';
                    var date2 = moment().utcOffset(0, false).toDate();
                    var lastDay = moment(date2.getFullYear(), date2.getMonth() + 1, 0).utcOffset(0, false).toDate();
                    $scope.created_date = moment(lastDay).utcOffset(0, false).toDate();
                }                
            }   
            
            if($stateParams.end_date_180){
                if($scope.created_date ==null || 
                    $scope.created_date == undefined || 
                    $scope.created_date=='') {
                $scope.end_date_lessthan_180 = $stateParams.end_date_180;
                $scope.date_field = "contract_end_date";
                $scope.date_period = "<=";
                $scope.created_date = moment().utcOffset(0, false).toDate();
                $scope.created_date.setDate( $scope.created_date.getDate() + 180 );
              }       
            }
            
        }
        // setTimeout(function(){
            tableState.date_period  = $scope.date_period;
            tableState.date_field  = $scope.date_field;
            tableState.business_unit_id = angular.copy($scope.business_unit_id); 
            if($scope.relationship_category_id && $scope.relationship_category_id !=null){
                tableState.relationship_category_id = $scope.relationship_category_id;
            }else delete tableState.relationship_category_id;
           
            if($scope.end_date_lessthan_90 && $scope.end_date_lessthan_90 != null){
                tableState.end_date_lessthan_90  = $scope.end_date_lessthan_90;
            }else{
                delete tableState.end_date_lessthan_90;
                $scope.end_date_lessthan_90 = '';
            }
            if($scope.end_date_lessthan_180 && $scope.end_date_lessthan_180 != null){
                tableState.end_date_lessthan_180  = $scope.end_date_lessthan_180;
            }else{
                delete tableState.end_date_lessthan_180;
                $scope.end_date_lessthan_180 = '';
            }
          
            if($scope.created_this_month && $scope.created_this_month !=null){
                tableState.created_this_month = $scope.created_this_month;
            }else {
                delete tableState.created_this_month;
                $scope.created_this_month=null;
            }
            if($scope.ending_this_month && $scope.ending_this_month !=null){
                tableState.ending_this_month = $scope.ending_this_month;
            }else {
                delete tableState.ending_this_month;
                $scope.ending_this_month=null;
            }
           
            tableState.overview=true;
            if($scope.resetPagination){
                tableState.pagination={};
                tableState.pagination.start='0';
                tableState.pagination.number='10';
            }
            if($scope.created_date ==null || $scope.created_date == undefined || $scope.created_date==''){
                delete tableState.date_period;
                delete tableState.date_field;
                $scope.date_period = '';
                $scope.date_field = '';
            } else{
                tableState.created_date = dateFilter($scope.created_date,'yyyy-MM-dd');
            }
            if(tableState.advancedsearch_get){}
            else tableState.advancedsearch_get={};
           
            $scope.tableStateRef = tableState;
            projectService.projectList(tableState).then (function(result){
                $scope.projectsList =[];
                $scope.projectsList = result.data.data;
                $scope.emptyTable=false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
               
                $scope.resetPagination=false;
                $scope.memorizeFilters(tableState);
                if(result.data.total_records < 1)
                    $scope.emptyTable=true;
            });
        // },2000);
    }
    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.resetPagination=true;
                $scope.callServer($scope.tableStateRef);
            }                
        });
    }

    $scope.projectworkflowData=function(data){
        $scope.projectWorkflowInfo='';
        var param ={};
        param.project_id = data.project_id;
        projectService.projectworkflowList(param).then(function(result){
            $scope.projectWorkflowInfo=result.data;
        });
    }

    $scope.filterDateType = function(val) {
        $scope.resetPagination=true;
        $scope.date_field = val;
        $stateParams.end_date = undefined ;
        $stateParams.this_month = undefined ;
        $stateParams.end_month = undefined ;
        if($scope.date_period)$scope.tableStateRef.date_period = $scope.date_period;
        else {
            $scope.tableStateRef.date_period = '=';
            $scope.date_period = '=';
        }
        if(!$scope.created_date) {
            angular.element('#created_date').addClass('req-filter');
        }
        $scope.tableStateRef.date_field = val;
        if($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_date){
            $scope.callServer($scope.tableStateRef);  
        }
    }
    $scope.filterDatePeriod = function(val) {
        $scope.resetPagination=true;
        $scope.date_period = val;
        $stateParams.end_date = undefined ;
        $stateParams.this_month = undefined ;
        $stateParams.end_month = undefined ;
        if($scope.date_field)$scope.tableStateRef.date_field = $scope.date_field;
        else {
            $scope.tableStateRef.date_field = 'created_on';
            $scope.date_field = 'created_on';
        }
        if(!$scope.created_date) {
            angular.element('#created_date').addClass('req-filter');
        }
        $scope.tableStateRef.date_period = val;
        if($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_date){
            $scope.callServer($scope.tableStateRef);
        }
    }
    
    $scope.clear = function() {
        $scope.created_date = '';
        $scope.date_period = '';
        $scope.date_field = '';
        $scope.business_unit_id = 'All';
        $scope.automatic_prolongation='';
        angular.element('#created_date').removeClass('req-filter');
        $localStorage.curUser.data.filters = {};
        $state.transitionTo("app.projects.all-projects",{reload: true, inherit: false});
    };

    $scope.selectDate = function(date) {
        var d = null;
        if(date){
            var element = angular.element('#created_date');
            element.removeClass("req-filter");
            element.addClass('active-filter');
            d= dateFilter(date,'yyyy-MM-dd');
            if($scope.date_field) 
                $scope.tableStateRef.date_field = $scope.date_field;
            else {
                $scope.date_field='created_on';
                $scope.tableStateRef.date_field = $scope.date_field;
            }
            if($scope.date_period) 
                $scope.tableStateRef.date_period = $scope.date_period;
            else {
                $scope.date_period='=';
                $scope.tableStateRef.date_period = $scope.date_period;
            }
        }
        $scope.tableStateRef.created_date = d;
        $scope.resetPagination=true;
        console.log("selectDate---------");
        $scope.callServer($scope.tableStateRef);
    }
   
    
    $scope.deleteProject = function (row) {
        var r=confirm($filter('translate')('general.alert_continue'));
        if(r==true){
            var params = {};
            params.contract_id = row.id_contract;
            params.user_role_id = $scope.user1.user_role_id;
            params.id_user = $scope.user1.id_user;
            contractService.delete(params).then(function (result) {
                if(result.status){
                    var obj = {};
                    obj.action_name = 'Delete';
                    obj.action_description = 'project delete $$('+result.data.file_name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = location.href;
                    if(AuthService.getFields().data.parent){
                        obj.user_id = AuthService.getFields().data.parent.id_user;
                        obj.acting_user_id = AuthService.getFields().data.data.id_user;
                    }
                    else obj.user_id = AuthService.getFields().data.data.id_user;
                    if(AuthService.getFields().access_token != undefined){
                        var s = AuthService.getFields().access_token.split(' ');
                        obj.access_token = s[1];
                    }
                    else obj.access_token = '';
                    $rootScope.toast('Success',result.message);
                    setTimeout(function(){
                        $scope.callServer($scope.tableStateRef);
                    },300);
                }
            });
        }
        
    }
  
  
    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        showWeeks: false
    };
    
  
    $scope.memorizeFilters = function(data){
       
    }

    //written by ashok
    $scope.getProjectByAccess=function(val){
        $scope.resetPagination=true;
        $scope.can_access = val;
        $scope.tableStateRef.can_access = val;
        if($scope.tableStateRef.can_access){
            $scope.callServer($scope.tableStateRef);  
        }else{
            delete $scope.tableStateRef.can_access;
            $scope.callServer($scope.tableStateRef);  
        } 
    }
     
    $scope.goToProjectReview = function(type,row){
        // console.log('row info',row);
        // console.log('type info',type);
        if(type.initiated){
            $state.go('app.projects.project-task',{name:row.contract_name,
                            id:encode(row.id_contract),
                            rId:encode(type.id_contract_review),
                            wId:encode(type.id_contract_workflow),
                            type:'workflow'
            })
        }
        else{
            $state.go('app.projects.view',
            {name:row.contract_name,id:encode(row.id_contract),
                wId:encode(type.id_contract_workflow),type:'workflow'}, { reload: true, inherit: false });
        }
     }
   
})
.controller('projectViewCtrl', function($sce,$timeout,$scope, $rootScope,$filter, $state, $stateParams,projectService,contractService, decode, encode, customerService,attachmentService,tagService,businessUnitService,calenderService, Upload,$location, $uibModal, userService, AuthService,dateFilter,providerService,masterService,templateService,moduleService,$localStorage){
    $scope.can_access = 1;     //written by ashok  
    $rootScope.module = 'Project Details';
    $rootScope.icon = "Projects";
    $rootScope.class ="project-logo";
    $rootScope.breadcrumbcolor='project-breadcrumb-color' ; 
    $scope.currencyList = [];
    $scope.displayCount = $rootScope.userPagination;
    $rootScope.displayName = $stateParams.name;
    $scope.isSubLoading = true;
    $scope.dynamicPopover = { templateUrl: 'myPopover.html' };
    var parentPage = $state.current.url.split("/")[1];
    var obj = {};
    obj.action_name = 'view';
    obj.action_description = 'view Projectt$$('+$stateParams.name+')';
    obj.module_type = $state.current.activeLink;
    obj.action_url = $location.$$absUrl;
    $rootScope.confirmNavigationForSubmit(obj);
    $scope.spendMgmtGraph ={};
    $scope.spendMgmtGraph.graph ={};
    $scope.spendLineGraph = {};



    $scope.detailsPage = function(row){
        console.log("yes",row);
        if(row.is_workflow=='1')
                 $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.contract_id),wId:encode(row.contract_workflow_id),type:'workflow'});
             else
                 $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.contract_id),type:'review'});
    }

    if($stateParams.wId)$scope.workflowId = decode($stateParams.wId);
    if($stateParams.type)$scope.isWorkflow = ($stateParams.type =='workflow')?'1':'0';
    if($stateParams.id){
        $scope.init = function (){
            $scope.project_id = decode($stateParams.id);
            $scope.project_id_encoded = $stateParams.id;
            var params = {};
            params.project_id  = $scope.project_id;
            params.id_user  = $scope.user1.id_user;
            params.user_role_id  = $scope.user1.user_role_id;
            params.id_contract_workflow  = $scope.workflowId;
            params.is_workflow  = $scope.isWorkflow;
            params.customer_id  = $scope.user1.customer_id;
            $scope.loading = false;
            $scope.tagStatus = false;
            projectService.projectInfo(params).then (function(result){
                //if(result.data.length>0){
                    if(result.data[0].contract_end_date=="1970-01-01")
                        result.data[0].contract_end_date='';
                     $scope.projectInfo = result.data[0];
                     $scope.projectInfo_id=$scope.projectInfo.id_contract;
                     $scope.data = result.data[0];
                    $scope.reviewWorkflowInfo = {};
                    $scope.reviewWorkflowInfo = result.data[0].project_task;
                    $scope.validation_status = result.data[0].validation_status;
                    $scope.ready_for_validation = result.ready_for_validation;
                    $scope.projectInfo.id_contract_review = result.data[0].id_contract_review;
                    if($scope.reviewWorkflowInfo.is_workflow==0){
                        var str = '<div><div class="text-left" style="text-align:left;">Recurrence : '+$scope.reviewWorkflowInfo.recurrenc+' </div><div style="text-align:left;"> Recurrence till : '+ dateFilter($scope.reviewWorkflowInfo.recurrence_till,'MMM dd,yyyy')+'</div></div>';
                        $scope.htmlTooltip = $sce.trustAsHtml(str);
                    }

                    
            
                    $scope.loading = true;
                    $scope.review_access = true;                           
                    if($scope.contractInfo.reaaer != "itako"){$scope.review_access = false;}
                //}else{
                    //$state.go('app.contract.contract-overview');
                //}
            });
            $timeout(function () {              
            },200);
            $scope.connectedContracts =[];
            $scope.connectedContractsList =function(){
                    projectService.getConnectedContracts({'customer_id':$scope.user1.customer_id,'project_id':decode($stateParams.id)}).then(function(result){
                        if(result.status){
                            $scope.connectedContracts = result.data;
                        }
                 });
                        
            }
           $scope.connectedContractsList();
        }
        $scope.req={};
        $scope.req.status=0;
        $scope.getProviderusers = function (tableState){
            var pagination = tableState.pagination;
               setTimeout(function(){
                   $scope.tableStateRef2 = tableState;
                   $scope.isSubLoading = true;
                   tableState.project_id = decode($stateParams.id);
                   tableState.user_type='external';
                   tableState.customer_id = $scope.user1.customer_id;
                   tableState.id_user = $scope.user1.id_user;
                   tableState.user_role_id = $scope.user1.user_role_id;
                   customerService.getUserList(tableState).then (function(result){
                       $scope.usersList = result.data.data;
                       $scope.usersListCount = result.data.total_records;
                       $scope.emptyTable=false;
                       $scope.displayCount = $rootScope.userPagination;
                       $scope.totalRecords1 = result.data.total_records;
                       tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                       $scope.isSubLoading = false;
                       if(result.data.total_records < 1)
                           $scope.emptyTable=true;
                   })
               },700);
           }

        $scope.defaultPages = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.getProviderusers($scope.tableStateRef2);
                }                
            });
        }


        $scope.getExtUsersByStatus= function(val){
            $scope.tableStateRef2.status=val;
            $scope.tableStateRef2.pagination.start='0';
            $scope.tableStateRef2.pagination.number='10';
            $scope.getProviderusers($scope.tableStateRef2);
        }
        $scope.getEventFeed = function (tableState){
             var pagination = tableState.pagination;
                setTimeout(function(){
                    $scope.tableStateRefEvent = tableState;
                    $scope.eventLoading = true;
                    tableState.reference_type = 'project';
                    tableState.reference_id = decode($stateParams.id);
                    projectService.eventFeedList(tableState).then (function(result){
                        $scope.eventList = result.data;
                        $scope.eventListCount = result.total_records;
                        $scope.eventEmptyTable=false;
                        $scope.displayCount = $rootScope.userPagination;
                        $scope.totalRecordsEvent = result.total_records;
                        console.log("io",$scope.totalRecordsEvent);
                        tableState.pagination.numberOfPages =  Math.ceil(result.total_records / $rootScope.userPagination);
                        $scope.eventLoading = false;
                        if(result.total_records < 1)
                            $scope.eventEmptyTable=true;
                    })
                },700);
            }
         $scope.defaultPages = function(val){
             userService.userPageCount({'display_rec_count':val}).then(function (result){
                 if(result.status){
                     $rootScope.userPagination = val;
                     $scope.getEventFeed($scope.tableStateRefEvent);
                 }                
             });
         }

         $scope.deleteEventFeed = function (id) {
            console.log(id);
            var r = confirm($filter('translate')('general.alert_continue'));
            if (r == true) {
                var params = {};
                params.id_event_feed = id.id_event_feed;
                projectService.deleteEventFeed(params).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                        $scope.getEventFeed($scope.tableStateRefEvent);
                    }
                    else {
                        $rootScope.toast('Error', result.error, 'error');
                    }
                })
            }

        }
        
        $scope.createEventFeed = function (row) {
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/bussiness_unit/create-event-feed.html',
                controller: function ($uibModalInstance, $scope,item) {
                    $scope.update = false;
                    $scope.title = 'controller.project_event';
                    $scope.bottom = 'general.save';
                    $scope.file = {};
                    $scope.eventAdd={};
                    $scope.contractLinks=[];
                    $scope.contractLink={};  
                    // $scope.eventAdd={};    
                    projectService.eventResponsibleUsers().then (function(result){
                        $scope.eventResponsibleUsers=result.data;
                    });

                    if(item){
                        console.log("item is",row);      
                        $scope.title = 'Project Event';
                        $scope.bottom = 'general.update';    
                        $scope.eventList=function(){             
                        projectService.eventFeedList({'id_event_feed':item.id_event_feed}).then (function(result){
                            $scope.eventAdd=result.data[0];
                            $scope.eventattachment=result.data[0].attachment;
                            if($scope.eventAdd.date) $scope.eventAdd.date = moment($scope.eventAdd.date).utcOffset(0, false).toDate();

                            $scope.options = {
                                minDate: moment().utcOffset(0, false).toDate(),
                                showWeeks: false
                            };
                            $scope.options2 = angular.copy($scope.options);
                            });
                    }
                    $scope.eventList();

                        $scope.deleteAttachmentEvent = function(id,name,documents){
                            var r=confirm($filter('translate')('general.alert_continue'));
                            $scope.deleConfirm = r;
                            if(r==true){
                                var params = {};
                                params.id_document = id;
                                attachmentService.deleteAttachments(params).then (function(result){
                                    if(result.status){
                                        $rootScope.toast('Success',result.message);
                                        projectService.eventFeedList({'id_event_feed':item.id_event_feed}).then (function(result){
                                            $scope.eventAdd.attchLinks=result.data[0].attachment;
                                        })      
                                        $scope.eventAdd.attachment= $scope.eventAdd.attchLinks;         
                                        var obj = {};
                                        obj.action_name = 'delete';
                                        obj.action_description = 'delete$$Attachment$$('+name+')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);
                                        $scope.init();
                                    }else{$rootScope.toast('Error',result.error,'error');}
                                })
                            }
                        }

                        projectService.eventResponsibleUsers().then (function(result){
                            $scope.eventResponsibleUsers=result.data;
                        });


                        $scope.addEventFeed=function(eventData){
                            if (eventData.date!=null)
                            eventData.date = dateFilter(eventData.date, 'yyyy-MM-dd');
                            else eventData.date='';
                             var eventAttachment = {};
                             eventAttachment.attachment_delete = [];
                             if ($scope.file.delete) {
                                 angular.forEach($scope.file.delete, function (i, o) {
                                     var obj = {};
                                     obj.id_document = i.id_document;
                                     eventAttachment.attachment_delete.push(obj);
                                 });
                             }
                            Upload.upload({
                                url: API_URL + 'Project/eventFeed',
                                data: {
                                    'file': $scope.file.attachment,
                                    'reference_type':'project',
                                    'reference_id':$scope.projectInfo.id_contract,
                                    'responsible_user_id':eventData.responsible_user_id,
                                    'subject':eventData.subject,
                                    'id_event_feed':item.id_event_feed,       
                                    'stakeholders':eventData.stakeholders!=null ? eventData.stakeholders:'',
                                    'date':eventData.date,
                                    'type':eventData.type!=null ? eventData.type:'',
                                    'links':$scope.contractLinks,
                                    'description':eventData.description ? eventData.description:''
                                    }
                            }).then(function (resp) {
                                if (resp.data.status) {
                                    $scope.getEventFeed($scope.tableStateRefEvent);
                                    $rootScope.toast('Success', resp.data.message);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', resp.data.error);
                                } 
                        });
                        }
                        
                    }else{
                        $scope.addEventFeed=function(eventData){
                            console.log("data",eventData);
                            var params={};                     
                            if (eventData.date!=null)
                            eventData.date = dateFilter(eventData.date, 'yyyy-MM-dd');
                            else eventData.date='';
                             var eventAttachment = {};
                             eventAttachment.attachment_delete = [];
                             if ($scope.file.delete) {
                                 angular.forEach($scope.file.delete, function (i, o) {
                                     var obj = {};
                                     obj.id_document = i.id_document;
                                     eventAttachment.attachment_delete.push(obj);
                                 });
                             }
                            Upload.upload({
                                url: API_URL + 'Project/eventFeed',
                                data: {
                                    'file': $scope.file.attachment,
                                    'reference_type':'project',
                                    'reference_id':$scope.projectInfo.id_contract,
                                    'responsible_user_id':eventData.responsible_user_id,
                                    'subject':eventData.subject,
                                    'stakeholders':eventData.stakeholders,
                                    'date':eventData.date,
                                    'type':eventData.type,
                                    'links':$scope.contractLinks,
                                    'description':eventData.description
                                }
                            }).then(function (resp) {
                                if (resp.data.status) {
                                    $scope.getEventFeed($scope.tableStateRefEvent);
                                    $rootScope.toast('Success', resp.data.message);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', resp.data.error);
                                } 
                        });
                        //     console.log("data",data);
                        //     $scope.data={};
                        //     var params={};
                        //     params=data;
                        //     params.reference_type='project';
                        //     params.reference_id=$scope.projectInfo.id_contract;
                        //     if (data.date)
                        //     params.date = dateFilter(data.date, 'yyyy-MM-dd');
                        //     params.responsible_user_id='U2FsdGVkX19UaGVAMTIzNOxxyN8lPAgvJTUG5qVHIQc';
                        //     // params.attachment_delete = [];
                        //     // if($scope.file.delete){
                        //     //     angular.forEach($scope.file.delete, function(i,o){
                        //     //         var obj = {};
                        //     //         obj.id_document = i.id_document;
                        //     //         params.attachment_delete.push(obj) ;
                        //     //     });
                        //     // }
                        //     // params.links_delete=$scope.links_delete;
                        //     // params.links=$scope.contractLinks;      
                        //     // params.file=data.file.attachment;          
                        //     projectService.eventFeedProject(params).then (function(result){
                        //         if (result.status) {
                        //             $scope.event=result;
                        //             $scope.data.file=[];
                        //             console.log("js",$scope.event);
                        //             $scope.getEventFeed($scope.tableStateRefEvent);
                        //             $scope.cancel();
                        //             $rootScope.toast('Success', result.message);
                        //         } else {
                        //             $rootScope.toast('Error', result.error, 'error');
    
                        //         }
                        //     });
    
                        }
                    }
                    
                   $scope.cancel = function () {
                    $scope.getEventFeed($scope.tableStateRefEvent);
                        $uibModalInstance.close();
                    };

                    $scope.verifyLink = function(data){
                        if(data !={}){
                            $scope.contractLinks.push(data);
                            $scope.contractLink={};
                        }
                    }    
                    $scope.removeLink = function(index){
                        var r=confirm($filter('translate')('general.alert_continue'));
                        if(r==true){
                            $scope.contractLinks.splice(index, 1);
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
        }

        $scope.eventAttachment = function(row) {
            $scope.contractLinks = [];
            $scope.contractLink={};
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/contracts/attachments-create-list.html',
                controller: function ($uibModalInstance, $scope, item) {
                    
                    if(item){
                        $scope.bottom = 'general.update';
                        $scope.title='controller.add_attachments_links'; 
                        $scope.file = {};
                        $scope.eventAdd={};
                        $scope.contractLinks=[];
                        $scope.contractLink={};  
    
                        $scope.eventList=function(){
                        projectService.eventFeedList({'id_event_feed':item.id_event_feed}).then (function(result){
                            $scope.eventAdd=result.data[0];
                        });
                    }
                    $scope.eventList();

                        $scope.verifyLink = function(data){
                            if(data !={}){
                                $scope.contractLinks.push(data);
                                $scope.contractLink={};
                            }
                        }    
                        $scope.removeLink = function(index){
                            var r=confirm($filter('translate')('general.alert_continue'));
                            if(r==true){
                                $scope.contractLinks.splice(index, 1);
                            }                    
                        }

                        $scope.deleteAttachmentEvent = function(id,name){
                            var r=confirm($filter('translate')('general.alert_continue'));
                            $scope.deleConfirm = r;
                            if(r==true){
                                var params = {};
                                params.id_document = id;
                                attachmentService.deleteAttachments(params).then (function(result){
                                    if(result.status){
                                        $rootScope.toast('Success',result.message);
                                        console.log("abcd");
                                        $scope.eventList();
                                        $scope.getEventFeed($scope.tableStateRefEvent);
                                        var obj = {};
                                        obj.action_name = 'delete';
                                        obj.action_description = 'delete$$Attachment$$('+name+')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);
                                        $scope.init();
                                    }else{$rootScope.toast('Error',result.error,'error');}
                                })
                            }
                        }


                        $scope.addEventAttachemts=function(data){
                            var file = data;
                            if(file){
                                Upload.upload({
                                    url: API_URL+'Document/add',
                                    data:{
                                        file:file,
                                        customer_id: $scope.user1.customer_id,
                                        module_id: decode($stateParams.id),
                                        module_type: 'project',
                                        reference_id: item.id_event_feed,
                                        reference_type: 'event_feed',
                                        document_type:0,
                                        uploaded_by: $scope.user1.id_user
                                    }
                                }).then(function (resp) {
                                    if(resp.data.status){
                                        $rootScope.toast('Success',resp.data.message);
                                        var obj = {};
                                        obj.action_name = 'upload';
                                        obj.action_description = 'upload$$module$$question$$attachement$$('+$stateParams.mName+')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);                                    
                                        $scope.cancel();
                                        $scope.getEventFeed($scope.tableStateRefEvent);
                                    }
                                    else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                                }, function (resp) {
                                    $rootScope.toast('Error',resp.data.error,'error');
                                }, function (evt) {
                                    $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                                });
    
                            }else{
                                $rootScope.toast('Error','invalid format','image-error');
                            }
                        }
                        $scope.uploadLinksEvent = function (contractLinks) {
                            var file = contractLinks;
                            if(contractLinks){
                                Upload.upload({
                                    url: API_URL+'Document/add',
                                    data:{
                                        file:contractLinks,
                                        customer_id: $scope.user1.customer_id,
                                        module_id: decode($stateParams.id),
                                        module_type: 'project',
                                        reference_id: item.id_event_feed,
                                        document_type:1,
                                        reference_type: 'event_feed',
                                        uploaded_by: $scope.user1.id_user
                                    }
                                }).then(function (resp) {
                                    if(resp.data.status){
                                        $rootScope.toast('Success',resp.data.message);
                                        var obj = {};
                                        obj.action_name = 'upload';
                                        obj.action_description = 'upload$$module$$question$$link$$('+$stateParams.mName+')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);
                                        $scope.cancel();
                                        $scope.getEventFeed($scope.tableStateRefEvent);
                                    }
                                    else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                                }, function (resp) {
                                        $rootScope.toast('Error',resp.data.error,'error');
                                }, function (evt) {
                                    $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                                });
                            }else{
                                $rootScope.toast('Error','No link selected','image-error');
                            }
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

        $scope.showForm = function(row){
                $scope.selectedRow = row;
                 var modalInstance = $uibModal.open({
                     animation: true,
                     backdrop: 'static',
                     keyboard: false,
                     scope: $scope,
                     openedClass: 'right-panel-modal modal-open',
                     templateUrl: 'edit-provider-user.html',
                     controller: function ($uibModalInstance, $scope) {
                        $scope.bottom = 'general.update';
                       
                        providerService.list({'customer_id': $scope.user1.customer_id,'status':1,'all_providers':true,'project_id':decode($stateParams.id)}).then(function(result){
                            $scope.providers = result.data.data;
                        });
                      
                        $scope.countriesList = {};
                        masterService.getCountiresList().then(function(result){
                            if(result.status){
                                $scope.countriesList = result.data;
                            }
                        })

                        $scope.disableField = true;
                        $scope.getValue = function(val){
                        console.log("userrelation",val);
                        if(val=='other') {
                            $scope.disableField = false;
                            }
                        else{
                        $scope.disableField = true;

                            }
                        }
                      

                        $scope.userInfo = function(){
                            var params ={};
                            params.user_id = row.id_user;
                            params.customer_id = $scope.user1.customer_id;
                            customerService.getUserById(params).then(function(result){
                                $scope.customUser = result.data;
                                
                                if($scope.customUser.gender=='other'){
                                    $scope.disableField = false;
                                }
                                if(result.data.user_role_id){
                                    for(var a in $scope.userRoles){
                                        if($scope.userRoles[a].id_user_role == result.data.user_role_id)
                                            $scope.customUser.user_role_id = $scope.userRoles[a];
                                    }
                                }
                            });
                        }
                        $scope.userInfo();


                        $scope.resetPassword = function(userPwd){
                            var params ={};
                            params.customer_id = $scope.user1.customer_id;
                            params.project_id = decode($stateParams.id);
                            params.user_id = $scope.user.id_user;
                            params.password = userPwd.npassword;
                            params.cpassword = userPwd.cpassword;
                            params.user_type = 'external';
                            customerService.resetPassword(params).then (function(result){
                                if(result.status){
                                    $rootScope.toast('Success',result.message);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$user$$password$$('+$scope.customUser.first_name+' '+$scope.customUser.last_name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.cancel();
                                }else{
                                    $rootScope.toast('Error',result.error,'error',$scope.user);
                                }
                            });
                        }
                        $scope.addUser =  function (customUser){
                            var params ={};
                            params = customUser;
                            params.created_by = $scope.user.id_user;
                            params.customer_id = $scope.user1.customer_id;
                            params.project_id = decode($stateParams.id);
                            params.user_type = 'external';
                            if(customUser.is_manual == 0){
                                delete customUser.password;
                                customUser.is_manual_password = 0;
                            }else{
                                customUser.is_manual_password = 1;
                            }
                           
                            customerService.postUser(params).then(function(result){
                                if(result.status){
                                    $rootScope.toast('Success',result.message);
                                    var obj = {};
                                    if(customUser.id_user>0)$scope.action = 'update';
                                    else $scope.action = 'create';
                                    obj.action_name = $scope.action;
                                    obj.action_description = $scope.action+'$$user$$('+customUser.first_name+' '+customUser.last_name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.cancel();
                                    $scope.getProviderusers($scope.tableStateRef2);
                                }else{
                                    $rootScope.toast('Error',result.error,'error',$scope.user);
                                }
                
                            });
                        }
                         $scope.cancel = function () {
                             $uibModalInstance.close();
                         };
                     },
                     resolve: {}
                 });
                 modalInstance.result.then(function ($data) {
                 }, function () {
                 });
               
        }

        $scope.addProviderUsers = function(row){
            $scope.selectedRow = row;
             var modalInstance = $uibModal.open({
                 animation: true,
                 backdrop: 'static',
                 keyboard: false,
                 scope: $scope,
                 openedClass: 'right-panel-modal modal-open',
                 templateUrl: 'create-provider-user.html',
                 controller: function ($uibModalInstance, $scope) {
                    $scope.bottom = 'general.save';
                   
                    providerService.list({'customer_id': $scope.user1.customer_id,'status':1,'all_providers':true,'project_id':decode($stateParams.id)}).then(function(result){
                        $scope.providers = result.data.data;
                    });

                    $scope.countriesList = {};
                    masterService.getCountiresList().then(function(result){
                        if(result.status){
                            $scope.countriesList = result.data;
                        }
                    })

                    $scope.disableField = true;
                    $scope.getValue = function(val){
                        console.log("userrelation",val);
                        if(val=='other') {
                            $scope.disableField = false;
                        }
                    else{
                    $scope.disableField = true;

                        }
                    }
                  
                  
                    $scope.addUser =  function (customUser){
                        var params ={};
                        params = customUser;
                        params.created_by = $scope.user.id_user;
                        params.customer_id = $scope.user1.customer_id;
                        params.project_id = decode($stateParams.id);
                        params.user_type = 'external';
                        if(customUser.is_manual == 0){
                            delete customUser.password;
                            customUser.is_manual_password = 0;
                        }else{
                            customUser.is_manual_password = 1;
                        }
                       
                        customerService.postUser(params).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success',result.message);
                                var obj = {};
                                if(customUser.id_user>0)$scope.action = 'update';
                                else $scope.action = 'create';
                                obj.action_name = $scope.action;
                                obj.action_description = $scope.action+'$$user$$('+customUser.first_name+' '+customUser.last_name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                //$state.go('app.external-user.ext-list');
                                $scope.cancel();
                                $scope.getProviderusers($scope.tableStateRef2);
                            }else{
                                $rootScope.toast('Error',result.error,'error',$scope.user);
                            }
            
                        });
                    }
                     $scope.cancel = function () {
                         $uibModalInstance.close();
                     };
                 },
                 resolve: {}
             });
             modalInstance.result.then(function ($data) {
             }, function () {
             });
         }

        
        $scope.goToCurrentContractDetails = function(row){
            if(row.is_workflow=='1')
                $state.go('app.contract.view',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
            else
                $state.go('app.contract.view',{name:row.contract_name,id:encode(row.id_contract),type:'review'});
        }


        $scope.goToProviderDetails = function(row){
            $state.go('app.provider.view',{name:row.provider_name,id:encode(row.id_provider)});
        }
     

        $scope.manageConnectedContracts = function () {
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'manage-connected-contracts.html',
                controller: function ($uibModalInstance, $scope) {
                    $scope.update = false;
                    $scope.showInnerDiv = false;
                    $scope.title = 'general.update';
                    $scope.bottom = 'general.save';
                    $scope.field = '';
                    $scope.isEdit = false;
                    $scope.contractParters={};
                    $scope.businessUnitList =[];
                    $scope.usersList =[];
                    //this service is for geeting connected contracts list
                    $scope.removeConnectedContract = function (row) {
                        var r=confirm($filter('translate')('general.alert_remove_link_project'));     //added by ashok
                        if(r==true){
                            var params = {};
                            params.contract_id = row.id_contract;
                            params.user_role_id = $scope.user1.user_role_id;
                            params.id_user = $scope.user1.id_user;  
                            params.customer_id =  $scope.user1.customer_id;
                            params.project_id = decode($stateParams.id);
                            projectService.deleteConnectedContracts(params).then(function (result) {
                                if(result.status){
                                    var obj = {};
                                    obj.action_name = 'Delete';
                                    obj.action_description = 'contract delete $$('+result.data.file_name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = location.href;
                                    if(AuthService.getFields().data.parent){
                                        obj.user_id = AuthService.getFields().data.parent.id_user;
                                        obj.acting_user_id = AuthService.getFields().data.data.id_user;
                                    }
                                    else obj.user_id = AuthService.getFields().data.data.id_user;
                                    if(AuthService.getFields().access_token != undefined){
                                        var s = AuthService.getFields().access_token.split(' ');
                                        obj.access_token = s[1];
                                    }
                                    else obj.access_token = '';
                                    $rootScope.toast('Success',result.message);
                                    setTimeout(function(){
                                        $uibModalInstance.close();
                                        $scope.init();
                                        $scope.connectedContractsList();
                                    },300);
                                }
                            });
                        }
                        
                    }  
                  
        
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                },
                resolve: {}
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        }

       
        $scope.addConnectedContracts = function (pageType=0,id_workflow=0,id_provider=0) {
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal contract-list-popup modal-open',
                templateUrl: 'views/Manage-Users/contracts/add-contracts-list.html',
                size: 'lg',
                controller: function ($uibModalInstance, $scope) {
                    $scope.update = false;
                    $scope.showInnerDiv = false;
                    $scope.title = 'general.update';
                    $scope.bottom = 'general.save';
                    $scope.field = '';
                    $scope.isEdit = false;
                    $scope.contractParters={};
                    $scope.businessUnitList =[];
                    $scope.usersList =[];
                    $scope.can_access=1;
                    $scope.date_field='';
                    $scope.date_period='';
                    $scope.searchFields = {};
                    $scope.business_unit_id='All';
                    $scope.relationship_category_id='';
                    $scope.automatic_prolongation=null;
                    $scope.provider_name='';
                    $scope.displayCount = $rootScope.userPagination;
                    $scope.id_workflow=id_workflow;
                    $scope.id_provider=id_provider;
                    $scope.pageType=pageType;
                    $scope.getBUList = function(){
                        var param ={};
                        param.user_role_id=$rootScope.user_role_id;
                        param.id_user=$rootScope.id_user;
                        param.customer_id = $scope.user1.customer_id;
                        param.status = 1;
                        businessUnitService.list(param).then(function(result){
                            result.data.data.unshift({'id_business_unit':'All', 'bu_name':'All'});
                            $scope.bussinessUnit = result.data.data;
                        });
                    }
                    $scope.getBUList();
                    $scope.getCategoryList = function(){
                        contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                            $scope.relationshipCategoryList = result.drop_down;
                        });
                    }
                    $scope.getCategoryList();
                    $scope.contractsList = [];
                    $scope.clear = function() {
                        $scope.created_date = null;
                        $scope.date_period = null;
                        $scope.date_field = null;
                        // $scope.business_unit_id = '';
                        $scope.business_unit_id='All';
                        $scope.relationship_category_id = null;
                        $scope.provider_name = '';
                        $scope.contract_status = null;
                        $scope.end_date_lessthan_90 = null;
                
                        $stateParams.pname = undefined ;
                        $stateParams.status = undefined ;
                        $stateParams.status1 = undefined ;
                        $stateParams.activity_filter = undefined ;
                        $stateParams.end_date = undefined;
                
                        angular.element('#created_date').removeClass('req-filter');
                    };
                    $scope.selectDate = function(date) {
                        var d = null;
                        $stateParams.end_date = undefined ;
                        $stateParams.this_month = undefined ;
                        $stateParams.end_month = undefined ;
                        if(date){
                            var element = angular.element('#created_date');
                            element.removeClass("req-filter");
                            element.addClass('active-filter');
                            d= dateFilter(date,'yyyy-MM-dd');
                            if($scope.date_field) 
                                $scope.tableStateRef.date_field = $scope.date_field;
                            else {
                                $scope.date_field='created_on';
                                $scope.tableStateRef.date_field = $scope.date_field;
                            }
                            if($scope.date_period) 
                                $scope.tableStateRef.date_period = $scope.date_period;
                            else {
                                $scope.date_period='=';
                                $scope.tableStateRef.date_period = $scope.date_period;
                            }
                        }
                        $scope.tableStateRef.created_date = d;
                        $scope.resetPagination=true;
                        console.log("selectDate---------");
                        $scope.callServer($scope.tableStateRef);
                    }
                    $scope.getProviderList = function(id){
                        $scope.business_unit_id=id;
                        var params = {};
                        if(id) {
                            $scope.provider_name = null;
                            params.business_unit_id = id;
                            $stateParams.pname=undefined;
                        }
                        // if(pageType){
                        //     params.id_provider=$scope.id_provider;
                        // }
                        params.customer_id = $scope.user1.customer_id;
                        params.id_user  = $scope.user1.id_user;
                        params.user_role_id  = $scope.user1.user_role_id;
                        params.status  = 1;
                        providerService.list(params).then(function(result){
                            if(!pageType){
                            result.data.data.unshift({'provider_name':'All'});
                            }
                            $scope.providerList = result.data.data;
                        });
                    };
                    $scope.getProviderList();

                    $scope.categoryUpdated=function(id){
                        $scope.relationship_category_id=id;
                }
                $scope.providerChanged=function(id){
                    $scope.provider_name=id;
            }
                    // written by ashok from 1065 to 1134
                    $scope.contractOverallDetails = function(data,type){
                        var params = {};
                        params.customer_id = $scope.user1.customer_id;
                        params.id_user =  $scope.user1.id_user;
                        params.user_role_id = $scope.user1.user_role_id;
                        params.pagination = $scope.tableStateRef.pagination;
                        params.search = $scope.tableStateRef.search;
                        params.sort = $scope.tableStateRef.sort;
                        params.created_date =  $scope.tableStateRef.created_date;
                        params.date_field =  $scope.tableStateRef.date_field;
                        params.date_period =  $scope.tableStateRef.date_period;
                        params.chart_type=type;
                        params.advancedsearch_get=data.advancedsearch_get;
                        if(!angular.isUndefined(data.business_unit_id)) params.business_unit_id = data.business_unit_id;
                        if(!angular.isUndefined(data.relationship_category_id)) params.relationship_category_id = data.relationship_category_id;
                        if(!angular.isUndefined(data.provider_name)) params.provider_name = data.provider_name;
                        if(!angular.isUndefined(data.contract_status)) params.contract_status = data.contract_status;
                        if(!angular.isUndefined(data.end_date_lessthan_90)) params.end_date_lessthan_90 = data.end_date_lessthan_90;
                        if(!angular.isUndefined(data.can_access)) params.can_access = data.can_access;
                        params.sort = data.sort;
                        contractService.contractOverallDetails(params).then(function(result){
                            $scope.myDataSource = result.data;
                        });
                    };
                    $scope.memorizeFilters = function(data){
                        $localStorage.curUser.data.filters.allContracts = undefined;
                        $localStorage.curUser.data.filters.allActivities = undefined;
                    }
                    $scope.filterDateType = function(val) {
                        $scope.resetPagination=true;
                        $scope.date_field = val;
                        $stateParams.end_date = undefined ;
                        $stateParams.this_month = undefined ;
                        $stateParams.end_month = undefined ;
                        if($scope.date_period)$scope.tableStateRef.date_period = $scope.date_period;
                        else {
                            $scope.tableStateRef.date_period = '=';
                            $scope.date_period = '=';
                        }
                        if(!$scope.created_date) {
                            angular.element('#created_date').addClass('req-filter');
                        }
                        $scope.tableStateRef.date_field = val;
                        if($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_date){
                            console.log("filterDateType---------");
                            $scope.callServer($scope.tableStateRef);  
                        }
                    }
                    $scope.filterDatePeriod = function(val) {
                        $scope.resetPagination=true;
                        $scope.date_period = val;
                        $stateParams.end_date = undefined ;
                        $stateParams.this_month = undefined ;
                        $stateParams.end_month = undefined ;
                        if($scope.date_field)$scope.tableStateRef.date_field = $scope.date_field;
                        else {
                            $scope.tableStateRef.date_field = 'created_on';
                            $scope.date_field = 'created_on';
                        }
                        if(!$scope.created_date) {
                            angular.element('#created_date').addClass('req-filter');
                        }
                        $scope.tableStateRef.date_period = val;
                        if($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_date){
                            console.log("filterDatePeriod---------");
                            $scope.callServer($scope.tableStateRef);
                        }
                    }
            
                    $scope.callServer = function (tableState){
                        $scope.filtersData = {};        
                        $scope.isLoading = true;
                        var pagination = tableState.pagination;
                        tableState.customer_id = $scope.user1.customer_id;
                        tableState.id_user  = $scope.user1.id_user;
                        tableState.user_role_id  = $scope.user1.user_role_id;
                        tableState.can_access  = $scope.can_access;                        
                        if($stateParams.pname==undefined && 
                            $stateParams.status==undefined && 
                            $stateParams.end_date==undefined &&
                            $stateParams.this_month==undefined &&
                            $stateParams.end_month==undefined &&
                            $stateParams.automatic_prolongation==undefined){
                        }else {
                            if($stateParams.pname){
                                $scope.contractsList = [];
                                if(tableState.provider_name != undefined){}
                                else {
                                    $scope.provider_name = $stateParams.pname;
                                }
                                $scope.contract_status = 'all';
                                $scope.resetPagination=true;
                                tableState.sort={};
                            }
                            if($stateParams.status){
                                if($scope.contract_status != $stateParams.status){}
                                else $scope.contract_status = $stateParams.status;
                            }
                            if($stateParams.end_date){
                                if($scope.created_date ==null || 
                                        $scope.created_date == undefined || 
                                        $scope.created_date=='') {
                                    $scope.end_date_lessthan_90 = $stateParams.end_date;
                                    $scope.date_field = "contract_end_date";
                                    $scope.date_period = "<=";
                                    $scope.created_date = moment().utcOffset(0, false).toDate();
                                    $scope.created_date.setDate( $scope.created_date.getDate() + 90 );
                                }                
                            }
                            if($stateParams.this_month){
                                if($scope.created_date ==null || 
                                        $scope.created_date == undefined || 
                                        $scope.created_date=='') {
                                    $scope.created_this_month = $stateParams.this_month;
                                    $scope.date_field = "created_on";
                                    $scope.date_period = ">=";
                                    $scope.created_date = moment().utcOffset(0, false).toDate();
                                    var date1= moment().utcOffset(0, false).toDate();
                                    var firstDay = moment(date1.getFullYear(), date1.getMonth(), 1).utcOffset(0, false).toDate();
                                    $scope.created_date = moment(firstDay).utcOffset(0, false).toDate();
                                }                
                            }        
                            if($stateParams.end_month){
                                if($scope.created_date ==null || 
                                        $scope.created_date == undefined || 
                                        $scope.created_date=='') {
                                    $scope.ending_this_month = $stateParams.end_month;
                                    $scope.date_field = 'contract_end_date';
                                    $scope.date_period = '<=';
                                    var date2 = moment().utcOffset(0, false).toDate();
                                    var lastDay = moment(date2.getFullYear(), date2.getMonth() + 1, 0).utcOffset(0, false).toDate();
                                    $scope.created_date = moment(lastDay).utcOffset(0, false).toDate();
                                }                
                            }    
                            if($stateParams.automatic_prolongation){
                                if($scope.automatic_prolongation != null){}
                                else{
                                    $scope.automatic_prolongation = $stateParams.automatic_prolongation;
                                }
                            }
                        }
                        // setTimeout(function(){
                            tableState.date_period  = $scope.date_period;
                            tableState.date_field  = $scope.date_field;
                            tableState.business_unit_id = angular.copy($scope.business_unit_id); 
                            if($scope.relationship_category_id && $scope.relationship_category_id !=null){
                                tableState.relationship_category_id = $scope.relationship_category_id;
                            }else delete tableState.relationship_category_id;
                            if($scope.provider_name!='' && $scope.provider_name != null && $scope.provider_name != undefined){
                                tableState.provider_name  = $scope.provider_name;
                            }else{
                                delete tableState.provider_name;
                                $scope.provider_name = '';
                            }

                            // if($scope.provider_name!='' && $scope.provider_name != null && $scope.provider_name != undefined){
                            //     tableState.provider_name  = $scope.provider_name;
                            // }else{
                            //     delete tableState.provider_name;
                            //     $scope.provider_name = '';
                            // }


                            if($scope.end_date_lessthan_90 && $scope.end_date_lessthan_90 != null){
                                tableState.end_date_lessthan_90  = $scope.end_date_lessthan_90;
                            }else{
                                delete tableState.end_date_lessthan_90;
                                $scope.end_date_lessthan_90 = '';
                            }
                            if($scope.contract_status && $scope.contract_status != null){
                                if($scope.contract_status !='all')
                                    tableState.contract_status  = $scope.contract_status;
                                else delete tableState.contract_status;
                            }else{
                                delete tableState.contract_status;
                                $scope.contract_status = '';
                            }
                            if($scope.created_this_month && $scope.created_this_month !=null){
                                tableState.created_this_month = $scope.created_this_month;
                            }else {
                                delete tableState.created_this_month;
                                $scope.created_this_month=null;
                            }
                            if($scope.ending_this_month && $scope.ending_this_month !=null){
                                tableState.ending_this_month = $scope.ending_this_month;
                            }else {
                                delete tableState.ending_this_month;
                                $scope.ending_this_month=null;
                            }
                            if($scope.automatic_prolongation && $scope.automatic_prolongation !=null){
                                tableState.automatic_prolongation = $scope.automatic_prolongation;
                            }else{
                                delete tableState.automatic_prolongation;
                                $scope.automatic_prolongation = null;
                            }
                            tableState.overview=true;
                            if($scope.resetPagination){
                                tableState.pagination={};
                                tableState.pagination.start='0';
                                tableState.pagination.number='10';
                            }
                            if($scope.created_date ==null || $scope.created_date == undefined || $scope.created_date==''){
                                delete tableState.date_period;
                                delete tableState.date_field;
                                $scope.date_period = '';
                                $scope.date_field = '';
                            } else{
                                tableState.created_date = dateFilter($scope.created_date,'yyyy-MM-dd');
                            }
                            if(tableState.advancedsearch_get){}
                            else tableState.advancedsearch_get={};
                            $scope.tableStateRef = tableState;
                            contractService.allContractsList(tableState).then (function(result){
                                $scope.contractsList =[];
                                // $scope.contractOverallDetails(tableState,'allcontracts');               //written by ashok
                                $scope.contractsList = result.data.data;
                                $scope.emptyTable=false;
                                $scope.displayCount = $rootScope.userPagination;
                                $scope.totalRecords = result.data.total_records;
                                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                                $scope.isLoading = false;
                                $scope.getCategoryList();
                                $scope.getBUList();
                                $scope.getProviderList(tableState.business_unit_id);
                                $scope.provider_name = tableState.provider_name;
                                $scope.resetPagination=false;
                                $scope.memorizeFilters(tableState);       //written by ashok
                                if(result.data.total_records < 1)
                                    $scope.emptyTable=true;
                            });
                        // },2000);
                    }
                    $scope.defaultPages = function(val){
                        userService.userPageCount({'display_rec_count':val}).then(function (result){
                            if(result.status){
                                $rootScope.userPagination = val;
                                $scope.resetPagination=true;
                                $scope.callServer($scope.tableStateRef);
                            }                
                        });
                    }
                  

                    $scope.addedContractsList = function(row){
                        // console.log("id",row);                         
                        var params ={};
                        params.contract_id = row.id_contract;
                        params.project_id = decode($stateParams.id);
                        params.id_user = $scope.user1.id_user;  
                        params.customer_id =  $scope.user1.customer_id;
                        projectService.addContractToProject(params).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success',result.message);
                                setTimeout(function(){
                                    $uibModalInstance.close();
                                    $scope.init();
                                },300);
                                $scope.connectedContractsList();   //added by ashok
                            }
                            else{
                                $rootScope.toast('Error',result.error.message);
                            }
                           
                        })
                    }
        
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                },
                resolve: {}
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });

           
        }


        $scope.addConnectedContractsForStoreModule = function (id_workflow,id_provider) {
            if($scope.storedModule){
                $scope.storedModule.close();
              }
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal contract-list-popup modal-open',
                templateUrl: 'views/Manage-Users/contracts/add-contracts-list-store-module.html',
                size: 'lg',
                controller: function ($uibModalInstance, $scope) {
                    $scope.update = false;
                    $scope.showInnerDiv = false;
                    $scope.title = 'general.update';
                    $scope.bottom = 'general.save';
                    $scope.field = '';
                    $scope.isEdit = false;
                    $scope.contractParters={};
                    $scope.businessUnitList =[];
                    $scope.usersList =[];
                    $scope.can_access=1;
                    $scope.date_field='';
                    $scope.date_period='';
                    $scope.searchFields = {};
                    $scope.business_unit_id='All';
                    $scope.relationship_category_id='';
                    $scope.automatic_prolongation=null;
                    $scope.provider_name='';
                    $scope.displayCount = $rootScope.userPagination;
                    $scope.id_workflow=id_workflow;
                    $scope.id_provider=id_provider;


                    $scope.getBUList = function(){
                        var param ={};
                        param.user_role_id=$rootScope.user_role_id;
                        param.id_user=$rootScope.id_user;
                        param.customer_id = $scope.user1.customer_id;
                        param.status = 1;
                        businessUnitService.list(param).then(function(result){
                            result.data.data.unshift({'id_business_unit':'All', 'bu_name':'All'});
                            $scope.bussinessUnit = result.data.data;
                        });
                    }
                    $scope.getBUList();
                    $scope.getCategoryList = function(){
                        contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                            $scope.relationshipCategoryList = result.drop_down;
                        });
                    }
                    $scope.getCategoryList();
                    $scope.contractsList = [];
                    $scope.clear = function() {
                        $scope.created_date = null;
                        $scope.date_period = null;
                        $scope.date_field = null;
                        // $scope.business_unit_id = '';
                        $scope.business_unit_id='All';
                        $scope.relationship_category_id = null;
                        $scope.provider_name = '';
                        $scope.contract_status = null;
                        $scope.end_date_lessthan_90 = null;
                
                        $stateParams.pname = undefined ;
                        $stateParams.status = undefined ;
                        $stateParams.status1 = undefined ;
                        $stateParams.activity_filter = undefined ;
                        $stateParams.end_date = undefined;
                
                        angular.element('#created_date').removeClass('req-filter');
                    };
                    $scope.selectDate = function(date) {
                        var d = null;
                        $stateParams.end_date = undefined ;
                        $stateParams.this_month = undefined ;
                        $stateParams.end_month = undefined ;
                        if(date){
                            var element = angular.element('#created_date');
                            element.removeClass("req-filter");
                            element.addClass('active-filter');
                            d= dateFilter(date,'yyyy-MM-dd');
                            if($scope.date_field) 
                                $scope.tableStateRef.date_field = $scope.date_field;
                            else {
                                $scope.date_field='created_on';
                                $scope.tableStateRef.date_field = $scope.date_field;
                            }
                            if($scope.date_period) 
                                $scope.tableStateRef.date_period = $scope.date_period;
                            else {
                                $scope.date_period='=';
                                $scope.tableStateRef.date_period = $scope.date_period;
                            }
                        }
                        $scope.tableStateRef.created_date = d;
                        $scope.resetPagination=true;
                        console.log("selectDate---------");
                        $scope.callServer($scope.tableStateRef);
                    }
                    $scope.getProviderList = function(id){
                        $scope.business_unit_id=id;
                        var params = {};
                        if(id) {
                            $scope.provider_name = null;
                            params.business_unit_id = id;
                            $stateParams.pname=undefined;
                        }
                        params.customer_id = $scope.user1.customer_id;
                        params.id_user  = $scope.user1.id_user;
                        params.user_role_id  = $scope.user1.user_role_id;
                        params.status  = 1;
                        providerService.list(params).then(function(result){
                            if(!pageType){
                            result.data.data.unshift({'provider_name':'All'});
                            }
                            $scope.providerList = result.data.data;
                        });
                    };
                    $scope.getProviderList();

                    $scope.categoryUpdated=function(id){
                        $scope.relationship_category_id=id;
                }
                $scope.providerChanged=function(id){
                    $scope.provider_name=id;
            }
                    $scope.contractOverallDetails = function(data,type){
                        var params = {};
                        params.customer_id = $scope.user1.customer_id;
                        params.id_user =  $scope.user1.id_user;
                        params.user_role_id = $scope.user1.user_role_id;
                        params.pagination = $scope.tableStateRef.pagination;
                        params.search = $scope.tableStateRef.search;
                        params.sort = $scope.tableStateRef.sort;
                        params.created_date =  $scope.tableStateRef.created_date;
                        params.date_field =  $scope.tableStateRef.date_field;
                        params.date_period =  $scope.tableStateRef.date_period;
                        params.chart_type=type;
                        params.advancedsearch_get=data.advancedsearch_get;
                        if(!angular.isUndefined(data.business_unit_id)) params.business_unit_id = data.business_unit_id;
                        if(!angular.isUndefined(data.relationship_category_id)) params.relationship_category_id = data.relationship_category_id;
                        if(!angular.isUndefined(data.provider_name)) params.provider_name = data.provider_name;
                        if(!angular.isUndefined(data.contract_status)) params.contract_status = data.contract_status;
                        if(!angular.isUndefined(data.end_date_lessthan_90)) params.end_date_lessthan_90 = data.end_date_lessthan_90;
                        if(!angular.isUndefined(data.can_access)) params.can_access = data.can_access;
                        params.sort = data.sort;
                        contractService.contractOverallDetails(params).then(function(result){
                            $scope.myDataSource = result.data;
                        });
                    };
                    $scope.memorizeFilters = function(data){
                        $localStorage.curUser.data.filters.allContracts = undefined;
                        $localStorage.curUser.data.filters.allActivities = undefined;
                    }

                    $scope.filterDateType = function(val) {
                        $scope.resetPagination=true;
                        $scope.date_field = val;
                        $stateParams.end_date = undefined ;
                        $stateParams.this_month = undefined ;
                        $stateParams.end_month = undefined ;
                        if($scope.date_period)$scope.tableStateRef.date_period = $scope.date_period;
                        else {
                            $scope.tableStateRef.date_period = '=';
                            $scope.date_period = '=';
                        }
                        if(!$scope.created_date) {
                            angular.element('#created_date').addClass('req-filter');
                        }
                        $scope.tableStateRef.date_field = val;
                        if($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_date){
                            console.log("filterDateType---------");
                            $scope.callServer($scope.tableStateRef);  
                        }
                    }
                    $scope.filterDatePeriod = function(val) {
                        $scope.resetPagination=true;
                        $scope.date_period = val;
                        $stateParams.end_date = undefined ;
                        $stateParams.this_month = undefined ;
                        $stateParams.end_month = undefined ;
                        if($scope.date_field)$scope.tableStateRef.date_field = $scope.date_field;
                        else {
                            $scope.tableStateRef.date_field = 'created_on';
                            $scope.date_field = 'created_on';
                        }
                        if(!$scope.created_date) {
                            angular.element('#created_date').addClass('req-filter');
                        }
                        $scope.tableStateRef.date_period = val;
                        if($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_date){
                            console.log("filterDatePeriod---------");
                            $scope.callServer($scope.tableStateRef);
                        }
                    }
                    
                    $scope.callServer = function (tableState){
                        $scope.filtersData = {};        
                        $scope.isLoading = true;
                        var pagination = tableState.pagination;
                        tableState.customer_id = $scope.user1.customer_id;
                        tableState.id_user  = $scope.user1.id_user;
                        tableState.user_role_id  = $scope.user1.user_role_id;
                        tableState.can_access  = $scope.can_access;
                        tableState.provider_id=$scope.id_provider;
                        
                        if($stateParams.pname==undefined && 
                            $stateParams.status==undefined && 
                            $stateParams.end_date==undefined &&
                            $stateParams.this_month==undefined &&
                            $stateParams.end_month==undefined &&
                            $stateParams.automatic_prolongation==undefined){
                        }else {
                            if($stateParams.pname){
                                $scope.contractsList = [];
                                if(tableState.provider_name != undefined){}
                                else {
                                    $scope.provider_name = $stateParams.pname;
                                }
                                $scope.contract_status = 'all';
                                $scope.resetPagination=true;
                                tableState.sort={};
                            }
                            if($stateParams.status){
                                if($scope.contract_status != $stateParams.status){}
                                else $scope.contract_status = $stateParams.status;
                            }
                            if($stateParams.end_date){
                                if($scope.created_date ==null || 
                                        $scope.created_date == undefined || 
                                        $scope.created_date=='') {
                                    $scope.end_date_lessthan_90 = $stateParams.end_date;
                                    $scope.date_field = "contract_end_date";
                                    $scope.date_period = "<=";
                                    $scope.created_date = moment().utcOffset(0, false).toDate();
                                    $scope.created_date.setDate( $scope.created_date.getDate() + 90 );
                                }                
                            }
                            if($stateParams.this_month){
                                if($scope.created_date ==null || 
                                        $scope.created_date == undefined || 
                                        $scope.created_date=='') {
                                    $scope.created_this_month = $stateParams.this_month;
                                    $scope.date_field = "created_on";
                                    $scope.date_period = ">=";
                                    $scope.created_date = moment().utcOffset(0, false).toDate();
                                    var date1= moment().utcOffset(0, false).toDate();
                                    var firstDay = moment(date1.getFullYear(), date1.getMonth(), 1).utcOffset(0, false).toDate();
                                    $scope.created_date = moment(firstDay).utcOffset(0, false).toDate();
                                }                
                            }        
                            if($stateParams.end_month){
                                if($scope.created_date ==null || 
                                        $scope.created_date == undefined || 
                                        $scope.created_date=='') {
                                    $scope.ending_this_month = $stateParams.end_month;
                                    $scope.date_field = 'contract_end_date';
                                    $scope.date_period = '<=';
                                    var date2 = moment().utcOffset(0, false).toDate();
                                    var lastDay = moment(date2.getFullYear(), date2.getMonth() + 1, 0).utcOffset(0, false).toDate();
                                    $scope.created_date = moment(lastDay).utcOffset(0, false).toDate();
                                }                
                            }    
                            if($stateParams.automatic_prolongation){
                                if($scope.automatic_prolongation != null){}
                                else{
                                    $scope.automatic_prolongation = $stateParams.automatic_prolongation;
                                }
                            }
                        }
                            tableState.date_period  = $scope.date_period;
                            tableState.date_field  = $scope.date_field;
                            tableState.business_unit_id = angular.copy($scope.business_unit_id); 
                            if($scope.relationship_category_id && $scope.relationship_category_id !=null){
                                tableState.relationship_category_id = $scope.relationship_category_id;
                            }else delete tableState.relationship_category_id;
                            if($scope.provider_name!='' && $scope.provider_name != null && $scope.provider_name != undefined){
                                tableState.provider_name  = $scope.provider_name;
                            }else{
                                delete tableState.provider_name;
                                $scope.provider_name = '';
                            }
                            if($scope.end_date_lessthan_90 && $scope.end_date_lessthan_90 != null){
                                tableState.end_date_lessthan_90  = $scope.end_date_lessthan_90;
                            }else{
                                delete tableState.end_date_lessthan_90;
                                $scope.end_date_lessthan_90 = '';
                            }
                            if($scope.contract_status && $scope.contract_status != null){
                                if($scope.contract_status !='all')
                                    tableState.contract_status  = $scope.contract_status;
                                else delete tableState.contract_status;
                            }else{
                                delete tableState.contract_status;
                                $scope.contract_status = '';
                            }
                            if($scope.created_this_month && $scope.created_this_month !=null){
                                tableState.created_this_month = $scope.created_this_month;
                            }else {
                                delete tableState.created_this_month;
                                $scope.created_this_month=null;
                            }
                            if($scope.ending_this_month && $scope.ending_this_month !=null){
                                tableState.ending_this_month = $scope.ending_this_month;
                            }else {
                                delete tableState.ending_this_month;
                                $scope.ending_this_month=null;
                            }
                            if($scope.automatic_prolongation && $scope.automatic_prolongation !=null){
                                tableState.automatic_prolongation = $scope.automatic_prolongation;
                            }else{
                                delete tableState.automatic_prolongation;
                                $scope.automatic_prolongation = null;
                            }
                            tableState.overview=true;
                            if($scope.resetPagination){
                                tableState.pagination={};
                                tableState.pagination.start='0';
                                tableState.pagination.number='10';
                            }
                            if($scope.created_date ==null || $scope.created_date == undefined || $scope.created_date==''){
                                delete tableState.date_period;
                                delete tableState.date_field;
                                $scope.date_period = '';
                                $scope.date_field = '';
                            } else{
                                tableState.created_date = dateFilter($scope.created_date,'yyyy-MM-dd');
                            }
                            if(tableState.advancedsearch_get){}
                            else tableState.advancedsearch_get={};
                            $scope.tableStateRef = tableState;
                            contractService.allContractsList(tableState).then (function(result){
                                $scope.contractsList =[];
                                // $scope.contractOverallDetails(tableState,'allcontracts');               //written by ashok
                                $scope.contractsList = result.data.data;
                                $scope.emptyTable=false;
                                $scope.displayCount = $rootScope.userPagination;
                                $scope.totalRecords = result.data.total_records;
                                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                                $scope.isLoading = false;
                                $scope.getCategoryList();
                                $scope.getBUList();
                                $scope.getProviderList(tableState.business_unit_id);
                                $scope.provider_name = tableState.provider_name;
                                $scope.resetPagination=false;
                                $scope.memorizeFilters(tableState);       //written by ashok
                                if(result.data.total_records < 1)
                                    $scope.emptyTable=true;
                            });
                        // },2000);
                    }
                    $scope.defaultPages = function(val){
                        userService.userPageCount({'display_rec_count':val}).then(function (result){
                            if(result.status){
                                $rootScope.userPagination = val;
                                $scope.resetPagination=true;
                                $scope.callServer($scope.tableStateRef);
                            }                
                        });
                    }
                  

                    $scope.coonectedContractsForStoreModule = function(row){
                        // console.log("id",row);
                        var r = confirm($filter('translate')('general.alert_selected_project_task'));
                            if(r== true){
                                var params ={};
                                params.id_contract = row.id_contract;
                                params.id_contract_workflow=$scope.id_workflow;
                            projectService.mapSubTaskContract(params).then(function(result){
                                    if(result.status){
                                        $rootScope.toast('Success',result.message);
                                        $uibModalInstance.close();
                                        $scope.getStoredModules();
                                        $scope.manageStoredModules();
                                    }
                                    else{
                                        $rootScope.toast('Error',result.error.message);
                                    }
                                });
                            }
                    }
        
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                        $scope.manageStoredModules();
                    };
                },
                resolve: {}
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });

           
        }


        //added by ashok from 1261 to 1477
        $scope.addConnectedProviders = function () {
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal contract-list-popup modal-open',
                templateUrl: 'views/Manage-Users/contracts/add-providers-list.html',
                size: 'lg',
                controller: function ($uibModalInstance, $scope) {
                    $scope.del=0;
                    $scope.can_access=1;
                    $scope.searchFields = {};
                    $scope.displayCount = $rootScope.userPagination;
                    $scope.resetPagination=false;
                    if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
                        $scope.del=1;
                    }
        
                    $scope.getCategoryList = function(){
                        providerService.getProviderRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                            $scope.relationshipCategoryList = result.drop_down;
                         });
                     }
                    
                     $scope.getCategoryList();
                     masterService.getCountiresList().then(function (result) {
                        if (result.status) {
                            $scope.countriesList = result.data;
                        }
                    });
                    
                    
                     $scope.providersList = [];
                     $scope.myDataSource = {};
                     $scope.itemsByPage = 10;
                     $scope.callServer = function (tableState){
                         $scope.filtersData = {};       
                         $scope.isLoading = true;
                         var pagination = tableState.pagination;
                         tableState.customer_id = $scope.user1.customer_id;
                         tableState.can_access  = $scope.can_access;
                         tableState.search_consider ='yes';
                             if($scope.country_id && $scope.country_id !=null){
                                tableState.country_id = $scope.country_id;
                            }else delete tableState.country_id;
                            
                            if($scope.relationship_category_id && $scope.relationship_category_id != null){
                                tableState.relationship_category_id  = angular.copy($scope.relationship_category_id);
                           }else{
                             delete tableState.relationship_category_id;
                             $scope.relationship_category_id = '';
                           }
                           if($scope.risk_profile && $scope.risk_profile !=null){ 
                              tableState.risk_profile = angular.copy($scope.risk_profile);         
                            }
                            else{
                                delete tableState.risk_profile;
                                $scope.risk_profile ='';
                            }
                            if($scope.approval_status && $scope.approval_status !=null){
                                tableState.approval_status  = angular.copy($scope.approval_status);
                            }
                             else{
                                delete tableState.approval_status;
                                $scope.approval_status='';
                            }
                             tableState.overview=true;
                             if($scope.resetPagination){
                                 tableState.pagination={};
                                 tableState.pagination.start='0';
                                 tableState.pagination.number='10';
                             }
                            
                             $scope.tableStateRef = tableState;
                             
                             providerService.list(tableState).then (function(result){
                                 $scope.providersList =[];
                                 $scope.providersList = result.data.data;
                                 $scope.labelNames=result.data.labels;
                                 $scope.providerListCount = result.data.total_records;
                                 $scope.emptyTable=false;
                                 $scope.displayCount = $rootScope.userPagination;
                                 $scope.totalRecords = result.data.total_records;
                                 tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                                 $scope.isLoading = false;
                                 $scope.resetPagination=false;
                                 $scope.memorizeFilters(tableState);
                                 if(result.data.total_records < 1) 
                                     $scope.emptyTable=true;
                             });
                         // },2000);
                     }


                     $scope.defaultPages = function(val){
                         userService.userPageCount({'display_rec_count':val}).then(function (result){
                             if(result.status){
                                 $rootScope.userPagination = val;
                                 $scope.resetPagination=true;
                                 $scope.callServer($scope.tableStateRef);
                             }                
                         });
                     }
                     
                     $scope.getProvidersByAccess=function(val){
                        $scope.resetPagination=true;
                        $scope.can_access = val;
                        $scope.tableStateRef.can_access = val;
                        if($scope.tableStateRef.can_access){
                            $scope.callServer($scope.tableStateRef);  
                        }else{
                            delete $scope.tableStateRef.can_access;
                            $scope.callServer($scope.tableStateRef);  
                        } 
                    }
            
                  

                    $scope.addedProjectsList = function(row){
                        var params ={};
                        params.provider_id = row.id_provider;
                        params.project_id = decode($stateParams.id);
                        // params.id_user = $scope.user1.id_user;  
                        params.customer_id =  $scope.user1.customer_id;
                        providerService.addProvidersToProject(params).then(function(result){
                            if(result.status){
                            $rootScope.toast('Success',result.message);
                                setTimeout(function(){
                                    $uibModalInstance.close();
                                    $scope.init();
                                },300);
                                $scope.ProvidersLists($scope.tableStateRef1);
                                $scope.getProviderusers($scope.tableStateRef2);
                            }
                            else{
                                $rootScope.toast('Error',result.error.message);
                            }
                        })
                    }
                 
                     $scope.memorizeFilters = function(data){
                         $localStorage.curUser.data.filters.allProviders = data;
                     }

                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                
                },
                resolve: {}
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });

           
        }


        $scope.goToDesign = function(){
          if($rootScope.access !='eu')
              $state.go('app.projects.task-design',{name:$stateParams.name,id:$stateParams.id,rId:encode($scope.reviewWorkflowInfo[0].id_contract_review),wId:encode($scope.reviewWorkflowInfo[0].id_contract_workflow),type:'workflow'});
          if($rootScope.access =='eu')
            $state.go('app.projects.task-design1',{name:$stateParams.name,id:$stateParams.id,rId:encode($scope.reviewWorkflowInfo[0].id_contract_review),wId:encode($scope.reviewWorkflowInfo[0].id_contract_workflow),type:'workflow'});
        }
        $scope.goToContractDetails = function(row){
            //console.log('row info',row);
            //var goView = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
           //$state.go('app.contract.view',{name:row.contract_name,id:encode(row.id_contract)});
            // if(row.is_workflow=='1')
            //     $state.go(goView,{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
            // else
            //     $state.go(goView,{name:row.contract_name,id:encode(row.id_contract),type:'review'});
        }
        $scope.open = function(g){
            if(g.open)
              g.open = false;
            else
              g.open = true;
            return g.open;
        }
        
       //added by ashok
    //    $scope.init();
    $scope.isSubLoading = false;
       $scope.ProvidersLists = function (tableState){
        var pagination = tableState.pagination;
           setTimeout(function(){
               $scope.tableStateRef1 = tableState;
               $scope.isSubLoading = true;
               tableState.project_id = decode($stateParams.id);
               tableState.type='project';
               tableState.customer_id = $scope.user1.customer_id;
               tableState.overview=true;
               tableState.can_access  = $scope.can_access;
               providerService.list(tableState).then (function(result){
                   $scope.providerList = result.data.data;
                   $scope.providerListCount = result.data.total_records;
                   $scope.emptyTable=false;
                   $scope.displayCount = $rootScope.userPagination;
                   $scope.totalRecords1 = result.data.total_records;
                   tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                   $scope.isSubLoading = false;
                   if(result.data.total_records < 1)
                       $scope.emptyTable=true;
               })
           },700);
       }
       

       $scope.defaultPages2 = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.ProvidersLists($scope.tableStateRef1);
            }                
        });
    }
        $scope.init();
        $scope.reviewAction = function (tableState){
            setTimeout(function(){
                $scope.tableStateRef = tableState;
                $scope.isLoading = true;
                var pagination = tableState.pagination;
                tableState.contract_id  = decode($stateParams.id);
                tableState.id_user  = $scope.user1.id_user;
                tableState.user_role_id  = $scope.user1.user_role_id;
                tableState.action_item_type  = 'outside';
                tableState.type='project_actionitems';
                contractService.reviewActionItemList(tableState).then (function(result){
                    $scope.reviewList = result.data.data;
                    $scope.reviewListCount = result.data.count;
                    $scope.emptyTable2=false;
                    $scope.displayCount = $rootScope.userPagination;
                    $scope.totalRecords1 = result.data.total_records;
                    tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                    $scope.isLoading = false;
                    if(result.data.total_records < 1)
                        $scope.emptyTable2=true;
                })
            },700);
        }
        $scope.defaultPages1 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.reviewAction($scope.tableStateRef);
                    $scope.providersList($scope.tableStateRef);
                }                
            });
        }
        $scope.deleteAttachment = function(id,name){
            console.log("iop")
            var r=confirm($filter('translate')('general.alert_continue'));
            $scope.deleConfirm = r;
            if(r==true){
                var params = {};
                params.id_document = id;
                attachmentService.deleteAttachments(params).then (function(result){
                    if(result.status){
                        $rootScope.toast('Success',result.message);
                        var obj = {};
                        obj.action_name = 'delete';
                        obj.action_description = 'delete$$Attachment$$('+name+')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                        $scope.init();
                    }else{$rootScope.toast('Error',result.error,'error');}
                })
            }
        }        
        $scope.choices = [{id: 'choice1',type:'new'}];
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
    }
   
   
    $scope.goToContractReview = function(row){
        //console.log('row info',row);
        $scope.id_contract_workflow =row.project_task[0].id_contract_workflow;
        //console.log($scope.id_contract_workflow);
        $scope.review_id = row.project_task[0].contract_review_id;
        $state.go('app.projects.project-task',{name:$stateParams.name,id:$stateParams.id,rId:encode($scope.review_id),wId:encode($scope.id_contract_workflow),type:'workflow'});
    }
    $scope.goToProjectLogs = function(id){
        $state.go('app.projects.project-log',{name:$stateParams.name,id:$stateParams.id,type:'workflow'});
    }

 
    $scope.goToCreateContract = function(row){
        $scope.showFiled = false;
        $scope.disabled = false;
        $scope.selectedRow = row;
        $scope.contractLinks = [];
        $scope.contractLink={};
         var modalInstance = $uibModal.open({
             animation: true,
             backdrop: 'static',
             keyboard: false,
             scope: $scope,
             openedClass: 'right-panel-modal modal-open',
             templateUrl: 'create-contract.html',
             controller: function ($uibModalInstance, $scope) {
                tagService.list({'status':1,'tag_type':'contract_tags'}).then(function(result){
                    if (result.status) {
                        $scope.tags = result.data;
                    }
                });
                contractService.generateContractId({'customer_id':$scope.user1.customer_id}).then(function(result){
                    if(result.status){
                        $scope.contract = result.data;
                    }
                })
                providerService.list({'customer_id': $scope.user1.customer_id,'status':1,'all_providers':true}).then(function(result){
                    $scope.providers = result.data.data;
                });
                masterService.currencyList().then(function(result){
                    $scope.currencyList = result.data;
                });
                contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                    $scope.relationshipCategoryList = result.drop_down;
                });
                contractService.getRelationshipClassiffication({'customer_id': $scope.user1.customer_id}).then(function(result){
                    $scope.relationshipClassificationList = result.data;
                });
                templateService.list().then(function (result){
                    $scope.templateList=result.data.data;
                });
                $scope.validateCategoryTemplate= function(obj){
                    angular.forEach($scope.relationshipCategoryList, function(o,i){
                        if(o.id_relationship_category==obj){
                            if(o.type == 'Without Review'){
                                $scope.enableTemplate= false;
                                $scope.contract.template_id='';
                            }else{
                                $scope.enableTemplate= true;
                                $scope.contract.template_id='';
                            } 
                            templateService.list().then(function (result){
                                $scope.templateList=result.data.data;
                            });
                        }
                    })
                }
                $scope.verifyLink = function(data){
                    if(data !={}){
                        $scope.contractLinks.push(data);
                        $scope.contractLink={};
                    }
                }
                $scope.removeLink = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                        $scope.contractLinks.splice(index, 1);
                    }                    
                }
                $scope.deleteFile = function(index,row){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    $scope.deleConfirm = r;
                    if(r==true){
                        $scope.contract.attachment.links.splice(index,1);
                        var obj={}; obj.id_document=row.id_document;
                        $scope.links_delete.push(obj) ;
                    }
                }

                $scope.getContractDelegates = function (id,contractId){
                    contractService.getDelegates({'id_business_unit': id}).then(function(result){
                        $scope.delegates = result.data;
                    });
                    var params = {};
                    params.business_unit_id = id;
                    params.contract_id = contractId;
                    params.type = "buowner";
                    contractService.getbuOwnerUsers(params).then(function(result){
                        $scope.buOwnerUsers = result.data;
                    });
                }
                $scope.currencyList = [];
                $scope.templateList = [];
                $scope.relationshipCategoryList = {};
                $scope.relationshipClassificationList = {};
                $scope.file={};
                $scope.links_delete = [];
                $scope.bottom = 'general.save';
                $scope.enableTemplate = true;
                $scope.addContract = function (data1){
                    $scope.formDataObj= angular.copy(data1);
                    var contract={};
                    contract= $scope.formDataObj;
                    $scope.options = {};
                    if(contract.contract_tags){
                       angular.forEach($scope.tags, function(i,o){
                           $scope.options[o] = {};
                           $scope.options[o].tag_id = i.tag_id;
                           $scope.options[o].tag_type = i.tag_type;              
                           if(i.tag_type =='date')
                               $scope.options[o].tag_option = dateFilter(data1.contract_tags[i.tag_id],'yyyy-MM-dd');
                           else if(i.tag_type !='date')
                               $scope.options[o].tag_option = data1.contract_tags[i.tag_id];
                           else $scope.options[o].tag_option = '';        
                       });
                    } 
                   contract.contract_tags = $scope.options;
                    contract.created_by = $scope.user.id_user;
                    contract.customer_id = $scope.user1.customer_id;
                    contract.project_id = decode($stateParams.id);
                    contract.contract_end_date = dateFilter(contract.contract_end_date,'yyyy-MM-dd');
                    contract.contract_start_date = dateFilter(contract.contract_start_date,'yyyy-MM-dd');
                    if($scope.user.access =='bo')
                        contract.contract_owner_id = $scope.user.id_user;
                    else contract.contract_owner_id = contract.contract_owner_id;
                    $scope.contract['auto_renewal'] = $scope.contract['auto_renewal']==1?'1':'0';
                    contract.attachment_delete = [];
                    if($scope.file.delete){
                        angular.forEach($scope.file.delete, function(i,o){
                            var obj = {};
                            obj.id_document = i.id_document;
                            contract.attachment_delete.push(obj) ;
                        });
                    }
                    contract.links_delete=$scope.links_delete;
                    contract.links=$scope.contractLinks;
                    var params = {};
                    angular.copy(contract,params);
                    params.updated_by = $scope.user.id_user;
                    params.project_id = decode($stateParams.id);
                    if(moment( params.contract_end_date).utcOffset(0, false).toDate()<= moment( params.contract_start_date).utcOffset(0, false).toDate()){
                        alert($filter('translate')('general.alert_start_date_less_end'));
                    }else{
                        if(contract.id_contract){
                            if(moment($scope.end_date).utcOffset(0, false).toDate() > moment( params.contract_end_date).utcOffset(0, false).toDate()){
                                var r = confirm($filter('translate')('general.alert_contract_end_update'));
                                if (r == true) {
                                    Upload.upload({
                                        url: API_URL+'Contract/update',
                                        data: {
                                            'file' : $scope.file.attachment,
                                            'contract': params
                                        }
                                    }).then(function(resp){
                                        if(resp.data.status){
                                            // if(contract.is_workflow=='1')
                                            //     $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id,wId:encode(contract.id_contract_workflow),type:'workflow'});
                                            // else
                                            //     $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id,type:'review'});
                                            // // $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id});
                                            $rootScope.toast('Success',resp.data.message);
                                            $uibModalInstance.close();
                                            $scope.connectedContractsList();
                                            var obj = {};
                                            obj.action_name = 'update';
                                            obj.action_description = 'update$$contract$$'+contract.contract_name;
                                            obj.module_type = $state.current.activeLink;
                                            obj.action_url = $location.$$absUrl;
                                            $rootScope.confirmNavigationForSubmit(obj);
                                        }else{
                                            $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                                        }
                                    },function(resp){
                                        $rootScope.toast('Error',resp.error);
                                    },function(evt){
                                        var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                                    });
                                }
                            }else {
                                Upload.upload({
                                    url: API_URL+'Contract/add',
                                    data: {
                                        'file' : $scope.file.attachment,
                                        'contract': params
                                    }
                                }).then(function(resp){
                                    if(resp.data.status){
                                        if(contract.is_workflow=='1')
                                            $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id,wId:encode(contract.id_contract_workflow),type:'workflow'});
                                        else
                                            $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id,type:'review'});
                                        $rootScope.toast('Success',resp.data.message);
                                        $uibModalInstance.close();
                                        $scope.connectedContractsList();
                                        var obj = {};
                                        obj.action_name = 'update';
                                        obj.action_description = 'update$$contract$$'+contract.contract_name;
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);
                                    }else{
                                        $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                                    }
                                },function(resp){
                                    $rootScope.toast('Error',resp.error);
                                },function(evt){
                                    var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                                });
                            }                
                        }
                        else{
                             Upload.upload({
                                url: API_URL+'Contract/add',
                                 data: {
                                    'file' : $scope.file.attachment,
                                    'contract': contract
                                 }
                             }).then(function(resp){
                             if(resp.data.status){
                                $state.go('app.projects.view',{name:$stateParams.name,id:$stateParams.id,type:'workflow'});
                                $rootScope.toast('Success',resp.data.message);
                                $uibModalInstance.close();
                                $scope.connectedContractsList();
                                 var obj = {};
                                 obj.action_name = 'add';
                                 obj.action_description = 'add$$contract$$'+contract.contract_name;
                                 obj.module_type = $state.current.activeLink;
                                 obj.action_url = $location.$$absUrl;
                                 $rootScope.confirmNavigationForSubmit(obj);
                             }else{
                                $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                             }
                             },function(resp){
                                $rootScope.toast('Error',resp.error);
                             },function(evt){
                                var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                             });
                        }
                    }
                }
                 $scope.cancel = function () {
                     $uibModalInstance.close();
                 };
             },
             resolve: {}
         });
         modalInstance.result.then(function ($data) {
         }, function () {
         });
     }

     //written by ashok
     $scope.goToCreateProvider = function(row){
        $scope.showFiled = false;
        $scope.selectedRow = row;
        $scope.contractLinks = [];
        $scope.contractLink={};

        $scope.title = 'general.create';
        $scope.disabled = false;
        $scope.file={};
        $scope.links_delete = [];
        $scope.bottom = 'general.save';
        $scope.isEdit = false;
         var modalInstance = $uibModal.open({
             animation: true,
             backdrop: 'static',
             keyboard: false,
             scope: $scope,
             openedClass: 'right-panel-modal modal-open',
             templateUrl: 'views/Manage-Users/contracts/create-provider.html',
             controller: function ($uibModalInstance, $scope) {
                masterService.getCountiresList().then(function (result) {
                    if (result.status) {
                        $scope.countriesList = result.data;
                    }
                });

                providerService.getProviderRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                    $scope.relationshipCategoryList = result.drop_down;
                });

                tagService.list({'status':1,'tag_type':'provider_tags'}).then(function(result){
                    if (result.status) {
                        $scope.tags = result.data;
                    }
                });

                $scope.providerLinks=[];
                $scope.providerLink={};

                $scope.verifyLink = function(data){
                    if(data !={}){
                        $scope.providerLinks.push(data);
                        $scope.providerLink={};
                    }
                }

                $scope.removeLink = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                        $scope.providerLinks.splice(index, 1);
                    }                    
                }

                $scope.deleteFile = function(index,row){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    $scope.deleConfirm = r;
                    if(r==true){
                        $scope.provider.attachment.links.splice(index,1);
                        var obj={}; obj.id_document=row.id_document;
                        $scope.links_delete.push(obj) ;
                    }
                }

                $scope.reset = function(val){
                    $scope.provider.provider_tags[val]='';
                }

                providerService.getProviderUniqueId({'customer_id': $scope.user1.customer_id}).then(function(result){
                    if(result.status){
                        $scope.provider = result.data;
                    }
                })

                $scope.save = function (data){
                    $scope.formDataObj= angular.copy(data);
                    $scope.userData = $localStorage.curUser.data.data;
                    var provider={};
                    provider= $scope.formDataObj;
                    $scope.options = {};
                    if(provider.provider_tags){
                        angular.forEach($scope.tags, function(i,o){
                            $scope.options[o] = {};
                            $scope.options[o].tag_id = i.tag_id;
                            $scope.options[o].tag_type =i.tag_type; 
                            if($scope.provider.provider_tags.feedback !=undefined)
                                $scope.options[o].comments = $scope.provider.provider_tags.feedback[i.tag_id];
                            else $scope.options[o].comments = '';
                        
                            if(i.tag_type =='date')
                                $scope.options[o].tag_option = dateFilter(data.provider_tags[i.tag_id],'yyyy-MM-dd');
                            else if(i.tag_type !='date')
                                $scope.options[o].tag_option = data.provider_tags[i.tag_id];
                            else $scope.options[o].tag_option = '';        
                        });
                    }
                    provider.provider_tags = $scope.options;
                    provider.created_by =$scope.userData.id_user;
                    provider.customer_id = $scope.user1.customer_id;
                    provider.project_id = decode($stateParams.id);
                    provider.attachment_delete = [];
                    if($scope.file.delete){
                        angular.forEach($scope.file.delete, function(i,o){
                            var obj = {};
                            obj.id_document = i.id_document;
                            params.attachment_delete.push(obj) ;
                        });
                    }
                    provider.links_delete=$scope.links_delete; 
                    provider.links=$scope.providerLinks;
                    var params={};
                    angular.copy(provider,params);
                    params.updated_by = $scope.user.id_user;
                    if(provider.id_provider){
                        Upload.upload({
                            url: API_URL+'Customer/addprovider',
                            data: {
                                'file' : $scope.file.attachment,
                                'provider': params
                                }
                            }).then(function(resp){
                                if(resp.data.status){
                                    $rootScope.toast('Success',resp.data.message);
                                    $uibModalInstance.close();
                                    $scope.ProvidersLists($scope.tableStateRef1);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$ provider$$'+provider.provider_name;
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                   
                                } else{
                                    $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                                }
                            },function(resp){
                                $rootScope.toast('Error',resp.error);
                            },function(evt){
                                var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                        });
                    }
                    else{
                        Upload.upload({
                            url: API_URL+'Customer/addprovider',
                            data: {
                                'file' : $scope.file.attachment,
                                'provider':params
                            }
                        }).then(function(resp){
                        if(resp.data.status){
                            $rootScope.toast('Success',resp.data.message);
                            $uibModalInstance.close();
                            $scope.ProvidersLists($scope.tableStateRef1);
                            var obj = {};
                            obj.action_name = 'add';
                            obj.action_description = 'add$$provider$$'+provider.provider_name;
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $uibModalInstance.close();
                            // $scope.ProvidersLists();
                            // $scope.getProviderList();
                        }else{
                            $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                        }
                        },function(resp){
                            $rootScope.toast('Error',resp.error);
                        },function(evt){
                            var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                        });
                    }
                    
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            },
            resolve: {}
        });
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    }
  
    $scope.showUpload = false;
    $scope.uploadDoc = function(){
       // $scope.showUpload = true;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'create-edit-project-doc.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.update = false;
                $scope.title = 'general.update';
                $scope.bottom = 'general.save';
                $scope.isEdit = false;
                $scope.contractParters={};
                $scope.contractLinks=[];
                $scope.contractLink={};
                var params ={};
                $scope.uploadAttachment = function(attachments,data){
                    if(attachments.length>0){
                        Upload.upload({
                            url: API_URL+'Document/add',
                            data:{
                                file:attachments,
                                customer_id: $scope.user1.customer_id,
                                module_id: decode($stateParams.id),
                                module_type: 'project',
                                is_workflow:$scope.isWorkflow,
                                reference_id: decode($stateParams.id),
                                reference_type: 'project',
                                document_type : 0,
                                contract_workflow_id : decode($stateParams.wId),
                                uploaded_by: $scope.user1.id_user
                            }
                        }).then(function (resp) {
                            $scope.showUpload = false;
                            if(resp.data.status){
                                $rootScope.toast('Success',resp.data.message);
                                var obj = {};
                                obj.action_name = 'upload';
                                obj.action_description = 'upload$$attachments$$for$$contract$$('+$stateParams.name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = window.location.href;
                                $rootScope.confirmNavigationForSubmit(obj);
                            }
                            else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                            $scope.init();
                            $scope.cancel();
                        }, function (resp) {
                            $rootScope.toast('Error',resp.data.error,'error');
                        }, function (evt) {
                            $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                        });
            
                    }else{
                        $rootScope.toast('Error','No file selected','image-error');
                    }
                }
                $scope.verifyLink = function(data){
                    if(data !={}){
                        $scope.contractLinks.push(data);
                        $scope.contractLink={};
                    }
                }
                $scope.removeLink = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                        $scope.contractLinks.splice(index, 1);
                    }
                }
                $scope.uploadLinks = function (contractLinks,data) {
                    if($scope.contractLinks.length>0){
                        Upload.upload({
                            url: API_URL+'Document/add',
                            data:{
                                file:contractLinks,
                                customer_id: $scope.user1.customer_id,
                                module_id: decode($stateParams.id),
                                module_type: 'project',
                                is_workflow:$scope.isWorkflow,
                                reference_id: decode($stateParams.id),
                                reference_type: 'project',
                                document_type : 1,
                                contract_workflow_id : decode($stateParams.wId),
                                uploaded_by: $scope.user1.id_user
                            }
                        }).then(function (resp) {
                            $scope.showUpload = false;
                            if(resp.data.status){
                                $rootScope.toast('Success',resp.data.message);
                                var obj = {};
                                obj.action_name = 'upload';
                                obj.action_description = 'upload$$link$$for$$contract$$('+$stateParams.name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = window.location.href;
                                $rootScope.confirmNavigationForSubmit(obj);
                            }
                            else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                            $scope.init();
                            $scope.cancel();
                        }, function (resp) {
                            $rootScope.toast('Error',resp.data.error,'error');
                        }, function (evt) {
                            $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                        });
                    }else {
                        $rootScope.toast('Error','No link added','image-error');
                    }
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            },
            resolve: {}
        });
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    }
    
    $scope.cancelAttachments = function(){$scope.showUpload = false;}
    $scope.updateContractReview = function (row, type) {
        $scope.type = type;
        $scope.data={};
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/create-edit-project-review.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.update = false;
                $scope.bottom = 'general.save';
                $scope.isEdit = false;
                if (item) {
                    $scope.isEdit = true;
                    $scope.submitStatus = true;
                    $scope.data = angular.copy(item);
                    delete $scope.data.comments;
                    $scope.data.due_date = moment($scope.data.due_date).utcOffset(0, false).toDate();
                    $scope.title = '';
                    $scope.update = true;
                    $scope.bottom = 'general.update';
                }else{$scope.data.due_date=moment().utcOffset(0, false).toDate();}
                if($scope.type == 'view')
                    $scope.bottom = 'contract.finish';
                var respUserParams = {};
                respUserParams.contract_review_id  = $scope.data.contract_review_id?$scope.data.contract_review_id:'0';
                respUserParams.contract_id  = decode($stateParams.id);
                respUserParams.project_id  = decode($stateParams.id);
                respUserParams.type  = 'project';
                respUserParams.module_id  = $scope.data.module_id;
                contractService.getActionItemResponsibleUsers(respUserParams).then(function(result){
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
                    $scope.due_date=angular.copy(data.due_date);
                    $scope.due_date=dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                    if($scope.type == 'view'){
                        params.external_users = data.external_users;
                        params.id_contract_review_action_item = data.id_contract_review_action_item;
                        params.comments = data.comments;
                        params.is_finish = data.is_finish;
                        params.updated_by = $scope.user.id_user;
                        params.contract_id  = decode($stateParams.id);
                        params.due_date  = $scope.due_date;
                        params.reference_type ='project';
                        if(data.is_finish  == 1){
                            var r=confirm($filter('translate')('general.alert_action_finish'));
                            $scope.deleConfirm = r;
                            if(r==true){
                                contractService.reviewActionItemUpdate(params).then(function (result) {
                                    if (result.status) {
                                        var obj = {};
                                        obj.action_name = 'Finish';
                                        obj.action_description = 'Finish$$Action$$Item$$('+data.action_item+')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);
                                        $rootScope.toast('Success', result.message);
                                        $scope.reviewAction($scope.tableStateRef);
                                        $scope.cancel();
                                    } else {
                                        $rootScope.toast('Error', result.error,'error');
                                    }
                                });
                            }
                        }else{
                            params.due_date  = $scope.due_date;
                            params.reference_type='project';
                            contractService.reviewActionItemUpdate(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    var obj = {};
                                    obj.action_name = 'Save';
                                    obj.action_description = 'Save$$Action$$Item$$('+data.action_item+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.reviewAction($scope.tableStateRef);
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
                        params.contract_id = params.id_contract = decode($stateParams.id);
                        params.due_date  = $scope.due_date;
                        contractService.addReviewActionItemList(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'update$$Action$$Item$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.getActionItemById(data.id_contract_review_action_item);
                                $scope.cancel();
                                $scope.reviewAction($scope.tableStateRef);
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
    }
    
    $scope.deleteContractActionItem = function(row){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            var params ={};
            params.id_contract_review_action_item  = row.id_contract_review_action_item ;
            params.updated_by  = $rootScope.id_user ;            
            contractService.deleteActionItem(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$Action$$Item$$('+row.action_item+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.reviewAction($scope.tableStateRef);
                    $scope.getTopicQuestions(Globalparams,true);
                }else $rootScope.toast('Error', result.error, 'error',$scope.user);
            });
        }
    }

    
    $scope.goToDashboard = function (data) {
        $state.go('app.projects.project-dashboard1',{name:$stateParams.name,id:$stateParams.id,rId:encode(data.project_task[0].id_contract_review),wId:encode(data.project_task[0].id_contract_workflow),type:'workflow'}); 
    }
   
    $scope.getDownloadAttachmentUrl = function(objData){
        var fileName = objData.document_source;
        var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
        var d = {};
        d.id_document = objData.id_document;
        var encryptedPath= objData.encryptedPath;
        var filePath =API_URL+'Cron/preview?file='+encryptedPath;
        encodePath =encode(filePath);
        if(fileExtension=='pdf' || fileExtension=='PDF'){
            window.open(window.origin+'/Document/web/preview.html?file='+encodePath+'#page=1');
        }
        else{
            contractService.getUrl(d).then(function (result) {
                if(result.status){
                    var obj = {};
                    obj.action_name = 'download';
                    obj.action_description = 'download$$attachment$$('+ objData.document_name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = location.href;
                    if(AuthService.getFields().data.parent){
                        obj.user_id = AuthService.getFields().data.parent.id_user;
                        obj.acting_user_id = AuthService.getFields().data.data.id_user;
                    }
                    else obj.user_id = AuthService.getFields().data.data.id_user;
                    if(AuthService.getFields().access_token != undefined){
                        var s = AuthService.getFields().access_token.split(' ');
                        obj.access_token = s[1];
                    }
                    else obj.access_token = '';
                    $rootScope.toast('Success',result.message);
                    userService.accessEntry(obj).then(function(result1){
                        if(result1.status){
                            if(DATA_ENCRYPT){
                                result.data.url =  GibberishAES.enc(result.data.url, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                result.data.file =  GibberishAES.enc(result.data.file, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                            }
                            window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+$rootScope.id_user+'&access_token='+obj.access_token;
                        }
                    });
                }
                else{
                    $rootScope.toast('Error',result.error);
                }
            });
        }
      
    };
   
   $scope.disable = false;
  // $scope.isView = false;
    $scope.updateProjectDetails =function (obj,val){
        $scope.info=val;
        if (val == 1){
            $scope.templateUrl ="views/Manage-Users/contracts/update-project-info-modal.html" ; 
         } 
         if (val== 2){
            $scope.templateUrl ="views/Manage-Users/contracts/project-tabs-info-modal.html" ; 
         }
         if(val==3){
            $scope.templateUrl ="views/Manage-Users/contracts/project-contracts.html"; 
         }
        $scope.selectedRow = obj;
        var modalInstance = $uibModal.open({
            nimation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: $scope.templateUrl,
            controller: function ($uibModalInstance, $scope,item) {
                $scope.bottom = 'general.update';
                //for provider attchemts open//
                $scope.fdata = {};
                $scope.isView = false;
                $scope.isLink = false;
                $scope.contractParters={};
                $scope.contractLinks=[];
                $scope.contractLink={};
                var params ={};

                $scope.uploadAttachment = function (fData) {
                    $scope.isView = true;
                    var params = {};
                    params.file = fData.file.attachments
                    params.customer_id = $scope.user1.customer_id;
                    params.module_id = decode($stateParams.id);
                    params.module_type = 'project';
                    params.reference_type= 'project';
                    params.is_workflow =$scope.isWorkflow;
                    params.reference_id = decode($stateParams.id);
                    params.document_type =0;
                    params.contract_workflow_id = decode($stateParams.wId);
                    params.uploaded_by = $scope.user1.id_user;
                    contractService.uploaddata(params).then(function (result) {
                      if(result.status){
                        $rootScope.toast('Success',result.message);
                        $scope.fdata.file=[];
                        $scope.isView = false;
                        $scope.getInfo();
                        $scope.init();
                      }
                      else{
                          $scope.isView = false;
                        $rootScope.toast('Error',result.error,'error');
                      }
                    })
                }
               
                $scope.verifyLink = function(data){
                    $scope.isLink = false;
                    if(data !={}){
                        $scope.contractLinks.push(data);
                        $scope.contractLink={};
                    }
                }
                $scope.removeLink = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                        $scope.contractLinks.splice(index, 1);
                    }
                }
                $scope.uploadLinks = function (contractLinks,data) {
                    $scope.isLink = true;
                    if($scope.contractLinks.length>0){
                        Upload.upload({
                            url: API_URL+'Document/add',
                            data:{
                                file:contractLinks,
                                customer_id: $scope.user1.customer_id,
                                module_id: decode($stateParams.id),
                                module_type: 'project',
                                is_workflow:$scope.isWorkflow,
                                reference_id: decode($stateParams.id),
                                reference_type: 'project',
                                document_type : 1,
                                contract_workflow_id : decode($stateParams.wId),
                                uploaded_by: $scope.user1.id_user
                            }
                        }).then(function (resp) {
                            $scope.showUpload = false;
                            if(resp.data.status){
                                $rootScope.toast('Success',resp.data.message);
                                $scope.contractLinks=[];
                                $scope.isLink = false;
                                var obj = {};
                                obj.action_name = 'upload';
                                obj.action_description = 'upload$$link$$for$$contract$$('+$stateParams.name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = window.location.href;
                                $rootScope.confirmNavigationForSubmit(obj);
                            }
                            else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                            $scope.getInfo();
                            $scope.init();
                            $scope.isLink = false;
                            //$scope.cancel();
                        }, function (resp) {
                            $rootScope.toast('Error',resp.data.error,'error');
                        }, function (evt) {
                            $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                        });
                    }else {
                        $rootScope.toast('Error','No link added','image-error');
                    }
                }



                $scope.deleteAttachment = function(id,name){
                    console.log(id);
                    var r=confirm($filter('translate')('general.alert_continue'));
                    $scope.deleConfirm = r;
                    if(r==true){
                        var params = {};
                        params.id_document = id;
                        attachmentService.deleteAttachments(params).then (function(result){
                            if(result.status){
                                $rootScope.toast('Success',result.message);
                                var obj = {};
                                obj.action_name = 'delete';
                                obj.action_description = 'delete$$Attachment$$('+name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.getInfo();
                                $scope.init();
                            }else{$rootScope.toast('Error',result.error,'error');}
                        })
                    }
                }        


                $scope.changeLockingStatus = function(info){
                    var params={};
                    params.id_document = info.id_document;
                    contractService.lockingStatus(params).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success', result.message);
                            $scope.getInfo();
                            $scope.init();
                        }
                    });
                }
                //for provider attachments close//

                //for connected contracts open//

                $scope.connectedContracts =[];
                $scope.connectedContractsList =function(){
                    projectService.getConnectedContracts({'customer_id':$scope.user1.customer_id,'project_id':decode($stateParams.id)}).then(function(result){
                        if(result.status){
                            $scope.connectedContracts = result.data;
                        }
                    })
                  
                }

                //$scope.connectedContractsList();
                $scope.removeConnectedContract = function (row) {
                    var r=confirm($filter('translate')('general.alert_remove_link_project'));     //added by ashok
                    if(r==true){
                        var params = {};
                        params.contract_id = row.id_contract;
                        params.user_role_id = $scope.user1.user_role_id;
                        params.id_user = $scope.user1.id_user;  
                        params.customer_id =  $scope.user1.customer_id;
                        params.project_id = decode($stateParams.id);
                        projectService.deleteConnectedContracts(params).then(function (result) {
                            if(result.status){
                                var obj = {};
                                obj.action_name = 'Delete';
                                obj.action_description = 'contract delete $$('+result.data.file_name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = location.href;
                                if(AuthService.getFields().data.parent){
                                    obj.user_id = AuthService.getFields().data.parent.id_user;
                                    obj.acting_user_id = AuthService.getFields().data.data.id_user;
                                }
                                else obj.user_id = AuthService.getFields().data.data.id_user;
                                if(AuthService.getFields().access_token != undefined){
                                    var s = AuthService.getFields().access_token.split(' ');
                                    obj.access_token = s[1];
                                }
                                else obj.access_token = '';
                                $rootScope.toast('Success',result.message);
                                setTimeout(function(){
                                    //$uibModalInstance.close();
                                    $scope.getInfo();
                                    $scope.init();
                                    $scope.connectedContractsList();
                                },300);
                            }
                        });
                    }
                    
                }
                $scope.connectedContractsList();

                //for connected contracts close//
                $scope.cancel = function(){
                    $uibModalInstance.close();
                }
                $scope.getInfo = function(){
                    var par = {};
                    par.customer_id  = $scope.user1.customer_id;
                    par.project_id  = $scope.project_id;
                    par.id_user  = $scope.user1.id_user;
                    par.user_role_id  = $scope.user1.user_role_id;
                    projectService.projectInfo(par).then (function(result){
                        if(result.status){
                            $scope.infoObj = result.data[0];
                           $scope.connected_contracts= result.connected_contracts;
                           $scope.project_attachments= result.project_attachments;
                           $scope.project_info= result.project_info;
                            $scope.infoObj.contract_start_date = moment($scope.infoObj.contract_start_date).utcOffset(0, false).toDate();
                            if($scope.infoObj.contract_end_date)$scope.infoObj.contract_end_date = moment($scope.infoObj.contract_end_date).utcOffset(0, false).toDate();
                            $scope.getContractDelegates($scope.infoObj.business_unit_id,$scope.infoObj.id_contract);
                            if($scope.infoObj.can_review==1)
                                $scope.enableTemplate = true;
                            else $scope.enableTemplate = false;
                        }
                    });
                }
                $scope.getInfo();
                $scope.getContractDelegates = function (id,contractId){
                    contractService.getDelegates({'id_business_unit': id,'contract_id':decode($stateParams.id)}).then(function(result){
                        $scope.delegates = result.data;
                    });
                    var params = {};
                    params.type = "buowner";
                    params.business_unit_id = id;
                    params.contract_id = contractId;
                    // console.log('params--', params);
                    contractService.getbuOwnerUsers(params).then(function(result){
                        $scope.buOwnerUsers = result.data;
                    });
                } 
              
                // masterService.currencyList({'customer_id'}).then(function(result){
                //     $scope.currencyList = result.data;
                // });
                masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                    $scope.currencyList = result.data;
                });
                $scope.updateProjectInfo = function(data){
                    console.log('data info',data);
                    var postData = angular.copy(data);
                    postData.contract_start_date = dateFilter(data.contract_start_date,'yyyy-MM-dd');
                    postData.contract_end_date = dateFilter(data.contract_end_date,'yyyy-MM-dd');
                    postData.customer_id=$scope.user1.customer_id;
                    postData.updated_by = $scope.user.id_user;

                    Upload.upload({
                        url: API_URL+'Project/updateProject',
                        data: {
                            'contract': postData
                        }
                    }).then(function(resp){
                        if(resp.data.status){
                            $rootScope.toast('Success',resp.data.message);
                            var obj = {};
                            obj.action_name = 'update';
                            obj.action_description = 'update$$contract$$'+postData.contract_name;
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.getInfo();
                            $scope.init();
                            //$uibModalInstance.close();
                        }
                        else{
                            $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                        }
                    },function(resp){
                        //$rootScope.toast('Error',resp.error);
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
    }
    $scope.connectedContractsList();
    $scope.createContractReview = function (row, type) {
        $scope.type = type;
        $scope.contract_id =  decode($stateParams.id);
        $scope.selectedRow = row;
        $scope.contract = {};
        $scope.data = {};
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: ' views/Manage-Users/contracts/create-edit-project-review.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.update = false;
                $scope.title = 'contract.create_action_item';
                $scope.bottom = 'general.save';
                $scope.isEdit = false;
                $scope.addaction = true;
                $scope.data.due_date=moment().utcOffset(0, false).toDate();
                
                if($scope.type == 'view') $scope.bottom = 'contract.finish';
                contractService.getActionItemResponsibleUsers({'contract_id': $scope.contract_id,'project_id': decode($stateParams.id),'type':'project'}).then(function(result){
                    $scope.userList = result.data;
                });
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                $scope.goToEdit = function(data){
                    $scope.data.due_date = moment(data.due_date).utcOffset(0, false).toDate();
                }
                var params ={};
                $scope.addReviewActionItem=function(data){
                    $scope.due_date=angular.copy(data.due_date);
                    $scope.due_date=dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                    // $scope.due_date=dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                    if($scope.type == 'view'){
                    //data.due_date = dateFilter(data.due_date,'yyyy-MM-dd');
                        var params = [];
                        params = angular.copy(data);
                        params.id_contract_review_action_item = data.id_contract_review_action_item;
                        delete params.description;
                        params.comments = data.comments;
                        params.is_finish = data.is_finish;
                        params.updated_by = $scope.user.id_user;
                        params.contract_id  = decode($stateParams.id);
                        params.due_date  = $scope.due_date;
                        params.reference_type ='project';
                        if(data.is_finish  == 1) {
                            var r = confirm($filter('translate')('general.alert_action_finish'));
                            $scope.deleConfirm = r;
                            if (r == true) {
                                contractService.reviewActionItemUpdate(params).then(function (result) {
                                    if (result.status) {
                                        $rootScope.toast('Success', result.message);
                                        var obj = {};
                                        obj.action_name = 'Finish';
                                        obj.action_description = 'Finish$$Action$$Item$$('+data.action_item+')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);
                                        $scope.reviewAction($scope.tableStateRef);
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
                                    obj.action_name = 'Save';
                                    obj.action_description = 'Save$$Action$$Item$$('+data.action_item+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.reviewAction($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error,'error');
                                }
                            });
                        }
                    }
                    else if(typeof data == undefined){
                        //data.due_date = dateFilter(data.due_date,'yyyy-MM-dd');
                        var params ={};
                        delete data.comments;
                        params = angular.copy(data);
                        params.description = data.description;
                        params.updated_by = $scope.user.id_user;
                        params.provider_id =  $scope.id ;
                        params.id_user  = $scope.user1.id_user;
                        params.user_role_id  = $scope.user1.user_role_id;
                        params.due_date  = $scope.due_date;
                        contractService.addReviewActionItemList(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'update$$Action$$Item$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.reviewAction($scope.tableStateRef);
                                $scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });
                    }
                    else {
                        var params ={};
                        delete data.comments;
                        params = data ;
                        params.description = data.description;
                        delete params.comments;
                        params.contract_id = decode($stateParams.id);
                        params.created_by = $scope.user.id_user;
                        params.id_user  = $scope.user1.id_user;
                        params.user_role_id  = $scope.user1.user_role_id;
                        params.due_date  = $scope.due_date;
                        params.reference_type='project';
                        contractService.addReviewActionItemList(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'add';
                                obj.action_description = 'add$$Action$$Item$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                if(data.id_contract_review_action_item){
                                    $scope.getActionItemById(data.id_contract_review_action_item);
                                    $scope.cancel();
                                }else{$scope.cancel();}
                                $scope.reviewAction($scope.tableStateRef);
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
    }

    $scope.openUnAnswered = function(row,flag){
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'open-unanswered-questions.html',
            controller: function ($uibModalInstance, $scope,item) {
                $scope.module = item;
                $scope.isStored = flag;
                $scope.title = item.module_name;
                $scope.bottom = 'general.save';
                $scope.unAnsweredTopics = [];
                var params ={};               
                params.module_id = item.id_module;
                params.contract_review_id = item.contract_review_id ;
                params.all_questions = flag;
                contractService.getUnAnswered(params).then(function (result) {
                    if(result.status){
                        $scope.unAnsweredTopics=result.data;                      
                    }else $rootScope.toast('Error', result.error, 'error',$scope.user);
                });
                $scope.goToReview = function (row,topic) {
                    if($stateParams.rId)var reviewId = decode($stateParams.rId);
                    var moduleId = item.id_module;
                    var module_name = item.module_name;
                    var topic_name = topic.topic_name;
                    var topic_id = encode(topic.id_topic);
                    var topic_id = encode(topic.id_topic);
                    $state.go('app.projects.project-module-task',{
                        name:$stateParams.name,id:$stateParams.id,rId:encode(row.contract_review_id),mName:module_name,
                        moduleId:encode(moduleId),tName:topic_name,tId:topic_id,qId:encode(row.id_question),
                        wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false});
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

    $scope.storedModulesList =[];     
    $scope.getStoredModules = function(){
        var params ={};  
        params.contract_id  = decode($stateParams.id);
        contractService.getStoredModules(params).then(function(result){
            $scope.storedModulesList = result.data;
            angular.forEach($scope.storedModulesList.workflow,function(o,i){
                // console.log("order",o);
                if(o.date)o.date=moment(o.date).utcOffset(0, false).toDate();
                angular.forEach(o.project_subtasks,function(d,i){
                    $scope.provider_id=d.provider_id;
                })
            })
        });
    }
    $scope.getStoredModules();
    $scope.manageStoredModules = function(){
        $scope.storedModule = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open stored-model',
            templateUrl: 'manage-stored-modules.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.title = 'contract.manage_stored_modules';
                $scope.bottom = 'general.save';
                $scope.options = {
                    minDate: moment().utcOffset(0, false).toDate(),
                    showWeeks: false
                };
                $scope.updateStoredModules = function(row){
                    var params={};
                    params.id_stored_module=row.id_stored_module;
                    params.activate_in_next_review=row.activate_in_next_review;
                    if(row.date) params.date = dateFilter(row.date,'yyyy-MM-dd');
                    contractService.updateStoredModules(params).then(function(result){
                        if (result.status) {
                            var obj = {};
                            obj.action_name = 'Update';
                            obj.action_description = 'Manage$$Stored$$Modules$$('+row.module_name+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.getStoredModules();
                            $scope.init();
                            $rootScope.toast('Success', result.message);
                            if($scope.storedModulesList.review.length==0 && $scope.storedModulesList.workflow.length==1)
                                $scope.cancel();
                        } else {
                            $rootScope.toast('Error', result.error,'error');
                        }
                    });
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            },
            resolve: {}
        });
        $scope.storedModule.result.then(function ($data) {
        }, function () {
        });
    }
    $scope.showStoredModuleQuestions= function(row){
        console.log('row info',row);
        //$scope.openUnAnswered(row,true);
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/archive/project-archieve-data-modal.html',
            controller: function ($uibModalInstance, $scope,item) {
                $scope.title="";
                $scope.name= item.calander_workflow_name;
                $scope.isWorkflow=false;
                $scope.moduleTopics = [];
                $scope.title = 'workflows.workflow';
                $scope.isWorkflow=true;

                var params ={};               
                params.module_id = item.id_module;
                params.contract_review_id = item.contract_review_id ;
                params.project_id = decode($stateParams.id);
                params.is_workflow=1;
                params.contract_workflow_id= item.contract_workflow_id;
                params.type='archieve';
                projectService.getProjectDashboard(params).then(function (result) {
                    if(result.status){
                        $scope.dashboardData =  result.data;
                        console.log("total ds",$scope.dashboardData.subtask_lists);
                        $scope.submittedBy = result.submitted.submitted_by;
                        $scope.submittedOn = result.submitted.submetted_on;
                    }else $rootScope.toast('Error', result.error, 'error',$scope.user);
                });
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

    $scope.getDataByReviewDate = function(row,type){
        console.log('in popup',row);
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/archive/review-date-data-modal.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.title="";
                $scope.name= item.templateName;
                $scope.subtask=item.is_subtask;
                $scope.projectType=type;
                if(item.is_subtask !='0'){  $scope.provider_name = item.provider_name;}
                $scope.isWorkflow=false;
                $scope.moduleTopics = [];
                if(item) { 
                    if(item.is_workflow=='1'){
                        $scope.title = 'workflows.workflow';
                        $scope.isWorkflow=true;

                        var params ={};               
                        params.module_id = item.module_id;
                        params.contract_review_id = item.contract_review_id ;
                        params.all_questions = true;
                        contractService.getUnAnswered(params).then(function (result) {
                            if(result.status){
                                $scope.moduleTopics = result.data;
                                $scope.projectType=result.type;
                                $scope.submittedBy = result.submitted.submitted_by;
                                $scope.submittedOn = result.submitted.submetted_on;
                            }else $rootScope.toast('Error', result.error, 'error',$scope.user);
                        });
                    }
                    else {
                        $scope.title = 'contract.review';

                        var params={};
                        params.contract_id = item.contract_id;
                        params.contract_review_id = item.contract_review_id;
                        params.is_workflow = $scope.isWorkflow;
                        params.id_user  = $scope.user1.id_user;
                        params.user_role_id  = $scope.user1.user_role_id;

                        contractService.getDashboard(params).then(function(result){
                            if(result.status)
                                $scope.moduleTopics =  result.data.modules;
                                $scope.projectType=result.type;
                                $scope.submittedBy = result.data.submitted_by;
                                $scope.submittedOn = result.data.submetted_on;
                        })
                    }
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
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
    }

      $scope.previewFeedback=function(row) { 
        $scope.selectedRow = row.question_feedback;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'view-feedback.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            },
        });    
    }

    $scope.previewExternalFeedback=function(row) { 
        $scope.selectedRow =row.external_user_question_feedback;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'view-external-feedback.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.cancel = function () {
                    $uibModalInstance.close();
                   
                };
            },
        });    
    }
  
    $scope.previewAttachments =function (data,flag){
        $scope.selectedRow =angular.copy(data);
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'view-question-attachments.html',
            controller: function ($uibModalInstance, $scope,item) {
                console.log(flag);
                $scope.isWorkflow=flag;
                $scope.attachments=item.attachments;
                $scope.question_text=item.question_text;
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


    $scope.createSubContract = function(){
        if($scope.isWorkflow=='1')
            $state.go('app.contract.create-sub-contract', {name:$scope.contractInfo.contract_name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
        else
            $state.go('app.contract.create-sub-contract', {name:$scope.contractInfo.contract_name,id:$stateParams.id,type:'review'});
    }

    $scope.shiftToNewDetails = function(item) {
        var obj ={};
        obj.id_contract = decode($stateParams.id);
        obj.contract_name = $stateParams.name;
        obj.id_contract_review = item.id_contract_workflow;
        $scope.goToReviewWorkflow(item,obj);
    }
    $scope.goToReviewWorkflow = function(type,row){
        if(type.initiated){
            $state.go('app.projects.project-task',{name:row.contract_name, id:encode(row.id_contract),rId:encode(type.id_contract_review),
                wId:encode(type.id_contract_workflow),type:'workflow'});
        }
        else{
            $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract),wId:encode(type.id_contract_review), wId:encode(type.id_contract_workflow),type:'workflow'});
        }
    }
    $scope.UpdateContractTags = function (row) {
        $scope.selectedRow = angular.copy(row);
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/update-tags-modal.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.update = false;
                $scope.bottom = 'general.save';
                $scope.tagsOptions = {
                    minDate: moment().utcOffset(0, false).toDate(),
                    showWeeks: false
                };
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                $scope.tagsData = [];
                $scope.tagsData = angular.copy(item);
                
                angular.forEach($scope.tagsData,function(i,o){                    
                    if(i.tag_type=='date'){
                        var d = (i.tag_answer)?moment(i.tag_answer).utcOffset(0, false).toDate():'';
                        i.tag_answer = d;
                    }
                    else{}
                });
                $scope.updateTags = function(data){
                    angular.forEach(data,function(i,o){
                        if(i.tag_type=='date'){
                            i.tag_answer = dateFilter(i.tag_answer,'yyyy-MM-dd');
                        }
                    });
                    var params ={};
                    params.id_contract = $scope.contract_id;
                    params.tag_type = 'contract_tags';
                    params.contract_tags = data;
                    tagService.updateContractTags(params).then(function(result){
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'Update';
                            obj.action_description = 'Update$$Contract$$Tags$$('+$stateParams.name+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.init();
                            $scope.cancel();
                        } else {
                            $rootScope.toast('Error', result.error,'error');
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
    }
    $scope.showfeedback=function(row) { 
        $scope.selectedRow =row.question_feedback;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'sm',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'show-feedback.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.cancel = function () {
                    $uibModalInstance.close();
                   
                };
            },
        });    
    }
    $scope.showAttachments =function (row){
        $scope.selectedRow =angular.copy(row);
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'show-question-attachments.html',
            size:'lg',
            controller: function ($uibModalInstance, $scope,item) {
                //console.log('item info',item);
                $scope.attachments=item.attachments;
                $scope.question_text=row.question_text;
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
    $scope.showdiscussion =function(row){
        $scope.selectedRow =row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'discussion-show.html',
            controller: function ($uibModalInstance, $scope,item) {
                $scope.discussDetails =item.discussion.log;
                $scope.question=item;
                if($scope.question.question_type=='date'){
                    $scope.question.question_answer = moment($scope.question.question_answer).utcOffset(0, false).toDate();
                    $scope.question.second_opinion = moment($scope.question.second_opinion).utcOffset(0, false).toDate();
                }
                $scope.question_text=item.question_text;
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
   
    $scope.initializeReview = function (val){
        var params ={};
        params.created_by = $scope.user.id_user;
        params.customer_id = $scope.user1.customer_id;
        params.contract_id = decode($stateParams.id);
        params.is_workflow = $scope.reviewWorkflowInfo[0].is_workflow;
        params.calender_id = $scope.reviewWorkflowInfo[0].calender_id;
        params.id_contract_workflow= $scope.reviewWorkflowInfo[0].id_contract_workflow;
        params.contract_review_type = 'adhoc_workflow';
        params.type ='project';
        //console.log('params info',params);
        projectService.initializeProjectReview(params).then(function(result){
            if(result.status){
                $rootScope.toast('Success', result.message);
                var obj = {};
                obj.action_name = 'initiate';
                obj.action_description = ($scope.isWorkflow=='1')?'initiate$$Task$$('+$stateParams.name+')':'initiate$$Review$$('+$stateParams.name+')';
                obj.module_type = $state.current.activeLink;
                obj.action_url = $location.$$absUrl;
                $rootScope.confirmNavigationForSubmit(obj);
                $state.go('app.projects.project-task',{name:$stateParams.name,id:$stateParams.id,rId:encode(result.data),wId:encode($scope.reviewWorkflowInfo[0].id_contract_workflow),type:'workflow'});
            }else  $rootScope.toast('Error', result.error,'error',$scope.user);
        });
    } 
    $scope.changeLockingStatus = function(info){
        var params={};
        params.id_document = info.id_document;
        contractService.lockingStatus(params).then(function(result){
            if(result.status){
                $rootScope.toast('Success', result.message);
                $scope.init();
            }
        });
    }

    $scope.deleteProviderFromProject = function(row){
        var r=confirm($filter('translate')('general.alert_continue'));
        if(r==true){
            var params ={};
            params.project_id = decode($stateParams.id);
            params.provider_id = row.id_provider;
            projectService.deleteProviderFromProject(params).then(function (result) {
                if(result.status){
                    var obj = {};
                    obj.action_name = 'Delete';
                    obj.action_description = 'provider delete $$('+result.data.file_name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = location.href;
                    if(AuthService.getFields().data.parent){
                        obj.user_id = AuthService.getFields().data.parent.id_user;
                        obj.acting_user_id = AuthService.getFields().data.data.id_user;
                    }
                    else obj.user_id = AuthService.getFields().data.data.id_user;
                    if(AuthService.getFields().access_token != undefined){
                        var s = AuthService.getFields().access_token.split(' ');
                        obj.access_token = s[1];
                    }
                    else obj.access_token = '';
                    $rootScope.toast('Success',result.message);
                    setTimeout(function(){
                        $scope.ProvidersLists($scope.tableStateRef1);
                        $scope.getProviderusers($scope.tableStateRef2);
                        $scope.init();
                    },300);
                }
                else{
                    //$rootScope.toast('error',result.message);
                    $rootScope.toast('Error', result.error,'error');
                }
            });
        }
       
    }

    $scope.addTaskInCalendar = function (title, flag, row) {
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal calendar-popup modal-open',
            templateUrl: 'add-review-calendar.html',
            controller: function ($uibModalInstance, $scope, item) {
                //console.log('item info',item);
                $scope.customOptions = {};
                $scope.customOptions.bussiness_unit_id =[];
                $scope.customOptions.contract_id =[];
                $scope.bottom = 'general.save';
                $scope.action = 'general.add';
                $scope.update = false;
                $scope.isEdit = false;
                $scope.addType = flag;
                $scope.validateRecurrence = function () {
                    $scope.options1 = {};
                    var dt = angular.copy(($scope.customOptions.date) ? $scope.customOptions.date : moment().utcOffset(0, false).toDate());
                    if ($scope.customOptions.recurrence == '1') dt.setMonth(dt.getMonth() + 1);
                    if ($scope.customOptions.recurrence == '2') dt.setMonth(dt.getMonth() + 3);
                    if ($scope.customOptions.recurrence == '3') dt.setFullYear(dt.getFullYear() + 1);
                    if ($scope.addType) $scope.customOptions.recurrence_till = null;
                    $scope.options1 = {
                        minDate: dt,
                        showWeeks: false
                    };
                }
                $scope.head = 'calender.' + title;

                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                 $scope.options = {
                    minDate: moment().utcOffset(0, false).toDate(),
                    showWeeks: false
                };
                $scope.options1 = angular.copy($scope.options);
                if (!flag) {
                    $scope.workflowsList = [];
                    var obj = {};
                    obj.is_workflow = true;
                    obj.status = 1;
                    moduleService.list(obj).then(function (result) {
                        if (result.status) {
                            $scope.workflowsList = result.data.data;
                        }
                    });
                }
                var params={};
                if (!flag) params.is_workflow = true;
                $scope.getFilters = function () {
                    params.customer_id = $scope.user1.customer_id;
                    params.business_ids =item.business_unit_id;
                    params.type='project';
                    calenderService.smartFilter(params).then(function (result) {
                        if (result.status) {
                            $scope.relationCategory = result.data.relationship_list;
                            $scope.business_units = result.data.business_unit;
                            $scope.providers = result.data.provider;
                            $scope.contracts = result.data.contract;
                            $scope.completed_contracts = result.data.completed_contracts;
                            $scope.customOptions.bussiness_unit_id.push(item.business_unit_id);
                            $scope.customOptions.contract_id.push(item.id_contract);
                        }
                       
                    });
                }
                $scope.getFilters();
               
               
                var params1=[];
                $scope.getSmartFilters = function (key) {
                    if (!flag) params1.is_workflow = true;
                    params1.type ='project';
                    if (key == "bussiness_unit_id") {
                        $scope.customOptions.relationship_category_id = [];
                        $scope.customOptions.contract_id = [];
                        $scope.customOptions.provider_id = [];
                    }
                    if (key == "relationship_category_id" && !$scope.isEdit) {
                        $scope.customOptions.contract_id = [];
                        $scope.customOptions.provider_id = [];
                    }
                    if (key == "provider_id")
                        $scope.customOptions.contract_id = [];
                    
                    if ($scope.customOptions.bussiness_unit_id)
                        params1["business_ids"] = $scope.customOptions.bussiness_unit_id.toString();
                    if (params1['business_ids'] == '') delete params1['business_ids'];
                    calenderService.smartFilter(params1).then(function (result) {
                        if (result.status) {
                            $scope.relationCategory = result.data.relationship_list;
                            $scope.business_units = result.data.business_unit;
                            $scope.providers = result.data.provider;
                            $scope.contracts = result.data.contract;
                        }
                    });
                }
                $scope.addReview = function (formData) {

                    var data = angular.copy(formData);
                    data.customer_id = $scope.user1.customer_id;
                    data.created_by = $scope.user.id_user;
                    data.type='project';
                    if ($scope.customOptions.relationship_category_id)
                        data.relationship_category_id = $scope.customOptions.relationship_category_id.toString();
                    if ($scope.customOptions.bussiness_unit_id) {
                        data.business_unit_id = $scope.customOptions.bussiness_unit_id.toString();
                        delete data.bussiness_unit_id;
                    }
                    if ($scope.customOptions.provider_id)
                        data.provider_id = $scope.customOptions.provider_id.toString();
                    if ($scope.customOptions.contract_id)
                        data.contract_id = $scope.customOptions.contract_id.toString();
                    data.date = dateFilter($scope.customOptions.date, 'yyyy-MM-dd');
                    if ($scope.customOptions.recurrence_till)
                        data.recurrence_till = dateFilter($scope.customOptions.recurrence_till, 'yyyy-MM-dd');
                    if (!flag) data.is_workflow = true;

                    if (!data.provider_id) delete data.provider_id;
                    if (!data.contract_id) delete data.contract_id;

                    if (flag) {
                        data.workflow_name = data.review_name;
                        delete data.review_name;
                    }
                    if(data.auto_initiate==1){
                        if(flag){
                            var alert1 = ($filter('translate')('normal.alert_review'))
                            var alert2 = ($filter('translate')('normal.alert_review_initiate'))
                            var str = '<span style="font-style: normal;">'+alert1+'</span> <br><br><span><b>NOTE :</b>&nbsp;'+alert2+'</span>'
                        }
                        else{
                            var alert1 = ($filter('translate')('normal.alert_task'))
                            var alert2 = ($filter('translate')('normal.alert_task_initiate'))
                            var str = '<span style="font-style: normal;">'+alert1+'</span> <br><br><span><b>NOTE :</b>&nbsp;'+alert2+'</span>'
                        }
                        // var str = '<span style="font-style: normal;">'+alert1+'</span> <br><br><span><b>NOTE :</b>&nbsp;'+alert2+'</span>'

                        // if(flag) var str = '<span style="font-style: normal;">Are you sure you want to automatically initiate ALL reviews in this calendar planning?</span> <br><br><span><b>NOTE :</b>&nbsp; Reviews will be initiated after 10 minutes of successful planning.</span>';
                        // else var str = '<span style="font-style: normal;">Are you sure you want to automatically initiate ALL tasks in this calendar planning?</span><br><br><span><b>NOTE :</b>&nbsp; Tasks will be initiated after 10 minutes of successful planning.</span>';
                        var modalInstance = $uibModal.open({
                            animation: true,
                            backdrop: 'static',
                            keyboard: false,
                            scope: $scope,
                            openedClass: 'right-panel-modal modal-open adv-search-model',
                            templateUrl: 'confirm-dialog.html',
                            controller: function ($uibModalInstance, $scope) {
                                $scope.val_data = $sce.trustAsHtml(str);
                                 console.log($scope.val_data);
                                $scope.saidOk = function(){
                                    $scope.serviceCall(data);
                                    $scope.cancel();
                                }
                                $scope.cancel = function () {
                                    $uibModalInstance.close();
                                };
                            }
                        });
                       
                    }else {
                        $scope.serviceCall(data);
                    }                        
                }
                $scope.serviceCall = function(data){
                    calenderService.addReview(data).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            $scope.cancel();
                            //$scope.callServer($scope.tableStateRef);
                            $scope.init();
                        } else $rootScope.toast('Error', result.error.message);
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
    }

    $scope.scrlTabsApi = {};
    $scope.reCalcScroll = function() {
        if($scope.scrlTabsApi.doRecalculate) {
            $scope.scrlTabsApi.doRecalculate();
        }
    };
    $scope.scrollIntoView = function(arg) {
      if($scope.scrlTabsApi.scrollTabIntoView) {
        $scope.scrlTabsApi.scrollTabIntoView(arg)
      }
    };
    $scope.activateTab = function(tab,index) {
        $timeout(function () {
            $scope.scrollIntoView(index);
        });
    }
})
.controller('projectReviewCtrl', function($sce,$scope, $rootScope, $state,$filter, $stateParams, contractService,projectService,userService, decode, encode, $uibModal, attachmentService, $location, dateFilter,businessUnitService){
    $rootScope.module = 'Project';
    $rootScope.displayName = $stateParams.name;
    $rootScope.icon = "Projects";
    $rootScope.class ="project-logo";
    $rootScope.breadcrumbcolor='project-breadcrumb-color'; 
    $scope.displayCount = $rootScope.userPagination;
    var params = {};
    $scope.loading = false;
    $scope.isWorkflow='0';
    var parentPage = $state.current.url.split("/")[1];    
    $scope.getProjectContributorsInfo = function(){
        if($stateParams.type)$scope.isWorkflow=($stateParams.type=='workflow')?'1':'0';
        params.contract_id = params.id_contract = decode($stateParams.id);
        params.project_id = decode($stateParams.id);
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.id_contract_workflow  = decode($stateParams.wId);
        params.contract_review_id= decode($stateParams.rId);
        params.is_workflow  = $scope.isWorkflow; 
        contractService.contractModule(params).then(function(result){
            if(result.status){
                $scope.contractModules = result.data;
                $scope.modulesValidated = result.all_modules_validate;
                $scope.no_modules = false;
                if(result.data == ''){$scope.no_modules = true;}
                $scope.loading = false;
                if($scope.contractModules)$scope.loading = true;
            }
        });
    }

    $scope.getProjectContributorsInfo();
   
   
    $scope.openUnAnswered = function(row,flag){
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'open-unanswered-questions.html',
            controller: function ($uibModalInstance, $scope,item) {
                $scope.module = item;
                $scope.isStored = flag;
                $scope.title = item.module_name;
                $scope.bottom = 'general.save';
                $scope.unAnsweredTopics = [];
                var params ={};               
                params.module_id = item.id_module;
                if($stateParams.rId)params.contract_review_id = decode($stateParams.rId);
                params.all_questions = flag;
                contractService.getUnAnswered(params).then(function (result) {                    
                    if(result.status){
                        $scope.unAnsweredTopics=result.data;
                       // $rootScope.toast('Success', result.message);                       
                    }else $rootScope.toast('Error', result.error, 'error',$scope.user);
                });
                $scope.goToReview = function (row,topic) {
                    if($stateParams.rId)var reviewId = decode($stateParams.rId);
                    var moduleId = item.id_module;
                    var module_name = item.module_name;
                    var topic_name = topic.topic_name;
                    var topic_id = encode(topic.id_topic);
                    var topic_id = encode(topic.id_topic);

                    if($rootScope.access=='eu'){
                        $state.go('app.projects.project-module-task11',{
                            name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,
                            moduleId:encode(moduleId),tName:topic_name,tId:topic_id,qId:encode(row.id_question),
                            wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false});
                    }else{
                        $state.go('app.projects.project-module-task',{
                            name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,
                            moduleId:encode(moduleId),tName:topic_name,tId:topic_id,qId:encode(row.id_question),
                            wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false});

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
    //$scope.discussion_access =false;
    //$scope.loading = false;
    $scope.info = function() {
        projectService.projectInfo(params).then (function(result){
            $scope.progress = result.data.progress;
            $scope.all_modules_validated = result.data.all_modles_validated;
            $scope.ready_for_validation = result.data[0].ready_for_validation;
            $scope.validation_status = result.data[0].validation_status;
            $scope.contractData = result.data[0];
            $scope.reviewWorkflowInfo = {};
            $scope.reviewWorkflowInfo = result.data[0].project_task;
            // $scope.projectreviewInfo ={};
            // $scope.projectreviewInfo= result.data[0].project_first_task;
            if( $scope.contractData){
                $scope.review_access = true;
                if($scope.contractData.reaaer != "itako"){$scope.review_access = false;}
                if($scope.contractData.ideedi != 'annus'){$scope.discussion_access = true;}
                
            }
            if($scope.reviewWorkflowInfo.is_workflow==0){
                var str = '<div><div style="text-align:left;">Recurrence : '+$scope.reviewWorkflowInfo.recurrenc+' </div><div style="text-align:left;"> Recurrence till : '+ dateFilter($scope.reviewWorkflowInfo.recurrence_till,'MMM dd,yyyy')+'</div></div>';
                $scope.htmlTooltip = $sce.trustAsHtml(str);
            }
        })
    }
    $scope.info();
    $scope.inviteValidatorModel = function(){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'invite-validator.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.bottom = 'general.yes';
                $scope.inviteValidator =function(){
                    if(!$scope.contractData.validation_contributor){
                        var params ={};               
                        params.validation_status = 2;
                        params.is_workflow=$scope.reviewWorkflowInfo[0].is_workflow;
                        params.contract_review_id= $scope.reviewWorkflowInfo[0].id_contract_review;
                        params.type='project';
                    }else {
                        var params ={};
                        params.validation_status=3;
                        params.is_workflow=$scope.reviewWorkflowInfo[0].is_workflow;
                        params.contract_review_id= $scope.reviewWorkflowInfo[0].id_contract_review;
                        params.type='project';
                    }
                     contractService.ProcessValidation(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            $scope.info();
                            //var goView = (parentPage == 'all-activities') ? 'app.contract.view1' : 'app.contract.view';
                    
                            if($scope.contractData.validation_contributor){
                               $state.go('app.dashboard');
                            }else{

                                $state.go('app.projects.view',{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
                                //if($scope.isWorkflow=='1')
                                //    $state.go(goView,{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
                                //else
                                   // $state.go(goView,{name:$stateParams.name,id:$stateParams.id,type:'review'});
                            }
                            $scope.cancel();                            
                        } else {
                            $rootScope.toast('Error', result.error,'error');
                        }
                    });

                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            },
            resolve: {
                item: function () {
                }
            }
        });
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    }
    var parentPage = $state.current.url.split("/")[1];
    $scope.goToDetails = function(){
        $state.go('app.projects.view',{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,rId:$stateParams.rId,type:'workflow'});
    }
    $scope.goToModuleQuestions = function (module) {
        console.log('name',module.subtask_name);
        var reviewId = module.contract_review_id;
        var moduleId = module.id_module;
        var module_name = module.module_name;
        var topic_name = module.default_topic.topic_name;
        var topic_id = encode(module.default_topic.id_topic);
        if(module.subtask_name !='') var pname = module.subtask_name;
        if($rootScope.access !='eu'){
            $state.go('app.projects.project-module-task',{name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,moduleId:encode(moduleId),
                tName:topic_name,pname:pname,tId:topic_id,wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
        }
        if($rootScope.access =='eu'){
            $state.go('app.projects.project-module-task11',{name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,moduleId:encode(moduleId),
                tName:topic_name,pname:pname,tId:topic_id,wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
        }
        
    }
    $scope.reviewAction = function (tableState){
        $scope.tableStateRef = tableState;
        $scope.isLoading = true;
        var pagination = tableState.pagination;
        //tableState.contract_review_id = decode($stateParams.rId);
        if($stateParams.rId)tableState.contract_review_id = decode($stateParams.rId);
        tableState.contract_id = decode($stateParams.id);
        tableState.id_user  = $scope.user1.id_user;
        tableState.user_role_id  = $scope.user1.user_role_id;
        tableState.is_workflow  = $scope.isWorkflow;
        tableState.contract_workflow_id  = decode($stateParams.wId);
        tableState.action_item_type  = 'inside';
        contractService.reviewActionItemList(tableState).then (function(result){
            $scope.reviewList = result.data.data;
            $scope.reviewListCount = result.data.count;
            $scope.emptyTable=false;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords = result.data.total_records;
            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
            $scope.isLoading = false;
            if(result.data.total_records < 1)
                $scope.emptyTable=true;
        })
    }
    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.reviewAction($scope.tableStateRef);
            }                
        });
    }
    $scope.updateContractAction = function (row, type) {
        $scope.type = type;
        $scope.selectedRow = row;
        $scope.data={};
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/create-edit-project-review.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.update = false;
                $scope.title = item.action_item;
                $scope.bottom = 'general.save';
                $scope.isEdit = false;
                if (item != 0 &&  item.hasOwnProperty('id_contract_review_action_item')) {
                    $scope.isEdit = true;
                    $scope.submitStatus = true;
                    $scope.data = angular.copy(item);
                    delete $scope.data.comments;
                    $scope.data.due_date = moment($scope.data.due_date).utcOffset(0, false).toDate();
                    $scope.update = true;
                    $scope.bottom = 'general.update';
                    $scope.addaction = false;
                }else{$scope.data.due_date=moment().utcOffset(0, false).toDate();}

                if($scope.type == 'view')
                    $scope.bottom = 'contract.finish';
                contractService.getActionItemResponsibleUsers({'contract_id': decode($stateParams.id),'contract_review_id': decode($stateParams.rId),'project_id':decode($stateParams.id),type:'project'}).then(function(result){
                    $scope.userList = result.data;
                });
                $scope.getActionItemById = function(id){
                    contractService.getActionItemDetails({'id_contract_review_action_item':id}).then(function(result){
                        $scope.data = result.data[0];
                    });
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                $scope.goToEdit = function(data){
                    $scope.data.due_date = moment(data.due_date).utcOffset(0, false).toDate();
                }
                var params ={};
                $scope.addReviewActionItem=function(data){
                    //data.due_date = dateFilter(data.due_date,'yyyy-MM-dd');
                    $scope.due_date = angular.copy(data.due_date);
                    $scope.due_date = dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                    if($scope.type == 'view'){
                        params.id_contract_review_action_item = data.id_contract_review_action_item;
                        params.comments = data.comments;
                        params.is_finish = data.is_finish;
                        params.updated_by = $scope.user.id_user;
                        params.contract_id  = decode($stateParams.id);
                        params.due_date  = $scope.due_date;
                        params.reference_type = 'project';
                        if(data.is_finish  == 1) {
                            var r = confirm($filter('translate')('general.alert_action_finish'));
                            $scope.deleConfirm = r;
                            if (r == true) {
                                contractService.reviewActionItemUpdate(params).then(function (result) {
                                    if (result.status) {
                                        $rootScope.toast('Success', result.message);
                                        var obj = {};
                                        obj.action_name = 'Finish';
                                        obj.action_description = 'Finish$$Action$$Item$$('+data.action_item+')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);
                                        $scope.reviewAction($scope.tableStateRef);
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
                                    obj.action_description = 'save$$Action$$Item$$('+data.action_item+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.reviewAction($scope.tableStateRef);
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
                        params.contract_id  = decode($stateParams.id);
                        params.due_date  = $scope.due_date;
                        params.is_workflow = $scope.isWorkflow;
                        params.contract_workflow_id  = decode($stateParams.wId);
                        contractService.addReviewActionItemList(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'update$$Action$$Item$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.getActionItemById(data.id_contract_review_action_item);
                                $scope.cancel();
                                $scope.reviewAction($scope.tableStateRef);
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
    }
    $scope.deleteContractActionItem = function(row){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            var params ={};
            params.id_contract_review_action_item  = row.id_contract_review_action_item ;
            params.updated_by  = $rootScope.id_user ;
            contractService.deleteActionItem(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$Action$$Item$$('+row.action_item+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.reviewAction($scope.tableStateRef);
                }else $rootScope.toast('Error', result.error, 'error',$scope.user);
            });
        }
    }
    $scope.finalizeReviewList = function(){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open stored-model',
            templateUrl: 'contract-review-finalize.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.allowToFinish = false;
                $scope.finalizeReview = function (finalize,exists) {
                    $scope.allowToFinish = true;
                    var params = {};
                    if($stateParams.rId)params.contract_review_id = decode($stateParams.rId);
                    params.contract_id = params.id_contract = decode($stateParams.id);
                    params.id_user  = $scope.user1.id_user;
                    params.user_role_id  = $scope.user1.user_role_id;
                    if(exists != 'annus'){
                        if(finalize.finalize_without_discussion == 1)
                            params.finalize_without_discussion = finalize.finalize_without_discussion;
                        params.finalize_comments = finalize.finalize_comments;
                    }
                    params.created_by  = $rootScope.id_user;
                    params.is_workflow  = 1;
                    params.contract_workflow_id  = decode($stateParams.wId);
                    projectService.finalizeProjectList(params).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'finalize';
                            obj.action_description = 'finalize$$Project$$'+($scope.isWorkflow=='1')?'Task$$':'Review$$('+$stateParams.name+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.reviewAction($scope.tableStateRef);
                            // if($scope.isWorkflow=='1')
                            //     $state.go('app.contract.contract-overview');
                            // else {
                            //     var stateStr = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
                            //     $state.go(stateStr,{name:$stateParams.name,id:$stateParams.id,type:'review'});
                            // }        
                            $state.go('app.projects.view',{name:$stateParams.name,id:$stateParams.id,type:'workflow'});                        
                        }else $rootScope.toast('Error', result.error, 'error',$scope.user);
                    });
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            },
            resolve: {}
        });
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    }
    
    $scope.getModuleAttachmentList = function(){
        var params ={};
        if($stateParams.rId)params.module_id = decode($stateParams.rId);
        params.module_type =  'contract_review';
        params.id_user  = $rootScope.id_user;
        params.user_role_id  = $rootScope.user_role_id;
        params.page_type  = 'contract_overview';
        params.contract_id  = decode($stateParams.id);
        params.is_workflow  = $scope.isWorkflow;
        params.contract_workflow_id  = decode($stateParams.wId);
        contractService.getAttachments(params).then(function(result){
            $scope.documentsList = result.data.all_records;
            $scope.attachmentList = result.data.documents.data;
            $scope.linkList = result.data.links.data;
        });
    }
    $scope.getModuleAttachmentList();
    $scope.storedModulesList =[];     
    $scope.getStoredModules = function(){
        var params ={};  
        params.contract_id  = decode($stateParams.id);
        contractService.getStoredModules(params).then(function(result){
            $scope.storedModulesList = result.data;
        });
    }

    //$scope.getStoredModules();
    $scope.manageStoredModules = function(){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open stored-model',
            templateUrl: 'manage-stored-modules.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.title = 'contract.manage_stored_modules';
                $scope.bottom = 'general.save';                
                $scope.updateStoredModules = function(row){
                    var params={};
                    params.id_stored_module=row.id_stored_module;
                    params.activate_in_next_review=row.activate_in_next_review;
                    contractService.updateStoredModules(params).then(function(result){
                        if (result.status) {
                            var obj = {};
                            obj.action_name = 'Update';
                            obj.action_description = 'Manage$$Stored$$Modules$$('+row.module_name+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.getStoredModules();
                            $rootScope.toast('Success', result.message);
                            //$scope.cancel();
                        } else {
                            $rootScope.toast('Error', result.error,'error');
                        }

                    });
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            },
            resolve: {}
        });
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    }
    $scope.showStoredModuleQuestions= function(row){
        $scope.openUnAnswered(row,true);
    }
    $scope.deleteModuleDocument = function(id,name){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            attachmentService.deleteAttachments({'id_document': id}).then (function(result){
                if(result.status){
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$'+(($scope.isWorkflow=='1')?'Task':'Review')+'$$Attachment$$('+name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.getModuleAttachmentList();
                    $rootScope.toast('Success',result.data.message);
                    $scope.getModuleAttachmentList($scope.tableDocStateRef2);
                }
            })
        }
    }
   
    $scope.goToDashboard = function () {
        if($rootScope.access !='eu')
          $state.go('app.projects.project-dashboard1',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        if($rootScope.access =='eu')
          $state.go('app.projects.project-dashboard11',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
    }
    $scope.goToDesign = function(){
      if($rootScope.access !='eu')
         $state.go('app.projects.task-design',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
      if($rootScope.access =='eu')
      $state.go('app.projects.task-design1',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
    }
    $scope.goToChangeLog = function(){
        $state.go('app.projects.task-change-log', {'name': $stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        
    }
    // $scope.initializeReview = function (val){
    //     var params ={};
    //     params.created_by = $scope.user.id_user;
    //     params.customer_id = $scope.user1.customer_id;
    //     params.contract_id = decode($stateParams.id);
    //     params.is_workflow = $scope.reviewWorkflowInfo.is_workflow;
    //     params.calender_id = $scope.reviewWorkflowInfo.calender_id;
    //     if(val == true) params.contract_review_type = ($scope.isWorkflow=='1')?'adhoc_workflow':'adhoc_review';
    //     contractService.initializeReview(params).then(function(result){
    //         if(result.status){
    //             $rootScope.toast('Success', result.message);
    //             var obj = {};
    //             obj.action_name = 'initiate';
    //             obj.action_description = ($scope.isWorkflow=='1')?'initiate$$Task$$('+$stateParams.name+')':'initiate$$Review$$('+$stateParams.name+')';
    //             obj.module_type = $state.current.activeLink;
    //             obj.action_url = $location.$$absUrl;
    //             $rootScope.confirmNavigationForSubmit(obj);
    //             if($scope.isWorkflow=='1') $state.transitionTo('app.contract.contract-workflow',{name:$stateParams.name,id:$stateParams.id,rId:encode(result.data),wId:$stateParams.wId,type:'workflow'});
    //             else $state.transitionTo('app.contract.contract-review',{name:$stateParams.name,id:$stateParams.id,rId:encode(result.data),type:'review'});
    //         }else  $rootScope.toast('Error', result.error,'error',$scope.user);
    //     });
    // }
    $scope.shiftToNewDetails = function(item) {
        var obj ={};
        obj.id_contract = decode($stateParams.id);
        obj.contract_name = $stateParams.name;
        obj.id_contract_review = item.id_contract_workflow;
        $scope.goToReviewWorkflow(item,obj);
    }
    $scope.goToReviewWorkflow = function(type,row){
        if(type.initiated){
            $state.go('app.projects.project-task',{name:row.contract_name, id:encode(row.id_contract),rId:encode(type.id_contract_review),
                wId:encode(type.id_contract_workflow),type:'workflow'});
        }
        else{
            $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract),wId:encode(type.id_contract_review),wId:encode(type.id_contract_workflow),type:'workflow'});
        }
       
        
    }

    $rootScope.expert = {};
    $rootScope.validator = {};
    $rootScope.provider = {};

    $rootScope.expert.contributors = [];
    $rootScope.validator.contributors = [];
    $rootScope.provider.contributors = [];

    $scope.addProjectContributors = function (info) {
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'add-edit-contributors.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.title = 'general.create';
                $scope.bottom = 'general.save';
                $scope.isEdit = false;
                $scope.showContractList = false;
                $scope.showContractList1 = false;
                $scope.showContractList2 = false;
                $scope.businessUnitList = [];
                $scope.data = {};
                $scope.data.expert = {};
                $scope.data.validator = {};
                $scope.data.provider = {};
            
                var param ={};
                param.project_id =  decode($stateParams.id);
                projectService.getAvailableProviders(param).then(function(result){
                    $scope.availableProviders = result.data;
                });
              
                var params = {};
                params.user_role_id = $scope.user1.user_role_id;
                params.customer_id  = $scope.user1.customer_id;
                params.user_id = $scope.user.id_user;
                businessUnitService.bulist(params).then(function(result){
                    $scope.businessUnitList = result.data;
                });


               
                var params={};
                if($stateParams.type)$scope.isWorkflow=($stateParams.type=='workflow')?'1':'0';
                params.contract_review_id = decode($stateParams.rId);
                params.contract_id = params.id_contract = decode($stateParams.id);
                params.id_user  = $scope.user1.id_user;
                params.user_role_id  = $scope.user1.user_role_id;
                params.id_contract_workflow  = decode($stateParams.wId);
                params.is_workflow  = $scope.isWorkflow; 
                params.id_module = info.id_module;
                contractService.contractModule(params).then(function(result){
                   // console.log(result);
                    if(result.status){
                        $scope.contractModuleTopics = result.data[0];
                        $rootScope.contributors = $scope.contractModuleTopics.contributors;

                        $rootScope.expert.contributors = $scope.contractModuleTopics.contributors.expert.data;
                        $rootScope.validator.contributors = $scope.contractModuleTopics.contributors.validator.data;
                        $rootScope.provider.contributors = $scope.contractModuleTopics.contributors.provider.data;
            
                        $scope.contributors1 = $scope.contractModuleTopics.contributors;
                        $scope.expert={}; $scope.validator={}; $scope.provider={};
                        $scope.expert.contributors1 = $scope.contractModuleTopics.contributors.expert.data;
                        $scope.validator.contributors1 = $scope.contractModuleTopics.contributors.validator.data;
                        $scope.provider.contributors1 = $scope.contractModuleTopics.contributors.provider.data
                    }
              
                
                $scope.getContributorsByBusinessUnit1 = function(selectedBU,contributorType) {
                    $scope.expertList = [];
                    var params = {};
                    params.contract_id = decode($stateParams.id);
                    params.type = 'contributor';
                    params.user_role_id = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.user_id = $scope.user.id_user;
                    params.project_id = decode($stateParams.id);
                    if (selectedBU !== '') {
                        params.business_unit_id = selectedBU;
                    }  
                    //$scope.getTopicQuestions(Globalparams,false);
                    contractService.reviewUsers(params).then(function(result){
                        $scope.expertList = result.data.expert;
                        if (selectedBU == '' || selectedBU == null ) {
                            if (contributorType == 'expert') {
                                $scope.data.expert.contributors = [];
                            }
                            else{
                                $scope.data.expert.contributors = [];
                            }                            
                            angular.forEach($scope.expert.contributors1, function(i,o){
                                angular.forEach(result.data.expert, function(i1,o1){
                                    if(i.id_user===i1.id_user){
                                        $scope.data.expert.contributors.push(i1);
                                    }                                 
                                });
                            });
                            angular.forEach($scope.data.expert.contributors, function(i,o){
                                $scope.expert.contributors1.push(i.id_user);
                            });
                        } else {}
                    });
                };
                $scope.getContributorsByBusinessUnit1('');
                $scope.getContributorsByBusinessUnit2 = function(selectedBU,contributorType) {
                    $scope.validatorList = [];
                    var params = {};
                    params.contract_id = decode($stateParams.id);
                    params.type = 'contributor';
                    params.user_role_id = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.user_id = $scope.user.id_user;
                    params.project_id = decode($stateParams.id);
                    if (selectedBU !== '') {
                        params.business_unit_id = selectedBU;
                    }  
                   // $scope.getTopicQuestions(Globalparams,false);                  
                    contractService.reviewUsers(params).then(function(result){
                        $scope.validatorList = result.data.validator;
                        if (selectedBU == '' || selectedBU == null ) {
                            
                            if (contributorType == 'validator') {
                                $scope.data.validator.contributors = [];
                            }
                            else{
                                $scope.data.validator.contributors = [];
                            }
                            angular.forEach($scope.validator.contributors1, function(i,o){
                                angular.forEach(result.data.validator, function(i1,o1){
                                    if(i.id_user===i1.id_user){
                                        $scope.data.validator.contributors.push(i1);
                                        $scope.data.validator.searchValidatorContracts=i1;                                      
                                    }                                 
                                });
                            });
                            angular.forEach($scope.data.validator.contributors, function(i,o){
                                $scope.validator.contributors1.push(i.id_user);
                            });
                        } else {}
                    });
                };
                $scope.getContributorsByBusinessUnit2('');
                $scope.getContributorsByBusinessUnit3 = function(selectedBU,contributorType) {
                    $scope.providerList = [];
                    var params = {};
                    params.contract_id = decode($stateParams.id);
                    params.type = 'contributor';
                    params.user_role_id = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.user_id = $scope.user.id_user;
                    params.project_id = decode($stateParams.id);
                    if (selectedBU !== '') {
                        params.id_provider = selectedBU;
                    }  
                    //$scope.getTopicQuestions(Globalparams,false);                  
                    contractService.reviewUsers(params).then(function(result){
                        $scope.providerList = result.data.provider;
                        if (selectedBU == '' || selectedBU == null ) {
                             if (contributorType == 'provider') {
                                $scope.data.provider.contributors = [];
                            }
                            else{
                                $scope.data.provider.contributors = [];
                            }                            
                            angular.forEach($scope.provider.contributors1, function(i,o){
                                angular.forEach(result.data.provider, function(i1,o1){
                                    if(i.id_user===i1.id_user){
                                        $scope.data.provider.contributors.push(i1);
                                    }                                 
                                });
                            });
                            angular.forEach($scope.data.provider.contributors, function(i,o){
                                $scope.provider.contributors1.push(i.id_user);
                            });
                        } else {}
                    });
                };
                $scope.getContributorsByBusinessUnit3('');
                $scope.addProviderContributer = function(data,type){  
                   // console.log('data info',data);
                    // console.log('type info',type);
                    // console.log('length',$scope.data.provider.contributors);
                    if (!$scope.data.provider.contributors) {
                        $scope.data.provider.contributors=[];
                     }                                  
                     var isPresent = false;
                     var isAdded = false;
                     var isenable = false;
                     angular.forEach($scope.data.provider.contributors, function(i,o){
                         if ((i.id_user  && i.id_user && i.id_provider ) === (data.id_user && data.id_provider)){
                             isPresent = true;
                             isenable=false;
                         }
                         if((i.id_provider == data.id_provider) && (i.id_user != data.id_user) ){
                             isAdded =true;
                             isenable=true;
                         }
                      });
                     
                      if(!isPresent){
                          $scope.data.provider.contributors.push(data);
                      }
                      if(isPresent && !isenable){
                         $rootScope.toast('Error', 'Contributor already added.');
                      }
                      if(isAdded && isenable){
                         $rootScope.toast('Error', 'Select only one user per provider');
                      }
                }
                $scope.addExpertContributer = function(data){
                   // $scope.data.expert.contributors=data;
                    if (!$scope.data.expert.contributors) {
                        $scope.data.expert.contributors = [];
                    }                    
                    var isPresent = false;
                    angular.forEach($scope.data.expert.contributors, function(i,o){
                       if (i.id_user && i.id_user === data.id_user)
                            isPresent = true;
                    });
                    if (!isPresent) {
                        $scope.data.expert.contributors.push(data);
                        $scope.showContractList = false;
                    } else {
                        $rootScope.toast('Error', 'Contributor already added.');
                    }
                }
                $scope.addValidatorContributer = function(data,type){
                    $scope.data.validator.contributors = [];
                    if(data){
                       $scope.data.validator.contributors.push(data);
                        $scope.showContractList1 = false;  
                    }
                }
              
                var params ={};
                $scope.showBtn = false;
                $scope.saveContributors=function(data){
                    $scope.showBtn = true;
                    params.contract_review_id = decode($stateParams.rId);
                    params.module_id = info.id_module;
                    params.created_by = $scope.user.id_user;
                    params.contract_id = decode($stateParams.id);
                    params.topic_id = $scope.topic_no;
                    params.user_role_id  = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.expert={};
                    params.validator={};
                    params.provider={};
                    params.expert.business_unit=data.expert.business_unit;
                    params.validator.business_unit=data.validator.business_unit;

                    var a11 = [],a12 = [],a13 = [],a2 = [], a3 = [], a4 = [];
                    var diff1=[];var diff2=[];var diff3=[];;

                    angular.forEach($scope.expert.contributors1, function(i,o){
                        console.log('i',i);
                        a11.push(i.id_user);
                    });
                    angular.forEach($scope.validator.contributors1, function(i,o){
                        a12.push(i.id_user);
                    });
                    angular.forEach($scope.provider.contributors1, function(i,o){
                        a13.push(i.id_user);
                    });
                    $rootScope.contributors = data.contributors;
                    $rootScope.expert.contributors = data.expert;
                    $rootScope.validator.contributors = data.validator;
                    $rootScope.provider.contributors  = data.provider;
                    angular.forEach($scope.data.expert.contributors, function(i,o){
                        a2.push(i.id_user);
                    });
                    if($scope.data.validator.contributors[0])
                        a3.push($scope.data.validator.contributors[0].id_user);                    
                    angular.forEach($scope.data.provider.contributors, function(i,o){
                        a4.push(i.id_user);
                    });                   
                    params.expert.contributors_add = a2.join(',');
                    params.validator.contributors_add = a3.join(',');
                    params.provider.contributors_add = a4.join(',');
                    if(a11!=undefined)if(a11.length>0)  diff1 = a11.diff(a2);   
                    if(a12!=undefined)if(a12.length>0)  diff2 = a12.diff(a3);
                    if(a13!=undefined)if(a13.length>0)  diff3 = a13.diff(a4);
                    if(diff1.length<=0){
                        params.expert.contributors_remove = '';
                    } else {
                        for (var i = 0; i < diff1.length; i++) {
                            if(diff1[i] == undefined){
                                delete diff1[i];
                            }
                        }
                        params.expert.contributors_remove = diff1.join(',');
                        params.expert.contributors_remove = params.expert.contributors_remove.replace(/,\s*$/, "");   /*remove last comma which is getting appended*/
                    }
                    if(diff2.length<=0){
                        params.validator.contributors_remove = '';
                    } else {
                        for (var i = 0; i < diff2.length; i++) {
                            if(diff2[i] == undefined){
                                delete diff2[i];
                            }
                        }
                        params.validator.contributors_remove = diff2.join(',');
                        params.validator.contributors_remove = params.validator.contributors_remove.replace(/,\s*$/, "");   /*remove last comma which is getting appended*/
                    }
                    if(diff3.length<=0){
                        params.provider.contributors_remove = '';
                    } else {
                        for (var i = 0; i < diff3.length; i++) {
                            if(diff3[i] == undefined){
                                delete diff3[i];
                            }
                        }
                        params.provider.contributors_remove = diff3.join(',');
                        params.provider.contributors_remove = params.provider.contributors_remove.replace(/,\s*$/, "");   /*remove last comma which is getting appended*/
                    }
                    $scope.showBtn = false;
                    projectService.addProjectContributors(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'update';
                            obj.action_description = 'update$$Cotributors$$('+$stateParams.name +' - '+ $stateParams.mName+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            // $scope.getContributorsList(params); //not required
                            $scope.getProjectContributorsInfo();
                            $scope.info();
                            $scope.cancel();
                        } else {
                            $rootScope.toast('Error', result.error,'error');
                        }
                    });
                }

                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                $scope.remove = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                    $scope.data.contributors.splice(index, 1);
                    }
                };
                $scope.removeExpert = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                    $scope.data.expert.contributors.splice(index, 1);
                    }
                };
                $scope.removeValidator = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                        delete $scope.data.validator.searchValidatorContracts;
                        $scope.data.validator.contributors[0]=false;
                    }
                };
                $scope.removeProvider = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                    $scope.data.provider.contributors.splice(index, 1);
                    }                   
                };
              });
            }
        
        });

    
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    };
    Array.prototype.diff = function(a) {
        return this.filter(function(i) {return a.indexOf(i) < 0;});
    };

})
.controller('projectModuleReviewCtrl', function($sce, $timeout,anchorSmoothScroll, $scope,$filter, removespecialcharFilter, underscoreaddFilter, $rootScope, $state, $stateParams, userService, businessUnitService, projectService,contractService, attachmentService, decode, encode, Upload, $uibModal, $location, dateFilter){
    var vm = this;
    $scope.displayCount = $rootScope.userPagination;
    $scope.scrlTabsApi = {};
    $scope.reCalcScroll = function() {
        if($scope.scrlTabsApi.doRecalculate) {
            $scope.scrlTabsApi.doRecalculate();
        }
    };
    $scope.scrollIntoView = function(arg) {
      if($scope.scrlTabsApi.scrollTabIntoView) {
        $scope.scrlTabsApi.scrollTabIntoView(arg)
      }
    };
    $scope.activateTab = function(tab,index) {
        $timeout(function () {
            $scope.scrollIntoView(index);
        });
    }
    var parentPage = $state.current.url.split("/")[1];
    console.log(parentPage);
    if($stateParams.pname != undefined) 
    $scope.topic_count = 1;
    $rootScope.module = 'Project';
    if($stateParams.pname != undefined) $rootScope.displayName = $stateParams.name +' - '+ $stateParams.mName +' '+$stateParams.pname;
    if($stateParams.pname == undefined) $rootScope.displayName = $stateParams.name +' - '+ $stateParams.mName;
    $rootScope.icon = "Projects";
    $rootScope.class ="project-logo"; 
    $rootScope.breadcrumbcolor='project-breadcrumb-color';
    $scope.optionsData = {};
    $rootScope.contributors = [];
    $rootScope.expert = {};
    $rootScope.validator = {};
    $rootScope.provider = {};

    $rootScope.expert.contributors = [];
    $rootScope.validator.contributors = [];
    $rootScope.provider.contributors = [];

    $scope.optionsData.feedback = {};
    $scope.optionsData.external_user_question_feedback= {};
    $scope.optionsData.answers = {};
    $scope.showFileToAttach = false;
    $scope.contract_review_id =  decode($stateParams.rId);
    $scope.module_id = decode($stateParams.moduleId);
    $scope.contract_id = decode($stateParams.id);

    $scope.contract_name = $stateParams.name;
    $scope.en_contract_id = $stateParams.id;
    $scope.en_rId = $stateParams.rId;
    $scope.mName = $stateParams.mName;
    $scope.en_moduleId = $stateParams.moduleId;

  

    var Globalparams= {};
    Globalparams.contract_review_id = decode($stateParams.rId);
    Globalparams.contract_workflow_id = decode($stateParams.wId);
    if($stateParams.type)$scope.isWorkflow = ($stateParams.type =='workflow')?'1':'0';
    Globalparams.module_id = decode($stateParams.moduleId);
    Globalparams.contract_id = decode($stateParams.id);
    Globalparams.id_topic = decode($stateParams.tId);
    Globalparams.is_workflow = $scope.isWorkflow;

    // $scope.gotoElement = function (eID) {
    //     var id = decode($stateParams.qId);
    //     var element = document.getElementById(id);
    //     element.classList.add("discussion-row");
    //     $location.hash(id);
    //     // $anchorScroll();
    //     //anchorSmoothScroll.scrollTo(id);
    //     $timeout(function () {
    //         element.classList.remove("discussion-row");
    //     }, 3000);
    // };


    $scope.gotoElement = function(eID){
        var blink = document.querySelector('[tokenid="'+eID+'"]');
       //console.log(blink);
       blink.classList.add("discussion-row");
       setTimeout(function() {
        blink.classList.remove("discussion-row");
    }, 3000);
    }
    $scope.getTopicQuestions = function(params,flag){
        $scope.loading = false;
        contractService.getcontractReviewModules(params).then(function(result){
            $scope.contractModuleTopics = result.data[0];
            if($scope.contractModuleTopics.contract_user_access==''){
                $rootScope.toast('Error', "You don't have access to review or workflow", 'error');
                window.history.back();
            }
            $scope.reCalcScroll();           
            $('#'+underscoreaddFilter(removespecialcharFilter($scope.contractModuleTopics.topics[0].topic_name))).addClass('current');
            $scope.topic_no = $scope.contractModuleTopics.topic_pagination.current?$scope.contractModuleTopics.topic_pagination.current:'';
            $scope.next = $scope.contractModuleTopics.topic_pagination.next;
            $scope.previous = $scope.contractModuleTopics.topic_pagination.previous;
            $scope.present_count = ($scope.contractModuleTopics.topic_pagination.count!=0)?$scope.contractModuleTopics.topic_pagination.current_count:0;
            $rootScope.contributors = $scope.contractModuleTopics.contributors;

            $rootScope.expert.contributors = $scope.contractModuleTopics.contributors.expert.data;
            $rootScope.validator.contributors = $scope.contractModuleTopics.contributors.validator.data;
            $rootScope.provider.contributors = $scope.contractModuleTopics.contributors.provider.data;

            $scope.contributors1 = $scope.contractModuleTopics.contributors;
            $scope.expert={}; $scope.validator={}; $scope.provider={};
            $scope.expert.contributors1 = $scope.contractModuleTopics.contributors.expert.data;
            $scope.validator.contributors1 = $scope.contractModuleTopics.contributors.validator.data;
            $scope.provider.contributors1 = $scope.contractModuleTopics.contributors.provider.data;

            if($scope.contractModuleTopics.topics.length>0)
                $rootScope.topicName = $scope.contractModuleTopics.topics[0].topic_name;
            $scope.attachment = [];
            
            if($scope.contractModuleTopics.topics.length>0){
                angular.forEach($scope.contractModuleTopics.topics[0].questions, function(item,key){
                    if(item.question_type == 'date'){
                        $scope.optionsData.answers[item.id_question] = (item.parent_question_answer)?moment(item.parent_question_answer).utcOffset(0, false).toDate():null; 
                    }
                    else $scope.optionsData.answers[item.id_question] = item.parent_question_answer;  
                    $scope.optionsData.feedback[item.id_question] = item.question_feedback;
                    $scope.optionsData.external_user_question_feedback[item.id_question] = item.external_user_question_feedback;
                    item.state=false;
                    item.state1=false;
                   if(item.help_text)
                       item.help_text = $sce.trustAsHtml('<pre class="text-tooltip" style="text-align:left;">'+item.help_text+'</pre>');
                });
                $scope.activeTopicTab=0;
                $scope.showTabs=false;
                // console.log("*-*-*-*-*-*-",$scope.contractModuleTopics);
                if($scope.contractModuleTopics.side_by_side_validation){
                    $scope.validatorQuestions();
                }
                $scope.contractModuleTopics.topic_tabs.some(function __forEachTab(tab,i) {
                    if(tab.id_topic == $scope.contractModuleTopics.topics[0].id_topic){
                        tab.active = true;
                        tab.class='current';
                        $scope.activateTab(tab,i);
                        $timeout(function () {
                            $scope.loading = true;
                            if($stateParams.qId && flag){
                                $scope.gotoElement(decode($stateParams.qId));
                            }
                        },500);
                        // $scope.scrollIntoView(i);
                        var ele = document.getElementById(underscoreaddFilter(removespecialcharFilter($scope.contractModuleTopics.topics[0].topic_name)));
                        ele.classList.add('current');
                        // return true; // exit loop
                    }else {
                        tab.active = false;
                        tab.class='';
                    }
                });
            }
            $scope.review_access = true;
            if($scope.contractModuleTopics.contract_details[0].reaaer!= "itako"){$scope.review_access = false;}
            $timeout(function () {
                $scope.loading = true;
                if($stateParams.qId && flag){
                    $scope.gotoElement(decode($stateParams.qId));
                }
            },500);
        });
    }
    $scope.getContributorsList = function(params){
        contractService.getcontractReviewModules(params).then(function(result){
            $rootScope.contributors = result.data[0].contributors;
            $scope.contributors1 = result.data[0].contributors;

            $rootScope.expert.contributors = result.data[0].contributors.expert;
            $rootScope.validator.contributors = result.data[0].contributors.validator;
            $rootScope.provider.contributors = result.data[0].contributors.provider;

            $scope.expert={}; $scope.validator={}; $scope.provider={};

            $scope.expert.contributors1 = result.data[0].contributors.expert.data;
            $scope.validator.contributors1 = result.data[0].contributors.validator.data;
            $scope.provider.contributors1 = result.data[0].contributors.provider.data;
            
        });
    }
    $scope.reset = function(val,ind){
        $scope.optionsData.answers[val]='';
        document.getElementById("toggle-"+ind).classList.remove('green-color','red-color');
        document.getElementById("toggle-"+ind).classList.add('blue-color');
        document.getElementById("question_"+val+"_red").classList.remove('red-circle');
        document.getElementById("question_"+val+"_blue").classList.add('blue-circle');
        document.getElementById("question_"+val+"_green").classList.remove('green-circle');
    }
    $scope.getTopicQuestions(Globalparams,true);
    $scope.goToDiscussion = function(row){
        var proceed=true;
        if($scope.contractModuleTopics.side_by_side_validation && row.readOnly) proceed=false;
        if(proceed && $rootScope.access !='eu'){
            $state.go('app.projects.task-design',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,
                mId:encode($scope.contractModuleTopics.id_module),tId:encode($scope.contractModuleTopics.topics[0].id_topic),
                qId:encode(row.id_question),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false});
        }
        if(proceed &&  $rootScope.access =='eu'){
            $state.go('app.projects.task-design1',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,
                         mId:encode($scope.contractModuleTopics.id_module),tId:encode($scope.contractModuleTopics.topics[0].id_topic),
                         qId:encode(row.id_question),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false});
        }
    }
    $scope.reviewAction = function (tableState){
        setTimeout(function(){
            $scope.tableStateRef = tableState;
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            if($stateParams.rId)tableState.contract_review_id = decode($stateParams.rId);
            tableState.module_id = decode($stateParams.moduleId);
            tableState.contract_id = decode($stateParams.id);
            tableState.topic_id = decode($stateParams.tId);
            tableState.id_user  = $scope.user1.id_user;
            tableState.user_role_id  = $scope.user1.user_role_id;
            tableState.page_type='contract_review';
            tableState.action_status='all'; // open,completed,all
            tableState.is_workflow  = $scope.isWorkflow;
            tableState.action_item_type  = 'inside';
            tableState.contract_workflow_id  = decode($stateParams.wId);
            contractService.reviewActionItemList(tableState).then (function(result){
                $scope.reviewList = result.data.data;
                $scope.reviewListCount = result.data.count;
                $scope.isLoading = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords1 = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if(result.data.total_records < 1)
                    $scope.emptyTable=true;
            })
        },500);
    }
    $scope.defaultPages1 = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.reviewAction($scope.tableStateRef);
            }                
        });
    }
    $scope.defaultPages2 = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getAttachmentList($scope.tableDocStateRef);
            }                
        });
    }
   
   
    $scope.goToNextTopic = function(next) {
        $state.go('app.projects.project-module-task',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,mName:$stateParams.mName,moduleId:$stateParams.moduleId,
            tName:next.next_text,tId:encode(next.next),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false});
    }
    $scope.goToPreviousTopic = function(previous) {
        $state.go('app.projects.project-module-task',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,mName:$stateParams.mName,moduleId:$stateParams.moduleId,
            tName:previous.previous_text,tId:encode(previous.previous),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false});
    }
    $scope.getAttachmentList = function(tableState){
        $scope.tableDocStateRef = tableState;
        var pagination = tableState.pagination;
        tableState.module_id = $scope.contract_review_id;
        tableState.module_type =  'contract_review';
        tableState.page_type= 'contract_review';
        tableState.reference_id = decode($stateParams.tId);
        tableState.reference_type = 'topic';
        tableState.id_user  = $rootScope.id_user;
        tableState.user_role_id  = $rootScope.user_role_id;
        tableState.is_workflow  =1;
        tableState.contract_workflow_id  = decode($stateParams.wId);
        contractService.getAttachments(tableState).then(function(result){
            $scope.attachmentList = result.data.result.data;
            $scope.emptyTable=false;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords2 = result.data.result.total_records;
            tableState.pagination.numberOfPages =  Math.ceil(result.data.result.total_records / $rootScope.userPagination);
            $scope.isLoading = false;
            if(result.data.result.total_records < 1)
                $scope.emptyTable=true;
        });
    }
    $scope.createContractReview = function (row, type, question,topic) {
        if(question !=''){
            if(!$scope.contractModuleTopics.action_item_question_link){
                var obj = {};
                obj.contract_review_id=$scope.contract_review_id;
                obj.created_by=$rootScope.id_user;
                $scope.options = {};
                angular.forEach(topic.questions, function(i,o){
                    if(i.id_question == question.id_question){
                        $scope.options[0] = {};
                        $scope.options[0].question_id = i.id_question;
                        $scope.options[0].parent_question_id = i.parent_question_id;
                        if($scope.optionsData.answers[i.id_question])
                            $scope.options[0].question_answer = $scope.optionsData.answers[i.id_question];
                        else $scope.options[0].question_answer = '';
                    }
                });               
                obj.data = $scope.options;
                obj.id_module = $scope.contractModuleTopics.id_module;
                obj.module_status = $scope.contractModuleTopics.module_status;
                obj.is_workflow = $scope.isWorkflow;
                if($scope.isWorkflow=='1'){
                    obj.id_contract_workflow= decode($stateParams.wId);
                }
                contractService.answerQuestion(obj).then(function(result){
                    if(result.status){
                        var obj = {};
                        obj.action_name = 'save';
                        obj.action_description = 'save$$module$$questions$$('+$stateParams.mName+')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                    } else $rootScope.toast('Error', result.error, 'error');
                });
            }
        }
        $scope.question_id = question.id_question;
        $scope.type = type;
        $scope.selectedRow = row;
        $scope.contract = {};
        $scope.data = {};
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/create-edit-project-review.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.update = false;
                $scope.title = 'contract.create_action_item';
                $scope.bottom = 'general.save';
                $scope.isEdit = false;
                if (typeof item != 'undefined') {
                    $scope.isEdit = true;
                    $scope.submitStatus = true;
                    $scope.title = '';
                    $scope.data = angular.copy(item);
                    delete $scope.data.comments;
                    $scope.data.due_date = moment($scope.data.due_date).utcOffset(0, false).toDate();
                    $scope.update = true;
                    $scope.bottom = 'general.update';
                    $scope.addaction = false;
                }
                else{
                    $scope.addaction = true;
                    $scope.data.due_date=moment().utcOffset(0, false).toDate();
                }
                $scope.saveAnswer = function(){
                    $scope.question = question;
                    //console.log('question info',question);
                    var obj = {};
                    obj.contract_review_id=$scope.contract_review_id;
                    obj.created_by=$rootScope.id_user;
                    $scope.options = {};        
                    angular.forEach($scope.contractModuleTopics.topics[0].questions, function(i,o){
                        if(!$scope.contractModuleTopics.side_by_side_validation){
                            $scope.options[o] = {};
                            $scope.options[o].question_id = i.id_question;
                            $scope.options[o].parent_question_id = i.parent_question_id;
                            if(i.question_type =='date')
                                $scope.options[o].question_answer = dateFilter($scope.optionsData.answers[i.id_question],'yyyy-MM-dd');
                            else if(i.question_type  != 'date') {
                                $scope.options[o].question_answer = $scope.optionsData.answers[i.id_question];
                            }
                            else $scope.options[o].question_answer = '';

                            if($scope.optionsData.external_user_question_feedback[i.id_question])
                            $scope.options[o].external_user_question_feedback = $scope.optionsData.external_user_question_feedback[i.id_question];
                        else $scope.options[o].external_user_question_feedback = '';

                        
                            if($scope.optionsData.feedback[i.id_question])
                                $scope.options[o].question_feedback = $scope.optionsData.feedback[i.id_question];
                            else $scope.options[o].question_feedback = '';
                        }
                        if(($scope.contractModuleTopics.side_by_side_validation && !i.readOnly)){
                            $scope.options[o] = {};
                            $scope.options[o].question_id = i.id_question;
                            $scope.options[o].parent_question_id = i.parent_question_id;
                            if(i.question_type =='date'){
                                if(o%2==0){
                                    $scope.options[o].v_question_answer = dateFilter( $scope.optionsData.answers[i.id_question+"1"],'yyyy-MM-dd');
                                    $scope.options[o].question_answer = dateFilter( $scope.optionsData.answers[i.id_question],'yyyy-MM-dd');
                                }else{
                                    $scope.options[o].v_question_answer = dateFilter( $scope.optionsData.answers[i.id_question],'yyyy-MM-dd');
                                    $scope.options[o].question_answer = dateFilter( $scope.optionsData.answers[i.id_question+"1"],'yyyy-MM-dd');
                                }
                            }
                            else if(i.question_type  != 'date') {
                                if(o%2==0){
                                    $scope.options[o].v_question_answer =   $scope.optionsData.answers[i.id_question+"1"];
                                    $scope.options[o].question_answer =   $scope.optionsData.answers[i.id_question];
                                }else{
                                    $scope.options[o].v_question_answer =   $scope.optionsData.answers[i.id_question];
                                    $scope.options[o].question_answer =   $scope.optionsData.answers[i.id_question+"1"];
                                }
                            }
                            else $scope.options[o].v_question_answer = '';
                            $scope.optionsData.feedback[i.id_question] = ( $scope.optionsData.feedback[i.id_question]) ?  $scope.optionsData.feedback[i.id_question] : '';
                            $scope.optionsData.feedback[i.id_question+"1"] = ( $scope.optionsData.feedback[i.id_question+"1"]) ?  $scope.optionsData.feedback[i.id_question+"1"] : '';
                            if(o%2==0){
                                $scope.options[o].question_feedback =  $scope.optionsData.feedback[i.id_question];
                                $scope.options[o].v_question_feedback =  $scope.optionsData.feedback[i.id_question+"1"]; 
                            }else{
                                $scope.options[o].question_feedback =  $scope.optionsData.feedback[i.id_question+"1"];
                                $scope.options[o].v_question_feedback =  $scope.optionsData.feedback[i.id_question];
                            }
                        }                
                    });
                    if($scope.contractModuleTopics.side_by_side_validation){
                        var arr = [];
                        angular.forEach($scope.options, function(i,o){
                            arr.push(i);
                        });
                        $scope.options = arr;
                    }
                    obj.data = $scope.options;
                    obj.id_module = $scope.contractModuleTopics.id_module;
                    obj.module_status = $scope.contractModuleTopics.module_status;
                    obj.is_workflow = 1;
                    if($scope.isWorkflow=='1'){
                        obj.id_contract_workflow= decode($stateParams.wId);
                    }
                    //console.log('obj infos',obj);
                    contractService.answerQuestion(obj).then(function(result) {
                        if(result.status){
                            var obj = {};
                            obj.action_name = 'save';
                            obj.action_description = 'save$$module$$questions$$('+$stateParams.mName+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            contractService.getcontractReviewModules(Globalparams).then(function(result){
                                angular.forEach(result.data[0].topics[0].questions, function(i,o){
                                    $scope.contractModuleTopics.topics[0].questions[o].attachment_count = i.attachment_count;
                                });
                            });
                            $scope.getTopicQuestions(Globalparams,true);
                        } else $rootScope.toast('Error', result.error, 'error');
                    });
                 }
                $scope.getActionItemById = function(id){
                    contractService.getActionItemDetails({'id_contract_review_action_item':id}).then(function(result){
                        $scope.data = result.data[0];
                        $scope.data.due_date = moment($scope.data.due_date).utcOffset(0, false).toDate();
                    });
                }
                if($scope.type == 'view') $scope.bottom = 'contract.finish';
                contractService.getActionItemResponsibleUsers({'contract_id': $scope.contract_id,'contract_review_id': $scope.contract_review_id,'project_id':decode($stateParams.id),'type':'project'}).then(function(result){
                    $scope.userList = result.data;
                });
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                $scope.goToEdit = function(data){
                    $scope.data.due_date = moment(data.due_date).utcOffset(0, false).toDate();
                }
                var params ={};
                $scope.addReviewActionItem=function(data){
                    //data.due_date = dateFilter(data.due_date,'yyyy-MM-dd');
                    $scope.due_date = angular.copy(data.due_date);
                    $scope.due_date = dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                    if($scope.type == 'view'){
                        var params = [];
                        params = angular.copy(data);
                        params.id_contract_review_action_item = data.id_contract_review_action_item;
                        delete params.description;
                        params.comments = data.comments;
                        params.is_finish = data.is_finish;
                        params.updated_by = $scope.user.id_user;
                        params.contract_id  = decode($stateParams.id);
                        params.due_date  = $scope.due_date;
                        params.reference_type ='project';
                        params.is_workflow = $scope.isWorkflow;
                        if(data.is_finish  == 1) {
                            var r = confirm($filter('translate')('general.alert_action_finish'));
                            $scope.deleConfirm = r;
                            if (r == true) {
                                contractService.reviewActionItemUpdate(params).then(function (result) {
                                    if (result.status) {                                        
                                        $rootScope.toast('Success', result.message);
                                        var obj = {};
                                        obj.action_name = 'Finish';
                                        obj.action_description = 'Finish$$Action$$Item$$('+data.action_item+')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $scope.getTopicQuestions(Globalparams,true);
                                        $rootScope.confirmNavigationForSubmit(obj);
                                        $scope.reviewAction($scope.tableStateRef);
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
                                    obj.action_name = 'Save';
                                    obj.action_description = 'Save$$Action$$Item$$('+data.action_item+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.reviewAction($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error,'error');
                                }
                            });
                        }
                    }
                    else if(typeof data == undefined){
                        var params ={};
                        delete data.comments;
                        params = angular.copy(data);
                        params.description = data.description;
                        params.contract_review_id = $scope.contract_review_id;
                        params.module_id = $scope.module_id;
                        params.updated_by = $scope.user.id_user;
                        params.contract_id = params.id_contract = $scope.contract_id;
                        params.topic_id = $scope.topic_no;
                        params.id_user  = $scope.user1.id_user;
                        params.user_role_id  = $scope.user1.user_role_id;
                        params.due_date  = $scope.due_date;
                        params.is_workflow = 1;
                        params.contract_workflow_id  = decode($stateParams.wId);
                        //console.log('params info',params);
                        contractService.addReviewActionItemList(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'update$$Action$$Item$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.reviewAction($scope.tableStateRef);
                                $scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });
                    }
                    else {
                        var params ={};
                        delete data.comments;
                        params = data ;
                        params.description = data.description;
                        delete params.comments;
                        params.module_id = $scope.module_id;
                        params.contract_id = $scope.contract_id;
                        params.created_by = $scope.user.id_user;
                        params.contract_id = params.id_contract = $scope.contract_id;
                        params.topic_id = $scope.topic_no;
                        params.id_user  = $scope.user1.id_user;
                        params.user_role_id  = $scope.user1.user_role_id;
                        params['contract_review_id'] = $scope.contract_review_id;
                        params.question_id= $scope.question_id;
                        params.due_date  = $scope.due_date;
                        params.is_workflow = 1;
                        params.contract_workflow_id  = decode($stateParams.wId);
                        if(type=='add' && question ==''&& topic ==''){
                            params.reference_type ='topic';
                        }
                        else{
                            params.reference_type ='question';
                        }
                        console.log('params1 info',params);
                        contractService.addReviewActionItemList(params).then(function (result) {
                            if (result.status) {
                                $scope.saveAnswer();
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'add';
                                obj.action_description = 'add$$Action$$Item$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.getTopicQuestions(Globalparams,false);
                                if(data.id_contract_review_action_item){
                                    $scope.getActionItemById(data.id_contract_review_action_item);
                                    $scope.cancel();
                                }else{$scope.cancel();}
                                $scope.reviewAction($scope.tableStateRef);
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
    }    
    $scope.addContributors = function () {
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'add-edit-contributors.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.title = 'general.create';
                $scope.bottom = 'general.save';
                $scope.isEdit = false;
                $scope.showContractList = false;
                $scope.showContractList1 = false;
                $scope.showContractList2 = false;
                $scope.businessUnitList = [];
                $scope.availableProviders =[];
                $scope.data = {};
                $scope.data.expert = {};
                $scope.data.validator = {};
                $scope.data.provider = {};
                
                var param ={};
                param.project_id =  decode($stateParams.id);
                projectService.getAvailableProviders(param).then(function(result){
                    $scope.availableProviders = result.data;
                });
                var params = {};
                params.user_role_id = $scope.user1.user_role_id;
                params.customer_id  = $scope.user1.customer_id;
                params.user_id = $scope.user.id_user;
                businessUnitService.bulist(params).then(function(result){
                   $scope.businessUnitList = result.data;
                });

              
                $scope.getContributorsByBusinessUnit1 = function(selectedBU,contributorType) {
                    $scope.expertList = [];
                    var params = {};
                    params.contract_id = decode($stateParams.id);
                    params.type = 'contributor';
                    params.user_role_id = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.user_id = $scope.user.id_user; 
                    params.project_id = decode($stateParams.id);
                    if (selectedBU !== '') {
                        params.business_unit_id = selectedBU;
                    }  
                    //$scope.getTopicQuestions(Globalparams,false);
                    contractService.reviewUsers(params).then(function(result){
                        $scope.expertList = result.data.expert;
                        if (selectedBU == '' || selectedBU == null ) {
                            if (contributorType == 'expert') {
                                $scope.data.expert.contributors = [];
                            }
                            else{
                                $scope.data.expert.contributors = [];
                            }                            
                            angular.forEach($scope.expert.contributors1, function(i,o){
                                angular.forEach(result.data.expert, function(i1,o1){
                                    if(i.id_user===i1.id_user){
                                        $scope.data.expert.contributors.push(i1);
                                    }                                 
                                });
                            });
                            angular.forEach($scope.data.expert.contributors, function(i,o){
                                $scope.expert.contributors1.push(i.id_user);
                            });
                        } else {}
                    });
                };
                $scope.getContributorsByBusinessUnit1('');
                $scope.getContributorsByBusinessUnit2 = function(selectedBU,contributorType) {
                    $scope.validatorList = [];
                    var params = {};
                    params.contract_id = $scope.contract_id;
                    params.type = 'contributor';
                    params.user_role_id = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.user_id = $scope.user.id_user;
                    params.project_id = decode($stateParams.id);
                    if (selectedBU !== '') {
                        params.business_unit_id = selectedBU;
                    }  
                   // $scope.getTopicQuestions(Globalparams,false);                  
                    contractService.reviewUsers(params).then(function(result){
                        $scope.validatorList = result.data.validator;
                        if (selectedBU == '' || selectedBU == null ) {
                            
                            if (contributorType == 'validator') {
                                $scope.data.validator.contributors = [];
                            }
                            else{
                                $scope.data.validator.contributors = [];
                            }
                            angular.forEach($scope.validator.contributors1, function(i,o){
                                console.log('i info',i);
                                console.log('o info',o);
                                angular.forEach(result.data.validator, function(i1,o1){
                                    if(i.id_user===i1.id_user){
                                        $scope.data.validator.contributors.push(i1);
                                        $scope.data.validator.searchValidatorContracts=i1;                                      
                                    }                                 
                                });
                            });
                            angular.forEach($scope.data.validator.contributors, function(i,o){
                                $scope.validator.contributors1.push(i.id_user);
                            });
                        } else {}
                    });
                };
                $scope.getContributorsByBusinessUnit2('');


                $scope.getContributorsByBusinessUnit3 = function(selectedBU,contributorType) {
                    $scope.providerList = [];
                    var params = {};
                    params.contract_id = $scope.contract_id;
                    params.type = 'contributor';
                    params.user_role_id = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.user_id = $scope.user.id_user;
                    params.project_id = decode($stateParams.id);
                    if (selectedBU !== '') {
                        params.id_provider = selectedBU;
                    }  
                    //$scope.getTopicQuestions(Globalparams,false);                  
                    contractService.reviewUsers(params).then(function(result){
                        $scope.providerList = result.data.provider;
                        if (selectedBU == '' || selectedBU == null ) {
                             if (contributorType == 'provider') {
                                $scope.data.provider.contributors = [];
                            }
                            else{
                                $scope.data.provider.contributors = [];
                            }     
                            //console.log($scope.provider);                       
                            angular.forEach($scope.provider.contributors1, function(i,o){
                                angular.forEach(result.data.provider, function(i1,o1){
                                    if(i.id_user===i1.id_user){
                                        $scope.data.provider.contributors.push(i1);
                                    }                                 
                                });
                            });
                            //console.log($scope.data.provider.contributors);
                            angular.forEach($scope.data.provider.contributors, function(i,o){
                                $scope.provider.contributors1.push(i.id_user);
                            });
                        } else {}
                    });
                };
                var isPresent = false;

                $scope.getContributorsByBusinessUnit3('');

                $scope.addProviderContributer = function(data,type){
                    // console.log('data info',data);
                    // console.log('type info',type);
                    // console.log('length',$scope.data.provider.contributors);
                    if (!$scope.data.provider.contributors) {
                       $scope.data.provider.contributors=[];
                    }                                  
                    var isPresent = false;
                    var isAdded = false;
                    var isenable = false;
                    angular.forEach($scope.data.provider.contributors, function(i,o){
                        if ((i.id_user  && i.id_user && i.id_provider ) === (data.id_user && data.id_provider)){
                            isPresent = true;
                            isenable=false;
                        }
                        if((i.id_provider == data.id_provider) && (i.id_user != data.id_user) ){
                            isAdded =true;
                            isenable=true;
                        }
                     });
                    
                     if(!isPresent){
                         $scope.data.provider.contributors.push(data);
                     }
                     if(isPresent && !isenable){
                        $rootScope.toast('Error', 'Contributor already added.');
                     }
                     if(isAdded && isenable){
                        $rootScope.toast('Error', 'Select only one user per provider');
                     }
              
               }
               
                $scope.addExpertContributer = function(data){
                    if (!$scope.data.expert.contributors) {
                        $scope.data.expert.contributors = [];
                    }                    
                    var isPresent = false;
                    angular.forEach($scope.data.expert.contributors, function(i,o){
                       if (i.id_user && i.id_user === data.id_user)
                            isPresent = true;
                    });
                    if (!isPresent) {
                        $scope.data.expert.contributors.push(data);
                        $scope.showContractList = false;
                    } else {
                        $rootScope.toast('Error', 'Contributor already added.');
                    }
                }
                $scope.addValidatorContributer = function(data,type){
                    $scope.data.validator.contributors = [];
                    if(data){
                       $scope.data.validator.contributors.push(data);
                        $scope.showContractList1 = false;  
                    }
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                var params ={};
                $scope.showBtn = false;
                $scope.saveContributors=function(data){
                    $scope.showBtn = true;
                    params.contract_review_id = $scope.contract_review_id;
                    params.module_id = $scope.module_id;
                    params.created_by = $scope.user.id_user;
                    params.contract_id = params.id_contract = $scope.contract_id;
                    params.topic_id = $scope.topic_no;
                    params.user_role_id  = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.expert={};
                    params.validator={};
                    params.provider={};
                    params.expert.business_unit=data.expert.business_unit;
                    params.validator.business_unit=data.validator.business_unit;

                    var a11 = [],a12 = [],a13 = [],a2 = [], a3 = [], a4 = [];
                    var diff1=[];var diff2=[];var diff3=[];;

                    angular.forEach($scope.expert.contributors1, function(i,o){
                        a11.push(i.id_user);
                    });
                    angular.forEach($scope.validator.contributors1, function(i,o){
                        a12.push(i.id_user);
                    });
                    angular.forEach($scope.provider.contributors1, function(i,o){
                        a13.push(i.id_user);
                    });
                    $rootScope.contributors = data.contributors;
                    $rootScope.expert.contributors = data.expert;
                    $rootScope.validator.contributors = data.validator;
                    $rootScope.provider.contributors  = data.provider;
                    angular.forEach($scope.data.expert.contributors, function(i,o){
                        a2.push(i.id_user);
                    });
                    if($scope.data.validator.contributors[0])
                        a3.push($scope.data.validator.contributors[0].id_user);                    
                    angular.forEach($scope.data.provider.contributors, function(i,o){
                        a4.push(i.id_user);
                    });                   
                    params.expert.contributors_add = a2.join(',');
                    params.validator.contributors_add = a3.join(',');
                    params.provider.contributors_add = a4.join(',');
                    if(a11!=undefined)if(a11.length>0)  diff1 = a11.diff(a2);   
                    if(a12!=undefined)if(a12.length>0)  diff2 = a12.diff(a3);
                    if(a13!=undefined)if(a13.length>0)  diff3 = a13.diff(a4);
                    if(diff1.length<=0){
                        params.expert.contributors_remove = '';
                    } else {
                        for (var i = 0; i < diff1.length; i++) {
                            if(diff1[i] == undefined){
                                delete diff1[i];
                            }
                        }
                        params.expert.contributors_remove = diff1.join(',');
                        params.expert.contributors_remove = params.expert.contributors_remove.replace(/,\s*$/, "");   /*remove last comma which is getting appended*/
                    }
                    if(diff2.length<=0){
                        params.validator.contributors_remove = '';
                    } else {
                        for (var i = 0; i < diff2.length; i++) {
                            if(diff2[i] == undefined){
                                delete diff2[i];
                            }
                        }
                        params.validator.contributors_remove = diff2.join(',');
                        params.validator.contributors_remove = params.validator.contributors_remove.replace(/,\s*$/, "");   /*remove last comma which is getting appended*/
                    }
                    if(diff3.length<=0){
                        params.provider.contributors_remove = '';
                    } else {
                        for (var i = 0; i < diff3.length; i++) {
                            if(diff3[i] == undefined){
                                delete diff3[i];
                            }
                        }
                        params.provider.contributors_remove = diff3.join(',');
                        params.provider.contributors_remove = params.provider.contributors_remove.replace(/,\s*$/, "");   /*remove last comma which is getting appended*/
                    }
                    $scope.showBtn = false;
                    projectService.addProjectContributors(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'update';
                            obj.action_description = 'update$$Cotributors$$('+$stateParams.name +' - '+ $stateParams.mName+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.getContributorsList(params); //not required
                            $scope.cancel();
                        } else {
                            $rootScope.toast('Error', result.error,'error');
                        }
                    });
                }
                $scope.remove = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                    $scope.data.contributors.splice(index, 1);
                    }
                };
                $scope.removeExpert = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                    $scope.data.expert.contributors.splice(index, 1);
                    }
                };
                $scope.removeValidator = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                        delete $scope.data.validator.searchValidatorContracts;
                        $scope.data.validator.contributors[0]=false;
                    }
                };
                $scope.removeProvider = function(index){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    if(r==true){
                    $scope.data.provider.contributors.splice(index, 1);
                    }                   
                };
            }
        });
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    };
    Array.prototype.diff = function(a) {
        return this.filter(function(i) {return a.indexOf(i) < 0;});
    };
     $scope.addFile = function (){ $scope.showFileToAttach = true;}
    $scope.removeFile = function (){ $scope.showFileToAttach = false;}
    $scope.addAttachment = function(file){
        var file = file;
        if(file){
            $rootScope.pageLoading =false;
            $rootScope.appInitialized=true;
            Upload.upload({
                url: API_URL+'Document/add',
                data:{
                    file:file,
                    customer_id : $scope.user1.customer_id,
                    module_id : $scope.contract_review_id,
                    module_type : 'contract_review',
                    is_workflow : $scope.isWorkflow,
                    reference_id : decode($stateParams.tId),
                    reference_type : 'topic',
                    uploaded_by : $scope.user1.id_user,
                    user_role_id  : $scope.user1.user_role_id,
                    contract_workflow_id : decode($stateParams.wId),
                }
            }).then(function (resp) {
                if(resp.data.status){
                    $rootScope.pageLoading =true;
                    $rootScope.appInitialized=false;
                    $rootScope.toast('Success',resp.data.message);
                    var obj = {};
                    obj.action_name = 'upload';
                    obj.action_description = 'upload$$Attachments$$('+$stateParams.name +' - '+ $stateParams.mName+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.removeFile();
                    $scope.getAttachmentList($scope.tableDocStateRef);
                }
                else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
            }, function (resp) {
                $rootScope.toast('Error',resp.data.error,'error');
            }, function (evt) {
                $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
            });

        }else{
            $rootScope.toast('Error','invalid format','image-error');
        }
    }
    $scope.deleteDocument = function(row,name){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            var params={};
            params = angular.copy(row);
            attachmentService.deleteAttachments(params).then (function(result){
                if(result.status){
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$Attachment$$('+name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $rootScope.toast('Success',result.data.message);
                    $scope.getAttachmentList($scope.tableDocStateRef);
                    $scope.getTopicQuestions(Globalparams,false);
                }
            })
        }
    }
    $scope.deleteContractActionItem = function(row){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            var params = angular.copy(row);
            params.updated_by  = $rootScope.id_user ;
            contractService.deleteActionItem(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$Action Item$$('+row.action_item+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.reviewAction($scope.tableStateRef);
                    $scope.getTopicQuestions(Globalparams,true);

                }else $rootScope.toast('Error', result.error, 'error',$scope.user);
            });
        }
    }
  
   
    $scope.cancel = function(){
        $state.go('app.projects.project-module-task',{name:$stateParams.name,id: $stateParams.id, rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
    }
    $scope.goToSave = function(data, options, opt){
        if(data){
            var params = {};
            if($stateParams.rId)params.contract_review_id = decode($stateParams.rId);
            params.created_by = $rootScope.id_user;
            $scope.options = {};
            angular.forEach($scope.contractModuleTopics.topics[0].questions, function(i,o){
                if(!$scope.contractModuleTopics.side_by_side_validation){
                    $scope.options[o] = {};
                    $scope.options[o].question_id = i.id_question;
                    $scope.options[o].parent_question_id = i.parent_question_id;
                    if(i.question_type =='date')
                        $scope.options[o].question_answer = dateFilter(data.answers[i.id_question],'yyyy-MM-dd');
                    else if(i.question_type  != 'date') {
                        $scope.options[o].question_answer = data.answers[i.id_question];
                    }
                    else $scope.options[o].question_answer = '';

                    if(data.external_user_question_feedback[i.id_question])
                    $scope.options[o].external_user_question_feedback = data.external_user_question_feedback[i.id_question];
                    else $scope.options[o].external_user_question_feedback = '';


                    if(data.feedback[i.id_question])
                        $scope.options[o].question_feedback = data.feedback[i.id_question];
                    else $scope.options[o].question_feedback = '';
                }
                if(($scope.contractModuleTopics.side_by_side_validation && !i.readOnly)){
                    $scope.options[o] = {};
                    $scope.options[o].question_id = i.id_question;
                    $scope.options[o].parent_question_id = i.parent_question_id;
                    //console.log('o info',o,o%2==0);
                    if(i.question_type =='date'){
                        if(o%2==0){
                            $scope.options[o].v_question_answer = dateFilter(data.answers[i.id_question+"1"],'yyyy-MM-dd');
                            $scope.options[o].question_answer = dateFilter(data.answers[i.id_question],'yyyy-MM-dd');
                        }else{
                            $scope.options[o].v_question_answer = dateFilter(data.answers[i.id_question],'yyyy-MM-dd');
                            $scope.options[o].question_answer = dateFilter(data.answers[i.id_question+"1"],'yyyy-MM-dd');
                        }
                    }
                    else if(i.question_type  != 'date') {
                        if(o%2==0){
                            $scope.options[o].v_question_answer =  data.answers[i.id_question+"1"];
                            $scope.options[o].question_answer =  data.answers[i.id_question];
                        }else{
                            $scope.options[o].v_question_answer =  data.answers[i.id_question];
                            $scope.options[o].question_answer =  data.answers[i.id_question+"1"];
                        }
                    }
                    else $scope.options[o].v_question_answer = '';
                    data.feedback[i.id_question] = (data.feedback[i.id_question]) ? data.feedback[i.id_question] : '';
                    data.feedback[i.id_question+"1"] = (data.feedback[i.id_question+"1"]) ? data.feedback[i.id_question+"1"] : '';
                    if(o%2==0){
                        $scope.options[o].question_feedback = data.feedback[i.id_question];
                        $scope.options[o].v_question_feedback = data.feedback[i.id_question+"1"]; 
                    }else{
                        $scope.options[o].question_feedback = data.feedback[i.id_question+"1"];
                        $scope.options[o].v_question_feedback = data.feedback[i.id_question];
                    }
                }                
            });
            if($scope.contractModuleTopics.side_by_side_validation){
                var arr = [];
                angular.forEach($scope.options, function(i,o){
                    arr.push(i);
                });
                $scope.options = arr;
            }
            //console.log($scope.options);
            params.data = $scope.options;
            options.type=($scope.isWorkflow=='1')?'workflow':'review';
            if($scope.isWorkflow=='1'){
                options.wId= $stateParams.wId;
                params.id_contract_workflow= decode($stateParams.wId);
            }
            params.is_workflow = $scope.isWorkflow;
            params.id_module = $scope.contractModuleTopics.id_module;
            params.module_status = $scope.contractModuleTopics.module_status;
            params.is_workflow = $scope.isWorkflow;
            contractService.answerQuestion(params).then(function(result){
                if(result.status){
                    if(result.toaster)$rootScope.toast('Success', result.message);
                    var obj = {};
                    obj.action_name = 'save';
                    obj.action_description = 'save$$module$$questions$$('+$stateParams.mName+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    params.module_id = $scope.module_id;
                    params.contract_id = $scope.contract_id;
                    params.id_topic = decode($stateParams.tId);
                    if(options!=''){
                        if(opt == 'exit' && $rootScope.access !='eu') {
                            $state.go('app.projects.project-task',options);
                        }
                        else if($rootScope.access !='eu'){
                           $state.go('app.projects.project-module-task',options,{reload:true, inherit:false });
                        }
                       
                        if(opt=='exit' && $rootScope.access=='eu'){
                            $state.go('app.projects.project-task11',options);
                        }
                        else if($rootScope.access=='eu'){
                            $state.go('app.projects.project-module-task11',options,{reload:true, inherit:false });
                        }                
                    }
                    $scope.getTopicQuestions(Globalparams,false);
                    //$scope.getTopicQuestions(params); //not required
                } else $rootScope.toast('Error', result.error, 'error');
            });
        }
    }
    $scope.save = function(options){
        $scope.goToSave(options,'','');
        var params = {};
        if($stateParams.rId)params.contract_review_id = decode($stateParams.rId);
        params.module_id = $scope.module_id;
        params.contract_id = $scope.contract_id;
        params.id_topic = decode($stateParams.tId);
        //$scope.getTopicQuestions(params); //not required
    }
    $scope.saveAndPrev = function(obj,options){
        $scope.goToSave(options, {name:$stateParams.name,
            id:$stateParams.id,
            rId:$stateParams.rId,
            mName:$stateParams.mName,
            moduleId:$stateParams.moduleId,
            tName:obj.previous_text,tId:encode(obj.previous)}, 'previous');
        var params = {};
        if($stateParams.rId)params.contract_review_id = decode($stateParams.rId);
        params.module_id = $scope.module_id;
        params.contract_id = $scope.contract_id;
        params.id_topic = decode($stateParams.tId);
    }
    $scope.saveAndNext = function(obj,options){
        $scope.goToSave(options, {name:$stateParams.name,
            id:$stateParams.id,
            rId:$stateParams.rId,
            mName:$stateParams.mName,
            moduleId:$stateParams.moduleId,
            tName:obj.next_text,tId:encode(obj.next)}, 'next');
        var params = {};
        if($stateParams.rId)params.contract_review_id = decode($stateParams.rId);
        params.module_id = $scope.module_id;
        params.contract_id = $scope.contract_id;
        params.id_topic = decode($stateParams.tId);
       

    }
    $scope.saveAndExit = function(options){
       $scope.goToSave(options, {name:$stateParams.name,id: $stateParams.id, rId:$stateParams.rId}, 'exit');
    }
    $scope.validationEnd = function(){
        var r=confirm($filter('translate')('general.alert_validation_finalize'));
        $scope.deleConfirm = r;
        if(r==true){
            contractService.validationEnd({'id_module': $scope.contractModuleTopics.id_module}).then(function (result) {
                if (result.status) {
                    $rootScope.toast('Success', result.message);
                    var obj = {};
                    obj.action_name = 'validation';
                    obj.action_description = 'validation$$review$$ended$$('+$stateParams.name +' - '+ $stateParams.mName+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    if($scope.isWorkflow=='1')
                        $state.go(goWorkflow, {name:$stateParams.name,id: $stateParams.id, rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
                    else
                        $state.go(goReview, {name:$stateParams.name,id: $stateParams.id, rId:$stateParams.rId,type:'review'});
                } else {
                    $rootScope.toast('Error', result.error,'error');
                }
            });
        }        
    }   
    $scope.addQuestionAttachemnts = function(row) {
        var proceed=true;
       if($scope.contractModuleTopics.side_by_side_validation && row.readOnly){proceed=false;}
       else proceed=true;
       if(proceed){
            $scope.contractLinks = [];
            $scope.contractLink={};
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/contracts/question-level-attachments.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.bottom = 'general.save';
                    $scope.isEdit = false;
                    if (item) {
                        $scope.isEdit = true;
                        $scope.getQuestionAttachmentsList = function(id){
                            var param ={};
                            param. customer_id = $scope.user1.customer_id;
                            if($stateParams.rId)param.module_id = decode($stateParams.rId);
                            param.module_type =  'contract_review';
                            param.page_type = 'contract_review';
                            param.reference_id = id;
                            param.reference_type = 'question';
                            param.id_user  = $rootScope.id_user;
                            param.user_role_id  = $rootScope.user_role_id;
                            param.is_workflow  = $scope.isWorkflow;
                            param.contract_workflow_id  = decode($stateParams.wId);
                            contractService.getAttachments(param).then(function(result){
                                $scope.questionAttachmentList = result.data.documents.data;
                                $scope.questionLinkList = result.data.links.data;
                            });
                        }
                        $scope.getQuestionAttachmentsList(row.id_question);
                    }
                    $scope.saveAnswer = function(){
                        $scope.question = item;
                        var obj = {};
                        obj.contract_review_id=$scope.contract_review_id;
                        obj.created_by=$rootScope.id_user;
                        $scope.options = {};   
                        angular.forEach($scope.contractModuleTopics.topics[0].questions, function(i,o){
                            if(!$scope.contractModuleTopics.side_by_side_validation){
                                $scope.options[o] = {};
                                $scope.options[o].question_id = i.id_question;
                                $scope.options[o].parent_question_id = i.parent_question_id;
                                if(i.question_type =='date')
                                    $scope.options[o].question_answer = dateFilter($scope.optionsData.answers[i.id_question],'yyyy-MM-dd');
                                else if(i.question_type  != 'date') {
                                    $scope.options[o].question_answer = $scope.optionsData.answers[i.id_question];
                                }
                                else $scope.options[o].question_answer = '';

                                if($scope.optionsData.external_user_question_feedback[i.id_question])
                                $scope.options[o].external_user_question_feedback = $scope.optionsData.external_user_question_feedback[i.id_question];
                                else $scope.options[o].external_user_question_feedback = '';

                            
                                if($scope.optionsData.feedback[i.id_question])
                                    $scope.options[o].question_feedback = $scope.optionsData.feedback[i.id_question];
                                else $scope.options[o].question_feedback = '';
                            }
                            if(($scope.contractModuleTopics.side_by_side_validation && !i.readOnly)){
                                $scope.options[o] = {};
                                $scope.options[o].question_id = i.id_question;
                                $scope.options[o].parent_question_id = i.parent_question_id;
                                if(i.question_type =='date'){
                                    if(o%2==0){
                                        $scope.options[o].v_question_answer = dateFilter( $scope.optionsData.answers[i.id_question+"1"],'yyyy-MM-dd');
                                        $scope.options[o].question_answer = dateFilter( $scope.optionsData.answers[i.id_question],'yyyy-MM-dd');
                                    }else{
                                        $scope.options[o].v_question_answer = dateFilter( $scope.optionsData.answers[i.id_question],'yyyy-MM-dd');
                                        $scope.options[o].question_answer = dateFilter( $scope.optionsData.answers[i.id_question+"1"],'yyyy-MM-dd');
                                    }
                                }
                                else if(i.question_type  != 'date') {
                                    if(o%2==0){
                                        $scope.options[o].v_question_answer =   $scope.optionsData.answers[i.id_question+"1"];
                                        $scope.options[o].question_answer =   $scope.optionsData.answers[i.id_question];
                                    }else{
                                        $scope.options[o].v_question_answer =   $scope.optionsData.answers[i.id_question];
                                        $scope.options[o].question_answer =   $scope.optionsData.answers[i.id_question+"1"];
                                    }
                                }
                                else $scope.options[o].v_question_answer = '';

                                $scope.optionsData.feedback[i.id_question] = ( $scope.optionsData.feedback[i.id_question]) ?  $scope.optionsData.feedback[i.id_question] : '';
                                $scope.optionsData.feedback[i.id_question+"1"] = ( $scope.optionsData.feedback[i.id_question+"1"]) ?  $scope.optionsData.feedback[i.id_question+"1"] : '';

                                $scope.optionsData.external_user_question_feedback[i.id_question] = ( $scope.optionsData.external_user_question_feedback[i.id_question]) ?  $scope.optionsData.external_user_question_feedback[i.id_question] : '';
                                $scope.optionsData.external_user_question_feedback[i.id_question+"1"] = ( $scope.optionsData.external_user_question_feedback[i.id_question+"1"]) ?  $scope.optionsData.external_user_question_feedback[i.id_question+"1"] : '';
                                if(o%2==0){
                                    $scope.options[o].question_feedback =  $scope.optionsData.feedback[i.id_question];
                                    $scope.options[o].v_question_feedback =  $scope.optionsData.feedback[i.id_question+"1"]; 
                                }else{
                                    $scope.options[o].question_feedback =  $scope.optionsData.feedback[i.id_question+"1"];
                                    $scope.options[o].v_question_feedback =  $scope.optionsData.feedback[i.id_question];
                                }
                            }                
                        });
                        if($scope.contractModuleTopics.side_by_side_validation){
                            var arr = [];
                            angular.forEach($scope.options, function(i,o){
                                arr.push(i);
                            });
                            $scope.options = arr;
                        }
                        obj.data = $scope.options;
                        obj.id_module = $scope.contractModuleTopics.id_module;
                        obj.module_status = $scope.contractModuleTopics.module_status;
                        obj.is_workflow = 1;
                        if($scope.isWorkflow=='1'){
                            obj.id_contract_workflow= decode($stateParams.wId);
                        }
                        //console.log('paru',obj);
                        contractService.answerQuestion(obj).then(function(result) {
                            if(result.status){
                                var obj = {};
                                obj.action_name = 'save';
                                obj.action_description = 'save$$module$$questions$$('+$stateParams.mName+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                contractService.getcontractReviewModules(Globalparams).then(function(result){
                                    angular.forEach(result.data[0].topics[0].questions, function(i,o){
                                        $scope.contractModuleTopics.topics[0].questions[o].attachment_count = i.attachment_count;
                                    })
                                });
                                $scope.getTopicQuestions(Globalparams,true);
                            }
                            else $rootScope.toast('Error', result.error, 'error');
                        });
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.deleteQuestionAttachment = function(id,name){
                        var r=confirm($filter('translate')('general.alert_continue'));
                        $scope.deleConfirm = r;
                        if(r==true){
                            var params = {};
                            params.id_document = id;
                            params.contract_review_id=$scope.contract_review_id;
                            params.parent_question_id=row.parent_question_id;
                            params.question_id=row.id_question;
                            attachmentService.deleteAttachments(params).then (function(result){
                                $rootScope.toast('Success',result.data.message);
                                var obj = {};
                                obj.action_name = 'delete';
                                obj.action_description = 'delete$$module$$question$$attachement$$('+name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.saveAnswer();
                                $scope.getQuestionAttachmentsList(row.id_question);
                                $scope.getAttachmentList($scope.tableDocStateRef);                                                              
                            })
                        }
                    }
                    var params ={};
                    $scope.addQuestionAttachemts=function(data){
                        var file = data;
                        if(file){
                            Upload.upload({
                                url: API_URL+'Document/add',
                                data:{
                                    file:file,
                                    customer_id: $scope.user1.customer_id,
                                    module_id: decode($stateParams.rId),
                                    module_type: 'contract_review',
                                    reference_id: row.id_question,
                                    is_workflow: $scope.isWorkflow,
                                    contract_review_id: $scope.contract_review_id,
                                    parent_question_id: row.parent_question_id,
                                    reference_type: 'question',
                                    document_type:0,
                                    contract_workflow_id : decode($stateParams.wId),
                                    uploaded_by: $scope.user1.id_user
                                }
                            }).then(function (resp) {
                                if(resp.data.status){
                                    $scope.getAttachmentList($scope.tableDocStateRef);
                                    $rootScope.toast('Success',resp.data.message);
                                    var obj = {};
                                    obj.action_name = 'upload';
                                    obj.action_description = 'upload$$module$$question$$attachement$$('+$stateParams.mName+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.removeFile();
                                    $scope.saveAnswer();                                    
                                    $scope.cancel();
                                }
                                else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                            }, function (resp) {
                                $rootScope.toast('Error',resp.data.error,'error');
                            }, function (evt) {
                                $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                            });

                        }else{
                            $rootScope.toast('Error','invalid format','image-error');
                        }
                    }
                    $scope.verifyLink = function(data){
                        if(data !={}){
                            $scope.contractLinks.push(data);
                            $scope.contractLink={};
                        }
                    }
                    $scope.removeLink = function(index){
                        var r=confirm($filter('translate')('general.alert_continue'));
                        if(r==true){
                            $scope.contractLinks.splice(index, 1);
                        }                    
                    }
                    $scope.uploadLinks = function (contractLinks) {
                        var file = contractLinks;
                        if(contractLinks){
                            Upload.upload({
                                url: API_URL+'Document/add',
                                data:{
                                    file:contractLinks,
                                    customer_id: $scope.user1.customer_id,
                                    module_id: decode($stateParams.rId),
                                    module_type: 'contract_review',
                                    reference_id: row.id_question,
                                    is_workflow: $scope.isWorkflow,
                                    contract_review_id: $scope.contract_review_id,
                                    parent_question_id: row.parent_question_id,
                                    document_type:1,
                                    reference_type: 'question',
                                    contract_workflow_id : decode($stateParams.wId),
                                    uploaded_by: $scope.user1.id_user
                                }
                            }).then(function (resp) {
                                if(resp.data.status){
                                    $scope.getAttachmentList($scope.tableDocStateRef);
                                    $rootScope.toast('Success',resp.data.message);
                                    var obj = {};
                                    obj.action_name = 'upload';
                                    obj.action_description = 'upload$$module$$question$$link$$('+$stateParams.mName+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.removeFile();
                                    $scope.saveAnswer();
                                   
                                    $scope.cancel();
                                }
                                else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                            }, function (resp) {
                                    $rootScope.toast('Error',resp.data.error,'error');
                            }, function (evt) {
                                $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                            });
                        }else{
                            $rootScope.toast('Error','No link selected','image-error');
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
        }
    }
    
    $scope.addSecondOpinion = function(row,topic) {
        $scope.question = row;
        // if(!$scope.contractModuleTopics.action_item_question_link){
            var obj = {};
            obj.contract_review_id=$scope.contract_review_id;
            obj.created_by=$rootScope.id_user;
            $scope.options = {};          
            angular.forEach($scope.contractModuleTopics.topics[0].questions, function(i,o){
                $scope.options[o] = {};
                $scope.options[o].question_id = i.id_question;
                $scope.options[o].parent_question_id = i.parent_question_id;
                if(i.question_type =='date')
                    $scope.options[o].question_answer = dateFilter($scope.optionsData.answers[i.id_question],'yyyy-MM-dd');
                else if(i.question_type  != 'date') {
                    $scope.options[o].question_answer = $scope.optionsData.answers[i.id_question];
                }
                else $scope.options[o].question_answer = '';

                if($scope.optionsData.external_user_question_feedback[i.id_question])
                $scope.options[o].external_user_question_feedback = $scope.optionsData.external_user_question_feedback[i.id_question];
                else $scope.options[o].external_user_question_feedback = '';


                if($scope.optionsData.feedback[i.id_question])
                    $scope.options[o].question_feedback = $scope.optionsData.feedback[i.id_question];
                else $scope.options[o].question_feedback = '';
            });
            obj.data = $scope.options;
            obj.id_module = $scope.contractModuleTopics.id_module;
            obj.module_status = $scope.contractModuleTopics.module_status;
            obj.is_workflow = $scope.isWorkflow;
            if($scope.isWorkflow=='1'){
                obj.id_contract_workflow= decode($stateParams.wId);
            }
            contractService.answerQuestion(obj).then(function(result){
                if(result.status){
                    var obj = {};
                    obj.action_name = 'save';
                    obj.action_description = 'save$$module$$questions$$('+$stateParams.mName+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                } else $rootScope.toast('Error', result.error, 'error');
            });
     
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/contracts/add-second-opinion.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.title = 'contract.add_second_opinion';
                    $scope.bottom = 'general.save';
                    $scope.isEdit = false;
                    var params = {};         
                    $scope.options = {};                           
                    $scope.showBtn = false;
                    $scope.options.second_opinion = $scope.question.second_opinion;
                    $scope.options.remarks = $scope.question.remarks;
                    if($scope.question.question_type == 'date'){
                        $scope.question.question_answer = ($scope.question.question_answer)?moment($scope.question.question_answer).utcOffset(0, false).toDate():null;
                    }
                    $scope.saveSecondOpinion = function(data){  
                        params.module_id =  $scope.module_id ;
                        params.contract_review_id =  $scope.contract_review_id;
                        params.question_id = row.id_question;
                        params.remarks = data.remarks;
                        if($scope.question.question_type == 'date'){
                            params.second_opinion = dateFilter(data.second_opinion,'yyyy-MM-dd');
                        }else
                            params.second_opinion = data.second_opinion;
                        params.created_by =  $scope.user1.id_user
                        params.type='project';
                        contractService.addSecondOpinion(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'add';
                                obj.action_description = 'add$$second$$opinion$$('+$stateParams.name +' - '+ $stateParams.mName+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $scope.cancel();
                                $scope.getTopicQuestions(Globalparams,false);
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });
                    }
                    $scope.remove = function(index){
                        $scope.data.contributors.splice(index, 1);
                    };
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
        // }
        
    }
    $scope.settings = { 
        template: "<div>{{option.name}}</div>",
        scrollable :true,
        showUncheckAll:false,
        showCheckAll:false,
        displayProp:'name',
        enableSearch:true,
        clearSearchOnClose:true,
        checkBoxes:true
    }
    $scope.events = { 
      //  onItemSelect: addExpertContributer(option,'expert')
    }

    $scope.goToDesign = function(){
        if($rootScope.access !='eu')
           $state.go('app.projects.task-design',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        if($rootScope.access=='eu')
        $state.go('app.projects.task-design1',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
    }
    $scope.saveForTab = function(obj,options){
        $scope.goToSave(options, {name:$stateParams.name,
            id:$stateParams.id,
            rId:$stateParams.rId,
            mName:$stateParams.mName,
            moduleId:$stateParams.moduleId,
            tName:obj.next_text,tId:encode(obj.next)}, 'tab');
    }
    $scope.switchTopic = function(tabInfo,optionsData,indx){
        $scope.activateTab(tabInfo,indx);
        var obj={}
        obj.next_text = tabInfo.topic_name;obj.next = tabInfo.id_topic;
        $scope.saveForTab(obj,optionsData);
    }
    $scope.validatorQuestions = function(){
        $scope.dualQuestions = [];
        angular.forEach($scope.contractModuleTopics.topics[0].questions, function(item,key){            
            var obj = {};
            var isValidator = (angular.lowercase($scope.contractModuleTopics.contract_user_access)=="validator");

            obj = angular.copy(item);
            obj.blueV = false;
            obj.readOnly=(isValidator)?true:false;
            obj.id_question= (isValidator)?obj.id_question+"1":obj.id_question;
            $scope.dualQuestions.push(obj);
          
            var obj1={};
            obj1 = angular.copy(item);
            obj1.blueV = true;
            obj1.readOnly = (isValidator)?false:true;
            obj1.id_question= (isValidator)?obj1.id_question:obj1.id_question+"1";
            $scope.dualQuestions.push(obj1);
        });
        angular.forEach($scope.dualQuestions, function(item,key){
            if(!item.blueV){
                if(item.question_type == 'date'){
                    $scope.optionsData.answers[item.id_question] = (item.parent_question_answer)?moment(item.parent_question_answer).utcOffset(0, false).toDate():null; 
                }
                else $scope.optionsData.answers[item.id_question] = item.parent_question_answer;  
                $scope.optionsData.feedback[item.id_question] = item.question_feedback;
                $scope.optionsData.external_user_question_feedback[item.id_question] = item.external_user_question_feedback;
                item.state=false;
                item.state1=false;
               if(item.help_text)
                   item.help_text = $sce.trustAsHtml('<pre class="text-tooltip" style="text-align:left;">'+item.help_text+'</pre>');
            }else{
                if(item.question_type == 'date'){
                    $scope.optionsData.answers[item.id_question] = (item.v_parent_question_answer)?moment(item.v_parent_question_answer).utcOffset(0, false).toDate():null; 
                }
                else $scope.optionsData.answers[item.id_question] = item.v_parent_question_answer;  
                $scope.optionsData.feedback[item.id_question] = item.v_question_feedback;
                $scope.optionsData.external_user_question_feedback[item.id_question] = item.external_user_question_feedback;
                item.state=false;
                item.state1=false;
               if(item.help_text)
                   item.help_text = $sce.trustAsHtml('<pre class="text-tooltip" style="text-align:left;">'+item.help_text+'</pre>');
            }
        });
        $scope.contractModuleTopics.topics[0].questions = angular.copy($scope.dualQuestions);
        // console.log("dual**-*-*-*-",$scope.dualQuestions);
    } 
    $scope.copyAnswers = function(item,ind,res){
        if(res =='-1'){
            document.getElementById("toggle-"+ind).classList.remove('green-color','red-color');
            document.getElementById("toggle-"+ind).classList.add('blue-color');
            document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
            document.getElementById("question_"+item.id_question+"_blue").classList.add('blue-circle');
            document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
        }
        else if(res=='1') {
            document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
            document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
            document.getElementById("question_"+item.id_question+"_green").classList.add('green-circle');
            document.getElementById("toggle-"+ind).classList.remove('blue-color','red-color');
            document.getElementById("toggle-"+ind).classList.add('green-color');
            
        }
        else if(res=='0'){
            document.getElementById("question_"+item.id_question+"_red").classList.add('red-circle');
            document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
            document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
            document.getElementById("toggle-"+ind).classList.remove('green-color','blue-color');
            document.getElementById("toggle-"+ind).classList.add('red-color');
        }
       if(item.question_type=='date'){
            $scope.optionsData.answers[item.id_question] =moment(item.parent_question_answer).utcOffset(0, false).toDate();
       }
       else {
           $scope.optionsData.answers[item.id_question] = item.parent_question_answer;
       }
       
    }

    $scope.emptyAnswers = function(item,ind,res){
        if(res =='-1'){
            document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
            document.getElementById("question_"+item.id_question+"_blue").classList.add('blue-circle');
            document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
            document.getElementById("toggle-"+ind).classList.remove('green-color','red-color');
            document.getElementById("toggle-"+ind).classList.add('blue-color');
        }
        else if(res=='1') {
            //alert('entered to res 1');
            document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
            document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
            document.getElementById("question_"+item.id_question+"_green").classList.add('green-circle');
            document.getElementById("toggle-"+ind).classList.remove('blue-color','red-color');
            document.getElementById("toggle-"+ind).classList.add('green-color');
        }
        else if(res=='0'){
            document.getElementById("question_"+item.id_question+"_red").classList.add('red-circle');
            document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
            document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
            document.getElementById("toggle-"+ind).classList.remove('blue-color','green-color');
            document.getElementById("toggle-"+ind).classList.add('red-color');
        }
       if(item.question_type=='date'){
            $scope.optionsData.answers[item.id_question] ='';
       }
       else {
           $scope.optionsData.answers[item.id_question] = '';
       }
       
    }
   

    $scope.changedValue = function(val,item,ind){
        console.log('val',val);
         console.log('ind',ind);
         console.log('item',item);
        $scope.selectedAnswer = val;
        if(item.question_type=='date'){
            $scope.selectedAnswer  = dateFilter( $scope.selectedAnswer,'yyyy-MM-dd');
           if($scope.selectedAnswer !=item.parent_question_answer) 
           {  
            document.getElementById("question_"+item.id_question+"_red").classList.add('red-circle');
            document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
            document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
            document.getElementById("toggle-"+ind).classList.remove('blue-color','green-color');
            document.getElementById("toggle-"+ind).classList.add('red-color');
            }
            if($scope.selectedAnswer ==item.parent_question_answer) 
            {  
                document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
                document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
                document.getElementById("question_"+item.id_question+"_green").classList.add('green-circle');
                document.getElementById("toggle-"+ind).classList.remove('blue-color','red-color');
                document.getElementById("toggle-"+ind).classList.add('green-color');
            }
            if($scope.selectedAnswer =='' || $scope.selectedAnswer ==null)
            {
                document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
                document.getElementById("question_"+item.id_question+"_blue").classList.add('blue-circle');
                document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
                document.getElementById("toggle-"+ind).classList.remove('green-color','red-color');
                document.getElementById("toggle-"+ind).classList.add('blue-color');
            }
        }
        else if(item.question_type =='input'){
            if(angular.lowercase($scope.selectedAnswer) !=angular.lowercase(item.parent_question_answer))
            {  

                document.getElementById("question_"+item.id_question+"_red").classList.add('red-circle');
                document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
                document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
                document.getElementById("toggle-"+ind).classList.remove('blue-color','green-color');
                document.getElementById("toggle-"+ind).classList.add('red-color');
                
            }
            if(angular.lowercase($scope.selectedAnswer) ==angular.lowercase(item.parent_question_answer))
             {   
            
                document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
                document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
                document.getElementById("question_"+item.id_question+"_green").classList.add('green-circle');
                document.getElementById("toggle-"+ind).classList.remove('blue-color','red-color');
                document.getElementById("toggle-"+ind).classList.add('green-color');
                
            }
            if($scope.selectedAnswer ==null || $scope.selectedAnswer=='')
            {
                document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
                document.getElementById("question_"+item.id_question+"_blue").classList.add('blue-circle');
                document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
                document.getElementById("toggle-"+ind).classList.remove('green-color','red-color');
                document.getElementById("toggle-"+ind).classList.add('blue-color');
               
            }
        }
        else{
            if($scope.selectedAnswer !=item.parent_question_answer) 
            {  

                document.getElementById("question_"+item.id_question+"_red").classList.add('red-circle');
                document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
                document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
                document.getElementById("toggle-"+ind).classList.remove('blue-color','green-color');
                document.getElementById("toggle-"+ind).classList.add('red-color');
                
            }
            if($scope.selectedAnswer ==item.parent_question_answer)
             {   
            
                document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
                document.getElementById("question_"+item.id_question+"_blue").classList.remove('blue-circle');
                document.getElementById("question_"+item.id_question+"_green").classList.add('green-circle');
                document.getElementById("toggle-"+ind).classList.remove('blue-color','red-color');
                document.getElementById("toggle-"+ind).classList.add('green-color');
                
            }
            if($scope.selectedAnswer ==null || $scope.selectedAnswer=='')
            {
                document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
                document.getElementById("question_"+item.id_question+"_blue").classList.add('blue-circle');
                document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
                document.getElementById("toggle-"+ind).classList.remove('green-color','red-color');
                document.getElementById("toggle-"+ind).classList.add('blue-color');
               
            }
           
        }
    }    
   
    $scope.goToRed = function(item,ind,res){
        $scope.optionsData.answers[item.id_question] = '';
        document.getElementById("toggle-"+ind).classList.remove('green-color','red-color');
        document.getElementById("toggle-"+ind).classList.add('blue-color');
        document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
        document.getElementById("question_"+item.id_question+"_blue").classList.add('blue-circle');
        document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
    }
})
.controller('projectLogCtrl', function($state, $scope, $rootScope, $stateParams,$filter, decode, contractService,userService,AuthService,projectService){
    $rootScope.module = 'Project Logs';
    $rootScope.displayName = $stateParams.name;
    $rootScope.icon = "Projects";
    $rootScope.class ="project-logo"; 
    $rootScope.breadcrumbcolor='project-breadcrumb-color';
    $scope.displayCount = $rootScope.userPagination;
    projectService.getprojectLogs({'project_id':decode($stateParams.id)}).then (function(result){
        if(result.status){
            $scope.currentContract = result.data.current_project_details;
            $scope.contractLogOptions = result.data.project_log_options;
            console.log("as",$scope.contractLogOptions);
        }
    });
    $scope.getContractLogs = function(logId) {
        var param = {};
        param.project_log_id = logId;
        param.project_id = decode($stateParams.id);
        projectService.getprojectLogs(param).then (function(result){
            if(result.status){
                $scope.currentContract = result.data.current_project_details;
                $scope.contractLogOptions = result.data.project_log_options;
                $scope.contractLogDetails = result.data.project_log_details;
            }
        });
    }
    $scope.FileList= [];
    $scope.getFileList = function(){
        $scope.isLoading = true;
        var params = {};
            params.id_contract = decode($stateParams.id);
            params.id_user  = $scope.user1.id_user;
            params.user_role_id  = $scope.user1.user_role_id;
            params.deleted = 0;
            params.updated_by = 1;
        contractService.getContractById(params).then(function(result){
            if(result.data.length > 0){
                if(result.data[0]['attachment']){
                    $scope.FileList = result.data[0].attachment;
                }
            }
            $scope.isLoading = false;
        });
    }
    $scope.getFileList();    
    $scope.getContractDocLogs = function(tableState) {
        $scope.contractFilesLogs={};
        $scope.tableStateRef = tableState;
        $scope.isLoading = true;
        var pagination = tableState.pagination;
        tableState.id_user  = $rootScope.id_user;
        tableState.user_role_id  = $rootScope.user_role_id;
        tableState.reference_id = decode($stateParams.id);
        tableState.reference_type =  'Project';
        tableState.updated_by = 0;
        contractService.getContractDocLogs(tableState).then (function(result){
            if(result.status){
                $scope.contractFilesLogs = result.data.data;
                $scope.emptyTable=false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if(result.data.total_records < 1)
                    $scope.emptyTable=true;
            }
        });
    }
    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getContractDocLogs($scope.tableStateRef);
            }                
        });
    }
})
.controller('projectDashboardCtrl', function($scope, $rootScope, $state,$filter, $stateParams,$timeout, contractService, decode, encode, $uibModal,userService,AuthService,projectService){
    $scope.dashboardData = {};
    $rootScope.module = 'Project Dashboard';
    $rootScope.icon = "Projects";
    $rootScope.class ="project-logo"; 
    $rootScope.breadcrumbcolor='project-breadcrumb-color';
    $rootScope.displayName = $stateParams.name;
    var parentPage = $state.current.url.split("/")[1];
    // $scope.isWorkflow='0';
    $scope.isWorkflow=($stateParams.type=='workflow')?'1':'0';
    var params={};
    params.project_id = decode($stateParams.id);
    params.contract_review_id = decode($stateParams.rId);
    if($stateParams.wId)params.contract_workflow_id = decode($stateParams.wId);
    params.is_workflow = $scope.isWorkflow;
    params.is_workflow = '1';
    params.id_user  = $scope.user1.id_user;
    params.user_role_id  = $scope.user1.user_role_id;
    $scope.showData={};
    
    $scope.getDashboardData = function(params){
        projectService.getProjectDashboard(params).then(function(result){
            if(result.status){
                if(result.special_message){
                    $rootScope.toast('customError',result.message);
                }
                $scope.dashboardData =  result.data;
                //console.log($scope.dashboardData.subtask_lists);
            }               
           // $scope.showData = $scope.dashboardData.modules;
        })
    }
    $scope.getDashboardData(params);
    $scope.goToDetails1 = function(){
        $state.go('app.projects.view',{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,rId:$stateParams.rId,type:'workflow'});
    }


    $scope.getDataByReviewDate = function(row){
        console.log("67",row)
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/archive/review-date-data-modal.html',
            controller: function ($uibModalInstance, $scope, item) {
                console.log('item info',item);
                $scope.title="";
                $scope.name= item.templateName;
                $scope.subtask=item.is_subtask;
                if(item.is_subtask !='0'){  $scope.provider_name = item.provider_name;}
               
                $scope.isWorkflow=false;
                $scope.moduleTopics = [];
                $scope.title = 'workflows.workflow';
                $scope.isWorkflow=true;
                  
                var params ={};               
                params.module_id = item.module_id;
                params.contract_review_id = item.contract_review_id ;
                params.all_questions = true;
                contractService.getUnAnswered(params).then(function (result) {
                    if(result.status){
                        $scope.moduleTopics = result.data;
                        $scope.side_by_side=result.side_by_side_validation;
                        $scope.projectType=result.type;
                        $scope.submittedBy = result.submitted.submitted_by;
                        $scope.submittedOn = result.submitted.submetted_on;
                    }else $rootScope.toast('Error', result.error, 'error',$scope.user);
                });
                     
                $scope.cancel = function () {
                    $uibModalInstance.close();
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
    }
    $scope.init = function (tableState){
        $scope.contract_id = decode($stateParams.id);
        var params = {};
        params.id_contract  = $scope.contract_id;
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.is_workflow  = $scope.isWorkflow;
        params.id_contract_workflow = decode($stateParams.wId);
        contractService.getContractById(params).then (function(result){
            $scope.contractInfo = $scope.data = result.data[0];
            $scope.workflowInfo=result.review_workflow_data.acitvity_name;
        })
    }
    $scope.init();
    $scope.goToNext = function(data,isNext){
        var params={};
        params.contract_id = decode($stateParams.id);
        if(isNext)
            params.contract_review_id = data.next;
        else params.contract_review_id = data.prev;
        params.is_workflow = $scope.isWorkflow;
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        if($scope.isWorkflow=='1'){
            params.contract_workflow_id = data.contract_workflow_id;
        }
        $scope.getDashboardData(params);
        $timeout(function () {
            $scope.init();
        },100);
    }    
    //parvathi code starts
    $scope.previewFeedback=function(row) { 
        $scope.selectedRow =row.question_feedback;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'view-feedback.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.cancel = function () {
                    $uibModalInstance.close();
                   
                };
            },
        });    
    }

    $scope.previewValidatorFeedback=function(row) { 
        $scope.selectedRow =row.v_question_feedback;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'view-validator-feedback.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.cancel = function () {
                    $uibModalInstance.close();
                   
                };
            },
        });    
    }

    $scope.previewExternalFeedback=function(row) { 
        $scope.selectedRow =row.external_user_question_feedback;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'view-external-feedback.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.cancel = function () {
                    $uibModalInstance.close();
                   
                };
            },
        });    
    }
    $scope.previewAttachments =function (data){
        $scope.selectedRow =angular.copy(data);
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'view-question-attachments.html',
            controller: function ($uibModalInstance, $scope,item) {
                $scope.attachments=item.attachments;
                $scope.question_text=item.question_text;
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
    

    $scope.showdiscussion =function(row,flag){
        $scope.selectedRow =row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'discussion-view.html',
            controller: function ($uibModalInstance, $scope,item) {
                $scope.discussDetails =item.discussion.log;
                $scope.question=item;
                if(flag)$scope.isWorkflow=true;
                else $scope.isWorkflow=false;
                if($scope.question.question_type=='date'){
                    $scope.question.question_answer = moment($scope.question.question_answer).utcOffset(0, false).toDate();
                    $scope.question.second_opinion = moment($scope.question.second_opinion).utcOffset(0, false).toDate();
                }
                $scope.question_text=item.question_text;
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
    //parvathi code ends

    $scope.showTopicQuestions = function(data,topic) {
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'modal-open questions-modal',
            templateUrl: 'view-topic-questions.html',
            size: 'lg',
            controller: function ($uibModalInstance, $scope) {
                $scope.load = true;
                $scope.title = topic.topic_name;
                var params= {};
                params.contract_review_id = $scope.dashboardData.contract_review_id;
                params.module_id = data.module_id;
                params.contract_id = decode($stateParams.id);
                params.id_topic = topic.topic_id;
                params.is_workflow = 1;
                    contractService.getTopicQuestionsById(params).then(function(result){
                        $scope.contractModuleTopics = result.data.questions;
                        $scope.load = false;
                    });
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };

            }
        });
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    }

    $scope.goToChangeLog = function(){
       $state.go('app.projects.task-change-log', {'name': $stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});             
    }
    $scope.exportReview = function (){
        var params={};
        params.contract_id = params.id_contract= decode($stateParams.id);
        params.id_user=  $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.contract_review_id = $scope.dashboardData.contract_review_id;
        params.is_workflow = $scope.isWorkflow;
        params.contract_workflow_id = $scope.dashboardData.contract_workflow_id;
        contractService.exportReviewData(params).then(function(result){            
            if(result.status){
                var obj = {};
                obj.action_name = 'export';
                obj.action_description = 'export$$contract$$review$$('+$stateParams.name+')';
                obj.module_type = $state.current.activeLink;
                obj.action_url = location.href;
                if(AuthService.getFields().data.parent){
                    obj.user_id = AuthService.getFields().data.parent.id_user;
                    obj.acting_user_id = AuthService.getFields().data.data.id_user;
                }
                else obj.user_id = AuthService.getFields().data.data.id_user;
                if(AuthService.getFields().access_token != undefined){
                    var s = AuthService.getFields().access_token.split(' ');
                    obj.access_token = s[1];
                }
                else obj.access_token = '';
                $rootScope.toast('Success',result.message);
                userService.accessEntry(obj).then(function(result1){
                    if(result1.status){
                        if(DATA_ENCRYPT){
                            result.data.file_path =  GibberishAES.enc(result.data.file_path, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                            result.data.file_name =  GibberishAES.enc(result.data.file_name, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                        }
                        window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+$rootScope.id_user+'&access_token='+obj.access_token;
                    }
                });
            }
            else $rootScope.toast('Error',result.error,'error');
        })
    }
    $scope.exportDashboardData = function (){
        var params={};
        params.contract_id = decode($stateParams.id);
        params.contract_review_id = $scope.dashboardData.data.contract_review_id;
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.contract_workflow_id = $scope.dashboardData.data.contract_workflow_id;
        params.is_workflow = $scope.isWorkflow;
        projectService.exportProjectDashboardData(params).then(function(result){            
            if(result.status){
                var obj = {};
                obj.action_name = 'export';
                obj.action_description = 'export$$project$$review$$('+$stateParams.name+')';
                obj.module_type = $state.current.activeLink;
                obj.action_url = location.href;
                if(AuthService.getFields().data.parent){
                    obj.user_id = AuthService.getFields().data.parent.id_user;
                    obj.acting_user_id = AuthService.getFields().data.data.id_user;
                }
                else obj.user_id = AuthService.getFields().data.data.id_user;
                if(AuthService.getFields().access_token != undefined){
                    var s = AuthService.getFields().access_token.split(' ');
                    obj.access_token = s[1];
                }
                else obj.access_token = '';
                $rootScope.toast('Success',result.message);
                userService.accessEntry(obj).then(function(result1){
                    if(result1.status){
                        if(DATA_ENCRYPT){
                            result.data.file_path =  GibberishAES.enc(result.data.file_path, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                            result.data.file_name =  GibberishAES.enc(result.data.file_name, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                        }
                        //window.location = API_URL+'download/downloadreport?path='+result.data.file_path+'&name='+result.data.file_name;
                        window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+$rootScope.id_user+'&access_token='+obj.access_token;
                    }
                });
            }
            else $rootScope.toast('Error',result.error,'error');
        })
    }
    $scope.downloadAttachment = function(objData){
        var d = {};
        d.id_document = objData.id_document;
        contractService.getUrl(d).then(function (result) {
            if(result.status){
                var obj = {};
                obj.action_name = 'download';
                obj.action_description = 'download$$contract$$review$$question$$attachment$$('+objData.document_name+')';
                obj.module_type = $state.current.activeLink;
                obj.action_url = location.href;
                if(AuthService.getFields().data.parent){
                    obj.user_id = AuthService.getFields().data.parent.id_user;
                    obj.acting_user_id = AuthService.getFields().data.data.id_user;
                }
                else obj.user_id = AuthService.getFields().data.data.id_user;
                if(AuthService.getFields().access_token != undefined){
                    var s = AuthService.getFields().access_token.split(' ');
                    obj.access_token = s[1];
                }
                else obj.access_token = '';
                $rootScope.toast('Success',result.message);
                userService.accessEntry(obj).then(function(result1){
                    if(result1.status){
                        if(DATA_ENCRYPT){
                            result.data.url =  GibberishAES.enc(result.data.url, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                            result.data.file =  GibberishAES.enc(result.data.file, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                        }
                        //window.location = API_URL+'download/downloadreport?path='+result.data.url+'&name='+result.data.file;
                        window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+$rootScope.id_user+'&access_token='+obj.access_token;
                    }
                });
            }else{
                $rootScope.toast('Error',result.error);
            }
        });
    };
    $scope.isNaN= function (n) {
        return isNaN(n);
    }

    $scope.goToTrends = function(){
        var goToTrends = (parentPage == 'all-activities')?'app.contract.review-trends':'app.contract.review-trends1';
        $state.go(goToTrends, {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.contractInfo.id_contract_review),type:'review'});
    }
})
.controller('projectReviewDesign', function($timeout,$scope, $rootScope,$state,$filter, $stateParams, encode, decode, contractService, $window, $location,anchorSmoothScroll){
    $rootScope.module = 'Project Task Discussion';
    $rootScope.displayName = $stateParams.name;
    $rootScope.icon = "Projects";
    $rootScope.class ="project-logo"; 
    $rootScope.breadcrumbcolor='project-breadcrumb-color';
    $scope.curReviewId = decode($stateParams.rId);
    $scope.isWorkflow=($stateParams.type=='workflow')?'1':'0';
    var parentPage = $state.current.url.split("/")[1];
    console.log(parentPage);
    $scope.init = function (tableState){
        $scope.loading = false;
        $scope.contract_id = decode($stateParams.id);
        var params = {};
        params.id_contract  = $scope.contract_id;
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.is_workflow = $scope.isWorkflow;
        params.id_contract_workflow = decode($stateParams.wId);
        contractService.getContractById(params).then (function(result){
            $scope.contractInfo = $scope.data = result.data[0];
            $scope.loading = true;
        })
    }
    $scope.init();

    $scope.gotoElement = function (eID) {
        var element = document.getElementById(eID);
        element.classList.add("discussion-row");
        $location.hash(eID);
        // $anchorScroll();
        anchorSmoothScroll.scrollTo(eID);
        setTimeout(function() {
            element.classList.remove("discussion-row");
        }, 3000);
    };

    $scope.getDiscussionQuestions = function (open_module){
        var params ={};
        params.contract_id = params.id_contract = decode($stateParams.id);
        params.id_user = $scope.user1.id_user;
        params.user_role_id = $scope.user1.user_role_id;
        params.is_workflow = $scope.isWorkflow;
        params.id_contract_workflow = decode($stateParams.wId);
        params.type='project';
        contractService.discussDetails(params).then(function(result){
            $scope.loading = false;
            if(result.status){
                $scope.discussionData = result.data;
                $scope.curReviewId = result.data.contract_review_id;
                $scope.loading = true;
                angular.forEach($scope.discussionData.review_discussion, function(item,key){
                    $scope.discussionData.review_discussion[key].open = false;
                    if(open_module){
                        if($scope.discussionData.review_discussion[key].id_module == open_module){
                            $scope.discussionData.review_discussion[key].open = true;
                        }
                   }else $scope.discussionData.review_discussion[key].open = ($stateParams.qId)?true:false;

                   angular.forEach(item.topics, function(topic,k){
                        angular.forEach(topic.questions, function(question,o){
                            if(question.question_type =='date'){
                                question.question_answer= moment(question.question_answer).utcOffset(0, false).toDate();
                                question.second_opinion= moment(question.second_opinion).utcOffset(0, false).toDate();
                            }
                        });
                   });
                });
                if($stateParams.qId){
                    $timeout(function () {
                        $scope.gotoElement(decode($stateParams.qId));
                    },200);
                }
            }
        });
    }
    $scope.getDiscussionQuestions();
    $scope.saveQuestionComments = function(questionData,type){
        var params ={};
        params.review_discussion = [];
        params.contract_id = params.id_contract = decode($stateParams.id);
        params.contract_review_id =  $scope.discussionData.contract_review_id;
        params.created_by = $scope.user.id_user;
        params.user_role_id = $scope.user.user_role_id;
        angular.forEach(questionData, function(item,key){
            if(key == 'topics'){
                angular.forEach(item,function(it,k){
                    angular.forEach(it.questions, function(row,val){
                        if(row.id_contract_review_discussion_question){
                            if(row.remarks && row.status == 1){
                                params.review_discussion.push(row);
                            }
                            if(row.status == 0){
                                params.review_discussion.push(row);
                            }
                        }else{
                            if(row.status == 1){
                                if(row.remarks){
                                    params.review_discussion.push(row);
                                }
                            }
                        }
                    });
                });
            }
        });
        if(params.review_discussion.length >0){
            contractService.postdiscussion(params).then(function(result){
                if(result.status){
                    setTimeout(function(){
                        $scope.getDiscussionQuestions(result.data.recent_module_id);
                    },500);
                    $rootScope.toast('Success', result.message);
                    if(type == 'init') $scope.action = 'Initiate';
                    else $scope.action= 'Save';
                    var obj = {};
                    obj.action_name = $scope.action;
                    obj.action_description = $scope.action+'$$Discussion$$('+questionData.module_name+'-'+$stateParams.name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);

                }
                else $rootScope.toast('Error', result.error,'error');
            });
        }else{$rootScope.toast('Warning','Please enter comments for at least one question', 'warning');}
    }
    $scope.closeDiscussion = function (data,$event){
        var params1 = {};
        params1.contract_review_discussion_id = data.id_contract_review_discussion;
        params1.contract_review_id =  $scope.discussionData.contract_review_id;
        params1.created_by = $scope.user.id_user;
        params1.module_id = data.id_module;
        params1.contract_id = params1.id_contract= decode($stateParams.id);
        params1.type='proejct';
        var r=confirm($filter('translate')('general.alert_discussion_module'));
        $scope.deleConfirm = r;
        if(r==true){
            contractService.closediscussion(params1).then(function(result){
                if(result.status){
                    $scope.getDiscussionQuestions();
                    $rootScope.toast('Success',result.message);
                    var obj = {};
                    obj.action_name = 'close';
                    obj.action_description = 'close$Discussion$$('+data.module_name+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    //$state.go('app.contract.contract-review',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId});
                }else $rootScope.toast('Error',result.error,'error');
            });
        }
    }
    $scope.getClosedDiscussions = function(closedId){
        var params ={};
        params.contract_id = params.id_contract = decode($stateParams.id);
        //params.contract_review_id =  decode($stateParams.rId);
        params.id_user = $scope.user1.id_user;
        params.user_role_id = $scope.user1.user_role_id;
        params.type='project';
        if(closedId) params.id_contract_review_discussion = closedId;
        contractService.discussDetails(params).then(function(result){
            $scope.loading = false;
            if(result.status){
                $scope.discussionData = result.data;
                $scope.curReviewId = result.data.contract_review_id;
                $scope.loading = true;
                angular.forEach($scope.discussionData.review_discussion, function(item,key){
                    $scope.discussionData.review_discussion[key].open = false;
                });
            }
        });
        //$scope.getDiscussionQuestions();
    }
    $scope.reloadDiscussions = function (){
        $window.location.reload();
    }
    $scope.goFromDiscussion = function (questionId,topic,module) {
        if($scope.isWorkflow=='1' && $rootScope.access !='eu'){
            $state.go('app.projects.project-module-task',
                {name:$stateParams.name,id:$stateParams.id,rId:encode($scope.curReviewId),mName:module.module_name,
                    moduleId:encode(module.id_module),tName:topic.topic_name,tId:encode(topic.id_topic),
                    qId:encode(questionId),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
        }
        if($scope.isWorkflow=='1' && $rootScope.access =='eu'){
            $state.go('app.projects.project-module-task11',
            {name:$stateParams.name,id:$stateParams.id,rId:encode($scope.curReviewId),mName:module.module_name,
                moduleId:encode(module.id_module),tName:topic.topic_name,tId:encode(topic.id_topic),qId:encode(questionId),type:'review'},{ reload: true, inherit: false });
        }
    };
})
.controller('projectChangeLogCtrl', function($scope, $rootScope, $state, $stateParams,$filter, decode, contractService, userService, AuthService){
    $scope.isWorkflow = '0';
    $scope.displayCount = $rootScope.userPagination;   
    if($stateParams.wId)$scope.workflowId = decode($stateParams.wId);
    if($stateParams.type)$scope.isWorkflow = ($stateParams.type =='workflow')?'1':'0';

    $scope.getFileList = function(tableState){
        $scope.tableStateRef = tableState;
        $scope.isLoading = true;
        var pagination = tableState.pagination;
        if($stateParams.rId)tableState.module_id = decode($stateParams.rId);
        tableState.module_type =  'contract_review';
        tableState.id_user  = $rootScope.id_user;
        tableState.user_role_id  = $rootScope.user_role_id;
        tableState.page_type  = 'contract_overview';
        tableState.contract_id  = decode($stateParams.id);
        tableState.deleted  = 0;
        tableState.updated_by = 1;
        tableState.is_workflow  = $scope.isWorkflow;
        tableState.contract_workflow_id  = decode($stateParams.wId);
        contractService.getFileLogs(tableState).then(function(result){
            $scope.FileList = result.data.result.data;
            $scope.emptyTable=false;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords = result.data.total_records;
            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
            $scope.isLoading = false;
            if(result.data.total_records < 1)
                $scope.emptyTable=true;
        })
    }
    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getFileList($scope.tableStateRef);
            }                
        });
    }
    $rootScope.module = 'Change Log';
    $rootScope.displayName = $stateParams.name;
    $rootScope.icon = "Projects";
    $rootScope.class ="project-logo"; 
    $rootScope.breadcrumbcolor='project-breadcrumb-color';
    $scope.contract_name = $stateParams.name;
    $scope.getTopics = function(id,type){
        var param ={};
        if($stateParams.rId)param.contract_review_id = decode($stateParams.rId);
        param.id_user  = $scope.user1.id_user;
        param.user_role_id  = $scope.user1.user_role_id;
        if(id){
            if($scope.change.module) param.id_module  = $scope.change.module;
            if($scope.change.topic) param.id_topic  = $scope.change.topic;
        }else {
            param.id_module  = 'all';
           if(type == 'module') param.id_module  = 'all';
           if(type == 'topic') param.id_module  = $scope.change.module;
        }
        contractService.getchangeLogs(param).then(function(result){
            $scope.modules = result.data.modules;
            if(param.id_module != undefined)
                $scope.topics = result.data.topics;
            $scope.questionsList = result.data.questions;
            $scope.contractInfo = result.data.review_information;
            $scope.noQuestions = false ;
            if(result.data.questions.length == 0) $scope.noQuestions = true;
        });
    }
    $scope.getTopics(0,'all');
})

.config(function(scrollableTabsetConfigProvider){
    scrollableTabsetConfigProvider.setShowTooltips (true);
    scrollableTabsetConfigProvider.setTooltipLeftPlacement('bottom');
    scrollableTabsetConfigProvider.setTooltipRightPlacement('left');
})
