<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$arac_id	= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;

/* Dosya Kapatılmışsa Hiç bir işlem yapılamaz */
$SQL_dosya_kapama_kontrol = <<< SQL
SELECT
	dosya_kapatma
FROM
	tb_arac_satislari
WHERE
	arac_id = ?
SQL;

$dosya_kapama_kontrol = $vt->selectSingle( $SQL_dosya_kapama_kontrol, array( $arac_id ) );
if( $dosya_kapama_kontrol[ 2 ]['dosya_kapatma'] == 1 ){
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Hata! Dosya Kapatıldığından Herhangi Bir İşlem Yapılamaz ' );
	$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
	header( 'Location: ../../index.php?modul=aracSatis&islem=satis&id='.$arac_id.'&tab_no='.$_REQUEST['tab_no'] );
	exit;
}
/* Dosya Kapatılmışsa Hiç bir işlem yapılamaz */


function resimkucult($filename1){
    $image = $filename1;
    $max_width = 1000;
    $max_height = 1000;
    $size = GetImageSize($image);
    $width = $size[0];
    $height = $size[1];
    $x_ratio = $max_width / $width;
    $y_ratio = $max_height / $height;
    if (($height <= $max_height) && ($width <= $max_width))
    {
      $tn_height = $height;
      $tn_width = $width;
    }
    elseif (($x_ratio * $height) < $max_height)
    {
      $tn_height = ceil($x_ratio * $height);
      $tn_width = $max_width;
    }
    else
    {
      $tn_width = ceil($y_ratio * $width);
      $tn_height = $max_height;
    }
    switch ($size['mime'])
    {
      case 'image/gif':
      {
        $src = ImageCreateFromGif($image);
        break;
      }
      case 'image/jpeg':
      {
        $src = ImageCreateFromJpeg($image);
        break;
      }
      case 'image/png':
      {
        $src = ImageCreateFromPNG($image);
      }
    }
    $dst = imagecreatetruecolor($tn_width,$tn_height);
    ImageCopyResized($dst, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height);
    //header('Content-type: ' . $size['mime']);
    ImageJpeg($dst, $image);
    ImageDestroy($src);
    ImageDestroy($dst);
}
 
if( isset( $_FILES['alici_kimlik_foto'] ) and $_FILES['alici_kimlik_foto']['size']>0 ){
	if( !file_exists("../../arac_resimler/".$_REQUEST['arac_no']."/") )
		mkdir("../../arac_resimler/".$_REQUEST['arac_no']."/");
	$dosya_adi=$_REQUEST['arac_no']."_alici_kimlik_foto.".pathinfo($_FILES['alici_kimlik_foto']['name'], PATHINFO_EXTENSION);
	$dizin = "../../arac_resimler/".$_REQUEST['arac_no']."/";
	$target_path_temp = $dizin.$dosya_adi;
	$alan_adi = 'alici_kimlik_foto';
	move_uploaded_file($_FILES['alici_kimlik_foto']['tmp_name'], $target_path_temp);
	resimkucult($target_path_temp);	
}
if( isset( $_FILES['alici_vekil_kimlik_foto'] ) and $_FILES['alici_vekil_kimlik_foto']['size']>0 ){
	if( !file_exists("../../arac_resimler/".$_REQUEST['arac_no']."/") )
		mkdir("../../arac_resimler/".$_REQUEST['arac_no']."/");
	$dosya_adi=$_REQUEST['arac_no']."_alici_vekil_kimlik_foto.".pathinfo($_FILES['alici_vekil_kimlik_foto']['name'], PATHINFO_EXTENSION);
	$dizin = "../../arac_resimler/".$_REQUEST['arac_no']."/";
	$target_path_temp = $dizin.$dosya_adi;
	$alan_adi = 'alici_vekil_kimlik_foto';
	move_uploaded_file($_FILES['alici_vekil_kimlik_foto']['tmp_name'], $target_path_temp);
	resimkucult($target_path_temp);	
}
if( isset( $_FILES['alici_vekaletname_foto'] ) and $_FILES['alici_vekaletname_foto']['size']>0 ){
	if( !file_exists("../../arac_resimler/".$_REQUEST['arac_no']."/") )
		mkdir("../../arac_resimler/".$_REQUEST['arac_no']."/");
	$dosya_adi=$_REQUEST['arac_no']."_alici_vekaletname_foto.".pathinfo($_FILES['alici_vekaletname_foto']['name'], PATHINFO_EXTENSION);
	$dizin = "../../arac_resimler/".$_REQUEST['arac_no']."/";
	$target_path_temp = $dizin.$dosya_adi;
	$alan_adi = 'alici_vekaletname_foto';
	move_uploaded_file($_FILES['alici_vekaletname_foto']['tmp_name'], $target_path_temp);
	resimkucult($target_path_temp);	
}

