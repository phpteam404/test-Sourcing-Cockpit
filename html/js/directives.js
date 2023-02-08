angular
    .module('app')
    .directive('a', preventClickDirective)
    .directive('a', bootstrapCollapseDirective)
    .directive('a', navigationDirective)
    .directive('nav', sidebarNavDynamicResizeDirective)
    .directive('button', layoutToggleDirective)
    .directive('a', layoutToggleDirective)
    .directive('button', collapseMenuTogglerDirective)
    .directive('div', bootstrapCarouselDirective)
    .directive('toggle', bootstrapTooltipsPopoversDirective)
    .directive('tab', bootstrapTabsDirective)
    .directive('button', cardCollapseDirective)
    .factory('AuthService', function ($rootScope, $http, $location, $localStorage, $state, userService) {
        return {
            login: function () {
                if ($localStorage.curUser && !angular.equals({}, $localStorage.curUser)) {
                    if ($localStorage.curUser.status) {
                        $http.defaults.headers.common['Authorization'] = $localStorage.curUser.access_token;
                        $http.defaults.headers.common['AppVersion'] = $rootScope.appVersion;
                        if ($localStorage.curUser.data.parent)
                            $http.defaults.headers.common['User'] = $localStorage.curUser.data.parent.id_user;
                        else $http.defaults.headers.common['User'] = $localStorage.curUser.data.data.id_user;
                        //$http.defaults.headers.common['lang'] = 'english';
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            },
            logout: function (user, pass) {

            },
            checkUrl: function (module_url) {

                var param = {};
                // param.module_url = module_url.trim();
                module_url = module_url.split('?')[0];

                if (module_url.split('/')[2] == 'relationship_classification' ||
                    module_url.split('/')[2] == 'admin_provider_relationship_classification' ||
                    module_url.split('/')[2] == 'admin' ||
                    module_url.split('/')[2] == 'user' ||
                    module_url.split('/')[2] == 'provider_relationship_classification') {
                    // console.log("abc");
                    param.module_url = '#' + '/' + module_url.split('/')[1] + '/' + module_url.split('/')[2] + '/' + module_url.split('/')[3];
                    // console.log("lo3",param.module_url);
                }
                else if (module_url.split('/')[1] == 'notifications') {
                    // console.log("notify");
                    param.module_url = '#' + '/' + module_url.split('/')[1];
                    // console.log("notify",param.module_url);
                }

                else if (module_url.split('/')[2] != undefined) {
                    // console.log("ionio1234");
                    param.module_url = '#' + '/' + module_url.split('/')[1] + '/' + module_url.split('/')[2];
                    //  console.log("lo2",param.module_url);
                }
                else {
                    // console.log("123");
                    param.module_url = '#' + '/' + module_url.split('/')[1];
                    // console.log("lo",param.module_url);
                }


                if ($localStorage.curUser.data.data.user_role_id) param.user_role_id = $localStorage.curUser.data.data.user_role_id;
                var data = [];
                /* return $http.get(API_URL + 'User/access', {params:param}).then(function (response) {
                     angular.forEach(response.data.data, function (value, key) {
                         angular.forEach(value, function (value, key) {
                             data[key] = value;
                         })
                     })
                     return data;
                 });*/
                return userService.getAccess(param).then(function (result) {
                    angular.forEach(result.data, function (value, key) {
                        angular.forEach(value, function (value, key) {
                            data[key] = value;
                        })
                    })
                    return data;
                });
            },
            isLoggedIn: function () {
                if ($localStorage.curUser && !angular.equals({}, $localStorage.curUser))
                    return true;
                return false;
                // Check auth token here from localStorage
            },
            getFields: function () {
                return $localStorage.curUser;
            }
        }
    })
    .factory('decode', function () {
        return function (text) {
            if (text != undefined) {
                return window.atob(text);
            }
        }
    })
    .factory('encode', function () {
        return function (text) {
            return window.btoa(text);
        }
    })
    .filter('decode', function () {
        return function (text) {
            if (text != undefined) {
                return window.atob(text);
            }
        }
    })

    .filter('encode', function () {
        return function (text) {
            if (text != undefined) {
                return window.btoa(text);
            }
        }
    })
    .filter('unique', function () {
        return function (arr, targetField) {
            var values = [],
                i,
                unique,
                l = arr.length,
                results = [],
                obj;
            for (i = 0; i < l; i++) {
                obj = arr[i];
                unique = true;
                for (v = 0; v < values.length; v++) {
                    if (obj[targetField] == values[v]) {
                        unique = false;
                    }
                }
                if (unique) {
                    values.push(obj[targetField]);
                    results.push(obj);
                }
            }
            return results;
        };
    })

    .filter("removeDups", function () {
        return function (data) {
            if (angular.isArray(data)) {
                var result = [];
                var key = {};
                for (var i = 0; i < data.length; i++) {
                    var val = data[i];
                    if (angular.isUndefined(key[val])) {
                        key[val] = val;
                        result.push(val);
                    }
                }
                if (result.length > 0) {
                    return result;
                }
            }
            return data;
        }
    })
    .filter('uniques', function () {
        return function (items, filterOn) {

            if (filterOn === false) {
                return items;
            }

            if ((filterOn || angular.isUndefined(filterOn)) && angular.isArray(items)) {
                var hashCheck = {}, newItems = [];

                var extractValueToCompare = function (item) {
                    if (angular.isObject(item) && angular.isString(filterOn)) {

                        var resolveSearch = function (object, keyString) {
                            if (typeof object == 'undefined') {
                                return object;
                            }
                            var values = keyString.split(".");
                            var firstValue = values[0];
                            keyString = keyString.replace(firstValue + ".", "");
                            if (values.length > 1) {
                                return resolveSearch(object[firstValue], keyString);
                            } else {
                                return object[firstValue];
                            }
                        }

                        return resolveSearch(item, filterOn);
                    } else {
                        return item;
                    }
                };

                angular.forEach(items, function (item) {
                    var valueToCheck, isDuplicate = false;

                    for (var i = 0; i < newItems.length; i++) {
                        if (angular.equals(extractValueToCompare(newItems[i]), extractValueToCompare(item))) {
                            isDuplicate = true;
                            break;
                        }
                    }
                    if (!isDuplicate) {
                        if (typeof item != 'undefined') {
                            newItems.push(item);
                        }
                    }

                });
                items = newItems;
            }
            return items;
        };
    })
    .filter('encode', function () {
        return function (text) {
            return window.btoa(text);
        }
    })
    .filter('isUndefinedOrNull', function () {
        return function (value) {
            if (value === null || value === undefined)
                return '---';
            else
                return value;
        };
    })
    .filter('isUndefinedOrNullOrZero', function () {
        return function (value) {
            if (value === null || value === undefined || value === 0 || value === '')
                return '---';
            else
                return value;
        };
    })
    .filter('ocrFilter', function () {
        return function (input, search) {
            if (!input) return input;
            if (!search) return input;
            if (search.status) {
                input = input.filter(item => {
                    if (item.status && item.status.indexOf(search.status) !== -1) {
                        return item;
                    }
                });
            }
            if (search.key) {
                input = input.filter(item => {
                    if (item.field_name) {
                        var searchKey = search.key.toLowerCase();
                        var searchValue = item.field_name; // +' ' +item.options.toString();
                        var re = new RegExp(searchKey, 'g');
                        var v = searchValue.toLowerCase();
                        // console.log(searchKey, v, v.match(re));
                        return v.match(re);
                    }
                });
            }
            return input;
        }
    })
    /*.directive('activeLink',['$location', '$state', 'underscoreaddFilter',function(location,$state,underscoreaddFilter){
        return {
            restrict: 'A',
            link: function(scope,element,attrs,controller){
                var clazz=attrs.activeLink;
                var path='';
                if(attrs.ngHref)
                    path=attrs.ngHref;
                if(attrs.href)
                    path=attrs.href;

                if(path!=undefined){
                    path=decodeURIComponent(path.substring(1));
                }else{
                    path='#';
                }//hack because path does not return including hashbang
                scope.location=location;
                scope.$watch('location.path()',function(newPath){
                    var url=attrs.aliasUrl;
                    if(path===newPath){
                        console.log('newPath',newPath);
                        console.log('path', path);
                        console.log('element--', element.parent());
                        element.parent().addClass(clazz);
                    } else{
                        element.parent().removeClass(clazz);
                    } 
                    if($state.$current.parent.hasOwnProperty('data')&&$state.$current.parent.data){
                        if($state.$current.parent.data.hasOwnProperty('activeLink')){
                            var id=underscoreaddFilter($state.$current.parent.data.activeLink);
                            $('#'+id).addClass('active');
                        }
                    }
                });
            }
        };
    }])*/
    .directive('activeLink', ['$location', '$state', '$window', function (location, $state, $window) {
        return {
            restrict: 'AE',
            link: function (scope, element, attrs, controller) {
                var clazz = attrs.activeLink;
                element.removeClass(clazz);
                var name = attrs.name;
                var path = '';
                if (attrs.ngHref)
                    path = attrs.ngHref;
                if (attrs.href)
                    path = attrs.href;
                if (path != undefined) {
                    path = decodeURIComponent(path.substring(1));
                } else {
                    path = '#';
                }//hack because path does not return including hashbang
                scope.location = '';
                scope.location = location;
                scope.$watch('location.path()', function (newPath, oldPath) {
                    oldPath = oldPath.replace('/', '/');
                    newPath = newPath.replace('/', '#/');
                    var arr = [];
                    arr = newPath.split('/');
                    angular.forEach(arr, function (i, o) {
                        if (o == 1) {
                            if (element.attr('href') == '#/' + arr[o]) {
                                element.removeClass(clazz);
                                element.addClass(clazz);
                                if (name != undefined) {
                                    var ids = [];
                                    ids = name.split('-');
                                    var parent = document.getElementById(ids[0].substring(6));
                                    parent.classList.add(clazz);
                                } else { }
                            } else {
                                //console.log('element', element.attr('href'));
                                //console.log('arr','#/' + arr[o]);
                                element.removeClass(clazz);
                            }
                        }
                    })
                });
            }
        };
    }])
    /*.directive('activeLink',['$location',function(location){
        return {
            restrict: 'A',
            link: function(scope,element,attrs,controller){                
                var clazz=attrs.activeLink;
                var path='';
                if(attrs.ngHref)
                    path=attrs.ngHref;
                if(attrs.href)
                    path=attrs.href;

                if(path!=undefined){
                    path=decodeURIComponent(path.substring(1));
                }else{
                    path='#';
                }//hack because path does not return including hashbang
                scope.location=location;
                scope.$watch('location.path()',function(newPath){
                    var url=attrs.aliasUrl;
                    if(path===newPath){
                        element.parent().addClass(clazz);
                    }else{
                        element.parent().removeClass(clazz);
                        if(newPath=='/provider'){
                            var element1 = document.getElementById("business_unit");
                            element1.classList.add(clazz);
                        }else {
                            element.parent().removeClass(clazz);
                        }
                    }
                });
            }
        };
    }])*/
    .filter('underscoreadd', function (lowercaseFilter) {
        return function (input) {
            if (input != null) {
                return lowercaseFilter(input.replace(/ /g, '_'));
            }
            else {
                return input;
            }

        };
    })
    .filter('removespecialchar', function (lowercaseFilter) {
        return function (input) {
            return lowercaseFilter(input.replace(/[&\/\\#,+()$~%.'":*?<>{}]/g, '_'));
        };
    })
    .filter('underscoreless', function (lowercaseFilter) {
        return function (input) {
            return lowercaseFilter(input.replace(/_/g, ' '));
        };
    })
    .filter('capitalize', function () {
        return function (input) {
            return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
        }
    })
    .filter('checkEmpty', function () {
        return function (input, string) {
            if (input == '' || input == null || input === undefined) {
                if (string)
                    return string;
                return '---';
            } else {
                return input;
            }
        };
    })
    .filter('replaceChar', function () {
        return function (input) {
            var temp = input.split('$$');
            input = input.replace('$$', ' ');
            return temp.join(' ');
        };
    })
    .filter('replaceUrl', function () {
        return function (input) {
            var temp = input.split('#');
            return temp[1];
        };
    })
    .directive('textLine', function () {
        return {
            link: function (scope, element) {
                element.addClass('text-line');
                element.bind('click', function () {
                    element.toggleClass('text-line');
                });
            },
        };
    })
    .filter('trusted', function ($sce) {
        return function (html) {
            return $sce.trustAsHtml(html)
        }
    })
    .filter('replaceWorflowToTask', function () {
        return function (input) {
            if (input) {
                var val = input.toLowerCase();
                if (val.indexOf('workflow') !== -1) {
                    // if(val.includes("workflow")){
                    val = val.replace("workflow", "task");
                    return val;
                } else return val;
            }
            return '';
        }
    })
    .directive('providerActive', function () {
        return {
            link: function (scope, element) {
                //element.addClass('provider-active');
                //element.parent().find('.provider-active').removeClass('provider-active');
                element.bind('click', function () {
                    element.parent().find('.provider-active').removeClass('provider-active');
                    element.addClass('provider-active');
                });
            },
        };
    })
    .directive("datePicker", [function () {
        return {
            restrict: "A",
            link: function (scope, element, attr) {
                jQuery('#appointment').datetimepicker({
                    format: 'DD/MM/YYYY',
                    //pickTime: false,
                    icons: {
                        time: "fa fa-clock-o",
                        date: "fa fa-calendar",
                        up: "fa fa-arrow-up",
                        down: "fa fa-arrow-down"
                    }
                });
            }
        }
    }])
    // .filter('utcToLocal',function($filter,moment){
    //     return function(dateString,format){  
    //         console.log("as",format)  
    //         var utcDate=moment.utc(dateString).toDate();
    //         if(!format||format=='')
    //             format='medium';

    // switch(format){
    //     case 'date':
    //         format='MMM d, y';
    //         break;
    //     case 'time':
    //         format='h:mm:ss a';
    //         break;
    //     case 'datetime':
    //         format='MMM d, y  HH:mm:ss';
    //         break;
    //     case 'iso':
    //         format='y-MM-dd';
    //         break;
    //     case 'ymd':
    //         format=' y-MM-dd';
    //         break;
    //     default:
    //         break;
    // }
    //         if(!dateString||dateString==''){
    //             return dateString;
    //         }else{
    //             var utcDate=moment.utc(dateString).toDate();
    //             var date=$filter('date')(utcDate,format);
    //             if(date == 'Invalid Date')return null;
    //             else return date;
    //         }
    //     };
    // })

    .filter('utcToLocal', function ($filter, moment) {
        return function (dateString, format) {
            if (dateString == "---" || dateString == null) {
                return "---";
            }


            var toLocal = false;
            if (!format || format == '')
                format = 'medium';
            if (format && format != '' && (format == "toLocalTime" || format == 'toLocalDate')) {
                toLocal = true;
            }

            switch (format) {
                case 'date':
                    format = 'MMM D, Y';
                    break;
                case 'time':
                    format = 'h:mm:ss a';
                    break;
                case 'datetime':
                    format = 'MMM D, Y HH:mm:ss';
                    break;
                case 'iso':
                    format = 'Y-MM-dd';
                    break;
                case 'ymd':
                    format = ' Y-MM-dd';
                    break;
                case 'toLocalTime':
                    format = 'MMM d, y HH:mm:ss';
                    break;
                case 'toLocalDate':
                    format = 'MMM d, y';
                    break;

                default:
                    break;
            }
            if (!dateString || dateString == '') {
                return dateString;
            } else {
                if (toLocal) {
                    var utcDate = moment.utc(dateString).toDate();
                    var date = $filter('date')(utcDate, format);
                }
                else {
                    var utcDate = moment.utc(dateString).format(format);
                    var date = $filter('date')(utcDate, format);
                }
                if (date == 'Invalid Date') return null;
                else return date;
            }
        };
    })

    .directive('nT', ['$location', 'AuthService', 'userService', function ($location, AuthService, userService, $rootScope, $state) {
        return {
            restrict: 'A',
            link: function ($scope, elem, attr) {
                elem.on('click', function () {
                    var attrData = angular.fromJson(attr.nT);
                    var data = {
                        'action_name': attrData.a_n,
                        'module_type': attrData.m_t,
                        'action_description': attrData.a_d,
                    }
                    var val = attrData.valid;
                    if (AuthService.getFields().data.parent) {
                        data.user_id = AuthService.getFields().data.parent.id_user;
                        data.acting_user_id = AuthService.getFields().data.data.id_user;
                    }
                    else data.user_id = AuthService.getFields().data.data.id_user;
                    data.action_url = $location.absUrl();
                    if (AuthService.getFields().access_token != undefined) {
                        var s = AuthService.getFields().access_token.split(' ');
                        data.access_token = s[1];
                    }
                    else data.access_token = '';
                    if (val) {
                        //console.log('data',data);
                        userService.accessLogs(data).then(function (result) {
                            if (result.status) { }
                        });
                    }
                });
            }
        };
    }])
    .directive('onlyDigits', function () {
        return {
            require: 'ngModel',
            restrict: 'A',
            link: function (scope, element, attr, ctrl) {
                function inputValue(val) {
                    if (val || val != ' ') {
                        var digits = val.replace(/[^0-9]/g, '');
                        if (digits !== val) {
                            ctrl.$setViewValue(digits);
                            ctrl.$render();
                        }
                        return parseInt(digits, 10);
                    }
                    return undefined;
                }

                ctrl.$parsers.push(inputValue);
            }
        };
    })

    .directive('numericOnly', function () {
        return {
            require: 'ngModel',
            link: function (scope, element, attrs, modelCtrl) {

                modelCtrl.$parsers.push(function (inputValue) {
                    //   var transformedInput = inputValue ? inputValue.replace(/[^\d,.-]/g,'') : null;
                    var transformedInput = inputValue ? inputValue.replace(/(,.*?),(.*,)?/, "$1") : null;
                    if (transformedInput != inputValue) {
                        modelCtrl.$setViewValue(transformedInput);
                        modelCtrl.$render();
                    }
                    //clear beginning 0  
                    if (transformedInput == 0) {
                        modelCtrl.$setViewValue(null);
                        modelCtrl.$render();
                    }
                    return transformedInput;
                });
            }
        };
    })
    .directive('alphaNumeric', function () {
        return {
            require: 'ngModel',
            restrict: 'A',
            link: function (scope, element, attr, ctrl) {
                function inputValue(val) {
                    if (val || val != ' ') {
                        var digits = val.replace(/[^a-zA-Z0-9]+$/g, '').replace(' ', ' ');
                        if (digits !== val) {
                            ctrl.$setViewValue(digits);
                            ctrl.$render();
                        }
                        return digits;
                    }
                    return undefined;
                }
                ctrl.$parsers.push(inputValue);
            }
        };
    })
    .directive('onlyText', function () {
        return {
            require: 'ngModel',
            restrict: 'A',
            link: function (scope, element, attr, ctrl) {
                function inputValue(val) {
                    if (val || val != ' ') {
                        var digits = val.replace(/[^a-zA-Z]+$/g, '').replace(' ', ' ');
                        if (digits !== val) {
                            ctrl.$setViewValue(digits);
                            ctrl.$render();
                        }
                        return digits;
                    }
                    return undefined;
                }
                ctrl.$parsers.push(inputValue);
            }
        };
    })


    .directive('numericComma', function () {
        return {
            require: 'ngModel',
            restrict: 'A',
            link: function (scope, element, attr, ctrl) {
                function inputValue(val) {
                    if (val || val != ' ') {
                        var digits = val.replace(/[^0-9,]+$/g, '').replace(' ', ' ');
                        //var digits=val.replace(/^0+$/g,'').replace(' ',' ');
                        if (digits.split(',').length > 2) {
                            digits = digits.substring(0, digits.length - 1);
                        }


                        if (digits !== val) {
                            ctrl.$setViewValue(digits);
                            ctrl.$render();
                        }
                        return digits;
                    }
                    return undefined;
                }
                ctrl.$parsers.push(inputValue);
            }
        };
    })

    .directive('autoFocus', ['$timeout', function ($timeout) {
        return {
            restrict: 'A',
            link: function ($scope, $element) {
                $timeout(function () {
                    $element[0].focus();
                });
            }
        }
    }])
    .directive('myDate', function (dateFilter) {
        return {
            restrict: 'EAC',
            require: '?ngModel',
            link: function (scope, element, attrs, ngModel) {
                ngModel.$parsers.push(function (viewValue) {
                    return dateFilter(viewValue, 'yyyy-MM-dd');
                });
            }
        };

    })
    .directive('loading', ['$http', 'httpLoader', '$rootScope', function ($http, httpLoader, $rootScope) {
        return {
            restrict: 'A',
            link: function (scope, elm, attrs) {
                scope.isLoading = function () {
                    //  console.log(httpLoader.getPendingReqs(),'httpLoader.getPendingReqs',$http.pendingRequests.length);
                    // return $http.pendingRequests.length;
                    return ($http.pendingRequests.filter(p => p.loading !== false)).length;
                };
                scope.$watch(scope.isLoading, function (v) {
                    //console.log(v);
                    if (v > 0) {
                        $(elm).fadeIn();
                    } else {
                        $rootScope.hidePreloader = false;
                        $(elm).fadeOut();
                    }
                });
            }
        };
    }])
    .filter('nlToArray', function () {
        var span = document.createElement('span');
        return function (text) {
            var lines = text.split(',');
            for (var i = 0; i < lines.length; i++) {
                span.innerText = lines[i];
                span.textContent = lines[i];
                lines[i] = span.innerHTML;
            }
            //console.log('lines',lines.join('\n'));
            return lines.join('\n');
        };
    })
    .filter('splitText', function ($sce) {
        return function (data, strLength) {
            if (data.trim().length > 0) {
                var d = data.match(new RegExp('.{1,' + strLength + '}', 'g'));
                if (d.length == 1) {
                    return $sce.trustAsHtml(d[0]);
                }
                else if (d.length > 2) {
                    d[1] = d[1].substring(0, (strLength - 3));
                    return $sce.trustAsHtml(d[0] + '<br/>' + d[1] + '...');
                } else {
                    return $sce.trustAsHtml(d[0] + '...');
                }
            }
        };
    })
    .filter('currencyFormat', function ($filter) {
        return function (numberData, currencyFormat) {
            if (numberData) {
                //console.log('data',numberData, currencyFormat);
                //console.log('data output;', $filter('number')(numberData, fractionSize));
                var fractionSize = '';
                var returnData = '';
                if (currencyFormat === 'EUR') {
                    fractionSize = '2';
                    returnData = $filter('number')(numberData, fractionSize);
                    //  console.log('returnData',returnData);
                    var splitData = returnData.split('.');
                    splitData[0] = (splitData[0].split(',')).join('.');
                    //  console.log('splitData.join()',splitData.join());
                    return splitData.join();
                } else {
                    return numberData;
                }
                //var d = data.match(new RegExp('.{1,' + strLength + '}', 'g'));
                /*if(d.length==1){
                    return $sce.trustAsHtml(d[0]);
                }
                else if(d.length>2){
                    d[1] = d[1].substring(0, (strLength-3));
                    return $sce.trustAsHtml(d[0]+'<br/>'+d[1]+'...');
                }else{
                    return $sce.trustAsHtml(d[0]+'...');
                }*/
                //return data;
            }
        };
    })
    .directive('changeColor', function () {
        return {
            restrict: 'AE',
            scope: {
                ngModel: '='
            },
            link: function (scope, element, attr, ngModel) {
                element.bind('click', function () {

                });
            }
        }
    })
    .filter('numberFormat', function ($filter) {
        return function (numberData) {
            if (numberData) {
                //console.log('data===**',numberData);
                //console.log('data output;', $filter('number')(numberData, fractionSize));
                var fractionSize = '';
                var returnData = '';
                fractionSize = '0';
                returnData = $filter('number')(numberData, fractionSize);
                // console.log('returnData',returnData);
                var splitData = returnData.split('.');
                splitData[0] = (splitData[0].split(',')).join(',');
                // console.log('splitData.join()',splitData.join());
                return splitData.join();

                //var d = data.match(new RegExp('.{1,' + strLength + '}', 'g'));
                /*if(d.length==1){
                    return $sce.trustAsHtml(d[0]);
                }
                else if(d.length>2){
                    d[1] = d[1].substring(0, (strLength-3));
                    return $sce.trustAsHtml(d[0]+'<br/>'+d[1]+'...');
                }else{
                    return $sce.trustAsHtml(d[0]+'...');
                }*/
                //return data;
            }
        };
    })
    .service('anchorSmoothScroll', function () {
        this.scrollTo = function (eID) {

            // This scrolling function
            // is from http://www.itnewb.com/tutorial/Creating-the-Smooth-Scroll-Effect-with-JavaScript
            var startY = currentYPosition();
            var stopY = elmYPosition(eID);
            var distance = stopY > startY ? stopY - startY : startY - stopY;
            if (distance < 100) {
                scrollTo(0, stopY);
                return;
            }
            var speed = Math.round(distance / 100);
            if (speed >= 20) speed = 20;
            var step = Math.round(distance / 25);
            var leapY = stopY > startY ? startY + step : startY - step;
            var timer = 0;
            if (stopY > startY) {
                for (var i = startY; i < stopY; i += step) {
                    setTimeout("window.scrollTo(0, " + leapY + ")", timer * speed);
                    leapY += step;
                    if (leapY > stopY) leapY = stopY;
                    timer++;
                }
                return;
            }
            for (var i = startY; i > stopY; i -= step) {
                setTimeout("window.scrollTo(0, " + leapY + ")", timer * speed);
                leapY -= step;
                if (leapY < stopY) leapY = stopY;
                timer++;
            }
            function currentYPosition() {
                // Firefox, Chrome, Opera, Safari
                if (self.pageYOffset) return self.pageYOffset;
                // Internet Explorer 6 - standards mode
                if (document.documentElement && document.documentElement.scrollTop)
                    return document.documentElement.scrollTop;
                // Internet Explorer 6, 7 and 8
                if (document.body.scrollTop) return document.body.scrollTop;
                return 0;
            }

            function elmYPosition(eID) {
                var elm = document.getElementById(eID);
                // console.log('elm', eID);
                var y = elm.offsetTop - 165;
                var node = elm;
                while (node.offsetParent && node.offsetParent != document.body) {
                    node = node.offsetParent;
                    y += node.offsetTop;
                }
                return y;
            }
        };
    })
    .directive('progressBar', function () {
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                var watchFor = attrs.progressBarWatch;

                // update now
                var val = scope[watchFor];
                element.attr('aria-valuenow', val)
                    .css('width', val + "%");

                // watch for the value
                scope.$watch(watchFor, function (val) {
                    element.attr('aria-valuenow', val)
                        .css('width', val + "%");
                })
            }
        }
    })
    .directive('parentActive', function ($rootScope, $state) {
        return {
            link: function (scope, element) {
                var leng = element.find('ul li.active').length;
                scope.$watch(function () {
                    return element.html();
                }, function () {
                    var leng = element.find('ul li.active').length;
                    if (leng > 0) {
                        element.addClass('active parent-sub');
                    } else {
                        element.removeClass('active parent-sub');
                    }
                });
            },
        };
    })
    .directive('ngEnter', function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    scope.$apply(function () {
                        scope.$eval(attrs.ngEnter);
                    });

                    event.preventDefault();
                }
            });
        };
    })
