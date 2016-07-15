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

<body onload="esperar('<?=base_url();?>');">

    <div id="cos-resultats">

        <span class="logout"><a href="<?=base_url();?>home/logout" class="link">Sortir</a></span>
        <span class="benvingut">Benvingut/da <strong><?=$this->session->userdata('uname');?></strong>!</span>

        <span class="frase-entrada">
        Esperant una frase des de Plaphoons...
        
        <div id="timer"><?=$first;?></div> 
        
        <a href="<?=base_url();?>frase" class="link">
            <span style="font-style: normal;">Entrada per llistats</span>
        </a>
        </span> 

        <div id="frasegenerada"><?=$second;?></div>
        
    </div>

</body>
