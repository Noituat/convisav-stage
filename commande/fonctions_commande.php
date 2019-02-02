<?php

//---------------- TEST_FIELD($DB, $FIELD) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction de test d'existence d'un champs dans une table de la base de données.
//Elle est principalement utilisée ici pour vérifier que le champs EXPORT_CDE existe dans la table
//'ps_orders', si ce champs n'existe pas, il est créé afin de permettre la mise à jour du traitement des commandes
//par le script.
//Le champs EXPORT_CDE est un booléen représenté par le type tinyint sur 2 valeurs (1 : true, 0 : false)
//la foncction ne retourne pas de valeur.
function testField($db, $field){
  try {
    $SQLExist = "SHOW COLUMNS FROM `ps_orders` LIKE '$field'";//Requête de test pour le nom de champs (initialement prévue pour le champ EXPORT_CDE)
    $exists = $db->query($SQLExist);//On effectue la requête auprès de la base de données entrée en paramètre de la fonction
    $fieldExist = $exists->fetchAll(PDO::FETCH_ASSOC);//On fetch ensuite cette réponse dans un tableau
    if(empty($fieldExist)){//Si le tableau est vide, cela signifie que le champs n'existe pas
      $SQLCreateField = "ALTER TABLE `ps_orders` ADD $field tinyint(1) DEFAULT 0";//Et on modifie la base de données pour le créé.
      $SQLCreateField = $db->query($SQLCreateField);//La requête est alors envoyée au serveur afin de créé ce champs dans la base
    }
    else{
    }
  } catch (\Exception $e) {//S'il y a une erreur dans l'execution de la fonction, on récupère les erreurs avec la fonction catch()
    exit();//Et on stoppe le fonctionnement de la page.
  }
}

//------------------ QUERY_ENTETE($DB) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction de requête sur la base de données pour le traitement du fichier commande_entete.csv
//Cette fonction permet de récupérer dans un tableau toutes les champs nécéssaires au logiciel ConviSAV
//pour chaque commande dont l'état du champs EXPORT_CDE est de 0 (commande non traitée);
//Cette fonction retourne un tableau dont chaque case contient les informations d'une commande.
function queryEntete($db){
  $tabEntete = [];//Tableau qui va contenir dans chaque case un tableau avec chaque commande.
  $resultEntete = $db->query("SELECT ord.id_order, ord.reference, cus.firstname, cus.lastname, cus.email, loc.address1, loc.postcode, loc.city, loc.phone, loc.id_country, loc.id_state, ord.date_add, ord.total_paid_tax_excl, ord.total_paid_tax_incl, ord.date_upd, ord.payment FROM ps_address AS loc INNER JOIN ps_customer AS cus ON cus.id_customer = loc.id_customer INNER JOIN ps_orders AS ord ON cus.id_customer = ord.id_customer WHERE ord.EXPORT_CDE = 0;");
  //On effectue la requête auprès de la base de données visant à récupérer les différents champs nécéssaires au logiciel ConviSAV
  $arrayEntete = $resultEntete->fetchAll(PDO::FETCH_NUM);//On fetch le résultat de la requête dans un tableau
  for ($i=0; $i < count($arrayEntete) ; $i++) {
    //En parcourant ce tableau, on va inscrire dans le tableau $tabEntete
    //le tableau de valeurs correspondant à chacune des commandes.
    //Cela va permettre de simplifier le script avec un tableau à 2 niveau (Niveau 1 -> Commande, Niveau 2 -> Valeurs)
    $tabEntete[] = $arrayEntete[$i];
  }
  return $tabEntete;//On retourne ensuite le tableau afin de pouvoir l'utiliser par la suite dans le script
}

