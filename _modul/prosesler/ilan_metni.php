<?php 

$SQL_arac_bilgileri = <<< SQL
SELECT
	a.*
	,at.adi as arac_tipi_adi
	,ar.adi as arac_renk_adi
	,am.adi as arac_marka_adi
	,akt.adi as arac_kasa_tipi_adi
	,act.adi as arac_cekis_tipi_adi
	,avt.adi as arac_vites_tipi_adi
	,ayt.adi as arac_yakit_tipi_adi
	,avs.adi as arac_vites_sayisi_adi
	,CONCAT(sk.adi," ",sk.soyadi) as personel
	,sube.yetki_belgesi_no
FROM
	tb_araclar as a
LEFT JOIN tb_arac_tipleri as at on a.arac_tipi_id = at.id
LEFT JOIN tb_arac_renkleri as ar on a.renk_id = ar.id
LEFT JOIN tb_arac_markalari as am on a.arac_marka_id = am.id
LEFT JOIN tb_arac_kasa_tipleri as akt on a.arac_kasa_tipi_id = akt.id
LEFT JOIN tb_arac_cekis_tipleri as act on a.arac_cekis_tipi_id = act.id
Left JOIN tb_arac_vites_tipleri as avt on a. arac_vites_tipi_id = avt.id
LEft JOIN tb_arac_yakit_tipleri as ayt on a.arac_yakit_tipi_id = ayt.id
LEFT JOIN tb_arac_vites_sayilari as avs on a.arac_vites_sayisi_id = avs.id
LEFT JOIN tb_sistem_kullanici as sk on a.personel_id = sk.id
LEFT JOIN tb_subeler as sube on a.sube_id = sube.id
WHERE
	a.id = ?
SQL;

$SQL_arac_lastik_tipi_ilan = <<< SQL
SELECT
	*
FROM
	tb_arac_lastik_tipleri
WHERE 
	id = ?
SQL;

$arac = $vt->selectSingle( $SQL_arac_bilgileri, array( $arac_id ) );
$ilan_arac_bilgileri = $arac[ 2 ];

if( $ilan_arac_bilgileri[ 'arac_durumu' ] == 1 ) $arac_durumu = "Sıfır"; elseif( $ilan_arac_bilgileri[ 'arac_durumu' ] == 2 ) $arac_durumu = "2. EL";
if( $ilan_arac_bilgileri[ 'plaka_durumu' ] == 1 ) $plaka_durumu = "TR Plaka"; elseif( $ilan_arac_bilgileri[ 'plaka_durumu' ] == 2 ) $plaka_durumu = "Yabancıdan Yabancıya"; elseif( $ilan_arac_bilgileri[ 'plaka_durumu' ] == 3 ) $plaka_durumu = "Misafir Plaka";
if( $ilan_arac_bilgileri[ 'garanti_durumu' ] == 1 ) $garanti_durumu = "VAR"; elseif( $ilan_arac_bilgileri[ 'garanti_durumu' ] == 2 ) $garanti_durumu = "YOK";
if( $ilan_arac_bilgileri[ 'yedek_anahtar' ] == 1 ) $yedek_anahtar = "VAR"; elseif( $ilan_arac_bilgileri[ 'yedek_anahtar' ] == 2 ) $yedek_anahtar = "YOK";
if( $ilan_arac_bilgileri[ 'duzenli_servis_bakimi' ] == 1 ) $duzenli_servis_bakimi = "EVET (Düzenli)"; elseif( $ilan_arac_bilgileri[ 'duzenli_servis_bakimi' ] == 2 ) $duzenli_servis_bakimi = "HAYIR (Düzenli Değil)";
if( $ilan_arac_bilgileri[ 'rehin_durumu' ] == 1 ) $rehin_durumu = "VAR"; elseif( $ilan_arac_bilgileri[ 'rehin_durumu' ] == 2 ) $rehin_durumu = "YOK";
if( $ilan_arac_bilgileri[ 'trafik_cezasi' ] == 1 ) $trafik_cezasi = "VAR"; elseif( $ilan_arac_bilgileri[ 'trafik_cezasi' ] == 2 ) $trafik_cezasi = "YOK";
if( $ilan_arac_bilgileri[ 'mtv_borcu' ] == 1 ) $mtv_borcu = "VAR"; elseif( $ilan_arac_bilgileri[ 'mtv_borcu' ] == 2 ) $mtv_borcu = "YOK";

