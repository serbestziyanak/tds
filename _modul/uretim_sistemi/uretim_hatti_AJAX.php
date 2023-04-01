<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

/* Personel başına tamamlanan iş */
$SQL_is_loglari = <<< SQL
SELECT
	 slg.makina_id
	,COUNT(slg.is_parca_id) AS tamamlanan
	,DATE_FORMAT(MAX(slg.tarih), '%H:%i') AS son_kesim_saati
FROM
	sayac_isler AS si
LEFT JOIN
	sayac_is_loglari_gunluk AS slg ON si.id = slg.is_id
WHERE
	si.aktif = 1 AND si.bitis_tarihi IS NULL
GROUP BY
	slg.makina_id
SQL;

$kayitlar	= array();
$kayit		= array();

$sonuclar = $vt->select( $SQL_is_loglari )[ 2 ];

foreach( $sonuclar as $sonuc ) {
	$kayit[ 'makina_id' ]			= $sonuc[ 'makina_id' ];
	$kayit[ 'tamamlanan' ]			= $sonuc[ 'tamamlanan' ]; 
	$kayit[ 'son_kesim_saati' ]		= $sonuc[ 'son_kesim_saati' ];
	$kayitlar[] = $kayit;
}

echo json_encode( array( "sonuclar" => $sonuclar ) );

?>