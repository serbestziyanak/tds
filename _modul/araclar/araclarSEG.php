<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;


/* Dosya Kapatılmışsa Hiç bir işlem yapılamaz */
$SQL_dosya_kapama_kontrol = <<< SQL
SELECT
	dosya_kapatma
FROM
	tb_arac_satislari
WHERE
	arac_id = ?
SQL;

$dosya_kapama_kontrol = $vt->selectSingle( $SQL_dosya_kapama_kontrol, array( $id ) );
if( $dosya_kapama_kontrol[ 2 ]['dosya_kapatma'] == 1 ){
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'Hata! Dosya Kapatıldığından Herhangi Bir İşlem Yapılamaz ' );
	$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
	header( 'Location: ../../index.php?modul=araclar&islem=detaylar&id='.$id.'&tab_no='.$_REQUEST['tab_no']);
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
 
if( isset( $_FILES['sahip_kimlik_foto'] ) and $_FILES['sahip_kimlik_foto']['size']>0 ){
	if( !file_exists("../../arac_resimler/".$_REQUEST['arac_no']."/") )
		mkdir("../../arac_resimler/".$_REQUEST['arac_no']."/");
	$dosya_adi=$_REQUEST['arac_no']."_sahip_kimlik_foto.".pathinfo($_FILES['sahip_kimlik_foto']['name'], PATHINFO_EXTENSION);
	$dizin = "../../arac_resimler/".$_REQUEST['arac_no']."/";
	$target_path_temp = $dizin.$dosya_adi;
	$alan_adi = 'sahip_kimlik_foto';
	move_uploaded_file($_FILES['sahip_kimlik_foto']['tmp_name'], $target_path_temp);
	resimkucult($target_path_temp);	
}
if( isset( $_FILES['vekil_kimlik_foto'] ) and $_FILES['vekil_kimlik_foto']['size']>0 ){
	if( !file_exists("../../arac_resimler/".$_REQUEST['arac_no']."/") )
		mkdir("../../arac_resimler/".$_REQUEST['arac_no']."/");
	$dosya_adi=$_REQUEST['arac_no']."_vekil_kimlik_foto.".pathinfo($_FILES['vekil_kimlik_foto']['name'], PATHINFO_EXTENSION);
	$dizin = "../../arac_resimler/".$_REQUEST['arac_no']."/";
	$target_path_temp = $dizin.$dosya_adi;
	$alan_adi = 'vekil_kimlik_foto';
	move_uploaded_file($_FILES['vekil_kimlik_foto']['tmp_name'], $target_path_temp);
	resimkucult($target_path_temp);	
}
if( isset( $_FILES['ruhsat_foto'] ) and $_FILES['ruhsat_foto']['size']>0 ){
	if( !file_exists("../../arac_resimler/".$_REQUEST['arac_no']."/") )
		mkdir("../../arac_resimler/".$_REQUEST['arac_no']."/");
	$dosya_adi=$_REQUEST['arac_no']."_ruhsat_foto.".pathinfo($_FILES['ruhsat_foto']['name'], PATHINFO_EXTENSION);
	$dizin = "../../arac_resimler/".$_REQUEST['arac_no']."/";
	$target_path_temp = $dizin.$dosya_adi;
	$alan_adi = 'ruhsat_foto';
	move_uploaded_file($_FILES['ruhsat_foto']['tmp_name'], $target_path_temp);
	resimkucult($target_path_temp);	
}

if( $_REQUEST['kayit_tarihi'] == '' )
	$kayit_tarihi=null;
else
	$kayit_tarihi=date('Y-m-d H:i',strtotime($_REQUEST['kayit_tarihi']));

if( $_REQUEST['ruhsat_muayene_gecerlilik_tarihi'] == '' )
	$ruhsat_muayene_gecerlilik_tarihi=null;
else
	$ruhsat_muayene_gecerlilik_tarihi=date('Y-m-d H:i',strtotime($_REQUEST['ruhsat_muayene_gecerlilik_tarihi']));


$SQL_guncelle1 = <<< SQL
UPDATE
	tb_araclar
SET
	 sube_id		= ?
WHERE
	id = ?
SQL;

