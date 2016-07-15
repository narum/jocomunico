angular.module('services', [])

.factory('Resources', function($rootScope, $resource){
	var baseUri = $rootScope.baseurl; // base URL enviado des de codeigniter mediante ng-init en main.html
	return {

		// Rutas de las API para las peticiones a codeigniter
		
		"nom": $resource(baseUri + "names"),
		"histo": $resource(baseUri + "histo"),
		"login": $resource(baseUri + "login"),
		"register": $resource(baseUri + "register/:funct",{funct:"@funct"}), //Acceder a funciones dentro de Register.php
		"main": $resource(baseUri + "main/:funct",{funct:"@funct"})
	};
})

.factory('AuthService', function($rootScope, $http, $location){

	// Funciones de comprobación del login
	
	return {
		"init": function() {
                        //DropDown Menu Bar
                        $rootScope.contentLanguageUserNonLoged = window.localStorage.getItem('contentLanguageUserNonLoged');
                        $rootScope.contentLanguageUserNonLogedAbbr = window.localStorage.getItem('contentLanguageUserNonLogedAbbr');
                        $rootScope.dropdownMenuBar = [];
                        if(!$rootScope.contentLanguageUserNonLoged||!$rootScope.contentLanguageUserNonLogedAbbr){
                            $rootScope.contentLanguageUserNonLoged = 1; // idioma por defecto al iniciar (catalan)
                            $rootScope.contentLanguageUserNonLogedAbbr = 'CA';
                            window.localStorage.setItem('contentLanguageUserNonLoged', $rootScope.contentLanguageUserNonLoged);
                            window.localStorage.setItem('contentLanguageUserNonLogedAbbr', $rootScope.contentLanguageUserNonLogedAbbr);
                        }
			//Login
			$rootScope.isLogged = false;
                        var token = window.localStorage.getItem('token'); //mirem si hi ha un token al LocalStorage de html5
                        var userConfig = JSON.parse(localStorage.getItem('userData'));
			if(token)
				this.login(token, userConfig);
		},
		"login": function(token, userConfig) {
			window.localStorage.setItem('token', token); // guardem el token al localStorage
                        window.localStorage.setItem('userData', JSON.stringify(userConfig));
			$http.defaults.headers.common['Authorization'] = 'Bearer '+token; // posem el token al header per a totes les peticions
			$http.defaults.headers.common['X-Authorization'] = 'Bearer '+token; // posem el token al header per a totes les peticions
			$rootScope.isLogged = true;
			$rootScope.interfaceLanguageId = userConfig.ID_ULanguage;
			$rootScope.expanLanguageId = userConfig.cfgExpansionLanguage;
                        $rootScope.sUserId = userConfig.ID_SU;
                        $rootScope.userId = userConfig.ID_User;
		},
		"logout": function() {
			window.localStorage.removeItem('token');
                        window.localStorage.removeItem('userData');
			delete $http.defaults.headers.common['Authorization'];
			delete $http.defaults.headers.common['X-Authorization'];
			$rootScope.isLogged = false;
                        $location.path('/login');
                        $rootScope.dropdownMenuBarValue = '/';
		}
	}
})


