<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$islem = array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';

$SQL_tum_personel_oku = <<< SQL
SELECT
	 tb_personel.id
	,CONCAT( adi, " ", soyadi ) as adi
	,ozluk_dosya_durumu
	,(select COUNT(tb_personel_ozluk_dosyalari.id) 
		FROM tb_personel_ozluk_dosyalari 
		WHERE tb_personel_ozluk_dosyalari.personel_id = tb_personel.id 
		GROUP BY personel_id) AS dosyaSayisi
FROM
	tb_personel
WHERE
	aktif = 1
SQL;

$SQL_tek_personel_oku = <<< SQL
SELECT
	id
	,adi,
	soyadi,
	ozluk_dosya_durumu,
	(select COUNT(tb_personel_ozluk_dosyalari.id) 
		FROM tb_personel_ozluk_dosyalari 
		WHERE tb_personel_ozluk_dosyalari.personel_id = tb_personel.id  
		GROUP BY personel_id) AS dosyaSayisi
FROM
	tb_personel
WHERE
	id = ?
SQL;

$SQL_personel_ozluk_dosyalari = <<< SQL
SELECT
	 od.id
	,od.dosya_turu_id
	,ot.adi 
	,od.dosya
FROM
	tb_personel_ozluk_dosyalari AS od
JOIN
	tb_personel_ozluk_dosya_turleri AS ot ON od.dosya_turu_id = ot.id
WHERE
	od.personel_id = ?
SQL;

//Personele ait tutanaklar lisetsi
$SQL_personel_tutanaklari = <<< SQL
SELECT
	t.id as tutanak_id,
	t.tarih,
	t.tip,
	t.saat,
	td.aciklama,
	td.dosya,
	td.id as dosya_id
FROM 
	tb_tutanak AS t 
INNER JOIN 
	tb_tutanak_dosyalari AS td ON t.id = td.tutanak_id
WHERE 
	t.firma_id 		= ? AND
	t.personel_id 	= ?
ORDER BY t.tarih ASC,  t.id ASC
SQL;

$SQL_personel_ozluk_dosya_turleri = <<< SQL
SELECT
	*
FROM
	tb_personel_ozluk_dosya_turleri
SQL;



$personeller					= $vt->select( $SQL_tum_personel_oku, array() );
$personel_id					= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];
$tek_personel					= $vt->select( $SQL_tek_personel_oku, array( $personel_id ) )[ 2 ][ 0 ];
$personel_ozluk_dosyalari		= $vt->select( $SQL_personel_ozluk_dosyalari, array( $personel_id ) )[2];
$personel_tutanaklari			= $vt->select( $SQL_personel_tutanaklari, array( $_SESSION['firma_id'], $personel_id ) )[2];
$personel_ozluk_dosya_turleri	= $vt->select( $SQL_personel_ozluk_dosya_turleri, array() );

//??zl??k Dosyas?? ????in ??stanilen Evrak Say??s?? 
$personel_ozluk_dosya_turleri_sayisi = $personel_ozluk_dosya_turleri[3];
$satir_renk				= $personel_id > 0	? 'table-warning' : '';

$personel_ozluk_dosyalari_idleri = array();
foreach( $personel_ozluk_dosyalari as $dosya ) $personel_ozluk_dosyalari_idleri[] = $dosya[ 'dosya_turu_id' ];

