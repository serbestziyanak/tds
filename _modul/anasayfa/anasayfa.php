<?php 
echo !defined("ADMIN") ? die("Görüntüleme Yetkiniz Bulunmamaktadır.") : null;  

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
    $mesaj                              = $_SESSION[ 'sonuclar' ][ 'mesaj' ];
    $mesaj_turu                         = $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
    unset( $_SESSION[ 'sonuclar' ] );
    echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$fn = new Fonksiyonlar();
$vt = new VeriTabani();

//Beyaz Yakalı Personle Haric Listesi
$SQL_beyaz_yakali_personel = <<< SQL
SELECT
    id
    ,adi
    ,soyadi
    ,grup_id
FROM
    tb_personel AS p
WHERE
    p.firma_id  = ? AND 
    p.grup_id   = ? AND 
    p.aktif     = 1 AND
    p.isten_cikis_tarihi IS NULL 
SQL;


//Giriş Yapmış ama çıkış yapmamış suan çalışan personel 
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

//Yazdırılmamış tutanak listesi
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

//Yazdırılan Tutanak Listesi
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
    gc.baslangic_saat  IS NOT NULL AND 
    gc.personel_id  = ? AND 
    gc.tarih        = ? AND
    p.firma_id      = ? AND 
    gc.aktif        = 1
ORDER BY baslangic_saat ASC 
SQL;

/*
Bugunkü Tarifeye ait giriş cıkış saatlerini veya tatil durumunu getiriyor
*/
$SQL_giris_cikis_saat = <<< SQL
SELECT 
    t1.*
from
    tb_tarifeler AS t1
LEFT JOIN tb_mesai_turu AS mt ON  t1.mesai_turu = mt.id

WHERE 
    t1.baslangic_tarih <= ? AND 
    t1.bitis_tarih >= ? AND
    mt.gunler LIKE ? AND 
    t1.grup_id LIKE ? AND
    t1.aktif = 1
ORDER BY t1.id DESC
LIMIT 1      
SQL;

//TARİFEYE AİT SAAT LİSTESİ
$SQL_tarife_saati = <<< SQL
SELECT 
    *
from
    tb_tarife_saati 
WHERE 
    tarife_id = ? AND 
    aktif = 1
ORDER BY baslangic ASC
SQL;

//TARİFEYE AİT SAAT LİSTESİ
$SQL_genel_ayarlar = <<< SQL
SELECT 
    *
from
    tb_genel_ayarlar
WHERE 
    firma_id = ?
SQL;

//Ay içierisinde Giriş Veya İşten Çıkan PErsonel Listesi
$SQL_ise_giris = <<< SQL
SELECT 
	COUNT(id) AS sayi
FROM 
	tb_personel
WHERE 
	DATE_FORMAT(ise_giris_tarihi,'%Y-%m') 	    = ? 
SQL;

$SQL_is_cikis = <<< SQL
SELECT 
	COUNT(id) AS sayi
FROM 
	tb_personel
WHERE 
	DATE_FORMAT(isten_cikis_tarihi,'%Y-%m') 	= ? 
SQL;

/*
Veri tabanında giriş veya çıkış boş olan personel listesi
*/
$SQL_eksik_hareket = <<< SQL
SELECT
    tb_giris_cikis.id,
    tb_giris_cikis.personel_id,
    CONCAT(tb_personel.adi," ",tb_personel.soyadi) AS adisoyadi,
    DATE_FORMAT(tb_giris_cikis.tarih,"%d.%m.%Y") AS tarih,
    DATE_FORMAT(tb_giris_cikis.tarih,"%Y-%m") AS YAtarih,
    DATE_FORMAT(tb_giris_cikis.tarih,"%Y-%m-%d") AS YAGtarih,
    tb_giris_cikis.tarih AS Otarih
FROM 
    tb_personel
INNER JOIN tb_giris_cikis ON tb_personel.id = tb_giris_cikis.personel_id
WHERE 
    (tb_giris_cikis.baslangic_saat IS NULL XOR tb_giris_cikis.bitis_saat IS NULL) AND
    tb_giris_cikis.aktif = 1 AND
    tb_personel.firma_id = ? AND
    DATE_FORMAT(tb_giris_cikis.tarih, "%Y-%m") = ? 
SQL;
$genel_ayarlar                          = $vt->select( $SQL_genel_ayarlar, array( $_SESSION[ "firma_id" ] ) ) [ 2 ][ 0 ];

