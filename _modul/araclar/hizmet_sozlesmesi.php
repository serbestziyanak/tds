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
				İş bu sözleşme aşağıda bilgileri belirtilen aracın satışına dair araç sahibi veya satmaya yetkili kişi (bundan sonra SATICI olarak anılacaktır.) ile OTOWOW OTOMOTİV (bundan sonra HİZMET VEREN olarak anılacaktır.) arasında 2 nüsha olarak düzenlenmiş olup 17 madden oluşmaktadır.							
			</div>
			<div style="text-align:right;">
				Sözleşme Tarihi : <?php echo date('d.m.Y'); ?>	
			</div>
			<div>
				<br>
				<br>
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
			<div>
				<br><b class="baslik">1. HİZMET ŞEKLİ</b>							
				<br>a) Söz konusu aracın SATICI'nın iradesi ile HİZMET VEREN'in aracı satmaya hazır hale getirmesi sürecine kadar olan hizmetlerin yürütülmesi işi kapsamında "hizmet paketi" maddesinde belirtilen işlemlerinden oluşmaktadır. 							
				<br>SATICI aracını satma iradesi ile HİZMET VEREN'e sunar. 							
				<br>HİZMET VEREN söz konusu araca ait hizmet paketi kapsamında aktivasyon gerçekleştirir.							
				<br>HİZMET VEREN araca ait bilgi ve içeriklere sahip olduktan sonra aynı gün içerisinde aracı SATICI'ya teslim eder.							
				<br>Araca ait bilgi ve görüntüler çeşitli internet platformlarında yayınlanır.							
				<br>Araç satış süreci boyunca SATICI'da kalır. Satışa hazır hale geldikten sonra SATICI'ya bilgi verilerek alıcı ile buluşturur.
				<br>
				<br><b class="baslik">2. HİZMET PAKETİ	</b>						
				<br>İç ve dış araç temizliği 							
				<br>Detaylı inceleme							
				<br>Test sürüşü							
				<br>Fotoğraf çekimi							
				<br>Video/Vlog Çekimi							
				<br>Ekspertiz işlemi yaptırma (traktörler hariç)							
				<br>Belirlenmiş pazaryeri platformlarında ilan yayınlama							
				<br>Satış aracılık hizmetleri							
			
			</div>
		</div>    
    </div>
    <div class="page">
        <div class="subpage">
			<br><b class="baslik">3. HİZMET ALANI</b>							
			<br>a) HİZMET VEREN'in hizmet alanı aracın detaylarına ilişkin inceleme, görüntü alma, ilan verme ve satış işlemlerinden ibaret olup diğer hizmetler bağımsız kuruluşlar tarafından sağlanmaktadır. 							
			<br>b) Hizmet alanı çerçevesinde yapılan işlemler tamamlandıktan sonra araç satış gerçekleşmesi sürecine kadar SATICI'da kalacak olup HİZMET VEREN bu süreye kadar araç ile ilgili hiçbir mesuliyeti kabul etmez.							
			<br>							
			<br><b class="baslik">4. ARACIN DURUMU</b>							
			<br>a) Aracın durumu, özellikleri ve varsa kusurları HİZMET VEREN ve bağlı kuruluşları tarafından belirlenecek olup bu kapsamda yapılacak giderler HİZMET VEREN tarafından ücretsiz olarak sunulacaktır.							
			<br>b) Araç satış süresince SATICI'da kalacağından satıcı araçta oluşan ve masraf çıkaran/çıkaracak her türlü değişikliği yazılı veya dijital (SMS veya İnternet mesajları) olarak HİZMET VEREN'e bildirmek durumundadır.							
			<br>c) Şayet varsa bildirilmediği halde satış sırasında farkedilen değişikliklerden VE oluşacak muhtel satış engellerinden SATICI sorumluğu kabul eder.							
			<br>							
			<br><b class="baslik">5. TEŞHİR VE İLAN</b>							
			<br>a) Araca ait bilgiler, ayrıntılı fotoğraflar, ayrıntılı videolar çeşitli sosyal medya, ilan ve satış platformlarında HİZMET VEREN tarafından teşhir edilecektir.							
			<br>b) Teşhir işlemi yapılırken araç sahibine ait bilgileri içeren kimlik bilgileri, plaka, şasi no, motor no vs. gibi bilgiler ve görüntüler paylaşılmayacaktır.							
			<br>c) Aracın özellikleri, kusurları ve genel durumuna ilişkin detayların şeffaflık ilkesi gereği yayınlanmasını her iki taraf da kabul ve beyan eder.							
			<br>							
			<br><b class="baslik">6. HİZMET BEDELİ</b>							
			<br>a) HİZMET VEREN söz konusu araca ilişkin yapılacak fotoğralama, video çekme, ekpertiz yapma, ilan verme işlemleri için SATICI'dan bir bedel talep etmemektedir.							
			<br>b) Hizmet bedeli SATICI'nın talep ettiği araç bedeline eklenerek satışa sunulmaktadır. 							
			<br>c) Hizmet bedeli araç satıldığı takdirde müşterisinden tahsil edilecektir.							
			<br>d) Söz konusu hizmet bedeli 7. maddede araç bedeli üzerinden fiyatlanmıştır.							
			<br>e) Söz konusu bedel bazı durumlarda satış sürecinde müşterisi tarafından SATICI'a ödemek durumunda kalabilir. Bu durumda henüz SATICI bu sözleşmede belirtilen hizmet bedelini satış günü mesai bitimine kadar HİZMET VEREN'e ödemek zorundadır. Aksi takdirde HİZMET VEREN araç satışına tedbir koyabilir, satışı geri çekebilir.							
			<br>							
			<br><b class="baslik">7. ARAÇ SATIŞ BEDELİ</b>							
			<br>a) Araç ilan bedeli <?php echo "<b>".$arac_bilgileri[ 'ilan_fiyati' ]."</b>"; ?> TL olarak belirlenmiş olup bu bedelin <?php echo "<b>".$arac_bilgileri[ 'talep_fiyat' ]."</b>"; ?> TL'si SATICI'ya, kalan	
			<br> <?php echo "<b>".$arac_bilgileri[ 'hizmet_bedeli' ]."</b>"; ?> TL si HİZMET VEREN'e hizmet bedeli olarak ödenecektir.  Kalan <?php echo "<b>".$arac_bilgileri[ 'pazarlik_payi' ]."</b>"; ?> TL ise
			<br> ilan fiyatına pazarlık amacı ile eklenmiş oluıp pazarlıksız ya da kısmi pazarlıklı satış durumunda bu bedel  							
			<br> Hizmet Veren'e ait olacaktır. 							
			<br>b) HİZMET VEREN araç satış bedelini değiştirse bile SATICI talep fiyatı olan <?php echo "<b>".$arac_bilgileri[ 'talep_fiyat' ]."</b>"; ?> TL üzerinden satış 	
			<br>yapmak zorundadır. SATICI satış sürecinde fiyat değişikliği talep edebilir. Bu durumda ek bir sözleşme düzenlenerek yeni fiyat üzerinden satış revize edilecektir. Yeni fiyat ek sözleşme tarihinden sonraki iş gününde geçerli olacaktır.							
			<br>c) Yeni satış bedeli talebi iyi niyet kuralları çerçevesinde gerçekleşecektir. Satıştan cayma olarak nitelendirilebilecek fiyat artışları durumunda Ek "satıştan cayma" kriterleri geçerli olacaktır.							
			<br>d) SATICI araç talep ettiği bedel dışında bir ücret talep etmeyecektir.							
			<br>e) Aracın noter satış sırasındaki belirlenen resmi değeri ile ilgili mali sorumluluk satıcıya aittir. 	
			<br>
			<br><b class="baslik">8. SATIŞTAN CAYMA</b>							
			<br>a) Araç satış sürecinde araç SATICI'da kalacağı için HİZMET VEREN dışında satış yapılabilinecektir. SATICI aracını başka bir kanal ile satması durumunda satışa HİZMET VEREN aracılık etmemiş dahi olsa hizmet bedelinin yarısı SATICI tarafından HİZMET VEREN'e ödenmek zorundadır.							
			<br>b) Ödeme noter devri yapılmadan gerçekleşecek olup aksi durumda devir yapılmayacaktır.							
			<br>c) Ödeme yapıldığı halde noter devri yapılamaması durumunda araç sahibi tahsil ettiği bedeli ödendiği şekilde iade etmek zorundadır.							
			<br>Bu durumda da <?php echo "<b>".$arac_bilgileri[ 'cayma_bedeli' ]."</b>"; ?> TL araç satışı gerçekleşmese dahi SATICI tarafından HİZMET VEREN'e ödemek zorundadır.
			<br>
			<br><b class="baslik">9. ÖDEME ŞEKLİ</b>							
			<br>a) Ödeme şekli SATICI'nın belirlediği yöntem ile satış sırasında geçerli olacak olup devir yapıldığı takdirde tahsilat gerçekleşmiş kabul edilecektir.							
			<br>b) SATICI, araç bedelini tahsil ettiğini teyit ettiği anda devir noter devir işlemleri gerçekleşecektir.							
			<br>Şayet SATICI araç bedelini tahisl edip araç devrini vermekten kaçınırsa tahsil ettiği meblağı aynı şekilde iade etmek zorundadır. Aksi durumda Şanlıurfa Mahkemeleri sorumludur.							
			<br>							
			<br><b class="baslik">10. ARACIN SATIŞI</b>							
			<br>a) Araç satış sürecinde ALICI müşteriler aracı görmek isteyebilir. Bu durumda HİZMET VEREN gelen her talebi SATICI'ya bildirerek randevu zamanı belirler. Müşteriler aracı test etmek isteyebilir, bağımsız ekpertizde özelliklerine ve kusurularına bakmak isteyebilir. Bu durum her defasında tekrar edebileceğinden SATICI aracı he randevuya yetiştirmek durumundandır. 							
			<br>b) Araç satış sürecinde ALICI müşteriler aracı görmek isteyebilir. Bu durumda HİZMET VEREN gelen her talebi SATICI'ya bildirerek randevu zamanı belirler. Müşteriler aracı test etmek isteyebilir, bağımsız ekpertizde özelliklerine ve kusurularına bakmak isteyebilir. Bu durum her defasında tekrar edebileceğinden SATICI aracı he randevuya zamanında getirmek durumundandır. Ertelenen ya da iptal edilen randevuları en az 1 saat önceden yazılı veya dijital olarak bildirmek durumundadır. 3 kez ertelenen randevu iptal gerekçesi sayılacağından satışa muhalefet değerlendirilir be bu durumda satıştan cayma işlemleri yapılarak cezai şartlar uygulanır.							
			<br>c) HİZMET VEREN, sözleşme tarihi itibariyle aracın devrine engel durumları tespit eder. Eğer engel durum var ise SATICI muhtemel satıştan önce söz konusu engelleri ortadan kaldırmayı kabul ve taahhüt eder.							
			<br>d) Engellerin SATICI tarafından kaldırılmaması veya yeni engellerin oluşması durumunda satışın gerçekleşmesini engelleyeceğinden müşteri olarak hazır bulunan ALICI'ya ödenmek üzere <b>1.000,00 TL</b>'ye ek olarak  hizmet bedelinin yarısını HİZMET VEREN'e ödemek zorundadır. 							
			<br>e) SATICI'dan kaynaklı satış engelleri sebebiyle ödenecek olan yukarıdaki maddede belirtilen meblağlar, SATICI'nın hizmet almaya devam etmesi durumunda sadece hizmet bedelinin yarısı olarak tahsil edilen bedel SATICI'ya iade edilir. Önceki ALICI'ya ödenen bedel geri alınamaz. Bu durumda hizmet süreci devam ederek yeni bir ALICI bulunması sağlanır. Yeni ALICI ile satış sürecine girilmesi ile yine satışa engel durumların oluşması durumunda aynı şekilde yeni satıcıya 1.000,00 TL HİZMET VEREN'e ise hizmet bedeli kadar tutarı ödemek zorundadır.							
			<br>							
			<br><b class="baslik">11. ARACIN KULLANIMINDAN DOĞACAK SORUNLAR</b>							
			<br>HİZMET VEREN, araca ait detaylara ulaşmak için hizmet kapsamında yapacağı bazı işlemler trafiğe açık alanda gereçkeleşecek olup bu husus SATICI tarafından kabul edilir. Bu süreçte HİZMET VEREN personellerinin karışacağı muhtemel kazalarda varsa aracın kasko poliçesi veya diğer sigorta klozlarından yararlanılmasını SATICI kabul eder.							
			<br>Hizmet paketi uygulandıktan sonraki satış sürecinde araç SATICI'da kalacağı için satışa kadarki tüm sorumluklar SATICI'ya aittir.							

		</div>  
	</div>  
	<div class="page">
		<div class="subpage">
			<br><b class="baslik">12. SORUMLULUKLAR</b>							
			<br>SATICI fiyat değişikliği ve satıştan cayma talebini HİZMET VEREN'e yazılı olarak ibraz etmek zorundadır.							
			<br>SATICI satış sürecinde araçta yaşanan tüm olumlu olumsuz değişiklikleri tam zamanlı olarak HİZMET VEREN'e bildirmek zorundadır.							
			<br>HİZMET VEREN, araca yönelik talep ve alım taleplerini SATICI ile paylacaktır.							
			<br>Satış sırasında noter randevusunda her iki taraf da bulunmak zorundadır.							
			<br>Araç devir işlemi SATICI'nın insiyatifindedir. Satış gerçkeştirdiği anda ödeme ile ilgili yükümlülükleri kabul eder.
			<br>
			<br><b class="baslik">13. HİZMET VEREN'in HAK VE YÜKÜMLÜLÜKLERİ</b>							
			<br>a) Otowow ve diğer ilan platformalarının kontrol ve denetimi altındaki kişisel kimlik, adres, iletişim bilgilerinin kaybolmasını, suistimal edilmesini ve değişitirlimesini, engellemek amacıyla makul güvenlik önlemleri almaktadır. Ancak HİZMET VEREN, bu bilgilerin güvenliğini hiçbir şekilde garanti etmez. SATICI'nın, HİMET VEREN'e aktardığı bilgi veriler gizli bilgi şeklinde yorumlanmayacaktır.							
			<br>b) HİZMET VEREN önceden SATICI'ya bildirimde bulunmadan bilgilerin biçim ve içeriğini kısmen veya tamamen değiştirebilir.							
			<br>c) HİZMET VEREN dilediği zamanda ve sebep göstermeksizi, önceden SATICI'ya bilgi vermeksizin hizmet kapsamını veya çeşitliliğini değiştirebileceği gibi sunulan hizmetleri kısmen veya tamamen dondurabilir sona erdirebilir veya tamamen iptal edebilir.							
			<br>
			<br><b class="baslik">14. SATICI'nın HAK VE YÜKÜMLÜLÜKLERİ</b>							
			<br>a) SATICI, işbu sözleşme hükümlerini hizmetlere ilişkin HİZMET VEREN tarafından açıklanan/açıklanacak her türlü beyanı kabul etmiş olmaktadır.							
			<br>b) SATICI, sözleşme sırasında kimlik, adres ve iletişim bilgilerinin eksiksiz ve doğru olduğunu, bilgilerinde değişiklik olması halinde derhal HİZMET VEREN'e iletceğini, eksik, güncel olmayan veya yanlış bilgi verilmesi nedeniyle ortaya çıkabilecek her türlü hukuki uyuşmazlık ve zarardan kendisinin sorumlu olacağını kabul ve beyan eder.							
			<br>
			<br><b class="baslik">15. SÖZLEŞME HÜKÜMLERİNDE DEĞİŞİKLİK</b>							
			<br>a) HİZMET VEREN, sözleşmede beliritlen iş ve işlemlerin daha etkin gerçkeleştirilebilmesi açısından yapacağı değişiklikler nedeniyle ve ayrıca gördüğü lüzum üzerinde SATICI'YA bildirimde bulunmaya gerek olmaksızın ve sebep göstermeksizin işbu sözleşme hükümlerinde tek taraflı olarak değişiklik yapma hakkına sahiptir. SATICI, bu hususu şimdiden gayri kabili rücu olarak kabul eder.							
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