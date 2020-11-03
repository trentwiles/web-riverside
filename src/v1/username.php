<?php
session_start();
header("Content-type: text/javascript");
echo "const user = " . $_SESSION["username"] . ";\n";
echo "const user_id = " . $_SESSION["id"] . ";\n";