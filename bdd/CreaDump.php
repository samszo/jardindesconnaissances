<?php
// Database configuration
$db_server   = "balpenammygen.mysql.db";
$db_name     = "balpenammygen";
$db_username = "balpenammygen";
$db_password = "Zappa2015";

echo "Votre base est en cours de sauvegarde.......

";
$date = new DateTime();
$strDate = $date->format('Y-m-d_H-i-s');
system("mysqldump --host=$db_server --user=$db_name --password=$db_password $db_username > generateur".$strDate.".sql");
echo "C'est fini. Vous pouvez récupérer la base par FTP";
?>