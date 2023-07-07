<?php 
	
include "../../_cekirdek/fonksiyonlar.php";
$vt		= new VeriTabani();
$fn		= new Fonksiyonlar();
error_reporting( 0 );

$SQL_giris_cikis_kaydet = <<< SQL
INSERT INTO
	tb_giris_cikis
SET
	personel_id		= ?,
	grup_id         = ?,
    tarih			= ?,
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
	p.firma_id 	= ? AND 
	p.kayit_no 	= ? AND
	p.aktif 	= 1
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

/*Genel Ayarlar*/
$SQL_genel_ayarlar = <<< SQL
SELECT 
	*
FROM 
	tb_genel_ayarlar
WHERE 
	firma_id 	= ?
SQL;

$SQL_kontrol = <<< SQL
SELECT
	 k.*
	,CASE k.super WHEN 1 THEN "Süper" ELSE r.adi END AS rol_adi
FROM
	tb_sistem_kullanici AS k
JOIN
	tb_roller AS r ON k.rol_id = r.id
WHERE
	k.email = ? AND
	k.sifre = ?
LIMIT 1
SQL;

/*
Gelen kullanıcı adı ve şifre kontrol edilecek
Yetki varsa işlem yapılmaya devam edilecek
*/

if ( !array_key_exists("kullanici", $_POST)){
    echo "error";
    die;
}else{

    $kullanici_bilgileri = explode( "\n", $_POST["kullanici"] );
    $sorguSonuc = $vt->selectSingle( $SQL_kontrol, array( $kullanici_bilgileri[0], md5( $kullanici_bilgileri[1] ) ) );

    if( !$sorguSonuc[ 0 ] ) {
        $kullaniciBilgileri	= $sorguSonuc[ 2 ];
        if( $kullaniciBilgileri[ 'id' ] * 1 < 1 ) {
            echo "error";
            die;
        }
    } else {
        echo "error";
        die;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $genel_ayarlar				= $vt->select( $SQL_genel_ayarlar, array( $_SESSION["firma_id"] ) )[ 2 ];

    $tatil_mesai_carpan_id 		= $genel_ayarlar[ 0 ][ "tatil_mesai_carpan_id" ];
    $normal_carpan_id 			= $genel_ayarlar[ 0 ][ "normal_carpan_id" ];


    $_SESSION[ "bosOlanKayitNumalarari" ] = array();

    $Dosya      = explode("\n", $_REQUEST["icerik"] ); 

    $firma_id   = $_POST["firma_id"];

    foreach($Dosya as $satir)
    {
        $satir_bol 	    = explode( ",", $satir );
        $dizi_test[]    = $satir_bol;
    }

    $vt->islemBaslat();
    foreach($dizi_test as $alt_dizi){
        $bosTemizle = array_filter($alt_dizi);

        if( count( $bosTemizle ) > 0 ){

            $tarih_bol  = explode( " ", $alt_dizi[3] );
            $tarih 		= str_replace(".","-", $tarih_bol[0]  );
            $saat 		= $tarih_bol[1];
            $personel_kayit_numarasi = intval( $alt_dizi[1] ); 

            $time_input = strtotime($tarih); 
            $date_input = getDate($time_input);    

            $tarihAl 	= $date_input["year"]."-".$date_input["mon"];
            $sayi 		= $date_input["mday"];

            //Gelen kayıt numarasına göre personelli çağırıyoruz
            $personel_varmi = $vt->select( $SQL_personel_oku, array($firma_id, $personel_kayit_numarasi ) ) [2];

            //Personel Varsa işlmelere devam ediliyor
            if ( count( $personel_varmi ) > 0 ){
                
                /*Personel giriş yapıp cıkış yapmadığını kontrol ediyoruz*/
                $girisvarmi  = $vt->select($SQL_personel_gun_cikis, array( $personel_varmi[ 0 ][ 'id' ], $tarih ))[ 2 ];
                
                if ( count( $girisvarmi ) > 0 ){
                    /*Giriş varsa saati çıkış saatini güncelliyoruz */
                    $update = $vt->update($SQL_bitis_saat_guncelle, array( $saat, $girisvarmi[0][ 'id' ] ));
                    $hesapla 	= $fn->puantajHesapla(  $personel_varmi[ 0 ][ 'id' ], $tarihAl, $sayi, $personel_varmi[0][ 'grup_id' ], array(), $tatil_mesai_carpan_id, $normal_carpan_id );
                    
                    /*Hesaplanan Degerleri Veri Tabanına Kaydetme İşlemi*/
                    $sonuc = $fn->puantajKaydet( $personel_varmi[ 0 ][ 'id' ], $tarihAl ,$sayi, $hesapla);
                }else{
                    $ekle = $vt->insert( $SQL_giris_cikis_kaydet, array( $personel_varmi[ 0 ][ 'id' ], $personel_varmi[ 0 ][ 'grup_id' ], $tarih, $saat ) );
                }
            }else{
                $sonuc = "error"; 
                echo $sonuc;
                if( !in_array( $personel_kayit_numarasi, $_SESSION[ "bosOlanKayitNumalarari" ] ) ){
                    $_SESSION[ "bosOlanKayitNumalarari" ][ $personel_kayit_numarasi ] = $personel_kayit_numarasi;
                }
                die;
            }
        }
    }

    $vt->islemKontrol();

    if( count( $_SESSION[ "bosOlanKayitNumalarari" ] ) > 0 ){
        $sonuc = "error";
    }else{
        $sonuc  = "success";
        $dizin  =  "../../firmaDosyalari/firmaGirisCikisDosyasi/firma_$firma_id.txt";
        $myfile = fopen($dizin, "a") or die("Unable to open file!");
        fwrite($myfile, $_POST["icerik"]);
        fclose($myfile);
    }

    echo $sonuc ;
}
?>
