<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();
/* Bu modülde yapılması gereken:
	Eğer işe ait iş günlüğü sürekli geçerli değil ise dünün iş günlüğü bugüne iş günlüğü olarak eklenmelidir.
*/


/*
Hedef geçerliliği:
1: İş bitinceye kadar geçerli
2: Seçilen gün geçerli
*/
$SQL_gunluk_hedef = <<<SQL
SELECT
	 sig.gunluk_hedef
	,sig.gecerlilik
	,sig.tarih
FROM
	sayac_is_gunlukleri AS sig
LEFT JOIN sayac_isler AS si ON sig.is_id = si.id
WHERE
	si.aktif = 1
AND si.bitis_tarihi IS NULL
AND (
	sig.gecerlilik = 1
	OR NOT EXISTS (
		SELECT
			TRUE
		FROM
			sayac_is_gunlukleri AS ig2
		WHERE
			ig2.is_id = si.id
		AND ig2.tarih > sig.tarih
	)
)
ORDER BY
	sig.tarih DESC
LIMIT 1
SQL;


$SQL_aktif_is = <<<SQL
SELECT
	 id
	,adi
	,siparis_adet
FROM
	sayac_isler
WHERE
	aktif = 1 AND bitis_tarihi IS NULL
SQL;

/* Makina bilgileri */
$SQL_makinalar = <<<SQL
SELECT
	 sm.id
	,CONCAT(p.adi," ",p.soyadi) AS personel_adi_soyadi
	,p.resim AS personel_resim
	,sc.sayac_no
	,ip.adi AS is_parca_adi
	,sm.is_basina_sayac_sayisi
FROM
	sayac_makina AS sm
LEFT JOIN
	tb_personel AS p ON sm.personel_id = p.id
LEFT JOIN
	sayac_sayac_cihazlari AS sc ON sm.sayac_cihaz_id = sc.id
LEFT JOIN
	sayac_is_parcalari AS ip ON sm.is_parca_id = ip.id
WHERE sm.aktif = 1
SQL;


/* Aktif olan işin bilgileri */
$aktif_is		= $vt->select( $SQL_aktif_is );
$is_id			= $aktif_is[ 2 ][ 0 ][ "id" ];
$is_adi			= $aktif_is[ 2 ][ 0 ][ "adi" ];
$siparis_adet	= $aktif_is[ 2 ][ 0 ][ "siparis_adet" ];


/* Aktif iş için belirlenen günlük hedefi bul */
$gunluk_hedef	= $vt->select( $SQL_gunluk_hedef );
$gunluk_hedef	= $gunluk_hedef[ "2" ][ 0 ][ "gunluk_hedef" ];
$gunluk_hedef	=  number_format( $gunluk_hedef, 0, '', ',' );



/* Tüm makinaların bilgilerini oku */
$makinalar = $vt->select( $SQL_makinalar );

?>


<style>
.badge-number{
	font-size: 1.3em;
	margin-bottom:5px;
	letter-spacing: .2rem;
	font-family:'digital-clock-font';
}

.badge-time{
	font-size: 1.1em;
	letter-spacing: .2rem;
	font-family:'digital-clock-font';
}

.badge-number-detail{
	font-size: 1.6em;
	margin-bottom:5px;
	letter-spacing: .2rem;
	font-family:'digital-clock-font';
}


.badge-number-sum{
	font-size: 2em;
	letter-spacing: .2rem;
	font-family:'digital-clock-font';
}

@font-face{
 font-family:'digital-clock-font';
 src: url('font/digital-7.ttf');
 letter-spacing: .2rem;
}
</style>

<script>
	$(document).keyup(function(e) {
		if (e.key === "Escape") {
			$(".timeline-item").hide(50);
		}
	});

	$(document).keyup(function(e) {
		if (e.ctrlKey && e.keyCode == 13) {
			if ( $(".timeline-item").is(':visible') ) {
				$(".timeline-item").hide(50);
			} else {
				$(".timeline-item").show(50);
			}
		}
	});

	function istasyonGoster(id){
		if ( $(id).is(':visible') ) {
			$(id).hide(50);
		} else {
			$(id).show(50);
		}
	}

	function istasyonGizle(id){
		$(id).hide(50);
	}
</script>

