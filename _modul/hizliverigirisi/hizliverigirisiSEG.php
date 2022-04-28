<?php
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();

$SQL_sil		= "";
$SQL_ekle		= "";
$SQL_guncelle	= "";
$tabloAdi		= $_REQUEST[ 'tabloAdi' ];

// Silinen kayıtları veritabanına yansıt.
if( array_key_exists( 'eklenenKayitlar', $_REQUEST ) ) {
	
}

// Eklenen kayıtları veritabanına yansıt.
if( array_key_exists( 'silinenKayitlar', $_REQUEST ) ) {
	$silinenKayitlar	= $_REQUEST[ 'silinenKayitlar' ];
	$silinen_idler		= implode( ",", $silinenKayitlar );
	$SQL_sil = "
		UPDATE
			$tabloAdi
		SET
			aktif = 0
		WHERE
			id IN($silinen_idler)
	";
	$vt->update( $SQL_sil, array() );
}

// Güncellenen kayıtları veritabanına yansıt.
if( array_key_exists( 'guncellenenKayitlar', $_REQUEST ) ) {

	$guncellenenKayitlar	= $_REQUEST[ 'guncellenenKayitlar' ];
	$param					= array();
	
	foreach( $guncellenenKayitlar as $kayit ) {
		$SQL_guncelle	= "UPDATE $tabloAdi SET ";
		$alanlar		= array();
		$degerler		= array();
		$id				= 0;
		foreach( $kayit as $anahtar => $deger ) {
			if( $anahtar == 'id' ) {
				$id = $deger;
				continue;
			}
			$alanlar[] = $anahtar;
			$degerler[] = $deger;
		}
		$degerler[] = $id;
		
		$SQL_guncelle 	.= implode( ' = ?, ', $alanlar ) . ' = ?';
		$SQL_guncelle	.= " WHERE id = ?";
		$vt->update( $SQL_guncelle, $degerler );
	}
	
}

?>