<?php

include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id', $_REQUEST )		? $_REQUEST[ 'personel_id' ]	: 0;


$SQL_ekle = <<< SQL
INSERT INTO
	tb_personel
SET
	 firma_id					= ?
	,bolum_id					= ?
	,tc_no						= ?
	,adi						= ?
	,soyadi						= ?
	,ise_giris_tarihi			= ?
	,isten_cikis_tarihi			= ?
	,sgk_kanun_no_id			= ?
	,ucret						= ?
	,calisma_gunu				= ?
	,hakedis_saati				= ?
	,agi						= ?
	,normal_calisma_tutari		= ?
	,yuzde_50_saati				= ?
	,yuzde_100_saati			= ?
	,ikinci_fazla_mesai_odemesi	= ?
	,mesai_kazanci				= ?
	,toplam_kesinti_saati		= ?
	,toplam_gelmeme_kesintisi	= ?
	,hesaplama_hatasi			= ?
	,bankaya_odenen				= ?
	,bes						= ?
	,avans_toplami				= ?
	,borc_tutari				= ?
	,odeme_tutari				= ?
	,iskur_odemesi				= ?
SQL;

$SQL_guncelle = <<< SQL
UPDATE
	tb_personel
SET
	 firma_id					= ?
	,bolum_id					= ?
	,tc_no						= ?
	,adi						= ?
	,soyadi						= ?
	,ise_giris_tarihi			= ?
	,isten_cikis_tarihi			= ?
	,sgk_kanun_no_id			= ?
	,ucret						= ?
	,calisma_gunu				= ?
	,hakedis_saati				= ?
	,agi						= ?
	,normal_calisma_tutari		= ?
	,yuzde_50_saati				= ?
	,yuzde_100_saati			= ?
	,ikinci_fazla_mesai_odemesi	= ?
	,mesai_kazanci				= ?
	,toplam_kesinti_saati		= ?
	,toplam_gelmeme_kesintisi	= ?
	,hesaplama_hatasi			= ?
	,bankaya_odenen				= ?
	,bes						= ?
	,avans_toplami				= ?
	,borc_tutari				= ?
	,odeme_tutari				= ?
	,iskur_odemesi				= ?
WHERE
	id = ?
SQL;

$SQL_sil = <<< SQL
UPDATE
	tb_personel
SET
	aktif = 0
WHERE
	id = ?
SQL;


$firma_id						= $_REQUEST[ 'firma_id' ];
$bolum_id						= $_REQUEST[ 'bolum_id' ];
$tc_no							= $_REQUEST[ 'tc_no' ];
$adi							= $_REQUEST[ 'adi' ];
$soyadi							= $_REQUEST[ 'soyadi' ];
$ise_giris_tarihi				= $_REQUEST[ 'ise_giris_tarihi' ];
$isten_cikis_tarihi				= $_REQUEST[ 'isten_cikis_tarihi' ];
$sgk_kanun_no_id				= $_REQUEST[ 'sgk_kanun_no_id' ];
$calisma_gunu					= $_REQUEST[ 'calisma_gunu' ];
$ucret							= $_REQUEST[ 'ucret' ][ 0 ]						 . "." . $_REQUEST[ 'ucret' ][ 1 ];
$hakedis_saati					= $_REQUEST[ 'hakedis_saati' ][ 0 ]				 . "." . $_REQUEST[ 'hakedis_saati' ][ 1 ];
$agi							= $_REQUEST[ 'agi' ][ 0 ]						 . "." . $_REQUEST[ 'agi' ][ 1 ];
$normal_calisma_tutari			= $_REQUEST[ 'normal_calisma_tutari' ][ 0 ]		 . "." . $_REQUEST[ 'normal_calisma_tutari' ][ 1 ];
$yuzde_50_saati					= $_REQUEST[ 'yuzde_50_saati' ][ 0 ]			 . "." . $_REQUEST[ 'yuzde_50_saati' ][ 1 ];
$yuzde_100_saati				= $_REQUEST[ 'yuzde_100_saati' ][ 0 ]			 . "." . $_REQUEST[ 'yuzde_100_saati' ][ 1 ];
$ikinci_fazla_mesai_odemesi		= $_REQUEST[ 'ikinci_fazla_mesai_odemesi' ][ 0 ] . "." . $_REQUEST[ 'ikinci_fazla_mesai_odemesi' ][ 1 ];
$mesai_kazanci					= $_REQUEST[ 'mesai_kazanci' ][ 0 ]				 . "." . $_REQUEST[ 'mesai_kazanci' ][ 1 ];
$toplam_kesinti_saati			= $_REQUEST[ 'toplam_kesinti_saati' ][ 0 ]		 . "." . $_REQUEST[ 'toplam_kesinti_saati' ][ 1 ];
$toplam_gelmeme_kesintisi		= $_REQUEST[ 'toplam_gelmeme_kesintisi' ][ 0 ]	 . "." . $_REQUEST[ 'toplam_gelmeme_kesintisi' ][ 1 ];
$hesaplama_hatasi				= $_REQUEST[ 'hesaplama_hatasi' ][ 0 ]			 . "." . $_REQUEST[ 'hesaplama_hatasi' ][ 1 ];
$bankaya_odenen					= $_REQUEST[ 'bankaya_odenen' ][ 0 ]			 . "." . $_REQUEST[ 'bankaya_odenen' ][ 1 ];
$bes							= $_REQUEST[ 'bes' ][ 0 ]						 . "." . $_REQUEST[ 'bes' ][ 1 ];
$avans_toplami					= $_REQUEST[ 'avans_toplami' ][ 0 ]				 . "." . $_REQUEST[ 'avans_toplami' ][ 1 ];
$borc_tutari					= $_REQUEST[ 'borc_tutari' ][ 0 ]				 . "." . $_REQUEST[ 'borc_tutari' ][ 1 ];
$odeme_tutari					= $_REQUEST[ 'odeme_tutari' ][ 0 ]				 . "." . $_REQUEST[ 'odeme_tutari' ][ 1 ];
$iskur_odemesi					= $_REQUEST[ 'iskur_odemesi' ][ 0 ]				 . "." . $_REQUEST[ 'iskur_odemesi' ][ 1 ];
$sonuc = "";

