<?php 
/*
AYARLANACAK CRON JOP ZAAMANINA GÖRE FİRMAYA AİT AKTİF PERSONELİNİN GRUPLARINI GİRİŞCIKIS TABLOSUNDA GRUP İD Yİ güncelleyecektir.
*/

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

/*Firmaya Ait ayarları çekiyoruz*/
$SQL_genel_ayarlar = <<< SQL
SELECT
    *
FROM
    tb_genel_ayarlar
WHERE
    firma_id     = ? 
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

/*
belirli tarihteki Tarifeye ait giriş cıkış saatlerini veya tatil durumunu getiriyor
*/
$SQL_tarife = <<< SQL
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
    t1.firma_id 	= ? AND
    t1.aktif = 1
ORDER BY t1.id DESC
LIMIT 1      
SQL;

/*Gelmeyen Personel veya tatil günleri için Veri eklenip personel grubu belirtilecektir.*/
$SQL_giris_cikis_ekle = <<< SQL
INSERT INTO 
	tb_giris_cikis 
SET
	personel_id = ?,
	grup_id 	= ?,
	tarih 		= ?
SQL;

/*Personel Grup Guncelle*/
$SQL_grup_guncelle = <<< SQL
UPDATE 
	tb_giris_cikis 
SET
	grup_id = ?
WHERE 
	personel_id = ? AND
	tarih 		= ? 
SQL;

$firmalar       		= $vt->select( $SQL_tum_firmalar,array() ) [2];
   

$tarih = date("Y-m");

$vt->islemBaslat();
while( $sayi <= $toplamGun ){


    $gun = $fn->gunVer( "$tarih-$sayi" );
    
    foreach ($firmalar as $firma) {

        $tum_personel 		= $vt->select( $SQL_tum_personel,array( $firma[ "id" ] ) ) [2];

        foreach ($tum_personel as $personel) {
            
            $personel_giris_cikis_saatleri  = $vt->select($SQL_belirli_tarihli_giris_cikis,array( $personel[ 'id' ],"$tarih-$sayi",$firma[ "id" ] ) )[2];
            
            $tarife_getir = $vt->select( $SQL_tarife, array( "$tarih-$sayi", "$tarih-$sayi", '%,'.$gun.',%', '%,'.$personel["grup_id"].',%',$firma[ "id" ] ) ) [ 2 ];

            if ( count( $tarife_getir ) > 0 AND count( $personel_giris_cikis_saatleri ) > 0  ){
                /*Personele ait tarife varsa ve personel giriş yapmış ise grubu girise kaydet */
                $vt->update($SQL_grup_guncelle, array( $personel[ "grup_id" ], $personel[ "id" ], "$tarih-$sayi" ) );

            }else if ( count( $tarife_getir ) > 0 AND $tarife_getir[0][ "tatil"] == 1 AND  count( $personel_giris_cikis_saatleri ) < 1  ){
                /*  Belirtilen gün tatil ise giriş çıkışa veri ekle*/
                $vt->select($SQL_giris_cikis_ekle, array( $personel[ "id" ],$personel[ "grup_id" ], "$tarih-$sayi" ) );

            }else if ( count( $tarife_getir ) > 0 AND $tarife_getir[0][ "tatil"] == 0 AND  count( $personel_giris_cikis_saatleri ) < 1  ){
                $vt->select( $SQL_giris_cikis_ekle, array( $personel[ "id" ],$personel[ "grup_id" ], "$tarih-$sayi" ) );

            }else if ( count( $tarife_getir ) < 1 AND count( $personel_giris_cikis_saatleri ) < 1 ){
                $vt->select( $SQL_giris_cikis_ekle, array( $personel[ "id" ],$personel[ "grup_id" ], "$tarih-$sayi" ) );
            }
        }
    }
    $sayi++;
}
$vt->islemBitir();