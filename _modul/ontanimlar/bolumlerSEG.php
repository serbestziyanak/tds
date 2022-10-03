<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$bolum_id	= array_key_exists( 'bolum_id' , $_REQUEST ) ? $_REQUEST[ 'bolum_id' ] : 0;
$islem		= array_key_exists( 'islem' , $_REQUEST ) ? $_REQUEST[ 'islem' ] : '';

$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "bolumler", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}

$SQL_ekle = <<< SQL
INSERT INTO
	tb_bolumler
SET
	firma_id = ?
	,adi = ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_bolumler
SET
	 adi = ?
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE
	tb_bolumler
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
			,$fn->ilkHarfleriBuyut( $_REQUEST[ 'bolum_adi' ] )
		) );
	break;
	case 'guncelle':
		$vt->update( $SQL_guncelle, array(
			 $fn->ilkHarfleriBuyut( $_REQUEST[ 'bolum_adi' ] )
			,$bolum_id
		) );
	break;
	case 'sil':
		$vt->update( $SQL_sil, array( $bolum_id ) );
	break;
}
$vt->islemBitir();
header( 'Location: ../../index.php?modul=bolumler' );


?>