if( $_REQUEST['satis_tarihi'] == '' )
	$satis_tarihi=null;
else
	$satis_tarihi=date('Y-m-d H:i',strtotime($_REQUEST['satis_tarihi']));

if( $_REQUEST['cayma_tarihi'] == '' )
	$cayma_tarihi=null;
else
	$cayma_tarihi=date('Y-m-d H:i',strtotime($_REQUEST['cayma_tarihi']));


$SQL_guncelle1 = <<< SQL
UPDATE
	tb_arac_satislari
SET
	 satis_tarihi	= ?
	,satis_fiyati	= ?
	,komisyon		= ?
WHERE
	arac_id = ?
SQL;

$SQL_guncelle2 = <<< SQL
UPDATE
	tb_arac_satislari
SET
	 alici_tc_no 	= ?
	,alici_adi 		= ?
	,alici_soyadi 	= ?
	,alici_cep_tel 	= ?
	,alici_email 	= ?
	,alici_adres	= ?
	,alici_vekil	= ?
	,personel_id	= ?
WHERE
	arac_id 		= ?
SQL;

$SQL_guncelle3 = <<< SQL
UPDATE
	tb_arac_satislari
SET
	 alici_vekil_tc_no 		= ?
	,alici_vekil_adi 		= ?
	,alici_vekil_soyadi 	= ?
	,alici_vekil_cep_tel 	= ?
	,alici_vekil_email 		= ?
	,alici_vekil_adres		= ?
WHERE
	arac_id 				= ?
SQL;

$SQL_guncelle4 = <<< SQL
UPDATE
	tb_arac_satislari
SET
	 print_satis_sozlesmesi = ?
WHERE
	arac_id = ?
SQL;

$SQL_guncelle5 = <<< SQL
UPDATE
	tb_arac_satislari
SET
{$alan_adi} = ?
WHERE
	arac_id 	= ?
SQL;

$SQL_guncelle6 = <<< SQL
UPDATE
	tb_araclar
SET
	 piyasa_degeri			= ?
	,kasko_degeri			= ?
	,talep_fiyat			= ?
	,hizmet_bedeli			= ?
	,pazarlik_payi			= ?
	,ilan_fiyati			= ?
WHERE
	id 	= ?
SQL;

$SQL_guncelle7 = <<< SQL
UPDATE
	tb_arac_satislari
SET
	 cayma_durumu			= ?
WHERE
	arac_id 	= ?
SQL;

$SQL_guncelle8 = <<< SQL
UPDATE
	tb_arac_satislari
SET
	 cayma_sebebi 			= ?
	,alinan_cayma_bedeli 	= ?
	,cayma_tarihi		 	= ?
WHERE
	arac_id 				= ?
SQL;

$SQL_guncelle9 = <<< SQL
UPDATE
	tb_arac_satislari
SET
	 print_cayma_protokolu	= ?
WHERE
	arac_id 				= ?
SQL;

$SQL_guncelle10 = <<< SQL
UPDATE
	tb_arac_satislari
SET
	 dosya_kapatma	= ?
WHERE
	arac_id 		= ?
SQL;

$SQL_sil = <<< SQL
DELETE FROM 
	tb_soforler 
WHERE 
	id = ?
SQL;

$SQL_yayindan_kaldir = <<< SQL
UPDATE 
	tb_arac_yayinlari 
SET
	 yayindan_alindi = 1
	,yayindan_alinma_tarihi = now()