?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="kayit_sil" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">L??tfen Dikkat!</h4>
			</div>
			<div class="modal-body">
				Bu kayd?? <b>Silmek</b> istedi??inize emin misiniz?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">??ptal</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	$( '#kayit_sil' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class = "col-md-4">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Personel Se??in</h3>
					</div>
					<div class="card-body">
						<table id="tbl_personelOzlukDosyalari" class="table table-sm table-bordered table-hover">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Ad??</th>
									<th style="width: 60px"> Eksik D.S.</th>
									<th data-priority=" 1" style="width: 20px">D??zenle</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1;  foreach( $personeller[ 2 ] AS $personel ) { 
									$evraklarBtnRenk = $personel[ "ozluk_dosya_durumu" ] == 1 ? 'success' : 'warning'; 
									$dosya_durumu = $personel[ "ozluk_dosya_durumu" ] == 1 ? 'Tamam' : 'Eksik'; 
								?>
								<tr  <?php if( $personel[ 'id' ] == $personel_id ) echo "class = '$satir_renk'";?>>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $personel[ 'adi' ]; ?></td>
									<td><?php echo $dosya_durumu; ?></td>
									<td align = "center">
									<a modul = 'firmalar' yetki_islem="evraklar" class = "btn btn-sm btn-<?php echo $evraklarBtnRenk; ?> btn-xs" href = "?modul=personelOzlukDosyalari&islem=guncelle&personel_id=<?php echo $personel[ 'id' ]; ?>" >
										Evraklar
									</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class = "col-md-8">
				<div class="card card-light">
					<div class="card-header">
						<h3 class="card-title">
							<?php echo $tek_personel[ 'adi' ] . " " . $tek_personel[ 'soyadi' ]; ?> - ??zl??k Dosyas?? Ekle
						</h3>
						<div class="card-tools">
							<div class="icheck-success">
								Personele Ait Dosyalar Tamamland?? &nbsp;
								<input type="checkbox" <?php echo $tek_personel[ 'ozluk_dosya_durumu' ] == 1 ? 'checked' : ''; ?>  onclick="dosyaDurumu(this,<?php echo $personel_id; ?>);" id="dosyaDurumu" >
								<label for="dosyaDurumu"  ></label>
							</div>
						</div>
					</div>
					<div class="card-body">
						<?php foreach( $personel_ozluk_dosya_turleri[ 2 ] AS $dosya_turu ) { ?>
							<form action = "_modul/personelOzlukDosyalari/personelOzlukDosyalariSEG.php" method = "POST" enctype="multipart/form-data">
								<div class="form-group">
									<label for="exampleInputFile"><?php echo $dosya_turu[ 'adi' ]; ?></label>
									<div class="input-group">
										<?php 
											if(in_array($dosya_turu["id"], $personel_ozluk_dosyalari_idleri)){
												$buttonRenk  = 'success';
												$buttonYazi = "G??ncelle";
											}else{
												$buttonRenk  = 'danger';
												$buttonYazi = "Kaydet";
											}
										?>	
										<div class="custom-file">
											<input type="hidden" value="<?php echo $dosya_turu[ 'id' ]; ?>" name="dosya_turu_id">
											<input type="hidden" value="<?php echo $personel_id?>" name="personel_id">
											<label class="custom-file-label " id="label-<?php echo $dosya_turu[ 'id' ]; ?>" for="exampleInputFile">Dosya Se??</label>
											<input type="file" class="custom-file-input OzlukDosya " data-id="<?php echo $dosya_turu[ 'id' ]; ?>" id="OzlukDosya-<?php echo $dosya_turu[ 'id' ]; ?>" name = "OzlukDosya[]" multiple <?php echo $dosya_turu[ 'filtre' ]; ?>>
											
										</div>
										<div class="input-group-append">
											<button class="btn  btn-<?php echo  $buttonRenk; ?>" type = "submit"><?php echo $buttonYazi; ?></button>
										</div>
									</div>
								</div>
							</form>
						<?php } ?>
					</div>
				</div>
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title"><?php echo $tek_personel[ 'adi' ] . " " . $tek_personel[ 'soyadi' ]; ?> - ??zl??k Dosyalar??</h3>
					</div>
					<div class="card-body">
						<div class="card card-default">
							<div class="card-body">
								<div id="actions" class="row">
									<table class="table table-striped table-valign-middle">
										<tbody>
										<?php
												if( count( $personel_ozluk_dosyalari ) > 0 ) {
													foreach( $personel_ozluk_dosyalari AS $dosya ) { ?>
														<tr>
															<td>
																<?php echo $dosya[ 'adi' ]; ?>
															</td>
															<td align = "right" width = "5%">
																<a href = "personel_ozluk_dosyalari/<?php echo $dosya[ 'dosya' ]; ?>"
																	data-toggle="tooltip"
																	data-placement="left"
																	title="Dosyay?? ??ndir" target="_blank">
																	<i class = "fa fa-download"></i>
																	
																</a>
															</td>
															<td align = "right" width = "5%">
																<a href = "" 
																	modul = 'personelOzlukDosyalari' yetki_islem="sil"
																	data-href="_modul/personelOzlukDosyalari/personelOzlukDosyalariSEG.php?islem=sil&personel_id=<?php echo $personel_id; ?>&dosya_id=<?php echo $dosya[ 'id' ]; ?>"
																	data-target="#kayit_sil"
																	data-toggle="modal"
																	data-toggle="tooltip" 
																	data-placement="left" 
																	title="Dosyay?? Sil">
																	<i class = "fa fa-trash color:red"></i>
																</a>
															</td>
														</tr>
												<?php
													}
												} else { ?>
												<h6>Listelenecek kay??t bulunamad??!</h6>
											<?php } ?>
										</tbody>	
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card card-warning">
					<div class="card-header">
						<h3 class="card-title"><?php echo $tek_personel[ 'adi' ] . " " . $tek_personel[ 'soyadi' ]; ?> - Tutanaklar??</h3>
						<div class="card-tools">
	                        <button type="button" class="btn btn-outline-dark personel-Tr" data-durum="yeni" data-personel_id ="<?php echo $personel_id; ?>">
	                            <i class="fas fa-file"></i> &nbsp; Yeni Tutanak Ekle
	                        </button>
	                    </div>
					</div>
					<div class="card-body">
						<div class="card card-default">
							<div class="card-body">
								<div id="actions" class="row">
									<table class="table table-striped table-valign-middle">
										<tbody>
											<?php
											if( count( $personel_tutanaklari ) > 0 ) {
												foreach( $personel_tutanaklari AS $tutanak ) { ?>
													<tr>
														<td>
															<?php
																if ( $tutanak[ "aciklama" ]  != '' ) {
																	echo $tutanak[ "aciklama" ];
																}else{
																	echo $tutanak[ 'tarih' ].'tarihli'.$fn->islem_tipi_isim( $tutanak[ "tip" ] ).'Tutana????';
																}
															?>
														</td>
														<td align = "right" width = "20%">
															<button class="personel-Tr btn btn-dark"
																data-personel_id    = "<?php echo $personel_id; ?>"
																data-tutanak_id 	= "<?php echo $tutanak[ 'tutanak_id' ]; ?>"
																data-tarih 		    = "<?php echo $tutanak[ 'tarih' ]; ?>"
																data-saat 			= "<?php echo $tutanak[ 'saat' ]; ?>"
																data-tip			= "<?php echo $tutanak[ 'tip' ]; ?>"
																data-durum			= "eski">
																<i class = "fa fa-file"></i>&nbsp; Dosya Y??kle
															</button>
														</td>

														<td align = "right" width = "5%">
															<a href = "tutanak/<?php echo $personel_id; ?>/<?php echo $tutanak[ 'dosya' ]; ?>"
																data-toggle="tooltip"
																data-placement="top"
																title="Dosyay?? ??ndir" target="_blank">
																<i class = "fa fa-download"></i>

															</a>
														</td>
														<td align = "right" width = "5%">
															<a href = "" 
															modul = 'personelOzlukDosyalari' yetki_islem="sil"
															data-href="_modul/tutanakolustur/tutanakolusturSEG.php?islem=sil&personel_id=<?php echo $personel_id; ?>&dosya_id=<?php echo $tutanak[ 'dosya_id' ]; ?>&tutanak_id=<?php echo $tutanak[ 'tutanak_id' ]; ?>"
															data-target="#kayit_sil"
															data-toggle="modal"
															data-toggle="tooltip" 
															data-placement="left" 
															title="Dosyay?? Sil">
															<i class = "fa fa-trash color:red"></i>
														</a>
													</td>
												</tr>
												<?php
											}
										} else { ?>
											<h6>Listelenecek kay??t bulunamad??!</h6>
										<?php } ?>
										</tbody>	
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- dropzone modal -->
<div class="modal fade" id="dosyayukle" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel"><?php echo $tek_personel[ 'adi' ] . " " . $tek_personel[ 'soyadi' ]; ?> - Tutanak Dosya Y??kleme</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<div class="dropzonedosya" id="DosyaAlani" >
	                <div class="card-body" id="CardBody">
	                    <form enctype="multipart/form-data" method="POST"  name="mainFileUploader" class="" id="dropzonform">
	                    	<div class="form-group">
	                    		<input type="text" name="aciklama" id="aciklama" class="form-control" placeholder="Ac??klama K??sm??">
	                        </div>
	                        <div action = "_modul/tutanakolustur/tutanakolusturSEG.php" class="dropzone" id="dropzone" style="min-height: 247px;">
	                            <div class="dz-message">
	                                <h3 class="m-h-lg">Y??klemek istedi??iniz dosyalar?? buyara s??r??kleyiniz</h3>
	                                <p class="m-b-lg text-muted">(Y??klemek i??in dosyalar??n??z?? s??r??kleyiniz yada buraya t??klay??n??z)<br>En Fazla 10 Resim Birden Y??kleyebilirsiniz</p>
	                            </div>
	                        </div>
	                        <input type="hidden" name="personel_id" id="personel_id">
	                        <input type="hidden" name="tutanak_id" id="tutanak_id">
	                        <input type="hidden" name="tip" id="tip">
	                        <input type="hidden" name="tarih" id="tarih">
	                        <input type="hidden" name="saat" id="saat">
	                        <input type="hidden" name="durum" id="durum">
	                        <input type="hidden" name="islem" id="islem" value="dosyaekle">
	                        <a href="javascript:void(0);" class="btn btn-outline-info" style="margin-top:10px; width: 100%;" id="submit-all">Y??kle</a>
	                    </form>
		            </div>
		        </div> 
			</div>
		</div>
	</div>
</div>



<script>
	$('#tbl_personelOzlukDosyalari').DataTable({
	  "paging": true,
	  "lengthChange": true,
	  "searching": true,
	  "ordering": true,
	  "info": true,
	  "autoWidth": false,
	  "responsive": true,
	  'pageLength'	: 25,
	  'stateSave'	: true,
	  'language'		: {
		'url': '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Turkish.json'
		}
	});

	$('.OzlukDosya').change(function () {
		var id = $(this).data("id");
		$(this).prev('label').text("Dosyalar Se??ildi");
		document.querySelector("#label-"+id).style.backgroundColor = "#28a745";
		document.querySelector("#label-"+id).style.color = "#ffffff";
		document.querySelector("#label-"+id).style.fontWeight = "bold";
		
	});

    $( "body" ).on('click', '.personel-Tr', function() {

        $("#dosyayukle").modal("show");

        //Tablodaki t??m sat??rlar?? normale ceviriyoruzz  T??klanan sat??r?? arka plan??n?? warning yap??yoruz
        $(".personel-Tr").each(function() {
            $(this).removeClass("table-warning")
        });
        $(this).addClass("table-warning");

        //Sat??ra ait data verileri ??ekiyoruz
        var personel_id = $( this ).data( "personel_id" );
        var tutanak_id  = $( this ).data( "tutanak_id" );
        var tip         = $( this ).data( "tip" ); 
        var tarih       = $( this ).data( "tarih" ); 
        var saat        = $( this ).data( "saat" ); 
        var durum       = $( this ).data( "durum" ); 

        //Gelen verileri forma at??yoruz
        $( "#personel_id" ).val( personel_id );
        $( "#tutanak_id" ).val( tutanak_id );
        $( "#tip" ).val( tip );
        $( "#tarih" ).val( tarih );
        $( "#saat" ).val( saat );
        $( "#durum" ).val( durum );
        
    });

    function dosyaDurumu(cb,personel_id) {
    	var dosya_durumu;
	  	var personel_id 	= personel_id; 
	  	 cb.checked == true  ?  dosya_durumu 	= 1 : dosya_durumu 	= 0;

	  	var url         	= '_modul/personelOzlukDosyalari/personelOzlukDosyalariSEG.php?islem=dosyadurumu';
        $.ajax({
            type: "POST",
            url: url,
            data: 'personel_id=' + personel_id+'&dosya_durumu=' + dosya_durumu, 
            cache: false,
            success: function(response) {
                var response = JSON.parse(response);
                if ( response.sonuc == 'ok' ){
                    mesajVer('Personele Ait Dosya Durumu G??ncelllendi','yesil');
                }
            }

        })
	}
    

</script>