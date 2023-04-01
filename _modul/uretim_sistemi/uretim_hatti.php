<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$SQL_aktif_is = <<<SQL
SELECT
	 id
	,adi
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
SQL;


/* Aktif olan işin bilgileri */
$aktif_is = $vt->select( $SQL_aktif_is );
$is_id	= $aktif_is[ 2 ][ 0 ][ "id" ];
$is_adi	= $aktif_is[ 2 ][ 0 ][ "adi" ];
/**/


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

.badge-number-detail{
	font-size: 1.6em;
	margin-bottom:5px;
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
			$(".timeline-item").hide(100);
		}
	});


	$(document).keyup(function(e) {
		if (e.ctrlKey && e.keyCode == 13) {
			$(".timeline-item").show(100);
		}
	});

	function istasyonGoster(id){
		if ( $(id).is(':visible') ) {
			$(id).hide(100);
		} else {
			$(id).show(100);
		}
	}

	function istasyonGizle(id){
		$(id).hide(100);
	}
</script>

<div class="content-wrapper">
	<section class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4"> 
					<div class="timeline">
					
					<?php
						foreach( $makinalar[ 2 ] as $makina ) {
						$personel_resim		= !is_null( $makina[ "personel_resim" ] ) ? "resimler/" .  $makina[ "personel_resim" ] : "resimler/resim_yok.jpg";
						$div_id				= "istasyon_" . $makina[ "id" ];
						$hedef_id			= "hedef_" . $makina[ "id" ];
						$tamamlanan_id		= "tamamlanan_" . $makina[ "id" ];
						
						$hedef_buyuk_id			= "hedef_buyuk_" . $makina[ "id" ];
						$tamamlanan_buyuk_id	= "tamamlanan_buyuk_" . $makina[ "id" ];
						
						$son_kesim_saati_id	= "son_kesim_saati_" . $makina[ "id" ];
					?>
						<div class="time-label">
							<span class="bg-default"><a href = "#" onclick = "istasyonGoster('<?php echo "#" . $div_id; ?>')"> <?php echo $makina[ "personel_adi_soyadi" ]; ?></a></span>
						</div>
						<div>
							<div>
								<img class=" img-circle elevation-2" style="height:35px;" src="<?php echo $personel_resim; ?>">&nbsp;
								<span class = "pt-3">
									<span class="badge bg-danger badge-number" id = "<?php echo $tamamlanan_id; ?>">0</span>
									<span class="badge bg-secondary badge-number " id = "<?php echo $hedef_id; ?>">0</span>
								</span>
							</div>
							<div class="timeline-item" id = "<?php echo $div_id; ?>" style = "display:none;">
								<span class="time">
									<i class="fas fa-clock"></i> <span id = "<?php echo $son_kesim_saati_id; ?>">00:00</span>
									<button type="button" class="btn btn-tool"><i class="fas fa-times fa-lg" onclick = "istasyonGizle('<?php echo "#" . $div_id; ?>')"></i></button>
								</span>
								<h3 class="timeline-header"><a href="#"><?php echo $makina[ "is_parca_adi" ]; ?></a> <?php echo $is_adi; ?></h3>
								<div class="timeline-body">
									<div class="card card-widget widget-user-2">
										<!-- Add the bg color to the header using any of the bg-* classes -->
										<div class="widget-user-header bg-success">
											<div class="widget-user-image">
												<img class="img-circle elevation-2" src="<?php echo $personel_resim; ?>" alt="User Avatar">
											</div>
											<!-- /.widget-user-image -->
											<h3 class="widget-user-username"><?php echo $makina[ "personel_adi_soyadi" ]; ?></h3>
											<h5 class="widget-user-desc"><?php echo "#" . $makina[ "sayac_no" ]; ?></h5>
										</div>
										<div class="card-footer p-0">
											<ul class="nav flex-column">
												<li class="nav-item">
													<a href="#" class="nav-link">
													Hedef(Günlük) <span class="float-right badge bg-danger badge-number-detail" id = "<?php echo $tamamlanan_buyuk_id; ?>">0</span>
													</a>
												</li>
												<li class="nav-item">
													<a href="#" class="nav-link">
													Tamamlanan <span class="float-right badge bg-secondary badge-number-detail" id = "<?php echo $hedef_buyuk_id; ?>">0</span>
													</a>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
						<br/>
					<?php } ?>
		
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<script>

$( document ).ready( function() {
	setInterval( function() {
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

					$( "#hedef_" + makina_id ).text( "890" );
					$( "#tamamlanan_" + makina_id ).text( tamamlanan );
					$( "#hedef_buyuk_" + makina_id ).text( "890" );
					$( "#tamamlanan_buyuk_" + makina_id ).text( tamamlanan );
					$( "#son_kesim_saati_" + makina_id ).text( son_kesim_saati );
				}
			}
			,error: function( xhr, status, error ) {
				console.log( xhr, status, error );
			}
		} );
	}, 5000 );
} );

</script>