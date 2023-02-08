/* ocLazyLoad config */

angular.module('app')
    .config(['$ocLazyLoadProvider', function($ocLazyLoadProvider) {
            $ocLazyLoadProvider.config({
                debug: true,
                events: false,
                modules: [
                    {
                        serie: true,
                        name: 'ui.sortable',
                        files: ['plugins/jquery-ui/jquery-ui.min.js','plugins/angular-ui-sortable/sortable.min.js']
                    },
                    {
                        name: 'attachment',
                        files: ['views/components/attachment/attachment-directive.js']
                    },
                    {
                        // 'https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js',
                        serie: true,
                        name: 'ng-fusioncharts',
                        files: [
                            'plugins/angular-fusionchart/src/fusioncharts.js',
                            'plugins/angular-fusionchart/src/fusioncharts.theme.fint.js',
                            'plugins/angular-fusionchart/src/angular-fusioncharts.js'
                        ]
                    },
                    {
                        name: 'ckeditor',
                        files: ['plugins/ckeditor/ckeditor.js','plugins/ckeditor/angular-ckeditor.js']
                    }
                ]
            })
        }
    ]);