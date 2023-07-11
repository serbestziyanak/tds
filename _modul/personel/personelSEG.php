<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id', $_REQUEST )		? $_REQUEST[ 'personel_id' ]	: 0;
$alanlar		= array();
$degerler		= array();
$yetiKontrol = $fn->yetkiKontrol( $_SESSION[ "kullanici_id" ], "personel", $islem );

if ( $yetiKontrol == 0 ) {
	include '../../yetki_yok_sayfasi/sayfaya_yetkiniz_yok.php';
	die();
}

 
$SQL_ekle		= "INSERT INTO tb_personel SET ";
$SQL_guncelle 	= "UPDATE tb_personel SET ";


/* Alanları ve değerleri ayrı ayrı dizilere at. */
$aktif_tab = $_REQUEST["aktif_tab"];

$alanlar[]		= 'firma_id';
$degerler[]		= $_SESSION['firma_id'];
foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or $alan == 'personel_id' or  $alan == 'PHPSESSID' or $alan == 'aktif_tab' ) continue;

	$tarih_alani = explode( '-', $alan );
	if( $tarih_alani[ 0 ] == 'tarihalani' ) {
		$alan 	= $tarih_alani[ 1 ];
		$alan = $tarih_alani[ 1 ];
		if( $deger == '' ) $deger = NULL;
		else $deger	= date( 'Y-m-d', strtotime( $deger ) );
	}
	$alanlar[]		= $alan;
	$degerler[]		= $deger;
}

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

if( $islem == 'guncelle' ) $degerler[] = $personel_id;

$SQL_sil = <<< SQL
UPDATE
	tb_personel
SET
	aktif = 0
WHERE
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );
$vt->islemBaslat();
switch( $islem ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, $degerler );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 

		$resim_adi		= "resim_yok.jpg";
		$son_eklenen_id	= $sonuc[ 2 ]; 
		if( isset( $_FILES[ 'input_personel_resim' ] ) and $_FILES[ 'input_personel_resim' ][ 'size' ] > 0 ) {
			$resim_adi	= $son_eklenen_id . "." . pathinfo( $_FILES[ 'input_personel_resim' ][ 'name' ], PATHINFO_EXTENSION );
			$dizin		= "../../personel_resimler/";
			$hedef_yol	= $dizin.$resim_adi;
			if( move_uploaded_file( $_FILES[ 'input_personel_resim' ][ 'tmp_name' ], $hedef_yol ) ) {
				$vt->update( 'UPDATE tb_personel SET resim = ? WHERE id = ?', array( $resim_adi, $son_eklenen_id ) );
			}
		}
		$personel_id = $son_eklenen_id;
	break;
	case 'guncelle':
		$sonuc = $vt->update( $SQL_guncelle, $degerler );
		$resim_adi = "resim_yok.jpg";
		if( isset( $_FILES[ 'input_personel_resim' ] ) and $_FILES[ 'input_personel_resim' ][ 'size' ] > 0 ) {
			$resim_adi	= $personel_id . "." . pathinfo( $_FILES[ 'input_personel_resim' ][ 'name' ], PATHINFO_EXTENSION );
			$dizin		= "../../personel_resimler/";
			$hedef_yol	= $dizin.$resim_adi;
			if( move_uploaded_file( $_FILES[ 'input_personel_resim' ][ 'tmp_name' ], $hedef_yol ) ) {
				$vt->update( 'UPDATE tb_personel SET resim = ? WHERE id = ?', array( $resim_adi, $personel_id ) );
			}
		}
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
	case 'sil':
		$sonuc = $vt->delete( $SQL_sil, array( $personel_id ) );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt silinrken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
}
$vt->islemBitir();
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $personel_id;
header( "Location:../../index.php?modul=personel&islem=guncelle&personel_id=".$personel_id."&aktif_tab=".$aktif_tab );
?>