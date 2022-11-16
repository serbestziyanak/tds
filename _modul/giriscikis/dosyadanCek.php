<?php 
	error_reporting( E_ALL );
	include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();


$dizin =  "../../dosyadanCek";

if( isset( $_FILES[ "file"]["tmp_name"] ) and $_FILES[ "file"][ 'size' ] > 0 ) {
	$firma	= uniqid() ."." . pathinfo( $_FILES[ "file"][ 'name' ], PATHINFO_EXTENSION );
	$hedef_yol		= $dizin.'/'.$firma;
	move_uploaded_file( $_FILES[ "file"][ 'tmp_name' ], $hedef_yol );
}else{
	die("Dosya Yüklenmedi");
}

$SQL_giris_cikis_kaydet = <<< SQL
INSERT INTO
	tb_giris_cikis
SET
	personel_id	= ?,
	tarih		= ?,
	baslangic_saat	= ?
SQL;

//Personel Olup Olmadıgını kontrol etme 
$SQL_personel_oku = <<< SQL
SELECT
	p.id,
	p.grup_id
FROM
	tb_personel AS p
WHERE
	p.firma_id = ? AND 
	p.kayit_no = ?
SQL;

//Çıkış Yapılıp Yapılmadığı Kontrolü
$SQL_personel_gun_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	personel_id 		   = ? AND 
	tarih 				   = ? AND 
	baslangic_saat IS NOT NULL AND 
	bitis_saat IS NULL AND
	aktif = 1
SQL;

//Giriş Çıkış id sine göre listeleme 
$SQL_personel_giris_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	id 		   = ? AND
	aktif 	   = 1
SQL;

$SQL_bitis_saat_guncelle = <<< SQL
UPDATE tb_giris_cikis
SET 
	bitis_saat 	= ?
WHERE
	id 			= ?  
SQL;

echo '<pre>';

$Dosya = fopen( "../../dosyadanCek/$firma", "r" ) or exit( "Dosya Açılamadı !" );

$vt->islemBaslat();

while( !feof( $Dosya ) )
{	
	
  	$satir 		= fgets( $Dosya );
  	$satir_bol 	= explode( ",", $satir );
  	$tarih_bol  = explode( " ", $satir_bol[3] );
  	$tarih 		= str_replace(".","-", $tarih_bol[0]  );
  	$saat 		= $tarih_bol[1];
  	$personel_kayit_numarasi = intval( $satir_bol[1] ); 

  	$time_input = strtotime($tarih); 
	$date_input = getDate($time_input);    

	$tarihAl 	= $date_input["year"]."-".$date_input["mon"];
	$sayi 		= $date_input["mday"];
	
	echo $_SESSION['firma_id'];
	echo $personel_kayit_numarasi;

  	//Gelen kayıt numarasına göre personelli çağırıyoruz
  	$personel_varmi = $vt->select( $SQL_personel_oku, array($_SESSION['firma_id'], $personel_kayit_numarasi ) ) [2];

	print_r($personel_varmi);
	die;

  	//Personel Varsa işlmelere devam ediliyor
  	if ( count( $personel_varmi ) > 0 ){
  		
  		//Personel giriş yapıp cıkış yapmadığını kontrol ediyoruz
  		$girisvarmi  = $vt->select($SQL_personel_gun_cikis, array( $personel_varmi[ 0 ][ 'id' ], $tarih ))[ 2 ];
  		
		print_r($girisvarmi);
		die;

  		if ( count( $girisvarmi ) > 0 ){
  			$update = $vt->update($SQL_bitis_saat_guncelle, array( $saat, $girisvarmi[0][ 'id' ] ));
			print_r($update);
			$hesapla 	= $fn->puantajHesapla(  $personel_varmi[ 0 ][ 'id' ], $tarihAl, $sayi, $personel_varmi[0][ 'grup_id' ] );
			print_r($hesapla);
			/*Hesaplanan Degerleri Veri Tabanına Kaydetme İşlemi*/
			$sonuc = $fn->puantajKaydet( $personel_varmi[ 0 ][ 'id' ], $tarihAl ,$sayi, $hesapla);
			print_r($sonuc);
  		}else{
  			$ekle = $vt->insert( $SQL_giris_cikis_kaydet, array( $personel_varmi[ 0 ][ 'id' ], $tarih, $saat ) );
			print_r($ekle);
		}
  	}else{
		echo 'Personel Yok';
	}
	echo $satir;
	die;

}
 
fclose($Dosya);
$vt->islemBitir();

unlink($dizin.'/'.$firma);


?>