//Yazdırma İşlemi Yapılan Tutanaklar Listesi
$yazdirilan_gelmeyen_tutanak_listesi    = $vt->select( $SQL_yazdirilan_tutanak_oku,array( $_SESSION[ "firma_id" ], "gunluk" ) ) [2];
$yazdirilan_gecgelen_tutanak_listesi    = $vt->select( $SQL_yazdirilan_tutanak_oku,array( $_SESSION[ "firma_id" ], "gecgelme" ) ) [2];
$yazdirilan_erkencikan_tutanak_listesi  = $vt->select( $SQL_yazdirilan_tutanak_oku,array( $_SESSION[ "firma_id" ], "erkencikma" ) ) [2];
$ay_icerisinde_giris_yapan              = $vt->select( $SQL_ise_giris,array( date("Y-m") ) ) [2][0]["sayi"];
$ay_icerisinde_cikis_yapan              = $vt->select( $SQL_is_cikis,array( date("Y-m") ) ) [2][0]["sayi"];
$eksik_hareket_olan_personel            = $vt->select( $SQL_eksik_hareket,array( $_SESSION[ "firma_id" ], date("Y-m") ) )[2];

$gun                                    = $fn->gunVer( date("Y-m-d") ); 

$erken_cikanlar                         = $fn->erkenCikanlar( date("Y-m-d"), '%,'.$gun.',%');
$erken_cikanlar_listesi                 = $fn->erkenCikanlarListesi( date("Y-m-d"), '%,'.$gun.',%');
$gec_gelenler                           = $fn->gecGelenler( date("Y-m-d"), '%,'.$gun.',%');
$gec_gelenler_listesi                   = $fn->gecGelenlerListesi( date("Y-m-d"), '%,'.$gun.',%');
$gelmeyenler                            = $fn->gelmeyenler( date("Y-m-d") );
$gelmeyenler_listesi                    = $fn->gelmeyenlerListesi( date("Y-m-d") );
$gelenler                               = $fn->gelenler( date("Y-m-d") );
$mesai_cikmayan                         = $fn->mesaiCikmayan( date("Y-m-d") );
$izinli_personel                        = $fn->izinliPersonel( date("Y-m-d") );

$suresi_dolmus_kategori                 = $fn->suresiDolmusKategori();
$suresi_dolmus_dosya                    = $fn->suresiDolmusDosya();

$gelmeyenler_sayisi                     = count( $gelmeyenler );
$gelenler_sayisi                        = count( $gelenler );
$mesai_cikmayan_sayisi                  = count( $mesai_cikmayan );
$izinli_personel_sayisi                 = count( $izinli_personel );


//tutanak dosyası oluşturulmayan personel listesi
$gelmeyen_tutanak_listesi               = $vt->select( $SQL_tutanak_oku,array( $_SESSION[ "firma_id" ], "gunluk" ) ) [2];
$gecgelen_tutanak_listesi               = $vt->select( $SQL_tutanak_oku,array( $_SESSION[ "firma_id" ], "gecgelme" ) ) [2];
$erkencikan_tutanak_listesi             = $vt->select( $SQL_tutanak_oku,array( $_SESSION[ "firma_id" ], "erkencikma" ) ) [2];

$beyaz_yakali_personel                  = $vt->select( $SQL_beyaz_yakali_personel,array( $_SESSION[ "firma_id" ], $genel_ayarlar["beyaz_yakali_personel"] ) ) [3];

$toplam_personel_sayisi                 = $gelmeyenler_sayisi + $mesai_cikmayan_sayisi + $izinli_personel_sayisi + $beyaz_yakali_personel +($gelenler_sayisi - $mesai_cikmayan_sayisi);

?>

