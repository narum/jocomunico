angular.module('app', [
	//Core
	'ngRoute',
	'ngResource',
	'ngCookies',
        'ngDraggable',
        'ngTouch',
	'ui.bootstrap',
        'ngDialog',
        'ngScrollbar',
        'angular-bind-html-compile',
        
	//Modules
	'controllers',
	'services',
        'udpCaptcha'

])
.config(function($httpProvider, $routeProvider, $locationProvider) {
    $httpProvider.defaults.withCredentials = true;
	// $locationProvider.html5Mode(true);

	// Rutas de los diferentes html

	$routeProvider
		.when('/login', {
			controller:'LoginCtrl',
			templateUrl:'../../angular_templates/login.html'
		})
		.when('/', {
			controller:'myCtrl',
			templateUrl:'../../angular_templates/MainBoard.html'
		})
		.when('/register', {
			controller:'RegisterCtrl',
			templateUrl:'../../angular_templates/register.html'
		})
		.when('/userConfig', {
			controller:'UserConfCtrl',
			templateUrl:'../../angular_templates/userConfig.html'
		})
                .when('/registerComplete', {
			controller:'RegisterCtrl',
			templateUrl:'../../angular_templates/registerComplete.html'
		})
                .when('/emailValidation/:emailKey/:id', {
			controller:'emailValidationCtrl',
			templateUrl:'../../angular_templates/emailValidation.html'
		})
                .when('/emailSended', {
			controller:'LoginCtrl',
			templateUrl:'../../angular_templates/emailSended.html'
		})
                .when('/passRecovery/:emailKey/:id', {
			controller:'passRecoveryCtrl',
			templateUrl:'../../angular_templates/passRecovery.html'
		})
                .when('/panelGroups', {
			controller:'panelCtrl',
			templateUrl:'../../angular_templates/PanelGroups.html'
		})
                .when('/addWord', {
			controller:'addWordCtrl',
			templateUrl:'../../angular_templates/addWord.html'
		})
                .when('/historic', {
			controller:'historicCtrl',
			templateUrl:'../../angular_templates/HistoricView.html'
		})
                .when('/sentencesFolder/:folderId/', {
			controller:'sentencesFolderCtrl',
			templateUrl:'../../angular_templates/sentencesFolder.html'
		})
                .when('/home', {
			controller:'infoCtrl',
			templateUrl:'../../angular_templates/Home.html'
		})
                .when('/about', {
			controller:'infoCtrl',
			templateUrl:'../../angular_templates/About.html'
		})
                .when('/privacy', {
			controller:'infoCtrl',
			templateUrl:'../../angular_templates/Privacy.html'
		})
                .when('/partners', {
			controller:'infoCtrl',
			templateUrl:'../../angular_templates/Partners.html'
		})
                .when('/cc', {
			controller:'infoCtrl',
			templateUrl:'../../angular_templates/CC.html'
		})
                .when('/faq', {
			controller:'faqCtrl',
			templateUrl:'../../angular_templates/Faq.html'
		})
                .when('/tips', {
			controller:'consellsCtrl',
			templateUrl:'../../angular_templates/Consells.html'
		})
                .when('/download', {
			controller:'downloadCtrl',
			templateUrl:'../../angular_templates/Download.html'
		})
		.otherwise({ redirectTo:'/' });
})
.run(function(AuthService){

	//Comprobamos el token para el login en services.js
	AuthService.init();
})
//Function to filter Duplicates From ng-repeat List
.filter('unique', function() {
    return function(collection, keyname) {
       var output = [], 
           keys = [];

       angular.forEach(collection, function(item) {
           var key = item[keyname];
           if(keys.indexOf(key) === -1) {
               keys.push(key);
               output.push(item);
           }
       });

       return output;
    };
});

