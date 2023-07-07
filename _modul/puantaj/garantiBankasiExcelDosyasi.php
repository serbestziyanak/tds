<?php

include "../../_cekirdek/fonksiyonlar.php";

/*PhpSpreadsheet kütüphanesini dahil ettik*/
require "../../_cekirdek/vendor/autoload.php";


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

//Personele Ait Listelenecek Hareket Ay
@$listelenecekAy	= array_key_exists( 'tarih'	,$_REQUEST ) ? $_REQUEST[ 'tarih' ]	: date("Y-m");
 
$tarih              = $listelenecekAy;

$tarihBol           = explode("-", $tarih);
$ay                 = intval($tarihBol[1] );
$yil                = $tarihBol[0];

$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
FROM
	tb_personel AS p
WHERE
	firma_id = ? AND p.aktif = 1
SQL;


//belirli bir aya göre personelin giriş çıkış hareketleri
//SELECT *, COUNT(tarih) AS tarihSayisi FROM tb_giris_cikis GROUP BY tarih ORDER BY tarih ASC
$SQL_tum_giris_cikis = <<< SQL
SELECT
	id
	,tarih
	,COUNT(tarih) AS tarihSayisi
	
FROM
	tb_giris_cikis
WHERE
	baslangic_saat  IS NOT NULL AND 
	personel_id 				= ? AND 
	DATE_FORMAT(tarih,'%Y-%m') 	=?  AND 
	aktif 					= 1
GROUP BY tarih
ORDER BY tarih ASC 
SQL;

//Belirli tarihe göre giriş çıkış yapılan saatler 
$SQL_belirli_tarihli_giris_cikis = <<< SQL
SELECT
     baslangic_saat
    ,bitis_saat
    ,maas_kesintisi
	,adi AS islemTipi
FROM
	tb_giris_cikis
LEFT JOIN tb_giris_cikis_tipi ON tb_giris_cikis_tipi.id =  tb_giris_cikis.islem_tipi
LEFT JOIN tb_giris_cikis_tipleri ON tb_giris_cikis_tipleri.id =  tb_giris_cikis_tipi.tip_id
WHERE
	baslangic_saat  IS NOT NULL AND 
	personel_id 	= ? AND 
	tarih 		=? AND 
	aktif 		= 1
ORDER BY baslangic_saat ASC 
SQL;


//FirmanınSectiği Giriş Cıkış Tipleri
$SQL_firma_giris_cikis_tipi = <<< SQL
SELECT
	 tip.id
	,tipler.adi
	,maas_kesintisi
FROM
	tb_giris_cikis_tipi AS tip
INNER JOIN tb_giris_cikis_tipleri AS tipler ON tip.tip_id = tipler.id
WHERE 
	tip.firma_id = ?
ORDER BY tipler.adi ASC
SQL;

//Tüm Giriş Çıkış Tipleri
$SQL_tum_giris_cikis_tipleri = <<< SQL
SELECT
tb_giris_cikis_tipleri.id,
tb_giris_cikis_tipleri.adi,
(SELECT tip_id from tb_giris_cikis_tipi WHERE tb_giris_cikis_tipi.tip_id = tb_giris_cikis_tipleri.id AND firma_id = 2) AS varmi
FROM
	tb_giris_cikis_tipleri
ORDER BY adi ASC
SQL;

//BELİRTİLEN TARİHLER ARASI EN YÜKSEK CARPANLI TARİFE 
$SQL_giris_cikis_saat = <<< SQL
SELECT 
	t1.*
from
	tb_tarifeler AS t1
LEFT JOIN tb_mesai_turu AS mt ON  t1.mesai_turu = mt.id

WHERE 
	t1.baslangic_tarih <= ? AND 
	t1.bitis_tarih >= ? AND
	mt.gunler LIKE ? AND 
	t1.grup_id LIKE ? AND
	t1.aktif = 1
ORDER BY t1.id DESC
LIMIT 1
SQL;

