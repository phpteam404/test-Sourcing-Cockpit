angular.module('app', ['ui.sortable'])
    .controller('tagsCtrl', function ($scope, $rootScope, $state,$localStorage,$translate,$filter, $stateParams, decode, $uibModal,businessUnitService, tagService, $location,dateFilter) {
        
        if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }
        
        
        $scope.tagsList = [];
        $rootScope.displayName = '';
        $rootScope.module = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.req={};
        $scope.req.status=0;  
        var params={};
        $scope.getTagsList = function (val) {
            console.log('val--', val);
            params.customer_id=$scope.user1.customer_id;
            params.status=val;
            params.tag_type="contract_tags"
            tagService.tagsList(params).then(function (result) {
                if (result.status) {
                    $scope.tagsList = result.data;
                }
            });
        }
        $scope.getTagsByStatus= function (val){
            $scope.req.status=val;
            $scope.getTagsList(val);
        }
        $scope.getTagsList(0);
       
        $scope.loadModal = function(type,row){
            $scope.tag = {};
            $scope.editTags=false;
            $scope.tag.option_name = [];
            $scope.option_delete = [];
            $scope.option_added = [];
            $scope.categories= {};
            $scope.tag_type = type;
            $scope.title ='general.add';
            $scope.bottom ='general.save';
            $scope.action ='general.add';
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/tags/tags-modal.html',
                resolve : {
                    item : row
                },
                controller: function ($uibModalInstance, $scope , item) {
                    $scope.tagType='Input Text';
                    if($scope.tag_type == 'radio') $scope.tagType='Yes / No';
                    if($scope.tag_type == 'dropdown') $scope.tagType='Dropdown';
                    if($scope.tag_type =='date') $scope.tagType='Date';
                    if($scope.tag_type =='selected') $scope.tagType='Selected Field';

                    if(item){
                        $scope.title ='general.edit';
                        $scope.bottom ='general.update';
                        $scope.action ='general.update';
                        $scope.editTags=true;

                        $scope.multiSelectChange=function(val){
                            // console.log("Ad",val)
                            if(val==0){
                                var r=confirm($filter('translate')('normal.multiselect_popup'));
                            }
                        }
                        tagService.getTagInfo({'id_tag': item.id_tag}).then (function(result){
                            if(result.status){
                                $scope.tag = result.data;
                                console.log("i", $scope.tag)
                                $scope.tag_type = result.data.tag_type;
                                //$scope.choices = result.data.option_name;
                                for(obj in  result.data.option_names ){
                                    // console.log('one',result.data)
                                    var opt = {
                                        "id" : "choice"+obj,
                                        "type":"update",
                                        "id_tag_option":result.data.option_names[obj].id_tag_option,
                                        "tag_option":result.data.option_names[obj].option_name,
                                        "id_tag_option_language": result.data.id_tag_option_language[obj]};
                                    $scope.choices[obj]=opt;
                                }
                                $scope.options = [];
                                $scope.tag.option_name = [];
                                angular.forEach(result.data.option_names,function(i,o){
                                    var obj = {};
                                    obj.tag_option =i.option_name;
                                    obj.id_tag_option =i.id_tag_option;
                                    obj.id_tag_option_language =i.id_tag_option_language;
                                    obj.option =i.option_name;
                                    $scope.tag.option_name[o] =obj;
                                    var optObj ={};
                                    optObj.id_tag_option = result.data.option_names[o].id_tag_option;
                                    optObj.id_tag_option_language = result.data.option_names[o].id_tag_option_language;
                                    optObj.option_name = result.data.option_names[o].option_name;
                                    $scope.options[o] = optObj;
                                });
                            }
                        });
                    }else{$scope.tag.tag_required = 1;}

                    var param ={};
                    param.user_role_id=$rootScope.user_role_id;
                    param.id_user=$rootScope.id_user;
                    param.customer_id = $scope.user1.customer_id;
                    // param.status = 1;
                    businessUnitService.list(param).then(function(result){
                        $scope.bussinessUnit = result.data.data;
                    });


                    if(type == 'input')$scope.tag.option_name[0] = '';
                    tagService.getTagOptions({'tag_type':type}).then(function(result){
                       // if(type == 'dropdown'){$scope.choices = result.data;}
                        if(type == 'radio'){
                            angular.forEach(result.data, function(item,key){
                                var obj = {};
                                obj.tag_option =item.tag_option;
                                obj.id_tag_option =item.id_tag_option;
                                obj.id_tag_option_language =item.id_tag_option_language;
                                $scope.tag.option_name[key] = obj ;
                            })
                        }

                        if(type == 'rag'){
                            angular.forEach(result.data, function(item,key){
                                var obj = {};
                                obj.option =item.tag_option;
                                obj.value =item.tag_value;
                                obj.id_tag_option =item.id_tag_option;
                                obj.id_tag_option_language =item.id_tag_option_language;
                                $scope.tag.option_name[key] = obj ;
                            })
                        }
                    });
                    $scope.addTag =  function (tag){
                        //console.log('tag info',tag);
                        var obj1 = {}; 
                        obj1.action_name = $scope.action;
                        obj1.action_description = $scope.action+'$$tag$$'+tag.tag_text;
                        obj1.module_type = $state.current.activeLink;
                        obj1.action_url= $location.$$absUrl;
                        //delete tag.option_name ;
                        delete tag.option_names;
                        tag.id_topic = $scope.topic_id;
                        tag.tag_type = $scope.tag_type;
                        //tag.tag_date= dateFilter(tag.date,"yyyy-MM-dd");
                        tag.option_delete = $scope.option_delete;
                        // tag.categories = $scope.categories;
                        tag.type='contract_tags';
                        if($scope.tag_type == 'dropdown'){
                            delete tag.option_name;
                            tag.option_name = $scope.choices;
                        }
                        else tag.option_name = $scope.tag.option_name;
                        // console.log('tag',tag);
                        if(tag.id_tag){
                            tag.updated_by = $scope.user.id_user;
                            tagService.updateTag(tag).then(function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $rootScope.toast('Success',result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.req.status=0;
                                    $scope.getTagsList(0);
                                }else{
                                    $rootScope.toast('Error',result.error,'error',$scope.tag);
                                }
                            })
                        }else{
                            tag.created_by = $scope.user.id_user;
                            tagService.postTags(tag).then (function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $rootScope.toast('Success',result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.req.status=0;
                                    $scope.getTagsList(0);
                                }else{
                                    $rootScope.toast('Error',result.error,'error',$scope.tag);
                                }
                            })
                        }
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.choices = [{id: 'choice1','type':'new'}];
                    $scope.addNewChoice = function(tag,index) {
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
                                if(i.id_tag_option == choice.id_tag_option){
                                    var obj = {};
                                    obj.id_tag_option =$scope.options[o].id_tag_option;
                                    obj.id_tag_option_language =$scope.options[o].id_tag_option_language;
                                    $scope.option_delete.push(obj);
                                }
                            })
                        }
                    };
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        }

        $scope.sortableOptions = {           
            start: function (e, ui) {
            },
            update: function (e, ui) {
                var params = {};
                params.data = $scope.tagsList;
                tagService.sortTags(params).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                    }
                })
            },
            stop: function (e, ui) {
            },
            axis: 'y',
            cursor: 'move',
            forceHelperSize: true,
            forcePlaceholderSize: true,
        };
    })
    .controller('providertagsCtrl',function($scope, $rootScope, $state,$filter, $stateParams, decode, $uibModal, tagService,businessUnitService, $location,dateFilter){
        $scope.tagsList = [];
        $scope.t_options =[
            {label: 0, value: '0'},
            {label: 0.1, value: '0.1'},
            {label: 1, value: '1'},
            {label: 'NA', value: 'NA'},
        ];
        $rootScope.displayName = '';
        $rootScope.module = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.req={};
        $scope.req.status=0;  
        var params={};

        $scope.getTagsList = function (val) {
            params.customer_id=$scope.user1.customer_id;
            params.status=val;
            params.tag_type="provider_tags"
            tagService.tagsList(params).then(function (result) {
                if (result.status) {
                    $scope.tagsList = result.data;
                }
            });
        }

        $scope.updatData=function(info){
            //console.log("information saved",info);
            var params={};
            params.id_tag=info.id_tag;
            params.id_tag_language=info.id_tag_language;
            params.tag_text=info.tag_text;
            tagService.updateTags(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    $scope.getTagsList(0);

                }else{
                    $rootScope.toast('Error',result.error,'error');
                }
            })

        }
        $scope.getTagsByStatus= function (val){
            $scope.req.status=val;
            $scope.getTagsList(val);
        }
        $scope.getTagsList(0);
        $rootScope.displayName = '';
        $rootScope.module = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.loadModal = function(type,row){
            $scope.editTags=false;
            $scope.tag = {};
            $scope.tag.option_name = [];
            $scope.option_delete = [];
            $scope.option_added = [];
            $scope.categories= {};
            $scope.tag_type = type;
            $scope.title ='general.add';
            $scope.bottom ='general.save';
            $scope.action ='general.add';
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/tags/tags-modal.html',
                resolve : {
                    item : row
                },
                controller: function ($uibModalInstance, $scope , item) {
                    $scope.tagType='Input Text';
                    if($scope.tag_type == 'radio') $scope.tagType='Yes / No';
                    if($scope.tag_type == 'dropdown') $scope.tagType='Dropdown';
                    if($scope.tag_type =='date') $scope.tagType='Date';
                    if($scope.tag_type =='selected') $scope.tagType='Selected Field';
                    if(item){
                        $scope.title ='general.edit';
                        $scope.bottom ='general.update';
                        $scope.action ='general.update';
                        $scope.editTags=true;
                        $scope.multiSelectChange=function(val){
                            // console.log("Ad",val)
                            if(val==0){
                                var r=confirm($filter('translate')('normal.multiselect_popup'));
                            }
                        }

                        tagService.getTagInfo({'id_tag': item.id_tag}).then (function(result){
                            if(result.status){
                                $scope.tag = result.data;
                                $scope.tag_type = result.data.tag_type;
                                //$scope.choices = result.data.option_name;
                                for(obj in  result.data.option_names ){
                                    // console.log('one',result.data)
                                    var opt = {
                                        "id" : "choice"+obj,
                                        "type":"update",
                                        "id_tag_option":result.data.option_names[obj].id_tag_option,
                                        "tag_option":result.data.option_names[obj].option_name,
                                        "id_tag_option_language": result.data.id_tag_option_language[obj]};
                                    $scope.choices[obj]=opt;
                                }
                                $scope.options = [];
                                $scope.tag.option_name = [];
                                angular.forEach(result.data.option_names,function(i,o){
                                    var obj = {};
                                    obj.tag_option =i.option_name;
                                    obj.id_tag_option =i.id_tag_option;
                                    obj.id_tag_option_language =i.id_tag_option_language;
                                    obj.option =i.option_name;
                                    $scope.tag.option_name[o] =obj;
                                    var optObj ={};
                                    optObj.id_tag_option = result.data.option_names[o].id_tag_option;
                                    optObj.id_tag_option_language = result.data.option_names[o].id_tag_option_language;
                                    optObj.option_name = result.data.option_names[o].option_name;
                                    $scope.options[o] = optObj;
                                });
                            }
                        });
                    }else{$scope.tag.tag_required = 1;}

                    var param ={};
                    param.user_role_id=$rootScope.user_role_id;
                    param.id_user=$rootScope.id_user;
                    param.customer_id = $scope.user1.customer_id;
                    // param.status = 1;
                    businessUnitService.list(param).then(function(result){
                        $scope.bussinessUnit = result.data.data;
                    });



                    if(type == 'input')$scope.tag.option_name[0] = '';
                    tagService.getTagOptions({'tag_type':type}).then(function(result){
                       // if(type == 'dropdown'){$scope.choices = result.data;}
                        if(type == 'radio'){
                            angular.forEach(result.data, function(item,key){
                                var obj = {};
                                obj.tag_option =item.tag_option;
                                obj.id_tag_option =item.id_tag_option;
                                obj.id_tag_option_language =item.id_tag_option_language;
                                $scope.tag.option_name[key] = obj ;
                            })
                        }

                        if(type == 'rag'){
                            angular.forEach(result.data, function(item,key){
                                var obj = {};
                                obj.option =item.tag_option;
                                obj.value =item.tag_value;
                                obj.id_tag_option =item.id_tag_option;
                                obj.id_tag_option_language =item.id_tag_option_language;
                                $scope.tag.option_name[key] = obj ;
                            })
                        }
                    });
                    $scope.addTag =  function (tag){
                       // console.log('tag info',tag);
                        var obj1 = {}; 
                        obj1.action_name = $scope.action;
                        obj1.action_description = $scope.action+'$$tag$$'+tag.tag_text;
                        obj1.module_type = $state.current.activeLink;
                        obj1.action_url= $location.$$absUrl;
                        //delete tag.option_name ;
                        delete tag.option_names;
                        tag.id_topic = $scope.topic_id;
                        tag.tag_type = $scope.tag_type;
                        //tag.tag_date= dateFilter(tag.date,"yyyy-MM-dd");
                        tag.option_delete = $scope.option_delete;
                        tag.categories = $scope.categories;
                        tag.type='provider_tags';
                        if($scope.tag_type == 'dropdown'){
                            delete tag.option_name;
                            tag.option_name = $scope.choices;
                        }
                        else tag.option_name = $scope.tag.option_name;
                        // console.log('tag',tag);
                        if(tag.id_tag){
                            tag.updated_by = $scope.user.id_user;
                            tagService.updateTag(tag).then(function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $rootScope.toast('Success',result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.req.status=0;
                                    $scope.getTagsList(0);
                                }else{
                                    $rootScope.toast('Error',result.error,'error',$scope.tag);
                                }
                            })
                        }else{
                            tag.created_by = $scope.user.id_user;
                            tagService.postTags(tag).then (function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $rootScope.toast('Success',result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.req.status=0;
                                    $scope.getTagsList(0);
                                }else{
                                    $rootScope.toast('Error',result.error,'error',$scope.tag);
                                }
                            })
                        }
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.choices = [{id: 'choice1','type':'new'}];
                    $scope.addNewChoice = function(tag,index) {
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
                                if(i.id_tag_option == choice.id_tag_option){
                                    var obj = {};
                                    obj.id_tag_option =$scope.options[o].id_tag_option;
                                    obj.id_tag_option_language =$scope.options[o].id_tag_option_language;
                                    $scope.option_delete.push(obj);
                                }
                            })
                        }
                    };
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        }

        $scope.sortableOptions = {           
            start: function (e, ui) {
            },
            update: function (e, ui) {
                var params = {};
                params.data = $scope.tagsList;
                tagService.sortTags(params).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                    }
                })
            },
            stop: function (e, ui) {
            },
            axis: 'y',
            cursor: 'move',
            forceHelperSize: true,
            forcePlaceholderSize: true,
        };
    }) 
   
    .controller('cataloguetagsCtrl',function($scope, $rootScope, $state, $filter,$stateParams, decode, $uibModal, tagService,businessUnitService, $location,dateFilter){
        $scope.tagsList = [];
        $scope.t_options =[
            {label: 0, value: '0'},
            {label: 0.1, value: '0.1'},
            {label: 1, value: '1'},
            {label: 'NA', value: 'NA'},
        ];
        $rootScope.displayName = '';
        $rootScope.module = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.req={};
        $scope.req.status=0;  
        var params={};

        $scope.getTagsList = function (val) {
            params.customer_id=$scope.user1.customer_id;
            params.status=val;
            params.tag_type="catalogue_tags"
            tagService.tagsList(params).then(function (result) {
                if (result.status) {
                    $scope.tagsList = result.data;
                }
            });
        }

        $scope.updatData=function(info){
            //console.log("information saved",info);
            var params={};
            params.id_tag=info.id_tag;
            params.id_tag_language=info.id_tag_language;
            params.tag_text=info.tag_text;
            tagService.updateTags(params).then(function(result){
                if(result.status){
                    $rootScope.toast('Success', result.message);
                    $scope.getTagsList(0);

                }else{
                    $rootScope.toast('Error',result.error,'error');
                }
            })

        }
        $scope.getTagsByStatus= function (val){
            $scope.req.status=val;
            $scope.getTagsList(val);
        }
        $scope.getTagsList(0);
        $rootScope.displayName = '';
        $rootScope.module = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.loadModal = function(type,row){
            $scope.editTags=false;
            $scope.tag = {};
            $scope.tag.option_name = [];
            $scope.option_delete = [];
            $scope.option_added = [];
            $scope.categories= {};
            $scope.tag_type = type;
            $scope.title ='general.add';
            $scope.bottom ='general.save';
            $scope.action ='general.add';
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/tags/tags-modal.html',
                resolve : {
                    item : row
                },
                controller: function ($uibModalInstance, $scope , item) {
                    $scope.tagType='Input Text';
                    if($scope.tag_type == 'radio') $scope.tagType='Yes / No';
                    if($scope.tag_type == 'dropdown') $scope.tagType='Dropdown';
                    if($scope.tag_type =='date') $scope.tagType='Date';
                    if($scope.tag_type =='selected') $scope.tagType='Selected Field';
                    if(item){
                        $scope.title ='general.edit';
                        $scope.bottom ='general.update';
                        $scope.action ='general.update';
                        $scope.editTags=true;

                        $scope.multiSelectChange=function(val){
                            // console.log("Ad",val)
                            if(val==0){
                                var r=confirm($filter('translate')('normal.multiselect_popup'));
                            }
                        }

                        tagService.getTagInfo({'id_tag': item.id_tag}).then (function(result){
                            if(result.status){
                                $scope.tag = result.data;
                                $scope.tag_type = result.data.tag_type;
                                //$scope.choices = result.data.option_name;
                                for(obj in  result.data.option_names ){
                                    // console.log('one',result.data)
                                    var opt = {
                                        "id" : "choice"+obj,
                                        "type":"update",
                                        "id_tag_option":result.data.option_names[obj].id_tag_option,
                                        "tag_option":result.data.option_names[obj].option_name,
                                        "id_tag_option_language": result.data.id_tag_option_language[obj]};
                                    $scope.choices[obj]=opt;
                                }
                                $scope.options = [];
                                $scope.tag.option_name = [];
                                angular.forEach(result.data.option_names,function(i,o){
                                    var obj = {};
                                    obj.tag_option =i.option_name;
                                    obj.id_tag_option =i.id_tag_option;
                                    obj.id_tag_option_language =i.id_tag_option_language;
                                    obj.option =i.option_name;
                                    $scope.tag.option_name[o] =obj;
                                    var optObj ={};
                                    optObj.id_tag_option = result.data.option_names[o].id_tag_option;
                                    optObj.id_tag_option_language = result.data.option_names[o].id_tag_option_language;
                                    optObj.option_name = result.data.option_names[o].option_name;
                                    $scope.options[o] = optObj;
                                });
                            }
                        });
                    }else{$scope.tag.tag_required = 1;}

                    var param ={};
                    param.user_role_id=$rootScope.user_role_id;
                    param.id_user=$rootScope.id_user;
                    param.customer_id = $scope.user1.customer_id;
                    // param.status = 1;
                    businessUnitService.list(param).then(function(result){
                        $scope.bussinessUnit = result.data.data;
                    });



                    if(type == 'input')$scope.tag.option_name[0] = '';
                    tagService.getTagOptions({'tag_type':type}).then(function(result){
                       // if(type == 'dropdown'){$scope.choices = result.data;}
                        if(type == 'radio'){
                            angular.forEach(result.data, function(item,key){
                                var obj = {};
                                obj.tag_option =item.tag_option;
                                obj.id_tag_option =item.id_tag_option;
                                obj.id_tag_option_language =item.id_tag_option_language;
                                $scope.tag.option_name[key] = obj ;
                            })
                        }

                        if(type == 'rag'){
                            angular.forEach(result.data, function(item,key){
                                var obj = {};
                                obj.option =item.tag_option;
                                obj.value =item.tag_value;
                                obj.id_tag_option =item.id_tag_option;
                                obj.id_tag_option_language =item.id_tag_option_language;
                                $scope.tag.option_name[key] = obj ;
                            })
                        }
                    });
                    $scope.addTag =  function (tag){
                        //console.log('tag info',tag);
                        var obj1 = {}; 
                        obj1.action_name = $scope.action;
                        obj1.action_description = $scope.action+'$$tag$$'+tag.tag_text;
                        obj1.module_type = $state.current.activeLink;
                        obj1.action_url= $location.$$absUrl;
                        //delete tag.option_name ;
                        delete tag.option_names;
                        tag.id_topic = $scope.topic_id;
                        tag.tag_type = $scope.tag_type;
                        //tag.tag_date= dateFilter(tag.date,"yyyy-MM-dd");
                        tag.option_delete = $scope.option_delete;
                        tag.categories = $scope.categories;
                        tag.type='catalogue_tags';
                        if($scope.tag_type == 'dropdown'){
                            delete tag.option_name;
                            tag.option_name = $scope.choices;
                        }
                        else tag.option_name = $scope.tag.option_name;
                        // console.log('tag',tag);
                        if(tag.id_tag){
                            tag.updated_by = $scope.user.id_user;
                            tagService.updateTag(tag).then(function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $rootScope.toast('Success',result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.req.status=0;
                                    $scope.getTagsList(0);
                                }else{
                                    $rootScope.toast('Error',result.error,'error',$scope.tag);
                                }
                            })
                        }else{
                            tag.created_by = $scope.user.id_user;
                            tagService.postTags(tag).then (function(result){
                                if(result.status){
                                    $scope.cancel();
                                    $rootScope.toast('Success',result.message);
                                    $rootScope.confirmNavigationForSubmit(obj1);
                                    $scope.req.status=0;
                                    $scope.getTagsList(0);
                                }else{
                                    $rootScope.toast('Error',result.error,'error',$scope.tag);
                                }
                            })
                        }
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.choices = [{id: 'choice1','type':'new'}];
                    $scope.addNewChoice = function(tag,index) {
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
                                if(i.id_tag_option == choice.id_tag_option){
                                    var obj = {};
                                    obj.id_tag_option =$scope.options[o].id_tag_option;
                                    obj.id_tag_option_language =$scope.options[o].id_tag_option_language;
                                    $scope.option_delete.push(obj);
                                }
                            })
                        }
                    };
                }
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        }

        $scope.sortableOptions = {           
            start: function (e, ui) {
            },
            update: function (e, ui) {
                var params = {};
                params.data = $scope.tagsList;
                tagService.sortTags(params).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                    }
                })
            },
            stop: function (e, ui) {
            },
            axis: 'y',
            cursor: 'move',
            forceHelperSize: true,
            forcePlaceholderSize: true,
        };
    }) 