<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;

$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "firmaSorulari", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}



//var_dump($_REQUEST);
//exit;
$secenekler = $_REQUEST['secenekler'];

$SQL_ekle = <<< SQL
INSERT INTO
	tb_sorular
SET
	 kategori_id 		= ?
	,soru 				= ?
	,soru_cevap_turu_id = ?
SQL;

$SQL_secenek_altina_soru_ekle = <<< SQL
INSERT INTO
	tb_sorular
SET
	 kategori_id 		= ?
	,soru 				= ?
	,soru_cevap_turu_id = ?
	,soru_secenek_id 	= ?
SQL;

$SQL_soru_secenek_ekle = <<< SQL
INSERT INTO
	tb_soru_secenekleri
SET
	 soru_id 		= ?
	,secenek		= ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_sorular
SET
	 kategori_id 		= ?
	,soru 				= ?
	,soru_cevap_turu_id = ?
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
DELETE FROM 
	tb_sorular
WHERE 
	id = ? 
SQL;


$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $_REQUEST[ 'kategori_id' ]
				,$_REQUEST[ 'soru' ]
				,$_REQUEST[ 'soru_cevap_turu_id' ]
			) );
			$son_eklenen_soru_id = $sorgu_sonuc[ 2 ];
			foreach( $secenekler as $secenek ){
				$sorgu_sonuc = $vt->insert( $SQL_soru_secenek_ekle, array(
					 $son_eklenen_soru_id
					,$secenek
				) );
			}
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'secenek_altina_soru_ekle':
			$sorgu_sonuc = $vt->insert( $SQL_secenek_altina_soru_ekle, array(
				 $_REQUEST[ 'kategori_id' ]
				,$_REQUEST[ 'soru' ]
				,$_REQUEST[ 'soru_cevap_turu_id' ]
				,$_REQUEST[ 'secenek_id' ]
			) );
			$son_eklenen_soru_id = $sorgu_sonuc[ 2 ];
			foreach( $secenekler as $secenek ){
				$sorgu_sonuc = $vt->insert( $SQL_soru_secenek_ekle, array(
					 $son_eklenen_soru_id
					,$secenek
				) );
			}
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
				$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
					 $fn->ilkHarfleriBuyut( $_REQUEST[ 'adi' ] )
					,$id
				) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'sil':
				$sorgu_sonuc = $vt->delete( $SQL_sil, array( $id ) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'sube_id' ] = $id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( "Location: ../../index.php?modul=soruEkle&kategori_id=$_REQUEST[kategori_id]" );


?>