//TARİFEYE AİT SAAT LİSTESİ
$SQL_tarife_saati = <<< SQL
SELECT 
	*
from
	tb_tarife_saati 
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;

//TARİFEYE AİT SAAT LİSTESİ
$SQL_mola_saati = <<< SQL
SELECT 
	*
from
	tb_molalar
WHERE 
	tarife_id = ? AND 
	aktif = 1
ORDER BY baslangic ASC
SQL;

/*AVANS KAZANÇ KESİNTİ TOPLAM TUTARI GETİRME*/
$SQL_toplam_avans_kesinti = <<< SQL
SELECT 
	SUM(tutar) AS toplamTutar
FROM 
	tb_avans_kesinti AS a
INNER JOIN tb_avans_kesinti_tipi AS t ON a.islem_tipi = t.id
WHERE 
	DATE_FORMAT(a.verilis_tarihi,'%Y-%m') 	= ?  AND 
	a.personel_id 						= ? AND
	t.maas_kesintisi 					= ? AND 
	a.aktif 							= 1 
SQL;

/*Donem Kontrolu Kapatılıp Kapatılmadığı kontrol edilecek*/
$SQL_donum_oku = <<< SQL
SELECT 
	tb_donem.*
	tb_kapatilan_carpanlar.eski_id
FROM 
	tb_donem
LEFT JOIN tb_kapatilan_carpanlar ON tb_kapatilan_carpanlar.id = tb_donem.normal_carpan_id
WHERE 
	firma_id 	= ?  AND 
	yil 		= ? AND
	ay 			= ? AND 
	aktif 		= 1 
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

/*Personel Maaş*/
$SQL_personel_maas = <<< SQL
SELECT
	tb_kapatilan_maas.maas
FROM
	tb_giris_cikis
INNER JOIN tb_kapatilan_maas ON tb_kapatilan_maas.id = tb_giris_cikis.maas
WHERE
	personel_id = ? AND DATE_FORMAT(tarih,'%Y-%m') = ?  AND tb_giris_cikis.aktif = 1
GROUP BY tb_giris_cikis.maas
LIMIT 1
SQL;

//TÜM KAPATILAN ÇARPANLARIN LİSTESİ
$SQL_kapatilan_carpan_oku = <<< SQL
SELECT 
	*
FROM 
	tb_kapatilan_carpanlar
WHERE 
	firma_id  	= ? AND
	yil 		= ? AND 
	ay 			= ? 
SQL;

//TÜM ÇARPANLARIN LİSTESİ
$SQL_carpan_oku = <<< SQL
SELECT 
	*
FROM 
	tb_carpanlar
WHERE 
	firma_id = ?
SQL;

$sorgu = "";

/*Kapatılmış Donem Kontrolü ve Çarpanları Ayrı bir Array içinde alip düzenleme işlemi*/
$guncel_carpan_listesi = array();
$donem						= $vt->select($SQL_donum_oku, array($_SESSION["firma_id"], $yil, $ay));
if ($donem[3] > 0) {

	$carpan_listesi			= $vt->select($SQL_kapatilan_carpan_oku, array($_SESSION["firma_id"], $yil, $ay))[2];
	foreach ($carpan_listesi as $carpan) {
		$sorgu .= 'sum(JSON_EXTRACT(calisma, \'$."' . $carpan["eski_id"] . '"\')) AS "' . $carpan["eski_id"] . '",';
	}

	foreach ($carpan_listesi as $key => $carpan) {
		$guncel_carpan_listesi[$key]["id"] 		= $carpan["eski_id"];
		$guncel_carpan_listesi[$key]["adi"] 	= $carpan["adi"];
		$guncel_carpan_listesi[$key]["carpan"] 	= $carpan["carpan"];
	}
	$genel_ayarlar = $donem[2];
	$genel_ayarlar[0]["normal_carpan_id"] = $donem[2][0]["eski_id"];
} else {

	/*Firmaya Ait Kullanılan Çarpan Listelerini Cektik*/
	$carpan_listesi = $vt->select($SQL_carpan_oku, array($_SESSION["firma_id"]))[2];
	foreach ($carpan_listesi as $carpan) {
		$sorgu .= 'sum(JSON_EXTRACT(calisma, \'$."' . $carpan["id"] . '"\')) AS "' . $carpan["id"] . '",';
	}

	foreach ($carpan_listesi as $key => $carpan) {
		$guncel_carpan_listesi[$key]["id"] 		= $carpan["id"];
		$guncel_carpan_listesi[$key]["adi"] 	= $carpan["adi"];
		$guncel_carpan_listesi[$key]["carpan"] 	= $carpan["carpan"];
	}
	$genel_ayarlar 	= $vt->select($SQL_genel_ayarlar, array($_SESSION["firma_id"]))[2];
}


