<?php
 $db_host="localhost";/*El nombre del host*/
 $db_usuario="root";/*El nombre del usuario*/
 $db_contra="";/*El nombre de la contraseña*/
 $db_nombre="tesis";/*El nombre de la base de datos*/
 $conn=new mysqli($db_host,$db_usuario,$db_contra,$db_nombre);/*La sentencia*/
 if(!$conn){
		echo ("No se ha conectado a la base de datos");
 }
?>