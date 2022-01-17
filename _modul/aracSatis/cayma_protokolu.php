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
WHERE
	a.id = ?
SQL;

$SQL_arac_satis_bilgileri = <<< SQL
SELECT
	*
FROM
	tb_arac_satislari
WHERE
	arac_id = ?
SQL;


$arac		 			= $vt->selectSingle( $SQL_arac_bilgileri, array( $arac_id ) );
$arac_satis	 			= $vt->selectSingle( $SQL_arac_satis_bilgileri, array( $arac_id ) );
$arac_bilgileri 		= $arac[ 2 ];
$arac_satis_bilgileri 	= $arac_satis[ 2 ];

if( $arac_bilgileri[ 'arac_durumu' ] == 1 ) $arac_durumu = "Sıfır"; elseif( $arac_bilgileri[ 'arac_durumu' ] == 2 ) $arac_durumu = "2. EL";
if( $arac_bilgileri[ 'plaka_durumu' ] == 1 ) $plaka_durumu = "TR Plaka"; elseif( $arac_bilgileri[ 'plaka_durumu' ] == 2 ) $plaka_durumu = "Yabancıdan Yabancıya"; elseif( $arac_bilgileri[ 'plaka_durumu' ] == 3 ) $plaka_durumu = "Misafir Plaka";
if( $arac_bilgileri[ 'garanti_durumu' ] == 1 ) $garanti_durumu = "VAR"; elseif( $arac_bilgileri[ 'garanti_durumu' ] == 2 ) $garanti_durumu = "YOK";
if( $arac_bilgileri[ 'yedek_anahtar' ] == 1 ) $yedek_anahtar = "VAR"; elseif( $arac_bilgileri[ 'yedek_anahtar' ] == 2 ) $yedek_anahtar = "YOK";
if( $arac_bilgileri[ 'duzenli_servis_bakimi' ] == 1 ) $duzenli_servis_bakimi = "EVET (Düzenli)"; elseif( $arac_bilgileri[ 'duzenli_servis_bakimi' ] == 2 ) $duzenli_servis_bakimi = "HAYIR (Düzenli Değil)";
if( $arac_bilgileri[ 'rehin_durumu' ] == 1 ) $rehin_durumu = "VAR"; elseif( $arac_bilgileri[ 'rehin_durumu' ] == 2 ) $rehin_durumu = "YOK";
if( $arac_bilgileri[ 'trafik_cezasi' ] == 1 ) $trafik_cezasi = "VAR"; elseif( $arac_bilgileri[ 'trafik_cezasi' ] == 2 ) $trafik_cezasi = "YOK";
if( $arac_bilgileri[ 'mtv_borcu' ] == 1 ) $mtv_borcu = "VAR"; elseif( $arac_bilgileri[ 'mtv_borcu' ] == 2 ) $mtv_borcu = "YOK";

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
</style>
</head>
<body>
<div class="book">
    <div class="page">
        <div class="subpage">
			<div>
				<table  width="100%" style="border: 0px solid gray; font: 16pt 'Calibri';">
					<tr>
						<td width="10%">
							<img src="../../img/wowlogo.jfif" height="100">
						</td>
						<td style="text-align:center;">
							<b>SATIŞTAN CAYMA PROTOKOLÜ</b>
						</td>
					</tr>
				</table>
				<br>
				<br>
			</div>
			<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Aşağıda bilgileri beliritlen aracımın satışından kendi isteğim ve iradem ile vazgeçiyorum. Aracımın satışının bugün gün bitimine kadar yayınlardan kaldırılmasını kabul ediyorum.										
			</div>
			<div style="text-align:right;">
				Tarihi : <?php echo date('d.m.Y'); ?>	
			</div>
			<div>
				<br>
				<br>
				<table width="100%">
					<tr>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">ARAÇ BİLGİLERİ</th>
								</tr>
								<tr>
									<td style="border:solid 1px;"  width="40%"><b>Araç No</b></td>
									<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'arac_no' ]; ?></td>
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
									<td style="border:solid 1px;"  width="40%"><b>Yedek Anahtar</b></td>
									<td style="border:solid 1px;"><?php echo $yedek_anahtar; ?></td>
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
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<br>
							<br>
							<br>
						</td>
					</tr>
					<tr>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" >CAYMA SEBEBİ</th>
								</tr>
								<tr>
									<td style="border:solid 1px;" height="50"><?php echo $arac_satis_bilgileri[ 'cayma_sebebi' ]; ?></td>
								</tr>
							</table>
						</td>
						<td width="4%">
						</td>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" >ALINAN CAYMA BEDELİ</th>
								</tr>
								<tr>
									<td style="border:solid 1px;font-size:18pt;" height="50" align="center"><?php echo $arac_satis_bilgileri[ 'alinan_cayma_bedeli' ]; ?>&#8378;</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div>
				<br>
				<br>
				<table width="100%">
					<tr>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">OTOWOW adına</th>
								</tr>
								<tr>
									<td style="border:solid 1px;text-align:center; color:gray;" valign="bottom" height="50">AD SOYAD - TC NO</td>
								</tr>
								<tr>
									<td style="border:solid 1px;text-align:center; color:gray;" valign="bottom" height="100">İMZA</td>
								</tr>
							</table>
						</td>
						<td width="4%">
						</td>
						<td width="48%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">SATICI adına</th>
								</tr>
								<tr>
									<td style="border:solid 1px;text-align:center; color:gray;" valign="bottom" height="50">AD SOYAD - TC NO</td>
								</tr>
								<tr>
									<td style="border:solid 1px;text-align:center; color:gray;" valign="bottom" height="100">İMZA</td>
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
<br>
<br>
</html>

<script>
//window.print();
</script>
<?PHP 
} else { header( 'Location: ../../index.php' );} 
?>