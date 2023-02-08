angular.module('app')
.controller('emailTempaltesCtrl', function($scope, $rootScope,$filter,$localStorage,$translate, $state, $stateParams, encode, decode,$uibModal,$window, userService, emailTempalteService, $location){
   
    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }
   
   
    var data = {};
    $scope.filter = {};
    $scope.filter.filter_type = "";
    $scope.displayCount = $rootScope.userPagination;
    $scope.callServer = function (tableState){
        console.log('callServer');
        $rootScope.module = '';
        $rootScope.displayName = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        $scope.tableStateRef = tableState;
        $scope.isLoading = true;
        var pagination = tableState.pagination;
        //tableState.customer_id = $scope.user1.customer_id;
        tableState.user_id  = $scope.user1.id_user;
        tableState.user_role_id  = $scope.user1.user_role_id;
        emailTempalteService.get(tableState).then(function(result){
            $scope.emailTemplates = result.data.data;
            data = result.data.data;
            $scope.emptyTable=false;
            $scope.displayCount = $rootScope.userPagination;
            $scope.totalRecords = result.data.total_records;
            tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
            $scope.isLoading = false;
            if(result.data.total_records < 1)
                $scope.emptyTable=true;
        })
    }
    $scope.defaultPages = function(val){
        userService.userPageCount({'display_rec_count':val}).then(function (result){
            if(result.status){
                $rootScope.userPagination = val;
                $scope.callServer($scope.tableStateRef);
            }                
        });
    }
    if($stateParams.id){
        $rootScope.module = 'Email Template';
        $rootScope.displayName = $stateParams.name;
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';
        var params = {};
        params.user_id = $scope.user1.id_user;
        params.user_role_id = $scope.user1.user_role_id;
        params.id_email_template = params.email_template_id = decode($stateParams.id);
        emailTempalteService.get(params).then(function(result){
            if(result.status) {
                $scope.template = result.data.data[0];
                $scope.template.logo = result.customer_logo;
                $scope.template.template_content = $scope.template.template_content.replace('{logo}',$scope.template.logo);
                $scope.templateContent = $scope.template.template_content;
                var s = '';
                if ($scope.template.wildcards.search(',') > 0) {
                    angular.forEach(JSON.parse($scope.template.wildcards), function (item, k) {
                        s = s + item + ', ';
                    })
                    $scope.template.wildcards = '';
                    $scope.template.wildcards = s.slice(0, -1);
                    $scope.template.wildcards = $scope.template.wildcards.split(',').slice(0,-1);
                }
                else
                    $scope.template.wildcards = JSON.parse($scope.template.wildcards);
                /*$scope.template.recipients = jQuery.parseJSON($scope.template.recipients)*/
                if ($scope.template.recipients.search(',') > 0) {
                    $scope.template.recipients = JSON.parse($scope.template.recipients);
                    console.log('recipients',$scope.template.recipients);
                } else
                    $scope.template.recipients = JSON.parse($scope.template.recipients);
            }
        })
    }
    $scope.goToList = function () {
        $state.go('app.email-templates.list');
    };
    $scope.updateTempalte = function (template) {
        template.user_id =  $scope.user.id_user;
        template.template_content = template.template_content.replace($scope.template.logo,'{logo}');
        emailTempalteService.post(template).then(function(result){
            if(result.status){
                $rootScope.toast('Success',result.message);
                var obj = {};
                obj.action_name = 'update';
                obj.action_description = 'update$$email template$$' + template.template_name;
                obj.module_type = $state.current.activeLink;
                obj.action_url = $location.$$absUrl;
                $rootScope.confirmNavigationForSubmit(obj);
                $state.go('app.email-templates.list');
            }
        })
    }
    $scope.testEmail = function(template) {
        var modalInstance = $uibModal.open({
            animation: true,
            backdrop: 'static',
            keyboard: false,
            scope: $scope,
            openedClass: 'modal-open questions-modal',
            templateUrl: 'fill-email-text.html',
            size: 'lg',
            controller: function ($uibModalInstance, $scope) {
                $scope.testTemplate={};
                $scope.testTemplate = template;
                $scope.testTemplate.content =  template.template_content;
                //$scope.testTemplate.content = template.header + ''+ template.template_content+ ''+ template.footer;
                $scope.sendSampleEmail = function(testTemplate){
                    var params={};
                    params.content = testTemplate.content;
                    params.subject = testTemplate.template_subject;
                    params.type = 'testmail';
                    params.to_email = testTemplate.to_email;
                    params.to_name = testTemplate.to_name;
                    emailTempalteService.testTemplate(params).then(function(result){
                        if(result.status){
                            $rootScope.toast('Success',result.message);
                            var obj = {};
                            obj.action_name = 'view';
                            obj.action_description = 'preview$$email template$$' + template.template_name;
                            obj.module_type = $state.current.activeLink;
                            obj.action_url = $location.$$absUrl;
                            $rootScope.confirmNavigationForSubmit(obj);
                            $scope.cancel();
                        }
                        else $rootScope.toast('Error',reult.error,'error');
                    });
                }
                $scope.cancel = function () {
                    $uibModalInstance.close();
                };
            }
        });
        modalInstance.result.then(function ($data) {
        }, function () {
        });
    }
    $scope.filterTemplates = function(val){
        if(val==""){
            $scope.tableStateRef.module="";
            delete $scope.tableStateRef.module;
        }
        else $scope.tableStateRef.module = val;
        $scope.tableStateRef.pagination.start="0";
        $scope.callServer($scope.tableStateRef);
    }
    $scope.updateEmailStatus = function(index,item){
        var r=confirm($filter('translate')('general.alert_continue'));
        $scope.deleConfirm = r;
        if(r==true) {
            var params = {};
            params.user_id = $scope.user.id_user;
            params.id_email_template = item.id_email_template;
            params.status = item.status;
            emailTempalteService.delete(params).then(function (result) {
                if (result.status) {
                    $rootScope.toast('Success', result.message);
                    $scope.callServer($scope.tableStateRef);
                }
                else $rootScope.toast('Error', reult.error, 'error');
            })
        }else{
            if($scope.emailTemplates[index].status=='1')
                $scope.emailTemplates[index].status = '0';
            else
                $scope.emailTemplates[index].status = '1';
            /*$scope.callServer($scope.tableStateRef);*/
        }
    };
})