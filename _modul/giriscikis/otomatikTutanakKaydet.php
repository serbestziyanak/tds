<?php 
include "../../_cekirdek/fonksiyonlar.php";
$fn = new Fonksiyonlar();
$vt = new VeriTabani();
error_reporting( E_ALL );

$SQL_tum_personel = <<< SQL
SELECT
    id
    ,adi
    ,soyadi
    ,grup_id
FROM
    tb_personel AS p
WHERE
    p.firma_id  = ? AND 
    p.aktif     = 1 
SQL;


$SQL_tum_firmalar = <<< SQL
SELECT
    *
FROM
    tb_firmalar
WHERE
    aktif     = 1 
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
    t1.grup_id = ? AND 
    t1.carpan   =   ( 
        SELECT 
            MAX(carpan) 
        from
            tb_tarifeler AS t
        LEFT JOIN tb_mesai_turu AS mt ON  t.mesai_turu = mt.id
        WHERE 
            baslangic_tarih <= ? AND 
            bitis_tarih >= ? AND 
            mt.gunler LIKE ? AND 
            t.firma_id = ? AND 
            t.grup_id = ? AND 
            t.aktif = 1
    )
            
SQL;

//Tutanak Kaydetme
$SQL_tutanak_kaydet = <<< SQL
INSERT INTO
    tb_tutanak
SET
     firma_id       = ?
    ,personel_id    = ?
    ,tarih          = ?
    ,saat           = ?
    ,tip            = ?
    ,ekleme_tarihi  = ?
    ,yazdirma       = ?
SQL;
    

$firmalar       = $vt->select( $SQL_tum_firmalar,array() ) [2];
    
