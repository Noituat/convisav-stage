<?php


//------------- UPDATE_STATUS($CSV) ------------------------------------------------------------------------------------------
//Fonction de mise à jour du status dans la base de données des livraisons
//Cette fonction va nous permettre de modifier l'état de la livraison, soit le champ current_state avec la valeur définie dans le fichier CSV fourni au script
function update_status($csv,$db){
  for ($i=0; $i < count($csv) ; $i++) {//Pour une valeur i égale au nombre de case du tableau csv, soit au nombre de livraisons à traiter
    if($csv[$i]["current_state"] == "Expediee"){//Si l'état de la livraison indiqué dans le tableau CSV est une chaine de caractère égale à "Expediee"
      $sql = "UPDATE `ps_orders` SET current_state = 4 WHERE reference = '".$csv[$i]["reference"]."';";//On effectue une requête de modification de l'état sur la base de données
      $query = $db->query($sql);
    }
    if($csv[$i]["current_state"] == "Livree"){//Si l'état de la livraison indiqué dans le tableau CSV est une chaine de caractère égale à "Livree"
      $sql = "UPDATE `ps_orders` SET current_state = 5 WHERE reference = '".$csv[$i]["reference"]."';";//On effectue une requête de modification de l'état sur la base de données
      $query = $db->query($sql);
    }
    if(($csv[$i]["current_state"] != "Expediee") && ($csv[$i]["current_state"] != "Livree")){//Si l'état de la livraison indiqué dans le tableau CSV ne correspond pas à un état connu,
      echo "La livraison ".$csv[$i]["reference"]." n'a pas pu être traitée car son état de correspond pas à un état valide.\n";//On indique à l'utilisateur que la livraison n'a pas pu être traitée.
    }
  }
}

//------------- PRINT_HTML_LOW_SECTION_NO_SCRIPT($DB, $FOLDER) ------------------------------------------------------------------------------------------
//Fonction d'affichage de la partie basse de la page html s'il n'y a pas de livraison à traiter.
//On indique alors à l'utilisateur qu'il n'y a pas fichier de log présent
//On ferme alors la connexion à la base de données et on stoppe l'éxécution du script.
function print_html_low_section_no_script($db){
  $db = null;//On clos la connexion
  //On renseigne que le fichier de log n'est pas présent et on redirige l'utilisateur vers le backOffice avec un script javascript dans le code html
  echo "<script type=\"text/javascript\">alert(\"Fichier non présent !\");window.location.replace(\"http://localhost/admin796yw9jli/index.php?controller=AdminOrders\"); </script>\n";
  echo "\t</body>\n";//On ferme la balise body
  echo "</html>\n";//Ainsi que la balise html
  exit();//On stoppe alors le fonctionnement du script
}

//------------- PRINT_HTML_LOW_SECTION_SCRIPT($DB) ------------------------------------------------------------------------------------------
//Fonction d'affichage de la partie basse de la page html s'il y a des livraison qui ont été traitées.
//On ferme alors la connexion àa la base de données et on stoppe l'éxécution du script.
function print_html_low_section_script($db){
  $db = null;//On clos la connexion à la base de données
  //On renseigne que le script a été executé et on redirige l'utilisateur vers le backOffice avec un script javascript dans le code html
  echo "<script type=\"text/javascript\">alert(\"Script éxécuté\");window.location.replace(\"http://localhost/admin796yw9jli/index.php?controller=AdminOrders\"); </script>\n";
  echo "\t</body>\n";//On ferme la balise body
  echo "</html>\n";//Ainsi que la balise html
  exit();//On stoppe alors le fonctionnement du script
}