<div class="row">
    <div class="col-sm">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo $mesai_cikmayan_sayisi; ?></h3>

                <p>Mesaide Olan</p>
            </div>
            <div class="icon">
                <i class="ion ion-person"></i>
            </div>
            <button  data-modal="listeKapsa" data-islem="personelListesiGetir" data-sorgu="mesaideOlan" data-url="./_modul/ajax/ajax_data.php" class=" btn w-100 rounded-0 listeGetir small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></button>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-sm">
        <!-- small box -->
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?php echo $gelenler_sayisi - $mesai_cikmayan_sayisi; ?></h3>

                <p>Mesai Çıkış</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <button  data-modal="listeKapsa" data-islem="personelListesiGetir" data-sorgu="mesaiCikis" data-url="./_modul/ajax/ajax_data.php"  class=" btn w-100 rounded-0 listeGetir small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></button>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-sm">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo $izinli_personel_sayisi; ?></h3>

                <p>İzinli</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <button  data-modal="listeKapsa" data-islem="personelListesiGetir" data-sorgu="izinli" data-url="./_modul/ajax/ajax_data.php"  class=" btn w-100 rounded-0 listeGetir small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></button>
        </div>
    </div>

    <!-- ./col -->
    <div class="col-sm">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo $gelmeyenler_sayisi; ?></h3>
                <p>Gelmeyen Personel</p>
            </div>
            <div class="icon">
                <i class="ion ion-person"></i>
            </div>
            <button  data-modal="listeKapsa" data-islem="personelListesiGetir" data-sorgu="gelmeyen" data-url="./_modul/ajax/ajax_data.php" class=" btn w-100 rounded-0 listeGetir small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></button>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-sm">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3><?php echo $ay_icerisinde_giris_yapan; ?></h3>

                <p>Ay içinde İşe Giriş Yapan</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <button  data-modal="listeKapsa" data-islem="personelListesiGetir" data-sorgu="iseGiris" data-url="./_modul/ajax/ajax_data.php" class=" btn w-100 rounded-0 listeGetir small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></button>
        </div>
    </div>
    <div class="col-sm">
        <!-- small box -->
        <div class="small-box bg-dark">
            <div class="inner">
                <h3><?php echo $ay_icerisinde_cikis_yapan; ?></h3>

                <p>Ay içinde İşten Çıkış Yapan</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <button  data-modal="listeKapsa" data-islem="personelListesiGetir" data-sorgu="istenCikis" data-url="./_modul/ajax/ajax_data.php" class=" btn w-100 rounded-0 listeGetir small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></button>
        </div>
    </div>
    

    <div class="col-sm">
        <!-- small box -->
        <div class="small-box bg-pink">
            <div class="inner">
                <h3><?php echo $beyaz_yakali_personel; ?></h3>

                <p>Beyaz Yakalı Personel</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <button  data-modal="listeKapsa" data-islem="personelListesiGetir" data-sorgu="beyazYakali" data-url="./_modul/ajax/ajax_data.php" class=" btn w-100 rounded-0 listeGetir small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></button>
        </div>
    </div>

    <!-- ./col -->
    <div class="col-sm">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?php echo $toplam_personel_sayisi; ?></h3>

                <p>Toplam Personel</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="?modul=personel" class=" small-box-footer">Personel Listesi <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    
</div>