$SQL_guncelle2 = <<< SQL
UPDATE
	tb_araclar
SET
	 sahip_tc_no 	= ?
	,sahip_adi 		= ?
	,sahip_soyadi 	= ?
	,sahip_cep_tel 	= ?
	,sahip_email 	= ?
	,sahip_adres	= ?
	,vekil			= ?
WHERE
	id 				= ?
SQL;

$SQL_guncelle3 = <<< SQL
UPDATE
	tb_araclar
SET
	 vekil_tc_no 	= ?
	,vekil_adi 		= ?
	,vekil_soyadi 	= ?
	,vekil_cep_tel 	= ?
	,vekil_email 	= ?
	,vekil_adres	= ?
WHERE
	id 				= ?
SQL;

$SQL_guncelle4 = <<< SQL
UPDATE
	tb_araclar
SET
	 plaka					= ?
	,arac_durumu			= ?
	,plaka_durumu			= ?
	,arac_tipi_id			= ?
	,arac_kasa_tipi_id		= ?
	,model_tipi				= ?
	,donanim_paketi			= ?
	,arac_vites_tipi_id		= ?
	,arac_vites_sayisi_id	= ?
	,km						= ?
	,garanti_durumu			= ?
	,arac_cekis_tipi_id		= ?
	,yedek_anahtar			= ?
	,duzenli_servis_bakimi	= ?
	,arac_marka_id			= ?
	,ticari_adi				= ?
	,silindir_hacmi			= ?
	,model_yili				= ?
	,arac_yakit_tipi_id		= ?
	,renk_id				= ?
	,tipi					= ?
	,motor_gucu				= ?
	,arac_ekstra			= ?
WHERE
	id 	= ?
SQL;

$SQL_guncelle5 = <<< SQL
UPDATE
	tb_araclar
SET
	 ruhsat_verildigi_il_ilce_y1			= ?
	,ruhsat_ilk_tescil_tarihi_b9			= ?
	,ruhsat_tescil_tarihi_1					= ?
	,ruhsat_arac_sinifi_j					= ?
	,ruhsat_cinsi_d5						= ?
	,ruhsat_motor_no_p5						= ?
	,ruhsat_sase_no_e						= ?
	,ruhsat_koltuk_sayisi_s1				= ?
	,ruhsat_kullanim_amaci_y3				= ?
	,ruhsat_belge_seri						= ?
	,ruhsat_no								= ?
	,ruhsat_muayene_gecerlilik_tarihi		= ?
WHERE
	id 	= ?
SQL;

$SQL_guncelle6 = <<< SQL
UPDATE
	tb_araclar
SET
	 piyasa_degeri					= ?
	,kasko_degeri					= ?
	,talep_fiyat					= ?
	,hizmet_bedeli					= ?
	,ek_hizmet_bedeli				= ?
	,ekstra_istenen_hizmet_bedeli	= ?
	,pazarlik_payi					= ?
	,ilan_fiyati					= ?
	,cayma_bedeli					= ?
WHERE
	id 	= ?
SQL;

$SQL_guncelle6_yeni_fiyat_ekle = <<< SQL
INSERT INTO
	tb_arac_fiyatlar
SET
	 piyasa_degeri					= ?
	,kasko_degeri					= ?
	,talep_fiyat					= ?
	,hizmet_bedeli					= ?
	,ek_hizmet_bedeli				= ?
	,ekstra_istenen_hizmet_bedeli	= ?
	,pazarlik_payi					= ?
	,ilan_fiyati					= ?
	,cayma_bedeli					= ?
	,arac_id						= ?
SQL;

$SQL_guncelle6_ekstra_hizmet_bedeli = <<< SQL
UPDATE
	tb_araclar
SET
	 ekstra_istenen_hizmet_bedeli 		= ?
	,ekstra_istenen_hizmet_bedeli_onay	= 2
WHERE
	id 	= ?
SQL;

$SQL_guncelle6_ekstra_hizmet_bedeli_onayla = <<< SQL
UPDATE
	tb_araclar
SET
	 hizmet_bedeli = hizmet_bedeli + ekstra_istenen_hizmet_bedeli
	,ilan_fiyati = ilan_fiyati + ekstra_istenen_hizmet_bedeli
	,ekstra_istenen_hizmet_bedeli_onay	= 1
