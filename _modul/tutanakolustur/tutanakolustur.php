<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();



$personel_id	= array_key_exists( 'personel_id'	,$_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
@$tarih			= array_key_exists( 'tarih'	,$_REQUEST ) ? $_REQUEST[ 'tarih' ]					: '';

$tarih 			= explode("-", $tarih);

//Gelen Personele Ait Bilgiler
$SQL_tek_personel_oku = <<< SQL
SELECT
	*,
	CONCAT(adi, ' ', soyadi) AS adsoyad
FROM
	tb_personel
WHERE
	id = ? AND firma_id =? AND aktif = 1
SQL;


//Çıkış Yapılıp Yapılmadığı Kontrolü
$SQL_personel_gun_giris_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis 
WHERE
	personel_id = ? AND tarih = ? 
SQL;

$personel 		= $vt->select( $SQL_tek_personel_oku, array($personel_id,$_SESSION['firma_id']) )[2];
$giriscikis 	= $vt->select( $SQL_personel_gun_giris_cikis, array($personel_id,$tarih) )[2];

if(count($personel)<1){
	echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
			<h5><i class="icon fas fa-ban"></i> Hata!</h5>
			Hatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
		</div>';
	die();
}
if(count($giriscikis)>0){
	echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
			<h5><i class="icon fas fa-ban"></i> Hata!</h5>
			Hatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
		</div>';
	die();
}

//saatin sonuna de veya da bırakacağımızı belirliyoruz
$saat_son_rakam = substr($_REQUEST['saat'], -1);
$ek = "de";
if ($saat_son_rakam == 0 or $saat_son_rakam == 6 or $saat_son_rakam == 9 ) {
	$ek = "da";
}


?>

<script type="text/javascript">
$(document).ready(function() {
	var tutanak_tipi 	= "<?php echo $_REQUEST['tip'] ?>";
	var tutanak_icerigi = '';

	//Tutanak Tipine göre tutanak içeriğini belirliyoruz Saatli mi veya erken çıkıs veya gec giriş oldugu mu 
	if ( tutanak_tipi 		== 'gunluk' ) {

		tutanak_icerigi = '\u200B\t \u200B\t Yukarıda Unvanı adresi yazılı işyerimizde çalışan <?php echo $personel[0]["tc_no"]; ?> T.C. kimlik numaralı <?php echo $personel[0]["adsoyad"] ?> isimli işçimiz  <?php echo date("d.m.Y", strtotime($_REQUEST["tarih"])); ?> tarihinde iznimiz ve bilgimiz dışında mazeretsiz olarak mesaisine gelmemiştir.\n\n\u200B \t\u200B\t İşbu tutanak <?php echo date("d.m.Y") ?> tarihinde aşağıda isimleri yazılı şahitler huzurunda düzenlenmiş ve müştereken imza altına alınmıştır.';

	}else if ( tutanak_tipi == 'gecgelme' ) {

		tutanak_icerigi = '\u200B\t \u200B\t Yukarıda Unvanı adresi yazılı işyerimizde çalışan <?php echo $personel[0]["tc_no"]; ?> T.C. kimlik numaralı <?php echo $personel[0]["adsoyad"] ?> isimli işçimiz <?php echo date("d.m.Y", strtotime($_REQUEST["tarih"])); ?> tarihinde iznimiz ve bilgimiz olmaksızın mazeretsiz olarak saat <?php echo $_REQUEST["saat"]."\'".$ek; ?> mesaisine gelmiştir.\n\n\u200B \t\u200B\t İşbu tutanak <?php echo date("d.m.Y") ?> tarihinde aşağıda isimleri yazılı şahitler huzurunda düzenlenmiş ve müştereken imza altına alınmıştır.';

	}else if ( tutanak_tipi == 'erkencikma' ){

		tutanak_icerigi = '\u200B\t \u200B\t Yukarıda Unvanı adresi yazılı işyerimizde çalışan <?php echo $personel[0]["tc_no"]; ?> T.C. kimlik numaralı <?php echo $personel[0]["adsoyad"] ?> isimli işçimiz <?php echo date("d.m.Y", strtotime($_REQUEST["tarih"])); ?> tarihinde iznimiz ve bilgimiz olmaksızın mazeretsiz olarak saat <?php echo $_REQUEST["saat"]."\'".$ek; ?> iş yerimizden ayrılmıştır.\n\n\u200B \t\u200B\t İşbu tutanak <?php echo date("d.m.Y") ?> tarihinde aşağıda isimleri yazılı şahitler huzurunda düzenlenmiş ve müştereken imza altına alınmıştır.';
	}

	var canvasElement = document.getElementById("canvas");
		var tarih = new Date();
	    var docDefinition = {
	        content: [
				{
					text: 'GÜNLÜK DEVAMSIZLIK\n\n\n\n Tutanaktır \n\n\n',
					style: 'header',
					alignment: 'center'
				},
				{
					columns: [
						{   
						    width: 160,
							text: [
						        {text:'İŞVERENİN\n\n', style:'isveren'},
						        {text:'Ünvanı\n\n', style:'unvan'},
						        {text:'Adresi\n\n', style:'adres'},
						        {text:'SGK İşyeri Sicil Numarası', style:'sicil'}
							],
						},
						{
						    width: 10,
						    text: [
						        {text:'\n\n', style:''},
						        {text:':\n\n', style:''},
						        {text:':\n\n', style:''},
						        {text:':', style:''}
							],
						},
						{  
						   width: 'auto',
						   text: [
						        {text:'\n\n', style:''},
						        {text:'TUŞBA KONUT İNŞ.TUR.SAN. VE TİC.LTD.ŞTİ.\n\n', style:''},
						        {text:'Şemsibey Osb Mahallesi Ahtamar Caddesi No:17\n\n', style:''},
						        {text:'2 1413 01 01 1045408 065 14 32 000', style:''}
							],
						}
					],
					style:'firmaBilgisi'
				},{
					text: tutanak_icerigi,
					style: 'metin'
				},
			],
			styles: {
				header: {
				    fontSize:15,
					bold: true,
					alignment: 'justify',
				},
				firmaBilgisi: {
					marginBottom: 50,
					alignment: 'left'
				},
				imza: {
					marginTop: 100,
					alignment: 'center'
				},
				metin: {
					alignment: 'justify',
				},
				isveren:{
				    decoration:'underline',
				    bold:true
				}
				
				
			},
			info: 
			{
		    	title: 'Günlük Devamsızlık Tutanağı',
		    	author: 'Syntax Yazılım PDKS',
		    	creator:'Syntax Yazılım PDKS',
		    	producer:'Syntax Yazılım PDKS',
		    	subject:'Günlük Devamsızlık Tutanağı'
		    }
	    };
	    createPdf(docDefinition).print({}, window);
	    
});
	
</script>