//Window.prototype.btoa = function(decodedData) {};
//Window.prototype.atob = function(encodedData) {};
//Prevent click if href="#"
function preventClickDirective() {
    var directive = {
        restrict: 'E',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {
        if (attrs.href === '#') {
            element.on('click', function (event) {
                event.preventDefault();
            });
        }
    }
}

//Bootstrap Collapse
function bootstrapCollapseDirective() {
    var directive = {
        restrict: 'E',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {
        if (attrs.toggle == 'collapse') {
            element.attr('href', 'javascript;').attr('data-target', attrs.href.replace('index.html', ''));
        }
    }
}

/**
* @desc Genesis main navigation - Siedebar menu
* @example <li class="nav-item nav-dropdown"></li>
*/
function navigationDirective() {
    var directive = {
        restrict: 'E',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {
        if (element.hasClass('nav-dropdown-toggle') && angular.element('body').hasClass('sidebar-nav') && angular.element('body').width() > 782) {
            element.on('click', function () {
                if (!angular.element('body').hasClass('compact-nav')) {
                    element.parent().toggleClass('open').find('.open').removeClass('open');
                }
            });
        } else if (element.hasClass('nav-dropdown-toggle') && angular.element('body').width() < 783) {
            element.on('click', function () {
                element.parent().toggleClass('open').find('.open').removeClass('open');
            });
        }
    }
}

//Dynamic resize .sidebar-nav
sidebarNavDynamicResizeDirective.$inject = ['$window', '$timeout'];
function sidebarNavDynamicResizeDirective($window, $timeout) {
    var directive = {
        restrict: 'E',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {

        if (element.hasClass('sidebar-nav') && angular.element('body').hasClass('fixed-nav')) {
            var bodyHeight = angular.element(window).height();
            scope.$watch(function () {
                var headerHeight = angular.element('header').outerHeight();

                if (angular.element('body').hasClass('sidebar-off-canvas')) {
                    element.css('height', bodyHeight);
                } else {
                    element.css('height', bodyHeight - headerHeight);
                }
            })

            angular.element($window).bind('resize', function () {
                var bodyHeight = angular.element(window).height();
                var headerHeight = angular.element('header').outerHeight();
                var sidebarHeaderHeight = angular.element('.sidebar-header').outerHeight();
                var sidebarFooterHeight = angular.element('.sidebar-footer').outerHeight();

                if (angular.element('body').hasClass('sidebar-off-canvas')) {
                    element.css('height', bodyHeight - sidebarHeaderHeight - sidebarFooterHeight);
                } else {
                    element.css('height', bodyHeight - headerHeight - sidebarHeaderHeight - sidebarFooterHeight);
                }
            });
        }
    }
}

//LayoutToggle
layoutToggleDirective.$inject = ['$interval'];
function layoutToggleDirective($interval) {
    var directive = {
        restrict: 'E',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {
        element.on('click', function () {

            var bodyClass = localStorage.getItem('body-class');

            if ((element.hasClass('layout-toggler') || element.hasClass('sidebar-close')) && angular.element('body').hasClass('sidebar-off-canvas')) {
                angular.element('body').toggleClass('sidebar-opened').parent().toggleClass('sidebar-opened');

                $interval(function () {
                    window.dispatchEvent(new Event('resize'));
                }, 100, 5)

            } else if (element.hasClass('layout-toggler') && (angular.element('body').hasClass('sidebar-nav') || bodyClass == 'sidebar-nav')) {
                //angular.element('body').toggleClass('sidebar-nav');
                localStorage.setItem('body-class', 'sidebar-nav');
                if (bodyClass == 'sidebar-nav') {
                    if ($localStorage.curUser.data.isSamlLogin == true) {
                        var samlLogoutUrl = $localStorage.curUser.data.SamlLogOutUrl;
                        localStorage.clear();
                        $window.location.href = samlLogoutUrl;
                    }
                    else {
                        localStorage.clear();
                    }
                }

                $interval(function () {
                    window.dispatchEvent(new Event('resize'));
                }, 100, 5)
            }

            if (element.hasClass('aside-toggle')) {
                angular.element('body').toggleClass('aside-menu-open');

                $interval(function () {
                    window.dispatchEvent(new Event('resize'));
                }, 100, 5)
            }
        });
    }
}

//Collapse menu toggler
function collapseMenuTogglerDirective() {
    var directive = {
        restrict: 'E',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {
        element.on('click', function () {
            if (element.hasClass('navbar-toggler') && !element.hasClass('layout-toggler')) {
                angular.element('body').toggleClass('mobile-open')
            }
        })
    }
}

//Bootstrap Carousel
function bootstrapCarouselDirective() {
    var directive = {
        restrict: 'E',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {
        if (attrs.ride == 'carousel') {
            element.find('a').each(function () {
                $(this).attr('data-target', $(this).attr('href').replace('index.html', '')).attr('href', 'javascript;;')
            });
        }
    }
}

//Bootstrap Tooltips & Popovers
function bootstrapTooltipsPopoversDirective() {
    var directive = {
        restrict: 'A',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {
        if (attrs.toggle == 'tooltip') {
            angular.element(element).tooltip();
        }
        if (attrs.toggle == 'popover') {
            angular.element(element).popover();
        }
    }
}

//Bootstrap Tabs
function bootstrapTabsDirective() {
    var directive = {
        restrict: 'A',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {
        element.click(function (e) {
            e.preventDefault();
            angular.element(element).tab('show');
        });
    }
}

//Card Collapse
function cardCollapseDirective() {
    var directive = {
        restrict: 'E',
        link: link
    }
    return directive;

    function link(scope, element, attrs) {
        if (attrs.toggle == 'collapse' && element.parent().hasClass('card-actions')) {

            if (element.parent().parent().parent().find('.card-block').hasClass('in')) {
                element.find('i').addClass('r180');
            }

            var id = 'collapse-' + Math.floor((Math.random() * 1000000000) + 1);
            element.attr('data-target', '#' + id)
            element.parent().parent().parent().find('.card-block').attr('id', id);

            element.on('click', function () {
                element.find('i').toggleClass('r180');
            })
        }
    }
}
