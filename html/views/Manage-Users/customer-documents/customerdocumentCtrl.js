angular.module('app', ['localytics.directives'])
    .controller('documentCustomerOverviewCtrl', function ($scope, $filter,$rootScope,$localStorage,$translate, $state, $stateParams, businessUnitService, documentService) {
        $scope.path = $stateParams.name;
        $scope.validateStatus=$stateParams.statusValidate;
        if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }

        
        if($scope.validateStatus=='C'){
            $scope.documentSubmit=true;
        }else{
            $scope.documentSubmit=false;
        }
        $scope.dynamicPopover = { templateUrl: 'myPopoverTemplate.html' };
        $scope.goToSpecifiedPage =function(pagenumber){
            console.log('pg',pagenumber);
            if(pagenumber){
                $scope.fileAccessurl = window.origin + '/Document/web/preview.html?file=' + $scope.path + '#page='+pagenumber;
            }
        }
        $scope.validateSubmit = true;
        $scope.validateOptions = true;
        $rootScope.displayName = ($stateParams.documentName) ? $stateParams.documentName : $stateParams.name ;
        $rootScope.module = 'Document Intelligence Details';
        $scope.fileAccessurl = window.origin + '/Document/web/preview.html?file=' + $scope.path + '#page=1';
        if($stateParams.id){
        documentService.getIntelligenceValidationList({ 'document_intelligence_id': $stateParams.id }).then(function (result) {
            $scope.validateResult = result.data.validateAnswers;
            console.log("js",$scope.validateResult);
            $scope.validatePercentage = result.data.validationComplitionPercentage;
            $scope.validatationInfo = result.data.validafationInfo;
            $scope.submit_validation = result.data.submit_validation;
            $scope.submitValidation = result.data.submit_validation;
        });
    }

        if ($scope.submit_validation == true) {
            $scope.validateSubmit = true;
        } else {
            $scope.validateSubmit = false;
        }

        $scope.setOptionStatus = function (field, optionIndex, optionStatus) {
            if ((optionStatus == 'A' || optionStatus == 'E') && field.status.length > 1) {
                field.status = field.status.map(s => 'R');
            }
            field.status[optionIndex] = optionStatus;
            return field;
        }

        $scope.saveDocument = function (data) {
            var params = {};
            params.validateAnswers = data;
            params.document_intelligence_id = $stateParams.id;
            documentService.ocrValidation(params).then(function (result) {
                $scope.validatePercentage = result.data.validationComplitionPercentage;
                $scope.validatationInfo = result.data.validafationInfo;
                $scope.submitValidation = result.data.submit_validation;
                if (result.status) {
                    $rootScope.toast('Success', result.message);
                } else {
                    $rootScope.toast('Error', result.error, 'error');
                }
            });

        }

        $scope.submitDocument = function (data) {
            var params = {};
            params.validateAnswers = data;
            params.document_intelligence_id = $stateParams.id;
            params.submit_validation = 1;
            documentService.submitValidation(params).then(function (result) {
                $state.go('app.customer-documents.all-documents');
                if (result.status) {
                    $rootScope.toast('Success', result.message);
                } else {
                    $rootScope.toast('Error', result.error, 'error');
                }
            });

        }
        $scope.closeDocument = function () {
            $state.go('app.customer-documents.all-documents');
        }
    })
    .controller('customerdocumentListCtrl', function ($scope, $interval,$translate, $filter,$rootScope, $uibModal, encode, decode, tagService,catalogueService, attachmentService, projectService, masterService, userService,dateFilter, templateService, providerService, businessUnitService, $state, documentService, contractService, Upload, $stateParams) {
        $rootScope.module = '';
        $rootScope.displayName = '';
        $rootScope.breadcrumbcolor = '';
        $rootScope.class = '';
        $rootScope.icon = '';
        $scope.currencyList = [];
        $scope.templateList = [];
        $scope.relationshipCategoryList = {};
        $scope.contractId = 0;
        $scope.contract = {};
        $scope.contract['auto_renewal'] = 1;
        $scope.disableTab = true;
        // console.log($rootScope.currentUrl);
        $scope.getDocumentIntelligenceList = function (tableState) {
            $scope.documentLoading = true;
            $scope.tableStateRef = tableState;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            if ($scope.ocr_status && $scope.ocr_status != null) {
                tableState.ocr_status = $scope.ocr_status;
            } else {
                delete tableState.ocr_status;
                $scope.ocr_status = null;
            }
            if ($scope.analysis_status && $scope.analysis_status != null) {
                tableState.analysis_status = $scope.analysis_status;
            } else {
                delete tableState.analysis_status;
                $scope.analysis_status = null;
            }
            if ($scope.validate_status && $scope.validate_status != null) {
                tableState.validate_status = $scope.validate_status;
            } else {
                delete tableState.validate_status;
                $scope.validate_status = null;
            }
            documentService.getDocumentIntelligence(tableState).then(function (result) {
                $scope.documentInfo = result.data.data;
                angular.forEach($scope.documentInfo,function(obj){
                if(obj.ocr_status=='F'&&obj.failed_reasons!='')
                    {
                        obj.failurereasonTooltip='OCR Conversion Failed';
                    }
                    else{
                        obj.failurereasonTooltip='';
                    }
                });

                    
                $scope.documentCount = result.data.total_records;
                $scope.emptyDocumentTable = false;
                $scope.displayCount = $rootScope.userPagination;
                tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.documentLoading = false;
                if (result.data.total_records < 1)
                    $scope.emptyDocumentTable = true;
            })
            $scope.refreshData = function () {
                $rootScope.hidePreloader = false;
                documentService.getDocumentIntelligence(tableState).then(function (result) {
                    var _newData = result.data.data;
                    $scope.documentInfo = $scope.documentInfo.map(d => {
                        var newData = _newData.find(nd => nd.id_document_intelligence == d.id_document_intelligence)
                        if (newData) {
                            return newData;
                        } else {
                            return d;
                        }
                    });
                    angular.forEach($scope.documentInfo,function(obj){
                        if(obj.ocr_status=='F'&&obj.failed_reasons!='')
                            {
                                obj.failurereasonTooltip='OCR Conversion Failed';
                            }
                            else{
                                obj.failurereasonTooltip='';
                            }
                        });
        
                });
            }
            var intervalPromise;
            intervalPromise = $interval($scope.refreshData, 60000);
            $scope.$on('$destroy', function () {
                if (intervalPromise)
                    $interval.cancel(intervalPromise);
            });

        }
        $scope.defaultPagesDocument = function (val) {
            userService.userPageCount({ 'display_rec_count': val }).then(function (result) {
                if (result.status) {
                    $rootScope.userPagination = val;
                    $scope.getDocumentIntelligenceList($scope.tableStateRef);
                }
            });
        }


        $scope.showdropdown = false;
        $scope.createDocument = function () {
            $scope.showdropdown = !$scope.showdropdown;
            var parent = document.getElementById("allDocuments");
            var parent1 = document.getElementById("documentPdf");
            if ($scope.showdropdown) {
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                //$scope.widgetinfo();
            } else {
                parent.classList.remove('showDivMenu');
                parent1.className = "fa fa-angle-double-down";
            }
        }

        // setInterval(function(){
        //     $scope.getDocumentIntelligenceList($scope.tableStateRef);
        // }, 20000)


        $scope.downloadPdf = function (info,type) {
            var encryptedPath = info.encrypted_original_document_path;
            if(type=='ocr'){
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

        $scope.createDocumentUploadPdf = function () {
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                size: 'lg',
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/customer-documents/add-pdf-files.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.title = 'documents.add_new_document';
                    $scope.bottom = 'documents.process';
                    $scope.documentpdf = {};
                    $scope.file = {};

                    if (item) {
                        $scope.title = 'documents.edit_document';
                        $scope.bottom = 'general.save';
                    }
                    contractService.responsibleUserList({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(), 'type': 'buowner','forDocumentIntelligence':'1' }).then(function (result) {
                        $scope.buOwnerUsers = result.data;
                    })

                    contractService.getDelegates({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(),'forDocumentIntelligence':'1' }).then(function (result) {
                        $scope.delegates = result.data;
                    })

                    documentService.getTemplatesList({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                        $scope.templates = result.data;
                    })
                    
                    
                    $scope.addDocumentPdf = function (data1) {
                        var attachmentSize=0;
                        angular.forEach($scope.file.attachment,function(i,o){
                            attachmentSize+= i.size;
                
                        });
                        if(attachmentSize>125829120){
                            $scope.callApi = false;
                                alert($filter('translate')('general.alert_file_size'));
                            }
                            else{
                                if($scope.file.attachment.length > 20){
                                        $scope.callApi = false;
                                        alert($filter('translate')('general.alert_file_size'));
                                    }
                                    else
                                    {
                                        $scope.callApi=true;
                                    }
                            }

                        if($scope.callApi)var r = confirm($filter('translate')('general.alert_document'));
                        if (r == true) {
                        var documentpdf = {};
                        documentpdf.attachment_delete = [];
                        if ($scope.file.delete) {
                            angular.forEach($scope.file.delete, function (i, o) {
                                var obj = {};
                                obj.id_document = i.id_document;
                                documentpdf.attachment_delete.push(obj);
                            });
                        }
                        if($scope.callApi){
                            Upload.upload({
                                url: API_URL + 'Document/createDocumentIntelligence',
                                data: {
                                    'file': $scope.file.attachment,
                                    'owner_id': data1.owner_id,
                                    'customer_id': $scope.user1.customer_id,
                                    'delegate_id': data1.delegate_id,
                                    'id_intelligence_template': data1.id_intelligence_template
                                }
                            }).then(function (resp) {
                                //console.log('resp',resp);
                                if (resp.data.status) {
                                    $rootScope.toast('Success', resp.data.message);
                                    $scope.cancel();
                                    $scope.getDocumentIntelligenceList($scope.tableStateRef);
                                    var obj = {};
                                    obj.action_name = 'add';
                                    obj.action_description = 'add$$documentIntelligence$$' + documentpdf.file_name;
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                } else {
                                    $rootScope.toast('Error', resp.data.error);
                                }
                            }, function (resp) {
                                $rootScope.toast('Error', resp.error);
                            }, function (evt) {
                                var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                            });
                        }
                       
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


        $scope.editDocumentUpload = function (row) {
            // console.log("row is:",row);
            var selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                size: 'lg',
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/customer-documents/edit-pdf-files.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.title = 'documents.edit_document';
                    $scope.bottom = 'general.save';


                    contractService.responsibleUserList({ 'array_buids': $scope.user1.encrypted_bu_ids.toString(), 'type': 'buowner' }).then(function (result) {
                        $scope.buOwnerUsers = result.data;
                    })

                    contractService.getDelegates({ 'array_buids': $scope.user1.encrypted_bu_ids.toString() }).then(function (result) {
                        $scope.delegates = result.data;
                    })

                    documentService.getTemplatesList({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                        $scope.templates = result.data;
                    })


                    documentService.getDocumentIntelligence({ 'id_document_intelligence': row.id_document_intelligence, 'customer_id': $scope.user1.customer_id }).then(function (result) {
                        $scope.document = result.data.data[0];
                        $scope.id_document_intelligence = result.data.data[0].id_document_intelligence;
                        // console.log($scope.id_document_intelligence);

                    });

                    $scope.editDocumentPdf = function (data1) {
                        var params = {};
                        params.original_document_name = data1.original_document_name;
                        params.owner_id = data1.owner_id;
                        params.delegate_id = data1.delegate_id;
                        params.intelligence_template_id = data1.intelligence_template_id;
                        params.id_document_intelligence = data1.id_document_intelligence;
                        documentService.updateDocument(params).then(function (result) {
                            if (result.status) {
                                $scope.cancel();
                                $scope.getDocumentIntelligenceList($scope.tableStateRef);
                                $rootScope.toast('Success', result.message);
                            }
                            else {
                                $rootScope.toast('Error', result.error, 'error');
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

        $scope.deleteDocument = function (id) {
            // console.log(id);
            var r = confirm($filter('translate')('normal.delete_document'));
            if (r == true) {
                var params = {};
                params.id_document_intelligence = id.id_document_intelligence;
                documentService.deleteDocumentPdf(params).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                        $scope.getDocumentIntelligenceList($scope.tableStateRef);
                    }
                    else {
                        $rootScope.toast('Error', result.error, 'error');
                    }
                })
            }

        }

        $scope.completeProcess = function (complete) {
            var r = confirm($filter('translate')('general.alert_cmplt_contrct_procss'));
            if (r == true) {
                var params = {};
                params.document_intelligence_id = complete.id_document_intelligence;
                params.complete_process = 1;
                documentService.submitValidation(params).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.message);
                    }
                    else {
                        $rootScope.toast('Error', result.error, 'error');
                    }
                })

            }

        }

        $scope.newContract = function (info) {
            $scope.id_document_intelligence = info.id_document_intelligence;
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
                templateUrl: 'views/Manage-Users/customer-documents/new-document-contract.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.title = 'documents.add_new_document';
                    $scope.bottom = 'general.create';
                    $scope.bottom1='general.update';
                    $scope.titles ='general.create';
                    $scope.enableTemplate = true;

                    contractService.generateContractId({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                        if (result.status) {
                            $scope.contract = result.data;
                        }
                    });


                    masterService.currencyList({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                        $scope.currencyList = result.data;
                    });
                    // $scope.currencySelect = function (val) {
                    //     $scope.currencyName = val;
                    // }
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
                        // console.log(id);
                        // console.log(contractId);
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

                    
                    documentService.getInitialTabCount({}).then(function(result){
                        if(result.status){
                            $scope.infoObj = result;
                        }
                    })
                    documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Contract Information' }).then(function (result) {
                        $scope.contractInformationAnswer = result.data.approvedOrEdited;
                        $scope.contractInformationReject = result.data.rejectedAnswers;
                    });

                    documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Contract Tag' }).then(function (result) {
                        $scope.contractTagAnswer = result.data.approvedOrEdited;
                        $scope.contractTagReject = result.data.rejectedAnswers;
                    });

                    documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Contract Value' }).then(function (result) {
                        $scope.contractValueAnswer = result.data.approvedOrEdited;
                        $scope.contractValueReject = result.data.rejectedAnswers;
                    });

                    $scope.obligationsFromAi = function () {
                        documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Obligation,Right' }).then(function (result) {
                            $scope.contractObligationAnswer = result.data.approvedOrEdited;
                            $scope.totalObligation = result.data.approved_records_count;
                            $scope.contractObligationReject = result.data.rejectedAnswers;
                        });
                    }
                    $scope.obligationsFromAi();

                    $scope.moveAttachmentFromAi = function () {
                        documentService.getDocumentIntelligence({ 'id_document_intelligence': info.id_document_intelligence, 'customer_id': $scope.user1.customer_id }).then(function (result) {
                         $scope.contractAttachmentAnswer = result.data.data[0];
                        });
                    }
                    $scope.moveAttachmentFromAi();

                    $scope.moveAttachments = function (data, isoriginal) {
                        //  console.log('data',data);
                        //  console.log('isOriginal',isoriginal);
                        var params = {};
                        params.reference_id = $scope.responseContractId;
                        params.id_document_intelligence = data.id_document_intelligence;
                        if (isoriginal) {
                            params.name = data.original_document_name;
                            params.path = data.original_document_path;
                        }
                        else {
                            params.name = data.ocr_document_name;
                            params.path = data.ocr_document_path;
                            params.is_ocr = 1;
                        }
                        params.reference_type = 'contract';
                        params.module_type = 'document_intelligence';
                        params.module_id = data.id_document_intelligence;

                        documentService.attachmentMove(params).then(function (result) {
                            //console.log('res',result);
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.moveAttachmentFromAi();
                                $scope.getInfo();
                            } else {
                                //$rootScope.toast('Error', 'failed');
                                $rootScope.toast('Error', result.error);
                            }
                        });
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

                    $scope.uploadAttachment = function (fData) {
                       $scope.isView = true;
                       var params = {};
                       params.file = fData.file.attachments;
                       params.module_id = 0;
                       params.module_type = 'contract_review';
                       params.reference_type = 'contract';
                       params.module_id = 0;
                       params.reference_id = $scope.responseContractId;
                       params.document_type = 0;
                       params.uploaded_by = $scope.user1.id_user;
                       params.customer_id = $scope.user1.customer_id;
                       contractService.uploaddata(params).then(function (result) {
                           if (result.status) {
                              $scope.isView = false;
                              $scope.moveAttachmentFromAi()
                               $rootScope.toast('Success', result.message);
                               $scope.getInfo();
                           }
                           else {
                               $scope.isView = false;
                               $rootScope.toast('Error', result.error, 'error');
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

                  $scope.deleteAttachment = function(id,name){
                    var r=confirm($filter('translate')('general.alert_continue'));
                    $scope.deleConfirm = r;
                    if(r==true){
                        var params = {};
                        params.id_document = id;
                        attachmentService.deleteAttachments(params).then (function(result){
                            if(result.status){
                                $rootScope.toast('Success',result.message);
                                $scope.getInfo();
                                var obj = {};
                                obj.action_name = 'delete';
                                obj.action_description = 'delete$$Attachment$$('+name+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                            }else{$rootScope.toast('Error',result.error,'error');}
                        })
                    }
                } 
                   $scope.removeLink = function (index) {
                    var r = confirm($filter('translate')('general.alert_continue'));
                    if (r == true) {
                        $scope.contractLinks.splice(index, 1);
                    }
                }
                $scope.uploadLinks = function (contractLinks) {
                    //console.log('link',contractLinks);
                    $scope.isLink = true;
                    var file = contractLinks;
                    if (contractLinks) {
                        Upload.upload({
                            url: API_URL + 'Document/add',
                            data: {
                                file: contractLinks,
                                customer_id: $scope.user1.customer_id,
                                module_id: 0,
                                module_type: 'contract',
                                reference_id: $scope.responseContractId,
                                document_type: 1,
                                reference_type: 'contract',
                                uploaded_by: $scope.user1.id_user
                            }
                        }).then(function (resp) {
                            if (resp.data.status) {
                                $rootScope.toast('Success', resp.data.message);
                                $scope.contractLinks=[];
                                $scope.isLink = false;
                                $scope.moveAttachmentFromAi();
                                $scope.getInfo();
                                var obj = {};
                                obj.action_name = 'upload';
                                obj.action_description = 'upload$$module$$question$$link$$(' + $stateParams.mName + ')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                               

                            }
                            else $rootScope.toast('Error', resp.data.error, 'error', $scope.user);
                        }, function (resp) {
                            $rootScope.toast('Error', resp.data.error, 'error');
                        }, function (evt) {
                            $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                        });
                    } else {
                        $rootScope.toast('Error', 'No link selected', 'image-error');
                    }
                }



                   $scope.verifyLink = function (data) {
                       if (data != {}) {
                           $scope.contractLinks.push(data);
                           $scope.contractLink = {};
                       }
                   }

                   $scope.redirectUrl = function(url){
                    if(url != undefined){
                        var r=confirm($filter('translate')('contract.alert_msg'));
                        if(r==true){
                            url = url.match(/^https?:/) ? url : '//' + url;
                            window.open(url,'_blank');
                        }
                    }
                };    
                   $scope.getDownloadUrl = function(objData){
                    var fileName = objData.document_source;
                    var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
                    var d = {};
                    d.id_document = objData.id_document;
                    var encryptedPath= objData.encryptedPath;
                    var filePath =API_URL+'Cron/preview?file='+encryptedPath;
                    encodePath =encode(filePath);
                    if(fileExtension=='pdf' || fileExtension=='PDF' ){
                        window.open(window.origin+'/Document/web/preview.html?file='+encodePath+'#page=1');
                    }
                    else{
                        contractService.getUrl(d).then(function (result) {
                            if(result.status){
                                var obj = {};
                                obj.action_name = 'download';
                                obj.action_description = 'download$$attachment$$('+ objData.document_name+')';
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
                                            result.data.url =  GibberishAES.enc(result.data.url, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                            result.data.file =  GibberishAES.enc(result.data.file, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                        }
                                        window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+obj.user_id+'&access_token='+obj.access_token;
                                    }
                                });
                            }
                        });
                    }
                   
                };

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
                    $scope.move = function (data) {
                        var params = {};
                        params.contract_id = $scope.responseContractId;
                        params.description = data.field_name;
                        // params.type  = data.field_type;
                        if (data.field_type == 'Right') {
                            params.type = 1;
                        }
                        else if (data.field_type == 'Obligation') {
                            params.type = 0;
                        }
                        params.detailed_description = data.options[0];
                        params.id_document_fields = data.id_document_fields
                        // console.log("params is:",params)
                        projectService.addObligations(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.obligationsFromAi();
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

                                        if ($scope.obligations.recurrence_start_date) $scope.obligations.recurrence_start_date = moment($scope.obligations.recurrence_start_date).utcOffset(0, false).toDate();
                                        if ($scope.obligations.recurrence_end_date) $scope.obligations.recurrence_end_date = moment($scope.obligations.recurrence_end_date).utcOffset(0, false).toDate();
                                        if ($scope.obligations.email_send_start_date) $scope.obligations.email_send_start_date = moment($scope.obligations.email_send_start_date).utcOffset(0, false).toDate();
                                        if ($scope.obligations.email_send_last_date) $scope.obligations.email_send_last_date = moment($scope.obligations.email_send_last_date).utcOffset(0, false).toDate();



                                        $scope.options = {
                                            minDate: moment().utcOffset(0, false).toDate(),
                                            showWeeks: false
                                        };
                                        $scope.options2 = angular.copy($scope.options);



                                        $scope.options3 = {
                                            minDate: moment().utcOffset(0, false).toDate(),
                                            showWeeks: false
                                        }
                                        $scope.options4 = angular.copy($scope.options3);


                                        var dt12 = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : moment().utcOffset(0, false).toDate());
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



                                        var dt23 = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : moment().utcOffset(0, false).toDate());

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
                                    var dt = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : moment().utcOffset(0, false).toDate());
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
                                    minDate: moment().utcOffset(0, false).toDate(),
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
                                    minDate: moment().utcOffset(0, false).toDate(),
                                    showWeeks: false
                                };
                                $scope.options4 = angular.copy($scope.options3);

                                $scope.emailRecurrence = function (info) {
                                    if (info) {
                                        $scope.obligations.email_send_last_date = '';
                                    }
                                    var dts = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : moment().utcOffset(0, false).toDate());
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


                    $scope.createContract = function(obj){
                        $scope.formDataObj = angular.copy(obj);
                        var contract = {};
                        contract = $scope.formDataObj;
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
                                var currencyInfo = $scope.currencyList.filter(item => { return item.id_currency == contract.currency_id; });
                                if (currencyInfo.length > 0) {
                                    $scope.infoObj.currency_name = currencyInfo[0]['currency_name'];
                                }
                                $scope.responseContractId = resp.data.contract_id;
                                $scope.disableTab = false;
                                $scope.disableCreate = true;
                                $rootScope.toast('Success', resp.data.message);
                                $scope.getInfo();
                                $scope.tagsData();
                                $scope.moveAttachment1();
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
                   
                    $scope.updateContractInfo = function (data) {
                        var postData = angular.copy(data);

                        delete postData.additional_recurring_fees; delete postData.additional_recurring_fees_period;
                        delete postData.additonal_one_off_fees; delete postData.po_number;
                        delete postData.contract_unique_id; delete postData.provider_partner_relationship_manager;
                        delete postData.unique_attachment; delete postData.internal_partner_relationship_manager;
                        delete postData.attachments; delete postData.internal_contract_sponsor; delete postData.internal_contract_responsible;
                        delete postData.action_items; delete postData.provider_contract_sponsor; delete  postData.provider_contract_responsible; 
                        postData.contract_start_date = dateFilter(data.contract_start_date, 'yyyy-MM-dd');
                        postData.contract_end_date = dateFilter(data.contract_end_date, 'yyyy-MM-dd');
                        postData.customer_id = $scope.user1.customer_id;
                        postData.id_document_intelligence = $scope.id_document_intelligence;
                        Upload.upload({
                            url: API_URL + 'Contract/update',
                            data: {
                                'contract': postData,
                            }
                        })
                            .then(function (resp) {
                                if (resp.data.status) {
                                    $rootScope.toast('Success', resp.data.message);
                                   
                                    $scope.getInfo();
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$contract$$' + postData.contract_name;
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.init();
                                    $scope.getInfo();
                                    //$uibModalInstance.close();
                                } else {
                                    $rootScope.toast('Error', resp.data.error, 'error', $scope.contract);
                                }
                            }, function (resp) {
                                $rootScope.toast('Error', resp.error);
                            });

                    }

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

                    $scope.tagsData=function(){
                        tagService.groupedTags({'status':1,'tag_type':'contract_tags'}).then(function(result){
                            if (result.status) {
                                $scope.tagsInfo = result.data;
                            }
                        });
                    }

                    $scope.tagsData34 = function(){
                        var params={};
                        if($scope.responseContractId)params.id_contract = $scope.responseContractId;
                        params.tag_type='contract_tags';
                        tagService.getContractTags(params).then (function(result){
                            if(result.status){
                                $scope.tagsInfo=[];
                                $scope.tagsInfo = result.data;
                                angular.forEach($scope.tagsInfo,function(i,o){
                                angular.forEach(i.tag_details,function(j,k){
                                    if(j.tag_type=='date'){
                                        j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                    }
                                })
                            });
                
                            }else {$rootScope.toast('Error',result.error,'error',$scope.contract);}
            
                        });
                    }

                    $scope.createTags = function (data) {
                        console.log("d",data);

                        angular.forEach(data,function(i,o){
                            angular.forEach(i.tag_details,function(j,k){
                            if(j.tag_type=='date'){
                                j.tag_answer = dateFilter(j.tag_answer,'yyyy-MM-dd');
                            }
                        });
                    });
                            var params = {};
                        params.id_contract = $scope.responseContractId;
                        params.tag_type = 'contract_tags';
                        params.contract_tags = data;

                            

                        tagService.updateContractTags(params).then(function (result) {
                            if (result.status) {
                                var object=
                                $rootScope.toast('Success', result.message);
                                $scope.titles ='general.update';            
                                $scope.getInfo();
                                $scope.tagsData34();
                                catalogueService.selectedMasteerList({'customer_id': $scope.user1.customer_id,'type':'contract'}).then (function(result){
                                    $scope.selectedInfoContract = result.data;
                                });
                                        
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Contract$$Tags$$(' + $stateParams.name + ')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                    
                            } else {
                                $rootScope.toast('Error', result.error, 'error');
                            }
                        });
                    }

                
                    $scope.contractValue = function (data) {
                        var params={};
                        params.updated_by = $scope.user.id_user;
                        params.id_contract = $scope.responseContractId;
                        params.po_number=data.po_number!=''?data.po_number:null;
                        params.contract_value_period=data.contract_value_period;
                        params.contract_value=data.contract_value;
                        params.contract_value_description=data.contract_value_description!=''?data.contract_value_description:null;
                        params.additional_recurring_fees_period=data.additional_recurring_fees_period!=''?data.additional_recurring_fees_period:null;
                        params.additional_recurring_fees=data.additional_recurring_fees!=''?data.additional_recurring_fees:null;
                        params.additional_recurring_value_description=data.additional_recurring_value_description!=''?data.additional_recurring_value_description:null;
                        params.additonal_one_off_fees=data.additonal_one_off_fees!=''?data.additonal_one_off_fees:null;
                        params.additonal_one_off_value_description=data.additonal_one_off_value_description!=''?data.additonal_one_off_value_description:null;
                        contractService.updateSpendMgmt(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.getInfo();
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Spend$$Lines$$(' + data.action_item + ')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                            } else {
                                $rootScope.toast('Error', result.error, 'error');
                            }
                        });
                    }
                    $scope.getInfo = function(){
                        var par = {};
                        if($scope.responseContractId)par.id_contract  = $scope.responseContractId;
                        par.id_user  = $scope.user1.id_user;
                        par.user_role_id  = $scope.user1.user_role_id;
                        par.id_contract_workflow  = $scope.workflowId;
                        par.id_contract_review  = decode($stateParams.rId);
                        par.is_workflow  = $scope.isWorkflow;
                        contractService.getContractById(par).then (function(result){
                            //console.log('result info',result);
                            if(result.status){
                                $scope.infoObj = result;
                                $scope.contract = result.data[0];
                                $scope.valueinfo =result.data[0];
                                $scope.currency_name = $scope.contract.currency_name;
                                if($scope.contract.is_template_lock ==1){
                                    $scope.lock = true;
                                }
                                else{
                                    $scope.lock=false;
                                }
                                $scope.contract.contract_start_date = moment($scope.contract.contract_start_date).utcOffset(0, false).toDate();
                                if($scope.contract.contract_end_date)$scope.contract.contract_end_date = moment($scope.contract.contract_end_date).utcOffset(0, false).toDate();
                                $scope.getContractDelegates($scope.contract.business_unit_id,$scope.contract.id_contract);
                                if($scope.contract.can_review==1)
                                    $scope.enableTemplate = true;
                                else $scope.enableTemplate = false;
                            }
                        });
                    }
                    //$scope.getInfo();

                    // $scope.tagsData = function(){
                    //     var params={};
                    //     if($scope.responseContractId)params.id_contract = $scope.responseContractId;
                    //     params.tag_type='contract_tags';
                    //     tagService.getContractTags(params).then (function(result){
                    //         if(result.status){
                    //             $scope.tagsInfo=[];
                    //             $scope.tagsInfo = result.data;
                    //             angular.forEach($scope.tagsInfo,function(i,o){
                    //                 if(i.tag_type=='date'){
                    //                     i.tag_answer = moment(i.tag_answer).utcOffset(0, false).toDate();
                    //                 }
                    //             })
                
                    //         }else {$rootScope.toast('Error',result.error,'error',$scope.contract);}
            
                    //     });
                    // }
                   
                    $scope.tagsOptions = {
                        minDate: moment().utcOffset(0, false).toDate(),
                        showWeeks: false
                    };
                   //$scope.tagsData();

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

        //update contract start
        $scope.updateSelectedContract = function (details) {
            $scope.contract_id = details.contract_id;
            $scope.id_document_intelligence = details.id_document_intelligence;
            $scope.contractLinks = [];
            $scope.contractLink = {};
            $scope.disableTab = false;
            $scope.fdata = {};
            $scope.isView = false;
            $scope.isLink = false;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                size: 'lg',
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/customer-documents/update-document-contract.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.bottom = 'general.update'

                    documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Contract Information' }).then(function (result) {
                        $scope.contractInformationAnswer = result.data.approvedOrEdited;
                        // console.log("approved", $scope.contractInformationAnswer);
                        $scope.contractInformationReject = result.data.rejectedAnswers;
                    });

                    documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Contract Tag' }).then(function (result) {
                        $scope.contractTagAnswer = result.data.approvedOrEdited;
                        $scope.contractTagReject = result.data.rejectedAnswers;
                    });

                    documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Contract Value' }).then(function (result) {
                        $scope.contractValueAnswer = result.data.approvedOrEdited;
                        $scope.contractValueReject = result.data.rejectedAnswers;
                    });

                    $scope.obligation = function () {

                        documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Obligation,Right' }).then(function (result) {
                            $scope.contractObligationAnswer = result.data.approvedOrEdited;
                            $scope.totalObligation =  result.data.approved_records_count;
                            $scope.contractObligationReject = result.data.rejectedAnswers;
                        });
                    }
                    $scope.obligation();


                    $scope.moveAttachment1 = function () {
                        documentService.getDocumentIntelligence({ 'id_document_intelligence': $scope.id_document_intelligence, 'customer_id': $scope.user1.customer_id }).then(function (result) {
                            $scope.contractAttachmentAnswer = result.data.data[0];
                        });
                    }
                    $scope.moveAttachment1();



                    masterService.currencyList({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                        $scope.currencyList = result.data;
                    });
                    templateService.list().then(function (result) {
                        $scope.templateList = result.data.data;
                    });
                    providerService.list({ 'customer_id': $scope.user1.customer_id, 'status': 1, 'all_providers': true }).then(function (result) {
                        $scope.providers = result.data.data;
                    });

                    $scope.getBUList = function () {
                        var param = {};
                        param.user_role_id = $rootScope.user_role_id;
                        param.id_user = $rootScope.id_user;
                        param.customer_id = $scope.user1.customer_id;
                        param.status = 1;
                        businessUnitService.list(param).then(function (result) {
                            result.data.data.unshift({ 'id_business_unit': 'All', 'bu_name': 'All' });
                            $scope.bussinessUnit = result.data.data;
                        });
                    }
                    $scope.getBUList();
                    $scope.getCategoryList = function () {
                        contractService.getRelationshipCategory({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                            $scope.relationshipCategoryList = result.drop_down;
                        });
                    }
                    $scope.getCategoryList();

                    $scope.moveAttachments = function (data, isoriginal) {
                        // console.log('data',data);
                        //console.log('isOriginal',isoriginal);
                        var params = {};
                        params.reference_id = data.contract_id;
                        params.id_document_intelligence = data.id_document_intelligence;
                        if (isoriginal) {
                            params.name = data.original_document_name;
                            params.path = data.original_document_path;
                        }
                        else {
                            params.name = data.ocr_document_name;
                            params.path = data.ocr_document_path;
                            params.is_ocr = 1;
                        }
                        params.reference_type = 'contract';
                        params.module_type = 'document_intelligence';
                        params.module_id = data.id_document_intelligence;

                        documentService.attachmentMove(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.moveAttachment1();
                                $scope.getInfo();
                            } else {
                                //$rootScope.toast('Error', 'failed', 'error');
                                $rootScope.toast('Error', result.error);
                            }
                        });
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


                    $scope.changeLockingStatus = function(info){
                      var params={};
                          params.id_document = info.id_document;
                    contractService.lockingStatus(params).then(function(result){
                        if(result.status){
                               $rootScope.toast('Success', result.message);
                                $scope.getInfo();
                                $scope.init();
                          }
                        });
                    }
                    $scope.moveAll = function (data) {
                        console.log('datamove',data);
                        projectService.moveAllObligation({ 'id_document_intelligence': $scope.id_document_intelligence }).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.obligation();
                                $scope.getObligations($scope.tableStateRef);
                                $scope.getInfo();
                            } else {
                                $rootScope.toast('Error', result.error, 'error');

                            }
                        });
                    }

                    $scope.move = function (data) {
                        var params = {};
                        params.contract_id = $scope.contract_id;
                        params.description = data.field_name;
                        if (data.field_type == 'Right') {
                            params.type = 1;
                        }
                        else if (data.field_type == 'Obligation') {
                            params.type = 0;
                        }
                        params.detailed_description = data.options[0];
                        params.id_document_fields = data.id_document_fields;

                        projectService.addObligations(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.obligation();
                                $scope.getObligations($scope.tableStateRef);
                                $scope.getInfo();
                            } else {
                                $rootScope.toast('Error', result.error, 'error');

                            }
                        });
                    }

                    $scope.getContractDelegates = function (id, contractId) {
                        contractService.getDelegates({ 'id_business_unit': id, 'contract_id': details.contract_id }).then(function (result) {
                            $scope.delegates = result.data;
                        });
                        var params = {};
                        params.type = "buowner";
                        params.business_unit_id = id;
                        params.contract_id = contractId;
                        contractService.getbuOwnerUsers(params).then(function (result) {
                            $scope.buOwnerUsers = result.data;
                        });
                    }

                    $scope.getProviderUserList = function () {
                        var reqObj = {};
                        reqObj.user_type = 'external';
                        reqObj.status = 1;
                        reqObj.customer_id = $scope.user1.customer_id;
                        reqObj.id_provider = $scope.contractInfo.provider_name;
                        reqObj.user_role_id = $scope.user1.user_role_id;
                        reqObj.id_user = $scope.user1.id_user;
                        reqObj.contract_id = $scope.contract_id;
                        customerService.getUserList(reqObj).then(function (result) {
                            $scope.providerUsersList = result.data.data;
                        });
                    }
                    $scope.updateLockingStatus = function (id) {
                        $scope.infoObj.is_template_lock = id;
                        if (id) {
                            $scope.lock = true;
                        }
                        else {
                            $scope.lock = false;
                        }
                    }

                    $scope.resetLockingStatus = function (id) {
                        $scope.infoObj.is_template_lock = id;
                        if (id) {
                            $scope.lock = false;
                        }
                        else {
                            $scope.lock = true;
                        }
                    }

                    $scope.getInfo = function () {
                        var par = {};
                        par.id_contract = $scope.contract_id;
                        par.id_user = $scope.user1.id_user;
                        par.user_role_id = $scope.user1.user_role_id;
                        par.is_workflow = 0;
                        contractService.getContractById(par).then(function (result) {
                            if (result.status) {
                                $scope.infoObj = result.data[0];
                                if ($scope.infoObj.is_template_lock == 1) {
                                    $scope.lock = true;
                                }
                                else {
                                    $scope.lock = false;
                                }
                                $scope.contract_attachments = result.contract_attachments;
                                $scope.contract_information = result.contract_information;
                                $scope.contract_tags = result.contract_tags;
                                $scope.obligationCount=result.obligations_count;
                                $scope.contract_spent_managment = result.contract_spent_managment;
                                $scope.infoObj.contract_start_date = moment($scope.infoObj.contract_start_date).utcOffset(0, false).toDate();    
                                $scope.infoObj.contract_end_date = moment($scope.infoObj.contract_end_date).utcOffset(0, false).toDate();
                                //console.log($scope.contract.contract_end_date);

                                $scope.getContractDelegates($scope.infoObj.business_unit_id, $scope.infoObj.id_contract);
                                if ($scope.infoObj.can_review == 1)
                                    $scope.enableTemplate = true;
                                else $scope.enableTemplate = false;
                            }
                        });
                    }
                    $scope.getInfo();


                    $scope.updateContractInfo = function (data) {
                        var postData = angular.copy(data);
                        delete postData.contract_unique_id;
                        delete postData.unique_attachment;
                        delete postData.attachments;
                        delete postData.action_items;
                        postData.contract_start_date = dateFilter(data.contract_start_date, 'yyyy-MM-dd');
                        postData.contract_end_date = dateFilter(data.contract_end_date, 'yyyy-MM-dd');
                        postData.customer_id = $scope.user1.customer_id;
                        postData.id_document_intelligence = $scope.id_document_intelligence;
                        Upload.upload({
                            url: API_URL + 'Contract/update',
                            data: {
                                'contract': postData,
                            }
                        })
                            .then(function (resp) {
                                if (resp.data.status) {
                                    $rootScope.toast('Success', resp.data.message);
                                    $scope.getInfo();
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$contract$$' + postData.contract_name;
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.init();
                                    $scope.getInfo();
                                    //$uibModalInstance.close();
                                } else {
                                    $rootScope.toast('Error', resp.data.error, 'error', $scope.contract);
                                }
                            }, function (resp) {
                                $rootScope.toast('Error', resp.error);
                            });

                    }


                    $scope.tagsData = function () {
                        tagService.getContractTags({ 'id_contract': $scope.contract_id, 'tag_type': 'contract_tags' }).then(function (result) {
                            //console.log('data',result);
                            if (result.status) {
                                $scope.tagsdata = [];
                                $scope.tagsdata = result.data;
                                angular.forEach($scope.tagsdata, function (i, o) {
                                    angular.forEach(i.tag_details, function (j, k) {
                                            if (j.tag_type == 'date') {
                                                j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                            }
                                        })
                                    });
        
                            } else { $rootScope.toast('Error', result.error, 'error', $scope.contract); }

                        });
                    }


                
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
                

                    $scope.tagsOptions = {
                        minDate: moment().utcOffset(0, false).toDate(),
                        showWeeks: false
                    };
                    $scope.tagsData();

                    $scope.updateTags = function (data) {
                        var params = {};
                        params.id_contract = $scope.contract_id;
                        params.tag_type = 'contract_tags';
                        angular.forEach(data, function (i, o) {
                        angular.forEach(i.tag_details, function (j, k) {
                            if (j.tag_type == 'date') {
                                j.tag_answer = dateFilter(j.tag_answer, 'yyyy-MM-dd');
                            }
                        });
                    });
                        params.contract_tags = data;
                        tagService.updateContractTags(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Contract$$Tags$$(' + $stateParams.name + ')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.getInfo();
                                $scope.tagsData();
                                // angular.forEach($scope.tagsInfo, function (i, o) {
                                //     if (i.tag_type == 'date') {
                                //         i.tag_answer = new Date(i.tag_answer);
                                //     }
                                // })
                                angular.forEach($scope.tagsInfo, function (i, o) {
                                    angular.forEach(i.tag_details, function (j, k) {
                                        if (j.tag_type == 'date') {
                                            j.tag_answer = dateFilter(j.tag_answer, 'yyyy-MM-dd');
                                        }
                                    });
                                });
            
                            } else {
                                $rootScope.toast('Error', result.error, 'error');
                            }
                        });
                    }

                    //tags service close//


                    //spend management starts//
                    contractService.getSpendMgmt({ 'id_contract': $scope.contract_id }).then(function (result) {
                        if (result.status) {
                            $scope.contractInfo = result.data[0];
                            // $scope.spendMgmtGraph.graph = result.graph;
                        }
                    });

                    $scope.updateSpendMngmt = function (data) {
                        params = data;
                        params.updated_by = $scope.user.id_user;
                        params.id_contract = $scope.contract_id;
                        contractService.updateSpendMgmt(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.getInfo();
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Spend$$Lines$$(' + data.action_item + ')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.init();
                                $scope.getInfo();
                                //$scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error, 'error');
                            }
                        });
                    }

                    //spend management close//

                    //obligations starts//

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
                            // templateUrl:'views/Manage-Users/customer-documents/create-edit-obligations.html',
                            controller: function ($uibModalInstance, $scope, item) {
                                $scope.title = 'general.add';
                                $scope.bottom = 'general.save';

                                projectService.getRecurrences().then(function (result) {
                                    $scope.recurrences = result.data;
                                });

                                projectService.resendRecurrence().then(function (result) {
                                    $scope.resend_recurrences = result.data;
                                });

                                if (item) {
                                    $scope.title = 'general.edit';
                                    projectService.getObligations({ 'contract_id': decode($stateParams.id), 'id_obligation': row.id_obligation }).then(function (result) {
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

                                        if ($scope.obligations.recurrence_start_date) $scope.obligations.recurrence_start_date = moment($scope.obligations.recurrence_start_date).utcOffset(0, false).toDate();
                                        if ($scope.obligations.recurrence_end_date) $scope.obligations.recurrence_end_date = moment($scope.obligations.recurrence_end_date).utcOffset(0, false).toDate();
                                        if ($scope.obligations.email_send_start_date) $scope.obligations.email_send_start_date = moment($scope.obligations.email_send_start_date).utcOffset(0, false).toDate();
                                        if ($scope.obligations.email_send_last_date) $scope.obligations.email_send_last_date = moment($scope.obligations.email_send_last_date).utcOffset(0, false).toDate();



                                        $scope.options = {
                                            minDate: moment().utcOffset(0, false).toDate(),
                                            showWeeks: false
                                        };
                                        $scope.options2 = angular.copy($scope.options);



                                        $scope.options3 = {
                                            minDate: moment().utcOffset(0, false).toDate(),
                                            showWeeks: false
                                        }
                                        $scope.options4 = angular.copy($scope.options3);


                                        var dt12 = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : moment().utcOffset(0, false).toDate());
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



                                        var dt23 = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : moment().utcOffset(0, false).toDate());

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
                                    params.contract_id = $scope.contract_id;
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
                                            $scope.getInfo();
                                            $scope.cancel();
                                            $scope.getObligations($scope.tableStateRef);
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
                                    // console.log(key);
                                    // console.log('calendar',$scope.obligations.calendar);
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
                                    var dt = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : moment().utcOffset(0, false).toDate());
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
                                    minDate: moment().utcOffset(0, false).toDate(),
                                    showWeeks: false
                                };
                                $scope.options2 = angular.copy($scope.options);
                                $scope.getRecurrenceSelected = function (val) {
                                    //console.log(val);
                                    //console.log('calendar',$scope.obligations.calendar);
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
                                    minDate: moment().utcOffset(0, false).toDate(),
                                    showWeeks: false
                                };
                                $scope.options4 = angular.copy($scope.options3);

                                $scope.emailRecurrence = function (info) {
                                    if (info) {
                                        $scope.obligations.email_send_last_date = '';
                                    }
                                    var dts = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : moment().utcOffset(0, false).toDate());
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
                                    // else{
                                    //     $scope.requiredFields =false;
                                    //     $scope.enddateField=false;
                                    //     $scope.requiredNotificationField=false;
                                    // }
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

                    $scope.getObligations = function (tableState) {
                        setTimeout(function () {
                            $scope.tableStateRef = tableState;
                            $scope.obligationLoading = true;
                            var pagination = tableState.pagination;
                            tableState.id_contract = $scope.contract_id;
                            tableState.id_user = $scope.user1.id_user;
                            tableState.user_role_id = $scope.user1.user_role_id;
                            projectService.getObligations(tableState).then(function (result) {
                                // console.log('result info',result);
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

                    //obligation ends//

                    //attachments starts//

                    $scope.uploadAttachment = function (fdata) {
                        // console.log('data1',fdata);
                        $scope.isView = true;
                        var params = {};
                        params.file = fdata.file.attachments;
                        params.module_id = 0;
                        params.module_type = 'contract_review';
                        params.reference_type = 'contract';
                        params.module_id = 0;
                        params.reference_id = $scope.contract_id;
                        params.document_type = 0;
                        params.uploaded_by = $scope.user1.id_user;
                        params.customer_id = $scope.user1.customer_id;
                        contractService.uploaddata(params).then(function (result) {
                            if(result.status){
                                $rootScope.toast('Success',result.message);
                                $scope.isView = false;
                                $scope.getInfo();
                              }
                            else {
                                $scope.isView = false;
                                $rootScope.toast('Error', result.error, 'error');
                            }
                        })
                    }

                    $scope.deleteAttachment = function (id, name) {
                        var r = confirm($filter('translate')('general.alert_continue'));
                        $scope.deleConfirm = r;
                        if (r == true) {
                            var params = {};
                            params.id_document = id;
                            attachmentService.deleteAttachments(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    // var obj = {};
                                    // obj.action_name = 'delete';
                                    // obj.action_description = 'delete$$Attachment$$('+name+')';
                                    // obj.module_type = $state.current.activeLink;
                                    // obj.action_url = $location.$$absUrl;
                                    // $rootScope.confirmNavigationForSubmit(obj);
                                    // $scope.init();
                                    $scope.getInfo();
                                } else { $rootScope.toast('Error', result.error, 'error'); }
                            })
                        }
                    }

                    $scope.verifyLink = function (data) {
                        // console.log("verify data is:",data)
                        if (data != {}) {
                            $scope.contractLinks.push(data);
                            $scope.contractLink = {};
                        }
                    }
                    $scope.removeLink = function (index) {
                        var r = confirm($filter('translate')('general.alert_continue'));
                        if (r == true) {
                            $scope.contractLinks.splice(index, 1);
                        }
                    }
                    $scope.uploadLinks = function (contractLinks) {
                        //console.log('links2',contractLinks);
                        $scope.isLink =true;
                        var file = contractLinks;
                        if (contractLinks) {
                            Upload.upload({
                                url: API_URL + 'Document/add',
                                data: {
                                    file: contractLinks,
                                    customer_id: $scope.user1.customer_id,
                                    module_id: 0,
                                    module_type: 'contract',
                                    reference_id: $scope.contract_id,
                                    document_type: 1,
                                    reference_type: 'contract',
                                    uploaded_by: $scope.user1.id_user
                                }
                            }).then(function (resp) {
                                if (resp.data.status) {
                                    $rootScope.toast('Success', resp.data.message);
                                    $scope.contractLinks=[];
                                    $scope.isLink = false;
                                    $scope.getInfo();
                                    var obj = {};
                                    obj.action_name = 'upload';
                                    obj.action_description = 'upload$$module$$question$$link$$(' + $stateParams.mName + ')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.cancel();

                                }
                                else $rootScope.toast('Error', resp.data.error, 'error', $scope.user);
                            }, function (resp) {
                                $rootScope.toast('Error', resp.data.error, 'error');
                            }, function (evt) {
                                $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                            });
                        } else {
                            $rootScope.toast('Error', 'No link selected', 'image-error');
                        }
                    }
                    $scope.redirectUrl = function(url){
                        if(url != undefined){
                            var r=confirm($filter('translate')('contract.alert_msg'));
                            if(r==true){
                                url = url.match(/^https?:/) ? url : '//' + url;
                                window.open(url,'_blank');
                            }
                        }
                    };   
                    $scope.getDownloadUrl = function(objData){
                        var fileName = objData.document_source;
                        var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
                        var d = {};
                        d.id_document = objData.id_document;
                        var encryptedPath= objData.encryptedPath;
                        var filePath =API_URL+'Cron/preview?file='+encryptedPath;
                        encodePath =encode(filePath);
                        if(fileExtension=='pdf' || fileExtension=='PDF' ){
                            window.open(window.origin+'/Document/web/preview.html?file='+encodePath+'#page=1');
                        }
                        else{
                            contractService.getUrl(d).then(function (result) {
                                if(result.status){
                                    var obj = {};
                                    obj.action_name = 'download';
                                    obj.action_description = 'download$$attachment$$('+ objData.document_name+')';
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
                                                result.data.url =  GibberishAES.enc(result.data.url, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                                result.data.file =  GibberishAES.enc(result.data.file, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                            }
                                            window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+obj.user_id+'&access_token='+obj.access_token;
                                        }
                                    });
                                }
                            });
                        }
                       
                    };

                    //attachments ends//
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


        //update selected contract start//

        $scope.updateContractFromDocument = function (data) {
            $scope.fdata = {};
            $scope.isView = false;
            $scope.isLink = false;
            $scope.id_document_intelligence = data.id_document_intelligence;
            $scope.contractListModal1 = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                // size: 'lg',
                openedClass: 'right-panel-modal contract-list-popup modal-open',
                templateUrl: 'views/Manage-Users/customer-documents/addContract-list.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.update = false;
                    $scope.bottom = 'general.save';
                    $scope.contractParters = {};
                    $scope.businessUnitList = [];
                    $scope.usersList = [];
                    $scope.can_access = 1;
                    $scope.date_field = '';
                    $scope.date_period = '';
                    $scope.searchFields = {};
                    $scope.business_unit_id = 'All';
                    $scope.relationship_category_id = '';
                    $scope.automatic_prolongation = null;
                    $scope.provider_name = '';
                    $scope.getBUList = function () {
                        var param = {};
                        param.user_role_id = $rootScope.user_role_id;
                        param.id_user = $rootScope.id_user;
                        param.customer_id = $scope.user1.customer_id;
                        param.status = 1;
                        businessUnitService.list(param).then(function (result) {
                            result.data.data.unshift({ 'id_business_unit': 'All', 'bu_name': 'All' });
                            $scope.bussinessUnit = result.data.data;
                        });
                    }
                    $scope.getBUList();
                    $scope.getCategoryList = function () {
                        contractService.getRelationshipCategory({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                            $scope.relationshipCategoryList = result.drop_down;
                        });
                    }
                    $scope.getCategoryList();
                    $scope.contractsList = [];
                    $scope.clear = function () {
                        $scope.created_date = null;
                        $scope.date_period = null;
                        $scope.date_field = null;
                        $scope.business_unit_id = 'All';
                        $scope.relationship_category_id = null;
                        $scope.provider_name = '';
                        $scope.contract_status = null;
                        $scope.end_date_lessthan_90 = null;
                        angular.element('#created_date').removeClass('req-filter');
                    };

                    $scope.selectDate = function (date) {
                        var d = null;
                        $stateParams.end_date = undefined;
                        $stateParams.this_month = undefined;
                        $stateParams.end_month = undefined;
                        if (date) {
                            var element = angular.element('#created_date');
                            element.removeClass("req-filter");
                            element.addClass('active-filter');
                            d = dateFilter(date, 'yyyy-MM-dd');
                            if ($scope.date_field)
                                $scope.tableStateRef.date_field = $scope.date_field;
                            else {
                                $scope.date_field = 'created_on';
                                $scope.tableStateRef.date_field = $scope.date_field;
                            }
                            if ($scope.date_period)
                                $scope.tableStateRef.date_period = $scope.date_period;
                            else {
                                $scope.date_period = '=';
                                $scope.tableStateRef.date_period = $scope.date_period;
                            }
                        }
                        $scope.tableStateRef.created_date = d;
                        $scope.resetPagination = true;
                        // console.log("selectDate---------");
                        $scope.callServer($scope.tableStateRef);
                    }
                    $scope.filterDateType = function (val) {
                        $scope.resetPagination = true;
                        $scope.date_field = val;
                        $stateParams.end_date = undefined;
                        $stateParams.this_month = undefined;
                        $stateParams.end_month = undefined;
                        if ($scope.date_period) $scope.tableStateRef.date_period = $scope.date_period;
                        else {
                            $scope.tableStateRef.date_period = '=';
                            $scope.date_period = '=';
                        }
                        if (!$scope.created_date) {
                            angular.element('#created_date').addClass('req-filter');
                        }
                        $scope.tableStateRef.date_field = val;
                        if ($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_date) {
                            $scope.callServer($scope.tableStateRef);
                        }
                    }
                    $scope.filterDatePeriod = function (val) {
                        $scope.resetPagination = true;
                        $scope.date_period = val;
                        $stateParams.end_date = undefined;
                        $stateParams.this_month = undefined;
                        $stateParams.end_month = undefined;
                        if ($scope.date_field) $scope.tableStateRef.date_field = $scope.date_field;
                        else {
                            $scope.tableStateRef.date_field = 'created_on';
                            $scope.date_field = 'created_on';
                        }
                        if (!$scope.created_date) {
                            angular.element('#created_date').addClass('req-filter');
                        }
                        $scope.tableStateRef.date_period = val;
                        if ($scope.tableStateRef.date_period && $scope.tableStateRef.date_field && $scope.tableStateRef.created_date) {
                            // console.log("filterDatePeriod---------");
                            $scope.callServer($scope.tableStateRef);
                        }
                    }

                    $scope.callServer = function (tableState) {
                        $scope.filtersData = {};
                        $scope.isLoading = true;
                        var pagination = tableState.pagination;
                        tableState.customer_id = $scope.user1.customer_id;
                        tableState.id_user = $scope.user1.id_user;
                        tableState.user_role_id = $scope.user1.user_role_id;
                        tableState.can_access = $scope.can_access;

                        if ($stateParams.pname == undefined &&
                            $stateParams.status == undefined &&
                            $stateParams.end_date == undefined &&
                            $stateParams.this_month == undefined &&
                            $stateParams.end_month == undefined &&
                            $stateParams.automatic_prolongation == undefined) {
                        } else {
                            if ($stateParams.pname) {
                                $scope.contractsList = [];
                                if (tableState.provider_name != undefined) { }
                                else {
                                    $scope.provider_name = $stateParams.pname;
                                }
                                $scope.contract_status = 'all';
                                $scope.resetPagination = true;
                                tableState.sort = {};
                            }
                            if ($stateParams.status) {
                                if ($scope.contract_status != $stateParams.status) { }
                                else $scope.contract_status = $stateParams.status;
                            }
                            if ($stateParams.end_date) {
                                if ($scope.created_date == null ||
                                    $scope.created_date == undefined ||
                                    $scope.created_date == '') {
                                    $scope.end_date_lessthan_90 = $stateParams.end_date;
                                    $scope.date_field = "contract_end_date";
                                    $scope.date_period = "<=";
                                    $scope.created_date = moment().utcOffset(0, false).toDate();
                                    $scope.created_date.setDate($scope.created_date.getDate() + 90);
                                }
                            }
                            if ($stateParams.this_month) {
                                if ($scope.created_date == null ||
                                    $scope.created_date == undefined ||
                                    $scope.created_date == '') {
                                    $scope.created_this_month = $stateParams.this_month;
                                    $scope.date_field = "created_on";
                                    $scope.date_period = ">=";
                                    $scope.created_date = moment().utcOffset(0, false).toDate();
                                    var date1 = moment().utcOffset(0, false).toDate();
                                    var firstDay = moment(date1.getFullYear(), date1.getMonth(), 1).utcOffset(0, false).toDate();
                                    $scope.created_date = moment(firstDay).utcOffset(0, false).toDate();
                                }
                            }
                            if ($stateParams.end_month) {
                                if ($scope.created_date == null ||
                                    $scope.created_date == undefined ||
                                    $scope.created_date == '') {
                                    $scope.ending_this_month = $stateParams.end_month;
                                    $scope.date_field = 'contract_end_date';
                                    $scope.date_period = '<=';
                                    var date2 = moment().utcOffset(0, false).toDate();
                                    var lastDay = moment(date2.getFullYear(), date2.getMonth() + 1, 0).utcOffset(0, false).toDate();
                                    $scope.created_date = moment(lastDay).utcOffset(0, false).toDate();
                                }
                            }
                            if ($stateParams.automatic_prolongation) {
                                if ($scope.automatic_prolongation != null) { }
                                else {
                                    $scope.automatic_prolongation = $stateParams.automatic_prolongation;
                                }
                            }
                        }
                        tableState.date_period = $scope.date_period;
                        tableState.date_field = $scope.date_field;
                        tableState.business_unit_id = angular.copy($scope.business_unit_id);
                        if ($scope.relationship_category_id && $scope.relationship_category_id != null) {
                            tableState.relationship_category_id = $scope.relationship_category_id;
                        } else delete tableState.relationship_category_id;
                        if ($scope.provider_name != '' && $scope.provider_name != null && $scope.provider_name != undefined) {
                            tableState.provider_name = $scope.provider_name;
                        } else {
                            delete tableState.provider_name;
                            $scope.provider_name = '';
                        }
                        if ($scope.end_date_lessthan_90 && $scope.end_date_lessthan_90 != null) {
                            tableState.end_date_lessthan_90 = $scope.end_date_lessthan_90;
                        } else {
                            delete tableState.end_date_lessthan_90;
                            $scope.end_date_lessthan_90 = '';
                        }
                        if ($scope.contract_status && $scope.contract_status != null) {
                            if ($scope.contract_status != 'all')
                                tableState.contract_status = $scope.contract_status;
                            else delete tableState.contract_status;
                        } else {
                            delete tableState.contract_status;
                            $scope.contract_status = '';
                        }
                        if ($scope.created_this_month && $scope.created_this_month != null) {
                            tableState.created_this_month = $scope.created_this_month;
                        } else {
                            delete tableState.created_this_month;
                            $scope.created_this_month = null;
                        }
                        if ($scope.ending_this_month && $scope.ending_this_month != null) {
                            tableState.ending_this_month = $scope.ending_this_month;
                        } else {
                            delete tableState.ending_this_month;
                            $scope.ending_this_month = null;
                        }
                        if ($scope.automatic_prolongation && $scope.automatic_prolongation != null) {
                            tableState.automatic_prolongation = $scope.automatic_prolongation;
                        } else {
                            delete tableState.automatic_prolongation;
                            $scope.automatic_prolongation = null;
                        }
                        tableState.overview = true;
                        if ($scope.resetPagination) {
                            tableState.pagination = {};
                            tableState.pagination.start = '0';
                            tableState.pagination.number = '10';
                        }
                        if ($scope.created_date == null || $scope.created_date == undefined || $scope.created_date == '') {
                            delete tableState.date_period;
                            delete tableState.date_field;
                            $scope.date_period = '';
                            $scope.date_field = '';
                        } else {
                            tableState.created_date = dateFilter($scope.created_date, 'yyyy-MM-dd');
                        }
                        if (tableState.advancedsearch_get) { }
                        else tableState.advancedsearch_get = {};
                        $scope.tableStateRef = tableState;
                        contractService.allContractsList(tableState).then(function (result) {
                            $scope.contractsList = [];
                            $scope.contractsList = result.data.data;
                            $scope.emptyTable = false;
                            $scope.displayCount = $rootScope.userPagination;
                            $scope.totalRecords = result.data.total_records;
                            tableState.pagination.numberOfPages = Math.ceil(result.data.total_records / $rootScope.userPagination);
                            $scope.isLoading = false;
                            $scope.getCategoryList();
                            $scope.getBUList();
                            $scope.getProviderList(tableState.business_unit_id);
                            $scope.provider_name = tableState.provider_name;
                            $scope.resetPagination = false;
                            if (result.data.total_records < 1)
                                $scope.emptyTable = true;
                        });

                    }
                    $scope.defaultPages = function (val) {
                        userService.userPageCount({ 'display_rec_count': val }).then(function (result) {
                            if (result.status) {
                                $rootScope.userPagination = val;
                                $scope.resetPagination = true;
                                $scope.callServer($scope.tableStateRef);
                            }
                        });
                    }

                    $scope.cancel1 = function () {
                        $uibModalInstance.close();
                    };
                    $scope.cancel1();

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
            $scope.contractListModal1.result.then(function ($data) {
            }, function () {
            });

        }


        $scope.updateSelectedOnlyContract = function (details) {
            $scope.contract_id = details.contract_id;
            if($scope.contractListModal1){
                $scope.contractListModal1.close();
              }            

            $scope.contractLinks = [];
            $scope.contractLink = {};
            $scope.disableTab = true;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                size: 'lg',
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/Manage-Users/customer-documents/update-document-contract.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.bottom = 'Connect';
                    $scope.connect = true;
                    documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Contract Information' }).then(function (result) {
                        $scope.contractInformationAnswer = result.data.approvedOrEdited;
                        $scope.contractInformationReject = result.data.rejectedAnswers;
                    });

                    documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Contract Tag' }).then(function (result) {
                        $scope.contractTagAnswer = result.data.approvedOrEdited;
                        $scope.contractTagReject = result.data.rejectedAnswers;
                    });

                    documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Contract Value' }).then(function (result) {
                        $scope.contractValueAnswer = result.data.approvedOrEdited;
                        $scope.contractValueReject = result.data.rejectedAnswers;
                    });
                    $scope.obligation3 = function () {

                        documentService.getIntelligenceAnswerList({ 'document_intelligence_id': $scope.id_document_intelligence, 'field_type': 'Obligation,Right' }).then(function (result) {
                            $scope.contractObligationAnswer = result.data.approvedOrEdited;
                            $scope.contractObligationReject = result.data.rejectedAnswers;
                            $scope.totalObligation = result.data.approved_records_count;

                        });
                    }
                    $scope.obligation3();


                    $scope.moveAttachment2 = function () {
                        documentService.getDocumentIntelligence({ 'id_document_intelligence': $scope.id_document_intelligence, 'customer_id': $scope.user1.customer_id }).then(function (result) {
                            $scope.contractAttachmentAnswer = result.data.data[0];
                            // console.log("att",$scope.contractAttachmentAnswer.original_document_name);
                        });
                    }
                    $scope.moveAttachment2();

                   
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
                    contractService.getRelationshipCategory({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                        $scope.relationshipCategoryList = result.drop_down;
                    });


                    masterService.currencyList({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                        $scope.currencyList = result.data;
                    });
                    templateService.list().then(function (result) {
                        $scope.templateList = result.data.data;
                    });
                    providerService.list({ 'customer_id': $scope.user1.customer_id, 'status': 1, 'all_providers': true }).then(function (result) {
                        $scope.providers = result.data.data;
                    });

                    $scope.moveAttachments = function (data, isoriginal) {
                        // console.log(data);
                        var params = {};
                        params.reference_id = data.contract_id;
                        params.id_document_intelligence = data.id_document_intelligence;
                        if (isoriginal) {
                            params.name = data.original_document_name;
                            params.path = data.original_document_path;
                        }
                        else {
                            params.name = data.ocr_document_name;
                            params.path = data.ocr_document_path;
                            params.is_ocr = 1;
                        }
                        params.reference_type = 'contract';
                        params.module_type = 'document_intelligence';
                        params.module_id = data.id_document_intelligence;
                        // console.log("attachment",params)

                        documentService.attachmentMove(params).then(function (result) {
                            // console.log("result is",result)
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.moveAttachment2();
                                $scope.getInfo();
                            } else {
                                //$rootScope.toast('Error', 'failed', 'error');
                                $rootScope.toast('Error', result.error);
                            }
                        });
                    }
                    $scope.moveAll = function (data) {
                        // console.log('1',data);
                        projectService.moveAllObligation({ 'id_document_intelligence': $scope.id_document_intelligence }).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.obligation3();
                                $scope.getObligations($scope.tableStateRef);
                                $scope.getInfo();
                            } else {
                                $rootScope.toast('Error', result.error, 'error');

                            }
                        });
                    }

                    $scope.move = function (data) {
                        var params = {};
                        params.contract_id = $scope.contract_id;
                        params.description = data.field_name;
                        if (data.field_type == 'Right') {
                            params.type = 1;
                        }
                        else if (data.field_type == 'Obligation') {
                            params.type = 0;
                        }
                        params.detailed_description = data.options[0];
                        params.id_document_fields = data.id_document_fields;
                        projectService.addObligations(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.obligation3();
                                $scope.getObligations($scope.tableStateRef);
                                $scope.getInfo();
                            } else {
                                $rootScope.toast('Error', result.error, 'error');

                            }
                        });
                    }

                    $scope.getContractDelegates = function (id, contractId) {
                        contractService.getDelegates({ 'id_business_unit': id, 'contract_id': details.contract_id }).then(function (result) {
                            $scope.delegates = result.data;
                        });
                        var params = {};
                        params.type = "buowner";
                        params.business_unit_id = id;
                        params.contract_id = contractId;
                        contractService.getbuOwnerUsers(params).then(function (result) {
                            $scope.buOwnerUsers = result.data;
                        });
                    }

                    $scope.getBUList = function () {
                        var param = {};
                        param.user_role_id = $rootScope.user_role_id;
                        param.id_user = $rootScope.id_user;
                        param.customer_id = $scope.user1.customer_id;
                        param.status = 1;
                        businessUnitService.list(param).then(function (result) {
                            result.data.data.unshift({ 'id_business_unit': 'All', 'bu_name': 'All' });
                            $scope.bussinessUnit = result.data.data;
                        });
                    }
                    $scope.getBUList();
                    $scope.changeLockingStatus = function(info){
                        var params={};
                              params.id_document = info.id_document;
                        contractService.lockingStatus(params).then(function(result){
                          if(result.status){
                                    $rootScope.toast('Success', result.message);
                                    $scope.getInfo();
                                    $scope.init();
                          }
                        });
                        }

                    $scope.getProviderUserList = function () {
                        var reqObj = {};
                        reqObj.user_type = 'external';
                        reqObj.status = 1;
                        reqObj.customer_id = $scope.user1.customer_id;
                        reqObj.id_provider = $scope.contractInfo.provider_name;
                        reqObj.user_role_id = $scope.user1.user_role_id;
                        reqObj.id_user = $scope.user1.id_user;
                        reqObj.contract_id = $scope.contract_id;
                        customerService.getUserList(reqObj).then(function (result) {
                            $scope.providerUsersList = result.data.data;
                        });
                    }
                    $scope.updateLockingStatus = function (id) {
                        $scope.infoObj.is_template_lock = id;
                        if (id) {
                            $scope.lock = true;
                        }
                        else {
                            $scope.lock = false;
                        }
                    }

                    $scope.resetLockingStatus = function (id) {
                        $scope.infoObj.is_template_lock = id;
                        if (id) {
                            $scope.lock = false;
                        }
                        else {
                            $scope.lock = true;
                        }
                    }

                    $scope.getInfo = function () {
                        var par = {};
                        par.id_contract = $scope.contract_id;
                        par.id_user = $scope.user1.id_user;
                        par.user_role_id = $scope.user1.user_role_id;
                        par.is_workflow = 0;
                        contractService.getContractById(par).then(function (result) {
                            if (result.status) {
                                $scope.infoObj = result.data[0];
                                if ($scope.infoObj.is_template_lock == 1) {
                                    $scope.lock = true;
                                }
                                else {
                                    $scope.lock = false;
                                }
                                $scope.contract_attachments = result.contract_attachments;
                                $scope.contract_information = result.contract_information;
                                $scope.contract_tags = result.contract_tags;
                                $scope.contract_spent_managment = result.contract_spent_managment;
                                $scope.obligationCount=result.obligations_count;
                                $scope.infoObj.contract_start_date = moment($scope.infoObj.contract_start_date).utcOffset(0, false).toDate();
                                if($scope.infoObj.contract_end_date)$scope.infoObj.contract_end_date = moment($scope.infoObj.contract_end_date).utcOffset(0, false).toDate();                                
                                $scope.getContractDelegates($scope.infoObj.business_unit_id, $scope.infoObj.id_contract);
                                if ($scope.infoObj.can_review == 1)
                                    $scope.enableTemplate = true;
                                else $scope.enableTemplate = false;
                            }
                        });
                    }
                    $scope.getInfo();


                    $scope.updateContractInfo = function (data) {
                        var postData = angular.copy(data);
                        delete postData.contract_unique_id;
                          delete postData.unique_attachment;
                        delete postData.attachments;
                        delete postData.action_items;
                        postData.contract_start_date = dateFilter(data.contract_start_date, 'yyyy-MM-dd');
                        if(data.contract_end_date!=null){
                            postData.contract_end_date = dateFilter(data.contract_end_date,'yyyy-MM-dd');
                        }
                        else{
                            postData.contract_end_date='';
                        }
                        
                        postData.customer_id = $scope.user1.customer_id;
                        postData.id_document_intelligence = $scope.id_document_intelligence;
                        console.log(postData);
                        Upload.upload({
                            url: API_URL + 'Contract/update',
                            data: {
                                'contract': postData,
                            }
                        })
                            .then(function (resp) {
                                if (resp.data.status) {
                                    $rootScope.toast('Success', resp.data.message);
                                    $scope.moveAttachment2();
                                    $scope.getInfo();
                                    $scope.disableTab = false;
                                    $scope.bottom = 'general.update';
                                    $scope.connect =false;
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$contract$$' + postData.contract_name;
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.init();
                                    $scope.getInfo();
                                    //$uibModalInstance.close();
                                } else {
                                    $rootScope.toast('Error', resp.data.error, 'error', $scope.contract);
                                }
                            }, function (resp) {
                                $rootScope.toast('Error', resp.error);
                            });

                    }



                    $scope.tagsData = function () {
                        tagService.getContractTags({ 'id_contract': $scope.contract_id, 'tag_type': 'contract_tags' }).then(function (result) {
                            if (result.status) {
                                $scope.tagsdata = [];
                                $scope.tagsdata = result.data;
                                angular.forEach($scope.tagsdata, function (i, o) {
                            angular.forEach(i.tag_details, function (j, k) {
                                    if (j.tag_type == 'date') {
                                        j.tag_answer = moment(j.tag_answer).utcOffset(0, false).toDate();
                                    }
                                })
                            });

                            } else { $rootScope.toast('Error', result.error, 'error', $scope.contract); }

                        });
                    }


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
                

                    $scope.tagsOptions = {
                        minDate: moment().utcOffset(0, false).toDate(),
                        showWeeks: false
                    };
                    $scope.tagsData();

                    $scope.updateTags = function (data) {
                        var params = {};
                        params.id_contract = $scope.contract_id;
                        params.tag_type = 'contract_tags';
                        angular.forEach(data, function (i, o) {
                            angular.forEach(i.tag_details, function (j, k) {
                                if (j.tag_type == 'date') {
                                    j.tag_answer = dateFilter(j.tag_answer, 'yyyy-MM-dd');
                                }
                            });
                        });
                        params.contract_tags = data;
                        tagService.updateContractTags(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Contract$$Tags$$(' + $stateParams.name + ')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.tagsData();
                                $scope.getInfo();
                                angular.forEach($scope.tagsInfo, function (i, o) {
                                    angular.forEach(i.tag_details, function (j, k) {
                                        if (j.tag_type == 'date') {
                                            j.tag_answer = dateFilter(j.tag_answer, 'yyyy-MM-dd');
                                        }
                                    });
                                });
                            } else {
                                $rootScope.toast('Error', result.error, 'error');
                            }
                        });
                    }

                    //tags service close//


                    //spend management starts//
                    contractService.getSpendMgmt({ 'id_contract': $scope.contract_id }).then(function (result) {
                        if (result.status) {
                            $scope.contractInfo = result.data[0];
                            // $scope.spendMgmtGraph.graph = result.graph;
                        }
                    });

                    $scope.updateSpendMngmt = function (data) {
                        params = data;
                        params.updated_by = $scope.user.id_user;
                        params.id_contract = $scope.contract_id;
                        contractService.updateSpendMgmt(params).then(function (result) {
                            if (result.status) {
                                $rootScope.toast('Success', result.message);
                                $scope.getInfo();
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Spend$$Lines$$(' + data.action_item + ')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.init();
                                $scope.getInfo();
                                //$scope.cancel();
                            } else {
                                $rootScope.toast('Error', result.error, 'error');
                            }
                        });
                    }

                    //spend management close//

                    //obligations starts//

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
                            // templateUrl:'views/Manage-Users/customer-documents/create-edit-obligations.html',
                            controller: function ($uibModalInstance, $scope, item) {
                                $scope.title = 'general.add';
                                $scope.bottom = 'general.save';

                                projectService.getRecurrences().then(function (result) {
                                    $scope.recurrences = result.data;
                                });

                                projectService.resendRecurrence().then(function (result) {
                                    $scope.resend_recurrences = result.data;
                                });

                                if (item) {
                                    $scope.title = 'general.edit';
                                    projectService.getObligations({ 'contract_id': decode($stateParams.id), 'id_obligation': row.id_obligation }).then(function (result) {
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

                                        if ($scope.obligations.recurrence_start_date) $scope.obligations.recurrence_start_date = moment($scope.obligations.recurrence_start_date).utcOffset(0, false).toDate();
                                        if ($scope.obligations.recurrence_end_date) $scope.obligations.recurrence_end_date = moment($scope.obligations.recurrence_end_date).utcOffset(0, false).toDate();
                                        if ($scope.obligations.email_send_start_date) $scope.obligations.email_send_start_date = moment($scope.obligations.email_send_start_date).utcOffset(0, false).toDate();
                                        if ($scope.obligations.email_send_last_date) $scope.obligations.email_send_last_date = moment($scope.obligations.email_send_last_date).utcOffset(0, false).toDate();



                                        $scope.options = {
                                            minDate: moment().utcOffset(0, false).toDate(),
                                            showWeeks: false
                                        };
                                        $scope.options2 = angular.copy($scope.options);



                                        $scope.options3 = {
                                            minDate: moment().utcOffset(0, false).toDate(),
                                            showWeeks: false
                                        }
                                        $scope.options4 = angular.copy($scope.options3);


                                        var dt12 = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : moment().utcOffset(0, false).toDate());
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



                                        var dt23 = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : moment().utcOffset(0, false).toDate());

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
                                    params.contract_id = $scope.contract_id;
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
                                            $scope.getInfo();
                                            $scope.getObligations($scope.tableStateRef);

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
                                    // console.log(key);
                                    // console.log('calendar',$scope.obligations.calendar);
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
                                    var dt = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : moment().utcOffset(0, false).toDate());
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
                                    minDate: moment().utcOffset(0, false).toDate(),
                                    showWeeks: false
                                };
                                $scope.options2 = angular.copy($scope.options);
                                $scope.getRecurrenceSelected = function (val) {
                                    //console.log(val);
                                    //console.log('calendar',$scope.obligations.calendar);
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
                                    minDate: moment().utcOffset(0, false).toDate(),
                                    showWeeks: false
                                };
                                $scope.options4 = angular.copy($scope.options3);

                                $scope.emailRecurrence = function (info) {
                                    if (info) {
                                        $scope.obligations.email_send_last_date = '';
                                    }
                                    var dts = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : moment().utcOffset(0, false).toDate());
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
                                    // else{
                                    //     $scope.requiredFields =false;
                                    //     $scope.enddateField=false;
                                    //     $scope.requiredNotificationField=false;
                                    // }
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

                    $scope.getObligations = function (tableState) {
                        setTimeout(function () {
                            $scope.tableStateRef = tableState;
                            $scope.obligationLoading = true;
                            var pagination = tableState.pagination;
                            tableState.id_contract = $scope.contract_id;
                            tableState.id_user = $scope.user1.id_user;
                            tableState.user_role_id = $scope.user1.user_role_id;
                            projectService.getObligations(tableState).then(function (result) {
                                // console.log('result info',result);
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

                    //obligation ends//

                    //attachments starts//

                    $scope.uploadAttachment = function (fdata) {
                        // console.log('data2',fdata);
                        $scope.isView = true;
                        var params = {};
                        params.file = fdata.file.attachments;;
                        params.module_id = 0;
                        params.module_type = 'contract_review';
                        params.reference_type = 'contract';
                        params.module_id = 0;
                        params.reference_id = $scope.contract_id;
                        params.document_type = 0;
                        params.uploaded_by = $scope.user1.id_user;
                        params.customer_id = $scope.user1.customer_id;
                        contractService.uploaddata(params).then(function (result) {
                            if (result.status) {
                                $scope.isView = false;
                                $rootScope.toast('Success', result.message);
                                $scope.getInfo();
                            }
                            else {
                                $scope.isView = false;
                                $rootScope.toast('Error', result.error, 'error');
                            }
                        })
                    }

                    $scope.deleteAttachment = function (id, name) {
                        var r = confirm($filter('translate')('general.alert_continue'));
                        $scope.deleConfirm = r;
                        if (r == true) {
                            var params = {};
                            params.id_document = id;
                            attachmentService.deleteAttachments(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    // var obj = {};
                                    // obj.action_name = 'delete';
                                    // obj.action_description = 'delete$$Attachment$$('+name+')';
                                    // obj.module_type = $state.current.activeLink;
                                    // obj.action_url = $location.$$absUrl;
                                    // $rootScope.confirmNavigationForSubmit(obj);
                                    // $scope.init();
                                    $scope.getInfo();
                                } else { $rootScope.toast('Error', result.error, 'error'); }
                            })
                        }
                    }


                    $scope.verifyLink = function (data) {
                        // console.log("verify data is:",data)
                        if (data != {}) {
                            $scope.contractLinks.push(data);
                            $scope.contractLink = {};
                        }
                    }
                    $scope.removeLink = function (index) {
                        var r = confirm($filter('translate')('general.alert_continue'));
                        if (r == true) {
                            $scope.contractLinks.splice(index, 1);
                        }
                    }
                    $scope.uploadLinks = function (contractLinks) {
                        //console.log('link3',contractLinks)
                        $scope.isLink=true;
                        var file = contractLinks;
                        if (contractLinks) {
                            Upload.upload({
                                url: API_URL + 'Document/add',
                                data: {
                                    file: contractLinks,
                                    customer_id: $scope.user1.customer_id,
                                    module_id: 0,
                                    module_type: 'contract',
                                    reference_id: $scope.contract_id,
                                    document_type: 1,
                                    reference_type: 'contract',
                                    uploaded_by: $scope.user1.id_user
                                }
                            }).then(function (resp) {
                                if (resp.data.status) {
                                    $scope.isLink=false;
                                    $rootScope.toast('Success', resp.data.message);
                                    $scope.contractLinks=[];
                                    $scope.isLink = false;
                                    $scope.getInfo();
                                    var obj = {};
                                    obj.action_name = 'upload';
                                    obj.action_description = 'upload$$module$$question$$link$$(' + $stateParams.mName + ')';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.cancel();

                                }
                                else $rootScope.toast('Error', resp.data.error, 'error', $scope.user);
                            }, function (resp) {
                                $rootScope.toast('Error', resp.data.error, 'error');
                            }, function (evt) {
                                $scope.progress = parseInt(100.0 * evt.loaded / evt.total);
                            });
                        } else {
                            $rootScope.toast('Error', 'No link selected', 'image-error');
                        }
                    }
                    $scope.redirectUrl = function(url){
                        if(url != undefined){
                            var r=confirm($filter('translate')('contract.alert_msg'));
                            if(r==true){
                                url = url.match(/^https?:/) ? url : '//' + url;
                                window.open(url,'_blank');
                            }
                        }
                    };   
                    $scope.getDownloadUrl = function(objData){
                        var fileName = objData.document_source;
                        var fileExtension = fileName.substr((fileName.lastIndexOf('.') + 1));
                        var d = {};
                        d.id_document = objData.id_document;
                        var encryptedPath= objData.encryptedPath;
                        var filePath =API_URL+'Cron/preview?file='+encryptedPath;
                        encodePath =encode(filePath);
                        if(fileExtension=='pdf' || fileExtension=='PDF' ){
                            window.open(window.origin+'/Document/web/preview.html?file='+encodePath+'#page=1');
                        }
                        else{
                            contractService.getUrl(d).then(function (result) {
                                if(result.status){
                                    var obj = {};
                                    obj.action_name = 'download';
                                    obj.action_description = 'download$$attachment$$('+ objData.document_name+')';
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
                                                result.data.url =  GibberishAES.enc(result.data.url, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                                result.data.file =  GibberishAES.enc(result.data.file, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                                            }
                                            window.location = API_URL+'download/downloadreportnew?id_download='+result.data+'&user_id='+obj.user_id+'&access_token='+obj.access_token;
                                        }
                                    });
                                }
                            });
                        }
                       
                    };

                    //attachments ends//
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

        $scope.validate = function (info,type) {
            var encryptedPath = info.encrypted_ocr_document_path;
            if(encryptedPath ==''||encryptedPath==null)
            {
                type = '';
            }
            if(type=='ocr'){
            //if ocr path available this block executed
                var is_ocr =1; 
                encryptedPath=info.encrypted_ocr_document_path;
                var is_document_intelligence =1; 
                var filePath = API_URL + 'Cron/preview?file=' + encryptedPath + '&is_ocr='+ is_ocr+'&is_document_intelligence='+is_document_intelligence;
                var documentIntelligenceName = info.ocr_display_name;
            }
            else{

                //if ocr path not available this block executed
                var is_document_intelligence =1; 
                var filePath = API_URL + 'Cron/preview?file=' + info.encrypted_original_document_path +'&is_document_intelligence='+is_document_intelligence ;
                var documentIntelligenceName = info.original_document_name;
 
            }
            encodePath = encode(filePath); 
            $state.go('app.customer-documents.side-by-side', { name: encodePath, id: info.id_document_intelligence,documentName:documentIntelligenceName, statusValidate:info.validate_status });
        }
       
        $scope.showOcr1 = false;
        $scope.ocrFunction1 = function () {
            $scope.showOcr1 = !$scope.showOcr1;
            var parent = document.getElementById("documetOcr1");
            var parent1 = document.getElementById("arrow-icon-ocr1");
            if ($scope.showOcr1) {
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                //$scope.widgetinfo();
            } else {
                parent.classList.remove('showDivMenu');
                parent1.className = "fa fa-angle-double-down";
            }
        }

        $scope.showOcr2 = false;
        $scope.ocrFunction2 = function () {
            $scope.showOcr2 = !$scope.showOcr2;
            var parent = document.getElementById("documetOcr2");
            var parent1 = document.getElementById("arrow-icon-ocr2");
            if ($scope.showOcr2) {
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                //$scope.widgetinfo();
            } else {
                parent.classList.remove('showDivMenu');
                parent1.className = "fa fa-angle-double-down";
            }
        }

        $scope.showOcr3 = false;
        $scope.ocrFunction3 = function () {
            $scope.showOcr3 = !$scope.showOcr3;
            var parent = document.getElementById("documetOcr3");
            var parent1 = document.getElementById("arrow-icon-ocr3");
            if ($scope.showOcr3) {
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                //$scope.widgetinfo();
            } else {
                parent.classList.remove('showDivMenu');
                parent1.className = "fa fa-angle-double-down";
            }
        }

        $scope.showOcr4 = false;
        $scope.ocrFunction4 = function () {
            $scope.showOcr4 = !$scope.showOcr4;
            var parent = document.getElementById("documetOcr4");
            var parent1 = document.getElementById("arrow-icon-ocr4");
            if ($scope.showOcr4) {
                parent.classList.add('showDivMenu');
                parent1.className = "fa fa-angle-double-up";
                //$scope.widgetinfo();
            } else {
                parent.classList.remove('showDivMenu');
                parent1.className = "fa fa-angle-double-down";
            }
        }
    })

