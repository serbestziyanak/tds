<?php 

function prosesler_esya_teslim($arac_bilgileri){
	$eksik_alan = 0;
	if( $arac_bilgileri[ 'proses1_esya_teslim' ] == "" or $arac_bilgileri[ 'proses1_esya_teslim' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}

function prosesler_arac_temizlik($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'proses2_arac_temizlik' ] == "" or $arac_bilgileri[ 'proses2_arac_temizlik' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}

function prosesler_tramer($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'proses3_agir_hasar_sorgusu' ] == "" or $arac_bilgileri[ 'proses3_agir_hasar_sorgusu' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'proses3_km_kontrolu' ] == "" or $arac_bilgileri[ 'proses3_km_kontrolu' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'proses3_tramer_kontrolu' ] == "" or $arac_bilgileri[ 'proses3_tramer_kontrolu' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'proses3_hasar_bilgileri' ] == "" or $arac_bilgileri[ 'proses3_hasar_bilgileri' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'proses3_tramer_kontrolu_yapildi' ] == "0")
		$eksik_alan = 0;
	return $eksik_alan;
}
		
function prosesler_expertiz($arac_expertiz_bilgileri){
	$eksik_alan = 0;	
	if( $arac_expertiz_bilgileri[ 'test_no' ] == "" or $arac_expertiz_bilgileri[ 'test_no' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}
		
function prosesler_gozle_kontrol($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'proses5_dis_makyaj_gozle_kontrol' ] == "" or $arac_bilgileri[ 'proses5_dis_makyaj_gozle_kontrol' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'proses5_ic_makyaj_gozle_kontrol' ] == "" or $arac_bilgileri[ 'proses5_ic_makyaj_gozle_kontrol' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'proses5_elektronik_aksam_gozle_kontrol' ] == "" or $arac_bilgileri[ 'proses5_elektronik_aksam_gozle_kontrol' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'proses5_mekanik_aksam_gozle_kontrol' ] == "" or $arac_bilgileri[ 'proses5_mekanik_aksam_gozle_kontrol' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'proses5_genel_gozle_kontrol' ] == "" or $arac_bilgileri[ 'proses5_genel_gozle_kontrol' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}
				
function prosesler_lastik($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'proses6_lastik_olcumu' ] == "" or $arac_bilgileri[ 'proses6_lastik_olcumu' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}
		
function prosesler_aku($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'proses7_alternator_durumu' ] == "" or $arac_bilgileri[ 'proses7_alternator_durumu' ] == null )
		$eksik_alan++;
	if( $arac_bilgileri[ 'proses7_aku_durumu' ] == "" or $arac_bilgileri[ 'proses7_aku_durumu' ] == null )
		$eksik_alan++;
	return $eksik_alan;
}

function prosesler_medya($arac_medya_sayisi){
	$eksik_alan = 0;	
	if( $arac_medya_sayisi == 0  )
		$eksik_alan++;
	return $eksik_alan;
}
				
function prosesler_onay($arac_bilgileri){
	$eksik_alan = 0;	
	if( $arac_bilgileri[ 'onaya_gonderildi' ] <> 1 or $arac_bilgileri[ 'onaylandi' ] <> 1 )
		$eksik_alan++;
	return $eksik_alan;
}
		
function prosesler_yayin($arac_yayinlari){
	$eksik_alan = 0;	
	if( $arac_yayinlari[ 3 ] == 0 )
		$eksik_alan++;
	return $eksik_alan;
}
		
function prosesler_genel_kontrol($arac_bilgileri,$arac_expertiz_bilgileri,$arac_medya_sayisi,$arac_yayinlari){
	$toplam_eksik_alan = 0;
	$toplam_eksik_alan += prosesler_esya_teslim($arac_bilgileri);
	$toplam_eksik_alan += prosesler_arac_temizlik($arac_bilgileri);
	$toplam_eksik_alan += prosesler_tramer($arac_bilgileri);
	$toplam_eksik_alan += prosesler_expertiz($arac_expertiz_bilgileri);
	$toplam_eksik_alan += prosesler_gozle_kontrol($arac_bilgileri);
	$toplam_eksik_alan += prosesler_lastik($arac_bilgileri);
	$toplam_eksik_alan += prosesler_aku($arac_bilgileri);
	$toplam_eksik_alan += prosesler_medya($arac_medya_sayisi);
	$toplam_eksik_alan += prosesler_onay($arac_bilgileri);
	$toplam_eksik_alan += prosesler_yayin($arac_yayinlari);
	
	return $toplam_eksik_alan;
}		
		

?>