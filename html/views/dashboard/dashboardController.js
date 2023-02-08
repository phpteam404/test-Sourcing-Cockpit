angular.module('app', ['ng-fusioncharts','ui.sortable','localytics.directives'])
    .controller('dashboardCtrl', function ($localStorage,$window, $filter,$scope,$translate,$sce,moment,$rootScope, $state, $timeout,$uibModal, dashboardService, 
        businessUnitService,contractService, encode, $uibModal, $location, AuthService, userService, dateFilter,projectService,tagService,providerService) {
            
        if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }

        if($localStorage.curUser.appVersion == undefined || $localStorage.curUser.appVersion !=$rootScope.appVersion){
            $localStorage.curUser.appVersion=$rootScope.appVersion;
            // $window.location.reload();
        }
        $scope.del = 0;
        $scope.resetPagination1=false;
        $scope.resetPagination2=false;
        $scope.showChartIcon=true;
        $scope.searchFields = {};
        $scope.activities={};
        $scope.def={};
        $scope.def2={};
        $scope.def3={};
        $scope.def4={};
        $scope.statusList=[];
        if ($rootScope.access == 'ca' || $rootScope.access == 'bo') {
            $scope.del = 1;
            $scope.active = 0;
        }
        if ($scope.user1.access == "eu") {
            $scope.active = 1;
            $scope.chartLabel='My contributions';
        } else $scope.active = 0;
        $scope.enableChart = false;


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
                if($scope.action.contract_id==undefined){
                   // console.log('in 145',resultarray);
                   action='deleted';
                    resultarray =[];
                }
                if($scope.action.contract_id!=undefined && $scope.action.contract_id.length==1){
                    action='added';
                    result_contract_id =$scope.action.contract_id;
                }
                if(resultarray[resultarray.length-2]!=undefined && resultarray[resultarray.length-1]!=undefined){
                    //console.log('entered if');
                     result_contract_id = arr_diff(resultarray[resultarray.length-2], resultarray[resultarray.length-1]);
                }
               
              
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
                        const currentKey = result_contract_id[0];
                        curData = curData.filter((item) => {return item.key != currentKey});
                    }
                    if($scope.action.contract_id==undefined){
                        curData=[];
                    }
                        var usersData=[];
                        angular.forEach(curData,function(i,o){
                            usersData.push(i.data);
                        })
                        res =intersection([usersData]);
                        $scope.userList =res;
                   });
                 
                    $scope.addActionItem = function(data){
                              $scope.due_date=angular.copy(data.due_date);
                              $scope.due_date=dateFilter($scope.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                              params = angular.copy(data);
                            if(data.type_id=='1') params.reference_type='contract';
                             if(data.type_id=='2') params.reference_type='project';
                             if(data.type_id=='3') params.reference_type='provider';
                             params.id_user  = $scope.user1.id_user;
                            params.user_role_id  = $scope.user1.user_role_id;
                            params.due_date = $scope.due_date;
                            params.created_by = $scope.user1.id_user;
                            if(key!='provider_id'){
                                params.contract_id= data.contract_id.toString();
                            }
                             if(key=='provider_id') {
                                 params.provider_id = data.contract_id.toString();
                                 delete params.contract_id=='';
                             }
                           
                            
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
        $scope.deleteContract = function (row) {
            var r = confirm($filter('translate')('general.alert_continue'));
            if (r == true) {
                var params = {};
                params.contract_id = row.id_contract;
                params.user_role_id = $scope.user1.user_role_id;
                params.id_user = $scope.user1.id_user;
                contractService.delete(params).then(function (result) {
                    if (result.status) {
                        var obj = {};
                        obj.action_name = 'Delete';
                        obj.action_description = 'contract delete $$(' + result.data.file_name + ')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = location.href;
                        if (AuthService.getFields().data.parent) {
                            obj.user_id = AuthService.getFields().data.parent.id_user;
                            obj.acting_user_id = AuthService.getFields().data.data.id_user;
                        }
                        else obj.user_id = AuthService.getFields().data.data.id_user;
                        if (AuthService.getFields().access_token != undefined) {
                            var s = AuthService.getFields().access_token.split(' ');
                            obj.access_token = s[1];
                        }
                        else obj.access_token = '';
                        $rootScope.toast('Success', result.message);
                        if ($state.current.name == "app.dashboard2")
                            $state.go('app.dashboard');
                        else $state.go('app.dashboard2');
                    }
                });
            }
        }
        $rootScope.chatLables = {};
        $rootScope.module = '';
        $rootScope.displayName = '';
        $rootScope.icon ='';
        $rootScope.class='';
        $rootScope.breadcrumbcolor='';
        $scope.dashboardData = {};
        $scope.your_action_items = 0;
        $scope.advanced_search1=0;
        $scope.advanced_search2=0;
        $scope.displayCount = $rootScope.userPagination;

        //console.log("user",$scope.user1.access);

        if($scope.user1.access !='eu' && $scope.user1.access !='wa'){
            //console.log("Asd",$scope.user1.access);
            var params = {};
            params.id_user = $scope.user1.id_user;
            params.user_role_id = $scope.user1.user_role_id;
            params.customer_id = $scope.user1.customer_id;
            dashboardService.allCounts(params).then(function (result) {
                $scope.dashboardDataCount = result.data;
            });
        }
        

            $scope.widgetacivityGraph = function () {
            var params = {};
            params.id_user = $scope.user1.id_user;
            params.user_role_id = $scope.user1.user_role_id;
            params.customer_id = $scope.user1.customer_id;
            dashboardService.acivityGraph(params).then(function (result) {
                $scope.allActivitiesData=result.data.all_activity;
                $scope.activitiesCount = result.data.all_activity.counts;
                $scope.activities.data = $scope.allActivitiesData.graph;
                //console.log("Asdf",$scope.activities.data)
            })    
        };
        if($scope.user1.access!='eu' && $scope.user1.access!='wa'){
            $scope.widgetacivityGraph();
            //console.log("ad",$scope.user1.access)
        }

        $scope.widgetRelationsGraph = function () {
            var params = {};
            params.id_user = $scope.user1.id_user;
            params.user_role_id = $scope.user1.user_role_id;
            params.customer_id = $scope.user1.customer_id;
            dashboardService.relationsGraph(params).then(function (result) {
                $scope.providersCount = result.data.providers.count;
                $scope.relations_currency_name=result.data.main_currency_name;
                $scope.def4.data = result.data.providers.provider_finacial_health_graph;
                $scope.def3.data = result.data.providers.provider_approval_status_graph;
                $scope.def2.data = result.data.providers.provider_risk_profile_graph;
                $scope.labelsAllrelations=result.data.provider_lables;
            })    
        };

        $scope.widgetactionsGraph = function () {
            var params = {};
            params.id_user = $scope.user1.id_user;
            params.user_role_id = $scope.user1.user_role_id;
            params.customer_id = $scope.user1.customer_id;
            dashboardService.actionItemsGraph(params).then(function (result) {
                $scope.actionItemcount = result.data.action_item.counts;
                $scope.def.data = result.data.action_item.graph;
            })    
        };

        $scope.widgetprojectsGraph = function () {
            var params = {};
            params.id_user = $scope.user1.id_user;
            params.user_role_id = $scope.user1.user_role_id;
            params.customer_id = $scope.user1.customer_id;
            dashboardService.projectsGraph(params).then(function (result) {
                $scope.projectsCount = result.data.projects;
                $scope.project_currency_name=result.data.main_currency_name;
            })    
        };

        $scope.widgetcontractsGraph = function () {
            var params = {};
            params.id_user = $scope.user1.id_user;
            params.user_role_id = $scope.user1.user_role_id;
            params.customer_id = $scope.user1.customer_id;
            dashboardService.contractsGraph(params).then(function (result) {
                $scope.endDateCounts = result.data.end_date;
                $scope.contract_currency_name=result.data.main_currency_name;
            })    
        };


        $scope.widgetcoworkersGraph = function () {
            var params = {};
            params.id_user = $scope.user1.id_user;
            params.user_role_id = $scope.user1.user_role_id;
            params.customer_id = $scope.user1.customer_id;
            dashboardService.coworkersGraph(params).then(function (result) {
                $scope.topContributors = result.data.co_workers_obj.top_contributions;
                $scope.coworkersCount = result.data.co_workers_obj.counts;
                $scope.coworkers.data = result.data.co_workers_obj.graph;
                //console.log("asd",$scope.coworkers.data);
            })    
        };




        $scope.getContractsInChart1 = function () {
            if($scope.user.access!='eu') {
                $scope.hideChart(false);
                $scope.searchFields = {};
                //$scope.contractOverallDetails($scope.tableStateRef, 'my_reviews');
            }
        }
        $scope.getContractsInChart2 = function () {
            $scope.hideChart(false);
            $scope.searchFields = {};
           // $scope.contractOverallDetails($scope.tableStateRef2, 'my_contributions');
        }
        $scope.getContractsInChart3 = function () {
            $scope.hideChart(false);
            $scope.tableStateRef1.tab = 'action item';
            //$scope.contractOverallDetails($scope.tableStateRef1, 'action_item');
        }
        $scope.displayChart = function () {
            angular.element('#chart').removeClass('hide');
            angular.element('#chart').removeAttr('ng-hide');
            $scope.enableChart = true;
            return true;
        }
        $scope.hideChart = function (flag) {
            if (flag) $rootScope.chatLables = $scope.myDataSource.chart;
            $timeout(function () {
                angular.element('#chart').addClass('hide');
            });
            $scope.enableChart = false;
        }
        // $scope.widgetinfo();
        $scope.myDataSource = {};
        $scope.contractOverallDetails = function (tableState, type) {
            var params = {};
            params = tableState;
            params.customer_id = $scope.user1.customer_id;
            params.id_user = $scope.user1.id_user;
            params.user_role_id = $scope.user1.user_role_id;
            params.chart_type = type;
            contractService.contractOverallDetails(params).then(function (result) {
                if (result.status) {
                    $scope.myDataSource = result.data;                    
                }
            });
        };
        if($scope.user1.access!='eu' && $scope.user1.access!='wa') {
        $scope.getPendingReviews = function (tableState) {
            $scope.isLoading22 = true;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            tableState.business_unit_id = $scope.business_unit_id;
            tableState.id_user = $scope.user1.id_user;
            tableState.user_role_id = $scope.user1.user_role_id;
            tableState.contract_status = 'pending review,review in progress,pending workflow,workflow in progress';
            tableState.all_contract = true;
            tableState.advanced_search = $scope.advanced_search1;
            if($scope.resetPagination1){
                tableState.pagination.start='0';
                tableState.pagination.number='10';
            }
            $scope.tableStateRef = tableState;
            if(tableState.advancedsearch_get){}
            else {
                tableState.advancedsearch_get={};
            }
    
            dashboardService.reviewsList(tableState).then(function (result) {
                    //$scope.contractOverallDetails(tableState, 'my_reviews');
                    $rootScope.chatLables = $scope.myDataSource.chart;
                $timeout(function () {
                }, 300);
                $scope.contractReviews = result.data.data;
                $scope.emptyTable = false;
                $scope.totalReviews = result.data.total_records;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords1 = result.data.total_records;
                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                if (result.data.total_records < 1) {
                    $scope.emptyTable = true;
                }
                $scope.resetPagination1=false;
                $scope.isLoading22 = false;
            })
        }
    }
        $scope.defaultPages1 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.getPendingReviews($scope.tableStateRef);
                }                
            });
        }
        $scope.getActionItems = function (tableState1) {
            $scope.isLoading1 = true;
            delete tableState1.contract_status;
            var pagination1 = tableState1.pagination;
            tableState1.id_user = $scope.user1.id_user;
            tableState1.user_role_id = $scope.user1.user_role_id;
            tableState1.customer_id = $scope.user1.customer_id;
            tableState1.contract_review_action_item_status = 'open';
            tableState1.page_type = 'dashboard';
            tableState1.all_contract = true;
            tableState1.advanced_search = $scope.advanced_search2;
            if($scope.resetPagination2){
                tableState1.pagination.start='0';
                tableState1.pagination.number='10';
            }
            $scope.tableStateRef1 = tableState1;
            contractService.getAllActionItems(tableState1).then(function (result) {
                $scope.actionItemsList = result.data.data;
                $scope.emptyTable1 = false;
                $scope.your_action_items = result.data.total_records;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState1.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading1 = false;
                if (result.data.total_records < 1)
                    $scope.emptyTable1 = true;
            })
        }
        $scope.defaultPages = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.getActionItems($scope.tableStateRef1);
                }                
            });
        }
        $scope.getMyContracts = function (tableState) {
            $scope.tableStateRef = tableState;
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            tableState.business_unit_id = $scope.business_unit_id;
            tableState.created_by = $scope.user1.id_user;
            tableState.id_user = $scope.user1.id_user;
            contractService.list(tableState).then(function (result) {
                $scope.myContract = result.data.data;
                $scope.myContractCount = result.data.total_records;
                $scope.emptyMyContractTable = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords2 = result.data.total_records;
                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isMyContractLoading = false;
                if (result.data.total_records < 1)
                    $scope.emptyMyContractTable = true;
            })
        }
        $scope.defaultPages2 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.getMyContracts($scope.tableStateRef);
                }                
            });
        }
        $scope.getContributingToContracts = function (tableState) {
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            tableState.business_unit_id = $scope.business_unit_id;
            tableState.id_user = $scope.user1.id_user;
            tableState.contract_status = 'review in progress,workflow in progress';
            tableState.customer_user = $scope.user1.id_user;
            tableState.all_contract = true;
            $scope.tableStateRef2 = tableState;
            if(tableState.advancedsearch_get){}
            else {
                tableState.advancedsearch_get={};
            }
            tableState.pagination.number = 10;
            dashboardService.contributorsList(tableState).then(function (result) {
                //console.log('tableState.search.predicateObject', window.navigator.userAgent);
                if (($scope.user.access == 'eu') ||
                    (!angular.equals(tableState.search,{}))) {
                   // $scope.contractOverallDetails(tableState, 'my_contributions');
                    $rootScope.chatLables = $scope.myDataSource.chart;                    
                    }
                $scope.contributingToContract = result.data.data;
                $scope.contributingToContractCount = result.data.total_records;
                $scope.emptyContributingToTable = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords3 = result.data.total_records;
                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isContributingToLoading = false;
                if (result.data.total_records < 1)
                    $scope.emptyContributingToTable = true;
            })
        }
        $scope.defaultPages3 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.getContributingToContracts($scope.tableStateRef2);
                }                
            });
        }
        $scope.goToOverview = function () {
            $state.go('app.contract.contract-overview');
        }
        $scope.getEndDateCntracts = function () {
            $state.go('app.contract.all-contracts', { end_date: true });
        }

        $scope.getEndDateProjects = function () {
            $state.go('app.projects.all-projects', { end_date: true });
        }
        $scope.goToActionItems = function () {
            $state.go('app.actionItems', { status: encode('open') });
        }

        $scope.goToRelations = function(){
            $state.go('app.provider.all-providers');
        }
        $scope.goToUsers = function () {
            $state.go('app.customer-user.list');
        }
        $scope.goToContributors = function () {
            $state.go('app.contributors.list');
        }

        $scope.goToContractDashboard = function (row) {
            if(row.is_workflow=='0' && $rootScope.access!='eu' && row.type =='contract')
                $state.go('app.contract.contract-dashboard', { name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),type:'review'});
            if(row.is_workflow =='1' && $rootScope.access !='eu' && row.type=='contract')
                $state.go('app.contract.workflow-dashboard', { name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow' });
            if(row.is_workflow=='1' && $rootScope.access =='eu' && row.type=='contract')
                $state.go('app.contract.workflow-dashboard11', { name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow' });
            if(row.is_workflow=='0' && $rootScope.access =='eu' && row.type=='contract')
                $state.go('app.contract.contract-dashboard11', { name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),type:'review'});
            if(row.is_workflow =='1' && $rootScope.access !='eu' && row.type=='project')
               $state.go('app.projects.project-dashboard1',{name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
            if(row.is_workflow =='1' && $rootScope.access =='eu' && row.type=='project')
              $state.go('app.projects.project-dashboard11',{name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
        }
        $scope.exportContractReview = function (row) {
            //console.log('row info',row);
            if(row.type=='contract'){
                var params = {};
                params.contract_id = params.id_contract = row.id_contract;
                params.id_user = $scope.user1.id_user;
                params.user_role_id = $scope.user1.user_role_id;
                params.is_workflow  = row.is_workflow;
                params.contract_workflow_id  = row.id_contract_workflow;
                contractService.exportReviewData(params).then(function (result) {
                    if (result.status) {
                        var obj = {};
                        obj.action_name = 'export';
                        obj.action_description = 'export$$contract$$review$$(' + row.contract_name + ')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = location.href;
                        if (AuthService.getFields().data.parent) {
                            obj.user_id = AuthService.getFields().data.parent.id_user;
                            obj.acting_user_id = AuthService.getFields().data.data.id_user;
                        }
                        else obj.user_id = AuthService.getFields().data.data.id_user;
                        if (AuthService.getFields().access_token != undefined) {
                            var s = AuthService.getFields().access_token.split(' ');
                            obj.access_token = s[1];
                        }
                        else obj.access_token = '';
                        $rootScope.toast('Success', result.message);
                        userService.accessEntry(obj).then(function (result1) {
                            if (result1.status) {
                                if (DATA_ENCRYPT) {
                                    result.data.file_path = GibberishAES.enc(result.data.file_path, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                    result.data.file_name = GibberishAES.enc(result.data.file_name, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                }
                                window.location = API_URL + 'download/downloadreportnew?id_download=' + result.data + '&user_id=' + obj.user_id + '&access_token=' + obj.access_token;
    
                            }
                        });
                    } else { $rootScope.toast('Error', result.error, 'l-error'); }
                })
            }
            else{
                var params ={};
                params.contract_id = params.id_contract = row.id_contract;
                params.id_user = $scope.user1.id_user;
                params.user_role_id = $scope.user1.user_role_id;
                params.is_workflow  = row.is_workflow;
                params.contract_workflow_id  = row.id_contract_workflow;
                params.contract_review_id = row.id_contract_review;
                projectService.exportProjectDashboardData(params).then(function (result) {
                    if (result.status) {
                        var obj = {};
                        obj.action_name = 'export';
                        obj.action_description = 'export$$project$$task$$(' + row.contract_name + ')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = location.href;
                        if (AuthService.getFields().data.parent) {
                            obj.user_id = AuthService.getFields().data.parent.id_user;
                            obj.acting_user_id = AuthService.getFields().data.data.id_user;
                        }
                        else obj.user_id = AuthService.getFields().data.data.id_user;
                        if (AuthService.getFields().access_token != undefined) {
                            var s = AuthService.getFields().access_token.split(' ');
                            obj.access_token = s[1];
                        }
                        else obj.access_token = '';
                        $rootScope.toast('Success', result.message);
                        userService.accessEntry(obj).then(function (result1) {
                            if (result1.status) {
                                if (DATA_ENCRYPT) {
                                    result.data.file_path = GibberishAES.enc(result.data.file_path, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                    result.data.file_name = GibberishAES.enc(result.data.file_name, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                }
                                window.location = API_URL + 'download/downloadreportnew?id_download=' + result.data + '&user_id=' + obj.user_id + '&access_token=' + obj.access_token;
    
                            }
                        });
                    } else { $rootScope.toast('Error', result.error, 'l-error'); }
                })
            }
          
            
        }
        var parentPage = $state.current.url.split("/")[1];
        var goWorkflow = (parentPage == 'all-activities') ? 'app.contract.contract-workflow' : 'app.contract.contract-workflow1';
        var goReview = (parentPage == 'all-activities') ? 'app.contract.contract-review' : 'app.contract.contract-review1';
        $scope.goToContractReview = function (row) {
        if(row.is_workflow=='0' && $rootScope.access !='eu' && row.type=='contract')
             $state.go(goReview,{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
        if(row.is_workflow =='1' && $rootScope.access !='eu' && row.type=='contract')
                $state.go(goWorkflow,{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
        
        if(row.is_workflow=='0' && $rootScope.access == 'eu'  && row.type=='contract')
                $state.go('app.contract.contract-review11',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
        if(row.is_workflow=='1' && $rootScope.access =='eu'  && row.type=='contract')
            $state.go('app.contract.contract-workflow11',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
        if(row.is_workflow=='1' && row.type =='project'  && $rootScope.access != 'eu' )
             $state.go('app.projects.project-task',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review), wId:encode(row.id_contract_workflow),type:'workflow'});
        if(row.is_workflow=='1' && row.type =='project'  && $rootScope.access == 'eu')
            $state.go('app.projects.project-task1',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review), wId:encode(row.id_contract_workflow),type:'workflow'});
        }
        $scope.goToPendingContracts = function () {
            $state.go('app.contract.contract-overview', { 'status': 'pending review' });
        }
        $scope.updateContractReview = function (row, type) {
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
                        $scope.data.due_date = new Date($scope.data.due_date);
                        $scope.title = '';
                        $scope.update = true;
                        $scope.bottom = 'general.update';
                    }else{$scope.data.due_date = new Date();}
                    if ($scope.type == 'view') {
                        $scope.bottom = 'contract.finish';
                    }
                    if ($scope.type == 'add') {
                        $scope.bottom = 'general.update';
                    }
                    var param = {};
                    param.contract_id = row.contract_id;
                    param.customer_id = $scope.user1.customer_id;
                    param.user_role_id = $scope.user1.user_role_id;
                    param.contract_review_id = row.contract_review_id;
                    contractService.getActionItemResponsibleUsers(param).then(function (result) {
                        $scope.userList = result.data;
                    });
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.goToEdit = function (data) {
                        $scope.data.due_date = new Date(data.due_date);
                    }
                    var params = {};
                    $scope.getActionItemById = function (id) {
                        contractService.getActionItemDetails({ 'id_contract_review_action_item': id }).then(function (result) {
                            $scope.data = result.data[0];
                        });
                    }
                    $scope.addReviewActionItem = function (data) {
                        data.due_date =  dateFilter(data.due_date,'yyyy-MM-ddTHH:mm:ss.sssZ');
                        if ($scope.type == 'view') {
                            params.id_contract_review_action_item = data.id_contract_review_action_item;
                            params.comments = data.comments;
                            params.is_finish = data.is_finish;
                            params.external_users = data.external_users;
                            params.updated_by = $scope.user.id_user;
                            params.contract_id = row.contract_id;
                            params.reference_type= row.type;
                            if (params.is_finish == 1) {
                                var r = confirm($filter('translate')('general.alert_action_finish'));
                                $scope.deleConfirm = r;
                                if (r == true) {
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'Finish Action Item$$(' + data.action_item + ')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    contractService.reviewActionItemUpdate(params).then(function (result) {
                                        if (result.status) {
                                            $rootScope.toast('Success', result.message);
                                            $scope.getActionItems($scope.tableStateRef1);
                                            $scope.cancel();
                                        } else {
                                            $rootScope.toast('Error', result.error, 'error');
                                        }
                                    });
                                }
                            } else {
                                contractService.reviewActionItemUpdate(params).then(function (result) {
                                    if (result.status) {
                                        $rootScope.toast('Success', result.message);
                                        $scope.getActionItemById(data.id_contract_review_action_item);
                                        $scope.getActionItems($scope.tableStateRef1);
                                        var obj = {};
                                        obj.action_name = 'save';
                                        obj.action_description = 'save Action Item$$(' + data.action_item + ')';
                                        obj.module_type = $state.current.activeLink;
                                        obj.action_url = $location.$$absUrl;
                                        $rootScope.confirmNavigationForSubmit(obj);
                                        $scope.cancel();
                                    } else {
                                        $rootScope.toast('Error', result.error, 'error');
                                    }
                                });
                            }
                        }
                        else if (data != 0 && data.hasOwnProperty('id_contract_review_action_item')) {
                            delete data.comments;
                            params = data;
                            params.updated_by = $scope.user.id_user;
                            params.contract_id = params.id_contract = row.contract_id;
                            param.reference_type= row.type;
                            contractService.addReviewActionItemList(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    $scope.getActionItemById(data.id_contract_review_action_item);
                                    $scope.getActionItems($scope.tableStateRef1);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update Action Item$$(' + data.action_item + ')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.type = 'view';
                                    $scope.bottom = 'contract.finish'
                                } else {
                                    $rootScope.toast('Error', result.error, 'error');
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
        $scope.goToContratDiscussion = function (row) {
            if(row.is_workflow=='1' && row.type=='project' && $rootScope.access !='eu')
                $state.go('app.projects.task-design',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
            if(row.is_workflow=='1' && row.type=='contract'  && $rootScope.access !='eu')
                $state.go('app.contract.workflow-design', { name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow' });
             if(row.is_workflow =='0' && row.type=='contract'  && $rootScope.access !='eu')
               $state.go('app.contract.review-design', { name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),type:'review' });
            if(row.is_workflow=='1' && row.type=='project' && $rootScope.access =='eu')
             $state.go('app.projects.task-design1',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
             if(row.is_workflow=='1' && row.type=='contract'  && $rootScope.access =='eu')
               $state.go('app.contract.workflow-design11233', { name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow' });
            if(row.is_workflow =='0' && row.type=='contract'  && $rootScope.access =='eu')
                $state.go('app.contract.review-design12334', { name: row.contract_name, id: encode(row.id_contract), rId: encode(row.id_contract_review),type:'review' });
        }

        $scope.createContract = function (row) {
            $state.go('app.contract.create-contract');
        }
        $scope.createProject = function (row) {
            $state.go('app.projects.create-project');
        }
        $scope.createRelation = function (row) {
            $state.go('app.provider.prvcreate');
        }

        $scope.customer_user = $localStorage.curUser.data.data;
        $scope.usersList = {};
        $scope.req={};
        $scope.req.status=0;

        // businessUnitService.list({'user_role_id':$scope.user1.user_role_id,'customer_id': $scope.customer_user.customer_id, status: 1,id_user:$scope.user1.id_user}).then(function(result){
        //     $scope.bussinessUnit = result.data.data;
        //     if($stateParams.buId){
        //         $scope.business_unit_id = decode($stateParams.buId);
        //     }
        // });

        $scope.createInternalUser = function () {
                $state.go('app.customer-user.create-customer-user', { id: encode($scope.customer_user.customer_id) });
        }



        $scope.chartLabel='';
        $scope.buttonClicked = function (index,tabName) {
            $scope.active = index;
            $scope.chartLabel=tabName;
            angular.element('#btn-adv-search2').removeClass('adv-active-search');
            angular.element('#btn-adv-search1').removeClass('adv-active-search');
            if(index==2)$scope.showChartIcon=false;
            else $scope.showChartIcon=true;
        };
        if ($scope.user1.access == "eu") {
            $scope.buttonClicked(1,'My contributions');
        } else  $scope.buttonClicked(0,'My reviews');

        $scope.getAdvSearch1 = function(val){
            if($scope.tableStateRef.search.predicateObject == undefined || 
                $scope.tableStateRef.search.predicateObject.search_key == undefined){
                    $scope.resetPagination1=false;
            }else  $scope.resetPagination1=true;
            $scope.advanced_search1=val;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open adv-search-model',
                templateUrl: 'views/Manage-Users/contracts/advance-search-fields.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.getSearchRecords=function(data){
                        $scope.searchFields=data;
                        // $scope.tableStateRef.advancedsearch_get = data;
                        $scope.checkBoxes = {};
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
                            angular.element('#btn-adv-search1').addClass('adv-active-search');
                            angular.element('#search_key1').focus();
                        }else{
                            angular.element('#btn-adv-search1').removeClass('adv-active-search');
                        }

                        $scope.cancel();
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
        $scope.getAdvSearch2 = function(val){
            if($scope.tableStateRef2.search.predicateObject == undefined || 
                $scope.tableStateRef2.search.predicateObject.search_key == undefined){
                    $scope.resetPagination2=false;
            }else  $scope.resetPagination2=true;
            $scope.advanced_search2=val;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open adv-search-model',
                templateUrl: 'views/Manage-Users/contracts/advance-search-fields.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.getSearchRecords=function(data){
                        $scope.searchFields=data;
                        $scope.checkBoxes={};
                       // $scope.tableStateRef2.advancedsearch_get = data;
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
    
                        $scope.tableStateRef2.advancedsearch_get = $scope.checkBoxes;
                        if(!angular.equals($scope.checkBoxes, {})){
                            angular.element('#btn-adv-search2').addClass('adv-active-search');
                            angular.element('#search_key2').focus();
                        }else{
                            angular.element('#btn-adv-search2').removeClass('adv-active-search');
                        }
                        $scope.cancel();
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

        $scope.goToContractDetails = function(row){
            //console.log('row info',row);
            if(row.is_workflow=='1' && row.type =='project'){
                $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
            }
            if(row.is_workflow=='1' && row.type =='contract'){
                $state.go('app.contract.view',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
            }
            if(row.is_workflow=='0' &&row.type =='contract'){
                $state.go('app.contract.view',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
            }
            if(row.type=='project' && row.is_initiated=='1' && row.is_subtask=='1'){
                $state.go('app.projects.project-task',{ name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
            }
            if(row.type=='project' && row.is_initiated=='0' && row.is_subtask=='1'){
                $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
            }
         }
        $scope.showDiv = false;
        $scope.myFunction = function(){
           $scope.showDiv =  !$scope.showDiv;
           var parent = document.getElementById("abcd");
           var parent1 = document.getElementById("arrow-icon");
           var parent2 = document.getElementById("abcd1");
           if($scope.showDiv){
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                parent2.classList.add('showTab');
                $scope.widgetactionsGraph();
               }else{
                parent.classList.remove('showDivMenu');
                parent2.classList.remove('showTab');
                parent1.className = "fa fa-angle-double-down";
           }           
        }
        $scope.myProvidersFunction = function(){
            $scope.showProviderDiv =  !$scope.showProviderDiv;
            var parent = document.getElementById("provider");
            var parent1 = document.getElementById("arrow-icon-provider");
            var parent2 = document.getElementById("provider1");
            if($scope.showProviderDiv){
                 parent.classList.add('showDivMenu');
                 parent2.classList.add('showTab');
                 parent1.className = "fa fa-angle-double-up";
                 $scope.widgetRelationsGraph();
            }else{
                 parent.classList.remove('showDivMenu');
                 parent2.classList.remove('showTab');
                 parent1.className = "fa fa-angle-double-down";
            }           
         }
        $scope.goToOverDueItems = function(){
            $state.go('app.actionItems', { due: encode('true') });
        }
        $scope.goToOpenItem = function(){
            $state.go('app.actionItems', { due: encode('false') });
        }
        $scope.getContractsthismonth = function(){
            $state.go('app.contract.all-contracts', { this_month: true });
        }

        $scope.getallProjects = function(){
            $state.go('app.projects.all-projects');
        }

        $scope.getProjectsthismonth = function(){
            $state.go('app.projects.all-projects', { this_month: true });
        }
        $scope.getContractsEndingThisMonth = function(){
            $state.go('app.contract.all-contracts', { end_month: true });
        }

        $scope.getProjectsEndingThisMonth = function(){
            $state.go('app.projects.all-projects', { end_month: true });
        }

        $scope.getautomaticProlongation = function(){
            $state.go('app.contract.all-contracts', { automatic_prolongation: true });
        }

        $scope.getEndDateProjects180 = function(){
            $state.go('app.projects.all-projects',{end_date_180 : true});
        }
        $scope.goToUsers = function(){
            $state.go ('app.customer-user.list');
        }
        $scope.goToContributors = function(){
            $state.go('app.contributors.list');
        }

       
        if($scope.user1.access!='eu' && $scope.user1.access!='wa'){
        contractService.getContractStatus().then(function(result){
            if(result.status){
                var obj = {key:'all',value:'All'};
                result.data = result.data.reverse();
                result.data.push(obj);
                $scope.statusList = result.data.reverse();
            }
        })
    }
        $scope.def = {
            "chart": {
              "caption" : "",
              "captionFontSize":"12",
              "captionFontBold":"0",
              "showvalues": "1",
              "yaxisname": "",
              "numdivlines": "0",
              "canvasborderalpha": "0",
              "canvasbgalpha": "1",
              "numvdivlines": "5",
              "plotgradientcolor": "",
              "anchorradius": "2",
              "anchorbordercolor": "",
              "creditLabel": "false",
              "key":"yiF3aI-8rA4B8E2F6B4B3E3D3D3C11A5C7qhhD4F1H3hD7E6F4A-9A-8kD2I3B6uwfB2C1C1uomB1E6B1C3F3C2A21A14B14A8D8bddH4C2WA9hlcE3E1A2raC5JD4E2F-11C-9hH1B3C2B4A4D4C3E4E2F2H3C3C1A5v==",
              "anchorbgcolor": "",
              "anchorbgalpha": "50",
              "anchorborderthickness": "0",
              "drawanchors": "0",
              "plotfillangle": "90",
              "plotfillalpha": "63",
              "vdivlinealpha": "1",
              "vdivlinecolor": "ffffff",
              "bgcolor": "ffffff,ffffff",
              "showplotborder": "0",
              "numbersuffix": "",
              "bordercolor": "",
              "borderalpha": "0",
              "canvasbgratio": "0",
              "basefontcolor": "",
              "basefontsize": "12",
              "outcnvbasefontcolor": "",
              "outcnvbasefontsize": "11",
              "showyaxisvalues": "0",
              "valueBgColor": "efefef",
              "valueBgAlpha": "50"
              //   "valueBorderColor": "efefef",
            },
            "data": []
        };

        $scope.showCoworkers = false;
        $scope.coworkersFunction = function(){
            $scope.showCoworkers = !$scope.showCoworkers;
            var parent = document.getElementById("allCoworkers");
            var parent1 = document.getElementById("arrow-icon-coworkers");
            var parent2 = document.getElementById("allCoworkers1");
            if($scope.showCoworkers){
                parent.classList.add('showDivMenu');
                parent2.classList.add('showTab');
                parent1.className ="fa fa-angle-double-up";
                $scope.widgetcoworkersGraph();
            }
            else{
                parent.classList.remove('showDivMenu');
                parent2.classList.remove('showTab');
                parent1.className ="fa fa-angle-double-down";
            }
        }


        $scope.showPie12 = false;
        $scope.dashboardFunction12 = function () {
            $scope.showPie12 = !$scope.showPie12;
            var parent = document.getElementById("alltotal");
            var parent1 = document.getElementById("arrow-icon-total");
            if ($scope.showPie12) {
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                // $scope.widgetinfo();
            } else {
                parent.classList.remove('showDivMenu');
                parent1.className = "fa fa-angle-double-down";
            }
        }


        $scope.showContributions = false;
        $scope.contributionsFunction = function () {
            $scope.showContributions = !$scope.showContributions;
            var parent = document.getElementById("allContributions");
            var parent1 = document.getElementById("arrow-icon-contributions");
            if ($scope.showContributions) {
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                // $scope.widgetinfo();
            } else {
                parent.classList.remove('showDivMenu');
                parent1.className = "fa fa-angle-double-down";
            }
        }


        $scope.showActionItems = false;
        $scope.actionItemsFunction = function () {
            $scope.showActionItems = !$scope.showActionItems;
            var parent = document.getElementById("allActionItems");
            var parent1 = document.getElementById("arrow-icon-action-items");
            if ($scope.showActionItems) {
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                // $scope.widgetinfo();
            } else {
                parent.classList.remove('showDivMenu');
                parent1.className = "fa fa-angle-double-down";
            }
        }



         $scope.showPie = false;
        $scope.dashboardFunction = function(){
            $scope.showPie =  !$scope.showPie;
            var parent = document.getElementById("allActivities");
            var parent1 = document.getElementById("arrow-icon-activities");
            var parent2 = document.getElementById("allActivities1");
            if($scope.showPie){
                parent.classList.add('showDivMenu');
                parent2.classList.add('showTab');
                parent1.className = "fa fa-angle-double-up";
                $scope.widgetacivityGraph();
                // $scope.widgetinfo();
            }else{
                parent.classList.remove('showDivMenu');
                parent2.classList.remove('showTab');
                parent1.className = "fa fa-angle-double-down";
            }
            FusionCharts.ready(function() {
                var revenueChart = new FusionCharts({
                  type: 'pie2d',
                  renderAt: 'chart-container',
                //   width: '350',
                //   height: '280',
                     width: '100%',
                     height: '280',
                  dataFormat: 'json',
                  dataSource: {
                    "chart": {
                        "caption": "",
                        "showlegend": "0",
                        "showpercentvalues": "0",
                        "bgcolor": "ffffff,ffffff",
                        "theme": "fusion",
                        "showplotborder": "1",
                        "startingAngle":"90",
                        "showLabels":"0",
                        "showValues":"1",
                        "showTooltip":"1",
                        "showBorder":"0",
                        "bgRatio":"50",
                        "showHoverEffect":"1",
                        "plotHoverEffect":"1",
                        "use3DLighting":"0",
                        "showShadow":"0",
                        "plottooltext":"<b>$label : </b>$value"
                    },
                    "data": $scope.activities.data
                  },
                  "events": {
                    "dataPlotClick": function(eventObj, dataObj) {
                        var flag = (eventObj.data.categoryLabel.toLowerCase().indexOf("review") !== -1)?'1':'2';
                        $state.go("app.contract.contract-overview",{ 'status1': eventObj.data.categoryLabel,'activity_filter':flag });
                    }
                  }
                }).render();
            });
        }

        $scope.goToReviewPage = function(row){
            if(row.type=='project' && $rootScope.access !='eu'){
                 $state.go('app.projects.project-task',{ name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
           }
           if(row.type=='contract' && row.is_workflow=='1' && $rootScope.access !='eu'){
               $state.go('app.contract.contract-workflow1',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
           }
           if(row.type=='contract' && row.is_workflow=='0' && $rootScope.access !='eu'){
               $state.go('app.contract.contract-review1',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'})
           }
           if(row.type=='project' && $rootScope.access =='eu'){
            $state.go('app.projects.project-task11',{ name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
          }

          if(row.is_workflow=='0' && $rootScope.access == 'eu'  && row.type=='contract')
                $state.go('app.contract.contract-review11',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),type:'review'});
          if(row.is_workflow=='1' && $rootScope.access =='eu'  && row.type=='contract')
                $state.go('app.contract.contract-workflow11',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.id_contract_review),wId:encode(row.id_contract_workflow),type:'workflow'});
           
        }
        $scope.goToActivities = function(status,type){
            $state.go("app.contract.contract-overview",{ 'status1':status,'activity_filter':type});
        }
        $scope.goToContracts = function(){
            $state.go("app.contract.all-contracts");
        }
        $scope.showEndDate = false;
        $scope.endDateFunction = function(){
            $scope.showEndDate = !$scope.showEndDate;
            var parent = document.getElementById("endDate");
            var parent2 = document.getElementById("endDate1");
            var parent3 = document.getElementById("customTab4");
            var parent1 = document.getElementById("arrow-icon-enddates");
            if($scope.showEndDate){
                parent.classList.add('showDivMenu');
                parent2.classList.add('showTab');
                parent3.classList.remove('leftmoves');
                parent1.className ="fa fa-angle-double-up";
                $scope.widgetcontractsGraph();
            }
            else{
                parent.classList.remove('showDivMenu');
                parent2.classList.remove('showTab');
                parent1.className ="fa fa-angle-double-down";
            }
        }

        $scope.projectendDateFunction = function(){
            $scope.projectshowEndDate = !$scope.projectshowEndDate;
            var parent = document.getElementById("endProjectDate");
            var parent1 = document.getElementById("arrow-icon-endprojectdates");
            var parent2 = document.getElementById("endProjectDate1");
            if($scope.projectshowEndDate){
                parent.classList.add('showDivMenu');
                parent2.classList.add('showTab');
                parent1.className ="fa fa-angle-double-up";
                $scope.widgetprojectsGraph();
            }
            else{
                parent.classList.remove('showDivMenu');
                parent2.classList.remove('showTab');
                parent1.className ="fa fa-angle-double-down";
            }
        }
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
             if(row.is_workflow=='0' && row.type=='contract' && $rootScope.access!='eu'){                        
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
                 if(row.is_workflow=='0' && row.type=='contract' && $rootScope.access=='eu'){                        
                    $state.go('app.contract.contract-module-review11',
                    {name:row.contract_name,id:encode(row.contract_id),rId:encode(row.contract_review_id),mName:row.module_name,
                        moduleId:encode(row.module_id),tName:row.topic_name,tId:encode(row.topic_id),
                        qId:encode(row.question_id),type:'review'},
                        { reload: true, inherit: false });
                }
          
        } 
        $scope.activities = {
            "chart": {
                "caption": "",
                "showlegend": "0",
                "showpercentvalues": "0",
                "bgcolor": "ffffff,ffffff",
                "theme": "fusion",
                "showplotborder": "1",
                "startingAngle":"90",
                "showLabels":"0",
                "showValues":"0",
                "showTooltip":"1",
                "showBorder":"0",
                "bgRatio":"50",
                "showHoverEffect":"1",
                "plotHoverEffect":"1",
                "use3DLighting":"0",
                "showShadow":"0",
                "plottooltext":"<b>$label : </b>$value"
              },
            "data":  []
        };
        $scope.coworkers = {
            "chart": {
                "caption": "",
                "captionFontSize":"12",
                "captionFontBold":"0",
                "showvalues": "1",
                "yaxisname": "",
                "numdivlines": "0",
                "maxBarHeight":"15",
                "canvasborderalpha": "0",
                "canvasbgalpha": "1",
                "numvdivlines": "5",
                "plotgradientcolor": "",
                "anchorradius": "2",
                "anchorbordercolor": "",
                "anchorbgcolor": "",
                "anchorbgalpha": "50",
                "anchorborderthickness": "0",
                "drawanchors": "0",
                "vdivlinealpha": "1",
                "vdivlinecolor": "ffffff",
                "bgcolor": "ffffff,ffffff",
                "showplotborder": "0",
                "numbersuffix": "",
                "bordercolor": "",
                "borderalpha": "0",
                "canvasbgratio": "0",
                "basefontcolor": "",
                "basefontsize": "12",
                "outcnvbasefontcolor": "",
                "outcnvbasefontsize": "12",
                "showyaxisvalues": "0",
                "valueBgColor": "efefef",
                "valueBgAlpha": "50"
            },
            "data": []
        };  

        $scope.def2 = {
            "chart": {
              "caption" : "",
              "captionFontSize":"12",
              "captionFontBold":"0",
              "showvalues": "1",
              "yaxisname": "",
              "numdivlines": "0",
              "canvasborderalpha": "0",
              "canvasbgalpha": "1",
              "numvdivlines": "5",
              "plotgradientcolor": "",
              "anchorradius": "2",
              "anchorbordercolor": "",
              "creditLabel": "false",
              "key":"yiF3aI-8rA4B8E2F6B4B3E3D3D3C11A5C7qhhD4F1H3hD7E6F4A-9A-8kD2I3B6uwfB2C1C1uomB1E6B1C3F3C2A21A14B14A8D8bddH4C2WA9hlcE3E1A2raC5JD4E2F-11C-9hH1B3C2B4A4D4C3E4E2F2H3C3C1A5v==",
              "anchorbgcolor": "",
              "anchorbgalpha": "50",
              "anchorborderthickness": "0",
              "drawanchors": "0",
              "plotfillangle": "90",
              "plotfillalpha": "63",
              "vdivlinealpha": "1",
              "vdivlinecolor": "ffffff",
              "bgcolor": "ffffff,ffffff",
              "showplotborder": "0",
              "numbersuffix": "",
              "bordercolor": "",
              "borderalpha": "0",
              "canvasbgratio": "0",
              "basefontcolor": "",
              "basefontsize": "12",
              "outcnvbasefontcolor": "",
              "outcnvbasefontsize": "11",
              "showyaxisvalues": "0",
              "valueBgColor": "efefef",
              "valueBgAlpha": "50"
              //   "valueBorderColor": "efefef",
            },
            "data": []
        };
        $scope.def3 = {
            "chart": {
              "caption" : "",
              "captionFontSize":"12",
              "captionFontBold":"0",
              "showvalues": "1",
              "yaxisname": "",
              "numdivlines": "0",
              "canvasborderalpha": "0",
              "canvasbgalpha": "1",
              "numvdivlines": "5",
              "plotgradientcolor": "",
              "anchorradius": "2",
              "anchorbordercolor": "",
              "creditLabel": "false",
              "key":"yiF3aI-8rA4B8E2F6B4B3E3D3D3C11A5C7qhhD4F1H3hD7E6F4A-9A-8kD2I3B6uwfB2C1C1uomB1E6B1C3F3C2A21A14B14A8D8bddH4C2WA9hlcE3E1A2raC5JD4E2F-11C-9hH1B3C2B4A4D4C3E4E2F2H3C3C1A5v==",
              "anchorbgcolor": "",
              "anchorbgalpha": "50",
              "anchorborderthickness": "0",
              "drawanchors": "0",
              "plotfillangle": "90",
              "plotfillalpha": "63",
              "vdivlinealpha": "1",
              "vdivlinecolor": "ffffff",
              "bgcolor": "ffffff,ffffff",
              "showplotborder": "0",
              "numbersuffix": "",
              "bordercolor": "",
              "borderalpha": "0",
              "canvasbgratio": "0",
              "basefontcolor": "",
              "basefontsize": "12",
              "outcnvbasefontcolor": "",
              "outcnvbasefontsize": "11",
              "showyaxisvalues": "0",
              "valueBgColor": "efefef",
              "valueBgAlpha": "50"
              //   "valueBorderColor": "efefef",
            },
            "data": []
        };

        $scope.def4 = {
            "chart": {
              "caption" : "",
              "captionFontSize":"12",
              "captionFontBold":"0",
              "showvalues": "1",
              "yaxisname": "",
              "numdivlines": "0",
              "canvasborderalpha": "0",
              "canvasbgalpha": "1",
              "numvdivlines": "5",
              "plotgradientcolor": "",
              "anchorradius": "2",
              "anchorbordercolor": "",
              "creditLabel": "false",
              "key":"yiF3aI-8rA4B8E2F6B4B3E3D3D3C11A5C7qhhD4F1H3hD7E6F4A-9A-8kD2I3B6uwfB2C1C1uomB1E6B1C3F3C2A21A14B14A8D8bddH4C2WA9hlcE3E1A2raC5JD4E2F-11C-9hH1B3C2B4A4D4C3E4E2F2H3C3C1A5v==",
              "anchorbgcolor": "",
              "anchorbgalpha": "50",
              "anchorborderthickness": "0",
              "drawanchors": "0",
              "plotfillangle": "90",
              "plotfillalpha": "63",
              "vdivlinealpha": "1",
              "vdivlinecolor": "ffffff",
              "bgcolor": "ffffff,ffffff",
              "showplotborder": "0",
              "numbersuffix": "",
              "bordercolor": "",
              "borderalpha": "0",
              "canvasbgratio": "0",
              "basefontcolor": "",
              "basefontsize": "12",
              "outcnvbasefontcolor": "",
              "outcnvbasefontsize": "11",
              "showyaxisvalues": "0",
              "valueBgColor": "efefef",
              "valueBgAlpha": "50"
              //   "valueBorderColor": "efefef",
            },
            "data": []
        };
    
        if($scope.user1.access!='eu' && $scope.user1.access!='wa'){
        $scope.panels=[];
        $scope.getdashboardTabs = function(){
            var params={};
            params.id_user= $scope.user1.id_user;
             params.user_role_id = $scope.user1.user_role_id;
            dashboardService.dashboardtabs(params).then(function(result){
                $scope.panels = result.data;
                $scope.reCalcScroll();
            })
        }

        $scope.getdashboardTabs();
    }

        // $scope.sortableOptions = {   
            
        //     start: function (e, ui) {
        //         // var r=confirm("Do you want to continue?");
        //         // if(r==true){
        //             var parentdl = document.getElementById("abcd");
        //             parentdl.classList.remove('showDivMenu');
        //             var parentdl1 = document.getElementById("abcd1");
        //             parentdl1.classList.remove('showTab');
        //             var parentd2 = document.getElementById("arrow-icon");
        //             parentd2.className="fa fa-angle-double-down";

        //             var parentd3 = document.getElementById("allCoworkers");
        //             parentd3.classList.remove('showDivMenu');
        //             var parentd31 = document.getElementById("allCoworkers1");
        //             parentd31.classList.remove('showTab');
        //             var parentd4 = document.getElementById("arrow-icon-coworkers");
        //             parentd4.className="fa fa-angle-double-down";

        //             var parentd5 = document.getElementById("provider");
        //             parentd5.classList.remove('showDivMenu');
        //             var parentd51 = document.getElementById("provider1");
        //             parentd51.classList.remove('showTab');
        //             var parentd5 = document.getElementById("arrow-icon-provider");
        //             parentd5.className="fa fa-angle-double-down";

        //             var parentd6 = document.getElementById("endProjectDate");
        //             parentd6.classList.remove('showDivMenu');
        //             var parentd61 = document.getElementById("endProjectDate1");
        //             parentd61.classList.remove('showTab');
        //             var parentd7 = document.getElementById("arrow-icon-endprojectdates");
        //             parentd7.className="fa fa-angle-double-down";


        //             var parentd8 = document.getElementById("endDate");
        //             parentd8.classList.remove('showDivMenu')
        //             var parentd81 = document.getElementById("endDate1");
        //             parentd81.classList.remove('showTab');
        //             var parentd9 = document.getElementById("arrow-icon-enddates");
        //             parentd9.className="fa fa-angle-double-down";

        //             var parentd10 = document.getElementById("allActivities");
        //             parentd10.classList.remove('showDivMenu');
        //             var parentd101 = document.getElementById("allActivities1");
        //             parentd101.classList.remove('showTab');
        //             var parentd11= document.getElementById("arrow-icon-activities");
        //             parentd11.className="fa fa-angle-double-down";
    
    
        //         //}
        //     },
        //     update: function (e, ui) {
               
        //         var params = {};
        //         params.id_user = $rootScope.id_user;
        //         params.data = $scope.panels;
        //         dashboardService.dashbaordTabsOrder(params).then(function (result) {
        //             if (result.status) {
        //                 $rootScope.toast('Success', result.message);
        //             }
        //         })
        //     },
        //     stop: function (e, ui) {
        //     },
        //     axis: 'x',
        //     cursor: 'move',
        //     forceHelperSize: true, 
        //     forcePlaceholderSize: true,
        // };

       
            $scope.sortableOptions = {
               placeholder: "placeholder",
                start: function (e,ui) {
                  
                    
                },
                update: function (e, ui) {
                    var params={};
                    params.id_user = $rootScope.id_user;
                    params.data = $scope.panels;
                    dashboardService.dashbaordTabsOrder(params).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                        }
                    })
                },
                stop :function(e,ui){

                },
            
               
                axis: 'x',
                cursor: 'move',
                forceHelperSize: true, 
                forcePlaceholderSize: true,
            };
            



       
        $scope.goToProjects = function(){
            $state.go('app.projects.all-projects')
        }
    })
