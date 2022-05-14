<?php 
echo !defined("ADMIN") ? die("Görüntüleme Yetkiniz Bulunmamaktadır.") : null;  

$fn = new Fonksiyonlar();
$vt = new VeriTabani();

$SQL_tum_personel = <<< SQL
SELECT
    id
    ,adi
    ,soyadi
FROM
    tb_personel AS p
WHERE
    p.firma_id  = ? AND 
    p.aktif     = 1 
SQL;

    //Giriş Yapmış ama Apmış ama çıkış yapmamış suan çalışan personel 
$SQL_icerde_olan_personel = <<< SQL
SELECT
    p.id
FROM
tb_personel AS p
INNER JOIN tb_giris_cikis AS gc ON gc.personel_id = p.id
WHERE
    p.firma_id     = ? AND 
    gc.tarih       = ? AND 
    gc.baslangic_saat IS NOT NULL AND 
    gc.bitis_saat     IS NULL AND 
    p.aktif        = 1 AND
    gc.aktif       = 1
GROUP BY p.id
SQL;

//Giriş Yapmış ama Apmış ama çıkış yapmamış suan çalışan personel 
$SQL_tutanak_oku = <<< SQL
SELECT
    p.id AS personel_id,
    p.adi,
    p.soyadi,
    t.tarih,
    t.saat,
    t.tip,
    t.id AS tutanak_id
FROM tb_tutanak as t
INNER JOIN tb_personel AS p ON p.id = t.personel_id
WHERE 
    t.firma_id  = ? AND
    t.tip       = ? AND
    p.aktif     = 1 AND 
    t.yazdirma  != 1 AND
    t.id not  IN (SELECT tutanak_id FROM tb_tutanak_dosyalari)
SQL;


//Giriş Yapmış ama Apmış ama çıkış yapmamış suan çalışan personel 
$SQL_yazdirilan_tutanak_oku = <<< SQL
SELECT
    p.id AS personel_id,
    p.adi,
    p.soyadi,
    t.tarih,
    t.saat,
    t.tip,
    t.id AS tutanak_id
FROM tb_tutanak as t
INNER JOIN tb_personel AS p ON p.id = t.personel_id
WHERE 
    t.firma_id  = ? AND
    t.tip       = ? AND
    p.aktif     = 1 AND 
    t.yazdirma  = 1 AND
    t.id not  IN (SELECT tutanak_id FROM tb_tutanak_dosyalari)
SQL;


//personele ait tutanak olusturma işlemi başlatıldı mı
$SQL_tutanak_varmi = <<< SQL
SELECT
    *
FROM
    tb_tutanak 
WHERE
    
    firma_id    = ? AND 
    personel_id = ? AND 
    tarih       = ? AND 
    tip         = ?
SQL;

//Tek personele ait tutanak listesi
$SQL_tek_tutanak_oku = <<< SQL
SELECT
    p.id AS personel_id,
    p.adi,
    p.soyadi,
    t.tarih,
    t.saat,
    t.tip,
    t.id AS tutanak_id
FROM tb_tutanak as t
INNER JOIN tb_personel AS p ON p.id = t.personel_id
WHERE 
    t.tip       = ? AND
    p.id        = ? AND
    t.tarih     = ? AND
    p.aktif     = 1 AND 
    t.id IN (SELECT tutanak_id FROM tb_tutanak_dosyalari)
SQL;


//İzinli Olan Veya gelip çıkış yapan personel sayısı 
$SQL_izinli_cikan_personel = <<< SQL
SELECT
    p.*
FROM
    tb_personel AS p
INNER JOIN tb_giris_cikis AS gc ON gc.personel_id = p.id
WHERE
    p.firma_id    = ? AND 
    gc.tarih      = ? AND 
    gc.baslangic_saat IS NOT NULL AND 
    gc.bitis_saat     IS NOT NULL AND 
    p.aktif       = 1 AND
    gc.aktif      = 1
GROUP BY p.id
ORDER BY adi ASC
SQL;

    //Belirli tarihe göre giriş çıkış yapılan saatler 
$SQL_belirli_tarihli_giris_cikis = <<< SQL
SELECT
    gc.id
    ,gc.baslangic_saat
    ,gc.bitis_saat
    ,gc.baslangic_saat_guncellenen
    ,gc.bitis_saat_guncellenen
    ,gc.islem_tipi
FROM
    tb_giris_cikis AS gc
LEFT JOIN tb_personel AS p ON gc.personel_id =  p.id
WHERE
    gc.personel_id  = ? AND 
    gc.tarih        = ? AND
    p.firma_id      = ? AND 
    gc.aktif        = 1
ORDER BY baslangic_saat ASC 
SQL;

$tum_personel                           = $vt->select( $SQL_tum_personel,array( $_SESSION[ "firma_id" ] ) ) [2];
$icerde_olan_personel                   = $vt->select( $SQL_icerde_olan_personel,array( $_SESSION[ "firma_id" ], date( "Y-m-d" ) ) ) [2];

