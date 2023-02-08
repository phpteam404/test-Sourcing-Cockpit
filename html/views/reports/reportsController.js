angular.module('app', ['localytics.directives'])
.controller('reportsCtrl', function($scope, $rootScope,$filter, $state,$translate, $stateParams,$localStorage, reportsService,encode,decode, $location,AuthService,userService){
    $scope.dynamicPopover = {
        butemplateUrl: 'businessTemplate.html',
        cltemplateUrl: 'classificationTemplate.html',
        sttemplateUrl: 'statusTemplate.html',

    };

    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }

    $scope.displayCount = $rootScope.userPagination;
    $scope.reportsListServer = function(tableState){
        $rootScope.displayName = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $rootScope.module = '';
        $scope.isLoading = true;
        $scope.tableStateRef=tableState;
        tableState.id_user = $scope.user1.id_user;
        tableState.user_role_id = $scope.user1.user_role_id;
        tableState.customer_id = $scope.user1.customer_id;
        var pagination = tableState.pagination;
        reportsService.reportsList(tableState).then(function(result){
            angular.forEach(result.data.data, function(item,o){
                if(item.business_units)item.business_units = item.business_units.split(',');
                if(item.classifications)item.classifications = item.classifications.split(',');
                if(item.review_statuses)item.review_statuses = item.review_statuses.split(',');
            })
            $scope.reportsList = result.data.data;
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
                $scope.reportsListServer($scope.tableStateRef);
            }                
        });
    }
    $scope.goToCreateReport = function(){
        $state.go('app.reports.create-report');
    }
    $scope.goToEditReport = function(row) {
        var urlParams = [];
        if(row.business_unit_ids){
            urlParams["bu"]=encode(row.business_unit_ids);
        }
        if(row.classification_ids){
            urlParams["cl"]=encode(row.classification_ids);
        }
        if(row.review_statuses){
            urlParams["st"]=encode(row.review_statuses);
        }
        if(row.contract_ids){
            urlParams["con"]=encode(row.contract_ids);
        }
        if(row.id_report){
            urlParams["id"]=encode(row.id_report);
        }
        $state.go('app.reports.report-edit',urlParams);
    }
    $scope.goToViewReport = function(row) {
        var urlParams = [];
        if(row.business_unit_ids){
            urlParams["bu"]=encode(row.business_unit_ids);
        }
        if(row.classification_ids){
            urlParams["cl"]=encode(row.classification_ids);
        }
        if(row.review_statuses){
            urlParams["st"]=encode(row.review_statuses);
        }
        if(row.contract_ids){
            urlParams["con"]=encode(row.contract_ids);
        }
        if(row.id_report){
            urlParams["id"]=encode(row.id_report);
        }
        $state.go('app.reports.report-view',urlParams);

    }
    $scope.deleteReport = function (row,$event) {
        var param ={};
        param.id_report = row.id_report;
        param.created_by = row.created_by;
        var r=confirm($filter('translate')('general.alert_delete_report'));
        $scope.deleConfirm = r;
        if(r==true){
            reportsService.deleteReport(param).then (function(result){
                if(result.status){
                    $rootScope.toast('Success',result.message);
                    var obj = {};
                    obj.action_name = 'delete';
                    obj.action_description = 'delete$$report$$( '+row.name+' )';
                    obj.module_type = $state.current.activeLink;
                    obj.action_url = $location.$$absUrl;
                    $rootScope.confirmNavigationForSubmit(obj);
                    $scope.reportsListServer($scope.tableStateRef);
                }
                else  $rootScope.toast('Error', result.error,'error');
            })
        }
    }
    $scope.exportReport = function(row) {
        var param ={};
        param.id_user = $scope.user1.id_user;
        param.customer_id = $scope.user1.customer_id;
        param.id_report = row.id_report;
        reportsService.exportReport(param).then(function(result){
            /*if(result.status){
                $rootScope.toast('Success', result.message);
                var a         = document.createElement('a');
                a.href        = result.data.file_path;
                a.download    = result.data.file_name;
                document.body.appendChild(a);
                var evObj = document.createEvent('MouseEvents');
                evObj.initEvent('click', true, true, window);
                a.dispatchEvent(evObj);
                var obj = {};
                obj.action_name = 'export';
                obj.action_description = 'export$$report$$( '+row.name+' )';
                obj.module_type = $state.current.activeLink;
                obj.action_url = window.location.href;
                $rootScope.confirmNavigationForSubmit(obj);
            }*/
            if(result.status){
                var obj = {};
                obj.action_name = 'export';
                obj.action_description = 'export$$report$$('+row.name+')';
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
                //$rootScope.toast('Success',result.message);
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
            }else{$rootScope.toast('Error',result.error,'error');}
        });
    }
})
.controller('createReportsCtrl', function($scope, $rootScope, $state, $stateParams,providerService, reportsService, $uibModal, encode, decode, dateFilter){
    $rootScope.displayName = '';
    $rootScope.module = '';
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
    $scope.customOptions = {};
    reportsService.getReportFilters({'customer_id':$scope.user1.customer_id,'can_review':1}).then(function(result){
        if(result.status) {
            if(result.data.criteria.business_units.length > 0)
                $scope.business_units = result.data.criteria.business_units;
            if(result.data.criteria.classifications.length > 0)
                $scope.classifications = result.data.criteria.classifications;
            if(result.data.criteria.contracts.length > 0)
                $scope.contracts = result.data.criteria.contracts;
            if(result.data.criteria.status.length > 0)
                $scope.status = result.data.criteria.status;
            if(result.data.criteria.description.length > 0)
                $scope.description = result.data.criteria.description;
        }
    });
    var params = {};
    params.customer_id = $scope.user1.customer_id;
    params.id_user  = $scope.user1.id_user;
    params.user_role_id  = $scope.user1.user_role_id;
    params.status  = 1;
    providerService.list(params).then(function(result){
        $scope.providers = result.data.data;
    });
    $rootScope.createReport = function(data){
        var urlParams = [];
        if(data.business_unit){
            urlParams["bu"]=encode(data.business_unit.toString());
        }
        if(data.classifications){
            urlParams["cl"]=encode(data.classifications.toString());
        }
        if(data.status){
            urlParams["st"]=encode(data.status.toString());
        }
        if(data.description){
            urlParams["desc"]=encode(data.description.toString());
        }
        if(data.contracts){
            urlParams["con"]=encode(data.contracts.toString());
        }
        if(data.provider){
            urlParams["pro"]=encode(data.provider.toString());
        }
        if($stateParams.id){
            urlParams["old"]=$stateParams.id;
        }
        if($stateParams.name){
            urlParams["name"]=$stateParams.name;
        }
        $state.go('app.reports.report-edit',urlParams);
      }
    if($stateParams){
        if($stateParams.bu){
            $scope.business_units = decode($stateParams.bu);
            $scope.customOptions.business_unit = $scope.business_units.split(',') ;
        }
        if($stateParams.cl){
            $scope.classifications = decode($stateParams.cl);
            $scope.customOptions.classifications = $scope.classifications.split(',') ;
        }
        if($stateParams.con){
            $scope.contracts = decode($stateParams.con);
            $scope.customOptions.contracts = $scope.contracts.split(',') ;
        }
        if($stateParams.st){
            $scope.status = decode($stateParams.st);
            $scope.customOptions.status = $scope.status.split(',') ;
        }
        if($stateParams.desc){
            $scope.description = decode($stateParams.desc);
            $scope.customOptions.description = $scope.description.split(',') ;
        }
    }
})
.controller('generateReportsCtrl', function($scope, $rootScope, $state,$filter, $stateParams, encode, decode, reportsService, $uibModal, $timeout, $location,AuthService,userService){
    $scope.reportsData = {};
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
    $scope.getSearchResults = function(){
        if($stateParams.name){
            $rootScope.displayName = $stateParams.name;
            $rootScope.module = 'Reports';
        }
        else {$rootScope.displayName = '';
            $rootScope.module = '';}
        var params= {};
        params.customer_id = $scope.user1.customer_id;
        if($stateParams){
            if($stateParams.bu) params.business_unit_ids = decode($stateParams.bu);
            if($stateParams.cl) params.classification_ids  = decode($stateParams.cl);
            if($stateParams.con)params.contract_ids  = decode($stateParams.con);
            if($stateParams.st)params.review_statuses  = decode($stateParams.st);
            if($stateParams.desc)params.calender_ids  = decode($stateParams.desc);
            if($stateParams.old) params.old_report_id = decode($stateParams.old) ;
            if($stateParams.pro) params.provider_ids = decode($stateParams.pro) ;
        }
        console.log('params--', params);
        reportsService.searchReports(params).then(function(result){
            if(result.status) {
                $scope.emptyTable=false;
                $scope.report_id = result.data.id_report;
                $scope.reportsData = result.data.data.report_contracts;
                $scope.searchModules = result.data.parent_modules;
                if(result.data.data.report_contracts.length < 1) $scope.emptyTable=true;
            }
        });
    }
    $scope.getReportDetails = function(id) {
        var params ={};
        if($stateParams.old) params.old_report_id = decode($stateParams.old) ;
        params.id_report = decode(id) ;
        params.customer_id = $scope.user1.customer_id;
        params.id_user = $scope.user1.id_user;
        params.user_role_id = $scope.user1.user_role_id;
        reportsService.getReportDetails(params).then(function(result){
            $scope.searchReultsData = result.data;
            $scope.report_id = decode(id);
           if(result.data.result[0].name != undefined) $scope.report_name = result.data.result[0].name;
            $rootScope.module = 'Report';
            $rootScope.displayName = $scope.report_name;
            $scope.reportsData = result.data.result[0].report_contracts;
            $scope.searchModules = result.data.global_modules;                 
        });
    }
    if($stateParams.id)
        $scope.getReportDetails($stateParams.id);
    else $scope.getSearchResults();
    $scope.changeDescision = function(val,row) {
        angular.forEach($scope.reportsData, function(item,key){
            if(item.contract_id){
                if(item.contract_id == row.contract_id){
                    if(val){item.decision_required =1;}
                    if(!val){item.decision_required =0;}
                }
            }else if(item.id_contract == row.id_contract){
                if(val){item.decision_required =1;}
                if(!val){item.decision_required =0;}
            }
        })
    }
    $scope.saveReport = function (data,save_type) {
        var params = {};
        if($stateParams.old) params.old_id_report = decode($stateParams.old) ;
        params.created_by = $scope.user1.id_user;
        if(typeof $scope.report_id!='undefined') {
            if ((isNaN($scope.report_id) === false && $scope.report_id > 0) || (isNaN($scope.report_id) === true && $scope.report_id.length > 0)) params.id_report = $scope.report_id;
            else  params.id_report = '';
        }
        else params.id_report = '';
        params.customer_id = $scope.user1.customer_id;
        if($stateParams.bu)params.business_unit_ids = decode($stateParams.bu);
        if($stateParams.cl)params.classification_ids = decode($stateParams.cl);
        if($stateParams.con)params.contract_ids = decode($stateParams.con);
        if($stateParams.st)params.review_statuses =  decode($stateParams.st);
        angular.forEach(data, function(item,key){
            if(!item.id_report_contract) item.id_report_contract='';
            angular.forEach(item.modules, function(i,o){
                if(!(item.module_score == undefined)){                
                    if(item.module_score[item.id_contract][i.module_id] == undefined) item.module_score[item.id_contract][i.module_id] = '';
                    i.score = item.module_score[item.id_contract][i.module_id];  
                } 
            });
           //delete item.module_score;
        });
        params.report_contracts = data;
        params.save_type = save_type;
        if(save_type == 'save'){
            if($scope.report_id){
                params.report_name = $scope.report_name;
                reportsService.saveReport(params).then(function(result){
                    if(result.status){
                        $rootScope.toast('Success',result.message);
                        var obj = {};
                        obj.action_name = 'update';
                        obj.action_description = 'update$$report$$( '+params.report_name+' )';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                        var urlParams = [];
                        if($stateParams.bu){
                            urlParams["bu"]=$stateParams.bu;
                        }
                        if($stateParams.cl){
                            urlParams["cl"]=$stateParams.cl;
                        }
                        if($stateParams.st){
                            urlParams["st"]=$stateParams.st;
                        }
                        if($stateParams.con){
                            urlParams["con"]=$stateParams.con;
                        }
                        if(result.data.id_report){
                            urlParams["id"]=encode(result.data.id_report);
                        }
                        $state.transitionTo('app.reports.report-edit',urlParams);
                    }else  $rootScope.toast('Error', result.error,'error');
                });
            }
            else if($stateParams.old){
                if($stateParams.old){
                    params.save_type =  'change criteria';
                    params.report_name = $stateParams.name;
                    reportsService.saveReport(params).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success',result.message);
                            var obj = {};
                            obj.action_name = 'add';
                            obj.action_description = 'add$$report$$( '+params.report_name+' )';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            var urlParams = [];
                            if($stateParams.bu){
                                urlParams["bu"]=$stateParams.bu;
                            }
                            if($stateParams.cl){
                                urlParams["cl"]=$stateParams.cl;
                            }
                            if($stateParams.st){
                                urlParams["st"]=$stateParams.st;
                            }
                            if($stateParams.con){
                                urlParams["con"]=$stateParams.con;
                            }
                            if(result.data.id_report){
                                urlParams["id"]=encode(result.data.id_report);
                            }
                            $state.transitionTo('app.reports.report-edit',urlParams);
                        }else  $rootScope.toast('Error', result.error,'error');
                    });
                }
            }else{
                $scope.reportName(params,save_type);
            }
        }else{
            $scope.reportName(params,save_type);
        }
    }
    $scope.reportName = function(params,save_type) {
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'report-name.html',
            controller: function ($uibModalInstance, $scope) {
                $scope.save = function (name) {
                    params.report_name = name;
                    reportsService.saveReport(params).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success',result.message);
                            $scope.cancel();
                            var obj = {};
                            obj.action_name = 'add';
                            obj.action_description = 'add$$report$$( '+params.report_name+' )';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            var urlParams = [];
                            if($stateParams.bu){
                                urlParams["bu"]=$stateParams.bu;
                            }
                            if($stateParams.cl){
                                urlParams["cl"]=$stateParams.cl;
                            }
                            if($stateParams.st){
                                urlParams["st"]=$stateParams.st;
                            }
                            if($stateParams.con){
                                urlParams["con"]=$stateParams.con;
                            }
                            if(result.data.id_report){
                                urlParams["id"]=encode(result.data.id_report);
                            }
                            $state.transitionTo('app.reports.report-edit',urlParams);
                        }else  $rootScope.toast('Error', result.error,'error');
                    })
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            },resolve: {}
        });
        modalInstance.result.then(function (result) {
        }, function () {
        });
    }
    $scope.loadExportModal = function(){
        $scope.count =0;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'modal-open',
            templateUrl: 'views/reports/export-modal.html',
            resolve : {
            },
            controller: function ($uibModalInstance, $scope) {
                $scope.count =0;
                $scope.export={};
                var ids = [];
                angular.forEach($scope.reportsData, function(item,key){
                    if(item.is_checked == 1){
                        $scope.count = $scope.count+1;
                        ids.push(item.id_report_contract);
                    }
                });
                $scope.checkSelectedCounts = function(val){
                    if($scope.count <1){
                        $rootScope.toast('Warning', 'No contracts are selected','warning');
                        $scope.export.getSelected = 'all_contracts';
                    }else{
                        $scope.export.getSelected = 'selected_contracts';
                    }
                }
                $scope.exportReport = function(data) {
                    var params = {};
                    params.id_user = $scope.user1.id_user;
                    params.customer_id = $scope.user1.customer_id;
                    params.id_report = decode($stateParams.id);
                    if(data.getSelected == 'selected_contracts'){
                        params.id_report_contract = ids.toString();
                    }
                    if(data.latestReviewDate == 1)params.last_review = 'yes';
                    else params.last_review = 'no';
                    if(data.status == 1)params.status = 'yes';
                    else params.status = 'no';
                    if(data.actionItems == 1)params.actionItems = 'yes';
                    else params.action_items = 'no';
                    if(data.rag == 1)params.rag = 'yes';
                    else params.rag = 'no';
                    if(data.comments == 1)params.comments = 'yes';
                    else params.comments = 'no';
                    if(data.export_review == 1)params.export_review = 'yes';
                    else params.export_review = 'no';
                    reportsService.exportReport(params).then(function(result){
                        if(result.status){
                            //$rootScope.toast('Success', result.message);
                            /* var a         = document.createElement('a');
                            a.href        = result.data.file_path;
                            a.download    = result.data.file_name;
                            document.body.appendChild(a);
                            a.click();
                            var obj = {};
                            obj.action_name = 'export';
                            obj.action_description = 'export$$report$$( '+$scope.report_name+' )';
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = window.location.href;
                            $rootScope.confirmNavigationForSubmit(obj);*/
                            var obj = {};
                            obj.action_name = 'export';
                            obj.action_description = 'export$$report$$('+$scope.report_name+')';
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
                            //$rootScope.toast('Success',result.message);
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
                        }else{$rootScope.toast('Error',result.error,'error');}
                        $scope.cancel();
                    });
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                }
            }
        });
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    }
    $scope.changeCrietia = function($event,report_name){
        var r=confirm($filter('translate')('general.alert_Report_Saved_Data'));
        $scope.deleConfirm = r;
        if(r==true){
            var obj = {};
            obj.action_name = 'change criteria';
            obj.action_description = 'change$$criteria$$report($$'+report_name+')';
            obj.module_type = $state.current.activeLink;
            obj.action_url = $location.$$absUrl;
            $rootScope.confirmNavigationForSubmit(obj);
            if($stateParams){
                var urlParams = [];
                if($stateParams.bu){
                    urlParams["bu"]=$stateParams.bu;
                }
                if($stateParams.cl){
                    urlParams["cl"]=$stateParams.cl;
                }
                if($stateParams.st){
                    urlParams["st"]=$stateParams.st;
                }
                if($stateParams.con){
                    urlParams["con"]=$stateParams.con;
                }
                if($stateParams.id){
                    urlParams["id"]=$stateParams.id;
                }
                if(report_name){
                    urlParams["name"]=report_name;
                }
                $state.go('app.reports.create-report',urlParams);
               /* if($stateParams.con){
                    $state.go('app.reports.create-report',
                        {bu:$stateParams.bu,cl:$stateParams.cl,st:$stateParams.st,con:$stateParams.con,id:$stateParams.id,name:report_name});
                }else
                    $state.go('app.reports.create-report',
                        {bu:$stateParams.bu,cl:$stateParams.cl,st:$stateParams.st,id:$stateParams.id,name:report_name});*/
            }else $state.go('app.reports.create-report');
        }
    }
    $scope.getContractsToReport = function (tableState) {
        var params = {};
        params.customer_id = $scope.user1.customer_id;
        params.business_unit_ids = decode($stateParams.bu);
        params.classification_ids = decode($stateParams.cl);
        if ($stateParams.con)params.contract_ids = decode($stateParams.con);
        params.review_statuses = decode($stateParams.st);
        params.calender_ids = decode($stateParams.desc);
        params.id_report = decode($stateParams.id);
        params.individual_contracts = 1;
        reportsService.searchReports(params).then(function (result) {
            if (result.status) {
                $scope.noContracts = false;
                //$scope.searchModules = result.data.parent_modules;
                if(result.data.data.report_contracts.length <1) $scope.noContracts = true;
                $scope.contractReports = {};
                for(var a in $scope.reportsData){
                    for(var b in result.data.data.report_contracts){
                        if((result.data.data.report_contracts[b]) && (result.data.data.report_contracts[b].id_contract == $scope.reportsData[a].id_contract)){
                            result.data.data.report_contracts.splice(b,1);
                            $scope.contractReports = result.data.data.report_contracts;
                        }else{$scope.contractReports = result.data.data.report_contracts;}
                    }
                }
                $scope.addContractsToReport();
            }
        });
    }
    $scope.addContractsToReport = function () {
        $scope.contractsToAdd = [];
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'add-individual-contracts.html',
            size: 'lg',
            controller: function ($uibModalInstance, $scope) {
                $scope.selectedContracts = [];
                var is_Set = true;
                $scope.addContracts = function(data){
                    angular.forEach(data,function(i,o){
                        angular.forEach($scope.contractReports, function(it,ind){
                            if(it.id_contract == i){
                                $scope.reportsData[$scope.reportsData.length] = it;
                                var obj = {};
                                obj.action_name = 'add';
                                obj.action_description = 'add$$individual$$contracts';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                            }
                        });
                    });
                    $scope.cancel();
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            },resolve: {}
        });
        modalInstance.result.then(function (result) {
        }, function () {
        });
        /*$scope.addContracts = function(data){
            angular.forEach(data,function(i,o){
                console.log('data',i);
                angular.forEach($scope.contractReports, function(item,key){
                    if(item.id_contract == i){
                        $scope.reportsData[$scope.reportsData.length] = item;
                        //console.log('typeof reportsData',$scope.reportsData.length, $scope.reportsData[$scope.reportsData.length]);
                        console.log('$scope.reportsData',$scope.reportsData);
                    }
                });
            });
        }*/
    }
    $scope.sortableOptions={
        update: function(e,ui){
            var params = {};
            params.data = $scope.reportsData;
        },
        stop: function(e,ui){
        }
    };
    $scope.goToList = function() {
        $state.go('app.reports.reporting');
    }
})