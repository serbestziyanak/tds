<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$islem			= array_key_exists( 'islem', $_REQUEST )			? $_REQUEST[ 'islem' ]			: 'ekle';
$alanlar		= array();
$degerler		= array();

 
$SQL_ekle		= "INSERT INTO tb_giris_cikis_tipi SET ";
$SQL_guncelle 	= "UPDATE tb_giris_cikis_tipi SET ";


/* Alanları ve değerleri ayrı ayrı dizilere at. */
$alanlar[] 		= 'firma_id';
$degerler[] 	= $_SESSION['firma_id'];
foreach( $_REQUEST as $alan => $deger ) {
	if( $alan == 'islem' or  $alan == 'PHPSESSID' ) continue;


		if ($alan == "tip_id" ) {
			$tipIdler[0] = $deger;
			$alanlar[1]	= $alan;
		}else{
			$tipIdler[1] = $deger;
			$alanlar[2]	= $alan;
		}
}

// print_r($tipIdler); //GELEN TİP IDLERİ LİSTELE
// die();

$SQL_ekle		.= implode( ' = ?, ', $alanlar ) . ' = ?';

$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
$SQL_guncelle	.= " WHERE id = ?";

if( $islem == 'guncelle' ) $degerler[] = $id;

$SQL_tum_personel_oku = <<< SQL
SELECT
	p.*
FROM
	tb_personel AS p
WHERE
	firma_id = ? AND p.aktif = 1
SQL;

$SQL_sil = <<< SQL
DELETE FROM
	tb_giris_cikis
WHERE
	id = ?
SQL;

$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id']) );
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];

$___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => 0 );


switch( $islem ) {
	case 'ekle':
		KSORT($alanlar);
		foreach ($tipIdler[0] as $alan => $tipId) {
			echo $tipId;
			$degerler[1] = $tipId; 
			if (!empty($tipIdler[1])) {
				// Kesinti yapılacak diye secenek seçilmiş mi diye kontrol ediliyor
				$degerler[2] = in_array($tipId, $tipIdler[1]) ?  1 : 0; 
			}else{
				$degerler[2] = 0;
			}
			
			KSORT($degerler); // Dizi anahtarına göre sıraladık
			//print_r($alanlar);die();
			$sonuc = $vt->insert( $SQL_ekle, $degerler );
			array_pop($degerler);//Maas Kesintisi Tipini Çıkardık
			array_pop($degerler);//Tip İd sini çıkardık
		}	
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt eklenirken bir hata oluştu ' . $sonuc[ 1 ] );
		else $___islem_sonuc = array( 'hata' => false, 'mesaj' => 'İşlem başarı ile gerçekleşti', 'id' => $sonuc[ 2 ] ); 
	break;
	case 'guncelle':
		$sonuc = $vt->update( $SQL_guncelle, $degerler );
		$resim_adi = "resim_yok.jpg";
		if( isset( $_FILES[ 'input_personel_resim' ] ) and $_FILES[ 'input_personel_resim' ][ 'size' ] > 0 ) {
			$resim_adi	= $personel_id . "." . pathinfo( $_FILES[ 'input_personel_resim' ][ 'name' ], PATHINFO_EXTENSION );
			$dizin		= "../../personel_resimler/";
			$hedef_yol	= $dizin.$resim_adi;
			if( move_uploaded_file( $_FILES[ 'input_personel_resim' ][ 'tmp_name' ], $hedef_yol ) ) {
				$vt->update( 'UPDATE tb_giris_cikis SET resim = ? WHERE id = ?', array( $resim_adi, $personel_id ) );
			}
		}
		if( $sonuc[ 0 ] ) $___islem_sonuc = array( 'hata' => $sonuc[ 0 ], 'mesaj' => 'Kayıt güncellenirken bir hata oluştu ' . $sonuc[ 1 ] );
	break;
	case 'sil':
		$sonuc = $vt->delete( $SQL_sil, array( $giriscikis_id) );
	break;
}
$_SESSION[ 'sonuclar' ] = $___islem_sonuc;
$_SESSION[ 'sonuclar' ][ 'id' ] = $personel_id;
header( "Location:../../index.php?modul=giriscikis");
?>