switch( $islem ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, array(
			 $firma_id
			,$bolum_id
			,$tc_no
			,$adi
			,$soyadi
			,$ise_giris_tarihi
			,$isten_cikis_tarihi
			,$sgk_kanun_no_id
			,$ucret
			,$calisma_gunu
			,$hakedis_saati
			,$agi
			,$normal_calisma_tutari
			,$yuzde_50_saati
			,$yuzde_100_saati
			,$ikinci_fazla_mesai_odemesi
			,$mesai_kazanci
			,$toplam_kesinti_saati
			,$toplam_gelmeme_kesintisi
			,$hesaplama_hatasi
			,$bankaya_odenen
			,$bes
			,$avans_toplami
			,$borc_tutari
			,$odeme_tutari
			,$iskur_odemesi
		) );
		$resim_adi		= "resim_yok.jpg";
		$son_eklenen_id	= $sonuc[2]; 
		if( isset( $_FILES[ 'personel_resim' ] ) and $_FILES[ 'personel_resim' ][ 'size' ] > 0 ) {
			$resim_adi	= $son_eklenen_id . "." . pathinfo( $_FILES[ 'personel_resim' ][ 'name' ], PATHINFO_EXTENSION );
			$dizin		= "../../personel_resimler/";
			$hedef_yol	= $dizin.$resim_adi;
			if( move_uploaded_file( $_FILES[ 'personel_resim' ][ 'tmp_name' ], $hedef_yol ) ) {
				$vt->update( 'UPDATE tb_personel SET personel_resim = ? WHERE id = ?', array( $resim_adi, $son_eklenen_id ) );
			}
		}
	break;
	case 'guncelle':
		$sonuc = $vt->update( $SQL_guncelle, array(
			 $firma_id
			,$bolum_id
			,$tc_no
			,$adi
			,$soyadi
			,$ise_giris_tarihi
			,$isten_cikis_tarihi
			,$sgk_kanun_no_id
			,$ucret
			,$calisma_gunu
			,$hakedis_saati
			,$agi
			,$normal_calisma_tutari
			,$yuzde_50_saati
			,$yuzde_100_saati
			,$ikinci_fazla_mesai_odemesi
			,$mesai_kazanci
			,$toplam_kesinti_saati
			,$toplam_gelmeme_kesintisi
			,$hesaplama_hatasi
			,$bankaya_odenen
			,$bes
			,$avans_toplami
			,$borc_tutari
			,$odeme_tutari
			,$iskur_odemesi
			,$personel_id
		) );
		$resim_adi = "resim_yok.jpg";
		if( isset( $_FILES[ 'personel_resim' ] ) and $_FILES[ 'personel_resim' ][ 'size' ] > 0 ) {
			$resim_adi	= $personel_id . "." . pathinfo( $_FILES[ 'personel_resim' ][ 'name' ], PATHINFO_EXTENSION );
			$dizin		= "../../personel_resimler/";
			$hedef_yol	= $dizin.$resim_adi;
			if( move_uploaded_file( $_FILES[ 'personel_resim' ][ 'tmp_name' ], $hedef_yol ) ) {
				$vt->update( 'UPDATE tb_personel SET personel_resim = ? WHERE id = ?', array( $resim_adi, $personel_id ) );
			}
		}
	break;
	case 'sil':
		$sonuc = $vt->delete( $SQL_sil, array( $personel_id ) );
	break;
}
header( "Location:../../index.php?modul=personelEkle&personel_id=$personel_id&islem=ekle" );
?>