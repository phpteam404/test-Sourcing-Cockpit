angular.module('app', ['localytics.directives', 'ui.bootstrap'])
.controller('currenciesCtrl', function ($state, $rootScope,$localStorage,$translate, $scope, $uibModal, encode, decode, currencyService, masterService) {
    $scope.showAddBtn = 0;

    // if($localStorage.curUser.data.data.language_iso_code){
    //     $translate.use($localStorage.curUser.data.data.language_iso_code)
    // }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
    //     $translate.use('en');
    // }

    if ($rootScope.access == 'wa' || $rootScope.access == 'ca') {
        $scope.showAddBtn = 1;
    }
    $scope.displayCount = $rootScope.userPagination;
    tableState.customer_id = $scope.user1.customer_id;
    
})
    
 .controller('currenciesCtrl', function ($rootScope,$localStorage,$translate,$scope,$uibModal,masterService,currencyService,userService) {

    
    if($localStorage.curUser.data.data.language_iso_code){
        $translate.use($localStorage.curUser.data.data.language_iso_code)
    }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
        $translate.use('en');
    }
    
    
    $scope.can_access=1;
        $scope.getDefaultCurrency=function(){
            masterService.getCurrency({'customer_id': $scope.user1.customer_id}).then(function(result){
                    $scope.currenciesList = result.data;
                    $scope.mainCurrency = result.main_currency[0];
                    if(result.is_disable_master_currency==1){
                        $scope.disablefields = true;
                    }
                    else{
                        $scope.disablefields = false;
                    }
                });
        }
        $scope.currencyChange= function(id){
            //console.log(id);
            if(id!=undefined){
                currencyService.postCurrency({'customer_id': $scope.user1.customer_id,'new_currency_code': id}).then(function(result){
                    $rootScope.toast('Success', result.message);
                    $scope.getDefaultCurrency();
                });
            }
           
        }
        $scope.getDefaultCurrency();
        
        

        $scope.getCurrencyList = function (tableState){
            setTimeout(function(){
                $scope.tableStateRef = tableState;
                $scope.currencyLoading = true;
                var pagination = tableState.pagination;
                tableState.customer_id  = $scope.user1.customer_id;
                tableState.can_access  = $scope.can_access;        
                masterService.getCurrency(tableState).then (function(result){
                    // console.log('result info',result);
                     $scope.currencyInfo = result.additional_currencies;
                     $scope.currencyCount = result.total_records;
                     console.log($scope.currencyCount)
                    $scope.emptyCurrencyTable=false;
                    $scope.displayCount = $rootScope.userPagination;
                    tableState.pagination.numberOfPages =  Math.ceil(result.total_records / $rootScope.userPagination);
                    $scope.currencyLoading = false;
                    if(result.total_records < 1)
                        $scope.emptyCurrencyTable=true;
                })
            },700);
        }
    
        $scope.defaultPagesCurrency = function(val){
            //console.log(val)
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.getCurrencyList($scope.tableStateRef);
                }                
            });
        }


        $scope.getContractsByAccess=function(val){
            // console.log('val',val);
             $scope.resetPagination=true;
             $scope.can_access = val;     
             $scope.tableStateRef.can_access = val;
            if($scope.tableStateRef.can_access){
                 $scope.getCurrencyList($scope.tableStateRef);  
             }else{
                 delete $scope.tableStateRef.can_access;
                 $scope.getCurrencyList($scope.tableStateRef);  
            }
         }


        $scope.AddCurrencyForm = function(row){
            $scope.selectedRow =row;
            //$scope.currency={};
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'currencyform.html',
                controller: function ($uibModalInstance,$scope,item) {
                    $scope.title ='general.add';
                    $scope.bottom ='general.save';
                    if(item){
                       //console.log('kasi');
                       $scope.isEdit=true;
                        $scope.title ='general.edit';
                        $scope.bottom ='general.update';

                        masterService.getAvailableCurrency({'customer_id': $scope.user1.customer_id,'type':'edit','currency_code':row.currency_name}).then(function(result){
                            $scope.avaiableCurrency = result.availableCurrencies;
                        });

                        var params={};
                        params.id_currency=item.id_currency;
                        currencyService.getAdditionalCurrencyInfo(params).then(function(result){
                            $scope.currency =result.info[0];
                        })
                           

                    }
                    

                    if(!item){
                        masterService.getAvailableCurrency({'customer_id': $scope.user1.customer_id}).then(function(result){
                            $scope.avaiableCurrency = result.availableCurrencies;
                        });
                    }
                 


                 
                 $scope.disablefieldCurrency = true;
                 $scope.currencyChanges = function(id){
                     angular.forEach($scope.avaiableCurrency, function(obj) {
                        if (obj.currency_full_name == id) {
                          $scope.currency.currency_code = obj.currency_name;
                        }
                        if(id==undefined){
                            $scope.currency.currency_code='';
                        }
                      });
                      
                 }

                 $scope.addCurrency = function(data){
                     var params={};
                     params =data;
                     params.customer_id =$scope.user1.customer_id;
                     if(!item){
                        currencyService.addAdditionalCurrencyData(params).then(function(result){
                            if(result.status){
                                $rootScope.toast('Success', result.message);
                                $scope.cancel();
                                $scope.getDefaultCurrency();
                                $scope.getCurrencyList($scope.tableStateRef);
                            }
                            else{
                                $rootScope.toast('Error',result.error,'error');
                            }
                        })
                     }
                     if(item){
                         currencyService.updateAdditionalCurrencyInfo(params).then(function(result){
                             if(result.status){
                                $rootScope.toast('Success', result.message);
                                $scope.cancel();
                                $scope.getCurrencyList($scope.tableStateRef);
                             }
                             else{
                                $rootScope.toast('Error',result.error,'error');
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

        
        
  }) 