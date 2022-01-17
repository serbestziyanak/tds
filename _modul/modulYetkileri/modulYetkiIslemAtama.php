<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'modul_id' , $_REQUEST ) ? $_REQUEST[ 'modul_id' ] : 0;

$SQL_modul_yetki_islem_temizle = <<< SQL
DELETE FROM
	tb_modul_yetki_islemler
WHERE
	modul_id = ?
SQL;

$SQL_ekle = <<< SQL
INSERT INTO
	tb_modul_yetki_islemler
SET
	 yetki_islem_id		= ?
	,modul_id			= ?
SQL;

$yetki_islem_idler = $_REQUEST[ 'chk_modul_yetki_islemler_idler' ];

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();

/* Önce eski kaytları sil */
$vt->delete( $SQL_modul_yetki_islem_temizle, array( $id ) );

/* Gelen depo idlerini kaydet*/
for( $i = 0; $i < count( $yetki_islem_idler ); $i++ ) {
	$sorgu_sonuc = $vt->insert( $SQL_ekle, array( $yetki_islem_idler[ $i ], $id ) );
	if( $sorgu_sonuc[ 0 ] ) break;
}
if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Yetki İşlemleri atanırken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );

$vt->islemBitir();
$___islem_sonuc[ 'modul_id' ]	= $id;
$_SESSION[ 'sonuclar' ]			= $___islem_sonuc;
$_SESSION[ 'aktif_tab_id' ]		= $_REQUEST[ 'aktif_tab_id' ];
header('Location: ' . $_SERVER['HTTP_REFERER']);
?>