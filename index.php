<?php
require_once "connection.php";

session_start();

$materials = array();
if(!isset($_SESSION['selectedMaterials'])){$selectedMaterials = array();}

$select_stmt = $db->prepare("SELECT id, isim, kategori FROM malzemeler ORDER BY RAND() LIMIT 14"); //14 tane random malzemeyi materials arrayine at
$select_stmt->execute();
$rows = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $material){
    array_push($materials, $material);
}

if (isset($_POST['foodSearch'])) {
    if(sizeof($_SESSION['selectedMaterials']) < 1){
        echo '<div class="alert alert-danger" role="alert"> Önce Malzeme Seçmelisin! </div>';
    }else{
        header('location: results.php?mats='. implode("-", $_SESSION['selectedMaterials']));
    }
}

if (isset($_POST['selectMaterial'])){ //malzemeler için onclick event
    $selectedMaterials = explode("-", $_POST['selectMaterial']);
    foreach ($selectedMaterials as $selectedMaterial){
        if(!in_array($selectedMaterial, $_SESSION['selectedMaterials'])){
            array_push($_SESSION['selectedMaterials'], $selectedMaterial);
        }
    }
}
if (isset($_POST['deselectMaterial'])){ //seçili malzemeler için onclick event
    $arr = array();
    array_push($arr, $_POST['deselectMaterial']);
    $_SESSION['selectedMaterials'] = array_diff( $_SESSION['selectedMaterials'], $arr);
}

if (isset($_POST['materialSearch'])){ //ara butonu için onclick event                
    $materials = [];               
    $select_stmt = $db->prepare("SELECT id, isim, kategori FROM malzemeler WHERE isim LIKE :searchW");
    $select_stmt->execute(['searchW' => '%'.$_POST['materialSearch'].'%']);
    $rows = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $material) {
        array_push($materials, $material);
    }
} 
else if (isset($_GET['viewsCat'])) { //malzemeler için kategori seçilmiş ise o kategorinin malzemelerini materials arrayine at
    $materials = [];
    $select_stmt = $db->prepare("SELECT id, isim, kategori FROM malzemeler WHERE kategori = :category");
    $select_stmt->execute(['category' => $_GET['viewsCat']]);
    $rows = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $material) {
        array_push($materials, $material);
    }
}
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
    <link rel="stylesheet" href="index.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway" />
</head>
<body>
    <div class="material_field">
        <div class="all_materials">
            <?php
                foreach($materials as $material){  //materials arrayindeki malzemeleri yazdır (kategori ve seçili olma durumlarına göre arkaplan rengi ver)
                    if ($material['kategori'] == 'Kasap') {$color = 'rgba(247, 60, 60, 0.89)';}
                    elseif ($material['kategori'] == 'Manav') {$color = 'rgba(70, 228, 117, 0.72);';}
                    elseif ($material['kategori'] == 'Market') {$color = 'rgba(39, 108, 211, 0.733)';}
                    else{$color = 'rgba(66, 66, 66, 0.89)';}

                    echo '<form method="POST" >';
                    if(!isset($selectedMaterials)){$selectedMaterials = array();}
                    $selectedMaterialsStr = implode('-', $selectedMaterials);
                    if(strlen($selectedMaterialsStr) > 0) {
                        echo '<button name="selectMaterial" type="submit" value="' . $selectedMaterialsStr . "-" . $material['isim'] . '"' . 'class="material" id="' . $material['isim'] . '"' . ' style="background-color:' . $color . ';">' . '<a>' . $material['isim'] . '</a></button>';
                    }else{
                        echo '<button name="selectMaterial" type="submit" value="' . $material['isim'] . '"' . 'class="material" id="' . $material['isim'] . '"' . ' style="background-color:' . $color . ';">' . '<a>' . $material['isim'] . '</a></button>';
                    }
                    echo '</form>';
                }

                if (isset($_POST['selectMaterial']) or isset($_GET['viewsCat']) or isset($_POST['materialSearch'])){}
                else{
                    echo '<div class="material dots"> . . . </div>';
                }
                ?>
                <div class="blankRow" style="width: 100%; height:1px;"></div>
                <div class="materialSearchContainer">
                    <form method="POST">
                        <input placeholder="Malzeme ara" type="text" id="materialSearch" class="materialSearch" name="materialSearch"> 
                            <span class="searchicon"></span> 
                            <input class="btn btn-primary btn-sm" type="submit" name="button" id="SearchBtn" value="Ara"></input>
                            <div class="icon-holder">
                                <div onClick="location.href='index.php?viewsCat=market'" class="icon" id="Market"> <img src="./icons/market.png" alt="Market"><div class="tooltip"> Tüm market malzemeleri </div></div>
                                <div onClick="location.href='index.php?viewsCat=kasap'" class="icon" id="Kasap"> <img src="./icons/kasap.png" alt="Kasap"><div class="tooltip"> Tüm kasap malzemeleri </div></div>
                                <div onClick="location.href='index.php?viewsCat=manav'" class="icon" id="Manav"> <img src="./icons/manav.png" alt="Manav"><div class="tooltip"> Tüm manav malzemeleri </div></div>
                            </div>
                        </input>
                    </form>
                </div>

        </div>
        <div style="width: fit-content; height: fit-content; margin: auto;">
            <H2 style="margin: auto; width: fit-content;">Seçili Malzemeler</H2> <p style="margin: auto; width: fit-content;">(silmek için tıkla)</p>
            <div class="selected_materials">
                <?php
                    if(isset($_SESSION['selectedMaterials'])){
                        foreach($_SESSION['selectedMaterials'] as $material){  //seçili malzemeleri yazdır

                            echo '<form method="POST" >';
                            echo '<button name="deselectMaterial" type="submit" value="' . $material . '"' . 'class="selectedMaterial" id="' . $material . '_selected"' . '>' . '<a>' . $material . '</a></button>';
                            echo '</form>';
                        }    
                    }
                ?>
            </div>
        </div>
    </div>
    <div class="findFoodBtn-wrapper">
        <form method="POST">
            <button class="btn btn-outline-primary btn-lg" type="submit" name="foodSearch">Seçili Malzemeleri İçeren Yemekleri Bul</button>
        </form>
    </div>
</body>
</html>
