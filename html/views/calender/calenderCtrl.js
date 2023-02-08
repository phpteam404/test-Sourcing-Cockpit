angular.module('app', ['localytics.directives', 'mwl.calendar', 'ui.bootstrap'])
    .controller('calenderCtrl', function ($rootScope,$localStorage,$translate) {
        $rootScope.module = '';
        $rootScope.displayName = '';
        $rootScope.breadcrumbcolor='';
        $rootScope.class='';
        $rootScope.icon='';

        if($localStorage.curUser.data.data.language_iso_code){
            $translate.use($localStorage.curUser.data.data.language_iso_code)
        }else if($localStorage.curUser.data.data.language_iso_code=='' || $localStorage.curUser.data.data.language_iso_code==undefined){
            $translate.use('en');
        }

    })
    .controller('calendarCtrlLatest', function ($state, $scope,$filter, $rootScope,$localStorage, $uibModal, dateFilter, $location, $filter,$sce, encode, moment, userService,
         $timeout, moduleService,calendarConfig, calenderService, projectService,relationCategoryService, contractService, $compile,$injector) {
        var vm = this;
        $scope.plannedList = [];
        $scope.filter = {};
        $scope.filter.search_key = '';
        $scope.reviewEvents = [];
        $scope.workflowEvents = [];
        vm.calendarView = 'month';
        // vm.viewDate = moment().utcOffset(0, false).toDate();
        vm.viewDate = new Date();
        console.log("h",vm);
        $scope.resetFilter = false;
        $scope.resetFilter1 = false;

        $scope.dynamicPopover = { templateUrl: 'myPopoverTemplate.html' };
        $scope.dynamicPopover3 = { templateUrl: 'myPopoverTemplate3.html' };
        $scope.dynamicPopover2 = {templateUrl: 'myPopoverTemplate2.html'};

        calendarConfig.i18nStrings.weekNumber = '';
        calendarConfig.showTimesOnWeekView = true;

        moment.locale('en_gb', {
            week : {
              dow : 1 // Monday is the first day of the week
            }
        });

        $scope.monthsList = [
            { name: 'January', value: 0 },
            { name: 'February', value: 1 },
            { name: 'March', value: 2 },
            { name: 'April', value: 3 },
            { name: 'May', value: 4 },
            { name: 'June', value: 5 },
            { name: 'July', value: 6 },
            { name: 'August', value: 7 },
            { name: 'September', value: 8 },
            { name: 'October', value: 9 },
            { name: 'November', value: 10 },
            { name: 'December', value: 11 }
        ];


        $scope.activityPlanning =false;
        $scope.planActivity = function(){
            $scope.activityPlanning =  !$scope.activityPlanning;
            var parent = document.getElementById("calendar-planning");
            var parent1 = document.getElementById("plan-activity");
            if($scope.activityPlanning){
                 parent.classList.add('showDivMenu');
                 parent1.className = "fa fa-angle-double-up";
            }else{
                 parent.classList.remove('showDivMenu');
                 parent1.className = "fa fa-angle-double-down";
            }           
        }
        $scope.displayCount = $rootScope.userPagination;
        $scope.callServer = function (tableState) {
            $scope.isLoading = true;
            $scope.emptyTable = false;
            var pagination = tableState.pagination;
            tableState.customer_id = $scope.user1.customer_id;
            $scope.tableStateRef = tableState;
            if (!tableState.date) tableState.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
            if (!tableState.filterType) tableState.filterType = 'month';
            calenderService.getPlannedList(tableState).then(function (result) {
                $scope.plannedList = result.data.data;
                $scope.filter.search_key = '';
                $scope.reviewEvents = result.data.review;
                $scope.workflowEvents = result.data.workflow;
                $scope.emptyTable = false;
                //console.log('$rootScope.userPagination---' , $rootScope.userPagination);
               // $scope.displayCount =  $localStorage.curUser.data.data.display_rec_count;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords1 = result.data.total_count;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_count / $rootScope.userPagination);
                $scope.isLoading = false;
                if (result.data.total_count < 1)
                    $scope.emptyTable = true;
                angular.forEach($scope.plannedList, function (o, i) {
                    o.bu_names = o.bu_name.toString();
                    var recurrence=($filter('translate')('normal.Recurrence'))
                    var recurrence_till=($filter('translate')('calender.recurrence_till'))
                    var str = '<div><div style="text-align:left;"> '+recurrence+' : '+o.recurrence_name+' </div><div style="text-align:left;"> '+recurrence_till+' : '+ dateFilter(o.recurrence_till,'MMM dd,yyyy')+'</div></div>';
                    o.tooltip_text = $sce.trustAsHtml(str);
                });
            });
        }
        $scope.defaultPages1 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServer($scope.tableStateRef);
                    $scope.callServer1($scope.tableStateRef1);
                }                
            });
        }
        $scope.searchPlannedList = function (key, event) {
            if (event.keyCode == 13 && key != '') {
                $scope.resetFilter = true;
                // $scope.callServer($scope.tableStateRef);
                $timeout(function () {
                    $scope.filteredList = [];
                    //console.log('key====', key);
                    angular.forEach($scope.plannedList, function (o, i) {
                        if (o.bu_name && o.bu_name[0] != '') {
                            var str = $filter('lowercase')(o.bu_name.toString());
                            // console.log('***', str.indexOf($filter('lowercase')(key)));
                            var flag = (str.indexOf($filter('lowercase')(key)) !== -1);
                            // var flag = str.includes($filter('lowercase')(key));
                            if (flag) {
                                $scope.filteredList[$scope.filteredList.length] = o;
                            }
                        }
                    });
                    $scope.plannedList = angular.copy($scope.filteredList);
                }, 1000);
            }
        }
        vm.events = [];
        $timeout(function () {
            $scope.getEvents();
        }, 100);
        $scope.monthReviewClick = function (data){
            console.log("review month");
            var str = vm.calendarTitle+ "-"+(data+1) + "-01";
            $scope.resetFilter = true;
            $scope.resetFilter1 = false;
            $scope.resetFilter2 = false;
            $scope.tableStateRef.date = dateFilter(str, 'yyyy-MM-dd');
            $scope.tableStateRef.filterType = (vm.calendarView == 'year') ? 'month' : 'date';
            $scope.tableStateRef.planType = 'Review';
            $scope.tableStateRef.pagination.start = '0';
            $scope.tableStateRef.pagination.number = '10';
            $scope.callServer($scope.tableStateRef);

            var element2 = document.getElementById('action_item');
            element2.classList.remove("active");
            var element = document.getElementById('planning');
            element.classList.add("active");
            $scope.activeForm = 0;
            $(".tab-pane").removeClass("active");
            $(".tab-pane:nth-child(1)").addClass("active");
        }
        $scope.monthWorkflowClick = function (data){
            var str = vm.calendarTitle+ "-"+(data+1) + "-01";
            $scope.resetFilter = true;
            $scope.resetFilter1 = false;
            $scope.resetFilter2 = false;
            $scope.tableStateRef.date = dateFilter(str, 'yyyy-MM-dd');
            $scope.tableStateRef.filterType = (vm.calendarView == 'year') ? 'month' : 'date';
            $scope.tableStateRef.planType = 'Workflow';
            $scope.tableStateRef.pagination.start = '0';
            $scope.tableStateRef.pagination.number = '10';
            $scope.callServer($scope.tableStateRef);

            var element2 = document.getElementById('action_item');
            element2.classList.remove("active");
            var element = document.getElementById('planning');
            element.classList.add("active");
            $scope.activeForm = 0;
            $(".tab-pane").removeClass("active");
            $(".tab-pane:nth-child(1)").addClass("active");
        }
        $scope.monthActionItemClick= function (data){
            var str = vm.calendarTitle+ "-"+(data+1) + "-01";
            $scope.resetFilter1 = true;
            $scope.resetFilter = false;
            $scope.resetFilter2 = false;
            $scope.tableStateRef1.date = dateFilter(str, 'yyyy-MM-dd');
            $scope.tableStateRef1.filterType = (vm.calendarView == 'year') ? 'month' : 'date';
            if (vm.calendarView == 'year') $scope.tableStateRef1.planType = 'Action Item';
            $scope.tableStateRef1.pagination.start = '0';
            $scope.tableStateRef1.pagination.number = '10';
            $scope.callServer1($scope.tableStateRef1);

            var element = document.getElementById('planning');
            element.classList.remove("active");
            var element2 = document.getElementById('action_item');
            element2.classList.add("active");
            $(".tab-pane").removeClass("active");
            $(".tab-pane:nth-child(2)").addClass("active");
            $scope.activeForm = 1;
        }

        $scope.monthObligationClick = function(data){
            var str = vm.calendarTitle+ "-"+(data+1) + "-01";
            $scope.resetFilter2 = true;
            $scope.resetFilter1 = false;
            $scope.resetFilter = false;
            $scope.tableStateRef2.date = dateFilter(str, 'yyyy-MM-dd');
            $scope.tableStateRef2.filterType = (vm.calendarView == 'year') ? 'month' : 'date';
            if (vm.calendarView == 'year') $scope.tableStateRef1.planType = 'Action Item';
            $scope.tableStateRef2.pagination.start = '0';
            $scope.tableStateRef2.pagination.number = '10';
            $scope.callServer2($scope.tableStateRef2);

            var element = document.getElementById('planning');
            element.classList.remove("active");
            var element2 = document.getElementById('action_item');
            element2.classList.remove("active");
            var element3 = document.getElementById('obligations_rights');
            element3.classList.add("active");
            $(".tab-pane").removeClass("active");
            $(".tab-pane:nth-child(3)").addClass("active");
            $scope.activeForm = 2;
        }
        $scope.monthEventClick= function (data){
            $scope.yearVal=vm.calendarTitle;
            var str = vm.calendarTitle+ "-"+(data+1) + "-01";
            vm.viewDate = dateFilter(moment(str,'yyyy-MM-dd').utcOffset(0, false).toDate());
            $scope.resetFilter = true;
            $scope.tableStateRef.date = dateFilter(str, 'yyyy-MM-dd');
            $scope.tableStateRef.filterType = (vm.calendarView == 'year') ? 'month' : 'date';
            $scope.tableStateRef.pagination.start = '0';
            $scope.tableStateRef.pagination.number = '10';
            $scope.callServer($scope.tableStateRef);

            var element2 = document.getElementById('action_item');
            element2.classList.remove("active");
            var element = document.getElementById('planning');
            element.classList.add("active");
            $scope.activeForm = 0;
            $(".tab-pane").removeClass("active");
            $(".tab-pane:nth-child(1)").addClass("active");

            $scope.resetFilter1 = true;
            $scope.tableStateRef1.date = dateFilter(moment(str), 'yyyy-MM-dd').utcOffset(0, false).toDate();
            $scope.tableStateRef1.filterType = (vm.calendarView == 'year') ? 'month' : 'date';
            $scope.tableStateRef1.pagination.start = '0';
            $scope.tableStateRef1.pagination.number = '10';
            $scope.callServer1($scope.tableStateRef1);

            var element = document.getElementById('planning');
            element.classList.remove("active");
            var element2 = document.getElementById('action_item');
            element2.classList.add("active");
            $(".tab-pane").removeClass("active");
            $(".tab-pane:nth-child(2)").addClass("active");
            $scope.activeForm = 1;
        }
        $scope.eventsList = [];
        $scope.getEvents = function (obj) {
            var params = {};
            params.customer_id = $scope.user1.customer_id;
            params.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
            params.filterType = (obj) ? obj : vm.calendarView;
            var view = vm.calendarView;
            calenderService.getPlannedEvents(params).then(function (result) {
                if (result.status ) {
                    vm.events = [];
                    if (result.data.review) {
                        angular.forEach(result.data.review, function (o, i) {
                            var  reviewDate = moment(new Date(o.date.substr(0, 16)));
                            var eventReviewDate = reviewDate.format("YYYY-MM-DD");
                            if (o.count > 0) {
                                obj = {};
                                obj.title = ($filter('translate')('calender.reviews'))+ ':'+ o.count;
                                obj.cssClass= 'review-event';
                                obj.color = calendarConfig.colorTypes.info;
                                 //obj.startsAt = moment(o.date).utcOffset(0, false).toDate();
                                //obj.startsAt = new Date(o.date);
                                obj.startsAt =eventReviewDate;
                                //obj.endsAt = dateFilter(o.date, 'yyyy-MM-dd');
                                obj.resizable = true,
                                obj.incrementsBadgeTotal = (vm.calendarView == 'year') ? true : false;
                                vm.events[vm.events.length] = obj;
                            }
                        });
                    }
                    if (result.data.workflow) {
                        angular.forEach(result.data.workflow, function (o, i) {
                            var  workflowDate = moment(new Date(o.date.substr(0, 16)));
                            var eventworkflowDate = workflowDate.format("YYYY-MM-DD");
                            if (o.count > 0) {
                                obj = {};
                                obj.title = ($filter('translate')('calender.tasks'))+ ':'+ o.count;
                                obj.cssClass ='workflow-event';
                                obj.color = calendarConfig.colorTypes.success;
                                // obj.startsAt = moment(o.date).utcOffset(0, false).toDate();
                                obj.startsAt = eventworkflowDate;
                                obj.resizable = true,
                                obj.incrementsBadgeTotal = (vm.calendarView == 'year') ? true : false;
                                vm.events[vm.events.length] = obj;
                            }
                        });
                    }
                    if (result.data.action_item) {
                        angular.forEach(result.data.action_item, function (o, i) {
                            //console.log('o',o.date);
                            var  actionItemDate = moment(new Date(o.date.substr(0, 16)));
                            var eventActionItemDate = actionItemDate.format("YYYY-MM-DD");
                            if (o.count > 0) {
                                obj = {};
                                obj.title = $filter('translate')('normal.action_items_calender') + ':'+ o.count;
                                obj.cssClass ='action-items-event';
                                obj.color = calendarConfig.colorTypes.warning;
                                 obj.startsAt = eventActionItemDate;
                                //obj.startsAt = moment(o.date).utcOffset(0, false).toDate();
                                //obj.startsAt =new Date(o.date);
                                obj.resizable = true,
                                obj.incrementsBadgeTotal = (vm.calendarView == 'year') ? true : false;
                                vm.events[vm.events.length] = obj;
                            }
                        });
                    }
                    if (result.data.obligations_item) {
                        angular.forEach(result.data.obligations_item, function (o, i) {
                            var  oRDate = moment(new Date(o.date.substr(0, 16)));
                            var eventORDate = oRDate.format("YYYY-MM-DD");
                            if (o.count > 0) {
                                obj = {};
                                obj.title = $filter('translate')('contract.obligation_rights') +':'+ + o.count;
                                obj.cssClass ='obligation-rights-event';
                                obj.color = calendarConfig.colorTypes.special;
                                obj.startsAt = eventORDate;
                                // obj.startsAt = moment(o.date).utcOffset(0, false).toDate();
                                obj.resizable = true,
                                    obj.incrementsBadgeTotal = (vm.calendarView == 'year') ? true : false;
                                vm.events[vm.events.length] = obj;
                            }
                        });
                    }
                    
                }
                $scope.ele1 = angular.element('.cal-year-box');
                if (result.status && params.filterType == 'year') {
                    $scope.eventsList = result.data;
                    angular.element('.cal-year-box').html('');
                    angular.forEach($scope.monthsList, function (i, o) {
                        var _ul = '<ul class="events-list-count" id="events_'+o+'" >';
                        var _div = "";
                        if($scope.eventsList[o].review[0].count>0){

                            var reviews = ($filter('translate')('calender.reviews'))
                            _ul += '<li class="review-event events-list-count-span pointer" uib-tooltip="'+reviews+'" tooltip-placement="top" tooltip-append-to-body="true" id="event_reviews_'+o+'" data-ng-click="$event.stopPropagation();$event.preventDefault();monthReviewClick('+o+')">'+$scope.eventsList[o].review[0].count+'</li>';
                            _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view"  id="months_'+o+'"  >';

                        }else{
                            _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'" >';
                            if($scope.eventsList[o].review[0].count>0 || $scope.eventsList[o].action_item[0].count>0)
                                _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'">';
                        }
                        if($scope.eventsList[o].workflow[0].count>0)
                        {
                            var tasks = ($filter('translate')('user.breadcrumb.tasks'))
                            _ul += '<li class="workflow-event events-list-count-span pointer" uib-tooltip="'+tasks+'"  tooltip-placement="top" tooltip-append-to-body="true" id="event_workflows_'+o+'" data-ng-click="$event.stopPropagation();$event.preventDefault();monthWorkflowClick('+o+')">'+$scope.eventsList[o].workflow[0].count+'</li>';
                            _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'" >';
                        }else
                        { 
                            _div ='<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'" >';
                            if($scope.eventsList[o].review[0].count>0 || $scope.eventsList[o].action_item[0].count>0)
                                _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'" >';
                        }
                        if($scope.eventsList[o].action_item[0].count>0)
                        {
                            var action_items = ($filter('translate')('normal.action_items_calender'))
                            _ul += '<li class="action-items-event events-list-count-span pointer" uib-tooltip="'+action_items+'" tooltip-placement="top" tooltip-append-to-body="true" id="event_actions_'+o+'" data-ng-click="$event.stopPropagation();$event.preventDefault();monthActionItemClick('+o+')">'+$scope.eventsList[o].action_item[0].count+'</li>';
                            _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'" >';
                        }
                        else
                        { 
                            _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'" >';
                            if($scope.eventsList[o].review[0].count>0 || $scope.eventsList[o].action_item[0].count>0)
                                _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'"  >';
                        }
                        if($scope.eventsList[o].obligations_item[0].count>0)
                        {
                            var obligation_rights = ($filter('translate')('contract.obligation_rights'))
                            console.log("jio",obligation_rights);

                            _ul += '<li class="obligation-rights-event events-list-count-span pointer" uib-tooltip="'+obligation_rights+'" tooltip-placement="top" tooltip-append-to-body="true" id="event_actions_'+o+'" data-ng-click="$event.stopPropagation();$event.preventDefault();monthObligationClick('+o+')">'+$scope.eventsList[o].obligations_item[0].count+'</li>';
                            _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'" >';
                        }
                        else
                        { 
                            _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'" >';
                            if($scope.eventsList[o].review[0].count>0 || $scope.eventsList[o].action_item[0].count>0)
                                _div = '<div class="span3 col-md-3 col-xs-6 cal-cell relative events-year-view" id="months_'+o+'"  >';
                        }
                        var _span = "<span>"+i.name +"</span>";
                        _div += _span+"\n"+_ul+"</div>";
                        $scope.ele1.append(_div);
                    });                   
                    var _obj = $($scope.ele1);
                    $injector.invoke(function ($compile) {
                        var div = $compile(_obj);
                        var content = div($scope);
                        $scope.ele1.append(content);
                    });
                    $('.cal-year-box').append($scope.ele1);
                }
                $timeout(function () {
                    var currentMonth = (moment().utcOffset(0, false).toDate()).getMonth();
                    var monthDiv = document.getElementById('months_'+currentMonth);
                    if(monthDiv && (vm.calendarTitle == $scope.current))monthDiv.classList.add("cal-day-today");
                },100);
            });
        }
        $scope.loadYearData = function (view, flag) {            
            $('.cal-month-day').removeClass('active');
            $timeout(function () {
                // console.log('loadYearData---', view);
                // console.log('$rootScope.rowsCount---', $rootScope.rowsCount);
                if (view == 'year') $scope.current = (moment().utcOffset(0, false).toDate()).getFullYear();
                vm.cellIsOpen = false;
                $scope.tableStateRef.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
                if (view == 'year') $scope.tableStateRef.filterType = 'year';
                else $scope.tableStateRef.filterType = 'month';
                $scope.tableStateRef.pagination.start = '0';
                $scope.tableStateRef.pagination.number = '10';
                if (flag) delete $scope.tableStateRef.planType;
                $scope.callServer($scope.tableStateRef);

                $scope.tableStateRef1.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
                if (view == 'year') $scope.tableStateRef1.filterType = 'year';
                else $scope.tableStateRef1.filterType = 'month';
                $scope.tableStateRef1.pagination.start = '0';
                $scope.tableStateRef1.pagination.number = '10';
                if (flag) delete $scope.tableStateRef1.planType;
                $scope.callServer1($scope.tableStateRef1);

                $scope.tableStateRef2.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
                if (view == 'year') $scope.tableStateRef2.filterType = 'year';
                else $scope.tableStateRef2.filterType = 'month';
                $scope.tableStateRef2.pagination.start = '0';
                $scope.tableStateRef2.pagination.number = '10';
                if (flag) delete $scope.tableStateRef2.planType;
                $scope.callServer2($scope.tableStateRef2);

                if (view != 'year') $scope.getEvents();
                if (view == 'year') $scope.getEvents('year');
                
            }, 100);
        }
        $scope.current = (moment().utcOffset(0, false).toDate()).getFullYear();
        vm.viewChangeClicked = function (nextView) {
            $('.cal-month-day').removeClass('active');
            if (nextView === 'day') return false;
            $timeout(function () {                
                console.log(nextView, '--vm.viewDate on timout ---', vm.viewDate);
                if (nextView == 'year') {
                    $scope.current = (moment().utcOffset(0, false).toDate()).getFullYear();
                    return true;
                }
                if (nextView === 'day') return false;
                if (nextView === 'month') {
                    $scope.tableStateRef.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
                    $scope.tableStateRef.filterType = nextView;
                    $scope.tableStateRef.pagination.start = '0';
                    $scope.tableStateRef.pagination.number = '10';
                    delete $scope.tableStateRef.planType;
                    $scope.callServer($scope.tableStateRef);

                    $scope.tableStateRef1.pagination.start = '0';
                    $scope.tableStateRef1.pagination.number = '10';
                    $scope.tableStateRef1.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
                    $scope.tableStateRef1.filterType = nextView;
                    delete $scope.tableStateRef1.planType;
                    $scope.callServer1($scope.tableStateRef1);
                    //console.log(vm.calendarView,'-nextView-', nextView);
                    $scope.getEvents(nextView);
                    if (vm.cellIsOpen) vm.cellIsOpen = false;
                    return true;
                }
            }, 100);
        };
        $scope.resetList = function (flag) {
            console.log('flag info',flag);
            $('.cal-month-day').removeClass('active');
            var str = vm.calendarTitle.split(' ')[1] + '-' + vm.calendarTitle.split(' ')[0] + '-' + '01';
            if (vm.calendarView == 'year') $scope.current = (moment().utcOffset(0, false).toDate()).getFullYear();
            vm.cellIsOpen = false;

        
            if (flag==true) {
                $scope.filter.search_key = '';
                $scope.resetFilter = false;
                $scope.tableStateRef.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
                if (vm.calendarView == 'year') $scope.tableStateRef.filterType = 'year';
                else $scope.tableStateRef.filterType = 'month';
                $scope.tableStateRef.pagination.start = '0';
                $scope.tableStateRef.pagination.number = '10';
                if (flag) delete $scope.tableStateRef.planType;
                $scope.callServer($scope.tableStateRef);

            } 
            
            if(flag==false) {
                $scope.filter.search_key = '';
                $scope.resetFilter1 = false;
                $scope.tableStateRef1.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
                if (vm.calendarView == 'year') $scope.tableStateRef1.filterType = 'year';
                else $scope.tableStateRef1.filterType = 'month';
                $scope.tableStateRef1.pagination.start = '0';
                $scope.tableStateRef1.pagination.number = '10';
                if (flag) delete $scope.tableStateRef1.planType;
                $scope.callServer1($scope.tableStateRef1);
            }

            if(flag=='obligation'){
                $scope.filter.search_key = '';
                $scope.resetFilter2 = false;
                $scope.tableStateRef2.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
                if (vm.calendarView == 'year') $scope.tableStateRef2.filterType = 'year';
                else $scope.tableStateRef2.filterType = 'month';
                $scope.tableStateRef2.pagination.start = '0';
                $scope.tableStateRef2.pagination.number = '10';
                if (flag) delete $scope.tableStateRef2.planType;
                $scope.callServer2($scope.tableStateRef2);
            }
        }
        vm.cellIsOpen = false;
        vm.eventClicked = function (event) {
            console.log('event',event);
            var str = event.title.split(' ')[0];
            if (str != 'Action') {
                $scope.resetFilter = true;
                $scope.resetFilter1 = false;
                $scope.resetFilter2 = false;
                $scope.tableStateRef.date = dateFilter(event.startsAt, 'yyyy-MM-dd');
                $scope.tableStateRef.filterType = (vm.calendarView == 'year') ? 'month' : 'date';
                if(event.title.substring(0,7)=='Reviews')
                    $scope.tableStateRef.planType ='Review';
                else
                    $scope.tableStateRef.planType ='Workflow';
                // $scope.tableStateRef.planType = event.title.split(' ')[0].replace('s', '');
                $scope.tableStateRef.pagination.start = '0';
                $scope.tableStateRef.pagination.number = '10';
                $scope.callServer($scope.tableStateRef);

                var element2 = document.getElementById('action_item');
                element2.classList.remove("active");
                var element = document.getElementById('planning');
                element.classList.add("active");
                $scope.activeForm = 0;
                $(".tab-pane").removeClass("active");
                $(".tab-pane:nth-child(1)").addClass("active");
            }
            if (str == 'Action') {
                $scope.resetFilter1 = true;
                $scope.resetFilter = false;
                $scope.resetFilter2 = false;
                $scope.tableStateRef1.date = dateFilter((moment(event.startsAt).utcOffset(0, false).toDate()), 'yyyy-MM-dd');
                $scope.tableStateRef1.filterType = (vm.calendarView == 'year') ? 'month' : 'date';
                if (vm.calendarView == 'year') $scope.tableStateRef1.planType = event.title.split(' ')[0].replace('s', '');
                $scope.tableStateRef1.pagination.start = '0';
                $scope.tableStateRef1.pagination.number = '10';
                $scope.callServer1($scope.tableStateRef1);

                var element = document.getElementById('planning');
                element.classList.remove("active");
                var element2 = document.getElementById('action_item');
                element2.classList.add("active");
                $(".tab-pane").removeClass("active");
                $(".tab-pane:nth-child(2)").addClass("active");
                $scope.activeForm = 1;
            }
            if (str == 'Obligations') {
                $scope.resetFilter2 = true;
                $scope.resetFilter = false;
                $scope.resetFilter1 = false;
                $scope.tableStateRef2.date = dateFilter((moment(event.startsAt).utcOffset(0, false).toDate()), 'yyyy-MM-dd');
                $scope.tableStateRef2.filterType = (vm.calendarView == 'year') ? 'month' : 'date';
                if (vm.calendarView == 'year') $scope.tableStateRef1.planType = event.title.split(' ')[0].replace('s', '');
                $scope.tableStateRef2.pagination.start = '0';
                $scope.tableStateRef2.pagination.number = '10';
                $scope.callServer2($scope.tableStateRef2);

                var element = document.getElementById('planning');
                element.classList.remove("active");
                var element2 = document.getElementById('action_item');
                element2.classList.remove("active");
                var element3 = document.getElementById('obligations_rights');
                element3.classList.add("active");
                $(".tab-pane").removeClass("active");
                $(".tab-pane:nth-child(3)").addClass("active");
                $scope.activeForm = 2;
            }
            var ele = angular.element.find("[ng-mouseleave='vm.highlightEvent(event, false)']");
            $('.cal-month-day').removeClass('active');            
        };
        vm.toggle = function ($event, field, event) {
            $event.preventDefault();
            $event.stopPropagation();
            event[field] = !event[field];
        };        
        vm.timespanClicked = function (date, cell) {
            console.log(vm.calendarView, '--date--1111', date);
            console.log(vm.calendarView, '--cell--', cell);
            if (vm.calendarView === 'month') {
                if ((vm.cellIsOpen && moment(date).startOf('day').isSame(moment(vm.viewDate).startOf('day'))) || cell.events.length === 0 || !cell.inMonth) {
                    vm.cellIsOpen = false;
                } else {
                    $scope.tableStateRef.date = dateFilter(date, 'yyyy-MM-dd');
                    $scope.tableStateRef.filterType = 'date';
                    $scope.tableStateRef.pagination.start = '0';
                    $scope.tableStateRef.pagination.number = '10';
                    delete $scope.tableStateRef.planType;
                    $scope.callServer($scope.tableStateRef);

                    $scope.tableStateRef1.date = dateFilter(date, 'yyyy-MM-dd');
                    $scope.tableStateRef1.filterType = 'date';
                    $scope.tableStateRef1.pagination.start = '0';
                    $scope.tableStateRef1.pagination.number = '10';
                    $scope.callServer1($scope.tableStateRef1);

                    $scope.getEvents();
                    $scope.resetFilter = true;
                    $scope.resetFilter1 = true;
                    vm.cellIsOpen = false;
                    vm.viewDate = date;
                }
            } else if (vm.calendarView === 'year') {
                if ((vm.cellIsOpen && moment(date).startOf('month').isSame(moment(vm.viewDate).startOf('month'))) || cell.events.length === 0) {
                    vm.cellIsOpen = false;
                } else {
                    console.log("2345");
                    vm.cellIsOpen = true;
                    vm.viewDate = date;
                    $scope.tableStateRef.date = dateFilter(date, 'yyyy-MM-dd');
                    $scope.tableStateRef.filterType = 'month';
                    $scope.tableStateRef.pagination.start = '0';
                    $scope.tableStateRef.pagination.number = '10';
                    delete $scope.tableStateRef.planType;
                    $scope.callServer($scope.tableStateRef);

                    $scope.tableStateRef1.date = dateFilter(date, 'yyyy-MM-dd');
                    $scope.tableStateRef1.filterType = 'month';
                    $scope.tableStateRef1.pagination.start = '0';
                    $scope.tableStateRef1.pagination.number = '10';
                    $scope.callServer1($scope.tableStateRef1);
                    $scope.getEvents();
                    $scope.resetFilter = true;
                    $scope.resetFilter1 = true;
                }
            }
        };


        $scope.goToContractReviwPage = function(row,val){
            $scope.activity_type = row.activity_type;
            $scope.ids=row.contract_id[val];
            $scope.contract_name = row.contract_name[val];
            $scope.review_id = row.contract_review_id[val];
            $scope.workflow_id = row.id_contract_workflow[val];
            $scope.initiated = row.initiated[val];
            $scope.is_workflow = row.is_workflow[val];

            if($scope.activity_type =='project'  && $scope.initiated==true ){
                //console.log('3');
                $state.go('app.projects.project-task',{name: $scope.contract_name,id:encode($scope.ids),rId:encode($scope.review_id),wId:encode( $scope.workflow_id),type:'workflow'})
            }

            if($scope.activity_type =='project' && $scope.initiated==false ){
                //console.log('1');
                $state.go('app.projects.view',{name:$scope.contract_name,id:encode($scope.ids),wId:encode($scope.workflow_id),type:'workflow'});
            }

            if($scope.activity_type =='contract' && $scope.initiated==false && $scope.is_workflow =='1'){
                //console.log('2');
                $state.go('app.contract.view',{name:$scope.contract_name,id:encode($scope.ids),wId:encode($scope.workflow_id),type:'workflow'}); 
            }

            if($scope.activity_type =='contract' && $scope.initiated==true && $scope.is_workflow =='1'){
                //console.log('4');
                $state.go('app.contract.contract-workflow1',{name:$scope.contract_name,id:encode($scope.ids),rId:encode($scope.review_id),wId:encode($scope.workflow_id),type:'workflow'});
            }
             if($scope.activity_type=='contract' && $scope.initiated == true && $scope.is_workflow =='0'){
                $state.go('app.contract.contract-review1',{name:$scope.contract_name,id:encode($scope.ids),rId:encode($scope.review_id),type:'review'});
             }
             if($scope.activity_type=='contract' && $scope.initiated == false && $scope.is_workflow =='0'){
                $state.go('app.contract.view',{name:$scope.contract_name,id:encode($scope.ids),type:'review'});
             }

    
            //console.log($scope.ids);
        }
       
        $scope.addReviewForCalendar = function (title, flag, row) {
            // console.log('title',title);
            console.log('flag',flag);
            // console.log('row',row);
            // if(flag){
            //     $scope.businessUnitField=false;
            // }

            if(title=='add_workflow_calendar_title_project'){
                $scope.enablefield= false;
            }
            else{
                $scope.enablefield= true; 
            }

            if((title=='add_workflow_calendar_title_project' && row != undefined) || (title=='add_workflow_calendar_title' && row !=undefined)){
                console.log("yu")
                $scope.disablefields = true;
            }
            else{
                $scope.disablefields = false;
            }
            $scope.selectedRow = row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'add-review-calendar.html',
                controller: function ($uibModalInstance, $scope, item) {
                    $scope.customOptions = {};
                    $scope.bottom = 'general.save';
                    $scope.action = 'general.add';
                    $scope.update = false;
                    $scope.isEdit = false;
                    $scope.addType = flag;
                    $scope.businessUnitField=true;
                    $scope.validateRecurrence = function () {
                        $scope.options1 = {};
                        var dt = angular.copy(($scope.customOptions.date) ? $scope.customOptions.date : moment().utcOffset(0, false).toDate());
                        if ($scope.customOptions.recurrence == '1') dt.setMonth(dt.getMonth() + 1);
                        if ($scope.customOptions.recurrence == '2') dt.setMonth(dt.getMonth() + 3);
                        if ($scope.customOptions.recurrence == '3') dt.setFullYear(dt.getFullYear() + 1);
                        if ($scope.addType) $scope.customOptions.recurrence_till = null;
                        $scope.options1 = {
                            minDate: dt,
                            showWeeks: false
                        };
                    }
                    if (item) {
                        $scope.bottom = 'general.update';
                        $scope.action = 'general.update';
                        $scope.update = true;
                        $scope.isEdit = true;
                        $scope.businessUnitField=false;
                        $scope.customOptions = angular.copy(item);
                        $scope.customOptions.recurrence = ($scope.customOptions.recurrence == '0') ? '' : $scope.customOptions.recurrence;
                        $scope.customOptions.date = moment($scope.customOptions.date).utcOffset(0, false).toDate();
                        $scope.customOptions.completed_contract_name = $scope.customOptions.completed_contract_name;
                        //$scope.validateRecurrence();
                        $scope.customOptions.recurrence_till = moment($scope.customOptions.recurrence_till).utcOffset(0, false).toDate();
                        $scope.customOptions.review_name = $scope.customOptions.workflow_name;
                    } else {
                        $scope.customOptions.recurrence = ''
                    }
                    $scope.head = 'calender.' + title;

                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.options = {
                        minDate: new Date(),
                        showWeeks: false
                    };
                    $scope.options1 = angular.copy($scope.options);
                    var params = {};
                    var params1 = [];
                    if(item){
                        // $scope.businessUnitField=false;
                        var obj = {};
                        obj = angular.copy(item);
                       if(item.bussiness_unit_id)params.business_ids = obj.bussiness_unit_id.toString();
                        if(item.relationship_category_id)params.relationship_category_id = obj.relationship_category_id.toString();
                       if(item.provider_id)params.provider_ids = obj.provider_id.toString();
                       if(item.contract_id)params.contract_ids = obj.contract_id.toString();
                    }
                    if (!flag) params.is_workflow = true;
                    $scope.getFilters = function (params) {
                        params.customer_id = $scope.user1.customer_id;
                        if ($scope.isEdit) params.id_calender = item.id_calender;
                        if(title=='add_workflow_calendar_title_project') params.type='project';
                        calenderService.smartFilter(params).then(function (result) {
                            if (result.status) {
                                $scope.relationCategory = result.data.relationship_list;
                                $scope.business_units = result.data.business_unit;
                                $scope.provider_relationship_category = result.data.provider_relationship_category;
                                $scope.providers = result.data.provider;
                                $scope.contracts = result.data.contract;
                                $scope.completed_contracts = result.data.completed_contracts;
                            }
                        });
                    }
                    if (!flag) {
                        $scope.workflowsList = [];
                        var obj = {};
                        obj.is_workflow = true;
                        obj.status = 1;
                        moduleService.list(obj).then(function (result) {
                            if (result.status) {
                                $scope.workflowsList = result.data.data;
                            }
                        });
                    }
                    $scope.getFilters(params);
                    $scope.getSmartFilters = function (key) {
                        if (!flag) params1.is_workflow = true;
                        if(title=='add_workflow_calendar_title_project') params.type='project';
                        if (key == "bussiness_unit_id") {
                            $scope.customOptions.relationship_category_id = [];
                            $scope.customOptions.provider_relationship_category_id=[];
                            $scope.customOptions.contract_id = [];
                            $scope.customOptions.provider_id = [];
                        }
                        if (key == "relationship_category_id" && !$scope.isEdit) {
                            $scope.customOptions.contract_id = [];
                            $scope.customOptions.provider_id = [];
                        }
                        if (key == "provider_id")
                            $scope.customOptions.contract_id = [];

                        if ($scope.customOptions.relationship_category_id)
                            params1["relationship_category_id"] = $scope.customOptions.relationship_category_id.toString();

                        if ($scope.customOptions.bussiness_unit_id){
                            $scope.businessUnitField=false;
                            params1["business_ids"] = $scope.customOptions.bussiness_unit_id.toString();
                        }else{
                            $scope.businessUnitField=true;
                            $scope.customOptions.date='';
                            $scope.customOptions.recurrence='';
                            $scope.customOptions.recurrence_till='';
                            $scope.customOptions.auto_initiate='';
                        }
                        if ($scope.customOptions.provider_id)
                            params1["provider_ids"] = $scope.customOptions.provider_id.toString();

                        if ($scope.customOptions.provider_relationship_category_id)
                            params1["provider_relationship_category_id"] = $scope.customOptions.provider_relationship_category_id.toString();

                        if (params1['business_ids'] == '') delete params1['business_ids'];
                        if (params1['relationship_category_id'] == '') delete params1['relationship_category_id'];
                        if (params1['provider_ids'] == '') delete params1['provider_ids'];
                        if(params1['provider_relationship_category_id']=='') delete params1['provider_relationship_category_id'];
                        //console.log('params1---', params1);
                        $scope.getFilters(params1);
                    }
                    $scope.addReview = function (formData) {

                        var data = angular.copy(formData);

                        data.customer_id = $scope.user1.customer_id;
                        data.created_by = $scope.user.id_user;
                        if(title=='add_workflow_calendar_title_project') data.type='project';
                        if ($scope.customOptions.relationship_category_id)
                            data.relationship_category_id = $scope.customOptions.relationship_category_id.toString();
                        if ($scope.customOptions.bussiness_unit_id) {
                            data.business_unit_id = $scope.customOptions.bussiness_unit_id.toString();
                            //delete $scope.customOptions.bussiness_unit_id;
                            delete data.bussiness_unit_id;
                        }
                        if ($scope.customOptions.provider_relationship_category_id)
                            data.provider_relationship_category_id = $scope.customOptions.provider_relationship_category_id.toString();
                        if ($scope.customOptions.provider_id)
                            data.provider_id = $scope.customOptions.provider_id.toString();
                        if ($scope.customOptions.contract_id)
                            data.contract_id = $scope.customOptions.contract_id.toString();
                        data.date = dateFilter($scope.customOptions.date, 'yyyy-MM-dd');
                        if ($scope.customOptions.recurrence_till)
                            data.recurrence_till = dateFilter($scope.customOptions.recurrence_till, 'yyyy-MM-dd');
                        if (!flag) data.is_workflow = true;

                        if (!data.provider_id) delete data.provider_id;
                        if (!data.contract_id) delete data.contract_id;

                        if (flag) {
                            data.workflow_name = data.review_name;
                            delete data.review_name;
                        }
                        if(data.auto_initiate==1 && !$scope.isEdit){
                            // if(flag) var str = '<span style="font-style: normal;">Are you sure you want to automatically initiate ALL reviews in this calendar planning?</span> <br><br><span><b>NOTE :</b>&nbsp; Reviews will be initiated after 10 minutes of successful planning.</span>';
                            // else var str = '<span style="font-style: normal;">Are you sure you want to automatically initiate ALL tasks in this calendar planning?</span><br><br><span><b>NOTE :</b>&nbsp; Tasks will be initiated after 10 minutes of successful planning.</span>';
                            if(flag){
                                var alert1 = ($filter('translate')('normal.alert_review'))
                                var alert2 = ($filter('translate')('normal.alert_review_initiate'))
                                var str = '<span style="font-style: normal;">'+alert1+'</span> <br><br><span><b>NOTE :</b>&nbsp;'+alert2+'</span>'
                            }
                            else{
                                var alert1 = ($filter('translate')('normal.alert_task'))
                                var alert2 = ($filter('translate')('normal.alert_task_initiate'))
                                var str = '<span style="font-style: normal;">'+alert1+'</span> <br><br><span><b>NOTE :</b>&nbsp;'+alert2+'</span>'
                            }
    
                            
                            var modalInstance = $uibModal.open({
                                animation: true,
                                backdrop: 'static',
                                keyboard: false,
                                scope: $scope,
                                openedClass: 'right-panel-modal modal-open adv-search-model',
                                templateUrl: 'confirm-dialog.html',
                                controller: function ($uibModalInstance, $scope) {
                                    $scope.val_data = $sce.trustAsHtml(str);
                                    $scope.saidOk = function(){
                                        $scope.serviceCall(data);
                                        $scope.cancel();
                                    }
                                    $scope.cancel = function () {
                                        $uibModalInstance.close();
                                    };
                                }
                            });
                            /*var r=confirm(htmlVal);
                            if(r){
                                $scope.serviceCall(data);
                            }*/
                        }else {
                            $scope.serviceCall(data);
                        }                        
                    }
                    $scope.serviceCall = function(data){
                        calenderService.addReview(data).then(function (result) {
                            if (result.status) {
                                $scope.activityPlanning =false;
                                $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = ($scope.isEdit) ? 'update' : 'add';
                                if (flag) obj.action_description = 'add$$review$$for$$calendar';
                                if (!flag) obj.action_description = 'add$$task$$for$$calendar';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);

                                $scope.tableStateRef.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
                                $scope.tableStateRef.filterType = vm.calendarView;
                                $scope.tableStateRef.pagination.start = '0';
                                $scope.tableStateRef.pagination.number = '10';
                                $scope.callServer($scope.tableStateRef);
                                console.log("asdf1345");
                               
                                $scope.getEvents();
                                $scope.cancel();
                            } else $rootScope.toast('Error', result.error.message);
                        });
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
        
        $scope.settingModel = function (flag) {
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                templateUrl: 'views/calender/change-setting.html',
                controller: function ($uibModalInstance, $scope, $filter) {
                    $scope.update = false;
                    $scope.title = 'general.create';
                    $scope.bottom = 'general.save';
                    $scope.isEdit = false;
                    $scope.type = flag
                    $scope.setting = {};
                    if (flag) {
                        var params = {};
                        params.customer_id = $scope.user1.customer_id;
                        params.can_review = 1;
                        relationCategoryService.list(params).then(function (result) {
                            //console.log('info',result);
                            $scope.relationCategory = result.data.data;
                        });

                        relationCategoryService.getSettingsData({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                                $scope.setting = {};
                                $scope.setting.days = result.data[0].days;
                                $scope.setting.r2_days = result.data[0].r2_days;
                                $scope.setting.r3_days = result.data[0].r3_days;
                        });
                    } else {
                        calenderService.getWorkflowRemainder({ 'customer_id': $scope.user1.customer_id }).then(function (result) {
                            $scope.setting.days = result.data[0].days;
                            $scope.setting.r2_days = result.data[0].r2_days;
                            $scope.setting.r3_days = result.data[0].r3_days;
                        });
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
                    $scope.addSetting = function (setting) {
                        var params = {};
                        params.updated_by = $scope.user.id_user;
                        params.customer_id = $scope.user1.customer_id;
                        if ($scope.type) {
                            params.relationship_category_id = [];
                            angular.forEach($scope.relationCategory, function (item, key) {
                                var obj = {};
                                obj.id = item.id_relationship_category;
                                obj.days = setting.days;
                                obj.r2_days =setting.r2_days;
                                obj.r3_days =setting.r3_days;
                                params.relationship_category_id[key] = obj;
                            });
                            relationCategoryService.updateSettings(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$Calendar$$reminders';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.cancel();
                                } else $rootScope.toast('Error', result.error, 'error');
                            });
                        } else {
                            params.days = setting.days;
                            params.r2_days = setting.r2_days;
                            params.r3_days = setting.r3_days;
                            calenderService.addWorkflowRemainder(params).then(function (result) {
                                if (result.status) {
                                    $rootScope.toast('Success', result.message);
                                    var obj = {};
                                    obj.action_name = 'update';
                                    obj.action_description = 'update$$Calendar$$task$$reminders';
                                    obj.module_type = $state.current.activeLink;
                                    obj.action_url = $location.$$absUrl;
                                    $rootScope.confirmNavigationForSubmit(obj);
                                    $scope.cancel();
                                } else $rootScope.toast('Error', result.error, 'error');
                            });
                        }
                    }
                },
                resolve: {}
            });
            modalInstance.result.then(function ($data) {
            }, function () {
            });
        };
        $scope.deleteFromPlanning = function (row) {
            var r = confirm("Are you sure that you want to delete the planning ?");
            if (r == true) {
                var obj = {};
                obj.id_calender = row.id_calender;
                if (row.is_workflow) obj.is_workflow = row.is_workflow;
                calenderService.deletePlanned(obj).then(function (result) {
                    if (result.status) {
                        $rootScope.toast('Success', result.data.message);
                        $scope.tableStateRef.pagination.start = '0';
                        $scope.tableStateRef.pagination.number = '10';
                        $scope.callServer($scope.tableStateRef);
                        $scope.getEvents();
                    } else $rootScope.toast('Error', result.data.message);
                });
            }
        }
        $scope.actionItemsList = [];
        $scope.callServer1 = function (tableState) {
            $scope.tableStateRef1 = tableState;
            $scope.isLoading = true;
            var pagination = tableState.pagination;
            tableState.user_role_id = $scope.user1.user_role_id;
            tableState.customer_id = $scope.user1.customer_id;
            tableState.id_user = $scope.user1.id_user;
            tableState.is_calendar =1;
            if (tableState.filterType) tableState.filterType = tableState.filterType;
            else tableState.filterType = vm.calendarView;
            if (tableState.date) tableState.date = tableState.date;
            else tableState.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
            tableState.contract_review_action_item_status = 'open';
            contractService.getAllActionItems(tableState).then(function (result) {
                $scope.actionItemsList = result.data.data;
                $scope.emptyTable2 = false;
                // $scope.displayCount =  $localStorage.curUser.data.data.display_rec_count;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords2 = result.data.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.data.total_records / $rootScope.userPagination);
                $scope.isLoading = false;
                if (result.data.total_records < 1)
                    $scope.emptyTable2 = true;
            })
        }
        $scope.defaultPages2 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServer1($scope.tableStateRef1);
                    $scope.callServer($scope.tableStateRef);
                }                
            });
        }

        $scope.obligationsList = [];
        $scope.callServer2 = function (tableState) {
            $scope.tableStateRef2 = tableState;
            $scope.isObligationLoading = true;
            var pagination = tableState.pagination;
            tableState.user_role_id = $scope.user1.user_role_id;
            tableState.customer_id = $scope.user1.customer_id;
            tableState.id_user = $scope.user1.id_user;
            tableState.calendar=1;
            if (tableState.filterType) tableState.filterType = tableState.filterType;
            else tableState.filterType = vm.calendarView;
            if (tableState.date) tableState.date = tableState.date;
            else tableState.date = dateFilter(vm.viewDate, 'yyyy-MM-dd');
            projectService.getObligations(tableState).then(function (result) {
                $scope.obligationsList = result.data;
                $scope.emptyTable3 = false;
                $scope.displayCount = $rootScope.userPagination;
                $scope.totalRecords3 = result.total_records;
                tableState.pagination.numberOfPages =  Math.ceil(result.total_records / $rootScope.userPagination);
                $scope.isObligationLoading = false;
                if (result.total_records < 1)
                    $scope.emptyTable3 = true;
            })
        }
        $scope.defaultPages3 = function(val){
            userService.userPageCount({'display_rec_count':val}).then(function (result){
                if(result.status){
                    $rootScope.userPagination = val;
                    $scope.callServer2($scope.tableStateRef2);
                    $scope.callServer1($scope.tableStateRef1);
                    $scope.callServer($scope.tableStateRef);
                }                
            });
        }
        $scope.gotToActionItems = function (row) {
            $state.go('app.actionItems', { id: encode(row.id_contract_review_action_item) }, { reload: true, inherit: false });
        }
        $scope.updateContractAction = function (row, type) {
                $scope.type = type;
                $scope.selectedRow = row;
                $scope.data={};
                var modalInstance = $uibModal.open({
                    animation: true,
                    backdrop: 'static',
                    keyboard: false,
                    scope: $scope,
                    openedClass: 'right-panel-modal modal-open',
                    templateUrl: 'views/calender/create-edit-contract-review.html',
                    controller: function ($uibModalInstance, $scope, item) {
                        $scope.update = false;
                        $scope.title = item.action_item;
                        $scope.bottom = 'general.save';
                        $scope.isEdit = false;
                        if (item != 0 &&  item.hasOwnProperty('id_contract_review_action_item')) {
                            $scope.isEdit = true;
                            $scope.submitStatus = true;
                            $scope.data = angular.copy(item);
                            delete $scope.data.comments;
                            $scope.data.due_date = moment($scope.data.due_date).utcOffset(0, false).toDate();
                            $scope.update = true;
                            $scope.bottom = 'general.update';
                            $scope.addaction = false;
                        }else{$scope.data.due_date=moment().utcOffset(0, false).toDate();}
        
                        if($scope.type == 'view')
                            $scope.bottom = 'contract.finish';
                        contractService.getActionItemResponsibleUsers({'contract_id': row.contract_id,'contract_review_id': row.contract_review_id}).then(function(result){
                            $scope.userList = result.data;
                        });
                        $scope.getActionItemById = function(id){
                            contractService.getActionItemDetails({'id_contract_review_action_item':id}).then(function(result){
                                $scope.data = result.data[0];
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

       
        $scope.createObligationRights = function(row){
            $scope.obligations={};
            $scope.selectedRow =row;
            var modalInstance = $uibModal.open({
                animation: true,
                backdrop: 'static',
                keyboard: false,
                scope: $scope,
                openedClass: 'right-panel-modal modal-open',
                // templateUrl: 'create-edit-obligation-rights.html',
                templateUrl:'views/Manage-Users/contracts/create-edit-obligations.html',
                controller: function ($uibModalInstance, $scope,item) {
                  $scope.title ='general.add';
                  $scope.bottom ='general.save'; 
                  //$scope.editField = false;
                  projectService.getRecurrences().then(function(result){
                       $scope.recurrences = result.data;
                  });
    
                   projectService.resendRecurrence().then(function(result){
                        $scope.resend_recurrences = result.data;
                  });
    
                  if(item){
                      $scope.title='general.edit';
                    projectService.getObligations({'id_obligation':row.id_obligation}).then(function(result){
                        $scope.obligations = result.data[0];
                         if($scope.obligations.email_notification==1){$scope.requiredFields=true;}
                        else { $scope.requiredFields=false;}
                       
    
                        if($scope.obligations.calendar==1){$scope.startFields=true;}
                        else { $scope.startFields=false;}
                        if($scope.obligations.recurrence=='Ad-hoc'){
                            $scope.anotherField =false;
                            $scope.defaultField=false;
                            $scope.calendarFields= false;
                            $scope.startFields=false;
                            $scope.enddateField=false;
                            
                        }
                        if($scope.obligations.recurrence=='One-off'){
                            $scope.enddateField=false;
                            $scope.startFields=true;
                            $scope.calendarFields= false;
                        }
    
                        if($scope.obligations.recurrence ='Monthly' || 'Annually' || 'Quarterly' ||'Semi-annually'){
                            $scope.startFields = true;
                            $scope.calendarFields = true;
                        }
    
                        if($scope.obligations.recurrence_start_date)$scope.obligations.recurrence_start_date = moment($scope.obligations.recurrence_start_date).utcOffset(0, false).toDate();
                        if($scope.obligations.recurrence_end_date)$scope.obligations.recurrence_end_date = moment($scope.obligations.recurrence_end_date).utcOffset(0, false).toDate();
                        if($scope.obligations.email_send_start_date)$scope.obligations.email_send_start_date = moment( $scope.obligations.email_send_start_date).utcOffset(0, false).toDate();
                        if($scope.obligations.email_send_last_date)$scope.obligations.email_send_last_date = moment( $scope.obligations.email_send_last_date).utcOffset(0, false).toDate();
    
    
    
                        $scope.options = {
                            minDate: new Date(),
                            showWeeks: false
                        };
                        $scope.options2 = angular.copy($scope.options);
    
                        //console.log($scope.obligations.recurrence_id);
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
                       
                    })
                  }
                 
                
                    $scope.addObligationRights=function(data){
                        params=data;
                        params.contract_id  = decode($stateParams.id);
                        if(params.recurrence_start_date!=null){
                            params.recurrence_start_date = dateFilter(data.recurrence_start_date,'yyyy-MM-dd');
                            $scope.requiredFields= false;
                            $scope.startFields =false;
                        }
                        if(params.recurrence_end_date!=null){
                            params.recurrence_end_date = dateFilter(data.recurrence_end_date,'yyyy-MM-dd');
                            $scope.requiredFields= false;
                            $scope.calendarFields =false;
                        }
    
                        if(params.email_send_start_date){
                            params.email_send_start_date = dateFilter(data.email_send_start_date,'yyyy-MM-dd');
                            $scope.requiredFields= false;
                        }
                        if(params.email_send_last_date!=null){
                            params.email_send_last_date = dateFilter(data.email_send_last_date,'yyyy-MM-dd');
                            $scope.requiredFields= false;
                        }
                        projectService.addObligations(params).then(function (result) {
                            if (result.status) {
                              $rootScope.toast('Success', result.message);
                                var obj = {};
                                obj.action_name = 'Update';
                                obj.action_description = 'Update$$Spend$$Lines$$('+data.action_item+')';
                                obj.module_type = $state.current.activeLink;
                                obj.action_url = $location.$$absUrl;
                                $rootScope.confirmNavigationForSubmit(obj);
                                $scope.cancel();
                                $scope.getObligations($scope.tableStateRef);
                                $scope.getTabsInfo();
                                $scope.init();
                            } else {
                                $rootScope.toast('Error', result.error,'error');
                                
                            }
                        });
                    }
    
                    $scope.getNotification=function(val){
                        //console.log('val',val);
                       if(val=='1'){
                           $scope.requiredFields = true;
                       }
                       else{
                        $scope.requiredFields = false;
                       }
                    }
                    $scope.cancel = function () {
                        $uibModalInstance.close();
                    };
    
    
                    $scope.getCalenderSelected = function(key){
                        // console.log(key);
                        // console.log('calendar',$scope.obligations.calendar);
                        if(key==1 &&  $scope.obligations.recurrence_id=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                            $scope.startFields =true;
                            $scope.calendarFields =false;
                        }
                        else if(key==1 && $scope.obligations.recurrence_id!='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                            $scope.startFields =true;
                            $scope.calendarFields =true;
                        }
                        else{
                            $scope.startFields =false;
                            $scope.calendarFields =false;
                            $scope.obligations.recurrence_end_date ='';
                            $scope.obligations.recurrence_start_date ='';
                        }
                    }
                    $scope.anotherField=true;
                    $scope.defaultField = true;
                    $scope.enddateField = true;
                    $scope.calendarFields=false;
                    $scope.startFields = false;
                    $scope.getDate = function(vali){
                       //console.log(vali);
                        var dt = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date :moment().utcOffset(0, false).toDate());
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
                    $scope.getRecurrenceSelected = function(val){
                        //console.log(val);
                        //console.log('calendar',$scope.obligations.calendar);
                        if($scope.obligations.calendar ==1 && $scope.obligations.recurrence_id=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                            $scope.startFields =true;
                            $scope.calendarFields=false;
                        }
                        else if($scope.obligations.calendar ==1 && $scope.obligations.recurrence_id!='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                            $scope.startFields =true;
                            $scope.calendarFields=true;
                        }
                        else{
                            $scope.startFields =false;
                            $scope.calendarFields=false;
                        }
                        if(val){
                            $scope.obligations.recurrence_start_date='';
                            $scope.obligations.recurrence_end_date='';
                        }
                       if(val=='U2FsdGVkX19UaGVAMTIzNP/rB5zlx1rEJtgL6QYTzis='){ 
                            $scope.obligations.calendar=0;
                            $scope.defaultField = false;
                            $scope.anotherField=false;
                            $scope.enddateField = false;
                            $scope.startFields = false;
                            $scope.calendarFields=false;
                        }
                       else{
                        $scope.defaultField = true;
                        $scope.anotherField=false;
                       }
                       if(val !='U2FsdGVkX19UaGVAMTIzNP/rB5zlx1rEJtgL6QYTzis='){
                         $scope.defaultField = true;
                         $scope.anotherField=true;
                         $scope.enddateField = true;
                      
                       }
                       if(val=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
                        $scope.defaultField = true;
                        $scope.anotherField=true;
                        $scope.enddateField = false;
                       }
                     
                    }
    
    
                    $scope.getEmaildate = function(item){
                       console.log(item);
                    }
                    $scope.options3 = {
                        minDate: new Date(),
                        showWeeks: false
                    };
                    $scope.options4 = angular.copy($scope.options3);
    
                    $scope.emailRecurrence = function(info){
                        //onsole.log(info);
                        var dts = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : moment().utcOffset(0, false).toDate());
                        //console.log(dts);
                        $scope.options4 = {};
                        
                        if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNORw6QCAdeOFRRsfh9uutYk=') dts.setMonth(dts.getMonth() + 1);
                        if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEyQIrGjAnMehP1bBlC0TWw=') dts.setMonth(dts.getMonth() + 3);
                        if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNFoKKPuBpe0GoOwvA+1t9+A=') dts.setMonth(dts.getMonth() + 6);
                        if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=') dts.setFullYear(dts.getFullYear() + 1);
                        $scope.options4 = {
                            minDate: dts,
                            showWeeks: false
                        };
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

        // $scope.editObligationRights = function(row){
        //     $scope.obligations={};
        //     $scope.selectedRow =row;
        //     var modalInstance = $uibModal.open({
        //         animation: true,
        //         backdrop: 'static',
        //         keyboard: false,
        //         scope: $scope,
        //         openedClass: 'right-panel-modal modal-open',
        //         // templateUrl: 'create-edit-obligation-rights.html',
        //         templateUrl:'views/Manage-Users/contracts/create-edit-obligations.html',
        //         controller: function ($uibModalInstance, $scope,item) {
        //           $scope.title ='general.add';
        //           $scope.bottom ='general.save'; 
        //           projectService.getRecurrences().then(function(result){
        //                $scope.recurrences = result.data;
        //           });
    
        //            projectService.resendRecurrence().then(function(result){
        //                 $scope.resend_recurrences = result.data;
        //           });
    
        //           if(item){
        //               $scope.title='general.edit';
        //             projectService.getObligations({'id_obligation':row.id_obligation}).then(function(result){
        //                 $scope.obligations = result.data[0];
        //                  if($scope.obligations.email_notification==1){$scope.requiredFields=true;}
        //                 else { $scope.requiredFields=false;}
                       
    
        //                 if($scope.obligations.calendar==1){$scope.startFields=true;}
        //                 else { $scope.startFields=false;}
        //                 if($scope.obligations.recurrence_start_date)$scope.obligations.recurrence_start_date = new Date($scope.obligations.recurrence_start_date);
        //                 if($scope.obligations.recurrence_end_date)$scope.obligations.recurrence_end_date = new Date( $scope.obligations.recurrence_end_date);
        //                 if($scope.obligations.email_send_start_date)$scope.obligations.email_send_start_date = new Date( $scope.obligations.email_send_start_date);
        //                 if($scope.obligations.email_send_last_date)$scope.obligations.email_send_last_date = new Date( $scope.obligations.email_send_last_date);
        //             })
        //           }
                 
                
        //             $scope.addObligationRights=function(data){
        //                 params=data;
        //                 params.contract_id  = decode($stateParams.id);
        //                 if(params.recurrence_start_date!=null){
        //                     params.recurrence_start_date = dateFilter(data.recurrence_start_date,'yyyy-MM-dd');
        //                     $scope.requiredFields= false;
        //                     $scope.startFields =false;
        //                 }
        //                 if(params.recurrence_end_date!=null){
        //                     params.recurrence_end_date = dateFilter(data.recurrence_end_date,'yyyy-MM-dd');
        //                     $scope.requiredFields= false;
        //                     $scope.calendarFields =false;
        //                 }
    
        //                 if(params.email_send_start_date){
        //                     params.email_send_start_date = dateFilter(data.email_send_start_date,'yyyy-MM-dd');
        //                     $scope.requiredFields= false;
        //                 }
        //                 if(params.email_send_last_date!=null){
        //                     params.email_send_last_date = dateFilter(data.email_send_last_date,'yyyy-MM-dd');
        //                     $scope.requiredFields= false;
        //                 }
        //                 projectService.addObligations(params).then(function (result) {
        //                     if (result.status) {
        //                       $rootScope.toast('Success', result.message);
        //                         var obj = {};
        //                         obj.action_name = 'Update';
        //                         obj.action_description = 'Update$$Spend$$Lines$$('+data.action_item+')';
        //                         obj.module_type = $state.current.activeLink;
        //                         obj.action_url = $location.$$absUrl;
        //                         $rootScope.confirmNavigationForSubmit(obj);
        //                         $scope.cancel();
        //                         $scope.getObligations($scope.tableStateRef);
        //                         $scope.getTabsInfo();
        //                         $scope.init();
        //                     } else {
        //                         $rootScope.toast('Error', result.error,'error');
                                
        //                     }
        //                 });
        //             }
    
        //             $scope.getNotification=function(val){
        //                 //console.log('val',val);
        //                if(val=='1'){
        //                    $scope.requiredFields = true;
        //                }
        //                else{
        //                 $scope.requiredFields = false;
        //                }
        //             }
        //             $scope.cancel = function () {
        //                 $uibModalInstance.close();
        //             };
    
    
        //             $scope.getCalenderSelected = function(key){
        //                 console.log(key);
        //                 if(key=='1' && $scope.obligations.recurrence_id!='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
        //                     $scope.startFields =true;
        //                     $scope.calendarFields=true;
        //                 }
        //                 else{
        //                     $scope.startFields =false;
        //                     $scope.calendarFields =false;
        //                     $scope.obligations.recurrence_end_date ='';
        //                     $scope.obligations.recurrence_start_date ='';
        //                 }
        //             }
        //             $scope.anotherField=true;
        //             $scope.defaultField = true;
        //             $scope.enddateField = true;
        //             $scope.calendarFields=false;
        //             $scope.startFields = false;
        //             $scope.getDate = function(vali){
        //                //console.log(vali);
        //                 var dt = angular.copy(($scope.obligations.recurrence_start_date) ? $scope.obligations.recurrence_start_date : new Date());
        //                 $scope.options2 = {};
                        
        //                 if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNORw6QCAdeOFRRsfh9uutYk=') dt.setMonth(dt.getMonth() + 1);
        //                 if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEyQIrGjAnMehP1bBlC0TWw=') dt.setMonth(dt.getMonth() + 3);
        //                 if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNFoKKPuBpe0GoOwvA+1t9+A=') dt.setMonth(dt.getMonth() + 6);
        //                 if ($scope.obligations.recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=') dt.setFullYear(dt.getFullYear() + 1);
        //                 $scope.options2 = {
        //                     minDate: dt,
        //                     showWeeks: false
        //                 };
        //             }
        //             $scope.options = {
        //                 minDate: new Date(),
        //                 showWeeks: false
        //             };
        //             $scope.options2 = angular.copy($scope.options);
        //             $scope.getRecurrenceSelected = function(val){
        //                 //console.log(val);
        //                 if(val){
        //                     $scope.obligations.recurrence_start_date='';
        //                     $scope.obligations.recurrence_end_date='';
        //                 }
        //                if(val=='U2FsdGVkX19UaGVAMTIzNP/rB5zlx1rEJtgL6QYTzis='){ 
        //                     $scope.obligations.calendar=0;
        //                     $scope.defaultField = false;
        //                     $scope.anotherField=false;
        //                     $scope.enddateField = false;
        //                     $scope.startFields = false;
        //                     $scope.calendarFields=false;
        //                 }
        //                else{
        //                 $scope.defaultField = true;
        //                 $scope.anotherField=false;
        //                }
        //                if(val !='U2FsdGVkX19UaGVAMTIzNP/rB5zlx1rEJtgL6QYTzis='){
        //                  $scope.defaultField = true;
        //                  $scope.anotherField=true;
        //                  $scope.enddateField = true;
                      
        //                }
        //                if(val=='U2FsdGVkX19UaGVAMTIzNDDaNJN32v4dWAIOINSI7pQ='){
        //                 $scope.defaultField = true;
        //                 $scope.anotherField=true;
        //                 $scope.enddateField = false;
        //                }
                     
        //             }
    
    
        //             $scope.getEmaildate = function(item){
        //                console.log(item);
        //             }
        //             $scope.options3 = {
        //                 minDate: new Date(),
        //                 showWeeks: false
        //             };
        //             $scope.options4 = angular.copy($scope.options3);
    
        //             $scope.emailRecurrence = function(info){
        //                 console.log(info);
        //                 var dts = angular.copy(($scope.obligations.email_send_start_date) ? $scope.obligations.email_send_start_date : new Date());
        //                 console.log(dts);
        //                 $scope.options4 = {};
                        
        //                 if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNORw6QCAdeOFRRsfh9uutYk=') dts.setMonth(dts.getMonth() + 1);
        //                 if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEyQIrGjAnMehP1bBlC0TWw=') dts.setMonth(dts.getMonth() + 3);
        //                 if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNFoKKPuBpe0GoOwvA+1t9+A=') dts.setMonth(dts.getMonth() + 6);
        //                 if ($scope.obligations.resend_recurrence_id == 'U2FsdGVkX19UaGVAMTIzNEV1ZvR9jxgyIc/o1qBljiE=') dts.setFullYear(dts.getFullYear() + 1);
        //                 $scope.options4 = {
        //                     minDate: dts,
        //                     showWeeks: false
        //                 };
        //             }
                    
        //         },
        //         resolve: {
        //             item: function () {
        //                 if ($scope.selectedRow) {
        //                     return $scope.selectedRow;
        //                 }
        //             }
        //         }
        //     });
        //     modalInstance.result.then(function ($data) {
        //     }, function () {
        //     });
        // }
       
        $scope.deleteObligation = function(info){
            var r=confirm($filter('translate')('general.alert_continue'));
            $scope.deleConfirm = r;
            if(r==true){
                var params ={};
                params.id_obligation  = info.id_obligation ;
                params.updated_by  = $rootScope.id_user ;            
                projectService.deleteObligations(params).then(function(result){
                    if(result.status){
                        $rootScope.toast('Success', result.message);
                        $scope.callServer2($scope.tableStateRef2);
                        var obj = {};
                        obj.action_name = 'delete';
                        obj.action_description = 'delete$$obligationItem$$('+row.id_obligation+')';
                        obj.module_type = $state.current.activeLink;
                        obj.action_url = $location.$$absUrl;
                        $rootScope.confirmNavigationForSubmit(obj);
                    }else $rootScope.toast('Error', result.error, 'error',$scope.user);
                });
            }
        }
        $scope.goToContractPage = function(row){
            //  if(row.is_workflow=='1')
            //     $state.go('app.contract.view',{name:row.contract_name,id:encode(row.contract_id),wId:encode(row.id_contract_workflow),type:'workflow'});
            //  if(row.is_workflow =='0')
                $state.go('app.contract.view',{name:row.contract_name,id:encode(row.contract_id),type:'review'});
        }
    })