WHERE
	id 	= ?
SQL;

$SQL_guncelle6_ekstra_hizmet_bedeli_onay_kaldir = <<< SQL
UPDATE
	tb_araclar
SET
	 hizmet_bedeli = hizmet_bedeli - ekstra_istenen_hizmet_bedeli
	,ilan_fiyati = ilan_fiyati - ekstra_istenen_hizmet_bedeli
	,ekstra_istenen_hizmet_bedeli = null
	,ekstra_istenen_hizmet_bedeli_onay	= 0
WHERE
	id 	= ?
SQL;

$SQL_hizmet_bedeli = <<< SQL
SELECT 
	*
FROM 
	tb_arac_hizmet_bedel_baremi 
WHERE 
	?>=min_deger AND ?<=max_deger
SQL;

$SQL_guncelle7 = <<< SQL
UPDATE
	tb_araclar
SET
	 rehin_durumu			= ?
	,trafik_cezasi			= ?
	,mtv_borcu			= ?
WHERE
	id 	= ?
SQL;

$SQL_guncelle8 = <<< SQL
UPDATE
	tb_araclar
SET
{$alan_adi} = ?
WHERE
	id 	= ?
SQL;

$SQL_sil = <<< SQL
DELETE FROM 
	tb_soforler 
WHERE 
	id = ?
SQL;

$SQL_guncelle9 = <<< SQL
UPDATE
	tb_araclar
SET
	 print_hizmet_sozlesmesi 	= ?
	,print_arac_info 			= ?
	,print_qr_kod 				= ?
