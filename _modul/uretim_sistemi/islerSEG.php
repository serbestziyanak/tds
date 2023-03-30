<?php
 // echo "<pre>";
 // print_r( $_REQUEST );
 // exit;

include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();
$id		= array_key_exists( 'is_id' , $_REQUEST ) ? $_REQUEST[ 'is_id' ] : 0;


$is_adi			= $_REQUEST[ 'is_adi' ];
$is_alma_tarihi	= date( 'Y-m-d', strtotime( $_REQUEST[ 'is_alma_tarihi' ] ));
$baslama_tarihi	= date( 'Y-m-d', strtotime( $_REQUEST[ 'baslama_tarihi' ] ));
$siparis_adet	= $_REQUEST[ 'siparis_adet' ];
$is_aktif		= array_key_exists( "is_aktif", $_REQUEST ) ? 1 : 0;
$aciklama		= $_REQUEST[ 'aciklama' ];

if( $_REQUEST[ 'bitis_tarihi' ] == '01.01.1970' OR strlen( $_REQUEST[ 'bitis_tarihi' ] ) < 10 OR  $is_aktif == 1 ) {
	$bitis_tarihi = NULL;
} else {
	$bitis_tarihi = date( 'Y-m-d', strtotime( $_REQUEST[ 'bitis_tarihi' ] ));
}


$SQL_ekle = <<< SQL
INSERT INTO
	sayac_isler
SET
	 adi			= ?
	,is_alma_tarihi	= ?
	,baslama_tarihi	= ?
	,bitis_tarihi	= ?
	,siparis_adet	= ?
	,aktif			= ?
	,aciklama		= ?
SQL;


$SQL_guncelle = <<< SQL
UPDATE
	sayac_isler
SET
	 adi			= ?
	,is_alma_tarihi	= ?
	,baslama_tarihi	= ?
	,bitis_tarihi	= ?
	,siparis_adet	= ?
	,aktif			= ?
	,aciklama		= ?
WHERE
	id = ?
SQL;

 
$SQL_sil = <<< SQL
	DELETE FROM sayac_isler WHERE id = ?
SQL;


$SQL_is_gunlukleri_sil = <<< SQL
	DELETE FROM sayac_is_gunlukleri WHERE is_id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $is_adi
				,$is_alma_tarihi
				,$baslama_tarihi
				,$bitis_tarihi
				,$siparis_adet
				,$is_aktif
				,$aciklama
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;

		case 'guncelle':
			$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
				 $is_adi
				,$is_alma_tarihi
				,$baslama_tarihi
				,$bitis_tarihi
				,$siparis_adet
				,$is_aktif
				,$aciklama
				,$id
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;

		case 'sil':
			$sorgu_sonuc = $vt->delete( $SQL_sil, array( $id ) );
			$sorgu_sonuc = $vt->delete( $SQL_is_gunlukleri_sil, array( $id ) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'id' ] = $id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( "Location:../../index.php?modul=isler" )
?>