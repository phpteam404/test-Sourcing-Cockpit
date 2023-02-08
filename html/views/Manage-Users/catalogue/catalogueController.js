angular.module('app',['localytics.directives'])
.controller('catalogueOverviewCtrl', function ($scope,$rootScope) {
   

})
.controller('catalogueListCtrl',function($scope,$sce,$rootScope,$state,$uibModal,$filter,dateFilter,Upload,encode,userService,
     AuthService,catalogueService,attachmentService,tagService,userService,masterService,contractService){
        $rootScope.icon ='Catalogue';
        $rootScope.module = '';
        $rootScope.displayName = '';
        $rootScope.class="contract-logo bordered-logo";
        $rootScope.breadcrumbcolor='contract-breadcrumb-color' ; 
        $scope.file={};
        $scope.links_delete = [];
        $scope.resetPagination=false;
        $scope.dynamicPopover = { templateUrl: 'myPopoverCatologue.html' };


        $scope.getCatalogueList = function (tableState){
            setTimeout(function(){
                $scope.tableStateRef = tableState;
                $scope.catalogueLoading = true;
                var pagination = tableState.pagination;
                tableState.is_advance_filter=1;
                    catalogueService.catalogueList(tableState).then(function (result) {
                    $scope.catalogueListData = result.data;
                    $scope.catalogueInfoCount = result.total_records;
                    $scope.emptyCatalogueTable=false;
                    $scope.displayCount = $rootScope.userPagination;
                    tableState.pagination.numberOfPages =  Math.ceil($scope.catalogueInfoCount / $rootScope.userPagination);
                    $scope.catalogueLoading = false;
                    if(result.total_records < 1)
                        $scope.emptyCatalogueTable=true;
                })
            },700);
        }

        $scope.defaultPagesCatalogue = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.getCatalogueList($scope.tableStateRef);
                }                
            });
        }

        $scope.deleteCatalogue = function(row){
            var r=confirm($filter('translate')('general.alert_continue'));
            if(r==true){
                var params ={};
                params.id_catalogue  = row.id_catalogue ;
                catalogueService.deleteCatalogue(params).then(function(result){
                    if(result.status){
                        $scope.getCatalogueList($scope.tableStateRef);
                        $rootScope.toast('Success', result.message);
                        var obj = {};
                        obj.action_name = 'delete';
                        obj.action_description = 'delete$$Action$$Item$$('+row.action_item+')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = $location.$$absUrl;
                    }else $rootScope.toast('Error', result.error);
                });
            }
        }

        $scope.exportCatalogueList = function(){

            catalogueService.getCatalogueExport().then(function (result) {
                if(result.status){
                    var obj = {};
                    obj.action_name = 'export';
                    obj.action_description = 'export$$catalogue list$$('+result.data.file_name+')';
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

        var parentPage = $state.current.url.split("/")[1];
        $scope.goToDetailsPage = function(row){
            // console.log("a",row);
            var goView = (parentPage == 'all-activities')?'app.contract.view1':'app.contract.view';
                $state.go(goView,{name:row.name,id:encode(row.id),type:'review'});
        }


        $scope.advancedFilterCatalogues = function(){
            $scope.filterCreate = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/contracts/advancedFilterContract.html',
                controller: function ($uibModalInstance,$scope,item) {
                    $scope.bottom ='general.save';

                    $scope.filterListCatalogues=function(){
                    var params ={};
                    // params.user_id=$scope.user.id_user;
                    params.module='all_catalogue_list';
                    $scope.filterLoading=true;
                    contractService.getContractList(params).then(function(result){
                        $scope.filterList=result.data;
                        $scope.filterLoading=false;
                        angular.forEach($scope.filterList,function(obj){
                            obj.value_names_string = $sce.trustAsHtml(obj.value_names_string);
                        });
                        $scope.filterContracts=false;
                        if($scope.filterList.length<1){
                            $scope.filterContracts=true;
                        }
                        });
                    }
                    $scope.filterListCatalogues();


                $scope.flterDelete=function(rowdata){
                    var r = confirm($filter('translate')('general.alert_Delete_filter'));
                    if(r==true){
                        contractService.deleteContractFlter({'id_master_filter':rowdata.id_master_filter}).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success', result.message);
                                $scope.filterListCatalogues();
                                $scope.catalogueFilter();
                                $scope.tableStateRef.pagination.start =0;
                                $scope.tableStateRef.pagination.totalItemCount =10;
                                $scope.tableStateRef.pagination.number =10;
                                $scope.getCatalogueList($scope.tableStateRef)                    }
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

        $scope.catalogueFilter=function(){
            var params ={};
            params.module = 'all_catalogue_list';
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
        $scope.catalogueFilter();


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
                templateUrl: 'views/Manage-Users/catalogue/create-catalogue-filter.html',
                controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';
                $scope.title='controller.add_filter_criteria'

                    contractService.getContractDomain({'domain_module': 'all_catalogue_list'}).then(function(result){
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
                        $scope.fieldSelect=domainFieldData[0].selected_field;

                        if($scope.feldName=='Currency'){
                            masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                                $scope.currencyList = result.data;
                            });
                        }

                        if($scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                            contractService.getContractTagsDrpdown({'id_tag': domainFieldId}).then(function(result){
                                $scope.tagsDropdownList = result.data;
                            });
                        }

                        if($scope.fieldSelect=='relation' && $scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
                                $scope.selectedInfoProvider = result.data;
                                });                    
                        }
                        if($scope.fieldSelect=='project' && $scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'project'}).then (function(result){
                                $scope.selectedInfoProject = result.data;
                            });
                        }
                        if($scope.fieldSelect=='contract' && $scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                                $scope.selectedInfoContract = result.data;
                            });
                        }
                        if($scope.fieldSelect=='catalogue' && $scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                            catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'catalogue'}).then (function(result){
                                $scope.selectedInfoCatalogue = result.data;
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
                        params.module='all_catalogue_list';
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
                    
                            if($scope.filterCreate.field_type=='numeric_text' || $scope.filterCreate.field_type=='free_text' || $scope.filterCreate.field_type=='drop_down' ||  $scope.filterCreate.field_type=='date'){  
                                contractService.getContractField({'id_master_domain': row.master_domain_id}).then(function(result){
                                        $scope.contractField = result.data;
                                    });
                            }
                            if($scope.feldName=='Currency'){
                                masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                                    $scope.currencyList = result.data;
                                });
                            }

                            if($scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                                console.log("a",row.master_domain_field_id);
                                contractService.getContractTagsDrpdown({'id_tag': row.master_domain_field_id}).then(function(result){
                                    $scope.tagsDropdownList = result.data;
                                    console.log("catalogue",$scope.tagsDropdownList)
                                });
                            }

                            if($scope.fieldSelect=='relation' && $scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'provider'}).then (function(result){
                                    $scope.selectedInfoProvider = result.data;
                                    });                    
                            }
                            if($scope.fieldSelect=='project' && $scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'project'}).then (function(result){
                                    $scope.selectedInfoProject = result.data;
                                });
                            }
                            if($scope.fieldSelect=='contract' && $scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                                    $scope.selectedInfoContract = result.data;
                                });
                            }
                            if($scope.fieldSelect=='catalogue' && $scope.fieldType=='drop_down' && $scope.domainType=='Catalogue Tags'){
                                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'catalogue'}).then (function(result){
                                    $scope.selectedInfoCatalogue = result.data;
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
                                    $scope.catalogueFilter();
                                    $scope.tableStateRef.pagination.start =0;
                                    $scope.tableStateRef.pagination.totalItemCount =10;
                                    $scope.tableStateRef.pagination.number =10;
                                    $scope.getCatalogueList($scope.tableStateRef)                    
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
                                $scope.catalogueFilter();
                                $scope.tableStateRef.pagination.start =0;
                                $scope.tableStateRef.pagination.totalItemCount =10;
                                $scope.tableStateRef.pagination.number =10;
                                $scope.getCatalogueList($scope.tableStateRef)                    
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
                        $scope.catalogueFilter();
                        $scope.tableStateRef.pagination.start =0;
                        $scope.tableStateRef.pagination.totalItemCount =10;
                        $scope.tableStateRef.pagination.number =10;
                        $scope.getCatalogueList($scope.tableStateRef)                    
                        $rootScope.toast('Success', result.message);
                    }
                    else{
                        $rootScope.toast('Error',result.error,'error');
                    }
                })
            }
        }


        $scope.catalogueLinks=[];
        $scope.catalogueLink={};
        $scope.verifyLink = function(data){
            if(data !={}){
                $scope.catalogueLinks.push(data);
                $scope.catalogueLink={};
            }
        }

        $scope.removeLink = function(index){
            var r=confirm($filter('translate')('general.alert_continue'));
            if(r==true){
                $scope.catalogueLinks.splice(index, 1);
            }                    
        }

        var parentPage = $state.current.url.split("/")[1];
        $scope.detailsPageGo = function(row,tag){
            console.log('row',row);
            console.log('tag',tag);
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
            $scope.catalogueInfo = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                size: 'lg',
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/catalogue/view-catalogue-info.html',
                controller: function ($uibModalInstance, $scope, item) {
                $scope.catalogueData=data;
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
                        obj.id_catalogue=$scope.catalogueData.id_catalogue;
                        catalogueService.catalogueList(obj).then(function (result) {
                            $scope.catalogue = result.data[0];
                            $scope.catalogue_attach_count = result.data[0].catalogue_attachments_count;
                            $scope.catalogue_info_count = result.data[0].catalogue_information;
                            $scope.catalogue_tags_count = result.data[0].catalogue_tags;
                        });
                    }
                    $scope.catalogueInfo();
                    
                    catalogueService.getCatalogueTags({'id_catalogue':$scope.catalogueData.id_catalogue}).then (function(result){   
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
            $scope.catalogueInfo.result.then(function ($data) {
            }, function () {
            });
        }
       


        $scope.editCatalogue = function (data) {
            $scope.selectedRow =data;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                size: 'lg',
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/catalogue/edit-catalogue.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.fdata = {};
                    $scope.isView = false;
                    $scope.isLink = false;
                    $scope.contractParters={};
                    $scope.contractLinks=[];
                    $scope.contractLink={};
                    $scope.catalogueInfo=data;
                    //console.log("as",$scope.catalogueInfo);

                    masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
                        $scope.currencyList = result.data;
                    });

                    $scope.getTags=function(){
                        catalogueService.getCatalogueTags({'id_catalogue':$scope.catalogueInfo.id_catalogue}).then (function(result){   
                            if(result.status){
                                $scope.tagsInfo=[];
                                $scope.tagsInfo = result.data;
                                $scope.ragExist=result.is_rag_exists;
                                angular.forEach($scope.tagsInfo,function(i,o){
                                    angular.forEach(i.tag_details,function(j,o){
                                    if(j.tag_type=='date'){
                                        j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                    
                                        }
                                    })
                                });
            
                                }else {$rootScope.toast('Error',result.error,'error');}
                            });
            
                    }
                    $scope.getTags();

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

                

                    $scope.verifyLink = function(data){
                        $scope.isLink = false;
                        if(data !={}){
                            $scope.catalogueLinks.push(data);
                            $scope.catalogueLink={};
                        }
                    }

                    $scope.removeLink = function(index){
                        var r=confirm($filter('translate')('general.alert_continue'));
                        if(r==true){
                            $scope.catalogueLinks.splice(index, 1);
                        }                    
                    }

                    $scope.changeLockingStatus = function(info){
                        var params={};
                        params.id_document = info.id_document;
                        contractService.lockingStatus(params).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success', result.message);
                                $scope.catalogueData();
                            }
                        });
                    }

                    $scope.catalogueData=function(){
                        var obj={};
                        obj.id_catalogue=$scope.catalogueInfo.id_catalogue;
                        catalogueService.catalogueList(obj).then(function (result) {
                            $scope.catalogue = result.data[0];
                            $scope.catalogue_attach_count = result.data[0].catalogue_attachments_count;
                            $scope.catalogue_info_count = result.data[0].catalogue_information;
                            $scope.catalogue_tags_count = result.data[0].catalogue_tags;
                    });
                    }
                    $scope.catalogueData();

                    $scope.updateCatalogue=function(data){
                        var params ={};
                        params.catalogue=data;
                        params.customer_id=$scope.user1.customer_id;
                        params.id_catalogue = $scope.catalogueInfo.id_catalogue;
                        catalogueService.updateCatalogue(params).then(function(result){
                            if (result.status) {
                                // $scope.catalogueData();
                                $scope.getCatalogueList($scope.tableStateRef);
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Contract$$Tags$$('+$stateParams.name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });

                    }
                    $scope.updateTags = function(data){
                        angular.forEach(data,function(i,o){
                            angular.forEach(i.tag_details,function(j,o){
                            if(j.tag_type=='date'){
                                j.tag_answer = dateFilter(j.tag_answer,'yyyy-MM-dd');
                            }
                        });
                    });

                        var params ={};
                        params.id_catalogue = $scope.catalogueInfo.id_catalogue;
                        params.catalogue_tags = data;
                        catalogueService.updateCatalogueTags(params).then(function(result){
                            if (result.status) {
                                $scope.getTags();
                                $scope.catalogueData();
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Contract$$Tags$$('+$stateParams.name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                            }
                        });
                    }


                    $scope.uploadAttachment = function(attachments,data){
                        $scope.isView = true;
                        if(attachments){
                            
                            Upload.upload({
                                url: API_URL+'Document/add',
                                data:{
                                    file:attachments.file.attachments,
                                    customer_id: $scope.user1.customer_id,
                                    module_id: $scope.catalogueInfo.id_catalogue,
                                    module_type: 'catalogue',
                                    reference_id: $scope.catalogueInfo.id_catalogue,
                                    reference_type: 'catalogue',
                                    document_type : 0,
                                    uploaded_by: $scope.user1.id_user
                                }
                            }).then(function (resp) {
                                if(resp.data.status){
                                    $scope.fdata.file=[];
                                    $scope.isView = false;
                                    $scope.catalogueData();
                                    $rootScope.toast('Success',resp.data.message);
                                    var obj = {};
                                    obj.action_name = 'upload';
                                    obj.action_description = 'upload$$attachments$$for$$contract$$('+$stateParams.name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = window.location.href;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                }
                                else 
                                { 
                                    $rootScope.toast('Error',resp.data.error,'error',$scope.user);
                                    $scope.isView=false;
                                }
                            
                            },function (resp) {
                                $rootScope.toast('Error',resp.data.error,'error');
                            }, function (evt) {
                                $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                            });

                            
                        }
                        else {

                            $rootScope.toast('Error','invalid format','image-error');
                        }
                    }
                    $scope.uploadLinks = function (catalogueLinks,data) {
                        $scope.isLink = true;
                        if(catalogueLinks.length>0){
                            Upload.upload({
                                url: API_URL+'Document/add',
                                data:{
                                    file:catalogueLinks,
                                    customer_id: $scope.user1.customer_id,
                                    module_id: $scope.catalogueInfo.id_catalogue,
                                    module_type: 'catalogue',
                                    reference_id: $scope.catalogueInfo.id_catalogue,
                                    reference_type: 'catalogue',
                                    document_type : 1,
                                    uploaded_by: $scope.user1.id_user
                                }
                            }).then(function (resp) {
                                if(resp.data.status){
                                    $rootScope.toast('Success',resp.data.message);
                                    $scope.catalogueLinks=[];
                                    $scope.isLink =false;
                                    $scope.catalogueData();
                                    var obj = {};
                                    obj.action_name = 'upload';
                                    obj.action_description = 'upload$$link$$for$$contract$$('+$stateParams.name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = window.location.href;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                }
                                else $rootScope.toast('Error',resp.data.error);
                            });
                        }
                        else {
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
                                    $scope.catalogueData();
                                    var obj = {};
                                    obj.action_name = 'delete';
                                    obj.action_description = 'delete$$Attachment$$('+name+')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                }else{$rootScope.toast('Error',result.error,'error');}
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

        $scope.createCatalogue = function () {
            $state.go('app.catalogue.create-catalogue');
        }
})


.controller('createCatalogueCtrl', function ($state,$scope,$rootScope,$filter,dateFilter,Upload,tagService,masterService,catalogueService) {
   
    $rootScope.module = '';
    $rootScope.displayName = '';
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon=''; 
    $scope.file={};
    $scope.links_delete = [];

    masterService.currencyList({'customer_id': $scope.user1.customer_id}).then(function(result){
        $scope.currencyList = result.data;
    });

   

    tagService.groupedTags({'status':1,'tag_type':'catalogue_tags'}).then(function(result){
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



    $scope.catalogueLinks=[];
    $scope.catalogueLink={};
    $scope.verifyLink = function(data){
        if(data !={}){
            $scope.catalogueLinks.push(data);
            $scope.catalogueLink={};
        }
    }

    $scope.removeLink = function(index){
        var r=confirm($filter('translate')('general.alert_continue'));
        if(r==true){
            $scope.catalogueLinks.splice(index, 1);
        }                    
    }

    var param={}
    param.customer_id=$scope.user1.customer_id;

    catalogueService.generateCatalogueId(param).then(function(result){
        if(result.status){
            $scope.catalogue = result.data;
        }
    });

    $scope.addCatalogue = function (data1){
        $scope.formDataObj= angular.copy(data1);
        var catalogue={};
        catalogue= $scope.formDataObj;
        $scope.grouped_tags = {};
        if(catalogue.grouped_tags){
            angular.forEach($scope.tagsBuilding, function(k,ko){
            $scope.options = {};
            angular.forEach(k.tag_details, function(i,o){
                $scope.options[o] = {};
                $scope.options[o].tag_id = i.tag_id;
                $scope.options[o].tag_type = i.tag_type;    
                $scope.options[o].multi_select = i.multi_select;  
                $scope.options[o].selected_field = i.selected_field;
                $scope.options[o].selected_field = i.selected_field;              
                if($scope.catalogue.grouped_tags.feedback !=undefined)
                $scope.options[o].comments = $scope.catalogue.grouped_tags.feedback[i.tag_id];
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
         catalogue.grouped_tags =  $scope.grouped_tags;
        catalogue.created_by = $scope.user.id_user;
        catalogue.customer_id = $scope.user1.customer_id;
        catalogue.attachment_delete = [];
        if($scope.file.delete){
            angular.forEach($scope.file.delete, function(i,o){
                var obj = {};
                obj.id_document = i.id_document;
                catalogue.attachment_delete.push(obj) ;
            });
        }
        catalogue.links_delete=$scope.links_delete;
        catalogue.links=$scope.catalogueLinks;
        // console.log("att",$scope.file);
        var params = {};
        angular.copy(catalogue,params);
        params.updated_by = $scope.user.id_user;

                 Upload.upload({
                    url: API_URL+'Catalogue/add',
                     data: {
                        'file' : $scope.file.attachment,
                        'catalogue': catalogue
                     }
                 }).then(function(resp){
                 if(resp.data.status){
                    $rootScope.toast('Success',resp.data.message);
                    $state.go('app.catalogue.catalogue-list');
                     var obj = {};
                     obj.action_name = 'add';
                     obj.action_description = 'add$$contract$$'+catalogue.catalogue_name;
                     obj.module_type = $state.current.activeLink;
                     obj.action_url = $location.$$absUrl;
                     $rootScope.confirmNavigationForSubmit(obj);
                 }else{
                    $rootScope.toast('Error',resp.data.error,'error',$scope.catalogue);
                 }
                 },function(resp){
                    $rootScope.toast('Error',resp.error);
                 },function(evt){
                    var progressPercentage=parseInt(100.0*evt.loaded/evt.total);
                 });                   
    }


    $scope.cancel = function () {
        $state.go('app.catalogue.catalogue-list');
    }
})

.config(function(scrollableTabsetConfigProvider){
    scrollableTabsetConfigProvider.setShowTooltips (true);
    scrollableTabsetConfigProvider.setTooltipLeftPlacement('bottom');
    scrollableTabsetConfigProvider.setTooltipRightPlacement('left');
})
