angular.module('controllers')
    .controller('downloadCtrl', function ($scope, $rootScope, $location, $http, ngDialog, dropdownMenuBarInit, AuthService, Resources, $timeout) {

        //Dropdown Menu Bar
            $rootScope.dropdownMenuBar = null;
            if($rootScope.isLogged){
                var languageId = $rootScope.interfaceLanguageId;
                $rootScope.dropdownMenuBarChangeLanguage = false;//Languages button available
            } else {
                var languageId = $rootScope.contentLanguageUserNonLoged;
                $rootScope.dropdownMenuBarChangeLanguage = true;//Languages button available
            }
            dropdownMenuBarInit(languageId)
                    .then(function () {
                        //Choose the buttons to show on bar
                        if ($rootScope.isLogged){
                            angular.forEach($rootScope.dropdownMenuBar, function (value) {
                                if (value.href == '/' || value.href == '/about' || value.href == '/panelGroups' || value.href == '/userConfig' || value.href == '/faq' || value.href == '/download' || value.href == '/tips' || value.href == '/privacy') {
                                    value.show = true;
                                } else {
                                    value.show = false;
                                }
                            });
                        }else{
                            angular.forEach($rootScope.dropdownMenuBar, function (value) {
                                if (value.href == '/home' || value.href == '/about' || value.href == '/faq' || value.href == '/download' || value.href == '/tips' || value.href == '/privacy') {
                                    value.show = true;
                                } else {
                                    value.show = false;
                                }
                            });
                        }
                    });
            $rootScope.dropdownMenuBarValue = '/download'; //Button selected on this view
            $rootScope.dropdownMenuBarButtonHide = false;
            //function to change html view
            $scope.go = function (path) {
                $rootScope.dropdownMenuBarValue = path; //Button selected on this view
                $location.path(path);
            };
            //function to change html content language
            $scope.changeLanguage = function (value) {
                $rootScope.contentLanguageUserNonLoged = value;
                window.localStorage.setItem('contentLanguageUserNonLoged', $rootScope.contentLanguageUserNonLoged);
                window.localStorage.setItem('contentLanguageUserNonLogedAbbr', $rootScope.contentLanguageUserNonLogedAbbr);
                Resources.register.get({'section': 'tips', 'idLanguage': value}, {'funct': "content"}).$promise
                        .then(function (results) {
                            $rootScope.langabbr = $rootScope.contentLanguageUserNonLogedAbbr;
                            $scope.text = results.data;
                            dropdownMenuBarInit(value);
                        });
            };
        
        /*
         * MENU CONFIGURATION
         */

        $scope.linkHome = function () {
            $location.path('/home');
        };

        //partial view
        $scope.view = [];
        $scope.view.Windows = false;
        $scope.view.Mac = false;
        $scope.view.Android = false;
        $scope.view.iOS = false;

        //Images
        $scope.img = [];
        $scope.img.fons = '/img/srcWeb/patterns/fons.png';
        $scope.img.Pattern3 = '/img/srcWeb/patterns/pattern3.png';
        $scope.img.Pattern4 = '/img/srcWeb/patterns/pattern4.png';
        $scope.img.Pattern6 = '/img/srcWeb/patterns/pattern6.png';
        $scope.img.loading = '/img/srcWeb/Login/loading.gif';
        $scope.img.whiteLoading = '/img/icons/whiteLoading.gif';
        $scope.img.Loading_icon = '/img/icons/Loading_icon.gif';
        $scope.img.orangeArrow = '/img/srcWeb/UserConfig/orangeArrow.png';  

        // Language
        $rootScope.langabbr = $rootScope.contentLanguageUserNonLogedAbbr;

        //Images
        $scope.img.button1 = 'img/srcWeb/home/windows.png';
        $scope.img.button2 = 'img/srcWeb/home/mac.png';
        $scope.img.button3 = 'img/srcWeb/home/android.png';
        $scope.img.button4 = 'img/srcWeb/home/ios.png';

        // Link colors
        $scope.link1color = "#3b93af";
        $scope.link2color = "#edb95d";
        $scope.link3color = "#f0a22e";
        $scope.link4color = "#3b93af";

        // Get content for the home view from ddbb   
        Resources.register.get({'section': 'tips', 'idLanguage': $rootScope.contentLanguageUserNonLoged}, {'funct': "content"}).$promise
            .then(function (results) {
                $scope.text = results.data;
            });

        $scope.linkColor = function (id, color, inout) {
            switch(id) {
                case "link-1":
                    $scope.link1color = color;
                    if (!inout) {
                        $scope.img.button1 = 'img/srcWeb/home/windows.png';
                    }
                    else {
                        $scope.img.button1 = 'img/srcWeb/home/windows-hov.png';
                    }
                    break;

                case "link-2":
                    $scope.link2color = color;
                    if (!inout) {
                        $scope.img.button2 = 'img/srcWeb/home/mac.png';
                    }
                    else {
                        $scope.img.button2 = 'img/srcWeb/home/mac-hov.png';
                    }
                    break;

                case "link-3":
                    $scope.link3color = color;
                    if (!inout) {
                        $scope.img.button3 = 'img/srcWeb/home/android.png';
                    }
                    else {
                        $scope.img.button3 = 'img/srcWeb/home/android-hov.png';
                    }
                    break;

                case "link-4":
                    $scope.link4color = color;
                    if (!inout) {
                        $scope.img.button4 = 'img/srcWeb/home/ios.png';
                    }
                    else {
                        $scope.img.button4 = 'img/srcWeb/home/iospress-hov.png';
                    }
                    break;
                    
                default:
                    break;
            }
        };
    });
