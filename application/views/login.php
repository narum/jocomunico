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

    <div id="coslogin">

        <form id="form-login" action="<?=base_url();?>" method="post" >

            <span class="frase-entrada">Introdueix el teu usuari i la contrasenya... </span>

            <div id="login-box">
                <input type=hidden name="envioform" value="true" />

                <label class="tagdades">Usuari: </label><br />
                <input type="text" name="usuari" size="30" class="barradatos" tabindex="1" maxlength="50" value="<?php echo set_value('usuari');?>"/>
                <br /><br />

                <label class="tagdades">Password: </label><br />
                <input type="password" name="pass" size="30" class="barradatos" tabindex="1" maxlength="50" />
                <br /><br />

                <?php echo validation_errors(); ?>

                <span class="boto-login"><?=form_submit('loginsubmit', 'Entrar', 'id="enviarlogin"');?></span>
            </div>
        </form>

    </div>

</body>
