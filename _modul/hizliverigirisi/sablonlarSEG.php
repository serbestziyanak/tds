<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();


$sablon_id	= array_key_exists( 'sablon_id' , $_REQUEST ) ? $_REQUEST[ 'sablon_id' ] : 0;
$islem		= array_key_exists( 'islem' , $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';
$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "sablonlar", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}

$sablon_adi				= $_REQUEST[ 'sablon_adi' ];
$sablon_tablo_adi		= $_REQUEST[ 'sablon_tablo_adi' ];
$sablon_tablo_adi_gizli	= $_REQUEST[ 'sablon_tablo_adi_gizli' ];
$sablon_alanlar			= $_REQUEST[ 'sablon_alanlar' ];

if( !in_array( 'id', $sablon_alanlar ) ) $sablon_alanlar[] = 'id';

$sablon_alanlar			= implode( ",", $sablon_alanlar );


$SQL_ekle = <<< SQL
INSERT INTO
	tb_hizli_veri_girisi_sablonlar
SET
	 adi			= ?
	,tablo_adi		= ?
	,alanlar		= ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_hizli_veri_girisi_sablonlar
SET
	 adi			= ?
	,tablo_adi		= ?
	,alanlar		= ?
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
DELETE FROM
	tb_hizli_veri_girisi_sablonlar
WHERE
	id = ?
SQL;
$vt->islemBaslat();

switch( $islem ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, array( $fn->ilkHarfleriBuyut( $sablon_adi ), $sablon_tablo_adi, $sablon_alanlar ) );
	break;
	case 'guncelle':
		$vt->update( $SQL_guncelle, array(  $fn->ilkHarfleriBuyut( $sablon_adi ), $sablon_tablo_adi_gizli, $sablon_alanlar, $sablon_id ) );
	break;
	case 'sil':
		$vt->delete( $SQL_sil, array( $sablon_id ) );
	break;
}
$vt->islemBitir();
header( 'Location: ../../index.php?modul=sablonlar&sablon_id=' . $sablon_id  );


?>