<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Natural Language Processing: AAC application in Catalan" />
<meta name="keywords" content="nlp, pln, processament, llenguatge, natural, natural, language, processing, aac, comunicació augmentativa
      i alternativa, comunicació, augmentativa, alternativa, augmentative and alternative communication, catalan, català, gratis, lliure,
      opensource, free, text generation, natural language, llenguatge natural, projecte, master, arasaac" />

<link href="<?= base_url();?>css/projecte.css" rel="stylesheet" type="text/css" />
<script src="<?= base_url();?>libraries/nlp/prototype.js" type="text/javascript"></script>
<script src="<?= base_url();?>libraries/nlp/scriptaculous.js" type="text/javascript"></script>
<script src="<?= base_url();?>libraries/nlp/scripts.js" type="text/javascript"></script>

<title>Jo Comunico Beta</title>

</head>

<body>

    <div id="cos">

        <form id="form-frase" action="<?=base_url();?>frase" method="post" >

            <span class="logout"><a href="<?=base_url();?>home/logout" class="link">Sortir</a></span>
            <span class="benvingut">Benvingut/da <strong><?=$this->session->userdata('uname');?></strong>!</span>
            <span class="frase-entrada">Introdueix una frase per generar... </span>

            <div id="frase-select-box">
                <input type=hidden name="envioform" value="true" />

                <div id="llistats">
                    
                    <div id="llistats-noms" class="llistatsclasse">
                        <span class="titolclasse">
                            <span style="margin-right:20px;">Noms</span>
                            <span class="nommodif" onclick="afegirModifNom('<?=base_url();?>', 'pl')">Plural</span>
                            <span class="nommodif" onclick="afegirModifNom('<?=base_url();?>', 'fem')">Femení</span>
                        </span>

                         <div id="llistat-noms-humans" class="llistatssubclasse">
                            <span class="titolsubclasse">Humans </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Pronoms: </span>
                                <select id="noms-pronoms" name="nompronom" onchange="afegirParaula('<?=base_url();?>' ,'nompronom', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsPronoun); $i++) { ?>
                                        <option value="<?=$nomsPronoun[$i]->nameid;?>"> <?=$nomsPronoun[$i]->nomtext;?> 
                                        <?php if ($nomsPronoun[$i]->nomtext == "jo") echo "(mi)" ?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nompronom', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Persones: </span>
                                <select id="noms-human" name="nomhuman" onchange="afegirParaula('<?=base_url();?>' ,'nomhuman', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsHuman); $i++) { ?>
                                        <option value="<?=$nomsHuman[$i]->nameid;?>"> <?=$nomsHuman[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomhuman', 'Nom');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Noms humans -->

                        <div id="llistat-noms-lloc-hores" class="llistatssubclasse">
                            <span class="titolsubclasse">Llocs i Hores </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Llocs: </span>
                                <select id="noms-llocs" name="nomlloc" onchange="afegirParaula('<?=base_url();?>' ,'nomlloc', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsLloc); $i++) { ?>
                                        <option value="<?=$nomsLloc[$i]->nameid;?>"> <?=$nomsLloc[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomlloc', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Hores: </span>
                                <select id="noms-hores" name="nomhores" onchange="afegirParaula('<?=base_url();?>' ,'nomhores', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsHora); $i++) { ?>
                                        <option value="<?=$nomsHora[$i]->nameid;?>"> <?=$nomsHora[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomhores', 'Nom');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Noms llocs i hores -->

                        <div id="llistat-noms-animats" class="llistatssubclasse">
                            <span class="titolsubclasse">Objectes o éssers animats </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Animals: </span>
                                <select id="noms-animal" name="nomanimal" onchange="afegirParaula('<?=base_url();?>' ,'nomanimal', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsAnimal); $i++) { ?>
                                        <option value="<?=$nomsAnimal[$i]->nameid;?>"> <?=$nomsAnimal[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomanimal', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Vehicles i Altres: </span>
                                <select id="noms-animat" name="nomanimat" onchange="afegirParaula('<?=base_url();?>' ,'nomanimat', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsAnimat); $i++) { ?>
                                        <option value="<?=$nomsAnimat[$i]->nameid;?>"> <?=$nomsAnimat[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomanimat', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Plantes: </span>
                                <select id="noms-planta" name="nomplanta" onchange="afegirParaula('<?=base_url();?>' ,'nomplanta', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsPlanta); $i++) { ?>
                                        <option value="<?=$nomsPlanta[$i]->nameid;?>"> <?=$nomsPlanta[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomplanta', 'Nom');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Noms animats -->

                        <div id="llistat-noms-temps" class="llistatssubclasse">
                            <span class="titolsubclasse">Temps </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">General: </span>
                                <select id="noms-temps" name="nomtemps" onchange="afegirParaula('<?=base_url();?>' ,'nomtemps', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsTemps); $i++) { ?>
                                        <option value="<?=$nomsTemps[$i]->nameid;?>"> <?=$nomsTemps[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomtemps', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Setmana: </span>
                                <select id="noms-week" name="nomweek" onchange="afegirParaula('<?=base_url();?>' ,'nomweek', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsWeek); $i++) { ?>
                                        <option value="<?=$nomsWeek[$i]->nameid;?>"> <?=$nomsWeek[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomweek', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Mesos: </span>
                                <select id="noms-month" name="nommonth" onchange="afegirParaula('<?=base_url();?>' ,'nommonth', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsMonth); $i++) { ?>
                                        <option value="<?=$nomsMonth[$i]->nameid;?>"> <?=$nomsMonth[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nommonth', 'Nom');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Noms temps -->

                        <div id="llistat-noms-objs-menjar" class="llistatssubclasse">
                            <span class="titolsubclasse">Objectes inanimats i menjar </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Objecte: </span>
                                <select id="noms-objecte" name="nomobjecte" onchange="afegirParaula('<?=base_url();?>' ,'nomobjecte', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsObjecte); $i++) { ?>
                                        <option value="<?=$nomsObjecte[$i]->nameid;?>"> <?=$nomsObjecte[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomobjecte', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Joguina i esport: </span>
                                <select id="noms-joc" name="nomsjoc" onchange="afegirParaula('<?=base_url();?>' ,'nomsjoc', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsJoc); $i++) { ?>
                                        <option value="<?=$nomsJoc[$i]->nameid;?>"> <?=$nomsJoc[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomsjoc', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Menjar: </span>
                                <select id="noms-menjar" name="nommenjar" onchange="afegirParaula('<?=base_url();?>' ,'nommenjar', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsMenjar); $i++) { ?>
                                        <option value="<?=$nomsMenjar[$i]->nameid;?>"> <?=$nomsMenjar[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nommenjar', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Beguda: </span>
                                <select id="noms-beguda" name="nombeguda" onchange="afegirParaula('<?=base_url();?>' ,'nombeguda', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsBeguda); $i++) { ?>
                                        <option value="<?=$nomsBeguda[$i]->nameid;?>"> <?=$nomsBeguda[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nombeguda', 'Nom');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Noms objectes i menjar -->

                        <div id="llistat-noms-altres" class="llistatssubclasse">
                            <span class="titolsubclasse">Abstractes i parts del cos </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Abstracte: </span>
                                <select id="noms-abstracte" name="nomabstracte" onchange="afegirParaula('<?=base_url();?>' ,'nomabstracte', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsAbstracte); $i++) { ?>
                                        <option value="<?=$nomsAbstracte[$i]->nameid;?>"> <?=$nomsAbstracte[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomabstracte', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Forma i color: </span>
                                <select id="noms-forma" name="nomforma" onchange="afegirParaula('<?=base_url();?>' ,'nomforma', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsForma); $i++) { ?>
                                        <option value="<?=$nomsForma[$i]->nameid;?>"> <?=$nomsForma[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomforma', 'Nom');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Cos: </span>
                                <select id="noms-cos" name="nomcos" onchange="afegirParaula('<?=base_url();?>' ,'nomcos', 'Nom');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($nomsCos); $i++) { ?>
                                        <option value="<?=$nomsCos[$i]->nameid;?>"> <?=$nomsCos[$i]->nomtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'nomcos', 'Nom');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Noms abstractes i parts del cos -->

                    </div> <!-- Fi llistat Noms -->

                    <div id="llistats-verbs" class="llistatsclasse">
                        <span class="titolclasse">Verbs</span>

                        <div id="llistat-verbs" class="llistatssubclasse">
                            <span class="titolsubclasse">Verbs més comuns </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Verb: </span>
                                <select id="verbs-all" name="verball" onchange="afegirParaula('<?=base_url();?>' ,'verball', 'Verb');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($verbs); $i++) { ?>
                                        <option value="<?=$verbs[$i]->verbid;?>"> <?=$verbs[$i]->verbtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'verball', 'Verb');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Verbs -->

                        <div id="temps-verbs" class="llistatssubclasse">
                            <span class="titolsubclasse">Temps verbal </span>

                            <div class="grupsubsubclasse">
                                <input type="radio" name="tense" value="defecte" checked><span class="radiotop">Defecte</span></input>
                                <input type="radio" name="tense" value="present"><span class="radiotop">Present</span></input>
                                <input type="radio" name="tense" value="perifrastic"><span class="radiotop">Passat</span></input><br />
                                <input type="radio" name="tense" value="perfet"><span class="radiotop">Passat inmediat</span></input>
                                <input type="radio" name="tense" value="imperfecte"><span class="radiotop">Passat llunyà</span></input><br />
                                <input type="radio" name="tense" value="futur"><span class="radiotop">Futur</span></input><br />
                            </div> <br /><br />

                        </div> <!-- Fi llistat Verbs -->
                        
                    </div> <!-- Fi Verbs -->

                    <div id="llistats-adjs-advs" class="llistatsclasse">
                        <span class="titolclasse">Adjectius i Adverbis</span>

                        <div id="llistat-adjs" class="llistatssubclasse">
                            <span class="titolsubclasse">Adjectius </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Per tot: </span>
                                <select id="adjs-all" name="adjall" onchange="afegirParaula('<?=base_url();?>' ,'adjall', 'Adjectiu');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($adjsAll); $i++) { ?>
                                        <option value="<?=$adjsAll[$i]->adjid;?>"> <?=$adjsAll[$i]->masc;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'adjall', 'Adjectiu');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Animat: </span>
                                <select id="adjs-animat" name="adjanimat" onchange="afegirParaula('<?=base_url();?>' ,'adjanimat', 'Adjectiu');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($adjsAnimat); $i++) { ?>
                                        <option value="<?=$adjsAnimat[$i]->adjid;?>"> <?=$adjsAnimat[$i]->masc;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'adjanimat', 'Adjectiu');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Objecte: </span>
                                <select id="adjs-objecte" name="adjobjecte" onchange="afegirParaula('<?=base_url();?>' ,'adjobjecte', 'Adjectiu');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($adjsObjecte); $i++) { ?>
                                        <option value="<?=$adjsObjecte[$i]->adjid;?>"> <?=$adjsObjecte[$i]->masc;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'adjobjecte', 'Adjectiu');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Color: </span>
                                <select id="adjs-color" name="adjcolor" onchange="afegirParaula('<?=base_url();?>' ,'adjcolor', 'Adjectiu');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($adjsColor); $i++) { ?>
                                        <option value="<?=$adjsColor[$i]->adjid;?>"> <?=$adjsColor[$i]->masc;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'adjcolor', 'Adjectiu');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Adjectius -->

                        <div id="llistat-advs" class="llistatssubclasse">
                            <span class="titolsubclasse">Adverbis </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Lloc: </span>
                                <select id="advs-lloc" name="advlloc" onchange="afegirParaula('<?=base_url();?>' ,'advlloc', 'Adverb');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($advsLloc); $i++) { ?>
                                        <option value="<?=$advsLloc[$i]->advid;?>"> <?=$advsLloc[$i]->advtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'advlloc', 'Adverb');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Temps: </span>
                                <select id="advs-temps" name="advtemps" onchange="afegirParaula('<?=base_url();?>' ,'advtemps', 'Adverb');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($advsTemps); $i++) { ?>
                                        <option value="<?=$advsTemps[$i]->advid;?>"> <?=$advsTemps[$i]->advtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'advtemps', 'Adverb');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Manera: </span>
                                <select id="advs-manera" name="advmanera" onchange="afegirParaula('<?=base_url();?>' ,'advmanera', 'Adverb');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($advsManera); $i++) { ?>
                                        <option value="<?=$advsManera[$i]->advid;?>"> <?=$advsManera[$i]->advtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'advmanera', 'Adverb');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Adverbis -->

                    </div> <!-- Fi Adjectius i Adverbis -->

                    <div id="llistats-modifs-exprs-nums-preg" class="llistatsclasse">
                        <span class="titolclasse">
                            <span style="margin-right:20px">Modificadors, Expressions i Preguntes</span>
                            <span class="nommodif" onclick="afegirModifNom('<?=base_url();?>', 'i')">i</span>
                        </span>

                        <div id="llistat-modifs" class="llistatssubclasse">
                            <span class="titolsubclasse">Modificadors i Números </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">De paraula: </span>
                                <select id="modifs-word" name="modifword" onchange="afegirParaula('<?=base_url();?>' ,'modifword', 'Modifier');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($modifsWord); $i++) { ?>
                                        <option value="<?=$modifsWord[$i]->modid;?>"> <?=$modifsWord[$i]->masc;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'modifword', 'Modifier');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">De frase: </span>
                                <select id="modifs-phrase" name="modifphrase" onchange="afegirParaula('<?=base_url();?>' ,'modifphrase', 'Modifier');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($modifsPhrase); $i++) { ?>
                                        <option value="<?=$modifsPhrase[$i]->modid;?>"> <?=$modifsPhrase[$i]->masc;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'modifphrase', 'Modifier');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Número: </span>
                                <select id="adjs-numeros" name="adjnumero" onchange="afegirParaula('<?=base_url();?>' ,'adjnumero', 'Adjectiu');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($adjsNumero); $i++) { ?>
                                        <option value="<?=$adjsNumero[$i]->adjid;?>"> <?=$adjsNumero[$i]->masc;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'adjnumero', 'Adjectiu');">+</span>
                            </div> <br /><br />

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Ordinal: </span>
                                <select id="adjs-ordinal" name="adjordinal" onchange="afegirParaula('<?=base_url();?>' ,'adjordinal', 'Adjectiu');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($adjsOrdinal); $i++) { ?>
                                        <option value="<?=$adjsOrdinal[$i]->adjid;?>"> <?=$adjsOrdinal[$i]->masc;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'adjordinal', 'Adjectiu');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Modificadors -->

                        <div id="llistat-exprs" class="llistatssubclasse">
                            <span class="titolsubclasse">Expressions</span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Expressió: </span>
                                <select id="exprs-complet" name="exprcomplet" onchange="afegirParaula('<?=base_url();?>' ,'exprcomplet', 'Expressions');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($expressions); $i++) { ?>
                                        <option value="<?=$expressions[$i]->exprid;?>"> <?=$expressions[$i]->exprtext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'exprcomplet', 'Expressions');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Expressions -->

                         <div id="llistat-pregs" class="llistatssubclasse">
                            <span class="titolsubclasse">Preguntes </span>

                            <div class="grupsubsubclasse">
                                <span class="titolsubsubclasse">Pregunta: </span>
                                <select id="parts-pregunta" name="partpregunta" onchange="afegirParaula('<?=base_url();?>' ,'partpregunta', 'PartPregunta');" size=1 tabindex="1" class="selectbox">
                                    <?php for ($i=0; $i<count($partspregunta); $i++) { ?>
                                        <option value="<?=$partspregunta[$i]->questid;?>"> <?=$partspregunta[$i]->parttext;?></option>
                                    <?php } ?>
                                </select>
                                <span class="botoafegir" onclick="afegirParaula('<?=base_url();?>' ,'partpregunta', 'PartPregunta');">+</span>
                            </div> <br /><br />

                        </div> <!-- Fi llistat Partícules de pregunta -->

                    </div> <!-- Fi Modificadors, Expressions i Preguntes -->
                    
                </div> <!-- Fi llistats --> <br />

                <div id="tipus-frase">
                    <span class="titolclasse">Modificadors de frase</span>
                    
                    <div id="contenidor-tipus-frase">
                        <span class="titolsubclasse">Tipus de frase </span>
                        <input type="radio" name="tipusfrase" value="defecte" checked><span class="radiobottom">Defecte</span></input>
                        <input type="radio" name="tipusfrase" value="enunciativa"><span class="radiobottom">Enunciativa</span></input>
                        <input type="radio" name="tipusfrase" value="desig"><span class="radiobottom">Desig</span></input>
                        <input type="radio" name="tipusfrase" value="permis"><span class="radiobottom">Demanar permís</span></input>
                        <input type="radio" name="tipusfrase" value="ordre"><span class="radiobottom">Ordre</span></input> <br />
                        <input type="radio" name="tipusfrase" value="pregunta"><span class="radiobottom">Pregunta</span></input>
                        <input type="radio" name="tipusfrase" value="resposta"><span class="radiobottom">Resposta</span></input>
                        <input type="radio" name="tipusfrase" value="condicional"><span class="radiobottom">Condicional</span></input>
                        <input type="radio" name="tipusfrase" value="exclamacio"><span class="radiobottom">Exclamació</span></input> <br /><br />

                        <input type="checkbox" name="negativa"><strong>Negativa</strong></input><br />
                    </div>

                    <span class="titolfraseobjectiu"> Frase objectiu: </span>
                    <input type="text" name="fraseobj" size="55" class="barrafraseobj" maxlength="500"/>
                    
                    <?php echo validation_errors(); ?>
                </div>

                <div id="frase-building">
                    <span class="titolclasse">Elements seleccionats</span>

                    <div id="contenidor-frase">
                        <?php
                            for ($i=0; $i<count($paraulesFrase); $i++) {

                                if ($paraulesFrase[$i] != null) {
                                    $word = $paraulesFrase[$i];
                            ?>

                                    <span class="paraula-building">

                        <?php
                                    echo $word->text;
                                    if($word->plural || $word->fem || $word->coord) {
                                        echo '(';
                                        if ($word->plural) echo 'pl';
                                        if ($word->plural && ($word->fem || $word->coord)) echo ', ';
                                        if ($word->fem) echo 'fem';
                                        if ($word->fem && $word->coord) echo ', ';
                                        if ($word->coord) echo 'i';
                                        echo ')';
                                    } ?>

                                        <span class="botoeliminar" onclick="eliminarParaula('<?=base_url();?>', '<?=$word->id;?>');">-</span>
                                    </span>
                        <?php   }
                            }
                        ?>
                    </div> <!-- Fi Contenidor Frase -->

                    <span class="boto-enviar-frase"><?=form_submit('frasesubmit', 'Generar', 'id="enviarfrase"');?></span>
                    
                </div> <!-- Fi Frase Building -->
                
            </div> <!-- Fi Frase Select Box -->
            
        </form>

    </div>

</body>