/*Personelin Aylık Puantaj Hesaplaması */
$SQL_puantaj_aylik =
	"SELECT
	p.*,
	g.adi AS grup_adi,
	" . $sorgu . "
	SUM( toplam_kesinti ) AS toplam_kesinti,
	COUNT( IF( tatil = 1, IF( maasa_etki_edilsin = 1 , toplam_kesinti ,  NULL )   , null) ) AS tatil_gun,
	COUNT( IF( tatil = 1, IF( maasa_etki_edilsin = 1 , IF( toplam_kesinti > 0, toplam_kesinti, NULL ) ,  NULL )   , NULL) ) AS tatil_kesinti_sayisi,
	count( IF( tatil = 1, IF( maasa_etki_edilsin = 1 , toplam_kesinti ,  NULL ) , NULL) ) AS tatil_sayisi,
	COUNT( IF( yarim_gun_tatil = 1, yarim_gun_tatil ,  NULL ) ) AS yarim_gun_tatil_sayisi,
	(SELECT 
		tb_kapatilan_maas.maas
	from 
		tb_giris_cikis 
	RIGHT JOIN 
		tb_kapatilan_maas ON tb_kapatilan_maas.id = tb_giris_cikis.maas
	WHERE 
		tb_giris_cikis.personel_id = tb_puantaj.personel_id AND 
		DATE_FORMAT(tb_giris_cikis.tarih,'%Y-%m') = ?
	GROUP BY tb_giris_cikis.personel_id
	ORDER BY tb_giris_cikis.tarih ASC
	LIMIT 1) AS kapali_maas,
	SUM(ucretli_izin) AS ucretli_izin,
	SUM(ucretsiz_izin) AS ucretsiz_izin,
	(
		SELECT 
			SUM(tutar) 
		FROM 
			tb_avans_kesinti AS a
		LEFT JOIN tb_avans_kesinti_tipi AS t ON a.islem_tipi = t.id
		WHERE
			DATE_FORMAT(a.verilis_tarihi,'%Y-%m') 	= ?  AND
			a.personel_id 							= tb_puantaj.personel_id AND
			t.maas_kesintisi 						= 0 AND 
			a.aktif 								= 1 
	) AS kazanc,
	(
		SELECT 
			SUM(tutar)
		FROM 
			tb_avans_kesinti AS a
		LEFT JOIN tb_avans_kesinti_tipi AS t ON a.islem_tipi = t.id
		WHERE 
			DATE_FORMAT(a.verilis_tarihi,'%Y-%m') 	= ?  AND 
			a.personel_id 							= tb_puantaj.personel_id AND
			t.maas_kesintisi 						= 1 AND 
			a.aktif 								= 1 
	) AS kesinti

FROM 
	tb_personel AS p
LEFT JOIN tb_puantaj ON p.id = tb_puantaj.personel_id
LEFT JOIN tb_gruplar  AS g ON g.id = p.grup_id
where 
	p.firma_id = ? AND
	DATE_FORMAT(tb_puantaj.tarih,'%Y-%m') = ? AND
	(p.isten_cikis_tarihi IS NULL OR DATE_FORMAT(p.isten_cikis_tarihi,'%Y-%m') >= ?) 