//tutanak dosyası oluşturulmayan personel listesi
$gelmeyen_tutanak_listesi               = $vt->select( $SQL_tutanak_oku,array( $_SESSION[ "firma_id" ], "gunluk" ) ) [2];
$gecgelen_tutanak_listesi               = $vt->select( $SQL_tutanak_oku,array( $_SESSION[ "firma_id" ], "gecgelme" ) ) [2];
$erkencikan_tutanak_listesi             = $vt->select( $SQL_tutanak_oku,array( $_SESSION[ "firma_id" ], "erkencikma" ) ) [2];

//Yazdırma İşlemi Yapılan Tutanaklar Listesi
$yazdirilan_gelmeyen_tutanak_listesi    = $vt->select( $SQL_yazdirilan_tutanak_oku,array( $_SESSION[ "firma_id" ], "gunluk" ) ) [2];
$yazdirilan_gecgelen_tutanak_listesi    = $vt->select( $SQL_yazdirilan_tutanak_oku,array( $_SESSION[ "firma_id" ], "gecgelme" ) ) [2];
$yazdirilan_erkencikan_tutanak_listesi  = $vt->select( $SQL_yazdirilan_tutanak_oku,array( $_SESSION[ "firma_id" ], "erkencikma" ) ) [2];


$gelmeyen_personel_sayisi               = Array();
$gelmeyen_personel_tutanak_tutulmayan   = Array();
$erken_cikan_personel_tutanak_tutulmayan= Array();
$gec_gelen_personel_tutanak_tutulmayan  = Array();
$izinli_personel_listesi                = Array();
$gelip_cikan_personel_listesi           = Array();

$gec_giris_saatler                      = Array();
$erken_cikis_saatler                    = Array();

foreach ($tum_personel as $personel) {

    //Personel bugun giriş veya çıkış yapmış mı kontrolünü sağlıyoruz
    $personel_giris_cikis_saatleri      = $vt->select($SQL_belirli_tarihli_giris_cikis,array( $personel[ 'id' ],date("Y-m-d"),$_SESSION[ 'firma_id' ] ) )[2];

    if (count($personel_giris_cikis_saatleri) < 1 ) {
        //PErsonel Hiç Gelmemiş ise
        $personele_ait_tutanak_dosyasi_var_mi   = $vt->select( $SQL_tek_tutanak_oku,array( "gunluk", $personel[ 'id' ], date("Y-m-d") ) ) [2];
        $personel_tabloya_eklendi_mi            = $vt->select( $SQL_tutanak_varmi,array( $_SESSION['firma_id'], $personel[ 'id' ],date("Y-m-d"), 'gunluk'  ) ) [2];
        //tutanak dosyaları eklenmisse 
        if ( count( $personele_ait_tutanak_dosyasi_var_mi ) <= 0 AND count( $personel_tabloya_eklendi_mi ) <= 0 ) {
            $gelmeyen_personel_tutanak_tutulmayan[]    = $personel;
        }
        
        $gelmeyen_personel_sayisi[]       = $personel;

    }else{

        $personel_giris_cikis_sayisi    = count($personel_giris_cikis_saatleri);

        //Personelin En erken giriş saati ve en geç çıkış saatini alıyoruz ona göre tutanak olusturulacak
        $son_cikis_index                = $personel_giris_cikis_sayisi - 1;
        $ilk_islemtipi                  = $personel_giris_cikis_saatleri[0]['islem_tipi'];
        $son_islemtipi                  = $personel_giris_cikis_saatleri[$son_cikis_index]['islem_tipi'];

        $ilkGirisSaat                   = $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ], $personel_giris_cikis_saatleri[0]["baslangic_saat_guncellenen"]);

        $SonCikisSaat                   = $fn->saatKarsilastir($personel_giris_cikis_saatleri[$son_cikis_index][ 'bitis_saat' ], $personel_giris_cikis_saatleri[$son_cikis_index]["bitis_saat_guncellenen"]);

        if ($ilkGirisSaat[0] > "08:00" AND ( $ilk_islemtipi == "" or $ilk_islemtipi == "0" )  ) {
            $personele_ait_tutanak_dosyasi_var_mi   = $vt->select( $SQL_tek_tutanak_oku,array( "gecgelme", $personel[ 'id' ], date("Y-m-d") ) ) [2];
            $personel_tabloya_eklendi_mi            = $vt->select( $SQL_tutanak_varmi,array( $_SESSION['firma_id'], $personel[ 'id' ],date("Y-m-d"), 'gecgelme' )  ) [2];
            if ( count( $personele_ait_tutanak_dosyasi_var_mi ) <= 0 AND count( $personel_tabloya_eklendi_mi ) <= 0  ) {
                $gec_gelen_personel_tutanak_tutulmayan[]   = $personel;
                $gec_giris_saatler[$personel["id"]]         = $ilkGirisSaat[0];
            }
            
        }

        if ($SonCikisSaat[0] < "18:30" AND $SonCikisSaat[0] != " - " AND ( $son_islemtipi == "" or $son_islemtipi == "0" ) ) {
            
            $personele_ait_tutanak_dosyasi_var_mi   = $vt->select( $SQL_tek_tutanak_oku,array( "erkencikma", $personel[ 'id' ], date("Y-m-d") ) ) [2];
            $personel_tabloya_eklendi_mi            = $vt->select( $SQL_tutanak_varmi,array( $_SESSION['firma_id'], $personel[ 'id' ],date("Y-m-d"), 'erkencikma' )  ) [2];
            if ( count( $personele_ait_tutanak_dosyasi_var_mi ) <= 0 AND count( $personel_tabloya_eklendi_mi ) <= 0 ) {
                $erken_cikan_personel_tutanak_tutulmayan[]  = $personel;
                $erken_cikan_personel_listesi[]             = $personel;
            }
            
        }

        if ( $personel_giris_cikis_sayisi == 1 AND  $ilk_islemtipi != "0"  ) {
            $izinli_personel_listesi[]              = $personel; 
        }

        if ($SonCikisSaat[0] != " - " AND ( $son_islemtipi == "" or $son_islemtipi == "0" ) ) {
            $gelip_cikan_personel_listesi[]         = $personel;
        }
    } 
}

