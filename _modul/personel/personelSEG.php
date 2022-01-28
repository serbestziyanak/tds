<?php

include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

echo "<pre>";
print_r( $_REQUEST );
exit;

/*
<?php
$a = "0(542) 220-5037";
$ara = array( ')', '(', ' ', '-');
$degistir = array('');

str_replace( $ara, $degistir, $a);
?>
*/

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id', $_REQUEST )		? $_REQUEST[ 'personel_id' ]	: 0;
$alanlar		= array();
$degerler		= array();


$SQL_ekle		= "INSERT INTO tb_personel SET ";
$SQL_guncelle 	= "UPDATE tb_personel SET ";


/* Alanları ve değerleri ayrı ayrı dizilere at. */
foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or $alan == 'personel_id' ) continue;
	$alanlar[]		= $alan;
	$degerler[]		= $deger;
}

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";


$SQL_sil = <<< SQL
UPDATE
	tb_personel
SET
	aktif = 0
WHERE
	id = ?
SQL;

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti' );

switch( $islem ) {
	case 'ekle':
		$sonuc = $vt->insert( $SQL_ekle, $degerler );
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );

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
	break;
	case 'guncelle':
		$sonuc = $vt->update( $SQL_guncelle, array() );
		$resim_adi = "resim_yok.jpg";
		if( isset( $_FILES[ 'input_personel_resim' ] ) and $_FILES[ 'input_personel_resim' ][ 'size' ] > 0 ) {
			$resim_adi	= $personel_id . "." . pathinfo( $_FILES[ 'input_personel_resim' ][ 'name' ], PATHINFO_EXTENSION );
			$dizin		= "../../personel_resimler/";
			$hedef_yol	= $dizin.$resim_adi;
			if( move_uploaded_file( $_FILES[ 'input_personel_resim' ][ 'tmp_name' ], $hedef_yol ) ) {
				$vt->update( 'UPDATE tb_personel SET resim = ? WHERE id = ?', array( $resim_adi, $personel_id ) );
			}
		}
	break;
	case 'sil':
		$sonuc = $vt->delete( $SQL_sil, array( $personel_id ) );
	break;
}

$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
header( "Location:../../index.php?modul=personel" );
?>