foreach ($firmalar as $firma) {

    $tum_personel                           = $vt->select( $SQL_tum_personel,array( $firma[ "id" ] ) ) [2];
    $icerde_olan_personel                   = $vt->select( $SQL_icerde_olan_personel,array( $firma[ "id" ], date( "Y-m-d" ) ) ) [2];

    foreach ($tum_personel as $personel) {

        $gun = $fn->gunVer( date("Y-m-d") );

        $giris_cikis_saat_getir = $vt->select( $SQL_giris_cikis_saat, array( date("Y-m-d"), date("Y-m-d"), '%,'.$gun.',%', $personel["grup_id"], date("Y-m-d"), date("Y-m-d"), '%,'.$gun.',%', $_SESSION['firma_id'], $personel["grup_id"] ) ) [ 2 ][ 0 ];

        //Mesaiye 10 DK gec Gelme olasıılıgını ekledik 10 dk ya kadaar gec gelebilir 
        $mesai_baslangic    = date("H:i", strtotime('+10 minutes', strtotime( $giris_cikis_saat_getir["mesai_baslangic"] ) ) );
        //Personel 5 DK  erken çıkabilir
        $mesai_bitis        = date("H:i", strtotime('-5 minutes',  strtotime( $giris_cikis_saat_getir["mesai_bitis"] ) ) );
        //Eger Tatil Olarak İsaretlenmisse Giriş Zorunluluğu bulunmayıp mesaiye gelmisse mesai yazdıracaktır.
        $tatil = $giris_cikis_saat_getir["tatil"] == 1  ?  'evet' : 'hayir';


        //Personel bugun giriş veya çıkış yapmış mı kontrolünü sağlıyoruz
        $personel_giris_cikis_saatleri      = $vt->select($SQL_belirli_tarihli_giris_cikis,array( $personel[ 'id' ],date("Y-m-d"),$_SESSION[ 'firma_id' ] ) )[2];

        if ( count($personel_giris_cikis_saatleri) < 1 ) {

            /*Bugun Tatil Degilse personelli gelmeyenler listesine al*/
            if ( $tatil == 'hayir' ) {
                //PErsonel Hiç Gelmemiş ise
                $personele_ait_tutanak_dosyasi_var_mi   = $vt->select( $SQL_tek_tutanak_oku,array( "gunluk", $personel[ 'id' ], date("Y-m-d") ) ) [2];
                $personel_tabloya_eklendi_mi            = $vt->select( $SQL_tutanak_varmi,array( $_SESSION['firma_id'], $personel[ 'id' ],date("Y-m-d"), 'gunluk'  ) ) [2];
                //tutanak dosyaları eklenmisse 
                if ( count( $personele_ait_tutanak_dosyasi_var_mi ) <= 0 AND count( $personel_tabloya_eklendi_mi ) <= 0 ) {
                    $degerler       = array(  $firma[ "id" ], $personel[ "id" ], date( "Y-m-d" ), "", "gunluk", date("Y-m-d H:i:s"), 0 );
                    $tutanak_Ekle   = $vt->insert( $SQL_tutanak_kaydet, $degerler );
                }
            }
        }else{

            $personel_giris_cikis_sayisi    = count($personel_giris_cikis_saatleri);

            //Personelin En erken giriş saati ve en geç çıkış saatini alıyoruz ona göre tutanak olusturulacak
            $son_cikis_index                = $personel_giris_cikis_sayisi - 1;
            $ilk_islemtipi                  = $personel_giris_cikis_saatleri[0]['islem_tipi'];
            $son_islemtipi                  = $personel_giris_cikis_saatleri[$son_cikis_index]['islem_tipi'];

            $ilkGirisSaat                   = $fn->saatKarsilastir($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ], $personel_giris_cikis_saatleri[0]["baslangic_saat_guncellenen"]);

            $SonCikisSaat                   = $fn->saatKarsilastir($personel_giris_cikis_saatleri[$son_cikis_index][ 'bitis_saat' ], $personel_giris_cikis_saatleri[$son_cikis_index]["bitis_saat_guncellenen"]);

            if ($ilkGirisSaat[0] > $mesai_baslangic AND ( $ilk_islemtipi == "" or $ilk_islemtipi == "0" )  ) {
                $personele_ait_tutanak_dosyasi_var_mi   = $vt->select( $SQL_tek_tutanak_oku,array( "gecgelme", $personel[ 'id' ], date("Y-m-d") ) ) [2];
                $personel_tabloya_eklendi_mi            = $vt->select( $SQL_tutanak_varmi,array( $_SESSION['firma_id'], $personel[ 'id' ],date("Y-m-d"), 'gecgelme' )  ) [2];
                if ( count( $personele_ait_tutanak_dosyasi_var_mi ) <= 0 AND count( $personel_tabloya_eklendi_mi ) <= 0  ) {
                    $degerler       = array(  $firma[ "id" ], $personel[ "id" ], date( "Y-m-d" ), $ilkGirisSaat[ 0 ], "gecgelme", date("Y-m-d H:i:s"), 0 );
                    $tutanak_Ekle   = $vt->insert( $SQL_tutanak_kaydet, $degerler );
                }
                
            }

            if ($SonCikisSaat[0] < $mesai_bitis AND $SonCikisSaat[0] != " - " AND ( $son_islemtipi == "" or $son_islemtipi == "0" ) ) {
                
                $personele_ait_tutanak_dosyasi_var_mi   = $vt->select( $SQL_tek_tutanak_oku,array( "erkencikma", $personel[ 'id' ], date("Y-m-d") ) ) [2];
                $personel_tabloya_eklendi_mi            = $vt->select( $SQL_tutanak_varmi,array( $_SESSION['firma_id'], $personel[ 'id' ],date("Y-m-d"), 'erkencikma' )  ) [2];
                if ( count( $personele_ait_tutanak_dosyasi_var_mi ) <= 0 AND count( $personel_tabloya_eklendi_mi ) <= 0 ) {
                    $degerler       = array(  $firma[ "id" ], $personel[ "id" ], date( "Y-m-d" ), $SonCikisSaat[ 0 ], "erkencikma", date("Y-m-d H:i:s"), 0 );
                    $tutanak_Ekle   = $vt->insert( $SQL_tutanak_kaydet, $degerler );
                }
                
            }
        } 
    }
}