GROUP BY p.id";


$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id'] ) )[2];
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 0 ][ 'id' ];


$aylik_calisma_saati 		= $genel_ayarlar[0][ "aylik_calisma_saati" ];
$haftalik_calisma_saati 	= $genel_ayarlar[0][ "haftalik_calisma_saati" ];
$giris_cikis_denetimi_grubu = $genel_ayarlar[0][ "giris_cikis_denetimi_grubu" ];
$pazar_kesinti_sayisi 		= $genel_ayarlar[0][ "pazar_kesinti_sayisi" ];
$puantaj_hesaplama_grubu 	= $genel_ayarlar[0][ "puantaj_hesaplama_grubu" ];
$beyaz_yakali_personel 		= $genel_ayarlar[0][ "beyaz_yakali_personel" ];
$giris_cikis_liste_goster 	= $genel_ayarlar[0][ "giris_cikis_liste_goster" ];
$giris_cikis_tutanak_kaydet = $genel_ayarlar[0][ "giris_cikis_tutanak_kaydet" ];
$tutanak_olustur 			= $genel_ayarlar[0][ "tutanak_olustur" ];
$normal_carpan_id 			= $genel_ayarlar[0][ "normal_carpan_id" ];
$tatil_mesai_carpan_id 		= $genel_ayarlar[0][ "tatil_mesai_carpan_id" ];
$gunluk_calisma_suresi 		= $genel_ayarlar[0][ "gunluk_calisma_suresi" ];
$yarim_gun_tatil_suresi 	= $genel_ayarlar[0][ "yarim_gun_tatil_suresi" ];

$ay  	 	= explode("-", $tarih);
$yil       	= $ay[ 0 ];
$ay 		= $ay[ 1 ];
$gunSayisi 	= date("t",mktime(0,0,0,$ay,01,$yil));	

$sayi =13;
$personelPuantaji = $vt->select( $SQL_puantaj_aylik, array( $tarih, $tarih, $tarih, $_SESSION[ 'firma_id' ], $tarih, $tarih ) )[ 2 ];

// Excel dosyasını yükle
$excel = IOFactory::load('garantiBankasiExcelDosyasi.xlsx');

// Aktif çalışma sayfasını seçs
$sheet = $excel->getActiveSheet();

