<?php 

function araclar_temel_bilgiler($arac_bilgileri){
	$eksik_alan = 0;
	if( $arac_bilgileri[ 'sube_id' ] == "" or $arac_bilgileri[ 'sube_id' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'arac_no' ] == "" or $arac_bilgileri[ 'arac_no' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'kayit_tarihi' ] == "" or $arac_bilgileri[ 'kayit_tarihi' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}

function araclar_arac_sahibi($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'sahip_tc_no' ] == "" or $arac_bilgileri[ 'sahip_tc_no' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'sahip_adi' ] == "" or $arac_bilgileri[ 'sahip_adi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'sahip_soyadi' ] == "" or $arac_bilgileri[ 'sahip_soyadi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'sahip_cep_tel' ] == "" or $arac_bilgileri[ 'sahip_cep_tel' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'sahip_email' ] == "" or $arac_bilgileri[ 'sahip_email' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'sahip_adres' ] == "" or $arac_bilgileri[ 'sahip_adres' ] == null )
		$eksik_alan++;	
	if( $arac_bilgileri[ 'vekil' ] == "" or $arac_bilgileri[ 'vekil' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}

function araclar_vekalet_bilgileri($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'vekil_tc_no' ] == "" or $arac_bilgileri[ 'vekil_tc_no' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'vekil_adi' ] == "" or $arac_bilgileri[ 'vekil_adi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'vekil_soyadi' ] == "" or $arac_bilgileri[ 'vekil_soyadi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'vekil_cep_tel' ] == "" or $arac_bilgileri[ 'vekil_cep_tel' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'vekil_email' ] == "" or $arac_bilgileri[ 'vekil_email' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'vekil_adres' ] == "" or $arac_bilgileri[ 'vekil_adres' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}
		
function araclar_arac_bilgileri($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'plaka' ] == "" or $arac_bilgileri[ 'plaka' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'arac_durumu' ] == "" or $arac_bilgileri[ 'arac_durumu' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'plaka_durumu' ] == "" or $arac_bilgileri[ 'plaka_durumu' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'arac_tipi_id' ] == "" or $arac_bilgileri[ 'arac_tipi_id' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'arac_kasa_tipi_id' ] == "" or $arac_bilgileri[ 'arac_kasa_tipi_id' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'model_tipi' ] == "" or $arac_bilgileri[ 'model_tipi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'donanim_paketi' ] == "" or $arac_bilgileri[ 'donanim_paketi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'arac_vites_tipi_id' ] == "" or $arac_bilgileri[ 'arac_vites_tipi_id' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'arac_vites_sayisi_id' ] == "" or $arac_bilgileri[ 'arac_vites_sayisi_id' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'km' ] == "" or $arac_bilgileri[ 'km' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'arac_cekis_tipi_id' ] == "" or $arac_bilgileri[ 'arac_cekis_tipi_id' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'arac_marka_id' ] == "" or $arac_bilgileri[ 'arac_marka_id' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ticari_adi' ] == "" or $arac_bilgileri[ 'ticari_adi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'silindir_hacmi' ] == "" or $arac_bilgileri[ 'silindir_hacmi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'model_yili' ] == "" or $arac_bilgileri[ 'model_yili' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'arac_yakit_tipi_id' ] == "" or $arac_bilgileri[ 'arac_yakit_tipi_id' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'renk_id' ] == "" or $arac_bilgileri[ 'renk_id' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'tipi' ] == "" or $arac_bilgileri[ 'tipi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'motor_gucu' ] == "" or $arac_bilgileri[ 'motor_gucu' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'garanti_durumu' ] == "" or $arac_bilgileri[ 'garanti_durumu' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'yedek_anahtar' ] == "" or $arac_bilgileri[ 'yedek_anahtar' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'duzenli_servis_bakimi' ] == "" or $arac_bilgileri[ 'duzenli_servis_bakimi' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}
		
function araclar_ruhsat_bilgileri($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'ruhsat_verildigi_il_ilce_y1' ] == "" or $arac_bilgileri[ 'ruhsat_verildigi_il_ilce_y1' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_ilk_tescil_tarihi_b9' ] == "" or $arac_bilgileri[ 'ruhsat_ilk_tescil_tarihi_b9' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_tescil_tarihi_1' ] == "" or $arac_bilgileri[ 'ruhsat_tescil_tarihi_1' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_arac_sinifi_j' ] == "" or $arac_bilgileri[ 'ruhsat_arac_sinifi_j' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_cinsi_d5' ] == "" or $arac_bilgileri[ 'ruhsat_cinsi_d5' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_motor_no_p5' ] == "" or $arac_bilgileri[ 'ruhsat_motor_no_p5' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_sase_no_e' ] == "" or $arac_bilgileri[ 'ruhsat_sase_no_e' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_koltuk_sayisi_s1' ] == "" or $arac_bilgileri[ 'ruhsat_koltuk_sayisi_s1' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_kullanim_amaci_y3' ] == "" or $arac_bilgileri[ 'ruhsat_kullanim_amaci_y3' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_belge_seri' ] == "" or $arac_bilgileri[ 'ruhsat_belge_seri' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_no' ] == "" or $arac_bilgileri[ 'ruhsat_no' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_muayene_gecerlilik_tarihi' ] == "" or $arac_bilgileri[ 'ruhsat_muayene_gecerlilik_tarihi' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}
				
function araclar_fiyatlama($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'piyasa_degeri' ] == "" or $arac_bilgileri[ 'piyasa_degeri' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'kasko_degeri' ] == "" or $arac_bilgileri[ 'kasko_degeri' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'talep_fiyat' ] == "" or $arac_bilgileri[ 'talep_fiyat' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'hizmet_bedeli' ] == "" or $arac_bilgileri[ 'hizmet_bedeli' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'pazarlik_payi' ] == "" or $arac_bilgileri[ 'pazarlik_payi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ilan_fiyati' ] == "" or $arac_bilgileri[ 'ilan_fiyati' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}
		
function araclar_kayit_kontrol($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'rehin_durumu' ] == "" or $arac_bilgileri[ 'rehin_durumu' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'trafik_cezasi' ] == "" or $arac_bilgileri[ 'trafik_cezasi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'mtv_borcu' ] == "" or $arac_bilgileri[ 'mtv_borcu' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}

function araclar_fotokopiler($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'sahip_kimlik_foto' ] == "" or $arac_bilgileri[ 'sahip_kimlik_foto' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'vekil_kimlik_foto' ] == 1 AND ($arac_bilgileri[ 'vekil_kimlik_foto' ] == "" or $arac_bilgileri[ 'vekil_kimlik_foto' ] == null) )
		$eksik_alan++;
	if( $arac_bilgileri[ 'ruhsat_foto' ] == "" or $arac_bilgileri[ 'ruhsat_foto' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}
				
function araclar_sozlesme($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'print_hizmet_sozlesmesi' ] == "" or $arac_bilgileri[ 'print_hizmet_sozlesmesi' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'print_arac_info' ] == "" or $arac_bilgileri[ 'print_arac_info' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'print_qr_kod' ] == "" or $arac_bilgileri[ 'print_qr_kod' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}
		
function arac_detaylari_genel_kontrol($arac_bilgileri){
	$toplam_eksik_alan = 0;
	$toplam_eksik_alan += araclar_temel_bilgiler($arac_bilgileri);
	$toplam_eksik_alan += araclar_arac_sahibi($arac_bilgileri);
	if( $arac_bilgileri[ 'vekil' ] == 1 ){
		$toplam_eksik_alan += araclar_vekalet_bilgileri($arac_bilgileri);
	}
	$toplam_eksik_alan += araclar_arac_bilgileri($arac_bilgileri);
	$toplam_eksik_alan += araclar_ruhsat_bilgileri($arac_bilgileri);
	$toplam_eksik_alan += araclar_fiyatlama($arac_bilgileri);
	$toplam_eksik_alan += araclar_kayit_kontrol($arac_bilgileri);
	$toplam_eksik_alan += araclar_fotokopiler($arac_bilgileri);
	$toplam_eksik_alan += araclar_sozlesme($arac_bilgileri);
	
	return $toplam_eksik_alan;
}		
		
		

?>