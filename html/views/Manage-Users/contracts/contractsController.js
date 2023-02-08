angular.module('app',['ng-fusioncharts','localytics.directives'])
.controller('contractOverviewCtrl', function($scope, $rootScope, $state,$localStorage, $filter,$translate, encode, businessUnitService, contractService, AuthService,userService,projectService){
    $scope.bussinessUnit = {};
    $scope.displayCount = $rootScope.userPagination;

    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }

    var param ={};
    $scope.del=0;
    if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
        $scope.del=1;
    }
    param.user_role_id=$rootScope.user_role_id;
     param.id_user=$rootScope.id_user;
     param.customer_id = $scope.user1.customer_id;
     param.status = 1;
     businessUnitService.list(param).then(function(result){
        // result.data.data.push({'id_business_unit':'All', 'bu_name':'All'});
        // $scope.bussinessUnit = result.data.data.reverse();
    $scope.bussinessUnit = result.data.data;
 });
    $scope.createContract = function(row){
        if(row)
            $state.go('app.contract.edit-contract',{name:row.contract_name,id:encode(row.id_contract)});
        else
            $state.go('app.contract.create-contract');
    }
    $scope.goToContractDashboard = function (row) {
     if(row.is_workflow=='0' && row.type=='contract')
           $state.go('app.contract.contract-dashboard',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
      if(row.is_workflow=='1' && row.type=='contract')
         $state.go('app.contract.workflow-dashboard',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
     if(row.is_workflow=='1'&& row.type=='project')
        $state.go('app.projects.project-dashboard1',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
     }
    $scope.exportContractReview = function (row) {
       
        var params={};
        if(row.type=='contract'){
            params.contract_id = params.id_contract= row.id_contract;
            params.id_user=  $scope.user1.id_user;
            params.user_role_id  = $scope.user1.user_role_id;
            params.is_workflow  = row.is_workflow;
            if(row.is_workflow=='1') params.contract_workflow_id  = row.id_contract_workflow;
            contractService.exportReviewData(params).then(function(result){
                if(result.status){
                    var obj = {};
                    obj.action_name = 'export';
                    obj.action_description = (row.is_workflow=='1')?'export$$contract$$workflow$$('+ row.contract_name+')':'export$$contract$$review$$('+ row.contract_name+')';
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
                }else{$rootScope.toast('Error',result.error,'l-error');}
            })
        }
        else{
            params.contract_id = row.id_contract;
            params.contract_review_id = row.id_contract_review;
            params.contract_workflow_id = row.id_contract_workflow;
            params.is_workflow =row.is_workflow;
            params.id_user=  $scope.user1.id_user;
            params.user_role_id  = $scope.user1.user_role_id;
            projectService.exportProjectDashboardData(params).then(function(result){
                if(result.status){
                    var obj = {};
                    obj.action_name = 'export';
                    obj.action_description = (row.is_workflow=='1')?'export$$contract$$workflow$$('+ row.contract_name+')':'export$$contract$$review$$('+ row.contract_name+')';
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
                }else{$rootScope.toast('Error',result.error,'l-error');}
            })
        }
    
    }

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
})
.controller('deletedContractListCtrl', function($scope, $rootScope,$localStorage,$translate,$filter, $state, $stateParams, contractService, encode,AuthService, userService){
    
    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }
    
    var param ={};
    param.user_role_id=$rootScope.user_role_id;
    param.id_user=$rootScope.id_user;
    param.customer_id = $scope.user1.customer_id;
    param.status = 1;
    $scope.displayCount = $rootScope.userPagination;
    $scope.getStatuses = function () {
        contractService.getContractStatus().then(function(result){
            if(result.status){
                var obj = {key:'all',value:'All'};
                result.data = result.data.reverse();
                result.data.push(obj);
                $scope.statusList = result.data.reverse();
            }
        })
    }
    $scope.getStatuses();
    $scope.deletedContractsList = {};
    $scope.myDataSource = {};

    $scope.callServer = function (tableState){
        $scope.filtersData = {};
        $rootScope.module = '';
        $rootScope.displayName = '';
        $rootScope.breadcrumbcolor=''; 
        $rootScope.icon='';
        $rootScope.class='';
        $scope.tableStateRef = tableState;
        $scope.isLoading = true;
        var pagination = tableState.pagination;
        tableState.customer_id = $scope.user1.customer_id;
        tableState.business_unit_id = $scope.business_unit_id;
        tableState.id_user  = $scope.user1.id_user;
        tableState.user_role_id  = $scope.user1.user_role_id;
        if($scope.provider_name && $scope.provider_name != null){
            tableState.provider_name  = $scope.provider_name;
        }else{
            delete tableState.provider_name;
            $scope.provider_name = '';
        }
        if($scope.contract_status && $scope.contract_status != null){
            if($scope.contract_status !='all')
                tableState.contract_status  = $scope.contract_status;
            else delete tableState.contract_status;
        }else{
            delete tableState.contract_status;
            $scope.contract_status = '';
        }
        $scope.filtersData.provider_name = tableState.provider_name;
        $scope.filtersData.business_unit_id = tableState.business_unit_id;
        $scope.filtersData.contract_status = tableState.contract_status;
        contractService.listDelete(tableState).then (function(result){
            /*if($scope.filtersData && (tableState.pagination.start == 0)){
                $scope.contractOverallDetails($scope.filtersData);
            }else{*/
            $scope.deletedContractsList = result.data.data;
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
                $scope.callServer($scope.tableStateRef);
            }                
        });
    }

    $scope.goToDashboard = function (row) {
        if(row.is_workflow=='0')
            $state.go('app.contract.contract-dashboard',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
        else
            $state.go('app.contract.workflow-dashboard',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
    }

    if($stateParams.pname){
        $scope.provider_name = $stateParams.pname;
        setTimeout(function(){
            $scope.callServer($scope.tableStateRef);
        },500);
    }
    if($stateParams.status){
        $scope.contract_status = $stateParams.status;
        setTimeout(function(){
            $scope.callServer($scope.tableStateRef);
        },300);
    }

    $scope.undoDeleteContract = function(row){
        var r=confirm($filter('translate')('general.alert_continue'));
        if(r==true){
            var params = {};
            params.contract_id = row.id_contract;
            params.user_role_id = $scope.user1.user_role_id;
            params.id_user = $scope.user1.id_user;
            contractService.undoDelete(params).then(function (result) {
                if(result.status){
                    var obj = {};
                    obj.action_name = 'Undo Delete';
                    obj.action_description = 'undo $$ contract delete $$('+result.data.file_name+')';
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
})
.controller('createContractCtrl', function($scope, $rootScope,$localStorage,$translate,$filter, $state,$stateParams,$location, decode,catalogueService,tagService,templateService, providerService, contractService, masterService,Upload, dateFilter){
    $scope.currencyList = [];
    $scope.templateList = [];
    $scope.relationshipCategoryList = {};
    $scope.relationshipClassificationList = {};
    $scope.contract = {};
    $scope.file={};
    $scope.links_delete = [];
    $rootScope.module = '';
    $rootScope.displayName = '';
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon=''; 
    $scope.del=0;
    $scope.contractId=0;
    $scope.isEditContract=false;
    $scope.disabled =false;
    $scope.showFiled = true;
    $localStorage.curUser.data.filters = {};
    
    if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
        $scope.del=1;
    }
    // tagService.list({'status':1,'tag_type':'contract_tags'}).then(function(result){
    //     if (result.status) {
    //         $scope.tags = result.data;
    //     }
    // });


    tagService.groupedTags({'status':1,'tag_type':'contract_tags'}).then(function(result){
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
    
    // providerService.list({'customer_id': $scope.user1.customer_id,'status':1,'all_providers':true}).then(function(result){
    //     $scope.providers = result.data.data;
    // });
    masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
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
    contractService.generateContractId({'customer_id':$scope.user1.customer_id}).then(function(result){
        if(result.status){
            $scope.contract = result.data;
        }
    });
    $scope.title = 'general.create';
    $scope.bottom = 'general.save';
    $scope.enableTemplate = true;
    if($stateParams.id){
        $scope.title = 'general.edit';
        $scope.bottom = 'general.update';
        $rootScope.module = 'Contract';
        $scope.isEditContract=true;
        $scope.contractId=decode($stateParams.id);
        $rootScope.displayName = $stateParams.name;
        var params = {};
        params.id_contract = decode($stateParams.id);
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        contractService.getContractById(params).then(function(result){
            $scope.contract = result.data[0];
            $scope.contract.contract_start_date = moment($scope.contract.contract_start_date).utcOffset(0, false).toDate();
            $scope.contract.contract_end_date = moment($scope.contract.contract_end_date).utcOffset(0, false).toDate();
            $scope.end_date = $scope.contract.contract_end_date;
            if($scope.contract.can_review==1)
                $scope.enableTemplate = true;
            else $scope.enableTemplate = false;
            $scope.contract['auto_renewal'] = $scope.contract['auto_renewal']==1?1:0;
            $scope.getContractDelegates($scope.contract.business_unit_id,$scope.contract.id_contract);
            angular.forEach($scope.tagsBuilding,function(k,o){
            angular.forEach(k.tag_details,function(i,o){
                if(i.tag_type=='input')$scope.contract.contract_tags[i.tag_id]=$scope.contract.contract_tags[o][i.tag_id];
                else if(i.tag_type!='input'){
                 angular.forEach(i.tag_options,function(item){
                   if(item.id_tag_option==$scope.contract.contract_tags[o][i.tag_id]){
                        $scope.contract.contract_tags[i.tag_id]=item.id_tag_option;
                   }else {}
                 });
               } else{}                        
           });
        });
        });
    }else{
        setTimeout(function(){
            $scope.contract['auto_renewal'] = 1;
        });
    }
    
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
    
    $scope.lock=false;
    $scope.updateLockingStatus = function(id){
        
        $scope.contract.is_template_lock =id;
        if(id){
            $scope.lock= true;
        }
        else{
            $scope.lock=false;
        }
    }

    $scope.resetLockingStatus = function(id){
        
        $scope.contract.is_template_lock =id;
        if(id){
            $scope.lock= false;
        }
        else{
            $scope.lock=true;
        }
    }
    $scope.addContract = function (data1){
        $scope.formDataObj= angular.copy(data1);
        var contract={};
        contract= $scope.formDataObj;
        $scope.grouped_tags = {};
        if(contract.grouped_tags){
            angular.forEach($scope.tagsBuilding, function(k,ko){
            $scope.options = {};
            angular.forEach(k.tag_details, function(i,o){
                $scope.options[o] = {};
                $scope.options[o].tag_id = i.tag_id;
                $scope.options[o].tag_type = i.tag_type;    
                $scope.options[o].multi_select = i.multi_select;  
                $scope.options[o].selected_field = i.selected_field;
                $scope.options[o].selected_field = i.selected_field;              
                if($scope.contract.grouped_tags.feedback !=undefined)
                $scope.options[o].comments = $scope.contract.grouped_tags.feedback[i.tag_id];
                else $scope.options[o].comments = '';

                if(i.tag_type =='date')
                    $scope.options[o].tag_option = dateFilter(data1.grouped_tags[i.tag_id],'yyyy-MM-dd');
                else if(i.tag_type !='date')
                    $scope.options[o].tag_option = data1.grouped_tags[i.tag_id];
                else $scope.options[o].tag_option = '';        
            });
            $scope.grouped_tags[ko] = {};
            $scope.grouped_tags[ko]['tag_details'] = {};
            $scope.grouped_tags[ko]['tag_details'] = $scope.options;
            //contract.grouped_tags[ko]['tag_details'] = $scope.options;

        });
         } 
        contract.grouped_tags =  $scope.grouped_tags;
        contract.created_by = $scope.user.id_user;
        contract.customer_id = $scope.user1.customer_id;
        if(contract.contract_end_date!=null){
            contract.contract_end_date = dateFilter(contract.contract_end_date,'yyyy-MM-dd');
        }
        else{
            contract.contract_end_date='';
        }
        contract.contract_start_date = dateFilter(contract.contract_start_date,'yyyy-MM-dd');
        if($scope.user.access =='bo' || $scope.user.access=='bm')
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
       
        if( params.contract_end_date){}
        if( params.contract_end_data  && moment( params.contract_end_date).utcOffset(0, false).toDate() <= moment( params.contract_start_date).utcOffset(0, false).toDate()){
            alert($filter('translate')('general.alert_start_date_less_end'));
        }else{
            if(contract.id_contract){
                if(moment( params.contract_end_date).utcOffset(0, false).toDate() > moment( params.contract_end_date).utcOffset(0, false).toDate()){
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
                                if(contract.is_workflow=='1')
                                    $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id,wId:encode(contract.id_contract_workflow),type:'workflow'});
                                else
                                    $state.go('app.contract.view',{name:contract.contract_name,id:$stateParams.id,type:'review'});
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
                    url: API_URL+'Contract/add',
                     data: {
                        'file' : $scope.file.attachment,
                        'contract': contract
                     }
                 }).then(function(resp){
                 if(resp.data.status){
                    $state.go('app.contract.all-contracts');
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
            $rootScope.module = 'Contract Details';
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
.controller('contractReviewActionItemCtrl', function($scope, $rootScope, $stateParams,$filter, contractService, decode, $uibModal,dateFilter, userService){
   
   
    if($stateParams.id){
        $scope.reviewList = {};
        $scope.callServer = function (tableState){
            $rootScope.module = 'Contract';
            $rootScope.displayName = $stateParams.name;
            $rootScope.breadcrumbcolor='contract-breadcrumb-color';
            $rootScope.class='Contract-logo';
            $rootScope.icon ='Contracts'; 
            $scope.tableStateRef = tableState;
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            $scope.contract_id = tableState.contract_id  = decode($stateParams.id);
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
                    $scope.callServer($scope.tableStateRef);
                }                
            });
        }
        $scope.createContractReview = function (id) {
            $scope.selectedRow = row;
            $scope.data={};
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/contracts/create-edit-contract-review.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.title = 'general.create';
                    $scope.bottom = 'general.save';
                    $scope.isEdit = false;
                    if (item != 0 &&  item.hasOwnProperty('id_contract_review_action_item')) {
                        $scope.isEdit = true;
                        $scope.submitStatus = true;
                        $scope.data = angular.copy(item);
                        delete $scope.data.comments;
                        $scope.data.due_date = moment($scope.data.due_date).utcOffset(0, false).toDate();
                        $scope.update = true;
                        $scope.title = 'general.edit';
                        $scope.bottom = 'general.update';
                    }else{$scope.data.due_date=moment().utcOffset(0, false).toDate();}
                    contractService.getActionItemResponsibleUsers({'contract_id': $scope.contract_id}).then(function(result){
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
                        //data.due_date = dateFilter(data.due_date,'yyyy-MM-dd');.
                        $scope.due_date=angular.copy(data.due_date);
                        $scope.due_date=dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                        if(data != 0 && data.hasOwnProperty('id_contract_review_action_item')){
                            delete data.comments;
                            params = angular.copy(data);
                            params.updated_by = $scope.user.id_user;
                            params.contract_id = params.id_contract = $scope.contract_id;
                            params.id_user  = $scope.user1.id_user;
                            params.user_role_id  = $scope.user1.user_role_id;
                            params.due_date = $scope.due_date;
                            params.is_workflow = $scope.isWorkflow;
                            params.contract_workflow_id  = decode($stateParams.wId);
                            contractService.addReviewActionItemList(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $scope.callServer($scope.tableStateRef);
                                    $scope.cancel();
                                } else {
                                    $rootScope.toast('Error', result.error,'error');
                                }
                            });
                        }else{
                            params = data ;
                            params.contract_id = $scope.contract_id;
                            params.created_by = $scope.user.id_user;
                            params.contract_id = params.id_contract = $scope.contract_id;
                            params.id_user  = $scope.user1.id_user;
                            params.user_role_id  = $scope.user1.user_role_id;
                            params.due_date = $scope.due_date;
                            params.is_workflow = $scope.isWorkflow;
                            params.contract_workflow_id  = decode($stateParams.wId);
                            contractService.addReviewActionItemList(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
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

    }
})
.controller('contractViewCtrl', function($sce,$timeout,$scope, $translate,$rootScope,$filter,$localStorage, $state, $stateParams,builderService,catalogueService,tagService,documentService,contractService, decode, encode, customerService,attachmentService,businessUnitService, Upload,$location, $uibModal, userService, AuthService,dateFilter,providerService,projectService,masterService,templateService,calenderService,moduleService){

    $scope.app_url = APP_DIR;     //written by ashok
    $rootScope.module = 'Contract Details';
    $rootScope.icon ='Contracts';
    $rootScope.class="contract-logo";
    $rootScope.breadcrumbcolor='contract-breadcrumb-color';
    $scope.currencyList = [];
    $scope.templateList = [];
    $scope.relationshipCategoryList = {};
    $scope.relationshipClassificationList = {};
    $scope.displayCount = $rootScope.userPagination;
    $rootScope.displayName = $stateParams.name;
    $scope.isSubLoading = true;
    $scope.disabled =false;
                    
    $scope.goToViewCatalogue = function () {
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size: 'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/catalogue/view-catalogue-info.html',
            controller: function ($uibModalInstance, $scope, item) {
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

    $scope.dynamicPopover = { templateUrl: 'myPopoverServiceCatologue.html' };
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
                            catalogueService.catalogueList(obj).then(function (result) {
                                $scope.catalogue = result.data[0];
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


    var obj = {};
    obj.action_name = 'view';
    obj.action_description = 'view Contract$$('+$stateParams.name+')';
    obj.module_type = $state.current.activeLink;
    obj.action_url = $location.$$absUrl;
    $rootScope.confirmNavigationForSubmit(obj);
    $scope.spendMgmtGraph ={};
    $scope.spendMgmtGraph.graph ={};
    $scope.spendMgmtGraph.serviceCatalogue ={};
    $scope.spendLineGraph = {};
    $scope.isWorkflow = '0';
    if($stateParams.wId)$scope.workflowId = decode($stateParams.wId);
    if($stateParams.type)$scope.isWorkflow = ($stateParams.type =='workflow')?'1':'0';
    if($stateParams.id){
        $scope.init = function (){
            $scope.contract_id = decode($stateParams.id);
            $scope.contract_id_encoded = $stateParams.id;
            var params = {};
            params.id_contract  = $scope.contract_id;
            params.id_user  = $scope.user1.id_user;
            params.user_role_id  = $scope.user1.user_role_id;
            params.id_contract_workflow  = $scope.workflowId;
            params.id_contract_review  = decode($stateParams.rId);
            params.is_workflow  = $scope.isWorkflow;
            params.customer_id  = $scope.user1.customer_id;
            $scope.loading = false;
            $scope.tagStatus = false;
           
            contractService.getContractById(params).then (function(result){
                if(result.data.length>0){
                    if(result.data[0].contract_end_date=="1970-01-01")
                        result.data[0].contract_end_date='';
                    $scope.contractInfo = result.data[0];                    
                    $scope.data = result.data[0];
                    $scope.reviewWorkflowInfo = {};
                    $scope.reviewWorkflowInfo = result.review_workflow_data;
                    $scope.validation_status = result.validation_status;
                    $scope.ready_for_validation = result.ready_for_validation;
                    $scope.contractInfo.id_contract_review = result.data[0].id_contract_review;
                    $scope.contractParters={};
                    contractService.getstakeholders({'id_contract':decode($stateParams.id)}).then(function (result) {
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
                    if($scope.reviewWorkflowInfo.is_workflow==0){
                        var str = '<div><div class="text-left" style="text-align:left;">Recurrence : '+$scope.reviewWorkflowInfo.recurrenc+' </div><div style="text-align:left;"> Recurrence till : '+ dateFilter($scope.reviewWorkflowInfo.recurrence_till,'MMM dd,yyyy')+'</div></div>';
                        $scope.htmlTooltip = $sce.trustAsHtml(str);
                    }
                    $scope.loading = true;
                    $scope.review_access = true;                           
                    if($scope.contractInfo.reaaer != "itako"){$scope.review_access = false;}
                }else{
                    $state.go('app.contract.contract-overview');
                }
            });

            $scope.createDocumentUploadPdf = function (data) {
                $scope.pdfInfo = data;
                $scope.document_name=data.document_name;
                $scope.uploaduser=data.uploaded_user;
                $scope.uploadOn=data.uploaded_on;
                $scope.id_document=data.id_document;
                var modalInstance = $uibModal.open({
                    animation: true,
                    backdrop: 'static',
                    keyboard: false,
                    scope: $scope,
                    size: 'lg',
                    openedClass: 'right-panel-modal modal-open',
                    templateUrl: 'views/Manage-Users/contracts/addDocument.html',
                    controller: function ($uibModalInstance, $scope) {
                        $scope.title = 'documents.add_new_document';
                        $scope.bottom = 'documents.process';
                        $scope.documentpdf = {};
                        $scope.file = {};
                        contractService.responsibleUserList({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(),'forDocumentIntelligence':'1','type': 'buowner' }).then(function (result) {
                            $scope.buOwnerUsers = result.data;
                        })
    
                        contractService.getDelegates({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(),'forDocumentIntelligence':'1' }).then(function (result) {
                            $scope.delegates = result.data;
                        })
    
                        documentService.getTemplatesList({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                            $scope.templates = result.data;
                        })

                        $scope.addDocumentPdf = function (data1) {
                            var r = confirm($filter('translate')('general.alert_document'));
                            if (r == true) {
                            
                            Upload.upload({
                                url: API_URL + 'Document/createDocumentIntelligence',
                                data: {
                                    'owner_id': data1.owner_id,
                                    'customer_id': $scope.user1.customer_id,
                                    'delegate_id': data1.delegate_id,
                                    'id_intelligence_template': data1.intelligence_template_id,
                                    'document_id':$scope.id_document
                                    // 'document_name':data.document_name,
                                    // 'document_source':data.document_source,
                                }
                            }).then(function (resp) {
                                
                                if (resp.data.status) {
                                    $rootScope.toast('Success', resp.data.message);
                                    $scope.cancel();
                                    $scope.getDocumentIntelligenceList($scope.tableStateRef);
                                    var obj = {};
                                    obj.action_name = 'add';
                                    obj.action_description = 'add$$documentIntelligence$$' + documentpdf.file_name;
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                } else {
                                    $rootScope.toast('Error', resp.data.error);
                                }
                            }, function (resp) {
                                $rootScope.toast('Error', resp.error);
                            }, function (evt) {
                                var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
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

        $scope.activityTags =false;
        $scope.tagsActivity = function(){
            $scope.activityTags =  !$scope.activityTags;
            var parent = document.getElementById("tags-business");
            var parent1 = document.getElementById("business-tags");
            if($scope.activityTags){
                 parent.classList.add('showDivMenu');
                 parent1.className = "fa fa-caret-down";
            }else{
                 parent.classList.remove('showDivMenu');
                 parent1.className = "fa fa-caret-right";
            }           
        }

        $scope.downloadPdf = function (info,type) {
            
            var encryptedPath = info.encryptedPath;
            
                var is_document_intelligence =1; 
                var filePath = API_URL + 'Cron/preview?file=' + encryptedPath +'&is_document_intelligence='+is_document_intelligence ;
            
            encodePath = encode(filePath);
            window.open(window.origin + '/Document/web/preview.html?file=' + encodePath + '#page=1');
        }
            //params.parent_contract_id = $scope.contract_id;          
        tagService.getContractTags({'id_contract':$scope.contract_id,'tag_type':'contract_tags'}).then (function(result){
            if(result.status){
                $scope.tagsInfo=[];
                $scope.tagsInfo = result.data;
            }else {$rootScope.toast('Error',result.error,'error',$scope.contract);}

        });
            $timeout(function () {
                // contractService.getSpendMgmt({'id_contract':decode($stateParams.id)}).then(function (result) {
                //     if (result.status) {
                //         $scope.spendMgmt=result.data[0];
                //         //$scope.spendMgmtGraph.graph = result.graph;
                //     }
                // });
                contractService.getSpendManagementInfo({'id_contract':decode($stateParams.id)}).then(function (result) {
                    if (result.status) {
                        $scope.spendMgmt=result.data[0];
                    }
                });
                contractService.getSpentline({'id_contract':decode($stateParams.id)}).then(function (result) {
                    if (result.status) {
                        $scope.spendLines=result.data;
                        $scope.spendMgmtGraph.graph = result.graph;
                    }
                });
            },200);
        }

        $scope.connectedContracts =[];
        $scope.connectedContractsList =function(){
            projectService.getConnectedContracts({'customer_id':$scope.user1.customer_id,'contract_id':decode($stateParams.id)}).then(function(result){
                if(result.status){
                    $scope.connectedContracts = result.data;
                }
            })
          
        }
        $scope.connectedContractsList();
        $scope.goToContractDetails = function(row){
            var goView = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
           $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract)});                             //written by ashok
            if(row.is_workflow=='1')
                $state.go(goView,{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
            else
                $state.go(goView,{name:row.contract_name,id:encode(row.id_contract),type:'review'});
        }
        $scope.open = function(g){
            if(g.open)
              g.open = false;
            else
              g.open = true;
            return g.open;
        }
        $scope.getChartData = function(){
            $timeout(function () {
                contractService.getSpentline({'id_contract':decode($stateParams.id)}).then(function (result) {
                    if (result.status) {
                        $scope.spendLines=result.data;
                        $scope.spendMgmtGraph.graph = result.graph;
                        $scope.spendMgmtGraph.serviceCatalogue = result.service_catalogue_graph;
                    }
                });
            },100);            
        }


        $scope.reviewworkflowData=function(data){
            $scope.reviewWorkflowInfo='';
            var param ={};
            param.contract_id = data.contract_id;
            contractService.reviewWorkflowInfo(param).then(function(result){
                $scope.reviewWorkflowInfo=result.data;
            });
        }

        $scope.callServerSubContract = function (tableState){
            $scope.tableStateRef = tableState;
            $scope.isSubLoading = true;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            tableState.business_unit_id = $scope.business_unit_id;
            tableState.id_user  = $scope.user1.id_user;
            tableState.user_role_id  = $scope.user1.user_role_id;
            tableState.parent_contract_id  = decode($stateParams.id);           
            contractService.allContractsList(tableState).then (function(result){
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
        $scope.init();
      
    
       
        $scope.deleteAttachment = function(id,name){
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
        $scope.goToEdit = function (){
            $state.go('app.contract.edit-contract',{name:$stateParams.name,id:$stateParams.id});
        }
    $scope.initializeReview = function (val){
        var params ={};
        params.created_by = $scope.user.id_user;
        params.customer_id = $scope.user1.customer_id;
        params.contract_id = decode($stateParams.id);
        params.is_workflow = $scope.isWorkflow;
        // params.workflow_template_id = $scope.contractInfo.workflow_template_id;
        params.id_contract_workflow = $scope.contractInfo.id_contract_workflow;
        params.calender_id = $scope.reviewWorkflowInfo.calender_id;
        if(val == true) params.contract_review_type = ($scope.isWorkflow=='1')?'adhoc_workflow':'adhoc_review';
        contractService.initializeReview(params).then(function(result){
            if(result.status){
                $rootScope.toast('Success', result.message);
                var obj = {};
                obj.action_name = 'initiate';
                if($scope.isWorkflow=='1')obj.action_description = 'initiate$$Task$$('+$stateParams.name+')';
                else obj.action_description = 'initiate$$Review$$('+$stateParams.name+')';
                obj.module_type = $state.current.activeLink;
                obj.action_url = $location.$$absUrl;
                $rootScope.confirmNavigationForSubmit(obj);
                if($scope.isWorkflow=='1'){
                    $state.go(goWorkflow,{name:$stateParams.name,
                                                                id:$stateParams.id,
                                                                rId:encode(result.data),
                                                                wId:$stateParams.wId,type:'workflow'});
                }
                else
                    $state.go(goReview,{name:$stateParams.name,id:$stateParams.id,rId:encode(result.data),type:'review'});
            }else $rootScope.toast('Error', result.error,'error',$scope.user);
        });
    }
   

    $scope.reviewAction = function (tableState){
        //setTimeout(function(){
            $scope.tableStateRef = tableState;
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.contract_id  = $scope.contract_id;
            tableState.id_user  = $scope.user1.id_user;
            tableState.user_role_id  = $scope.user1.user_role_id;
            tableState.action_item_type  = 'outside';
            //tableState.id_contract_review  = $scope.data.id_contract_review;
            contractService.reviewActionItemList(tableState).then (function(result){
                $scope.reviewList = result.data.data;
                $scope.reviewListCount = result.data.count; 
                //$localStorage.curUser.data.filters.actionItemCount = result.data.count;
                $scope.emptyTable=false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords1 = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if(result.data.total_records < 1)
                $scope.emptyTable=true;
            })
            
        //},700);
    }
    
    $scope.defaultPages1 = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.reviewAction($scope.tableStateRef);
            }                
        });
    }
    

    $scope.getTabsInfo = function(){
        var params={};
        params.id_contract = $scope.contract_id;
        params.action_item_type='outside';
        params.id_user= $scope.user1.id_user;
        params.user_role_id = $scope.user1.user_role_id;
        projectService.contractInfoTabs(params).then(function(result){
            $scope.tabs = result.data;
            $scope.reCalcScroll();
        })
    }

    $scope.getTabsInfo();

    $scope.showSubContracts = false;
        $scope.SubContractFunction = function () {
            $scope.showSubContracts = !$scope.showSubContracts;
            var parent = document.getElementById("allSubContracts");
            var parent1 = document.getElementById("arrow-icon-subContracts");
            if ($scope.showSubContracts) {
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                //$scope.widgetinfo();
            } else {
                parent.classList.remove('showDivMenu');
                parent1.className = "fa fa-angle-double-down";
            }
        }
    
    $scope.goToContractReview = function(row){
        if(row.is_workflow=='0' && $rootScope.access !='eu')
            $state.go(goReview,{name:$stateParams.name,id:$stateParams.id,rId:encode(row.id_contract_review),type:'review'});
        if(row.is_workflow =='1' && $rootScope.access !='eu'){
            $state.go(goWorkflow,{name:$stateParams.name,id:$stateParams.id,rId:encode(row.id_contract_review),wId:$stateParams.wId,type:'workflow'});
        }
        if(row.is_workflow=='0' && $rootScope.access == 'eu'){
            $state.go('app.contract.contract-review11',{name:$stateParams.name,id:$stateParams.id,rId:encode(row.id_contract_review),type:'review'});
        }
        if(row.is_workflow=='1' && $rootScope.access =='eu'){
            $state.go('app.contract.contract-workflow11',{name:$stateParams.name,id:$stateParams.id,rId:encode(row.id_contract_review),wId:$stateParams.wId,type:'workflow'});
        }
    }
   
   
    $scope.goToContractLogs = function(id){
        var goWorkflowLog = (parentPage == 'all-activities')?'app.contract.workflow-log':'app.contract.workflow-log1';
        var goReviewLog = (parentPage == 'all-activities')?'app.contract.contract-log':'app.contract.contract-log1';
        if($scope.isWorkflow=='1')
            $state.go(goWorkflowLog,{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
        else
            $state.go(goReviewLog,{name:$stateParams.name,id:$stateParams.id,type:'review'});
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
            templateUrl: 'create-edit-contract-doc.html',
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
                                module_id: data.id_contract_review,
                                module_type: 'contract_review',
                                is_workflow:$scope.isWorkflow,
                                reference_id: decode($stateParams.id),
                                reference_type: 'contract',
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
                                module_id: data.id_contract_review,
                                module_type: 'contract_review',
                                is_workflow:$scope.isWorkflow,
                                reference_id: decode($stateParams.id),
                                reference_type: 'contract',
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
                    // $scope.data.due_date = moment($scope.data.due_date).utcOffset(0, false).toDate();
                    $scope.title = '';
                    $scope.update = true;
                    $scope.bottom = 'general.update';
                }else{$scope.data.due_date=moment().utcOffset(0, false).toDate();}
                if($scope.type == 'view')
                    $scope.bottom = 'contract.finish';

               
                var respUserParams = {};
                respUserParams.contract_review_id  = $scope.data.contract_review_id?$scope.data.contract_review_id:'0';
                respUserParams.contract_id  = decode($stateParams.id);
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
                        params.reference_type ='contract';
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
                            params.reference_type ='contract';
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
    
    $scope.removeSubAgreement=function(id){
        var r = confirm($filter('translate')('general.alert_remove_contract_subagree'));
        if(r==true){
        contractService.removeSub({id_contract:id.id_contract}).then(function(result){
            if(result.status){
                $rootScope.toast('Success', result.message);
                $scope.callServerSubContract($scope.tableStateRef);
                $scope.getTabsInfo();
            }else{
                $rootScope.toast('Error', result.error,'error');
            }
         });
        }
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
                    $scope.getTabsInfo();
                }else $rootScope.toast('Error', result.error, 'error',$scope.user);
            });
        }
    }
    
    $scope.goToDashboard = function (data) {
        var goWorkflowDashboard = (parentPage == 'all-activities')?'app.contract.workflow-dashboard':'app.contract.workflow-dashboard1';
        var goReviewDashboard = (parentPage == 'all-activities')?'app.contract.contract-dashboard':'app.contract.contract-dashboard1';
        if(data.is_workflow=='1'){
            $state.go(goWorkflowDashboard,{name:$stateParams.name,id:$stateParams.id,rId:encode($scope.contractInfo.id_contract_review),wId:$stateParams.wId,type:'workflow'});
        }
        else{
            $state.go(goReviewDashboard,{name:$stateParams.name,id:$stateParams.id,rId:encode($scope.contractInfo.id_contract_review),type:'review'});
        }
    }
    $scope.goToDesign = function(data){
        var goWorkflowDesign = (parentPage == 'all-activities')?'app.contract.workflow-design':'app.contract.workflow-design1';
        var goReviewDesign = (parentPage == 'all-activities')?'app.contract.review-design':'app.contract.review-design1';
        
        if($scope.isWorkflow=='1' && $rootScope.access!='eu')
            $state.go(goWorkflowDesign,{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        if($scope.isWorkflow=='0' && $rootScope.access!='eu')
            $state.go(goReviewDesign,{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});
        if($scope.isWorkflow=='1' && $rootScope.access =='eu')
            $state.go('app.contract.workflow-design11233',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        if($scope.isWorkflow=='0' && $rootScope.access =='eu')
            $state.go('app.contract.review-design12334',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});
    }
    $scope.myFunction=function() { 
        if((navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) != -1 ) 
       {
           $scope.browser ='Opera';
       }
       else if(navigator.userAgent.indexOf("Chrome") != -1 )
       {
            $scope.browser ='Chrome';
       }
       else if(navigator.userAgent.indexOf("Safari") != -1)
       {
            $scope.browser ='Safari';
       }
       else if(navigator.userAgent.indexOf("Firefox") != -1 ) 
       {
            $scope.browser ='Firefox';
       }
       else if((navigator.userAgent.indexOf("MSIE") != -1 ) || (!!document.documentMode == true )) //IF IE > 10
       {
            $scope.browser ='IE'; 
       }  
       else 
       {
        $scope.browser ='unknown';
       }
       }
    $scope.myFunction();

  

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
            else{
                $rootScope.toast('Error',result.error);
            }
        }
        );
    }
    
};

      
    
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
                contractService.getstakeholders({'id_contract':decode($stateParams.id)}).then(function (result) {
                    if (result.status) {
                        $scope.contractParters=result.data;
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
                    reqObj.id_provider = $scope.contractInfo.provider_name;
                    reqObj.user_role_id = $scope.user1.user_role_id;
                    reqObj.id_user = $scope.user1.id_user;                    
                    customerService.getUserList(reqObj).then(function (result){
                        $scope.providerUsersList = result.data.data;                    
                    });
                }              
                var params ={};
                $scope.update=function(data){
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
                    params.id_contract  = decode($stateParams.id);
                    contractService.addSponsers(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'Create';
                            obj.action_description = 'Create$$Sponsor$$Information$$('+data.action_item+')';
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
                        $scope.contractParters.data[id].provider = $scope.contractInfo.provider_name_show;
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
    $scope.updateSpendManagement = function () {
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'create-edit-spend-management.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.update = false;
                $scope.title = 'general.update';
                $scope.bottom = 'general.save';
                $scope.isEdit = false;
                var params ={};
                $scope.spendLine = {};
                // contractService.getSpendMgmt({'id_contract':decode($stateParams.id)}).then(function (result) {
                contractService.getSpendManagementInfo({'id_contract':decode($stateParams.id)}).then(function (result) {
                    if (result.status) {
                        $scope.contractInfo=result.data[0];
                    }
                });
                $scope.update=function(data){
                    params=data;
                    params.updated_by = $scope.user.id_user;
                    params.id_contract  = decode($stateParams.id);
                    contractService.updateSpendMgmt(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'Update';
                            obj.action_description = 'Update$$Spend$$Lines$$('+data.action_item+')';
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

    $scope.updateSpendLines = function (row) {
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'create-edit-spend-lines.html',
            controller: function ($uibModalInstance, $scope,item) {
                $scope.title = 'general.add';
                $scope.bottom = 'general.update';
                $scope.file={};
                $scope.links_delete = [];
                $scope.contractLinks = [];
                $scope.contractLink={};


                $scope.getSpendInfo = function(){
                    contractService.getSpentline({'spent_line_id':row.id,'id_contract':decode($stateParams.id)}).then(function (result) {
                        if (result.status) {
                            $scope.spendInfo=result.data[0];
                            if($scope.spendInfo.from_date){
                            $scope.spendInfo.from_date = moment($scope.spendInfo.from_date).utcOffset(0, false).toDate();
                            }
                            $scope.spendInfo.to_date = moment($scope.spendInfo.to_date).utcOffset(0, false).toDate()
                        }
                    });
                }
                $scope.getSpendInfo();


                $scope.getOnlyEvidences = function(){
                    contractService.getOnlyEvidences({'spent_line_id':row.id}).then(function (result) {
                        if (result.status) {
                            $scope.spendInfo1=result.data[0];
                        }
                    });
                }
                $scope.getOnlyEvidences();
                $scope.spendInfo = {};
                if($scope.spendInfo.length>0){
                    $scope.spendInfo.spent_period=$scope.spendInfo[0].spent_period;
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

                $scope.update=function(obj){
                    var data = angular.copy(obj);
                    if(data.spent_amount==undefined){
                        $rootScope.toast('Error', 'Spend Amount is required');
                        $scope.init();
                    }else{
                        data.from_date = dateFilter(data.from_date,'yyyy-MM-dd');
                        data.to_date = dateFilter(data.to_date,'yyyy-MM-dd');
                        params=data;
                        params.updated_by = $scope.user.id_user;
                        params.id_contract  = decode($stateParams.id);
                        contractService.updateSpendLine(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Spend$$Information$$('+data.action_item+')';
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
                }

               
                $scope.addViewSpendAttachments = function(row) {
                    //$scope.info = row;
                        $scope.contractLinks = [];
                        $scope.contractLink={};
                        $scope.selectedRow = row;
                        var modalInstance = $uibModal.open({
                            animation: true,
                            backdrop: 'static',
                            keyboard: false,
                            scope: $scope,
                            openedClass: 'right-panel-modal modal-open',
                            templateUrl: 'views/Manage-Users/contracts/spend-view-attachments.html',
                            controller: function ($uibModalInstance, $scope, item) {
                                $scope.update = false;
                                $scope.bottom = 'general.save';
                                $scope.isEdit = false;
                                  
                                $scope.cancel = function () {
                                    $uibModalInstance.close();
                                };
                                $scope.deletespendAttachment = function(id,name){
                                    var r=confirm($filter('translate')('general.alert_continue'));
                                    $scope.deleConfirm = r;
                                    if(r==true){
                                        var params = {};
                                        params.id_document = id;
                                        attachmentService.deleteAttachments(params).then (function(result){
                                            $rootScope.toast('Success',result.data.message);
                                            var obj = {};
                                            obj.action_name = 'delete';
                                            obj.action_description = 'delete$$module$$question$$attachement$$('+name+')';
                                            obj.module_type = $state.current.activeLink;
                                            obj.action_url = $location.$$absUrl;
                                            $rootScope.confirmNavigationForSubmit(obj);
                                            $scope.getSpendInfo(); 
                                            $scope.getOnlyEvidences();  
                                            $scope.callspentCount();                                                         
                                        })
                                    }
                                }
                                var params ={};
                                $scope.addSpendAttachemts=function(data){
                                    var file = data;
                                    if(file){
                                        Upload.upload({
                                            url: API_URL+'Document/add',
                                            data:{
                                                file:file,
                                                customer_id: $scope.user1.customer_id,
                                                module_id: decode($stateParams.id),
                                                module_type: 'contract',
                                                reference_id: row.id,
                                                reference_type: 'spent_lines',
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
                                                $scope.getOnlyEvidences();
                                                $scope.callspentCount(); 
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
                                $scope.verifySpendLink = function(data){
                                    if(data !={}){
                                        $scope.contractLinks.push(data);
                                        $scope.contractLink={};
                                    }
                                }
                                $scope.removespendLink = function(index){
                                    var r=confirm($filter('translate')('general.alert_continue'));
                                    if(r==true){
                                        $scope.contractLinks.splice(index, 1);
                                    }                    
                                }
                                $scope.uploadSpendLinks = function (contractLinks) {
                                    var file = contractLinks;
                                    if(contractLinks){
                                        Upload.upload({
                                            url: API_URL+'Document/add',
                                            data:{
                                                file:contractLinks,
                                                customer_id: $scope.user1.customer_id,
                                                module_id: decode($stateParams.id),
                                                module_type: 'contract',
                                                reference_id: row.id,
                                                document_type:1,
                                                reference_type: 'spent_lines',
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
                                                $scope.getOnlyEvidences();
                                                $scope.callspentCount(); 
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

    $scope.addSpendLines = function () {
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            // templateUrl: 'create-edit-spend-lines.html',
            templateUrl: 'views/Manage-Users/contracts/spend-attachments.html',
            controller: function ($uibModalInstance, $scope,item) {
                $scope.title = 'general.add';
                $scope.bottom = 'general.save';
                $scope.file={};
                $scope.links_delete = [];
               $scope.contractLinks = [];
                $scope.contractLink={};
                var params ={};                
           

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

                // $scope.update=function(obj){
                //     var data = angular.copy(obj);
                //     if(data.spent_amount==undefined){
                //         $rootScope.toast('Error', 'Spend Amount is required');
                //         $scope.init();
                //     }else{
                //         data.from_date = dateFilter(data.from_date,'yyyy-MM-dd');
                //         data.to_date = dateFilter(data.to_date,'yyyy-MM-dd');
                //         params=data;
                //         params.updated_by = $scope.user.id_user;
                //         params.id_contract  = decode($stateParams.id);
                //         contractService.updateSpendLine(params).then(function (result) {
                //             if (result.status) {
                //                 $rootScope.toast('Success', result.message);
                //                 var obj = {};
                //                 obj.action_name = 'Update';
                //                 obj.action_description = 'Update$$Spend$$Information$$('+data.action_item+')';
                //                 obj.module_type = $state.current.activeLink;
                //                 obj.action_url = $location.$$absUrl;
                //                 $rootScope.confirmNavigationForSubmit(obj);
                //                 $scope.init();
                //                 //$scope.cancel();
                //             } else {
                //                 $rootScope.toast('Error', result.error,'error');
                //             }
                //         });
                //     }                
                // }

                $scope.update = function (data1){
                    if(data1.spent_amount==undefined){
                        $rootScope.toast('Error', 'Spend Amount is required');
                       $scope.init();
                  }
                  else{
                    $scope.formDataObj= angular.copy(data1);
                    var contract={};
                    contract= $scope.formDataObj;
                    contract.created_by = $scope.user.id_user;
                    contract.customer_id = $scope.user1.customer_id;
                    contract.from_date = dateFilter(contract.from_date,'yyyy-MM-dd');
                    contract.to_date = dateFilter(contract.to_date,'yyyy-MM-dd');
                    contract.id_contract = decode($stateParams.id);
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
                
                             Upload.upload({
                                url: API_URL+'Contract/spentline',
                                 data: {
                                    'file' : $scope.file.attachment,
                                    'contract': contract
                                 }
                             }).then(function(resp){
                             if(resp.data.status){
                               
                                $rootScope.toast('Success',resp.data.message);
                                $uibModalInstance.close();
                                 var obj = {};
                                 obj.action_name = 'add';
                                 obj.action_description = 'add$$contract$$'+contract.contract_name;
                                 obj.module_type = $state.current.activeLink;
                                 obj.action_url = $location.$$absUrl;
                                 $rootScope.confirmNavigationForSubmit(obj);
                                 $scope.callspentCount();
                                 $scope.getChartData();
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
    $scope.deleteSpentLine = function (row) {
        var r=confirm($filter('translate')('general.alert_Delete'));
        if(r==true){
            var params ={}; 
            params=row;
            params.updated_by = $scope.user.id_user;
            params.status = 0;
            params.id_contract  = decode($stateParams.id);
            contractService.updateSpendLine(params).then(function (result) {
                if (result.status) {
                    $rootScope.toast('Success', 'Deleted successfully');
                    contractService.getSpentline({'id_contract':decode($stateParams.id)}).then(function (result) {
                        if (result.status) {
                            $scope.spendLines=result.data;
                            $scope.spendMgmtGraph.graph = result.graph;
                        }
                    });
                    var obj = {};
                    obj.action_name = 'Delete';
                    obj.action_description = 'Delete$$Spend$$Information$$('+data.action_item+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                } else {
                    $rootScope.toast('Error', result.error,'error');
                }
            });
        }        
    }

    $scope.callspentCount = function(){
        contractService.getSpentline({'id_contract':decode($stateParams.id)}).then(function (result) {
            if (result.status) {
                $scope.spendLines=result.data;
            }
        });

    }
    $scope.callspentCount();

    $scope.viewSpendAttachments = function(row) {
        //$scope.info = row;
            $scope.contractLinks = [];
            $scope.contractLink={};
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/contracts/spend-view-attachments.html',
                //templateUrl: 'views/Manage-Users/contracts/question-level-attachments.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.bottom = 'general.save';
                    $scope.isEdit = false;

                    $scope.getSpendInfoData = function(){
                        contractService.getSpentline({'spent_line_id':row.id,'id_contract':decode($stateParams.id)}).then(function (result) {
                            if (result.status) {
                                $scope.spendInfo=result.data[0];
                            }
                        });
                    }
                    $scope.getSpendInfoData();
                      

                    $scope.getOnlyEvidences = function(){
                        contractService.getOnlyEvidences({'spent_line_id':row.id}).then(function (result) {
                            if (result.status) {
                                $scope.spendInfo1=result.data[0];
                            }
                        });
                    }
                    $scope.getOnlyEvidences();

                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.deletespendAttachment = function(id,name){
                        var r=confirm($filter('translate')('general.alert_continue'));
                        $scope.deleConfirm = r;
                        if(r==true){
                            var params = {};
                            params.id_document = id;
                            attachmentService.deleteAttachments(params).then (function(result){
                                $rootScope.toast('Success',result.data.message);
                                var obj = {};
                                obj.action_name = 'delete';
                                obj.action_description = 'delete$$module$$question$$attachement$$('+name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.callspentCount(); 
                                $scope.getOnlyEvidences();
                                $scope.getSpendInfoData();
                                
                            })
                        }
                    }
                    var params ={};
                    $scope.addSpendAttachemts=function(data){
                        var file = data;
                        if(file){
                            Upload.upload({
                                url: API_URL+'Document/add',
                                data:{
                                    file:file,
                                    customer_id: $scope.user1.customer_id,
                                    module_id: decode($stateParams.id),
                                    module_type: 'contract',
                                    reference_id: row.id,
                                    reference_type: 'spent_lines',
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
                                    $scope.callspentCount();
                                    $scope.getOnlyEvidences();
                                    $scope.getSpendInfoData();
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
                    $scope.verifySpendLink = function(data){
                        if(data !={}){
                            $scope.contractLinks.push(data);
                            $scope.contractLink={};
                        }
                    }
                    $scope.removespendLink = function(index){
                        var r=confirm($filter('translate')('general.alert_continue'));
                        if(r==true){
                            $scope.contractLinks.splice(index, 1);
                        }                    
                    }
                    $scope.uploadSpendLinks = function (contractLinks) {
                        var file = contractLinks;
                        if(contractLinks){
                            Upload.upload({
                                url: API_URL+'Document/add',
                                data:{
                                    file:contractLinks,
                                    customer_id: $scope.user1.customer_id,
                                    module_id: decode($stateParams.id),
                                    module_type: 'contract',
                                    reference_id: row.id,
                                    document_type:1,
                                    reference_type: 'spent_lines',
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
                                    $scope.callspentCount();
                                    $scope.getOnlyEvidences();
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
            templateUrl: 'views/Manage-Users/contracts/create-edit-contract-review.html',
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
                    $scope.data.due_date=moment().utcOffset(0, false).toDate();;
                }
                $scope.getActionItemById = function(id){
                    contractService.getActionItemDetails({'id_contract_review_action_item':id}).then(function(result){
                        $scope.data = result.data[0];
                        $scope.data.due_date = moment($scope.data.due_date).utcOffset(0, false).toDate();
                    });
                }
                if($scope.type == 'view') $scope.bottom = 'contract.finish';
                contractService.getActionItemResponsibleUsers({'contract_id': $scope.contract_id,'contract_review_id': $scope.contractInfo.id_contract_review}).then(function(result){
                    $scope.userList = result.data;
                });
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
                $scope.goToEdit = function(data){
                    $scope.data.due_date = moment(data.due_date).utcOffset(0, false).toDate();;
                }
                var params ={};
                $scope.addReviewActionItem=function(data){
                    $scope.due_date=angular.copy(data.due_date);
                    $scope.due_date=dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                    
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
                        params.reference_type ='contract';
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
                                        $scope.serviceCatelogue($scope.tableStateRef);
                                        $scope.getTabsInfo();
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
                                    $scope.serviceCatelogue($scope.tableStateRef);
                                    $scope.cancel();
                                    $scope.getTabsInfo();
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
                        params.updated_by = $scope.user.id_user;
                        params.contract_id = params.id_contract = $scope.contract_id;
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
                                $scope.serviceCatelogue($scope.tableStateRef);
                                $scope.cancel();
                                $scope.getTabsInfo();
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
                        params.contract_id = $scope.contract_id;
                        params.created_by = $scope.user.id_user;
                        params.contract_id = params.id_contract = $scope.contract_id;
                        params.id_user  = $scope.user1.id_user;
                        params.user_role_id  = $scope.user1.user_role_id;
                        params.due_date  = $scope.due_date;
                        params.reference_type ='contract';
                        
                        contractService.addReviewActionItemList(params).then(function (result) {
                            if(result.status){
                                $rootScope.toast('Success', result.message);
                                $scope.reviewAction($scope.tableStateRef);
                                $scope.getTabsInfo();
                                $scope.cancel();
                                if(data.id_contract_review_action_item){
                                    $scope.getActionItemById(data.id_contract_review_action_item);
                                }
                                else{
                                    $scope.cancel();
                                    $scope.getTabsInfo();
                                }
                                var obj = {};
                                obj.action_name = 'add';
                                obj.action_description = 'add$$Action$$Item$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                            }
                            else{
                                $rootScope.toast('Error', result.error,'error');
                            }
                            // if (result.status) {
                            //     $rootScope.toast('Success', result.message);
                            //     var obj = {};
                            //     obj.action_name = 'add';
                            //     obj.action_description = 'add$$Action$$Item$$('+data.action_item+')';
                            //     obj.module_type = $state.current.activeLink;
                            //     obj.action_url = $location.$$absUrl;
                            //     $rootScope.confirmNavigationForSubmit(obj);
                            //     if(data.id_contract_review_action_item){
                            //         $scope.getActionItemById(data.id_contract_review_action_item);
                            //         $scope.getTabsInfo();
                            //         $scope.cancel();
                            //     }else{
                            //         $scope.cancel();}
                            //         $scope.getTabsInfo();
                            //     $scope.reviewAction($scope.tableStateRef);
                            //     //$scope.serviceCatelogue($scope.tableStateRef);
                            // } else {
                            //     $rootScope.toast('Error', result.error,'error');
                            // }
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
                        $scope.side_by_side=result.side_by_side_validation     
                    }else $rootScope.toast('Error', result.error, 'error',$scope.user);
                });


                $scope.previewfeedback=function(row) { 
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


                $scope.goToReview = function (row,topic) {
                    if($stateParams.rId)var reviewId = decode($stateParams.rId);
                    var moduleId = item.id_module;
                    var module_name = item.module_name;
                    var topic_name = topic.topic_name;
                    var topic_id = encode(topic.id_topic);
                    
                    var goWorkflowMdule = (parentPage == 'all-activities') ? 'app.contract.contract-module-workflow' : 'app.contract.contract-module-workflow1';
                    var goReviewMdule = (parentPage == 'all-activities') ? 'app.contract.contract-module-review' : 'app.contract.contract-module-review1';
                    if($scope.isWorkflow=='1'){
                        $state.go(goWorkflowMdule,
                            {name:$stateParams.name,id:$stateParams.id,rId:encode(row.contract_review_id),mName:module_name,
                            moduleId:encode(moduleId),tName:topic_name,tId:topic_id,qId:encode(row.id_question),
                            wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
                    }else{
                        $state.go(goReviewMdule,
                        {name:$stateParams.name,id:$stateParams.id,rId:encode(row.contract_review_id),mName:module_name,
                            moduleId:encode(moduleId),tName:topic_name,tId:topic_id,qId:encode(row.id_question),type:'review'},{ reload: true, inherit: false });
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

    $scope.storedModulesList =[];     
    $scope.getStoredModules = function(){
        var params ={};  
        params.contract_id  = decode($stateParams.id);
        contractService.getStoredModules(params).then(function(result){
            $scope.storedModulesList = result.data;
            angular.forEach($scope.storedModulesList.workflow,function(o,i){
                if(o.date)o.date=moment(o.date).utcOffset(0, false).toDate();
            })
        });
    }
    $scope.getStoredModules();
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
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    }
    $scope.showStoredModuleQuestions= function(row){
        $scope.openUnAnswered(row,true);
    }

    $scope.createSubContract = function(){
        if($scope.isWorkflow=='1')
            $state.go('app.contract.create-sub-contract', {name:$scope.contractInfo.contract_name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
        else
            $state.go('app.contract.create-sub-contract', {name:$scope.contractInfo.contract_name,id:$stateParams.id,type:'review'});
    }



    $scope.addExistingSubContracts = function () {
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
                    $scope.callServer($scope.tableStateRef);
                }
                
                //written by ashok from 1065 to 1134
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
                $scope.hierarchy='single';
                $scope.callServer = function (tableState){
                    $scope.filtersData = {};        
                    $scope.isLoading = true;
                    var pagination = tableState.pagination;
                    tableState.customer_id = $scope.user1.customer_id;
                    tableState.id_user  = $scope.user1.id_user;
                    tableState.user_role_id  = $scope.user1.user_role_id;
                    tableState.can_access  = 0;
                    tableState.hierarchy= $scope.hierarchy;
                    
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
              
                $scope.addedSubContractsList=function(data,ctninfo){
                    var params={};
                    params.parent_contract_id=ctninfo.id_contract;                    ;
                    params.child_contract_id=data.contract_id;
                    contractService.childMapContracts(params).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success',result.message);
                            delete $scope.tableStateRef.hierarchy;
                            delete $scope.tableStateRef.sort;
                            delete $scope.tableStateRef.search;
                            $scope.tableStateRef.pagination={};
                            $scope.tableStateRef.pagination.start='0';
                            $scope.tableStateRef.pagination.number='10';
                            $scope.callServerSubContract($scope.tableStateRef);
                            $scope.getTabsInfo();
                            $scope.cancel();
                            $scope.showSubContracts = true;
                        }
                        else{
                            $rootScope.toast('Error',result.error);
                        } 
                    });
                }
                $scope.addedContractsList = function(row){
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

    $scope.shiftToNewDetails = function(item) {
        var obj ={};
        obj.id_contract = decode($stateParams.id);
        obj.contract_name = $stateParams.name;
        obj.id_contract_review = item.id_contract_review;
        $scope.goToReviewWorkflow(item,obj);
    }
    $scope.goToReviewWorkflow = function(type,row){
        
        var goWorkflow = (parentPage == 'all-activities') ? 'app.contract.contract-workflow' : 'app.contract.contract-workflow1';
        var goToView = (parentPage == 'all-activities') ? 'app.contract.view1' : 'app.contract.view';
        var goToReview = (parentPage == 'all-activities') ? 'app.contract.contract-review' : 'app.contract.contract-review1';
        if(type.is_workflow==1){
            if(type.initiated){
                $state.go(goWorkflow,{name:row.contract_name,
                    id:encode(row.id_contract),
                    rId:encode(type.id_contract_review),
                    wId:encode(type.id_contract_workflow),
                    type:'workflow'});
            }else{
                $state.go(goToView,
                        {name:row.contract_name,id:encode(row.id_contract),wId:encode(type.id_contract_workflow),type:'workflow'}, { reload: true, inherit: false });
            }
        }else{
            if(type.id_contract_review && type.initiated)
                $state.go(goToReview,{name:row.contract_name,id:encode(row.id_contract),rId:encode(type.id_contract_review),type:'review'});
            else
                $state.go(goToView,{name:row.contract_name,id:encode(row.id_contract),type:'review'});
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
                    angular.forEach(i.tag_details,function(j,o){                   
                    if(j.tag_type=='date'){
                        var d = (j.tag_answer)?moment(j.tag_answer).utcOffset(0, false).toDate():'';
                        j.tag_answer = d;
                    }
                    else{}
                });
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
    $scope.updateContractDetails =function (obj,val){
       console.log('v',val);
        $scope.info=val;
        if(val==1){
            $scope.templateUrl ='views/Manage-Users/contracts/update-contract-info-modal.html';
        }
        if(val==2){
            $scope.templateUrl ='views/Manage-Users/contracts/update-tags-modal.html';
        }
        if(val==3){
            $scope.templateUrl ='views/Manage-Users/contracts/update-contract-info-modal.html';
        }
        if(val==4){
            $scope.templateUrl ='views/Manage-Users/contracts/update-tags-modal.html';
        }
        if(val==5){
            $scope.templateUrl ='views/Manage-Users/contracts/update-tags-modal.html';
        }

        
        $scope.selectedRow = obj;
        var modalInstance = $uibModal.open({
            nimation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal  contract-list-popup modal-open',
            templateUrl: $scope.templateUrl,
            controller: function ($uibModalInstance, $scope,item) {
                $scope.bottom = 'general.update';
                $scope.fdata = {};
                $scope.isView = false;
                $scope.isLink = false;
                $scope.contractParters={};
                $scope.contractLinks=[];
                $scope.contractLink={};

                $scope.getCounts =function(){
                    contractService.getTabsCount({'id_contract':$scope.contract_id}).then(function(result){
                        $scope.contract_attachments = result.data.contract_attachments;
                        $scope.contract_information =result.data.contract_information;
                        $scope.contract_tags = result.data.contract_tags;
                        $scope.contract_spent_managment = result.data.contract_spent_managment;
                        $scope.contract_stake_holder=result.data.contract_stake_holder;
                    })
                }
                $scope.getCounts();
                if($scope.info===1){ //contractInfoUpdate
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

                    $scope.getInfo = function(){
                        var par = {};
                        par.id_contract  = $scope.contract_id;
                        par.id_user  = $scope.user1.id_user;
                        par.user_role_id  = $scope.user1.user_role_id;
                        par.id_contract_workflow  = $scope.workflowId;
                        par.id_contract_review  = decode($stateParams.rId);
                        par.is_workflow  = $scope.isWorkflow;
                        contractService.getContractById(par).then (function(result){
                            if(result.status){
                                $scope.infoObj = result.data[0];
                                if($scope.infoObj.is_template_lock ==1){
                                    $scope.lock = true;
                                }
                                else{
                                    $scope.lock=false;
                                }
                                
                                $scope.infoObj.contract_start_date=moment($scope.infoObj.contract_start_date).utcOffset(0, false).toDate();
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
                        contractService.getbuOwnerUsers(params).then(function(result){
                            $scope.buOwnerUsers = result.data;
                        });
                    } 

                    $scope.updateLockingStatus = function(id){
                        $scope.infoObj.is_template_lock =id;
                        if(id){
                            $scope.lock= true;
                        }
                        else{
                            $scope.lock=false;
                        }
                    }
                
                    $scope.resetLockingStatus = function(id){
                        $scope.infoObj.is_template_lock =id;
                        if(id){
                            $scope.lock= false;
                        }
                        else{
                            $scope.lock=true;
                        }
                    }
                    $scope.updateContractInfo = function(data){
                        var postData = angular.copy(data);
                        delete postData.contract_unique_id;
                        postData.contract_start_date = dateFilter(data.contract_start_date,'yyyy-MM-dd');
                        postData.contract_end_date = dateFilter(data.contract_end_date,'yyyy-MM-dd');
                        postData.customer_id=$scope.user1.customer_id;
                        postData.updated_by = $scope.user.id_user;
                        Upload.upload({
                            url: API_URL+'Contract/update',
                            data: {
                                'contract': postData
                            }
                        })
                       .then(function(resp){
                            if(resp.data.status){
                                $rootScope.toast('Success',resp.data.message);
                                var obj = {};
                                obj.action_name = 'update';
                                obj.action_description = 'update$$contract$$'+postData.contract_name;
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.init();
                                $scope.getInfo();
                                $scope.getCounts();
                            }else{
                                $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                            }
                        },function(resp){
                            $rootScope.toast('Error',resp.error);
                        });
                    }

                    masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                        $scope.currencyList = result.data;
                    });
                    contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                        $scope.relationshipCategoryList = result.drop_down;
                    });

                    templateService.list().then(function (result){
                        $scope.templateList=result.data.data;
                    });
                    catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
                        $scope.selectedInfoProvider = result.data;
                    });
                }

                if($scope.info==2){ //tags Update
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
                    $scope.tagsData = function(){
                        tagService.getContractTags({'id_contract':$scope.contract_id,'tag_type':'contract_tags'}).then (function(result){
                            if(result.status){
                                $scope.tagsInfo=[];
                                $scope.tagsInfo = result.data;
                                angular.forEach($scope.tagsInfo,function(i,o){
                                    angular.forEach(i.tag_details,function(j,o){
                                    if(j.tag_type=='date'){
                                        j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                      
                                        }
                                    })
                                })
                
                            }else {$rootScope.toast('Error',result.error,'error',$scope.contract);}
            
                        });
                    }
                    $scope.tagsOptions = {
                        minDate: moment().utcOffset(0, false).toDate(),
                        showWeeks: false
                    };
                    $scope.tagsData(); 
                    $scope.updateTags = function(data){
                       var params ={};
                         params.id_contract = $scope.contract_id;
                         params.tag_type = 'contract_tags';
                         angular.forEach(data,function(i,o){
                             angular.forEach(i.tag_details,function(j,o){
                             if(j.tag_type=='date'){
                                 j.tag_answer = dateFilter(j.tag_answer,'yyyy-MM-dd');
                             }
                         });
                     });
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
                                 $scope.getCounts();
                                 $scope.tagsData();
                                 $scope.init();
                                 angular.forEach($scope.tagsInfo,function(i,o){
                                     angular.forEach(i.tag_details,function(j,o){
                                         if(j.tag_type=='date'){
                                             j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                             }
                                         })
                                 })
                                 
                             } else {
                                 $rootScope.toast('Error', result.error,'error');
                             }
                        });
                    }
                    
                }

                if($scope.info==3){ //projected Value
                    //  console.log('entered');
                    $scope.projectContractValue=$scope.contractInfo.contract_value_period;
                    $scope.validateRecurrence =function(selectedDate){
                        //console.log('sd',selectedDate);
                        $scope.options1 = {};
                        var dt = angular.copy((selectedDate) ? selectedDate : moment().utcOffset(0, false).toDate());
                        $scope.options1 = {
                            minDate: dt,
                            showWeeks: false
                        };
                    }
                    $scope.options = {
                        minDate: new Date(),
                        showWeeks: false
                    };
                    $scope.options1 = angular.copy($scope.options);
                     $scope.getValue = function(val,data){
                        //console.log('v',val);
                        if(val!=null){
                            $scope.hideLabel = true;
                        }
                        $scope.contractInfo.contract_value = 0;
                        data.forEach(item => {
                            delete item.id;
                            delete item.type;
                            if(item.amount>0){
                                $scope.contractInfo.contract_value += parseInt(item.amount);
                            }
                        });
                        //console.log('onch',$scope.contractInfo.contract_value);
                     }
    
                     $scope.spendLine = {};
                     $scope.callSpendInfo = function(){
                        // contractService.getSpendMgmt({'id_contract':decode($stateParams.id)}).then(function (result) {
                        contractService.getSpendManagementInfo({'id_contract':decode($stateParams.id)}).then(function (result) {
                            if (result.status) {
                                $scope.contractInfo=result.data[0];
                                if($scope.contractInfo.contract_value_period==='budget') $scope.hideLabel =true;
                                $scope.choices = $scope.contractInfo.contract_budget_data;
                                angular.forEach($scope.choices,function(obj,o){
                                   //console.log('i',obj)
                                   if(obj.from_date)obj.from_date= moment(obj.from_date).utcOffset(0, false).toDate();
                                   if(obj.to_date)obj.to_date= moment(obj.to_date).utcOffset(0, false).toDate();
                                })
                               $scope.addNewChoice = function() {
                                   var newItemNo = $scope.choices.length+1;
                                   $scope.choices.push({'id':'choice'+newItemNo,'type':'new'});
                               };
                               $scope.removeChoice = function(index,info) {
                                    $scope.choices.splice(index,1);
                                    $scope.contractInfo.contract_value = 0;
                                    info.forEach(item => {
                                        if(item.amount>0){
                                            $scope.contractInfo.contract_value += parseInt(item.amount);
                                        }
                                            
                                     }); 
                                 }
                            }
                        });
                     }
                    
                     $scope.callSpendInfo();

                     $scope.options1 = {
                        minDate: new Date(),
                        showWeeks: false
                    };


                    $scope.projectValueChange=function(data){
                        $scope.contractInfo.contract_value ='';
                        $scope.hideLabel =false;
                        $scope.projectContractValue=data;
                        $scope.choices = [{id: 'choice1','type':'new'}];
                        $scope.addNewChoice = function() {
                            
                              var newItemNo = $scope.choices.length+1;
                              $scope.choices.push({'id':'choice'+newItemNo,'type':'new'});
                              $scope.lastItem=$scope.choices.slice(-1)[0];
                      };
      
                          $scope.removeChoice = function(index) {
                              $scope.choices.splice(index,1);
                          }
                     }

                     $scope.updateSpendMngmt=function(data,choices){
                        params=data;
                        params.updated_by = $scope.user.id_user;
                        params.id_contract  = decode($stateParams.id);
                        if(choices){
                            const filterBudgetValues = choices.filter(element => {
                                delete element.type;
                                delete element.id;
                                element.from_date= dateFilter(element.from_date,'yyyy-MM-dd');
                                element.to_date= dateFilter(element.to_date,'yyyy-MM-dd');
                                if(element.amount!=null ||element.amount!=undefined){
                                    return true; 
                                }
                                return false;
                            });
                            params.contract_budget_data = filterBudgetValues;
                            //params.budget_data = choices;
                        }
                        contractService.updateSpendMgmt(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Spend$$Lines$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.callSpendInfo();
                                $scope.init();
                                $scope.getCounts();
                                //$scope.getInfo();
                                //$scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });
                     }
                    
                }

                if($scope.info==4){ //contract Attachments

                    $scope.getContractAttachmentsInfo = function(){
                        contractService.getContractAttachments({'id_contract':$scope.contract_id}).then(function(result){
                            $scope.contractAttachmentsInfo = result.data;
                        })
                    }
                    $scope.getContractAttachmentsInfo();
                    $scope.uploadAttachment = function (fData,data) {
                        $scope.isView = true;
                        var params = {};
                        params.file = fData.file.attachments
                        params.customer_id = $scope.user1.customer_id;
                        params.module_id = data.id_contract_review;
                        params.module_type = 'contract_review';
                        params.reference_type= 'contract';
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
                            $scope.init();
                            $scope.getCounts();
                            $scope.getContractAttachmentsInfo();
                            //$scope.getInfo();
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
                                    module_id: data.id_contract_review,
                                    module_type: 'contract_review',
                                    is_workflow:$scope.isWorkflow,
                                    reference_id: decode($stateParams.id),
                                    reference_type: 'contract',
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
                                $scope.init();
                                $scope.getCounts();
                                $scope.getContractAttachmentsInfo();
                                //$scope.getInfo();
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
                                    $scope.getCounts();
                                    //$scope.getInfo();
                                }else{$rootScope.toast('Error',result.error,'error');}
                            })
                        }
                    }  
                }

                if($scope.info==5){ //contract Stakeholders

                    $scope.contractParters={};
                    $scope.businessUnitList =[];
                    $scope.usersList =[];
                    contractService.getstakeholders({'id_contract':decode($stateParams.id)}).then(function (result) {
                        
                        if (result.status) {
                            $scope.contractParters=result.data;
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
                        reqObj.id_provider = $scope.contractInfo.provider_name;
                        reqObj.user_role_id = $scope.user1.user_role_id;
                        reqObj.id_user = $scope.user1.id_user;
                        reqObj.contract_id =$scope.contract_id;                 
                        customerService.getUserList(reqObj).then(function (result){
                            $scope.providerUsersList = result.data.data;                    
                        });
                    }              
                    var params ={};
                    $scope.update=function(data){
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
                        params.id_contract  = decode($stateParams.id);
                        contractService.addSponsers(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'Create';
                                obj.action_description = 'Create$$Sponsor$$Information$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.getCounts();
                                //$scope.getInfo();
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
                            $scope.contractParters.data[id].provider = $scope.contractInfo.provider_name_show;
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
                }

                $scope.OpenContractInfo = function(){
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
    
                        $scope.getInfo = function(){
                            var par = {};
                            par.id_contract  = $scope.contract_id;
                            par.id_user  = $scope.user1.id_user;
                            par.user_role_id  = $scope.user1.user_role_id;
                            par.id_contract_workflow  = $scope.workflowId;
                            par.id_contract_review  = decode($stateParams.rId);
                            par.is_workflow  = $scope.isWorkflow;
                            contractService.getContractById(par).then (function(result){
                                if(result.status){
                                    $scope.infoObj = result.data[0];
                                    if($scope.infoObj.is_template_lock ==1){
                                        $scope.lock = true;
                                    }
                                    else{
                                        $scope.lock=false;
                                    }
                                    $scope.contract_attachments = result.contract_attachments;
                                    $scope.contract_information =result.contract_information;
                                    $scope.contract_tags = result.contract_tags;
                                    $scope.contract_spent_managment = result.contract_spent_managment;
                                    $scope.contract_stake_holder=result.contract_stake_holder;
                                    $scope.infoObj.contract_start_date=moment($scope.infoObj.contract_start_date).utcOffset(0, false).toDate();
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
                            contractService.getbuOwnerUsers(params).then(function(result){
                                $scope.buOwnerUsers = result.data;
                            });
                        } 
    
                        $scope.updateLockingStatus = function(id){
                            $scope.infoObj.is_template_lock =id;
                            if(id){
                                $scope.lock= true;
                            }
                            else{
                                $scope.lock=false;
                            }
                        }
                    
                        $scope.resetLockingStatus = function(id){
                            $scope.infoObj.is_template_lock =id;
                            if(id){
                                $scope.lock= false;
                            }
                            else{
                                $scope.lock=true;
                            }
                        }
                        $scope.updateContractInfo = function(data){
                            var postData = angular.copy(data);
                            delete postData.contract_unique_id;
                            postData.contract_start_date = dateFilter(data.contract_start_date,'yyyy-MM-dd');
                            postData.contract_end_date = dateFilter(data.contract_end_date,'yyyy-MM-dd');
                            postData.customer_id=$scope.user1.customer_id;
                            postData.updated_by = $scope.user.id_user;
                            Upload.upload({
                                url: API_URL+'Contract/update',
                                data: {
                                    'contract': postData
                                }
                            })
                           .then(function(resp){
                                if(resp.data.status){
                                    $rootScope.toast('Success',resp.data.message);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$contract$$'+postData.contract_name;
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.init();
                                    $scope.getInfo();
                                    $scope.getCounts();
                                }else{
                                    $rootScope.toast('Error',resp.data.error,'error',$scope.contract);
                                }
                            },function(resp){
                                $rootScope.toast('Error',resp.error);
                            });
                        }
    
                        masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                            $scope.currencyList = result.data;
                        });
                        contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                            $scope.relationshipCategoryList = result.drop_down;
                        });

                        catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
                            $scope.selectedInfoProvider = result.data;
                        });
    
                        templateService.list().then(function (result){
                            $scope.templateList=result.data.data;
                        });
                    
                }

                $scope.OpenTags = function(){
                   
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
                        $scope.tagsData = function(){
                            tagService.getContractTags({'id_contract':$scope.contract_id,'tag_type':'contract_tags'}).then (function(result){
                                if(result.status){
                                    $scope.tagsInfo=[];
                                    $scope.tagsInfo = result.data;
                                    angular.forEach($scope.tagsInfo,function(i,o){
                                        angular.forEach(i.tag_details,function(j,o){
                                        if(j.tag_type=='date'){
                                             j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                            }
                                        }) 
                                    })
                    
                                }else {$rootScope.toast('Error',result.error,'error',$scope.contract);}
                
                            });
                        }
                        $scope.tagsOptions = {
                            minDate: moment().utcOffset(0, false).toDate(),
                            showWeeks: false
                        };
                        $scope.tagsData(); 
                        $scope.updateTags = function(data){
                           var params ={};
                             params.id_contract = $scope.contract_id;
                             params.tag_type = 'contract_tags';
                             angular.forEach(data,function(i,o){
                                 angular.forEach(i.tag_details,function(j,o){
                                 if(j.tag_type=='date'){
                                     j.tag_answer = dateFilter(j.tag_answer,'yyyy-MM-dd');
                                 }
                             });
                         });
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
                                     $scope.getCounts();
                                     $scope.tagsData();
                                     $scope.init();
                                     angular.forEach($scope.tagsInfo,function(i,o){
                                         angular.forEach(i.tag_details,function(j,o){
                                             if(j.tag_type=='date'){
                                                 j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                                 }
                                             })
                                     })
                                     
                                 } else {
                                     $rootScope.toast('Error', result.error,'error');
                                 }
                            });
                        }
                        
                    
                }
                $scope.OpenContractValues = function(){
                    $scope.projectContractValue=$scope.contractInfo.contract_value_period;
                   
                    
                    $scope.validateRecurrence =function(selectedDate){
                        //console.log('sd',selectedDate);
                        $scope.options1 = {};
                        var dt = angular.copy((selectedDate) ? selectedDate : moment().utcOffset(0, false).toDate());
                        $scope.options1 = {
                            minDate: dt,
                            showWeeks: false
                        };
                    }
                    $scope.options = {
                        minDate: new Date(),
                        showWeeks: false
                    };
                    $scope.options1 = angular.copy($scope.options);
                  

                     $scope.projectValueChange=function(data){
                        $scope.contractInfo.contract_value ='';
                        //$scope.contractInfo.contract_budget_data ='';
                        $scope.hideLabel =false;
                      $scope.projectContractValue=data;
                      $scope.choices = [{id: 'choice1','type':'new'}];
                      $scope.addNewChoice = function() {
                            var newItemNo = $scope.choices.length+1;
                            $scope.choices.push({'id':'choice'+newItemNo,'type':'new'});
        
                      };
    
                        $scope.removeChoice = function(index,info) {
                            //console.log('in',info);
                            $scope.choices.splice(index,1);
                            $scope.contractInfo.contract_value = 0;
                            info.forEach(item => {
                                $scope.contractInfo.contract_value += parseInt(item.amount);
                            });
                            //console.log('1',$scope.contractInfo.contract_value);
                        }
    
                     }

                     $scope.hideLabel = false;
                     $scope.getValue = function(val,data){
                        //console.log('val',val);
                        //console.log('data',data);
                        if(val!=null){
                            $scope.hideLabel = true;
                        }
                        $scope.contractInfo.contract_value = 0;
                        data.forEach(item => {
                            //console.log('item',item);
                            delete item.id;
                            delete item.type;
                            if(item.amount>0){
                                $scope.contractInfo.contract_value += parseInt(item.amount);
                            }
                        });
                        //console.log('onch',$scope.contractInfo.contract_value);
                     }
    
                     $scope.spendLine = {};
                     $scope.callSpendInfo = function(){
                        // contractService.getSpendMgmt({'id_contract':decode($stateParams.id)}).then(function (result) {
                        contractService.getSpendManagementInfo({'id_contract':decode($stateParams.id)}).then(function (result) {
                            if (result.status) {
                                $scope.contractInfo=result.data[0];
                                if($scope.contractInfo.contract_value_period==='budget') $scope.hideLabel =true;
                                $scope.choices = $scope.contractInfo.contract_budget_data;
                                angular.forEach($scope.choices,function(obj,o){
                                   //console.log('i',obj)
                                   if(obj.from_date)obj.from_date= moment(obj.from_date).utcOffset(0, false).toDate();
                                   if(obj.to_date)obj.to_date= moment(obj.to_date).utcOffset(0, false).toDate();
                                })
                               $scope.addNewChoice = function() {
                                   var newItemNo = $scope.choices.length+1;
                                   $scope.choices.push({'id':'choice'+newItemNo,'type':'new'});
                               };
                               $scope.removeChoice = function(index,info) {
                                    $scope.choices.splice(index,1);
                                    $scope.contractInfo.contract_value = 0;
                                    info.forEach(item => {
                                        if(item.amount>0){
                                            $scope.contractInfo.contract_value += parseInt(item.amount);
                                        }
                                            
                                     }); 
                                 }
                            }
                        });
                     }
                    
                     $scope.callSpendInfo();

                     $scope.updateSpendMngmt=function(data,choices){
                        //console.log('da',data);
                         //console.log("data",data.contract_budget_data);
                        //console.log("choices",choices);
                       params=data;
                       params.updated_by = $scope.user.id_user;
                       params.id_contract  = decode($stateParams.id);
                       if(choices){
                            const filterBudgetValues = choices.filter(element => {
                                delete element.type;
                                delete element.id;
                                element.from_date= dateFilter(element.from_date,'yyyy-MM-dd');
                                element.to_date= dateFilter(element.to_date,'yyyy-MM-dd');
                                if(element.amount!=null ||element.amount!=undefined){
                                    return true; 
                                }
                                return false;
                            });
                            params.contract_budget_data= filterBudgetValues;
                       }
                     
                       //console.log('results',filterBudgetValues);               
                       //console.log('params',params);
                       contractService.updateSpendMgmt(params).then(function (result) {
                           if (result.status) {
                               $rootScope.toast('Success', result.message);
                               var obj = {};
                               obj.action_name = 'Update';
                               obj.action_description = 'Update$$Spend$$Lines$$('+data.action_item+')';
                               obj.module_type = $state.current.activeLink;
                               obj.action_url = $location.$$absUrl;
                               $rootScope.confirmNavigationForSubmit(obj);
                               $scope.callSpendInfo();
                               $scope.getCounts();
                               $scope.init();
                               //$scope.getInfo();
                           } else {
                               $rootScope.toast('Error', result.error,'error');
                           }
                       });
                     }
                }
    
                $scope.OpenContractAttachments = function(){
                    $scope.getContractAttachmentsInfo = function(){
                        contractService.getContractAttachments({'id_contract':$scope.contract_id}).then(function(result){
                            $scope.contractAttachmentsInfo = result.data;
                        })
                    }
                    $scope.getContractAttachmentsInfo();
                    $scope.uploadAttachment = function (fData,data) {
                        $scope.isView = true;
                        var params = {};
                        params.file = fData.file.attachments
                        params.customer_id = $scope.user1.customer_id;
                        params.module_id = data.id_contract_review;
                        params.module_type = 'contract_review';
                        params.reference_type= 'contract';
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
                            $scope.getContractAttachmentsInfo();
                            $scope.getCounts();
                            $scope.init();
                            //$scope.getInfo();
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
                                    module_id: data.id_contract_review,
                                    module_type: 'contract_review',
                                    is_workflow:$scope.isWorkflow,
                                    reference_id: decode($stateParams.id),
                                    reference_type: 'contract',
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
                                //$scope.getInfo();
                                $scope.getContractAttachmentsInfo();
                                $scope.getCounts();
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
    
                    $scope.deleteAttachment = function(id,name){
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
                                    $scope.getContractAttachmentsInfo();
                                    $scope.getCounts();
                                    $scope.init();
                                }else{$rootScope.toast('Error',result.error,'error');}
                            })
                        }
                    }    
                }
    
                $scope.OpenContractStakeholders = function(){
                        $scope.contractParters={};
                        $scope.businessUnitList =[];
                        $scope.usersList =[];
                contractService.getstakeholders({'id_contract':decode($stateParams.id)}).then(function (result) {
                    if (result.status) {
                        $scope.contractParters=result.data;
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
                    reqObj.id_provider = $scope.contractInfo.provider_name;
                    reqObj.user_role_id = $scope.user1.user_role_id;
                    reqObj.id_user = $scope.user1.id_user;
                    reqObj.contract_id =$scope.contract_id;                 
                    customerService.getUserList(reqObj).then(function (result){
                        $scope.providerUsersList = result.data.data;                    
                    });
                }              
                var params ={};
                $scope.update=function(data){
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
                    params.id_contract  = decode($stateParams.id);
                    contractService.addSponsers(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'Create';
                            obj.action_description = 'Create$$Sponsor$$Information$$('+data.action_item+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.init();
                            $scope.getInfo();
                            $scope.getCounts();
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
                        $scope.contractParters.data[id].provider = $scope.contractInfo.provider_name_show;
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
                //stakeholders ends//
                }
                $scope.cancel = function(){
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

    $scope.viewVersions=function(){

        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/contract-versions.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='normal.versions';
                $scope.title='normal.versions';
                $scope.versionInfo=true;
                $scope.readOnlyLink=false;
                $scope.rowLatestData=function(){
                    // var params={};
                    // params.key='customerContractBuildDetails';
                    // params.method='GET';
                    // params.id=parseInt($stateParams.contract_build_id);
                    // builderService.builderList(params).then(function (result) {
                    //     $scope.versionDetails = result.data;
                    // $scope.mostRecentInfo=$scope.versionDetails.versions.slice(-1)[0];
                    // $scope.mostRecentVersion=$scope.versionDetails.versions.slice(-1)[0].version;
                    // $scope.versionDetails.versions.pop();
                    // })

                    var params={};
                    params.key='customerContractBuildDetails';
                    params.method='GET';
                    params.id=$scope.contractInfo.contract_build_id;
                        builderService.builderList(params).then(function (result) {
                        $scope.versionDetails = result.data;
                        if($scope.versionDetails.versions){
                            $scope.mostRecentInfo=$scope.versionDetails.versions.slice(-1)[0];
                            $scope.mostRecentVersion=$scope.versionDetails.versions.slice(-1)[0].version;
                        }                     
                    });    
            
                }     
                $scope.rowLatestData(); 

                $scope.getPreview=function(versionData){

                    var modalInstance = $uibModal.open({
                        animation: true,
                        backdrop: 'static',
                        keyboard: false,
                        scope: $scope,
                        openedClass: 'right-panel-modal modal-open',
                        templateUrl: 'views/Manage-Users/customer-contract-builder/preview.html',
                        controller: function ($uibModalInstance,$scope,item) {
                            $scope.bottom ='general.save';
                                $scope.versionInfo=versionData;
                            

                                var params={};
                                params.key='contractPreview';
                                params.method='GET';
                                params.id=$scope.versionDetails.contractBuildId;
                                params.structure_id=versionData.id;
                                builderService.builderList(params).then(function (result) {
                                    $scope.previewData = result.data;
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
                
                $scope.getDoc=function(versionData){

                    var params={};
                    params.key='downloadContractPreview';
                    params.method='GET';
                    params.id=$scope.versionDetails.contractBuildId;
                    params.structure_id=versionData.id;
                    builderService.builderList(params).then(function (result) {
                        $scope.previewData = result.data;
                    });
                }
                $scope.downloadPdf = function(information){
                    var params={};
                    params.key='contractBuildPdf';
                    params.method='GET';
                    params.structure_id=information.id;
                    params.id=$scope.versionDetails.contractBuildId;
                    params.contract_builder_name=$scope.versionDetails.name;
                    params.version_number= information.version
                    builderService.builderList(params).then(function (result) {
                        //console.log('res',result);
                        if(result.status){
                            var obj = {};
                            obj.action_name = 'export';
                            obj.action_description = 'export$$contractBuilder list$$('+result.data.file_name+')';
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
                     });
                }
                $scope.downloaddocx = function(docxinfo){
                    var params={};
                    params.key='contractBuilderDocx';
                    params.method='GET';
                    params.structure_id=docxinfo.id;
                    params.id=$scope.versionDetails.contractBuildId;
                    params.contract_builder_name=$scope.versionDetails.name;
                    params.version_number=docxinfo.version
                    builderService.builderList(params).then(function (result) {
                        //console.log('res',result);
                        if(result.status){
                            var obj = {};
                            obj.action_name = 'export';
                            obj.action_description = 'export$$contractBuilder list$$('+result.data.file_name+')';
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
                     });

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
        })
    }

    

    $scope.goToProviderDetails = function(row){
        $state.go('app.provider.view',{name:row.provider_name_show,id:encode(row.provider_name)});
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
                    //params.contract_ids=item.contract_id;
                    calenderService.smartFilter(params).then(function (result) {
                        if (result.status) {
                            $scope.relationCategory = result.data.relationship_list;
                            $scope.business_units = result.data.business_unit;
                            $scope.providers = result.data.provider;
                            $scope.contracts = result.data.contract;
                            $scope.provider_relationship_category = result.data.provider_relationship_category;
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

                    if ($scope.customOptions.relationship_category_id)
                        params1["relationship_category_id"] = $scope.customOptions.relationship_category_id.toString();

                    if ($scope.customOptions.bussiness_unit_id)
                        params1["business_ids"] = $scope.customOptions.bussiness_unit_id.toString();

                    if ($scope.customOptions.provider_id)
                        params1["provider_ids"] = $scope.customOptions.provider_id.toString();
                        

                    if ($scope.customOptions.provider_relationship_category_id)
                        params1["provider_relationship_category_id"] = $scope.customOptions.provider_relationship_category_id.toString();
                    
                    if (params1['business_ids'] == '') delete params1['business_ids'];
                    if (params1['relationship_category_id'] == '') delete params1['relationship_category_id'];
                    if (params1['provider_ids'] == '') delete params1['provider_ids'];
                    if(params1['provider_relationship_category_id']=='') delete params1['provider_relationship_category_id'];
                    calenderService.smartFilter(params1).then(function (result) {
                        if (result.status) {
                            $scope.relationCategory = result.data.relationship_list;
                            $scope.business_units = result.data.business_unit;
                            $scope.provider_relationship_category = result.data.provider_relationship_category;
                            $scope.providers = result.data.provider;
                            $scope.contracts = result.data.contract;
    
                        }
                    });
                }
                $scope.addReview = function (formData) {

                    var data = angular.copy(formData);

                    data.customer_id = $scope.user1.customer_id;
                    data.created_by = $scope.user.id_user;

                    if ($scope.customOptions.relationship_category_id)
                        data.relationship_category_id = $scope.customOptions.relationship_category_id.toString();
                    if ($scope.customOptions.bussiness_unit_id) {
                        data.business_unit_id = $scope.customOptions.bussiness_unit_id.toString();
                        delete data.bussiness_unit_id;
                    }
                    if ($scope.customOptions.provider_relationship_category_id)
                            data.provider_relationship_category_id = $scope.customOptions.provider_relationship_category_id.toString();

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
                            $scope.init();
                            $scope.callServerSubContract($scope.tableStateRef);
                            //$scope.callServer($scope.tableStateRef);
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

    // $scope.updateTotal = function(catologue) {
    //     var up = catologue.unit_price;
    //     price = up.replace(/,/g, '.');
    //     if(!catologue.quantity || !price) {
    //         catologue.calculated_total_item_spend = 0;
    //     } else {
    //        catologue.calculated_total_item_spend = Math.round(catologue.quantity * price);
    //     }
    // };


    $scope.serviceCatelogue = function (tableState){
        setTimeout(function(){
            $scope.tableStateRef = tableState;
            $scope.isCatelogueLoading = true;
            var pagination = tableState.pagination;
            tableState.id_contract  = $scope.contract_id;
            tableState.id_user  = $scope.user1.id_user;
            tableState.user_role_id  = $scope.user1.user_role_id;
            contractService.getServiceCatologue(tableState).then (function(result){
                 $scope.serviceCatelogueInfo = result.data;
                $scope.serviceCatolgueCount = result.total_records;
                $scope.emptyCatolgueTable=false;
                $scope.displayCount = $rootScope.userPagination;
                tableState.pagination.numberOfPages =  Math.ceil(result.total_records / $rootScope.userPagination);
                $scope.isCatelogueLoading = false;
                if(result.total_records < 1)
                    $scope.emptyCatolgueTable=true;
            })
        },700);
    }

    $scope.defaultPages123 = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.serviceCatelogue($scope.tableStateRef);
            }                
        });
    }

    $scope.addServiceCatalogue = function(row){
        $scope.selectedRow = row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'create-edit-service-catalogue.html',
            controller: function ($uibModalInstance, $scope,item) {
              $scope.title ='general.add';
              $scope.bottom ='general.save'; 
              $scope.editCatalogue=false;
                contractService.servicePeriodicity().then(function(result){
                    $scope.periodicity = result.data;
                });
                var catalogue={};
                catalogue.status=1;
                catalogueService.catalogueList(catalogue).then(function(result){
                    $scope.catalogueList = result.data;
                });

                $scope.getCataloguename=function(data){
                    $scope.catologue.unit_price='';
                     $scope.catologue.quantity='';
                     $scope.catologue.calculated_total_item_spend='';
                   var catalogue= $scope.catalogueList.filter(item => { return item.id_catalogue == data; });
                   $scope.baseCurrency=catalogue[0].currency_name;

                   var param={};
                   param.base_currency_code= $scope.baseCurrency;
                   param.convertable_currency_code=$scope.contractInfo.currency_name;
                   catalogueService.getExchange(param).then(function(result){
                       $scope.exchangeRate = result.data.exchange_rate;
                       });
                }


                $scope.updateTotal = function(catologue) {
                    var up = catologue.unit_price;
                    price = up.replace(/,/g, '.');
                    if(!catologue.quantity || !price) {
                        catologue.calculated_total_item_spend = 0;
                    } else {
                        catologue.calculated_total_item_spend = Math.round((catologue.quantity * price) * $scope.exchangeRate);

                    }
                };

                $scope.createCatalogue = function () {
                    $state.go('app.catalogue.create-catalogue');
                }

                $scope.detailsPageGo = function(row,tag){
                    if($scope.catalogueInfo1){
                        $scope.catalogueInfo1.close();
                    }
                    if($scope.catalogueDetailsInfo){
                        $scope.catalogueDetailsInfo.close();
                      }
                    var goView = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
                    if(tag.selected_field=='contract'){
                        console.log("thiscontract");
                        $state.go(goView,{name:row.name,id:encode(row.id),type:'review'});
                    }
                    else if(tag.selected_field=='relation'){
                        $state.go('app.provider.view',{name:row.name,id:encode(row.id)});
                    }
                    else if(tag.selected_field=='project'){
                        $state.go('app.projects.view',{name:row.name,id:encode(row.id),type:'workflow'});
                    }
                    else if(tag.selected_field=='catalogue'){
                        // if($scope.catalogueDetailsInfo){
                        //   $scope.catalogueDetailsInfo.close();
                        // }          
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
                                      catalogueService.catalogueList(obj).then(function (result) {
                                          $scope.catalogue = result.data[0];
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

                $scope.goToViewCatalogue = function (data) {
                    $scope.catalogueInfoId=data;
                    $scope.catalogueInfo1 = $uibModal.open({
                        animation: true,
                        backdrop: 'static',
                        keyboard: false,
                        scope: $scope,
                        size: 'lg',
                        openedClass: 'right-panel-modal modal-open',
                        templateUrl: 'views/Manage-Users/catalogue/view-catalogue-info.html',
                        controller: function ($uibModalInstance, $scope, item) {                        
                            $scope.catalogueInfo=function(){
                                var obj={};
                                obj.id_catalogue=$scope.catalogueInfoId;
                                catalogueService.catalogueList(obj).then(function (result) {
                                    $scope.catalogue = result.data[0];
                                    $scope.catalogue_attach_count = result.data[0].catalogue_attachments_count;
                                    $scope.catalogue_info_count = result.data[0].catalogue_information;
                                    $scope.catalogue_tags_count = result.data[0].catalogue_tags;
                                });
                            }
                            $scope.catalogueInfo();

                            var obj1={};
                            obj1.id_catalogue=$scope.catalogueInfoId;
                            catalogueService.getCatalogueTags(obj1).then (function(result){   
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
                    $scope.catalogueInfo1.result.then(function ($data) {
                    }, function () {
                    });
                }

                $scope.enablefield = false;
                $scope.getOpinion = function(value) {
                  if(value ==1){
                    $scope.enablefield = true;
                    $scope.catologue.manual_total_item_spend_add_to_chart ='';
                    $scope.catologue.manual_total_item_spend='';
                  }
                  else
                  {
                    $scope.enablefield = false;
                    $scope.catologue.manual_total_item_spend_add_to_chart =  $scope.manual_graph;
                    $scope.catologue.manual_total_item_spend=  $scope.manual_item_spend;
                  }
                    
                }

                if(item){
                    $scope.title = 'general.edit';
                    $scope.bottom ='general.update';
                    $scope.editCatalogue=true;
                    var param={};
                    param.id_contract = decode($stateParams.id);
                    param.id_service_catalogue = row.id_service_catalogue;
                    contractService.getServiceCatologue(param).then(function(result){
                        $scope.catologue = result.data[0];
                        var catalogue={};
                        catalogue.status=1;
                        catalogueService.catalogueList(catalogue).then(function(result){
                            $scope.catalogueList = result.data;
                        });
        
                        if($scope.catologue.catalogue_id){       
                            var catalogue= $scope.catalogueList.filter(item => { return item.id_catalogue == $scope.catologue.catalogue_id; });
                            $scope.baseCurrency=catalogue[0].currency_name;
    
                            var param={};
                            param.base_currency_code= $scope.baseCurrency;
                            param.convertable_currency_code=$scope.contractInfo.currency_name;
                            catalogueService.getExchange(param).then(function(result){
                                $scope.exchangeRate = result.data.exchange_rate;
                                });
                            }
                        $scope.manual_item_spend = $scope.catologue.manual_total_item_spend;
                        $scope.manual_graph = $scope.catologue.manual_total_item_spend_add_to_chart;
                        if($scope.catologue.calculated_total_item_spend_add_to_chart==1){ $scope.enablefield = true;}
                        if($scope.catologue.period_start_date)$scope.catologue.period_start_date = moment( $scope.catologue.period_start_date).utcOffset(0, false).toDate();
                        if($scope.catologue.period_end_date)$scope.catologue.period_end_date = moment( $scope.catologue.period_end_date).utcOffset(0, false).toDate();
                    })

                    $scope.updateTotal = function(catologue) {
                        var up = catologue.unit_price;
                        price = up.replace(/,/g, '.');
                        if(!catologue.quantity || !price) {
                            catologue.calculated_total_item_spend = 0;
                        } else {
                            catologue.calculated_total_item_spend = Math.round((catologue.quantity * price) * $scope.exchangeRate);
    
                        }
                    };

                }
               

                $scope.addServiceCatalogue=function(data){
                    params=data;
                    params.contract_id  = decode($stateParams.id);
                    if(params.period_start_date)params.period_start_date = dateFilter(data.period_start_date,'yyyy-MM-dd');
                    if(params.period_end_date)params.period_end_date = dateFilter(data.period_end_date,'yyyy-MM-dd');
                    contractService.addServiceCatologue(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'Update';
                            obj.action_description = 'Update$$Spend$$Lines$$('+data.action_item+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.cancel();
                            $scope.serviceCatelogue($scope.tableStateRef);
                            $scope.getChartData();
                            $scope.getTabsInfo();
                            //$scope.init();
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
  

    $scope.deleteServiceCatelogueAction = function(row){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            var params ={};
            params.id_service_catalogue  = row.id_service_catalogue ;
            params.updated_by  = $rootScope.id_user ;            
            contractService.deleteServiceCatologue(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$Action$$Item$$('+row.action_item+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.serviceCatelogue($scope.tableStateRef);
                       $scope.getChartData();
                       $scope.getTabsInfo();
                }else $rootScope.toast('Error', result.error, 'error',$scope.user);
            });
        }
    }

    $scope.getEventFeed = function (tableState){
        var pagination = tableState.pagination;
           setTimeout(function(){
               $scope.tableStateRefEvent = tableState;
               $scope.eventLoading = true;
               tableState.reference_type = 'contract';
               tableState.reference_id = decode($stateParams.id);
               projectService.eventFeedList(tableState).then (function(result){
                   $scope.eventList = result.data;
                   $scope.eventListCount = result.total_records;
                   $scope.eventEmptyTable=false;
                   $scope.displayCount = $rootScope.userPagination;
                   $scope.totalRecordsEvent = result.total_records;
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
        var r = confirm($filter('translate')('general.alert_continue'));
        if (r == true) {
            var params = {};
            params.id_event_feed = id.id_event_feed;
            projectService.deleteEventFeed(params).then(function (result) {
                if (result.status) {
                    $rootScope.toast('Success', result.message);
                    $scope.getEventFeed($scope.tableStateRefEvent);
                    $scope.getTabsInfo();
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
                $scope.title = 'controller.contract_event';
                $scope.bottom = 'general.save';
                $scope.file = {};
                $scope.eventAdd={};
                $scope.contractLinks=[];
                $scope.contractLink={};  
                projectService.eventResponsibleUsers().then (function(result){
                    $scope.eventResponsibleUsers=result.data;
                });

                if(item){
                    $scope.title = 'controller.contract_event';
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
                        if (eventData.date!=null){            
                        eventData.date = dateFilter(eventData.date, 'yyyy-MM-dd');
                    }else{
                        eventData.date='';
                    }
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
                                'reference_type':'contract',
                                'reference_id':decode($stateParams.id),
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
                                $scope.getTabsInfo();
                                $rootScope.toast('Success', resp.data.message);
                                $scope.cancel();
                            } else {
                                $rootScope.toast('Error', resp.data.error);
                            } 
                    });
                    }
                    
                }else{
                    $scope.addEventFeed=function(eventData){
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
                                'reference_type':'contract',
                                'reference_id':decode($stateParams.id),
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
                                $scope.getTabsInfo();
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


                    $scope.deleteAttachmentEvent = function(id,name){
                        var r=confirm($filter('translate')('general.alert_continue'));
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
    
    $scope.getObligations = function (tableState){
        setTimeout(function(){
            $scope.tableStateRef = tableState;
            $scope.obligationLoading = true;
            var pagination = tableState.pagination;
            tableState.id_contract  = $scope.contract_id;
            tableState.id_user  = $scope.user1.id_user;
            tableState.user_role_id  = $scope.user1.user_role_id;
            projectService.getObligations(tableState).then (function(result){
                 $scope.obligationsInfo = result.data;
                $scope.obligationsInfoCount = result.total_records;
                $scope.emptyObligationTable=false;
                $scope.displayCount = $rootScope.userPagination;
                tableState.pagination.numberOfPages =  Math.ceil(result.total_records / $rootScope.userPagination);
                $scope.obligationLoading = false;
                if(result.total_records < 1)
                    $scope.emptyObligationTable=true;
            })
        },700);
    }

    $scope.defaultPagesObligations = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getObligations($scope.tableStateRef);
            }                
        });
    }
  
    $scope.deleteObligation = function(info){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            var params ={};
            params.id_obligation  = info.id_obligation ;
            params.updated_by  = $rootScope.id_user ;            
            projectService.deleteObligations(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    $scope.getObligations($scope.tableStateRef);
                    $scope.getTabsInfo();
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$obligationItem$$('+row.id_obligation+')';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                }else $rootScope.toast('Error', result.error, 'error',$scope.user);
            });
        }
    }
    $scope.createObligationRights = function(row){
        $scope.obligations={};
        $scope.selectedRow =row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            // templateUrl: 'create-edit-obligation-rights.html',
            templateUrl:'views/Manage-Users/contracts/create-edit-obligations.html',
            controller: function ($uibModalInstance, $scope,item) {
              $scope.title ='general.add';
              $scope.bottom ='general.save'; 
              //$scope.editField = false;
              
              projectService.getRecurrences().then(function(result){
                   $scope.recurrences = result.data;
              });

               projectService.resendRecurrence().then(function(result){
                    $scope.resend_recurrences = result.data;
              });

              if(item){
                  $scope.title='general.edit';
                projectService.getObligations({'contract_id':decode($stateParams.id),'id_obligation':row.id_obligation}).then(function(result){
                    $scope.obligations = result.data[0];
                     if($scope.obligations.email_notification==1){$scope.requiredFields=true;}
                    else { $scope.requiredFields=false;}
                   

                    if($scope.obligations.calendar==1){$scope.startFields=true;}
                    else { $scope.startFields=false;}
                    if($scope.obligations.recurrence=='Ad-hoc'){
                        $scope.anotherField =false;
                        $scope.defaultField=false;
                        $scope.startFields=false;
                        $scope.enddateField=false;
                        $scope.calendarFields= false;
                        
                    }
                    if($scope.obligations.recurrence=='One-off' && ($scope.obligations.calendar==1 || $scope.obligations.calendar==0)){
                        $scope.enddateField=false;
                        $scope.startFields=true;
                        $scope.calendarFields= false;
                    }

                    if($scope.obligations.recurrence =='Monthly' && $scope.obligations.calendar==1 ){
                        $scope.startFields = true;
                        $scope.calendarFields = true;
                    }
                    if($scope.obligations.recurrence =='Annually' && $scope.obligations.calendar==1 ){
                        $scope.startFields = true;
                        $scope.calendarFields = true;
                    }
                    if($scope.obligations.recurrence =='Semi-annually' && $scope.obligations.calendar==1){
                        $scope.startFields = true;
                        $scope.calendarFields = true;
                    }
                    if($scope.obligations.recurrence =='Quarterly' &&  $scope.obligations.calendar==1){
                        $scope.startFields = true;
                        $scope.calendarFields = true;
                    }

                    if($scope.obligations.resend_recurrence=='One-off' && $scope.obligations.email_notification==1){
                        $scope.enddateField = false;
                        $scope.requiredFields=true;
                        $scope.requiredNotificationField= false;
                    }
                    if($scope.obligations.resend_recurrence=='One-off' && $scope.obligations.email_notification==0){
                        $scope.enddateField = false;
                    }
                    
                    if($scope.obligations.resend_recurrence=='Monthly' && $scope.obligations.email_notification==1){
                        $scope.enddateField = true;
                        $scope.requiredFields=true;
                        $scope.requiredNotificationField= true;
                    }
                    if($scope.obligations.resend_recurrence=='Annually' && $scope.obligations.email_notification==1){
                        $scope.enddateField = true;
                        $scope.requiredFields=true;
                        $scope.requiredNotificationField= true;
                    }
                    if($scope.obligations.resend_recurrence=='Semi-annually' && $scope.obligations.email_notification==1){
                        $scope.enddateField = true;
                        $scope.requiredFields=true;
                        $scope.requiredNotificationField= true;
                    }
                    if($scope.obligations.resend_recurrence=='Quarterly' && $scope.obligations.email_notification==1){
                        $scope.enddateField = true;
                        $scope.requiredFields=true;
                        $scope.requiredNotificationField= true;
                    }

                    if($scope.obligations.recurrence_start_date)$scope.obligations.recurrence_start_date = moment($scope.obligations.recurrence_start_date).utcOffset(0, false).toDate();
                    if($scope.obligations.recurrence_end_date)$scope.obligations.recurrence_end_date = moment($scope.obligations.recurrence_end_date).utcOffset(0, false).toDate();
                    if($scope.obligations.email_send_start_date)$scope.obligations.email_send_start_date = moment( $scope.obligations.email_send_start_date).utcOffset(0, false).toDate();
                    if($scope.obligations.email_send_last_date)$scope.obligations.email_send_last_date = moment( $scope.obligations.email_send_last_date).utcOffset(0, false).toDate();



                    $scope.options = {
                        minDate: moment().utcOffset(0, false).toDate(),
                        showWeeks: false
                    };
                    $scope.options2 = angular.copy($scope.options);



                    $scope.options3 ={
                        minDate: moment().utcOffset(0, false).toDate(),
                        showWeeks: false
                    }
                    $scope.options4 = angular.copy($scope.options3);
                   
                  
                    var dt12 = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : moment().utcOffset(0, false).toDate());
                    $scope.options2 = {};
                    $scope.options2 = {
                        minDate: dt12,
                        showWeeks: false
                    };
                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNORw6QCAdeOFRRsfh9uutYk=') dt12.setMonth(dt12.getMonth() + 1);
                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEyQIrGjAnMehP1bBlC0TWw=') dt12.setMonth(dt12.getMonth() + 3);
                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNFoKKPuBpe0GoOwvA+1t9+A=') dt12.setMonth(dt12.getMonth() + 6);
                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=') dt12.setFullYear(dt12.getFullYear() + 1);
                   


                    var dt23 = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : moment().utcOffset(0, false).toDate());

                    $scope.options4 = {};
                    
                    if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNORw6QCAdeOFRRsfh9uutYk=') dt23.setMonth(dt23.getMonth() + 1);
                    if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEyQIrGjAnMehP1bBlC0TWw=') dt23.setMonth(dt23.getMonth() + 3);
                    if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNFoKKPuBpe0GoOwvA+1t9+A=') dt23.setMonth(dt23.getMonth() + 6);
                    if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=') dt23.setFullYear(dt23.getFullYear() + 1);
                    $scope.options4 = {
                        minDate: dt23,
                        showWeeks: false
                    };
                })
              }
             
            
                $scope.addObligationRights=function(data){
                    params=data;
                    params.contract_id  = decode($stateParams.id);
                    if(params.recurrence_start_date!=null){
                        params.recurrence_start_date = dateFilter(data.recurrence_start_date,'yyyy-MM-dd');
                        $scope.requiredFields= false;
                        $scope.startFields =false;
                    }
                    if(params.recurrence_end_date!=null){
                        params.recurrence_end_date = dateFilter(data.recurrence_end_date,'yyyy-MM-dd');
                        $scope.requiredFields= false;
                        $scope.calendarFields =false;
                    }

                    if(params.email_send_start_date){
                        params.email_send_start_date = dateFilter(data.email_send_start_date,'yyyy-MM-dd');
                        $scope.requiredFields= false;
                    }
                    if(params.email_send_last_date!=null){
                        params.email_send_last_date = dateFilter(data.email_send_last_date,'yyyy-MM-dd');
                        $scope.requiredFields= false;
                        $scope.requiredNotificationField=false;
                    }
                    projectService.addObligations(params).then(function (result) {
                        if (result.status) {
                          $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'Update';
                            obj.action_description = 'Update$$Obligations';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.cancel();
                            $scope.getObligations($scope.tableStateRef);
                            $scope.getTabsInfo();
                            $scope.init();
                        } else {
                            $rootScope.toast('Error', result.error,'error');
                            
                        }
                    });
                }

                $scope.getNotification=function(val){

                    if(val){
                        $scope.obligations.email_send_last_date='';
                    }
                  
                   if(val=='1' && $scope.obligations.resend_recurrence_id=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                       $scope.requiredFields = true;
                       $scope.requiredNotificationField=false;
                   }
                   else if(val=='1' && $scope.obligations.resend_recurrence_id!='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                    $scope.requiredFields = true;
                    $scope.requiredNotificationField=true;
                   }
                   else{
                    $scope.requiredFields = false;
                    $scope.requiredNotificationField=false;
                   }
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };


                $scope.getCalenderSelected = function(key){
                    if(key==1 &&  $scope.obligations.recurrence_id=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                        $scope.startFields =true;
                        $scope.calendarFields =false;
                        $scope.enddateField=false;
                    }
                    else if(key==1 && $scope.obligations.recurrence_id!='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                        $scope.startFields =true;
                        $scope.calendarFields =true;
                    }
                    else{
                        $scope.startFields =false;
                        $scope.calendarFields =false;
                        $scope.obligations.recurrence_end_date ='';
                        $scope.obligations.recurrence_start_date ='';
                    }
                }
                $scope.anotherField=true;
                $scope.defaultField = true;
                $scope.enddateField = true;
                $scope.calendarFields=false;
                $scope.startFields = false;
                $scope.getDate = function(vali){
                    var dt = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : moment().utcOffset(0, false).toDate());
                    $scope.options2 = {};
                    
                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNORw6QCAdeOFRRsfh9uutYk=') dt.setMonth(dt.getMonth() + 1);
                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEyQIrGjAnMehP1bBlC0TWw=') dt.setMonth(dt.getMonth() + 3);
                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNFoKKPuBpe0GoOwvA+1t9+A=') dt.setMonth(dt.getMonth() + 6);
                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=') dt.setFullYear(dt.getFullYear() + 1);
                    $scope.options2 = {
                        minDate: dt,
                        showWeeks: false
                    };
                }
                $scope.options = {
                    minDate: moment().utcOffset(0, false).toDate(),
                    showWeeks: false
                };
                $scope.options2 = angular.copy($scope.options);
                $scope.getRecurrenceSelected = function(val){
                    if($scope.obligations.calendar ==1 && $scope.obligations.recurrence_id=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                        $scope.startFields =true;
                        $scope.calendarFields=false;
                    }
                    else if($scope.obligations.calendar ==1 && $scope.obligations.recurrence_id!='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                        $scope.startFields =true;
                        $scope.calendarFields=true;
                    }
                    else{
                        $scope.startFields =false;
                        $scope.calendarFields=false;
                    }
                    if(val){
                        $scope.obligations.recurrence_start_date='';
                        $scope.obligations.recurrence_end_date='';
                    }
                   if(val=='U2FsdGVkX19UaGVAMTIzNP/rB5zlx1rEJtgL6QYTzis='){ 
                        $scope.obligations.calendar=0;
                        $scope.defaultField = false;
                        $scope.anotherField=false;
                        $scope.enddateField = false;
                        $scope.startFields = false;
                        $scope.calendarFields=false;
                    }
                   else{
                    $scope.defaultField = true;
                    $scope.anotherField=false;
                   }
                   if(val !='U2FsdGVkX19UaGVAMTIzNP/rB5zlx1rEJtgL6QYTzis='){
                     $scope.defaultField = true;
                     $scope.anotherField=true;
                     $scope.enddateField = true;
                  
                   }
                   if(val=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                    $scope.defaultField = true;
                    $scope.anotherField=true;
                    $scope.enddateField = false;
                   }
                 
                }


                $scope.getEmaildate = function(item){
                }
                $scope.options3 = {
                    minDate: moment().utcOffset(0, false).toDate(),
                    showWeeks: false
                };
                $scope.options4 = angular.copy($scope.options3);

                $scope.emailRecurrence = function(info){
                    if(info){
                        $scope.obligations.email_send_last_date='';
                    }
                    var dts = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : moment().utcOffset(0, false).toDate());
                    $scope.options4 = {};
                    
                    if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNORw6QCAdeOFRRsfh9uutYk=' &&  $scope.obligations.email_send_start_date !=null) dts.setMonth(dts.getMonth() + 1);
                    if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEyQIrGjAnMehP1bBlC0TWw=') dts.setMonth(dts.getMonth() + 3);
                    if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNFoKKPuBpe0GoOwvA+1t9+A=') dts.setMonth(dts.getMonth() + 6);
                    if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=') dts.setFullYear(dts.getFullYear() + 1);
                    $scope.options4 = {
                        minDate: dts,
                        showWeeks: false
                    };

                    if(info=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                        $scope.enddateField=false;
                    }
                    else{
                        $scope.enddateField=true;
                    }

                    if($scope.obligations.email_notification ==1 && $scope.obligations.resend_recurrence_id=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                        $scope.requiredFields =true;
                        $scope.requiredNotificationField=false;
                        $scope.enddateField=false;
                    }
                    else if($scope.obligations.email_notification ==1 && $scope.obligations.resend_recurrence_id!='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                        $scope.requiredFields =true;
                        $scope.requiredNotificationField=true;
                        $scope.enddateField=true;
                    }
                    // else{
                    //     $scope.requiredFields =false;
                    //     $scope.enddateField=false;
                    //     $scope.requiredNotificationField=false;
                    // }
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
.controller('contractReviewCtrl', function($sce,$scope, $rootScope, $state, $stateParams,$filter, contractService,userService,businessUnitService, decode, encode, $uibModal, attachmentService, $location, dateFilter){
    $rootScope.module = 'Contract';
    $rootScope.displayName = $stateParams.name;
    $rootScope.icon ='Contracts';
    $rootScope.class="contract-logo";
    $rootScope.breadcrumbcolor='contract-breadcrumb-color';  
    $scope.displayCount = $rootScope.userPagination;
    var params = {};
    $scope.loading = false;
    $scope.isWorkflow='0';
    var parentPage = $state.current.url.split("/")[1];
    
    

    $scope.getModulecontributorInfo = function(){
        if($stateParams.type)$scope.isWorkflow=($stateParams.type=='workflow')?'1':'0';
    params.contract_review_id = decode($stateParams.rId);
    params.contract_id = params.id_contract = decode($stateParams.id);
    params.id_user  = $scope.user1.id_user;
    params.user_role_id  = $scope.user1.user_role_id;
    params.id_contract_workflow  = decode($stateParams.wId);
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
    $scope.getModulecontributorInfo();
    
    if($rootScope.access=='eu'){
        var goWorkflowMdule = (parentPage == 'all-activities') ? 'app.contract.contract-module-workflow' : 'app.contract.contract-module-workflow11';
        var goReviewMdule = (parentPage == 'all-activities') ? 'app.contract.contract-module-review' : 'app.contract.contract-module-review11';
    }else{
        var goWorkflowMdule = (parentPage == 'all-activities') ? 'app.contract.contract-module-workflow' : 'app.contract.contract-module-workflow1';
        var goReviewMdule = (parentPage == 'all-activities') ? 'app.contract.contract-module-review' : 'app.contract.contract-module-review1';
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
                    if($scope.isWorkflow=='1'){
                        $state.go(goWorkflowMdule,
                        {name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,
                            moduleId:encode(moduleId),tName:topic_name,tId:topic_id,qId:encode(row.id_question),
                            wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
                    }else{                        
                        $state.go(goReviewMdule,
                        {name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,
                            moduleId:encode(moduleId),tName:topic_name,tId:topic_id,qId:encode(row.id_question),type:'review'},
                            { reload: true, inherit: false });
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
        contractService.getContractById(params).then (function(result){
            $scope.progress = result.data.progress;
            $scope.all_modules_validated = result.all_modles_validated;
            $scope.ready_for_validation = result.ready_for_validation;
            $scope.validation_status = result.validation_status;
            $scope.contractData = result.data[0];
            $scope.reviewWorkflowInfo={};
            $scope.reviewWorkflowInfo = result.review_workflow_data;
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
                        params.is_workflow=$scope.contractData.is_workflow;
                        params.contract_review_id= $scope.contractData.id_contract_review;
                        params.type='contract';
                    }else {
                        var params ={};
                        params.validation_status=3;
                        params.is_workflow=$scope.contractData.is_workflow;
                        params.contract_review_id= $scope.contractData.id_contract_review;
                        params.type ='contract';
                    }
                     contractService.ProcessValidation(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            $scope.info();
                            var goView = (parentPage == 'all-activities') ? 'app.contract.view1' : 'app.contract.view';
                    
                            if($scope.contractData.validation_contributor){
                                $state.go('app.dashboard');
                            }else{
                                if($scope.isWorkflow=='1')
                                    $state.go(goView,{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
                                else
                                    $state.go(goView,{name:$stateParams.name,id:$stateParams.id,type:'review'});
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
        var stateStr = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
        if($scope.isWorkflow=='1')
            $state.go(stateStr,{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
        else
            $state.go(stateStr,{name:$stateParams.name,id:$stateParams.id,type:'review'});
    }
    $scope.goToModuleQuestions = function (module) {
        var reviewId = module.contract_review_id;
        var moduleId = module.id_module;
        var module_name = module.module_name;
        var topic_name = module.default_topic.topic_name;
        var topic_id = encode(module.default_topic.id_topic);
        if($scope.isWorkflow=='1' && $rootScope.access !='eu'){
           $state.go(goWorkflowMdule,
                {name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,moduleId:encode(moduleId),
                    tName:topic_name,tId:topic_id,wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
        }
        if($scope.isWorkflow=='0' && $rootScope.access !='eu'){
             $state.go(goReviewMdule,
                {name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,moduleId:encode(moduleId),
                    tName:topic_name,tId:topic_id,type:'review'},{ reload: true, inherit: false });
        }
        if($scope.isWorkflow =='0' && $rootScope.access =='eu'){
            $state.go('app.contract.contract-module-review11',{name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,moduleId:encode(moduleId),
                tName:topic_name,tId:topic_id,type:'review'},{ reload: true, inherit: false
            })
        }
        if($scope.isWorkflow=='1' && $rootScope.access =='eu'){
            $state.go('app.contract.contract-module-workflow11',{
                name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,moduleId:encode(moduleId),
                tName:topic_name,tId:topic_id,wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false
            })
        }
    }
    // $scope.goToModuleQuestions = function (module) {
    //     //console.log('module info',module);
    //     var reviewId = module.contract_review_id;
    //     var moduleId = module.id_module;
    //     var module_name = module.module_name;
    //     var topic_name = module.default_topic.topic_name;
    //     var topic_id = encode(module.default_topic.id_topic);
    //     if($scope.isWorkflow=='1'){
    //        $state.go(goWorkflowMdule,
    //             {name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,moduleId:encode(moduleId),
    //                 tName:topic_name,tId:topic_id,wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
    //     }else{
    //          $state.go(goReviewMdule,
    //             {name:$stateParams.name,id:$stateParams.id,rId:encode(reviewId),mName:module_name,moduleId:encode(moduleId),
    //                 tName:topic_name,tId:topic_id,type:'review'},{ reload: true, inherit: false });
    //     }
    // }
    $scope.reviewAction = function (tableState){
        $scope.tableStateRef = tableState;
        $scope.isLoading = true;
        var pagination = tableState.pagination;
        //tableState.contract_review_id = decode($stateParams.rId);
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
            templateUrl: 'views/Manage-Users/contracts/create-edit-contract-review.html',
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
                contractService.getActionItemResponsibleUsers({'contract_id': decode($stateParams.id),'contract_review_id': decode($stateParams.rId)}).then(function(result){
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
                        params.reference_type ='contract';
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
    $scope.finalizeReviewList = function(row){
        $scope.detailsData=row;
       /* var r = confirm('Do you want to finish the review ?');
        if(r == true){
            params.created_by  = $rootScope.id_user ;
            contractService.finalizeReviewList(params).then(function(result){
                if(result.status){
                    $state.go('app.contract.view',{name:$stateParams.name,id:$stateParams.id});
                    $rootScope.toast('Success', result.message);
                    $scope.reviewAction($scope.tableStateRef);
                }else $rootScope.toast('Error', result.error, 'error',$scope.user);
            });
        }*/
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
                    params.is_workflow  = $scope.isWorkflow;
                    params.contract_workflow_id  = decode($stateParams.wId);
                    contractService.finalizeReviewList(params).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'finalize';
                            obj.action_description = 'finalize$$Contract$$'+($scope.isWorkflow=='1')?'Task$$':'Review$$('+$stateParams.name+')';
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
                            $state.go('app.contract.view',{name:$scope.detailsData.contract_name,id:encode($scope.detailsData.id_contract),type:'review'});                          
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
            $scope.attachmentList = result.data.documents.data;
            $scope.linkList = result.data.links.data;
            $scope.documentsList = result.data.all_records;
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
        var goWorkflowDashboard = (parentPage == 'all-activities')?'app.contract.workflow-dashboard':'app.contract.workflow-dashboard1';
        var goReviewDashboard = (parentPage == 'all-activities')?'app.contract.contract-dashboard':'app.contract.contract-dashboard1';
        if($scope.isWorkflow=='1' && $rootScope.access !='eu')
            $state.go(goWorkflowDashboard,{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        if($scope.isWorkflow=='0' && $rootScope.access !='eu')
            $state.go(goReviewDashboard,{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});
        if($scope.isWorkflow=='1' && $rootScope.access =='eu')
            $state.go('app.contract.workflow-dashboard11',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        if($scope.isWorkflow=='0' && $rootScope.access =='eu')
            $state.go('app.contract.contract-dashboard11',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});
    }
    $scope.goToDesign = function(){
        var goWorkflowDesign = (parentPage == 'all-activities')?'app.contract.workflow-design':'app.contract.workflow-design1';
        var goReviewDesign = (parentPage == 'all-activities')?'app.contract.review-design':'app.contract.review-design1';
        
        if($scope.isWorkflow=='1' && $rootScope.access!='eu')
            $state.go(goWorkflowDesign,{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        if($scope.isWorkflow=='0' && $rootScope.access!='eu')
            $state.go(goReviewDesign,{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});
        if($scope.isWorkflow=='1' && $rootScope.access =='eu')
            $state.go('app.contract.workflow-design11233',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        if($scope.isWorkflow=='0' && $rootScope.access =='eu')
            $state.go('app.contract.review-design12334',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});
    }
    $scope.goToChangeLog = function(){
        var goWorkflowLog = (parentPage == 'all-activities')?'app.contract.workflow-change-log':'app.contract.workflow-change-log1';
        var goReviewLog = (parentPage == 'all-activities')?'app.contract.review-change-log':'app.contract.review-change-log1';
        
        if($scope.isWorkflow=='1' && $rootScope.access !='eu'){
            $state.go(goWorkflowLog, {'name': $stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        }
        if($scope.isWorkflow=='0' && $rootScope.access !='eu')
           $state.go(goReviewLog, {'name': $stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});
       if($scope.isWorkflow=='1' && $rootScope.access =='eu'){
           $state.go('app.contract.workflow-change-log11234', {'name': $stateParams.name,id:$stateParams.id,rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
       }
       if($scope.isWorkflow=='0' && $rootScope.access =='eu'){
           $state.go('app.contract.review-change-log12345', {'name': $stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});

       }
       
    }
    $scope.initializeReview = function (val){
        var params ={};
        params.created_by = $scope.user.id_user;
        params.customer_id = $scope.user1.customer_id;
        params.contract_id = decode($stateParams.id);
        params.is_workflow = $scope.isWorkflow;
        params.calender_id = $scope.reviewWorkflowInfo.calender_id;
        if(val == true) params.contract_review_type = ($scope.isWorkflow=='1')?'adhoc_workflow':'adhoc_review';
        contractService.initializeReview(params).then(function(result){
            if(result.status){
                $rootScope.toast('Success', result.message);
                var obj = {};
                obj.action_name = 'initiate';
                obj.action_description = ($scope.isWorkflow=='1')?'initiate$$Task$$('+$stateParams.name+')':'initiate$$Review$$('+$stateParams.name+')';
                obj.module_type = $state.current.activeLink;
                obj.action_url = $location.$$absUrl;
                $rootScope.confirmNavigationForSubmit(obj);
                if($scope.isWorkflow=='1') $state.transitionTo('app.contract.contract-workflow',{name:$stateParams.name,id:$stateParams.id,rId:encode(result.data),wId:$stateParams.wId,type:'workflow'});
                else $state.transitionTo('app.contract.contract-review',{name:$stateParams.name,id:$stateParams.id,rId:encode(result.data),type:'review'});
            }else  $rootScope.toast('Error', result.error,'error',$scope.user);
        });
    }
    $scope.shiftToNewDetails = function(item) {
        var obj ={};
        obj.id_contract = decode($stateParams.id);
        obj.contract_name = $stateParams.name;
        obj.id_contract_review = item.id_contract_review;
        $scope.goToReviewWorkflow(item,obj);
    }
    $scope.goToReviewWorkflow = function(type,row){
        if($scope.user1.access=='eu'){
            var goWorkflow = (parentPage == 'all-activities') ? 'app.contract.contract-workflow' : 'app.contract.contract-workflow11';
        }else{
            var goWorkflow = (parentPage == 'all-activities') ? 'app.contract.contract-workflow' : 'app.contract.contract-workflow1';
        }

        if($scope.user1.access=='eu'){
            var goToReview = (parentPage == 'all-activities') ? 'app.contract.contract-review' : 'app.contract.contract-review11';
        }else{
            var goToReview = (parentPage == 'all-activities') ? 'app.contract.contract-review' : 'app.contract.contract-review1';
        }

        // var goWorkflow = (parentPage == 'all-activities') ? 'app.contract.contract-workflow' : 'app.contract.contract-workflow1';
        var goToView = (parentPage == 'all-activities') ? 'app.contract.view1' : 'app.contract.view';
        // var goToReview = (parentPage == 'all-activities') ? 'app.contract.contract-review' : 'app.contract.contract-review1';
        
        if(type.is_workflow==1){
            if(type.initiated){
                $state.go(goWorkflow,{name:row.contract_name,
                    id:encode(row.id_contract),
                    rId:encode(row.id_contract_review),
                    wId:encode(type.id_contract_workflow),
                    type:'workflow'});
            }else{
                $state.go(goToView, {name:row.contract_name,id:encode(row.id_contract),wId:encode(type.id_contract_workflow),type:'workflow'}, { reload: true, inherit: false });
            }
        }else{
            if(type.id_contract_review && type.initiated)
                $state.go(goToReview,{name:row.contract_name,id:encode(row.id_contract),rId:encode(type.id_contract_review),type:'review'});
            else
                $state.go(goToView,{name:row.contract_name,id:encode(row.id_contract),type:'review'});
        }
    }


    $rootScope.expert = {};
    $rootScope.validator = {};
    $rootScope.provider = {};

    $rootScope.expert.contributors = [];
    $rootScope.validator.contributors = [];
    $rootScope.provider.contributors = [];

    $scope.addContractContributors = function (info) {
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
                    if (selectedBU !== '') {
                        params.business_unit_id = selectedBU;
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
                    if (!$scope.data.provider.contributors) {
                        $scope.data.provider.contributors = [];
                    }                                  
                    var isPresent = false;
                    angular.forEach($scope.data.provider.contributors, function(i,o){
                        if (i.id_user && i.id_user === data.id_user)
                            isPresent = true;
                    });
                    if (!isPresent) {
                        $scope.data.provider.contributors.push(data);
                        $scope.showContractList2 = false;
                    } else {
                        $rootScope.toast('Error', 'Contributor already added.');
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
                    contractService.addContributors(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            var obj = {};
                            obj.action_name = 'update';
                            obj.action_description = 'update$$Cotributors$$('+$stateParams.name +' - '+ $stateParams.mName+')';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            // $scope.getContributorsList(params); //not required
                            $scope.getModulecontributorInfo();
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
.controller('contractModuleReviewCtrl', function($sce, $timeout,anchorSmoothScroll, $scope,$filter, removespecialcharFilter, underscoreaddFilter, $rootScope, $state, $stateParams, userService, businessUnitService, contractService, attachmentService, decode, encode, Upload, $uibModal, $location, dateFilter){
    var vm = this;
    //window.history.back();
    
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
       // if(tab.disabled)return;
        //tab.select();
        $timeout(function () {
            $scope.scrollIntoView(index);
        });
    }
    var parentPage = $state.current.url.split("/")[1];
    
    $scope.topic_count = 1;
    $rootScope.module = 'Contract';
    $rootScope.displayName = $stateParams.name +' - '+ $stateParams.mName ;
    $rootScope.icon ='Contracts';
    $rootScope.class="contract-logo";
    $rootScope.breadcrumbcolor='contract-breadcrumb-color';  
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

    $scope.isWorkflow='0';
    if($stateParams.type)$scope.isWorkflow=($stateParams.type=='workflow')?'1':'0';

    var Globalparams= {};
    Globalparams.contract_review_id = decode($stateParams.rId);
    if($scope.isWorkflow=='1')Globalparams.contract_workflow_id = decode($stateParams.wId);
    Globalparams.module_id = decode($stateParams.moduleId);
    Globalparams.contract_id = decode($stateParams.id);
    Globalparams.id_topic = decode($stateParams.tId);
    Globalparams.is_workflow = $scope.isWorkflow;

    // $scope.gotoElement = function (eID) {
    //     var id = decode($stateParams.qId);
    //     var element = document.getElementById("toggle-"+indx).classList.add("discussion-row");
    //     $location.hash(id);
    //     $timeout(function () {
    //         document.getElementById("toggle-"+indx).classList.remove("discussion-row");
    //     }, 3000);
    // };

    // $scope.gotoElement = function (eID) {
    //     var element = document.getElementById(eID);
    //     element.classList.add("discussion-row");
    //     $location.hash(eID);
    //     // $anchorScroll();
    //     anchorSmoothScroll.scrollTo(eID);
    //     setTimeout(function() {
    //         element.classList.remove("discussion-row");
    //     }, 3000);
    // };
   
    $scope.gotoElement = function(eID){
        var blink = document.querySelector('[tokenid="'+eID+'"]');
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
        var goWorkflowDesign = (parentPage == 'all-activities')?'app.contract.workflow-design':'app.contract.workflow-design1';
        var goReviewDesign = (parentPage == 'all-activities')?'app.contract.review-design':'app.contract.review-design1';
        var proceed=true;
        if($scope.contractModuleTopics.side_by_side_validation && row.readOnly) proceed=false;
        if(proceed){
            if($scope.isWorkflow=='1' && $rootScope.access!='eu'){
                $state.go(goWorkflowDesign,{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,
                        mId:encode($scope.contractModuleTopics.id_module),tId:encode($scope.contractModuleTopics.topics[0].id_topic),
                        qId:encode(row.id_question),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
            }
            if($scope.isWorkflow=='0' && $rootScope.access!='eu'){
                $state.go(goReviewDesign,{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,
                    mId:encode($scope.contractModuleTopics.id_module),tId:encode($scope.contractModuleTopics.topics[0].id_topic),
                    qId:encode(row.id_question),type:'review'},{ reload: true, inherit: false });
            }
            if($scope.isWorkflow=='1' && $rootScope.access =='eu'){
                $state.go('app.contract.workflow-design11233',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,
                    mId:encode($scope.contractModuleTopics.id_module),tId:encode($scope.contractModuleTopics.topics[0].id_topic),
                    qId:encode(row.id_question),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false })
            }
            if($scope.isWorkflow=='0' && $rootScope.access =='eu'){
                $state.go('app.contract.review-design12334',{name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,
                    mId:encode($scope.contractModuleTopics.id_module),tId:encode($scope.contractModuleTopics.topics[0].id_topic),
                    qId:encode(row.id_question),type:'review'},{ reload: true, inherit: false });
            }
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
    var goWorkflowMdule = (parentPage == 'all-activities') ? 'app.contract.contract-module-workflow' : 'app.contract.contract-module-workflow1';
    var goReviewMdule = (parentPage == 'all-activities') ? 'app.contract.contract-module-review' : 'app.contract.contract-module-review1';
   
    $scope.goToNextTopic = function(next) {
        if($scope.isWorkflow=='1'){
            $state.transitionTo(goWorkflowMdule,
                {name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,mName:$stateParams.mName,moduleId:$stateParams.moduleId,
                    tName:next.next_text,tId:encode(next.next),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
        }else{
            $state.transitionTo(goReviewMdule,
                    {name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,mName:$stateParams.mName,moduleId:$stateParams.moduleId,
                        tName:next.next_text,tId:encode(next.next),type:'review'},{ reload: true, inherit: false });
        }
    }
    $scope.goToPreviousTopic = function(previous) {
        if($scope.isWorkflow=='1'){            
            $state.transitionTo(goWorkflowMdule,
                {name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,mName:$stateParams.mName,moduleId:$stateParams.moduleId,
                    tName:previous.previous_text,tId:encode(previous.previous),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
        }else{
            $state.transitionTo(goReviewMdule,
                {name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,mName:$stateParams.mName,moduleId:$stateParams.moduleId,
                tName:previous.previous_text,tId:encode(previous.previous),wId:$stateParams.wId,type:'review'},{ reload: true, inherit: false });
        }
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
        tableState.is_workflow  = $scope.isWorkflow;
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
            templateUrl: 'views/Manage-Users/contracts/create-edit-contract-review.html',
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

                            if($scope.optionsData.feedback[i.id_question])
                                $scope.options[o].question_feedback = $scope.optionsData.feedback[i.id_question];
                            else $scope.options[o].question_feedback = '';

                            if($scope.optionsData.external_user_question_feedback[i.id_question])
                            $scope.options[o].external_user_question_feedback = $scope.optionsData.external_user_question_feedback[i.id_question];
                        else $scope.options[o].external_user_question_feedback = '';



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

                            $scope.optionsData.external_user_question_feedback[i.id_question] = ( $scope.optionsData.external_user_question_feedback[i.id_question]) ?  $scope.optionsData.external_user_question_feedback[i.id_question] : '';
                            $scope.optionsData.external_user_question_feedback[i.id_question+"1"] = ( $scope.optionsData.external_user_question_feedback[i.id_question+"1"]) ?  $scope.optionsData.external_user_question_feedback[i.id_question+"1"] : '';

                            $scope.optionsData.feedback[i.id_question] = ( $scope.optionsData.feedback[i.id_question]) ?  $scope.optionsData.feedback[i.id_question] : '';
                            $scope.optionsData.feedback[i.id_question+"1"] = ( $scope.optionsData.feedback[i.id_question+"1"]) ?  $scope.optionsData.feedback[i.id_question+"1"] : '';
                            
                            if(o%2==0){

                                $scope.options[o].external_user_question_feedback =  $scope.optionsData.external_user_question_feedback[i.id_question];
                                $scope.options[o].v_external_user_question_feedback =  $scope.optionsData.external_user_question_feedback[i.id_question+"1"]; 

                                $scope.options[o].question_feedback =  $scope.optionsData.feedback[i.id_question];
                                $scope.options[o].v_question_feedback =  $scope.optionsData.feedback[i.id_question+"1"]; 
                            }else{
                                $scope.options[o].question_feedback =  $scope.optionsData.feedback[i.id_question+"1"];
                                $scope.options[o].v_question_feedback =  $scope.optionsData.feedback[i.id_question];

                                $scope.options[o].external_user_question_feedback =  $scope.optionsData.external_user_question_feedback[i.id_question+"1"];
                                $scope.options[o].v_external_user_question_feedback =  $scope.optionsData.external_user_question_feedback[i.id_question];
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
                    obj.is_workflow = $scope.isWorkflow;
                    if($scope.isWorkflow=='1'){
                        obj.id_contract_workflow= decode($stateParams.wId);
                    }
                    
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
                contractService.getActionItemResponsibleUsers({'contract_id': $scope.contract_id,'contract_review_id': $scope.contract_review_id}).then(function(result){
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
                        params.is_workflow = $scope.isWorkflow;
                        params.reference_type ='contract';
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
                        params.is_workflow = $scope.isWorkflow;
                        params.contract_workflow_id  = decode($stateParams.wId);
                        if(type=='add' && question ==''&& topic ==''){
                            params.reference_type ='topic';
                        }
                        else{
                            params.reference_type ='question';
                        }
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
                $scope.data = {};
                $scope.data.expert = {};
                $scope.data.validator = {};
                $scope.data.provider = {};
                
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
                    params.contract_id = $scope.contract_id;
                    params.type = 'contributor';
                    params.user_role_id = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.user_id = $scope.user.id_user;
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
                    params.contract_id = $scope.contract_id;
                    params.type = 'contributor';
                    params.user_role_id = $scope.user1.user_role_id;
                    params.customer_id  = $scope.user1.customer_id;
                    params.user_id = $scope.user.id_user;
                    if (selectedBU !== '') {
                        params.business_unit_id = selectedBU;
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
                    if (!$scope.data.provider.contributors) {
                        $scope.data.provider.contributors = [];
                    }                                  
                    var isPresent = false;
                    angular.forEach($scope.data.provider.contributors, function(i,o){
                        if (i.id_user && i.id_user === data.id_user)
                            isPresent = true;
                    });
                    if (!isPresent) {
                        $scope.data.provider.contributors.push(data);
                        $scope.showContractList2 = false;
                    } else {
                        $rootScope.toast('Error', 'Contributor already added.');
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
                    contractService.addContributors(params).then(function (result) {
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
                }else $rootScope.toast('Error', result.error, 'error',$scope.user);
            });
        }
    }
    var goWorkflow = (parentPage == 'all-activities') ? 'app.contract.contract-workflow' : 'app.contract.contract-workflow1';
    var goReview = (parentPage == 'all-activities') ? 'app.contract.contract-review' : 'app.contract.contract-review1';
   
    $scope.cancel = function(){
        if($scope.isWorkflow=='1')
            $state.go(goWorkflow, {name:$stateParams.name,id: $stateParams.id, rId:$stateParams.rId,wId:$stateParams.wId,type:'workflow'});
        else
            $state.go(goReview, {name:$stateParams.name,id: $stateParams.id, rId:$stateParams.rId,type:'review'});
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
                    else{

                     $scope.options[o].v_question_answer = '';
                     
                    }    
                    data.feedback[i.id_question] = (data.feedback[i.id_question]) ? data.feedback[i.id_question] : '';
                    data.feedback[i.id_question+"1"] = (data.feedback[i.id_question+"1"]) ? data.feedback[i.id_question+"1"] : '';
                    data.external_user_question_feedback[i.id_question] = (data.external_user_question_feedback[i.id_question]) ? data.external_user_question_feedback[i.id_question] : '';
                    data.external_user_question_feedback[i.id_question+"1"] = (data.external_user_question_feedback[i.id_question+"1"]) ? data.external_user_question_feedback[i.id_question+"1"] : '';

                    
                    if(o%2==0){
                        $scope.options[o].question_feedback = data.feedback[i.id_question];
                        $scope.options[o].v_question_feedback = data.feedback[i.id_question+"1"]; 

                        $scope.options[o].external_user_question_feedback = data.external_user_question_feedback[i.id_question];
                        $scope.options[o].v_external_user_question_feedback = data.external_user_question_feedback[i.id_question+"1"]; 

                    }else{
                        $scope.options[o].question_feedback = data.feedback[i.id_question+"1"];
                        $scope.options[o].v_question_feedback = data.feedback[i.id_question];

                        $scope.options[o].external_user_question_feedback = data.external_user_question_feedback[i.id_question+"1"];
                        $scope.options[o].v_external_user_question_feedback = data.external_user_question_feedback[i.id_question];

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
                            if($scope.isWorkflow=='1')
                                $state.go(goWorkflow, options);
                            else
                                $state.go(goReview, options);
                        }
                        else if($rootScope.access !='eu') {
                            if($scope.isWorkflow=='1')
                                $state.go(goWorkflowMdule, options, { reload: true, inherit: false });
                            else
                                $state.go(goReviewMdule, options, { reload: true, inherit: false });
                        }    
                        if(opt=='exit' && $rootScope.access=='eu'){
                            if($scope.isWorkflow=='1')
                                $state.go('app.contract.contract-workflow11', options);
                            else
                              $state.go('app.contract.contract-review11', options);
                        }
                        else if($rootScope.access=='eu'){
                            if($scope.isWorkflow=='1')
                               $state.go('app.contract.contract-module-workflow11', options, { reload: true, inherit: false });  
                            else
                                $state.go('app.contract.contract-module-review11', options, { reload: true, inherit: false });
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
        //$scope.getTopicQuestions(params); //not required
        //$scope.goToNextTopic(obj);
        /*$state.go('app.contract.contract-module-review',
            {name:$stateParams.name,id:$stateParams.id,rId:$stateParams.rId,mName:$stateParams.mName,moduleId:$stateParams.moduleId,
                tName:obj.next_text,tId:encode(obj.next)});*/

    }
    $scope.saveAndExit = function(options){
        
       $scope.goToSave(options, {name:$stateParams.name,id: $stateParams.id, rId:$stateParams.rId}, 'exit');
        /*$state.go('app.contract.contract-review', {name:$stateParams.name,id: $stateParams.id, rId:$stateParams.rId});*/
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

                                if($scope.optionsData.feedback[i.id_question])
                                    $scope.options[o].question_feedback = $scope.optionsData.feedback[i.id_question];
                                else $scope.options[o].question_feedback = '';

                                if($scope.optionsData.external_user_question_feedback[i.id_question])
                                $scope.options[o].external_user_question_feedback = $scope.optionsData.external_user_question_feedback[i.id_question];
                            else $scope.options[o].external_user_question_feedback = '';

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

                                $scope.optionsData.external_user_question_feedback[i.id_question] = ( $scope.optionsData.external_user_question_feedback[i.id_question]) ?  $scope.optionsData.external_user_question_feedback[i.id_question] : '';
                                $scope.optionsData.external_user_question_feedback[i.id_question+"1"] = ( $scope.optionsData.external_user_question_feedback[i.id_question+"1"]) ?  $scope.optionsData.external_user_question_feedback[i.id_question+"1"] : '';


                                $scope.optionsData.feedback[i.id_question] = ( $scope.optionsData.feedback[i.id_question]) ?  $scope.optionsData.feedback[i.id_question] : '';
                                $scope.optionsData.feedback[i.id_question+"1"] = ( $scope.optionsData.feedback[i.id_question+"1"]) ?  $scope.optionsData.feedback[i.id_question+"1"] : '';
                                if(o%2==0){                                

                                    $scope.options[o].external_user_question_feedback =  $scope.optionsData.external_user_question_feedback[i.id_question];
                                    $scope.options[o].v_external_user_question_feedback =  $scope.optionsData.external_user_question_feedback[i.id_question+"1"]; 

                                    $scope.options[o].question_feedback =  $scope.optionsData.feedback[i.id_question];
                                    $scope.options[o].v_question_feedback =  $scope.optionsData.feedback[i.id_question+"1"]; 
                                }else{

                                    $scope.options[o].external_user_question_feedback =  $scope.optionsData.external_user_question_feedback[i.id_question+"1"];
                                    $scope.options[o].v_external_user_question_feedback =  $scope.optionsData.external_user_question_feedback[i.id_question];

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
                        obj.is_workflow = $scope.isWorkflow;
                        if($scope.isWorkflow=='1'){
                            obj.id_contract_workflow= decode($stateParams.wId);
                        }
                        
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
        // }
        /*if(!$scope.question.question_answer) {
            var r=confirm("Please save your answer first. ");
            $scope.deleConfirm = r;  */          
        // }else {
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
                        params.created_by =  $scope.user1.id_user;
                        params.type='contract';
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

    $scope.goToDesign = function(data){
        var goWorkflowDesign = (parentPage == 'all-activities')?'app.contract.workflow-design':'app.contract.workflow-design1';
        var goReviewDesign = (parentPage == 'all-activities')?'app.contract.review-design':'app.contract.review-design1';
        
        if($scope.isWorkflow=='1' && $rootScope.access !='eu')
            $state.go(goWorkflowDesign,{name:$stateParams.name,id:$stateParams.id,rId:encode(data.contract_review_id),wId:$stateParams.wId,type:'workflow'});
        if($scope.isWorkflow=='0' && $rootScope.access !='eu')
            $state.go(goReviewDesign,{name:$stateParams.name,id:$stateParams.id,rId:encode(data.contract_review_id),type:'review'});
        if($scope.isWorkflow=='1' && $rootScope.access =='eu')
            $state.go('app.contract.workflow-design11233',{name:$stateParams.name,id:$stateParams.id,rId:encode(data.contract_review_id),wId:$stateParams.wId,type:'workflow'});
        if($scope.isWorkflow=='0' && $rootScope.access =='eu')
            $state.go('app.contract.review-design12334',{name:$stateParams.name,id:$stateParams.id,rId:encode(data.contract_review_id),type:'review'});
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
                $scope.optionsData.external_user_question_feedback[item.id_question] = item.v_external_user_question_feedback;


                item.state=false;
                item.state1=false;
               if(item.help_text)
                   item.help_text = $sce.trustAsHtml('<pre class="text-tooltip" style="text-align:left;">'+item.help_text+'</pre>');
            }
        });
        $scope.contractModuleTopics.topics[0].questions = angular.copy($scope.dualQuestions);
        
    } 

   

    $scope.goToRed = function(item,ind,res){
        $scope.optionsData.answers[item.id_question] = '';
        document.getElementById("toggle-"+ind).classList.remove('green-color','red-color');
        document.getElementById("toggle-"+ind).classList.add('blue-color');
        document.getElementById("question_"+item.id_question+"_red").classList.remove('red-circle');
        document.getElementById("question_"+item.id_question+"_blue").classList.add('blue-circle');
        document.getElementById("question_"+item.id_question+"_green").classList.remove('green-circle');
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

})
.controller('contractDashboardCtrl', function($scope, $rootScope, $state, $stateParams,$timeout,$filter, contractService, decode, encode, $uibModal,userService,AuthService){
    $scope.dashboardData = {};
    $rootScope.module = 'Contract Dashboard';
    $rootScope.displayName = $stateParams.name;
    $rootScope.icon ='Contracts';
    $rootScope.class="contract-logo";
    $rootScope.breadcrumbcolor='contract-breadcrumb-color';  
    var parentPage = $state.current.url.split("/")[1];
    
    $scope.isWorkflow='0';
    $scope.isWorkflow=($stateParams.type=='workflow')?'1':'0';
    var params={};
    params.contract_id = decode($stateParams.id);
    params.contract_review_id = decode($stateParams.rId);
    if($stateParams.wId)params.contract_workflow_id = decode($stateParams.wId);
    params.is_workflow = $scope.isWorkflow;
    params.id_user  = $scope.user1.id_user;
    params.user_role_id  = $scope.user1.user_role_id;
    $scope.showData={};

    
    $scope.goToDetails1 = function(){
        var stateStr = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
        if($scope.isWorkflow=='1')
            $state.go(stateStr,{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
        else
            $state.go(stateStr,{name:$stateParams.name,id:$stateParams.id,type:'review'});
    }
    
    $scope.getDashboardData = function(params){
        contractService.getDashboard(params).then(function(result){
            if(result.status){
                if(result.special_message){
                    $rootScope.toast('customError',result.message);
                }
                $scope.dashboardData =  result.data;
                } 
                })
            }
            $scope.getDashboardData(params);
            
            
  

   
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
    $scope.previewfeedback=function(row) { 
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
    $scope.previewdiscussion =function(row){
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
                params.is_workflow = $scope.isWorkflow;
                    contractService.getTopicQuestionsById(params).then(function(result){
                        $scope.contractModuleTopics = result.data.questions;
                        $scope.side_by_side=result.data.side_by_side_validation;
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
        var goWorkflowLog = (parentPage == 'all-activities')?'app.contract.workflow-change-log':'app.contract.workflow-change-log1';
        var goReviewLog = (parentPage == 'all-activities')?'app.contract.review-change-log':'app.contract.review-change-log1';
         //TO DO workflows
         if($scope.isWorkflow=='1' && $rootScope.access !='eu'){
             $state.go(goWorkflowLog, {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.dashboardData.contract_review_id),wId:$stateParams.wId,type:'workflow'});
         }
         if($scope.isWorkflow=='0' && $rootScope.access !='eu')
            $state.go(goReviewLog, {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.contractInfo.id_contract_review),type:'review'});
        if($scope.isWorkflow=='1' && $rootScope.access =='eu'){
            $state.go('app.contract.workflow-change-log11234', {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.contractInfo.id_contract_review),wId:$stateParams.wId,type:'workflow'});
        }
        if($scope.isWorkflow=='0' && $rootScope.access =='eu'){
            $state.go('app.contract.review-change-log12345', {'name': $stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});

        }
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
        params.contract_review_id = $scope.dashboardData.contract_review_id;
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.contract_workflow_id = $scope.dashboardData.contract_workflow_id;
        params.is_workflow = $scope.isWorkflow;
        contractService.exportDashboardData(params).then(function(result){
            
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
                        
                        window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+$rootScope.id_user+'&access_token='+obj.access_token;
                    }
                });
            }
            else{
                $rootScope.toast('Error',result.error);
            }

        });
    };
    $scope.isNaN= function (n) {
        return isNaN(n);
    }
    $scope.goToDetails1 = function(){
        var stateStr = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
        if($scope.isWorkflow=='1')
            $state.go(stateStr,{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
        else
            $state.go(stateStr,{name:$stateParams.name,id:$stateParams.id,type:'review'});
    }

    $scope.goToTrends = function(){
        var goToTrends = (parentPage == 'all-activities')?'app.contract.review-trends':'app.contract.review-trends1';
        if($rootScope.access!='eu')
            $state.go(goToTrends, {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.contractInfo.id_contract_review),type:'review'});
        if($rootScope.access=='eu'){
            $state.go('app.contract.review-trends11122', {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.contractInfo.id_contract_review),type:'review'});
        }
    }
})
.controller('ReviewDesign', function($timeout,$scope, $rootScope,$state, $stateParams, $filter,encode, decode, contractService, $window, $location,anchorSmoothScroll){
    $rootScope.module = ($stateParams.type=='workflow')?'Contract Task Discussion':'Contract Review Discussion';
    $rootScope.displayName = $stateParams.name;
    $rootScope.icon ='Contracts';
    $rootScope.class="contract-logo";
    $rootScope.breadcrumbcolor='contract-breadcrumb-color'; 
    $scope.curReviewId = decode($stateParams.rId);
    $scope.isWorkflow='0';
    $scope.isWorkflow=($stateParams.type=='workflow')?'1':'0';
    var parentPage = $state.current.url.split("/")[1];

    
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
        params.type='contract';
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
                                question.second_opinion= moment(question.second_opinion).utcOffset(0, false).toDate()
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
        params1.type='contract';
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
        params.type ='contract';
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
        var goWorkflowMdle = (parentPage == 'all-activities')?'app.contract.contract-module-workflow':'app.contract.contract-module-workflow1';
        var goReviewMdle = (parentPage == 'all-activities')?'app.contract.contract-module-review':'app.contract.contract-module-review1';
        
        if($scope.isWorkflow=='1' && $rootScope.access !='eu'){
            $state.go(goWorkflowMdle,
                {name:$stateParams.name,id:$stateParams.id,rId:encode($scope.curReviewId),mName:module.module_name,
                    moduleId:encode(module.id_module),tName:topic.topic_name,tId:encode(topic.id_topic),
                    qId:encode(questionId),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
        }
        if($scope.isWorkflow=='0' && $rootScope.access !='eu'){
            $state.go(goReviewMdle,
            {name:$stateParams.name,id:$stateParams.id,rId:encode($scope.curReviewId),mName:module.module_name,
                moduleId:encode(module.id_module),tName:topic.topic_name,tId:encode(topic.id_topic),qId:encode(questionId),type:'review'},{ reload: true, inherit: false });
        }

        if($scope.isWorkflow=='1' && $rootScope.access =='eu'){
            $state.go('app.contract.contract-module-workflow11',
                {name:$stateParams.name,id:$stateParams.id,rId:encode($scope.curReviewId),mName:module.module_name,
                    moduleId:encode(module.id_module),tName:topic.topic_name,tId:encode(topic.id_topic),
                    qId:encode(questionId),wId:$stateParams.wId,type:'workflow'},{ reload: true, inherit: false });
        }
        if($scope.isWorkflow=='0' && $rootScope.access =='eu'){
            $state.go('app.contract.contract-module-review11',
            {name:$stateParams.name,id:$stateParams.id,rId:encode($scope.curReviewId),mName:module.module_name,
                moduleId:encode(module.id_module),tName:topic.topic_name,tId:encode(topic.id_topic),qId:encode(questionId),type:'review'},{ reload: true, inherit: false });
        }

    };
})
.controller('ReviewChangeLogCtrl', function($scope, $rootScope, $state, $stateParams,$filter, decode, contractService, userService, AuthService){
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
    $scope.contract_name = $stateParams.name;
    $rootScope.breadcrumbcolor='contract-breadcrumb-color'; 
    $rootScope.class='contract-logo';
    $rootScope.icon ='Contracts';
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
.controller('contractLogCtrl', function($state, $scope, $rootScope, $stateParams, $filter,decode, contractService,userService,AuthService){
    $rootScope.module = 'Contract Logs';
    $rootScope.displayName = $stateParams.name;
    $rootScope.breadcrumbcolor='contract-breadcrumb-color'; 
    $rootScope.icon='Contracts';
    $rootScope.class='contract-logo';
    $scope.displayCount = $rootScope.userPagination;

    contractService.getLogs({'contract_id':decode($stateParams.id)}).then (function(result){
        if(result.status){
            $scope.currentContract = result.data.current_cotract_detailis;
            $scope.contractLogOptions = result.data.contract_log_options;
        }
    });
    $scope.getContractLogs = function(logId) {
        var param = {};
        param.contract_log_id = logId;
        param.contract_id = decode($stateParams.id);
        contractService.getLogs(param).then (function(result){
            if(result.status){
                $scope.currentContract = result.data.current_cotract_detailis;
                $scope.contractLogOptions = result.data.contract_log_options;
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
        tableState.reference_type =  'contract';
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
.controller('subContractCreateCtrl', function($scope, $rootScope, $state, $localStorage,$filter, $stateParams,catalogueService,$location, encode, decode,templateService, tagService,providerService, contractService, masterService,Upload, dateFilter){
    $scope.currencyList = [];
    $scope.templateList = [];
    $scope.disabled =false;
    $scope.relationshipCategoryList = {};
    $scope.relationshipClassificationList = {};
    $scope.contract = {};
    $scope.file={};
    $scope.links_delete = [];
    $rootScope.module = '';
    $rootScope.displayName = '';
    $rootScope.breadcrumbcolor=''; 
    $rootScope.icon ='';
    $rootScope.class='';
    $scope.isWorkflow = '0';
    $localStorage.curUser.data.filters = {};
    if($stateParams.wId)$scope.workflowId = decode($stateParams.wId);
    if($stateParams.type)$scope.isWorkflow = ($stateParams.type =='workflow')?'1':'0';

    masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
        $scope.currencyList = result.data;
    });
    // tagService.list({'status':1,'tag_type':'contract_tags'}).then(function(result){
    //     if (result.status) {
    //         $scope.tags = result.data;
    //     }
    // });
    tagService.groupedTags({'status':1,'tag_type':'contract_tags'}).then(function(result){
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

    contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
        $scope.relationshipCategoryList = result.drop_down;
    });
    contractService.getRelationshipClassiffication({'customer_id': $scope.user1.customer_id}).then(function(result){
        $scope.relationshipClassificationList = result.data;
    });
    catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
        $scope.selectedInfoProvider = result.data;
        });    
    // providerService.list({'customer_id': $scope.user1.customer_id,'status':1,'all_providers':true}).then(function(result){
    //     $scope.providers = result.data.data;
    // });

    contractService.generateContractId({'customer_id':$scope.user1.customer_id,'type':'sub_contract'}).then(function(result){
        if(result.status){
            $scope.sub_contract_unique_id = result.data.sub_contract_unique_id;
        }
    });
    
    
    if($stateParams.id){
        $rootScope.module = 'Contract';
        $rootScope.displayName = $stateParams.name;
        var params = {};
        params.id_contract = decode($stateParams.id);
        
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.is_workflow  = $scope.isWorkflow;
        contractService.getContractById(params).then(function(result){
            $scope.contract = result.data[0];
            
            $scope.contract['contract_name'] = '';
            $scope.contract.contract_start_date = moment($scope.contract.contract_start_date).utcOffset(0, false).toDate();
            $scope.contract.contract_end_date = moment($scope.contract.contract_end_date).utcOffset(0, false).toDate();
            $scope.contract['auto_renewal'] = 0;
            $scope.contract['contract_value'] = '';
            $scope.contract['classification_id'] = '';
            $scope.contract['relationship_category_id'] = '';
            $scope.contract['delegate_id'] = '';
            $scope.contract['description'] = '';
            $scope.contract['internal_contract_sponsor'] = '';
            $scope.contract['provider_contract_sponsor'] = '';
            $scope.contract['internal_partner_relationship_manager'] = '';
            $scope.contract['provider_partner_relationship_manager'] = '';
            $scope.contract['internal_contract_responsible'] = '';
            $scope.contract['provider_contract_responsible'] = '';
            $scope.contract['attachment'] = '';
            //$scope.file['attachment'] = '';
            $scope.getContractDelegates($scope.contract.business_unit_id,$scope.contract.id_contract);
            $scope.contract['parent_contract_id'] = params.id_contract;
            $scope.dat = ($scope.contract['contract_end_date'] - moment("1970-01-01").utcOffset(0, false).toDate())/1000/60/60/24;
            if($scope.dat==0)
                $scope.contract['contract_end_date']='';
        });
    }
    $scope.title = 'general.create';
    $scope.bottom = 'general.save';
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
    $scope.contractLinks=[];
    $scope.contractLink={};
    $scope.verifyLink = function(data){
        if(data !={}){
            $scope.contractLinks.push(data);
            $scope.contractLink={};
        }
    }
    $scope.removeLink = function(index){
        var r=confirm(filter('translate')('general.alert_continue'));
        if(r==true){
            $scope.contractLinks.splice(index, 1);
        }                    
    }
    $scope.deleteFile = function(index,row){
        var r=confirm(filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true){
            $scope.contract.attachment.links.splice(index,1);
            var obj={}; obj.id_document=row.id_document;
            $scope.links_delete.push(obj) ;
        }
    }
    $scope.lock=false;
    $scope.updateLockingStatus = function(id){
        $scope.contract.is_template_lock =id;
        if(id){
            $scope.lock= true;
        }
        else{
            $scope.lock=false;
        }
    }

    $scope.resetLockingStatus = function(id){
        $scope.contract.is_template_lock =id;
        if(id){
            $scope.lock= false;
        }
        else{
            $scope.lock=true;
        }
    }
    $scope.subContractCreate = function (data1,unique_id){
        
        var contract = angular.copy(data1)
        
        delete data1.contract_unique_id;
        contract.contract_unique_id = unique_id;
        contract.created_by = $scope.user.id_user;
        contract.customer_id = $scope.user1.customer_id;
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
        $scope.options = {};
        // if(contract.contract_tags){
        //     angular.forEach($scope.tags, function(i,o){
        //         $scope.options[o] = {};
        //         $scope.options[o].tag_id = i.tag_id;
        //         $scope.options[o].tag_type = i.tag_type;
        //         if(i.tag_type =='date')
        //             $scope.options[o].tag_option = dateFilter(data1.contract_tags[i.tag_id],'yyyy-MM-dd');
        //         else if(i.tag_type !='date')
        //             $scope.options[o].tag_option = data1.contract_tags[i.tag_id];
        //         else $scope.options[o].tag_option = '';         
        //     });
        // }        
        // contract.contract_tags = $scope.options;

        $scope.grouped_tags = {};
        if(contract.grouped_tags){
        angular.forEach($scope.tagsBuilding, function(k,ko){
        $scope.options = {};
        angular.forEach(k.tag_details, function(i,o){
            $scope.options[o] = {};
            $scope.options[o].tag_id = i.tag_id;
            $scope.options[o].tag_type = i.tag_type;    
            $scope.options[o].multi_select = i.multi_select;  
            $scope.options[o].selected_field = i.selected_field;
            $scope.options[o].selected_field = i.selected_field;              
            if($scope.contract.grouped_tags.feedback !=undefined)
            $scope.options[o].comments = $scope.contract.grouped_tags.feedback[i.tag_id];
            else $scope.options[o].comments = '';

            if(i.tag_type =='date')
                $scope.options[o].tag_option = dateFilter(data1.grouped_tags[i.tag_id],'yyyy-MM-dd');
            else if(i.tag_type !='date')
                $scope.options[o].tag_option = data1.grouped_tags[i.tag_id];
            else $scope.options[o].tag_option = '';        
            });
            $scope.grouped_tags[ko] = {};
            $scope.grouped_tags[ko]['tag_details'] = {};
            $scope.grouped_tags[ko]['tag_details'] = $scope.options;
                });
            } 
        contract.grouped_tags =  $scope.grouped_tags;
        contract.links = $scope.contractLinks
        var params = {};
        angular.copy(contract,params);
        params.updated_by = $scope.user.id_user;            
        if(moment( params.contract_end_date).utcOffset(0, false).toDate() <= moment( params.contract_start_date).utcOffset(0, false).toDate()){
            alert($filter('translate')('general.alert_start_date_less_end'));
        }else{
            if(!params.parent_contract_id){
                params.parent_contract_id = $scope.contract.id_contract;
            }
            if (params.id_contract) {
                delete params.id_contract;
            }
            Upload.upload({
                url: API_URL+'Contract/add',
                data: {
                    'file' : $scope.file.attachment,
                    'contract': params
                }
            }).then(function(resp){
                if(resp.data.status){
                    $state.go('app.contract.view',{name: resp.data.contract_data.contract_name,id: encode(resp.data.contract_data.contract_id),type:'review'}); 
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
    $scope.cancel = function(){
        //$window.history.back();
        if($stateParams.id){
            $rootScope.module = 'Contract Details';
            $rootScope.displayName = $stateParams.name;
            if($scope.isWorkflow=='1'){
               $state.go('app.contract.view',{name: $stateParams.name,
                        id: $stateParams.id,wId:$stateParams.wId,type:'workflow'});
            }else{
                $state.go('app.contract.view',{name: $stateParams.name,
                    id: $stateParams.id,type:'review'});
            }
            // $state.go('app.contract.view',{name:$stateParams.name,id:$stateParams.id});
        }
       else $state.go('app.contract.all-contracts');
    }
    $scope.enableTemplate = true;
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
})
.controller('contractFileLogCtrl', function($scope, $rootScope, $stateParams,$filter, decode, contractService, userService){
    $scope.FileList = [];
    $rootScope.icon ='Contracts';
    $rootScope.class="contract-logo";
    $rootScope.breadcrumbcolor='contract-breadcrumb-color'; 


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
})
.controller('contractTrendsCtrl', function($scope, $rootScope, $state, $stateParams,$timeout,$filter, contractService, decode, encode, $uibModal,userService,AuthService){
    $scope.trendsData = {};
    $rootScope.module = 'Contract Trends';
    $rootScope.displayName = $stateParams.name;
    $rootScope.breadcrumbcolor='contract-breadcrumb-color'; 
    $rootScope.icon ='Contracts';
    $rootScope.class='contract-logo';
    $scope.isWorkflow='0';
    $scope.isWorkflow=($stateParams.type=='workflow')?'1':'0';
    var parentPage = $state.current.url.split("/")[1];
   
    var params={};
    params.contract_id = decode($stateParams.id);
    params.contract_review_id = decode($stateParams.rId);
    if($stateParams.wId)params.contract_workflow_id = decode($stateParams.wId);
    params.is_workflow = $scope.isWorkflow;
    params.id_user  = $scope.user1.id_user;
    params.user_role_id  = $scope.user1.user_role_id;
    $scope.showData={};


    $scope.goToDetails1 = function(){
        var stateStr = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
        if($scope.isWorkflow=='1')
            $state.go(stateStr,{name:$stateParams.name,id:$stateParams.id,wId:$stateParams.wId,type:'workflow'});
        else
            $state.go(stateStr,{name:$stateParams.name,id:$stateParams.id,type:'review'});
    }
    
    $scope.getTrendsData = function(params){
        contractService.getTrends(params).then(function(result){
            if(result.status)
                $scope.trendsData =  result.data;
           // $scope.showData = $scope.trendsData.modules;
        })
    }
    $scope.getTrendsData(params);
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
        })
    }
    $scope.init();
    $scope.goToNext = function(data,isNext){
        var params={};
        params.contract_id = decode($stateParams.id);
        if(isNext)
            params.trend_type = 'next';
        else params.trend_type = 'prev';
        $scope.trend_type = params.trend_type;
        params.is_workflow = $scope.isWorkflow;
        params.offset = data.offset;
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.contract_review_id = data.contract_review_id;
        if($scope.isWorkflow=='1'){
            params.contract_workflow_id = data.contract_workflow_id;
        }
        $scope.getTrendsData(params);
        $timeout(function () {
            $scope.init();
        },100);
    }    

    $scope.goToChangeLog = function(){
        var goWorkflowLog = (parentPage == 'all-activities') ? 'app.contract.workflow-change-log' : 'app.contract.workflow-change-log1';
        var goReviewLog = (parentPage == 'all-activities') ? 'app.contract.review-change-log' : 'app.contract.review-change-log1';
        
         if($scope.isWorkflow=='1' && $rootScope.access!='eu'){
             $state.go(goWorkflowLog, {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.trendsData.contract_review_id),wId:$stateParams.wId,type:'workflow'});
         }
         if($scope.isWorkflow=='0' && $rootScope.access!='eu')
            $state.go(goReviewLog, {'name': $stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});

        if($scope.isWorkflow=='1' && $rootScope.access =='eu'){
            $state.go('app.contract.workflow-change-log11234', {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.trendsData.contract_review_id),wId:$stateParams.wId,type:'workflow'});
        }
        if($scope.isWorkflow=='0' && $rootScope.access =='eu'){
            $state.go('app.contract.review-change-log12345', {'name': $stateParams.name,id:$stateParams.id,rId:$stateParams.rId,type:'review'});

        }
     
    }
    $scope.exportReview = function (){
        var params={};
        params.contract_id = params.id_contract= decode($stateParams.id);
        params.id_user=  $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.contract_review_id = $scope.trendsData.contract_review_id;
        params.is_workflow = $scope.isWorkflow;
        params.export_type = 'trends';
        params.offset = $scope.trendsData.offset;
        params.trend_type = $scope.trend_type;
        params.contract_workflow_id = $scope.trendsData.contract_workflow_id;
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
    $scope.exportTrendsData = function (){
        var params={};
        params.contract_id = decode($stateParams.id);
        params.contract_review_id = $scope.trendsData.contract_review_id;
        params.id_user  = $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        params.contract_workflow_id = $scope.trendsData.contract_workflow_id;
        params.is_workflow = $scope.isWorkflow;
        params.export_type = 'trends';
        params.offset = $scope.trendsData.offset;
        contractService.exportDashboardData(params).then(function(result){            
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
                        window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+$rootScope.id_user+'&access_token='+obj.access_token;
                    }
                });
            }
            else{
                $rootScope.toast('Error',result.error);
            }
        });
    };
    $scope.isNaN= function (n) {
        return isNaN(n);
    }

    if($rootScope.access=='eu'){
        var goWorkflowDsbrd = (parentPage == 'all-activities') ? 'app.contract.workflow-dashboard' : 'app.contract.workflow-dashboard11';
        var goReviewDsbrd = (parentPage == 'all-activities') ? 'app.contract.contract-dashboard' : 'app.contract.contract-dashboard11';
    }else{
        var goWorkflowDsbrd = (parentPage == 'all-activities') ? 'app.contract.workflow-dashboard' : 'app.contract.workflow-dashboard1';
        var goReviewDsbrd = (parentPage == 'all-activities') ? 'app.contract.contract-dashboard' : 'app.contract.contract-dashboard1';
    }
    
    $scope.goToDashboard = function(){

        if($scope.isWorkflow=='1'){
            $state.go(goWorkflowDsbrd, {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.contractInfo.id_contract_review),wId:encode($scope.contractInfo.id_contract_workflow),type:'workflow'});
        }else
            $state.go(goReviewDsbrd, {'name': $stateParams.name,id:$stateParams.id,rId:encode($scope.contractInfo.id_contract_review),type:'review'});
    }
    $scope.goToDashboardByDate = function(obj){
        if($scope.isWorkflow=='1' && $rootScope.access !='eu'){
            $state.go(goWorkflowDsbrd, {'name': $stateParams.name,id:$stateParams.id,rId:encode(obj.contract_review_id),wId:encode(obj.contract_workflow_id),type:'workflow'});
        }
        if($scope.isWorkflow=='0' && $rootScope.access !='eu')
            $state.go(goReviewDsbrd, {'name': $stateParams.name,id:$stateParams.id,rId:encode(obj.contract_review_id),type:'review'});
        if($scope.isWorkflow=='1' && $rootScope.access =='eu'){
            $state.go('app.contract.workflow-dashboard11',{'name': $stateParams.name,id:$stateParams.id,rId:encode(obj.contract_review_id),wId:encode(obj.contract_workflow_id),type:'workflow'})
        }
         if($scope.isWorkflow=='0' && $rootScope.access =='eu'){
             $state.go('app.contract.contract-dashboard11',{'name': $stateParams.name,id:$stateParams.id,rId:encode(obj.contract_review_id),type:'review'})
         }
    }
})
.controller('allActivitiesListCtrl', function($sce,$scope, $rootScope, $state, $stateParams, $filter,$localStorage, dateFilter, $timeout,$uibModal, providerService, contractService, businessUnitService, encode, AuthService,userService){
    var param ={};
    $scope.del=0;
    $scope.contract_status='';
    $scope.can_access=1;
    $scope.date_field='';
    $scope.date_period='';
    $scope.provider_name='';
    $scope.business_unit_id='';
    $scope.relationship_category_id='';
    $scope.searchFields = {};
    $scope.totalRecords=0;
    $scope.resetPagination=false;
    $scope.displayCount = $rootScope.userPagination;
    $localStorage.curUser.data.filters.allContracts = undefined;
    $scope.dynamicPopover = { templateUrl: 'myPopoverTemplate.html' };
    if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
        $scope.del=1;
    }


    $scope.advancedFilterActivities = function(){
        $scope.filterCreate = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/advancedFilterContract.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';


                $scope.filterListActivities=function(){
                var params ={};
                // params.user_id=$scope.user.id_user;
                params.module='all_activities';
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
                $scope.filterListActivities();


            $scope.flterDelete=function(rowdata){
                var r = confirm($filter('translate')('general.alert_Delete_filter'));
                if(r==true){
                    contractService.deleteContractFlter({'id_master_filter':rowdata.id_master_filter}).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success', result.message);
                            $scope.filterListActivities();
                            $scope.activitiesFilter();
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
            templateUrl: 'views/Manage-Users/contracts/create-activites-filter.html',
            controller: function ($uibModalInstance,$scope,item) {
            $scope.bottom ='general.save';
             $scope.title='controller.add_filter_criteria'

                contractService.getContractDomain({'domain_module': 'all_activities'}).then(function(result){
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

                    if($scope.feldName=='Expert Contributor'){
                        var params={};
                        params.contributor_type='expert';
                        contractService.getContributersFilter(params).then(function (result) {
                            $scope.expertContributor = result.data;
                        })
                            }

                    if($scope.feldName=='Validator Contributor'){
                        var params={};
                        params.contributor_type='validator';
                        contractService.getContributersFilter(params).then(function (result) {
                            $scope.validatorContributor = result.data;
                        })
                            }

                    if($scope.feldName=='Relation Contributor'){
                        var params={};
                        params.contributor_type='provider';
                        contractService.getContributersFilter(params).then(function (result) {
                            $scope.relationContributor = result.data;
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
                    params.module='all_activities';
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
                                $scope.filterCreate.value = moment( $scope.filterCreate.value).utcOffset(0, false).toDate();
                            }
                        $scope.options = {
                            minDate: moment().utcOffset(0, false).toDate(),
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

                    if($scope.feldName=='Expert Contributor'){
                        var params={};
                        params.contributor_type='expert';
                        contractService.getContributersFilter(params).then(function (result) {
                            $scope.expertContributor = result.data;
                        })
                            }

                    if($scope.feldName=='Validator Contributor'){
                        var params={};
                        params.contributor_type='validator';
                        contractService.getContributersFilter(params).then(function (result) {
                            $scope.validatorContributor = result.data;
                        })
                            }

                    if($scope.feldName=='Relation Contributor'){
                        var params={};
                        params.contributor_type='provider';
                        contractService.getContributersFilter(params).then(function (result) {
                            $scope.relationContributor = result.data;
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
                                $scope.activitiesFilter();
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
                            $scope.activitiesFilter();
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
                    $scope.activitiesFilter();
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
    
        $scope.activitiesFilter=function(){
        var params ={};
        // params.user_id = $scope.user1.id_user;
        params.module = 'all_activities';
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
        $scope.activitiesFilter();

    $scope.getBUList = function(){
        param.user_role_id=$rootScope.user_role_id;
        param.id_user=$rootScope.id_user;
        param.customer_id = $scope.user1.customer_id;
        param.status = 1;
        businessUnitService.list(param).then(function(result){
            var obj = {'id_business_unit':'All', 'bu_name':'All'};
            result.data.data = result.data.data.reverse();
            result.data.data.push(obj);
            $scope.bussinessUnit = result.data.data.reverse();
        });
    }
    $scope.getBUList();
    $scope.getStatuses = function () {
        $scope.resetPagination=true;
        contractService.getContractStatus().then(function(result){
            if(result.status){
                var obj = {key:'all',value:'All'};
                result.data = result.data.reverse();
                result.data.push(obj);
                $scope.statusList = result.data.reverse();
            }
        })
    };
    // $scope.getCategoryList = function () {
    //     contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
    //         $scope.relationshipCategoryList = result.drop_down;
    //     });
    // }
    // $scope.getCategoryList();      
   
  
    $scope.getStatuses();
    $scope.contractsList = [];
  
    // $scope.getProviderList = function(id){        
    //     var params = {};
    //     if(id) {
    //         $stateParams.pname=undefined;
    //         params.business_unit_id = id;
    //         $scope.provider_name=null;
    //     }
    //     params.customer_id = $scope.user1.customer_id;
    //     params.id_user  = $scope.user1.id_user;
    //     params.user_role_id  = $scope.user1.user_role_id;
    //     params.status  = 1;
    //     providerService.list(params).then(function(result){
    //         var obj = {'provider_name':'All'};
    //         result.data.data = result.data.data.reverse();
    //         result.data.data.push(obj);
    //         $scope.providerList = result.data.data.reverse();
    //     });
    // };
    // $scope.getProviderList();
   
 
    if($localStorage.curUser.data.filters.allActivities){
        var filter = $localStorage.curUser.data.filters.allActivities;
        if(filter){
            if(filter.provider_name){
                $scope.provider_name = angular.copy(filter.provider_name);
                $stateParams.pname=undefined;
            }            
            $scope.business_unit_id = angular.copy(filter.business_unit_id);
            $scope.relationship_category_id = angular.copy(filter.relationship_category_id);
            $scope.hierarchy = angular.copy(filter.hierarchy);
            $scope.validation_key =angular.copy(filter.validation_key);
            if(filter.contract_status)
                $scope.contract_status = angular.copy(filter.contract_status);
            else $scope.contract_status = 'all';
            $scope.activity_filter = angular.copy(filter.activity_filter);           
        }
    }
    $scope.callServer = function (tableState){
        $scope.filtersData = {};
        $rootScope.module = '';
        $rootScope.displayName = '';    
        $rootScope.icon ='';
        $rootScope.class='';
        $rootScope.breadcrumbcolor='';   
        $scope.isLoading = true;
        $scope.isLoadingPagination = true;
        var pagination = tableState.pagination;
        tableState.customer_id = $scope.user1.customer_id;
        tableState.id_user  = $scope.user1.id_user;
        tableState.user_role_id  = $scope.user1.user_role_id;
        tableState.can_access  = $scope.can_access;
        tableState.is_advance_filter=1;        
        tableState.overview=true;        
        if($scope.resetPagination){
            tableState.pagination={};
            tableState.pagination.start='0';
            tableState.pagination.number='10';
        }
        if($stateParams.pname==undefined &&
            $stateParams.status==undefined &&
            $stateParams.status1==undefined &&
            $stateParams.activity_filter==undefined &&
            $stateParams.end_date==undefined){}
        else{
            if($stateParams.pname){
                $scope.contractsList = [];
                if(tableState.provider_name != undefined){}
                else {
                    $scope.provider_name = $stateParams.pname;
                    $scope.contract_status = 'all';
                    $scope.resetPagination=true;
                }                
                tableState.sort={};
            }
            if($stateParams.status){
                if($scope.contract_status != $stateParams.status){}
                else {
                    $scope.contract_status = $stateParams.status;
                }
            }
            if($stateParams.end_date){
                $scope.end_date_lessthan_90 = $stateParams.end_date;                
            }
            if($stateParams.activity_filter && $stateParams.status1) {
                $scope.resetPagination=true;
                var str =  $stateParams.status1;
                $scope.statusList = [
                    {"key":'all',"value":'All'},
                    {"key":"new","value":"New"},
                    {"key":"pending review","value":"Reviews to Initiate"},
                    {"key":"review in progress","value":"Reviews in Progress"},
                    {"key":"review finalized","value":"Reviews Finalized"},
                    {"key":"pending workflow","value":"Tasks to Initiate"},
                    {"key":"workflow in progress","value":"Tasks in Progress"},
                    {"key":"workflow finalized","value":"Tasks Finalized"}
                ];
                angular.forEach($scope.statusList,function(item){
                    if(str.indexOf("New") !== -1)str="New";
                    // if(str.includes("New"))str="New";
                    if(angular.equals(item.value,str)){
                        $scope.contract_status = item.key;
                        $scope.activity_filter = $stateParams.activity_filter;
                    }
                });
            }
        }
        //$scope.business_unit_id=[];
        tableState.business_unit_id=[];
        // if($scope.business_unit_id && $scope.business_unit_id != null){
        //     console.log('tablestate',$scope.business_unit_id);
        //     tableState.business_unit_id  = angular.copy($scope.business_unit_id);
        // }else{
        //     delete tableState.business_unit_id;
        //     $scope.business_unit_id = '';
        // }
        if($scope.business_unit_id && $scope.business_unit_id != null){
            //console.log('tablestate',$scope.business_unit_id);
            tableState.business_unit_id.push($scope.business_unit_id);
        }else{
            delete tableState.business_unit_id;
            $scope.business_unit_id = '';
        }
        if($scope.relationship_category_id && $scope.relationship_category_id != null){
            tableState.relationship_category_id  = angular.copy($scope.relationship_category_id);
        }else{
            delete tableState.relationship_category_id;
            $scope.relationship_category_id = '';
        }
        if($scope.activity_filter && $scope.activity_filter !=null){
            tableState.activity_filter = $scope.activity_filter;
        }
        else{
            delete tableState.activity_filter;
            $scope.activity_filter ='';
        }
        
        if($scope.provider_name && $scope.provider_name != null){
            tableState.provider_name  = angular.copy($scope.provider_name);
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
        if($scope.activity_filter && $scope.activity_filter != null){
            tableState.activity_filter = $scope.activity_filter;
        }else{
            delete tableState.activity_filter;
            $scope.activity_filter = '';
        }
        if($scope.validation_key && $scope.validation_key != null){
            tableState.validation_key = $scope.validation_key;
        }else{
            delete tableState.validation_key;
            $scope.validation_key = '';
        }
        if($scope.hierarchy && $scope.hierarchy != null){
            tableState.hierarchy = $scope.hierarchy;
        }else{
            delete tableState.hierarchy;
            $scope.hierarchy = '';
        }
      

        if($scope.contractType && $scope.contractType != null){
            if($scope.contractType =='my_contracts'){
                tableState.created_by  = $scope.user1.id_user;
            } else {
                delete tableState.created_by;
            }
            if($scope.contractType =='contributing_to'){
                tableState.customer_user  = $scope.user1.id_user;
            } else {
                delete tableState.customer_user;
            }
            delete tableState.user_role_id;
        }else{
            delete tableState.created_by;
            delete tableState.customer_user;
            tableState.user_role_id = $scope.user1.user_role_id;
        }            
        $scope.totalRecords = 0;
        $scope.tableStateRef = tableState;
        if(tableState.advancedsearch_get){}
        else {tableState.advancedsearch_get={};}
        // if(tableState.contribution_type!=null && (tableState.activity_filter|| tableState.business_unit_id ||tableState.relationship_category_id||tableState.provider_name||tableState.validation_key)){
        //     tableState.activity_filter=''; tableState.business_unit_id=''; tableState.relationship_category_id; tableState.provider_name=''; tableState.validation_key;
        // }
        contractService.list(tableState).then (function(result){
            $scope.contractsList =[];
            $scope.contractsList = result.data.data;           
            $scope.emptyTable=false;
            $scope.totalRecords = 0;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords = result.data.total_records;
            var pages = 0;
            pages = Math.ceil(result.data.total_records / $rootScope.userPagination);
            tableState.pagination.numberOfPages =  pages; 
            $scope.isLoading = false;
            $scope.resetPagination=false;
            $scope.getCategoryList();
            $scope.getBUList();
            $scope.getProviderList(tableState.business_unit_id);
            $scope.provider_name = tableState.provider_name;
            $scope.memorizeFilters(tableState);
            if(result.data.total_records < 1)
                $scope.emptyTable=true;
        });
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
    $scope.getAdvSearch = function(val) {
        if($scope.tableStateRef.search.predicateObject == undefined || 
                $scope.tableStateRef.search.predicateObject.search_key == undefined)
            $scope.resetPagination=false;
        else  $scope.resetPagination=true;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open adv-search-model',
            templateUrl: 'views/Manage-Users/contracts/advance-search-fields.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.getSearchRecords=function(data){ 
                    $scope.searchFields = data;
                    $scope.tableStateRef.advancedsearch_get = data;
                    $scope.checkBoxes = angular.copy(data);

                    if(!$scope.searchFields.provider_name_search) delete $scope.checkBoxes.provider_name_search
                    if(!$scope.searchFields.contract_name) delete $scope.checkBoxes.contract_name
                    if(!$scope.searchFields.relationship_category_name) delete $scope.checkBoxes.relationship_category_name
                    if(!$scope.searchFields.bu_name) delete $scope.checkBoxes.bu_name
                    if(!$scope.searchFields.contract_value) delete $scope.checkBoxes.contract_value
                    if(!$scope.searchFields.description) delete $scope.checkBoxes.description
                    if(!$scope.searchFields.tag_option_value) delete $scope.checkBoxes.tag_option_value
                    if(!$scope.searchFields.classification) delete $scope.checkBoxes.classification
                    if(!$scope.searchFields.owner) delete $scope.checkBoxes.owner
                    if(!$scope.searchFields.delegate) delete $scope.checkBoxes.delegate
                    if(!$scope.searchFields.automatic_prolongation) delete $scope.checkBoxes.automatic_prolongation

                    $scope.tableStateRef.advancedsearch_get = $scope.checkBoxes;

                    if(!angular.equals($scope.checkBoxes, {})){
                        angular.element('#btn-adv-search').addClass('adv-active-search');
                        angular.element('#search_key').focus();
                    }else{
                        angular.element('#btn-adv-search').removeClass('adv-active-search');
                    } 
                    $scope.cancel();
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
    $scope.filterActivityType = function(val) {
        $scope.resetPagination=true;
        $scope.activity_filter = val;     
        $scope.tableStateRef.activity_filter = val;
        $scope.tableStateRef.sort={};
        $stateParams.activity_filter=undefined;
       if($scope.tableStateRef.activity_filter){
            $scope.callServer($scope.tableStateRef);  
        }else{
            delete $scope.tableStateRef.activity_filter;
            $scope.callServer($scope.tableStateRef);  
       } 
    }

    $scope.filterValidationStatus = function(val) {
        $scope.resetPagination=true;
        $scope.validation_key = val;     
        $scope.tableStateRef.validation_key = val;
        $scope.tableStateRef.sort={};
        $stateParams.validation_key=undefined;
       if($scope.tableStateRef.validation_key){
            $scope.callServer($scope.tableStateRef);  
        }else{
            delete $scope.tableStateRef.validation_key;
            $scope.callServer($scope.tableStateRef);  
       } 
    }
    $scope.filterHierarchy = function(val) {
        $scope.resetPagination=true;
        $scope.hierarchy = val;     
        $scope.tableStateRef.hierarchy = val;
        $scope.tableStateRef.sort={};
        $stateParams.hierarchy=undefined;
       if($scope.tableStateRef.hierarchy){
            $scope.callServer($scope.tableStateRef);  
        }else{
            delete $scope.tableStateRef.hierarchy;
            $scope.callServer($scope.tableStateRef);  
       } 
    }

    $scope.filterContributionStatus = function(val) {
        $scope.resetPagination=true;
        $scope.contribution_type = val;     
        $scope.tableStateRef.contribution_type = val;
        $scope.tableStateRef.sort={};
        $stateParams.contribution_type=undefined;
       if($scope.tableStateRef.contribution_type){
            $scope.callServer($scope.tableStateRef);  
        }else{
            delete $scope.tableStateRef.contribution_type;
            $scope.callServer($scope.tableStateRef);  
       } 
    }

    $scope.openSubContracts = function (info) {
        $scope.sub_contracts = info.sub_contracts;
        $scope.particular_contract_name = info.contract_name;
        $scope.parentContractdetails=info;

        $scope.reviewworkflowData=function(data){
            $scope.reviewWorkflowInfo='';
            var param ={};
            param.contract_id = data.contract_id;
            contractService.reviewWorkflowInfo(param).then(function(result){
                $scope.reviewWorkflowInfo=result.data;
            });
        }

        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'sub-contract-details.html',
            controller: function ($uibModalInstance, $scope, item) {

                

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
    $scope.goToContractDetails = function(row){
       if(row.is_workflow=='1' && row.type=='contract')
            $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
      if(row.is_workflow =='0' && row.type=='contract')
            $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.id_contract),type:'review'});
      if(row.is_workflow =='1' && row.type=='project')
      $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
    }
    $scope.goToDashboard = function (row) {
        if(row.is_workflow=='0' && row.type=='contract')
            $state.go('app.contract.contract-dashboard',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
        if(row.is_workflow=='1' && row.type=='contract')
            $state.go('app.contract.workflow-dashboard',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
        if(row.is_workflow=='1'&& row.type=='project')
          $state.go('app.projects.project-dashboard1',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
    }
    $scope.exportContract = function (row) {
        var params={};
        params.contract_id = params.id_contract= row.id_contract;
        params.id_user=  $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        contractService.exportReviewData(params).then(function (result) {
            if(result.status){
                var obj = {};
                obj.action_name = 'export';
                obj.action_description = 'export$$contract$$('+row.contract_name+')';
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
        });
    }
    $scope.goToContractReview = function(row){
        
        if(row.is_workflow=='0' && row.type =='contract')
            $state.go('app.contract.contract-review',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
        if(row.is_workflow=='1' && row.type =='contract'){
            $state.go('app.contract.contract-workflow',{name:row.contract_name,
                                                        id:encode(row.id_contract),
                                                        rId:encode(row.id_contract_review),
                                                        wId:encode(row.id_contract_workflow),
                                                        type:'workflow'});
        }
        if(row.is_workflow=='1' && row.type =='project' && row.is_initiated ==1){
            $state.go('app.projects.project-task',{name:row.contract_name,id:encode(row.id_contract), rId:encode(row.id_contract_review),
                                                  wId:encode(row.id_contract_workflow), type:'workflow'});
        }
        if(row.is_workflow=='1' && row.type =='project' && row.is_initiated ==0){
            $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
        }
    }
        
    $scope.getByStatus = function(){
        setTimeout(function(){
            $stateParams.status1=undefined;
            $stateParams.status=undefined;
            $scope.resetPagination=true;
            $scope.tableStateRef.sort={};
            $scope.callServer($scope.tableStateRef);
        },0);//1500
    }
    $scope.exportContractsList = function(){
        var params = {};
        // params.customer_id = $scope.user1.customer_id;
        // params.user_role_id = $scope.user1.user_role_id;
        // params.id_user = $scope.user1.id_user;
        params.export_type = 'All_Activities';
        contractService.exportContracts(params).then(function (result) {
            if(result.status){
                var obj = {};
                obj.action_name = 'export';
                obj.action_description = 'export$$contracts list$$('+result.data.file_name+')';
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
        });
    }
    $scope.goToContratDiscussion = function(row){
        
        if(row.is_workflow=='0' && row.type=='contract')
            $state.go('app.contract.review-design',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
        if(row.is_workflow=='1' && row.type=='contract')
            $state.go('app.contract.workflow-design',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
        if(row.is_workflow=='1' && row.type=='project'){
            $state.go('app.projects.task-design',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
        }
    }
    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        showWeeks: false
    };
    $scope.providerChanged = function(){
        $scope.contract_status='all';
        if($localStorage.curUser.data.filters.allActivities){
            setTimeout(function(){
                $scope.getBUList();
                $scope.getCategoryList();
                $stateParams.pname=undefined;
                var filter = $localStorage.curUser.data.filters.allActivities;
                if(filter){
                    $scope.business_unit_id = angular.copy(filter.business_unit_id);
                    $scope.relationship_category_id = angular.copy(filter.relationship_category_id);
                }
            },0);//800
        }
    }
    $scope.categoryUpdated = function(){
        if($localStorage.curUser.data.filters.allActivities){
            setTimeout(function(){
                $scope.getBUList();
                var filter = $localStorage.curUser.data.filters.allActivities;
                if(filter){
                    $scope.business_unit_id = angular.copy(filter.business_unit_id);
                }
            },0);//600
        }
    }
    $scope.clear = function() {
        $scope.created_date = null;
        $scope.date_period = null;
        $scope.date_field = null;
        $scope.business_unit_id = '';
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
        // $state.transitionTo("app.contract.contract-overview",{reload: true, inherit: false});
    };
    $scope.getContractsByAccess=function(val){
      
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
    $scope.memorizeFilters = function(data){
        $localStorage.curUser.data.filters.allActivities = data;
        $localStorage.curUser.data.filters.allContracts = undefined;
    }
    $scope.filterByProvider = function(row){
        $localStorage.curUser.data.filters={};
        $state.transitionTo("app.contract.contract-overview",{pname:(row.provider_name)},{reload: true, inherit: false});
    }   


    $scope.goToReviewPage = function(row){
        
        if(row.type=='project' && row.is_initiated =='1'){
             $state.go('app.projects.project-task',{ name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
       }
       if(row.type=='contract' && row.is_workflow=='1' && row.is_initiated=='1'){
           $state.go('app.contract.contract-workflow',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
       }
       if(row.type=='contract' && row.is_workflow=='0' && row.is_initiated=='1'){
           $state.go('app.contract.contract-review',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'})
       }

       if(row.type=='project' && row.is_initiated =='0'){
           $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
       }
       if(row.type =='contract' && row.is_initiated =='0' && row.is_workflow=='0'){
         $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.id_contract),type:'review'}); 
       }
       if(row.type =='contract' && row.is_initiated =='0' && row.is_workflow=='1'){
        $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'}); 
       }
    }
})

.controller('allContractListCtrl', function($scope, $rootScope, $state,$filter, $stateParams, $localStorage, dateFilter, $timeout,$uibModal, 
            providerService, contractService, businessUnitService, encode,AuthService,catalogueService,userService,calenderService,$sce,moduleService,projectService,masterService,templateService){
    $scope.del=0;
    $scope.contract_status='';
    $scope.can_access=1;
    $scope.date_field='';
    $scope.date_period='';
    $scope.searchFields = {};
    $scope.automatic_prolongation=null;
    $scope.provider_name='';
    $scope.displayCount = $rootScope.userPagination;
    $localStorage.curUser.data.filters.allActivities = undefined;
    $scope.resetPagination=false;
    $scope.showBU = true;


    if($scope.user1.access == 'ca' || $scope.user1.access == 'bo'){
        $scope.del=1;
    }
    $scope.dynamicPopover2 = {templateUrl: 'myPopoverTemplate2.html'};
    //Advanced Contract Filter starts here

    $scope.advancedFilterContract = function(){
        $scope.filterCreate = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/contracts/advancedFilterContract.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';


                $scope.filterListContracts=function(){
                var params ={};
                // params.user_id=$scope.user.id_user;
                params.module='all_contracts_list';
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
                $scope.filterListContracts();


            $scope.flterDelete=function(rowdata){
                var r = confirm($filter('translate')('general.alert_Delete_filter'));
                if(r==true){
                    contractService.deleteContractFlter({'id_master_filter':rowdata.id_master_filter}).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success', result.message);
                            $scope.filterListContracts();
                            $scope.filterListContracters();
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
            templateUrl: 'views/Manage-Users/contracts/create-contract-filter.html',  
            controller: function ($uibModalInstance,$scope,item) {
            $scope.bottom ='general.save';
             $scope.title='controller.add_filter_criteria'

                contractService.getContractDomain({'domain_module': 'all_contracts_list'}).then(function(result){
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
                    $scope.tagType=domainFieldData[0].tag_type;
                    $scope.fieldSelect=domainFieldData[0].selected_field;
                    if($scope.feldName=='Currency'){
                    masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                        $scope.currencyList = result.data;
                    });
                }

                    if($scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                    contractService.getContractTagsDrpdown({'id_tag': domainFieldId}).then(function(result){
                        $scope.tagsDropdownList = result.data;
                    });
                }

                if($scope.fieldSelect=='relation' && $scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                    catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
                        $scope.selectedInfoProvider = result.data;
                        });                    
                }
                if($scope.fieldSelect=='project' && $scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                    catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'project'}).then (function(result){
                        $scope.selectedInfoProject = result.data;
                    });
                }
                if($scope.fieldSelect=='contract' && $scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                    catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                        $scope.selectedInfoContract = result.data;
                    });
                }
                if($scope.fieldSelect=='catalogue' && $scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                    catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'catalogue'}).then (function(result){
                        $scope.selectedInfoCatalogue = result.data;
                    });
                }


                    if($scope.feldName=='Template'){
                    templateService.list().then(function (result){
                        $scope.templateList=result.data.data;
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

                    if($scope.feldName=='Relation name'){
                    var params={};
                    params.customer_id = $scope.user1.customer_id;
                    // params.id_user  = $scope.user1.id_user;
                    // params.user_role_id  = $scope.user1.user_role_id;
                    params.status  = 1;
                    providerService.list(params).then(function(result){
                    $scope.providerList = result.data.data;
                    });
                    }

                    if($scope.feldName=='Category '){
                contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
                    $scope.relationshipCategoryList = result.drop_down;
                });
                     }

                    if($scope.feldName=='Owner'){
                contractService.responsibleUserList({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(), 'type': 'buowner','forDocumentIntelligence':'1','forAdvacedFilter':'1' }).then(function (result) {
                    $scope.buOwnerUsers = result.data;
                })
                     }

                     if($scope.feldName=='Recurrence'){
                     projectService.getRecurrences().then(function(result){
                        $scope.recurrences = result.data;
                        });
                    }

                    if($scope.feldName=='Notification Resend Recurrence'){
                    projectService.resendRecurrence().then(function(result){
                         $scope.resend_recurrences = result.data;
                        });
                    }
                    if($scope.feldName=='Payment Periodicity'){
                    contractService.servicePeriodicity().then(function(result){
                        $scope.periodicity = result.data;
                        });
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
                    // params.user_id=$scope.user.id_user;
                    params.module='all_contracts_list';
                    params.id_master_filter=row.id_master_filter;
                    
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


                        if($scope.fieldSelect=='relation' && $scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
                                $scope.selectedInfoProvider = result.data;
                                });                    
                        }
                        if($scope.fieldSelect=='project' && $scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'project'}).then (function(result){
                                $scope.selectedInfoProject = result.data;
                            });
                        }
                        if($scope.fieldSelect=='contract' && $scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                                $scope.selectedInfoContract = result.data;
                            });
                        }
                        if($scope.fieldSelect=='catalogue' && $scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'catalogue'}).then (function(result){
                                $scope.selectedInfoCatalogue = result.data;
                            });
                        }

                        

                    if($scope.feldName=='Currency'){
                        masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                            $scope.currencyList = result.data;
                        });
                    }

                    if($scope.fieldType=='drop_down' && $scope.domainType=='Contract Tags'){
                        contractService.getContractTagsDrpdown({'id_tag': row.master_domain_field_id}).then(function(result){
                            $scope.tagsDropdownList = result.data;
                        });
                    }

                    if($scope.feldName=='Template'){
                        templateService.list().then(function (result){
                            $scope.templateList=result.data.data;
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

                    if($scope.feldName=='Relation name'){
                        var params={};
                        params.customer_id = $scope.user1.customer_id;
                        // params.id_user  = $scope.user1.id_user;
                        // params.user_role_id  = $scope.user1.user_role_id;
                        params.status  = 1;
                        providerService.list(params).then(function(result){
                        $scope.providerList = result.data.data;
                        });
                    }

                    if($scope.feldName=='Category '){
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

                    if($scope.feldName=='Recurrence'){
                        projectService.getRecurrences().then(function(result){
                           $scope.recurrences = result.data;
                           });
                       }
                       
                    if($scope.feldName=='Notification Resend Recurrence'){
                        projectService.resendRecurrence().then(function(result){
                        $scope.resend_recurrences = result.data;
                        });
                    }

                    if($scope.feldName=='Payment Periodicity'){
                        contractService.servicePeriodicity().then(function(result){
                            $scope.periodicity = result.data;
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
                                $scope.filterListContracters();
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
                            $scope.filterListContracters();
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
                    $scope.filterListContracters();
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

        $scope.filterListContracters=function(){
        var params ={};
        // params.user_id = $scope.user1.id_user;
        params.module = 'all_contracts_list';
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
        $scope.filterListContracters();
//Advanced Contract  Fillter ends here
   
    
    // $scope.getCategoryList = function(){
    //     contractService.getRelationshipCategory({'customer_id': $scope.user1.customer_id}).then(function(result){
    //         $scope.relationshipCategoryList = result.drop_down;
    //     });
    // }

 
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
                   
                    calenderService.smartFilter(params).then(function (result) {
                        if (result.status) {
                            
                            $scope.relationCategory = result.data.relationship_list;
                            $scope.business_units = result.data.business_unit;
                            $scope.providers = result.data.provider;
                            $scope.contracts = result.data.contract;
                            $scope.completed_contracts = result.data.completed_contracts;
                            $scope.provider_relationship_category = result.data.provider_relationship_category;
                            $scope.customOptions.bussiness_unit_id.push(item.business_unit_id);
                            $scope.customOptions.contract_id.push(item.id_contract);
                        }
                       
                    });
                }
                $scope.getFilters();
               
               
                var params1=[];
                $scope.getSmartFilters = function (key) {
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

                    if ($scope.customOptions.relationship_category_id)
                        params1["relationship_category_id"] = $scope.customOptions.relationship_category_id.toString();

                
                    if ($scope.customOptions.provider_id)
                        params1["provider_ids"] = $scope.customOptions.provider_id.toString();


                    if ($scope.customOptions.bussiness_unit_id)
                        params1["business_ids"] = $scope.customOptions.bussiness_unit_id.toString();
                    
                    if ($scope.customOptions.provider_relationship_category_id)
                        params1["provider_relationship_category_id"] = $scope.customOptions.provider_relationship_category_id.toString();

                    if (params1['business_ids'] == '') delete params1['business_ids'];
                    if (params1['relationship_category_id'] == '') delete params1['relationship_category_id'];
                    if (params1['provider_ids'] == '') delete params1['provider_ids'];
                    if(params1['provider_relationship_category_id']=='') delete params1['provider_relationship_category_id'];
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

                    if ($scope.customOptions.relationship_category_id)
                        data.relationship_category_id = $scope.customOptions.relationship_category_id.toString();
                    if ($scope.customOptions.bussiness_unit_id) {
                        data.business_unit_id = $scope.customOptions.bussiness_unit_id.toString();
                        delete data.bussiness_unit_id;
                    }

                    if ($scope.customOptions.provider_relationship_category_id)
                            data.provider_relationship_category_id = $scope.customOptions.provider_relationship_category_id.toString();
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
    // $scope.getCategoryList();

    if($localStorage.curUser.data.filters.allContracts){
        var filter = $localStorage.curUser.data.filters.allContracts;
        if(filter){
            if(filter.provider_name){
                $scope.provider_name = filter.provider_name;
                $stateParams.pname=undefined;
            }            
            $scope.business_unit_id = angular.copy(filter.business_unit_id);
            $scope.hierarchy = angular.copy(filter.hierarchy);
            $scope.contract_active_status = angular.copy(filter.contract_active_status);
            $scope.relationship_category_id = angular.copy(filter.relationship_category_id);
            $scope.created_date = (filter.created_date)?moment(filter.created_date).utcOffset(0, false).toDate():null;
            
            if($scope.created_date)$scope.date_period = filter.date_period;
            if($scope.created_date)$scope.date_field = filter.date_field;
            $scope.resetPagination=true;    
            if(filter.created_date != undefined && filter.created_date != 'Invalid Date'){
                var element = angular.element('#created_date');
                element.removeClass("req-filter");
                element.addClass('active-filter');
                if($scope.date_field){}
                else {
                    $scope.date_field='created_on';
                }
                if($scope.date_period){}
                else {
                    $scope.date_period='=';
                }
            }else{
                $scope.date_field='';
                $scope.date_period='';
            }
        }
    }
    $scope.displayChart = function () {
        $scope.contractOverallDetails();
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
    
    $scope.contractsList = [];
    $scope.myDataSource = {};
    

    $scope.itemsByPage = 10;
    $scope.callServer = function (tableState){
        $scope.filtersData = {};
        $rootScope.module = '';
        $rootScope.displayName = '';
        $rootScope.icon ='Contracts';
        $rootScope.class="contract-logo";
        $rootScope.breadcrumbcolor='contract-breadcrumb-color' ;       
        $scope.isLoading = true;
        var pagination = tableState.pagination;
        tableState.customer_id = $scope.user1.customer_id;
        tableState.id_user  = $scope.user1.id_user;
        tableState.user_role_id  = $scope.user1.user_role_id;
        tableState.can_access  = $scope.can_access;
        tableState.is_advance_filter=1;
        
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
            if($scope.hierarchy && $scope.hierarchy !=null){
                tableState.hierarchy = $scope.hierarchy;
            }else{
                delete tableState.hierarchy;
                $scope.hierarchy = null;
            }
            if($scope.contract_active_status && $scope.contract_active_status !=null){
                tableState.contract_active_status = $scope.contract_active_status;
            }else{
                delete tableState.contract_active_status;
                $scope.contract_active_status = null;
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

                
                $scope.contractOverallDetails = function(){
                    var data=tableState;
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
                    params.chart_type='allcontracts';
                    params.advancedsearch_get=data.advancedsearch_get;
                    if(!angular.isUndefined(data.business_unit_id)) params.business_unit_id = data.business_unit_id;
                    if(!angular.isUndefined(data.hierarchy)) params.hierarchy = data.hierarchy;
                    if(!angular.isUndefined(data.contract_active_status)) params.contract_active_status = data.contract_active_status;
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
                // $scope.contractOverallDetails(tableState,'allcontracts');
                $scope.contractsList = result.data.data;
                $scope.emptyTable=false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                // $scope.getCategoryList();
                // $scope.getBUList();
                // $scope.getProviderList(tableState.business_unit_id);
                $scope.provider_name = tableState.provider_name;
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
    $scope.reviewworkflowData=function(data){
        $scope.reviewWorkflowInfo='';
        var param ={};
        param.contract_id = data.contract_id;
        contractService.reviewWorkflowInfo(param).then(function(result){
            $scope.reviewWorkflowInfo=result.data;
        });
    }


    $scope.getAdvSearch = function(val) {
        if($scope.tableStateRef.search.predicateObject == undefined || 
                $scope.tableStateRef.search.predicateObject.search_key == undefined)
            $scope.resetPagination=false;
        else  $scope.resetPagination=true;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open adv-search-model',
            templateUrl: 'views/Manage-Users/contracts/advance-search-fields.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.getSearchRecords=function(data){ 
                    $scope.searchFields = data;
                    $scope.tableStateRef.advancedsearch_get = data;
                    $scope.checkBoxes = angular.copy(data);

                    if(!$scope.searchFields.provider_name_search) delete $scope.checkBoxes.provider_name_search
                    if(!$scope.searchFields.contract_name) delete $scope.checkBoxes.contract_name
                    if(!$scope.searchFields.relationship_category_name) delete $scope.checkBoxes.relationship_category_name
                    if(!$scope.searchFields.bu_name) delete $scope.checkBoxes.bu_name
                    if(!$scope.searchFields.contract_value) delete $scope.checkBoxes.contract_value
                    if(!$scope.searchFields.description) delete $scope.checkBoxes.description
                    if(!$scope.searchFields.tag_option_value) delete $scope.checkBoxes.tag_option_value
                    if(!$scope.searchFields.classification) delete $scope.checkBoxes.classification
                    if(!$scope.searchFields.owner) delete $scope.checkBoxes.owner
                    if(!$scope.searchFields.delegate) delete $scope.checkBoxes.delegate
                    if(!$scope.searchFields.automatic_prolongation) delete $scope.checkBoxes.automatic_prolongation

                    $scope.tableStateRef.advancedsearch_get = $scope.checkBoxes;

                    if(!angular.equals($scope.checkBoxes, {})){
                        angular.element('#btn-adv-search').addClass('adv-active-search');
                        angular.element('#search_key').focus();
                    }else{
                        angular.element('#btn-adv-search').removeClass('adv-active-search');
                    } 
                    $scope.cancel();
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
            //("filterDateType---------");
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
    $scope.goToContractDetails = function(row){        
       if(row.is_workflow=='1')
            $state.go('app.contract.view',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
        else
            $state.go('app.contract.view',{name:row.contract_name,id:encode(row.id_contract),type:'review'});
    }
    $scope.goToDashboard = function (row) {
        if(row.is_workflow=='0')
            $state.go('app.contract.contract-dashboard',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
        else
            $state.go('app.contract.workflow-dashboard',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
    }
    $scope.exportContract = function (row) {
        var params={};
        params.contract_id = params.id_contract= row.id_contract;
        params.id_user=  $scope.user1.id_user;
        params.user_role_id  = $scope.user1.user_role_id;
        contractService.exportReviewData(params).then(function (result) {
            if(result.status){
                var obj = {};
                obj.action_name = 'export';
                obj.action_description = 'export$$contract$$('+row.contract_name+')';
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
        });
    }
    $scope.goToContractReview = function(row){
        if(row.is_workflow=='0'){
            $state.go('app.contract.contract-review1',{name:row.contract_name,
                                    id:encode(row.id_contract),
                                    rId:encode(row.id_contract_review),
                                    type:'review'});
        }else {
            $state.go('app.contract.contract-workflow1',{name:row.contract_name,
                                    id:encode(row.id_contract),
                                    rId:encode(row.id_contract_review),
                                    wId:encode(row.id_contract_workflow),
                                    type:'workflow'});
        }
    }
    $scope.deleteContract = function (row) {
        var r=confirm($filter('translate')('general.alert_continue'));
        if(r==true){
            var params = {};
            params.contract_id = row.id_contract;
            params.user_role_id = $scope.user1.user_role_id;
            params.id_user = $scope.user1.id_user;
            params.is_workflow  = row.is_workflow;
            if(row.is_workflow=='1'){
                params.id_contract_workflow  = row.id_contract_workflow ;
            }
            contractService.delete(params).then(function (result) {
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
                        $scope.callServer($scope.tableStateRef);
                    },300);
                }
            });
        }
        
    }
    

    $scope.exportContractsList = function(){
        var params = {};
        
        params.export_type = 'All_contracts';
        contractService.exportContracts(params).then(function (result) {
            if(result.status){
                var obj = {};
                obj.action_name = 'export';
                obj.action_description = 'export$$contracts list$$('+result.data.file_name+')';
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
        });
    }
    $scope.goToContratDiscussion = function(row){
        if(row.is_workflow=='0')
            $state.go('app.contract.review-design',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
        else
            $state.go('app.contract.workflow-design',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
    }
    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        showWeeks: false
    };
    $scope.clear = function() {
        $scope.created_date = '';
        $scope.date_period = '';
        $scope.date_field = '';
        $scope.business_unit_id = 'All';
        $scope.provider_name = null;
        $scope.relationship_category_id = null;
        $scope.contract_status = null;
        $scope.end_date_lessthan_90 = null;
        $scope.created_this_month=null;
        $scope.ending_this_month=null;
        $scope.automatic_prolongation='';
        $scope.hierarchy='';
        angular.element('#created_date').removeClass('req-filter');
        $localStorage.curUser.data.filters = {};
        $state.transitionTo("app.contract.all-contracts",{reload: true, inherit: false});
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
        
        $scope.callServer($scope.tableStateRef);
    }
    $scope.getContractsByAccess=function(val){
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
    $scope.goToReviewWorkflow = function(type,row){
        if(type.is_workflow==1){
            if(type.initiated){
                $state.go('app.contract.contract-workflow1',{name:row.contract_name,
                    id:encode(row.id_contract),
                    rId:encode(type.id_contract_review),
                    wId:encode(type.id_contract_workflow),
                    type:'workflow'});
            }else{
                $state.go('app.contract.view',
                        {name:row.contract_name,id:encode(row.id_contract),wId:encode(type.id_contract_workflow),type:'workflow'}, { reload: true, inherit: false });
            }
        }else{
            if(type.id_contract_review && type.initiated)
                $state.go('app.contract.contract-review1',{name:row.contract_name,id:encode(row.id_contract),rId:encode(type.id_contract_review),type:'review'});
            else
                $state.go('app.contract.view',{name:row.contract_name,id:encode(row.id_contract),type:'review'});
        }
    }

    $scope.memorizeFilters = function(data){
        $localStorage.curUser.data.filters.allContracts = data;
        $localStorage.curUser.data.filters.allActivities = undefined;
    }
    $scope.filterByProvider = function(row){
        $localStorage.curUser.data.filters={};
        $state.transitionTo("app.contract.all-contracts",{pname:(row.provider_name)},{reload: true, inherit: true});
    } 
    
    
    $scope.openSubContracts = function (info) {
        
        $scope.sub_contracts = info.sub_contracts;
        $scope.particular_contract_name = info.contract_name;
        $scope.parentDetails=info;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size:'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'sub-contract-details.html',
            controller: function ($uibModalInstance, $scope, item) {
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


.config(function(scrollableTabsetConfigProvider){
    scrollableTabsetConfigProvider.setShowTooltips (true);
    scrollableTabsetConfigProvider.setTooltipLeftPlacement('bottom');
    scrollableTabsetConfigProvider.setTooltipRightPlacement('left');
})