foreach ( $personelPuantaji as $puantaj ) {
	
	$gunSayisi = $fn->gunSayisi($puantaj[ "isten_cikis_tarihi" ], $listelenecekAy, $yil, $ay);

    $toplamKazanc = 0;
	$mesaiKazanci = 0;

	$ucret = $puantaj["kapali_maas"] == "" ? $puantaj["ucret"] : $puantaj["kapali_maas"];

	/*Kesintiler*/
	/*Kesinti Saati Toplamları Maaşı Hesaplanacak Alınacak*/
	$kesintiTutar 					= ($ucret / $aylik_calisma_saati / 60) * 1 * $puantaj["toplam_kesinti"];

	/*Tatil Gunleri Toplam Dakika*/
	$tatilGunleriToplamDakika 		= ($puantaj["tatil_gun"] - $puantaj["tatil_kesinti_sayisi"]) * $gunluk_calisma_suresi;

	/*Yarım Gün Tatil Süresi Toplamı*/
	$yarimGunTatilSuresi			= $puantaj["yarim_gun_tatil_sayisi"]	* $yarim_gun_tatil_suresi;

	/*NORMAL HAKEDİŞ  Maaş - Kesinti */
	
	if ( $gunSayisi >=30 ){

		$normalCalismaSuresi 			= (($aylik_calisma_saati * 60) - $tatilGunleriToplamDakika - $puantaj["toplam_kesinti"] - $yarimGunTatilSuresi);
		$puantaj[$normal_carpan_id] 	= $normalCalismaSuresi;
		
		/*Normal Maaşın Hesaplanması*/
		$normalMaas 					=  $ucret - $kesintiTutar;

	}else{
		$normalMaas  = ( ( $ucret / $aylik_calisma_saati ) / 60 ) * $puantaj[$normal_carpan_id];

	}

	/*Tatil Kesinti Hesaplaması*/
	$tatilKesinti					= $puantaj["tatil_kesinti_sayisi"] * $gunluk_calisma_suresi;

    foreach ($guncel_carpan_listesi as $carpan) {

		/* -- Maaş Hesaplasması == ( personelin aylık ucreti / 225 / 60 ) * carpan --*/
		if ($puantaj[$carpan["id"]] > 0 or $beyaz_yakali_personel != $puantaj["grup_id"]) {
			if ($carpan["id"] != $normal_carpan_id) {
				$kazanc 		 = ($ucret / $aylik_calisma_saati / 60) * $carpan["carpan"] * $puantaj[$carpan["id"]];
				$mesaiKazanci  	+= $kazanc;
			} 
		}
	}

    if ( $beyaz_yakali_personel == $puantaj[ "grup_id" ] ){
        $tutar 			= $fn->parabirimi( ( ( $ucret / 30 ) * $gunSayisi )+ $puantaj["kazanc"] - $puantaj["kesinti"]);
    }else{
		$tatilUcreti 	= ($ucret / $aylik_calisma_saati / 60) * ( ( $puantaj["tatil_gun"] - $puantaj["tatil_kesinti_sayisi"] ) * $gunluk_calisma_suresi );
        $tutar 			= $fn->parabirimi( $normalMaas + $mesaiKazanci + $puantaj[ "kazanc" ] - $puantaj[ "kesinti" ] + $tatilUcreti ); 
    }

    // isim, tckmno, Banka kodu, Sube Kodu, Hesap, IBAN, Tutar, isim, isim
    // Yeni verileri ekle
    $sheet->setCellValue("A$sayi", $puantaj["adi"].' '.$puantaj["soyadi"]);
    $sheet->setCellValue("B$sayi", $puantaj["tc_no"]);
    $sheet->setCellValue("C$sayi", '');
    $sheet->setCellValue("D$sayi", $puantaj["banka_sube"]);
    $sheet->setCellValue("E$sayi", $puantaj["banka_hesap_no"]);
    $sheet->setCellValue("F$sayi", str_replace(" ", "",$puantaj["iban"]));
    $sheet->setCellValue("G$sayi", $tutar);
    $sheet->setCellValue("H$sayi", $puantaj["adi"].' '.$puantaj["soyadi"]);
    $sheet->setCellValue("I$sayi", $puantaj["adi"].' '.$puantaj["soyadi"]);

    // Hücreye kenarlık ekleme
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '000000'],
            ],
        ],
    ];
    $sheet->getStyle("A$sayi")->applyFromArray($styleArray);
    $sheet->getStyle("B$sayi")->applyFromArray($styleArray);
    $sheet->getStyle("C$sayi")->applyFromArray($styleArray);
    $sheet->getStyle("D$sayi")->applyFromArray($styleArray);
    $sheet->getStyle("E$sayi")->applyFromArray($styleArray);
    $sheet->getStyle("F$sayi")->applyFromArray($styleArray);
    $sheet->getStyle("G$sayi")->applyFromArray($styleArray);
    $sheet->getStyle("H$sayi")->applyFromArray($styleArray);
    $sheet->getStyle("I$sayi")->applyFromArray($styleArray);

    /*Yazı Rengi Ayarlama İşlemi*/
    $sheet->getStyle("D$sayi")->getFont()->setColor(new Color('3d9a66'));
    $sayi++;
}

$dosya_adi = $fn->ayAdiVer(intval(date("m")),2)."-".date("Y");
// Excel dosyasını kaydet
$writer = IOFactory::createWriter($excel, 'Xlsx');
$writer->save("puantajlar/$dosya_adi.xlsx");

header("Content-Type: application/xlsx");
header("Content-Disposition: attachment; filename='$dosya_adi.xlsx'");

header("Location: ./puantajlar/$dosya_adi.xlsx");

?>