<!-- GENEL AYARLARDAN GİRİŞ ÇIKIŞ LİSTERİ GÖSTER EVET OLARAK İŞARETLENMİŞ İSE LİSTELER GORUNTULENECEK -->
<?php if ( $genel_ayarlar[ "giris_cikis_liste_goster" ] == 1 ) { ?>
<div class="row">
    <div class="col-12 col-sm-6">
        <div class="card  card-tabs card-yukseklik">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-pills nav-tabs tab-container" id="custom-tabs-two-tab" role="tablist" style="padding: 10px 0px 15px 0px;">
                    <li class="pt-2 px-3"><h3 class="card-title"><b>Bekleyen Tutanaklar</b></h3></li>
                    <li class="nav-item">
                        <a class="nav-link active" id="custom-tabs-two-home-tab" data-toggle="pill" href="#custom-tabs-two-home" role="tab" aria-controls="custom-tabs-two-home" aria-selected="false">Gelmeyenler <b class=" badge bg-warning"><?php echo count( $gelmeyenler_listesi ) + count( $gelmeyen_tutanak_listesi ); ?></b></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill" href="#custom-tabs-two-profile" role="tab" aria-controls="custom-tabs-two-profile" aria-selected="false">Geç Gelenler <b class="badge bg-warning"><?php echo count( $gec_gelenler_listesi ) + count( $gecgelen_tutanak_listesi ); ?></b></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-two-messages-tab" data-toggle="pill" href="#custom-tabs-two-messages" role="tab" aria-controls="custom-tabs-two-messages" aria-selected="false">Erken Çıkanlar <b class="badge bg-warning"><?php echo count( $erken_cikanlar_listesi ) + count( $erkencikan_tutanak_listesi ); ?></b></a>
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
                                <th width="50">Tarih</th>
                                <th width="30">Yazdırma</th>
                                <th width="100">İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($gelmeyenler_listesi as $personel) { ?>
                                    <tr>
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $personel["adsoyad"]; ?></td>
                                        <td><?php echo date( 'd.m.Y' ); ?></td>
                                        <td  class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type                 = "checkbox"
                                                data-personel_id     = "<?php echo $personel[ 'id' ]; ?>" 
                                                data-tutanak_id      = ""
                                                data-tip             = "gunluk" 
                                                data-ad              = "<?php echo $personel[ 'adsoyad' ]; ?>"
                                                data-tarih           = "<?php echo date( 'Y-m-d' ); ?>"
                                                class                = "yazdirma"
                                                id                   = "<?php echo $sayi.'-'.$personel[ "id" ]; ?>">
                                                <label for="<?php echo $sayi.'-'.$personel[ "id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td >
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="anasayfa" yetki_islem="tutanakYaz" target="_blank"  href="?modul=tutanakolustur&personel_id=<?php echo $personel[ 'id' ]; ?>&tarih=<?php echo date("Y-m-d"); ?>&tip=gunluk" class="btn btn-warning btn-xs  float-left">PDF Al</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php $sayi++; } ?>

                                <?php foreach ($gelmeyen_tutanak_listesi as $tutanak_personel) { ?>
                                    <tr class="personel-Tr<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $tutanak_personel["adi"].' '.$tutanak_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $tutanak_personel[ 'tarih' ] ) ); ?></td>
                                        <td  class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id     = "<?php echo $tutanak_personel[ 'personel_id' ]; ?>" 
                                                data-tutanak_id      = "<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>"
                                                data-tip             = "gunluk" 
                                                data-ad              = "<?php echo $personel[ 'adi' ].' '.$personel["soyadi"]; ?>"
                                                data-tarih           = "<?php echo $tutanak_personel[ 'tarih' ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "<?php echo $sayi.'-'.$tutanak_personel[ "personel_id" ]; ?>">
                                                <label for="<?php echo $sayi.'-'.$tutanak_personel[ "personel_id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td >
                                            <button class="btn btn-danger tutanakSil float-left btn-xs mr-1 " modul="anasayfa" yetki_islem="tutanakSil" data-islem="tutanakSil" data-url="./_modul/ajax/ajax_data.php" data-id="<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>">Kaydı Sil</button>
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="anasayfa" yetki_islem="tutanakYaz" target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $tutanak_personel[ 'personel_id' ]; ?>&tarih=<?php echo $tutanak_personel[ 'tarih' ]; ?>&tip=gunluk" class="btn btn-warning btn-xs  float-left">PDF Al</a>
                                            <?php } ?>
                                        </td>
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
                                <th width="50">Tarih</th>
                                <th width="30">Yazdırma</th>
                                <th width="100">İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($gec_gelenler_listesi as $personel) { ?>
                                    <tr>
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $personel["adsoyad"]; ?></td>
                                        <td><?php echo date( 'd.m.Y' ); ?></td>
                                        <td  class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $personel[ 'id' ]; ?>" 
                                                data-tutanak_id     = ""
                                                data-tip            = "gecgelme" 
                                                data-ad             = "<?php echo $personel[ 'adsoyad' ]; ?>"
                                                data-tarih          = "<?php echo date( 'Y-m-d' ); ?>"
                                                data-saat           = "<?php echo $personel[ "baslangic_saat" ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "gecgelme-<?php echo $sayi.'-'.$personel[ "id" ]; ?>">
                                                <label for="gecgelme-<?php echo $sayi.'-'.$personel[ "id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td >
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?> 
                                                <a trget="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $personel[ 'id' ]; ?>&tarih=<?php echo date("Y-m-d"); ?>&tip=gecgelme&saat=<?php echo $personel[ "baslangic_saat" ] ?>" class="btn btn-warning btn-xs  float-left">PDF Al</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php $sayi++; } ?>

                                <?php foreach ($gecgelen_tutanak_listesi as $gecgelen_personel) { ?>
                                    <tr class="personel-Tr<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $gecgelen_personel[ 'tarih' ] ) ); ?></td>
                                        <td  class="text-center">
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
                                        <td >
                                        <button class="btn btn-danger tutanakSil float-left btn-xs mr-1 " modul="anasayfa" yetki_islem="tutanakSil" data-islem="tutanakSil" data-url="./_modul/ajax/ajax_data.php" data-id="<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>">Kaydı Sil</button>
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="anasayfa" yetki_islem="tutanakYaz" target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $gecgelen_personel[ 'personel_id' ]; ?>&tarih=<?php echo $gecgelen_personel[ 'tarih' ]; ?>&tip=gecgelme&saat=<?php echo $gecgelen_personel[ 'saat' ]; ?>" class="btn btn-warning btn-xs  float-left">PDF Al</a>
                                            <?php } ?>
                                        </td>
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
                                <th width="50">Tarih</th>
                                <th width="30">Yazdırma</th>
                                <th width="100">İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($erken_cikanlar_listesi as $personel) { ?>
                                    <tr>
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $personel["adsoyad"]; ?></td>
                                        <td><?php echo date( 'd.m.Y' ); ?></td>
                                        <td  class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $personel[ 'id' ]; ?>" 
                                                data-tutanak_id     = ""
                                                data-tip            = "erkencikma" 
                                                data-ad             = "<?php echo $personel[ 'adsoyad' ]; ?>"
                                                data-tarih          = "<?php echo date( 'Y-m-d' ); ?>"
                                                data-saat           = "<?php echo $personel[ "bitis_saat" ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "erkencikma-<?php echo $sayi.'-'.$personel[ "id" ]; ?>">
                                                <label for="erkencikma-<?php echo $sayi.'-'.$personel[ "id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td >
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="anasayfa" yetki_islem="tutanakYaz" target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $personel[ 'id' ]; ?>&tarih=<?php echo date("Y-m-d"); ?>&tip=erkencikma&saat=<?php echo $personel[ "bitis_saat" ]; ?>" class="btn btn-warning btn-xs  float-left">PDF Al</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php $sayi++; } ?>

                                <?php foreach ($erkencikan_tutanak_listesi as $erkencikan_personel) { ?>
                                    <tr class="personel-Tr<?php echo $erkencikan_personel[ 'tutanak_id' ]; ?>">
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $erkencikan_personel["adi"].' '.$erkencikan_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $erkencikan_personel[ 'tarih' ] ) ); ?></td>
                                        <td  class="text-center">
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
                                        <td >
                                        <button class="btn btn-danger tutanakSil float-left btn-xs mr-1 " modul="anasayfa" yetki_islem="tutanakSil" data-islem="tutanakSil" data-url="./_modul/ajax/ajax_data.php" data-id="<?php echo $erkencikan_personel[ 'tutanak_id' ]; ?>">Kaydı Sil</button>
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="anasayfa" yetki_islem="tutanakYaz" target="_blank" href="?modul=erkencikanolustur&personel_id=<?php echo $erkencikan_personel[ 'personel_id' ]; ?>&tarih=<?php echo $erkencikan_personel[ 'tarih' ]; ?>&tip=erkencikma&saat=<?php echo $erkencikan_personel[ 'saat' ]; ?>" class="btn btn-warning btn-xs  float-left">PDF Al</a>
                                            <?php } ?>
                                        </td>
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
        <div class="card  card-tabs card-yukseklik" id="yazdirilanTutanaklar">
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
                                <th width="50">Tarih</th>
                                <th width="30">Yazdırma</th>
                                <th width="100">İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($yazdirilan_gelmeyen_tutanak_listesi as $tutanak_personel) { ?>
                                    <tr class                = " personel-Tr personel-Tr<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>" 
                                        data-personel_id     = "<?php echo $tutanak_personel[ 'personel_id' ]; ?>"
                                        data-tutanak_id      = "<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>"
                                        data-tip             = "gunluk"
                                        data-ad              = "<?php echo $tutanak_personel["adi"].' '.$tutanak_personel["soyadi"]; ?>"
                                        data-tarih           = "<?php echo $tutanak_personel[ 'tarih' ]; ?>">
                                        <td><?php echo $sayi; ?></td>
                                        <td><?php echo $tutanak_personel["adi"].' '.$tutanak_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $tutanak_personel[ 'tarih' ] ) ); ?></td>
                                        <td class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                checked
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $tutanak_personel[ 'personel_id' ]; ?>"
                                                data-tutanak_id     = "<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>"
                                                data-tip            = "gunluk"
                                                data-ad             = "<?php echo $tutanak_personel["adi"].' '.$tutanak_personel["soyadi"]; ?>"
                                                data-tarih          = "<?php echo $tutanak_personel[ 'tarih' ]; ?>"
                                                data-saat           = ""
                                                class               = "yazdirma"
                                                id                  = "<?php echo $sayi.'-'.$tutanak_personel[ "personel_id" ]; ?>">
                                                <label for="<?php echo $sayi.'-'.$tutanak_personel[ "personel_id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="150px" class="text-center">
                                            <button data-id="<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>" class="btn btn-xs btn-dark personel-tutanak-aktar"><i class="fas fa-upload"></i></button>
                                            <button class="btn btn-danger tutanakSil float-left btn-xs mr-1 " modul="anasayfa" yetki_islem="tutanakSil" data-islem="tutanakSil" data-url="./_modul/ajax/ajax_data.php" data-id="<?php echo $tutanak_personel[ 'tutanak_id' ]; ?>">Kaydı Sil</button>
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="anasayfa" yetki_islem="tutanakYaz" target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $tutanak_personel[ 'personel_id' ]; ?>&tarih=<?php echo $tutanak_personel[ 'tarih' ]; ?>&tip=gunluk" class="btn btn-warning btn-xs  float-left">PDF Al</a>
                                            <?php } ?>
                                        </td>
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
                                <th>Yazdırma</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($yazdirilan_gecgelen_tutanak_listesi as $gecgelen_personel) { ?>
                                    <tr class               = "personel-Tr personel-Tr<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>" 
                                        data-personel_id    = "<?php echo $gecgelen_personel[ 'personel_id' ]; ?>"
                                        data-tutanak_id     = "<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>"
                                        data-tip            = "gecgelme"
                                        data-ad             = "<?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?>"
                                        data-tarih          = "<?php echo $gecgelen_personel[ 'tarih' ]; ?>"
                                        data-saat           = "<?php echo $gecgelen_personel[ 'saat' ]; ?>">
                                        <td><?php echo $sayi; ?></td>
                                        <td><?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $gecgelen_personel[ 'tarih' ] ) ); ?></td>
                                        <td class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                checked
                                                type="checkbox"
                                                data-personel_id    = "<?php echo $gecgelen_personel[ 'personel_id' ]; ?>"
                                                data-tutanak_id     = "<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>"
                                                data-tip            = "gecgelme"
                                                data-ad             = "<?php echo $gecgelen_personel["adi"].' '.$gecgelen_personel["soyadi"]; ?>"
                                                data-tarih          = "<?php echo $gecgelen_personel[ 'tarih' ]; ?>"
                                                data-saat           = "<?php echo $gecgelen_personel[ 'saat' ]; ?>"
                                                class                = "yazdirma"
                                                id                   = "<?php echo $sayi.'-'.$gecgelen_personel[ "personel_id" ]; ?>">
                                                <label for="<?php echo $sayi.'-'.$gecgelen_personel[ "personel_id" ]; ?>"></label>
                                            </div>
                                        </td>
                                        <td width="150px" class="text-center">
                                            <button data-id="<?php echo $tutanak_personel[ 'tutanak_id' ] ?>" class="btn btn-xs btn-dark personel-tutanak-aktar"><i class="fas fa-upload"></i></button>
                                            <button class="btn btn-danger tutanakSil float-left btn-xs mr-1" modul="anasayfa" yetki_islem="tutanakSil" data-islem="tutanakSil" data-url="./_modul/ajax/ajax_data.php" data-id="<?php echo $gecgelen_personel[ 'tutanak_id' ]; ?>">Kaydı Sil</button>
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="anasayfa" yetki_islem="tutanakYaz" target="_blank" href="?modul=tutanakolustur&personel_id=<?php echo $gecgelen_personel[ 'personel_id' ]; ?>&tarih=<?php echo $gecgelen_personel[ 'tarih' ]; ?>&tip=gecgelme&saat=<?php echo $gecgelen_personel[ 'saat' ]; ?>" class="btn btn-warning btn-xs  float-left">PDF Al</a>
                                            <?php } ?>
                                        </td>
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
                                <th width="20">Tarih</th>
                                <th width="70">Yazdırma</th>
                                <th width="30">İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($yazdirilan_erkencikan_tutanak_listesi as $erkencikan_personel) { ?>
                                    <tr class               = "personel-Tr personel-Tr<?php echo $erkencikan_personel[ 'tutanak_id' ]; ?>" 
                                        data-personel_id    = "<?php echo $erkencikan_personel[ 'personel_id' ]; ?>"
                                        data-tutanak_id     = "<?php echo $erkencikan_personel[ 'tutanak_id' ]; ?>"
                                        data-tip            = "erkencikma"
                                        data-ad             = "<?php echo $erkencikan_personel["adi"].' '.$erkencikan_personel["soyadi"]; ?>"
                                        data-tarih          = "<?php echo $erkencikan_personel[ 'tarih' ]; ?>"
                                        data-saat           = "<?php echo $erkencikan_personel[ 'saat' ]; ?>">
                                        <td><?php echo $sayi; ?></td>
                                        <td><?php echo $erkencikan_personel["adi"].' '.$erkencikan_personel["soyadi"]; ?></td>
                                        <td><?php echo date( 'd.m.Y', strtotime( $erkencikan_personel[ 'tarih' ] ) ); ?></td>
                                        <td class="text-center">
                                            <div class="icheck-primary d-inline ml-2">
                                                <input
                                                checked
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
                                        <td width="150px" class="text-center">
                                            <button data-id="<?php echo $tutanak_personel[ 'tutanak_id' ] ?>" class="btn btn-xs btn-dark personel-tutanak-aktar"><i class="fas fa-upload"></i></button>
                                            <button class="btn btn-danger tutanakSil float-left btn-xs mr-1" modul="anasayfa" yetki_islem="tutanakSil" data-islem="tutanakSil" data-url="./_modul/ajax/ajax_data.php" data-id="<?php echo $erkencikan_personel[ 'tutanak_id' ]; ?>">Kaydı Sil</button>
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="anasayfa" yetki_islem="tutanakYaz" target="_blank" href="?modul=erkencikanolustur&personel_id=<?php echo $erkencikan_personel[ 'personel_id' ]; ?>&tarih=<?php echo $erkencikan_personel[ 'tarih' ]; ?>&tip=erkencikma&saat=<?php echo $erkencikan_personel[ 'saat' ]; ?>" class="btn btn-warning btn-xs  float-left">PDF Al</a>
                                            <?php } ?>
                                        </td>
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
                    <form enctype="multipart/form-data" method="POST"  name="mainFileUploader" class="" id="dropzonform">
                        <div class="form-group">
                            <input type="text" name="aciklama" id="aciklama" class="form-control" placeholder="Acıklama Kısmı">
                        </div>
                        <div class="dropzone" action="_modul/tutanakolustur/tutanakolusturSEG.php"  id="dropzone" style="min-height: 247px;">
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
<?php } ?>

