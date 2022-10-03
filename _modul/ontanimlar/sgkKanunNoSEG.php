<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$islem		= array_key_exists( 'islem' , $_REQUEST ) ? $_REQUEST[ 'islem' ] : '';
$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "sgkKanunNo", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}


$SQL_ekle = <<< SQL
INSERT INTO
	tb_sgk_kanun_no
SET
	firma_id 	= ?,
	adi 		= ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_sgk_kanun_no
SET
	 adi = ?
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE
	tb_sgk_kanun_no
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
			,$fn->ilkHarfleriBuyut( $_REQUEST[ 'sgk_kanun_adi' ] )
		) );
	break;
	case 'guncelle':
		$vt->update( $SQL_guncelle, array(
			 $fn->ilkHarfleriBuyut( $_REQUEST[ 'sgk_kanun_adi' ] )
			,$id
		) );
	break;
	case 'sil':
		$vt->update( $SQL_sil, array( $id ) );
	break;
}
$vt->islemBitir();
header( 'Location: ../../index.php?modul=sgkKanunNo' );


?>