angular.module('app')
.controller('notificationCtrl', function($scope, $rootScope, $state, $stateParams, encode, decode, notificationService, dateFilter){
    $scope.minDate = new Date();
    $scope.options = {
        maxDate: new Date(),
        showWeeks: false
    };
    $scope.dynamicPopover = {templateUrl: 'myPopoverTemplate.html',templateUrl1: 'myPopoverTemplate1.html'};
    /*$scope.for_date = new Date();*/
    if($stateParams.date){
        $scope.for_date = new Date($stateParams.date);
    }
    else
        $state.go('app.notifiationList');

    $scope.content = {};
    $scope.getData = function(date){
        $state.go('app.notifiation',{'date': dateFilter(date,'yyyy-MM-dd')});
    };
    $scope.getUpdateListOfDate = function(forDate){
        var params = {};
        params.customer_id = $scope.user1.customer_id;
        params.id_user = $scope.user1.id_user;
        params.date = dateFilter(forDate,'yyyy-MM-dd');
        notificationService.getUpdates(params).then(function(result){
            if(result.data.length > 0){
                $rootScope.getNotificationsCount();
                $scope.emptyData = false;
                $scope.updatesDate = result.data[0].date;
                $scope.updatesDateStatus = result.data[0].status;
                $scope.content = JSON.parse(result.data[0].content);
                var contract_updates = [];
                var user_updates = [];
                angular.forEach($scope.content, function(item,key){
                    if(key == 'changes_contract'){
                        angular.forEach(item,function(i,o){
                            i.action_name = 'contract changed';
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'changes_contract_status'){
                        angular.forEach(item,function(i,o){
                            i.action_name = 'contract status changed';
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'review_started'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'review_updated'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'review_finalized'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'contributor_add'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'contributor_remove'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'discussion_started'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'discussion_updated'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'discussion_closed'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'action_item_created'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'action_item_updated'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'action_item_closed'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'report_created'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'report_edited'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'report_deleted'){
                        angular.forEach(item,function(i,o){
                            i.action_name = key;
                            contract_updates.push(i);
                        })
                    }
                    if(key == 'user_create'){
                        angular.forEach(item,function(i,o){
                            i.action_name = 'user_created';
                            user_updates.push(i);
                        })
                    }
                    if(key == 'user_update'){
                        angular.forEach(item,function(i,o){
                            i.action_name = 'user_updated';
                            user_updates.push(i);
                        })
                    }
                    if(key == 'user_delete'){
                        angular.forEach(item,function(i,o){
                            i.action_name = 'user_deleted';
                            user_updates.push(i);
                        })
                    }
                })
                $scope.contract_updates = contract_updates.reverse();
                $scope.user_updates = user_updates.reverse();
                $scope.new_contracts = $scope.content.new_contract;
                angular.forEach($scope.user_updates, function(it,k){
                   if(it.business_unit){it.business_unit = it.business_unit.split(',');}
                });
            }else{
                $scope.emptyData = true;
            }
        });
    }
    var date = dateFilter($scope.for_date,'yyyy-MM-dd');
    date = new Date(date);
    $scope.getUpdateListOfDate(date);
})
.controller('notificationListCtrl', function($scope,userService, $rootScope, $state, $stateParams, encode, decode, notificationService, dateFilter){
    $scope.displayCount = $rootScope.userPagination;
    $scope.getNotificationCounts = function(is_opened){
        console.log('is_opened',is_opened);
        var params = {};
        params.id_user = $scope.user1.id_user;
        /*if(is_opened==0 || is_opened==1)*/
        if(is_opened!='')
            params.is_opened = is_opened;
        notificationService.getCount(params).then(function(result){
            if(is_opened=='')
                $scope.allNotificationCounts = result.data;
            else if(is_opened==0)
                $scope.newNotificationCounts = result.data;
            else if(is_opened==1)
                $scope.readNotificationCounts = result.data;
        });
    };
    $scope.getAllNotification = function(tableState){
        $scope.isLoadingAll = true;
        $scope.tableStateRef=tableState;
        tableState.id_user = $scope.user1.id_user;
        var pagination = tableState.pagination;
        notificationService.list(tableState).then(function(result){
            $scope.notificationAllList = result.data.data;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords2 = result.data.total_records;
            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
            $scope.isLoadingAll = false;
            $scope.getNotificationCounts('');
        });
    };
    $scope.defaultPages2 = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getAllNotification($scope.tableStateRef);
            }                
        });
    }
    $scope.defaultPages1 = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.getNewNotification($scope.tableStateNewRef);
            }                
        });
    }
    $scope.getNewNotification = function(tableState){
        $scope.isLoadingNew = true;
        $scope.tableStateNewRef=tableState;
        tableState.id_user = $scope.user1.id_user;
        tableState.is_opened = 0;
        var pagination = tableState.pagination;
        notificationService.list(tableState).then(function(result){
            $scope.notificationNewList = result.data.data;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords1 = result.data.total_records;
            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
            $scope.isLoadingNew = false;
            $scope.getNotificationCounts('0');
        });
    };
    $scope.getData = function(date){
        $state.go('app.notifiation',{'date': dateFilter(date,'yyyy-MM-dd')});
    };
})