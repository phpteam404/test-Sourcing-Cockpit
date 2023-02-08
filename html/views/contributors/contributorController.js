angular.module('app')
    .controller('contributorsCtrl', function ($scope, $rootScope, $state,$localStorage, $translate,userService, $stateParams, decode, encode,contributorService) {
        $scope.contributors=[];
        $scope.dynamicPopover = {templateUrl: 'myPopoverTemplate.html'};
        $scope.dynamicPopover1 = {templateUrl: 'myPopoverTemplate1.html'};
        $scope.displayCount = $rootScope.userPagination;
        $scope.resetPagination=false;

        if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }

        $scope.callServer = function(tableState){
            $rootScope.displayName = '';
            $rootScope.module = '';
            $rootScope.breadcrumbcolor='';
            $rootScope.class='';
            $rootScope.icon='';
            $scope.isLoading = true;
            $scope.emptyTable = false;
            $scope.tableStateRef=tableState;
            var pagination = tableState.pagination;
            if($scope.contribution_type !=undefined  && $scope.contribution_type !=null ){
                tableState.contribution_type = $scope.contribution_type;
            }
            else{
                delete tableState.contribution_type;
                $scope.contribution_type = ''; 
            }
            if($scope.resetPagination){
                tableState.pagination={};
                tableState.pagination.start='0';
                tableState.pagination.number='10';
            }
            contributorService.list(tableState).then(function (result){
                $scope.contributors = result.data.data;
                $scope.emptyTable = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                $scope.resetPagination=false;
                if(result.data.total_records < 1)$scope.emptyTable = true;
            });
        }
        $scope.defaultPages = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServer($scope.tableStateRef);
                }                
            });
        }
        if($stateParams.contribution_type){
            $scope.contribution_type = $stateParams.contribution_type;
            setTimeout(function(){
                $scope.callServer($scope.tableStateRef);
            },500);
        }
        $scope.filterByContributorRole = function(val) {
            $scope.resetPagination=true;
            $scope.contribution_type = val;
            if(val){
                $scope.tableStateRef.contribution_type = val;
                $scope.callServer($scope.tableStateRef);  
            }else $scope.callServer($scope.tableStateRef);
        }

        $scope.goToCurrentModulePage = function(info){
            console.log('info',info);
            if(info.type =='project'){
                $state.go('app.projects.view',{name:info.contract_name,id:encode(info.id_contract),type:'workflow'});
            }
            if(info.type =='contract'){
                $state.go('app.contract.view',{name:info.contract_name,id:encode(info.id_contract),type:'review'});
            }
        }

        $scope.goToCurrentReviewPage = function(row){
            console.log('kasi',row);
            if(row.type=='project' && row.initiated ==true){
                 $state.go('app.projects.project-task',{ name:row.contract_name,id:encode(row.id_contract),rId:encode(row.contract_review_id),wId:encode(row.id_contract_workflow),type:'workflow'});
           }
           if(row.type=='contract' && row.is_workflow=='1' && row.initiated==true){
               $state.go('app.contract.contract-workflow',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.contract_review_id),wId:encode(row.id_contract_workflow),type:'workflow'});
           }
           if(row.type=='contract' && row.is_workflow=='0' && row.initiated==true){
               $state.go('app.contract.contract-review',{name:row.contract_name,id:encode(row.id_contract),rId:encode(row.contract_review_id),type:'review'})
           }
    
           if(row.type=='project' && row.initiated ==false){
               $state.go('app.projects.view',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'});
           }
           if(row.type =='contract' && row.initiated ==false && row.is_workflow=='0'){
             $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.id_contract),type:'review'}); 
           }
           if(row.type =='contract' && row.initiated ==false && row.is_workflow=='1'){
            $state.go('app.contract.view1',{name:row.contract_name,id:encode(row.id_contract),wId:encode(row.id_contract_workflow),type:'workflow'}); 
           }
        }
    })   