<div class="content-wrapper">
	<section class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4 offset-md-3">
					<div class="info-box bg-gradient-info mx-auto">
						<span class="badge bg-secondary badge-number-sum"><br><span id = "toplam">0</span></span>

						<div class="info-box-content">
							<span class="info-box-number"><h6>İş Tanımı : <?php echo $is_adi; ?></h6></span>
							<span class="info-box-text">Sipariş Adet : <?php echo number_format( $siparis_adet, 0, '', ',' ); ?></span>
							<span class="info-box-text">Günlük Hedef : <?php echo $gunluk_hedef; ?></span>

							<div class="progress">
								<div class="progress-bar" style="width: 1%"></div>
							</div>
							<span class="progress-description">
								<span id = "tamamlanan_yuzde">Tamamlanan : %0</span>
							</span>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-4"> 
					<div class="timeline">

					<?php
						foreach( $makinalar[ 2 ] as $makina ) {
						$personel_resim		= !is_null( $makina[ "personel_resim" ] ) ? "personel_resimler/" .  $makina[ "personel_resim" ] : "resimler/resim_yok.jpg";
						$div_id				= "istasyon_" . $makina[ "id" ];
						$hedef_id			= "hedef_" . $makina[ "id" ];
						$tamamlanan_id		= "tamamlanan_" . $makina[ "id" ];

						$hedef_buyuk_id			= "hedef_buyuk_" . $makina[ "id" ];
						$tamamlanan_buyuk_id	= "tamamlanan_buyuk_" . $makina[ "id" ];

						$son_kesim_saati_id			= "son_kesim_saati_" . $makina[ "id" ];
						$son_kesim_saati_card_id	= "son_kesim_saati_card_" . $makina[ "id" ];
					?>
						<div class="time-label">
							<span class="bg-default border" ><a style = "cursor: pointer;" class = "wait" onclick = "istasyonGoster('<?php echo "#" . $div_id; ?>')"> <?php echo $makina[ "personel_adi_soyadi" ]; ?></a></span>
						</div>
						<div>
							<div>
								<img class=" img-circle elevation-2" style="height:40px; height:40px;margin-bottom: px;margin-left: -13px;" src="<?php echo $personel_resim; ?>">&nbsp;&nbsp;&nbsp;
								<span>
									<span class="badge bg-danger badge-number" id = "<?php echo $tamamlanan_id; ?>">0</span>
								<!--	<span class="badge bg-secondary badge-number"><?php echo $gunluk_hedef; ?></span> -->
									<span class="badge bg-warning badge-number" id = "<?php echo $son_kesim_saati_id; ?>">00:00</span>
								</span>
							</div>
							<div class="timeline-item" id = "<?php echo $div_id; ?>" style = "display:none;">
								<span class="time">
									<i class="fas fa-clock"></i> <span id = "<?php echo $son_kesim_saati_card_id; ?>">00:00</span>
									<button type="button" class="btn btn-tool"><i class="fas fa-times fa-lg" onclick = "istasyonGizle('<?php echo "#" . $div_id; ?>')"></i></button>
								</span>
								<h3 class="timeline-header"><?php echo $is_adi; ?> - <?php echo $makina[ "is_parca_adi" ]; ?></h3>
								<div class="timeline-body">
									<div class="card card-widget widget-user-2">
										<!-- Add the bg color to the header using any of the bg-* classes -->
										<div class="widget-user-header bg-success">
											<h6><?php echo "#" . $makina[ "sayac_no" ]; ?> <?php echo $makina[ "personel_adi_soyadi" ]; ?></h6>
										</div>
										<div class="card-footer p-0">
											<ul class="nav flex-column">
												<li class="nav-item">
													<a href="#" class="nav-link">
													Tamamlanan <span class="float-right badge bg-danger badge-number-detail" id = "<?php echo $tamamlanan_buyuk_id; ?>">0</span>
													</a>
												</li>
												<li class="nav-item">
													<a href="#" class="nav-link">
													Hedef(Günlük) <span class="float-right badge bg-secondary badge-number-detail"><?php echo $gunluk_hedef; ?></span>
													</a>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					</div>
				</div>

			</div>
		</div>
	</section>
</div>

<script>

function veri_getir() {
		$.ajax( {
			 url		: "_modul/uretim_sistemi/uretim_hatti_AJAX.php"
			,type		: "GET"
			,dataType	: "json"
			,success	: function( data ) {
				let sonuclar = data.sonuclar;
				for( var i = 0; i < sonuclar.length; i++ ) {
					
					let makina_id		= sonuclar[ i ][ "makina_id" ];
					let tamamlanan		= sonuclar[ i ][ "tamamlanan" ];
					let son_kesim_saati	= sonuclar[ i ][ "son_kesim_saati" ];

					//$( "#hedef_" + makina_id ).text( "890" );
					$( "#tamamlanan_" + makina_id ).text( tamamlanan );
					//$( "#hedef_buyuk_" + makina_id ).text( "890" );
					$( "#tamamlanan_buyuk_" + makina_id ).text( tamamlanan );
					$( "#son_kesim_saati_" + makina_id ).text( son_kesim_saati );
					$( "#son_kesim_saati_card_" + makina_id ).text( son_kesim_saati );
				}
				$( "#toplam" ).text( data.toplam );
				$( "#tamamlanan_yuzde" ).text( "Tamamlanan :  %" + data.tamamlanan_yuzde );
				let style = "width: " + data.tamamlanan_yuzde + "%";
				$( ".progress-bar" ).prop( 'style', style );
			}
			,error: function( xhr, status, error ) {
				console.log( xhr, status, error );
			}
		} );
	}

$( document ).ready( function() {
	veri_getir();
	setInterval( veri_getir, 3000 );
} );

</script>