$motor_gucu = round($ilan_arac_bilgileri[ 'motor_gucu' ]*1.34102);

?>
<div id="ilan_metni">
<br><b><?php echo $ilan_arac_bilgileri[ 'arac_marka_adi' ]." ".$ilan_arac_bilgileri[ 'ticari_adi' ]." - ".$ilan_arac_bilgileri[ 'model_tipi' ]." ".$ilan_arac_bilgileri[ 'donanim_paketi' ]." - ".$ilan_arac_bilgileri[ 'model_yili' ]." - KM : ".$ilan_arac_bilgileri[ 'km' ]." - ".$ilan_arac_bilgileri[ 'arac_vites_tipi_adi' ]." - ".$ilan_arac_bilgileri[ 'arac_yakit_tipi_adi' ]." - Araç No : ".$ilan_arac_bilgileri[ 'arac_no' ]; ?></b>
<br>
<br>İncelediğiniz araç OTOWOW markasının müşteri portföyünden 2. el olarak satışa sunduğu bir araçtır.
<br> 
<br><b>Yetki Belgesi No :</b> <?php echo $ilan_arac_bilgileri[ 'yetki_belgesi_no' ]; ?>
<br> 
<br><b>30 DAKİKADA KREDİ İMKANI</b>
<br> 
<br><b>ARAÇ BİLGİLERİ</b>
<br><b>Araç Tipi :</b> <?php echo $ilan_arac_bilgileri[ 'arac_tipi_adi' ]; ?>
<br><b>Kasa Tipi :</b> <?php echo $ilan_arac_bilgileri[ 'arac_kasa_tipi_adi' ]; ?>
<br><b>Model Tipi :</b> <?php echo $ilan_arac_bilgileri[ 'model_tipi' ]; ?>
<br><b>Donanım Paketi :</b> <?php echo $ilan_arac_bilgileri[ 'donanim_paketi' ]; ?>
<br><b>Vites Tipi :</b> <?php echo $ilan_arac_bilgileri[ 'arac_vites_tipi_adi' ]; ?>
<br><b>Vites Sayısı :</b> <?php echo $ilan_arac_bilgileri[ 'arac_vites_sayisi_adi' ]; ?>
<br><b>Kilometre/Saat :</b> <?php echo $ilan_arac_bilgileri[ 'km' ]; ?>
<br><b>Garanti :</b> <?php echo $garanti_durumu; ?>
<br><b>Çekiş :</b> <?php echo $ilan_arac_bilgileri[ 'arac_cekis_tipi_adi' ]; ?>
<br><b>Yedek Anahtar :</b> <?php echo $yedek_anahtar ?>
<br><b>Düzenli Servis Bakımı :</b> <?php echo $duzenli_servis_bakimi ?>
<br><b>Marka (D1) :</b> <?php echo $ilan_arac_bilgileri[ 'arac_marka_adi' ]; ?>
<br><b>Ticari Adı (D3) :</b> <?php echo $ilan_arac_bilgileri[ 'ticari_adi' ]; ?>
<br><b>Silindir Hacmi (P1) :</b> <?php echo $ilan_arac_bilgileri[ 'silindir_hacmi' ]; ?>
<br><b>Model Yıl (D4) :</b> <?php echo $ilan_arac_bilgileri[ 'model_yili' ]; ?>
<br><b>Yakıt Cinsi (P3) :</b> <?php echo $ilan_arac_bilgileri[ 'arac_yakit_tipi_adi' ]; ?>
<br><b>Rengi ( R) :</b> <?php echo $ilan_arac_bilgileri[ 'arac_renk_adi' ]; ?>
<br><b>Tipi (D2) :</b> <?php echo $ilan_arac_bilgileri[ 'tipi' ]; ?>
<br><b>Motor Gücü (kW) :</b> <?php echo $ilan_arac_bilgileri[ 'motor_gucu' ]; ?>
<br><b>Motor Gücü (HP) :</b> <?php echo $motor_gucu; ?>
<br><b>Motor No :</b> <?php echo substr($ilan_arac_bilgileri[ 'ruhsat_motor_no_p5' ],0,strlen($ilan_arac_bilgileri[ 'ruhsat_motor_no_p5' ])-5)."*****"; ?>
<br><b>Şasi No :</b> <?php echo substr($ilan_arac_bilgileri[ 'ruhsat_sase_no_e' ],0,strlen($ilan_arac_bilgileri[ 'ruhsat_sase_no_e' ])-5)."*****"; ?>
<br><b>Özellikler ve Ekstralar :</b> <?php echo $ilan_arac_bilgileri[ 'arac_ekstra' ]; ?>
<br> 
<br>
<?php
$arac_lastik_tipi_ilan = $vt->selectSingle( $SQL_arac_lastik_tipi_ilan, array( $ilan_arac_bilgileri[ 'proses6_on_sol_lastik_tipi_id' ] ) );
$proses6_on_sol_lastik_tipi_adi = $arac_lastik_tipi_ilan[ 2 ]['adi'];
$proses6_on_sol_lastik_tipi_min = $arac_lastik_tipi_ilan[ 2 ]['min'];