//------------------ QUERY_ENTETE($DB) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction de requête sur la base de données pour le traitement du fichier commande_entete.csv
//Cette fonction permet de récupérer dans un tableau toutes les champs nécéssaires au logiciel ConviSAV
//pour chaque commande dont l'état du champs EXPORT_CDE est de 0 (commande non traitée);
//Cette fonction retourne un tableau dont chaque case contient les informations d'une commande.
function queryKeysEntete($db){
  $tabEntete = [];//Tableau qui va contenir dans chaque case un tableau avec chaque commande.
  $resultEntete = $db->query("SELECT ord.id_order, ord.reference, cus.firstname, cus.lastname, cus.email, loc.address1, loc.postcode, loc.city, loc.phone, loc.id_country, loc.id_state, ord.date_add, ord.total_paid_tax_excl, ord.total_paid_tax_incl, ord.date_upd, ord.payment FROM ps_address AS loc INNER JOIN ps_customer AS cus ON cus.id_customer = loc.id_customer INNER JOIN ps_orders AS ord ON cus.id_customer = ord.id_customer WHERE ord.EXPORT_CDE = 0;");
  //On effectue la requête auprès de la base de données visant à récupérer les différents champs nécéssaires au logiciel ConviSAV
  $arrayEntete = $resultEntete->fetchAll(PDO::FETCH_ASSOC);//On fetch le résultat de la requête dans un tableau
  for ($i=0; $i < count($arrayEntete) ; $i++) {
    //En parcourant ce tableau, on va inscrire dans le tableau $tabEntete
    //le tableau de valeurs correspondant à chacune des commandes.
    //Cela va permettre de simplifier le script avec un tableau à 2 niveau (Niveau 1 -> Commande, Niveau 2 -> Valeurs)
    $tabEntete[] = $arrayEntete[$i];
  }
  return $tabEntete;//On retourne ensuite le tableau afin de pouvoir l'utiliser par la suite dans le script
}

//----------------- QUERY_DETAIL($DB) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction de requête sur la base de données pour le traitement du fichier commande_detail.csv
//Cette fonction permet de réccupérer dans un tableau toutes les champs nécéssaires au logiciel ConviSAV
//pour chaque commande dont l'état du champs EXPORT_CDE est de 0 (commande non traitée);
//Cette fonction marche de la même facon que la fonction queryEntete() renseignée plus haut.
//Cette fonction retourne un tableau dont chaque case contient les informations d'une commande.
function queryDetail($db){
  $tabDetail = [];
  $sqlDetail = $db->query("SELECT ord.id_order, ord.reference, ord.current_state, det.product_ean13, det.product_isbn, det.unit_price_tax_excl, tax.rate, det.total_shipping_price_tax_excl, det.total_shipping_price_tax_incl FROM ps_orders as ord INNER JOIN ps_order_detail as det on ord.id_order = det.id_order INNER JOIN ps_tax as tax on det.id_tax_rules_group = tax.id_tax WHERE ord.EXPORT_CDE = 0;");
  $arrayDetail = $sqlDetail->fetchAll(PDO::FETCH_NUM);
  for ($j=0; $j < count($arrayDetail) ; $j++) {
    $tabDetail[] = $arrayDetail[$j];
  }
  return $tabDetail;
}

//----------------- QUERY_DETAIL($DB) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction de requête sur la base de données pour le traitement du fichier commande_detail.csv
//Cette fonction permet de réccupérer dans un tableau toutes les champs nécéssaires au logiciel ConviSAV
//pour chaque commande dont l'état du champs EXPORT_CDE est de 0 (commande non traitée);
//Cette fonction marche de la même facon que la fonction queryEntete() renseignée plus haut.
//Cette fonction retourne un tableau dont chaque case contient les informations d'une commande.
function queryKeysDetail($db){
  $tabDetail = [];
  $sqlDetail = $db->query("SELECT ord.id_order, ord.reference, ord.current_state, det.product_ean13, det.product_isbn, det.unit_price_tax_excl, tax.rate, det.total_shipping_price_tax_excl, det.total_shipping_price_tax_incl FROM ps_orders as ord INNER JOIN ps_order_detail as det on ord.id_order = det.id_order INNER JOIN ps_tax as tax on det.id_tax_rules_group = tax.id_tax WHERE ord.EXPORT_CDE = 0;");
  $arrayDetail = $sqlDetail->fetchAll(PDO::FETCH_ASSOC);
  for ($j=0; $j < count($arrayDetail) ; $j++) {
    $tabDetail[] = $arrayDetail[$j];
  }
  return $tabDetail;
}

