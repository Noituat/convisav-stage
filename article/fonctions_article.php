<?php

//------------- PRINT_HTML_LOW_SECTION_NO_SCRIPT($DB) --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//Fonction d'affichage de la partie basse de la page html s'il n'y a pas de livraison à traiter.
//On indique alors à l'utilisateur qu'il n'y a pas fichier de log présent
//On ferme alors la connexion à la base de données et on stoppe l'éxécution du script.
function print_html_low_section_no_script($db){
  $db = null;//On clos la connexion
  //On renseigne que le fichier de log n'est pas présent et on redirige l'utilisateur vers le backOffice avec un script javascript dans le code html
  echo "<script type=\"text/javascript\">alert(\"Fichier non présent dans le répertoire\");window.location.replace(\"http://localhost/admin796yw9jli/index.php?controller=AdminDashboard\"); </script>\n";
  echo "\t</body>\n";//On ferme la balise body
  echo "</html>\n";//Ainsi que la balise html
  exit();//On stoppe alors le fonctionnement du script
}

//------------- PRINT_HTML_LOW_SECTION_SCRIPT($DB) ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//Fonction d'affichage de la partie basse de la page html s'il y a des livraison qui ont été traitées.
//On ferme alors la connexion àa la base de données et on stoppe l'éxécution du script.
function print_html_low_section_script($db){
  $db = null;//On clos la connexion à la base de données
  //On renseigne que le script a été executé et on redirige l'utilisateur vers le backOffice avec un script javascript dans le code html
  echo "<script type=\"text/javascript\">alert(\"Script exécuté\");window.location.replace(\"http://localhost/admin796yw9jli/index.php?controller=AdminDashboard\"); </script>\n";
  echo "\t</body>\n";//On ferme la balise body
  echo "</html>\n";//Ainsi que la balise html
  exit();//On stoppe alors le fonctionnement du script
}

