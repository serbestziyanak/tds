<?php
session_start();
include "../../_cekirdek/fonksiyonlar.php";
$fn = new Fonksiyonlar();
$vt	= new Veritabani();

$tablo_adi = $_REQUEST[ 'tablo_adi' ];
$sablon_id = $_REQUEST[ 'sablon_id' ];

$SQL_sablon_alanlari = <<< SQL
SELECT
	alanlar
FROM
	tb_hizli_veri_girisi_sablonlar
WHERE
	id = ?
SQL;


$SQL_tablo_alanlari = <<< SQL
SELECT
	 COLUMN_NAME as alan_orj
	,COLUMN_COMMENT as alan_tr
FROM
	INFORMATION_SCHEMA.COLUMNS
WHERE
	TABLE_SCHEMA = Database()
AND
	TABLE_NAME = ?
SQL;


$sablon_alanlar	= explode( ",", $vt->select( $SQL_sablon_alanlari, array( $sablon_id ) )[ 2 ][ 0 ][ 'alanlar' ] );
$tablo_alanlar	= $vt->select( $SQL_tablo_alanlari, array( $tablo_adi ) )[ 2 ];

$sonuc = "";

foreach( $tablo_alanlar AS $alan ) {
	$id		= $alan[ 'alan_orj' ];
	$adi	= strlen( $alan[ 'alan_tr' ] ) > 0 ? explode( "-", $alan[ 'alan_tr' ] )[ 0 ] : $alan[ 'alan_orj' ];
	
	if( $id == 'id' or $id == 'aktif' ) continue;
	if( in_array( $alan[ 'alan_orj' ], $sablon_alanlar ) && $sablon_id ) {
		$sonuc .= "<option value = '$id' selected>$adi</option>";
	} else {
		$sonuc .= "<option value = '$id'>$adi</option>";
	}
}
echo $sonuc;

?>