//----------------- FILE_TEST($LOOPER, $DIR) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction de test de fichier pour vérifier que les fichiers csv que nous souhaitons créer n'existe pas déja dans le dossier FTP
//Le dossier FTP est à renseigner en fonction de l'emplacement du dossier sur le serveur sur la machine.
//Si jamais le fichier de log existe (commande.log), on indique à l'utilisateur que le fichier existe à l'aide d'une alertBox()
//et on redirige l'utilisateur vers le backOffice du site web.
//Si le fichier n'existe pas, on vérifie si les fichiers CSV n'existe pas, s'ils existent, on les supprime du dossier
//car si le fichier de log n'existe pas, cela signifie que l'etat de la commande (EXPORT_CDE) n'as pas été modifié.
//La commande sera retraitée avec l'appel de ce script.
//Cette fonction ne retourne aucune valeur
function file_test($looper, $dir){//La variable $looper permet de savoir si le script doit être effectué en boucle ou une seule fois.
  if(file_exists($dir)){//Si le fichier log existe et que le script ne dois s'executer qu'une seule fois
    if($looper == "once"){
      //Alors on indique à l'utilisateur que le fichier existe et on stoppe l'éxécution du script
      echo "<script type=\"text/javascript\">alert(\"Le fichier commande.log existe dans le dossier FTP, une liste de commande n'as pas été intégrée dans ConviSAV !\");window.location.replace(\"http://localhost/admin796yw9jli/index.php?controller=AdminDashboard\"); </script>\n";
      exit();
    }
    else{
      return 1;
    }
  }
  else{//Sinon si les fichiers existent, on les supprime.
    if(file_exists($dir."commande_entete.csv")){
      unlink($dir."commande_entete.csv");
    }
    if(file_exists($dir."commande_detail.csv")){
      unlink($dir."commande_detail.csv");
    }
  }
}

//----------------- GET_KEYS($TAB) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction qui permet de transformer la requête des commandes à la base de données en un Tableau
//qui contient le nom de chacun des champs de la requête. Cette fonction retourne un tableau avec
//le nom des champs qui seront utilisés dans les fichiers commande_entete.csv et commande_detail.csv
//On ne récupère que la première case du tableau concerné.
function getKeys($tab){
  $keys = array_keys($tab[0]);//On utilise ici une fonction de php, array_keys() qui permet de récupérer
  //seulement le nom des champs d'un tableau qui seront stockées dans un autre tableau
  //Cette fonction va nous permettre de faire par la suite une ligne d'entete de fichier csv.
  return $keys;//On retourne ce tableau pour qu'il soit traité par la suite dans le reste du script.
}

//------------ MAKE_FIELD_LINE($KEYSLINE, $FILE) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction qui permet de transformer le tableau des entetes en ligne qui sera inscrite au début du fichier ($file)
//renseigné en paramètre de la fonction.
//Cette fonction balaye le tableau case par case et ajoute chaque case à la ligne à écrire.
//Cette ligne est ensuite écrite avec un encodage utf8 afin d'afficher correctement les accents dans le fichier correspondant
function make_keys_line($keysLine, $file){
  $lineChamps = "";//On créé une variable de chaine de caractère vide qui va nous permettre de stocker la ligne que nous allons écrire dans le fichier
  for ($c=0; $c < count($keysLine) ; $c++) {//Pour une valeur c égale au nombre de cases dans le tableau contenant les entetes de fichier csv
    for ($d=0; $d < count($keysLine[$c]) ; $d++) {//Pour une valeur d égale au nombre de cases pour une commande (soit égale au nombre de champs récupérés par la requête MySQL)
      if($d == (count($keysLine[$c])-1)){//Si on est à la dernière case du second tableau (celui du nom des entetes)
        $lineChamps = $lineChamps."\"".$keysLine[$c][$d]."\";";//On formate la ligne de sorte à afficher un point virgule en fin de ligne.
      }
      else{
        $lineChamps = $lineChamps."\"".$keysLine[$c][$d]."\", ";//Sinon on ajoute une virgule en fin de ligne afin de séparer clairement les champs
      }
    }
  }
  fwrite($file, utf8_encode($lineChamps));//Enfin on ajoute cette ligne au fichier csv correspondant ($file)
  fwrite($file, "\r\n");//Cette ligne est ajoutée avec un encodage utf8
}

//---------- MAKE_ARRAY_REFERENCE($TAB) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction qui permet de récupérer toutes les références dans le tableau provenant de la requête des commandes
//On pour chaque case du tableau, on teste si la seconde case (reference de la commande) n'est pas déja présente dans le Tableau
//afin d'éviter les doublons. (On ne souhaite avoir que des références unique afin de pouvoir les tester par la suite).
//Cette fonction retourne un tableau contenant les références unique dans chaque case.
function make_array_reference($tab){
  $references = [];//On initie un tableau de référence vide
  for ($a=0; $a < count($tab); $a++) {//Pour une valeur a égale au nombre de case du tableau contenant les différentes commandes
    for ($b=0; $b < count($tab[$a]) ; $b++) {//Pour une valeur b égale au nombre de cases du tabeau de chaque commande
      if(!in_array($tab[$a][1], $references)){//Si la référence du la commande n'est pas déja dans le tableau des références
        $references[] = $tab[$a][1];//Alors on ajoute cette référence dans le tableau des références
        //De cette manière, on s'assure que le tableau ne contient que des références uniques
      }
    }
  }
  return $references;//enfin on revoie le tableau des références
}