WHERE 
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			$sorgu_sonuc = $vt->insert( $SQL_ekle, array(
				 $fn->ilkHarfleriBuyut( $_REQUEST[ 'sofor_adi' ] )
				,$fn->tumuBuyukHarf( $_REQUEST[ 'sofor_soyadi' ] )
				,$_REQUEST[ 'sofor_cep_telefonu' ]
				,$_REQUEST[ 'sofor_iban' ]
				,$_REQUEST[ 'sofor_firma_id' ]
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
			if( array_key_exists( 'tab_no', $_REQUEST ) ) {
				switch( $_REQUEST[ 'tab_no' ] ) {
					case '1':
						$sorgu_sonuc = $vt->update( $SQL_guncelle1, array(
							 $satis_tarihi
							,$_REQUEST[ 'satis_fiyati' ]
							,$_REQUEST[ 'komisyon' ]
							,$arac_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '2':
						$sorgu_sonuc = $vt->update( $SQL_guncelle2, array(
							 $_REQUEST[ 'alici_tc_no' ]
							,$fn->ilkHarfleriBuyut( $_REQUEST[ 'alici_adi' ] )
							,$fn->tumuBuyukHarf( $_REQUEST[ 'alici_soyadi' ] )
							,$_REQUEST[ 'alici_cep_tel' ]
							,$_REQUEST[ 'alici_email' ]
							,$_REQUEST[ 'alici_adres' ]
							,$_REQUEST[ 'alici_vekil' ]
							,$_SESSION[ 'kullanici_id' ]
							,$arac_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '3':
						$sorgu_sonuc = $vt->update( $SQL_guncelle3, array(
							 $_REQUEST[ 'alici_vekil_tc_no' ]
							,$fn->ilkHarfleriBuyut( $_REQUEST[ 'alici_vekil_adi' ] )
							,$fn->tumuBuyukHarf( $_REQUEST[ 'alici_vekil_soyadi' ] )
							,$_REQUEST[ 'alici_vekil_cep_tel' ]
							,$_REQUEST[ 'alici_vekil_email' ]
							,$_REQUEST[ 'alici_vekil_adres' ]
							,$arac_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '4':
						$sorgu_sonuc = $vt->update( $SQL_guncelle4, array(
							 $_REQUEST[ 'print_satis_sozlesmesi' ]
							,$arac_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '5':
						$sorgu_sonuc = $vt->update( $SQL_guncelle5, array(
							 $dosya_adi
							,$arac_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '6':
						$sorgu_sonuc = $vt->update( $SQL_guncelle6, array(
							 $_REQUEST[ 'piyasa_degeri' ]
							,$_REQUEST[ 'kasko_degeri' ]
							,$_REQUEST[ 'talep_fiyat' ]
							,$_REQUEST[ 'hizmet_bedeli' ]
							,$_REQUEST[ 'pazarlik_payi' ]
							,$_REQUEST[ 'ilan_fiyati' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '7':
						$sorgu_sonuc = $vt->update( $SQL_guncelle7, array(
							 $_REQUEST[ 'cayma_durumu' ]
							,$arac_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '8':
						$sorgu_sonuc = $vt->update( $SQL_guncelle8, array(
							 $_REQUEST[ 'cayma_sebebi' ]
							,$_REQUEST[ 'alinan_cayma_bedeli' ]
							,$cayma_tarihi
							,$arac_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '9':
						$sorgu_sonuc = $vt->update( $SQL_guncelle9, array(
							 $_REQUEST[ 'print_cayma_protokolu' ]
							,$arac_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '10':
						$sorgu_sonuc = $vt->update( $SQL_guncelle10, array(
							 $_REQUEST[ 'dosya_kapatma' ]
							,$arac_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
				}
			}
		break;
		case 'sil':
				$sorgu_sonuc = $vt->delete( $SQL_sil, array( $id ) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'yayindan_kaldir':
				$sorgu_sonuc = $vt->update( $SQL_yayindan_kaldir, array( $_REQUEST[ 'yayin_id' ] ) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'id' ] = $id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( 'Location: ' . $_SERVER['HTTP_REFERER'] . '&tab_no=' . $_REQUEST[ 'tab_no' ] );

?>