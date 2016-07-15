<?php
    // BUILDS THE SELECTED NOUNS CONTAINER

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

                <span class="botoeliminar" onclick="eliminarParaula('<?=base_url();?>', '<?=$word->identry;?>');">-</span>
            </span>
<?php   }
    }
?>