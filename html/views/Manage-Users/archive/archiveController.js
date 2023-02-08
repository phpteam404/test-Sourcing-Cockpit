angular.module('app',['localytics.directives'])
.controller('archiveListCtrl', function($scope, $sce,$rootScope,$translate,$filter, $state, $stateParams,$filter, $localStorage, dateFilter, $timeout,$uibModal, providerService, archiveService, contractService, businessUnitService, encode, AuthService,userService,projectService){
   
    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }
   
    var param ={};
    $scope.del=0;
    $scope.contract_status='';
    $scope.can_access=1;
    $scope.date_field='cr.updated_on';
    $scope.date_period='';
    $scope.provider_name='';
    $scope.business_unit_id='';
    $scope.relationship_category_id='';
    $scope.searchFields = {}; 
    $scope.displayCount = $rootScope.userPagination;
    $localStorage.curUser.data.filters.allActivities = undefined;
    $scope.resetPagination=false;
    if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
        $scope.del=1;
    }

    $scope.advancedFilterArchive = function(){
        $scope.filterCreate = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/advancedFilterContract.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';

                $scope.filterListArchive=function(){
                var params ={};
                params.user_id=$scope.user.id_user;
                params.module='archive';
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
                $scope.filterListArchive();


            $scope.flterDelete=function(rowdata){
                var r = confirm($filter('translate')('general.alert_Delete_filter'));
                if(r==true){
                    contractService.deleteContractFlter({'id_master_filter':rowdata.id_master_filter}).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success', result.message);
                            $scope.filterArchiveList();
                            $scope.filterListArchive();
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
            templateUrl: 'views/Manage-Users/archive/create-archive-filter.html',
            controller: function ($uibModalInstance,$scope,item) {
            $scope.bottom ='general.save';
             $scope.title='controller.add_filter_criteria'

                contractService.getContractDomain({'domain_module': 'archive'}).then(function(result){
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

                    if($scope.feldName=='Category'){
                contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                    $scope.relationshipCategoryList = result.drop_down;
                });
                     }

                    if($scope.feldName=='Owner'){
                contractService.responsibleUserList({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(), 'type': 'buowner','forDocumentIntelligence':'1','forAdvacedFilter':'1' }).then(function (result) {
                    $scope.buOwnerUsers = result.data;
                })
                     }

                    if($scope.feldName=='Delegate'){
                    contractService.getDelegates({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(),'forDocumentIntelligence':'1','forAdvacedFilter':'1' }).then(function (result) {
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
                    params.user_id=$scope.user.id_user;
                    params.module='archive';
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
                                $scope.filterCreate.value = new Date( $scope.filterCreate.value);
                            }
                        $scope.options = {
                            minDate: new Date(),
                            showWeeks: false
                            };
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

                    if($scope.feldName=='Category'){
                    contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                        $scope.relationshipCategoryList = result.drop_down;
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
                });


                    $scope.addContractFilter=function(fields){
                        var para = angular.copy(fields);
                        para.user_id=$scope.user.id_user;
                        para.field=$scope.feldName;
                        para.field_type=$scope.fieldType;
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
                                $scope.filterArchiveList();
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
                    params.user_id=$scope.user.id_user;
                    params.field=$scope.feldName;
                    params.field_type=$scope.fieldType;  

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
                            $scope.filterArchiveList();
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
                    $scope.filterArchiveList();
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
    
        $scope.filterArchiveList=function(){
        var params ={};
        params.user_id = $scope.user1.id_user;
        params.module = 'archive';
        contractService.getContractList(params).then(function(result){
            $scope.filterList=result.data;

            angular.forEach($scope.filterList,function(obj){
                obj.value_names_string = $sce.trustAsHtml(obj.value_names_string);
                console.log("log",obj.value_names_string);
            });

            $scope.filterCross=false;
            if($scope.filterList.length>0){
                $scope.filterCross=true;
                }
            });
        }
        $scope.filterArchiveList();


    // param.user_role_id=$rootScope.user_role_id;
    // param.id_user=$rootScope.id_user;
    // param.customer_id = $scope.user1.customer_id;
    // param.status = 1;
    // businessUnitService.list(param).then(function(result){
    //     result.data.data.unshift({'id_business_unit':'All', 'bu_name':'All'});
    //     $scope.bussinessUnit = result.data.data;
    // });
    $scope.getStatuses = function () {
        $scope.resetPagination=true;
        $stateParams.pname=undefined;
    }
    // contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
    //     $scope.relationshipCategoryList = result.drop_down;
    // });
    $scope.contractsList = [];
    $scope.myDataSource = {};
    // $scope.getProviderList = function(id){
    //     //$scope.provider_name = null;
    //     var params = {};
    //     if(id) {
    //         $stateParams.pname=undefined;
    //         params.business_unit_id = id;
    //         $scope.provider_name = null;
    //     }
    //     params.customer_id = $scope.user1.customer_id;
    //     params.id_user  = $scope.user1.id_user;
    //     params.user_role_id  = $scope.user1.user_role_id;
    //     params.status  = 1;
    //     providerService.list(params).then(function(result){
    //         result.data.data.unshift({'provider_name':'All'});
    //         $scope.providerList = result.data.data;
    //     });
    // };
    // $scope.getProviderList();
    $scope.callServer = function (tableState){
        $rootScope.module = '';
        $rootScope.displayName = ''; 
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';        
        $scope.isLoading = true;
        var pagination = tableState.pagination;
        tableState.customer_id = $scope.user1.customer_id;
        tableState.date_period  = $scope.date_period;
        tableState.date_field  = $scope.date_field; 
        tableState.is_advance_filter=1;
        if($stateParams.pname){
            $scope.contractsList = [];
            if(tableState.provider_name != undefined){
            }
            else {
                $scope.provider_name = $stateParams.pname;
                $scope.resetPagination=true;
            }             
        }
        if($scope.provider_name && $scope.provider_name != null){
            if($scope.provider_name !='All')
                tableState.provider_name  = angular.copy($scope.provider_name);
            else delete tableState.provider_name;
        }else{
            delete tableState.provider_name;
            $scope.provider_name = '';
        } 
        if($scope.relationship_category_id && $scope.relationship_category_id != null){
            tableState.relationship_category_id  = $scope.relationship_category_id;
        }else{
            delete tableState.relationship_category_id;
            $scope.relationship_category_id = '';
        }
        if($scope.resetPagination){
            tableState.pagination={};
            tableState.pagination.start='0';
            tableState.pagination.number='10';
        }
        if(tableState.updated_on ==null || tableState.updated_on == undefined){
            delete tableState.date_period;
            delete tableState.date_field;
            $scope.date_period = '';
            $scope.date_field = 'cr.updated_on';   
        } else{
            tableState.updated_on = dateFilter($scope.updated_on,'yyyy-MM-dd');
        }
        if($scope.id_business_unit !='All')
            tableState.id_business_unit  = angular.copy($scope.id_business_unit);
        else delete tableState.id_business_unit;

        $scope.tableStateRef = tableState;

        archiveService.allArchiveList(tableState).then (function(result){
            $scope.contractsList =[];           
            $scope.contractsList = result.data.data;           
            $scope.emptyTable=false;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords = result.data.total_records;
            // $scope.getProviderList(tableState.id_business_unit);
            $scope.provider_name = angular.copy(tableState.provider_name);
            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
            $scope.isLoading = false;
            $scope.resetPagination=false;
            $scope.memorizeFilters(tableState);
            if(result.data.total_records < 1)
                $scope.emptyTable=true;
        })
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
    $scope.filterDateType = function(val) {
        $scope.resetPagination=true;
        $scope.date_field = val;
        if($scope.date_period)$scope.tableStateRef.date_period = $scope.date_period;
        else {
            $scope.tableStateRef.date_period = '=';
            $scope.date_period = '=';
        }
        if(!$scope.created_on) {
            angular.element('#created_date').addClass('req-filter');
        }
        $scope.tableStateRef.date_field = val;
        if($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_on){
            $scope.callServer($scope.tableStateRef);  
        }
    }
    $scope.filterDatePeriod = function(val) {
        $scope.resetPagination=true;
        $scope.date_period = val;
        if($scope.date_field)$scope.tableStateRef.date_field = $scope.date_field;
        else {
            $scope.tableStateRef.date_field = 'cr.created_on';
            $scope.date_field = 'cr.created_on';
        }
        if(!$scope.created_on) {
            angular.element('#created_date').addClass('req-filter');
        }
        $scope.tableStateRef.date_period = val;
        if($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_on){
            $scope.callServer($scope.tableStateRef);  
        }
    }    
    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        showWeeks: false
    };
    $scope.clear = function() {
        $scope.updated_on = null;
        $scope.date_period = null;
        $scope.date_field = 'cr.updated_on';
        $scope.id_business_unit = null;
        $scope.relationship_category_id = null;
        $scope.provider_name = null;
        $stateParams.pname=undefined;
        $scope.contract_status = null;
        $scope.end_date_lessthan_90 = null;
        $scope.activity_topic=null;
        $scope.activity_type=null;
        $scope.tableStateRef.search = {};
        $scope.resetPagination=true;
        angular.element('#created_date').removeClass('req-filter');
        $state.transitionTo("app.archive",{reload: true, inherit: false});
    };
    $scope.filterActivityType = function(val) {
        $scope.resetPagination=true;
        $scope.activity_topic  = val;     
        $scope.tableStateRef.activity_topic = val;
       if($scope.tableStateRef.activity_topic){
            $scope.callServer($scope.tableStateRef);  
        }else{
            delete $scope.tableStateRef.activity_topic;
            $scope.callServer($scope.tableStateRef);
       } 
    }

    $scope.filterType = function(val){
        $scope.resetPagination=true;
        $scope.activity_type  = val;
        $scope.tableStateRef.activity_type = val;
        if($scope.tableStateRef.activity_type){
            $scope.callServer($scope.tableStateRef);  
        }else{
            delete $scope.tableStateRef.activity_type;
            $scope.callServer($scope.tableStateRef);
       } 
    }
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
                $scope.date_field='cr.created_on';
                $scope.tableStateRef.date_field = $scope.date_field;
            }
            if($scope.date_period) 
                $scope.tableStateRef.date_period = $scope.date_period;
            else {
                $scope.date_period='=';
                $scope.tableStateRef.date_period = $scope.date_period;
            }
        }
        $scope.tableStateRef.updated_on = d;
        $scope.resetPagination=true;
        $scope.callServer($scope.tableStateRef);
    }
    $scope.getArchiveFiles = function(row){
        console.log(row);
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/archive/file_links_list.html',
            controller: function ($uibModalInstance, $scope, item) {
                if (item) {
                    $scope.isEdit = true;
                    $scope.getQuestionAttachmentsList = function(obj){
                        var param ={};                            
                        param.id_contract_review  = obj.id_contract_review;
                        archiveService.archiveFilesList(param).then(function(result){
                            $scope.attachments = result.data;
                        });
                    }
                    $scope.getQuestionAttachmentsList(item);
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                }
                $scope.verifyLink = function(data){
                    if(data !={}){
                        $scope.contractLinks.push(data);
                        $scope.contractLink={};
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
    $scope.filterByProvider = function(row){
        $state.transitionTo("app.archive",{pname:(row.providerName)},{reload: true, inherit: false});
    }
    $scope.getDownloadUrl = function(objData){
        console.log('ob',objData);
        var d = {};
        d.id_document = objData.id_document;
        var fileName = objData.document_source;
        var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
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
                    $rootScope.toast('Error',result.error,'l-error');
                }
            });
        }
      
    };
    $scope.redirectUrl = function(url){
        console.log(url);
        if(url != undefined){
            var r=confirm($filter('translate')('contract.alert_msg'));
            if(r==true){
                url = url.match(/^https?:/) ? url : '//' + url;
                window.open(url,'_blank');
            }
        }
    };
    $scope.goToContractDashboard = function(row){
        $state.go('app.contract.contract-dashboard1',{name:row.contract_name,id:encode(row.contract_id),rId:encode(row.id_contract_review),type:'review'});
    }
    $scope.getDataByReviewDate = function(row,type){
        // console.log('in popup',row);
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
                $scope.type=item.type;
                $scope.projectType=type;
                console.log("Asd",type);
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
                                // console.log("hj",$scope.moduleTopics)
                                $scope.side_by_side=result.side_by_side_validation;
                                // console.log("kl",$scope.side_by_side);
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
                                $scope.side_by_side=result.side_by_side_validation;
                                console.log("kl",$scope.side_by_side);
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
        console.log("asdf",row);
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
        $scope.selectedRow = row.external_user_question_feedback;
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

    $scope.showdiscussion =function(row,flag){
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
                if(flag)$scope.isWorkflow=true;
                else $scope.isWorkflow=false;
                if($scope.question.question_type=='date'){
                    $scope.question.question_answer = new Date($scope.question.question_answer);
                    $scope.question.second_opinion = new Date($scope.question.second_opinion);
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

    $scope.getDataByProjectDate = function(row){
        console.log('row info',row);
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/archive/project-archieve-data-modal.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.title="";
                $scope.name= item.templateName;
                $scope.type=item.type;
                $scope.isWorkflow=false;
                $scope.moduleTopics = [];
                $scope.title = 'workflows.workflow';
                $scope.isWorkflow=true;

                var params ={};               
                params.module_id = item.module_id;
                params.contract_review_id = item.id_contract_review ;
                params.project_id = item.contract_id;
                params.is_workflow=1;
                params.contract_workflow_id= item.contract_workflow_id;
                params.type='archieve';
                projectService.getProjectDashboard(params).then(function (result) {
                    if(result.status){
                        $scope.dashboardData =  result.data;
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
  
})