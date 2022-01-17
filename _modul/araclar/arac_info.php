<?php 
include "../../_cekirdek/fonksiyonlar.php";
if( array_key_exists( 'giris_var', $_SESSION ) && $_SESSION[ 'giris_var' ] == 'evet' ) { 
$fn = new Fonksiyonlar();
$vt	= new VeriTabani();
$arac_id = $_REQUEST['arac_id'];

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
WHERE
	a.id = ?
SQL;

$arac = $vt->selectSingle( $SQL_arac_bilgileri, array( $arac_id ) );
$arac_bilgileri = $arac[ 2 ];

if( $arac_bilgileri[ 'arac_durumu' ] == 1 ) $arac_durumu = "Sıfır"; elseif( $arac_bilgileri[ 'arac_durumu' ] == 2 ) $arac_durumu = "2. EL";
if( $arac_bilgileri[ 'plaka_durumu' ] == 1 ) $plaka_durumu = "TR Plaka"; elseif( $arac_bilgileri[ 'plaka_durumu' ] == 2 ) $plaka_durumu = "Yabancıdan Yabancıya"; elseif( $arac_bilgileri[ 'plaka_durumu' ] == 3 ) $plaka_durumu = "Misafir Plaka";
if( $arac_bilgileri[ 'garanti_durumu' ] == 1 ) $garanti_durumu = "VAR"; elseif( $arac_bilgileri[ 'garanti_durumu' ] == 2 ) $garanti_durumu = "YOK";
if( $arac_bilgileri[ 'yedek_anahtar' ] == 1 ) $yedek_anahtar = "VAR"; elseif( $arac_bilgileri[ 'yedek_anahtar' ] == 2 ) $yedek_anahtar = "YOK";
if( $arac_bilgileri[ 'duzenli_servis_bakimi' ] == 1 ) $duzenli_servis_bakimi = "EVET (Düzenli)"; elseif( $arac_bilgileri[ 'duzenli_servis_bakimi' ] == 2 ) $duzenli_servis_bakimi = "HAYIR (Düzenli Değil)";
if( $arac_bilgileri[ 'rehin_durumu' ] == 1 ) $rehin_durumu = "VAR"; elseif( $arac_bilgileri[ 'rehin_durumu' ] == 2 ) $rehin_durumu = "YOK";
if( $arac_bilgileri[ 'trafik_cezasi' ] == 1 ) $trafik_cezasi = "VAR"; elseif( $arac_bilgileri[ 'trafik_cezasi' ] == 2 ) $trafik_cezasi = "YOK";
if( $arac_bilgileri[ 'mtv_borcu' ] == 1 ) $mtv_borcu = "VAR"; elseif( $arac_bilgileri[ 'mtv_borcu' ] == 2 ) $mtv_borcu = "YOK";

$motor_gucu = round($arac_bilgileri[ 'motor_gucu' ]*1.361);

?>
<html>
<head>
<style>
    body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        background-color: gray;
        font: 10pt "Calibri";
		text-align : justify;
    }
	table{
		font: 10pt "Calibri";
	}
	.baslik{
		font-size : 11pt;
	}
    * {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
    }
    .page {
        width: 210mm;
        min-height: 297mm;
        padding: 10mm;
        margin: 10mm auto;
        border: 1px #D3D3D3 solid;
        border-radius: 5px;
        background: white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }
    .subpage {
        padding: 0cm;
        //border: 5px red solid;
        height: 257mm;
        //outline: 2cm #FFEAEA solid;
    }
    
    @page {
        size: A4;
        //margin: 0mm;
    }
    @media print {
        html, body {
            width: 210mm;
            height: 297mm; 
			background: white;			
        }
        .page {
            margin: 0mm;
            border: initial;
            border-radius: initial;
            width: initial;
            min-height: initial;
            box-shadow: initial;
            background: initial;
            page-break-after: always;
        }
    }
	.ruhsat_baslik{
		color : #1267a0;
		font: 6pt "Tohama";
		padding : 0px;
		margin : 0px;
		font-weight : none;
	}
	.ruhsat_deger{
		text-align : center;
		font: 8pt "Calibri";
		font-weight:bold;
		padding : 0px;
		margin : 0px;
	}
	#ruhsat td{
		vertical-align: text-top;
		padding : 0px;
		margin : 0px;
	}
	td .ruhsat_bg{
		background-color : #D9D9D9;
	}
