angular.module('controllers', [])

// Controlador del Login

        .controller('LoginCtrl', function ($scope, Resources, $location, AuthService, $rootScope, dropdownMenuBarInit, $timeout) {
            //Definición de variables
            $scope.view2 = false;// vista de recuperación de contraseña
            $timeout(function () {
                $scope.viewActived = true; // para activar la vista
            }, 1000);
            var loginResource = Resources.login;

            //Imagenes
            $scope.img = [];
            $scope.img.fons = '/img/srcWeb/patterns/fons.png';
            $scope.img.Patterns1_08 = '/img/srcWeb/patterns/pattern3.png';
            $scope.img.loading = '/img/srcWeb/Login/loading.gif';
            $scope.img.clau = '/img/srcWeb/Login/clau.png';
            $scope.img.fletxaLogin2 = '/img/srcWeb/Login/fletxaLogin2.png';
            $scope.img.BotoEntra = '/img/srcWeb/Login/login.png';
            $scope.img.BotoEntra2 = '/img/srcWeb/Login/login-hov.png';
            
            //HTML text content
            Resources.register.get({'section': 'login', 'idLanguage': $rootScope.contentLanguageUserNonLoged}, {'funct': "content"}).$promise
                    .then(function (results) {
                        $scope.content = results.data;
                        $scope.viewActivated = true; // para activar la vista
                    });

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
            $rootScope.dropdownMenuBarValue = '/login'; //Button selected on this view
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
                Resources.register.get({'section': 'login', 'idLanguage': value}, {'funct': "content"}).$promise
                        .then(function (results) {
                            $scope.content = results.data;
                            dropdownMenuBarInit(value);
                        });
            };

            // Función que coje el user y pass y comprueba que sean correctos
            $scope.login = function () {
                var body = {
                    user: $scope.username,
                    pass: $scope.password
                };
                // Petición del login
                loginResource.save(body).$promise  // POST (en angular 'save') del user y pass
                        .then(function (result) {				// respuesta ok!
                            var token = result.data.token;
                            var userConfig = result.data.userConfig;
                            if (userConfig.UserValidated == '1' || userConfig.UserValidated == '2') {
                                AuthService.login(token, userConfig);
                                $location.path('/');
                                $rootScope.dropdownMenuBarValue = '/'; //Button selected on this view
                            } else {
                                $scope.state = 'has-warning';
                            }
                        })
                        .catch(function (error) {	// no respuesta
                            $scope.state = 'has-error';
                            console.log(error);
                        });
            };
            // Cambiar estados del formulario
            $scope.changeFormState = function () {
                $scope.state = '';
                $scope.state2 = '';
            };

            // Renovar la contrasseña
            $scope.forgotPass = function () {
                Resources.register.save({'user': $scope.user}, {'funct': "passRecoveryMail"}).$promise
                        .then(function (results) {
                            console.log(results.message);
                            console.log(results);
                            if (results.exist) {
                                if (results.local) {
                                    $location.path(results.url);
                                } else {                  // añadir else if de results.sended cuando funcione el servidor smtp y envie el mail!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                                    $location.path('/emailSended');
                                    $rootScope.dropdownMenuBarValue = ''; //Button selected on this view
                                }
                            } else {
                                $scope.state2 = 'has-error';
                            }
                        });
            };
            $scope.viewActivated = false; // para activar el gif de loading...
        })

//Controlador del registro de usuario
        .controller('RegisterCtrl', function ($scope, $rootScope, $captcha, Resources, md5, $q, $location, dropdownMenuBarInit) {

            //Inicializamos el formulario y las variables necesarias
            $scope.formData = {};  //Datos del formulario
            $scope.languageList = []; //lista de idiomas seleccionados por el usuario
            $scope.state = {user: "", password: ""};// estado de cada campo del formulario
            var userOk = false; // variables de validación
            var emailOk = false; // variables de validación
            var languageOk = false; // variables de validación

            //Imagenes
            $scope.img = [];
            $scope.img.fons = '/img/srcWeb/patterns/fons.png';
            $scope.img.Patterns1_08 = '/img/srcWeb/patterns/pattern3.png';
            $scope.img.loading = '/img/srcWeb/Login/loading.gif';
            $scope.img.yo_3 = '/img/icons/yo_3.png';
            $scope.img.yo_1 = '/img/icons/yo_1.png';
            $scope.img.fletxaLogin2 = '/img/srcWeb/Login/fletxaLogin2.png';
            $scope.img.BotoCrea = '/img/srcWeb/Login/new.png';
            $scope.img.BotoCrea2 = '/img/srcWeb/Login/new-hov.png';

            //HTML text content
            Resources.register.get({'section': 'userRegister', 'idLanguage': $rootScope.contentLanguageUserNonLoged}, {'funct': "content"}).$promise
                    .then(function (results) {
                        $scope.content = results.data;
                        $scope.viewActived = true; // para activar la vista
                    });

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
            $rootScope.dropdownMenuBarValue = '/register'; //Button selected on this view
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
                Resources.register.get({'section': 'userRegister', 'idLanguage': value}, {'funct': "content"}).$promise
                        .then(function (results) {
                            $scope.content = results.data;
                            dropdownMenuBarInit(value);
                        });
            };
            //Idiomas disponibles para el desplegable de idiomas a seleccionar.
            Resources.register.get({'funct': "languagesAvailable"}).$promise
                    .then(function (results) {
                        $scope.availableLanguageOptions = results.languages;
                    });
            //Borrar el formulario
            $scope.resetForm = function () {
                $scope.formData = {};
                $scope.registerForm.$setPristine();//poner el formulario en estado inicial
            };

            //Validación del usuario
            $scope.checkUser = function (formData) {
                if (formData.SUname == null) {
                    $scope.state.user = 'has-warning';
                    userOk = false;  // Usamos una variable en vez del return por que la función promise tarda mas en retornar el resultado y nos dava error al comprobarlo en el submit
                    return;
                }
                if (formData.SUname.length < 4 || formData.SUname.length >= 50) { // minimo y maximo de caracteres requeridos
                    $scope.state.user = 'has-warning';
                    userOk = false;
                } else {
                    Resources.register.get({//enviamos los datos de la tabla de la base de datos donde queremos comprobar el nombre
                        'table': "SuperUser",
                        'column': "SUname",
                        'data': formData.SUname}, {'funct': "checkData"}).$promise
                            .then(function (results) {
                                if (results.exist == "false") {
                                    $scope.state.user = 'has-success'; //Si no exixte el nombre ponemos el checkbox en success
                                    userOk = true;
                                } else if (results.exist == "true") {
                                    $scope.state.user = 'has-error'; //Si exixte el nombre ponemos el checkbox en error
                                    userOk = false;
                                }
                            })
                            .catch(function (error) { // no respuesta
                                console.log('get_error:', error);
                                userOk = false;
                            });
                }
            };

            //Validar la igualdad de los dos passwords
            $scope.checkPassword = function (formData) {
                if (formData.pswd == null || formData.pswd.length >= 32) { // minimo y maximo de caracteres requeridos
                    $scope.state.password = 'has-warning';
                    $scope.state.confirmPassword = 'has-warning';
                    return false;
                }
                if (formData.pswd.length < 4) {
                    $scope.state.password = 'has-warning';
                    return false;
                } else {
                    $scope.state.password = 'has-success';
                    var passOk = true;
                }
                if (formData.pswd != formData.confirmPassword && passOk && $scope.registerForm.confirmPassword.$dirty) {
                    $scope.state.confirmPassword = 'has-warning';
                    return false;
                } else
                if (formData.pswd == formData.confirmPassword) {
                    $scope.state.confirmPassword = 'has-success';
                    return true;
                }
            };

            //Comprobar que ha entrado texto en el campo nombre
            $scope.checkName = function (formData) {
                if (formData.realname == null || formData.realname == '' || formData.realname.length >= 200) { // minimo y maximo de caracteres requeridos
                    $scope.state.name = 'has-error';
                    return false;
                } else {
                    $scope.state.name = 'has-success';
                    return true;
                }
            };

            //Comprobar que ha entrado texto en el campo apellidos
            $scope.checkLastname = function (formData) {
                if (formData.surnames == null || formData.surnames == '' || formData.surnames.length >= 300) { // minimo y maximo de caracteres requeridos
                    $scope.state.lastname = 'has-error';
                    return false;
                } else {
                    $scope.state.lastname = 'has-success';
                    return true;
                }
            };

            //Validación del email
            var emailFormat = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
            $scope.checkEmail = function (formData) {
                if (formData.email == null || formData.email == '' || formData.email.length >= 300) { // comprovacion de formato y minimo y maximo de caracteres requeridos
                    $scope.state.email = 'has-warning';
                    emailOk = false;
                    return;
                }
                if (String(formData.email).search(emailFormat) == -1) {
                    $scope.state.email = 'has-warning';
                    emailOk = false;
                } else {
                    Resources.register.get({//enviamos los datos de la tabla de la base de datos donde queremos comprobar el nombre
                        'table': "SuperUser",
                        'column': "email",
                        'data': formData.email}, {'funct': "checkData"}).$promise
                            .then(function (results) {
                                if (results.exist == "false") {
                                    $scope.state.email = 'has-success'; //Si no exixte el nombre ponemos el checkbox en success
                                    emailOk = true;
                                } else if (results.exist == "true") {
                                    $scope.state.email = 'has-error'; //Si exixte el nombre ponemos el checkbox en error
                                    emailOk = false;
                                }
                            });
                }
            };

            //Añadir idiomas
            $scope.addLanguage = function (idLanguage) {
                $scope.singleLanguageSelected=true;
                angular.forEach($scope.availableLanguageOptions, function (value, key) {
                    if (value.ID_Language == idLanguage) {
                        $scope.languageList.push($scope.availableLanguageOptions[key]);//añadimos el idioma a la lista .push(objeto)
                        $scope.availableLanguageOptions.splice(key, 1);//Borrar idioma de las opciones .splice(posicion, numero de items)
                        $scope.state.languageSelected = 'has-success';
                        languageOk = true;
                    }
                });
            };

            //Quitar idiomas
            $scope.removeLanguage = function (index) {
                $scope.singleLanguageSelected=false;
                $scope.availableLanguageOptions.push($scope.languageList[index]);
                $scope.languageList.splice(index, 1);//Borrar item de un array .splice(posicion, numero de items)
            };

            //Genero de la aplicación (Masculino/femenino)
            $scope.gender = function (gender) {
                if (gender == 'female') {
                    $scope.state.female = 'has-success';
                    $scope.state.male = '';
                    $scope.formData.cfgIsFem = '1';
                    return true;
                } else if (gender == 'male') {
                    $scope.state.female = '';
                    $scope.state.male = 'has-success';
                    $scope.formData.cfgIsFem = '0';
                    return true;
                }
                if (gender.cfgIsFem == null || gender.cfgIsFem == '') {
                    $scope.state.female = 'has-error';
                    $scope.state.male = 'has-error';
                    return false;
                } else {
                    return true;
                }
            }

            $scope.checkCaptcha = function () {
                //si pasa la validacion correctamente
                if ($captcha.checkResult($scope.resultado) === true)
                {
                    $scope.captchaState = 'has-success';
                    return true;
                }
                //si falla la validacion
                else
                {
                    return false;
                }
            }

            $scope.submitForm = function (formData) {
                // Llamamos las funciones para printar el error en el formulario si nunca se han llamado

                if (!$scope.checkCaptcha()) {
                    $scope.captchaState = 'has-error';
                }
                $scope.checkUser(formData);
                $scope.checkEmail(formData);
                $scope.checkPassword(formData);
                $scope.checkName(formData);
                $scope.checkLastname(formData);
                $scope.gender(formData);
                // Comprobamos si el usuario ha introducido algun idioma
                if ($scope.languageList.length == 0) {
                    $scope.state.languageSelected = 'has-error';
                    languageOk = false;
                }
                // Comprobamos todos los campos del formulario accediendo a las funciones o mirando las variables de estado
                if ($scope.checkCaptcha() && userOk && $scope.checkPassword(formData) && $scope.checkName(formData) && $scope.checkLastname(formData) && emailOk && languageOk && $scope.gender(formData)) {
                    $location.path('/registerComplete');
                    $rootScope.dropdownMenuBarValue = ''; //Dropdown bar button selected on this view
                    $rootScope.viewActived2 = false; // para activar el gif de loading...
                    $rootScope.localServer = true;


                    //Borramos los campos inecesarios
                    delete formData.confirmPassword;
                    delete formData.languageSelected;
                    //Ponemos como idioma por defecto el primero de la lista que ha seleccionado el usuario
                    var defLanguage = $scope.languageList[0].ID_Language;
                    //Ciframos el password en md5
                    $pass = formData.pswd;
                    formData.pswd = md5.createHash($pass);
                    //Pasamos los datos a formato JSON string
                    var data = {'data': JSON.stringify(formData), 'table': 'SuperUser'};
                    //enviamos los datos del formulario.
                    Resources.register.save(data, {'funct': "saveData"}).$promise
                            .then(function (results) {
                                var promises = []; //PROMESAS
                                angular.forEach($scope.languageList, function (value) {
                                    var deferred = $q.defer();//PROMESAS
                                    //enviamos los usuarios con cada idioma.
                                    Resources.register.save({'SUname': formData.SUname, 'ID_ULanguage': value.ID_Language, 'defLanguage': defLanguage}, {'funct': "saveUserData"}).$promise
                                            .then(function (response) {
                                                deferred.resolve(response);//PROMESAS
                                                $id_su = response.ID_SU;
                                                $id_usu = response.ID_U;
                                                
                                                //Cargamos la board inicial (Oscar)
                                                Resources.register.save({'idsu': $id_su, 'idusu' :$id_usu}, {'funct': "copyDefaultGroupBoard"}).$promise
                                                .then(function (results) {
                                                    console.log(results);
                                                });
                                                
                                            });
                                    promises.push(deferred.promise);//PROMESAS
                                });

                                //Funcion que se llama al finalizar todas las promesas
                                $q.all(promises).then(function () {
                                    //Vista confirmación
                                    Resources.register.save({'user': $id_su}, {'funct': "generateValidationMail"}).$promise
                                            .then(function (results) {
                                                if (results.local) { // Cambiar por results.sended cuando funcione el servidor smtp y envie el mail!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                                                    $rootScope.localServer = true;
                                                    $rootScope.viewActived2 = true; // para activar la vista
                                                } else {
                                                    $rootScope.localServer = false;
                                                    $rootScope.viewActived2 = true; // para activar la vista
                                                }
                                            });
                                });
                            });
                }
            };
            $scope.viewActived = false; // para activar el gif de loading...
        })

//User email validation
        .controller('emailValidationCtrl', function ($scope, $routeParams, Resources, $rootScope, $location, dropdownMenuBarInit) {
            //Variables
            $scope.activedValidation = false;// para activar el gif de loading
            //Imagenes
            $scope.img = [];
            $scope.img.fons = '/img/srcWeb/patterns/fons.png';
            $scope.img.Patterns1_08 = '/img/srcWeb/patterns/pattern3.png';
            $scope.img.loading = '/img/srcWeb/Login/loading.gif';

            //HTML text content
            Resources.register.get({'section': 'emailValidation', 'idLanguage': $rootScope.contentLanguageUserNonLoged}, {'funct': "content"}).$promise
                    .then(function (results) {
                        $scope.content = results.data;
                        $scope.viewActived = true; // para activar la vista
                    });
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
            $rootScope.dropdownMenuBarValue = ''; //Button selected on this view
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
                Resources.register.get({'section': 'emailValidation', 'idLanguage': value}, {'funct': "content"}).$promise
                        .then(function (results) {
                            $scope.content = results.data;
                            dropdownMenuBarInit(value);
                        });
            };

            //Enviamos la clave y el id para comprobar el email del usuario
            Resources.register.save({'emailKey': $routeParams.emailKey, 'ID_SU': $routeParams.id}, {'funct': "emailValidation"}).$promise
                    .then(function (results) {
                        $scope.validated = results.validated;
                        $scope.activedValidation = true;// para activar la vista;
                    });
            $scope.viewActived = false; // para activar el gif de loading...
        })

