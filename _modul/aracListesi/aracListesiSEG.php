<?php
include "../../_cekirdek/fonksiyonlar.php";

$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;

if( $_REQUEST['kayit_tarihi'] == '' )
	$kayit_tarihi=null;
else
	$kayit_tarihi=date('Y-m-d H:i',strtotime($_REQUEST['kayit_tarihi']));



$SQL_ekle = <<< SQL
INSERT INTO
	tb_araclar
SET
	 sube_id		= ?
	,plaka			= ?
	,personel_id 	= ?
	,arac_no		= ?
	,kayit_tarihi		= ?
SQL;

$SQL_arac_expertiz_ekle = <<< SQL
INSERT INTO
	tb_arac_expertiz
SET
	 arac_id = ?
SQL;

$SQL_arac_satis_ekle = <<< SQL
INSERT INTO
	tb_arac_satislari
SET
	 arac_id = ?
SQL;

$SQL_arac_no = <<< SQL
SELECT
	max(arac_no)+1 as maks
FROM 
	tb_araclar
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_araclar
SET
	 sube_id		= ?
	,plaka			= ?
	,personel_id 	= ?
	,arac_no		= ?
	,kayit_tarihi	= ?
WHERE
	id = ?
SQL;

$SQL_onayla = <<< SQL
UPDATE
	tb_araclar
SET
	 onaylandi				= not onaylandi
	,onaylayan_personel_id	= ?
	,onay_tarihi			= now()
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE 
	tb_araclar 
SET
	aktif = 0
WHERE 
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			$sorgu_sonuc2 = $vt->select( $SQL_arac_no, array(  ) );
			$yeni_arac_no = $sorgu_sonuc2[ 2 ];
			if( isset( $yeni_arac_no[0][ 'maks' ] ) ) {
				$yeni_arac_no = $yeni_arac_no[0][ 'maks' ];
			} else {
				$yeni_arac_no=12345;
			}
			mkdir("../../arac_resimler/".$yeni_arac_no."/");
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $_REQUEST[ 'sube_id' ]
				,$fn->tumuBuyukHarf( $_REQUEST[ 'plaka' ] )
				,$_SESSION[ 'kullanici_id' ]
				,$yeni_arac_no
				,$kayit_tarihi
			) );
			$enson_id = $sorgu_sonuc[ 2 ];
			$sorgu_sonuc3 = $vt->insert( $SQL_arac_expertiz_ekle, array( $sorgu_sonuc[ 2 ] ) );
			$sorgu_sonuc4 = $vt->insert( $SQL_arac_satis_ekle, array( $sorgu_sonuc[ 2 ] ) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
				$sorgu_sonuc = $vt->update( $SQL_guncelle, array(
					 $_REQUEST[ 'sube_id' ]
					,$fn->tumuBuyukHarf( $_REQUEST[ 'plaka' ] )
					,$_SESSION[ 'kullanici_id' ]
					,$yeni_arac_no
					,$kayit_tarihi
					,$id
				) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'onayla':
				$sorgu_sonuc = $vt->update( $SQL_onayla, array(
					 $_SESSION[ 'kullanici_id' ]
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
header( 'Location: ../../index.php?modul=araclar&islem=detaylar&id='.$enson_id.'&tab_no=2' );




?>