//Función que retorna el contenido de texto de la vista al pasarle los parametros section y language
.factory('txtContent',  function(Resources, $http, $rootScope){
	return function name(section){
		var languageid = $rootScope.interfaceLanguageId;
		return Resources.main.get({'section':section, 'idLanguage':languageid}, {'funct': "content"}).$promise;
	}
})
//Función que inicializa la barra del menu superior
.factory('dropdownMenuBarInit',  function(Resources, $rootScope, txtContent){
    return function(Language){
        //Get menu bar content
        var content = function(){
            if($rootScope.isLogged){
                return txtContent("menuBar").then(function (results) {
                    $rootScope.dropdownMenuBarChangeLanguage = false; //Show language button
                    $rootScope.dropdownMenuBarLanguage = false; // Show the langauges available
                    return results;
                });
            }else{
                return Resources.register.get({'section': 'menuBar', 'idLanguage': Language}, {'funct': "content"}).$promise
                .then(function (results) {
                    //Languages on dropdown menu bar
                    Resources.register.get({'funct': "languagesAvailable"}).$promise
                    .then(function (languageAbbr) {
                        $rootScope.languages = languageAbbr.languages;
                        $rootScope.dropdownMenuBarChangeLanguage = true; //Show language button
                        $rootScope.dropdownMenuBarLanguage = false; // Show the langauges available
                    });
                    return results;
                });
            }
        }
        if($rootScope.dropdownMenuBar == undefined||$rootScope.dropdownMenuBar.length == 0){
            return content().then(function(results){
                //Dropdown Menu Bar
                $rootScope.dropdownMenuBar = [];
                $rootScope.dropdownMenuBar.push({id:'1', name: results.data.logout, href: 'logout', iconInitial: '/img/srcWeb/DropdownMenuBar/clauIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/clauIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/clauIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'2', name: results.data.privacity, href: '/privacy', iconInitial: '/img/srcWeb/DropdownMenuBar/lockIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/lockIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/lockIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'3', name: results.data.tutorial, href: '/tips', iconInitial: '/img/srcWeb/DropdownMenuBar/tutorialIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/tutorialIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/tutorialIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'4', name: results.data.download, href: '/download', iconInitial: '/img/srcWeb/DropdownMenuBar/downloadIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/downloadIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/downloadIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'5', name: results.data.faq, href: '/faq', iconInitial: '/img/srcWeb/DropdownMenuBar/faqIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/faqIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/faqIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'6', name: results.data.userConfig, href: '/userConfig', iconInitial: '/img/srcWeb/DropdownMenuBar/configIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/configIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/configIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'7', name: results.data.panelGroups, href: '/panelGroups', iconInitial: '/img/srcWeb/DropdownMenuBar/panellsIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/panellsIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/panellsIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'8', name: results.data.editPanel, href: 'editPanel', iconInitial: '/img/srcWeb/DropdownMenuBar/editaPanellIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/editaPanellIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/editaPanellIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'9', name: results.data.info, href: '/about', iconInitial: '/img/srcWeb/DropdownMenuBar/sobrejocomIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/sobrejocomIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/sobrejocomIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'10', name: results.data.home, href: '/home', iconInitial: '/img/srcWeb/DropdownMenuBar/iniciIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/iniciIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/iniciIconSelected.png', show:false});
                $rootScope.dropdownMenuBar.push({id:'11', name: results.data.init, href: '/', iconInitial: '/img/srcWeb/DropdownMenuBar/iniciIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/iniciIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/iniciIconSelected.png', show:false});
                $rootScope.languageButton = {name: results.data.languages, iconInitial: '/img/srcWeb/DropdownMenuBar/idiomaIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/idiomaIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/idiomaIconSelected.png'};
                $rootScope.contactButton = {name: results.data.contact, iconInitial: '/img/srcWeb/DropdownMenuBar/mailIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/mailIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/mailIconSelected.png'};
                $rootScope.exitButton = {name: results.data.exit, iconInitial: '/img/srcWeb/DropdownMenuBar/powerIcon.png', iconHover: '/img/srcWeb/DropdownMenuBar/powerIconHover.png', iconSelected: '/img/srcWeb/DropdownMenuBar/powerIconSelected.png'};
                $rootScope.languageButtonIcon = $rootScope.languageButton.iconInitial;
                $rootScope.contactButtonIcon = $rootScope.contactButton.iconInitial;
                $rootScope.exitButtonIcon = $rootScope.exitButton.iconInitial;
                $rootScope.exitTextContent = results.data.shortcutExitFullScreen;
            });
        }else{
            return content().then(function(results){
                    $rootScope.dropdownMenuBar[0].name = results.data.logout;
                    $rootScope.dropdownMenuBar[1].name = results.data.privacity;
                    $rootScope.dropdownMenuBar[2].name = results.data.tutorial;
                    $rootScope.dropdownMenuBar[3].name = results.data.download;
                    $rootScope.dropdownMenuBar[4].name = results.data.faq;
                    $rootScope.dropdownMenuBar[5].name = results.data.userConfig;
                    $rootScope.dropdownMenuBar[6].name = results.data.panelGroups;
                    $rootScope.dropdownMenuBar[7].name = results.data.editPanel;
                    $rootScope.dropdownMenuBar[8].name = results.data.info;
                    $rootScope.dropdownMenuBar[9].name = results.data.home;
                    $rootScope.dropdownMenuBar[10].name = results.data.init;
                    $rootScope.languageButton.name = results.data.languages;
                    $rootScope.contactButton.name = results.data.contact;
                    $rootScope.exitButton.name = results.data.exit;
                    $rootScope.exitTextContent = results.data.shortcutExitFullScreen;
            });
        }
    };
})



/*
 * http://kevin.vanzonneveld.net
 *     original by: Webtoolkit.info (http://www.webtoolkit.info/)
 *   namespaced by: Michael White (http://getsprink.com)
 *      tweaked by: Jack
 *     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 *        input by: Brett Zamir (http://brett-zamir.me)
 *     bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 *      depends on: utf8_encode
 *       example 1: md5('Kevin van Zonneveld');
 *       returns 1: '6e658d4bfcb59cc13f96c14450ac40b9'
 *  Adapted to AngularJS Service by: Jim Lavin (http://jimlavin.net)
 *  after injecting into your controller, directive or service
 *       example 1: md5.createHash('Kevin van Zonneveld');
 *       returns 1: '6e658d4bfcb59cc13f96c14450ac40b9'
 */

.factory('md5', function() {

    var md5 = {

        createHash: function(str) {

            var xl;

            var rotateLeft = function(lValue, iShiftBits) {
                return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
            };

            var addUnsigned = function(lX, lY) {
                var lX4, lY4, lX8, lY8, lResult;
                lX8 = (lX & 0x80000000);
                lY8 = (lY & 0x80000000);
                lX4 = (lX & 0x40000000);
                lY4 = (lY & 0x40000000);
                lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
                if (lX4 & lY4) {
                    return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
                }
                if (lX4 | lY4) {
                    if (lResult & 0x40000000) {
                        return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
                    } else {
                        return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
                    }
                } else {
                    return (lResult ^ lX8 ^ lY8);
                }
            };

            var _F = function(x, y, z) {
                return (x & y) | ((~x) & z);
            };
            var _G = function(x, y, z) {
                return (x & z) | (y & (~z));
            };
            var _H = function(x, y, z) {
                return (x ^ y ^ z);
            };
            var _I = function(x, y, z) {
                return (y ^ (x | (~z)));
            };

            var _FF = function(a, b, c, d, x, s, ac) {
                a = addUnsigned(a, addUnsigned(addUnsigned(_F(b, c, d), x), ac));
                return addUnsigned(rotateLeft(a, s), b);
            };

            var _GG = function(a, b, c, d, x, s, ac) {
                a = addUnsigned(a, addUnsigned(addUnsigned(_G(b, c, d), x), ac));
                return addUnsigned(rotateLeft(a, s), b);
            };

            var _HH = function(a, b, c, d, x, s, ac) {
                a = addUnsigned(a, addUnsigned(addUnsigned(_H(b, c, d), x), ac));
                return addUnsigned(rotateLeft(a, s), b);
            };

            var _II = function(a, b, c, d, x, s, ac) {
                a = addUnsigned(a, addUnsigned(addUnsigned(_I(b, c, d), x), ac));
                return addUnsigned(rotateLeft(a, s), b);
            };

            var convertToWordArray = function(str) {
                var lWordCount;
                var lMessageLength = str.length;
                var lNumberOfWords_temp1 = lMessageLength + 8;
                var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
                var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
                var lWordArray = new Array(lNumberOfWords - 1);
                var lBytePosition = 0;
                var lByteCount = 0;
                while (lByteCount < lMessageLength) {
                    lWordCount = (lByteCount - (lByteCount % 4)) / 4;
                    lBytePosition = (lByteCount % 4) * 8;
                    lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount) << lBytePosition));
                    lByteCount += 1;
                }
                lWordCount = (lByteCount - (lByteCount % 4)) / 4;
                lBytePosition = (lByteCount % 4) * 8;
                lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
                lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
                lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
                return lWordArray;
            };

            var wordToHex = function(lValue) {
                var wordToHexValue = '',
                    wordToHexValue_temp = '',
                    lByte, lCount;
                for (lCount = 0; lCount <= 3; lCount += 1) {
                    lByte = (lValue >>> (lCount * 8)) & 255;
                    wordToHexValue_temp = '0' + lByte.toString(16);
                    wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length - 2, 2);
                }
                return wordToHexValue;
            };

            var x = [],
                k, AA, BB, CC, DD, a, b, c, d, S11 = 7,
                S12 = 12,
                S13 = 17,
                S14 = 22,
                S21 = 5,
                S22 = 9,
                S23 = 14,
                S24 = 20,
                S31 = 4,
                S32 = 11,
                S33 = 16,
                S34 = 23,
                S41 = 6,
                S42 = 10,
                S43 = 15,
                S44 = 21;

            //str = this.utf8_encode(str);
            x = convertToWordArray(str);
            a = 0x67452301;
            b = 0xEFCDAB89;
            c = 0x98BADCFE;
            d = 0x10325476;

            xl = x.length;
            for (k = 0; k < xl; k += 16) {
                AA = a;
                BB = b;
                CC = c;
                DD = d;
                a = _FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
                d = _FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
                c = _FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
                b = _FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
                a = _FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
                d = _FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
                c = _FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
                b = _FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
                a = _FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
                d = _FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
                c = _FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
                b = _FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
                a = _FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
                d = _FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
                c = _FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
                b = _FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
                a = _GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
                d = _GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
                c = _GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
                b = _GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
                a = _GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
                d = _GG(d, a, b, c, x[k + 10], S22, 0x2441453);
                c = _GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
                b = _GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
                a = _GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
                d = _GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
                c = _GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
                b = _GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
                a = _GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
                d = _GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
                c = _GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
                b = _GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
                a = _HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
                d = _HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
                c = _HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
                b = _HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
                a = _HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
                d = _HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
                c = _HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
                b = _HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
                a = _HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
                d = _HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
                c = _HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
                b = _HH(b, c, d, a, x[k + 6], S34, 0x4881D05);
                a = _HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
                d = _HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
                c = _HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
                b = _HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
                a = _II(a, b, c, d, x[k + 0], S41, 0xF4292244);
                d = _II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
                c = _II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
                b = _II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
                a = _II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
                d = _II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
                c = _II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
                b = _II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
                a = _II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
                d = _II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
                c = _II(c, d, a, b, x[k + 6], S43, 0xA3014314);
                b = _II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
                a = _II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
                d = _II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
                c = _II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
                b = _II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
                a = addUnsigned(a, AA);
                b = addUnsigned(b, BB);
                c = addUnsigned(c, CC);
                d = addUnsigned(d, DD);
            }

            var temp = wordToHex(a) + wordToHex(b) + wordToHex(c) + wordToHex(d);

            return temp.toLowerCase();
        }

    };

    return md5;

});