//Password recovery controller
        .controller('passRecoveryCtrl', function ($scope, $rootScope, $routeParams, Resources, md5, $location, dropdownMenuBarInit) {

            //initialize variables
            $scope.formData = {};  //Datos del formulario
            $scope.state = {password: "", confirmPassword: ""};// estado de cada campo del formulario

            //Imagenes
            $scope.img = [];
            $scope.img.fons = '/img/srcWeb/patterns/fons.png';
            $scope.img.Patterns1_08 = '/img/srcWeb/patterns/pattern3.png';
            $scope.img.loading = '/img/srcWeb/Login/loading.gif';

            //HTML text content
            Resources.register.get({'section': 'passRecovery', 'idLanguage': $rootScope.contentLanguageUserNonLoged}, {'funct': "content"}).$promise
                    .then(function (results) {
                        $scope.content = results.data;
                        $scope.viewActived = true; // para activar la vista
                    });
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
            $rootScope.dropdownMenuBarValue = ''; //Button selected on this view
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
                Resources.register.get({'section': 'passRecovery', 'idLanguage': value}, {'funct': "content"}).$promise
                        .then(function (results) {
                            $scope.content = results.data;
                            dropdownMenuBarInit(value);
                        });
            };

            //Check if url user exists
            Resources.register.save({'emailKey': $routeParams.emailKey, 'ID_SU': $routeParams.id}, {'funct': "emailValidation"}).$promise
                    .then(function (results) {
                        if (results.userExist) {
                            $scope.linkExpiredView = false;
                            $scope.enterPassView = true;
                            $scope.passChangedView = false;
                        } else {
                            $scope.linkExpiredView = true;
                            $scope.enterPassView = false;
                            $scope.passChangedView = false;
                        }
                    });

            //Check if passwords are equal.
            $scope.checkPassword = function (formData) {
                if (formData.pswd == null || formData.pswd.length >= 32) { // minimo y maximo de caracteres requeridos
                    $scope.state.password = 'has-warning';
                    $scope.state.confirmPassword = 'has-warning';
                    return false;
                }
                if (formData.pswd.length < 4) {
                    $scope.state.password = 'has-warning';
                    return false;
                } else {
                    $scope.state.password = 'has-success';
                    var passOk = true;
                }
                if (formData.pswd != formData.confirmPassword && passOk && $scope.PassForm.confirmPassword.$dirty) {
                    $scope.state.confirmPassword = 'has-warning';
                    return false;
                } else
                if (formData.pswd == formData.confirmPassword) {
                    $scope.state.confirmPassword = 'has-success';
                    return true;
                }
            };
            //Send new password
            $scope.sendPass = function (formData) {

                if ($scope.checkPassword(formData)) {
                    //HTML views
                    $scope.linkExpiredView = false;
                    $scope.enterPassView = false;
                    $scope.passChangedView = false;
                    //md5 password encode and Json formating
                    $password = md5.createHash(formData.pswd);
                    $pass = '{"pswd":"' + $password + '"}';
                    //Send new password
                    Resources.register.save({'emailKey': $routeParams.emailKey, 'ID_SU': $routeParams.id, 'pass': $pass}, {'funct': "changePass"}).$promise
                            .then(function (results) {
                                if (results.passChanged) {
                                    $scope.linkExpiredView = false;
                                    $scope.enterPassView = false;
                                    $scope.passChangedView = true;
                                }
                            });
                }
            }
            //HTML views
            $scope.viewActived = false; // para activar el gif del loading
            $scope.linkExpiredView = false;
            $scope.enterPassView = false;
            $scope.passChangedView = false;
        })

