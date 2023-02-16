<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$personel_id	= array_key_exists( 'personel_id'	, $_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
$tarih			= array_key_exists( 'tarih'		    , $_REQUEST ) ? $_REQUEST[ 'tarih' ]		: '';

//Gelen Personele Ait Bilgiler
$SQL_tek_personel_oku = <<< SQL
SELECT
	*,
	CONCAT(adi, ' ', soyadi) AS adsoyad,
	(
		SELECT tb_bolumler.adi FROM tb_bolumler WHERE tb_bolumler.id = tb_personel.bolum_id
	) AS bolum_adi 
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

//Çıkış Yapılıp Yapılmadığı Kontrolü
$SQL_firma_oku = <<< SQL
SELECT
	*
FROM
	tb_firmalar
WHERE
	id 	= ?
SQL;

$firma 			= $vt->select( $SQL_firma_oku, array( $_SESSION['firma_id'] ) )[ 2 ][ 0 ];
$personel 		= $vt->select( $SQL_tek_personel_oku, array($personel_id,$_SESSION['firma_id']) )[2];
$giriscikis 	= $vt->select( $SQL_personel_gun_giris_cikis, array($personel_id,$tarih) )[2];

if(count($personel)<1){
	echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
			<h5><i class="icon fas fa-ban"></i> Hata!</h5>
			aHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
		</div>';
	die();
}
if ( $_REQUEST['tip'] == "gunluk" ) {
	if(count($giriscikis)>0){
		echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
				<h5><i class="icon fas fa-ban"></i> Hata!</h5>
				bHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
			</div>';
		die();
	}
}else{
	if(count($giriscikis)<1){
		echo '<div class="alert alert-danger alert-dismissible col-sm-6 offset-sm-3 align-items-center">
				<h5><i class="icon fas fa-ban"></i> Hata!</h5>
				bHatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gerekmekte.
			</div>';
		die();
	}
}

if ( array_key_exists( 'saat', $_REQUEST ) ){
	//saatin sonuna de veya da bırakacağımızı belirliyoruz
	$saat_son_rakam = substr($_REQUEST['saat'], -1);
	$ek = "de";
	if ($saat_son_rakam == 0 or $saat_son_rakam == 6 or $saat_son_rakam == 9 ) {
		$ek = "da";
	}
}

?>

<script type="text/javascript">
$(document).ready(function() {
	var tutanak_tipi 	= "<?php echo $_REQUEST['tip'] ?>";
	var tutanak_icerigi = '';

	//Tutanak Tipine göre tutanak içeriğini belirliyoruz Saatli mi veya erken çıkıs veya gec giriş oldugu mu 
	if ( tutanak_tipi 		== 'gunluk' ) {

		tutanak_icerigi = '\u200B\t \u200B\t Yukarıda Unvanı adresi yazılı işyerimizde çalışan <?php echo $personel[0]["tc_no"]; ?> T.C. kimlik numaralı <?php echo $personel[0]["adsoyad"] ?> isimli işçimiz  <?php echo date("d.m.Y", strtotime($_REQUEST["tarih"])); ?> tarihinde iznimiz ve bilgimiz dışında mazeretsiz olarak mesaisine gelmemiştir.\n\n\u200B \t\u200B\t İşbu tutanak <?php echo date("d.m.Y") ?> tarihinde aşağıda isimleri yazılı şahitler huzurunda düzenlenmiş ve müştereken imza altına alınmıştır.';
		var tip 			= 'Mazaretsiz Gelmeme ';
	}else if ( tutanak_tipi == 'gecgelme' ) {

		tutanak_icerigi = '\u200B\t \u200B\t Yukarıda Unvanı adresi yazılı işyerimizde çalışan <?php echo $personel[0]["tc_no"]; ?> T.C. kimlik numaralı <?php echo $personel[0]["adsoyad"] ?> isimli işçimiz <?php echo date("d.m.Y", strtotime($_REQUEST["tarih"])); ?> tarihinde iznimiz ve bilgimiz olmaksızın mazeretsiz olarak saat <?php echo $_REQUEST["saat"]."\'".$ek; ?> mesaisine gelmiştir.\n\n\u200B \t\u200B\t İşbu tutanak <?php echo date("d.m.Y") ?> tarihinde aşağıda isimleri yazılı şahitler huzurunda düzenlenmiş ve müştereken imza altına alınmıştır.';
		var tip 			= 'Mazaretsiz  Geç Gelme';
	}else if ( tutanak_tipi == 'erkencikma' ){

		tutanak_icerigi = '\u200B\t \u200B\t Yukarıda Unvanı adresi yazılı işyerimizde çalışan <?php echo $personel[0]["tc_no"]; ?> T.C. kimlik numaralı <?php echo $personel[0]["adsoyad"] ?> isimli işçimiz <?php echo date("d.m.Y", strtotime($_REQUEST["tarih"])); ?> tarihinde iznimiz ve bilgimiz olmaksızın mazeretsiz olarak saat <?php echo $_REQUEST["saat"]."\'".$ek; ?> iş yerimizden ayrılmıştır.\n\n\u200B \t\u200B\t İşbu tutanak <?php echo date("d.m.Y") ?> tarihinde aşağıda isimleri yazılı şahitler huzurunda düzenlenmiş ve müştereken imza altına alınmıştır.';
		var tip 			= 'Mazaretsiz Erken Çıkma';
	}

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
						        {text:'Adresi\n\n\n', style:'adres'},
						        {text:'SGK İşyeri Sicil Numarası', style:'sicil'}
							],
						},
						{
						    width: 10,
						    text: [
						        {text:'\n\n', style:''},
						        {text:':\n\n', style:''},
						        {text:':\n\n\n', style:''},
						        {text:':', style:''}
							],
						},
						{  
						   width: 'auto',
						   text: [
						        {text:'\n\n', style:''},
						        {text:'<?php echo $firma[ "firma" ]; ?>\n\n', style:''},
						        {text:'<?php echo $firma[ "adres" ];  ?>\n\n', style:''},
						        {text:'<?php echo $firma[ "ticaret_sicil_no" ]?>', style:''}
							],
						}
					],
					style:'firmaBilgisi'
				},{
					text: tutanak_icerigi,
					style: 'metin',
					pageBreak: 'after'
				},
				{
					text: '\nSAVUNMA İSTEM YAZISI\n\n',
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
								{text:'Adresi\n\n\n', style:'adres'},
								{text:'SGK İşyeri Sicil Numarası', style:'sicil'}
							],
						},
						{
							width: 10,
							text: [
								{text:'\n\n', style:''},
								{text:':\n\n', style:''},
								{text:':\n\n\n', style:''},
								{text:':', style:''}
							],
						},
						{  
						width: 'auto',
						text: [
								{text:'\n\n', style:''},
								{text:'<?php echo $firma[ "firma" ]; ?>\n\n', style:''},
								{text:'<?php echo $firma[ "adres" ];  ?>\n\n'},
								{text:'<?php echo $firma[ "ticaret_sicil_no" ]?>', style:''}
							],
						}
					],
					style:'bosluk'
				},
				{
					columns: [
						{   
							width: 160,
							text: [
								{text:'İŞÇİNİN\n\n', style:'isveren'},
								{text:'Adı Soyadı\n', style:'unvan'},
								{text:'T.C. Kimlik No\n', style:'adres'},
								{text:'Bölümü\n\n', style:'sicil'},
								{text:'Savunma İstem Tarihi', style:'adres'}
							],
						},
						{
							width: 10,
							text: [
								{text:'\n\n', style:''},
								{text:':\n', style:''},
								{text:':\n', style:''},
								{text:':\n\n', style:''},
								{text:':', style:''},
							],
						},
						{  
						width: 'auto',
						text: [
								{text:'\n\n', style:''},
								{text:'<?php echo $personel[0]["adsoyad"] ?>\n', style:''},
								{text:'<?php echo $personel[0]["tc_no"]; ?>\n'},
								{text:'<?php echo $personel[0]["bolum_adi"]; ?>\n\n', style:''},
								{text:'<?php echo date("d/m/Y"); ?>', style:''},
							],
						}
					],
					style:'bosluk'
				},
				{
					text: '\u200B\t \u200B\t<?php echo date("d/m/Y", strtotime($_REQUEST["tarih"])); ?> tarihinde yapmamanız gerektiği halde yapmış olduğunuz  "'+tip+'" '+
					' şeklindeki aykırı hareketinizle ilgili 4857 sayılı İş Kanununun 25/II/… Maddesi göz önünde' +
					'bulundurularak, 109. maddesi uyarınca …../……/……tarihine kadar savunma yapmanız gerekmektedir.' +
					'Savunmanızı haklı kılacak geçerli belgelerinizin olması halinde bu belgeleri savunmanıza eklemeniz, ' +
					'aksi takdirde savunmanızın soyut kalacağı ve bahaneden öteye gitmeyeceğini, savunma yapmamanız' +
					'halinde ise, aykırı davranışı kabul etmiş sayılacağınızı peşinen bildiririz.' +
					'\n\n \u200B\t \u200B\tDüzenleyeceğiniz savunma yazınızı en son ..…/……/202… tarihinde saat 17:00 ‘a kadar İnsan ' +
					'Kaynakları Departmanına vermeniz hususunu bilgilerinize sunarız.' +
					'\n\nBir nüshasını aldım.\n\nTarih: ...../...../202...\n\n',
					style: 'metin'
				},
				{
					columns: [
						{   
							text: [
								{text:'İşçinin Adı Soyadı\n',style:'imza'},
								{text:'İmzası', style:'imza'}
							],
						},
						{
						},
						{
						},
						{  
						width: 'auto',
						text: [
								{text:'İşveren/İşveren Vekili\n',style:'imza'},
								{text:'Adı Soyadı', style:'imza'}
							],
						}
					]
				},
				{
					text:[
					{text:'\n\n\n\n( AŞAĞIDAKİ TUTANAK İŞÇİNİN SAVUNMA YAZISINI ALMAMASI HALİNE KULLANILACAKTIR. )\n\n\n'},
					{text:'İşçi  işbu savunma istem yazısının içeriği hakkında bilgi almış (yazı kendisinin yüzene karşı okunmuş/yazıyı kendisi okumuş) fakat imzadan imtina etmiştir.\nTarih:…/…./202...'},  
					],
					
				},{
					columns: [
						{   
							text:'Tanık',style:'center'
						},
						{   
							text:'Tanık',style:'center',
						},
						{   
							text:'Tanık',style:'center',
						},
					]
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
				bosluk: {
					marginBottom: 25,
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
				},
				center:{
					marginTop: 10,
					alignment:'center'
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