</style>
</head>
<body>
<div class="book">
    <div class="page">
        <div class="subpage">
			<div>
				<table  width="100%" style="border: 0px solid gray;border-collapse: collapse; font: 16pt 'Calibri';">
					<tr>
						<th style="border:solid 0px;background-color:#fbd601;">
							ARAÇ İNFO
						</th>
					</tr>
				</table>
			</div>
			<div>
				<br>
				<table width="100%">
					<tr>
						<td width="48%">
							<img src="../../img/wowlogo.jfif" height="85">
						</td>
						<td width="4%">
						</td>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">ARAÇ BİLGİLERİ</th>
								</tr>
								<tr>
									<td style="border:solid 1px;"  width="40%"><b>Araç No</b></td>
									<td style="border:solid 1px; background-color:#404040; color:white; font-weight:bold; font-size:14pt;"><?php echo $arac_bilgileri[ 'arac_no' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"  width="40%"><b>Kayıt Tarihi</b></td>
									<td style="border:solid 1px;"><?php echo date('d.m.Y H:i',strtotime($arac_bilgileri['kayit_tarihi'])); ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"  width="40%"><b>Kayıt Yapan</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'personel' ]; ?></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<br>
				<table width="100%">
					<tr>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">ARAÇ SAHİBİ BİLGİLERİ</th>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>TC no / Vergi No</b></td>
									<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'sahip_tc_no' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"  width="30%"><b>Soyadı / Unvan</b></td>
									<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'sahip_soyadi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Adı</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'sahip_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Telefon</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'sahip_cep_tel' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>e-Posta</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'sahip_email' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Adres</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'sahip_adres' ]; ?></td>
								</tr>
							</table>
						</td>
						<td width="4%">
						</td>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">VEKİL BİLGİLERİ</th>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>TC no / Vergi No</b></td>
									<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'vekil_tc_no' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"  width="30%"><b>Soyadı / Unvan</b></td>
									<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'vekil_soyadi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Adı</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'vekil_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Telefon</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'vekil_cep_tel' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>e-Posta</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'vekil_email' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Adres</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'vekil_adres' ]; ?></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<br>
				<table width="100%">
					<tr>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">FİYATLAMA</th>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Piyasa Değeri</b></td>
									<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'piyasa_degeri' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"  width="30%"><b>Kasko Değeri</b></td>
									<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'kasko_degeri' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Talep Fiyat</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'talep_fiyat' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>İlan Fiyatı</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'ilan_fiyati' ]; ?></td>
								</tr>
							</table>
						</td>
						<td width="4%">
						</td>
						<td width="48%" style="border:solid 1px;background-color:#fbd601;">
							<center>Fiyatlama Endeksi Gelecek</center>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<br>
				<table width="100%">
					<tr>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">ARAÇ BİLGİLERİ</th>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Araç Durumu</b></td>
									<td style="border:solid 1px;"><?php echo $arac_durumu; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Plaka Durumu</b></td>
									<td style="border:solid 1px;"><?php echo $plaka_durumu; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Araç Tipi</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'arac_tipi_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Kasa Tipi</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'arac_kasa_tipi_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Model Tipi</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'model_tipi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Donanım Paketi</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'donanim_paketi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Vites Tipi</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'arac_vites_tipi_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Kilometre/Saat</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'km' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Garanti</b></td>
									<td style="border:solid 1px;"><?php echo $garanti_durumu; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Çekiş</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'arac_cekis_tipi_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"  width="40%"><b>Yedek Anahtar</b></td>
									<td style="border:solid 1px;"><?php echo $yedek_anahtar; ?></td>
								</tr>
							</table>
						</td>
						<td width="4%">
						</td>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">ARAÇ BİLGİLERİ</th>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Düzenli Servis Bakımı</b></td>
									<td style="border:solid 1px;"><?php echo $duzenli_servis_bakimi; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Marka (D1)</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'arac_marka_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Ticari Adı (D3)</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'ticari_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Silindir Hacmi (P1)</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'silindir_hacmi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Model Yıl (D4)</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'model_yili' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Plaka (A)</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'plaka' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Yakıt Cinsi (P3)</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'arac_yakit_tipi_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Rengi ( R)</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'arac_renk_adi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Tipi (D2)</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'tipi' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Motor Gücü (kW)</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'motor_gucu' ]; ?></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Motor Gücü (HP)</b></td>
									<td style="border:solid 1px;"><?php echo $motor_gucu; ?></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<br>
				<table width="100%" border="0">
					<tr>
						<td width="70%"  valign="top">
							<table style="border:solid 1px;border-collapse: collapse;padding:0;" width="500" id="ruhsat">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="8">Ruhsat Bilgileri</th>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="4" width="50%" class="ruhsat_bg"><div class="ruhsat_baslik">(Y1) VERİLDİĞİ İL İLÇE</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_verildigi_il_ilce_y1' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="4" width="50%" class="ruhsat_bg"><div class="ruhsat_baslik">(Y4) TC KİMLİK NO / VERGİ NO</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'sahip_tc_no' ]; ?></div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(A) PLAKA</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'plaka' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(B9) İLK TESCİL TARİHİ</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_ilk_tescil_tarihi_b9' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="4" class="ruhsat_bg"><div class="ruhsat_baslik">(C11) SOYADI / TİCARİ ÜNVANI</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'sahip_soyadi' ]; ?></div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(Y2) TESCİL SIRA NO</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_tescil_sira_no_y2' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(1) TESCİL TARİHİ</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_tescil_tarihi_1' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="4" class="ruhsat_bg"><div class="ruhsat_baslik">(C12) ADI</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'sahip_adi' ]; ?></div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(D1) MARKASI</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'arac_marka_adi' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(D2) TİPİ</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'tipi' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="4" rowspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(C13) ADRESİ</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'sahip_adres' ]; ?></div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(D13) TİCARİ ADI</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ticari_adi' ]; ?></div></td>
									<td style="border:solid 1px;" class="ruhsat_bg"><div class="ruhsat_baslik">(D4) MODEL</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'model_yili' ]; ?></div></td>
									<td style="border:solid 1px;" class="ruhsat_bg"><div class="ruhsat_baslik">(J) ARAÇ SINIFI</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_arac_sinifi_j' ]; ?></div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2" width="25%" class="ruhsat_bg"><div class="ruhsat_baslik">(D5) CİNSİ</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_cinsi_d5' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2" width="25%" class="ruhsat_bg"><div class="ruhsat_baslik">(R) RENGİ</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'arac_renk_adi' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2" width="25%" rowspan="4"><div class="ruhsat_baslik">(Z1) ARAÇ ÜZERİNDEKİ HAK VE MENFAATİ BULUNANLAR</div><div class="ruhsat_deger">&nbsp;</div></td>
									<td style="border:solid 1px;" colspan="2" width="25%"><div class="ruhsat_baslik">(Z31) NOTER SATIŞ TARİHİ</div><div class="ruhsat_deger">&nbsp;</div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="4" class="ruhsat_bg"><div class="ruhsat_baslik">(P5) MOTOR NO</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_motor_no_p5' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2"><div class="ruhsat_baslik">(Z32) NOTER SATIŞ NO</div><div class="ruhsat_deger">&nbsp;</div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="4" class="ruhsat_bg"><div class="ruhsat_baslik">(E) ŞASE NO</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_sase_no_e' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2" rowspan="2"><div class="ruhsat_baslik">(Z33) NOTERİN ADI</div><div class="ruhsat_deger">&nbsp;</div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2"><div class="ruhsat_baslik">(G1) NET AĞIRLIK</div><div class="ruhsat_deger">&nbsp;</div></td>
									<td style="border:solid 1px;" colspan="2"><div class="ruhsat_baslik">(F1) AZAMİ YÜK AĞIRLIĞI (kg)</div><div class="ruhsat_deger">&nbsp;</div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2"><div class="ruhsat_baslik">(G)KATAR AĞIRLIĞI</div><div class="ruhsat_deger">&nbsp;</div></td>
									<td style="border:solid 1px;" colspan="2"><div class="ruhsat_baslik">(G2) RÖMORK AZAMİ YÜK</div><div class="ruhsat_deger">&nbsp;</div></td>
									<td style="border:solid 1px;" colspan="2" rowspan="3"><div class="ruhsat_baslik">(Z2) DİĞER BİLGİLER</div><div class="ruhsat_deger">&nbsp;</div></td>
									<td style="border:solid 1px;" colspan="2" rowspan="4"><div class="ruhsat_baslik">(Z4) NOTER MÜHÜR İMZA</div><div class="ruhsat_deger">&nbsp;</div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(S1) KOLTUK SAYISI</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_koltuk_sayisi_s1' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2"><div class="ruhsat_baslik">(S2) AYAKTA YOLCU SAYISI</div><div class="ruhsat_deger">&nbsp;</div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(P1) SİLİNDİR HACMİ (cm3)</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'silindir_hacmi' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(P2) MOTOR GÜCÜ (kw)</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'motor_gucu' ]; ?></div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(P3) YAKIT CİNSİ</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'arac_yakit_tipi_adi' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2"><div class="ruhsat_baslik">(Q) GÜÇ AĞIRLIK ORANI </div><div class="ruhsat_deger">&nbsp;</div></td>
									<td style="border:solid 1px;" colspan="2" rowspan="2"><div class="ruhsat_baslik">(Y5) ONAYLAYAN SİCİL</div><div class="ruhsat_deger">&nbsp;</div></td>
								</tr>
								<tr>
									<td style="border:solid 1px;" colspan="2" class="ruhsat_bg"><div class="ruhsat_baslik">(Y3) KULLANIM AMACI</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_kullanim_amaci_y3' ]; ?></div></td>
									<td style="border:solid 1px;" colspan="2"><div class="ruhsat_baslik">(K) TİP ONAY NO</div><div class="ruhsat_deger">&nbsp;</div></td>
									<td style="border:solid 1px;" class="ruhsat_bg"><div class="ruhsat_baslik">BELGE SERİ</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_belge_seri' ]; ?></div></td>
									<td style="border:solid 1px;" class="ruhsat_bg"><div class="ruhsat_baslik">NO</div><div class="ruhsat_deger"><?php echo $arac_bilgileri[ 'ruhsat_no' ]; ?></div></td>
								</tr>
							</table>
						</td>
						<td width="4%">
						</td>
						<td width="26%" valign="top">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;">MUAYENE GEÇERLİLİK TARİHİ</th>
								</tr>
								<tr>
									<td style="border:solid 1px;background-color : #D9D9D9;" align="center"><b><?php echo date('d.m.Y',strtotime($arac_bilgileri['ruhsat_muayene_gecerlilik_tarihi'])); ?></b></td>
								</tr>
							</table>
							<br>
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">Baskılar</th>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Sözleşme</b></td>
									<td style="border:solid 1px;"><b><?php if( $arac_bilgileri[ 'print_hizmet_sozlesmesi' ] == 1 ) echo "&#10003;"; ?></b></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>QR kod</b></td>
									<td style="border:solid 1px;"><b><?php if( $arac_bilgileri[ 'print_qr_kod' ] == 1 ) echo "&#10003;"; ?></b></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Araç Sahibi Kimlik</b></td>
									<td style="border:solid 1px;"><b><?php if( $arac_bilgileri[ 'sahip_kimlik_foto' ] * 1 > 1 ) echo "&#10003;"; ?></b></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Vekil Kimlik</b></td>
									<td style="border:solid 1px;"><b><?php if( $arac_bilgileri[ 'vekil_kimlik_foto' ] * 1 > 1 ) echo "&#10003;"; ?></b></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Ruhsat</b></td>
									<td style="border:solid 1px;"><b><?php if( $arac_bilgileri[ 'ruhsat_foto' ] * 1 > 1 ) echo "&#10003;"; ?></b></td>
								</tr>
								<tr>
									<td style="border:solid 1px;"><b>Araç İnfo</b></td>
									<td style="border:solid 1px;"><b><?php if( $arac_bilgileri[ 'print_arac_info' ] == 1 ) echo "&#10003;"; ?></b></td>
								</tr>
							</table>
							<br>
							<br>
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">SATICI adına</th>
								</tr>
								<tr>
									<td style="border:solid 1px;text-align:center; color:gray;" valign="bottom" height="30">AD SOYAD - TC NO</td>
								</tr>
								<tr>
									<td style="border:solid 1px;text-align:center; color:gray;" valign="bottom" height="80">İMZA</td>
								</tr>
							</table>

						</td>
					</tr>
				</table>
			</div>
		</div>
    </div>
</div>
</body>
</html>

<script>
//window.print();
</script>
<?PHP 
} else { header( 'Location: ../../index.php' );} 
?>