$arac_lastik_tipi_ilan = $vt->selectSingle( $SQL_arac_lastik_tipi_ilan, array( $ilan_arac_bilgileri[ 'proses6_on_sag_lastik_tipi_id' ] ) );
$proses6_on_sag_lastik_tipi_adi = $arac_lastik_tipi_ilan[ 2 ]['adi'];
$proses6_on_sag_lastik_tipi_min = $arac_lastik_tipi_ilan[ 2 ]['min'];

$arac_lastik_tipi_ilan = $vt->selectSingle( $SQL_arac_lastik_tipi_ilan, array( $ilan_arac_bilgileri[ 'proses6_arka_sol_lastik_tipi_id' ] ) );
$proses6_arka_sol_lastik_tipi_adi = $arac_lastik_tipi_ilan[ 2 ]['adi'];
$proses6_arka_sol_lastik_tipi_min = $arac_lastik_tipi_ilan[ 2 ]['min'];

$arac_lastik_tipi_ilan = $vt->selectSingle( $SQL_arac_lastik_tipi_ilan, array( $ilan_arac_bilgileri[ 'proses6_arka_sag_lastik_tipi_id' ] ) );
$proses6_arka_sag_lastik_tipi_adi = $arac_lastik_tipi_ilan[ 2 ]['adi'];
$proses6_arka_sag_lastik_tipi_min = $arac_lastik_tipi_ilan[ 2 ]['min'];

$arac_lastik_tipi_ilan = $vt->selectSingle( $SQL_arac_lastik_tipi_ilan, array( $ilan_arac_bilgileri[ 'proses6_stepne_lastik_tipi_id' ] ) );
$proses6_stepne_lastik_tipi_adi = $arac_lastik_tipi_ilan[ 2 ]['adi'];

