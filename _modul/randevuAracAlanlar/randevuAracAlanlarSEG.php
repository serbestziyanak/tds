<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;

if( $_REQUEST['randevu_tarihi'] == '' )
	$randevu_tarihi=null;
else
	$randevu_tarihi=date('Y-m-d H:i',strtotime($_REQUEST['randevu_tarihi']));


$SQL_ekle = <<< SQL
INSERT INTO
	tb_randevular
SET
	 arac_id			= ?
	,randevu_tipi		= ?
	,adi				= ?
	,soyadi				= ?
	,cep_tel			= ?
	,email				= ?
	,notlar				= ?
	,sube_id			= ?
	,randevu_tarihi		= ?
	,personel_id		= ?
	,kayit_tarihi		= now()
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_randevular
SET
	 arac_id		= ?
	,randevu_tipi		= ?
	,adi				= ?
	,soyadi				= ?
	,cep_tel			= ?
	,email				= ?
	,notlar				= ?
	,sube_id			= ?
	,randevu_tarihi		= ?
	,guncelleme_tarihi	= now()
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE 
	tb_randevular 
SET
	 aktif 			= 0
	,silme_tarihi 	= now()
WHERE 
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $_REQUEST[ 'arac_id' ]
				,$_REQUEST[ 'randevu_tipi' ]
				,$_REQUEST[ 'adi' ]
				,$_REQUEST[ 'soyadi' ]
				,$_REQUEST[ 'cep_tel' ]
				,$_REQUEST[ 'email' ]
				,$_REQUEST[ 'notlar' ]
				,$_REQUEST[ 'sube_id' ]
				,$randevu_tarihi
				,$_SESSION[ 'kullanici_id' ]
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
			$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
				 $_REQUEST[ 'arac_id' ]
				,$_REQUEST[ 'randevu_tipi' ]
				,$_REQUEST[ 'adi' ]
				,$_REQUEST[ 'soyadi' ]
				,$_REQUEST[ 'cep_tel' ]
				,$_REQUEST[ 'email' ]
				,$_REQUEST[ 'notlar' ]
				,$_REQUEST[ 'sube_id' ]
				,$randevu_tarihi
				,$id
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'sil':
			$sorgu_sonuc = $vt->update( $SQL_sil, array( $id ) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'sofor_id' ] = $id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( 'Location: ../../index.php?modul=randevuAracAlanlar' );


?>