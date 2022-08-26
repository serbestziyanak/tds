<?php
$fn = new Fonksiyonlar();

$islem          					= array_key_exists( 'islem', $_REQUEST )  ? $_REQUEST[ 'islem' ] 	: 'ekle';

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj                 			= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu            			= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$donem_desleri_id	= array_key_exists( 'donem_desleri_id'	,$_REQUEST ) ? $_REQUEST[ 'donem_desleri_id' ]	: 0;

//bolume Ait bölüleri getirme
$SQL_programlar = <<< SQL
SELECT
	*
FROM
	tb_programlar
WHERE 
	universite_id = ? AND
	aktif 	 = 1
SQL;

$SQL_ders_yillari_getir = <<< SQL
SELECT
	*
FROM
	tb_ders_yillari
WHERE
	universite_id = ? AND
	aktif 		  = 1
SQL;

$SQL_donemler_getir = <<< SQL
SELECT
	*
FROM
	tb_donemler
WHERE
	universite_id 	= ? AND
	program_id 		= ? AND
	aktif 		  	= 1
SQL;

$SQL_dersler_getir = <<< SQL
SELECT 
	dd.ders_id,
	dd.teorik_ders_saati,
	dd.uygulama_ders_saati,
	d.adi
FROM  
	tb_donem_dersleri as dd
LEFT JOIN tb_dersler  as d ON d.id = dd.ders_id
LEFT JOIN tb_ders_yili_donemleri as dyd ON dyd.id = dd.ders_yili_donem_id
WHERE 
	dyd.ders_yili_id  	=? AND
	dyd.program_id 		= ? AND 
	dyd.donem_id 		= ?
SQL;




		

$ders_yillari		= $vt->select( $SQL_ders_yillari_getir, array($_SESSION[ 'universite_id' ] ) )[ 2 ];
$programlar			= $vt->select( $SQL_programlar, array( $_SESSION[ 'universite_id' ] ) )[ 2 ];

?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="sil_onay">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Lütfen Dikkat!</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p><b>Bu kategoriyi sildiğinizde kategori altındaki alt kategoriler de silinecektir.</b></p>
				<p>Bu kaydı <b>Silmek</b> istediğinize emin misiniz?</p>
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn btn-success" data-dismiss="modal">İptal</button>
				<a type="button" class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<script>
	$( '#sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

<script>  
	$(document).ready(function() {
		$('#limit-belirle').change(function() {

			$(this).closest('form').submit();

		});
	});
</script>
<div class="row">
	<!-- left column -->
	<div class="col-md-5">
		<!-- general form elements -->
		<div class="card card-secondary">
			<div class="card-header">
				<h3 class="card-title">Dönem Dersi Ekle / Güncelle</h3>
			</div>
			<!-- /.card-header -->
			<!-- form start -->
			<form id = "kayit_formu" action = "_modul/donemDersleri/donemDersleriSEG.php" method = "POST">
				<div class="card-body">
					<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
					<div class="form-group">
						<label  class="control-label">Program</label>
						<select class="form-control select2" name = "program_id" id="program-sec" data-url="./_modul/ajax/ajax_data.php" data-islem="dersYillariListe" required>
							<option>Seçiniz...</option>
							<?php 
								foreach( $programlar AS $program ){
									echo '<option value="'.$program[ "id" ].'" '.($program[ "program_id" ] == $program[ "id" ] ? "selected" : null) .'>'.$program[ "adi" ].'</option>';
								}

							?>
						</select>
					</div>
					<div class="form-group" id="dersYillari"> </div>
					<div class="form-group" id="donemListesi"> </div>
					<div class="form-group" id="dersler"> </div>

					
				</div>
				<!-- /.card-body -->
				<div class="card-footer">
					<button modul= 'programlar' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
					<button onclick="window.location.href = '?modul=programlar&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
				</div>
			</form>
		</div>
		<!-- /.card -->
	</div>
	<!--/.col (left) -->
	<div class="col-md-7">
		<div class="card card-secondary">
			<div class="card-header">
				<h3 class="card-title">Dönem Dersleri</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body p-0">

				<ul class="tree">
				<?php  
					/*DERS Yılıllarını Getiriyoruz*/
					$ders_yillari = $vt->select( $SQL_ders_yillari_getir, array( $_SESSION[ "universite_id" ] ) )[2];

					foreach ($ders_yillari as $ders_yili ) { ?>
						
						<li><div class="sticky"><?php  echo $ders_yili[ "adi" ]; ?></div>
						<ul>
				<?php 
						/*Programların  Listesi*/
						$programlar = $vt->select( $SQL_programlar, array( $_SESSION[ "universite_id" ] ) )[2];
						foreach ($programlar as $program) { ?>
							
							<!-- Programlar -->
							<li><div class="sticky"><?php echo $program[ "adi" ] ?></div> <!-- Second level node -->
							<ul class="ders-ul">
				<?php 		
							/*Dönemler Listesi*/
							$donemler = $vt->select( $SQL_donemler_getir, array( $_SESSION[ "universite_id" ], $program[ "id" ] ) )[2];
							foreach ( $donemler AS $donem ){ ?>
								<!--Dönemler-->
								<li>
									<div class="ders-kapsa">
										<?php echo $donem[ "adi" ]  ?>
										<a href="?modul=donemDersleri&ders_yili_id=<?php echo $ders_yili[ 'id' ] ?>&program_id=<?php echo $program[ 'id' ] ?>&donem_id=<?php echo $donem[ 'id' ] ?>" class="btn btn-warning float-right btn-xs">Düzenle</a>
									</div>
								<ul class="ders-ul">
				<?php 
								/*Ders Listesi*/
								$dersler = $vt->select( $SQL_dersler_getir, array( $ders_yili[ "id" ], $program[ "id" ], $donem[ "id" ] ) )[2];
								foreach ( $dersler as $ders ) { ?>
									<li><div class="ders-kapsa"><?php echo $ders[ "adi" ];   ?></div></li>				
				<?php			
								}
								echo '</ul></li>';
							}
							echo '</ul></li>';
						}
						echo '</ul></li>';
					} 
				?>
				</ul>
			</div>
			<!-- /.card -->
		</div>
		<!-- right column -->
	</div>

<script type="text/javascript">
	
	$('#program-sec').on("change", function(e) { 
	    var $program_id 		= $(this).val();
	    var $data_islem = $(this).data("islem");
	    var $data_url 	= $(this).data("url");
	    $("#dersYillari").empty();
	    $.post($data_url, { islem : $data_islem,program_id : $program_id}, function (response) {
	        $("#dersYillari").append(response);
	    });
	});	
</script>