//Controlador de la configuración de usuario
        .controller('UserConfCtrl', function ($http, $scope, $rootScope, Resources, AuthService, txtContent, $location, $timeout, dropdownMenuBarInit) {
            // Comprobación del login   IMPORTANTE!!! PONER EN TODOS LOS CONTROLADORES
            if (!$rootScope.isLogged) {
                $rootScope.dropdownMenuBarValue = '/home'; //Dropdown bar button selected on this view
                $location.path('/home');
            }
            //Dropdown Menu Bar
            $rootScope.dropdownMenuBar = null;
            $rootScope.dropdownMenuBarButtonHide = false;
            $rootScope.dropdownMenuBarValue = '/userConfig'; //Button selected on this view
            $rootScope.dropdownMenuBarChangeLanguage = false;//Languages button available

            //Choose the buttons to show on bar
            dropdownMenuBarInit($rootScope.interfaceLanguageId)
                    .then(function () {
                        //Choose the buttons to show on bar
                        angular.forEach($rootScope.dropdownMenuBar, function (value) {
                            if (value.href == '/' || value.href == '/panelGroups' || value.href == '/userConfig' || value.href == '/faq' || value.href == '/tips' || value.href == '/privacy' || value.href == 'logout') {
                                value.show = true;
                            } else {
                                value.show = false;
                            }
                        });
                    });
            //function to change html view
            $scope.go = function (path) {
                if (path == 'logout') {
                    $('#logoutModal').modal('toggle');
                } else {
                    $rootScope.dropdownMenuBarValue = path; //Button selected on this view
                    $location.path(path);
                }
            };
            //Log Out Modal
            Resources.main.get({'section': 'logoutModal', 'idLanguage': $rootScope.interfaceLanguageId}, {'funct': "content"}).$promise
                    .then(function (results) {
                        $scope.logoutContent = results.data;
                    });
            $scope.logout = function () {
                $scope.viewActived = false;
                $timeout(function () {
                    AuthService.logout();
                }, 1000);
            };

            // Declaración de variables
            $scope.canEdit = false;
            $scope.loadingEdit = false;
            $scope.loadingOldPass = false;
            $scope.local = false;
            $scope.interfaceLanguageBarEnable = true;
            $scope.expansionLanguageEnable = true;
            $scope.userDataForm = [];
            var count1 = 0;
            var count2 = 0;
            // Initialize variables for the information popups
            $scope.infoModalContent = "";
            $scope.infoModalTitle = "";
            //Imagenes
            $scope.img = [];
            $scope.img.fons = '/img/srcWeb/patterns/fons.png';
            $scope.img.loading = '/img/srcWeb/Login/loading.gif';
            $scope.img.Patterns1_12 = '/img/srcWeb/Patterns1-12.png';
            $scope.img.Patterns1_08 = '/img/srcWeb/patterns/pattern3.png';
            $scope.img.whiteLoading = '/img/icons/whiteLoading.gif';
            $scope.img.Loading_icon = '/img/icons/Loading_icon.gif';
            $scope.img.Icon_Alert = '/img/icons/info alert.png';
            $scope.img.infoRed = '/img/icons/infoRed.png';
            $scope.img.info = '/img/icons/info.png';
            $scope.img.orangeArrow = '/img/srcWeb/UserConfig/orangeArrow.png';
            $scope.img.audioPlay = '/img/srcWeb/UserConfig/audioPlay.png';
            $scope.img.audioPlay2 = '/img/srcWeb/UserConfig/audioPlay2.png';
            $scope.img.menuUp = '/img/srcWeb/UserConfig/menuUp.png';
            $scope.img.menuDown = '/img/srcWeb/UserConfig/menuDown.png';
            $scope.img.MenuHomeActive = '/img/srcWeb/UserConfig/menuHomeActive.jpg';
            $scope.img.menuButton1 = '/img/srcWeb/UserConfig/menuButton1.jpg';
            $scope.img.menuButton2 = '/img/srcWeb/UserConfig/menuButton2.jpg';
            $scope.img.menuButton3 = '/img/srcWeb/UserConfig/menuButton3.jpg';
            $scope.img.textInCellOff = '/img/srcWeb/UserConfig/textInCellOff.png';
            $scope.img.textInCellOn = '/img/srcWeb/UserConfig/textInCellOn.png';
            $scope.img.cfgUsageMouse = '/img/srcWeb/UserConfig/cfgUsageMouse.png';
            $scope.img.cfgUsageOneC = '/img/srcWeb/UserConfig/cfgUsageOneC.png';
            $scope.img.cfgUsageTwoC = '/img/srcWeb/UserConfig/cfgUsageTwoC.png';
            $scope.img.cfgScanningRow = '/img/srcWeb/UserConfig/cfgScanningRow.png';
            $scope.img.cfgScanningCol = '/img/srcWeb/UserConfig/cfgScanningCol.png';
            $scope.img.cfgScanningCustom = '/img/srcWeb/UserConfig/cfgScanningCustom.png';
            $scope.img.lowSorpresaFlecha = '/img/srcWeb/Mus/lowSorpresaFlecha.png';
            $scope.img.lowDormFlecha = '/img/srcWeb/Mus/lowDormFlecha.png';
            $scope.img.lowHolaFlecha = '/img/srcWeb/Mus/lowHolaFlecha.png';
            $scope.img.scanOrder123 = '/img/srcWeb/UserConfig/scanOrder123.gif';
            $scope.img.scanOrder132 = '/img/srcWeb/UserConfig/scanOrder132.gif';
            $scope.img.scanOrder213 = '/img/srcWeb/UserConfig/scanOrder213.gif';
            $scope.img.scanOrder231 = '/img/srcWeb/UserConfig/scanOrder231.gif';
            $scope.img.scanOrder312 = '/img/srcWeb/UserConfig/scanOrder312.gif';
            $scope.img.scanOrder321 = '/img/srcWeb/UserConfig/scanOrder321.gif';
            $scope.img.scanOrderPred = '/img/srcWeb/UserConfig/scanOrderPred.png';
            $scope.img.scanOrderMenu = '/img/srcWeb/UserConfig/scanOrderMenu.png';
            $scope.img.scanOrderPanel = '/img/srcWeb/UserConfig/scanOrderPanel.png';
            //Pedimos la configuración del usuario a la base de datos
            $scope.getConfig = function () {
                $scope.interfaceLanguages = [];
                $scope.expansionLanguages = [];
                $scope.numPictosBarPred = ['2', '3', '4', '5', '6', '7', '8', '9', '10'];
                return Resources.main.get({'IdSu': $rootScope.sUserId}, {'funct': "getConfig"}).$promise
                        .then(function (results) {
                            window.localStorage.removeItem('userData');
                            window.localStorage.setItem('userData', JSON.stringify(results.userConfig));
                            $rootScope.interfaceLanguageId = results.userConfig.ID_ULanguage;
                            $rootScope.expanLanguageId = results.userConfig.cfgExpansionLanguage;
                            $scope.userData = results.userConfig;
                            $scope.interfaceLanguage = results.languages[results.userConfig.ID_ULanguage - 1].languageName;
                            $scope.expansionLanguage = results.languages[results.userConfig.cfgExpansionLanguage - 1].languageName;
                            $scope.languages = results.languages;

                            //Necessary for preselected ng-options (select) works
                            $scope.userData.cfgPredBarNumPred = $scope.numPictosBarPred[results.userConfig.cfgPredBarNumPred - 2];

                            //Parse string to integer
                            $scope.userData.cfgTimeLapseSelect = parseInt(results.userConfig.cfgTimeLapseSelect, 10);
                            $scope.userData.cfgTimeNoRepeatedClick = parseInt(results.userConfig.cfgTimeNoRepeatedClick, 10);
                            $scope.userData.cfgTimeClick = parseInt(results.userConfig.cfgTimeClick, 10);
                            $scope.userData.cfgTimeScanning = parseInt(results.userConfig.cfgTimeScanning, 10);
                            $scope.userData.cfgVoiceOfflineRate = parseInt(results.userConfig.cfgVoiceOfflineRate, 10);

                            //change string (0,1) to boolean (true,false)
                            $scope.userData.cfgExpansionOnOff = ($scope.userData.cfgExpansionOnOff === "1");
                            $scope.userData.cfgPredOnOff = ($scope.userData.cfgPredOnOff === "1");
                            $scope.userData.cfgMenuHomeActive = ($scope.userData.cfgMenuHomeActive === "1");
                            $scope.userData.cfgMenuReadActive = ($scope.userData.cfgMenuReadActive === "1");
                            $scope.userData.cfgMenuDeleteLastActive = ($scope.userData.cfgMenuDeleteLastActive === "1");
                            $scope.userData.cfgMenuDeleteAllActive = ($scope.userData.cfgMenuDeleteAllActive === "1");
                            $scope.userData.cfgAutoEraseSentenceBar = ($scope.userData.cfgAutoEraseSentenceBar === "1");
                            $scope.userData.cfgTimeLapseSelectOnOff = ($scope.userData.cfgTimeLapseSelectOnOff === "1");
                            $scope.userData.cfgScanningOnOff = ($scope.userData.cfgScanningOnOff === "1");
                            $scope.userData.cfgScanningAutoOnOff = ($scope.userData.cfgScanningAutoOnOff === "1");
                            $scope.userData.cfgCancelScanOnOff = ($scope.userData.cfgCancelScanOnOff === "1");
                            $scope.userData.cfgScanStartClick = ($scope.userData.cfgScanStartClick === "1");
                            $scope.userData.cfgHistOnOff = ($scope.userData.cfgHistOnOff === "1");
                            $scope.userData.cfgInterfaceVoiceOnOff = ($scope.userData.cfgInterfaceVoiceOnOff === "1");
                            $scope.userData.cfgUserExpansionFeedback = ($scope.userData.cfgUserExpansionFeedback === "1");
                            $scope.userData.cfgInterfaceVoiceMascFem = ($scope.userData.cfgInterfaceVoiceMascFem === "masc");
                            $scope.scanOrder = $scope.userData.cfgScanOrderPred + $scope.userData.cfgScanOrderMenu + $scope.userData.cfgScanOrderPanel;

                            var count = results.users[0].ID_ULanguage;
                            angular.forEach(results.users, function (value) {
                                if (value.ID_ULanguage == count) {
                                    $scope.interfaceLanguages.push({name: results.languages[value.ID_ULanguage - 1].languageName, user: value.ID_User});
                                    count++;
                                }
                                $scope.expansionLanguages.push({idInterfaceLanguage: count - 1, name: results.languages[value.cfgExpansionLanguage - 1].languageName, user: value.ID_User});

                            });

                            //Mostrar el menu voices cuando no se ha introducido ninguna voz
                            if ($scope.userData.UserValidated == '1') {
                                $scope.contentBar11 = true;
                            }
                            //Delete name and surnames input text box
                            delete $scope.userDataForm.realName;
                            delete $scope.userDataForm.surNames;
                            $scope.loadingEdit = false;
                            // Pedimos los textos para cargar la pagina
                            txtContent("userConfig").then(function (results) {
                                $scope.content = results.data;
                                $scope.$broadcast("rebuild:me");
                                //Enable bar after change Language
                                $scope.interfaceLanguageBarEnable = true;
                                $scope.expansionLanguageEnable = true;
                                //Enable content view
                                $scope.viewActived = true;
                            });
                            
                            // If a voices error had been triggered while using the app
                            // the error is shown and set back to 0 with errorVoicesSeen.
                            if ($scope.userData.errorTemp !== '0' &&
                                    $scope.userData.errorTemp !== null) {
                                
                                var errorcode = parseInt($scope.userData.errorTemp);
                                                                
                                txtContent("errorVoices").then(function (content) {
                                        $scope.errorMessage = content.data['errorVoicesText2'] + content.data[errorcode];
                                        $scope.errorCode = content.data['errorVoicesTitle'] + " " + errorcode;
                                        
                                        Resources.main.get({'funct': "errorVoicesSeen"});
                                        
                                        $scope.toggleInfoModal($scope.errorCode, $scope.errorMessage);
                                    });
                            }
                        });
            };


            //Cambiar datos de usuario
            $scope.saveUserData = function () {
                if (($scope.userDataForm.realName == undefined || $scope.userDataForm.realName == '') && ($scope.userDataForm.surNames == undefined || $scope.userDataForm.surNames == '')) {
                    //Campos vacios
                    return;
                }
                $scope.loadingEdit = true;
                if (($scope.userDataForm.realName == undefined) || ($scope.userDataForm.realName == '')) {
                    $data = '{"surnames":"' + $scope.userDataForm.surNames + '"}';
                } else if (($scope.userDataForm.surNames == undefined) || ($scope.userDataForm.surNames == '')) {
                    $data = '{"realname":"' + $scope.userDataForm.realName + '"}';
                } else {
                    $data = '{"realname":"' + $scope.userDataForm.realName + '","surnames":"' + $scope.userDataForm.surNames + '"}';
                }
                Resources.main.save({'IdSu': $rootScope.sUserId, 'data': $data}, {'funct': "saveSUserNames"}).$promise
                        .then(function () {
                            $scope.getConfig();
                        });
            }
            $scope.deleteForm = function () {
                delete $scope.userDataForm.realName;
                delete $scope.userDataForm.surNames;
                $scope.deleteFormPass();
            }
            $scope.deleteFormPass = function () {
                delete $scope.oldPass;
                delete $scope.newPass1;
                delete $scope.newPass2;
                $scope.oldPassState = '';
                $scope.newPassLengthOk = '';
                $scope.newPassState = '';
            }
            //Cambiar Contraseña
            $scope.checkOldPass = function () {
                count1++;
                $scope.oldPassState = '';
                $scope.loadingOldPass = true;
                $scope.oldPassDirty = true;
                Resources.main.save({'IdSu': $rootScope.sUserId, 'pass': $scope.oldPass}, {'funct': "checkPassword"}).$promise
                        .then(function (results) {
                            count2++;
                            if (count1 == count2) {
                                $scope.oldPassState = results.data;
                                $scope.oldPassDirty = false;
                                $scope.loadingOldPass = false;
                            }
                        });
            }
            //Check new password length
            $scope.minPassLength = function () {
                if ($scope.newPass1.length < 4) {
                    $scope.newPassLengthOk = 'false';
                } else {
                    $scope.newPassLengthOk = 'true';
                }
            }
            //Check if new passwords are equal.
            $scope.comparePass = function () {
                if ($scope.newPass1 === $scope.newPass2) {
                    $scope.newPassState = 'true';
                } else {
                    $scope.newPassState = 'false';
                }
            }
            //Save New password
            $scope.saveNewPass = function () {
                Resources.main.save({'IdSu': $rootScope.sUserId, 'oldPass': $scope.oldPass, 'newPass': $scope.newPass2}, {'funct': "savePassword"}).$promise
                        .then(function () {
                            $scope.deleteFormPass();
                        });
            }
            //Change Language (user)
            $scope.changeLanguage = function (idUser) {
                $scope.contentBar1 = false;
                $scope.contentBar2 = false;
                $scope.interfaceLanguageBarEnable = false;
                $scope.expansionLanguageEnable = false;
                Resources.main.save({'IdSu': $rootScope.sUserId, 'idU': idUser}, {'funct': "changeDefUser"}).$promise
                        .then(function () {
                            delete $scope.interfaceLanguages;
                            $scope.interfaceLanguages = [];
                            delete $scope.expansionLanguages;
                            $scope.expansionLanguages = [];
                            $scope.getConfig()
                                    .then(function () {
                                        //Change content language logout modal
                                        Resources.main.get({'section': 'logoutModal', 'idLanguage': $rootScope.interfaceLanguageId}, {'funct': "content"}).$promise
                                                .then(function (results) {
                                                    $scope.logoutContent = results.data;
                                                    //Change content language dropdown Menu Bar
                                                    dropdownMenuBarInit($rootScope.interfaceLanguageId);
                                                });
                                    });
                        });
            };

            $scope.changeOnOff = function (bool, data) {
                if (bool) {
                    Resources.main.save({'IdSu': $rootScope.sUserId, 'data': data, 'value': '1'}, {'funct': "changeCfgBool"}).$promise
                            .then(function (results) {
                                window.localStorage.removeItem('userData');
                                window.localStorage.setItem('userData', JSON.stringify(results.userConfig));
                            });
                } else {
                    Resources.main.save({'IdSu': $rootScope.sUserId, 'data': data, 'value': '0'}, {'funct': "changeCfgBool"}).$promise
                            .then(function (results) {
                                window.localStorage.removeItem('userData');
                                window.localStorage.setItem('userData', JSON.stringify(results.userConfig));
                            });
                }
            }

            $scope.changeRadioState = function (value, data) {
                Resources.main.save({'IdSu': $rootScope.sUserId, 'data': data, 'value': value}, {'funct': "changeCfgBool"}).$promise
                        .then(function (results) {
                            window.localStorage.removeItem('userData');
                            window.localStorage.setItem('userData', JSON.stringify(results.userConfig));
                        });
            }
            $scope.changeCfgVoices = function (value, data) {
                Resources.main.save({'IdU': $rootScope.userId, 'data': data, 'value': value}, {'funct': "changeCfgVoices"}).$promise
                if ($scope.userData.UserValidated == '1' && $scope.userData.cfgExpansionVoiceOnline != null && (!$scope.local || $scope.userData.cfgExpansionVoiceOffline != null)) {
                    Resources.main.save({'IdSu': $rootScope.sUserId, 'data': 'UserValidated', 'value': '2'}, {'funct': "userValidate2"}).$promise
                }
            }

            $scope.changeMascFem = function (bool, data) {
                if (bool) {
                    Resources.main.save({'IdU': $rootScope.userId, 'data': data, 'value': 'masc'}, {'funct': "changeCfgVoices"}).$promise
                } else {
                    Resources.main.save({'IdU': $rootScope.userId, 'data': data, 'value': 'fem'}, {'funct': "changeCfgVoices"}).$promise
                }
            }

            $scope.changeOnOffUser = function (bool, data) {
                if (bool) {
                    Resources.main.save({'IdU': $rootScope.userId, 'data': data, 'value': '1'}, {'funct': "changeCfgVoices"}).$promise
                } else {
                    Resources.main.save({'IdU': $rootScope.userId, 'data': data, 'value': '0'}, {'funct': "changeCfgVoices"}).$promise
                }
            }

            $scope.checkNumberTime = function (value, data) {
                if (angular.isNumber(value)) {
                    $scope.changeRadioState(value, data);
                }
            };

            $scope.addLanguage = function (idLanguage) {
                if (idLanguage == 'submit' && $scope.addLanguageTo == 'interface') {
                    $scope.languageEnable = false;
                    $scope.contentBar1 = false;
                    $scope.contentBar2 = false;
                    $scope.interfaceLanguageBarEnable = false;
                    $scope.expansionLanguageEnable = false;
                    Resources.main.save({'IdSu': $rootScope.sUserId, 'ID_ULanguage': $scope.addlanguageId, 'cfgExpansionLanguage': $scope.addlanguageId}, {'funct': "addUser"}).$promise
                            .then(function () {
                                delete $scope.interfaceLanguages;
                                $scope.interfaceLanguages = [];
                                delete $scope.expansionLanguages;
                                $scope.expansionLanguages = [];
                                $scope.getConfig();
                            });
                } else if (idLanguage == 'submit' && $scope.addLanguageTo == 'expansion') {
                    $scope.languageEnable = false;
                    $scope.contentBar1 = false;
                    $scope.contentBar2 = false;
                    $scope.interfaceLanguageBarEnable = false;
                    $scope.expansionLanguageEnable = false;
                    Resources.main.save({'IdSu': $rootScope.sUserId, 'ID_ULanguage': $rootScope.interfaceLanguageId, 'cfgExpansionLanguage': $scope.addlanguageId}, {'funct': "addUser"}).$promise
                            .then(function () {
                                delete $scope.interfaceLanguages;
                                $scope.interfaceLanguages = [];
                                delete $scope.expansionLanguages;
                                $scope.expansionLanguages = [];
                                $scope.getConfig();
                            });
                } else {
                    $scope.addlanguageId = idLanguage;
                }
                $scope.languageEnable = true;
            };

            //Audio Configuration (Myaudio library access)
            $scope.getAudioLists = function () {
                return Resources.main.get({'funct': "getVoices"}).$promise
                        .then(function (results) {
                            var error = false;
                            angular.forEach(results.voices, function (voice) {
                                if (voice[1] && !error) {
                                    error = true;
                                    txtContent("errorVoices").then(function (content) {
                                        $scope.errorMessage = content.data[voice[3]];
                                        $scope.errorCode = voice[3];
                                        $('#errorVoicesModal').modal({backdrop: 'static'});
                                    });
                                }
                            });
                            $scope.interfaceVoicesList = results.voices.interfaceVoices[0];
                            $scope.expansionVoicesList = results.voices.expansionVoices[0];
                            $scope.interfaceVoicesList[0].voiceCompleteName = results.voices.interfaceVoices[0][0].voiceName + ' - [online]';
                            $scope.interfaceVoicesList[1].voiceCompleteName = results.voices.interfaceVoices[0][1].voiceName + ' - [online]';
                            var count1 = 0;
                            var count2 = 0;
                            angular.forEach($scope.interfaceVoicesList, function (value) {
                                if (count1 >= 2) {
                                    $scope.interfaceVoicesList[count1].voiceCompleteName = value.voiceName + ' - [offline]';
                                }
                                count1++
                            });
                            angular.forEach($scope.expansionVoicesList, function (value) {
                                if (value.voiceType == 'online') {
                                    var name = value.voiceName;
                                    var language = value.vocalwareLangAbbr;
                                    var country = value.vocalwareDescr;
                                    var type = value.voiceType;
                                    if (country == null) {
                                        $scope.expansionVoicesList[count2].voiceCompleteName = name + ' - ' + language + ' - ' + '[' + type + ']';

                                    } else {
                                        $scope.expansionVoicesList[count2].voiceCompleteName = name + ' - ' + language + ' (' + country + ') - ' + '[' + type + ']';
                                    }
                                } else {
                                    $scope.expansionVoicesList[count2].voiceCompleteName = value.voiceName + ' - [offline]';
                                }
                                if (value.ID_Voice == $scope.userData.cfgExpansionVoiceOnline && !($scope.userData.cfgExpansionVoiceOnline == null)) {
                                    $scope.userData.cfgExpansionVoiceOnline = value.voiceName;
                                }
                                count2++;
                            });
                            if (results.appRunning == 'local') {
                                $scope.local = true;
                                $scope.interfaceVoicesListOffline = results.voices.interfaceVoicesOffline[0];
                                $scope.expansionVoicesOffline = results.voices.expansionVoicesOffline[0];
                                var count3 = 0;
                                var count4 = 0;
                                angular.forEach($scope.interfaceVoicesListOffline, function (value) {
                                    $scope.interfaceVoicesListOffline[count3].voiceCompleteName = value.voiceName + ' - [offline]';
                                    count3++
                                });
                                angular.forEach($scope.expansionVoicesOffline, function (value) {
                                    $scope.expansionVoicesOffline[count4].voiceCompleteName = value.voiceName + ' - [offline]';
                                    count4++
                                });
                            }
                        });
            };
            $scope.getConfig()
                    .then(function () {
                        $scope.getAudioLists()
                                .then(function () {
//                            $scope.viewActived = true;
                                });
                    });

            $scope.expansionVoiceChange = function (data) {
                angular.forEach($scope.expansionVoicesList, function (value) {
                    if (value.voiceName === data) {
                        if (value.voiceType === 'offline') {
                            $scope.changeCfgVoices(value.voiceName, 'ExpansionVoiceOnline');
                            $scope.changeCfgVoices('offline', 'ExpansionVoiceOnlineType');
                        } else if (value.voiceType === 'online') {
                            $scope.changeCfgVoices(value.ID_Voice, 'ExpansionVoiceOnline');
                            $scope.changeCfgVoices('online', 'ExpansionVoiceOnlineType');
                        }
                    }
                });
                if ($scope.userData.UserValidated == '1' && $scope.userData.cfgExpansionVoiceOnline != null && (!$scope.local || $scope.userData.cfgExpansionVoiceOffline != null)) {
                    Resources.main.save({'IdSu': $rootScope.sUserId, 'data': 'UserValidated', 'value': '2'}, {'funct': "userValidate2"}).$promise
                    //Cargamos la board inicial (Oscar)
                    $http.post($scope.baseurl + "PanelGroup/copyDefaultGroupBoard");
                }
            };

            $scope.mascFemVoice = function (data) {
                if (data === $scope.interfaceVoicesList[0].voiceName) {
                    $scope.changeMascFem(false, 'InterfaceVoiceMascFem');
                } else if (data === $scope.interfaceVoicesList[1].voiceName) {
                    $scope.changeMascFem(true, 'InterfaceVoiceMascFem');
                }
            };

            $scope.generateAudio = function (voice, type) {
                angular.forEach($scope.expansionVoicesList, function (value) {
                    if (value.voiceName === voice && value.voiceType === 'online') {
                        voice = value.ID_Voice;
                        type = 'online';
                    }
                });
                if (voice == $scope.interfaceVoicesList[0].voiceName || voice == $scope.interfaceVoicesList[1].voiceName) {
                    type = 'online';
                }
                Resources.main.save({'IdU': $rootScope.userId, 'text': $scope.content.voicePlay, 'voice': voice, 'type': type, 'language': $scope.userData.ID_ULanguage, 'rate': '0'}, {'funct': "generateAudio"}).$promise
                        .then(function (results) {
                            if (results[1]) {
                                txtContent("errorVoices").then(function (content) {
                                    $scope.errorVoicesTitle = content.data.errorVoicesTitle;
                                    $scope.errorVoicesSentence = content.data.error;
                                    $scope.errorVoicesOk = content.data.ok;
                                    $scope.errorVoicesMessage = content.data[results[3]];
                                    $scope.errorVoicesCode = results[3];
                                    $('#errorVoicesModal').modal({backdrop: 'static'});
                                });
                            } else {
                                $scope.sound = "mp3/" + results[0];
                                var audiotoplay = $('#utterance');
                                audiotoplay.src = "mp3/" + results[0];
                            }
                        });
            };
            $scope.changeScanOrder = function () {
                if ($scope.scanOrderCount == 4) {
                    $scope.scanOrder = $scope.pred + $scope.menu + $scope.panel;
                    $scope.changeRadioState($scope.scanOrder.charAt(0), 'ScanOrderPred');
                    $scope.changeRadioState($scope.scanOrder.charAt(1), 'ScanOrderMenu');
                    $scope.changeRadioState($scope.scanOrder.charAt(2), 'ScanOrderPanel');
                    $('#scanOrderModal').modal('toggle');
                    $scope.button1 = true;
                    $scope.button2 = true;
                    $scope.button3 = true;
                    $scope.scanOrderCount = 1;
                    $scope.ScanOrderNames = [];
                }
            };
            $scope.ScanOrderNames = [];
            $scope.selectScanOrder = function (value) {
                if (value == 'Pred') {
                    $scope.ScanOrderNames.push({'id': $scope.scanOrderCount + '.', 'name': $scope.content.scanOrderPred});
                    $scope.pred = $scope.scanOrderCount.toString();
                } else if (value == 'Menu') {
                    $scope.ScanOrderNames.push({'id': $scope.scanOrderCount + '.', 'name': $scope.content.scanOrderMenu});
                    $scope.menu = $scope.scanOrderCount.toString();
                } else if (value == 'Panel') {
                    $scope.ScanOrderNames.push({'id': $scope.scanOrderCount + '.', 'name': $scope.content.scanOrderPanel});
                    $scope.panel = $scope.scanOrderCount.toString();
                }
                $scope.scanOrderCount++;
            };
            $scope.exit = function () {
                $scope.viewActived = false;
                $scope.getConfig()
                        .then(function () {
                            $location.path('/panelGroups');
                            $rootScope.dropdownMenuBarValue = '/panelGroups'; //Dropdown bar button selected on this view
                        });
            };
            
            $scope.style_changes_title = '';
            
             // Activate information modals (popups)
            $scope.toggleInfoModal = function (title, text) {
                $scope.infoModalContent = text;
                $scope.infoModalTitle = title;
                
                $('#infoModal').modal('toggle');
            };
            
            $scope.viewActived = false; // para activar el gif del loading
        })
        .controller('myCtrl', function (Resources, $location, $scope, $http, ngDialog, txtContent, $rootScope, $interval, $timeout, dropdownMenuBarInit, AuthService) {

            $scope.isAndroid = false;

            $(function() { // Wait for page to finish loading.
                if(navigator != undefined && navigator.userAgent != undefined) {
                    user_agent = navigator.userAgent.toLowerCase();
                    if(user_agent.indexOf('android') > -1) { // Is Android.
                        $scope.isAndroid = true;
                    }
                }
            });
            
            $scope.viewActived = false;
            $timeout(function () {
                $scope.viewActived = true;
            }, 1000);
    
            //Dropdown Menu Bar
            $rootScope.dropdownMenuBar = null;
            $rootScope.dropdownMenuBarValue = '/'; //Button selected on this view
            $rootScope.dropdownMenuBarButtonHide = true;
            $rootScope.dropdownMenuBarChangeLanguage = false;//Languages button available
            //SentenceBar button to open dropdown menu bar when hover
            $("#idSentenceBar").hover(function () {
                console.log('hover');
                $scope.dropdownMenuOpen = true;
            });
            //Choose the buttons to show on bar
            dropdownMenuBarInit($rootScope.interfaceLanguageId)
                    .then(function () {
                        //Choose the buttons to show on bar
                        angular.forEach($rootScope.dropdownMenuBar, function (value) {
                            if (value.href == '/' || value.href == 'editPanel' || value.href == '/panelGroups' || value.href == '/userConfig' || value.href == '/faq' || value.href == '/tips' || value.href == '/privacy' || value.href == 'logout') {
                                value.show = true;
                            } else {
                                value.show = false;
                            }
                        });
                    });
            //function to change html view
            $scope.go = function (path) {
                if (path == '/') {
                    $scope.config();
                } else if (path == 'logout') {
                    $('#logoutModal').modal('toggle');
                } else if (path == 'editPanel') {
                    $scope.edit();
                } else {
                    $location.path(path);
                    $rootScope.dropdownMenuBarValue = path; //Button selected on this view
                }
            };

            //Log Out Modal
            $scope.img = [];
            $scope.img.lowSorpresaFlecha = '/img/srcWeb/Mus/lowSorpresaFlecha.png';
            $scope.img.Patterns1_08 = '/img/srcWeb/patterns/pattern3.png';
            $scope.img.loading = '/img/srcWeb/Login/loading.gif';
            Resources.main.get({'section': 'logoutModal', 'idLanguage': $rootScope.interfaceLanguageId}, {'funct': "content"}).$promise
                    .then(function (results) {
                        $scope.logoutContent = results.data;
                    });
            $scope.logout = function () {
                $scope.viewActived = false;
                $timeout(function () {
                    AuthService.logout();
                }, 1000);
            };

            if (!$rootScope.isLogged) {
                $rootScope.dropdownMenuBarValue = '/home'; //Dropdown bar button selected on this view
                $location.path('/home');
            } else {
                Resources.main.get({'IdSu': $rootScope.sUserId}, {'funct': "getConfig"}).$promise
                        .then(function (results) {
                            if (results.userConfig.UserValidated == '1') {
                                //redirecciona a configuración de usuario para el primer acceso a la aplicación
                                $location.path('/userConfig');
                            }
                        });
            }

            // Pedimos los textos para cargar la pagina
            txtContent("mainboard").then(function (results) {
                $rootScope.content = results.data;
            });

            // Get event Edit call in the mune bar
            $scope.$on("EditCallFromMenu", function () {
                $scope.edit();
            });
            // Get event Init call in the mune bar
            $scope.$on("IniciCallFromMenu", function () {
                //MODIF: Se tiene que hacer con configuracion de usuario

                $scope.config();
            });
            //MODIF: Solo para hacer pruebas
            $scope.$on("ScanCallFromMenu", function () {
                $scope.InitScan();
            });

            $scope.setTimer = function () {
                $interval.cancel($scope.intervalScan);
                var Intervalscan = $scope.cfgTimeScanning;
                function myTimer() {
                    if ($scope.isScanningCancel) {
                        //We are not scanning cancel anymore
                        $scope.isScanningCancel = false;
                    } else {
                        $scope.nextBlockScan();
                    }
                }
                ;
                $scope.intervalScan = $interval(myTimer, Intervalscan);
            };
            $scope.InitScan = function ()
            {
                if ($scope.inEdit) {
                    return false;
                }
                $scope.inScan = true;
                if ($scope.cfgScanningCustomRowCol == 0) {
                    $scope.getMaxScanBlock1();
                }

                //When the scan is automatic, this timer manage when the scan have to move to the next block
                if ($scope.timerScan) {
                    $scope.setTimer();
                }

                $scope.arrayScannedCells = null;
                $scope.isScanningCancel = false;
                //The user cfg tell us where we have to start
                if ($scope.cfgScanStartClick && $scope.isScanning != "nowait") {
                    $scope.isScanning = "waiting";
                } else {
                    $scope.nextBlockToScan(0);
                }
            };
            //Return true if the cell it's being scanned
            $scope.isScanned = function (picto) {
                if ($scope.inScan && $scope.isScanningCancel === false && $scope.arrayScannedCells != null) {
                    switch ($scope.isScanning) {
                        case "board":
                            for (var i = 0; i < $scope.arrayScannedCells.length; i++) {
                                if ($scope.arrayScannedCells[i].ID_Cell == picto.ID_Cell) {
                                    return true;
                                }
                            }
                            break;
                        case "boardCell":
                            if (picto.ID_Cell === $scope.arrayScannedCells[$scope.indexScannedCells].ID_Cell) {
                                return true;
                            }
                            break;
                        case "boardCustomExtra":
                            for (var i = 0; i < $scope.arrayScannedCells.length; i++) {
                                if ($scope.arrayScannedCells[i].customScanBlock2 == $scope.indexScannedCells && $scope.arrayScannedCells[i].ID_Cell == picto.ID_Cell) {
                                    return true;
                                }
                            }
                            break;
                    }
                }
                return false;


            };
            // When we get out from scanMode stops the interval
            $scope.$watch('inScan', function () {
                if ($scope.inScan === false) {
                    $interval.cancel($scope.intervalScan);
                }
            });

            //Control the left click button while scanning
            $scope.scanLeftClick = function ()
            {
                if ($scope.inScan) {
                    //If user have start scan click activate we have to wait until he press one button
                    if ($scope.isScanning == "waiting") {
                        $scope.nextBlockToScan(0);
                        $scope.setTimer();
                    } else if ($scope.isScanningCancel) {
                        $scope.isScanningCancel = false;
                        $scope.isScanning = "nowait";
                        $scope.InitScan();
                    } else {
                        if (!$scope.longclick)
                        {
                            $scope.selectBlockScan();
                        }
                    }
                }
            };

            //Control the right click button while scanning
            $scope.scanRightClick = function ()
            {
                if ($scope.inScan) {
                    //If user have start scan click activate we have to wait until he press one button
                    if ($scope.isScanning == "waiting") {
                        $scope.nextBlockToScan(0);
                        $scope.setTimer();
                    }
                    if ($scope.isScanningCancel) {
                        $scope.isScanningCancel = false;
                        $scope.isScanning = "nowait";
                        $scope.InitScan();
                    } else {
                        if (!$scope.longclick && !$scope.timerScan)
                        {
                            $scope.nextBlockScan();
                        }
                    }
                }
            };
            //Control the long click button while scanning (to detect when it's a long one)
            $scope.playLongClick = function ()
            {
                var userConfig = JSON.parse(localStorage.getItem('userData'));
                if ($scope.inScan) {
                    if ($scope.longclick)
                    {
                        $timeout.cancel($scope.scanLongClickTime);
                        $scope.scanLongClickController = true;
                        $scope.scanLongClickTime = $timeout($scope.selectBlockScan, userConfig.cfgTimeClick);
                    }
                }
            };
            //Control the long click button while scanning (to detect when it's a short one)
            $scope.cancelLongClick = function ()
            {
                if ($scope.inScan) {
                    if ($scope.longclick)
                    {
                        if ($scope.scanLongClickController)
                        {
                            $timeout.cancel($scope.scanLongClickTime);
                            $scope.nextBlockScan();
                        } else
                        {

                        }
                    }
                }
            };

            // Get the number of scan blocks 
            $scope.getMaxScanBlock1 = function ()
            {
                var maxCustomScanBlockProv = 0;
                for (var i = 0; i < $scope.columns * $scope.rows; i++) {
                    if ($scope.data[i].customScanBlock1 > maxCustomScanBlockProv) {
                        maxCustomScanBlockProv = $scope.data[i].customScanBlock1;
                    }
                }
                $scope.maxCustomScanBlock = maxCustomScanBlockProv;
            };
            // Get the number of level 2 scan blocks
            $scope.getMaxScanBlock2 = function ()
            {
                var maxCustomScanBlockProv = 0;
                for (var i = 0; i < $scope.arrayScannedCells.length; i++) {
                    if ($scope.arrayScannedCells[i].customScanBlock2 > maxCustomScanBlockProv) {
                        maxCustomScanBlockProv = $scope.arrayScannedCells[i].customScanBlock2;
                    }
                }
                $scope.maxCustomScanBlock = maxCustomScanBlockProv;

            };
            //Update the array that contains the cells that we have to scan
            $scope.getScanArray = function () {
                //True if in the group there are at least one cell that it have to be scanned
                var toScan = false;
                var arrayScannedCellsProv = [];
                if ($scope.cfgScanningCustomRowCol == 0) {
                    //The last group have been reached
                    if ($scope.indexScannedBlock > $scope.maxCustomScanBlock) {
                        $scope.nextBlockToScan($scope.cfgScanOrderPanel);
                        return false;
                    } else {
                        var j = 0;
                        //Search in the board the cells that are in the current scan block
                        for (var i = 0; i < $scope.columns * $scope.rows; i++) {
                            if ($scope.data[i].customScanBlock1 == $scope.indexScannedBlock) {
                                //If the cell is disable it won't be added to the array
                                if ($scope.haveToBeScanned($scope.data[i])) {
                                    toScan = true;
                                    arrayScannedCellsProv[j] = $scope.data[i];
                                    j++;
                                }
                            }
                        }
                    }
                    //Works like the last one except that the cell will be added to the array anyway (to avoid strange empty slots in the scan) and we have not to read all the cell, we acces to the cell by the pos 
                } else if ($scope.cfgScanningCustomRowCol == 1) {
                    if ($scope.indexScannedBlock > $scope.rows - 1) {
                        $scope.nextBlockToScan($scope.cfgScanOrderPanel);
                        return false;
                    } else {
                        for (var i = 0; i < $scope.columns; i++) {
                            arrayScannedCellsProv[i] = $scope.data[$scope.indexScannedBlock * $scope.columns + i];
                            if ($scope.haveToBeScanned(arrayScannedCellsProv[i])) {
                                toScan = true;
                            }
                        }
                    }
                    //Pretty the same
                } else {
                    if ($scope.indexScannedBlock > $scope.columns - 1) {
                        $scope.nextBlockToScan($scope.cfgScanOrderPanel);
                        return false;
                    } else {
                        for (var i = 0; i < $scope.rows; i++) {
                            arrayScannedCellsProv[i] = $scope.data[i * $scope.columns + $scope.indexScannedBlock];
                            if ($scope.haveToBeScanned(arrayScannedCellsProv[i])) {
                                toScan = true;
                            }
                        }
                    }
                }
                //If all the cells inside the array have no value (empty or no active cells) pass to the next block
                if (toScan === false) {
                    arrayScannedCellsProv = null;
                    $scope.nextBlockScan();
                } else {
                    //Else, update the cell array
                    $scope.$evalAsync(function ($scope) {
                        // Cuando efectuas un cambio en una variable de angular, el html puede no darse cuenta de este cambion (pasa pocas veces, cuando el cambio se hace sin interactuación del usuario o cuando el usuario ha interactuado y pasa un ciclo de angular). Con el eval, obliga a angular a actualizarse (llama a un apply, pero sin dar error)
                        $scope.arrayScannedCells = angular.copy(arrayScannedCellsProv);
                    });
                }
            };
            //This method check if the actual scan block (second level in the custom scan) it's correct
            $scope.getCustomScanCell = function () {
                if ($scope.indexScannedCells > $scope.maxCustomScanBlock) {
                    $scope.indexScannedCells = null;
                }
                var moreThanOneGroup = false;
                var lastGroup = -1;
                var toScan = false;
                //Search in the array cell the cells that are in the current scan block 2 
                for (var i = 0; i < $scope.arrayScannedCells.length; i++) {
                    //Check if there are only one subgroup
                    if ($scope.arrayScannedCells[i].customScanBlock2 != lastGroup) {
                        if (lastGroup == -1) {
                            lastGroup = $scope.arrayScannedCells[i].customScanBlock2;
                        } else {
                            moreThanOneGroup = true;
                        }
                    }
                    if ($scope.arrayScannedCells[i].customScanBlock2 == $scope.indexScannedCells) {
                        if ($scope.haveToBeScanned($scope.arrayScannedCells[i])) {
                            toScan = true;
                        }
                    }
                }
                //If all the cells inside the array have no value (empty or no active cells) pass to the next block
                if (!toScan) {
                    $scope.nextBlockScan();
                }
                //If there are only one subgroup, then select the group (if we don't do this the user have to do a extra and confusing click
                if (!moreThanOneGroup && toScan) {
                    $scope.selectBlockScan();
                }

            };
            //Check if the actual cell (in this point we are pinting to just a one, not a group) is active. Also check if we pass all the cells to start again
            $scope.getScanCell = function () {
                if ($scope.cfgScanningCustomRowCol == 0) {
                    if ($scope.indexScannedCells > $scope.arrayScannedCells.length - 1) {
                        $scope.isScanning = "nowait";
                        $scope.InitScan();
                        return false;
                    }
                    if (!$scope.haveToBeScanned($scope.arrayScannedCells[$scope.indexScannedCells])) {
                        $scope.nextBlockScan();
                    }

                } else {
                    //Nos hemos pasado
                    if (($scope.cfgScanningCustomRowCol == 1 && $scope.indexScannedCells > $scope.columns - 1) || ($scope.cfgScanningCustomRowCol == 2 && $scope.indexScannedCells > $scope.rows - 1)) {
                        $scope.isScanning = "nowait";
                        $scope.InitScan();
                        return false;
                    }
                    if (!$scope.haveToBeScanned($scope.arrayScannedCells[$scope.indexScannedCells])) {
                        $scope.nextBlockScan();
                    }
                }
            };
            //Check if the cell have to be added to the array or not
            $scope.haveToBeScanned = function (picto) {
                return (picto.activeCell == 1 && (picto.ID_CFunction != null || picto.ID_CPicto != null || picto.ID_CSentence != null || picto.ID_Fuction != null || picto.boardLink != null || picto.sentenceFolder));
            };
            //OrderScan contains the order that the user establish so, whenever we pass to another scan (only first level) we have to determine the next by this array. Be carefulthe array start with 0 and the user order is 1, 2 and 3, so we don't have to increment the index, we use the current block orde to resolve what the next is
            $scope.nextBlockToScan = function (current) {
                switch ($scope.orderScan[current]) {
                    case "prediction":
                        $scope.isScanning = "prediction";
                        if ($scope.cfgPredOnOff === '0') {
                            $scope.nextBlockToScan($scope.cfgScanOrderPred);
                        }
                        break;
                    case "sentence":
                        $scope.isScanning = "sentence";
                        if ($scope.cfgMenuDeleteLastActive + $scope.cfgMenuDeleteAllActive + $scope.cfgMenuReadActive + $scope.cfgMenuHomeActive < 1) {
                            $scope.nextBlockToScan($scope.cfgScanOrderMenu);
                        }
                        break;
                    case "board":
                        $scope.isScanning = "board";
                        $scope.indexScannedCells = 0; //Cell inside the array
                        $scope.indexScannedBlock = 0; //Column or row inside the whole board
                        $scope.arrayScannedCells = $scope.getScanArray();
                        break;
                    default:
                        $scope.isScanning = "nowait";
                        $scope.InitScan();
                        break;
                }
            };
            // Change the current scan block
            $scope.nextBlockScan = function () {
                if ($scope.inScan) {
                    switch ($scope.isScanning) {
                        case "goodPhrase":
                            $scope.isScanning = "badPhrase";
                            break;
                        case "badPhrase":
                            $scope.feedback(1);
                            break;
                        case "waiting"://Do nothing
                            break;
                        case "prediction":
                            $scope.nextBlockToScan($scope.cfgScanOrderPred);
                            break;
                        case "sentence":
                            $scope.nextBlockToScan($scope.cfgScanOrderMenu);
                            break;
                        case "board":
                            $scope.indexScannedBlock = $scope.indexScannedBlock + 1;
                            $scope.arrayScannedCells = $scope.getScanArray();
                            break;
                        case "boardCustomExtra":
                            if ($scope.indexScannedCells === null) {
                                $scope.isScanning = "nowait";
                                $scope.InitScan();
                                return false;
                            } else {
                                $scope.indexScannedCells = $scope.indexScannedCells + 1;
                                $scope.getCustomScanCell();
                            }
                            break;
                        case "boardCell":
                            $scope.indexScannedCells = $scope.indexScannedCells + 1;
                            $scope.getScanCell();
                            break;
                        case "predictionCell":
                            $scope.indexScannedCells = $scope.indexScannedCells + 1;
                            if ($scope.indexScannedCells > $scope.arrayScannedCells.length - 1) {
                                $scope.isScanning = "nowait";
                                $scope.InitScan();
                            }
                            break;
                        case "home":
                            $scope.isScanning = "read";
                            if ($scope.cfgMenuReadActive == 0) {
                                $scope.nextBlockScan();
                            }
                            break;
                        case "read":
                            $scope.isScanning = "deletelast";
                            if ($scope.cfgMenuDeleteLastActive == 0) {
                                $scope.nextBlockScan();
                            }
                            break;
                        case "deletelast":
                            $scope.isScanning = "deleteall";
                            if ($scope.cfgMenuDeleteAllActive == 0) {
                                $scope.nextBlockScan();
                            }
                            break;
                        case "deleteall":
                            $scope.isScanning = "nowait";
                            $scope.InitScan();
                    }

                }
            };
            //Pass to the next scan level (subgroup)
            $scope.selectBlockScan = function () {
                if ($scope.inScan) {
                    //when we select a picto cancel the actual timer and set up another
                    if ($scope.timerScan) {
                        $scope.setTimer();
                    }
                    //cancel long click
                    if ($scope.longclick)
                    {
                        $scope.scanLongClickController = false;
                    }
                    switch ($scope.isScanning) {
                        case "goodPhrase":
                            $scope.feedback(1);
                            break;
                        case "badPhrase":
                            $scope.feedback(0);
                            break;
                        case "waiting"://Do nothing
                            break;
                        case "prediction":
                            $scope.arrayScannedCells = $scope.recommenderArray;
                            $scope.indexScannedCells = 0;
                            $scope.isScanning = "predictionCell";
                            $scope.isScanningCancel = $scope.cfgCancelScanOnOff;
                            break;
                        case "sentence":
                            $scope.isScanningCancel = $scope.cfgCancelScanOnOff;
                            $scope.isScanning = "home";
                            if ($scope.cfgMenuHomeActive === 0) {
                                $scope.nextBlockScan();
                            }
                            break;
                        case "board":
                            $scope.isScanningCancel = $scope.cfgCancelScanOnOff;
                            $scope.indexScannedCells = 0; //Cell inside the array
                            if ($scope.cfgScanningCustomRowCol == 0) {
                                $scope.getMaxScanBlock2();
                                $scope.isScanning = "boardCustomExtra";
                                $scope.getCustomScanCell();
                            } else {
                                $scope.isScanning = "boardCell";
                                $scope.getScanCell();
                            }
                            break;
                        case "boardCustomExtra":
                            $scope.isScanningCancel = $scope.cfgCancelScanOnOff;
                            $scope.selectCustomScannedCell();
                            break;
                        case "boardCell":
                            $scope.selectScannedCell();
                            break;
                        case "predictionCell":
                            $scope.addToSentence($scope.arrayScannedCells[$scope.indexScannedCells].pictoid, $scope.arrayScannedCells[$scope.indexScannedCells].imgCell, $scope.arrayScannedCells[$scope.indexScannedCells].pictotext);
                            $scope.InitScan();
                            break;
                        case "home":
                            $scope.goPrimaryBoard();
                            break;
                        case "read":
                            $scope.generate();
                            $scope.InitScan();
                            break;
                        case "deletelast":
                            $scope.deleteLast();
                            $scope.InitScan();
                            break;
                        case "deleteall":
                            $scope.deleteAll();
                            $scope.InitScan();
                            break;

                    }
                }
            };

            // Select the current cell (the index point to the array with all the cells)
            $scope.selectScannedCell = function ()
            {
                $scope.clickOnCell($scope.arrayScannedCells[$scope.indexScannedCells]);
                $scope.InitScan();
            };

            // Select the current cell (the index point to the array with all the cells)
            $scope.selectCustomScannedCell = function ()
            {
                if ($scope.indexScannedCells > $scope.maxCustomScanBlock) {
                    $scope.indexScannedCells = null;
                }
                var arrayScannedCellsProv = [];
                var j = 0;
                for (var i = 0; i < $scope.arrayScannedCells.length; i++) {
                    if ($scope.arrayScannedCells[i].customScanBlock2 == $scope.indexScannedCells) {
                        arrayScannedCellsProv[j] = $scope.arrayScannedCells[i];
                        j++;
                    }
                }
                if (arrayScannedCellsProv.length == 1) {
                    $scope.clickOnCell($scope.arrayScannedCells[0]);
                    $scope.InitScan();
                } else {
                    $scope.$evalAsync(function ($scope) {
                        $scope.arrayScannedCells = angular.copy(arrayScannedCellsProv);
                        $scope.indexScannedCells = 0;
                    });
                    $scope.isScanning = "boardCell";
                }
            };

            $scope.isPictoActive = function (picto) {
                return (picto.activeCell == 1 || (picto.activeCell == 0 && $scope.inEdit));
            };
            //We have to react to the ng-click events? In edit mode we have to react to some event, in a diferent way
            $scope.isClickEnable = function () {
                return(!($scope.inScan || $scope.cfgTimeOverOnOff));
            };
            // Get the user config and show the board
            $scope.config = function ()
            {
                //-----------Iniciacion-----------
                var userConfig = JSON.parse(localStorage.getItem('userData'));
                var url = $scope.baseurl + "Board/loadCFG";

                var postdata = {lusuid: userConfig.ID_ULanguage};
                //MODIF: borrar
                $http.post(url, postdata);

                $scope.userViewHeight = 100;
                $scope.searchFolderHeight = 0;

                $scope.puntuando = false;
                if (!$scope.tense)
                    $scope.tense = "defecte";
                if (!$scope.tipusfrase)
                    $scope.tipusfrase = "defecte";
                if (!$scope.negativa)
                    $scope.negativa = false;
                $scope.SearchType = "Tots";
                $scope.inEdit = false;
                $scope.inScan = false;

                //-----------Iniciacion-----------

                // load user configuration in the scope
                $scope.cfgPredOnOff = userConfig.cfgPredOnOff;
                $scope.cfgPredBarVertHor = userConfig.cfgPredBarVertHor;
                $scope.cfgPredBarNumPred = userConfig.cfgPredBarNumPred;

                $scope.sentenceViewHeight = 16;
                $scope.userViewWidth = 12;
                $scope.searchFolderHeight = 0;
                $scope.boardHeight = 83;
                if ($scope.cfgPredOnOff === '1' && $scope.cfgPredBarVertHor === '0') { // Prediction on and vertical
                    $scope.predViewWidth = 1;
                    $scope.userViewWidth = 11;
                    if (window.innerWidth < 1400) {
                        $scope.predViewWidth = 2;
                        $scope.userViewWidth = 10;
                    }
                }

                $scope.cfgMenuHomeActive = userConfig.cfgMenuHomeActive;
                $scope.cfgMenuReadActive = userConfig.cfgMenuReadActive;
                $scope.cfgMenuDeleteLastActive = userConfig.cfgMenuDeleteLastActive;
                $scope.cfgMenuDeleteAllActive = userConfig.cfgMenuDeleteAllActive;
                $scope.cfgSentenceBarUpDown = userConfig.cfgSentenceBarUpDown;
                $scope.pictoBarWidth = 12 - $scope.cfgMenuHomeActive - $scope.cfgMenuReadActive - $scope.cfgMenuDeleteLastActive - $scope.cfgMenuDeleteAllActive;
                $scope.cfgAutoEraseSentenceBar = userConfig.cfgAutoEraseSentenceBar;
                $scope.cfgScanningCustomRowCol = userConfig.cfgScanningCustomRowCol;
                $scope.longclick = userConfig.cfgScanningAutoOnOff == 0 ? true : false;
                $scope.timerScan = userConfig.cfgScanningAutoOnOff == 1 ? true : false;
                $scope.cfgTimeScanning = userConfig.cfgTimeScanning;
                $scope.cfgTimeOverOnOff = userConfig.cfgTimeLapseSelectOnOff == 1 ? true : false;
                $scope.cfgTimeOver = userConfig.cfgTimeLapseSelect;
                $scope.cfgTimeNoRepeatedClickOnOff = userConfig.cfgTimeNoRepeatedClickOnOff;
                $scope.cfgTimeNoRepeatedClick = userConfig.cfgTimeNoRepeatedClick;
                $scope.TimeMultiClic = 0;
                $scope.cfgScanningOnOff = userConfig.cfgScanningOnOff;
                $scope.cfgScanStartClick = userConfig.cfgScanStartClick == 1 ? true : false;
                $scope.cfgCancelScanOnOff = userConfig.cfgCancelScanOnOff == 1 ? true : false;
                $scope.cfgTextInCell = userConfig.cfgTextInCell == 1 ? true : false;
                $scope.cfgUserExpansionFeedback = userConfig.cfgUserExpansionFeedback == 1 ? true : false;
                $scope.cfgScanOrderPred = userConfig.cfgScanOrderPred;
                $scope.cfgScanOrderMenu = userConfig.cfgScanOrderMenu;
                $scope.cfgScanOrderPanel = userConfig.cfgScanOrderPanel;
                $scope.orderScan = ["", "", ""];
                $scope.orderScan[userConfig.cfgScanOrderPred - 1] = "prediction";
                $scope.orderScan[userConfig.cfgScanOrderMenu - 1] = "sentence";
                $scope.orderScan[userConfig.cfgScanOrderPanel - 1] = "board";
                $scope.cfgBgColorPanel = userConfig.cfgBgColorPanel;
                $scope.cfgBgColorPred = userConfig.cfgBgColorPred;
                $scope.cfgScanColor = userConfig.cfgScanColor;
                $scope.cfgExpansionOnOff = userConfig.cfgExpansionOnOff == 1 ? true : false;
                $scope.cfgInterfaceVoiceOnOff = userConfig.cfgInterfaceVoiceOnOff == 1 ? true : false;
                if (userConfig.cfgUsageMouseOneCTwoC == 0) {
                    $scope.longclick = false;
                    $scope.timerScan = false;
                    $scope.cfgScanStartClick = false;
                } else if (userConfig.cfgUsageMouseOneCTwoC == 1) {
                    if ($scope.longclick) {
                        $scope.cfgScanStartClick = false;
                        $scope.cfgCancelScanOnOff = false;
                    }
                    $scope.cfgTimeOverOnOff = false;
                    $scope.cfgTimeNoRepeatedClickOnOff = false;
                } else if (userConfig.cfgUsageMouseOneCTwoC == 2) {
                    $scope.longclick = false;
                    $scope.timerScan = false;
                    $scope.cfgTimeOverOnOff = false;
                    $scope.cfgTimeNoRepeatedClickOnOff = false;
                    $scope.cfgScanStartClick = false;
                    $scope.cfgCancelScanOnOff = false;
                }
                $scope.getSentence();
                $scope.getPred();
                //If there are some request from another controller, it's loaded
                if ($rootScope.editPanelInfo != null) {
                    $scope.showBoard($rootScope.editPanelInfo.idBoard);
                    $scope.edit();
                    $rootScope.editPanelInfo = null;
                } else if ($rootScope.boardToShow) {
                    $scope.showBoard($rootScope.boardToShow).then(function () {
                        if ($scope.cfgScanningOnOff == 1) {
                            $scope.InitScan();
                        }
                    });
                    $rootScope.boardToShow = null;
                } else {
                    $scope.getPrimaryUserBoard();
                }
            };
            //Get the current sentence in order to show to the user
            $scope.getSentence = function () {
                var url = $scope.baseurl + "Board/getTempSentence";

                return $http.post(url).success(function (response)
                {
                    $scope.dataTemp = response.data;
                });

            };
            //Get priamry user board (primary board in the priamry group) and show it. if the user have scannigOn start Scan
            $scope.getPrimaryUserBoard = function () {
                var url = $scope.baseurl + "Board/getPrimaryUserBoard";

                $http.post(url).success(function (response)
                {
                    $scope.idboard = response.idboard;
                    //If the user doesn't have one or some gone wrong a error modal is displayed
                    if ($scope.idboard === null) {
                        $('#errorNoPrimaryBoard').modal({backdrop: 'static'});
                    } else {
                        $scope.showBoard('0').then(function () {
                            if ($scope.cfgScanningOnOff == 1) {
                                $scope.InitScan();
                            }
                        });
                    }
                });
            };
            //When the user press acept (in the no primaryboard modal) panelgroups it's loaded 
            $scope.aceptErrorNPB = function () {
                
                $timeout(function () {
                    $location.path('/panelGroups');
                }, 500);
                
            };
            /*
             * Return: array from 0 to repeatnum
             */
            $scope.range = function ($repeatnum)
            {
                var n = [];
                for (i = 1; i <= $repeatnum; i++)
                {
                    n.push(i);
                }
                return n;
            };

            /*
             * Show board and the pictograms
             */
            $scope.showBoard = function (id)
            {

                //If the id is 0, show (reload) the actual board. Else the current board is changed (and showed)
                if (id === '0') {
                    id = $scope.idboard;
                } else {
                    $scope.idboard = id;
                }

                var url = $scope.baseurl + "Board/showCellboard";
                var postdata = {idboard: id};

                return $http.post(url, postdata).success(function (response)
                {
                    $scope.columns = response.col;
                    $scope.rows = response.row;
                    $scope.data = response.data;
                    $scope.autoRead = response.autoRead;
                    //Something gone wrong... (maybe he/she tried to access to another user's board)
                    if ($scope.data == null) {
                        $location.path('/panelGroups');
                    }
                });
            };
            //Load the recommenderArray that will be displayed in the prediction bar.
            $scope.getPred = function ()
            {
                console.log("What's happening...");
                var url = $scope.baseurl + "Board/getPrediction";
                $http.post(url).success(function (response)
                {
                    $scope.recommenderArray = response.recommenderArray;
                });
            };

            /*
             * Show edit view board
             */
            $scope.edit = function ()
            {
                $scope.readmore = false;
                $scope.uploading = false;
                $scope.evt = {nameboard: "", altura: 1, amplada: 1, autoreturn: false, autoread: false};
                $scope.fv = {colorPaintingSelected: "ffffff", painting: false};
                $scope.getGroupBoardInfo();
                $scope.inEdit = true;
                $scope.inScan = false;
                $scope.cfgPredOnOff = 0;
                $scope.predViewWidth = 0;
                $scope.boardHeight = 100;
                $scope.userViewWidth = 9;
                $scope.editViewWidth = 3;
                $scope.userViewHeight = 85;
                $scope.searchFolderHeight = 13;
                $scope.typeSearh = "picto";
                $scope.typeImgEditSearch = "Arasaac";

                if (window.innerWidth < 1050) {
                    $scope.userViewWidth = 8;
                    $scope.editViewWidth = 4;
                }
                //Load the colors from the DDBB in the drop down menu
                $scope.getColors();

                var url = $scope.baseurl + "Board/getCellboard";
                var postdata = {idboard: $scope.idboard};

                $http.post(url, postdata).success(function (response)
                {
                    $scope.evt.nameboard = response.name;
                    $scope.evt.altura = $scope.range(10)[response.row - 1].valueOf();
                    $scope.evt.amplada = $scope.range(10)[response.col - 1].valueOf();
                    $scope.evt.autoreturn = (response.autoReturn === '1' ? true : false);
                    $scope.evt.autoread = (response.autoRead === '1' ? true : false);

                });
            };

            $scope.getColors = function () {
                var url = $scope.baseurl + "Board/getColors";
                $http.post(url).success(function (response)
                {
                    $scope.colors = response.data;
                });
            };
            //Change the tab in the serach view (edit mode) between pictos and images
            $scope.changeEditSearch = function (source) {
                if ($scope.typeSearh != source)
                    $scope.typeSearh = source;
            };
            // Gets all the boards and initialize the two dropdown menus
            $scope.getGroupBoardInfo = function () {
                var url = $scope.baseurl + "Board/getBoards";
                var postdata = {idboard: $scope.idboard};

                $http.post(url, postdata).success(function (response)
                {

                    $scope.allBoards = response.boards;
                    $scope.nameBoard = response.name;
                    for (var i = 0; i < $scope.allBoards.length; i++) {
                        if ($scope.allBoards[i].ID_Board == $scope.idboard) {
                            $scope.sb = {selectBoard: $scope.allBoards[i]};
                            break;
                        }
                    }
                    $scope.primaryBoard = {ID_Board: response.primaryBoard.ID_Board};
                });

            };
            //Change the autoReturn poperty in the database
            $scope.changeAutoReturn = function ()
            {
                var postdata = {id: $scope.idboard, value: $scope.evt.autoreturn};
                var URL = $scope.baseurl + "Board/changeAutoReturn";
                $http.post(URL, postdata).
                        success(function ()
                        {

                        });
            };
            //Change the autoReadSentence poperty in the database
            $scope.changeAutoReadSentence = function ()
            {
                var postdata = {id: $scope.idboard, value: $scope.evt.autoread};
                var URL = $scope.baseurl + "Board/changeAutoRead";
                $http.post(URL, postdata).
                        success(function ()
                        {

                        });
            };

            // Change the primary board of the group
            $scope.changePrimaryBoard = function (value)
            {
                var postdata = {id: value.ID_Board, idBoard: value.ID_GBBoard};
                var url = $scope.baseurl + "Board/changePrimaryBoard";

                $http.post(url, postdata).success(function (response)
                {

                });
            };
            // Change the shown board
            $scope.changeBoard = function ()
            {
                $scope.showBoard($scope.sb.selectBoard.ID_Board);
                // We are in edit mode so update the edit information
                $scope.edit();
            };
            // Change the name board
            $scope.changeNameBoard = function (key, nameboard, boardindex)
            {
                if (key === 13)
                {
                    var postdata = {Name: nameboard, ID: boardindex};
                    var URL = $scope.baseurl + "Board/modifyNameboard";
                    $http.post(URL, postdata).
                            success(function (response)
                            {
                                $scope.edit();
                            });
                }
            };

            /*
             * Resize cellboard (height and width)
             */
            $scope.changeSize = function ($newH, $newW, $HW)
            {

                var url = $scope.baseurl + "Board/getCellboard";
                var postdata = {idboard: $scope.idboard};

                $http.post(url, postdata).success(function (response)
                {

                    $scope.oldH = response.row;
                    $scope.oldW = response.col;


                    if ($HW == "1") {
                        $newH = $scope.oldH;
                    } else {
                        $newW = $scope.oldW;
                    }
                    var postdata = {r: $newH, c: $newW, idboard: $scope.idboard};
                    if ($newH < $scope.oldH || $newW < $scope.oldW) {
                        $scope.openConfirmSize($newH, $scope.oldH, $newW, $scope.oldW);
                    } else {

                        var url = $scope.baseurl + "Board/modifyCellBoard";
                        $http.post(url, postdata).then(function ()
                        {
                            $scope.showBoard($scope.idboard);
                        });
                        $scope.edit();

                    }
                });
            };
            /*
             * Open a dialog to confirm the new resize (when you resize to a lower size)
             */
            $scope.openConfirmSize = function ($newH, $oldH, $newW, $oldW) {

                var postdata = {r: $newH, c: $newW, idboard: $scope.idboard};
                //Object of all new/old sizes
                $scope.FormData = {
                    newH: $newH,
                    oldH: $oldH,
                    newW: $newW,
                    oldW: $oldW,
                    HWType: 2,
                    Dnum: 0
                };
                if ($newH !== $oldH)
                {
                    $scope.FormData.HWType = 0;
                    $scope.FormData.Dnum = ($oldH - $newH);
                } else if ($newW !== $oldW)
                {
                    $scope.FormData.HWType = 1;
                    $scope.FormData.Dnum = ($oldW - $newW);
                }
                $scope.DataResize = postdata;
                $('#ConfirmResize').modal({backdrop: 'static'});
            };
            $scope.AcceptOpenConfirmSize = function () {
                var url = $scope.baseurl + "Board/modifyCellBoard";
                $http.post(url, $scope.DataResize).then(function (response) {
                    $scope.showBoard('0');
                });
            };

            $scope.DenyOpenConfirmSize = function () {
                //reload the dropdown menus
                $scope.edit();
                $scope.showBoard('0');
            };


            /*
             * The user has clicked the cell. The cell can be:
             *      SentenceFolder: The user will be redirected to the historic view
             *      Sentence: The sentence will be readed
             *      Picto: The picto is added to the sentece
             *      Link to another board: the user will be redirected to this new board
             *      Function: go to the historic, change the tipus or tme of the sentence...
             * The last three can be together in the same cell
             *      
             */
            $scope.clickOnCell = function (cell) {

                if (!$scope.inEdit && cell.activeCell == 1) {
                    if (cell.sentenceFolder) {
                        $rootScope.senteceFolderToShow = {folder: cell.sentenceFolder, boardID: $scope.idboard};
                        $location.path('/historic');
                        return false;
                    }
                    if (cell.ID_CSentence) {
                        $scope.readText(cell.sPreRecText, false);
                        return false;
                    }
                    var text = "";
                    // Just read once, and, if the autoread is activated in this board we don't have to read (the text will be mixed up)
                    var readed = false;
                    if ($scope.autoRead) {
                        readed = true;
                    }
                    if (cell.ID_CPicto !== null) {
                        if (cell.textInCell !== null) {
                            text = cell.textInCell;
                        } else {
                            text = cell.pictotext;
                        }
                        if (readed)
                            text = "";
                        $scope.addToSentence(cell.ID_CPicto, cell.imgCell, text);
                        readed = true;
                    }
                    if (cell.boardLink !== null) {
                        if (readed === true) {
                            text = "";
                        } else {
                            text = cell.textInCell;
                        }

                        $scope.showBoard(cell.boardLink);
                        $scope.readText(text, true);
                        readed = true;
                    }
                    if (cell.ID_CFunction !== null) {
                        if (readed === true) {
                            text = "";
                        } else if (cell.textInCell !== null) {
                            text = cell.textInCell;
                        } else {
                            text = cell.textFunction;
                        }
                        $scope.clickOnFunction(cell.ID_CFunction, text, readed);
                    }
                    if (cell.boardLink === null) {
                        $scope.autoReturn();
                        $scope.automaticRead();
                    }
                    //If we are in edit mode the user can not create a sentence (or whatever). But, the user can edit the color of the cells
                } else if ($scope.inEdit && $scope.fv.painting) {
                    var postdata = {id: cell.ID_RCell, color: $scope.fv.colorPaintingSelected};
                    var url = $scope.baseurl + "Board/modifyColorCell";
                    $http.post(url, postdata).then(function ()
                    {
                        //To update the field in the ng-repeat we have to change the object itself (not only the property)
                        var obj = $scope.data[cell.posInBoard - 1];
                        obj.color = $scope.fv.colorPaintingSelected;
                        $scope.data[cell.posInBoard - 1] = angular.copy(obj);
                    });
                }
            };
            //Bool is true when the text comes from interface and false if the text is the sentence
            $scope.readText = function (text, bool) {
                if (((bool && $scope.cfgInterfaceVoiceOnOff) || (!bool)) && (text !== "")) {
                    $scope.sound = "mp3/empty.m4a";
                    var postdata = {text: text, interface: bool};
                    var url = $scope.baseurl + "Board/readText";
                    $http.post(url, postdata).success(function (response) {
                        $scope.dataAudio = response.audio;
                        if (!$scope.dataAudio[1]) {
                            $scope.sound = "mp3/" + $scope.dataAudio[0];
                            var audiotoplay = $('#utterance');
                            audiotoplay.src = "mp3/" + $scope.dataAudio[0];
                            
                            console.log($scope.dataAudio[0]);
                            if ($scope.cfgTimeOverOnOff) {
                                $timeout(function () {
                                    audiotoplay.get(0).play();
                                });
                            }
                        }
                    });
                }
            }
            /*
             * If this option is true on confing, it will automatic click when mouse is over the div and the timeout ends.
             */
            $scope.TimeoutOverClick = function (type, object)
            {
                if ($scope.inScan) {
                    return false;
                }
                //This timeout.cancel avoid possible multiple calls.
                $timeout.cancel($scope.OverAutoClick);
                //Check the element over we are
                if (type === 0)
                {
                    $scope.OverAutoClick = $timeout(function () {
                        $scope.clickOnCell(object);
                    }, $scope.cfgTimeOver);
                } else if (type === 1)
                {
                    $scope.OverAutoClick = $timeout(function () {
                        $scope.addToSentence(object.id, object.imgPicto, object.pictotext);
                    }, $scope.cfgTimeOver);
                } else if (type === 2)
                {
                    if (object === 'home')
                    {
                        $scope.OverAutoClick = $timeout(function () {
                            $scope.goPrimaryBoard();
                        }, $scope.cfgTimeOver);
                    } else if (object === 'generate')
                    {
                        $scope.OverAutoClick = $timeout(function () {
                            $scope.generate();
                        }, $scope.cfgTimeOver);
                    } else if (object === 'deleteLast')
                    {
                        $scope.OverAutoClick = $timeout(function () {
                            $scope.deleteLast();
                        }, $scope.cfgTimeOver);
                    } else if (object === 'deleteAll')
                    {
                        $scope.OverAutoClick = $timeout(function () {
                            $scope.deleteAll();
                        }, $scope.cfgTimeOver);
                    }
                } else if (type === 3)
                {
                    if (object === 'Good')
                    {
                        $scope.OverAutoClick = $timeout(function () {
                            $scope.feedback(1);
                        }, $scope.cfgTimeOver);
                    } else if (object === 'Bad')
                    {
                        $scope.OverAutoClick = $timeout(function () {
                            $scope.feedback(0);
                        }, $scope.cfgTimeOver);
                    }
                }
            };
            // Cancel the timer that control the lapsus time click
            $scope.CancelTimeoutOverClick = function ()
            {
                if ($scope.inScan) {
                    return false;
                }
                $timeout.cancel($scope.OverAutoClick);
            };

            /*
             * Add the selected pictogram to the sentence
             */
            $scope.addToSentence = function (id, img, text) {

                if ($scope.TimeMultiClic === 0)
                {

                    if ($scope.cfgTimeNoRepeatedClickOnOff === 1)
                    {
                        $scope.TimeMultiClic = 1;
                    }

                    var url = $scope.baseurl + "Board/addWord";
                    var postdata = {id: id, imgtemp: img};

                    $http.post(url, postdata).success(function (response)
                    {
                        $scope.dataTemp = response.data;
                        $scope.info = "";

                        $scope.readText(text, true);
                        $scope.getPred();
                    });
                }
                //MODIF: comentario op por parte de jordi
                if ($scope.cfgTimeNoRepeatedClickOnOff === 1)
                {
                    $scope.cfgTimeNoRepeatedClickOnOff = 2;
                    $scope.TimeoutMultiClic = $timeout(function () {
                        $scope.cfgTimeNoRepeatedClickOnOff = 1;
                        $scope.TimeMultiClic = 0;
                    }, $scope.cfgTimeNoRepeatedClick);
                }
            };
            //Check if autoReturn is '1'. If so, return to the primary board
            $scope.autoReturn = function () {
                var url = $scope.baseurl + "Board/autoReturn";
                var postdata = {id: $scope.idboard};

                $http.post(url, postdata).success(function (response)
                {

                    if (response.idPrimaryBoard !== null) {
                        $scope.showBoard(response.idPrimaryBoard);
                    }
                });
            };
            //Check if autoReadSentence is '1'. If so, generate the sentence
            $scope.automaticRead = function () {
                var url = $scope.baseurl + "Board/autoReadSentence";
                var postdata = {id: $scope.idboard};

                $http.post(url, postdata).success(function (response)
                {
                    if (response.read == '1') {
                        $scope.generate();
                    }
                });
            };
            /*
             * If you click in a function (not a pictogram) this controller carries you
             * to the specific function
             */
            $scope.clickOnFunction = function (id, text, readed) {
                var url = $scope.baseurl + "Board/getFunction";
                var postdata = {id: id, tense: $scope.tense, tipusfrase: $scope.tipusfrase, negativa: $scope.negativa};

                $http.post(url, postdata).success(function (response)
                {
                    var control = response.control;

                    $scope.dataTemp = response.data;
                    $scope.tense = response.tense;
                    $scope.tipusfrase = response.tipusfrase;
                    $scope.negativa = response.negativa;
                    if ((control !== "") && (control !== "home") && (control !== "historic")) {
                        var url = $scope.baseurl + "Board/" + control;
                        var postdata = {tense: $scope.tense, tipusfrase: $scope.tipusfrase, negativa: $scope.negativa};

                        $http.post(url, postdata).success(function (response)
                        {
                            $scope.info = response.info;
                            if (control !== "generate") {
                                $scope.dataTemp = response.data;
                                if (control === "deleteAllWords") {
                                    $scope.tense = "defecte";
                                    $scope.tipusfrase = "defecte";
                                    $scope.negativa = false;
                                    $scope.getPred();
                                } else if (control === "deleteLastWord") {
                                    $scope.getPred();
                                }
                                if (!readed) {
                                    $scope.readText(text, true);
                                }
                            } else {
                                $scope.tense = "defecte";
                                $scope.tipusfrase = "defecte";
                                $scope.negativa = false

                                $scope.readText($scope.info.frasefinal, false);
                            }
                        });
                    } else if ((control === "home")) {
                        $scope.config();
                    } else if ((control === "historic")) {
                        $rootScope.senteceFolderToShow = {folder: null, boardID: $scope.idboard};
                        $location.path('/historic');
                    } else {
                        if (!readed) {
                            $scope.readText(text, true);
                        }
                    }
                });
            };
            /*
             * Remove last word added to the sentence
             */
            $scope.deleteLast = function () {

                var url = $scope.baseurl + "Board/deleteLastWord";

                $http.post(url).success(function (response)
                {
                    $scope.dataTemp = response.data;
                    $scope.getPred();
                });
            };
            /*
             * Remove the whole sentence
             */
            $scope.deleteAll = function () {

                var url = $scope.baseurl + "Board/deleteAllWords";

                $http.post(url).success(function (response)
                {

                    $scope.tense = "defecte";
                    $scope.tipusfrase = "defecte";
                    $scope.negativa = false;
                    $scope.dataTemp = response.data;
                    $scope.info = "";
                    $scope.getPred();
                });
            };

            $scope.goPrimaryBoard = function () {
                $scope.config();
            };
            /*
             * Generate the current senence under contruction.
             * Add the pictograms (and the sentence itself) in the historic
             */

            $scope.generate = function () {
                var url = $scope.baseurl + "Board/generate";
                var postdata = {tense: $scope.tense, tipusfrase: $scope.tipusfrase, negativa: $scope.negativa};
                $http.post(url, postdata).success(function (response)
                {
                    //$scope.dataTemp = response.data;
                    $scope.info = response.info;
                    $scope.info.errormessage = response.errorText;
                    //$scope.data = response.data;

                    if ($scope.cfgUserExpansionFeedback) {
                        $scope.puntuar();
                    }

                    $scope.readText($scope.info.frasefinal, false);
                    $scope.getPred();
                });
                if ($scope.cfgAutoEraseSentenceBar) {
                    $scope.tense = "defecte";
                    $scope.tipusfrase = "defecte";
                    $scope.negativa = false;
                }
            };
            //Show the feedback panel, scanning it if inScan is true
            $scope.puntuar = function () {
                if (!$scope.inEdit) {
                    $scope.puntuando = true;
                    if ($scope.inScan) {
                        $scope.isScanning = "goodPhrase";
                    }
                }

            };
            //Send the user feedback to the php controller and remove the score panel
            $scope.feedback = function (point) {

                var url = $scope.baseurl + "Board/score";
                var postdata = {score: point};
                $http.post(url, postdata).success(function (response)
                {

                });
                $scope.puntuando = false;
                //If needed, restart scan
                if ($scope.inScan) {
                    $scope.InitScan();
                }


            };

            $scope.playSentenceAudio = function () //MODIF esto se usa?
            {
                var postdata = {voice: 0, sentence: $scope.info.frasefinal};//MODIF: canviar ek voice per cfg
                var URL = $scope.baseurl + "Board/getAudioSentence";

                $http.post(URL, postdata).
                        success(function (response)
                        {
                            $scope.dataAudio = response.data;

                            if ($scope.dataAudio[1]) {
                                txtContent("errorVoices").then(function (content) {
                                    $scope.errorMessage = content.data[$scope.dataAudio[3]];
                                    $scope.errorCode = $scope.dataAudio[3];
                                    $('#errorVoicesModal').modal({backdrop: 'static'});
                                });
                            } else {
                                $scope.sound = "mp3/" + $scope.dataAudio[0];
                                $timeout(function () {
                                    $('#utterance').get(0).play();
                                });
                            }

                        });
            };
            $scope.playPictoAudio = function (text) //esto se usa?
            {
                var postdata = {voice: 0, sentence: text};//MODIF: canviar ek voice per cfg
                var URL = $scope.baseurl + "Board/getAudioSentence";

                $http.post(URL, postdata).
                        success(function (response)
                        {
                            $scope.dataAudio = response.data;

                            if ($scope.dataAudio[1]) {
                                txtContent("errorVoices").then(function (content) {
                                    $scope.errorMessage = content.data[$scope.dataAudio[3]];
                                    $scope.errorCode = $scope.dataAudio[3];
                                    $('#errorVoicesModal').modal({backdrop: 'static'});
                                });
                            } else {
                                $scope.sound = "mp3/" + $scope.dataAudio[0];
                                $timeout(function () {
                                    $('#utterance').get(0).play();
                                });
                            }

                        });
            };
            /*
             * Return pictograms from database. The result depends on 
             * Searchtype (noms, verbs...) and Name (letters with the word start with)
             */
            $scope.searchDone = function (name, Searchtype)
            {

                var URL = "";
                var postdata = {id: name};
                //Radio button function parameter, to set search type
                switch (Searchtype)
                {
                    case "Tots":
                        URL = $scope.baseurl + "SearchWord/getDBAll";
                        break;
                    case "Noms":
                        URL = $scope.baseurl + "SearchWord/getDBNames";
                        break;
                    case "Verb":
                        URL = $scope.baseurl + "SearchWord/getDBVerbs";
                        break;
                    case "Adj":
                        URL = $scope.baseurl + "SearchWord/getDBAdj";
                        break;
                    case "Exp":
                        URL = $scope.baseurl + "SearchWord/getDBExprs";
                        break;
                    case "Altres":
                        URL = $scope.baseurl + "SearchWord/getDBOthers";
                        break;
                    default:
                        URL = $scope.baseurl + "SearchWord/getDBAll";
                }
                //Request via post to controller search data from database
                $http.post(URL, postdata).
                        success(function (response)
                        {
                            $scope.dataWord = response.data;
                        });
            };
            $scope.search = function (name, Searchtype)
            {
                $timeout.cancel($scope.searchTimeout);
                $scope.searchTimeout = $timeout(function () {
                    $scope.searchDone(name, Searchtype);
                }, 500);
            };
            /*
             * Return uploaded images from database. There are two types, the users images an the arasaac (not user images)
             */
            $scope.searchImg = function (name, typeImgEditSearch) {
                var URL = "";
                switch (typeImgEditSearch)
                {
                    case "Arasaac":
                        URL = $scope.baseurl + "ImgUploader/getImagesArasaac";
                        break;
                    case "Uploads":
                        URL = $scope.baseurl + "ImgUploader/getImagesUploads";
                        break;
                }
                var postdata = {name: name};
                $http.post(URL, postdata).
                        success(function (response)
                        {
                            $scope.imgData = response.data;
                        });
            }

            //get all the photos attached to the pictos
            $scope.searchFoto = function (name)
            {
                var URL = $scope.baseurl + "SearchWord/getDBAll";
                var postdata = {id: name};
                //Request via post to controller search data from database
                $http.post(URL, postdata).
                        success(function (response)
                        {
                            $scope.allImg = response.data;
                        });
            };
            // Upload and resize the image
            $scope.uploadFile = function () {
                $scope.myFile = document.getElementById('file-input').files;
                $scope.uploading = true;
                var i;
                var uploadUrl = $scope.baseurl + "ImgUploader/upload";

                var fd = new FormData();
                fd.append('vocabulary', angular.toJson(false));
                for (i = 0; i < $scope.myFile.length; i++) {
                    fd.append('file' + i, $scope.myFile[i]);
                }
                $http.post(uploadUrl, fd, {
                    headers: {'Content-Type': undefined}
                })
                        .success(function (response) {
                            $scope.uploading = false;
                            if (response.error) {
                                //open modal
                                console.log(response.errorText);
                                $scope.errorText = response.errorText;
                                $('#errorImgModal').modal({backdrop: 'static'});
                            }
                        });
            };


            /*
             * PosInBoard is the element over we drop the "draggable data". Data contains the info we drag 
             */
            $scope.onDropSwap = function (posInBoard, data, evt) {
                var URL = "";
                //Significa que no hay que hacer swap, solo cambiar la imagen
                if (data.imgPath) {
                    var postdata = {pos: posInBoard, idboard: $scope.idboard, imgCell: data.imgPath};
                    URL = $scope.baseurl + "Board/changeImgCell";
                }//Significa que no hay que hacer swap, solo medio swap...
                else if (data.idpicto) {
                    URL = $scope.baseurl + "Board/addPicto";
                    var postdata = {id: data.idpicto, pos: posInBoard, idboard: $scope.idboard};
                } else {
                    var postdata = {pos1: data.posInBoardPicto, pos2: posInBoard, idboard: $scope.idboard};
                    URL = $scope.baseurl + "Board/swapPicto";
                }

                $http.post(URL, postdata).
                        success(function (response)
                        {
                            $scope.statusWord = response.status;
                            $scope.data = response.data;
                        });
            };
            $scope.removePicto = function (data) {
                var postdata = {pos: data.posInBoard, idboard: $scope.idboard};
                var URL = $scope.baseurl + "Board/removePicto";
                $http.post(URL, postdata).
                        success(function (response)
                        {
                            $scope.statusWord = response.status;
                            $scope.data = response.data;
                        });
            };
            $scope.onDropRemove = function (data, evt) {
                var postdata = {pos: data.posInBoardPicto, idboard: $scope.idboard};
                var URL = $scope.baseurl + "Board/removePicto";
                $http.post(URL, postdata).
                        success(function (response)
                        {
                            $scope.statusWord = response.status;
                            $scope.data = response.data;
                        });
            };



            /***************************************************
             *
             *  editFolders functions
             *  
             ***************************************************/
            $scope.CreateBoard = function () {
                var postdata = {id: $scope.idboard};
                var URL = $scope.baseurl + "Board/getIDGroupBoards";

                $http.post(URL, postdata).success(function (response)
                {
                    $scope.idGroupBoard = response.idGroupBoard;
                    var URL = $scope.baseurl + "PanelGroup/getPanelGroupInfo";
                    //alert($scope.idGroupBoard);
                    var postdata = {idGroupBoard: $scope.idGroupBoard};
                    $http.post(URL, postdata).
                            success(function (response)
                            {

                                $scope.CreateBoardData = {CreateBoardName: '', height: response.defHeight.toString(), width: response.defWidth.toString(), idGroupBoard: response.ID_GB};
                                $scope.CreateBoardData.height = $scope.range(10)[response.defHeight - 1].valueOf();
                                $scope.CreateBoardData.width = $scope.range(10)[response.defWidth - 1].valueOf();

                                $('#ConfirmCreateBoard').modal({backdrop: 'static'});
                            });
                });

            };
            $scope.confirmCreateBoard = function () {
                URL = $scope.baseurl + "Board/newBoard";
                $http.post(URL, $scope.CreateBoardData).success(function (response)
                {
                    $scope.showBoard(response.idBoard);
                    $scope.edit();
                });
            };
            $scope.RemoveBoard = function () {
                $scope.RemoveBoardData = {BoardName: $scope.evt.nameboard};
                $('#ConfirmRemoveBoard').modal({backdrop: 'static'});
            };
            $scope.ConfirmRemoveBoard = function () {
                var postdata = {id: $scope.idboard};
                var URL = $scope.baseurl + "Board/removeBoard";
                $http.post(URL, postdata).success(function (response)
                {
                    if (response.idboard != null)
                    {
                        $scope.showBoard(response.idboard);
                        $scope.edit();
                    } else {
                        $location.path('/panelGroups');
                    }
                });
            };

            $scope.copyBoard = function () {

                var postdata = {id: $scope.idboard};
                var URL = $scope.baseurl + "Board/getIDGroupBoards";

                $http.post(URL, postdata).success(function (response)
                {
                    $scope.idGroupBoard = response.idGroupBoard;
                    var URL = $scope.baseurl + "PanelGroup/getUserPanelGroups";

                    $http.post(URL).
                            success(function (response)
                            {
                                $scope.panels = response.panels;
                                $scope.CopyBoardData = {CreateBoardName: "", CopiedBoardName: $scope.evt.nameboard, idGroupBoard: {ID_GB: $scope.idGroupBoard.toString()}, id: $scope.idboard, panels: $scope.panels, height: $scope.evt.altura, width: $scope.evt.amplada, autoreturn: $scope.evt.autoreturn, autoread: $scope.evt.autoread, srcGroupBoard: $scope.idGroupBoard.toString()};
                                $('#ConfirmCopyBoard').modal({backdrop: 'static'});
                            });
                });
            };
            $scope.ConfirmCopyBoard = function () {
                URL = $scope.baseurl + "Board/copyBoard";
                $scope.CopyBoardData.idGroupBoard = parseInt($scope.CopyBoardData.idGroupBoard.ID_GB.toString());

                $http.post(URL, $scope.CopyBoardData).success(function (response)
                {
                    $scope.idGroupBoard = $scope.CopyBoardData.idGroupBoard.ID_GB;
                    $scope.showBoard(response.idBoard);
                    $scope.edit();
                });
            };
            $scope.startPainting = function () {
                $scope.fv.painting = true;

            };
            $scope.stopPainting = function () {
                $scope.fv.painting = false;
            };
            /*
             * Open edit cell dialog and asign the controller
             */
            $scope.openEditCellMenu = function (id) {
                if ($scope.inEdit) {
                    $scope.idEditCell = id;
                    ngDialog.open({
                        template: $scope.baseurl + '/angular_templates/EditCellView.html',
                        className: 'ngdialog-theme-default dialogEdit',
                        scope: $scope,
                        controller: 'Edit',
                        showClose: false
                    });
                }
            };
            
            $scope.style_changes_title = '';
            
             // Activate information modals (popups)
            $scope.toggleInfoModal = function (title, text) {
                $scope.infoModalContent = text;
                $scope.infoModalTitle = title;
                $scope.style_changes_title = 'padding-top: 2vh;';
                $('#infoModal').modal('toggle');
            };
        })

        // Edit controller 
        .controller('Edit', function ($scope, $http, ngDialog, $timeout) {
            // Get the cell clicked (the cell in the cicked position in the current board
            var url = $scope.baseurl + "Board/getCell";
            var postdata = {pos: $scope.idEditCell, idboard: $scope.idboard};

            $http.post(url, postdata).success(function (response)
            {
                $scope.Editinfo = response.info;
                var idCell = response.info.ID_RCell;

                $scope.changeCellType = function () {
                    if ($scope.cellType == "sentence" || $scope.cellType == "sFolder") {
                        $scope.checkboxFuncType = false;
                        $scope.checkboxBoardsGroup = false;
                    }
                };
                $scope.removeFile = function () {
                    $scope.uploadedFile = null;
                };
                // Gets functions from database and shows them the dropmenu
                $scope.getFunctions = function () {
                    var url = $scope.baseurl + "Board/getFunctions";
                    $http.post(url).success(function (response)
                    {
                        $scope.functions = response.functions;
                        //Inicializa el dropdown menu
                        $scope.funcType = {ID_Function: $scope.Editinfo.ID_CFunction};
                        if ($scope.Editinfo.ID_CFunction !== null) {
                            $scope.checkboxFuncType = true;
                        }
                    });
                };
                // Gets all boards in the same group and shows them the dropmenu
                $scope.getBoards = function () {
                    var url = $scope.baseurl + "Board/getBoards";
                    var postdata = {idboard: $scope.idboard};

                    $http.post(url, postdata).success(function (response)
                    {
                        $scope.boards = response.boards;
                        $scope.boardsGroup = {ID_Board: $scope.Editinfo.boardLink};
                        if ($scope.Editinfo.boardLink !== null) {
                            $scope.checkboxBoardsGroup = true;
                        }
                    });
                };
                // Gets the sentence asigned (if there is any) to the cell and show it to the user
                $scope.getSentence = function (id) {
                    var url = $scope.baseurl + "Board/getSentence";
                    var postdata = {id: id};

                    $http.post(url, postdata).success(function (response)
                    {
                        $scope.sentenceSelectedId = response.sentence.ID_SSentence;
                        $scope.sentenceSelectedText = response.sentence.generatorString;
                    });
                };
                // Gets all pre-record sentences from database and shows it the dropmenu
                $scope.searchSentece = function (sentence) {
                    var postdata = {search: sentence};
                    var URL = $scope.baseurl + "Board/searchSentence";

                    $http.post(URL, postdata).
                            success(function (response)
                            {
                                $scope.sentenceResult = response.sentence;
                            });
                };
                // Asigns the selected sentence to the cell (provisionally) and show it to the user
                $scope.selectSentence = function (id, text) {
                    $scope.sentenceSelectedId = id;
                    $scope.sentenceSelectedText = text;
                };
                // Gets the sentence folder asigned (if there is any) to the cell and shows it to the user
                $scope.getSFolder = function (id) {
                    var url = $scope.baseurl + "Board/getSFolder";
                    var postdata = {id: id};

                    $http.post(url, postdata).success(function (response)
                    {
                        $scope.sFolderSelectedId = response.sFolder.ID_Folder;
                        $scope.sFolderSelectedImg = response.sFolder.imgSFolder;
                        $scope.sFolderSelectedText = response.sFolder.folderName;
                    });
                };
                // Gets all sentence folders from database and shows it the dropmenu
                $scope.searchSFolder = function (sFolder) {
                    var postdata = {search: sFolder};
                    var URL = $scope.baseurl + "Board/searchSFolder";

                    $http.post(URL, postdata).
                            success(function (response)
                            {
                                $scope.sFolderResult = response.sfolder;
                            });
                };
                // Asigns the selected sentence folder to the cell (provisionally) and show it to the user
                $scope.selectSFolder = function (id, img, text) {
                    $scope.sFolderSelectedId = id;
                    $scope.sFolderSelectedImg = img;
                    $scope.sFolderSelectedText = text;
                };
                
                // Closes the editCell dialog
                $scope.closeDialog = function() {
                    ngDialog.close();
                };
                
                //Initialize the dropdwon menus and all the variables that will be shown to the user
                $scope.getFunctions();
                $scope.getBoards();
                $scope.colorSelected = $scope.Editinfo.color;
                $scope.cellType = $scope.Editinfo.cellType;
                $scope.numScanBlockText1 = $scope.range(10)[$scope.Editinfo.customScanBlock1 - 1];
                $scope.textInScanBlockText1 = $scope.Editinfo.customScanBlockText1;
                $scope.numScanBlockText2 = $scope.range(10)[$scope.Editinfo.customScanBlock2 - 1];
                $scope.textInScanBlockText2 = $scope.Editinfo.customScanBlockText2;
                $scope.idPictoEdit = $scope.Editinfo.ID_CPicto;
                $scope.imgPictoEdit = $scope.Editinfo.imgPicto;
                $scope.uploadedFile = $scope.Editinfo.imgCell;
                $scope.imgFunct = $scope.Editinfo.imgFunct;
                // Check the values in order to active checkbox and this stuff
                if ($scope.Editinfo.textInCell !== null) {
                    $scope.checkboxTextInCell = true;
                    $scope.textInCell = $scope.Editinfo.textInCell;
                }
                if ($scope.Editinfo.activeCell === "1") {
                    $scope.checkboxVisible = true;
                }
                if ($scope.Editinfo.isFixedInGroupBoards === "1") {
                    $scope.checkboxIsFixed = true;
                }
                if ($scope.Editinfo.customScanBlockText1 !== "") {
                    $scope.checkboxScanBlockText1 = true;
                }
                if ($scope.Editinfo.customScanBlock2 !== null) {
                    $scope.checkboxScanBlockText2 = true;
                }
                if (response.info.cellType === 'sentence') {
                    $scope.getSentence(response.info.ID_CSentence);
                }
                if (response.info.cellType === 'sfolder') {
                    $scope.getSFolder(response.info.sentenceFolder);
                }
                // Save all the provisionally data asigned to the cell
                $scope.savedata = function () {
                    var url = $scope.baseurl + "Board/editCell";
                    var postdata = {id: idCell, idPicto: $scope.idPictoEdit, idSentence: $scope.sentenceSelectedId, idSFolder: $scope.sFolderSelectedId, boardLink: $scope.boardsGroup.ID_Board, idFunct: $scope.funcType.ID_Function, textInCell: $scope.textInCell, visible: "1", isFixed: "1", numScanBlockText1: $scope.numScanBlockText1, textInScanBlockText1: $scope.textInScanBlockText1, numScanBlockText2: $scope.numScanBlockText2, textInScanBlockText2: $scope.textInScanBlockText2, cellType: $scope.cellType, color: $scope.colorSelected, imgCell: $scope.uploadedFile};
                    // Check another time null values and config the data that will be save in the data base
                    if (!$scope.checkboxFuncType) {
                        postdata.idFunct = null;
                    }
                    if (!$scope.checkboxBoardsGroup) {
                        postdata.boardLink = null;
                    }
                    if (!$scope.checkboxTextInCell) {
                        postdata.textInCell = null;
                    }
                    if (!$scope.checkboxVisible) {
                        postdata.visible = "0";
                    }
                    if (!$scope.checkboxIsFixed) {
                        postdata.isFixed = "0";
                    }
                    if (!$scope.checkboxScanBlockText1) {
                        postdata.numScanBlockText1 = "1";
                        postdata.textInScanBlockText1 = null;
                    }
                    if (!$scope.checkboxScanBlockText2) {
                        postdata.numScanBlockText2 = null;
                        postdata.textInScanBlockText2 = null;
                    }
                    if ($scope.cellType !== 'picto') {
                        postdata.idPicto = null;
                    }
                    if ($scope.cellType !== 'sentence') {
                        postdata.idSentence = null;
                    }
                    if ($scope.cellType !== 'sfolder') {
                        postdata.idSFolder = null;
                    }
                    if ($scope.uploadedFile === null) {
                        var postdataimg = {pos: response.info.posInBoard, idboard: response.info.ID_RBoard, imgCell: null};
                        var URLimg = $scope.baseurl + "Board/changeImgCell";
                        $http.post(URLimg, postdataimg);
                    }

                    $http.post(url, postdata).success(function ()
                    {
                        $scope.showBoard("0");
                        ngDialog.close();
                    });
                };
            }
            );
        })

        .controller('menuCtrl', function ($scope, $http, ngDialog, txtContent, $rootScope, AuthService, $location) {
            $scope.userConfig = function () {
                $location.path('/userConfig');
            };

            $scope.panelMenu = function () {
                $location.path('/panelGroups');
            };

            $scope.editMenu = function () {
                $scope.$emit("EditCallFromMenu", {});
            };
            // Función salir del login
            $scope.logOut = function () {
                ngDialog.openConfirm({
                    template: $scope.baseurl + '/angular_templates/ConfirmLogout.html',
                    scope: $scope,
                    className: 'ngdialog-theme-default dialogLogOut'
                }).then(function () {
                    AuthService.logout();
                });

            };


            $scope.home = function () {
                if ($location.path() == '/') {
                    $scope.config();
                } else {
                    $location.path('/');
                }

            };

            $scope.IniciScan = function () {
                $scope.$emit("ScanCallFromMenu", {});
            };
        })

        .controller('historicCtrl', function ($scope, $rootScope, txtContent, $location, $http, dropdownMenuBarInit, AuthService, Resources, $timeout, $interval) {
            // Comprobación del login   IMPORTANTE!!! PONER EN TODOS LOS CONTROLADORES
            if (!$rootScope.isLogged) {
                $rootScope.dropdownMenuBarValue = '/home'; //Dropdown bar button selected on this view
                $location.path('/home');
            }
            // Pedimos los textos para cargar la pagina
            txtContent("historicview").then(function (results) {
                $scope.content = results.data;
            });

            //Dropdown Menu Bar
            $rootScope.dropdownMenuBar = null;
            $rootScope.dropdownMenuBarValue = '/'; //Button selected on this view
            $rootScope.dropdownMenuBarButtonHide = true;
            $rootScope.dropdownMenuBarChangeLanguage = false;//Languages button available
            //SentenceBar button to open dropdown menu bar when hover
            $("#idSentenceBar").hover(function () {
                console.log('hover');
                $scope.dropdownMenuOpen = true;
            });
            //Choose the buttons to show on bar
            dropdownMenuBarInit($rootScope.interfaceLanguageId)
                    .then(function () {
                        //Choose the buttons to show on bar
                        angular.forEach($rootScope.dropdownMenuBar, function (value) {
                            if (value.href == '/' || value.href == 'editPanel' || value.href == '/panelGroups' || value.href == '/userConfig' || value.href == '/faq' || value.href == '/tips' || value.href == '/privacy' || value.href == 'logout') {
                                value.show = true;
                            } else {
                                value.show = false;
                            }
                        });
                    });
            //function to change html view
            $scope.go = function (path) {
                if (path == '/') {
                    $scope.config();
                } else if (path == 'logout') {
                    $('#logoutModal').modal('toggle');
                } else if (path == 'editPanel') {
                    $scope.edit();
                } else {
                    $location.path(path);
                    $rootScope.dropdownMenuBarValue = path; //Button selected on this view
                }
            };

            //Log Out Modal
            $scope.img = [];
            $scope.img.lowSorpresaFlecha = '/img/srcWeb/Mus/lowSorpresaFlecha.png';
            $scope.img.Patterns1_08 = '/img/srcWeb/patterns/pattern3.png';
            Resources.main.get({'section': 'logoutModal', 'idLanguage': $rootScope.interfaceLanguageId}, {'funct': "content"}).$promise
                    .then(function (results) {
                        $scope.logoutContent = results.data;
                    });
            $scope.logout = function () {
                $scope.viewActived = false;
                $timeout(function () {
                    AuthService.logout();
                }, 1000);
            };

            $scope.back = function () {
                $rootScope.boardToShow = $scope.backBoard;
                $location.path('/');
            };

            $scope.home = function () {
                $location.path('/');
            };

            $scope.getTodayHistoric = function () {
                $scope.folder = null;
                $scope.timeHis = "today";
                $scope.pagHistoric = 0;
                $scope.getHistoric();
            };

            $scope.getLastWeekHistoric = function () {
                $scope.folder = null;
                $scope.timeHis = "lastWeek";
                $scope.pagHistoric = 0;
                $scope.getHistoric();
            };

            $scope.getLastMonthHistoric = function () {
                $scope.folder = null;
                $scope.timeHis = "lastMonth";
                $scope.pagHistoric = 0;
                $scope.getHistoric();

            };

            $scope.getHistoric = function () {
                var day = 1;
                switch ($scope.timeHis) {
                    case "today":
                        day = 1;
                        break;
                    case "lastWeek":
                        day = 7;
                        break;
                    case "lastMonth":
                        day = 31;
                        break;
                }
                var postdata = {day: day, pagHistoric: $scope.pagHistoric};
                var url = $scope.baseurl + "historic/getHistoric";

                $http.post(url, postdata).
                        success(function (response)
                        {
                            $scope.historic = response.historic;
                            while ($scope.historic.length < 10) {
                                $scope.historic.push(null);
                            }
                            if ($scope.pagHistoric == 0) {
                                $scope.pagBackHistoricEnabled = false;
                            } else {
                                $scope.pagBackHistoricEnabled = true;
                            }
                            if ($scope.pagHistoric + 10 > response.count) {
                                $scope.pagNextHistoricEnabled = false;
                            } else {
                                $scope.pagNextHistoricEnabled = true;
                            }
                        });
            };

            $scope.previousPagHistoric = function () {
                if ($scope.pagBackHistoricEnabled) {
                    $scope.pagHistoric -= 10;
                    $scope.getHistoric();
                }
            };

            $scope.nextPagHistoric = function () {
                if ($scope.pagNextHistoricEnabled) {
                    $scope.pagHistoric += 10;
                    $scope.getHistoric();
                }
            };

            $scope.changeFolder = function (id) {
                $scope.timeHis = null;
                $scope.folder = id;
                $scope.getFolder();
            };

            $scope.previousPagSFolder = function () {
                if ($scope.pagBackFolderEnabled) {
                    $scope.pagSFolder -= 4;
                    $scope.pagNextFolderEnabled = true;
                    if ($scope.pagSFolder == 0) {
                        $scope.pagBackFolderEnabled = false;
                    } else {
                        $scope.pagBackFolderEnabled = true;
                    }
                }
            };

            $scope.nextPagSFolder = function () {
                if ($scope.pagNextFolderEnabled) {
                    $scope.pagSFolder += 4;
                    $scope.pagBackFolderEnabled = true;
                    if ($scope.sFolderResult.length < $scope.pagSFolder + 4) {
                        $scope.pagNextFolderEnabled = false;
                    } else {
                        $scope.pagNextFolderEnabled = true;
                    }
                }
            };

            $scope.getSFolders = function () {
                var URL = $scope.baseurl + "historic/getSFolder";

                $http.post(URL).
                        success(function (response)
                        {
                            $scope.sFolderResult = response.sFolder;
                            if ($scope.pagSFolder == 0) {
                                $scope.pagBackFolderEnabled = false;
                            }
                            if ($scope.sFolderResult == null) {
                                $scope.pagNextFolderEnabled = false;
                            }
                            if ($scope.sFolderResult.length < $scope.pagSFolder + 4) {
                                $scope.pagBackFolderEnabled = false;
                            }
                        });
            };

            $scope.getFolder = function () {
                var postdata = {folder: $scope.folder, pagHistoric: $scope.pagHistoric};
                var url = $scope.baseurl + "historic/getFolder";

                $http.post(url, postdata).
                        success(function (response)
                        {
                            $scope.historic = response.historic;
                            while ($scope.historic.length < 10) {
                                $scope.historic.push(null);
                            }
                            if ($scope.pagHistoric == 0) {
                                $scope.pagBackHistoricEnabled = false;
                            }
                            if ($scope.pagHistoric + 10 > response.count) {
                                $scope.pagNextHistoricEnabled = false;
                            }
                        });
            };
            //Bool is true when the text comes from interface and false if the text is the sentence
            $scope.readText = function (text, bool) {
                if (text !== "") {
                    $scope.sound = "mp3/empty.m4a";
                    var postdata = {text: text, interface: bool};
                    var url = $scope.baseurl + "Board/readText";
                    $http.post(url, postdata).success(function (response) {
                        $scope.dataAudio = response.audio;
                        if ($scope.dataAudio[1]) {
                            if (false) {
                                txtContent("errorVoices").then(function (content) {
                                    $scope.errorMessage = content.data[$scope.dataAudio[3]];
                                    $scope.errorCode = $scope.dataAudio[3];
                                    $('#errorVoicesModal').modal({backdrop: 'static'});
                                });
                            }
                        } else {
                            $scope.sound = "mp3/" + $scope.dataAudio[0];
                            var audiotoplay = $('#utterance');
                            audiotoplay.src = "mp3/" + $scope.dataAudio[0];
                            if ($scope.cfgTimeOverOnOff) {
                                $timeout(function () {
                                    audiotoplay.get(0).play();
                                });
                            }
                        }
                    });
                }
            };

            $scope.clickOnSentence = function (text) {
                $scope.readText(text, false);
                $scope.initScan();
            };

            /*
             * SCAN
             */

            $scope.InitScan = function ()
            {

                $scope.inScan = true;
                //When the scan is automatic, this timer manage when the scan have to move to the next block            

                $scope.scanningFolder = -1;
                $scope.scanningSentence = -1;

                if ($scope.timerScan) {
                    $scope.setTimer();
                }

                if ($scope.cfgScanStartClick && $scope.isScanning != "nowait") {
                    $scope.isScanning = "waiting";
                } else {
                    $scope.isScanning = "1row";
                }




            };

            $scope.setTimer = function () {
                $interval.cancel($scope.intervalScan);
                var Intervalscan = $scope.cfgTimeScanning;
                function myTimer() {
                    if ($scope.isScanningCancel) {
                        //We are not scanning cancel anmore
                        $scope.isScanningCancel = false;
                    } else {
                        $scope.nextBlockScan();
                    }
                }
                ;
                $scope.intervalScan = $interval(myTimer, Intervalscan);
            };

            //Control the left click button while scanning
            $scope.scanLeftClick = function ()
            {
                if ($scope.inScan) {
                    //If user have start scan click activate we have to wait until he press one button
                    if ($scope.isScanning == "waiting") {
                        $scope.isScanning = "1row";
                        $scope.setTimer();
                    } else if ($scope.isScanningCancel) {
                        $scope.isScanningCancel = false;
                        $scope.isScanning = "nowait";
                        $scope.InitScan();
                    } else {
                        if (!$scope.longclick)
                        {
                            $scope.selectBlockScan();
                        }
                    }
                }
            };

            //Control the right click button while scanning
            $scope.scanRightClick = function ()
            {
                if ($scope.inScan) {
                    //If user have start scan click activate we have to wait until he press one button
                    if ($scope.isScanning == "waiting") {
                        $scope.isScanning = "1row";
                        $scope.setTimer();
                    }
                    if ($scope.isScanningCancel) {
                        $scope.isScanningCancel = false;
                        $scope.isScanning = "nowait";
                        $scope.InitScan();
                    } else {
                        if (!$scope.longclick && !$scope.timerScan)
                        {
                            $scope.nextBlockScan();
                        }
                    }
                }
            };
            //Control the long click button while scanning (to detect when it's a long one)
            $scope.playLongClick = function ()
            {
                var userConfig = JSON.parse(localStorage.getItem('userData'));
                if ($scope.inScan) {
                    if ($scope.longclick)
                    {
                        $timeout.cancel($scope.scanLongClickTime);
                        $scope.scanLongClickController = true;
                        $scope.scanLongClickTime = $timeout($scope.selectBlockScan, userConfig.cfgTimeClick);
                    }
                }
            };
            //Control the long click button while scanning (to detect when it's a short one)
            $scope.cancelLongClick = function ()
            {
                if ($scope.inScan) {
                    if ($scope.longclick)
                    {
                        if ($scope.scanLongClickController)
                        {
                            $timeout.cancel($scope.scanLongClickTime);
                            $scope.nextBlockScan();
                        } else
                        {

                        }
                    }
                }
            };

            $scope.nextFolderToScan = function () {
                $scope.scanningFolder = $scope.scanningFolder + 1;
                if ($scope.scanningFolder > $scope.sFolderResult.length || $scope.scanningFolder / 4 >= 1) {
                    $scope.isScanning = "nextPagFolder";
                    if (!$scope.pagNextFolderEnabled) {
                        $scope.nextBlockScan();
                    }
                }
            };
            $scope.getFolderToScan = function () {
                $scope.changeFolder($scope.sFolderResult[$scope.scanningFolder].ID_Folder);
            };
            $scope.nextSenteceToScan = function () {
                $scope.scanningSentence = $scope.scanningSentence + 2;
                if ($scope.scanningSentence >= 10 || $scope.historic[$scope.scanningSentence] == null) {
                    $scope.isScanning = "nowait";
                    $scope.InitScan();
                }
            };
            $scope.isScanned = function (indice) {
                if ($scope.scanningSentence == -1 && !$scope.isScanningCancel) {
                    if ($scope.isScanning == "1column" && indice % 2 == 0) {
                        return true;
                    } else if ($scope.isScanning == "2column" && indice % 2 == 1) {
                        return true;
                    }
                } else if (indice == $scope.scanningSentence && !$scope.isScanningCancel) {
                    return true;
                }
                return false;
            };
            $scope.getSenteceToScan = function () {
                $scope.readText($scope.historic[$scope.scanningSentence][0].generatorString || $scope.historic[$scope.scanningSentence][0].sPreRecText, false);
                $scope.InitScan();
            };
            // Change the current scan block
            $scope.nextBlockScan = function () {
                if ($scope.inScan) {
                    switch ($scope.isScanning) {
                        case "waiting":
                            break;
                        case "1row":
                            $scope.isScanning = "2row";
                            break;
                        case "2row":
                            $scope.isScanning = "1column";
                            break;
                        case "1column":
                            $scope.isScanning = "2column";
                            break;
                        case "2column":
                            $scope.isScanning = "nowait";
                            $scope.InitScan();
                            break;
                        case "back":
                            $scope.isScanning = "home";
                            break;
                        case "home":
                            $scope.isScanning = "today";
                            break;
                        case "today":
                            $scope.isScanning = "lastWeek";
                            break;
                        case "lastWeek":
                            $scope.isScanning = "lastMonth";
                            break;
                        case "lastMonth":
                            $scope.isScanning = "nowait";
                            $scope.InitScan();
                            break;
                        case "backPagHistoric":
                            $scope.isScanning = "nextPagHistoric";
                            if (!$scope.pagNextHistoricEnabled) {
                                $scope.nextBlockScan();
                            }
                            break;
                        case "nextPagHistoric":
                            $scope.isScanning = "backPagFolder";
                            if (!$scope.pagBackFolderEnabled) {
                                $scope.nextBlockScan();
                            }
                            break;
                        case "backPagFolder":
                            $scope.isScanning = "folders";
                            $scope.scanningFolder = $scope.pagSFolder;
                            break;
                        case "folders":
                            $scope.nextFolderToScan();
                            break;
                        case "nextPagFolder":
                            $scope.isScanning = "nowait";
                            $scope.InitScan();
                            break;
                        case "even":
                            $scope.nextSenteceToScan();
                            break;
                        case "odd":
                            $scope.nextSenteceToScan();
                            break;
                    }

                }
            };
            //Pass to the next scan level (subgroup)
            $scope.selectBlockScan = function () {
                if ($scope.inScan) {
                    //when we select a picto cancel the actual timer and set up another
                    if ($scope.timerScan) {
                        $scope.setTimer();
                    }
                    //cancel long click
                    if ($scope.longclick)
                    {
                        $scope.scanLongClickController = false;
                    }
                    switch ($scope.isScanning) {
                        case "waiting"://Do nothing
                            break;
                        case "1row":
                            $scope.isScanning = "back";
                            $scope.isScanningCancel = $scope.cfgCancelScanOnOff;
                            break;
                        case "2row":
                            $scope.isScanning = "backPagHistoric";
                            $scope.isScanningCancel = $scope.cfgCancelScanOnOff;
                            if (!$scope.pagBackHistoricEnabled) {
                                $scope.nextBlockScan();
                            }
                            break;
                        case "1column":
                            $scope.isScanningCancel = $scope.cfgCancelScanOnOff;
                            $scope.scanningSentence = 0;
                            $scope.isScanning = "even";
                            if ($scope.historic[$scope.scanningSentence] == null) {
                                $scope.InitScan();
                            }
                            break;
                        case "2column":
                            $scope.isScanningCancel = $scope.cfgCancelScanOnOff;
                            $scope.scanningSentence = 1;
                            $scope.isScanning = "odd";
                            if ($scope.historic[$scope.scanningSentence] == null) {
                                $scope.InitScan();
                            }
                            break;
                        case "back":
                            $scope.back();
                            break;
                        case "home":
                            $scope.home();
                            break;
                        case "today":
                            $scope.getTodayHistoric();
                            $scope.InitScan();
                            break;
                        case "lastWeek":
                            $scope.getLastWeekHistoric();
                            $scope.InitScan();
                            break;
                        case "lastMonth":
                            $scope.getLastMonthHistoric();
                            $scope.InitScan();
                            break;
                        case "backPagHistoric":
                            $scope.previousPagHistoric();
                            $scope.InitScan();
                            break;
                        case "nextPagHistoric":
                            $scope.nextPagHistoric();
                            $scope.InitScan();
                            break;
                        case "folders":
                            $scope.getFolderToScan();
                            $scope.InitScan();
                            break;
                        case "backPagFolder":
                            $scope.previousPagSFolder();
                            $scope.InitScan();
                            break;
                        case "nextPagFolder":
                            $scope.nextPagSFolder();
                            $scope.InitScan();
                            break;
                        case "even":
                            $scope.getSenteceToScan();
                            break;
                        case "odd":
                            $scope.getSenteceToScan();
                            break;
                    }
                }
            };
            var userConfig = JSON.parse(localStorage.getItem('userData'));
            $scope.cfgScanColor = userConfig.cfgScanColor;
            $scope.longclick = userConfig.cfgScanningAutoOnOff == 0 ? true : false;
            $scope.timerScan = userConfig.cfgScanningAutoOnOff == 1 ? true : false;
            $scope.cfgTimeScanning = userConfig.cfgTimeScanning;
            $scope.cfgTimeOverOnOff = userConfig.cfgTimeLapseSelectOnOff == 1 ? true : false;
            $scope.cfgTimeOver = userConfig.cfgTimeLapseSelect;
            $scope.cfgScanningOnOff = userConfig.cfgScanningOnOff;
            $scope.cfgScanStartClick = userConfig.cfgScanStartClick == 1 ? true : false;
            $scope.cfgCancelScanOnOff = userConfig.cfgCancelScanOnOff == 1 ? true : false;

            if (userConfig.cfgUsageMouseOneCTwoC == 0) {
                $scope.longclick = false;
                $scope.timerScan = false;
                $scope.cfgScanStartClick = false;
            } else if (userConfig.cfgUsageMouseOneCTwoC == 1) {
                if ($scope.longclick) {
                    $scope.cfgScanStartClick = false;
                    $scope.cfgCancelScanOnOff = false;
                }
                $scope.cfgTimeOverOnOff = false;
                $scope.cfgTimeNoRepeatedClickOnOff = false;
            } else if (userConfig.cfgUsageMouseOneCTwoC == 2) {
                $scope.longclick = false;
                $scope.timerScan = false;
                $scope.cfgTimeOverOnOff = false;
                $scope.cfgTimeNoRepeatedClickOnOff = false;
                $scope.cfgScanStartClick = false;
                $scope.cfgCancelScanOnOff = false;
            }


            if ($scope.cfgScanningOnOff == 1) {
                $scope.InitScan();
            }
            //Init the pag

            $scope.pagNextFolderEnabled = true;
            $scope.pagNextHistoricEnabled = true;
            $scope.pagBackFolderEnabled = true;
            $scope.pagBackHistoricEnabled = true;
            $scope.pagSFolder = 0;
            $scope.pagHistoric = 0;
            $scope.getSFolders();
            var userConfig = JSON.parse(localStorage.getItem('userData'));
            $scope.cfgBgColorPanel = userConfig.cfgBgColorPanel;
            $scope.cfgBgColorMenu = userConfig.cfgBgColorPred;
            if ($rootScope.senteceFolderToShow) {
                if ($rootScope.senteceFolderToShow.folder) {
                    $scope.changeFolder($rootScope.senteceFolderToShow.folder);
                    $scope.backBoard = $rootScope.senteceFolderToShow.boardID;
                    $rootScope.senteceFolderToShow = null;
                    //Lo dejo asi por que demomento lleva a today, pero se puede cambiar y poner mas
                } else {
                    $scope.timeHis = "today";
                    $scope.getHistoric();
                }
            } else {
                $scope.timeHis = "today";
                $scope.getHistoric();
            }

        })




//Add a directive in order to recognize the right click
        .directive('ngRightClick', function ($parse) {
            return function (scope, element, attrs) {
                var fn = $parse(attrs.ngRightClick);
                element.bind('contextmenu', function (event) {
                    scope.$apply(function () {
                        event.preventDefault();
                        fn(scope, {$event: event});
                    });
                });
            };
        })

        .directive('onFinishLoop', function ($timeout) {
            return {
                restrict: 'A',
                link: function (scope, element, attr) {
                    if (scope.$last === true) {
                        $timeout(function () {
                            scope.$emit(attr.onFinishLoop);
                        });
                    }
                }
            }
        });

