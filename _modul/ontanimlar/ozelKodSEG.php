<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$ozel_kod_id	= array_key_exists( 'ozel_kod_id' , $_REQUEST ) ? $_REQUEST[ 'ozel_kod_id' ] : 0;
$islem		= array_key_exists( 'islem' , $_REQUEST ) ? $_REQUEST[ 'islem' ] : '';

$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "ozelKod", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}

$SQL_ekle = <<< SQL
INSERT INTO
	tb_ozel_kod
SET
	firma_id 	= ?,
	 adi 		= ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_ozel_kod
SET
	 adi = ?
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE
	tb_ozel_kod
SET
	 aktif = 0
WHERE
	id = ?
SQL;
$vt->islemBaslat();

switch( $_REQUEST[ 'islem' ] ) {
	case 'ekle':
		$vt->insert( $SQL_ekle, array(
			$_SESSION[ "firma_id" ]
			,$fn->ilkHarfleriBuyut( $_REQUEST[ 'ozel_kod_adi' ] )
		) );
	break;
	case 'guncelle':
		$vt->update( $SQL_guncelle, array(
			 $fn->ilkHarfleriBuyut( $_REQUEST[ 'ozel_kod_adi' ] )
			,$ozel_kod_id
		) );
	break;
	case 'sil':
		$vt->update( $SQL_sil, array( $ozel_kod_id ) );
	break;
}
$vt->islemBitir();
header( 'Location: ../../index.php?modul=ozelKod' );


?>