<?php
 $db_host="sql305.infinityfree.com";/*El nombre del host*/
 $db_usuario="if0_39399863";/*El nombre del usuario*/
 $db_contra="xK5exaOffflYBW";/*El nombre de la contraseña*/
 $db_nombre="if0_39399863_tesis";/*El nombre de la base de datos*/
 $conn=new mysqli($db_host,$db_usuario,$db_contra,$db_nombre);/*La sentencia*/
 if(!$conn){
		echo ("No se ha conectado a la base de datos");
 }
?>