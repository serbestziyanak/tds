<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$personel_id	= array_key_exists( 'personel_id'	, $_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
$tarih			= array_key_exists( 'tarih'		    , $_REQUEST ) ? $_REQUEST[ 'tarih' ]		: '';

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
			Hatalı İşlem Yapmaya Çalışmaktasınız. Hemen Sayfadan Çıkmanız gereskmekte.
		</div>';
	die();
}

?>

<script type="text/javascript">
$(document).ready(function() {
	var tutanak_tipi 	= "<?php echo $_REQUEST['tip'] ?>";
    var tarih = new Date();
    var docDefinition = {
        content: [
            {
                style: 'tableExample',
                table: {
                    headerRows: 1,
                    widths: ["15%","5%","30%","10%","5%","35%"],
                    // keepWithHeaderRows: 1,
                    body: [
                            [
                                {text: 'T.C\nMİLLÎ EĞİTİM BAKANLIĞI\nİŞLETMELERDE MESLEKİ EĞİTİM SÖZLEŞMESİ', style: 'tableHeader', colSpan: 6, alignment: 'center'},{},{},{},{},{}
                            ],
                            [
                                {text:'',colSpan:6, alignment: 'center' },{},{},{},{},{}
                            ],
                            [
                                {text:'Adı Soyadı',colSpan:2,},{},{text:'<?php echo $personel[0]["adi"]." ".$personel[0]["soyadi"] ?>'},{text:'Kayıtlı olduğu Okul/Kurumun adı',colSpan:2},{},{}  					
                            ],
                            
                            [
                                {text:'T.C. Kimlik No',colSpan:2,},{},{text:'<?php echo $personel[0]["tc_no"]; ?>'},{text:'Okul Numarası',colSpan:2},{},{}  					
                            ],
                            
                            [
                                {text:'Baba Adı ',colSpan:2,},{},{text:'<?php echo $personel[0]["baba_adi"]; ?>'},{text:'Sınıfı-Şubesi',colSpan:2},{},{}  					
                            ],
                            
                            [
                                {text:'Ana Adı',colSpan:2,},{},{text:'<?php echo $personel[0]["ana_adi"]; ?>'},{text:'Alanı/Dalı',colSpan:2},{},{}  					
                            ],
                            [
                                {text:'Doğum Yeri',colSpan:2,},{},{text:'<?php echo $personel[0]["dogum_yeri"]; ?>'},{text:'Öğrenci Cep Telefonu',colSpan:2},{},{}  					
                            ],
                            [
                                {text:'Doğum Tarihi\n(Gün/Ay/Yıl)',colSpan:2,},{},{text:'<?php echo date("d/m/Y", strtotime($personel[0]["dogum_tarihi"])); ?>'},{text:'İşletmede Mesleki Eğitime\nBaşlama Tarihi',colSpan:2},{},{}  					
                            ],
                            [
                                {text:'OKUL/KURUMDA İRTİBAT SAĞLANACAK KOORDİNATÖR MÜDÜR YARDIMCISININ',colSpan:6,style: 'tableHeader',alignment: 'center',},{},{},{},{},{}				
                            ],
                            [
                                {text:'Adı Soyadı:',colSpan:3,},{},{},{text:'Adresi:\n\n', colSpan:3},{},{}  					
                            ],
                            [
                                {text:'Telefonu:',colSpan:3,},{},{},{text:'E Posta:', colSpan:3},{},{}  					
                            ],
                            [
                                {text: 'ÖĞRENCİ VELİSİNİN BİLGİLERİ ', style: 'tableHeader', colSpan: 3, alignment: 'center'},{},{},{text: 'İŞYERİ (İŞLETME) BİLGİLERİ', style: 'tableHeader', colSpan: 3, alignment: 'center'},{},{}
                            ],
                            [
                                {text: 'Adı Soyadı', colSpan: 2,},{},{},{text: 'Adı', colSpan: 2,},{},{text:'Akyol Denim Tekstil Sanayi ve Ticaret LTD.STİ.'},
                            ],
                            [
                                {text: 'ikamet Adresi', colSpan: 2,rowSpan:2},{},{text:'',rowSpan:2},{text: 'Adresi', colSpan: 2,},{},{text:'Şemsibey OSB Mahallesi Ahtamara Caddesi No:17 Tuşba / VAN'},
                            ],
                            [
                                {},{},{},{text: 'İşletme Temsilcisinin\nAdı Soyadı', colSpan: 2,},{},{text:'Serdar ÇAÇA'},
                            ],
                            [
                                {text: 'Yakınlığı', colSpan: 2,},{},{},{text: 'Telefon Numarası', colSpan: 2,},{},{text:'90 537 612 4543'},
                            ],
                            [
                                {text: 'Telefonu', rowSpan:2},{text:'Ev'},{},{text: 'Fax Numarası', colSpan: 2,},{},{},
                            ],
                            [
                                {text: 'Telefonu'},{text:'Cep'},{},{text: 'E-Posta adresi', colSpan: 2,},{},{text:'akyoldenimmuhasebe@gmail.com'},
                            ],
                            [
                                {text: 'E Posta', colSpan: 2,},{},{},{text: 'Vergi No', colSpan: 2,},{},{text:'8720627945'},
                            ],
                            [
                                {text: '\nÖğrencinin 18 yaşından \nbüyük olması ve velisi\nbulunmaması halinde\nirtibat sağlanacak kişinin\n\n', colSpan: 2,rowSpan:3},{},{text:'Adı Soyadı : \nİletişim Bilgileri:',rowSpan:3},{text: 'SGK İşyeri Sicil No/Bağkur No', colSpan: 3,alignment: 'center',},{},{},
                            ],
                            [
                                {},{},{},{text: '2 1413 01 01 1045408 065 14 32 000', colSpan: 3, alignment: 'center'},{},{},
                            ],
                            [
                                {},{},{},{text: 'İŞLETME IBAN NO', colSpan: 3,alignment: 'center',},{},{},
                            ],
                            [
                                {
                                    table: {
                                        body: [
                                            [
                                                {text:'TR' ,alignment: 'center',fontSize:12},
                                                {text:'7',alignment: 'center',fontSize:12},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'',border: [false, false, false, false],},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'6',alignment: 'center',fontSize:12},
                                                {text:'',border: [false, false, false, false],},
                                                {text:'2',alignment: 'center',fontSize:12},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'1',alignment: 'center',fontSize:12},
                                                {text:'',border: [false, false, false, false],},
                                                {text:'2',alignment: 'center',fontSize:12},
                                                {text:'8',alignment: 'center',fontSize:12},
                                                {text:'1',alignment: 'center',fontSize:12},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'',border: [false, false, false, false],},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'0',alignment: 'center',fontSize:12},
                                                {text:'6',alignment: 'center',fontSize:12},
                                                {text:'',border: [false, false, false, false],},
                                                {text:'2',alignment: 'center',fontSize:12},
                                                {text:'9',alignment: 'center',fontSize:12},
                                                {text:'6',alignment: 'center',fontSize:12},
                                                {text:'6',alignment: 'center',fontSize:12},
                                                {text:'',border: [false, false, false, false],},
                                                {text:'9',alignment: 'center',fontSize:12},
                                                {text:'8',alignment: 'center',fontSize:12},
                                                {text:'',border: [false, false, false, false],},
                                            ],
                                        ]
                                    },
                                    colSpan:6,},{},{},{},{},{},
                            ],
                            [
                                {text:[
                                        {text:'Destek Ödemesi :', fontSize: 12, bold: true},{text:' (İşletme tarafından doldurulacaktır)',fontSize:11}
                                    ],colSpan:6,border: [true, true, true, ],},{},{},{},{},{}				
                            ],
                            [
                                {
                                    table: {
                                        widths: [6,"*",],
                                        body: [
                                            [
                                                {text:'',lineHeight:10,width:40},
                                                {text:'İstiyorum (Öğrencinin maaş ödeme belgesini her ayın 10 una kadar okula göndermeyi taahhüt ediyorum)',border: [true, false, false, false],},
                                            ],
                                        ]
                                    },
                                    colSpan:6,border: [true, false, true, false]},{},{},{},{},{},
                            ],
                            [
                                {
                                    table: {
                                        widths: [6,"*",],
                                        body: [
                                            [
                                                {text:''},
                                                {text:'İstemiyorum',border: [true, false, false, false],},
                                            ],
                                        ]
                                    },
                                    colSpan:6,border: [true, false, true, false]},{},{},{},{},{},
                            ],
                    ]
                }
            },
                {
                style: 'tableExample',
                table: {
                    headerRows: 1,
                    widths: ["33,33%","33,33%","34%",],
                    // keepWithHeaderRows: 1,
                    body: [
                            [
                                {text: 'Okul/Kurumun Adı', alignment: 'center',bold:true},
                                {text: 'İşletmenin Adı',  alignment: 'center',bold:true},
                                {text: 'Öğrenci 18 yaşından küçükse\nyasal temsilcisi (velisi) veya öğrenci\n18 yaşından büyükse öğrencinin\nkendisi imzalayacaktır.', bold:true},
                            ],
                            [
                                {text: 'Okul/Kurum Müdürünün', alignment: 'center',bold:true},
                                {text: 'İşveren veya Vekilinin',  alignment: 'center',bold:true},
                                {text: 'Öğrenci veya Velisinin', alignment: 'center', bold:true},
                            ],
                            [
                                {text: 'Adı Soyadı :',bold:true},
                                {text: 'Adı Soyadı :\nGörevi:',  bold:true},
                                {text: 'Adı Soyadı : <?php echo $personel[0]["adi"]." ".$personel[0]["soyadi"]; ?>', bold:true},
                            ],
                            [
                                {text: 'Tarih : …. / …. /202.. ', alignment: 'center',bold:true},
                                {text: 'Tarih : …. / …. / 202..',  alignment: 'center',bold:true},
                                {text: 'Tarih : …./ …./ 202..', alignment: 'center', bold:true},
                            ],
                            [
                                {text: '\nİmza-Mühür\n\n\n', alignment: 'center',bold:true},
                                {text: '',  alignment: 'center',bold:true},
                                {text: '', alignment: 'center', bold:true},
                            ],
                    ]
                }
            },
        ],
        styles: {
            header: {
                fontSize: 18,
                bold: true,
                margin: [0, 0, 0, 10]
            },
            subheader: {
                fontSize: 16,
                bold: true,
                margin: [0, 10, 0, 5]
            },
            tableExample: {
                fontSize: 9,
            },
            tableHeader: {
                bold: true,
                fontSize: 12,
                color: 'black'
            }
        },
        defaultStyle: {
            columnGap: 20
        },
        info: 
        {
            title: 'Günlük Devamsızlık Tutanağı',
            author: 'Syntax Yazılım PDKS',
            creator:'Syntax Yazılım PDKS',
            producer:'Syntax Yazılım PDKWS',
            subject:'Günlük Devamsızlık Tutanağı'
        }
    };
    createPdf(docDefinition).print({}, window);
});
	
</script>