<?php 
/*
AYARLANACAK CRON JOP ZAAMANINA GÖRE FİRMAYA AİT AKTİF PERSONELİNİN GRUPLARINI GİRİŞCIKIS TABLOSUNDA GRUP İD Yİ güncelleyecektir.
*/

include "../../_cekirdek/fonksiyonlar.php";
$fn = new Fonksiyonlar();
$vt = new VeriTabani();
error_reporting( 0 );

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
    p.aktif     = 1 AND 
    (p.isten_cikis_tarihi IS NULL OR DATE_FORMAT(p.isten_cikis_tarihi,'%Y-%m') >= ?)
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
   
$sayi = 1;


if ( $_POST AND $_POST[ "donem" ] ){
    
    $gelentarih = $_POST[ "donem" ];
    $bol        = explode( "-", $gelentarih );
    $ay         = $bol[1];
    $yil        = $bol[0];
    
    $gunSayisi = $fn->ikiHaneliVer($ay) == date("m") ? date("d") : date("t",mktime(0,0,0,$ay,01,$yil));	
    
    
    
    
    $tum_personel 		= $vt->select( $SQL_tum_personel,array( $_SESSION[ "firma_id" ], "$yil-$ay" ) ) [2];
    $genel_ayarlar 		= $vt->select( $SQL_genel_ayarlar, array(  $_SESSION[ "firma_id" ] ) )[ 2 ][ 0 ];
    
    /*Genel ayarlardan grupla ile ilgileri verileri diziye çevirdik*/
    $tatil_mesai_carpan_id 	    = $genel_ayarlar[ "tatil_mesai_carpan_id" ];
    $normal_carpan_id 	        = $genel_ayarlar[ "normal_carpan_id" ];
    $vt->islemBaslat();
    while( $sayi <= $gunSayisi ){
            
            foreach ($tum_personel as $personel) {
    
                $hesapla 	= $fn->puantajHesapla(  $personel[ 'id' ], $_POST[ "donem" ], $sayi, $personel[ 'grup_id' ],array(),$tatil_mesai_carpan_id,$normal_carpan_id);
                
                /*Hesaplanan Degerleri Veri Tabanına Kaydetme İşlemi*/
                $fn->puantajKaydet( $personel[ 'id' ], $_POST[ "donem" ], $sayi, $hesapla);
                
            }
        $sayi++;
    }
    
    $vt->islemBitir();
    echo "1";
}else{
    echo "Yetkiniz Bulunmuyor";
}