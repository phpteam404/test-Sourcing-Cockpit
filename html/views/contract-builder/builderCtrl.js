angular.module('app',['localytics.directives'])
.controller('builderOverviewCtrl', function ($scope,$rootScope) {
   
})

.controller('builderListCtrl',function($scope,$rootScope,$state,$uibModal,builderService,userService){
     $scope.dynamicPopover = { templateUrl: 'builderCustomersPopover.html' };

     $scope.getContractBuilderList = function(tableState){
        $rootScope.module = '';
        $rootScope.displayName = '';  
        $scope.builderLoading = true;
        var pagination = tableState.pagination;
        tableState.key='masterTemplateList';
        tableState.method='GET';
        $scope.tableStateRef = tableState;
        tableState.parameters={};
        
        if(tableState.pagination.start==0){ tableState.parameters.page =1; }

        if(tableState.pagination.start.toString().length==2){tableState.parameters.page = Number(String(tableState.pagination.start)[0])+1;}
       
        if(tableState.pagination.start.toString().length==3){tableState.parameters.page = Number(String(tableState.pagination.start)[0])+Number(String(tableState.pagination.start)[1])+1;}
        if(tableState.pagination.start.toString().length==4){
            tableState.parameters.page = Number(String(tableState.pagination.start)[0])+Number(String(tableState.pagination.start)[1])+Number(String(tableState.pagination.start)[2])+ 1;
        }
        if(tableState.pagination.start.toString().length==5){
            tableState.parameters.page = Number(String(tableState.pagination.start)[0])+Number(String(tableState.pagination.start)[1])+Number(String(tableState.pagination.start)[2])+Number(String(tableState.pagination.start)[3]) +1;
        }
        if(tableState.pagination.start.toString().length==6){
            tableState.parameters.page = Number(String(tableState.pagination.start)[0])+Number(String(tableState.pagination.start)[1])+Number(String(tableState.pagination.start)[2])+Number(String(tableState.pagination.start)[3])+Number(String(tableState.pagination.start)[4])+1;
        }
        if(tableState.search!=null && tableState.search.predicateObject!=null &&tableState.search.predicateObject.search_key!=null){
            tableState.parameters={};
           tableState.parameters.q=tableState.search.predicateObject.search_key;
           if(tableState.pagination.start==0){ tableState.parameters.page =1; }

        if(tableState.pagination.start.toString().length==2){tableState.parameters.page = Number(String(tableState.pagination.start)[0])+1;}
       
        if(tableState.pagination.start.toString().length==3){tableState.parameters.page = Number(String(tableState.pagination.start)[0])+Number(String(tableState.pagination.start)[1])+1;}
        if(tableState.pagination.start.toString().length==4){
            tableState.parameters.page = Number(String(tableState.pagination.start)[0])+Number(String(tableState.pagination.start)[1])+Number(String(tableState.pagination.start)[2])+ 1;
        }
        if(tableState.pagination.start.toString().length==5){
            tableState.parameters.page = Number(String(tableState.pagination.start)[0])+Number(String(tableState.pagination.start)[1])+Number(String(tableState.pagination.start)[2])+Number(String(tableState.pagination.start)[3]) +1;
        }
        if(tableState.pagination.start.toString().length==6){
            tableState.parameters.page = Number(String(tableState.pagination.start)[0])+Number(String(tableState.pagination.start)[1])+Number(String(tableState.pagination.start)[2])+Number(String(tableState.pagination.start)[3])+Number(String(tableState.pagination.start)[4])+1;
        }
        }
        builderService.builderList(tableState).then(function (result) {
            console.log(result.data.totalItems);
            $scope.builderData=[];
            $scope.builderData = result.data._embedded.item; 
            $scope.builderLoading = false;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords=result.data.totalItems;
            $scope.builderTable=false;
            tableState.pagination.numberOfPages = Math.ceil(result.data.totalItems / $rootScope.userPagination);
            if(totalRecords < 1)
                $scope.builderTable=true;
       
        });
     }
    
     $scope.defaultBuilderPages = function(val){
        
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.resetPagination=true;
                $scope.tableStateRef.itemsPerPage=val;
                $scope.getContractBuilderList($scope.tableStateRef);
            }                
        });
        
       
    }
    

    $scope.previewTemplate = function(row){
        $scope.structureRowId=row.customers[0].id
       $state.go('app.builder.view',{tname:row.name,lang:row.language,strucutreId:row.id});                            
    }
})

.controller('builderDetailsCtrl',function($scope,$rootScope,$stateParams,$uibModal,builderService){
    
    $rootScope.module = 'Template';
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
    var displayingName=$stateParams.tname+'-'+$stateParams.lang
    console.log("ki",$stateParams.tname+'-'+$stateParams.lang)
    $rootScope.displayName = displayingName;
    $scope.masterStructrureId=$stateParams.strucutreId;

    var params={};
    params.key='masterTemplateList';
    params.method='GET';
    params.pagination=false;
    params.parameters={
        'id':parseInt($scope.masterStructrureId)
    }
    builderService.builderList(params).then(function (result) {
        $scope.builderData = result.data[0];
    })        


})