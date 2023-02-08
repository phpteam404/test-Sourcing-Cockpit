angular.module('app',['localytics.directives','ui.bootstrap'])
.controller('customerBuilderOverviewCtrl', function ($scope,$sce,$state,$stateParams,$rootScope,$uibModal,$filter,catalogueService,builderService) {
     $rootScope.module = 'Build Contract';
     $rootScope.displayName = $stateParams.contractName;
    if($stateParams.contract_build_id){
     var params={};
            params.key='customerContractBuildDetails';
            params.method='GET';
            params.id=parseInt($stateParams.contract_build_id);
        builderService.builderList(params).then(function (result) {
            $scope.versionDetails = result.data;
            $scope.masterStructureId=result.data.masterStructureId;
            if($scope.masterStructureId){
                var params={};
                params.key='masterTemplateList';
                params.method='GET';
                params.pagination=false;
                params.parameters={
                    'id':parseInt($scope.masterStructureId)
                }
                builderService.builderList(params).then(function (result) {
                    $scope.buildermodules = result.data[0];
                })        
            }
                $scope.structureName=result.data.structureName;
                $scope.relationName=result.data.relationName;
                $scope.statusContractBuild=result.data.status;
                if($scope.version){
                    $scope.version=result.data.version;
                }
                $scope.language=result.data.language;
                if($scope.versionDetails.versions){
                    $scope.mostRecentInfo=$scope.versionDetails.versions.slice(-1)[0];
                    $scope.mostRecentVersion=$scope.versionDetails.versions.slice(-1)[0].version;
                    $scope.recentId=$scope.versionDetails.versions.slice(-1)[0].id;
                }                 
                //console.log("a",$scope.recentId)
                if($scope.recentId!=undefined){
                    var params={};
                    params.key='masterTemplateList';
                    params.method='GET';
                    params.pagination=false;
                    params.parameters={
                        'id':parseInt($scope.recentId)
                    }
                    builderService.builderList(params).then(function (result) {
                        $scope.newbuildermodules = result.data[0];   
                    })  
                var params={};
                params.key='structureDiff';
                params.method='GET';
                params.pagination=false;
                params.id=parseInt($scope.masterStructureId),
                params.child_id=parseInt($scope.recentId)
                builderService.builderList(params).then(function (result) {
                    //console.log('result',result);
                    $scope.rightSideData=result.data;
                    var params={};
                        params.key='masterTemplateList';
                        params.method='GET';
                        params.pagination=false;
                        params.parameters={
                            'id':parseInt($scope.masterStructureId)
                        }
                        builderService.builderList(params).then(function (result) {
                            $scope.buildermodules = result.data[0];
                           
                            angular.forEach($scope.rightSideData,function(a,b){
                                //console.log('a',a);
                                angular.forEach(a.children,function(c,d){
                                    //console.log('c',c);
                                    
                                    angular.forEach($scope.buildermodules.content,function(g,h){
                                        //console.log('g',g);
                                        angular.forEach(g.children,function(i,j){
                                            //console.log('i',i);
                                            if(i.id==c.id && c.isMoved){
                                                // console.log('i',i);
                                                i.movedKey =true;
                                            }
                                            angular.forEach(i.children,function(k,l){
                                                // console.log('k',k);
                                                // console.log('c',c);
                                                angular.forEach(c.children,function(e,f){
                                                    //console.log('e',e);
                                                    if(e.id==k.id && e.isMoved){
                                                        k.movedKey=true;
                                                    }
                                                    
                                                })
                                            })
                                        })
                                        
                                    })
                                })
                            })
                            //console.log('buildermodules',$scope.buildermodules)
                        })  
                }) 
            }
                    
        });
}
       


    $scope.closeDocument=function(){
        var r=confirm($filter('translate')('normal.close_contract_builder'));
        if(r==true){
            $state.go('app.customer-builder.builder-list');
        }
    }

    $scope.newbuildermodules ={
        "content": [
        ]
    };
    $scope.moduleChecked=function(module,mainModule){
        //console.log('module',module);
        //console.log('mainModule',mainModule);
        if(module==true){
            angular.forEach(mainModule.children,function(t,o){
                 t.checked=true;
                angular.forEach(t.children,function(c,o){
                    c.checked=true;
                })
            })    
        }
        else{
            angular.forEach(mainModule.children,function(t,o){
                    t.checked=false;
                angular.forEach(t.children,function(c,o){
                    c.checked=false;
                })
            })   
        }
        
    }
    $scope.addSelected = function(builder){
        //console.log('$scope.newbuildermodules.content',$scope.newbuildermodules.content);
        //console.log('builder',builder);
        angular.forEach(builder,function(i,o){
            //console.log('i',i);
            //console.log('o',o);
            if(i.checked){
                //console.log('in if');
               if($scope.newbuildermodules.content.length>0){
                    angular.forEach($scope.newbuildermodules.content,function(a,b){
                        if(a.id==i.id && i.checked){
                            $scope.newbuildermodules.content=[];
                            $scope.newbuildermodules.content.push(i);
                            // console.log('$scope.newbuildermodules.content',$scope.newbuildermodules.content);
                        }
                        else
                        {
                             let e = builder;
                             indexValue = e.findIndex(x => x.id ===i.id);
                             $scope.newbuildermodules.content.splice(indexValue, 0, i);
                        }
                    })  
                }
                else{
                    //console.log('in else');
                    $scope.newbuildermodules.content=[];
                    $scope.newbuildermodules.content.push(i);
                } 
            }
            else{
                angular.forEach(i.children,function(j,o){
                    if(j.type == "Clause")
                    {    
                        if(angular.isDefined(j.checked) && j.checked == true)
                        {
                                    if($scope.newbuildermodules.content.length>0){       
                                      angular.forEach($scope.newbuildermodules.content,function(c,d){
                                        //console.log('c',c);
                                         if(c.id ==i.id){
                                            let a = builder[d].children;
                                            index = a.findIndex(x => x.id ===j.id);
                                            //$scope.newbuildermodules.content[d].children.splice(index, 0, j);
                                         }
                                         else{
                                            search = obj => obj.id == i.id;
                                            firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
                                            if(firstlevelIndex == -1)
                                            {
                                                updatedBuild = angular.copy(i);
                                                updatedBuild.children =[];
                                                $scope.newbuildermodules.content.push(updatedBuild);
                                                search = obj => obj.id == i.id;
                                                firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
                                            }
                                           search = obj => obj.id == j.id;
                                           secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);
                                           if(secondlevelIndex == -1)
                                           {
                                               $scope.newbuildermodules.content[firstlevelIndex].children.push(angular.copy(j));
                                           }
                                         }
                                       })
                                    }
                                    else{
                                        
                                        updatedBuild = angular.copy(i);
                                        updatedBuild.children =[];
                                        $scope.newbuildermodules.content.push(updatedBuild);
                                        $scope.newbuildermodules.content[o].children.push(angular.copy(j));
                                    }

                                   
                            }
                            else if((angular.isDefined(j.checked)) || (j.checked == false))
                            {
                                //this block for removing elements from updated build with unselected elements

                                search = obj => obj.id == i.id;
                                firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);

                                //console.log("firstlevelIndex",firstlevelIndex);

                                if(firstlevelIndex != -1)
                                {
                                    //first level exist then removing second level

                                    //checking for second level exist or not
                                    search = obj => obj.id == j.id;
                                    secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);

                                    if(secondlevelIndex != -1)
                                    {
                                        //console.log('secondlevelIndex',secondlevelIndex);
                                        //removing that element
                                        $scope.newbuildermodules.content[firstlevelIndex].children.splice(secondlevelIndex, 1);

                                        //removing first element also if their are second elements
                                        if($scope.newbuildermodules.content[firstlevelIndex].children.length == 0)
                                        {
                                            //console.log('firstlevelIndex',firstlevelIndex);
                                            $scope.newbuildermodules.content.splice(firstlevelIndex, 1);
                                        }
                                    }
                                }
                            }

                    }
                    else if (j.type == "Subheading")
                    {
                        angular.forEach(j.children,function(k,o){

                            if(angular.isDefined(k.checked) && k.checked == true)
                            {
                                
                                if($scope.newbuildermodules.content.length>0){
                                    angular.forEach($scope.newbuildermodules.content,function(c,d){
                                        if(c.id ==i.id){
                                            let a = builder[d].children;
                                            index = a.findIndex(x => x.id ===j.id);
                                            //$scope.newbuildermodules.content[d].children.splice(index, 0, k);
                                        }
                                        else{
                                            search = obj => obj.id == i.id;
                                            firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
                                            if(firstlevelIndex == -1)
                                            {
                                                updatedBuild = angular.copy(i);
                                                updatedBuild.children =[];
                                                $scope.newbuildermodules.content.push(updatedBuild);
                                                search = obj => obj.id == i.id;
                                                firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
                                            }
                                            search = obj => obj.id == j.id;
                                            secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);
                                            if(secondlevelIndex == -1)
                                            {
                                                $scope.newbuildermodules.content[firstlevelIndex].children.push(angular.copy(j));
                                            }
                                        }
                                        })
                                    }
                                    else{
                                        updatedBuild = angular.copy(i);
                                        updatedBuild.children =[];
                                        $scope.newbuildermodules.content.push(updatedBuild);
                                        $scope.newbuildermodules.content[o].children.push(angular.copy(j));
                                    }
                            }

                            else if((angular.isDefined(k.checked)) || (k.checked == false))
                            {
                                search = obj => obj.id == i.id;
                                firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);

                                if(firstlevelIndex != -1)
                                {
                                    //first level exist then removing second level

                                    //checking for second level exist or not
                                    search = obj => obj.id == j.id;
                                    secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);

                                    if(secondlevelIndex != -1)
                                    {
                                        //console.log('secondlevelIndex',secondlevelIndex);

                                        //checking for third level exist or not
                                        search = obj => obj.id == k.id;
                                        thirdlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children[secondlevelIndex].children.findIndex(search);

                                        if(thirdlevelIndex != -1)
                                        {
                                            //removing that third level element

                                            $scope.newbuildermodules.content[firstlevelIndex].children[secondlevelIndex].children.splice(thirdlevelIndex, 1);

                                            if($scope.newbuildermodules.content[firstlevelIndex].children[secondlevelIndex].children.length == 0)
                                            {
                                                //removing that element
                                                $scope.newbuildermodules.content[firstlevelIndex].children.splice(secondlevelIndex, 1);

                                                //removing first element also if their are second elements
                                                if($scope.newbuildermodules.content[firstlevelIndex].children.length == 0)
                                                {
                                                    //console.log('firstlevelIndex',firstlevelIndex);
                                                    $scope.newbuildermodules.content.splice(firstlevelIndex, 1);
                                                }
                                            }
                                        }
                                    }
                                }

                            }
                        
                        });

                    }
                    
                })
            }
        })
        //console.log('$scope.newbuildermodules', $scope.newbuildermodules);
    }

    $scope.addUnSelected=function(builder){
        //console.log('builder',builder);
        angular.forEach(builder,function(i,o){
                angular.forEach(i.children,function(j,o){
                    if(j.type == "Clause")
                    {
                        if(!angular.isDefined(j.checked) || j.checked == false)
                        {
                            //checking first level exist or not
                            search = obj => obj.id == i.id;
                            firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
        
                            if(firstlevelIndex == -1)
                            {
                                //firstlevel does not exist
                                updatedBuild = angular.copy(i);
                                updatedBuild.children =[];
                                $scope.newbuildermodules.content.push(updatedBuild);
                                search = obj => obj.id == i.id;
                                firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
                            }
        
                            //checking second level exist are not
                            //console.log("firstlevelIndex",firstlevelIndex);
                            search = obj => obj.id == j.id;
                            secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);
                            if(secondlevelIndex == -1)
                            {
                                $scope.newbuildermodules.content[firstlevelIndex].children.push(angular.copy(j));
                            }
                        }
                        else if((angular.isDefined(j.checked)) || (j.checked == true))
                        {
                            //this block for removing elements from updated build with unselected elements
        
                            search = obj => obj.id == i.id;
                            firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
        
                            //console.log("firstlevelIndex",firstlevelIndex);
        
                            if(firstlevelIndex != -1)
                            {
                                //first level exist then removing second level
        
                                //checking for second level exist or not
                                search = obj => obj.id == j.id;
                                secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);
        
                                if(secondlevelIndex != -1)
                                {
                                    //console.log('secondlevelIndex',secondlevelIndex);
                                    //removing that element
                                    $scope.newbuildermodules.content[firstlevelIndex].children.splice(secondlevelIndex, 1);
        
                                    //removing first element also if their are second elements
                                    if($scope.newbuildermodules.content[firstlevelIndex].children.length == 0)
                                    {
                                        //console.log('firstlevelIndex',firstlevelIndex);
                                        $scope.newbuildermodules.content.splice(firstlevelIndex, 1);
                                    }
                                }
                            }
                        }
        
                    }
                    else if (j.type == "Subheading")
                    {
                        angular.forEach(j.children,function(k,o){
        
                            if(!angular.isDefined(k.checked) || k.checked == false)
                            {
                                //checking first level exist or not
                                search = obj => obj.id == i.id;
                                firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
        
                                if(firstlevelIndex == -1)
                                {
                                    //firstlevel does not exist
                                    updatedBuild = angular.copy(i);
                                    updatedBuild.children =[];
                                    $scope.newbuildermodules.content.push(updatedBuild);
                                    search = obj => obj.id == i.id;
                                    firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
                                }
        
                                //checking second level exist or not
        
                                search = obj => obj.id == j.id;
                                secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);
                                if(secondlevelIndex == -1)
                                {
                                    secondlevelData = angular.copy(j);
                                    secondlevelData.children = [];
                                    $scope.newbuildermodules.content[firstlevelIndex].children.push(secondlevelData);
                                    search = obj => obj.id == j.id;
                                    secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);
        
                                }
        
                                //checking third level exist or not
                                search = obj => obj.id == k.id;
                                thirdlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children[secondlevelIndex].children.findIndex(search);
                                if(thirdlevelIndex == -1)
                                {
                                    thirdlevelIndex = angular.copy(k);
                                    thirdlevelIndex.children = [];
                                    $scope.newbuildermodules.content[firstlevelIndex].children[secondlevelIndex].children.push(thirdlevelIndex);
                                    // search = obj => obj.id == j.id;
                                    // secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);
        
                                }
                            }
        
                            else if((angular.isDefined(k.checked)) || (k.checked == true))
                            {
                                search = obj => obj.id == i.id;
                                firstlevelIndex = $scope.newbuildermodules.content.findIndex(search);
        
                                if(firstlevelIndex != -1)
                                {
                                    //first level exist then removing second level
        
                                    //checking for second level exist or not
                                    search = obj => obj.id == j.id;
                                    secondlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children.findIndex(search);
        
                                    if(secondlevelIndex != -1)
                                    {
                                        //console.log('secondlevelIndex',secondlevelIndex);
        
                                        //checking for third level exist or not
                                        search = obj => obj.id == k.id;
                                        thirdlevelIndex = $scope.newbuildermodules.content[firstlevelIndex].children[secondlevelIndex].children.findIndex(search);
        
                                        if(thirdlevelIndex != -1)
                                        {
                                            //removing that third level element
        
                                            $scope.newbuildermodules.content[firstlevelIndex].children[secondlevelIndex].children.splice(thirdlevelIndex, 1);
        
                                            if($scope.newbuildermodules.content[firstlevelIndex].children[secondlevelIndex].children.length == 0)
                                            {
                                                //removing that element
                                                $scope.newbuildermodules.content[firstlevelIndex].children.splice(secondlevelIndex, 1);
        
                                                //removing first element also if their are second elements
                                                if($scope.newbuildermodules.content[firstlevelIndex].children.length == 0)
                                                {
                                                    //console.log('firstlevelIndex',firstlevelIndex);
                                                    $scope.newbuildermodules.content.splice(firstlevelIndex, 1);
                                                }
                                            }
                                        }
                                    }
                                }
        
                            }
                        
                        });
        
                    }
                })
           
        });
        //console.log('$scope.newbuildermodules', $scope.newbuildermodules);
    }

    $scope.finalizeStructure=function(info){
       //console.log("recent",$scope.recentId);
        var params={};
        params.key='createStructure';
        params.method='POST';

        if(info=='true'){
            $scope.status = 'finalized';
        }else{
            $scope.status = 'in_progress';
        }

        angular.forEach($scope.newbuildermodules.content,function(i,x){
            console.log('$scope.newbuildermodules.content',$scope.newbuildermodules.content)
            if($scope.recentId){
                // console.log('1');
                delete i.id;
                delete i.movedKey;
            }
            delete i.checked;
            angular.forEach(i.children,function(j,y){
                //console.log("a",j);
                if($scope.recentId){
                    // console.log('2');
                    delete j.id
                }
                delete j.checked;
                delete j.open;
                delete j.movedKey;
                angular.forEach(j.children,function(k,z){
                    if($scope.recentId){
                        // console.log('3');
                        delete k.id
                    }
                    delete k.checked;
                    delete k.movedKey;
                });
            });

        });

        params.parameters={
            'parent':parseInt($scope.masterStructureId),
            'userId':$scope.user1.customer_id,
            'status':$scope.status,
            'contractId':parseInt($stateParams.contract_build_id),
            'content':$scope.newbuildermodules.content,
            'userName':$scope.user1.first_name + ' ' + $scope.user1.last_name
        }

            builderService.builderList(params).then(function (result) {
            $scope.saveStructure = result.data;

                var params={};
                params.key='masterTemplateList';
                params.method='GET';
                params.pagination=false;
                params.parameters={
                    'id':parseInt($scope.saveStructure.id)
                }
                builderService.builderList(params).then(function (result) {
                    $scope.newbuildermodules = result.data[0];
                    //console.log("con",$scope.newbuildermodules.content);
                 });

                 var params={};
                 params.key='structureDiff';
                 params.method='GET';
                 params.pagination=false;
                 params.id=parseInt($scope.masterStructureId),
                 params.child_id=parseInt($scope.saveStructure.id)
                 builderService.builderList(params).then(function (result) {
                    //console.log('result',result);
                    $scope.rightSideData=result.data;

                    var params1={};
                        params1.key='masterTemplateList';
                        params1.method='GET';
                        params1.pagination=false;
                        params1.parameters={
                            'id':parseInt($scope.masterStructureId)
                        }
                        builderService.builderList(params1).then(function (result) {
                            $scope.buildermodules = result.data[0];
                            angular.forEach($scope.rightSideData,function(a,b){
                                //console.log('a',a);
                                angular.forEach(a.children,function(c,d){
                                    //console.log('c',c);
                                    
                                    angular.forEach($scope.buildermodules.content,function(g,h){
                                        //console.log('g',g);
                                        angular.forEach(g.children,function(i,j){
                                            //console.log('i',i);
                                            if(i.id==c.id && c.isMoved){
                                                // console.log('i',i);
                                                i.movedKey =true;
                                            }
                                            angular.forEach(i.children,function(k,l){
                                                // console.log('k',k);
                                                // console.log('c',c);
                                                angular.forEach(c.children,function(e,f){
                                                    //console.log('e',e);
                                                    if(e.id==k.id && e.isMoved){
                                                        k.movedKey=true;
                                                    }
                                                    
                                                })
                                            })
                                        })
                                        
                                    })
                                })
                            })
                        })  
                })
                })
    }

    $scope.openEditor = function(data,firstIndex,SecondIndex){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/editor.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';
                $scope.title='normal.add_contract_build';
                $scope.language=false;
                $scope.question=data;

                $scope.saveEditorContent=function(editorData){

                    // console.log("op",firstIndex);
                    // console.log("op",SecondIndex);
                    // console.log("op",editorData);
                 

                    if(SecondIndex == 'undefined')
                    {
                        $scope.newbuildermodules.content[firstIndex].content = editorData;
                    }
                    else if(SecondIndex != 'undefined' && $scope.newbuildermodules.content[firstIndex].children[SecondIndex].type=='Subheading'){
                        //console.log("asd",$scope.newbuildermodules.content[firstIndex].children[SecondIndex]);
                        $scope.newbuildermodules.content[firstIndex].children[SecondIndex].children[0].content = editorData;
                    }
                    else if( SecondIndex != 'undefined' && $scope.newbuildermodules.content[firstIndex].children[SecondIndex].type=='Clause'){
                        $scope.newbuildermodules.content[firstIndex].children[SecondIndex].content = editorData;
                    }

                    $uibModalInstance.close();                      
                }

                $scope.closeEditor=function(){

                    $uibModalInstance.close();
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

    $scope.removeElement =function(firstIndex,SecondIndex){

        var r=confirm($filter('translate')('normal.remove_contract_clause'));

        if(r==true){
          //console.log("lk",firstIndex);
          //console.log("lk2",SecondIndex);


        if(SecondIndex!=undefined){
            //console.log("asdf");
            $scope.newbuildermodules.content[firstIndex].children.splice(SecondIndex, 1);
        }else{
            //console.log("kio234")
            $scope.newbuildermodules.content.splice(firstIndex, 1);
        }
    }
    }

    $scope.questRemoveElement=function(firstIndex,SecondIndex){
        var r=confirm($filter('translate')('normal.remove_contract_clause'));

        if(r==true){
            $scope.newbuildermodules.content[firstIndex].children[SecondIndex].children.splice(SecondIndex, 1);
        }

    }


    $scope.addModuleContent = function(){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/templateAddingData.html',
            controller: function ($uibModalInstance,$scope,item) {

                $scope.name="Add Heading";
                $scope.enter_name="Heading Name";
                $scope.bottom = 'general.save';

                $scope.saveModuleContent=function(templateData){
                    //console.log("asdf",templateData);

                    var Heading={
                        'name':templateData,
                        // 'id':'',
                        'type':'Heading',
                        'children':[]
                    }
                    $scope.newbuildermodules.content.push(Heading);

                    //console.log("module",$scope.newbuildermodules)

                    $uibModalInstance.close();                      
                }

                $scope.closeEditor=function(){

                    $uibModalInstance.close();
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

    $scope.addTopicContent = function(firstIndex){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/templateAddingData.html',
            controller: function ($uibModalInstance,$scope,item) {

                $scope.name="Add Subheading";
                $scope.enter_name="Subheading Name";
                $scope.bottom = 'general.save';

                    $scope.saveModuleContent=function(data){
                        var subHeading={
                            'name':data,
                            // 'id':'',
                            'type':'Subheading',
                            'children':[]
                        }
                        $scope.newbuildermodules.content[firstIndex].children.push(subHeading);

                        $uibModalInstance.close();
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

    $scope.addQuestionContent = function(data,firstIndex,secondindex){
        // console.log(firstIndex);
        // console.log(secondindex);
        // console.log( $scope.newbuildermodules.content);
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/templateAddingData.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.name="Add Clause";
                $scope.enter_name="Clause";
                $scope.language=false;
                $scope.questionData=data;
                $scope.bottom = 'general.save';

                $scope.saveModuleContent=function(data){
                    //console.log("l",data);
                    var Clause={
                        'name':'',
                        // 'id':'',
                        'type':'Clause',
                        'children':[],
                        'content' : data
                    };
                   // console.log($scope.newbuildermodules.content[firstIndex].children[secondindex]);
                    $scope.newbuildermodules.content[firstIndex].children[secondindex].children.push(Clause);

                    //console.log("topic",$scope.newbuildermodules)                        
                    $uibModalInstance.close();

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

   
    $scope.editModuleContent = function(data,firstIndex){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/templateAddingData.html',
            controller: function ($uibModalInstance,$scope,item) {

                $scope.name="Edit Module";
                $scope.enter_name="Module Name";
                $scope.bottom = 'general.update';

                $scope.question=data;

                if(data){
                    $scope.saveModuleContent=function(data){
                        $scope.newbuildermodules.content[firstIndex].name=data;    
                        $uibModalInstance.close();
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
    $scope.editTopicContent = function(data,firstIndex,secondIndex){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/templateAddingData.html',
            controller: function ($uibModalInstance,$scope,item) {

                $scope.name="Edit Topic";
                $scope.enter_name="Topic Name";
                $scope.bottom = 'general.update';
                $scope.question=data;

                if(data){
                    $scope.saveModuleContent=function(data){
                        $scope.newbuildermodules.content[firstIndex].children[secondIndex].name=data;    
                        $uibModalInstance.close();
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


    $scope.showPie = false;
    $scope.templateItems = function () {
        $scope.showPie = !$scope.showPie;
        var parent = document.getElementById("alltotal");
        var parent1 = document.getElementById("arrow-icon-total");
        if ($scope.showPie) {
            parent.classList.add('showDivMenu');
            parent1.className = "fa fa-angle-double-up";
            //$scope.widgetinfo();
        } else {
            parent.classList.remove('showDivMenu');
            parent1.className = "fa fa-angle-double-down";
        }
    }

    $scope.versionShow=function(){
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/versions.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='normal.versions';
                $scope.title='normal.versions';
                $scope.versionInfo=true;
                $scope.readOnlyLink=false;
                $scope.structureName=$scope.versionDetails.masterStructureName;
                $scope.contractName=$scope.versionDetails.variables.contract_name; 
                $scope.rowLatestData=function(){
                    var params={};
                    params.key='customerContractBuildDetails';
                    params.method='GET';
                    params.id=parseInt($stateParams.contract_build_id);
                    builderService.builderList(params).then(function (result) {
                        $scope.versionDetails = result.data;
                    $scope.mostRecentInfo=$scope.versionDetails.versions.slice(-1)[0];
                    $scope.mostRecentVersion=$scope.versionDetails.versions.slice(-1)[0].version;
                    $scope.versionDetails.versions.pop();
                    })
                }     
                $scope.rowLatestData(); 

                $scope.getLinkContract=function(versionData){    
                    var modalInstance = $uibModal.open({
                        animation: true,
                        backdrop: 'static',
                        keyboard: false,
                        scope: $scope,
                        openedClass: 'right-panel-modal modal-open',
                        templateUrl: 'views/Manage-Users/customer-contract-builder/linkpdf.html',
                        controller: function ($uibModalInstance,$scope,item) {
                            $scope.bottom ='general.save';
                                $scope.title='normal.link';
                                $scope.versionInfo=versionData;

                                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                                    $scope.contracts = result.data;
                                });
                            
                            $scope.buildLinkSave = function(data){
                              
                                var params={};
                                params.key='linkSCPcontractToBuild';
                                params.method='POST';
                                params.build_status=$scope.versionInfo.status;
                                params.version_number= $scope.versionInfo.version;
                                params.contract_builder_name=$scope.versionDetails.name;

                                params.parameters={
                                    'structureId':$scope.versionInfo.id,
                                    'contractBuildId':$scope.versionDetails.contractBuildId,
                                    'scpContractId':data,
                                    'link':1,
                                }
                                
                                builderService.builderList(params).then(function (result) {
                                    if(result.status){
                                            $scope.readOnlyLink=true;
                                            $scope.rowLatestData(); 
                                        $rootScope.toast('Success', 'Customer Contract Builder Linked Successfully');
                                    }
                                    else{
                                        $scope.readOnlyLink=false;
                                        $rootScope.toast('Error','error');
                                    }
                                })
    
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
        });
    }

})
.controller('customerBuilderListCtrl',function($scope,$filter,$rootScope,$state,$uibModal,Upload,encode,$localStorage,dateFilter,
               tagService,attachmentService,projectService,contractService,templateService,providerService,AuthService,
               documentService,businessUnitService,masterService,builderService,catalogueService,customerService,userService,
               providerService){
   
    $rootScope.module = 'Document Intelligence Template';
    $rootScope.displayName = '';
    $rootScope.breadcrumbcolor='';
    $rootScope.class='';
    $rootScope.icon='';
    $scope.templateName=null;
    $scope.status=null;
    $scope.contractOwnerId=null;
    $scope.relationId=null;
    $scope.localInfo=$localStorage.curUser.data.data;



    $scope.addContract = function (info) {
        //console.log("add",info);
        $scope.contractLinks = [];
        $scope.contractLink = {};
        $scope.fdata = {};
        $scope.isView = false;
        $scope.isLink = false;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            size: 'lg',
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/new-add-contract.html',
            controller: function ($uibModalInstance, $scope, item) {
                $scope.title = 'documents.add_new_document';
                $scope.bottom = 'general.create';
                $scope.bottom1='general.update';
                $scope.titles ='general.create';
                $scope.enableTemplate = true;
                $scope.builderInfo=info;
                $scope.disableTab = true;

                $scope.contractBuilderId=info.contractBuildId;
    
                var params={};
                params.key='contractVariablesdata';
                params.method='GET';
                params.id=$scope.builderInfo.contractBuildId;
                builderService.builderList(params).then(function (result) {
                    $scope.variableInfo = result.data;
                });
                
                console.log("asd",$scope.builderInfo.contractBuildId);
                var params={};
                params.key='customerContractBuildDetails';
                params.method='GET';
                params.id=parseInt($scope.builderInfo.contractBuildId);
                    builderService.builderList(params).then(function (result) {
                    $scope.ObligationInfo=result.data.obligations;
                    $scope.rights=result.data.rights;
                    $scope.versionDetails = result.data;
                    $scope.contract_attachments_total=result.data.versions.length;
                    console.log("total",$scope.contract_attachments_total);
                    if($scope.versionDetails.versions){
                        $scope.mostRecentInfo=$scope.versionDetails.versions.slice(-1)[0];
                        $scope.mostRecentVersion=$scope.versionDetails.versions.slice(-1)[0].version;
                    }                     
                });    

                $scope.downloadPdf = function(information){
                    // console.log('in',information);
                    // console.log("versionDetails",$scope.versionDetails);
                    var params={};
                    params.key='contractBuildPdf';
                    params.method='GET';
                    params.structure_id=information.id;
                    params.id=$scope.versionDetails.contractBuildId;
                    params.contract_builder_name=$scope.versionDetails.name;
                    params.version_number=information.version
                    builderService.builderList(params).then(function (result) {
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

                $scope.getLinkContract=function(versionData){    
                    var modalInstance = $uibModal.open({
                        animation: true,
                        backdrop: 'static',
                        keyboard: false,
                        scope: $scope,
                        openedClass: 'right-panel-modal modal-open',
                        templateUrl: 'views/Manage-Users/customer-contract-builder/linkpdf.html',
                        controller: function ($uibModalInstance,$scope,item) {
                            $scope.bottom ='general.save';
                                $scope.title='normal.link';
                                $scope.versionInfo=versionData;
                                console.log("a",$scope.versionInfo);
            
                                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                                    $scope.contracts = result.data;
                                });
                            
                            $scope.buildLinkSave = function(data){
                              
                                var params={};
                                params.key='linkSCPcontractToBuild';
                                params.method='POST';
                                params.build_status=$scope.versionInfo.status;
                                params.version_number= $scope.versionInfo.version;
                                params.contract_builder_name=$scope.versionDetails.name;
            
                                params.parameters={
                                    'structureId':$scope.versionInfo.id,
                                    'contractBuildId':$scope.versionDetails.contractBuildId,
                                    'scpContractId':data,
                                    'link':1,
                                }
                                
                                builderService.builderList(params).then(function (result) {
                                    if(result.status){
                                            $scope.readOnlyLink=true;
                                            $scope.rowLatestData(); 
                                        $rootScope.toast('Success', 'Customer Contract Builder Linked Successfully');
                                    }
                                    else{
                                        $scope.readOnlyLink=false;
                                        $rootScope.toast('Error','error');
                                    }
                                })
            
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
    
                contractService.generateContractId({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                    if (result.status) {
                        $scope.contract = result.data;
                    }
                });
    
    
                masterService.currencyList({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                    $scope.currencyList = result.data;
                });
    
                var param = {};
                param.user_role_id = $rootScope.user_role_id;
                param.id_user = $rootScope.id_user;
                param.customer_id = $scope.user1.customer_id;
                param.status = 1;
                businessUnitService.list(param).then(function (result) {
                    $scope.bussinessUnit = result.data.data;
                });
    
                templateService.list().then(function (result) {
                    $scope.templateList = result.data.data;
                });
    
                contractService.getRelationshipCategory({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                    $scope.relationshipCategoryList = result.drop_down;
                });
    
                providerService.list({ 'customer_id': $scope.user1.customer_id, 'status': 1, 'all_providers': true }).then(function (result) {
                    $scope.providers = result.data.data;
                });
    
                $scope.getContractDelegates = function (id, contractId) {
                    contractService.getDelegates({ 'id_business_unit': id }).then(function (result) {
                        $scope.delegates = result.data;
                    });
                    var params = {};
                    params.business_unit_id = id;
                    params.contract_id = contractId;
                    params.type = "buowner";
                    contractService.getbuOwnerUsers(params).then(function (result) {
                        $scope.buOwnerUsers = result.data;
                    });
                }
    
                $scope.lock = false;
    
                $scope.updateLockingStatus = function (id) {
                    $scope.contract.is_template_lock = id;
                    if (id) {
                        $scope.lock = true;
                    }
                    else {
                        $scope.lock = false;
                    }
                }
                $scope.resetLockingStatus = function (id) {
                    $scope.contract.is_template_lock = id;
                    if (id) {
                        $scope.lock = false;
                    }
                    else {
                        $scope.lock = true;
                    }
                }    
    

                $scope.pdfShow = function (info,val) {
                    var encryptedPath = info.encrypted_original_document_path;
                    if(val=='ocr'){
                            var is_ocr =1;
                            encryptedPath=info.encrypted_ocr_document_path;
                            var is_document_intelligence =1;
                            var filePath = API_URL + 'Cron/preview?file=' + encryptedPath + '&is_ocr='+ is_ocr+'&is_document_intelligence='+is_document_intelligence;
                    }
                    else{
                            var is_document_intelligence =1;
                            var filePath = API_URL + 'Cron/preview?file=' + encryptedPath +'&is_document_intelligence='+is_document_intelligence ;
                    }
                    encodePath = encode(filePath);
                   
                    window.open(window.origin + '/Document/web/preview.html?file=' + encodePath + '#page=1');
                }
                $scope.validateCategoryTemplate = function (obj) {
                    angular.forEach($scope.relationshipCategoryList, function (o, i) {
                        if (o.id_relationship_category == obj) {
                            if (o.type == 'Without Review') {
                                $scope.enableTemplate = false;
                                $scope.contract.template_id = '';
                            } else {
                                $scope.enableTemplate = true;
                                $scope.contract.template_id = '';
                            }
                            templateService.list().then(function (result) {
                                $scope.templateList = result.data.data;
                            });
                        }
                    })
                }
    
    
               $scope.changeLockingStatus = function(info){
                var params={};
                params.id_document = info.id_document;
                contractService.lockingStatus(params).then(function(result){
                    if(result.status){
                        $rootScope.toast('Success', result.message);
                        $scope.getInfo();
                    }
                });
              }
    
            
    
                $scope.getObligations = function (tableState) {
                    setTimeout(function () {
                        $scope.tableStateRef = tableState;
                        $scope.obligationLoading = true;
                        var pagination = tableState.pagination;
                        tableState.id_contract = $scope.responseContractId;
                        tableState.id_user = $scope.user1.id_user;
                        tableState.user_role_id = $scope.user1.user_role_id;
                        projectService.getObligations(tableState).then(function (result) {
                            $scope.obligationsInfo = result.data;
                            $scope.obligationsInfoCount = result.total_records;
                            $scope.emptyObligationTable = false;
                            $scope.displayCount = $rootScope.userPagination;
                            tableState.pagination.numberOfPages = Math.ceil(result.total_records / $rootScope.userPagination);
                            $scope.obligationLoading = false;
                            if (result.total_records < 1)
                                $scope.emptyObligationTable = true;
                        })
                    }, 700);
                }
    
                $scope.defaultPagesObligations = function (val) {
                    userService.userPageCount({ 'display_rec_count': val }).then(function (result) {
                        if (result.status) {
                            $rootScope.userPagination = val;
                            $scope.getObligations($scope.tableStateRef);
                        }
                    });
                }
    
                $scope.moveAll = function (data) {
                    //console.log('2',data);
                    projectService.moveAllObligation({'id_document_intelligence':$scope.id_document_intelligence}).then(function (result) {
                        if (result.status) {
                            $rootScope.toast('Success', result.message);
                            // $scope.getIntelligenceAnswerList();
                            $scope.obligationsFromAi();
                            $scope.getObligations($scope.tableStateRef);
                            $scope.getInfo();
                        } else {
                            $rootScope.toast('Error', result.error, 'error');
    
                        }
                    });
                }
                $scope.move = function (data,info) {
                    console.log("asd",data);
                    var params = {};
                    params.contract_id = $scope.responseContractId;
                    params.description = data.name;
                    // params.type  = data.field_type;
                    if (info == true) {
                        params.type = 0;
                    }else{
                        params.type = 1;
                    }
                    params.detailed_description = '';
                    console.log("asd",params);
                    projectService.addObligations(params).then(function (result) {
                        if (result.status) {
                            console.log("result is",result);

                            var params={};
                            params.key='customerContractBuildDetails';
                            params.method='GET';
                            params.id=parseInt($scope.builderInfo.contractBuildId);
                                builderService.builderList(params).then(function (result) {
                                $scope.ObligationInfo=result.data.obligations;
                                $scope.rights=result.data.rights;
                            });
                                    
                            // var params={};
                            // params.key='contractVariablesdata';
                            // params.method='GET';
                            // params.id=$scope.builderInfo.contractBuildId;
                            // builderService.builderList(params).then(function (result) {
                            //     $scope.variableInfo = result.data;
                            // });
                            console.log("varaible",$scope.variableInfo);
                            
                            $rootScope.toast('Success', result.message);
                            $scope.getObligations($scope.tableStateRef);
                            $scope.getInfo();
                        } else {
                            $rootScope.toast('Error', result.error, 'error');
    
                        }
                    });
                }
    
                $scope.createObligationRights = function (row) {
                    $scope.obligations = {};
                    $scope.selectedRow = row;
                    var modalInstance = $uibModal.open({
                        animation: true,
                        backdrop: 'static',
                        keyboard: false,
                        scope: $scope,
                        openedClass: 'right-panel-modal modal-open',
                        templateUrl: 'views/Manage-Users/contracts/create-edit-obligations.html',
                        controller: function ($uibModalInstance, $scope, item) {
                            $scope.title = 'general.add';
                            $scope.bottom = 'general.save';
                            //$scope.editField = false;
    
                            projectService.getRecurrences().then(function (result) {
                                $scope.recurrences = result.data;
                            });
    
                            projectService.resendRecurrence().then(function (result) {
                                $scope.resend_recurrences = result.data;
                            });
    
                            if (item) {
                                $scope.title = 'general.edit';
                                projectService.getObligations({ 'contract_id': $scope.responseContractId, 'id_obligation': row.id_obligation }).then(function (result) {
                                    $scope.obligations = result.data[0];
                                    if ($scope.obligations.email_notification == 1) { $scope.requiredFields = true; }
                                    else { $scope.requiredFields = false; }
    
    
                                    if ($scope.obligations.calendar == 1) { $scope.startFields = true; }
                                    else { $scope.startFields = false; }
                                    if ($scope.obligations.recurrence == 'Ad-hoc') {
                                        $scope.anotherField = false;
                                        $scope.defaultField = false;
                                        $scope.startFields = false;
                                        $scope.enddateField = false;
                                        $scope.calendarFields = false;
    
                                    }
                                    if ($scope.obligations.recurrence == 'One-off' && ($scope.obligations.calendar == 1 || $scope.obligations.calendar == 0)) {
                                        $scope.enddateField = false;
                                        $scope.startFields = true;
                                        $scope.calendarFields = false;
                                    }
    
                                    if ($scope.obligations.recurrence == 'Monthly' && $scope.obligations.calendar == 1) {
                                        $scope.startFields = true;
                                        $scope.calendarFields = true;
                                    }
                                    if ($scope.obligations.recurrence == 'Annually' && $scope.obligations.calendar == 1) {
                                        $scope.startFields = true;
                                        $scope.calendarFields = true;
                                    }
                                    if ($scope.obligations.recurrence == 'Semi-annually' && $scope.obligations.calendar == 1) {
                                        $scope.startFields = true;
                                        $scope.calendarFields = true;
                                    }
                                    if ($scope.obligations.recurrence == 'Quarterly' && $scope.obligations.calendar == 1) {
                                        $scope.startFields = true;
                                        $scope.calendarFields = true;
                                    }
    
                                    if ($scope.obligations.resend_recurrence == 'One-off' && $scope.obligations.email_notification == 1) {
                                        $scope.enddateField = false;
                                        $scope.requiredFields = true;
                                        $scope.requiredNotificationField = false;
                                    }
                                    if ($scope.obligations.resend_recurrence == 'One-off' && $scope.obligations.email_notification == 0) {
                                        $scope.enddateField = false;
                                    }
    
                                    if ($scope.obligations.resend_recurrence == 'Monthly' && $scope.obligations.email_notification == 1) {
                                        $scope.enddateField = true;
                                        $scope.requiredFields = true;
                                        $scope.requiredNotificationField = true;
                                    }
                                    if ($scope.obligations.resend_recurrence == 'Annually' && $scope.obligations.email_notification == 1) {
                                        $scope.enddateField = true;
                                        $scope.requiredFields = true;
                                        $scope.requiredNotificationField = true;
                                    }
                                    if ($scope.obligations.resend_recurrence == 'Semi-annually' && $scope.obligations.email_notification == 1) {
                                        $scope.enddateField = true;
                                        $scope.requiredFields = true;
                                        $scope.requiredNotificationField = true;
                                    }
                                    if ($scope.obligations.resend_recurrence == 'Quarterly' && $scope.obligations.email_notification == 1) {
                                        $scope.enddateField = true;
                                        $scope.requiredFields = true;
                                        $scope.requiredNotificationField = true;
                                    }
    
                                    if ($scope.obligations.recurrence_start_date) $scope.obligations.recurrence_start_date = new Date($scope.obligations.recurrence_start_date);
                                    if ($scope.obligations.recurrence_end_date) $scope.obligations.recurrence_end_date = new Date($scope.obligations.recurrence_end_date);
                                    if ($scope.obligations.email_send_start_date) $scope.obligations.email_send_start_date = new Date($scope.obligations.email_send_start_date);
                                    if ($scope.obligations.email_send_last_date) $scope.obligations.email_send_last_date = new Date($scope.obligations.email_send_last_date);
    
    
    
                                    $scope.options = {
                                        minDate: new Date(),
                                        showWeeks: false
                                    };
                                    $scope.options2 = angular.copy($scope.options);
    
    
    
                                    $scope.options3 = {
                                        minDate: new Date(),
                                        showWeeks: false
                                    }
                                    $scope.options4 = angular.copy($scope.options3);
    
    
                                    var dt12 = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : new Date());
                                    //console.log(dt12);
                                    $scope.options2 = {};
                                    $scope.options2 = {
                                        minDate: dt12,
                                        showWeeks: false
                                    };
                                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNORw6QCAdeOFRRsfh9uutYk=') dt12.setMonth(dt12.getMonth() + 1);
                                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEyQIrGjAnMehP1bBlC0TWw=') dt12.setMonth(dt12.getMonth() + 3);
                                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNFoKKPuBpe0GoOwvA+1t9+A=') dt12.setMonth(dt12.getMonth() + 6);
                                    if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=') dt12.setFullYear(dt12.getFullYear() + 1);
    
    
    
                                    var dt23 = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : new Date());
    
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
    
    
                            $scope.addObligationRights = function (data) {
                                params = data;
                                params.contract_id = $scope.responseContractId;
                                if (params.recurrence_start_date != null) {
                                    params.recurrence_start_date = dateFilter(data.recurrence_start_date, 'yyyy-MM-dd');
                                    $scope.requiredFields = false;
                                    $scope.startFields = false;
                                }
                                if (params.recurrence_end_date != null) {
                                    params.recurrence_end_date = dateFilter(data.recurrence_end_date, 'yyyy-MM-dd');
                                    $scope.requiredFields = false;
                                    $scope.calendarFields = false;
                                }
    
                                if (params.email_send_start_date) {
                                    params.email_send_start_date = dateFilter(data.email_send_start_date, 'yyyy-MM-dd');
                                    $scope.requiredFields = false;
                                }
                                if (params.email_send_last_date != null) {
                                    params.email_send_last_date = dateFilter(data.email_send_last_date, 'yyyy-MM-dd');
                                    $scope.requiredFields = false;
                                    $scope.requiredNotificationField = false;
                                }
                                projectService.addObligations(params).then(function (result) {
                                    if (result.status) {
                                        $rootScope.toast('Success', result.message);
                                        $scope.cancel();
                                        $scope.getObligations($scope.tableStateRef);
                                        $scope.getInfo();
                                         // var obj = {};
                                        // obj.action_name = 'Update';
                                        // obj.action_description = 'Update$$Spend$$Lines$$('+data.action_item+')';
                                        // obj.module_type = $state.current.activeLink;
                                        // obj.action_url = $location.$$absUrl;
                                        // $rootScope.confirmNavigationForSubmit(obj);
                                       
                                    } else {
                                        $rootScope.toast('Error', result.error, 'error');
    
                                    }
                                });
                            }
    
                            $scope.getNotification = function (val) {
    
                                if (val) {
                                    $scope.obligations.email_send_last_date = '';
                                }
    
                                if (val == '1' && $scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.requiredFields = true;
                                    $scope.requiredNotificationField = false;
                                }
                                else if (val == '1' && $scope.obligations.resend_recurrence_id != 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.requiredFields = true;
                                    $scope.requiredNotificationField = true;
                                }
                                else {
                                    $scope.requiredFields = false;
                                    $scope.requiredNotificationField = false;
                                }
                            }
                            $scope.cancel = function () {
                                $uibModalInstance.close();
                            };
    
    
                            $scope.getCalenderSelected = function (key) {
                                if (key == 1 && $scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.startFields = true;
                                    $scope.calendarFields = false;
                                    $scope.enddateField = false;
                                }
                                else if (key == 1 && $scope.obligations.recurrence_id != 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.startFields = true;
                                    $scope.calendarFields = true;
                                }
                                else {
                                    $scope.startFields = false;
                                    $scope.calendarFields = false;
                                    $scope.obligations.recurrence_end_date = '';
                                    $scope.obligations.recurrence_start_date = '';
                                }
                            }
                            $scope.anotherField = true;
                            $scope.defaultField = true;
                            $scope.enddateField = true;
                            $scope.calendarFields = false;
                            $scope.startFields = false;
                            $scope.getDate = function (vali) {
                                //console.log(vali);
                                var dt = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : new Date());
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
                                minDate: new Date(),
                                showWeeks: false
                            };
                            $scope.options2 = angular.copy($scope.options);
                            $scope.getRecurrenceSelected = function (val) {
                                if ($scope.obligations.calendar == 1 && $scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.startFields = true;
                                    $scope.calendarFields = false;
                                }
                                else if ($scope.obligations.calendar == 1 && $scope.obligations.recurrence_id != 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.startFields = true;
                                    $scope.calendarFields = true;
                                }
                                else {
                                    $scope.startFields = false;
                                    $scope.calendarFields = false;
                                }
                                if (val) {
                                    $scope.obligations.recurrence_start_date = '';
                                    $scope.obligations.recurrence_end_date = '';
                                }
                                if (val == 'U2FsdGVkX19UaGVAMTIzNP/rB5zlx1rEJtgL6QYTzis=') {
                                    $scope.obligations.calendar = 0;
                                    $scope.defaultField = false;
                                    $scope.anotherField = false;
                                    $scope.enddateField = false;
                                    $scope.startFields = false;
                                    $scope.calendarFields = false;
                                }
                                else {
                                    $scope.defaultField = true;
                                    $scope.anotherField = false;
                                }
                                if (val != 'U2FsdGVkX19UaGVAMTIzNP/rB5zlx1rEJtgL6QYTzis=') {
                                    $scope.defaultField = true;
                                    $scope.anotherField = true;
                                    $scope.enddateField = true;
    
                                }
                                if (val == 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.defaultField = true;
                                    $scope.anotherField = true;
                                    $scope.enddateField = false;
                                }
    
                            }
    
    
                            $scope.getEmaildate = function (item) {
                                //console.log(item);
                            }
                            $scope.options3 = {
                                minDate: new Date(),
                                showWeeks: false
                            };
                            $scope.options4 = angular.copy($scope.options3);
    
                            $scope.emailRecurrence = function (info) {
                                if (info) {
                                    $scope.obligations.email_send_last_date = '';
                                }
                                var dts = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : new Date());
                                $scope.options4 = {};
    
                                if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNORw6QCAdeOFRRsfh9uutYk=' && $scope.obligations.email_send_start_date != null) dts.setMonth(dts.getMonth() + 1);
                                if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEyQIrGjAnMehP1bBlC0TWw=') dts.setMonth(dts.getMonth() + 3);
                                if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNFoKKPuBpe0GoOwvA+1t9+A=') dts.setMonth(dts.getMonth() + 6);
                                if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=') dts.setFullYear(dts.getFullYear() + 1);
                                $scope.options4 = {
                                    minDate: dts,
                                    showWeeks: false
                                };
    
                                if (info == 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.enddateField = false;
                                }
                                else {
                                    $scope.enddateField = true;
                                }
    
                                if ($scope.obligations.email_notification == 1 && $scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.requiredFields = true;
                                    $scope.requiredNotificationField = false;
                                    $scope.enddateField = false;
                                }
                                else if ($scope.obligations.email_notification == 1 && $scope.obligations.resend_recurrence_id != 'U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ=') {
                                    $scope.requiredFields = true;
                                    $scope.requiredNotificationField = true;
                                    $scope.enddateField = true;
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
    
    
                $scope.deleteObligation = function (info) {
                    var r = confirm($filter('translate')('general.alert_continue'));
                    $scope.deleConfirm = r;
                    if (r == true) {
                        var params = {};
                        params.id_obligation = info.id_obligation;
                        params.updated_by = $rootScope.id_user;
                        projectService.deleteObligations(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.getObligations($scope.tableStateRef);
                                $scope.getInfo();
                                var obj = {};
                                obj.action_name = 'delete';
                                obj.action_description = 'delete$$obligationItem$$(' + row.id_obligation + ')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                            } else $rootScope.toast('Error', result.error, 'error', $scope.user);
                        });
                    }
                }
    
    
                if(!$scope.responseContractId){
                    $scope.contract_information='1/15';
                    // $scope.contract_tags='0'
                    $scope.contract_spent_managment='0/6'
                    // $scope.contract_attachments='0'
                    $scope.obligations_count='0'
                }
    
                $scope.getInfo = function(){
                    var par = {};
                    par.id_contract = $scope.responseContractId;
                    par.id_user = $scope.user1.id_user;
                    par.user_role_id = $scope.user1.user_role_id;
                    contractService.getContractById(par).then (function(result){
                        if(result.status){
                            $scope.infoObj = result;
                            // $scope.contract_attachments = result.contract_attachments;
                            $scope.contract_information =result.contract_information;
                            $scope.contract_tags = result.contract_tags;
                            $scope.contract_spent_managment = result.contract_spent_managment;
                            $scope.contractInfo=result.data[0]
                            $scope.contract = result.data[0];
                            // $scope.valueinfo =result.data[0];
                            // $scope.currency_name = $scope.contract.currency_name;
                            if($scope.contract.is_template_lock ==1){
                                $scope.lock = true;
                            }
                            else{
                                $scope.lock=false;
                            }
                            $scope.contract.contract_start_date = new Date($scope.contract.contract_start_date);
                            if($scope.contract.contract_end_date)$scope.contract.contract_end_date = new Date($scope.contract.contract_end_date);
                            $scope.getContractDelegates($scope.contract.business_unit_id,$scope.contract.id_contract);
                            if($scope.contract.can_review==1)
                                $scope.enableTemplate = true;
                            else $scope.enableTemplate = false;
                        }
                    });
                }
    
                
                tagService.groupedTags({'status':1,'tag_type':'contract_tags'}).then(function(result){
                    if (result.status) {
                        $scope.tagsInfo = result.data;
                        $scope.total=0;
                        angular.forEach($scope.tagsInfo,function(i,o){
                            if(!$scope.responseContractId){
                                $scope.total+= i.count;
                                $scope.contract_tags='0/'+$scope.total
                            }
                        })
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
        
            // $scope.getCounts =function(){
            //     contractService.getTabsCount({'id_contract':$scope.responseContractId}).then(function(result){
            //         $scope.contract_attachments = result.data.contract_attachments;
            //         $scope.contract_information =result.data.contract_information;
            //         $scope.contract_tags = result.data.contract_tags;
            //         $scope.contract_spent_managment = result.data.contract_spent_managment;
            //         $scope.contract_stake_holder=result.data.contract_stake_holder;
            //     })
            // }
    
            
                $scope.createContract = function(obj){
                    $scope.formDataObj = angular.copy(obj);
                    var contract = {};
                    contract = $scope.formDataObj;
                    contract.contract_build_id=$scope.contractBuilderId;
                    contract.created_by = $scope.user.id_user;
                    contract.customer_id = $scope.user1.customer_id;
                    contract.id_document_intelligence = $scope.id_document_intelligence;
                    if (contract.contract_end_date != null) {
                        contract.contract_end_date = dateFilter(contract.contract_end_date, 'yyyy-MM-dd');
                    }
                    else {
                        contract.contract_end_date = '';
                    }
                    contract.contract_start_date = dateFilter(contract.contract_start_date, 'yyyy-MM-dd');
                    contract.contract_start_date = dateFilter(contract.contract_start_date, 'yyyy-MM-dd');
                    if ($scope.user.access == 'bo' || $scope.user.access == 'bm')
                        contract.contract_owner_id = $scope.user.id_user;
                    else contract.contract_owner_id = contract.contract_owner_id;
                    $scope.contract['auto_renewal'] = $scope.contract['auto_renewal'] == 1 ? '1' : '0';
    
                    Upload.upload({
                        url: API_URL + 'Contract/add',
                        data: {
                            'contract': contract
                        }
                    }).then(function (resp) {
                        if (resp.data.status) {
                            // var currencyInfo = $scope.currencyList.filter(item => { return item.id_currency == contract.currency_id; });
                            // if (currencyInfo.length > 0) {
                            //     $scope.infoObj.currency_name = currencyInfo[0]['currency_name'];
                            // }
                            $scope.responseContractId = resp.data.contract_id;
                            $scope.getInfo();
                            $scope.disableTab = false;
                            $scope.disableCreate = true;
                            $rootScope.toast('Success', resp.data.message);
                            var obj = {};
                            obj.action_name = 'add';
                            obj.action_description = 'add$$contract$$' + contract.contract_name;
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                        } else {
                            $scope.disableTab = true;
                            $rootScope.toast('Error', resp.data.error, 'error', $scope.contract);
                        }
                    }, function (resp) {
                        $rootScope.toast('Error', resp.error);
                    }, function (evt) {
                        var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                    });
                }
    
                $scope.updateTags = function(data){
                    var params ={};
                      params.id_contract = $scope.responseContractId;
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
                            //   $scope.getCounts();
                              $scope.tagsData();
                              $scope.init();
                          } else {
                              $rootScope.toast('Error', result.error,'error');
                          }
                     });
                 }
    
                 $scope.getValue = function(val,data,contractInfo){
                    $scope.contractInfo=contractInfo;
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
                 }
    
                 $scope.projectValueChange=function(data,info){
                    info.contract_value ='';
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
                        params.id_contract  = $scope.responseContractId;
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
                                $scope.init();
                                // $scope.getCounts();
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


    $scope.search =function(event,searchText){
        if (event.keyCode == 13) {
            var params={};
            params.key='masterTemplateList';
            params.method='GET';
            params.q=searchText;
            params.parameters={
                'customer':'U2FsdGVkX19UaGVAMTIzNEawbve/fTHUEJnev2QpYzo='
            }
            builderService.builderList(params).then(function (result) {
                $scope.builderData = result.data;
            }) 
        }
    }
    var params={};
        params.customer_id = $scope.user1.customer_id;
        params.status  = 1;
        providerService.list(params).then(function(result){
                $scope.providerList = result.data.data;
        });


    var params={};
            params.key='masterTemplateList';
            params.method='GET';
            params.parameters={
                'customer':'U2FsdGVkX19UaGVAMTIzNEawbve/fTHUEJnev2QpYzo='
            }
                builderService.builderList(params).then(function (result) {
                    $scope.builderData = result.data;
            })


    var param ={};
    param.customer_id = $scope.user1.customer_id;
    param.user_role_id = $scope.user1.user_role_id;
    param.id_user = $scope.user1.id_user;
    param.user_type = 'internal';
    param.contractOwner=1;
    customerService.getUserList(param).then(function(result){
        $scope.ownerList=result.data.data;
    })

    $scope.templateChange=function(data){
        $scope.templateName=data;
        $scope.builderList($scope.tableStateRef);
    }

    $scope.contractChange=function(data){
        $scope.contractOwnerId=data;
        $scope.builderList($scope.tableStateRef);
    }

    $scope.relationChange=function(data){
        $scope.relationId=data;
        $scope.builderList($scope.tableStateRef);
    }

    $scope.contractStatus=function(data){
        $scope.status=data;
        $scope.builderList($scope.tableStateRef);
    }
     
    $scope.builderList = function(tableState){
        $rootScope.module = '';
        $rootScope.displayName = '';  
        $scope.builderLoading = true;
        var pagination = tableState.pagination;
        tableState.key='customerContractBuild';
        tableState.method='GET';
        $scope.tableStateRef = tableState;
        //console.log($scope.templateName);
        tableState.parameters={};
        if($scope.templateName && $scope.templateName!=null){
            params.parameters.id=$scope.templateName;
        }
        if($scope.contractOwnerId && $scope.contractOwnerId!=null){
            params.parameters={};
            params.parameters.contractOwnerId=$scope.contractOwnerId;
        }
        if($scope.relationId && $scope.relationId!=null){
               params.parameters={};
               params.parameters.relationId=$scope.relationId;
        }
        if($scope.status && $scope.status!=null){
              params.parameters={};
              params.parameters.status=$scope.status;
        }
        //tableState.parameters={};
        
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
            // tableState.parameters={};
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
        //console.log('t',tableState)
        builderService.builderList(tableState).then(function (result) {
            $scope.customerBuilderData=[];
            $scope.customerBuilderData = result.data._embedded.item; 
            $scope.builderLoading = false;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords=result.data.totalItems;
            $scope.builderTable=false;
            tableState.pagination.numberOfPages = Math.ceil(result.data.totalItems / $rootScope.userPagination);
            if(totalRecords < 1)
                $scope.builderTable=true;
       
        });
     }
    
     $scope.CustomerBuilderDefaultPages = function(val){
        
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.resetPagination=true;
                $scope.tableStateRef.itemsPerPage=val;
                $scope.builderList($scope.tableStateRef);
            }                
        });
        
       
    }
    

    $scope.createContractBuilder = function(row){
        var selectedRow= row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/create-contract-builder.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.save';
                $scope.title='normal.add_contract_build';
                $scope.language=false;

                var params={};
                params.key='masterTemplateList';
                params.method='GET';
                params.parameters={
                    'customer':'U2FsdGVkX19UaGVAMTIzNEawbve/fTHUEJnev2QpYzo='
                }
                builderService.builderList(params).then(function (result) {
                    $scope.builderData = result.data;
                })
                   
                $scope.selectTemplate=function(data){
                    if(data){
                        $scope.language=true;

                        var params={};
                        params.key='getStructureVariable';
                        params.method='GET';
                        params.id=data;
                        builderService.builderList(params).then(function (result) {
                            $scope.variableData = result.data;
                        })
       
                    }
                    var templateId= $scope.builderData.filter(item => { return item.id == data; });
                    $scope.templateData=templateId[0];
                    $scope.templateLang=templateId[0].language;
                }

                var params={};
                params.customer_id = $scope.user1.customer_id;
                builderService.getCustomerBuilderList(params).then(function(result){
                $scope.customersList = result.data;
                // console.log("io",$scope.customersList)
                });

                $scope.selectCustomer=function(info){
                    var customerData= $scope.customersList.filter(item => { return item.display_name == info; });
                    $scope.customerInfo=customerData[0];
                }

                var params={};
                params.customer_id = $scope.user1.customer_id;
                params.status  = 1;
                providerService.list(params).then(function(result){
                $scope.providerList = result.data.data;
                });

                $scope.selectRelations= function(relationData){
                    var relationData= $scope.providerList.filter(item => { return item.provider_name == relationData; });
                    $scope.relationInfo=relationData[0];
                }
               

                    $scope.addContractBuilder=function(totalData,variableData){
                        variable={
                        }
                        angular.forEach(variableData,function(i,o){


                            if(i.type=='date' && i.answer!=null){
                                i.answer = dateFilter(i.answer,'yyyy-MM-dd');
                            }
                            variable[i.tag]=i.answer;
                        })

                        // console.log("vari34",variable);
                        var params={};
                        params.key='createContractBuild';
                        params.method='POST';
                       
                        if($scope.customerInfo.type=='business_unit'){
                            $scope.business_unit_id=$scope.customerInfo.id_business_unit;
                        }else{
                            $scope.business_unit_id=null;
                        }
                       
                        params.parameters={
                            'structure':totalData.structure_id,
                            'name':totalData.name,
                            'relationId':$scope.relationInfo.id_provider,
                            'relationName':totalData.relationName,
                            'contractOwnerId':$scope.localInfo.id_user,
                            'contractOwnerName':$scope.localInfo.first_name +' '+ $scope.localInfo.last_name,
                            'businessUnitId':$scope.business_unit_id,
                            'customerId':$scope.user1.customer_id,
                            'variables':variable
   
                        }
                        builderService.builderList(params).then(function (result) {
                            if(result.status){
                                $uibModalInstance.close();
                                $scope.builderList($scope.tableStateRef);
                               $rootScope.toast('Success', 'Customer Contract Builder Created Successfully');
                            }
                            else{
                                $rootScope.toast('Error','error');
                            }
                        })
                   
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

    $scope.editContractBuilder = function(row){
        var selectedRow= row;
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/edit-contract-builder.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='general.update';
                $scope.title='normal.edit_contract_build';                    

               
                if(row){
                    $scope.builder=row;

                    if($scope.builder.variables.start_date)
                    $scope.builder.variables.start_date = new Date( $scope.builder.variables.start_date);

                    var params={};
                    params.key='masterTemplateList';
                    params.method='GET';
                    params.parameters={
                        'customer':'U2FsdGVkX19UaGVAMTIzNEawbve/fTHUEJnev2QpYzo='
                    }
                    builderService.builderList(params).then(function (result) {
                        $scope.builderData = result.data;
                    })
   
                    if($scope.builder.relationName){
                        var params={};
                        params.customer_id = $scope.user1.customer_id;
                        params.status  = 1;
                        providerService.list(params).then(function(result){
                        $scope.providerList = result.data.data;
                            });        
                        var relationData= $scope.providerList.filter(item => { return item.provider_name == $scope.builder.relationName; });
                        $scope.relationInfo=relationData[0];
                    }
                    if($scope.builder.masterStructureId){
                    var params={};
                    params.key='getStructureVariable';
                    params.method='GET';
                    params.id=$scope.builder.masterStructureId;
                    builderService.builderList(params).then(function (result) {
                        $scope.variableData = result.data;
                    })
                    }
                   
                    if($scope.builder.businessUnitId==''){
                        // console.log("customer1",$scope.builder);
                        var params={};
                        params.customer_id = $scope.user1.customer_id;
                        builderService.getCustomerBuilderList(params).then(function(result){
                        $scope.customersList = result.data;

                        var customerData= $scope.customersList.filter(item =>{ return item.id_customer == $scope.builder.customerId;});
                        $scope.customerInfo=customerData[0];

                        $scope.selectCustomer=function(info){
                            var customerData= $scope.customersList.filter(item => { return item.id_customer == info; });
                            $scope.customerInfo=customerData[0];
                        }
                        });

                    }

                    if($scope.builder.businessUnitId){
                        // console.log("rk",$scope.builder.businessUnitId);
                        var params={};
                        params.customer_id = $scope.user1.customer_id;
                        builderService.getCustomerBuilderList(params).then(function(result){
                        $scope.customersList = result.data;

                        var customerData= $scope.customersList.filter(item => { return item.id_business_unit== $scope.builder.businessUnitId; });
                        $scope.customerInfo=customerData[0];

                        $scope.selectCustomer=function(info){
                        var customerData= $scope.customersList.filter(item => { return item.id_business_unit == info; });
                        $scope.customerInfo=customerData[0];
                    }

                        });
                    }

                   

                        $scope.updateContractBuilder=function(totalData,variableData){

                            var params={};
                            params.key='updateContractBuild';
                            params.method='PATCH';
                            params.id=totalData.contractBuildId;

                            angular.forEach(variableData,function(i,o){
                                if(i.type=='date' && totalData.variables.start_date!=null){
                                    totalData.variables.start_date=dateFilter(totalData.variables.start_date,'yyyy-MM-dd');
                                }

                                variable={
                                    'start_date':totalData.variables.start_date,
                                    'currency':totalData.variables.currency
                                }
                            })

       
                            if($scope.customerInfo.type=='business_unit'){
                                $scope.business_unit_id=$scope.customerInfo.id_business_unit;
                            }else{
                                $scope.business_unit_id=null;
                            }
       
                           
                            params.parameters={
                                'structure':totalData.masterStructureId,
                                'name':totalData.name,
                                'relationId':totalData.relationId,
                                'relationName':totalData.relationName,
                                'contractOwnerId':totalData.contractOwnerId,
                                'contractOwnerName':totalData.contractOwnerName,
                                'businessUnitId':$scope.business_unit_id,
                                'id':totalData.contractBuildId,
                                'customerId':$scope.user1.customer_id,
                                'variables':variable
                            }        
                            builderService.builderList(params).then(function (result) {
                                if(result.status){
                                    $uibModalInstance.close();
                                    $scope.builderList($scope.tableStateRef);
                                   $rootScope.toast('Success', 'Customer Contract Builder Updated Successfully');
                                }
                                else{
                                    $rootScope.toast('Error','error');
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

    $scope.deleteStructure=function(dataStructure){

        var r=confirm($filter('translate')('general.alert_continue'));
        if(r=true){

        var params={};
        params.key='deleteContractBuild';
        params.method='DELETE';
        params.id=dataStructure.contractBuildId;

        builderService.builderList(params).then(function (result) {

            if(result.status){
                $scope.builderList($scope.tableStateRef);
                $rootScope.toast('Success', 'Contract build deleted Successfully');
            }
            else{
            //    $rootScope.toast('Error',result.error,'error');
           }
   
        });
    }
    }

    $scope.versionShow=function(row){
        var selectedRow= row;
        var modalInstance= $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'right-panel-modal modal-open',
            templateUrl: 'views/Manage-Users/customer-contract-builder/versions.html',
            controller: function ($uibModalInstance,$scope,item) {
                $scope.bottom ='normal.versions';
                $scope.title='normal.versions';
                $scope.versionInfo=false;

                $scope.rowData=function(){
                    var params={};
                    params.key='customerContractBuildDetails';
                    params.method='GET';
                    params.id=row.contractBuildId;
                    builderService.builderList(params).then(function (result) {
                    $scope.versionDetails = result.data;
                    // $scope.versionDetails=row;
                    $scope.mostRecentInfo=$scope.versionDetails.versions.slice(-1)[0];
                    $scope.mostRecentVersion=$scope.versionDetails.versions.slice(-1)[0].version;
                    $scope.versionDetails.versions.pop();
                        });
                    }
                    $scope.rowData();

                // console.log("a",$scope.versionDetails.versions.pop());

                $scope.getLinkContract=function(versionData){
                    // console.log("this one");

                    var modalInstance = $uibModal.open({
                        animation: true,
                        backdrop: 'static',
                        keyboard: false,
                        scope: $scope,
                        openedClass: 'right-panel-modal modal-open',
                        templateUrl: 'views/Manage-Users/customer-contract-builder/linkpdf.html',
                        controller: function ($uibModalInstance,$scope,item) {
                            $scope.bottom ='general.save';
                             $scope.title='normal.link';
                             $scope.versionInfo=versionData;

                             catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                                $scope.contracts = result.data;
                            });

                            $scope.buildLinkSave = function(data){
                                var params={};
                                params.key='linkSCPcontractToBuild';
                                params.method='POST';
                                params.version_number= $scope.versionInfo.version;
                                params.contract_builder_name=$scope.versionDetails.name;
                                params.build_status=$scope.versionInfo.status;

                                params.parameters={
                                    'structureId':$scope.versionInfo.id,
                                    'contractBuildId':$scope.versionDetails.contractBuildId,
                                    'scpContractId':data,
                                    'link':1,
                                }
                               
                                builderService.builderList(params).then(function (result) {
                                    if(result.status){
                                        $scope.readOnlyLink=true;
                                        $scope.rowData();
                                        $scope.cancel();
                                       $rootScope.toast('Success', 'Customer Contract Builder Linked Successfully');
                                    }
                                    else{
                                        $scope.readOnlyLink=false;
                                        $rootScope.toast('Error','error');
                                    }
                                })
   
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

                $scope.getPreview=function(versionData){
                    // console.log("ver",versionData);
                    // console.log("l",$scope.versionDetails);

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
                    // console.log("ver",versionData);
                    // console.log("l",$scope.versionDetails);

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
                    // console.log('in',information);
                    // console.log("versionDetails",$scope.versionDetails);
                    var params={};
                    params.key='contractBuildPdf';
                    params.method='GET';
                    params.structure_id=information.id;
                    params.id=$scope.versionDetails.contractBuildId;
                    params.contract_builder_name=$scope.versionDetails.name;
                    params.version_number=information.version
                    builderService.builderList(params).then(function (result) {
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
        });
    }

   
    $scope.templatePage=function(rowData){
        $state.go('app.customer-builder.template-by-side',{contract_build_id: rowData.contractBuildId})
    }

})