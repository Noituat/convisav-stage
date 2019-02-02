<?php
include 'fonctions_commande.php';//Inclusion du fichier contenant toutes les fonctions relatives au bon fonctionnement du script
include '../COMMUN.php';

$csvDir = getCommandeDir();
$logFile = getLog("commande");

//Affichage de la page web
print_html_head_section();

//Variables passées en paramètre à l'appel du script, la valeur $looper permet de définir si le script doit être éxécuté en boucle ou
//de façon unique. La variable $timer définit le temps de pause entre 2 éxécution du script le temps est en secondes et peut être modifiée
//dans le fichier nav.tpl qui renseigne les liens affichés dans le backOffice du site
$looper = $_GET['loop'];
$timer = $_GET['pause'];

//On effectue une connexion à la base de données
$conn = connectDB();
//On teste alors si le champs EXPORT_CDE existe dans la base de données dans la table 'ps_orders'
testField($conn, "EXPORT_CDE");

while(true){//Boucle d'éxécution du fichier, si $looper est différent de "loop", la boucle sera stoppée en fin de première éxécution
  $fileValue = file_test($looper, $logFile);//On teste la présence des fichiers dans le dossier FTP/commande
  if($fileValue == 1){
    break;
  }
  else{
    //On effectue alors les requêtes MySQL auprès de la base de données pour le fichier d'entete et le fichier de détail
    $tabEntete = queryEntete($conn);
    $tabDetail = queryDetail($conn);

    $tabKeysEntete = queryKeysEntete($conn);
    $tabKeysDetail = queryKeysDetail($conn);
    //Ces requêtes vont permettre de créer des lignes de caractère qui seront inscrites dans les fichiers csv mais également
    //de formater une ligne d'entete pour chaque fichier csv en fonction des différents champs qui auront été récupérés par la requête MySQL
    if(!empty($tabEntete) && !empty($tabDetail)){//Si aucun des tableaux n'est vide,
      //Alors on créé les fichiers dans le dossier FTP (Celui ci est à modifier pour coller avec le répertoire du dossier sur le serveur)
      $entete = fopen($csvDir."commande_entete.csv", 'w+');
      $detail = fopen($csvDir."commande_detail.csv", 'w+');

      //On initie plusieurs tableaux :
        //References : tableau contenant toutes les références dans le tableau destiné au fichier commande_entete.csv
        //ReferencesOK : tableau contenant toutes les références téstées qui sont présentes dans les tableaux entete et détail
        //KeysEntete : tableau contenant les entetes pour le fichier commande_entete.csv
        //KeysDetail : tableau contenant les entetes pour le fichier commande_detail.csv
      $references = [];
      $referencesOK = [];

      $keysEntete = [];
      $keysDetail = [];

      //On stocke ensuite les clés des requêtes MySQL afin de pouvoir formater les lignes d'entete pour les fichiers csv
      $keysEntete[] = getKeys($tabKeysEntete);
      $keysDetail[] = getKeys($tabKeysDetail);

      //On formate donc les lignes d'entete avec la fonction make_keys_line présente dans le fichier de fonctions php inclut au début du script
      make_keys_line($keysEntete, $entete);
      make_keys_line($keysDetail, $detail);

      //On récupère maintenant les toutes les références de la requête MySQL pour les entetes de commande
      $references = make_array_reference($tabEntete);
      //Et on teste les références afin de savoir si elles sont présentes au moins une fois dans les 2 tableaux entete et détail.
      //En effet il ne faut pas traiter une commande si elle ne possède pas de ligne dans le tableau détail.
      //Le résultat de cette commande est stocké dans le tableau des références téstées.
      $referencesOK = test_reference($references, $tabDetail);

      //On procède alors à l'écriture des lignes des fichiers CSV
      make_files($conn, $referencesOK, $tabEntete, $entete, $tabDetail, $detail);

      //Une fois l'écriture terminée, on ferme les 2 fichiers
      fclose($entete);
      fclose($detail);
      //Et à ce moment la uniquement, on créé le sémaphore, un fichier .log qui permettra de savoir si les commandes ont finies d'être exportées
      $log = fopen($logFile, 'w+');
      fclose($log);//On le ferme tout de suite après

      //Si une variable $timer a été renseignée lors de l'appel du script, on fait une pause égale au temps renseigné en secondes
      if(isset($timer) && $looper == "loop"){
        sleep($timer);
      }
      //Si le script a été appelé a ne s'executer qu'une seule fois, on affiche le bas de la page, ce qui a pour effet de stopper le fonctionnement du script
      if($looper == "once"){
        $conn = print_html_low_section_script($conn, $references);
      }
    }
    else{//Si un des 2 tableaux est vide, le second doit l'être également, donc on sort du script et on renseigne qu'aucune commande n'a été traitée.
      if($looper == "once"){
        $conn = print_html_low_section_no_script($conn);
      }
    }
  }
}//Fin du script.
?>
