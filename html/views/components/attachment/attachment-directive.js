/**
 * Created by RAKESH on 13-04-2016.
 */
/**
 * A service that returns some data.
 *
 * <attachment-view module-id="1" ref-id="{{currentContactViewId}}"></attachment-view>
 */
'use strict';
angular.module('attachment',[])
    .controller('attachmentsCtrl',function($scope,$rootScope,$filter,Upload){
        $scope.uploadImage=[];
        $scope.ngModel =[];
        $scope.nodata = false;
        //get uploaded attachments
        $scope.userId = $rootScope.userId;
        $scope.showAttachmentDiv = false;
        $scope.getDownloadUrl = function(objData){
            var data = {};
            if(objData.id_crm_document_version && !objData.parent){
                data = {'id_crm_document_version': objData.id_crm_document_version};
            }else if(objData.id_crm_document && objData.parent==1){
                data = {'crm_document_id': objData.id_crm_document};
            }
        };
        $scope.getDocumentType= function(){
        }
        $scope.getFiles=function(){
            $scope.isLoading = true;
            var params ={};
        }
        $scope.clear=function(){
            $scope.uploadImage=[];
        }
        $scope.subUploadFile = function (file,document_id) {
            $scope.progressId = document_id;
            if(file){
                /*Upload.upload({
                    url: API_URL+'Crm/document',
                    data:{
                        file:{'attachment':file},
                        //'group_id':$scope.refId,
                        //'company_id':$rootScope.id_company,
                        //'user_id':$rootScope.userId,
                        'document_type':this.document_type,
                        //'crm_module_id' :$scope.crm_module_id,
                        'description':this.description,
                        //'module_id':$scope.crmModuleId,
                        'document_id':document_id

                    }
                }).then(function (resp) {
                    //console.log('resp',resp);
                    $rootScope.toast('Success',resp.data.message);
                    $scope.getFiles();
                }, function (resp) {
                    $rootScope.toast('Error',resp.data.error,'error');
                }, function (evt) {
                    $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                });*/

            }else{
                $rootScope.toast('Error','invalid format','image-error');
            }

        };
        $scope.deleteFile = function(type,document){
            var param = {};
            //param.type = type;
            param.document_id =document.id_crm_document_version;
            if(document.parent){
                param.type = 'document';
            }else{
                param.type = 'version';
            }
            var r=confirm($filter('translate')('general.alert_continue'));
            $scope.deleConfirm = r;
            if(r==true){
                /*crmService.document.delete(param).then(function(result){
                    if(result.status){
                        $scope.getFiles();
                        $rootScope.toast('Success',result.message);
                    }else{

                        $rootScope.toast('Error',result.error,'error');
                    }
                });*/
            }
        }
        //$scope.save=function(upload,form){
            $scope.ngModel = $scope.uploadImage;
            $scope.submitStatus=true;
            /*if(form.$valid){
                var $fileCount=1;
                $.each($scope.uploadImage,function(parentKey,parentvalue){
                    Upload.upload({
                        async:true,
                        url:API_URL+'Crm/document',
                        data:{
                            file:{'attachment':parentvalue},
                            'group_id':$scope.refId,
                            'company_id':$rootScope.id_company,
                            'user_id':$rootScope.userId,
                            'document_type':this.document_type,
                            'description':this.description,
                            'module_id':$scope.crmModuleId,
                            'form_key':$scope.form_key,
                            'field_key':$scope.field_key,
                            'crm_module_id' :$scope.crm_module_id,
                            'id_project_stage_section_form':$rootScope.formId
                        }
                    }).progress(function(e){
                        $.each($scope.uploadImage,function(value,key){
                            if(this.$$hashKey==parentvalue.$$hashKey){
                                this.progress=parseInt(100.0*e.loaded/e.total);
                            }
                        })
                    }).success(function(data){
                        if($scope.uploadImage.length==$fileCount){
                            $scope.getFiles();
                            $rootScope.toast('Success',data.message);
                            $scope.uploadImage=[];
                            $scope.submitStatus=false;
                        }
                        $fileCount++;
                        if(data.status){
                        }else{
                            $rootScope.toast('Error',data.error,'error',$scope.sector);
                        }
                    });
                });
            }*/
        //}

        $scope.uploadFiles=function(file){
            if(!$.isArray(file)){
                return false;
            }
            if(file.length==0){
                $rootScope.toast('Error','invalid format','image-error');
            }
            $.each(file,function(key,value){
                value.document_type='';
                value.progress=0;
                value.description='';
                value.descriptionName='description_'+key;
                value.documentTypeName='document_type_'+key;
                $scope.uploadImage.push(value);
            });
        };
        $scope.removeImage=function(obj,index){
            var r=confirm($filter('translate')('general.alert_continue'));
            if(r==true){
                $scope.uploadImage.splice(index,1);
            }
        };
    })
    .controller('attachmentsFilesCtrl',function($scope,$filter,$rootScope,Upload){
        $scope.uploadImage=[];
        $scope.ngModel = [];
        $scope.getDocumentType= function(){
            /*.getAttachmentsTypes({'module_id':$scope.crmModuleId,'form_key':$scope.form_key}).then(function(result){
                if(result.status){
                    $scope.documentsTypes=result.data;
                }
            });*/
        }
        $scope.uploadFiles=function(file){
            if(!$.isArray(file)){
                return false;
            }
            if(!$scope.allowMultiple){
                $scope.uploadImage = [];
            }
            angular.forEach(file,function(item){
                item.document_type = item.type;
                item.description='';
                $scope.uploadImage.push(item);
            })
            $scope.ngModel = $scope.uploadImage;
        };
        $scope.removeImage=function(index,f){
            var r=confirm($filter('translate')('general.alert_continue'));
            if(r==true){
                $scope.uploadImage.splice(index,1);
                $scope.ngModel = $scope.uploadImage;
            }
        };
    })
    .directive('attachmentView',function(){
        return {
            restrict:'EA',
            controller:'attachmentsCtrl',
            scope: {
                ngModel:'=',
                getData:'='
            },
            link:function(scope,element,attrs,ngModel){
                //scope.crmModuleId=attrs.moduleId;
                //scope.refId=attrs.refId;
                //scope.form_key=attrs.formKey;
                //scope.field_key=attrs.fieldKey;
                //scope.crm_module_id=attrs.crmModuleId;
                scope.hideSelectType=true;
                if(attrs.hideSelectType){
                    scope.hideSelectType = false;
                }else{
                    scope.getDocumentType();
                }
                scope.showAddBtn=true;
                //console.log('attrs.showAddBtn',attrs.showAddBtn);
                if(attrs.showAddBtn=="false"){
                    scope.showAddBtn = false;
                }
                if(attrs.title){
                    scope.title = attrs.title;
                }else{
                    scope.title = 'Attachments';
                }
                scope.getFiles();
            },
            templateUrl:function(elem,attrs){
                return 'views/components/attachment/view.html'
            }
        }
    })
    .directive('attachmentList',function(){
        return {
            restrict:'EA',
            controller:'attachmentsCtrl',
            link:function(scope,element,attrs){
                scope.crmModuleId=attrs.moduleId;
                scope.refId=attrs.refId;
                scope.form_key=attrs.formKey;
                scope.field_key=attrs.fieldKey;
                scope.getFiles();
            },
            templateUrl:function(elem,attrs){
                var template = 'views/components/attachment/list.html';
                if(attrs.templateUrl)
                    template = attrs.templateUrl;
                return template;
            }
        }
    })
    .directive('attachmentFiles',function($rootScope){
        return {
            restrict:'EA',
            controller:'attachmentsFilesCtrl',
            scope: {
                ngModel:'='
             },

            link:function(scope,element,attrs,ngModel){
                scope.url=$rootScope.currentUrl;
                scope.crmModuleId=attrs.moduleId;
                scope.form_key=attrs.formKey;
                scope.allowMultiple = true;
                if(attrs.allowMultiple=='false'){
                    scope.allowMultiple = false;
                }
                scope.showSelectType=false;
                if(attrs.showSelectType==='true'){
                    scope.getDocumentType();
                    scope.showSelectType = true;
                }
            },
            
            templateUrl:function(elem,attrs){
                return 'views/components/attachment/files.html'
            }
        }
    })
    .directive('attachmentListTemplate',function(){
        return {
            restrict: 'EA',
            scope:{
                getData:"=",
                altConfig:'=',
                ngModel:'='
            },
            templateUrl:function(elem,attrs){
                return 'views/components/attachment/list.html'
            },
            controller: function($scope,$rootScope){
                $scope.getDownloadUrl = function(objData){
                    //console.log(objData);
                };
                $scope.ngModel = [];
                $scope.deleteFile = function(index,row){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    $scope.deleConfirm = r;
                    if(r==true){
                        $scope.uploadedDocuments.splice(index,1);
                        $scope.ngModel.push(row);
                    }
                }
            },
            link: function(scope,element,attrs){
                scope.$watch('getData', function (newVal) {
                    scope.uploadedDocuments = newVal;
                });
                scope.title = 'Attachments List';
            }
        }
    })




