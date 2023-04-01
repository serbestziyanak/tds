<?php
 // echo "<pre>";
 // print_r( $_REQUEST );
 // exit;


include "../../_cekirdek/fonksiyonlar.php";
$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();

$is_id			= array_key_exists( 'is_id' , $_REQUEST ) ? $_REQUEST[ 'is_id' ] : 0;
$gunluk_id		= array_key_exists( "gunluk_id", $_REQUEST ) ?  $_REQUEST[ 'gunluk_id' ] : 0;
$gunluk_hedef	= $_REQUEST[ 'gunluk_hedef' ];
$gecerlilik		= $_REQUEST[ 'gecerlilik' ];


if( $_REQUEST[ 'tarih' ] == '01.01.1970' OR strlen( $_REQUEST[ 'tarih' ] ) < 10 ) {
	$tarih = NULL;
} else {
	$tarih = date( 'Y-m-d', strtotime( $_REQUEST[ 'tarih' ] ));
}


$SQL_ekle = <<< SQL
INSERT INTO
	sayac_is_gunlukleri
SET
	 is_id			= ?
	,gunluk_hedef	= ?
	,tarih			= ?
	,gecerlilik		= ?
	,tamamlanan		= 0
SQL;


$SQL_guncelle = <<< SQL
UPDATE
	sayac_is_gunlukleri
SET
	 is_id			= ?
	,gunluk_hedef	= ?
	,tarih			= ?
	,gecerlilik		= ?
WHERE
	id = ?
SQL;


$SQL_is_gunlukleri_sil = <<< SQL
	DELETE FROM sayac_is_gunlukleri WHERE id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $is_id
				,$gunluk_hedef
				,$tarih
				,$gecerlilik
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;

		case 'guncelle':
			if( !$___islem_sonuc[ "hata" ] ) {
				$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
					 $is_id
					,$gunluk_hedef
					,$tarih
					,$gecerlilik
					,$gunluk_id
				) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
			}
		break;

		case 'sil':
			$sorgu_sonuc = $vt->delete( $SQL_is_gunlukleri_sil, array( $gunluk_id ) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'id' ] = $gunluk_id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( "Location:../../index.php?modul=is_gunlukleri" )
?>