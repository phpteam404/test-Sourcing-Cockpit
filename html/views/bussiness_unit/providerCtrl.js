angular.module('app',['ng-fusioncharts','localytics.directives'])
    .controller('providerCtrl', function ($state, $rootScope, $scope,$localStorage, $translate,$uibModal, encode, decode, businessUnitService, providerService, masterService) {
        $scope.showAddBtn = 0;
        if ($rootScope.access == 'wa' || $rootScope.access == 'ca') {
            $scope.showAddBtn = 1;
        }

        if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }

        $scope.displayCount = $rootScope.userPagination;
        tableState.customer_id = $scope.user1.customer_id;
        providerService.list(tableState).then(function (result) {
            $scope.providers = result.data.data;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords = result.data.total_count;
            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_count / $rootScope.userPagination);
            $scope.isLoading = false;
        });
    })


    .controller('providerCtrl', function($scope, $rootScope, $translate,$state, $stateParams, $localStorage, dateFilter, $timeout,$uibModal,providerService, contractService, masterService, encode, catalogueService,AuthService,userService,$sce,projectService,moduleService){
         $scope.del=0;
        //  $scope.can_access=1;
         $scope.searchFields = {};
         $scope.displayCount = $rootScope.userPagination;
         $scope.resetPagination=false;
         if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
             $scope.del=1;
         }

         if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }



         $scope.advancedFilterRelation = function(){
            $scope.filterCreate = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/contracts/advancedFilterContract.html',
                controller: function ($uibModalInstance,$scope,item) {
                    $scope.bottom ='general.save';
    
    
                    $scope.filterListRelations=function(){
                    var params ={};
                    // params.user_id=$scope.user.id_user;
                    params.module='all_relations_list';
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
                    $scope.filterListRelations();
    
    
                $scope.flterDelete=function(rowdata){
                    var r = confirm("Are you sure that you want to delete the filter ?");
                    if(r==true){
                        contractService.deleteContractFlter({'id_master_filter':rowdata.id_master_filter}).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success', result.message);
                                $scope.filterListRelations();
                                $scope.tableStateRef.pagination.start =0;
                                $scope.tableStateRef.pagination.totalItemCount =10;
                                $scope.tableStateRef.pagination.number =10;
                                $scope.filterRelationList();
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
                templateUrl: 'views/bussiness_unit/create-provider-filter.html', 
                controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';
                 $scope.title='controller.add_filter_criteria'
    
                    contractService.getContractDomain({'domain_module': 'all_relations_list'}).then(function(result){
                        $scope.contractFilter = result.data;                   
                        });
                        

                     
                        $scope.getContractDomainFieldList=function(id){
                            //console.log('id',id);
                        $scope.idDomain=id; 
                        $scope.filterCreate.master_domain_field_id='';
                        var domainId= $scope.contractFilter.filter(item => { return item.id_master_domain == id; });
                        $scope.domainType= domainId[0].domain;
                        console.log("asd",$scope.domainType)
                        contractService.getContractField({'id_master_domain':id}).then(function(result){
                            console.log('res',result);
                            $scope.contractField = result.data;
                        });
                    }
                      
                  
                    $scope.getContractCondition=function(domainFieldId){
                        //console.log('val',domainFieldId);
                        $scope.filterCreate.value='';
                        var domainFieldData = $scope.contractField.filter(item => { return item.id_master_domain_fields == domainFieldId; });
                        //console.log(domainFieldData);
                        if(domainFieldId!=undefined)$scope.fieldType=domainFieldData[0].field_type;
                        if(domainFieldId!=undefined)$scope.feldName=domainFieldData[0].field_name;
                        if(domainFieldId!=undefined)$scope.fieldSelect=domainFieldData[0].selected_field;
                        console.log("asd",$scope.fieldType)
                        console.log("asd",$scope.fieldSelect)


    
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
    
                        if($scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' ||$scope.domainType=='Relation Fixed Tags')){
                            contractService.getContractTagsDrpdown({'id_tag': domainFieldId}).then(function(result){
                                $scope.tagsDropdownList = result.data;
                            });
                         }

                         if($scope.fieldSelect=='relation' && $scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' || $scope.domainType=='Relation Fixed Tags')){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
                                $scope.selectedInfoProvider = result.data;
                                });                    
                        }
                        if($scope.fieldSelect=='project' && $scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' || $scope.domainType=='Relation Fixed Tags')){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'project'}).then (function(result){
                                $scope.selectedInfoProject = result.data;
                            });
                        }
                        if($scope.fieldSelect=='contract' && $scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' || $scope.domainType=='Relation Fixed Tags')){
                            console.log("contract")
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                                $scope.selectedInfoContract = result.data;
                            });
                        }
                        if($scope.fieldSelect=='catalogue' && $scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' || $scope.domainType=='Relation Fixed Tags')){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'catalogue'}).then (function(result){
                                $scope.selectedInfoCatalogue = result.data;
                            });
                        }
        

                        
        
                        if($scope.feldName=='Country')
                            masterService.getCountiresList().then(function (result) {
                                if (result.status) {
                                        $scope.countriesList = result.data;
                                    }
                        });
    
                        if($scope.feldName=='Category'){
                            providerService.getProviderRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                                $scope.relationshipCategoryList = result.drop_down;
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
                        params.module='all_relations_list';
                        params.id_master_filter=row.id_master_filter;
                        console.log("id enter",params.id_master_filter);
                        contractService.getContractList(params).then(function(result){
                            $scope.filterCreate=result.data[0];
                            $scope.fieldType=result.data[0].field_type;
                            $scope.feldName=result.data[0].field;
                            $scope.domainType= result.data[0].domain;
                            $scope.tagType=result.data[0].tag_type;
                            $scope.fieldSelect=result.data[0].selected_field;    
                            if($scope.filterCreate.field_type=='numeric_text' || $scope.filterCreate.field_type=='free_text' || $scope.filterCreate.field_type=='drop_down' ||  $scope.filterCreate.field_type=='date'){  
                                contractService.getContractField({'id_master_domain': row.master_domain_id}).then(function(result){
                                        $scope.contractField = result.data;
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
    
                        
    
                        if($scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' ||$scope.domainType=='Relation Fixed Tags')){
                            contractService.getContractTagsDrpdown({'id_tag': row.master_domain_field_id}).then(function(result){
                                $scope.tagsDropdownList = result.data;
                            });
                        }


                        if($scope.fieldSelect=='relation' && $scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' ||$scope.domainType=='Relation Fixed Tags')){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
                                $scope.selectedInfoProvider = result.data;
                                });                    
                        }
                        if($scope.fieldSelect=='project' && $scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' ||$scope.domainType=='Relation Fixed Tags')){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'project'}).then (function(result){
                                $scope.selectedInfoProject = result.data;
                            });
                        }
                        if($scope.fieldSelect=='contract' && $scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' ||$scope.domainType=='Relation Fixed Tags')){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                                $scope.selectedInfoContract = result.data;
                            });
                        }
                        if($scope.fieldSelect=='catalogue' && $scope.fieldType=='drop_down' && ($scope.domainType=='Relation Tags' ||$scope.domainType=='Relation Fixed Tags')){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'catalogue'}).then (function(result){
                                $scope.selectedInfoCatalogue = result.data;
                            });
                        }



                        if($scope.feldName=='Responsible User'){
                            projectService.eventResponsibleUsers().then (function(result){
                                $scope.eventResponsibleUsers=result.data;
                            });
                        }

                        if($scope.feldName=='Country')
                         masterService.getCountiresList().then(function (result) {
                             if (result.status) {
                                    $scope.countriesList = result.data;
                                }
                            });

    
                        if($scope.feldName=='Category'){
                            providerService.getProviderRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                                $scope.relationshipCategoryList = result.drop_down;
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
                                    $scope.tableStateRef.pagination.start =0;
                                    $scope.tableStateRef.pagination.totalItemCount =10;
                                    $scope.tableStateRef.pagination.number =10;
                                    $scope.filterRelationList();
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
                                $scope.filterRelationList();
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
            var r = confirm("Are you sure that you want to delete the filter ?");
            if(r==true){
                contractService.deleteContractFlter({'id_master_filter':rowdata.id_master_filter}).then(function(result){
                    if(result.status){
                        $scope.filterRelationList();
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
        
            $scope.filterRelationList=function(){
            var params ={};
            // params.user_id = $scope.user1.id_user;
            params.module = 'all_relations_list';
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
            $scope.filterRelationList();
        
        //  masterService.getCountiresList().then(function (result) {
        //     if (result.status) {
        //         $scope.countriesList = result.data;
        //     }
        // });
        //  $scope.getCategoryList = function(){
        //     providerService.getProviderRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
        //         $scope.relationshipCategoryList = result.drop_down;
        //      });
        //  }
        
        //  $scope.getCategoryList();
         $scope.goToProviderDetails = function(row){        
            $state.go('app.provider.view',{name:row.provider_name,id:encode(row.id_provider),type:'review'});
        } 

        $scope.createProvider = function(){
            $state.go('app.provider.prvcreate');
        } 
         $scope.displayChart = function () {
            $scope.contractOverallDetails();
             angular.element('#chart').removeClass('hide');
             angular.element('#chart').removeAttr('ng-hide');
             $scope.showChart = true;
             return true;
         }
         $scope.hideChart = function (flag) {
             console.log(flag);
             //if(flag) $rootScope.chatLables = $scope.myDataSource.chart;
             $timeout(function () {
                 angular.element('#chart').addClass('hide');
             });
             $scope.showChart = false;
         }

         
        
         $scope.providersList = [];
         $scope.myDataSource = {};
         $scope.itemsByPage = 10;
         $scope.callServer = function (tableState){
             $scope.filtersData = {};
             $rootScope.module = '';
             $rootScope.displayName = '';  
             $rootScope.breadcrumbcolor='provider-breadcrumb-color';
             $rootScope.class='provider-content';
             $rootScope.icon='Relations';      
             $scope.isLoading = true;
             var pagination = tableState.pagination;
             tableState.customer_id = $scope.user1.customer_id;
             tableState.can_access  = $scope.can_access;
             tableState.is_advance_filter=1;
             

            //  if($stateParams.approval_status==undefined &&
            //     $stateParams.risk_profile==undefined){}
                //else{
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
                    if($scope.can_access && $scope.can_access !=null){
                        tableState.can_access  = angular.copy($scope.can_access);
                    }
                     else{
                        delete tableState.can_access;
                        $scope.can_access='';
                    }

                    if($stateParams.approval_status) {
                        tableState.approval_status  =$stateParams.approval_status;
                        $scope.approval_status = $stateParams.approval_status;
                    }
                    if($stateParams.risk_profile) {
                        tableState.risk_profile  =$stateParams.risk_profile;
                        $scope.risk_profile = $stateParams.risk_profile;
                    }
                //}
                
                 tableState.overview=true;
                 if($scope.resetPagination){
                     tableState.pagination={};
                     tableState.pagination.start='0';
                     tableState.pagination.number='10';
                 }
                
                 $scope.tableStateRef = tableState;
                 providerService.list(tableState).then (function(result){
                     //console.log(result.data.total_records);
                     $scope.providersList =[];
                     $scope.providersList = result.data.data;
                     $scope.labelNames=result.data.labels;

                     $scope.contractOverallDetails = function(){
                        var data=tableState;
                        var params = {};
                        params.customer_id = $scope.user1.customer_id;
                        params.id_user =  $scope.user1.id_user;
                        params.user_role_id = $scope.user1.user_role_id;
                        params.pagination = $scope.tableStateRef.pagination;
                        params.search = $scope.tableStateRef.search;
                        params.sort = $scope.tableStateRef.sort;
                        params.chart_type='allproviders';
                        if(!angular.isUndefined(data.country_id)) params.country_id = data.country_id;
                        if(!angular.isUndefined(data.relationship_category_id)) params.relationship_category_id = data.relationship_category_id;
                        if(!angular.isUndefined(data.risk_profile)) params.risk_profile = data.risk_profile;
                        if(!angular.isUndefined(data.approval_status)) params.approval_status = data.approval_status;
                        if(!angular.isUndefined(data.can_access)) params.can_access = data.can_access;
                        params.sort = data.sort;
                        providerService.providerOverallDetails(params).then(function(result){
                            $scope.myDataSource = result.data;
                        });
                    };

                    //  $scope.contractOverallDetails(tableState,'allproviders');
                     $scope.emptyTable=false;
                     $scope.displayCount = $rootScope.userPagination;
                     $scope.totalRecords = result.data.total_records;
                     tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                     $scope.isLoading = false;
                     $scope.resetPagination=false;
                     $scope.memorizeFilters(tableState);
                     if(result.data.total_records > 0) 
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

        $scope.exportProvidersList = function(){
            var params = {};
            // params.customer_id = $scope.user1.customer_id;
            // params.user_role_id = $scope.user1.user_role_id;
            // params.id_user = $scope.user1.id_user;
            params.export_type = 'All_Providers';
            providerService.exportProviders(params).then(function (result) {
                if(result.status){
                    var obj = {};
                    obj.action_name = 'export';
                    obj.action_description = 'export$$providers list$$('+result.data.file_name+')';
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
            });
        }
     
         $scope.memorizeFilters = function(data){
             $localStorage.curUser.data.filters.allProviders = data;
         }
        
     })
    .controller('providerCreateCtrl', function ($state, $rootScope, $scope, $uibModal, $localStorage,Upload, catalogueService,contractService,providerService,tagService, masterService, $location,dateFilter) {
        $scope.title = 'general.create';
        $scope.disabled = false;
        $scope.file={};
        $scope.links_delete = [];
        $scope.bottom = 'general.save';
        $scope.isEdit = false;
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon=''; 
        masterService.getCountiresList().then(function (result) {
            if (result.status) {
                $scope.countriesList = result.data;
            }
        });

        $scope.cancel = function () {
            $state.go('app.provider.all-providers');
        };

        providerService.getProviderRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
            $scope.relationshipCategoryList = result.drop_down;
        });

        // tagService.list({'status':1,'tag_type':'provider_tags'}).then(function(result){
        //     if (result.status) {
        //         $scope.tags = result.data;
        //     }
        // });

        tagService.groupedTags({'status':1,'tag_type':'provider_tags'}).then(function(result){
            if (result.status) {
                $scope.tagsBuilding = result.data;
            }
        });

        catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
            $scope.selectedInfoContract = result.data;
        });
    
        catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'project'}).then (function(result){
            $scope.selectedInfoProject = result.data;
        });
        catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
        $scope.selectedInfoProvider = result.data;
        });
        catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'catalogue'}).then (function(result){
            $scope.selectedInfoCatalogue = result.data;
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
            var r=confirm("Do you want to continue?");
            if(r==true){
                $scope.providerLinks.splice(index, 1);
            }                    
        }

        $scope.deleteFile = function(index,row){
            var r=confirm("Do you want to continue?");
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
            //console.log('data',data);
            $scope.formDataObj= angular.copy(data);
            $scope.userData = $localStorage.curUser.data.data;
            var provider={};
            provider= $scope.formDataObj;
            $scope.options = {};
            $scope.grouped_tags = {};
            if(provider.grouped_tags){
                angular.forEach($scope.tagsBuilding, function(k,ko){
                    $scope.options = {};
                angular.forEach(k.tag_details, function(i,o){
                    //console.log('i info',i);
                    $scope.options[o] = {};
                    $scope.options[o].tag_id = i.tag_id;
                    $scope.options[o].tag_type =i.tag_type; 
                    $scope.options[o].multi_select = i.multi_select;  
                    $scope.options[o].selected_field = i.selected_field;           
                    if($scope.provider.grouped_tags.feedback !=undefined)
                        $scope.options[o].comments = $scope.provider.grouped_tags.feedback[i.tag_id];
                    else $scope.options[o].comments = '';
                   
                    if(i.tag_type =='date')
                        $scope.options[o].tag_option = dateFilter(data.grouped_tags[i.tag_id],'yyyy-MM-dd');
                    else if(i.tag_type !='date')
                        $scope.options[o].tag_option = data.grouped_tags[i.tag_id];
                    else $scope.options[o].tag_option = '';        
                });
                $scope.grouped_tags[ko] = {};
                $scope.grouped_tags[ko]['tag_details'] = {};
                $scope.grouped_tags[ko]['tag_details'] = $scope.options;    
                });
            }
            provider.grouped_tags = $scope.grouped_tags;
            provider.created_by =$scope.userData.id_user;
            provider.customer_id = $scope.user1.customer_id;
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
                                     $state.go('app.provider.all-providers');
                                    $rootScope.toast('Success',resp.data.message);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$ provider$$'+provider.provider_name;
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
                else{
                     Upload.upload({
                        url: API_URL+'Customer/addprovider',
                         data: {
                            'file' : $scope.file.attachment,
                             'provider':params
                         }
                     }).then(function(resp){
                     if(resp.data.status){
                        $state.go('app.provider.all-providers');
                        $rootScope.toast('Success',resp.data.message);
                         var obj = {};
                         obj.action_name = 'add';
                         obj.action_description = 'add$$provider$$'+provider.provider_name;
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
        
       
    })
    .controller('providerEditCtrl', function ($state, $rootScope, $scope, $uibModal, $localStorage, providerService, tagService,contractService,masterService, $stateParams, encode, decode, $location) {
        //console.log('providerEditCtrl loaded');
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon=''; 
        if ($stateParams.id) {
            $scope.title = 'general.edit';
            $scope.bottom = 'general.update';
            $scope.isEdit = true;
            $scope.user = $localStorage.curUser.data.data;
            $scope.id = decode($stateParams.id);
            masterService.getCountiresList().then(function (result) {
                if (result.status) {
                    $scope.countriesList = result.data;
                }
            });
            $scope.cancel = function () {
                //$state.go('app.provider.list');
                $state.go('app.provider.all-providers');
            };
            providerService.list({'id_provider': $scope.id ,'customer_id':$scope.user1.customer_id}).then(function (result) {
                if (result.status) {
                    $scope.provider = result.data.data[0];
                }
            });
            contractService.getRelationshipCategory({  'id_provider': $scope.id,'customer_id': $scope.user1.customer_id}).then(function(result){
                $scope.relationshipCategoryList = result.drop_down;
            });
            
            tagService.list({'status':1,'type':'provider_tags'}).then(function(result){
                if (result.status) {
                    $scope.tags = result.data;
                }
            });

            $scope.tagsData = [];
            $scope.tagsData = angular.copy($scope.provider);
            angular.forEach(function(i,o){
                if(i.tag_type=='date'){
                    i.tag_answer = dateFilter(i.tag_answer,'yyyy-MM-dd');
                }
            });
            $scope.save = function (data) {
                var params = {};
                params = data;
                params.provider_tags = data;
                params.id_provider =  $scope.id;
                params.customer_id = $scope.user1.customer_id;
                params.updated_by = $scope.user.id_user;
                providerService.update(params).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                        var obj = {};
                        obj.action_name = 'update';
                        obj.action_description = 'update$$provider$$(' + data.provider_name + ')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                        $state.go('app.provider.all-providers');
                    } else {
                        $rootScope.toast('Error', result.error, 'error');
                    }
                });
            }
        } else {
            $state.go('app.provider.all-providers');
        }
    })

   
    .controller('providerViewCtrl', function($sce,$timeout,$scope, $rootScope, $state, $stateParams, $filter,catalogueService,tagService,projectService,masterService,contractService, decode, encode, customerService,attachmentService,businessUnitService, Upload,$location, $uibModal, userService, AuthService,dateFilter,providerService,masterService){
        $rootScope.module = 'Relation Details';
        $rootScope.breadcrumbcolor='provider-breadcrumb-color';
        $rootScope.class='provider-content';
        $rootScope.icon='Relations'; 
        //console.log('stateparams info',$stateParams);
        if ($rootScope.access == 'wa' || $rootScope.access == 'ca') {
            $scope.showAddBtn = 1;
        }
        //console.log($rootScope);
        $scope.app_url= APP_DIR;
        $scope.loggin_user_id = $rootScope.id_user;
        $scope.disabled = false;
        $scope.currencyList = [];
        $scope.relationshipCategoryList = {};
        $scope.relationshipClassificationList = {};
        $scope.displayCount = $rootScope.userPagination;
        $rootScope.displayName = $stateParams.name;
        $scope.isSubLoading = true;
        $scope.id = decode($stateParams.id);
        var parentPage = $state.current.url.split("/")[1];
        var obj = {};
        obj.action_name = 'view';
        obj.action_description = 'view providers$$('+$stateParams.name+')';
        obj.module_type = $state.current.activeLink;
        obj.action_url = $location.$$absUrl;
        $rootScope.confirmNavigationForSubmit(obj);
        $scope.spendMgmtGraph ={};
        $scope.spendMgmtGraph.graph ={};
        $scope.spendLineGraph = {};

        var parentPage = $state.current.url.split("/")[1];
        var goWorkflow = (parentPage == 'all-activities')?'app.contract.contract-workflow':'app.contract.contract-workflow1';
        var goReview = (parentPage == 'all-activities')?'app.contract.contract-review':'app.contract.contract-review1';    

        $scope.detailsPageGo = function(row,tag){
            var goView = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
            if(tag.selected_field=='contract'){
                $state.go(goView,{name:row.name,id:encode(row.id),type:'review'});
            }
            else if(tag.selected_field=='relation'){
                $state.go('app.provider.view',{name:row.name,id:encode(row.id)});
            }
            else if(tag.selected_field=='project'){
                $state.go('app.projects.view',{name:row.name,id:encode(row.id),type:'workflow'});
            }
            else if(tag.selected_field=='catalogue'){
                console.log("23",row)
                console.log("tag",tag)
    
                if($scope.catalogueInfo){
                    $scope.catalogueInfo.close();
                  }  
                  if($scope.catalogueDetailsInfo){
                    $scope.catalogueDetailsInfo.close();
                  }          
    
                $scope.id_catalogue=row.id;
                    $scope.catalogueDetailsInfo = $uibModal.open({
                        animation: true,
                        backdrop: 'static',
                        keyboard: false,
                        scope: $scope,
                        size: 'lg',
                        openedClass: 'right-panel-modal modal-open',
                        templateUrl: 'views/Manage-Users/catalogue/view-catalogue-info.html',
                        controller: function ($uibModalInstance, $scope, item) {
                                    
                            $scope.getDownloadUrl = function(objData){
                                var fileName = objData.document_source;
                                var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
                                var d = {};
                                d.id_document = objData.id_document;
                                var encryptedPath= objData.encryptedPath;
                                var filePath =API_URL+'Cron/preview?file='+encryptedPath;
                                encodePath =encode(filePath);
                                if(fileExtension=='pdf' || fileExtension=='PDF' ){
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
                
                            $scope.catalogueInfo=function(){
                                var obj={};
                                obj.id_catalogue=$scope.id_catalogue;
                                console.log("as")
                                catalogueService.catalogueList(obj).then(function (result) {
                                    $scope.catalogue = result.data[0];
                                    console.log("345",$scope.catalogue.attachment.documents.length);
                                    $scope.catalogue_attach_count = result.data[0].catalogue_attachments_count;
                                    $scope.catalogue_info_count = result.data[0].catalogue_information;
                                    $scope.catalogue_tags_count = result.data[0].catalogue_tags;
                                });
                            }
                            $scope.catalogueInfo();
                               
                            catalogueService.getCatalogueTags({'id_catalogue':$scope.id_catalogue}).then (function(result){   
                                if(result.status){
                                    $scope.tagsInfo=[];
                                    $scope.tagsInfo = result.data;
                                    angular.forEach($scope.tagsInfo,function(i,o){
                                        angular.forEach(i.tag_details,function(j,o){
                                        if(j.tag_type=='date'){
                                            j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                          
                                            }
                                        })
                                    });
                
                                    }else {$rootScope.toast('Error',result.error,'error');}
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
                    $scope.catalogueDetailsInfo.result.then(function ($data) {
                    }, function () {
                    });
                
            }
        }

        
      
        if($stateParams.id){
            $scope.getProvidersInfo = function(){
                providerService.list({ 'id_provider': $scope.id ,'customer_id':$scope.user1.customer_id}).then(function (result) {
                    if (result.status) {
                        $scope.provider = result.data.data[0];
                        $scope.relationLabelnames=result.data.labels;
                        // console.log("details",result.data);
                        $scope.data =result.data.data[0];
                    }
                });
            }
            
        }
        $scope.getProvidersInfo();

        $scope.changeLockingStatus = function(info){
            var params={};
            params.id_document = info.id_document;
            contractService.lockingStatus(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    $scope.getProvidersInfo();
                }
            });
        }
        masterService.getCountiresList().then(function (result) {
            if (result.status) {
                $scope.countriesList = result.data;
            }
        });

        $scope.providerTagsDetails = function(){
            tagService.providerTagsInfo({'id_provider':$scope.id,'tag_type':'provider_tags'}).then (function(result){
                if(result.status){
                    $scope.providertagsInfo=[];
                    $scope.providertagsInfo = result.data;
                    //console.log("provider view",$scope.providertagsInfo);
                }else {$rootScope.toast('Error',result.error,'error',$scope.contract);}
    
            });
        }
        $scope.providerTagsDetails();

        $scope.uploadDoc = function(){
            // $scope.showUpload = true;
             var modalInstance = $uibModal.open({
                 animation: true,
                 backdrop: 'static',
                 keyboard: false,
                 scope: $scope,
                 openedClass: 'right-panel-modal modal-open',
                 templateUrl: 'create-edit-provider-doc.html',
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
                                     module_id:  $scope.user1.customer_id,
                                     module_type: 'provider',
                                     reference_id: decode($stateParams.id),
                                     reference_type: 'provider',
                                     document_type : 0,
                                     uploaded_by: $scope.user1.id_user
                                 }
                             }).then(function (resp) {
                                 $scope.showUpload = false;
                                 if(resp.data.status){
                                     $rootScope.toast('Success',resp.data.message);
                                     $scope.cancel();
                                     $scope.getProvidersInfo();
                                     var obj = {};
                                     obj.action_name = 'upload';
                                     obj.action_description = 'upload$$attachments$$for$$ provider$$('+$stateParams.name+')';
                                     obj.module_type = $state.current.activeLink;
                                     obj.action_url = window.location.href;
                                     $rootScope.confirmNavigationForSubmit(obj);
                                 }
                                 else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                                 $scope.init();
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
                         var r=confirm("Do you want to continue?");
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
                                     module_id: data.id_provider,
                                     module_type: 'provider',
                                     reference_id: decode($stateParams.id),
                                     reference_type: 'provider',
                                     document_type : 1,
                                     uploaded_by: $scope.user1.id_user
                                 }
                             }).then(function (resp) {
                                 $scope.showUpload = false;
                                 if(resp.data.status){
                                     $rootScope.toast('Success',resp.data.message);
                                     $scope.cancel();
                                     $scope.getProvidersInfo();
                                     var obj = {};
                                     obj.action_name = 'upload';
                                     obj.action_description = 'upload$$link$$for$$ provider$$('+$stateParams.name+')';
                                     obj.module_type = $state.current.activeLink;
                                     obj.action_url = window.location.href;
                                     $rootScope.confirmNavigationForSubmit(obj);
                                 }
                                 else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                                 $scope.init();
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
     
        
        $scope.UpdateProviderTags = function (row) {
            //console.log('row info',row);
            $scope.selectedRow = angular.copy(row);
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: '  views/bussiness_unit/update-provider-tags-modal.html',
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
                        else{
                            i.tag_answer = i.tag_answer;
                        }
                    });
                    $scope.updateTags = function(data){
                        // angular.forEach(data,function(i,o){
                        //     if(i.tag_type=='date'){
                        //         i.tag_answer = dateFilter(i.tag_answer,'yyyy-MM-dd');
                        //     }
                        // });
                        angular.forEach(data,function(i,o){
                            angular.forEach(i.tag_details,function(j,o){
                            if(j.tag_type=='date'){
                                j.tag_answer = dateFilter(j.tag_answer,'yyyy-MM-dd');
                            }
                        });
                    });
    
                        var params ={};
                        params.id_provider = $scope.id;
                        params.tag_type = 'provider_tags'
                        params.provider_tags = data;
                        tagService.updateProviderTags(params).then(function(result){
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.cancel();
                                $scope.providerTagsDetails();
                                $scope.getProvidersInfo();
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Contract$$Tags$$('+$stateParams.name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.init();
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
        $scope.getEventFeed = function (tableState){
            var pagination = tableState.pagination;
               setTimeout(function(){
                   $scope.tableStateRefEvent = tableState;
                   $scope.eventLoading = true;
                   tableState.reference_type = 'provider';
                   tableState.reference_id = decode($stateParams.id);
                   projectService.eventFeedList(tableState).then (function(result){
                       $scope.eventList = result.data;
                       $scope.eventListCount = result.total_records;
                       $scope.eventEmptyTable=false;
                       $scope.displayCount = $rootScope.userPagination;
                       $scope.totalRecordsEvent = result.total_records;
                       //console.log("io",$scope.totalRecordsEvent);
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
           var r = confirm("Do you want to continue?");
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
                   $scope.title = 'controller.relation_Event';
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
                       $scope.title = 'controller.relation_Event';
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

                       $scope.deleteAttachmentEvent = function(id,name){
                           var r=confirm("Do you want to continue?");
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
                           console.log("jio",eventData);
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
                                   'reference_type':'provider',
                                   'reference_id':$scope.provider.id_provider,
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
                                   'reference_type':'provider',
                                   'reference_id':$scope.provider.id_provider,
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
                       var r=confirm("Do you want to continue?");
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
                           var r=confirm("Do you want to continue?");
                           if(r==true){
                               $scope.contractLinks.splice(index, 1);
                           }                    
                       }

                       $scope.deleteAttachmentEvent = function(id,name){
                        var r=confirm("Do you want to continue?");
                        $scope.deleConfirm = r;
                        if(r==true){
                            var params = {};
                            params.id_document = id;
                            attachmentService.deleteAttachments(params).then (function(result){
                                if(result.status){
                                    $rootScope.toast('Success',result.message);
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
                                       module_type: 'provider',
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
                                       module_type: 'provider',
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

        $scope.getDownloadUrl = function(objData){
            var fileName = objData.document_source;
            var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
            //console.log(fileExtension)
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
                //console.log('paru');
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
                });
            }
           
        };


        $scope.updateProviderDetails =function (obj,val){
            //console.log('val',val);
            $scope.info = val;
            if (val == 1){
                $scope.templateUrl ="views/bussiness_unit/update-provider-info-modal.html" ; 
             } 
             if (val== 2){
                $scope.templateUrl ="views/bussiness_unit/update-provider-tags-modal.html" ; 
             }
             if(val==3){
                $scope.templateUrl ="views/bussiness_unit/update-provider-tags-modal.html"; 
             }
             if(val==4){
                $scope.templateUrl ="views/bussiness_unit/update-provider-info-modal.html"; 
             }
            $scope.selectedRow = obj;
            var modalInstance = $uibModal.open({
                nimation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal contract-list-popup modal-open',
                templateUrl: $scope.templateUrl,
                controller: function ($uibModalInstance, $scope,item) {
                    $scope.bottom = 'general.update';
                    $scope.fdata = {};
                    $scope.isView = false;
                    $scope.isLink = false;
                    $scope.contractParters={};
                    $scope.contractLinks=[];
                    $scope.contractLink={};
                    $scope.getInfo = function(){
                        var par = {};
                        par.id_provider = $scope.id;
                        par.customer_id =$scope.user1.customer_id;
                        providerService.list(par).then (function(result){
                            if(result.status){
                                $scope.infoObj = result.data.data[0];
                            }
                        });
                    }
                    $scope.getInfo();
                    //tags service open//

                    catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                        $scope.selectedInfoContract = result.data;
                });

                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'project'}).then (function(result){
                    $scope.selectedInfoProject = result.data;
                });
                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
                $scope.selectedInfoProvider = result.data;
                });
                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'catalogue'}).then (function(result){
                    $scope.selectedInfoCatalogue = result.data;
                });



                
                    $scope.providerTagsDetails1 = function(){
                        tagService.providerTagsInfo({'id_provider':$scope.id,'tag_type':'provider_tags'}).then (function(result){
                            if(result.status){
                                $scope.providertagsInfo=[];
                                $scope.providertagsInfo = result.data;
                                // angular.forEach($scope.providertagsInfo,function(i,o){
                                //     if(i.tag_type=='date'){
                                //         i.tag_answer = new Date(i.tag_answer);
                                //     }
                                // });
                                angular.forEach($scope.providertagsInfo,function(i,o){
                                    angular.forEach(i.tag_details,function(j,o){
                                    if(j.tag_type=='date'){
                                        j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                        }
                                    })
                                })
                            }else {$rootScope.toast('Error',result.error,'error',$scope.contract);}
                
                        });
                    }
                    $scope.providerTagsDetails1();

                    $scope.updateTags = function(data){
                        //console.log('data info',data);
                      
                        var params ={};
                        params.id_provider = $scope.id;
                        params.tag_type = 'provider_tags';
                        // angular.forEach(data,function(i,o){
                        //     if(i.tag_type=='date'){
                        //         i.tag_answer = dateFilter(i.tag_answer,'yyyy-MM-dd');
                        //     }
                        // });
                        angular.forEach(data,function(i,o){
                            angular.forEach(i.tag_details,function(j,o){
                            if(j.tag_type=='date'){
                                j.tag_answer = dateFilter(j.tag_answer,'yyyy-MM-dd');
                            }
                        });
                    });
    
                        params.grouped_tags = data;
                        tagService.updateProviderTags(params).then(function(result){
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Contract$$Tags$$('+$stateParams.name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                // angular.forEach($scope.providertagsInfo,function(i,o){
                                //     if(i.tag_type=='date'){
                                //         i.tag_answer = moment(i.tag_answer).utcOffset(0, false).toDate();
                                //     }
                                // })
                                angular.forEach($scope.providertagsInfo,function(i,o){
                                    angular.forEach(i.tag_details,function(j,o){
                                    if(j.tag_type=='date'){
                                        j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                        }
                                    })
                                })

                                $scope.getInfo();
                                $scope.providerTagsDetails();
                                $scope.getProvidersInfo();
                                //$scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                       });
                    }
                    //tags service close//

                    $scope.uploadAttachment = function (fData) {
                        $scope.isView = true;
                        var params = {};
                        params.file = fData.file.attachments
                        params.customer_id = $scope.user1.customer_id;
                        params.module_id = $scope.user1.customer_id;
                        params.module_type = 'provider';
                        params.reference_type= 'provider';
                        params.reference_id = decode($stateParams.id);
                        params.document_type =0;
                        params.uploaded_by = $scope.user1.id_user;
                        contractService.uploaddata(params).then(function (result) {
                          //console.log('result info',result);
                          if(result.status){
                            $rootScope.toast('Success',result.message);
                            $scope.fdata.file=[];
                            $scope.isView = false;
                            $scope.getInfo();
                            $scope.getProvidersInfo();
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
                        var r=confirm("Do you want to continue?");
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
                                    module_id: data.id_provider,
                                    module_type: 'provider',
                                    reference_id: decode($stateParams.id),
                                    reference_type: 'provider',
                                    document_type : 1,
                                    uploaded_by: $scope.user1.id_user
                                }
                            }).then(function (resp) {
                                $scope.showUpload = false;
                                if(resp.data.status){
                                    $rootScope.toast('Success',resp.data.message);
                                    //$scope.cancel();
                                    $scope.contractLinks=[];
                                    $scope.isLink = false;
                                    $scope.getProvidersInfo();
                                    var obj = {};
                                    obj.action_name = 'upload';
                                    obj.action_description = 'upload$$link$$for$$ provider$$('+$stateParams.name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = window.location.href;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                }
                                else $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                                $scope.getInfo();
                            }, function (resp) {
                                $rootScope.toast('Error',resp.data.error,'error');
                            }, function (evt) {
                                $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                            });
                        }else {
                            $rootScope.toast('Error','No link added','image-error');
                        }
                    }

                    $scope.changeLockingStatus = function(info){
                        var params={};
                        params.id_document = info.id_document;
                        contractService.lockingStatus(params).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success', result.message);
                                $scope.getInfo();
                                $scope.getProvidersInfo();
                            }
                        });
                    }

                    $scope.deleteModalAttachment = function(id,name){
                        var r=confirm("Do you want to continue?");
                        $scope.deleConfirm = r;
                        if(r==true){
                            var params = {};
                            params.id_document = id;
                            attachmentService.deleteAttachments(params).then (function(result){
                                if(result.status){
                                    $rootScope.toast('Success',result.message);
                                    $scope.getInfo();
                                    var obj = {};
                                    obj.action_name = 'delete';
                                    obj.action_description = 'delete$$Attachment$$('+name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.getProvidersInfo();
                                }else{$rootScope.toast('Error',result.error,'error');}
                            })
                        }
                    } 
                    //provider attachments close//

                    //provider stakeholders open//

                    $scope.contractParters={};
                    $scope.businessUnitList =[];
                    $scope.usersList =[];
                    contractService.getstakeholders({'id_provider':decode($stateParams.id)}).then(function (result) {
                        //console.log('result info',result);
                        if (result.status) {
                            $scope.contractParters=result.data;
                            //console.log('contractparters',$scope.contractParters);
                            $scope.contractParters.data = {};
                            if($scope.contractParters){
                                if($scope.contractParters.internal_contract_sponsor && $scope.contractParters.internal_contract_sponsor.id_user) {
                                    $scope.contractParters.data.internal_contract_sponsor = angular.copy($scope.contractParters.internal_contract_sponsor);
                                    $scope.contractParters.internal_contract_sponsor = $scope.contractParters.internal_contract_sponsor.name;
                                }
                                if($scope.contractParters.provider_contract_sponsor && $scope.contractParters.provider_contract_sponsor.id_user) {
                                    $scope.contractParters.data.provider_contract_sponsor = angular.copy($scope.contractParters.provider_contract_sponsor);
                                    $scope.contractParters.provider_contract_sponsor = $scope.contractParters.provider_contract_sponsor.name;
                                }
                                if($scope.contractParters.internal_partner_relationship_manager && $scope.contractParters.internal_partner_relationship_manager.id_user) {
                                    $scope.contractParters.data.internal_partner_relationship_manager = angular.copy($scope.contractParters.internal_partner_relationship_manager);
                                    $scope.contractParters.internal_partner_relationship_manager = $scope.contractParters.internal_partner_relationship_manager.name;
                                }
                                if($scope.contractParters.provider_partner_relationship_manager && $scope.contractParters.provider_partner_relationship_manager.id_user) {
                                    $scope.contractParters.data.provider_partner_relationship_manager = angular.copy($scope.contractParters.provider_partner_relationship_manager);
                                    $scope.contractParters.provider_partner_relationship_manager = $scope.contractParters.provider_partner_relationship_manager.name;
                                }
                                if($scope.contractParters.internal_contract_responsible && $scope.contractParters.internal_contract_responsible.id_user) {
                                    $scope.contractParters.data.internal_contract_responsible = angular.copy($scope.contractParters.internal_contract_responsible);
                                    $scope.contractParters.internal_contract_responsible = $scope.contractParters.internal_contract_responsible.name;
                                }
                                if($scope.contractParters.provider_contract_responsible && $scope.contractParters.provider_contract_responsible.id_user) {
                                    $scope.contractParters.data.provider_contract_responsible = angular.copy($scope.contractParters.provider_contract_responsible);
                                    $scope.contractParters.provider_contract_responsible = $scope.contractParters.provider_contract_responsible.name;
                                }
                                if($scope.contractParters.internal_contract_sponsor)
                                    $scope.disableOne=true;
                                if($scope.contractParters.provider_contract_sponsor)
                                    $scope.disableFour=true;
                                if($scope.contractParters.internal_partner_relationship_manager)
                                    $scope.disableTwo=true;
                                if($scope.contractParters.provider_partner_relationship_manager)
                                    $scope.disableFive=true;
                                if($scope.contractParters.internal_contract_responsible)
                                    $scope.disableThree=true;
                                if($scope.contractParters.provider_contract_responsible)
                                    $scope.disableSix=true;
                            }
                            $scope.removeAfterIcon();
                        }
                    });
                    var params1 = {};
                    params1.user_role_id = $scope.user1.user_role_id;
                    params1.customer_id  = $scope.user1.customer_id;
                    params1.id_user = $scope.user.id_user;
                    params1.status = 1;
                    businessUnitService.list(params1).then(function(result){
                        $scope.businessUnitList = result.data.data;
                        $scope.getUserList('');
                        $scope.getProviderUserList('');
                    });
                    $scope.getUserList = function(bid){
                        //console.log('bid',bid);
                        var reqObj={};
                        reqObj.user_type = 'internal';
                        reqObj.status=1;
                        reqObj.customer_id = $scope.user1.customer_id;
                        reqObj.user_role_id = $scope.user1.user_role_id;
                        reqObj.id_user = $scope.user1.id_user;
                        if(bid){
                            reqObj.business_unit_id = bid;
                        }
                        customerService.getUserList(reqObj).then(function (result){
                            $scope.usersList = result.data.data;
                        });
                    } 
                    $scope.getProviderUserList = function(){
                        var reqObj={};
                        reqObj.user_type = 'external';
                        reqObj.status=1;
                        reqObj.customer_id = $scope.user1.customer_id;
                        reqObj.id_provider = $scope.id;
                        reqObj.user_role_id = $scope.user1.user_role_id;
                        reqObj.id_user = $scope.user1.id_user;                    
                        customerService.getUserList(reqObj).then(function (result){
                            $scope.providerUsersList = result.data.data;                    
                        });
                    }              
                    var params ={};
                    $scope.update=function(data){
                        //console.log('data info',data);
                        var formData = angular.copy(data);
                        
                        if(formData.data && formData.data.internal_contract_sponsor && formData.data.internal_contract_sponsor.id_user) {
                            formData.internal_contract_sponsor = formData.data.internal_contract_sponsor;
                        }
                        if(formData.data && formData.data.provider_contract_sponsor && formData.data.provider_contract_sponsor.id_user) {
                            formData.provider_contract_sponsor = formData.data.provider_contract_sponsor;
                        }
                        if(formData.data && formData.data.internal_partner_relationship_manager && formData.data.internal_partner_relationship_manager.id_user) {
                            formData.internal_partner_relationship_manager = formData.data.internal_partner_relationship_manager;
                        }
                        if(formData.data && formData.data.provider_partner_relationship_manager && formData.data.provider_partner_relationship_manager.id_user) {
                            formData.provider_partner_relationship_manager = formData.data.provider_partner_relationship_manager;
                        }
                        if(formData.data && formData.data.internal_contract_responsible && formData.data.internal_contract_responsible.id_user) {
                            formData.internal_contract_responsible = formData.data.internal_contract_responsible;
                        }
                        if(formData.data && formData.data.provider_contract_responsible && formData.data.provider_contract_responsible.id_user) {
                            formData.provider_contract_responsible = formData.data.provider_contract_responsible;
                        }
                        params=formData;
                        params.updated_by = $scope.user.id_user;
                        params.id_provider  = decode($stateParams.id);
                        contractService.addSponsers(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                //$scope.cancel();
                                $scope.getProviderStakeholders();
                                var obj = {};
                                obj.action_name = 'Create';
                                obj.action_description = 'Create$$Sponsor$$Information$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.getInfo();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });
                    }
                    $scope.addInternalUsers = function (fieldId) {
                        $scope.removeAfterIcon();
                        angular.element('#'+fieldId).addClass('afterIcon');
                        $scope.showInnerDiv = true;
                        $scope.field = fieldId;
                    }
                    $scope.removeAfterIcon = function(){
                        $scope.showInnerDiv = false;
                        angular.element('#internal_contract_sponsor').removeClass('afterIcon');  
                        angular.element('#provider_contract_sponsor').removeClass('afterIcon');  
                        angular.element('#internal_partner_relationship_manager').removeClass('afterIcon');  
                        angular.element('#provider_partner_relationship_manager').removeClass('afterIcon');  
                        angular.element('#internal_contract_responsible').removeClass('afterIcon');  
                        angular.element('#provider_contract_responsible').removeClass('afterIcon');  
                    }
                    $scope.addInternalSponser = function(data,id,count) {
                        if( data != undefined && data.user != null ){
                            $scope.contractParters.data[id] = {};
                            $scope.contractParters.data[id].id_user = data.user.id_user;
                            $scope.contractParters.data[id].name = data.user.name;
                            $scope.contractParters.data[id].business_unit = data.business_unit;
                            $scope.contractParters[id] = data.user.name;
                            if(count==1)$scope.disableOne = true;
                            if(count==2)$scope.disableTwo = true;
                            if(count==3)$scope.disableThree = true;
                        }
                        $scope.showInnerDiv = false;
                        angular.element('#'+id).removeClass('afterIcon');
                    }
                    $scope.enableSponsor = function(id,count){
                        if(count==1)$scope.disableOne = false;
                        if(count==2)$scope.disableTwo = false;
                        if(count==3)$scope.disableThree = false;
                        $scope.contractParters[id]='';
                        $scope.contractParters.data[id]={};
                    }
                    $scope.addInternalProvider = function(data,id,count) {
                        if( data != undefined && data.user != null ){
                            $scope.contractParters.data[id] = {};
                            $scope.contractParters.data[id].id_user = data.user.id_user;
                            $scope.contractParters.data[id].name = data.user.name;
                            $scope.contractParters.data[id].provider = $scope.provider.provider_name;
                            $scope.contractParters[id] = data.user.name;
                            if(count==4)$scope.disableFour = true;
                            if(count==5)$scope.disableFive = true;
                            if(count==6)$scope.disableSix = true;
                        }
                        $scope.showInnerDiv = false;
                        angular.element('#'+id).removeClass('afterIcon');
                    }
                    $scope.enableProvider = function(id,count){
                        if(count==4)$scope.disableFour = false;
                        if(count==5)$scope.disableFive = false;
                        if(count==6)$scope.disableSix = false;
                        $scope.contractParters[id]='';
                        $scope.contractParters.data[id]={};
                    }

                    //provider stakeholders close//
                    $scope.cancel = function(){
                        $scope.providerTagsDetails();
                        $uibModalInstance.close();
                    }
                   
                    $scope.cancel1 = function(){
                        $uibModalInstance.close();
                    }
                  
                 
                    providerService.getProviderRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                        $scope.relationshipCategoryList = result.drop_down;
                     });
                    
                  
                    $scope.updateProviderInfo = function(data){
                        var postData = angular.copy(data);
                       
                        postData.customer_id=$scope.user1.customer_id;
                        Upload.upload({
                            url: API_URL+'Customer/updateProviderData',
                            data: {
                                'provider': postData
                            }
                        }).then(function(resp){
                            if(resp.data.status){
                                $rootScope.toast('Success',resp.data.message);
                                //$uibModalInstance.close();
                                $scope.getProvidersInfo();
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'update$$ provider$$'+postData.provider_name;
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.getInfo();
                            }else{
                                $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                            }
                        },function(resp){
                            $rootScope.toast('Error',resp.error);
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
                templateUrl: 'views/bussiness_unit/create-edit-provider-review.html',
                controller: function ($uibModalInstance, $scope) {
                    $scope.update = false;
                    $scope.title = 'contract.create_action_item';
                    $scope.bottom = 'general.save';
                    $scope.isEdit = false;
                    $scope.addaction = true;
                    $scope.data.due_date=moment().utcOffset(0, false).toDate();
                    
                    if($scope.type == 'view') $scope.bottom = 'contract.finish';
                    contractService.getActionItemResponsibleUsers({'id_provider': $scope.id}).then(function(result){
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
                        //console.log('data info',data);
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
                            if(data.is_finish  == 1) {
                                var r = confirm("Are you sure that you want to finish this action item ?");
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
                            params.provider_id =   $scope.id ;
                            params.created_by = $scope.user.id_user;
                            params.provider_id =   $scope.id ;
                            params.id_user  = $scope.user1.id_user;
                            params.user_role_id  = $scope.user1.user_role_id;
                            params.due_date  = $scope.due_date;
                            params.reference_type='provider';
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
                                //window.location = API_URL+'download/downloadreport?path='+result.data.url+'&name='+result.data.file;
                                window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+$rootScope.id_user+'&access_token='+obj.access_token;
                            }
                        });
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


        $scope.callServerSubContract = function (tableState){
            $scope.tableStateRef = tableState;
            $scope.isSubLoading = true;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            tableState.id_user=$scope.user1.user_id;
            tableState.business_unit_id='All';
            tableState.overview ='true';
            tableState.can_access = $scope.can_access;
            tableState.user_role_id = $rootScope.user_role_id
            tableState.provider_id = decode($stateParams.id);
            tableState.provider_name = $stateParams.name;
            // tableState.activity_filter =1;           
            providerService.providerContractslist(tableState).then (function(result){
                $scope.subContractsList = result.data.data;
                $scope.subContractCount =result.data.total_records;
                $scope.review_name=result.data.data.review_name;
                $scope.emptySubTable=false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords2 = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isSubLoading = false;
                if(result.data.total_records < 1)
                    $scope.emptySubTable=true;
            })
        }
        $scope.defaultPages2 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServerSubContract($scope.tableStateRef);
                }                
            });
        }
      
        $scope.goToContractDetails = function(row){
            if(row.is_workflow=='1')
                 $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
             else
                 $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.id_contract),type:'review'});
         }

         $scope.getProviderStakeholders = function(){
            contractService.getstakeholders({'id_provider':decode($stateParams.id)}).then(function (result) {
                if (result.status) {
                    $scope.contractParters=result.data;
                    if($scope.contractParters){                                       
                        if($scope.contractParters.internal_contract_sponsor && $scope.contractParters.internal_contract_sponsor.id_user) {
                            $scope.contractParters.internal_contract_sponsor = $scope.contractParters.internal_contract_sponsor.name;
                        }
                        if($scope.contractParters.provider_contract_sponsor && $scope.contractParters.provider_contract_sponsor.id_user) {
                            $scope.contractParters.provider_contract_sponsor = $scope.contractParters.provider_contract_sponsor.name;
                        }
                        if($scope.contractParters.internal_partner_relationship_manager && $scope.contractParters.internal_partner_relationship_manager.id_user) {
                            $scope.contractParters.internal_partner_relationship_manager = $scope.contractParters.internal_partner_relationship_manager.name;
                        }
                        if($scope.contractParters.provider_partner_relationship_manager && $scope.contractParters.provider_partner_relationship_manager.id_user) {
                            $scope.contractParters.provider_partner_relationship_manager = $scope.contractParters.provider_partner_relationship_manager.name;
                        }
                        if($scope.contractParters.internal_contract_responsible && $scope.contractParters.internal_contract_responsible.id_user) {
                            $scope.contractParters.internal_contract_responsible = $scope.contractParters.internal_contract_responsible.name;
                        }
                        if($scope.contractParters.provider_contract_responsible && $scope.contractParters.provider_contract_responsible.id_user) {
                            $scope.contractParters.provider_contract_responsible = $scope.contractParters.provider_contract_responsible.name;
                        }                                        
                    }
                }
            });
         }
         $scope.getProviderStakeholders();
         
         $scope.addSponsers = function () {
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'create-edit-contract-sponsor.html',
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
                    contractService.getstakeholders({'id_provider':decode($stateParams.id)}).then(function (result) {
                        //console.log('result info',result);
                        if (result.status) {
                            $scope.contractParters=result.data;
                            //console.log('contractparters',$scope.contractParters);
                            $scope.contractParters.data = {};
                            if($scope.contractParters){
                                if($scope.contractParters.internal_contract_sponsor && $scope.contractParters.internal_contract_sponsor.id_user) {
                                    $scope.contractParters.data.internal_contract_sponsor = angular.copy($scope.contractParters.internal_contract_sponsor);
                                    $scope.contractParters.internal_contract_sponsor = $scope.contractParters.internal_contract_sponsor.name;
                                }
                                if($scope.contractParters.provider_contract_sponsor && $scope.contractParters.provider_contract_sponsor.id_user) {
                                    $scope.contractParters.data.provider_contract_sponsor = angular.copy($scope.contractParters.provider_contract_sponsor);
                                    $scope.contractParters.provider_contract_sponsor = $scope.contractParters.provider_contract_sponsor.name;
                                }
                                if($scope.contractParters.internal_partner_relationship_manager && $scope.contractParters.internal_partner_relationship_manager.id_user) {
                                    $scope.contractParters.data.internal_partner_relationship_manager = angular.copy($scope.contractParters.internal_partner_relationship_manager);
                                    $scope.contractParters.internal_partner_relationship_manager = $scope.contractParters.internal_partner_relationship_manager.name;
                                }
                                if($scope.contractParters.provider_partner_relationship_manager && $scope.contractParters.provider_partner_relationship_manager.id_user) {
                                    $scope.contractParters.data.provider_partner_relationship_manager = angular.copy($scope.contractParters.provider_partner_relationship_manager);
                                    $scope.contractParters.provider_partner_relationship_manager = $scope.contractParters.provider_partner_relationship_manager.name;
                                }
                                if($scope.contractParters.internal_contract_responsible && $scope.contractParters.internal_contract_responsible.id_user) {
                                    $scope.contractParters.data.internal_contract_responsible = angular.copy($scope.contractParters.internal_contract_responsible);
                                    $scope.contractParters.internal_contract_responsible = $scope.contractParters.internal_contract_responsible.name;
                                }
                                if($scope.contractParters.provider_contract_responsible && $scope.contractParters.provider_contract_responsible.id_user) {
                                    $scope.contractParters.data.provider_contract_responsible = angular.copy($scope.contractParters.provider_contract_responsible);
                                    $scope.contractParters.provider_contract_responsible = $scope.contractParters.provider_contract_responsible.name;
                                }
                                if($scope.contractParters.internal_contract_sponsor)
                                    $scope.disableOne=true;
                                if($scope.contractParters.provider_contract_sponsor)
                                    $scope.disableFour=true;
                                if($scope.contractParters.internal_partner_relationship_manager)
                                    $scope.disableTwo=true;
                                if($scope.contractParters.provider_partner_relationship_manager)
                                    $scope.disableFive=true;
                                if($scope.contractParters.internal_contract_responsible)
                                    $scope.disableThree=true;
                                if($scope.contractParters.provider_contract_responsible)
                                    $scope.disableSix=true;
                            }
                            $scope.removeAfterIcon();
                        }
                    });
                    var params1 = {};
                    params1.user_role_id = $scope.user1.user_role_id;
                    params1.customer_id  = $scope.user1.customer_id;
                    params1.id_user = $scope.user.id_user;
                    params1.status = 1;
                    businessUnitService.list(params1).then(function(result){
                        $scope.businessUnitList = result.data.data;
                        $scope.getUserList('');
                        $scope.getProviderUserList('');
                    });
                    $scope.getUserList = function(bid){
                        var reqObj={};
                        reqObj.user_type = 'internal';
                        reqObj.status=1;
                        reqObj.customer_id = $scope.user1.customer_id;
                        reqObj.user_role_id = $scope.user1.user_role_id;
                        reqObj.id_user = $scope.user1.id_user;
                        if(bid){
                            reqObj.business_unit_id = bid;
                        }
                        customerService.getUserList(reqObj).then(function (result){
                            $scope.usersList = result.data.data;
                        });
                    } 
                    $scope.getProviderUserList = function(){
                        var reqObj={};
                        reqObj.user_type = 'external';
                        reqObj.status=1;
                        reqObj.customer_id = $scope.user1.customer_id;
                        reqObj.id_provider = $scope.id;
                        reqObj.user_role_id = $scope.user1.user_role_id;
                        reqObj.id_user = $scope.user1.id_user;                    
                        customerService.getUserList(reqObj).then(function (result){
                            $scope.providerUsersList = result.data.data;                    
                        });
                    }              
                    var params ={};
                    $scope.update=function(data){
                        //console.log('data info',data);
                        var formData = angular.copy(data);
                        
                        if(formData.data && formData.data.internal_contract_sponsor && formData.data.internal_contract_sponsor.id_user) {
                            formData.internal_contract_sponsor = formData.data.internal_contract_sponsor;
                        }
                        if(formData.data && formData.data.provider_contract_sponsor && formData.data.provider_contract_sponsor.id_user) {
                            formData.provider_contract_sponsor = formData.data.provider_contract_sponsor;
                        }
                        if(formData.data && formData.data.internal_partner_relationship_manager && formData.data.internal_partner_relationship_manager.id_user) {
                            formData.internal_partner_relationship_manager = formData.data.internal_partner_relationship_manager;
                        }
                        if(formData.data && formData.data.provider_partner_relationship_manager && formData.data.provider_partner_relationship_manager.id_user) {
                            formData.provider_partner_relationship_manager = formData.data.provider_partner_relationship_manager;
                        }
                        if(formData.data && formData.data.internal_contract_responsible && formData.data.internal_contract_responsible.id_user) {
                            formData.internal_contract_responsible = formData.data.internal_contract_responsible;
                        }
                        if(formData.data && formData.data.provider_contract_responsible && formData.data.provider_contract_responsible.id_user) {
                            formData.provider_contract_responsible = formData.data.provider_contract_responsible;
                        }
                        params=formData;
                        params.updated_by = $scope.user.id_user;
                        params.id_provider  = decode($stateParams.id);
                        contractService.addSponsers(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.cancel();
                                $scope.getProviderStakeholders();
                                var obj = {};
                                obj.action_name = 'Create';
                                obj.action_description = 'Create$$Sponsor$$Information$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.init();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });
                    }
                    $scope.addInternalUsers = function (fieldId) {
                        $scope.removeAfterIcon();
                        angular.element('#'+fieldId).addClass('afterIcon');
                        $scope.showInnerDiv = true;
                        $scope.field = fieldId;
                    }
                    $scope.removeAfterIcon = function(){
                        $scope.showInnerDiv = false;
                        angular.element('#internal_contract_sponsor').removeClass('afterIcon');  
                        angular.element('#provider_contract_sponsor').removeClass('afterIcon');  
                        angular.element('#internal_partner_relationship_manager').removeClass('afterIcon');  
                        angular.element('#provider_partner_relationship_manager').removeClass('afterIcon');  
                        angular.element('#internal_contract_responsible').removeClass('afterIcon');  
                        angular.element('#provider_contract_responsible').removeClass('afterIcon');  
                    }
                    $scope.addInternalSponser = function(data,id,count) {
                        if( data != undefined && data.user != null ){
                            $scope.contractParters.data[id] = {};
                            $scope.contractParters.data[id].id_user = data.user.id_user;
                            $scope.contractParters.data[id].name = data.user.name;
                            $scope.contractParters.data[id].business_unit = data.business_unit;
                            $scope.contractParters[id] = data.user.name;
                            if(count==1)$scope.disableOne = true;
                            if(count==2)$scope.disableTwo = true;
                            if(count==3)$scope.disableThree = true;
                        }
                        $scope.showInnerDiv = false;
                        angular.element('#'+id).removeClass('afterIcon');
                    }
                    $scope.enableSponsor = function(id,count){
                        if(count==1)$scope.disableOne = false;
                        if(count==2)$scope.disableTwo = false;
                        if(count==3)$scope.disableThree = false;
                        $scope.contractParters[id]='';
                        $scope.contractParters.data[id]={};
                    }
                    $scope.addInternalProvider = function(data,id,count) {
                        if( data != undefined && data.user != null ){
                            $scope.contractParters.data[id] = {};
                            $scope.contractParters.data[id].id_user = data.user.id_user;
                            $scope.contractParters.data[id].name = data.user.name;
                            $scope.contractParters.data[id].provider = $scope.provider.provider_name;
                            $scope.contractParters[id] = data.user.name;
                            if(count==4)$scope.disableFour = true;
                            if(count==5)$scope.disableFive = true;
                            if(count==6)$scope.disableSix = true;
                        }
                        $scope.showInnerDiv = false;
                        angular.element('#'+id).removeClass('afterIcon');
                    }
                    $scope.enableProvider = function(id,count){
                        if(count==4)$scope.disableFour = false;
                        if(count==5)$scope.disableFive = false;
                        if(count==6)$scope.disableSix = false;
                        $scope.contractParters[id]='';
                        $scope.contractParters.data[id]={};
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

        $scope.reviewAction = function (tableState){
            setTimeout(function(){
                $scope.tableStateRef = tableState;
                $scope.isLoading = true;
                var pagination = tableState.pagination;
                tableState.provider_id  = $scope.id;
                tableState.id_user  = $scope.user1.id_user;
                tableState.user_role_id  = $scope.user1.user_role_id;
                tableState.action_item_type  = 'outside';
                //tableState.id_contract_review  = $scope.data.id_contract_review;
                contractService.reviewActionItemList(tableState).then (function(result){
                    $scope.reviewList = result.data.data;
                    $scope.reviewListCount = result.data.count;
                    $scope.emptyTable=false;
                    $scope.displayCount = $rootScope.userPagination;
                    $scope.totalRecords1 = result.data.total_records;
                    tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                    $scope.isLoading = false;
                    if(result.data.total_records < 1)
                        $scope.emptyTable=true;
                })
            },700);
        }

        $scope.defaultPages1 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.reviewAction($scope.tableStateRef);
                }                
            });
        }

        $scope.updateContractReview = function (row, type) {
            //console.log('row info',row);
            $scope.type = type;
            $scope.data={};
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
                    // respUserParams.contract_review_id  = $scope.data.contract_review_id?$scope.data.contract_review_id:'0';
                    respUserParams.provider_id  = decode($stateParams.id);
                    //console.log(respUserParams);
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
                        //data.due_date = dateFilter(data.due_date,'yyyy-MM-dd');
                        $scope.due_date=angular.copy(data.due_date);
                        $scope.due_date=dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                        if($scope.type == 'view'){
                            params.external_users = data.external_users;
                            params.id_contract_review_action_item = data.id_contract_review_action_item;
                            params.comments = data.comments;
                            params.is_finish = data.is_finish;
                            params.updated_by = $scope.user.id_user;
                            params.provider_id =   $scope.id ;
                            params.due_date  = $scope.due_date;
                            if(data.is_finish  == 1){
                                var r=confirm("Are you sure that you want to finish this action item ?");
                                $scope.deleConfirm = r;
                                if(r==true){
                                    contractService.reviewActionItemUpdate(params).then(function (result) {
                                        if (result.status) {
                                            $scope.cancel();
                                            var obj = {};
                                            obj.action_name = 'Finish';
                                            obj.action_description = 'Finish$$Action$$Item$$('+data.action_item+')';
                                            obj.module_type = $state.current.activeLink;
                                            obj.action_url = $location.$$absUrl;
                                            $rootScope.confirmNavigationForSubmit(obj);
                                            $rootScope.toast('Success', result.message);
                                            $scope.reviewAction($scope.tableStateRef);
                                        } else {
                                            $rootScope.toast('Error', result.error,'error');
                                        }
                                    });
                                }
                            }else{
                                params.due_date  = $scope.due_date;
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
                            params.provider_id = $scope.id;
                            params.due_date  = $scope.due_date;
                            //console.log(params);
                            contractService.addReviewActionItemList(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$Action$$Item$$('+data.action_item+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.cancel();
                                    $scope.getActionItemById(data.id_contract_review_action_item);
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
            var r=confirm("Do you want to continue?");
            $scope.deleConfirm = r;
            if(r==true){
                var params ={};
                params.id_contract_review_action_item  = row.id_contract_review_action_item;
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
        $scope.deleteAttachment = function(id,name){
            var r=confirm("Do you want to continue?");
            $scope.deleConfirm = r;
            if(r==true){
                var params = {};
                params.id_document = id;
                attachmentService.deleteAttachments(params).then (function(result){
                    if(result.status){
                        $rootScope.toast('Success',result.message);
                        $scope.getProvidersInfo();
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
        $scope.goToProviderLogs = function(id){
           $state.go('app.provider.provider-log1',{name:$stateParams.name,id:$stateParams.id});
        } 

        
        $scope.req={};
        $scope.req.status=0;
        $scope.getProviderusers = function (tableState){
            var pagination = tableState.pagination;
               setTimeout(function(){
                   $scope.tableStateRef2 = tableState;
                   $scope.isSubLoading = true;
                   tableState.id_provider = decode($stateParams.id);
                   tableState.user_type='external';
                   tableState.customer_id = $scope.user1.customer_id;
                   tableState.id_user = $scope.user1.id_user;
                   tableState.user_role_id = $scope.user1.user_role_id;
                   customerService.getUserList(tableState).then (function(result){
                       $scope.usersList = result.data.data;
                       $scope.usersListCount = result.data.total_records;
                       $scope.emptyTable1=false;
                       $scope.displayCount = $rootScope.userPagination;
                       $scope.totalRecords1 = result.data.total_records;
                       tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                       $scope.isSubLoading = false;
                       if(result.data.total_records < 1)
                          $scope.emptyTable1=true;
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
                  
                  
                    $scope.disableField = true;
                    $scope.userInfo = function(){
                        var params ={};
                        params.user_id = row.id_user;
                        params.customer_id = $scope.user1.customer_id;
                        customerService.getUserById(params).then(function(result){
                            console.log('result',result);
                            $scope.customUser = result.data;
                            if($scope.customUser.contribution_type==0){
                                $scope.hidefield =true;
                            }
                            else{
                                $scope.hidefield =false;
                                $scope.hideManual = true;
                            }
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

                    $scope.countriesList = {};
                    masterService.getCountiresList().then(function(result){
                        if(result.status){
                            $scope.countriesList = result.data;
                        }
                    })
                    $scope.getValue = function(val){
                        // console.log("userrelation",val);
                        if(val=='other') {
                            $scope.disableField = false;
                        }
                        else{
                        $scope.disableField = true;

                            }
                        }
                        $scope.customUser={};
                        $scope.hidefield = true;
                        $scope.changeStatus = function(value){
                            //console.log('v',value);
                            if(value==0){
                            $scope.hidefield = true;
                            $scope.hideManual = false;
                            $scope.hidePasswordfield = false;
                            $scope.customUser.is_manual ='';   
                            $scope.customUser.user_status ='1'; 
                            }
                            else{
                                $scope.hidefield = true; 
                                $scope.hidePasswordfield = true;
                            }
                        }

                    $scope.addUser =  function (customUser){
                        var params ={};
                        params = customUser;
                        params.created_by = $scope.user.id_user;
                        params.customer_id = $scope.user1.customer_id;
                        params.provider_name = decode($stateParams.id);
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
                               //$scope.getProviderusers($scope.tableStateRef2);
                            }else{
                                $rootScope.toast('Error',result.error,'error',$scope.user);
                            }
                        });
                    }

                    $scope.goToLink = function(link){
                        if(link != undefined){
                            var r=confirm($filter('translate')('contract.alert_msg'));
                            if(r==true){
                                link = link.match(/^https?:/) ? link : '//' + link;
                                window.open(link,'_blank');
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

        $scope.deleteExternalUser = function(row){
            var r=confirm($filter('translate')('general.alert_continue'));
            if(r==true){
                customerService.deleteUsers({'id_user':row.id_user}).then(function(result){
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                        $scope.getProviderusers($scope.tableStateRef2);
                    } else {
                        $rootScope.toast('Error', result.error,'error');
                    }
                })
            }
            
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
                    
                    $scope.disableField = true;
                    $scope.getValue = function(val){
                        // console.log("userrelationcreation",val);
                        if(val=='other') {
                            $scope.disableField = false;
                        }
                    else{
                    $scope.disableField = true;

                        }
                    }
                    $scope.customUser={};
                    $scope.changeStatus = function(value){
                        console.log('v1',value);
                        if(value==0){
                        $scope.hidefield = true;
                        $scope.customUser.is_manual ='';   
                        $scope.customUser.user_status ='1'; 
                        }
                        else{
                            $scope.hidefield = false; 
                        }
                    }
              
                $scope.addUser =  function (customUser){
                    var params ={};
                    params = customUser;
                    params.created_by = $scope.user.id_user;
                    params.customer_id = $scope.user1.customer_id;
                    params.provider_name = decode($stateParams.id);
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
    .controller('providerLogCtrl', function($state, $scope, $rootScope, $stateParams, decode,encode, providerService,contractService,userService,AuthService){
        $rootScope.module = 'Provider Logs';
        $rootScope.displayName = $stateParams.name;
        $rootScope.breadcrumbcolor='provider-breadcrumb-color';
        $rootScope.class='provider-content';
        $rootScope.icon='Relations'; 
        $scope.displayCount = $rootScope.userPagination;
        providerService.getProviderLogs({'id_provider':decode($stateParams.id)}).then (function(result){
            if(result.status){
                $scope.currentContract = result.data.current_Provider_detailis;
                $scope.providerLogOptions = result.data.provider_log_options;
            }
        });

        $scope.getDownloadUrl = function(objData){
            var fileName = objData.document_source;
            var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
            //console.log(fileExtension)
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
                //console.log('paru');
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
                });
            }
           
        };
        $scope.getContractLogs = function(logId) {
            //console.log(logId);
            var param = {};
            param.provider_log_id = logId;
            param.id_provider = decode($stateParams.id);
            providerService.getProviderLogs(param).then (function(result){
                if(result.status){
                    $scope.currentContract = result.data.current_Provider_detailis;
                    $scope.contractLogOptions = result.data.provider_log_options;
                    $scope.contractLogDetails = result.data.contract_log_details;
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
            tableState.reference_type =  'provider';
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