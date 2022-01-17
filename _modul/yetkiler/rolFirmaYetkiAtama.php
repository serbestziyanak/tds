<?php
include "../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'rol_id' , $_REQUEST ) ? $_REQUEST[ 'rol_id' ] : 0;

$SQL_rol_firma_temizle = <<< SQL
DELETE FROM
	tb_rol_yetkili_firmalar
WHERE
	rol_id = ?
SQL;

$SQL_ekle = <<< SQL
INSERT INTO
	tb_rol_yetkili_firmalar
SET
	 firma_id	= ?
	,rol_id		= ?
SQL;

$firma_idler = $_REQUEST[ 'chk_firma_idler' ];



$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();

/* Önce eski kaytları sil */
$vt->insert( $SQL_rol_firma_temizle, array( $id ) );

/* Gelen depo idlerini kaydet*/
for( $i = 0; $i < count( $firma_idler ); $i++ ) {
	$sorgu_sonuc = $vt->insert( $SQL_ekle, array( $firma_idler[ $i ], $id ) );
	if( $sorgu_sonuc[ 0 ] ) break;
}
if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Firmalar eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );

$vt->islemBitir();
$___islem_sonuc[ 'rol_id' ]		= $id;
$_SESSION[ 'sonuclar' ]			= $___islem_sonuc;
$_SESSION[ 'aktif_tab_id' ]		= $_REQUEST[ 'aktif_tab_id' ];
header( 'Location: ../index.php?modul=yetkiler' );


?>