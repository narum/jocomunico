<html ng-app="app">
    <head>
        <title>Jocomunico</title>
        <link rel="icon" type="image/ico" href="img/icons/favicon.ico">
        <base href="/"></base>

        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
        <meta name="HandheldFriendly" content="true">
        <meta name="apple-mobile-web-app-capable" content="no"/>
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta http-equiv="Cache-control" content="no-transform">
        
        <!-- Android and iOS app link icons -->
        <link href="img/icons/apple-touch-icon.png" rel="apple-touch-icon" />
        <link href="img/icons/apple-touch-icon-57x57.png" rel="apple-touch-icon" sizes="57x57" />
        <link href="img/icons/apple-touch-icon-76x76.png" rel="apple-touch-icon" sizes="76x76" />
        <link href="img/icons/apple-touch-icon-120x120.png" rel="apple-touch-icon" sizes="120x120" />
        <link href="img/icons/apple-touch-icon-152x152.png" rel="apple-touch-icon" sizes="152x152" />
        <link href="img/icons/apple-touch-icon-167x167.png" rel="apple-touch-icon" sizes="167x167" />
        <link href="img/icons/apple-touch-icon-180x180.png" rel="apple-touch-icon" sizes="180x180" />
        <link href="img/icons/icon-hires.png" rel="icon" sizes="192x192" />
        <link href="img/icons/icon-normal.png" rel="icon" sizes="128x128" />
        
        <link rel="stylesheet" type="text/css" href="libraries/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="libraries/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="css/typeahead.css">
        <link rel="stylesheet" type="text/css" href="css/app.css">
        <!--MODIF: sacar de aqui y poner en el html. Primero sacar todo lo comun-->

        <link rel="stylesheet" type="text/css" href="css/generico.css">
        <link rel="stylesheet" type="text/css" href="/libraries/ngDialog.min.css"/>
        <link rel="stylesheet" type="text/css" href="/libraries/ngDialog-theme-default.min.css"/>


    </head>
    <body>
        <div ng-view class="root" ng-init="baseurl = '<?= base_url(); ?>'"></div>
        <script>
            var stringScript = '';
            stringScript += '<script type="text/javascript" src="libraries/jquery.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/angular.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/angular-route.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/angular-resource.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/angular-cookies.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/ui-bootstrap.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/ui-bootstrap-tpls.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/ng-scrollbar.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/bootstrap.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/app.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/controllers.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/services.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/captcha.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/panelController.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/addWordController.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/sentencesFolderController.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/infoController.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/consellsController.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/downloadController.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="angular_js/faqController.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/angular-bind-html-compile.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/ngTouch.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/ngDraggable.min.js"></' + 'script>';
            stringScript += '<script type="text/javascript" src="libraries/ngDialog.min.js"></' + 'script>';
            document.write(stringScript);
        </script>
    </body>
</html>