?>

<div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo count( $icerde_olan_personel ); ?></h3>

                <p>İçerde Olan Toplam Personel</p>
            </div>
            <div class="icon">
                <i class="ion ion-person"></i>
            </div>
            <a href="#" class="small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo count( $izinli_personel_listesi ) + count( $gelip_cikan_personel_listesi ); ?></h3>

                <p>İzinli veya işe gelip Çıkan Personel </p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="javascript:void(0);" class="small-box-footer">&nbsp;</a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo count($gelmeyen_personel_sayisi); ?></h3>
                <p>Gelmeyen Personel</p>
            </div>
            <div class="icon">
                <i class="ion ion-person"></i>
            </div>
            <a href="#" class="small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?php echo count( $tum_personel ); ?></h3>

                <p>Toplam Personel</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<div class="row">
    <div class="col-12 col-sm-6">
        <div class="card  card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-pills nav-tabs tab-container" id="custom-tabs-two-tab" role="tablist" style="padding: 10px 0px 15px 0px;">
                    <li class="pt-2 px-3"><h3 class="card-title"><b>Bekleyen Tutanaklar</b></h3></li>
                    <li class="nav-item">
                        <a class="nav-link active" id="custom-tabs-two-home-tab" data-toggle="pill" href="#custom-tabs-two-home" role="tab" aria-controls="custom-tabs-two-home" aria-selected="false">Gelmeyenler <b class=" badge bg-warning"><?php echo count( $gelmeyen_personel_tutanak_tutulmayan ) + count( $gelmeyen_tutanak_listesi ); ?></b></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill" href="#custom-tabs-two-profile" role="tab" aria-controls="custom-tabs-two-profile" aria-selected="false">Geç Gelenler <b class="badge bg-warning"><?php echo count( $gec_gelen_personel_tutanak_tutulmayan ) + count( $gecgelen_tutanak_listesi ); ?></b></a>

                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-two-messages-tab" data-toggle="pill" href="#custom-tabs-two-messages" role="tab" aria-controls="custom-tabs-two-messages" aria-selected="false">Erken Çıkanlar <b class="badge bg-warning"><?php echo count( $erken_cikan_personel_listesi ) + count( $erkencikan_tutanak_listesi ); ?></b></a>
                    </li>
                </ul>
            </div>
            <div class="card-body direct-chat-messages" style="height:auto; min-height: 333px; max-height: 530px; ">
                <div class="tab-content" id="custom-tabs-two-tabContent">
                    <div class="tab-pane fade active show" id="custom-tabs-two-home" role="tabpanel" aria-labelledby="custom-tabs-two-home-tab">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_gelmeyenler" style="width: 100%;">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th>Tarih</th>
                                <th>Yazırma</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($gelmeyen_personel_tutanak_tutulmayan as $personel) { ?>
                                    <tr class                = "" 
                                        data-personel_id     = "<?php echo $personel[ 'id' ]; ?>" 
                                        data-tutanak_id      = ""
                                        data-tip             = "gunluk" 
                                        data-ad              = "<?php echo $personel[ 'adi' ].' '.$personel["soyadi"]; ?>"
                                        data-tarih           = "<?php echo date( 'Y-m-d' ); ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $personel["adi"].' '.$personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y' ); ?></td>
                                        <td width="80" class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type                 = "checkbox"
                                                data-personel_id     = "<?php echo $personel[ 'id' ]; ?>" 
                                                data-tutanak_id      = ""
                                                data-tip             = "gunluk" 
                                                data-ad              = "<?php echo $personel[ 'adi' ].' '.$personel["soyadi"]; ?>"
                                                data-tarih           = "<?php echo date( 'Y-m-d' ); ?>"
                                                class                = "yazdirma"
                                                id                   = "<?php echo $sayi.'-'.$personel[ "id" ]; ?>">
                                                <label for="<?php echo $sayi.'-'.$personel[ "id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="80"><a target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $personel[ 'id' ]; ?>&tarih=<?php echo date("Y-m-d"); ?>&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>

                                <?php foreach ($gelmeyen_tutanak_listesi as $tutanak_personel) { ?>
                                    <tr class                = "" 
                                        data-personel_id     = "<?php echo $tutanak_personel[ 'personel_id' ]; ?>"
                                        data-tutanak_id      = "<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>"
                                        data-tip             = "gunluk"
                                        data-ad              = "<?php echo $tutanak_personel["adi"].' '.$tutanak_personel["soyadi"]; ?>"
                                        data-tarih           = "<?php echo $tutanak_personel[ 'tarih' ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $tutanak_personel["adi"].' '.$tutanak_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $tutanak_personel[ 'tarih' ] ) ); ?></td>
                                        <td width="80" class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id     = "<?php echo $tutanak_personel[ 'personel_id' ]; ?>" 
                                                data-tutanak_id      = ""
                                                data-tip             = "gunluk" 
                                                data-ad              = "<?php echo $personel[ 'adi' ].' '.$personel["soyadi"]; ?>"
                                                data-tarih           = "<?php echo date( 'Y-m-d' ); ?>"
                                                class                = "yazdirma"
                                                id                   = "<?php echo $sayi.'-'.$tutanak_personel[ "personel_id" ]; ?>">
                                                <label for="<?php echo $sayi.'-'.$tutanak_personel[ "personel_id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="80"><a target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $tutanak_personel[ 'personel_id' ]; ?>&tarih=<?php echo $tutanak_personel[ 'tarih' ]; ?>&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="custom-tabs-two-profile" role="tabpanel" aria-labelledby="custom-tabs-two-profile-tab">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_gec_gelenler" style="width: 100%;">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th>Tarih</th>
                                <th width="80">Yazdırma</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($gec_gelen_personel_tutanak_tutulmayan as $personel) { ?>
                                    <tr class               = "" 
                                        data-personel_id    = "<?php echo $personel[ 'id' ]; ?>" 
                                        data-tutanak_id     = ""
                                        data-tip            = "gecgelme" 
                                        data-ad             = "<?php echo $personel[ 'adi' ].' '.$personel["soyadi"]; ?>"
                                        data-tarih          = "<?php echo date( 'Y-m-d' ); ?>"
                                        data-saat           = "<?php echo $gec_giris_saatler[ $personel[ 'id' ] ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $personel["adi"].' '.$personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y' ); ?></td>
                                        <td width="80" class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $personel[ 'id' ]; ?>" 
                                                data-tutanak_id     = ""
                                                data-tip            = "gecgelme" 
                                                data-ad             = "<?php echo $personel[ 'adi' ].' '.$personel["soyadi"]; ?>"
                                                data-tarih          = "<?php echo date( 'Y-m-d' ); ?>"
                                                data-saat           = "<?php echo $gec_giris_saatler[ $personel[ 'id' ] ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "gecgelme-<?php echo $sayi.'-'.$personel[ "id" ]; ?>">
                                                <label for="gecgelme-<?php echo $sayi.'-'.$personel[ "id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="80"><a target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $personel[ 'id' ]; ?>&tarih=<?php echo date("Y-m-d"); ?>&tip=gecgelme&saat=<?php echo $gec_giris_saatler[ $personel[ 'id' ] ] ?>" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>

                                <?php foreach ($gecgelen_tutanak_listesi as $gecgelen_personel) { ?>
                                    <tr class               = "" 
                                        data-personel_id    = "<?php echo $gecgelen_personel[ 'personel_id' ]; ?>"
                                        data-tutanak_id     = "<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>"
                                        data-tip            = "gecgelme"
                                        data-ad             = "<?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?>"
                                        data-tarih          = "<?php echo $gecgelen_personel[ 'tarih' ]; ?>"
                                        data-saat           = "<?php echo $gecgelen_personel[ 'saat' ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $gecgelen_personel[ 'tarih' ] ) ); ?></td>
                                        <td width="80" class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $gecgelen_personel[ 'personel_id' ]; ?>"
                                                data-tutanak_id     = "<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>"
                                                data-tip            = "gecgelme"
                                                data-ad             = "<?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?>"
                                                data-tarih          = "<?php echo $gecgelen_personel[ 'tarih' ]; ?>"
                                                data-saat           = "<?php echo $gecgelen_personel[ 'saat' ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "gecgelme-<?php echo $sayi.'-'.$gecgelen_personel[ "personel_id" ]; ?>">
                                                <label for="gecgelme-<?php echo $sayi.'-'.$gecgelen_personel[ "personel_id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="80"><a target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $gecgelen_personel[ 'personel_id' ]; ?>&tarih=<?php echo $gecgelen_personel[ 'tarih' ]; ?>&tip=gecgelme&saat=<?php echo $gecgelen_personel[ 'saat' ]; ?>" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>
                                
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="custom-tabs-two-messages" role="tabpanel" aria-labelledby="custom-tabs-two-messages-tab">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_erken_cikanlar" style="width: 100%;">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th>Tarih</th>
                                <th width="80px">Yazırma</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($erken_cikan_personel_listesi as $personel) { ?>
                                    <tr class               = "" 
                                        data-personel_id    = "<?php echo $personel[ 'id' ]; ?>" 
                                        data-tutanak_id     = ""
                                        data-tip            = "erkencikma" 
                                        data-ad             = "<?php echo $personel[ 'adi' ].' '.$personel["soyadi"]; ?>"
                                        data-tarih          = "<?php echo date( 'Y-m-d' ); ?>"
                                        data-saat           = "<?php echo $gec_giris_saatler[ $personel[ 'id' ] ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $personel["adi"].' '.$personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y' ); ?></td>
                                        <td width="80" class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $personel[ 'id' ]; ?>" 
                                                data-tutanak_id     = ""
                                                data-tip            = "erkencikma" 
                                                data-ad             = "<?php echo $personel[ 'adi' ].' '.$personel["soyadi"]; ?>"
                                                data-tarih          = "<?php echo date( 'Y-m-d' ); ?>"
                                                data-saat           = "<?php echo $gec_giris_saatler[ $personel[ 'id' ] ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "erkencikma-<?php echo $sayi.'-'.$personel[ "id" ]; ?>">
                                                <label for="erkencikma-<?php echo $sayi.'-'.$personel[ "id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="80"><a target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $personel[ 'id' ]; ?>&tarih=<?php echo date("Y-m-d"); ?>&tip=erkencikma&saat=<?php echo $gec_giris_saatler[ $personel[ 'id' ] ] ?>" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>

                                <?php foreach ($erkencikan_tutanak_listesi as $erkencikan_personel) { ?>
                                    <tr class               = "" 
                                        data-personel_id    = "<?php echo $erkencikan_personel[ 'personel_id' ]; ?>"
                                        data-tutanak_id     = "<?php echo $erkencikan_personel[ 'tutanak_id' ]; ?>"
                                        data-tip            = "erkencikma"
                                        data-ad             = "<?php echo $erkencikan_personel["adi"].' '.$erkencikan_personel["soyadi"]; ?>"
                                        data-tarih          = "<?php echo $erkencikan_personel[ 'tarih' ]; ?>"
                                        data-saat           = "<?php echo $erkencikan_personel[ 'saat' ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $erkencikan_personel["adi"].' '.$erkencikan_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $erkencikan_personel[ 'tarih' ] ) ); ?></td>
                                        <td width="80" class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $erkencikan_personel[ 'personel_id' ]; ?>"
                                                data-tutanak_id     = "<?php echo $erkencikan_personel[ 'tutanak_id' ]; ?>"
                                                data-tip            = "erkencikma"
                                                data-ad             = "<?php echo $erkencikan_personel["adi"].' '.$erkencikan_personel["soyadi"]; ?>"
                                                data-tarih          = "<?php echo $erkencikan_personel[ 'tarih' ]; ?>"
                                                data-saat           = "<?php echo $erkencikan_personel[ 'saat' ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "<?php echo $sayi.'-'.$erkencikan_personel[ "personel_id" ]; ?>">
                                                <label for="<?php echo $sayi.'-'.$erkencikan_personel[ "personel_id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="80"><a target="_blank" href="?modul=erkencikanolustur&personel_id=<?php echo $erkencikan_personel[ 'personel_id' ]; ?>&tarih=<?php echo $erkencikan_personel[ 'tarih' ]; ?>&tip=erkencikma&saat=<?php echo $erkencikan_personel[ 'saat' ]; ?>" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6" >
        <div class="card  card-tabs" id="yazdirilanTutanaklar">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-pills nav-tabs tab-container" id="custom-tabs-two-tab" role="tablist" style="padding: 10px 0px 15px 0px;">
                    <li class="pt-2 px-3"><h3 class="card-title"><b>Dosya Yüklenmeyen T.</b></h3></li>
                    <li class="nav-item">
                        <a class="nav-link active" id="custom-tabs-two-home-tab" data-toggle="pill" href="#yazdirilanGelmeyen" role="tab" aria-controls="yazdirilanGelmeyen" aria-selected="false">Gelmeyenler <b class=" badge bg-warning"><?php echo  count( $yazdirilan_gelmeyen_tutanak_listesi ); ?></b></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill" href="#yazdirilanGecGelen" role="tab" aria-controls="yazdirilanGecGelen" aria-selected="false">Geç Gelenler <b class="badge bg-warning"><?php echo count( $yazdirilan_gecgelen_tutanak_listesi ); ?></b></a>

                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-two-messages-tab" data-toggle="pill" href="#yazidirlanErkenCikan" role="tab" aria-controls="yazidirlanErkenCikan" aria-selected="false">Erken Çıkanlar <b class="badge bg-warning"><?php echo count( $yazdirilan_erkencikan_tutanak_listesi ); ?></b></a>
                    </li>
                </ul>
            </div>
            <div class="card-body direct-chat-messages" style="height:auto; min-height: 333px; max-height: 530px; ">
                <div class="tab-content" id="custom-tabs-two-tabContent">
                    <div class="tab-pane fade active show" id="yazdirilanGelmeyen" role="tabpanel" aria-labelledby="custom-tabs-two-home-tab">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_gelmeyenler" style="width: 100%;">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th>Tarih</th>
                                <th>Yazırma</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($yazdirilan_gelmeyen_tutanak_listesi as $tutanak_personel) { ?>
                                    <tr class                = "personel-Tr" 
                                        data-personel_id     = "<?php echo $tutanak_personel[ 'personel_id' ]; ?>"
                                        data-tutanak_id      = "<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>"
                                        data-tip             = "gunluk"
                                        data-ad              = "<?php echo $tutanak_personel["adi"].' '.$tutanak_personel["soyadi"]; ?>"
                                        data-tarih           = "<?php echo $tutanak_personel[ 'tarih' ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $tutanak_personel["adi"].' '.$tutanak_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $tutanak_personel[ 'tarih' ] ) ); ?></td>
                                        <td width="80" class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id     = "<?php echo $tutanak_personel[ 'personel_id' ]; ?>" 
                                                data-tutanak_id      = ""
                                                data-tip             = "gunluk" 
                                                data-ad              = "<?php echo $personel[ 'adi' ].' '.$personel["soyadi"]; ?>"
                                                data-tarih           = "<?php echo date( 'Y-m-d' ); ?>"
                                                class                = "yazdirma"
                                                id                   = "<?php echo $sayi.'-'.$tutanak_personel[ "personel_id" ]; ?>">
                                                <label for="<?php echo $sayi.'-'.$tutanak_personel[ "personel_id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="80"><a target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $tutanak_personel[ 'personel_id' ]; ?>&tarih=<?php echo $tutanak_personel[ 'tarih' ]; ?>&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="yazdirilanGecGelen" role="tabpanel" aria-labelledby="custom-tabs-two-profile-tab">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_gec_gelenler" style="width: 100%;">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th>Tarih</th>
                                <th width="80">Yazdırma</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($yazdirilan_gecgelen_tutanak_listesi as $gecgelen_personel) { ?>
                                    <tr class               = "personel-Tr" 
                                        data-personel_id    = "<?php echo $gecgelen_personel[ 'personel_id' ]; ?>"
                                        data-tutanak_id     = "<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>"
                                        data-tip            = "gecgelme"
                                        data-ad             = "<?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?>"
                                        data-tarih          = "<?php echo $gecgelen_personel[ 'tarih' ]; ?>"
                                        data-saat           = "<?php echo $gecgelen_personel[ 'saat' ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $gecgelen_personel[ 'tarih' ] ) ); ?></td>
                                        <td width="80" class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $gecgelen_personel[ 'personel_id' ]; ?>"
                                                data-tutanak_id     = "<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>"
                                                data-tip            = "gecgelme"
                                                data-ad             = "<?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?>"
                                                data-tarih          = "<?php echo $gecgelen_personel[ 'tarih' ]; ?>"
                                                data-saat           = "<?php echo $gecgelen_personel[ 'saat' ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "gecgelme-<?php echo $sayi.'-'.$gecgelen_personel[ "personel_id" ]; ?>">
                                                <label for="gecgelme-<?php echo $sayi.'-'.$gecgelen_personel[ "personel_id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="80"><a target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $gecgelen_personel[ 'personel_id' ]; ?>&tarih=<?php echo $gecgelen_personel[ 'tarih' ]; ?>&tip=gecgelme&saat=<?php echo $gecgelen_personel[ 'saat' ]; ?>" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="yazidirlanErkenCikan" role="tabpanel" aria-labelledby="custom-tabs-two-messages-tab">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_erken_cikanlar" style="width: 100%;">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th>Tarih</th>
                                <th width="80px">Yazırma</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($yazdirilan_erkencikan_tutanak_listesi as $erkencikan_personel) { ?>
                                    <tr class               = "personel-Tr" 
                                        data-personel_id    = "<?php echo $erkencikan_personel[ 'personel_id' ]; ?>"
                                        data-tutanak_id     = "<?php echo $erkencikan_personel[ 'tutanak_id' ]; ?>"
                                        data-tip            = "erkencikma"
                                        data-ad             = "<?php echo $erkencikan_personel["adi"].' '.$erkencikan_personel["soyadi"]; ?>"
                                        data-tarih          = "<?php echo $erkencikan_personel[ 'tarih' ]; ?>"
                                        data-saat           = "<?php echo $erkencikan_personel[ 'saat' ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $erkencikan_personel["adi"].' '.$erkencikan_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $erkencikan_personel[ 'tarih' ] ) ); ?></td>
                                        <td width="80" class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $erkencikan_personel[ 'personel_id' ]; ?>"
                                                data-tutanak_id     = "<?php echo $erkencikan_personel[ 'tutanak_id' ]; ?>"
                                                data-tip            = "erkencikma"
                                                data-ad             = "<?php echo $erkencikan_personel["adi"].' '.$erkencikan_personel["soyadi"]; ?>"
                                                data-tarih          = "<?php echo $erkencikan_personel[ 'tarih' ]; ?>"
                                                data-saat           = "<?php echo $erkencikan_personel[ 'saat' ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "<?php echo $sayi.'-'.$erkencikan_personel[ "personel_id" ]; ?>">
                                                <label for="<?php echo $sayi.'-'.$erkencikan_personel[ "personel_id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="80"><a target="_blank" href="?modul=erkencikanolustur&personel_id=<?php echo $erkencikan_personel[ 'personel_id' ]; ?>&tarih=<?php echo $erkencikan_personel[ 'tarih' ]; ?>&tip=erkencikma&saat=<?php echo $erkencikan_personel[ 'saat' ]; ?>" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="dropzonedosya" id="DosyaAlani" style="position: absolute; top:0;display: none;">
            <div class=" card card-info">
                <div class="card-header" style="padding: 1.4rem 1.25rem;" id="CardHeader">
                    <h3 class="card-title"><span id="baslik">Seçilen Personele İçin Dosya Ekleme Kısmı</span></h3>
                    <div class="card-tools">
                        <button type="button" class="btn bg-info btn-sm dropzoneKapat" >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" id="CardBody">
                    <form enctype="multipart/form-data" method="POST"  name="mainFileUploader" class="">
                        <div class="form-group">
                            <input type="text" name="aciklama" id="aciklama" class="form-control" placeholder="Acıklama Kısmı">
                        </div>
                        <div class="dropzone" id="dropzone" style="min-height: 247px;">
                            <div class="dz-message">
                                <h3 class="m-h-lg">Yüklemek istediğiniz dosyaları buyara sürükleyiniz</h3>
                                <p class="m-b-lg text-muted">(Yüklemek için dosyalarınızı sürükleyiniz yada buraya tıklayınız)<br>En Fazla 10 Resim Birden Yükleyebilirsiniz</p>
                            </div>
                        </div>
                        <input type="hidden" name="personel_id" id="personel_id">
                        <input type="hidden" name="tutanak_id" id="tutanak_id">
                        <input type="hidden" name="tip" id="tip">
                        <input type="hidden" name="tarih" id="tarih">
                        <input type="hidden" name="saat" id="saat">
                        <input type="hidden" name="islem" id="islem" value="dosyaekle">
                        <a href="javascript:void(0);" class="btn btn-outline-info" style="margin-top:10px; width: 100%;" id="submit-all">Yükle</a>
                    </form>
                </div>
            </div>
        </div> 
    </div>
    

            
</div>
<style type="text/css">
    .tab-container .nav-link.active{
        border: 1px solid transparent;
    }
</style>

<script type="text/javascript">

    Dropzone.options.dropzone = {
        url: '_modul/tutanakolustur/tutanakolusturSEG.php',
        autoProcessQueue: false,
        uploadMultiple:true,
        parallelUploads: 10,
        maxFiles: 10,
        acceptedFiles: ".jpeg,.jpg,.png,.pdf",

        init: function () {

            var submitButton = document.querySelector("#submit-all");
            var wrapperThis = this;

            submitButton.addEventListener("click", function () {
                wrapperThis.processQueue();
            });

            this.on("addedfile", function (file) {

                // Kaldır Butonu Oluşturma
                var removeButton = Dropzone.createElement("<div class'text-center' style='display: block; width: 100%;text-align: center;margin-top: 7px;'><button style='display:block;width: 100%;border-radius:7px;' class='btn btn-xs btn-danger'>Kaldır</button>");

                // Kaldır Butonuna Tıklandığında
                removeButton.addEventListener("click", function (e) {
                    // Make sure the button click doesn't submit the form:
                    e.preventDefault();
                    e.stopPropagation();

                    // Remove the file preview.
                    wrapperThis.removeFile(file);
                    // If you want to the delete the file on the server as well,
                    // you can do the AJAX request here.
                });

                // Add the button to the file preview element.
                file.previewElement.appendChild(removeButton);
            });

            this.on('sendingmultiple', function (data, xhr, formData) {
                formData.append( "personel_id",  $( "#personel_id" ).val() );
                formData.append( "tutanak_id",   $( "#tutanak_id" ).val() );
                formData.append( "tip",          $( "#tip" ).val() );
                formData.append( "tarih",        $( "#tarih" ).val() );
                formData.append( "saat",         $( "#saat" ).val() );
                formData.append( "aciklama",     $( "#aciklama" ).val() );
                formData.append( "islem",        $( "#islem" ).val() );
            });

            // this.on('completemultiple', function (){
            //     mesajVer('Personel İçin Tutanaklar Eklendi', 'yesil');
            //     setTimeout(reload(), 5000);
            // });

            // this.on("queuecomplete", function (file) {
            //     mesajVer('Personel İçin Tutanaklar Eklendi', 'yesil');
            //     setTimeout(location.reload(), 5000);
            // });
        },
        success: function(file, response){
            var response = JSON.parse(response);
            if ( response.sonuc == 'ok' ){
                mesajVer('Personel İçin Tutanaklar Eklendi', 'yesil');
                
                const yenile = setTimeout(sayfa_yenile, 2500);

                function sayfa_yenile() {
                    location.reload();
                }
            }
        }

    };

    $( "body" ).on('click', '.personel-Tr', function() {

        $("#DosyaAlani").fadeToggle(250);

        var genislik            = document.getElementById("yazdirilanTutanaklar").offsetWidth;
        var yukseklik           = document.getElementById("yazdirilanTutanaklar").offsetHeight;
        
        //Dosya Yükleme Alanının Boyutları
        var baslikyukseklik     = document.getElementById("CardHeader").offsetHeight;
        var icerikyukseklik     = document.getElementById("CardBody").offsetHeight;
        var dropzoneyukseklik   = document.getElementById("dropzone").offsetHeight;

        //Yazıdırlmauan Tutanaklar genişliğini dosya yükleme alanına atıyoruz
        document.getElementById("DosyaAlani").style.width = genislik+"px";
        var dosyalani = baslikyukseklik + icerikyukseklik;
        if (yukseklik > dosyalani){
            var yukseklikfarki = yukseklik - dosyalani ;
            document.getElementById("dropzone").style.height = yukseklikfarki+dropzoneyukseklik+"px";
        }

        //Tablodaki tüm satırları normale ceviriyoruzz  Tıklanan satırı arka planını warning yapıyoruz
        $(".personel-Tr").each(function() {
            $(this).removeClass("table-warning")
        });
        $(this).addClass("table-warning");

        //Satıra ait data verileri çekiyoruz
        var personel_id = $( this ).data( "personel_id" );
        var tutanak_id  = $( this ).data( "tutanak_id" );
        var ad          = $( this ).data( "ad" ); 
        var tip         = $( this ).data( "tip" ); 
        var tarih       = $( this ).data( "tarih" ); 
        var saat        = $( this ).data( "saat" );

        //Gelen verileri forma atıyoruz
        $( "#personel_id" ).val( personel_id );
        $( "#tutanak_id" ).val( tutanak_id );
        $( "#tip" ).val( tip );
        $( "#tarih" ).val( tarih );
        $( "#saat" ).val( saat );
        $( "#baslik" ).html( '<b>'+ad+'</b> İçin Dosya Yüklenecektir' );
        
    });

    $('.dropzoneKapat').click(function() {
        $(".dropzonedosya").fadeToggle(250);
    }); 

    $( "body" ).on('change', '.yazdirma', function() {
        var personel_id = $( this ).data( "personel_id" );
        var tutanak_id  = $( this ).data( "tutanak_id" );
        var tip         = $( this ).data( "tip" ); 
        var tarih       = $( this ).data( "tarih" ); 
        var saat        = $( this ).data( "saat" ); 

        var url         = '_modul/tutanakolustur/tutanakolusturSEG.php?islem=yazdirma';
        $.ajax({
            type: "POST",
            url: url,
            data: 'personel_id=' + personel_id+'&tutanak_id=' + tutanak_id+'&tip=' + tip+'&tarih=' + tarih+'&saat=' + saat, 
            cache: false,
            success: function(response) {
                var response = JSON.parse(response);
                if ( response.sonuc == 'ok' ){
                    const yenile = setTimeout(sayfa_yenile, 100);
                    function sayfa_yenile() {
                        location.reload();
                    }
                }
            }

        })
    });
    
    //$(".urun_tr_"+$id).fadeOut(300, function(){ $(this).remove();});
    // e.preventDefault();
    // $(this).closest(".form-ici").remove();

    var tbl_erken_cikanlar = $( "#tbl_erken_cikanlar" ).DataTable( {
        "responsive": true, "lengthChange": true, "autoWidth": true,
        "stateSave": true,
        "language": {
            "decimal"           : "",
            "emptyTable"        : "Gösterilecek kayıt yok!",
            "info"              : "Toplam _TOTAL_ kayıttan _START_ ve _END_ arası gösteriliyor",
            "infoEmpty"         : "Toplam 0 kayıttan 0 ve 0 arası gösteriliyor",
            "infoFiltered"      : "",
            "infoPostFix"       : "",
            "thousands"         : ",",
            "lengthMenu"        : "Show _MENU_ entries",
            "loadingRecords"    : "Yükleniyor...",
            "processing"        : "İşleniyor...",
            "search"            : "Ara:",
            "zeroRecords"       : "Eşleşen kayıt bulunamadı!",
            "paginate"          : {
                "first"     : "İlk",
                "last"      : "Son",
                "next"      : "Sonraki",
                "previous"  : "Önceki"
            }
        }
    } );

    var tbl_gec_gelenler = $( "#tbl_gec_gelenler" ).DataTable( {
        "responsive": true, "lengthChange": true, "autoWidth": true,
        "stateSave": true,
        "language": {
            "decimal"           : "",
            "emptyTable"        : "Gösterilecek kayıt yok!",
            "info"              : "Toplam _TOTAL_ kayıttan _START_ ve _END_ arası gösteriliyor",
            "infoEmpty"         : "Toplam 0 kayıttan 0 ve 0 arası gösteriliyor",
            "infoFiltered"      : "",
            "infoPostFix"       : "",
            "thousands"         : ",",
            "lengthMenu"        : "Show _MENU_ entries",
            "loadingRecords"    : "Yükleniyor...",
            "processing"        : "İşleniyor...",
            "search"            : "Ara:",
            "zeroRecords"       : "Eşleşen kayıt bulunamadı!",
            "paginate"          : {
                "first"     : "İlk",
                "last"      : "Son",
                "next"      : "Sonraki",
                "previous"  : "Önceki"
            }
        }
    } );
    
    var tbl_gelmeyenler = $( "#tbl_gelmeyenler" ).DataTable( {
        "responsive": true, "lengthChange": true, "autoWidth": true,
        "stateSave": true,
        "language": {
            "decimal"           : "",
            "emptyTable"        : "Gösterilecek kayıt yok!",
            "info"              : "Toplam _TOTAL_ kayıttan _START_ ve _END_ arası gösteriliyor",
            "infoEmpty"         : "Toplam 0 kayıttan 0 ve 0 arası gösteriliyor",
            "infoFiltered"      : "",
            "infoPostFix"       : "",
            "thousands"         : ",",
            "lengthMenu"        : "Show _MENU_ entries",
            "loadingRecords"    : "Yükleniyor...",
            "processing"        : "İşleniyor...",
            "search"            : "Ara:",
            "zeroRecords"       : "Eşleşen kayıt bulunamadı!",
            "paginate"          : {
                "first"     : "İlk",
                "last"      : "Son",
                "next"      : "Sonraki",
                "previous"  : "Önceki"
            }
        }
    } );


    
    
</script>