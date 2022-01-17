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
	,sube.*
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
LEFT JOIN tb_subeler as sube on a.sube_id = sube.id
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
        font: 8pt "Calibri";
		text-align : justify;
    }
	table{
		font: 8pt "Calibri";
	}
	.baslik{
		font-size : 9pt;
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
							<b>ARACILIK HİZMET SÖZLEŞMESİ</b>
						</td>
					</tr>
				</table>
				<br>
				<br>
			</div>
			<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				İş bu sözleşme aşağıda bilgileri belirtilen aracın alımına a dair alan veya almaya yetkili kişi (bundan sonra ALICI olarak anılacaktır.) ile aracını satan veya satmaya yetkili kişi (bundan sonra SATICI olarak anılacaktır.) ve satıcının iradesiyle satılmakta olan aracı satmak üzere faaliyet gösteren OTOWOW OTOMOTİV (bundan sonra HİZMET VEREN olarak anılacaktır.) arasında 3 nüsha olarak düzenlenmiş olup 17 madden oluşmaktadır.			</div>
			<div style="text-align:right;">
				Sözleşme Tarihi : <?php echo date('d.m.Y'); ?>	
			</div>
			<div>
				<br>
				<table style="border:solid 1px;border-collapse: collapse;" width="100%">
					<tr>
						<th style="border:solid 1px;background-color:#fbd601;" colspan="2">HİZMET VEREN BİLGİLERİ</th>
					</tr>
					<tr>
						<td style="border:solid 1px;"  width="30%"><b>Firma</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'firma' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Unvan</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'unvan' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Vergi No</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'vergi_no' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Vergi Dairesi</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'vergi_dairesi' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Ticaret Sicil No</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'ticaret_sicil_no' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Telefon</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'tel' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Adres</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'adres' ]; ?> </td>
					</tr>
				</table>
			</div>
			<div>
				<br>
				<table style="border:solid 1px;border-collapse: collapse;" width="100%">
					<tr>
						<th style="border:solid 1px;background-color:#fbd601;" colspan="2">ALICI BİLGİLERİ</th>
					</tr>
					<tr>
						<td style="border:solid 1px;"  width="30%"><b>Soyadı / Unvan</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_satis_bilgileri[ 'alici_soyadi' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Adı</b></td>
						<td style="border:solid 1px;"><?php echo $arac_satis_bilgileri[ 'alici_adi' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>TC no / Vergi No</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_satis_bilgileri[ 'alici_tc_no' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Telefon</b></td>
						<td style="border:solid 1px;"><?php echo $arac_satis_bilgileri[ 'alici_cep_tel' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>e-Posta</b></td>
						<td style="border:solid 1px;"><?php echo $arac_satis_bilgileri[ 'alici_email' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Adres</b></td>
						<td style="border:solid 1px;"><?php echo $arac_satis_bilgileri[ 'alici_adres' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Vekil Ad Soyad</b></td>
						<td style="border:solid 1px;"><?php echo $arac_satis_bilgileri[ 'alici_vekil_adi' ]." ".$arac_satis_bilgileri[ 'alici_vekil_soyadi' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Vekil TC No</b></td>
						<td style="border:solid 1px;"><?php echo $arac_satis_bilgileri[ 'alici_vekil_tc_no' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Varsa Vekil Telefon</b></td>
						<td style="border:solid 1px;"><?php echo $arac_satis_bilgileri[ 'alici_vekil_cep_tel' ]; ?></td>
					</tr>
				</table>
			</div>
			<div>
				<br>
				<table style="border:solid 1px;border-collapse: collapse;" width="100%">
					<tr>
						<th style="border:solid 1px;background-color:#fbd601;" colspan="2">SATICI BİLGİLERİ</th>
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
						<td style="border:solid 1px;"><b>TC no / Vergi No</b></td>
						<td style="border:solid 1px;"> <?php echo $arac_bilgileri[ 'sahip_tc_no' ]; ?></td>
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
					<tr>
						<td style="border:solid 1px;"><b>Vekil Ad Soyad</b></td>
						<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'vekil_adi' ]." ".$arac_bilgileri[ 'vekil_soyadi' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Vekil TC No</b></td>
						<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'vekil_tc_no' ]; ?></td>
					</tr>
					<tr>
						<td style="border:solid 1px;"><b>Varsa Vekil Telefon</b></td>
						<td style="border:solid 1px;"><?php echo $arac_bilgileri[ 'vekil_cep_tel' ]; ?></td>
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
				</table>
			</div>
			<br><b class="baslik">1. HİZMET ŞEKLİ</b>							
			<br>a) Bu sözleşme kapsamında söz konusu aracın satışa hazır hale getirilerek resmi olarak devir edilmesine kadar olan sürecte SATICI (Aracın devirden önceki sahibi) ile ALICI alıcı arasında aracılık hizmeti verilmektedir.							
			<br>b) SATICI ile HİZMET VEREN arasında		12.01.2021	tarihinde imzalanan sözleşmeye istinaden HİZMET VEREN tarafından yukarıda bilgileri beliritlen aracın satış yetkisi vermesi ile başlayan sürecin sonunda ALICI 'nın aracı kendi iradesi ile satın almaya karar vermiştir.							
			<br>c) HİZMET VEREN hizmet paketi kapsamında aracılık hizmetlerini gerçekleştirir.							
			<br>d) ALICI ve SATICI arasındaki satış süreci koordinasyonu HİZMET VEREN tarafından gerçekleştirilir.										

		</div>    
    </div>
    <div class="page">
        <div class="subpage">
			<b class="baslik">2. HİZMET PAKETİ	</b>						
			<br>Aracın genel durumu ile ilgili bilgileri paylaşmak.
			<br>Tramer kayıtlarını sunmak.
			<br>Araç devir durumunu kontrol etmek
			<br>İlan tarihi itibariyle yapılan Ekspertiz bilgilerini sunmak
			<br>							
			<br><b class="baslik">3. HİZMET ALANI</b>							
			<br>a) Aracın gözle görünen kısımları dışında mekanik ve detaylı kontrolleri bağımsız ekspertizlerce yaptırılarak bilgi sunulduğundan bilgi geçerliliği söz konusu araca ait ilan tarihi itibariyle geçerlidir.							
			<br>b) Hizmet alanı çerçevesinde yapılan işlemler tamamlandıktan sonra araç satış gerçekleşmesi sürecine kadar SATICI'da kalacak olup HİZMET VEREN bu süreye kadar araç ile ilgili hiçbir mesuliyeti kabul etmez.							
			<br>							
			<br><b class="baslik">4. ARACIN DURUMU</b>							
			<br>a) Araç ilan süresinden satış süresine kadar geçen zamanda SATICI'da beklediğinden ilanlarda belirtilen km ve ekspertiz bilgileri ilan tarihi itibariyle geçerli olup muhtemek değişiklikler HİZMET VEREN sorumluğunda değildir.							
			<br>b) Aracın gözle görünen kısımları dışında mekanik ve detaylı kontrolleri bağımsız ekspertizlerce yaptırılarak bilgi sunulduğundan bilgi geçerliliği söz konusu araca ait ilan tarihi itibariyle geçerlidir.							
			<br>c) ALICI dilediği takdirdirde ücretini kendisi karşılamak suretiyle araç kontrollerini yaptımak üzere bağımsız talep edebilir. Bu durumda da aksi durumda da sorumluluk ALICI'ya aittir.							
			<br>d) Araç durumunda ilanlardan farklı bir netice oluşması durumunda eğer satış gerçekleşmezse ALICI, HİZMET VEREN'den masraf olarak 1.000 TL'den başka ücret talep edemez.							
			<br>e) Araç durumunda ilanlardan farklı bir netice oluşması durumunda eğer satış gerçekleşirse  ALICI, HİZMET VEREN'den herhangi bir ücret talep edemez.							
			<br>f) Satış sonrasında araç durumunda ilanlardan farklı bir netice oluşması durumunda HİZMET VEREN'in herhangi bir sorumluluğu bulunmamakta olup tüm sorumluluk ALICI'ya aittir.							
			<br>							
			<br><b class="baslik">6. HİZMET BEDELİ</b>							
			<br>a) HİZMET VEREN söz konusu araca ilişkin yapılacak fotoğralama, video çekme, ekpertiz yapma, ilan verme işlemleri için SATICI'dan bir bedel talep etmemektedir.							
			<br>b) Hizmet bedeli SATICI'nın talep ettiği araç bedeline eklenerek satışa sunulmaktadır. 							
			<br>c) Hizmet bedeli araç satıldığı takdirde müşterisinden tahsil edilecektir.							
			<br>d) Söz konusu hizmet bedeli 7. maddede araç bedeli üzerinden fiyatlanmıştır.							
			<br>e) Söz konusu bedel bazı durumlarda satış sürecinde müşterisi tarafından SATICI'a ödemek durumunda kalabilir. Bu durumda henüz SATICI bu sözleşmede belirtilen hizmet bedelini satış günü mesai bitimine kadar HİZMET VEREN'e ödemek zorundadır. Aksi takdirde HİZMET VEREN araç satışına tedbir koyabilir, satışı geri çekebilir.							
			<br>							
			<br><b class="baslik">7. ARAÇ SATIŞ BEDELİ</b>							
			<br>a) Araç satış  bedeli 	 <?php echo $fn->sayiFormatiVer($arac_satis_bilgileri[ 'satis_fiyati' ]); ?> 	TL olarak belirlenmiş olup bu bedelin <?php echo $arac_satis_bilgileri[ 'satis_fiyati' ]-$arac_satis_bilgileri[ 'komisyon' ]; ?> TL'si SATICI'ya, kalan	<?php echo $arac_satis_bilgileri[ 'komisyon' ]; ?> 	TL si HİZMET VEREN'e hizmet bedeli olarak ödenecektir.  						
			<br>b)  Doğabilecek diğer tüm gideler ALICI ile SATICI'yı bağlamaktadır.							
			<br>
			<br><b class="baslik">8. SATIŞTAN CAYMA</b>							
			<br>a) Alıştan cayma sadece SATICI'nın kabulü ile yapılabilir. SATICI iadeyi kabul etmemesi durumunda mahkeme yolu açık olacak ve ALICI, HİZMET VEREN'den herhangi bir ücret veya masraf talep etmeyecektir.							
			<br>b) SATICI'ın iadeyi kabul etmesi durumunda ALICI ve SATICI herbiri 1.000,TL HİZMET VEREN'e ödemeyi kabul ve taahhüt eder.							
			<br>
			<br><b class="baslik">9. ÖDEME ŞEKLİ</b>							
			<br>a) Ödeme şekli SATICI'nın belirlediği yöntem ile satış sırasında geçerli olacak olup devir yapıldığı takdirde tahsilat gerçekleşmiş kabul edilecektir.							
			<br>b) SATICI, araç bedelini tahsil ettiğini teyit ettiği anda devir noter devir işlemleri gerçekleşecektir. Şayet SATICI araç bedelini tahisl edip araç devrini vermekten kaçınırsa tahsil ettiği meblağı aynı şekilde iade etmek zorundadır. Aksi durumda Şanlıurfa Mahkemeleri sorumludur.							
			<br>c) Araç bedeli, Noterde hazır bulunan ALICI ve SATICI arasında belirlenen şekilde yapıldıktan sonra resmi devir işlemleri gerçekleşir.							
			<br>							
			<br><b class="baslik">10. ARACIN SATIŞI</b>							
			<br>a) İşbu sözleşme tarafların imzalamasından sonra araç satışı için resmi devir işlemleri yapılabilir.							
			<br>b) ALICI,  aracı mevcut hali ile aldığını tüm sorumluluğu aldığını kabul ve tahhüt eder.							
			<br>c) Sözleşmenin imzalanması ödemenin alındığı anlamına gelmediği gibi ödeme ancak noter devri sırasında taraflara yapılarak gerçekleşebilir.							
			<br>d) Sözleşme imzalandıktan sonra aracın devir edilmesinde engel oluşması durumunda mağdur tarafın maddi zararları, mağdur eden tarafından karşılanacaktır.							
			<br>e) Engellerin SATICI tarafından kaldırılmaması veya yeni engellerin oluşması durumunda satışın gerçekleşmesini engelleyeceğinden müşteri olarak hazır bulunan ALICI'ya ödenmek üzere 1.000,00 TL'ye ek olarak  hizmet bedelinin yarısını HİZMET VEREN'e ödemek zorundadır. 							
			<br>f) SATICI'dan kaynaklı satış engelleri sebebiyle ödenecek olan yukarıdaki maddede belirtilen meblağlar, SATICI'nın hizmet almaya devam etmesi durumunda sadece hizmet bedelinin yarısı olarak tahsil edilen bedel SATICI'ya iade edilir. Önceki ALICI'ya ödenen bedel geri alınamaz. Bu durumda hizmet süreci devam ederek yeni bir ALICI bulunması sağlanır. Yeni ALICI ile satış sürecine girilmesi ile yine satışa engel durumların oluşması durumunda aynı şekilde yeni satıcıya 1.000,00 TL HİZMET VEREN'e ise hizmet bedeli kadar tutarı ödemek zorundadır.							
			<br>g) Satışa çıkan engelin ALICI'dan kaynaklanması durumunda kaparo ödemişse kaparosu geri iade edilmez. Kaparo ödememişse hizmet bedeli olarak 500 TL ödemeyi kabul ve taahüt eder. 							
			<br>							
			<br><b class="baslik">11. ARACIN KULLANIMINDAN DOĞACAK SORUNLAR</b>							
			<br>İşbu sözleşmenin imzalanması ile noter devri gerçekleşmesi arasında geçen zamanda aracın maddi hasara uğraması durumunda mesul taraf mağdur tarafın mağduriyetini karşılamak zorundadır.							
			<br>							
			<br><b class="baslik">12. SORUMLULUKLAR</b>							
			<br>a) Taraflar, işbu sözleşme imzalandıktan sonra aracı satmaktan vazgeçemez, satışı zorlaştıracak engeller çıkartamaz.							
			<br>b) Satış sırasında noter randevusunda ALICI veya VEKİLİ ve SATICI veya VEKİLİ  noterde hazır bulunmak zorundadır.							
			<br>c) SATICI, ödemeyi tamamen almadan satış işlemini gerçekleştirip gerçekleştirmemekte serbesttir.							
			<br>d) Ödeme kısmen yapılıp bir bölümünün başka zaman ödeme hali oluşması durumunda ALICI ve SATICI mütabakatı esastır. Bu hususta HİZMET VEREN 'in herhangi bir sorumluluğu bulunmamaktadır.							
			<br>e) Araç devir işlemi SATICI'nın insiyatifindedir. Satış gerçkeştirdiği anda ödeme ile ilgili yükümlülükleri kabul eder.							

		</div>  
	</div>  
	<div class="page">
		<div class="subpage">
			<br><b class="baslik">13. HİZMET VEREN'in HAK VE YÜKÜMLÜLÜKLERİ</b>							
			<br>a) Otowow ve diğer ilan platformalarının kontrol ve denetimi altındaki kişisel kimlik, adres, iletişim bilgilerinin kaybolmasını, suistimal edilmesini ve değişitirlimesini, engellemek amacıyla makul güvenlik önlemleri almaktadır. Ancak HİZMET VEREN, bu bilgilerin güvenliğini hiçbir şekilde garanti etmez. SATICI'nın, HİMET VEREN'e aktardığı bilgi veriler gizli bilgi şeklinde yorumlanmayacaktır.							
			<br>b) HİZMET VEREN önceden SATICI'ya bildirimde bulunmadan bilgilerin biçim ve içeriğini kısmen veya tamamen değiştirebilir.							
			<br>c) HİZMET VEREN dilediği zamanda ve sebep göstermeksizi, önceden SATICI'ya bilgi vermeksizin hizmet kapsamını veya çeşitliliğini değiştirebileceği gibi sunulan hizmetleri kısmen veya tamamen dondurabilir sona erdirebilir veya tamamen iptal edebilir.							
			<br>
			<br><b class="baslik">14. SATICI'nın HAK VE YÜKÜMLÜLÜKLERİ</b>							
			<br>a) SATICI, sözleşme sırasında kimlik, adres ve iletişim bilgilerinin eksiksiz ve doğru olduğunu, bilgilerinde değişiklik olması halinde derhal HİZMET VEREN'e iletceğini, eksik, güncel olmayan veya yanlış bilgi verilmesi nedeniyle ortaya çıkabilecek her türlü hukuki uyuşmazlık ve zarardan kendisinin sorumlu olacağını kabul ve beyan eder.							
			<br>b) ALICI ve  SATICI, işbu sözleşme hükümlerini hizmetlere ilişkin HİZMET VEREN tarafından açıklanan/açıklanacak her türlü beyanı kabul etmiş olmaktadır.							
			<br>
			<br><b class="baslik">16. SÖZLEŞMENİN SÜRESİ, FESİH VE İPTALİ</b>							
			<br>a) İşbu sözleşme onaylandığı andan itibaren yürürlüğe girer ve aracın resmi olarak el değiştirmesi (satılması) sürecine kadar devam eder.							
			<br>b) Taraflardan herhangi biri gerek görmesi durumunda dilediği zaman işbu sözleşmeyi gerekçeli olarak  yazılı bildirimde bulunarak 1 gün önel vermek kaydıyla tek taraflı olarak feshetmeye yetkildiir. 							
			<br>c) Tek taraflı fesih SATICI tarafından makul gereçekçeli dahi olsa satıştan cayma hükümleri gereğince yükümlülğklerini yerine getirmek durumundadır.							
			<br>d) Fesih durumunda diğer taraf  fesheden taraftan sorumluklukları dışında haksız, yersiz, sebepsiz, mehilsiz, zamansız bir fesihte bulunuduğu, iyi niyete aykırı davranılığı veya sair bir nenen ve bahane öne sürerek hak, alacak, kâr kaybı, zarar ziyan tazminatı veya başkaca bir nam ve ünvan altında herhangi bir ödeme talep edemez. 							
			<br>
			<br><b class="baslik">17. YETKİ VE DELİL ANLAŞMASI</b>							
			<br>a) İşbu sözleşmenin uygulanmasından kaynaklanan sorunların çözümğnde Türk Hukuku uygulanacak ve Şanlıurfa mahkemeleriile İcra Daireleri yetkili olacaktır. 							
			<br>b) SATICI, çıkabilecek ihtilaflarda, HİZMET VEREN'in her türlü belge, kayıt defterleri ile dijital ortamdaki her türlü bilgi yazı ve kayıtların tek, mühasır ve kesin teşkil edeceğini ve bağlayıcı olacağını bu maddenin HMK. 193 maddesi kapsamında bir delil sözlşemesi olduğunu kabul eder.							
			<div>
				<br>
				<br>
				<table width="100%">
					<tr>
						<td width="40%">
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
						<td width="20%">
						</td>
						<td width="40%">
							<table style="border:solid 1px;border-collapse: collapse;" width="100%">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">HİZMET VEREN adına</th>
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
					<tr>
						<td colspan="3">
							<br>
							<table style="border:solid 1px;border-collapse: collapse;" width="40%" align="center">
								<tr>
									<th style="border:solid 1px;background-color:#fbd601;" colspan="2">HİZMET VEREN adına</th>
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
</html>

<script>
//window.print();
</script>
<?PHP 
} else { header( 'Location: ../../index.php' );} 
?>