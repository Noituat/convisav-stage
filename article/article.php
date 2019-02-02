<?php
include 'fonctions_article.php';//Inclusion du fichier contenant toutes les fonctions relatives au bon fonctionnement du script
include '../COMMUN.php';

$csvDir = getArticle();
$log = getLog("article");


//On affiche désormais le haut de la page html
print_html_head_section();

$looper = $_GET['loop'];
$timer = $_GET['pause'];

$conn = connectDB();//On effectue une connexion à la base de données
date_default_timezone_set("Europe/Paris");//Modification de la zone géographique du serveur web. Cela va nous permettre de mettre l'heure exacte dans les champs qui le requiert pour l'ajout et la modification des produits dans la base de données.

while(true){

  //Si le fichier article.log existe dans le dossier FTP/article, on peut traiter le fichier article.csv car on est sur qu'il existe.
  if(file_exists($log)){
    $csv = get_csv($csvDir);//On appelle la fonction get_csv() qui va nous permettre de générer un tableau contenant toutes les valeurs des produits présents dans le fichier CSV
    alter_database($csv, $conn);//On effectue la modification de la base de données pour chacun des articles présents dans le tableau.
    unlink($csvDir);//On supprime alors le fichier CSV
    unlink($log);//Puis le fichier de LOG.
    if(isset($timer) && $looper == "loop"){
      sleep($timer);
    }
  }
  else{//Si le fichier n'est pas présent dans le dossier lors de l'appel du script
    if($looper == "once"){
      print_html_low_section_no_script($conn);//On affiche le bas de la page html avec indication que le fichier n'est pas présent et on clos l'éxécution du script
    }
  }
  if($looper == "once"){
    print_html_low_section_script($conn);//On affiche alors le bas de la page html avec indication que le script a bien été exécuté et on clos l'éxécution du script
  }
}
?>
