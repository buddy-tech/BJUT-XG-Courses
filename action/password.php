<?php
  include_once "../src/php/init.php";
  include_once "../src/php/user.php";
  $page_path="..";
  init_mysql();
  password($_POST[password]);
  header("Location: ./logout.php");
?>