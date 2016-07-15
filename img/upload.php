<?php

  $target_dir = "../img/";
  $target_file = $target_dir . basename($_FILES['file']['name']);
  var_dump($_FILES);

  move_uploaded_file( $_FILES['file']['tmp_name'] , $target_file );


?>
