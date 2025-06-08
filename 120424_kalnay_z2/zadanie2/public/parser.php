<?php
ini_set('display_errors', 1);
ini_Set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('index.php');



function parseCSV($db,$filename){
    $handle = fopen($filename,'r');
    $data = array();

    while(($row = fgetcsv($handle,0,';'))!== FALSE){
        $data[] = array_filter($row);
      /*  $sex = substr($row[4], -1); // csak az utolsó karakter
        //1528-1752 fyzika
        //1754-1947 chemia //meg orszag mapot kell csinalni
        //1949-2175 medicina
        //2177-2296 literatura
       //2580-2719 mier
      //megvannak a orszagokkal osszekotve a prize meg a tobbi cucc meg kell
        if($row[1]==NULL){
            $name = NULL;
        }
        else{$name = $row[1];}
        if($row[2]==NULL){
            $surname = NULL;
        }
        else{$surname = $row[2];}
        if($row[3]==NULL){
            $organisation= NULL;
        }
        else{$organisation = $row[3];}
        if($row[6]==NULL){
            $death = NULL;
        }
        else{$death = $row[5];}
        insertNobel($db,$name,$surname,$organisation,$sex,$row[5],$death);*/
        //insertCountry($db, $row[1]);
        //connectCountryPerson($db,$row[1],$row[2]);
        //insertPrizeDetails($db,$row[0],$row[1],$row[2],$row[3]);
        //insertPrize($db,$row[0],$row[1],$row[2],$row[3],NULL);
        //connectPersonPrize($db,$row[0],$row[1]);
    }

    fclose($handle);
    unset($data[0]);
    return $data;

}

//$person = parseCSV($db,'nobel_v5.csv');//fyzika
//$person = parseCSV($db,'przemapFyzika.csv');

//$person = parseCSV($db,'country.csv');
//$person = parseCSV($db,'personcountrymap5.csv');
//$person = parseCSV($db, 'countries5.csv');

echo "<pre>";
print_r($person);
echo"</pre>";