?>
<br><b>LASTİK SEVİYE ÖLÇÜMÜ</b>
<br><b>Ön Sol Lastik Seviye Ölç. (mm/%) 	:</b> <?php if( $ilan_arac_bilgileri[ 'proses6_on_sol_deger' ] == '' or $ilan_arac_bilgileri[ 'proses6_on_sol_deger' ] == null or $ilan_arac_bilgileri[ 'proses6_on_sol_deger' ] == 0 ){ echo "Ölçülmedi"; }else{ ?><?php echo $ilan_arac_bilgileri[ 'proses6_on_sol_deger' ]; ?> (<?php echo $proses6_on_sol_lastik_tipi_adi ?>) <?php 		if( $ilan_arac_bilgileri[ 'proses6_on_sol_deger' ] >= $proses6_on_sol_lastik_tipi_min  ) echo "Yeterli"; else echo "Yetersiz";      }?>
<br><b>Ön Sağ Lastik Seviye Ölç. (mm/%) 	:</b> <?php if( $ilan_arac_bilgileri[ 'proses6_on_sag_deger' ] == '' or $ilan_arac_bilgileri[ 'proses6_on_sag_deger' ] == null or $ilan_arac_bilgileri[ 'proses6_on_sag_deger' ] == 0 ){ echo "Ölçülmedi"; }else{ ?><?php echo $ilan_arac_bilgileri[ 'proses6_on_sag_deger' ]; ?> (<?php echo $proses6_on_sag_lastik_tipi_adi ?>) <?php		if( $ilan_arac_bilgileri[ 'proses6_on_sag_deger' ] >= $proses6_on_sag_lastik_tipi_min  ) echo "Yeterli"; else echo "Yetersiz";     	}?>
<br><b>Arka Sol Lastik Seviye Ölç. (mm/%) 	:</b> <?php if( $ilan_arac_bilgileri[ 'proses6_arka_sol_deger' ] == '' or $ilan_arac_bilgileri[ 'proses6_arka_sol_deger' ] == null or $ilan_arac_bilgileri[ 'proses6_arka_sol_deger' ] == 0 ){ echo "Ölçülmedi"; }else{ ?><?php echo $ilan_arac_bilgileri[ 'proses6_arka_sol_deger' ]; ?> (<?php echo $proses6_arka_sol_lastik_tipi_adi ?>) <?php if( $ilan_arac_bilgileri[ 'proses6_arka_sol_deger' ] >= $proses6_arka_sol_lastik_tipi_min  ) echo "Yeterli"; else echo "Yetersiz"; }?>
<br><b>Arka Sağ Lastik Seviye Ölç. (mm/%) 	:</b> <?php if( $ilan_arac_bilgileri[ 'proses6_arka_sag_deger' ] == '' or $ilan_arac_bilgileri[ 'proses6_arka_sag_deger' ] == null  or $ilan_arac_bilgileri[ 'proses6_arka_sag_deger' ] == 0 ){ echo "Ölçülmedi"; }else{ ?><?php echo $ilan_arac_bilgileri[ 'proses6_arka_sag_deger' ]; ?> (<?php echo $proses6_arka_sag_lastik_tipi_adi ?>) <?php if( $ilan_arac_bilgileri[ 'proses6_arka_sag_deger' ] >= $proses6_arka_sag_lastik_tipi_min  ) echo "Yeterli"; else echo "Yetersiz"; }?>
<br><b>Stepne Durumu :</b> <?php echo $proses6_stepne_lastik_tipi_adi ?> 
<br> 
<br> 
<br><b>AKÜ ÖLÇÜMÜ</b>
<br><b>Akü Durumu (Kontakt Açık) : </b>
<?php if($ilan_arac_bilgileri[ 'proses7_alternator_durumu' ] == 0 ) echo "Ölçülmedi"; ?> 
<?php if($ilan_arac_bilgileri[ 'proses7_alternator_durumu' ] == 1 ) echo "İyi"; ?> 
<?php if($ilan_arac_bilgileri[ 'proses7_alternator_durumu' ] == 2 ) echo "Kötü"; ?> 
<br><b>Akü Durumu (Kontakt Kapalı) : </b>
<?php if($ilan_arac_bilgileri[ 'proses7_aku_durumu' ] == 0 ) echo "Ölçülmedi"; ?> 
<?php if($ilan_arac_bilgileri[ 'proses7_aku_durumu' ] == 1 ) echo "İyi"; ?> 
<?php if($ilan_arac_bilgileri[ 'proses7_aku_durumu' ] == 2 ) echo "Kötü"; ?> 
<?php if($ilan_arac_bilgileri[ 'proses7_aku_durumu' ] == 3 ) echo "Orta"; ?> 
<br> 
<br> 
<br><b>TRAMER BİLGİLERİ</b>
<?php 
if($ilan_arac_bilgileri[ 'proses3_tramer_kontrolu_yapildi' ] == 0 ){
	echo "<br>Tramer sorgusu yapılmamıştır."; 
}else{
?>
<br><b>Ağır Hasar Sorgusu :</b> 
<?php if($ilan_arac_bilgileri[ 'proses3_agir_hasar_sorgusu' ] == 1 ) echo "Ağır Hasarlı"; ?> 
<?php if($ilan_arac_bilgileri[ 'proses3_agir_hasar_sorgusu' ] == 1 ) echo "Ağır Hasarlı"; ?> 
<?php if($ilan_arac_bilgileri[ 'proses3_agir_hasar_sorgusu' ] == 2 ) echo "Ağır Hasarlı Değil"; ?> 
<br><b>Kilometre Kontrolü* :</b> <?php echo $ilan_arac_bilgileri[ 'proses3_km_kontrolu' ]; ?>
<br><b>Tramer Kontrolü** :</b> 
<?php if($ilan_arac_bilgileri[ 'proses3_tramer_kontrolu' ] == 1 ) echo "Var"; ?> 
<?php if($ilan_arac_bilgileri[ 'proses3_tramer_kontrolu' ] == 2 ) echo "Yok"; ?> 
<br><b>Hasar Bilgileri*** :</b> <?php echo $ilan_arac_bilgileri[ 'proses3_hasar_bilgileri' ]; ?>
<?php } ?>
<br> 
<br> 
<br><b>GÖZLE KONTROL</b>
<br><b>Dış Makyaj Gözle Kontrolü : </b>
<?php echo $ilan_arac_bilgileri[ 'proses5_dis_makyaj_gozle_kontrol' ]; ?>
<?php if($ilan_arac_bilgileri[ 'proses5_dis_makyaj_gozle_kontrol' ] == '' or $ilan_arac_bilgileri[ 'proses5_dis_makyaj_gozle_kontrol' ] == null ) echo "Sorunsuz"; ?> 
<br><b>İç Makyaj Gözle Kontrolü : </b>
<?php echo $ilan_arac_bilgileri[ 'proses5_ic_makyaj_gozle_kontrol' ]; ?>
<?php if($ilan_arac_bilgileri[ 'proses5_ic_makyaj_gozle_kontrol' ] == '' or $ilan_arac_bilgileri[ 'proses5_ic_makyaj_gozle_kontrol' ] == null ) echo "Sorunsuz"; ?> 
<br><b>Elektronik Aksam Gözle Kontrolü :</b> 
<?php echo $ilan_arac_bilgileri[ 'proses5_elektronik_aksam_gozle_kontrol' ]; ?>
<?php if($ilan_arac_bilgileri[ 'proses5_elektronik_aksam_gozle_kontrol' ] == '' or $ilan_arac_bilgileri[ 'proses5_elektronik_aksam_gozle_kontrol' ] == null ) echo "Sorunsuz"; ?> 
<br><b>Mekanik Aksam Gözle Kontrolü : </b>
<?php echo $ilan_arac_bilgileri[ 'proses5_mekanik_aksam_gozle_kontrol' ]; ?>
<?php if($ilan_arac_bilgileri[ 'proses5_mekanik_aksam_gozle_kontrol' ] == '' or $ilan_arac_bilgileri[ 'proses5_mekanik_aksam_gozle_kontrol' ] == null ) echo "Sorunsuz"; ?> 
<br><b>Genel Gözle Kontrol : </b>
<?php echo $ilan_arac_bilgileri[ 'proses5_genel_gozle_kontrol' ]; ?>
<?php if($ilan_arac_bilgileri[ 'proses5_genel_gozle_kontrol' ] == '' or $ilan_arac_bilgileri[ 'proses5_genel_gozle_kontrol' ] == null ) echo "Sorunsuz"; ?> 
<br> 
<?php
if($arac_expertiz_bilgileri[ 'expertiz_yapildi' ] == 0 ){
	echo "<br>Expertiz yapılmamıştır."; 
}else{

$lokal_boyali = "";
$boyali = "";
$degisen = "";
if( $arac_expertiz_bilgileri[ 'sol_on_camurluk_boya_id' ] == 2  ) $lokal_boyali .="Sol Ön Çamurluk,"; 
if( $arac_expertiz_bilgileri[ 'sol_on_camurluk_boya_id' ] == 3  ) $boyali .="Sol Ön Çamurluk,"; 
if( $arac_expertiz_bilgileri[ 'sol_on_camurluk_boya_id' ] == 4  ) $degisen .="Sol Ön Çamurluk,"; 

if( $arac_expertiz_bilgileri[ 'sol_on_kapi_boya_id' ] == 2  ) $lokal_boyali .="Sol Ön Kapı,"; 
if( $arac_expertiz_bilgileri[ 'sol_on_kapi_boya_id' ] == 3  ) $boyali .="Sol Ön Kapı,"; 
if( $arac_expertiz_bilgileri[ 'sol_on_kapi_boya_id' ] == 4  ) $degisen .="Sol Ön Kapı,"; 

if( $arac_expertiz_bilgileri[ 'sol_arka_kapi_boya_id' ] == 2  ) $lokal_boyali .="Sol Arka Kapı,"; 
if( $arac_expertiz_bilgileri[ 'sol_arka_kapi_boya_id' ] == 3  ) $boyali .="Sol Arka Kapı,"; 
if( $arac_expertiz_bilgileri[ 'sol_arka_kapi_boya_id' ] == 4  ) $degisen .="Sol Arka Kapı,"; 

if( $arac_expertiz_bilgileri[ 'sol_arka_camurluk_boya_id' ] == 2  ) $lokal_boyali .="Sol Arka Çamurluk,"; 
if( $arac_expertiz_bilgileri[ 'sol_arka_camurluk_boya_id' ] == 3  ) $boyali .="Sol Arka Çamurluk,"; 
if( $arac_expertiz_bilgileri[ 'sol_arka_camurluk_boya_id' ] == 4  ) $degisen .="Sol Arka Çamurluk,"; 

if( $arac_expertiz_bilgileri[ 'arka_tampon_boya_id' ] == 2  ) $lokal_boyali .="Arka Tampon,"; 
if( $arac_expertiz_bilgileri[ 'arka_tampon_boya_id' ] == 3  ) $boyali .="Arka Tampon,"; 
if( $arac_expertiz_bilgileri[ 'arka_tampon_boya_id' ] == 4  ) $degisen .="Arka Tampon,"; 

if( $arac_expertiz_bilgileri[ 'arka_bagaj_kapisi_boya_id' ] == 2  ) $lokal_boyali .="Arka Bagaj Kapısı,"; 
if( $arac_expertiz_bilgileri[ 'arka_bagaj_kapisi_boya_id' ] == 3  ) $boyali .="Arka Bagaj Kapısı,"; 
if( $arac_expertiz_bilgileri[ 'arka_bagaj_kapisi_boya_id' ] == 4  ) $degisen .="Arka Bagaj Kapısı,"; 

if( $arac_expertiz_bilgileri[ 'sag_arka_camurluk_boya_id' ] == 2  ) $lokal_boyali .="Sağ Arka Çamurluk,"; 
if( $arac_expertiz_bilgileri[ 'sag_arka_camurluk_boya_id' ] == 3  ) $boyali .="Sağ Arka Çamurluk,"; 
if( $arac_expertiz_bilgileri[ 'sag_arka_camurluk_boya_id' ] == 4  ) $degisen .="Sağ Arka Çamurluk,"; 

if( $arac_expertiz_bilgileri[ 'sag_arka_kapi_boya_id' ] == 2  ) $lokal_boyali .="Sağ Arka Kapı,"; 
if( $arac_expertiz_bilgileri[ 'sag_arka_kapi_boya_id' ] == 3  ) $boyali .="Sağ Arka Kapı,"; 
if( $arac_expertiz_bilgileri[ 'sag_arka_kapi_boya_id' ] == 4  ) $degisen .="Sağ Arka Kapı,"; 

if( $arac_expertiz_bilgileri[ 'sag_on_kapi_boya_id' ] == 2  ) $lokal_boyali .="Sağ Ön Kapı,"; 
if( $arac_expertiz_bilgileri[ 'sag_on_kapi_boya_id' ] == 3  ) $boyali .="Sağ Ön Kapı,"; 
if( $arac_expertiz_bilgileri[ 'sag_on_kapi_boya_id' ] == 4  ) $degisen .="Sağ Ön Kapı,"; 

if( $arac_expertiz_bilgileri[ 'sag_on_camurluk_boya_id' ] == 2  ) $lokal_boyali .="Sağ Ön Çamurluk,"; 
if( $arac_expertiz_bilgileri[ 'sag_on_camurluk_boya_id' ] == 3  ) $boyali .="Sağ Ön Çamurluk,"; 
if( $arac_expertiz_bilgileri[ 'sag_on_camurluk_boya_id' ] == 4  ) $degisen .="Sağ Ön Çamurluk,"; 

if( $arac_expertiz_bilgileri[ 'on_tampon_boya_id' ] == 2  ) $lokal_boyali .="Ön Tampon,"; 
if( $arac_expertiz_bilgileri[ 'on_tampon_boya_id' ] == 3  ) $boyali .="Ön Tampon,"; 
if( $arac_expertiz_bilgileri[ 'on_tampon_boya_id' ] == 4  ) $degisen .="Ön Tampon,"; 

if( $arac_expertiz_bilgileri[ 'on_kaput_boya_id' ] == 2  ) $lokal_boyali .="Ön Kaput,"; 
if( $arac_expertiz_bilgileri[ 'on_kaput_boya_id' ] == 3  ) $boyali .="Ön Kaput,"; 
if( $arac_expertiz_bilgileri[ 'on_kaput_boya_id' ] == 4  ) $degisen .="Ön Kaput,"; 

if( $arac_expertiz_bilgileri[ 'tavan_boya_id' ] == 2  ) $lokal_boyali .="Tavan,"; 
if( $arac_expertiz_bilgileri[ 'tavan_boya_id' ] == 3  ) $boyali .="Tavan,"; 
if( $arac_expertiz_bilgileri[ 'tavan_boya_id' ] == 4  ) $degisen .="Tavan,"; 

if( $arac_expertiz_bilgileri[ 'sol_marspiyel_boya_id' ] == 2  ) $lokal_boyali .="Sol Marşpiyel,"; 
if( $arac_expertiz_bilgileri[ 'sol_marspiyel_boya_id' ] == 3  ) $boyali .="Sol Marşpiyel,"; 
if( $arac_expertiz_bilgileri[ 'sol_marspiyel_boya_id' ] == 4  ) $degisen .="Sol Marşpiyel,"; 

if( $arac_expertiz_bilgileri[ 'sag_marspiyel_boya_id' ] == 2  ) $lokal_boyali .="Sağ Marşpiyel,"; 
if( $arac_expertiz_bilgileri[ 'sag_marspiyel_boya_id' ] == 3  ) $boyali .="Sağ Marşpiyel,"; 
if( $arac_expertiz_bilgileri[ 'sag_marspiyel_boya_id' ] == 4  ) $degisen .="Sağ Marşpiyel,"; 
?>
<br><b>EKSPERTİZ BİLGİLERİ</b>
<br><b>Lokal Boyalı Parçalar :</b> <?php echo $lokal_boyali; ?>
<br><b>Boyalı Parçalar :</b> <?php echo $boyali; ?>
<br><b>Değişen Parçalar :</b> <?php echo $degisen; ?>
<br><b>Motor Gücü (HP) :</b> <?php echo $arac_expertiz_bilgileri[ 'motor_hp' ]; ?>
<br><b>Motor Torku (Nm) :</b> <?php echo $arac_expertiz_bilgileri[ 'motor_tork' ]; ?>
<br><b>Teker Gücü (HP) :</b> <?php echo $arac_expertiz_bilgileri[ 'teker_hp' ]; ?>
<br><b>Teker Torku (Nm) :</b> <?php echo $arac_expertiz_bilgileri[ 'teker_tork' ]; ?>
<br><b>Kayıp Güç (HP) :</b> <?php echo $arac_expertiz_bilgileri[ 'kayip_guc_hp' ]; ?>
<br><b>Motor Performansı (%) :</b> <?php echo $arac_expertiz_bilgileri[ 'motor_performans' ]; ?>
<br><b>Fren Ölçümü - Ön Fark (%) :</b> <?php echo $arac_expertiz_bilgileri[ 'fren_on_sapma' ]; ?>
<br><b>Fren Ölçümü - Arka Fark (%) :</b> <?php echo $arac_expertiz_bilgileri[ 'fren_arka_sapma' ]; ?>
<br><b>Yanal Kayma Ölçümü - Ön (m/km) :</b> <?php echo $arac_expertiz_bilgileri[ 'yanal_kayma_on' ]; ?>
<br><b>Yanal Kayma Ölçümü - Arka (m/km) :</b> <?php echo $arac_expertiz_bilgileri[ 'yanal_kayma_arka' ]; ?>
<br><b>Yanal Kayma Sapma (⁰) :</b> <?php echo $arac_expertiz_bilgileri[ 'yanal_kayma' ]; ?>
<br><b>Bağımsız Ekspertiz Notları :</b> <?php echo $arac_expertiz_bilgileri[ 'bagimsiz_expertiz_notlari' ]; ?>
<?php } ?>
<br> 
<br>Hizmet ilkeleri gereği araç yayın süresi boyunca sahibinde kaldığı için araca ait olan tüm bilgiler ilan saati itibariyle geçerlidir. Aracı satın almak isteyen müşterilerimiz satış öncesi yeniden ekspertiz yaptırabilirler. 
<br><b>
		Satın almak istediğiniz araca ait tüm detaylara araç başına gelmeden erişebileceğiniz hizmet paketimizden faydalanabilirsiniz.
	</b>
<br>Araç başındaki tatsız sürprizlerle karşılaşmayacaksınız!
<br>Araca ait bağımsız ekspertiz raporu, detaylı profesyonel fotoğraflar, detaylı test sürüşü vlogu, Tramer bilgileri, devir bilgileri, Lastik diş kalınlığı ölçme ve analizi, akü ölçüm ve analizi, satın alma endeksi gibi detayları ayrıca talep edebilirsiniz.
<br>Satın almaya karar vermeniz durumunda aracın bulunduğu ile ulaşımınızı havaalanı veya otogar tranferleriniz için shuttle servis hizmeti sunuyoruz.
<br>OTOWOW OTOMOTİV - yeni nesil oto galeri
<br>(Bir Türk kuruluşudur.)
<br>Karşıyaka Mah. 4012. Sokak No:4/1 Karaköprü - Şanlıurfa
<br>Telefon & Whatsapp 0 532 202 06 84
<br>Hafta içi ve Cumartesi 09:00 - 19:00 saatleri arasında ulaşabilirsiniz.
<br>Lütfen Araç Numarasını not ediniz! Bilgi almak için bu kodu kullanabilirsiniz : <b><?php echo $ilan_arac_bilgileri[ 'arac_no' ]; ?></b>
</div>