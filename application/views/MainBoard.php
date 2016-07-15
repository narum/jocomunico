<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" ng-app="mySearch" style="max-height: 100vh;">


    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="Natural Language Processing: AAC application in Catalan" />
    <meta name="keywords" content="nlp, pln, processament, llenguatge, natural, natural, language, processing, aac, comunicació augmentativa
          i alternativa, comunicació, augmentativa, alternativa, augmentative and alternative communication, catalan, català, gratis, lliure,
          opensource, free, text generation, natural language, llenguatge natural, projecte, master, arasaac" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<!--<link href="<?= base_url(); ?>css/projecte.css" rel="stylesheet" type="text/css" />-->
    <script src="<?= base_url(); ?>js/nlp/scripts.js" type="text/javascript"></script>
    
    <link rel="stylesheet" href="<?= base_url(); ?>/css/ngDialog-theme-default.css"/>
    <link rel="stylesheet" href="<?= base_url(); ?>/css/ngDialog.css"/>
    <link rel="stylesheet" href="<?= base_url(); ?>/css/style-jc.css"/>
    <script src="<?= base_url(); ?>/bootstrap-3.3.6-dist/js/bootstrap.min.js"></script>
    <script src="<?= base_url(); ?>js/angular/angular.min.js" type="text/javascript"></script>
    <script src="<?= base_url(); ?>js/angular/angular-bind-html-compile.js" type="text/javascript"></script>
    <script src="<?= base_url(); ?>js/angular/angular-sanitize.min.js" type="text/javascript"></script>
    <script src="<?= base_url(); ?>js/angular/ngDraggable.js" type="text/javascript"></script>
    <script src="<?= base_url(); ?>js/angular/ngDialog.min.js" type="text/javascript"></script>
    <script src="<?= base_url(); ?>js/BoardScripts.js" type="text/javascript"></script>


    <title>Jo Comunico Beta</title>


    <body ng-controller="myCtrl" ng-init="baseurl = '<?= base_url(); ?>'" oncontextmenu="return false" ng-drop="true">
        <div id="mainboard">
            <div id="header" class="container-fluid text-center row">
                <div style="background-color: Green;height:100%;">
                    
                </div>
            </div>
            <div id="userview" class="container-fluid" ng-init="config(2)">
                <div class="row fullheight">
                    <div ng-hide="grid1hide" class="col-xs-{{grid1}} fullheight" style="background-color: Blue;">
                            <div id="board" class="fullheight">
                               <div id="trashView" class="row">
                                    <div class="row fullheight">
                                        <div class="col-sm-12 fullheight" ng-include="'http://localhost/jocomunico/EditViewTrash.html'">
                                         
                                        </div>
                                    </div>
                                </div>
                                <div id="searchView" class="container-fluid">
                                    <div class="row fullheight">
                                        <div class="col-sm-12 fullheight" ng-include="'http://localhost/jocomunico/EditView.html'">
                                         
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="col-xs-{{grid2}} col-md-{{grid2}} fullheight" ng-init="data = []" style="background-color: Red;">
                        <div id="board" class="fullheight">
                            <div class="container-fluid" ng-hide="subgrid1hide" style="height:{{subgrid1}}%">
                                <div class="row fullheight" ng-include="'http://localhost/jocomunico/SentenceView.html'">                     
                                </div>
                            </div>
                            <div class="container-fluid" style="height:{{subgrid2}}%">
                                <div class="row fullheight" >

                                    <div class="col-xs-12" ng-repeat="picto in data" ng-style="{'width':'{{100 / columns}}%','height':'{{100 / rows}}% '}">

                                        <img ng-if="picto.imgPicto" width="100%" height="100%" ng-src="{{baseurl}}img/pictos/{{picto.imgPicto}}"  ng-click="addToSentence(picto.pictoid);" ng-right-click="openMenu(picto.pictoid);" ng-drop="true" ng-drop-success="onDropSwap({{picto.posInBoard}},$data,$event)" ng-drag="true" ng-drag-data="{posInBoardPicto:'{{picto.posInBoard}}'}" data-allow-transform="true"/>
                                        
                                        
                                        
                                        <div ng-drop="true" ng-drop-success="onDropSwap({{picto.posInBoard}},$data,$event)" ng-if="!picto.imgPicto" class="emptyImg" />
                                    </div>
                                </div>

                            </div>
                            <br/><br/><br/><br/>
                        </div>
                        <div class="row" ng-hide="subgrid3hide" style="height:{{subgrid3}}%">
                            <h4>SENTENCEBOARD1.PHP</h4>
                        </div>
                    </div>
                </div>
                <div ng-hide="grid3hide" class="col-xs-{{grid3}} fullheight" style="background-color: Blue;">
                    <div>
                        <h4>UNKNOWN.PHP</h4>
                    </div>
                </div>

            </div>

        </div>
        <div id="footer" class="container-fluid text-center row">
            <div style="background-color: orange">
                <br />
                <button class="btn-success" ng-click="showall()">2:8:2</button>
                <button class="btn-success" ng-click="showright()">10:2</button>
                <button class="btn-success" ng-click="showleft()">2:10</button>
                <button class="btn-success" ng-click="showmid()">12</button>
                <button class="btn-success" ng-click="showupdown()">2:8:2</button>
                <button class="btn-success" ng-click="showup()">2:10</button>
                <button class="btn-success" ng-click="showdown()">10:2</button>
                <button class="btn-success" ng-click="showmiddle()">12</button>
                <br />
            </div>
            <div style="background-color: yellow">
                <br />
                <button class="btn-success" ng-click="edit()">edit</button>
                <br />
            </div>
            <div style="background-color: grey">
                <br />
                <button class="btn-success" ng-click="addColumn()">addColumn</button>
                <button class="btn-success" ng-click="addRow()">addRow</button>
                <br />
                <br />
                <button class="btn-success" ng-click="removeColumn()">removeColumn</button>
                <button class="btn-success" ng-click="removeRow()">removeRow</button>
                <br />
            </div>

        </div>
        </body>
</html>