//------------------------- ALTER_DATABASE($CSV, $db) ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//Fonction d'ajout/suppression de produit dans la base de données
//Cette fonction va nous permettre d'altérer les tables de la base de données afin d'ajouter de de modifier les produits grâce à un fichier CSV fourni par ConviSAV.
//Cette fonction prends en entrée le tableau contenant toutes les informations contenues dans le fichier CSV.
//Elle effectue les modifications en interne et ne renvoie pas de valeur en fin d'éxécution.
function alter_database($csv, $db){
  for ($i=0; $i < count($csv); $i++) {//Pour une valeur i égale au nombre de cases dans le tableau des produits
    if($csv[$i]["flag"] == "DEL"){//Si le flag du produit est "DEL"
      try{//On entre dans une boucle try/catch afin de pouvoir récupérer les erreurs de traitement et de les stocker dans un fichier de log si nécéssaire par la suite.
        //Dans un premier temps, cela nous servira à afficher les erreurs en période de test.

        //On récupère dans un premier temps l'ID et la référence du produit en cours de traitement.
        $test = "SELECT id_product, reference FROM ps_product WHERE reference = \"".$csv[$i]["reference"]."\";";
        $test = $db->query($test);//On envoie la requête à la base de données
        $test = $test->fetchAll(PDO::FETCH_ASSOC);//Enfin on fetch la réponse dans un tableau.
        if(!empty($test)){//Si ce tableau est vide, c'est que le produit en question n'existe pas, il ne peut alors pas être traité. On s'assure donc que ce tableau ne soit pas vide avant de traiter le produit en question

          //On commence par supprimer l'entrée du produit dans la table 'ps_category_product' en nous servant de l'ID comme clé unique de suppression.
          //On est obligés d'utiliser une jointure afin de pouvoir supprimer le produit car c'est avec l'ID du produit que nous séléctionnons le produit à modifier.
          $tableUPD = "DELETE FROM ps_category_product USING ps_category_product JOIN ps_product on ps_product.id_product = ps_category_product.id_product WHERE ps_product.reference = \"".$test[0]["reference"]."\";";
          $db->query($tableUPD);
          //On supprimer ensuite l'entrée du produit dans la table 'ps_product_shop' en effectuant également un jointure.
          $requeteShop = "DELETE FROM ps_product_shop USING ps_product_shop JOIN ps_product on ps_product.id_product = ps_product_shop.id_product WHERE ps_product.reference = \"".$test[0]["reference"]."\";";
          $db->query($requeteShop);
          //On supprimer ensuite l'entrée du produit dans la table 'ps_product_lang' en effectuant également un jointure.
          $requeteLang = "DELETE FROM ps_product_lang USING ps_product_lang JOIN ps_product on ps_product_lang.id_product = ps_product.id_product WHERE ps_product.reference = \"".$test[0]["reference"]."\";";
          $db->query($requeteLang);

          $requeteStock = "DELETE FROM ps_stock_available USING ps_stock_available JOIN ps_product on ps_stock_available.id_product = ps_product.id_product WHERE ps_product.reference = \"".$test[0]["reference"]."\";";
          $db->query($requeteStock);
          //On supprimer ensuite l'entrée du produit dans la table 'ps_product' qui est la dernière table à modifier afin de supprimer définitivement le produit.
          $requeteProduit = "DELETE FROM ps_product USING ps_product WHERE ps_product.reference = \"".$test[0]["reference"]."\";";
          $db->query($requeteProduit);
        }
      }
      catch(\Exception $e){
        //On récupère les éventuelles erreurs de traitement que nous pourrons stocker dans un fichier journal de log par la suite.
        echo $e;
      }
    }

    if($csv[$i]["flag"] == "STOCK"){
      $test = "SELECT id_product, reference FROM ps_product WHERE reference = \"".$csv[$i]["reference"]."\";";
      $test = $db->query($test);//On envoie la requête à la base de données
      $test = $test->fetchAll(PDO::FETCH_ASSOC);//Enfin on fetch la réponse dans un tableau.
      if(!empty($test)){//Si ce tableau est vide, c'est que le produit en question n'existe pas, il ne peut alors pas être traité. On s'assure donc que ce tableau ne soit pas vide avant de traiter le produit en question
        $requeteProduit = "UPDATE ps_product SET quantity = ".$csv[$i]["quantity"]." WHERE id_product = ".$test[0]["id_product"].";";
        $db->query($requeteProduit);

        $requeteStock = "UPDATE ps_stock_available SET quantity = ".$csv[$i]["quantity"].", physical_quantity = ".$csv[$i]["quantity"]." WHERE id_product = ".$test[0]["id_product"].";";
        $db->query($requeteStock);
      }
    }

    if(($csv[$i]["flag"] != "DEL") && ($csv[$i]["flag"] != "STOCK") ){//Si le flag est égal à "ADD"

      $date  = date("Y-m-d h:i:s");//On récupère la date au format utilisé par la base de données du site
      try {//On entre dans une boucle try/catch afin de pouvoir récupérer les erreurs de traitement et de les stocker dans un fichier de log si nécéssaire par la suite.
        //Dans un premier temps, cela nous servira à afficher les erreurs en période de test.
        //On récupère dans un premier temps l'ID et la référence du produit en cours de traitement.
        $test = "SELECT id_product, reference FROM ps_product WHERE reference = \"".$csv[$i]["reference"]."\";";
        $test = $db->query($test);//On envoie la requête à la base de données
        $test = $test->fetchAll(PDO::FETCH_ASSOC);//Enfin on fetch la réponse dans un tableau.
        if(!empty($test)){//Si ce tableau est vide, c'est que le produit en question n'existe pas, il ne peut alors pas être traité. On s'assure donc que ce tableau ne soit pas vide avant de traiter le produit en question
          //Ici, si le tableau n'est pas vide, on va modifier les valeurs du produit qui est déja présent dans la table avec la même référence.
          //On va modifier dans la table 'ps_product' les différentes valeurs présentes dans le tableau CSV.
          $requeteProduit = "UPDATE ps_product SET ean13 = \"".$csv[$i]["ean13"]."\", price = ".$csv[$i]["prix"].", date_upd = \"$date\" WHERE reference = \"".$csv[$i]["reference"]."\";";
          $db->query($requeteProduit);
          //On va modifier dans la table 'ps_product_lang' les différentes valeurs présentes dans le tableau CSV.
          $requeteLang = "UPDATE ps_product_lang SET link_rewrite = \"".$csv[$i]["reference"]."\" ,name = \"".$csv[$i]["libelle"]."\" WHERE id_product = ".$test[0]["id_product"].";";
          $db->query($requeteLang);
          //On va modifier dans la table 'ps_product_shop' les différentes valeurs présentes dans le tableau CSV.
          $requeteShop = "UPDATE ps_product_shop SET price = ".$csv[$i]["prix"].", available_date = \"$date\", date_udp = \"$date\" WHERE id_product = \"".$test[0]["id_product"]."\";";
          $db->query($requeteShop);

        }
        else{
          //Si le tableau est vide c'est qu'aucune référence identique n'a été trouvée dans la table et donc que le produit n'existe pas. Il faut donc le créé.
          //On va d'abord ajouter dans la table
          //On va ajouter dans la table 'ps_product' une entrée pour la référence en cours avec ses différentes valeurs présentes dans le tableau CSV.
          $requeteProduit = "INSERT INTO";
          $requeteProduit = $requeteProduit." ps_product (id_supplier, id_manufacturer, id_category_default, id_shop_default, id_tax_rules_group, on_sale, online_only, ean13, isbn, upc, ecotax, minimal_quantity, low_stock_threshold, low_stock_alert, price, wholesale_price, unity, unit_price_ratio, additional_shipping_cost, reference, supplier_reference, location, width, height, depth, weight, out_of_stock, additional_delivery_times, quantity_discount, customizable, uploadable_files, text_fields, active, redirect_type, id_type_redirected, available_for_order, available_date, show_condition, show_price, indexed, visibility, cache_is_pack, cache_has_attachments, is_virtual, cache_default_attribute, date_add, date_upd, advanced_stock_management, pack_stock_type, state) ";
          $requeteProduit = $requeteProduit."VALUES (0, 0, 2, 1, 1, 0, 0, \"".$csv[$i]["ean13"]."\", \"\", \"\", 0.00000, 1, null, 0, ".$csv[$i]["prix"].", 0.00000, null, 0.00000, 0.00, \"".$csv[$i]["reference"]."\", null, null, 0.00000,  0.00000, 0.00000, 0.00000, 2, 1, 0, 0, 0, 0, 1, \"404\", 0, 1, \"$date\", 0, 1, 1, \"both\", 0, 0, 0, 0, \"$date\", \"$date\", 0, 3, 1);";
          $db->query($requeteProduit);
          //L'ID dans la table 'ps_product' est un auto-increment, nous devons donc récupérer la valeur qui vient d'être ajoutée au produit avec comme critère de recherche, la référence du produit que nous venons d'ajouter.
          $id = $db->query("SELECT id_product FROM `ps_product` WHERE reference = \"".$csv[$i]["reference"]."\";");
          $id = $id->fetchAll(PDO::FETCH_ASSOC);
          $id = $id[0]["id_product"];
          //On va ajouter dans la table 'ps_product_lang' une entrée pour la référence en cours avec ses différentes valeurs présentes dans le tableau CSV.
          $requeteLang = "INSERT INTO ps_product_lang";
          $requeteLang = $requeteLang." VALUES($id, 1, 1, \"<p>Produit importé par ConviSAV</p>\", \"<p>Produit importé par ConviSAV</p>\", \"produit-$id-\", \"\", \"\", \"\", \"".$csv[$i]["libelle"]."\", \"\", \"\", \"\", \"\");";
          $db->query($requeteLang);
          //On va ajouter dans la table 'ps_category_product' une entrée pour la référence en cours avec ses différentes valeurs présentes dans le tableau CSV.
          $tableUPD = "INSERT INTO ps_category_product (id_category, id_product) VALUES (2, $id);";
          $db->query($tableUPD);
          //On va ajouter dans la table 'ps_product_shop' une entrée pour la référence en cours avec ses différentes valeurs présentes dans le tableau CSV.
          $requeteShop = "INSERT INTO ps_product_shop";
          $requeteShop = $requeteShop." VALUES($id, 1, 2, 1, 0, 0, 0.00000, 1, null, 0, ".$csv[$i]["prix"].", 0.00000, null, 0.00000, 0.00, 0, 0, 0, 1, \"404\", 0, 1, \"$date\", 0, \"new\", 1, 1, \"both\", 0, 0, \"$date\", \"$date\", 3);";
          $db->query($requeteShop);

          $requeteStock = "INSERT INTO ps_stock_available (id_product, quantity, physical_quantity, id_product_attribute, id_shop, id_shop_group)";
          $requeteStock = $requeteStock." VALUES (".$id.", ".$csv[$i]["quantite"].", ".$csv[$i]["quantite"].", 0, 1, 0)";
          $db->query($requeteStock);
        }
      }
      catch(\Exception $e){
        echo $e;
      }
    }
  }

}
?>
