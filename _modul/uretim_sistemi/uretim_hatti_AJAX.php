<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

/* Personel başına tamamlanan iş */
$SQL_is_loglari = <<< SQL
SELECT
	 slg.makina_id
	,FLOOR(COUNT(slg.is_parca_id) / sm.is_basina_sayac_sayisi) AS tamamlanan
	,DATE_FORMAT(MAX(slg.tarih), '%H:%i') AS son_kesim_saati
	,si.siparis_adet
FROM
	sayac_isler AS si
LEFT JOIN
	sayac_is_loglari_gunluk AS slg ON si.id = slg.is_id
LEFT JOIN
	sayac_makina AS sm ON slg.makina_id = sm.id
WHERE
	si.aktif = 1 AND si.bitis_tarihi IS NULL
GROUP BY
	slg.makina_id
SQL;

$kayitlar	= array();
$kayit		= array();

$sonuclar = $vt->select( $SQL_is_loglari )[ 2 ];
$toplam_tamamlanan = 0;
$siparis_adet = $sonuclar[ 0 ][ "siparis_adet" ];

foreach( $sonuclar as $sonuc ) $toplam_tamamlanan += $sonuc[ "tamamlanan" ];

$tamamlanan_yuzde = floor( ( $toplam_tamamlanan * 100 ) / $siparis_adet );


if ( $toplam_tamamlanan > 1000) {
	$toplam_tamamlanan = number_format($toplam_tamamlanan, 0, '', ',');
}



echo json_encode( array( "sonuclar" => $sonuclar, "toplam" => $toplam_tamamlanan, "tamamlanan_yuzde" => $tamamlanan_yuzde ) );

?>