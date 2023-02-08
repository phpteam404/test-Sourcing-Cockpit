angular.module('app')
.controller('withAdminHistoryCtrl', function($scope, $rootScope, $state, $stateParams,userService,historyService, encode, decode){
    $rootScope.module = '';
    $rootScope.displayName = '';
    $scope.displayCount = $rootScope.userPagination;
    $scope.getCustomersHistoryList = function getCustomersHistoryList(tableState) {
        $scope.tableRef = tableState;
        var pagination = tableState.pagination;
        historyService.customersList(tableState).then(function (result){
            if (result.status) {
                $scope.customersList = result.data.data;
                $scope.emptyTable = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if(result.data.total_records < 1)$scope.emptyTable = true;
            } else {
                $rootScope.toast('Error', result.error,'error');
            }
        });
    }
    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getCustomersHistoryList($scope.tableRef);
            }                
        });
    }
    $scope.goToCustomerUsers = function(row){
        $state.go('app.admin-logs.customer-user-list',{cName:row.company_name,cId:encode(row.id_customer)});
    }
})
.controller('withAdminUsersCtrl', function($scope, $rootScope, $state,userService, $stateParams,historyService, encode, decode){
    if($stateParams.cId){
        $rootScope.module = 'Customer';
        $rootScope.displayName = $stateParams.cName;
        $scope.dynamicPopover = {templateUrl: 'myPopoverTemplate.html'};
        $scope.displayCount = $rootScope.userPagination;
        $scope.getCustomerUsers = function getCustomerUsers(tableState){
            $scope.tableSelfRef = tableState;
            var pagination = tableState.pagination;
            tableState.customer_id =  decode($stateParams.cId);
            tableState.id_user =  $scope.user1.id_user;
            tableState.user_role_id = $scope.user1.user_role_id;
            historyService.getCustomerUsers(tableState).then( function(result){
                if (result.status) {
                    $scope.customerUsersList = result.data.data;
                    $scope.emptyTable = false;
                    $scope.displayCount = $rootScope.userPagination;
                    $scope.totalRecords = result.data.total_records;
                    tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                    $scope.isLoading = false;
                    if(result.data.total_records < 1)$scope.emptyTable = true;
                } else {
                    $rootScope.toast('Error', result.error,'error');
                }
            });
        }
        $scope.defaultPages = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.getCustomerUsers($scope.tableSelfRef);
                }                
            });
        }
    }
    $scope.gotoActions = function(row) {
        $state.go('app.admin-logs.customer-user-logs',{cName:$stateParams.cName,cId:$stateParams.cId,uName:row.name,id:encode(row.id_user)});
    }
})
.controller('withAdminUserLogsCtrl', function($scope,$rootScope, $state, $stateParams, encode, decode, historyService, dateFilter, userService){
    $rootScope.module = 'Customer';
    $rootScope.displayName = $stateParams.cName+' - '+$stateParams.uName;
    $scope.userSummary = {};
    $scope.userInfo = {};
    $scope.logs = {};
    $scope.displayCount = $rootScope.userPagination;
    $scope.summary = function(){
        var params = {};
        params.type = 'summary';
        params.id_user = decode($stateParams.id);
        historyService.getSummary(params).then(function(result){
            $scope.userSummary = result.data.history.data[0];
            $scope.userInfo = result.data.user_info;
        });
    }
    $scope.summary();
    $scope.showTable = false;
    $scope.params = {};
    if($stateParams.from){$scope.logs.from_date = new Date(decode($stateParams.from));}
    if($stateParams.to){$scope.logs.to_date = new Date(decode($stateParams.to));}
    $scope.getUserLogHistory = function(logs) {
        $scope.showTable = true;
        $scope.params.to_date = dateFilter(logs.to_date,'yyyy-MM-dd');
        $scope.params.from_date = dateFilter(logs.from_date,'yyyy-MM-dd');
        $scope.getLogHistory($scope.tableLogRef);
    }
    if($scope.logs){
        $scope.params.from_date = dateFilter($scope.logs.from_date,'yyyy-MM-dd');
        $scope.params.to_date = dateFilter($scope.logs.to_date,'yyyy-MM-dd');
    }
    $scope.getLogHistory = function getLogHistory(tableState){
        $scope.tableLogRef= tableState;
        $scope.tableLogRef.from_date = $scope.params.from_date;
        $scope.tableLogRef.to_date = $scope.params.to_date;
        $scope.tableLogRef.type = 'detail';
        $scope.tableLogRef.id_user =  decode($stateParams.id);
        historyService.getSummary(tableState).then(function(result){
            if(result.status){
                $scope.userLogsHistory =  result.data.history.data;
                $scope.emptyTable = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.history.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.history.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if(result.data.history.total_records < 1)$scope.emptyTable = true;
            }
        });
    }
    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getLogHistory($scope.tableLogRef);
            }                
        });
    }
    $scope.goToAccessedLogs = function(row){
        $state.go('app.admin-logs.customer-user-actions',{cName:$stateParams.cName,cId:$stateParams.cId,uName:$stateParams.uName,id:$stateParams.id,
            from:encode($scope.params.from_date),to:encode($scope.params.to_date),token:encode(row.access_token)});
    }
})
.controller('withAdminUserActionsCtrl', function($scope,$rootScope, $state, $stateParams, decode, historyService, userService){
    $rootScope.module = 'Customer';
    $rootScope.displayName = $stateParams.cName+' - '+$stateParams.uName;
    $scope.displayCount = $rootScope.userPagination;
    $scope.getActionsList = function getActionsList(tableState){
        $scope.tableRef1 = tableState;
        var pagination = tableState.pagination;
        tableState.access_token = decode($stateParams.token);
        historyService.getActionsList(tableState).then(function(result){
            if(result.status){
                $scope.actionsList = result.data.data;
                $scope.emptyTable = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if(result.data.total_records < 1)$scope.emptyTable = true;
            }
        });
    }
    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getActionsList($scope.tableRef1);
            }                
        });
    }
})