//------------- TEST_REFERENCE($REFERENCES, $TAB) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction de test de références.
//Cette fonction teste pour un tableau donné si la référence présente dans le tableau de références
//se trouve dans le tableau renseigné en paramètre de la fonction et que la même référence ne se trouve pas dans
//le tableau des références déja testés ($referencesOK), on met la reference dans le tableau $referencesOK
//Cette fonction retourne le tableau des références 'OK', qui ont donc été testées et qui sont présentes
//dans le tableau des entetes de commande.
function test_reference($references, $tab){
  $referencesOK = [];//On initie un tableau de références testées, ce tableau est vide.
  for ($i=0; $i < count($references) ; $i++) {//Pour une valeur i égale au nombre de références dans le tableau entré en paramètre
    for ($j=0; $j < count($tab) ; $j++) {//Pour une valeur j égale au nombre de case dans le tableau des commandes
      if(in_array($references[$i] , $tab[$j]) && !in_array($references[$i], $referencesOK)){
        //Si la référence est présente dans la case du tableau des commandes et que cette même référence n'est pas présente dans le tableau des références déja testées
        //alors on ajoute ce référence au tableau des références OK
        $referencesOK[] = $references[$i];
      }
    }
  }
  return $referencesOK;//enfin on retourne le tableau des références testées.
}

//------------ MAKE_FILES($DB, $REFERENCESOK, $TABENTETE, $FILEENTETE, $TABDETAIL, $FILEDETAIL) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction qui va permettre de remplir les 2 fichiers (commande_entete.csv et commande_detail.csv) en fonction
//des valeurs présentes dans les tableaux entrés en paramètre.
//Dans un premier temps, on teste de savoir si la reference de la commande traitée se trouve dans le tableau des références
//qui ont été testées auparavant. Si c'est le cas, on créé la ligne qui sera remplie dans le fichier d'entete.
//On écrit ensuite cette ligne dans le fichier d'entete et on passe à l'écriture de la première ligne du fichier de détail.
//Etant donné qu'il peut y avoir plusieurs lignes de détail pour la meme commande d'entete, on rempli d'abord la ligne
//du fichier d'entete et ensuite pour la référence du fichier d'entete (qui est unique), on rempli chaque ligne
//associée à cette référence dans le fichier de détail.
//On écrit la ligne de détail dans le fichier correspondant.
//Une fois cette référence traitée, on modifie l'état de la commande dans la base de données pour la passée en traitée
//(EXPORT_CDE = 1) pour seulement la référence en cours ($curRef).
//Cette fonction tourne en boucle tant que chaque commande présente dans le tableau $referencesOK n'est pas traitée
//Cette fonction ne retourne rien.


