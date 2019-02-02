<?php

$ftp = "C:/wamp/www/FTP/";
$article = "article/article";
$commande = "commande/commande";
$livraison = "livraison/livraison";


function getArticle(){
  global $article, $ftp;
  $articleCSV = $ftp.$article.".csv";
  return $articleCSV;
}
function getCommandeDir(){
  global $commande, $ftp;
  $commandeCSV = $ftp."commande/";
  return $commandeCSV;
}
function getLivraison(){
  global $livraison, $ftp;
  $livraisonCSV = $ftp.$livraison.".csv";
  return $livraisonCSV;
}

function getLOG($file){
  global $ftp, $article, $commande, $livraison;
  switch($file){
    case 'article':
      $log = $ftp.$article.".log";
      break;
    case 'commande':
      $log = $ftp.$commande.".log";
      break;
    case 'livraison':
      $log = $ftp.$livraison.".log";
      break;
    default:
      echo "Erreur lors de la récupération du dossier FTP";
      $log = "";
      exit();
      break;
  }
  return $log;
}

//--------------- CONNECTDB() -------------------------------------------------------------------------------------------------
//Fonction de connexion à la base de données, les identifiants sont à changer en fonction de ceux utilisés
//par la base de données.
//La fonction retourne la connexion à la base de données par la variable $conn. Cette variable est utilisée
//lors le chaque requête sur la base de données.
function connectDB(){
  try {//On tente la connexion avec un try pour prévenir d'éventuelles erreurs lors de l'éxécution du script.
    $servername = "localhost";//Nom du serveur (127.0.0.1)
    $username = "root";//Identifiant
    $pass = "";//Mot de Passe

    //On effectue la requête en PDO de connexion à la base de données
    $conn = new PDO("mysql:host=$servername;dbname=prestashop",$username,$pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (\Exception $e) {//On récupère les éventuelles erreurs que l'on pourra par exemple stocker dans un fichier de log par la suite
    echo "Erreur lors de la connexion à la base de données";
    exit();//En cas d'erreur à la connexion, on stoppe l'éxécution du script.
  }
    return $conn;//Sinon on retourne l'état de la base de données afin de permettre les différentes requêtes.
}

//------------ PRINT_HTML_HEADER_SECTION() ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//Fonction d'affichage du haut de la page html
//cette fonction, si elle est commentée permet d'afficher simplement le script dans un invite de commande sur windows.
function print_html_head_section(){
  echo "<html>\n";//On affiche d'abord le balise html
  echo "\t<body>\n";//ainsi qu'une balise body afin de structurer le code de la page
}


//------------- GET_CSV($FILE) ------------------------------------------------------------------------------------------
//Fonction qui permet de récupérer le contenu du fichier CSV dans un tableau que nous pourrons traiter par la suite dans le script
function get_csv($file){
  $csv = array_map('str_getcsv', file($file));//On utilise la fonction array_map() pour récupérer le contenu du fichier CSV dans un tableau
  //qui sera automatiquement arrangé pour rentrer chaque valeur dans un tableau $csv
    array_walk($csv, function(&$a) use ($csv) {//Pour chaque valeur du tableau, on applique la fonction $a définie en dessous pour le tableau $csv
     $a = array_combine($csv[0], $a);//La fonction array_combine($clé, $valeur) permet de créé un tableau en utilisant un tableau de clé et un tableau de valeur
    });
  array_shift($csv);//On applique maintenant la fonction array_shift pour retirer la première ligne du tableau, qui représente l'entete du fichier CSV
  return $csv;//Et on retourne le tableau ainsi créé.
}

?>
