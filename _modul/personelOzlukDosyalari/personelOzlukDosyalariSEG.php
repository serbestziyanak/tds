<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id', $_REQUEST )		? $_REQUEST[ 'personel_id' ]	: 0;
$dosya_turu_id	= array_key_exists( 'dosya_turu_id', $_REQUEST )	? $_REQUEST[ 'dosya_turu_id' ]	: 0;
$dosya_id		= array_key_exists( 'dosya_id', $_REQUEST )			? $_REQUEST[ 'dosya_id' ]		: 0;

$SQL_tum_personel_oku = <<< SQL
SELECT
	tc_no
FROM
	tb_personel
WHERE
	aktif = 1 AND id = ?
SQL;


$SQL_dosya_turu_adi = <<< SQL
SELECT
	*
FROM
	tb_personel_ozluk_dosya_turleri
WHERE
	id = ?
SQL;


$SQL_dosya_kaydet = <<< SQL
INSERT INTO
	tb_personel_ozluk_dosyalari
SET
	 personel_id		= ?
	,dosya_turu_id		= ?
	,dosya				= ?
SQL;

$SQL_dosya_sil = <<< SQL
DELETE FROM
	tb_personel_ozluk_dosyalari
WHERE
	id = ?
SQL;


switch( $islem ) {
	case 'ekle':
		$personel			= $vt->select( $SQL_tum_personel_oku, array( $personel_id ) );
		$dosya_turu_adi		= $vt->select( $SQL_dosya_turu_adi, array( $dosya_turu_id ) );

		$dosya_turu_adi		= $dosya_turu_adi[ 2 ][ 0 ][ 'adi' ];
		$tc_no				= $personel[ 2 ][ 0 ][ 'tc_no' ];


		if( isset( $_FILES[ 'ozluk_dosyasi' ] ) and $_FILES[ 'ozluk_dosyasi' ][ 'size' ] > 0 ) {
			$dosya_adi	= $tc_no . "_" . $dosya_turu_adi . "." . pathinfo( $_FILES[ 'ozluk_dosyasi' ][ 'name' ], PATHINFO_EXTENSION );
			$dizin		= "../../personel_ozluk_dosyalari/";
			$hedef_yol	= $dizin.$dosya_adi;
			if( move_uploaded_file( $_FILES[ 'ozluk_dosyasi' ][ 'tmp_name' ], $hedef_yol ) ) {
				$vt->insert( $SQL_dosya_kaydet, array( $personel_id, $dosya_turu_id, $dosya_adi ) );
			}
		}
	break;
	case 'sil':
		$vt->delete( $SQL_dosya_sil, array( $dosya_id ) );
	break;
}
header( "Location:../../index.php?modul=personelOzlukDosyalari&personel_id=$personel_id" );
?>