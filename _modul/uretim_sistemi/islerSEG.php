<?php
 // echo "<pre>";
 // print_r( $_REQUEST );
 // exit;

include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();
$is_id	= array_key_exists( 'is_id' , $_REQUEST ) ? $_REQUEST[ 'is_id' ] : 0;

$is_adi			= $_REQUEST[ 'is_adi' ];
$is_alma_tarihi	= date( 'Y-m-d', strtotime( $_REQUEST[ 'is_alma_tarihi' ] ));
$baslama_tarihi	= date( 'Y-m-d', strtotime( $_REQUEST[ 'baslama_tarihi' ] ));
$siparis_adet	= $_REQUEST[ 'siparis_adet' ];
$aktif			= array_key_exists( "is_aktif", $_REQUEST ) ? 1 : 0;
$aciklama		= $_REQUEST[ 'aciklama' ];



/* İş sonlandırma işlemi */
if( $_REQUEST[ 'bitis_tarihi' ] == '01.01.1970' OR strlen( $_REQUEST[ 'bitis_tarihi' ] ) < 10 OR $_REQUEST[ 'bitis_tarihi' ] == NULL ) {
	$bitis_tarihi = NULL;
} else {
	$bitis_tarihi = date( 'Y-m-d', strtotime( $_REQUEST[ 'bitis_tarihi' ] ));
}
/**/


$SQL_aktif_is_idler = <<<SQL
	SELECT id FROM sayac_isler WHERE aktif = 1;
SQL;


$SQL_ekle = <<< SQL
INSERT INTO
	sayac_isler
SET
	 adi			= ?
	,is_alma_tarihi	= ?
	,baslama_tarihi	= ?
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
	,siparis_adet	= ?
	,aktif			= ?
	,aciklama		= ?
WHERE
	id = ?
SQL;

$SQL_is_sonlandir = <<< SQL
UPDATE
	sayac_isler
SET
	 aktif			= 0
	,bitis_tarihi	= ?
WHERE
	id = ?
SQL;


$SQL_is_gunlugu_ekle = <<< SQL
INSERT INTO
	sayac_is_gunlukleri
SET
	 is_id			= ?
	,gunluk_hedef	= 100
	,tamamlanan		= 0
	,tarih			= now()
SQL;

 
$SQL_sil = <<< SQL
	DELETE FROM sayac_isler WHERE id = ?
SQL;


$SQL_is_gunlukleri_sil = <<< SQL
	DELETE FROM sayac_is_gunlukleri WHERE is_id = ?
SQL;



/* Aktif iş ile igili kontroller*/
$aktif_is_idler = array();
$aktif_isler = $vt->select( $SQL_aktif_is_idler );
foreach( $aktif_isler[ 2 ] AS $aktif_is ) $aktif_is_idler[] = $aktif_is[ 'id' ];


$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			if( count( $aktif_is_idler ) > 0 && $aktif * 1 == 1 ) {
				$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Sistemde aktif bir iş zaten mevcut.<br>Aktif işi sonlandırın veya ekleyeceğiniz işi pasif bir iş olarak ekleyiniz' );
			}

			if( !$___islem_sonuc[ "hata" ] )
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $is_adi
				,$baslama_tarihi
				,$is_alma_tarihi
				,$siparis_adet
				,$aktif
				,$aciklama
			) );

			if( !$___islem_sonuc[ "hata" ] ) {
				$eklenen_is_id = $sorgu_sonuc[ 2 ];
				$sorgu_sonuc = $vt->insert( $SQL_is_gunlugu_ekle, array( $eklenen_is_id ) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'İşe ait günlük hedef eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
			}
		break;

		case 'guncelle':
			if(  count( $aktif_is_idler ) > 0 && !in_array( $is_id, $aktif_is_idler ) && $aktif * 1 == 1 ) {
				$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Sistemde aktif bir iş mevcut.Lütfen önce bu işi sonlandırın veya pasifleştirin.' );
			}

			if( !$___islem_sonuc[ "hata" ] ) {
				$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
					 $is_adi
					,$is_alma_tarihi
					,$baslama_tarihi
					,$siparis_adet
					,$aktif
					,$aciklama
					,$is_id
				) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
			}
		break;

		case 'sil':
			$sorgu_sonuc = $vt->delete( $SQL_sil, array( $is_id ) );
			$sorgu_sonuc = $vt->delete( $SQL_is_gunlukleri_sil, array( $is_id ) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		
		case 'is_sonlandir':
			if( $bitis_tarihi == NULL ) $___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İş sonlandırma tarihi gönderilemediği için işlem iptal eidldi ' . $sorgu_sonuc[ 1 ] );
			if( !$___islem_sonuc[ "hata" ] ) {
				$sorgu_sonuc = $vt->update( $SQL_is_sonlandir, array(
					 $bitis_tarihi
					,$is_id
				));
			}
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'İş sonlandırılırıken bir hata meydana geldi ' . $sorgu_sonuc[ 1 ] );
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