<!--Süresi Doalcak Firma Dosyaları Başlangıc Kodları-->
<div class="row">
    <div class="col-12 col-sm-6">
        <div class="card  card-tabs card-yukseklik">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-pills nav-tabs tab-container" id="custom-tabs-two-tab" role="tablist" style="padding: 10px 0px 15px 0px;">
                    <li class="pt-2 text-left"><h3 class="card-title"><b>Süresi Dolacak Evraklar</b></h3></li>
                    <li class="nav-item">
                        <a class="nav-link active" id="kategori" data-toggle="pill" href="#kategori-tab" role="tab" aria-controls="kategori-tab" aria-selected="false">Kategoriler <b class=" badge bg-warning"><?php echo count( $suresi_dolmus_kategori ); ?></b></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="dosya" data-toggle="pill" href="#dosya-tab" role="tab" aria-controls="dosya-tab" aria-selected="false">Dosyalar <b class="badge bg-warning"><?php echo count( $suresi_dolmus_dosya ); ?></b></a>
                    </li>
                </ul>
            </div>
            <div class="card-body direct-chat-messages" style="height:auto; min-height: 333px; max-height: 530px; ">
                <div class="tab-content" id="custom-tabs-two-tabContent">
                    <div class="tab-pane fade active show" id="kategori-tab" role="tabpanel" aria-labelledby="kategori">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_gelmeyenler" style="width: 100%;">
                            <thead>
                                <th>#</th>
                                <th>Adı</th>
                                <th>Konum</th>
                                <th >Tarih</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($suresi_dolmus_kategori as $evrak) { ?>
                                    <tr>
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $evrak["adi"]; ?></td>
                                        <td>
                                            <?php
                                                $konum = $fn->kategoriHiyerarsiAdi( $evrak[ "kategori" ] );
                                                echo $konum == "" ? "Kategori Yok": $konum;
                                            ?>
                                        </td>
                                        <td  class="text-center">
                                            <?php
                                                $suanki_tarih 		= date_create(date('Y-m-d'));
                                                $hatirlanacak_tarih = date_create($evrak[ 'tarih' ]);
                                                
                                                    $kalan_gun 			= date_diff($suanki_tarih,$hatirlanacak_tarih);
                                                    $isaret = $kalan_gun->format("%R") == "+" ? 'Kaldı' : 'Geçti';
                                                    $renk = $kalan_gun->format("%R") == "+" ? 'success' : 'danger';
                                                    echo "<span class='text-$renk'>".$kalan_gun->format("%a Gün ").$isaret."</span>";
                                            ?>
                                        </td>
                                        <td >
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="firmaDosyalari" yetki_islem="evraklar"  href="?modul=firmaDosyalari&islem=evraklar&ust_id=<?php echo $evrak[ "kategori" ]; ?>&kategori_id=<?php echo $evrak[ "id" ]; ?>&dosyaTuru_id=<?php echo $evrak[ "id" ]; ?>&alt-liste=<?php echo $fn->kategoriHiyerarsiId( $evrak[ "id" ] ); ?>" class="btn btn-warning btn-xs">Evraklar</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php $sayi++; } ?>

                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="dosya-tab" role="tabpanel" aria-labelledby="dosya">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_gec_gelenler" style="width: 100%;">
                            <thead>
                                <th>#</th>
                                <th>Adı</th>
                                <th>Konum</th>
                                <th >Tarih</th>
                                <th>İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($suresi_dolmus_dosya as $evrak) { ?>
                                    <tr>
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $evrak["adi"]; ?></td>
                                        <td>
                                            <?php
                                                $konum = $fn->kategoriHiyerarsiAdi( $evrak[ "kategori" ] );
                                                echo $konum == "" ? "Kategori Yok": $konum;
                                            ?>
                                        </td>
                                        <td  class="text-center">
                                            <?php
                                                $suanki_tarih 		= date_create(date('Y-m-d'));
                                                $hatirlanacak_tarih = date_create($evrak[ 'tarih' ]);
                                                
                                                    $kalan_gun 	= date_diff($suanki_tarih,$hatirlanacak_tarih);
                                                    $isaret     = $kalan_gun->format("%R") == "+" ? 'Kaldı' : 'Geçti';
                                                    $renk       = $kalan_gun->format("%R") == "+" ? 'success' : 'danger';
                                                    echo "<span class='text-$renk'>".$kalan_gun->format("%a Gün ").$isaret."</span>";
                                            ?>
                                        </td>
                                        <td >
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="firmaDosyalari" yetki_islem="evraklar"  href="?modul=firmaDosyalari&islem=evraklar&kategori_id=<?php echo $evrak[ "kategori" ]; ?>&dosyaTuru_id=<?php echo $evrak[ "kategori" ]; ?>&alt-liste=<?php echo $fn->kategoriHiyerarsiId( $evrak[ "kategori" ] ); ?>" class="btn btn-warning btn-xs">Evraklar</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php $sayi++; } ?>                              
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6">
        <div class="card  card-tabs card-yukseklik card-danger">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-pills nav-tabs pl-3" id="custom-tabs-two-tab" role="tablist" style="padding: 10px 0px 15px 0px;">
                    <li class="pt-2"><h3 class="card-title"><b>Ay İçerisinde Eksik Hareket Olan Personel Listesi</b></h3></li>
                </ul>
            </div>
            <div class="card-body direct-chat-messages" style="height:auto; min-height: 333px; max-height: 530px; ">
                <div class="tab-content" id="custom-tabs-two-tabContent">
                    <div class="tab-pane fade active show" id="kategori-tab" role="tabpanel" aria-labelledby="kategori">
                        <table class="table table-bordered table-hover table-sm dataTable no-footer dtr-inline" id="tbl_gelmeyenler" style="width: 100%;">
                            <thead>
                                <th>#</th>
                                <th>Adı Soyadı</th>
                                <th class="text-center">Tarih</th>
                                <th class="text-center" width="150">İşlem</th>
                            </thead>
                            <tbody>
                                <?php $sayi = 1; foreach ($eksik_hareket_olan_personel as $hareket) { ?>
                                    <tr>
                                        <td width="20"><?php echo $sayi; ?></td>
                                        <td><?php echo $hareket["adisoyadi"]; ?></td>
                                        <td class="text-center" ><?php echo $hareket["tarih"]; ?></td>
                                        <td width="120" class="text-center">
                                            <?php if ( $genel_ayarlar[ 'tutanak_olustur' ] == 1 ) { ?>
                                                <a modul="giriscikis" yetki_islem="duzenle"  href="?modul=giriscikis&personel_id=<?php echo $hareket['personel_id']; ?>&tarih=<?php echo $hareket[ "YAtarih" ]; ?>&duzenlenecek_tarih=<?php echo $hareket[ "YAGtarih" ]; ?>&islem=saatduzenle" class="btn btn-warning btn-xs">Personel Hareketine Git</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php $sayi++; } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="overflow-hidden" id="listeKapsa"></div>
<style type="text/css">
    .tab-container .nav-link.active{
        border: 1px solid transparent;
    }
</style>

<script type="text/javascript">

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
                    const yenile = setTimeout(sayfa_yenile, 0);
                    function sayfa_yenile() {
                        location.reload();
                    }
                }
            }

        })
    });

    $( "body" ).on('click', '.personel-tutanak-aktar', function() {
        var id = $(this).data("id");
        $("#DosyaAlani").fadeToggle(500);

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
        $(".personel-Tr"+id).addClass("table-warning");

        //Satıra ait data verileri çekiyoruz
        var personel_id =  $(".personel-Tr"+id).data( "personel_id" );
        var tutanak_id  =  $(".personel-Tr"+id).data( "tutanak_id" );
        var ad          =  $(".personel-Tr"+id).data( "ad" ); 
        var tip         =  $(".personel-Tr"+id).data( "tip" ); 
        var tarih       =  $(".personel-Tr"+id).data( "tarih" ); 
        var saat        =  $(".personel-Tr"+id).data( "saat" );

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

    $('.listeGetir').on("click", function(e) { 
        var data_url    = $(this).data("url");
        var modal       = $(this).data("modal");
        var islem       = $(this).data("islem");
        var sorgu       = $(this).data("sorgu");
        $("#" + modal).empty();
        $.post(data_url, { islem : islem, sorgu : sorgu }, function (response) {
            $("#" + modal).append(response);
            personelListesi_dataTable();
            $("#liste").modal("show");
            
        });
    });

    
    $('.tutanakSil').on("click", function(e) { 
        var data_url    = $(this).data("url");
        var islem       = $(this).data("islem");
        var id          = $(this).data("id");
        if (confirm('Tutanak tutma işlemi silinecektir. Bu işlem geri getirilmez. Onaylıyor musunuz?')) {
            $.post(data_url, { islem : islem, id : id }, function (response) {
                if(response == 1){
                    $(".personel-Tr"+id).remove();
                }
            });
        }
        
    });


    
    function personelListesi_dataTable(){    
        $( "#personelListesi" ).DataTable( {
            "responsive": true,
            "lengthChange": true, 
            "autoWidth": false,
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
    }
    
    <?php if ( $genel_ayarlar[ "giris_cikis_liste_goster" ] == 1 ) { ?>
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
    <?php } ?>

</script>