WHERE
	id 	= ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'yeni_fiyat_ekle':
			$hizmet_bedeli = $vt->selectSingle( $SQL_hizmet_bedeli, array( $_REQUEST[ 'talep_fiyat' ],$_REQUEST[ 'talep_fiyat' ] ) );
			$ilan_fiyati = $_REQUEST[ 'talep_fiyat' ] + $hizmet_bedeli[ 2 ][ 'hizmet_bedeli' ]*2 + $hizmet_bedeli[ 2 ][ 'ek_hizmet_bedeli' ] + $_REQUEST[ 'ekstra_istenen_hizmet_bedeli' ];
			//print_r($hizmet_bedeli[2]);
			//exit;
			$sorgu_sonuc = $vt->insert( $SQL_guncelle6_yeni_fiyat_ekle, array(
				 $_REQUEST[ 'piyasa_degeri' ]
				,$_REQUEST[ 'kasko_degeri' ]
				,$_REQUEST[ 'talep_fiyat' ]
				,$hizmet_bedeli[ 2 ][ 'hizmet_bedeli' ] + $hizmet_bedeli[ 2 ][ 'ek_hizmet_bedeli' ]
				,$hizmet_bedeli[ 2 ][ 'ek_hizmet_bedeli' ]
				,$_REQUEST[ 'ekstra_istenen_hizmet_bedeli' ]
				,$hizmet_bedeli[ 2 ][ 'hizmet_bedeli' ]
				,$ilan_fiyati
				,$hizmet_bedeli[ 2 ][ 'cayma_bedeli' ]
				,$id
			) );
			$sorgu_sonuc = $vt->update( $SQL_guncelle6, array(
				 $_REQUEST[ 'piyasa_degeri' ]
				,$_REQUEST[ 'kasko_degeri' ]
				,$_REQUEST[ 'talep_fiyat' ]
				,$hizmet_bedeli[ 2 ][ 'hizmet_bedeli' ] + $hizmet_bedeli[ 2 ][ 'ek_hizmet_bedeli' ]
				,$hizmet_bedeli[ 2 ][ 'ek_hizmet_bedeli' ]
				,$_REQUEST[ 'ekstra_istenen_hizmet_bedeli' ]
				,$hizmet_bedeli[ 2 ][ 'hizmet_bedeli' ]
				,$ilan_fiyati
				,$hizmet_bedeli[ 2 ][ 'cayma_bedeli' ]
				,$id
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'ekstra_istenen_hizmet_bedeli':
			$sorgu_sonuc = $vt->update( $SQL_guncelle6_ekstra_hizmet_bedeli, array(
				 $_REQUEST[ 'ekstra_istenen_hizmet_bedeli' ]
				,$id
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'ekstra_hizmet_bedeli_onayla':
			$sorgu_sonuc = $vt->update( $SQL_guncelle6_ekstra_hizmet_bedeli_onayla, array(
				$id
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'ekstra_hizmet_bedeli_onay_kaldir':
			$sorgu_sonuc = $vt->update( $SQL_guncelle6_ekstra_hizmet_bedeli_onay_kaldir, array(
				$id
			) );
			if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
			if( array_key_exists( 'tab_no', $_REQUEST ) ) {
				switch( $_REQUEST[ 'tab_no' ] ) {
					case '1':
						$sorgu_sonuc = $vt->update( $SQL_guncelle1, array(
							 $_REQUEST[ 'sube_id' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '2':
						$sorgu_sonuc = $vt->update( $SQL_guncelle2, array(
							 $_REQUEST[ 'sahip_tc_no' ]
							,$fn->ilkHarfleriBuyut( $_REQUEST[ 'sahip_adi' ] )
							,$fn->tumuBuyukHarf( $_REQUEST[ 'sahip_soyadi' ] )
							,$_REQUEST[ 'sahip_cep_tel' ]
							,$_REQUEST[ 'sahip_email' ]
							,$_REQUEST[ 'sahip_adres' ]
							,$_REQUEST[ 'vekil' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '3':
						$sorgu_sonuc = $vt->update( $SQL_guncelle3, array(
							 $_REQUEST[ 'vekil_tc_no' ]
							,$fn->ilkHarfleriBuyut( $_REQUEST[ 'vekil_adi' ] )
							,$fn->tumuBuyukHarf( $_REQUEST[ 'vekil_soyadi' ] )
							,$_REQUEST[ 'vekil_cep_tel' ]
							,$_REQUEST[ 'vekil_email' ]
							,$_REQUEST[ 'vekil_adres' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '4':
						$sorgu_sonuc = $vt->update( $SQL_guncelle4, array(
							 $_REQUEST[ 'plaka' ]
							,$_REQUEST[ 'arac_durumu' ]
							,$_REQUEST[ 'plaka_durumu' ]
							,$_REQUEST[ 'arac_tipi_id' ]
							,$_REQUEST[ 'arac_kasa_tipi_id' ]
							,$_REQUEST[ 'model_tipi' ]
							,$_REQUEST[ 'donanim_paketi' ]
							,$_REQUEST[ 'arac_vites_tipi_id' ]
							,$_REQUEST[ 'arac_vites_sayisi_id' ]
							,$_REQUEST[ 'km' ]
							,$_REQUEST[ 'garanti_durumu' ]
							,$_REQUEST[ 'arac_cekis_tipi_id' ]
							,$_REQUEST[ 'yedek_anahtar' ]
							,$_REQUEST[ 'duzenli_servis_bakimi' ]
							,$_REQUEST[ 'arac_marka_id' ]
							,$_REQUEST[ 'ticari_adi' ]
							,$_REQUEST[ 'silindir_hacmi' ]
							,$_REQUEST[ 'model_yili' ]
							,$_REQUEST[ 'arac_yakit_tipi_id' ]
							,$_REQUEST[ 'renk_id' ]
							,$_REQUEST[ 'tipi' ]
							,$_REQUEST[ 'motor_gucu' ]
							,$_REQUEST[ 'arac_ekstra' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '5':
						$sorgu_sonuc = $vt->update( $SQL_guncelle5, array(
							 $_REQUEST[ 'ruhsat_verildigi_il_ilce_y1' ]
							,$_REQUEST[ 'ruhsat_ilk_tescil_tarihi_b9' ]
							,$_REQUEST[ 'ruhsat_tescil_tarihi_1' ]
							,$_REQUEST[ 'ruhsat_arac_sinifi_j' ]
							,$_REQUEST[ 'ruhsat_cinsi_d5' ]
							,$_REQUEST[ 'ruhsat_motor_no_p5' ]
							,$_REQUEST[ 'ruhsat_sase_no_e' ]
							,$_REQUEST[ 'ruhsat_koltuk_sayisi_s1' ]
							,$_REQUEST[ 'ruhsat_kullanim_amaci_y3' ]
							,$_REQUEST[ 'ruhsat_belge_seri' ]
							,$_REQUEST[ 'ruhsat_no' ]
							,$ruhsat_muayene_gecerlilik_tarihi
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '6':
						$hizmet_bedeli = $vt->selectSingle( $SQL_hizmet_bedeli, array( $_REQUEST[ 'talep_fiyat' ],$_REQUEST[ 'talep_fiyat' ] ) );
						$ilan_fiyati = $_REQUEST[ 'talep_fiyat' ] + $hizmet_bedeli[ 2 ][ 'hizmet_bedeli' ]*2 + $hizmet_bedeli[ 2 ][ 'ek_hizmet_bedeli' ] + $_REQUEST[ 'ekstra_istenen_hizmet_bedeli' ];
						//print_r($hizmet_bedeli[2]);
						//exit;
						$sorgu_sonuc = $vt->update( $SQL_guncelle6, array(
							 $_REQUEST[ 'piyasa_degeri' ]
							,$_REQUEST[ 'kasko_degeri' ]
							,$_REQUEST[ 'talep_fiyat' ]
							,$hizmet_bedeli[ 2 ][ 'hizmet_bedeli' ] + $hizmet_bedeli[ 2 ][ 'ek_hizmet_bedeli' ]
							,$hizmet_bedeli[ 2 ][ 'ek_hizmet_bedeli' ]
							,$_REQUEST[ 'ekstra_istenen_hizmet_bedeli' ]
							,$hizmet_bedeli[ 2 ][ 'hizmet_bedeli' ]
							,$ilan_fiyati
							,$hizmet_bedeli[ 2 ][ 'cayma_bedeli' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '7':
						$sorgu_sonuc = $vt->update( $SQL_guncelle7, array(
							 $_REQUEST[ 'rehin_durumu' ]
							,$_REQUEST[ 'trafik_cezasi' ]
							,$_REQUEST[ 'mtv_borcu' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '8':
						$sorgu_sonuc = $vt->update( $SQL_guncelle8, array(
							 $dosya_adi
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '9':
						$sorgu_sonuc = $vt->update( $SQL_guncelle9, array(
							 $_REQUEST[ 'print_hizmet_sozlesmesi' ]
							,$_REQUEST[ 'print_arac_info' ]
							,$_REQUEST[ 'print_qr_kod' ]
							,$id
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
	}
} else {
	$___islem_sonuc = array( 'hata' => true, 'mesaj' => 'İşlem türü gönderilmediğinden dolayı işleminiz iptal edildi' );
}
$vt->islemBitir();
$___islem_sonuc[ 'id' ] = $id;
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;



$tab_no = $_REQUEST[ 'tab_no' ]+1;
if( $_REQUEST[ 'tab_no' ] == 2 and $_REQUEST[ 'vekil' ] == 0 )
	$tab_no = 4;
if( $_REQUEST[ 'tab_no' ] == 9)
	$tab_no = 9;
if( $_REQUEST[ 'tab_no' ] == 8)
	$tab_no = 8;
if( $_REQUEST[ 'islem' ] == 'ekstra_hizmet_bedeli_onayla' and $_REQUEST[ 'tab_no' ] == 6 )
	$tab_no = 6;
if( $_REQUEST[ 'islem' ] == 'ekstra_hizmet_bedeli_onay_kaldir' and $_REQUEST[ 'tab_no' ] == 6 )
	$tab_no = 6;
if( $_REQUEST[ 'islem' ] == 'ekstra_istenen_hizmet_bedeli' and $_REQUEST[ 'tab_no' ] == 6 )
	$tab_no = 6;
if( $_REQUEST[ 'islem' ] == 'yeni_fiyat_ekle' and $_REQUEST[ 'tab_no' ] == 6 )
	$tab_no = 6;
	

header( 'Location: ../../index.php?modul=araclar&islem=detaylar&id='.$id.'&tab_no=' . $tab_no );

?>