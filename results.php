<?php
require_once "connection.php";

session_start();

$resultIds = array();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarifmatik</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="global.css"/>
    <link rel="stylesheet" href="results.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    <a class="backBtn" href="index.php"> <span class="material-symbols-outlined">arrow_back</span> Geri Dön</a>
    <style>
    .material-symbols-outlined {
    font-variation-settings:
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 48
    }
    </style>
    <div class="results-wrapper">
        <?php

        if(!isset($_GET['mats'])){
            header('Location: index.php');
        }else{
            
            $selectedMaterials = explode("-", $_GET['mats']);
            foreach($selectedMaterials as $material){
                $select_stmt = $db->prepare("SELECT id FROM malzemeler WHERE isim = :name");     //GELEN MALZEME İSMİNDEN IDYİ CEK
                $select_stmt->execute(['name' => $material]);
                $materialId = $select_stmt->fetch(PDO::FETCH_ASSOC);                                 

                $select_stmt = $db->prepare("SELECT yemekId FROM yemeklervemalzemeleri WHERE malzemeId = :materialId");
                $select_stmt->execute(['materialId' => $materialId['id']]);                     //MALZEME İDSİNE GÖRE BULUNDUGU YEMEKLERİN İDLERİNİ CEK $resultIds'E PUSHLA
                $resultIdsR = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($resultIdsR as $result) {
                    if(!in_array($result['yemekId'], $resultIds)){
                        array_push($resultIds, $result['yemekId']);
                    }
                }
            }
            foreach($resultIds as $resultId){
                $select_stmt = $db->prepare("SELECT isim, kategori, tarif FROM yemekler WHERE id = :id");    //RESULTIDLERİ KULLANARAK İSİM, KATEGORİ, TARİF ÇEK
                $select_stmt->execute(['id' => $resultId]);
                $result = $select_stmt->fetch(PDO::FETCH_ASSOC);   

                $select_stmt = $db->prepare("SELECT malzemeId FROM yemeklervemalzemeleri WHERE yemekId = :id"); //YEMEK IDSİ İLE TÜM GEREKLİ MALZEMELERİN İDLERİNİ CEK
                $select_stmt->execute(['id' => $resultId]);
                $reqMaterialIds = $select_stmt->fetchAll(PDO::FETCH_ASSOC);

                if(!isset($reqMaterials)){$reqMaterials = array();}
                foreach ($reqMaterialIds as $reqMaterialId) {
                    $select_stmt = $db->prepare("SELECT isim FROM malzemeler WHERE id = :id"); //GEREKLİ MALZEMELERİN İDLERİNİ KULLANARAK İSİMLERİNİ ÇEK VE $reqMaterials'a pushla
                    $select_stmt->execute(['id' => $reqMaterialId['malzemeId']]);
                    $reqMaterial = $select_stmt->fetch(PDO::FETCH_ASSOC);
                    if (!in_array($reqMaterial['isim'], $reqMaterials)) {
                        array_push($reqMaterials, $reqMaterial['isim']);
                    }
                }

                ?>
                <div class="result-wrapper">
                    <div class="left-wrapper">
                        <div class="name"> <?=$result['isim']?> </div>
                        <div class="category"> (<?=$result['kategori']?>) </div>
                    </div>
                    <div class="right-wrapper">
                        <div class="recipe"> <?=$result['tarif']?> </div>
                        <div class="reqMaterials"> <?php foreach($reqMaterials as $reqMaterial){echo "<div class='reqMaterial'>" . $reqMaterial . "</div>";} ?> </div>
                    </div>
                </div>
                <?php
            }

        }
        ?>

    </div>
</body>
</html>