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

<title>Jo Comunico Beta - Resultats</title>

</head>

<body>
    

    <div id="cos-resultats">
        
        <span class="logout"><a href="<?=base_url();?>home/logout" class="link">Sortir</a></span>
        <span class="benvingut">Benvingut/da <strong><?=$this->session->userdata('uname');?></strong>!</span>

        <span class="frase-entrada">
            Gràcies per enviar-nos una frase! Ara és hora de puntuar-la...
            &nbsp;&nbsp;&nbsp; 
            <a href="<?=base_url();?>frase" class="link">
                <span style="font-style: normal;">Nova frase</span>
            </a>
        </span> 
        <br /><br />
        
        <form id="form-notes" action="<?=base_url();?>resultats/gracies" method="post" >
            <input type="hidden" name="identry" value="<?=$identry;?>" />
        
            <div id="wrapper-left">
                <div id="resultats">
                    <div id="inputwords">
                        <span class="parseheader">Paraules introduïdes:</span> <br />
                        <span class="introwords"><?=$inputwords;?></span>
                    </div>

                    <div id="frasefinal">
                        <span class="parseheader">Frase generada:</span> <br />
                        <span class="fraseresultat"><?=$frasefinal;?></span>
                    </div>

                    <div id="parsepattern">
                        <?php 
                            if ($error) { ?> <span class="errormessage"><?=$errormessage;?></span>
                            <?php } else { ?> <span class="parseheader">Parse tree:</span> <br />
                                <span class="pattern"> <?=$printparsepattern;?> </span> <br />
                                <?php if ($errormessage != null) { ?>
                                    <span class="errormessage"><?=$errormessage;?></span>
                        <?php }
                        } ?>
                    </div>

                   <!-- <object width="300" height="42" style="margin-left:20px;">
                        <param name="src" value="<?=base_url();?><?=$audio;?>">
                        <param name="autoplay" value="false">
                        <param name="controller" value="true">
                        <param name="bgcolor" value="#FFFFFF">
                        <embed src="<?=base_url();?><?=$audio;?>" loop="false" width="300" height="42"
                        controller="true" bgcolor="#FFFFFF"></embed>
                    </object> -->
                    
                </div>

                <div id="comentaris-box">
                    <div id="comentaris">
                        <span class="parseheader">Comentaris:</span>
                        <textarea name="comments" class="textarea"> </textarea>
                        <span class="info">*Deixeu comentaris relacionats amb els errors del generador
                        i/o del parser si les opcions escollides no són prou clares.</span>
                    </div>
                </div>
                
                <span class="boto-enviar-notes"><input type="submit" name="notessubmit" value="Enviar puntuacions" id="enviarnotes" /></span>

            </div>

            <div id="puntuacions">

                <div id="scoregenerator">
                    <span class="parseheader">Puntuació frase generada:</span> 
                    <span class="radioscore"><input type="radio" name="scoregen" value="9" checked>
                            La frase s'ha generat <strong>perfectament</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoregen" value="8">
                            La frase està ben generada i s'entén, el parsing era correcte, però hi ha petits errors
                            en l'<strong>ordre de les paraules</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoregen" value="7">
                            La frase està ben generada i s'entén, el parsing era correcte, però hi ha petits errors
                            en els <strong>articles</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoregen" value="6">
                            La frase està ben generada i s'entén, el parsing era correcte, però hi ha petits errors
                            en les <strong>conjugacions verbals</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoregen" value="5">
                            La frase està ben generada i s'entén, el parsing era correcte, però hi ha petits <strong>errors
                            no descrits en les opcions anteriors</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoregen" value="4">
                            La frase està ben generada i s'entén, tot i que hi ha <strong>errors provinents del parsing</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoregen" value="3">
                            La frase està ben generada, però <strong>no s'entén</strong>, ja que hi ha <strong>errors provinents del parsing</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoregen" value="2">
                            La frase està mal generada i <strong>no s'entén</strong>, tot i que el <strong>parsing era correcte</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoregen" value="1">
                            La frase està mal generada i <strong>no s'entén</strong>. A més el <strong>parsing tampoc era correcte</strong>.<br />
                    </input></span>
                </div>

                <div id="scoreparser">
                    <span class="parseheader">Puntuació parser:</span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="10" checked>
                            L'anàlisi del parser és <strong>correcte</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="9">
                            L'anàlisi del parser <strong>no és l'esperat, però</strong> l'opció escollida també pot
                            ser bona, ja que <strong>no canvia gaire el sentit de la frase</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="8">
                            L'anàlisi del parser és <strong>incorrecte</strong>. Error en la detecció del <strong>subjecte</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="7">
                            L'anàlisi del parser és <strong>incorrecte</strong>. L'<strong>adjectiu</strong> no està al lloc adequat.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="6">
                            L'anàlisi del parser és <strong>incorrecte</strong>. Error en la <strong>detecció d'un nom com a
                            complement de nom</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="5">
                            L'anàlisi del parser és <strong>incorrecte</strong>. Ha posat un <strong>nom a un slot que no tocava</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="4">
                            L'anàlisi del parser és </strong>incorrecte</strong>. El <strong>sentit del verb</strong> escollit pel parser
                            <strong>no és el desitjat</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="3">
                            L'anàlisi del parser és <strong>incorrecte</strong>. L'error pot ser degut a l'<strong>ordre d'entrada
                            de les paraules</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="2">
                            L'anàlisi del parser és <strong>incorrecte</strong>. Error <strong>múltiple o no descrit en les 
                            opcions anteriors</strong>.<br />
                    </input></span>
                    <span class="radioscore"><input type="radio" name="scoreparser" value="1">
                            L'anàlisi del parser és <strong>incorrecte i no té cap mena de sentit</strong>.<br />
                    </input></span>
                </div>

            </div> <!-- Fi puntuacions -->
            
        </form>    
    </div>

</body>
