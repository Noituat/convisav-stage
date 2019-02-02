<?php
include 'fonctions_livraison.php';//Inclusion du fichier contenant toutes les fonctions relatives au bon fonctionnement du script
include '../COMMUN.php';

$csvFile = getLivraison();
$log = getLog("livraison");
$conn = connectDB();//On effectue une connexion à la base de données



//Variables passées en paramètre à l'appel du script, la valeur $looper permet de définir si le script doit être éxécuté en boucle ou
//de façon unique. La variable $timer définit le temps de pause entre 2 éxécution du script le temps est en secondes et peut être modifiée
//dans le fichier nav.tpl qui renseigne les liens affichés dans le backOffice du site
$looper = $_GET['loop'];
$timer = $_GET['pause'];

print_html_head_section();//Affichage de la page web


while(true){//Boucle d'éxécution du fichier, si $looper est différent de "loop", la boucle sera stoppée en fin de première éxécution

  if(file_exists("$log")){
    //Si le fichier de log existe dans le dossier FTP, cela signifie que le fichier CSV existe également puisqu'il est crée si et seulement si le ficier CSV est correctement créé
    $csv = get_csv($csvFile);//On transforme le fichier CSV en tableau qu'il sera possible de manipuler avec le script
    update_status($csv, $conn);//On modifie alors l'état de la commande en fonction des valeurs renseignées dans le fichier CSV
    //On supprime alors les 2 fichiers en commençant toujours par le fichier CSV
    unlink($csvFile);
    unlink($log);
    //Si un timer a été renseigné en paramètre du script, on l'applique
    if(isset($timer) && $looper == "loop"){
      sleep($timer);
    }
  }
  else{// Si le fichier de log n'existe pas, il n'y a pas de livraison à traiter alors on affiche le bas de la page et on stoppe l'éxécution du script
    if($looper == "once"){
      print_html_low_section_no_script($conn);
    }
  }
  if($looper == "once"){
      //Si le script a été appelé a ne s'executer qu'une seule fois, on affiche le bas de la page, ce qui a pour effet de stopper le fonctionnement du script
    print_html_low_section_script($conn);
  }
}
?>
