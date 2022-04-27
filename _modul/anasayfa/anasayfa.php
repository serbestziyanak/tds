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
    p.aktif        = 1 
GROUP BY p.id
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
    p.aktif       = 1
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
    gc.personel_id = ? AND gc.tarih =? AND p.firma_id = ?
ORDER BY baslangic_saat ASC 
SQL;

$tum_personel                       = $vt->select( $SQL_tum_personel,array( $_SESSION[ "firma_id" ] ) ) [2];
$icerde_olan_personel               = $vt->select( $SQL_icerde_olan_personel,array( $_SESSION[ "firma_id" ], date( "Y-m-d" ) ) ) [2];
$izinli_cikan_personel              = $vt->select( $SQL_izinli_cikan_personel,array( $_SESSION[ "firma_id" ], date( "Y-m-d" ) ) ) [2];

$gelmeyen_personel_listesi          = Array();
$erken_cikan_personel_listesi       = Array();
$gec_gelen_personel_listesi         = Array();
$izinli_personel_listesi            = Array();
$gelip_cikan_personel_listesi       = Array();

$gec_giris_saatler                  = Array();
$erken_cikis_saatler                = Array();

foreach ($tum_personel as $personel) {
    $personel_giris_cikis_saatleri  = $vt->select($SQL_belirli_tarihli_giris_cikis,array( $personel[ 'id' ],date("Y-m-d"),$_SESSION[ 'firma_id' ] ) )[2];

    if (count($personel_giris_cikis_saatleri) < 1 ) {

        $gelmeyen_personel_listesi[]    = $personel;

    }else{

        $personel_giris_cikis_sayisi    = count($personel_giris_cikis_saatleri);

        //Personelin En erken giriş saati ve en geç çıkış saatini alıyoruz ona göre tutanak olusturulacak
        $son_cikis_index                = $personel_giris_cikis_sayisi - 1;
        $ilk_islemtipi                  = $personel_giris_cikis_saatleri[0]['islem_tipi'];
        $son_islemtipi                  = $personel_giris_cikis_saatleri[$son_cikis_index]['islem_tipi'];

        $ilkGirisSaat                   = $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ], $personel_giris_cikis_saatleri[0]["baslangic_saat_guncellenen"]);

        $SonCikisSaat                   = $fn->saatKarsilastir($personel_giris_cikis_saatleri[$son_cikis_index][ 'bitis_saat' ], $personel_giris_cikis_saatleri[$son_cikis_index]["bitis_saat_guncellenen"]);

        if ($ilkGirisSaat > "08:00" AND ( $ilk_islemtipi == "" or $ilk_islemtipi == "0" )  ) {
            $gec_giris_saatler[$personel["id"]]     = $ilkGirisSaat;
            $gec_gelen_personel_listesi[]           = $personel;
        }

        if ($SonCikisSaat < "18:30" AND $SonCikisSaat != " - " AND ( $son_islemtipi == "" or $son_islemtipi == "0" ) ) {
            $erken_giris_saatler[$personel["id"]]   = $SonCikisSaat;
            $erken_cikan_personel_listesi[]         = $personel;
        }

        if ( $personel_giris_cikis_sayisi == 1 AND  $ilk_islemtipi != "0"  ) {
            $izinli_personel_listesi[]              = $personel; 
        }

        if ($SonCikisSaat != " - " AND ( $son_islemtipi == "" or $son_islemtipi == "0" ) ) {
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
                <h3><?php echo count($gelmeyen_personel_listesi); ?></h3>
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
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="custom-tabs-two-home-tab" data-toggle="pill" href="#custom-tabs-two-home" role="tab" aria-controls="custom-tabs-two-home" aria-selected="false">Gelmeyenler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill" href="#custom-tabs-two-profile" role="tab" aria-controls="custom-tabs-two-profile" aria-selected="false">Geç Gelenler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-two-messages-tab" data-toggle="pill" href="#custom-tabs-two-messages" role="tab" aria-controls="custom-tabs-two-messages" aria-selected="false">Erken Çıkanlar</a>
                    </li>
                </ul>
            </div>
            <div class="card-body direct-chat-messages" style="height:360px;">
                <div class="tab-content" id="custom-tabs-two-tabContent">
                    <div class="tab-pane fade active show" id="custom-tabs-two-home" role="tabpanel" aria-labelledby="custom-tabs-two-home-tab">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_gelmeyenler">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($gelmeyen_personel_listesi as $personel) { ?>
                                    <tr>
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $personel["adi"].' '.$personel["soyadi"]; ?></td>
                                        <td width="80"><a href="?modul=tutanakolustur&personel_id=<?php echo $personel['id']; ?>&tarih=<?php echo date("Y-m-d"); ?>&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                    </tr>
                                <?php $sayi++; } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="custom-tabs-two-profile" role="tabpanel" aria-labelledby="custom-tabs-two-profile-tab">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_gec_gelenler">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td width="20">1</td>
                                    <td>Resül EVİS</td>
                                    <td width="80"><a href="?modul=tutanakolustur&personel_id=9&tarih=2022-04-1&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                </tr>
                                <tr>
                                    <td width="20">1</td>
                                    <td>Resül EVİS</td>
                                    <td width="80"><a href="?modul=tutanakolustur&personel_id=9&tarih=2022-04-1&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                </tr>
                                <tr>
                                    <td width="20">1</td>
                                    <td>Resül EVİS</td>
                                    <td width="80"><a href="?modul=tutanakolustur&personel_id=9&tarih=2022-04-1&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                </tr>
                                <tr>
                                    <td width="20">1</td>
                                    <td>Resül EVİS</td>
                                    <td width="80"><a href="?modul=tutanakolustur&personel_id=9&tarih=2022-04-1&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="custom-tabs-two-messages" role="tabpanel" aria-labelledby="custom-tabs-two-messages-tab">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_erken_cikanlar">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td width="20">1</td>
                                    <td>Resül EVİS</td>
                                    <td width="80"><a href="?modul=tutanakolustur&personel_id=9&tarih=2022-04-1&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                </tr>
                                <tr>
                                    <td width="20">1</td>
                                    <td>Resül EVİS</td>
                                    <td width="80"><a href="?modul=tutanakolustur&personel_id=9&tarih=2022-04-1&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                </tr>
                                <tr>
                                    <td width="20">1</td>
                                    <td>Resül EVİS</td>
                                    <td width="80"><a href="?modul=tutanakolustur&personel_id=9&tarih=2022-04-1&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                </tr>
                                <tr>
                                    <td width="20">1</td>
                                    <td>Resül EVİS</td>
                                    <td width="80"><a href="?modul=tutanakolustur&personel_id=9&tarih=2022-04-1&tip=gunluk" class="btn btn-danger btn-xs">Tutanak Tut</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
