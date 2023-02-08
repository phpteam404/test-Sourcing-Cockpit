'use strict';
angular.module('app')
    .factory('httpLoader', function ($rootScope,$templateCache) {
        var pendingReqs = {};
        $templateCache.removeAll();
        return {
            addPendingReq: function (config) {
                if (config.hasOwnProperty('loaderId')) {
                    if (config.loaderId)
                        $('#' + config.loaderId).fadeIn();
                } else {
                    pendingReqs[config.url] = true;
                }
            },
            subtractPendingReq: function (config) {
                if (config && config.hasOwnProperty('loaderId')) {
                    if (config.loaderId)
                        $('#' + config.loaderId).fadeOut();
                } else {
                    if (config) {
                        delete pendingReqs[config.url];
                    } else {
                        pendingReqs = {};
                    }
                }
            },
            getPendingReqs: function () {
                return sizeOf(pendingReqs);
            }
        }
        function sizeOf(obj) {
            var size = 0,
                key;
            for (key in obj) {
                if (obj.hasOwnProperty(key)) {
                    size++;
                }
            }
            return size;
        }

    })
    .factory('errorInterceptor', function ($q, $rootScope, httpLoader,toastr) {
        toastr.options = {
            showMethod: 'fadeIn',
            preventDuplicates: true,
            timeOut: 3000
        };
        return {
            request: function (config) {
                httpLoader.addPendingReq(config);
                var encrypt = DATA_ENCRYPT;

                if (config.hasOwnProperty('DATA_ENCRYPT'))
                    encrypt = config.DATA_ENCRYPT;
                if (encrypt) {
                    config.headers.User = btoa(config.headers.User + 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                }
                if (config.hasOwnProperty('params') && encrypt) {
                    var actualParams = config.params;
                    config.params = {};
                    config.params.requestData = GibberishAES.enc(JSON.stringify(actualParams), 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                }
                else if (config.hasOwnProperty('data') && encrypt) {
                    if (config.data) {
                        var actualParams = config.data;
                        config.data = {};
                        if (actualParams.hasOwnProperty('file')) {
                            config.data.file = actualParams.file;
                            delete actualParams.file;
                        }
                        config.data.requestData = GibberishAES.enc(JSON.stringify(actualParams), 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx');
                    }
                }
                return config || $q.when(config);
            },
            response: function (response) {
                httpLoader.subtractPendingReq(response.config);

                var encrypt = DATA_ENCRYPT;
                if (response.hasOwnProperty('DATA_ENCRYPT'))
                    encrypt = config.DATA_ENCRYPT

                if (response.data.hasOwnProperty('responseData') && encrypt) {
                    response.data = JSON.parse(GibberishAES.dec(response.data.responseData, 'JKj178jircAPx7h4CbGyYVV6u0A1JF7YN5GfWDWx'));
                }

                return response || $q.when(response);
            },
            responseError: function (response) {
                httpLoader.subtractPendingReq(response.config);

                if (response && response.status === 404) {
                    $rootScope.toast('404', 'Not Found', 'warning');
                }
                if (response && response.status === -1) {
                    $rootScope.toast('Service Connection Error', 'Error while fetching URL', 'warning');
                }
                if (response && response.status === 401) {
                    $rootScope.toast('401', 'session expired', 'warning');
                    $rootScope.$broadcast('loggedOut');
                }
                if (response && response.status >= 500) {
                    $rootScope.toast('Oops!!!', 'Something went wrong. Please try again.', 'warning');
                }
                /*if (response && response.status >= 301) {
                    $rootScope.toast('Oops!!!', 'session expired.', 'warning');
                }*/
                return $q.reject(response);
            }
        };
    })
    .config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.useXDomain = true;
        $httpProvider.defaults.headers.common['Cache-Control'] = 'no-store, no-cache, must-revalidate';
        $httpProvider.defaults.headers.common['Content-Type'] = 'application/json;charset=UTF-8';
        $httpProvider.defaults.headers.common['Content-Security-Policy'] = "default-src 'self'";


        $httpProvider.defaults.cache = false;
        delete $httpProvider.defaults.headers.common['X-Requested-With'];
       // console.log('$httpProvider.defaults', $httpProvider.defaults);
        $httpProvider.interceptors.push('errorInterceptor');
    }])
    .factory('masterService', function ($http) {
        return {
            getCountiresList: function (params){
                return $http.get(API_URL + 'Master/countryList',{'params' : params}).then(function (response){
                    return response.data;
                });
            },
            getUserRole: function (params) {
                return $http.get(API_URL + 'Master/role', {'params': params}).then(function (response) {
                   return response.data;
                });
            },
            getCurrency:function(params){
                return $http.get(API_URL + 'Master/getMasterCurrency', {'params': params}).then(function (response) {
                    return response.data;
                });
            },
           
            getAvailableCurrency:function(params){
                return $http.get(API_URL + 'Master/getAvailableCurrencies', {'params': params}).then(function (response) {
                    return response.data;
                });
            },
            currencyList: function (params) {
                return $http.get(API_URL + 'Master/currencyList', {'params': params}).then(function (response) {
                    return response.data;
                });
            }
        }
    })
    .factory('currencyService',function($http){
        return{
             postCurrency: function (params) {
                return $http.post(API_URL + 'Master/updatemastercurrency', params).then(function (response) {
                    return response.data;
                });
            },
             addAdditionalCurrencyData: function (params) {
                return $http.post(API_URL + 'Master/createAdditionalCurrency', params).then(function (response) {
                    return response.data;
                });
            },
            getAdditionalCurrencyInfo :function(params){
                return $http.get(API_URL+'Master/currencyInfo',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            updateAdditionalCurrencyInfo :function(params){
                return $http.post(API_URL+'Master/updateAddtionalcurency',params).then(function(response){
                    return response.data;
                })
            }
        }
            
    })
    .factory('documentService',function($http,Upload){
        return{
             createTemplate: function (params) {
                return $http.post(API_URL+'Document/createIntelligencetemplate',params).then(function (response) {
                    return response.data;
                });
            },
            getTemplateList :function(params){
                return $http.get(API_URL+'Document/intelligencetemplateLlist',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            templateUpdate:function(params){
                return $http.post(API_URL+'Document/updateDocumemtTemplate',params).then(function(response){
                    return response.data;
                })
            },
            createQuestion: function (params) {
                return $http.post(API_URL+'Document/addTemplatequestions',params).then(function (response) {
                    return response.data;
                });
            },
            templateQuestionsList :function(params){
                return $http.get(API_URL+'Document/templateQuestionList',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            deleteTemplateQuestion :function(params){
                return $http.delete(API_URL + 'Document/deleteQuestion', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            updatetemplateQuestion :function(params){
                return $http.post(API_URL+'Document/updateQuestion',params).then(function (response) {
                    return response.data;
                });
            },
            getTemplatesList :function(params){
                return $http.get(API_URL+'Document/customerTemplates',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            createDocumentIntelligence: function (params) {
                return $http.post(API_URL+'Document/createDocumentIntelligence',params).then(function (response) {
                    return response.data;
                });
            },
            getDocumentIntelligence :function(params){
                // return $http.get(API_URL+'Document/documentIntelligenceList',{'params':params}).then(function(response){
                //     return response.data;
                // })
                return $http({method:'GET',url:API_URL+'Document/documentIntelligenceList','params':params,'loading':false}).then(function(response){
                    return response.data;
                })
            },
            updateDocument :function(params){
                return $http.post(API_URL+'Document/updateDocumemtIntelligence',params).then(function (response) {
                    return response.data;
                });
            },
            deleteDocumentPdf :function(params){
                return $http.delete(API_URL + 'Document/deleteDocumentIntelligence', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            getIntelligenceAnswerList :function(params){
                return $http.get(API_URL+'Document/intelligenceQuestionAnswersList',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            getIntelligenceValidationList :function(params){
                return $http.get(API_URL+'Document/intelligenceValidationAnswersList',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            ocrValidation :function(params){
                return $http.post(API_URL+'Document/saveValidation',params).then(function (response) {
                    return response.data;
                });
            },
            submitValidation :function(params){
                return $http.post(API_URL+'Document/updateDocumentIntelligenceStatus',params).then(function (response) {
                    return response.data;
                });
            },
            attachmentMove :function(params){
                return $http.post(API_URL+'Document/moveDocument',params).then(function (response) {
                    return response.data;
                });
            },
            getInitialTabCount :function(params){
                return $http.get(API_URL+'Master/contractStaticTabsCount',{'params':params}).then(function (response) {
                    return response.data;
                });
            }

        }
            
    })


    .factory('userService', function ($http, $localStorage, $rootScope) {
        return {
            post: function (params) {
                return $http.post(API_URL + 'Signup/login', params).then(function (response) {
                    return response.data;
                });
            },
            signUpCheckmail: function (params) {
                return $http.post(API_URL + 'Signup/checkmail', params).then(function (response) {
                    return response.data;
                });
            },
            sendVerificationCode: function (params) {
                return $http.post(API_URL + 'Signup/sendVerificationCode', params).then(function (response) {
                    return response.data;
                });
            },
            sendMfa: function (params) {
                return $http.post(API_URL + 'Signup/verifyCode', params).then(function (response) {
                    return response.data;
                });
            },


            forgotPassword: function (param) {
                return $http.post(API_URL + 'Signup/forgetPassword', param).then(function (response) {
                    return response.data;
                });
            },
            getUserProfile: function (param) {
                return $http.get(API_URL + 'User/info', { 'params': param }).then(function (response) {
                    return response.data;
                });
            },
            postUserProfile: function (param) {
                return $http.post(API_URL + 'User/update', param).then(function (response) {
                    return response.data;
                });
            },
            changePassword: function (params) {
                return $http.post(API_URL + 'User/changePassword', params).then(function (response) {
                    return response.data;
                });
            },
            accessLogs: function (params) {
                return $http.post(API_URL + 'User/accessLog', params).then(function (response) {
                   return response.data;
                });
            },
            companyDetails: function (params) {
                return $http.get(API_URL + 'Customer/details', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            updateCompany: function (params) {
                return $http.post(API_URL + 'Customer/update', params).then(function (response) {
                   return response.data;
                });
            },
            loginAs: function (params) {
                return $http.get(API_URL + 'user/loginasuser', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            unBlock: function (params) {
                return $http.post(API_URL + 'user/unblock', params).then(function (response) {
                    return response.data;
                });
            },
            accessEntry: function (params) {
                return $http.post(API_URL + 'User/accessLog', params).then(function (response) {
                    return response.data;
                });
            },
            signUp: function (params) {
                return $http.post(API_URL + 'Signup/getEncryptionSettings', params).then(function (response) {
                    return response.data;
                });
            },
            getAccess: function (params) {
                return $http.get(API_URL + 'User/access', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            logout: function (params) {
                return $http.post(API_URL + 'User/logout', params).then(function (response) {
                    return response.data;
                });
            },
            userPageCount: function (params) {
                return $http.post(API_URL + 'User/UserRecorCount', params).then(function(response){
                    if(response.data.status){
                        var obj = {};
                        obj = $localStorage.curUser;
                        obj.data.data.display_rec_count = params.display_rec_count; 
                        $localStorage.curUser = obj;
                        $rootScope.userPagination = params.display_rec_count;
                    }
                    return response.data;
                });
            }
        }
    })
    .factory('customerService', function ($http) {
        return {
            list: function (param) {
                return $http.get(API_URL + 'Customer/list', { params: param }).then(function (response) {
                    return response.data;
                });
            },
            add: function (params) {
                return $http.post(API_URL + 'Customer/add', params).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Customer/update', params).then(function (response) {
                    return response.data;
                });
            },
            delete: function (params) {
                return $http.delete(API_URL + 'Customer/delete', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            getCustomer: function (param) {
                return $http.get(API_URL + 'Customer/info', { params: param }).then(function (response) {
                   return response.data;
                });
            },
            getAdminList: function (param) {
                return $http.get(API_URL + 'Customer/adminList', { 'params': param }).then(function (response) {
                 return response.data;
              });
            },
            postAdmin: function (params) {
                return $http.post(API_URL + 'Customer/admin', params).then(function (response) {
                    return response.data;
                });
            },
            deleteAdmin: function (params) {
                return $http.delete(API_URL + 'Customer/admin', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            getAdminById: function (params) {
                return $http.get(API_URL + 'Customer/admin', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            resetPassword: function (params) {
                return $http.post(API_URL + 'Customer/resetPassword', params).then(function (response) {
                    return response.data;
                });
            },
            getUserList: function (param) {
                return $http.get(API_URL + 'Customer/userList', { 'params': param }).then(function (response) {
                    return response.data;
                });
            },
            postUser: function (params) {
                return $http.post(API_URL + 'Customer/user', params).then(function (response) {
                    return response.data;
                });
            },
            deleteUser: function (params) {
                return $http.delete(API_URL + 'Customer/user', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            getUserById: function (params) {
                return $http.get(API_URL + 'Customer/user', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getTemplates: function (params) {
                return $http.get(API_URL + 'Template/details', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getLDAPCustomer: function (params) {
                return $http.get(API_URL + 'User/ldapdata', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },

            getSAMLCustomer: function (params) {
                return $http.get(API_URL + 'User/samldata', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            
            saveLDAP: function (params) {
                return $http.post(API_URL + 'User/ldap', params).then(function (response) {
                    return response.data;
                });
            },

            saveSAML: function (params) {
                return $http.post(API_URL + 'User/saml', params).then(function (response) {
                    return response.data;
                });
            },

            saveMFA: function (params) {
                return $http.post(API_URL + 'User/mfa', params).then(function (response) {
                    return response.data;
                });
            },

            testLDAP: function (params) {
                return $http.post(API_URL + 'Customer/checkAD', params).then(function (response) {
                    return response.data;
                });
            },
            linkTemplate: function (params) {
                console.log(params);
                return $http.post(API_URL + 'Template/linkTemplateCustomer', params).then(function (response) {
                    return response.data;
                });
            },
            getUserContributions: function (params) {
                return $http.get(API_URL + 'contract/getContributionsResult', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            languageSelection: function (params) {
                return $http.get(API_URL + 'Master/language', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            deleteUsers(params){
                return $http.delete(API_URL + 'Customer/userDelete', { 'params': params }).then(function (response) {
                    return response.data;
                });
            }
        }
    })
    .factory('moduleService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Module/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            add: function (params) {
                return $http.post(API_URL + 'Module/add', params).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Module/update', params).then(function (response) {
                    return response.data;
                });
            },
            delete: function (params) {
                return $http.delete(API_URL + 'Module/delete', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
        }
    })
    .factory('topicService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Topic/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            add: function (params) {
                return $http.post(API_URL + 'Topic/add', params).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Topic/update', params).then(function (response) {
                    return response.data;
                });
            },
            delete: function (params) {
                return $http.delete(API_URL + 'Topic/delete', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getTopicTypes: function (params) {
                return $http.get(API_URL + 'topic/types', { 'params': params }).then(function (response) {
                   return response.data;
                });
            }
        }
    })
    .factory('questionsService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Question/list', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getTopicQuestions: function (params) {
                return $http.get(API_URL + 'Question/topicQuestions', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            postQuestions: function (params) {
                return $http.post(API_URL + 'Question/add', params).then(function (response) {
                    return response.data;
                })
            },
            getQuestionInfo: function (params) {
                return $http.get(API_URL + 'Question/info', { 'params': params }).then(function (response) {
                  return response.data;
                })
            },
            updateQuestion: function (params) {
                return $http.post(API_URL + 'Question/update', params).then(function (response) {
                    return response.data;
                })
            },
            updateQuestionStatus: function (params) {
                return $http.post(API_URL + 'Question/updateStatus', params).then(function (response) {
                    return response.data;
                })
            },
            questionCategory: function (params) {
                return $http.get(API_URL + 'Question/category', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            sortQuestions: function (params) {
                return $http.post(API_URL + 'Question/order', params).then(function (response) {
                   return response.data;
                });
            },
            updateRelationship: function (params) {
                return $http.post(API_URL + 'Question/updateRelationshipCategories', params).then(function (response) {
                   return response.data;
                });
            },
            getQuestionOptions: function (params) {
                return $http.get(API_URL + 'Question/questionmasteroptions', { 'params': params }).then(function (response) {
                    return response.data;
                });
            }
        }
    })
    .factory('tagService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'tag/tags', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            groupedTags: function (params) {
                return $http.get(API_URL + 'Tag/Groupedtags', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },

            tagsList: function (params) {
                return $http.get(API_URL + 'tag/list', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            postTags: function (params) {
                return $http.post(API_URL + 'tag/add', params).then(function (response) {
                    return response.data;
                })
            },
            getTagInfo: function (params) {
                return $http.get(API_URL + 'tag/info', { 'params': params }).then(function (response) {
                  return response.data;
                })
            },
            updateTags: function (params) {
                return $http.post(API_URL + 'tag/fixedTaglabelUpdate', params).then(function (response) {
                    return response.data;
                })
            },            sortTags: function (params) {
                return $http.post(API_URL + 'tag/order', params).then(function (response) {
                   return response.data;
                });
            },
            updateTag: function (params) {
                return $http.post(API_URL + 'tag/update', params).then(function (response) {
                    return response.data;
                })
            },
            updateTagStatus: function (params) {
                return $http.post(API_URL + 'tag/updateStatus', params).then(function (response) {
                    return response.data;
                })
            },
            getTagOptions: function (params) {
                return $http.get(API_URL + 'tag/tagmasteroptions', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getContractTags: function (params) {
                return $http.get(API_URL + 'Contract/contractTags', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            updateContractTags: function (params) {
                return $http.post(API_URL + 'Contract/contractTagsUpdate', params).then(function (response) {
                    return response.data;
                })
            },
            providerTagsInfo :function(params){
                return $http.get(API_URL +'Customer/providerTags', {'params':params}).then(function(response){
                    return response.data;
                })
            },
            updateProviderTags: function (params) {
                return $http.post(API_URL + 'Customer/provideerTagsUpdate', params).then(function (response) {
                    return response.data;
                })
            },
            updateProvider: function (params) {
                return $http.post(API_URL + 'Customer/updateProvider', params).then(function (response) {
                    return response.data;
                });
            },
        }
    })
    .factory('settingsService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Settings/info', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Settings/update', params).then(function (response) {
                    return response.data;
                });
            }
        }
    })
    .factory('relationCategoryService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Relationship_category/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            add: function (params) {
                return $http.post(API_URL + 'Relationship_category/add', params).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Relationship_category/update', params).then(function (response) {
                    return response.data;
                });
            },
            providerupdate :function(params){
                return $http.post(API_URL +'Relationship_category/updateProviderCategories',params).then(function(response){
                    return response.data;
                })
            },
            addprovidercategories: function(params){
                return $http.post(API_URL+'Relationship_category/addProviderCategories',params).then(function(response){
                    return response.data;
                })
            },
            updateSettings: function (params) {
                return $http.post(API_URL + 'Customer/relationshipCategoryRemainder', params).then(function (response) {
                   return response.data;
                });
            },
            getSettingsData: function (params) {
                return $http.get(API_URL + 'Customer/relationshipCategoryRemainder', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            subList: function (params) {
                return $http.get(API_URL + 'Relationship_category/nrlist', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            addSub: function (params) {
                return $http.post(API_URL + 'Relationship_category/addnrcategory', params).then(function (response) {
                    return response.data;
                });
            },
            addProviderSub :function (params){
                return $http.post(API_URL + 'Relationship_category/AddAdditionalProviderCategories',params).then(function(response){
                    return response.data;
                })
            },
            updateSub: function (params) {
                return $http.post(API_URL + 'Relationship_category/updatenrcategory', params).then(function (response) {
                    return response.data;
                });
            },
            providerupdateSub :function (params){
                return $http.post(API_URL +'Relationship_category/updateAdditionalProviderCategories',params).then(function(response){
                    return response.data;
                })
            },
            providerCategoriesList: function(params){
                return $http.get(API_URL + 'Relationship_category/ProviderRelationshipCategoriesList', { 'params': params }).then(function (response) {
                    return response.data;
                });
                
            },
            providerAdditionalCategoriesList :function(params){
                return $http.get(API_URL + 'Relationship_category/AdditionalProviderCategories', { 'params': params }).then(function (response) {
                        return response.data;
                });
            }
        }
    })
    .factory('relationshipClassificationService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Relationship_category/classificationList', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            providersClassificationList :function (params){
                return $http.get(API_URL + 'Relationship_category/ProviderRelationshipclassificationList',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            add: function (params) {
                return $http.post(API_URL + 'Relationship_category/classificationAdd', params).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Relationship_category/classificationUpdate', params).then(function (response) {
                    return response.data;
                });
            },
            providerUpdate :function(params){
                return $http.post(API_URL + 'Relationship_category/providerclassificationUpdate',params).then(function(response){
                    return response.data;
                })
            },
            saveClassification: function (params) {
                return $http.post(API_URL + 'Relationship_category/classificationChildAdd', params).then(function (response) {
                    return response.data;
                });
            },
            saveproviderClassification :function(params){
                return $http.post(API_URL +'Relationship_category/providerclassificationChildAdd',params).then(function(response){
                    return response.data;
                })
            },
            listChildClassification: function (params) {
                return $http.get(API_URL + 'Relationship_category/classificationChild', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            listProviderChildClassification :function (params){
                return $http.get(API_URL +'Relationship_category/providerclassificationChild',{'params':params }).then(function(response){
                    return response.data;
                })
            },
            addChildClassification: function (params) {
                return $http.post(API_URL + 'Relationship_category/classificationChildAdd', params).then(function (response) {
                    return response.data;
                });
            },
            addproviderClassifications :function(params){
                return $http.post(API_URL+'Relationship_category/providerclassificationAdd',params).then(function(response){
                    return response.data;
                })
            }

        }
    })
    .factory('templateService', function ($http) {
        return {
            getModulesData: function (params) {
                return $http.get(API_URL + 'Template/alltemplates', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getImportTemplates: function (params) {
                return $http.get(API_URL + 'Template/getImportTemplates', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            list: function (params) {
                return $http.get(API_URL + 'Template/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getCounts: function (params) {
                return $http.get(API_URL + 'Template/count', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            info: function (params) {
                return $http.get(API_URL + 'Template/info', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            add: function (params) {
                return $http.post(API_URL + 'Template/add', params).then(function (response) {
                    return response.data;
                });
            },
            linkCustomerTemplate: function (params) {
                return $http.post(API_URL + 'Template/linkCustomerTemplate', params).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Template/update', params).then(function (response) {
                    return response.data;
                });
            },
            templateList: function (params) {
                return $http.get(API_URL + 'Template/details', { 'params': params }).then(function (response) {
                 return response.data;
              });
            },
            moduleList: function (params) {
                return $http.get(API_URL + 'Template/moduleList', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            addModule: function (params) {
                return $http.post(API_URL + 'Template/module', params).then(function (response) {
                    return response.data;
                });
            },
            deleteModule: function (params) {
                return $http.delete(API_URL + 'Template/module', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getModule: function (params) {
                return $http.get(API_URL + 'Template/module', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            topicList: function (params) {
                return $http.get(API_URL + 'Template/topicList', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            addTopic: function (params) {
                return $http.post(API_URL + 'Template/Topic', params).then(function (response) {
                    return response.data;
                });
            },
            deleteTopic: function (params) {
                return $http.delete(API_URL + 'Template/Topic', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getTopic: function (params) {
                return $http.get(API_URL + 'Template/topic', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            questionList: function (params) {
                return $http.get(API_URL + 'Template/questionList', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            postQuestion: function (params) {
                return $http.post(API_URL + 'Template/question', params).then(function (response) {
                    return response.data;
                });
            },
            getAllModules: function (params) {
                return $http.get(API_URL + 'Template/allModules', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getAllTopics: function (params) {
                return $http.get(API_URL + 'Template/allTopics', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getAllQuestions: function (params) {
                return $http.get(API_URL + 'Template/allQuestions', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            deleteQuestion: function (params) {
                return $http.delete(API_URL + 'Template/question', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            sortModules: function (params) {
                return $http.post(API_URL + 'Template/moduleOrder', params).then(function (response) {
                    return response.data;
                })
            },
            sortTopics: function (params) {
                return $http.post(API_URL + 'Template/topicOrder', params).then(function (response) {
                    return response.data;
                })
            },
            sortQuestions: function (params) {
                return $http.post(API_URL + 'Template/questionOrder', params).then(function (response) {
                    return response.data;
                })
            },
            previewTemplate: function (params) {
                return $http.get(API_URL + 'Template/templatePreview', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            viewTemplate: function (params) {
                return $http.get(API_URL + 'Template/templateView', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            sortAll: function (params) {
                return $http.post(API_URL + 'Template/templateOrder', params).then(function (response) {
                   return response.data;
                });
            }
        }
    })
    .factory('businessUnitService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Business_unit/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            bulist: function (params) {
                return $http.get(API_URL + 'Business_unit/bulist', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            get: function (params) {
                return $http.get(API_URL + 'Business_unit/details', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            add: function (params) {
                return $http.post(API_URL + 'Business_unit/add', params).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Business_unit/update', params).then(function (response) {
                    return response.data;
                });
            }
        }
    })
    .factory('providerService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Customer/provider', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            add: function (params) {
                return $http.post(API_URL + 'Customer/addprovider', params).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Customer/updateprovider', params).then(function (response) {
                    return response.data;
                });
            },
            exportProviders: function (params) {
                return $http.get(API_URL + 'Customer/providersListExport', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            getProviderRelationshipCategory: function (params) {
                return $http.get(API_URL + 'Contract/ProviderRelationshipCategory', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getProviderUniqueId :function(params){
                return $http.get(API_URL +'Customer/GenerateProviderId',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            providerOverallDetails: function (params) {
                return $http.get(API_URL + 'Customer/providerListGraph', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            providerContractslist: function (params) {
                return $http.get(API_URL + 'Contract/ProviderContracts', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getProviderLogs: function (params) {
                return $http.get(API_URL + 'Customer/providerlog', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            //written by ashok
            addProvidersToProject: function(params) {
                return $http.post(API_URL + 'Project/mappingProviderToProject',{ 'params': params}).then(function (response) {
                    return response.data;
                })
            }
            
        }
    })
    .factory('dashboardService', function ($http) {
        return {
            info: function (params) {
                return $http.get(API_URL + 'Customer/dashboard', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },

            allCounts: function (params) {
                return $http.get(API_URL + 'Dashboard/counts', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },

            acivityGraph: function (params) {
                return $http.get(API_URL + 'Dashboard/allactivitiesGraph', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },

            relationsGraph: function (params) {
                return $http.get(API_URL + 'Dashboard/allrelationsGraph', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },

            actionItemsGraph: function (params) {
                return $http.get(API_URL + 'Dashboard/allactionItemsGraph', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },

            projectsGraph: function (params) {
                return $http.get(API_URL + 'Dashboard/allprojectsGraph', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },

            coworkersGraph: function (params) {
                return $http.get(API_URL + 'Dashboard/allcoworkersGraph', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            contractsGraph: function (params) {
                return $http.get(API_URL + 'Dashboard/allcontractsGraph', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },

            
            contractsList: function (params) {
                return $http.get(API_URL + 'Contract/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            reviewsList: function (params) {
                return $http.get(API_URL + 'contract/myreviewslist', { 'params': params}).then(function (response) {
                    return response.data;
                });
            },
            contributorsList: function (params) {
                return $http.get(API_URL + 'contract/mycontributionslist', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            dashboardtabs :function(params){
                return $http.get(API_URL + 'Customer/dashboardInfoTabs',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            dashbaordTabsOrder:function(params){
                return $http.post(API_URL +'Customer/addDashboard',params).then(function(response){
                    return response.data;
                })
            }
           
        }
    })
    .factory('projectService',function ($http){
        return{
            generateprojectId :function (params){
                return $http.get(API_URL+'Master/GenerateProductId',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            projectList: function(params){
                return $http.get(API_URL+'Project/projectList',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            projectworkflowList: function(params){
                return $http.get(API_URL+'Project/getProjectTasks',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            projectInfo :function(params){
                return $http.get(API_URL +'project/projectInfo',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            getConnectedContracts :function (params){
                return $http.get(API_URL +'Master/getConnectedContractsProjects',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            deleteConnectedContracts :function(params){
                return $http.post(API_URL +'Master/deleteConnectedcontracts',params).then(function(response){
                    return response.data;
                })
            },
            addContractToProject :function(params){
                return $http.post(API_URL + 'Master/addContractToProject',params).then(function(response){
                    return response.data;
                })
            },
            initializeProjectReview: function (params) {
                return $http.get(API_URL + 'Project/initiateProjectTask', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getAvailableProviders :function(params){
                return $http.get(API_URL +'Project/getrojectProviders',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            addProjectContributors: function (params) {
                return $http.post(API_URL + 'Project/contractContributor', params).then(function (response) {
                    return response.data;
                });
            },
            finalizeProjectList: function (params) {
                return $http.post(API_URL + 'Project/projecttaskfinalize', params).then(function (response) {
                    return response.data;
                });
            },
            exportProjectDashboardData: function (params) {
                return $http.get(API_URL + 'Project/projectdashboardexport', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            getprojectLogs: function (params) {
                return $http.get(API_URL + 'Project/project_log', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getProjectDashboard: function (params) {
                return $http.get(API_URL + 'Project/projectDashboard', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            deleteProviderFromProject: function(params){
                return $http.post(API_URL +'Project/unmappingProviderToProject',params).then(function(response){
                    return response.data;
                })
            },
            getRecurrences:function(params){
                return $http.get(API_URL+'Project/recurrence_dropdown',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            resendRecurrence:function(params){
                return $http.get(API_URL+"Project/resend_recurrence_dropdown",{'params':params}).then(function(response){
                    return response.data;
                })
            },
            addObligations:function(params){
                return $http.post(API_URL+'Project/createobligations',params).then(function(response){
                    return response.data;
                })
            },

            moveAllObligation:function(params){
                return $http.post(API_URL+'Project/moveAllObligation',params).then(function(response){
                    return response.data;
                })
            },

            deleteObligations: function (params) {
                return $http.delete(API_URL + 'Project/deleteobligations', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            getObligations:function(params){
                return $http.get(API_URL+'Project/getobligations',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            contractInfoTabs:function(params){
                return $http.get(API_URL+'Project/contractInfoTabs',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            getAllContractsAndProjects:function(params){
                return $http.get(API_URL+'Project/allContractsandProjects',{'params':params}).then(function(response){
                    return response.data;
                })
            },

            multipleActionItemsAdding:function(params){
                return $http.post(API_URL+'Project/ReviewActionItemadd',params).then(function(response){
                    return response.data;
                })
            },

            mapSubTaskContract:function(params){
                return $http.get(API_URL+'Master/mapSubtaskToContract',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            eventFeedProject:function(params){
                return $http.post(API_URL+'Project/eventFeed',params).then(function(response){
                    return response.data;
                })
            },
            eventFeedList:function(params){
                return $http.get(API_URL+'Project/eventFeed',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            eventResponsibleUsers:function(params){
                return $http.get(API_URL+'Project/eventFeedResponsibleUsers',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            deleteEventFeed: function (params) {
                return $http.delete(API_URL + 'Project/eventFeed', { params: params }).then(function (response) {
                    return response.data;
                });
            },
        }
    })
    .factory('contractService', function ($http,Upload) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Contract/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getTabsCount(params){
                return $http.get(API_URL + 'Contract/contractFieldCountInfo', { 'params': params }).then(function (response) {
                    return response.data;
                }); 
            },
            getContractAttachments(params){
                return $http.get(API_URL + 'Contract/contractAttachments', { 'params': params }).then(function (response) {
                    return response.data;
                }); 
            },
            allContractsList: function (params) {
                return $http.get(API_URL + 'Contract/allContractList', { 'params': params }).then(function (response) {
                    return response.data;
                });
            }, 
            reviewWorkflowInfo: function (params) {
                return $http.get(API_URL + 'Contract/WorkflowReview', { 'params': params }).then(function (response) {
                    return response.data;
                });
            }, 
            childMapContracts: function (params) {
                return $http.post(API_URL + 'Master/mapChildContract', params).then(function (response) {
                    return response.data;
                });
            },

            listDelete: function (params) {
                return $http.get(API_URL + 'Contract/deletedList', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            delete: function (params) {
                return $http.post(API_URL + 'Contract/delecteContract', params).then(function (response) {
                    return response.data;
                });
            },
            removeSub: function (params) {
                return $http.post(API_URL + 'Master/unmapChildContract', params).then(function (response) {
                    return response.data;
                });
            },
            undoDelete: function (params) {
                return $http.post(API_URL + 'Contract/undoDelContract', params).then(function (response) {
                    return response.data;
                });
            },
            add: function (params) {
                return $http.post(API_URL + 'Contract/add', params).then(function (response) {
                   return response.data;
                });
            },
            getContractStatus: function (params) {
                return $http.get(API_URL + 'Contract/contractStatus', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getContractDomain: function (params) {
                return $http.get(API_URL + 'master/getMasterDomains', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },

            getContractField: function (params) {
                return $http.get(API_URL + 'Master/getMasterDomainFields', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },

            getContractField: function (params) {
                return $http.get(API_URL + 'Master/getMasterDomainFields', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },

            getContractList: function (params) {
                return $http.get(API_URL + 'master/filtersList', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },

            deleteContractFlter: function (params) {
                return $http.delete(API_URL + 'master/filter', { params: params }).then(function (response) {
                    return response.data;
                });
            },

            getContractTagsDrpdown: function (params) {
                return $http.get(API_URL + 'tag/getTagOptions', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },





            filterCreate: function (params) {
                return $http.post(API_URL + 'Master/createFilter', params).then(function (response) {
                   return response.data;
                });
            },


            getContractById: function (params) {
                return $http.get(API_URL + 'Contract/info', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            update: function (params) {
                return $http.post(API_URL + 'Contract/update', params).then(function (response) {
                    return response.data;
                });
            },
            getRelationshipCategory: function (params) {
                return $http.get(API_URL + 'Contract/relationshipCategory', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getRelationshipClassiffication: function (params) {
                return $http.get(API_URL + 'Contract/relationshipClassification', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getDelegates: function (params) {
                return $http.get(API_URL + 'Contract/getdelegates', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            reviewActionItemList: function (params) {
                return $http.get(API_URL + 'Contract/reviewActionItems', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            addReviewActionItemList: function (params) {
                return $http.post(API_URL + 'Contract/ReviewActionItem', params).then(function (response) {
                    return response.data;
                });
            },
            reviewActionItemUpdate: function (params) {
                return $http.post(API_URL + 'Contract/ReviewActionItemUpdate', params).then(function (response) {
                    return response.data;
                });
            },
            addSecondOpinion: function (params) {
                return $http.post(API_URL + 'Contract/addSecondOpinion', params).then(function (response) {
                    return response.data;
                });
            },
            responsibleUserList: function (params) {
                return $http.get(API_URL + 'Contract/users', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getActionItemResponsibleUsers: function (params) {
                return $http.get(API_URL + 'Contract/actionitemresponsibleusers', { 'params': params }).then(function (response) {
                 return response.data;
              });
            },

            getResponsibleUserFilter: function (params) {
                return $http.get(API_URL + 'User/getResponsibleUserForFilter', { 'params': params }).then(function (response) {
                    return response.data;
              });
            },

            getContributersFilter: function (params) {
                return $http.get(API_URL + 'User/getContributersForFilter', { 'params': params }).then(function (response) {
                    return response.data;
              });
            },


            contractResponsibleUserList: function (params) {
                return $http.get(API_URL + 'Contract/contractreviewusers', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getbuOwnerUsers: function (params) {
                return $http.get(API_URL + 'Contract/users', { 'params': params }).then(function (response) {
                   return response.data;
                })
            },
            initializeReview: function (params) {
                return $http.get(API_URL + 'Contract/initializeReview', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            contractModule: function (params) {
                return $http.get(API_URL + 'Contract/module', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getcontractReviewModules: function (params) {
                return $http.get(API_URL + 'Contract/contractReview', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            addContributors: function (params) {
                return $http.post(API_URL + 'Contract/contractContributor', params).then(function (response) {
                    return response.data;
                });
            },
            ProcessValidation:function (params){
                return $http.post(API_URL + 'Contract/ProcessValidation',params).then(function (response){
                    return response.data;
                });
            },
            getAttachments: function (params) {
                return $http.get(API_URL + 'Document/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            deleteActionItem: function (params) {
                return $http.delete(API_URL + 'Contract/ReviewActionItemDelete', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            finalizeReviewList: function (params) {
                return $http.post(API_URL + 'Contract/finalize', params).then(function (response) {
                    return response.data;
                });
            },
            answerQuestion: function (params) {
                return $http.post(API_URL + 'Contract/questionAnswer', params).then(function (response) {
                   return response.data;
                });
            },
            getDashboard: function (params) {
                return $http.get(API_URL + 'Contract/dashboard', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getTrends: function (params) {
                return $http.get(API_URL + 'Contract/trends', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getAllActionItems: function (params) {
                return $http.get(API_URL + 'Contract/actionItems', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            contractProviders: function (params) {
                return $http.get(API_URL + 'Contract/providers', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getActionItemDetails: function (params) {
                return $http.get(API_URL + 'Contract/actionItemDetails', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            contractOverallDetails: function (params) {
                return $http.get(API_URL + 'Contract/contractDetails', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getTopicQuestionsById: function (params) {
                return $http.get(API_URL + 'Contract/getTopic', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getUrl: function (params) {
                return $http.get(API_URL + 'Contract/getDownloadedFile', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getchangeLogs: function (params) {
                return $http.get(API_URL + 'Contract/contractReviewChangelog', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            exportReviewData: function (params) {
                return $http.get(API_URL + 'Contract/export', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            exportDashboardData: function (params) {
                return $http.get(API_URL + 'Contract/dashboardexport', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            discussDetails: function (params) {
                return $http.get(API_URL + 'Project/reviewdiscussion', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            postdiscussion: function (params) {
                return $http.post(API_URL + 'Project/reviewdiscussion', params).then(function (response) {
                   return response.data;
                });
            },
            closediscussion: function (params) {
                return $http.post(API_URL + 'Project/reviewdiscussionclose', params).then(function (response) {
                 return response.data;
              });
            },
            getLogs: function (params) {
                return $http.get(API_URL + 'Contract/contract_log', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            providerList: function (params) {
                return $http.get(API_URL + 'Contract/listProviders', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            exportContracts: function (params) {
                return $http.get(API_URL + 'Contract/contractListExport', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            reviewUsers: function (params) {
                return $http.get(API_URL + 'Contract/reviewlevelusers', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            getBussinessUnitList: function (params) {
                return $http.get(API_URL + 'Business_unit/list', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            getFileLogs: function (params) {
                return $http.get(API_URL + 'Document/list', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getContractDocLogs: function (params) {
                return $http.get(API_URL + 'Document/ContractDoclist', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            addSponsers: function (params) {
                return $http.post(API_URL + 'Contract/updatestakeholders', params).then(function (response) {
                 return response.data;
              });
            },
            getstakeholders: function (params) {
                return $http.get(API_URL + 'Contract/getstakeholders', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            validationEnd: function (params) {
                return $http.post(API_URL + 'Contract/validateModule', params).then(function (response) {
                 return response.data;
              });
            },
            getSpendMgmt: function (params) {
                return $http.get(API_URL + 'Contract/spent_information', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            updateSpendMgmt: function (params) {
                return $http.post(API_URL + 'Contract/spent_information', params).then(function (response) {
                 return response.data;
              });
            },
            getSpentline: function (params) {
                return $http.get(API_URL + 'Contract/spentline', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getSpendManagementInfo :function(params){
                return $http.get(API_URL + 'Master/getSpendData', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            updateSpendLine: function (params) {
                return $http.post(API_URL + 'Contract/spentline', params).then(function (response) {
                 return response.data;
              });
            },
            getUnAnswered: function (params) {
                return $http.get(API_URL + 'Contract/unansweredquestions', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getStoredModules: function (params) {
                return $http.get(API_URL + 'Contract/getStoredModules', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            updateStoredModules: function (params) {
                return $http.post(API_URL + 'Contract/updateStoredModule', params).then(function (response) {
                 return response.data;
              });
            },
            generateContractId :function (params){
                return $http.get(API_URL+'Master/GeneratecontractId',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            lockingStatus: function(params){
                return $http.post(API_URL +'Document/lock_unlock',params).then(function(response){
                    return response.data;
                })
            },
            uploaddata: function (params) {
                return Upload.upload({
                    async: true,
                    url: API_URL + 'Document/add',
                    data: params
                }).then(function (response) {
                    return response.data;
                });
            },
            servicePeriodicity :function(){
                return $http.get(API_URL+'Project/payment_periodicity').then(function(response){
                    return response.data;
                })
            },
            addServiceCatologue :function(params){
                return $http.post(API_URL +'Project/service_catalogue',params).then(function(response){
                    return response.data;
                })
            },
            getServiceCatologue:function(params){
                return $http.get(API_URL+'Project/service_catalogue',{'params':params}).then(function(response){
                    return response.data;
                })
            },
            deleteServiceCatologue: function (params) {
                return $http.delete(API_URL + 'Project/service_catalogue', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            getOnlyEvidences :function(params){
                return $http.get(API_URL+'Project/evidences',{'params':params}).then(function(response){
                    return response.data;
                })
            }
        }
    })
    .factory('archiveService', function ($http) {
        return {
            allArchiveList: function (params) {
                return $http.get(API_URL + 'Contract/getArchiveList', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            archiveFilesList: function (params) {
                return $http.get(API_URL + 'Contract/getArchiveAttachments', { 'params': params }).then(function (response) {
                    return response.data;
                });
            }
        }
    })
    .factory('actionItemsService', function ($http) {
        return {
            getActionItemFilters: function (params) {
                return $http.get(API_URL + 'Contract/actionItemFilters', { 'params': params }).then(function (response) {
                    return response.data;
                });
            }
        }
    })
    .factory('attachmentService', function ($http) {
        return {
            getAttachments: function (params) {
                return $http.get(API_URL + 'Document/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            deleteAttachments: function (params) {
                return $http.delete(API_URL + 'Document/delete', { 'params': params }).then(function (response) {
                    return response.data;
                });
            }
        }
    })
    .factory('calenderService', function ($http) {
        return {
            post: function (params) {
                return $http.post(API_URL + 'customer/calender', params).then(function (response) {
                    return response.data;
                });
            },
            get: function (params) {
                return $http.get(API_URL + 'customer/calender', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            yearly: function (params) {
                return $http.get(API_URL + 'customer/calenderYearView', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            addReview: function (params) {
                return $http.post(API_URL + 'calender/add', params).then(function (response) {
                    return response.data;
                });
            },
            smartFilter: function (params) {
                return $http.get(API_URL + 'calender/smart_filter', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getPlannedList: function (params) {
                return $http.get(API_URL + 'calender/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getPlannedEvents : function(params) {
                return $http.get(API_URL + 'calender/calendarevents', {'params': params }).then(function (response){
                    return response.data;
                });
            },
            addWorkflowRemainder: function (params) {
                return $http.post(API_URL + 'Customer/workflowRemainder', params).then(function (response) {
                    return response.data;
                });
            },
            getWorkflowRemainder : function(params) {
                return $http.get(API_URL + 'Customer/workflowRemainder', {'params': params }).then(function (response){
                    return response.data;
                });
            },
            deletePlanned : function(params) {
                return $http.get(API_URL + 'Calender/deletecalender', {'params': params }).then(function (response){
                    return response.data;
                });
            }
        }
    })
    .factory('emailTempalteService', function ($http) {
        return {
            get: function (params) {
                return $http.get(API_URL + 'Contract/emailTemplateList', { 'params': params }).then(function (response) {
                    return response.data;
                })
            },
            post: function (params) {
                return $http.post(API_URL + 'Contract/emailTemplateUpdate', params).then(function (response) {
                   return response.data;
                });
            },
            testTemplate: function (params) {
                return $http.post(API_URL + 'customer/testemailtemplate', params).then(function (response) {
                   return response.data;
                });
            },
            delete: function (params) {
                return $http.post(API_URL + 'Contract/emailTemplateUpdateStatus', params).then(function (response) {
                    return response.data;
                });
            },
        }
    })
    .factory('reportsService', function ($http) {
        return {
            reportsList: function (params) {
                return $http.get(API_URL + 'report/list', { 'params': params }).then(function (response) {
                  return response.data;
              })
            },
            getReportFilters: function (params) {
                return $http.get(API_URL + 'Report/criteria', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            searchReports: function (params) {
                return $http.get(API_URL + 'Report/search', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            saveReport: function (params) {
                return $http.post(API_URL + 'Report/saveReport', params).then(function (response) {
                    return response.data;
                })
            },
            getReportDetails: function (params) {
                return $http.get(API_URL + 'report/report', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            deleteReport: function (params) {
                return $http.delete(API_URL + 'report/delete', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            exportReport: function (params) {
                return $http.get(API_URL + 'Report/export', { params: params }).then(function (response) {
                   return response.data;
                });
            }
        }
    })
    .factory('historyService', function ($http) {
        return {
            customersList: function (params) {
                return $http.get(API_URL + 'Customer/listCustomers', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getCustomerUsers: function (params) {
                return $http.get(API_URL + 'Customer/userListHistory', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getSummary: function (params) {
                return $http.get(API_URL + 'Customer/userHistory', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            getActionsList: function (params) {
                return $http.get(API_URL + 'Customer/actionList', { 'params': params }).then(function (response) {
                    return response.data;
                })
            }
        }
    })
    .factory('notificationService', function ($http) {
        return {
            getUpdates: function (params) {
                return $http.get(API_URL + 'Customer/dailyupdates', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getCount: function (params) {
                return $http.get(API_URL + 'customer/dailynotificationcount', { 'params': params }).then(function (response) {
                   return response.data;
                });
            },
            list : function(params) {
                return $http.get(API_URL+ 'customer/notification', {'params' : params}).then(function(response){
                    return response.data;
                });
            }
        }
    })
    .factory('contributorService', function ($http) {
        return {
            list: function (params) {
                return $http.get(API_URL + 'Customer/DelegateContributors', { 'params': params }).then(function (response) {
                    return response.data;
                });
            }
        }
    })

    .factory('builderService', function ($http) {
        return {
            // builderList: function (params) {
            //     return $http.get(API_URL + 'Contract_builder/url', { 'params': params }).then(function (response) {
            //         return response.data;
            //     });

            builderList: function (params) {
                return $http.post(API_URL + 'Contract_builder/url', params).then(function (response) {
                    return response.data;
                });    
            },
            getCustomerBuilderList :function(params){
                return $http.get(API_URL+'Contract_builder/searchCustomer',{'params':params}).then(function(response){
                    return response.data;
                })
            }
        }
    })

    .factory('catalogueService', function ($http,Upload) {
        return {

            generateCatalogueId: function (params) {
                return $http.get(API_URL + 'Catalogue/GenerateCataloguerId', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            catalogueList: function (params) {
                return $http.get(API_URL + 'Catalogue/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            selectedMasteerList: function (params) {
                return $http.get(API_URL + 'Master/list', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },

            getCatalogueTags: function (params) {
                return $http.get(API_URL + 'Catalogue/catalogueTags', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            updateCatalogue: function (params) {
                return $http.post(API_URL + 'Catalogue/update', params).then(function (response) {
                    return response.data;
                })
            },
            updateCatalogueTags: function (params) {
                return $http.post(API_URL + 'Catalogue/catalogueTagsUpdate', params).then(function (response) {
                    return response.data;
                })
            },
            deleteCatalogue :function(params){
                return $http.delete(API_URL + 'Catalogue/catalogue', { params: params }).then(function (response) {
                    return response.data;
                });
            },
            getCatalogueExport: function (params) {
                return $http.get(API_URL + 'catalogue/catalogueExport', { 'params': params }).then(function (response) {
                    return response.data;
                });
            },
            getExchange: function (params) {
                return $http.get(API_URL + 'Catalogue/exchangeRate', { 'params': params }).then(function (response) {
                    return response.data;
                });
            } 
        }
    })