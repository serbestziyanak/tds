<?php 
/*
AYARLANACAK CRON JOP ZAAMANINA GÖRE FİRMANIN PERSONELİNİ KONTROL EDECEK VE O GUNGU TARİFEYE GÖRE GİRİŞ ÇIKIŞLARI KONTROL EDİP TUTANAKLARI KAYDEDECEKTİR
*/

include "../../_cekirdek/fonksiyonlar.php";
$fn = new Fonksiyonlar();
$vt = new VeriTabani();
error_reporting(0);

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

$tarih  = array_key_exists("tarih", $_REQUEST) ? $_REQUEST["tarih"] : date("Y-m-d");
$gun    = $fn->gunVer( $tarih ); 

$firmalar       = $vt->select( $SQL_tum_firmalar,array() ) [2];
$vt->islemBaslat();
foreach ($firmalar as $firma) {

    $tum_personel                           = $vt->select( $SQL_tum_personel,array( $firma[ "id" ] ) ) [2];

    $erken_cikanlar_listesi                 = $fn->erkenCikanlarListesi( $tarih, '%,'.$gun.',%');
    $gec_gelenler_listesi                   = $fn->gecGelenlerListesi( $tarih, '%,'.$gun.',%');
    $gelmeyenler_listesi                    = $fn->gelmeyenlerListesi( $tarih );

    foreach( $erken_cikanlar_listesi as $personel ){
        $personele_ait_tutanak_dosyasi_var_mi   = $vt->select( $SQL_tek_tutanak_oku,array( "erkencikma", $personel[ 'id' ], $tarih ) ) [2];
        $personel_tabloya_eklendi_mi            = $vt->select( $SQL_tutanak_varmi,array( $firma[ "id" ], $personel[ 'id' ],$tarih, 'erkencikma' )  ) [2];      
        if ( count( $personele_ait_tutanak_dosyasi_var_mi ) <= 0 AND count( $personel_tabloya_eklendi_mi ) <= 0 ) {
            $degerler       = array(  $firma[ "id" ], $personel[ "id" ], $tarih, "", "gunluk", date("Y-m-d H:i:s"), 0 );
            $tutanak_Ekle   = $vt->insert( $SQL_tutanak_kaydet, $degerler );
        }
    }
    
    foreach( $gec_gelenler_listesi as $personel ){
        $personele_ait_tutanak_dosyasi_var_mi   = $vt->select( $SQL_tek_tutanak_oku,array( "gecgelme", $personel[ 'id' ], $tarih ) ) [2];
        $personel_tabloya_eklendi_mi            = $vt->select( $SQL_tutanak_varmi,array( $firma[ "id" ], $personel[ 'id' ],$tarih, 'gecgelme' )  ) [2];
        if ( count( $personele_ait_tutanak_dosyasi_var_mi ) <= 0 AND count( $personel_tabloya_eklendi_mi ) <= 0  ) {
            $degerler       = array(  $firma[ "id" ], $personel[ "id" ], $tarih, $ilkGirisSaat[ 0 ], "gecgelme", date("Y-m-d H:i:s"), 0 );
            $tutanak_Ekle   = $vt->insert( $SQL_tutanak_kaydet, $degerler );
        }
    }

    foreach( $gelmeyenler_listesi as $personel ){
        //PErsonel Hiç Gelmemiş ise
        $personele_ait_tutanak_dosyasi_var_mi   = $vt->select( $SQL_tek_tutanak_oku,array( "gunluk", $personel[ 'id' ], $tarih ) ) [2];
        $personel_tabloya_eklendi_mi            = $vt->select( $SQL_tutanak_varmi,array( $firma[ "id" ], $personel[ 'id' ],$tarih, 'gunluk'  ) ) [2];
        //tutanak dosyaları eklenmisse 
        if ( count( $personele_ait_tutanak_dosyasi_var_mi ) <= 0 AND count( $personel_tabloya_eklendi_mi ) <= 0 ) {
            $degerler       = array(  $firma[ "id" ], $personel[ "id" ], $tarih, "", "gunluk", date("Y-m-d H:i:s"), 0 );
            $tutanak_Ekle   = $vt->insert( $SQL_tutanak_kaydet, $degerler );
        }
    }
}
$vt->islemBitir();