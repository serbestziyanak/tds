<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;


$SQL_ekle = <<< SQL
INSERT INTO
	tb_crm_musteri_portfoy
SET
	 arac_marka_id				= ?
	,arac_tipi_id				= ?
	,arac_model					= ?
	,arac_model_yili			= ?
	,adi						= ?
	,soyadi						= ?
	,cep_tel					= ?
	,email						= ?
	,notlar						= ?
	,sube_id					= ?
	,ekleyen_personel_id		= ?
	,guncelleyen_personel_id	= ?
	,arama_yapildi				= ?
	,arac_alis_satis			= ?
	,mesaj_gonderildi			= ?
	,kayit_tarihi				= now()
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_crm_musteri_portfoy
SET
	 arac_marka_id				= ?
	,arac_tipi_id				= ?
	,arac_model					= ?
	,arac_model_yili			= ?
	,adi						= ?
	,soyadi						= ?
	,cep_tel					= ?
	,email						= ?
	,notlar						= ?
	,sube_id					= ?
	,guncelleyen_personel_id	= ?
	,arama_yapildi				= ?
	,arac_alis_satis			= ?
	,mesaj_gonderildi			= ?
	,guncelleme_tarihi			= now()
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE 
	tb_crm_musteri_portfoy 
SET
	 aktif 			= 0
	,silme_tarihi 	= now()
WHERE 
	id = ?
SQL;

$SQL_crm_musteri_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_crm_musteri_portfoy
WHERE
	cep_tel = ? AND aktif = 1
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			$crm_musteri = $vt->rowCount( $SQL_crm_musteri_bilgileri, array( $_REQUEST[ 'cep_tel' ] ) );
			if( $crm_musteri[ 2 ] > 0 ){
				$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Hata! Bu Cep Telefonu Daha Önce Sisteme Eklenmiş' );
				$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
				header( 'Location: ../../index.php?modul=crmMusteriPortfoy' );
				exit;
			}

			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $_REQUEST[ 'arac_marka_id' ]
				,$_REQUEST[ 'arac_tipi_id' ]
				,$_REQUEST[ 'arac_model' ]
				,$_REQUEST[ 'arac_model_yili' ]
				,$_REQUEST[ 'adi' ]
				,$_REQUEST[ 'soyadi' ]
				,$_REQUEST[ 'cep_tel' ]
				,$_REQUEST[ 'email' ]
				,$_REQUEST[ 'notlar' ]
				,$_REQUEST[ 'sube_id' ]
				,$_SESSION[ 'kullanici_id' ]
				,$_SESSION[ 'kullanici_id' ]
				,$_REQUEST[ 'arama_yapildi' ]
				,$_REQUEST[ 'arac_alis_satis' ]
				,$_REQUEST[ 'mesaj_gonderildi' ]
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
			$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
				 $_REQUEST[ 'arac_marka_id' ]
				,$_REQUEST[ 'arac_tipi_id' ]
				,$_REQUEST[ 'arac_model' ]
				,$_REQUEST[ 'arac_model_yili' ]
				,$_REQUEST[ 'adi' ]
				,$_REQUEST[ 'soyadi' ]
				,$_REQUEST[ 'cep_tel' ]
				,$_REQUEST[ 'email' ]
				,$_REQUEST[ 'notlar' ]
				,$_REQUEST[ 'sube_id' ]
				,$_SESSION[ 'kullanici_id' ]
				,$_REQUEST[ 'arama_yapildi' ]
				,$_REQUEST[ 'arac_alis_satis' ]
				,$_REQUEST[ 'mesaj_gonderildi' ]
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
header( 'Location: ../../index.php?modul=crmMusteriPortfoy' );


?>