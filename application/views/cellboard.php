<div class="row" style="height:100%;">
    <?php
    $percentageh = 100 / $r;
    for ($ir = 0; $ir < $r; $ir++) { ?>
        <div class="col-xs-12" style="background-color: #<?= $ir ?>00<?= $ir ?>00;height:<?= $percentageh ?>%;">
            <?php
            $percentagew = 100 / $c;
            for ($i = 0; $i < $c; $i++) { 
                ?>
                <div class="col-xs-12" style="background-color: #<?= $i ?>000<?= $i ?>0;width:<?= $percentagew ?>%;height:100%;">

                    <img width="100%" height="100%" src="<?=base_url();?>img/intgris.gif"/>

                </div>
                <?php
            }
            ?>

        </div>
    <?php } ?>


</div>

