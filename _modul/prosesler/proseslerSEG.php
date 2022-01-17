<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt			= new VeriTabani();
$fn			= new Fonksiyonlar();
$id			= array_key_exists( 'id' , $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$expertiz_id	= array_key_exists( 'expertiz_id' , $_REQUEST ) ? $_REQUEST[ 'expertiz_id' ] : 0;

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
	header( 'Location: ../../index.php?modul=prosesler&islem=prosesler&id='.$id.'&tab_no='.$_REQUEST['tab_no'] );
	exit;
}
/* Dosya Kapatılmışsa Hiç bir işlem yapılamaz */


function resimkucult($filename1,$max_width,$max_height){
    $image = $filename1;
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
function sifre_uret($uzunluk){
    if(!is_numeric($uzunluk) || $uzunluk <= 0){
            $uzunluk = 8;
    }
    if($uzunluk  > 32){
		$uzunluk = 32;
	}
	$karakter = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	mt_srand(microtime() * 1000000);
 
	for($i = 0; $i < $uzunluk; $i++){
		$key = mt_rand(0,strlen($karakter)-1);
		$pwd = $pwd . $karakter{$key};
	}
	for($i = 0; $i < $uzunluk; $i++){
		$key1 = mt_rand(0,strlen($pwd)-1);
		$key2 = mt_rand(0,strlen($pwd)-1);
	
		$tmp = $pwd{$key1};
		$pwd{$key1} = $pwd{$key2};
		$pwd{$key2} = $tmp;
	}
        return $pwd;
}
if( isset( $_FILES['file'] ) and $_FILES['file']['size']>0 ){
	if( !file_exists("../../arac_resimler/".$_REQUEST['arac_no']."/") )
		mkdir("../../arac_resimler/".$_REQUEST['arac_no']."/");
		
	if( !file_exists("../../arac_resimler/".$_REQUEST['arac_no']."/kucuk/") )
		mkdir("../../arac_resimler/".$_REQUEST['arac_no']."/kucuk/");
	$dosya_adi=$_REQUEST['arac_no']."_arac_foto_".sifre_uret(16).".".pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
	$dizin = "../../arac_resimler/".$_REQUEST['arac_no']."/";
	$dizin_kucuk = "../../arac_resimler/".$_REQUEST['arac_no']."/kucuk/";
	$target_path_temp = $dizin.$dosya_adi;
	$target_path_temp_kucuk = $dizin_kucuk.$dosya_adi;
	move_uploaded_file($_FILES['file']['tmp_name'], $target_path_temp);
	copy($target_path_temp, $target_path_temp_kucuk);
	resimkucult($target_path_temp,1200,1200);	
	resimkucult($target_path_temp_kucuk,400,400);	
}

if( isset( $_FILES['expertiz_raporu'] ) and $_FILES['expertiz_raporu']['size']>0 ){
	if( !file_exists("../../arac_resimler/".$_REQUEST['arac_no']."/") )
		mkdir("../../arac_resimler/".$_REQUEST['arac_no']."/");
	$expertiz_dosya_adi=$_REQUEST['arac_no']."_arac_expertiz_raporu_".sifre_uret(16).".".pathinfo($_FILES['expertiz_raporu']['name'], PATHINFO_EXTENSION);
	$dizin = "../../arac_resimler/".$_REQUEST['arac_no']."/";
	$target_path_temp = $dizin.$expertiz_dosya_adi;
	move_uploaded_file($_FILES['expertiz_raporu']['tmp_name'], $target_path_temp);
}

if( $_REQUEST['expertiz_tarihi'] == '' )
	$expertiz_tarihi=null;
else
	$expertiz_tarihi=date('Y-m-d H:i',strtotime($_REQUEST['expertiz_tarihi']));


$SQL_guncelle1 = <<< SQL
UPDATE
	tb_araclar
SET
	 proses1_esya_teslim = ?
WHERE
	id = ?
SQL;

$SQL_guncelle2 = <<< SQL
UPDATE
	tb_araclar
SET
	 proses2_arac_temizlik 	= ?
WHERE
	id 				= ?
SQL;

$SQL_guncelle3 = <<< SQL
UPDATE
	tb_araclar
SET
	 proses3_agir_hasar_sorgusu 		= ?
	,proses3_km_kontrolu 				= ?
	,proses3_tramer_kontrolu 			= ?
	,proses3_hasar_bilgileri 			= ?
	,proses3_tramer_kontrolu_yapildi 	= ?
WHERE
	id 				= ?
SQL;

$SQL_guncelle4_raporlu = <<< SQL
UPDATE
	tb_arac_expertiz
SET
 arac_id						 = ?
,test_no						 = ?
,expertiz_yapildi				 = ?
,sol_on_camurluk_boya_id		 = ?
,sol_on_camurluk_kaporta_id		 = ?
,sol_on_kapi_boya_id			 = ?
,sol_on_kapi_kaporta_id			 = ?
,sol_arka_kapi_boya_id			 = ?
,sol_arka_kapi_kaporta_id		 = ?
,sol_arka_camurluk_boya_id		 = ?
,sol_arka_camurluk_kaporta_id	 = ?
,arka_tampon_boya_id			 = ?
,arka_tampon_kaporta_id			 = ?
,arka_bagaj_kapisi_boya_id		 = ?
,arka_bagaj_kapisi_kaporta_id	 = ?
,sag_arka_camurluk_boya_id		 = ?
,sag_arka_camurluk_kaporta_id	 = ?
,sag_arka_kapi_boya_id			 = ?
,sag_arka_kapi_kaporta_id		 = ?
,sag_on_kapi_boya_id			 = ?
,sag_on_kapi_kaporta_id			 = ?
,sag_on_camurluk_boya_id		 = ?
,sag_on_camurluk_kaporta_id		 = ?
,on_tampon_boya_id				 = ?
,on_tampon_kaporta_id			 = ?
,on_kaput_boya_id				 = ?
,on_kaput_kaporta_id			 = ?
,tavan_boya_id					 = ?
,tavan_kaporta_id				 = ?
,sol_marspiyel_boya_id			 = ?
,sol_marspiyel_kaporta_id		 = ?
,sag_marspiyel_boya_id			 = ?
,sag_marspiyel_kaporta_id		 = ?
,motor_hp						 = ?
,motor_tork						 = ?
,teker_hp						 = ?
,teker_tork						 = ?
,kayip_guc_hp					 = ?
,motor_performans				 = ?
,yanal_kayma_on					 = ?
,yanal_kayma_arka				 = ?
,yanal_kayma					 = ?
,suspansiyon_on_sol				 = ?
,suspansiyon_on_sag				 = ?
,suspansiyon_arka_sol			 = ?
,suspansiyon_arka_sag			 = ?
,suspansiyon_on_fark			 = ?
,suspansiyon_arka_fark			 = ?
,suspansiyon_sol_fark			 = ?
,suspansiyon_sag_fark			 = ?
,fren_on_sol_kn					 = ?
,fren_on_sol_yuzde				 = ?
,fren_on_sag_kn					 = ?
,fren_on_sag_yuzde				 = ?
,fren_on_sapma					 = ?
,fren_arka_sol_kn				 = ?
,fren_arka_sol_yuzde			 = ?
,fren_arka_sag_kn				 = ?
,fren_arka_sag_yuzde			 = ?
,fren_arka_sapma				 = ?
,bagimsiz_expertiz_notlari		 = ?
,expertiz_tarihi				 = ?
,expertiz_raporu				 = ?
WHERE
	id 	= ?
SQL;

$SQL_guncelle4_raporsuz = <<< SQL
UPDATE
	tb_arac_expertiz
SET
 arac_id						 = ?
,test_no						 = ?
,expertiz_yapildi				 = ?
,sol_on_camurluk_boya_id		 = ?
,sol_on_camurluk_kaporta_id		 = ?
,sol_on_kapi_boya_id			 = ?
,sol_on_kapi_kaporta_id			 = ?
,sol_arka_kapi_boya_id			 = ?
,sol_arka_kapi_kaporta_id		 = ?
,sol_arka_camurluk_boya_id		 = ?
,sol_arka_camurluk_kaporta_id	 = ?
,arka_tampon_boya_id			 = ?
,arka_tampon_kaporta_id			 = ?
,arka_bagaj_kapisi_boya_id		 = ?
,arka_bagaj_kapisi_kaporta_id	 = ?
,sag_arka_camurluk_boya_id		 = ?
,sag_arka_camurluk_kaporta_id	 = ?
,sag_arka_kapi_boya_id			 = ?
,sag_arka_kapi_kaporta_id		 = ?
,sag_on_kapi_boya_id			 = ?
,sag_on_kapi_kaporta_id			 = ?
,sag_on_camurluk_boya_id		 = ?
,sag_on_camurluk_kaporta_id		 = ?
,on_tampon_boya_id				 = ?
,on_tampon_kaporta_id			 = ?
,on_kaput_boya_id				 = ?
,on_kaput_kaporta_id			 = ?
,tavan_boya_id					 = ?
,tavan_kaporta_id				 = ?
,sol_marspiyel_boya_id			 = ?
,sol_marspiyel_kaporta_id		 = ?
,sag_marspiyel_boya_id			 = ?
,sag_marspiyel_kaporta_id		 = ?
,motor_hp						 = ?
,motor_tork						 = ?
,teker_hp						 = ?
,teker_tork						 = ?
,kayip_guc_hp					 = ?
,motor_performans				 = ?
,yanal_kayma_on					 = ?
,yanal_kayma_arka				 = ?
,yanal_kayma					 = ?
,suspansiyon_on_sol				 = ?
,suspansiyon_on_sag				 = ?
,suspansiyon_arka_sol			 = ?
,suspansiyon_arka_sag			 = ?
,suspansiyon_on_fark			 = ?
,suspansiyon_arka_fark			 = ?
,suspansiyon_sol_fark			 = ?
,suspansiyon_sag_fark			 = ?
,fren_on_sol_kn					 = ?
,fren_on_sol_yuzde				 = ?
,fren_on_sag_kn					 = ?
,fren_on_sag_yuzde				 = ?
,fren_on_sapma					 = ?
,fren_arka_sol_kn				 = ?
,fren_arka_sol_yuzde			 = ?
,fren_arka_sag_kn				 = ?
,fren_arka_sag_yuzde			 = ?
,fren_arka_sapma				 = ?
,bagimsiz_expertiz_notlari		 = ?
,expertiz_tarihi				 = ?
WHERE
	id 	= ?
SQL;



$SQL_guncelle5 = <<< SQL
UPDATE
	tb_araclar
SET
 proses5_dis_makyaj_gozle_kontrol		 = ?
,proses5_ic_makyaj_gozle_kontrol		 = ?
,proses5_elektronik_aksam_gozle_kontrol	 = ?
,proses5_mekanik_aksam_gozle_kontrol	 = ?
,proses5_genel_gozle_kontrol			 = ?
WHERE
	id 	= ?
SQL;

$SQL_guncelle6 = <<< SQL
UPDATE
	tb_araclar
SET
	 proses6_on_sol_deger			 = ?
	,proses6_on_sol_lastik_tipi_id	 = ?
	,proses6_on_sag_deger			 = ?
	,proses6_on_sag_lastik_tipi_id	 = ?
	,proses6_arka_sol_deger			 = ?
	,proses6_arka_sol_lastik_tipi_id = ?
	,proses6_arka_sag_deger			 = ?
	,proses6_arka_sag_lastik_tipi_id = ?
	,proses6_stepne_lastik_tipi_id	 = ?
	,proses6_lastik_olcumu			 = ?
WHERE
	id 	= ?
SQL;

$SQL_guncelle7 = <<< SQL
UPDATE
	tb_araclar
SET
	 proses7_alternator_durumu	= ?
	,proses7_aku_durumu			= ?
WHERE
	id 	= ?
SQL;

$SQL_guncelle10 = <<< SQL
UPDATE
	tb_araclar
SET
	 onaya_gonderildi			= 1
	,onaya_gonderen_personel_id	= ?
	,onaya_gonderme_tarihi		= now()
WHERE
	id 	= ?
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


$SQL_medya_ekle = <<< SQL
INSERT INTO
	tb_arac_medya
SET
	 arac_id		 = ?
	,dosya_adi	 = ?
SQL;

$SQL_yayin_ekle = <<< SQL
INSERT INTO
	tb_arac_yayinlari
SET
	 arac_id	 = ?
	,yayin_yeri_id	 = ?
	,yayin_linki	 = ?
	,yayinlandi	 = 1
	,yayinlanma_tarihi	 = now()
SQL;

$SQL_yayin_guncelle = <<< SQL
UPDATE
	tb_arac_yayinlari
SET
	 arac_id	 = ?
	,yayin_yeri_id	 = ?
	,yayin_linki	 = ?
	,yayinlandi	 = 1
WHERE
	id = ?
SQL;

$SQL_medya_sil = <<< SQL
DELETE FROM 
	tb_arac_medya 
WHERE 
	id = ?
SQL;

$SQL_medya_kapak = <<< SQL
UPDATE
	tb_arac_medya
SET
	kapak_foto	 = 1
WHERE
	id = ?
SQL;

$SQL_medya_kapak2 = <<< SQL
UPDATE
	tb_arac_medya
SET
	kapak_foto	 = 0
WHERE
	id != ? and arac_id = ?
SQL;

$SQL_yayindan_kaldir = <<< SQL
UPDATE 
	tb_arac_yayinlari 
SET
	 yayindan_alindi = not yayindan_alindi
	,yayindan_alinma_tarihi = now()
WHERE 
	id = ?
SQL;


$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );
$vt->islemBaslat();
if( array_key_exists( 'islem', $_REQUEST ) ) {
	switch( $_REQUEST[ 'islem' ] ) {
		case 'ekle':
			if( array_key_exists( 'tab_no', $_REQUEST ) ) {
				switch( $_REQUEST[ 'tab_no' ] ) {
					case '9':
						$sorgu_sonuc = $vt->insert( $SQL_yayin_ekle, array(
							 $id
							,$_REQUEST[ 'yayin_yeri_id' ]
							,$_REQUEST[ 'yayin_linki' ]
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
				}
			}
		break;
		case 'yayin_guncelle':
			if( array_key_exists( 'tab_no', $_REQUEST ) ) {
				switch( $_REQUEST[ 'tab_no' ] ) {
					case '9':
						$sorgu_sonuc = $vt->update( $SQL_yayin_guncelle, array(
							 $id
							,$_REQUEST[ 'yayin_yeri_id' ]
							,$_REQUEST[ 'yayin_linki' ]
							,$_REQUEST[ 'yayin_id' ]
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
				}
			}		
		break;
		case 'medya_sil':
			if( array_key_exists( 'tab_no', $_REQUEST ) ) {
				switch( $_REQUEST[ 'tab_no' ] ) {
					case '8':
						$sorgu_sonuc = $vt->delete( $SQL_medya_sil, array(
							 $_REQUEST['medya_id']
						) );
						unlink( "../../arac_resimler/".$_REQUEST['arac_no']."/".$_REQUEST['dosya_adi'] );
						unlink( "../../arac_resimler/".$_REQUEST['arac_no']."/kucuk/".$_REQUEST['dosya_adi'] );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Medya Silinirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
				}
			}		
		break;
		case 'medya_kapak':
			if( array_key_exists( 'tab_no', $_REQUEST ) ) {
				switch( $_REQUEST[ 'tab_no' ] ) {
					case '8':
						$sorgu_sonuc = $vt->update( $SQL_medya_kapak, array(
							 $_REQUEST['medya_id']
						) );
						$sorgu_sonuc = $vt->update( $SQL_medya_kapak2, array(
							 $_REQUEST['medya_id'], $id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
				}
			}		
		break;
		case 'onayla':
				$sorgu_sonuc = $vt->update( $SQL_onayla, array(
					 $_SESSION[ 'kullanici_id' ]
					,$id
				) );
				if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
		break;
		case 'guncelle':
			if( array_key_exists( 'tab_no', $_REQUEST ) ) {
				switch( $_REQUEST[ 'tab_no' ] ) {
					case '1':
						$sorgu_sonuc = $vt->update( $SQL_guncelle1, array(
							 $_REQUEST[ 'proses1_esya_teslim' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '2':
						$sorgu_sonuc = $vt->update( $SQL_guncelle2, array(
							 $_REQUEST[ 'proses2_arac_temizlik' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '3':
						$sorgu_sonuc = $vt->update( $SQL_guncelle3, array(
							 $_REQUEST[ 'proses3_agir_hasar_sorgusu' ]
							,$_REQUEST[ 'proses3_km_kontrolu' ]
							,$_REQUEST[ 'proses3_tramer_kontrolu' ]
							,$_REQUEST[ 'proses3_hasar_bilgileri' ]
							,$_REQUEST[ 'proses3_tramer_kontrolu_yapildi' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '4':
					if( isset( $_FILES['expertiz_raporu'] ) and $_FILES['expertiz_raporu']['size']>0 ){
						$sorgu_sonuc = $vt->update( $SQL_guncelle4_raporlu, array(
							 $id
							,$_REQUEST[ 'test_no' ]
							,$_REQUEST[ 'expertiz_yapildi' ]
							,$_REQUEST[ 'sol_on_camurluk_boya_id' ]
							,$_REQUEST[ 'sol_on_camurluk_kaporta_id' ]
							,$_REQUEST[ 'sol_on_kapi_boya_id' ]
							,$_REQUEST[ 'sol_on_kapi_kaporta_id' ]
							,$_REQUEST[ 'sol_arka_kapi_boya_id' ]
							,$_REQUEST[ 'sol_arka_kapi_kaporta_id' ]
							,$_REQUEST[ 'sol_arka_camurluk_boya_id' ]
							,$_REQUEST[ 'sol_arka_camurluk_kaporta_id' ]
							,$_REQUEST[ 'arka_tampon_boya_id' ]
							,$_REQUEST[ 'arka_tampon_kaporta_id' ]
							,$_REQUEST[ 'arka_bagaj_kapisi_boya_id' ]
							,$_REQUEST[ 'arka_bagaj_kapisi_kaporta_id' ]
							,$_REQUEST[ 'sag_arka_camurluk_boya_id' ]
							,$_REQUEST[ 'sag_arka_camurluk_kaporta_id' ]
							,$_REQUEST[ 'sag_arka_kapi_boya_id' ]
							,$_REQUEST[ 'sag_arka_kapi_kaporta_id' ]
							,$_REQUEST[ 'sag_on_kapi_boya_id' ]
							,$_REQUEST[ 'sag_on_kapi_kaporta_id' ]
							,$_REQUEST[ 'sag_on_camurluk_boya_id' ]
							,$_REQUEST[ 'sag_on_camurluk_kaporta_id' ]
							,$_REQUEST[ 'on_tampon_boya_id' ]
							,$_REQUEST[ 'on_tampon_kaporta_id' ]
							,$_REQUEST[ 'on_kaput_boya_id' ]
							,$_REQUEST[ 'on_kaput_kaporta_id' ]
							,$_REQUEST[ 'tavan_boya_id' ]
							,$_REQUEST[ 'tavan_kaporta_id' ]
							,$_REQUEST[ 'sol_marspiyel_boya_id' ]
							,$_REQUEST[ 'sol_marspiyel_kaporta_id' ]
							,$_REQUEST[ 'sag_marspiyel_boya_id' ]
							,$_REQUEST[ 'sag_marspiyel_kaporta_id' ]
							,$_REQUEST[ 'motor_hp' ]
							,$_REQUEST[ 'motor_tork' ]
							,$_REQUEST[ 'teker_hp' ]
							,$_REQUEST[ 'teker_tork' ]
							,$_REQUEST[ 'kayip_guc_hp' ]
							,$_REQUEST[ 'motor_performans' ]
							,$_REQUEST[ 'yanal_kayma_on' ]
							,$_REQUEST[ 'yanal_kayma_arka' ]
							,$_REQUEST[ 'yanal_kayma' ]
							,$_REQUEST[ 'suspansiyon_on_sol' ]
							,$_REQUEST[ 'suspansiyon_on_sag' ]
							,$_REQUEST[ 'suspansiyon_arka_sol' ]
							,$_REQUEST[ 'suspansiyon_arka_sag' ]
							,$_REQUEST[ 'suspansiyon_on_fark' ]
							,$_REQUEST[ 'suspansiyon_arka_fark' ]
							,$_REQUEST[ 'suspansiyon_sol_fark' ]
							,$_REQUEST[ 'suspansiyon_sag_fark' ]
							,$_REQUEST[ 'fren_on_sol_kn' ]
							,$_REQUEST[ 'fren_on_sol_yuzde' ]
							,$_REQUEST[ 'fren_on_sag_kn' ]
							,$_REQUEST[ 'fren_on_sag_yuzde' ]
							,$_REQUEST[ 'fren_on_sapma' ]
							,$_REQUEST[ 'fren_arka_sol_kn' ]
							,$_REQUEST[ 'fren_arka_sol_yuzde' ]
							,$_REQUEST[ 'fren_arka_sag_kn' ]
							,$_REQUEST[ 'fren_arka_sag_yuzde' ]
							,$_REQUEST[ 'fren_arka_sapma' ]
							,$_REQUEST[ 'bagimsiz_expertiz_notlari' ]
							,$expertiz_tarihi
							,$expertiz_dosya_adi
							,$expertiz_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					}else{
						$sorgu_sonuc = $vt->update( $SQL_guncelle4_raporsuz, array(
							 $id
							,$_REQUEST[ 'test_no' ]
							,$_REQUEST[ 'expertiz_yapildi' ]
							,$_REQUEST[ 'sol_on_camurluk_boya_id' ]
							,$_REQUEST[ 'sol_on_camurluk_kaporta_id' ]
							,$_REQUEST[ 'sol_on_kapi_boya_id' ]
							,$_REQUEST[ 'sol_on_kapi_kaporta_id' ]
							,$_REQUEST[ 'sol_arka_kapi_boya_id' ]
							,$_REQUEST[ 'sol_arka_kapi_kaporta_id' ]
							,$_REQUEST[ 'sol_arka_camurluk_boya_id' ]
							,$_REQUEST[ 'sol_arka_camurluk_kaporta_id' ]
							,$_REQUEST[ 'arka_tampon_boya_id' ]
							,$_REQUEST[ 'arka_tampon_kaporta_id' ]
							,$_REQUEST[ 'arka_bagaj_kapisi_boya_id' ]
							,$_REQUEST[ 'arka_bagaj_kapisi_kaporta_id' ]
							,$_REQUEST[ 'sag_arka_camurluk_boya_id' ]
							,$_REQUEST[ 'sag_arka_camurluk_kaporta_id' ]
							,$_REQUEST[ 'sag_arka_kapi_boya_id' ]
							,$_REQUEST[ 'sag_arka_kapi_kaporta_id' ]
							,$_REQUEST[ 'sag_on_kapi_boya_id' ]
							,$_REQUEST[ 'sag_on_kapi_kaporta_id' ]
							,$_REQUEST[ 'sag_on_camurluk_boya_id' ]
							,$_REQUEST[ 'sag_on_camurluk_kaporta_id' ]
							,$_REQUEST[ 'on_tampon_boya_id' ]
							,$_REQUEST[ 'on_tampon_kaporta_id' ]
							,$_REQUEST[ 'on_kaput_boya_id' ]
							,$_REQUEST[ 'on_kaput_kaporta_id' ]
							,$_REQUEST[ 'tavan_boya_id' ]
							,$_REQUEST[ 'tavan_kaporta_id' ]
							,$_REQUEST[ 'sol_marspiyel_boya_id' ]
							,$_REQUEST[ 'sol_marspiyel_kaporta_id' ]
							,$_REQUEST[ 'sag_marspiyel_boya_id' ]
							,$_REQUEST[ 'sag_marspiyel_kaporta_id' ]
							,$_REQUEST[ 'motor_hp' ]
							,$_REQUEST[ 'motor_tork' ]
							,$_REQUEST[ 'teker_hp' ]
							,$_REQUEST[ 'teker_tork' ]
							,$_REQUEST[ 'kayip_guc_hp' ]
							,$_REQUEST[ 'motor_performans' ]
							,$_REQUEST[ 'yanal_kayma_on' ]
							,$_REQUEST[ 'yanal_kayma_arka' ]
							,$_REQUEST[ 'yanal_kayma' ]
							,$_REQUEST[ 'suspansiyon_on_sol' ]
							,$_REQUEST[ 'suspansiyon_on_sag' ]
							,$_REQUEST[ 'suspansiyon_arka_sol' ]
							,$_REQUEST[ 'suspansiyon_arka_sag' ]
							,$_REQUEST[ 'suspansiyon_on_fark' ]
							,$_REQUEST[ 'suspansiyon_arka_fark' ]
							,$_REQUEST[ 'suspansiyon_sol_fark' ]
							,$_REQUEST[ 'suspansiyon_sag_fark' ]
							,$_REQUEST[ 'fren_on_sol_kn' ]
							,$_REQUEST[ 'fren_on_sol_yuzde' ]
							,$_REQUEST[ 'fren_on_sag_kn' ]
							,$_REQUEST[ 'fren_on_sag_yuzde' ]
							,$_REQUEST[ 'fren_on_sapma' ]
							,$_REQUEST[ 'fren_arka_sol_kn' ]
							,$_REQUEST[ 'fren_arka_sol_yuzde' ]
							,$_REQUEST[ 'fren_arka_sag_kn' ]
							,$_REQUEST[ 'fren_arka_sag_yuzde' ]
							,$_REQUEST[ 'fren_arka_sapma' ]
							,$_REQUEST[ 'bagimsiz_expertiz_notlari' ]
							,$expertiz_tarihi
							,$expertiz_id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );						
					}
					break;
					case '5':
						$sorgu_sonuc = $vt->update( $SQL_guncelle5, array(
							 $_REQUEST[ 'proses5_dis_makyaj_gozle_kontrol' ]
							,$_REQUEST[ 'proses5_ic_makyaj_gozle_kontrol' ]
							,$_REQUEST[ 'proses5_elektronik_aksam_gozle_kontrol' ]
							,$_REQUEST[ 'proses5_mekanik_aksam_gozle_kontrol' ]
							,$_REQUEST[ 'proses5_genel_gozle_kontrol' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '6':
						$sorgu_sonuc = $vt->update( $SQL_guncelle6, array(
							 $_REQUEST[ 'proses6_on_sol_deger' ]
							,$_REQUEST[ 'proses6_on_sol_lastik_tipi_id' ]
							,$_REQUEST[ 'proses6_on_sag_deger' ]
							,$_REQUEST[ 'proses6_on_sag_lastik_tipi_id' ]
							,$_REQUEST[ 'proses6_arka_sol_deger' ]
							,$_REQUEST[ 'proses6_arka_sol_lastik_tipi_id' ]
							,$_REQUEST[ 'proses6_arka_sag_deger' ]
							,$_REQUEST[ 'proses6_arka_sag_lastik_tipi_id' ]
							,$_REQUEST[ 'proses6_stepne_lastik_tipi_id' ]
							,$_REQUEST[ 'proses6_lastik_olcumu' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '7':
						$sorgu_sonuc = $vt->update( $SQL_guncelle7, array(
							 $_REQUEST[ 'proses7_alternator_durumu' ]
							,$_REQUEST[ 'proses7_aku_durumu' ]
							,$id
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '8':
						$sorgu_sonuc = $vt->insert( $SQL_medya_ekle, array(
							 $id
							,$dosya_adi
						) );
						if( $sorgu_sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sorgu_sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sorgu_sonuc[ 1 ] );
					break;
					case '10':
						$sorgu_sonuc = $vt->update( $SQL_guncelle10, array(
							 $_SESSION[ 'kullanici_id' ]
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

$tab_no = $_REQUEST[ 'tab_no' ]+1;
if( $_REQUEST[ 'tab_no' ] == 8 )
	$tab_no = 8;
if( $_REQUEST[ 'tab_no' ] == 11 )
	$tab_no = 9;
if( $_REQUEST[ 'tab_no' ] == 9 )
	$tab_no = 9;
header( 'Location: ../../index.php?modul=prosesler&id='.$id.'&tab_no=' . $tab_no );

?>