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


$sonuclar = $vt->select( $SQL_is_loglari )[ 2 ];

$tamamlananlar = [];
$siparis_adet = $sonuclar[ 0 ][ "siparis_adet" ];

foreach( $sonuclar as $sonuc ) {
	if( $sonuc[ "tamamlanan" ] * 1 > 0 )
		$tamamlananlar[] = $sonuc[ "tamamlanan" ];
};

$tamamlanan = min( $tamamlananlar );

$tamamlanan_yuzde = floor( ( $tamamlanan * 100 ) / $siparis_adet );


if( $tamamlanan > 1000 ) {
	$tamamlanan = number_format( $tamamlanan, 0, '', ',' );
}


echo json_encode( array( "sonuclar" => $sonuclar, "toplam" => $tamamlanan, "tamamlanan_yuzde" => $tamamlanan_yuzde ) );

?>