function make_files($db, $referencesOK, $tabEntete, $fileEntete, $tabDetail, $fileDetail){
  $lineEntete = "";//On initie deux variables de chaine de caractère qui vont stocker chacune des lignes à inscrire dans les fichiers csv passés en paramètre
  $lineDetail = "";
  $curRef = "";//On initie également une variable où on stockera la référence actuellement traitée
  for ($i=0; $i < count($tabEntete) ; $i++) {//Pour une valeur i égale au nombre de cases dans le tableau des entetes de commadne
    if(in_array($tabEntete[$i][1], $referencesOK)){//Si la référence de la commande est contenue dans le tableau des références testées
      $curRef = $tabEntete[$i][1];//On renseigne la référence en cours de traitement
      for ($k=0; $k < count($tabEntete[$i]) ; $k++) {//Pour une valeur k égale au nombre d'élément du tableau de la première commande
        switch ($k) {//On formate la ligne en fonction de la position du curseur
          case 0://Si c'est la première case du tableau, on ouvre les guillemets et on ajoute la valeur à la ligne
          $lineEntete ="\"".$tabEntete[$i][$k];
          break;
          case count($tabEntete[$i])-1://Si c'est la dernière valeur, on ajoute des guillemets, la valeur ainsi qu'un point virgule de fin de ligne
          $lineEntete =$lineEntete."\", \"".$tabEntete[$i][$k]."\";\r\n";
          break;
          default :
          $lineEntete = $lineEntete."\", \"".$tabEntete[$i][$k];//Sinon, pour toutes les autres valeurs, on ajoute une virgule et la valeur.
          break;
        }
      }
    }
    fwrite($fileEntete, utf8_encode($lineEntete));//On écrit cette ligne dans le fichier commande_entete.csv et on passe au traitement du tableau de détail
    for ($j=0; $j < count($tabDetail) ; $j++) {//Pour une valeur j égale au nombre de commandes présentes dans le tableau de détail
      if ($tabDetail[$j][1] == $curRef) {//Si la référence de la commande est égale à la référence de la commande actuellement traitée
        for ($k=0; $k < count($tabDetail[$j]) ; $k++) {//Pour une valeur k égale au nombre de cases présentes dans le tableau de la commande
          switch ($k) {//On formate la ligne en fonction de la position du curseur
            case 0://Si on est à la première case
            $lineDetails ="\"".$tabDetail[$j][$k];//Si c'est la première case du tableau, on ouvre les guillemets et on ajoute la valeur à la ligne
            break;

            case count($tabDetail[$j])-1:
            $lineDetails =$lineDetails."\", \"".$tabDetail[$j][$k]."\";\r\n";//Si c'est la dernière valeur, on ajoute des guillemets, la valeur ainsi qu'un point virgule de fin de ligne
            break;

            default :
            $lineDetails = $lineDetails."\", \"".$tabDetail[$j][$k];//Sinon, pour toutes les autres valeurs, on ajoute une virgule et la valeur.
            break;
          }
        }
        fwrite($fileDetail, utf8_encode($lineDetails));
      }
    }
    //Enfin pour la commande en cours, séléctionnée par la référence de la commande, on modifie l'état de la commande qui vient d'être traitée
    //en modifiant la valeur du champs EXPORT_CDE dans la base de données dans la table 'ps_orders'
    $SQLModifExport = "UPDATE ps_orders SET EXPORT_CDE = 1 WHERE reference = \"".$curRef."\";";
    $db->query($SQLModifExport);
  }
}

//------------ PRINT_HTML_LOW_SECTION_NO_SCRIPT($DB) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction d'affichage de la partie basse de la page html s'il n'y a pas de commande à traiter.
//On indique alors à l'utilisateur qu'il n'y a pas de commande à traiter (pas de commande dans 'ps_orders' avec comme valeur 0 dans le champ EXPORT_CDE).
//On ferme alors la connexion àa la base de données et on stoppe l'éxécution du script.
function print_html_low_section_no_script($db){
  $db = null;//On clos la connexion à la base de données
  //On renseigne également qu'aucune commande n'a été traitée et on redirige l'utilisateur vers le backOffice avec un script javascript dans le code html
  echo "<script type=\"text/javascript\">alert(\"Pas de commande à traiter !\");window.location.replace(\"http://localhost/admin796yw9jli/index.php?controller=AdminDashboard\"); </script>\n";
  echo "\t</body>\n";//On ferme la balise body
  echo "</html>\n";//Ainsi que la balise html
  exit();//On stoppe alors le fonctionnement du script
}

//------------ PRINT_HTML_LOW_SECTION_SCRIPT($DB, $REFERENCES) ---------------------------------------------------------------------------------------------------------------------------------------
//Fonction d'affichage de la partie basse de la page html s'il y a des commandes qui ont été traitées.
//On indique alors à l'utilisateur que le script a été éxécuté ainsi que le nombre de commandes qui ont été traitées.
//On ferme alors la connexion àa la base de données et on stoppe l'éxécution du script.
function print_html_low_section_script($db, $references){
  $db = null;//On clos la connexion à la base de données
  //On renseigne également qu'un certain nombre de commandes ont été traitées et on redirige l'utilisateur vers le backOffice avec un script javascript dans le code html
  echo "<script type=\"text/javascript\">alert(\"Script exécuté pour ".count($references)." commandes !\");window.location.replace(\"http://localhost/admin796yw9jli/index.php?controller=AdminDashboard\"); </script>\n";
  echo "\t</body>\n";//On ferme la balise body
  echo "</html>\n";//Ainsi que la balise html
  exit();//On stoppe alors le fonctionnement du script
}
?>
