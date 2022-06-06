<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();


/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'tarife_id' ]			= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$islem			= array_key_exists( 'islem'			,$_REQUEST ) ? $_REQUEST[ 'islem' ]			: 'ekle';
$tarife_id		= array_key_exists( 'tarife_id'		,$_REQUEST ) ? $_REQUEST[ 'tarife_id' ]		: 0;


$satir_renk				= $tarife_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $tarife_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $tarife_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';



$SQL_tum_tarife_oku = <<< SQL
SELECT 
	t.id,
	t.adi,
	(select 
		COUNT(id) 
	FROM tb_molalar 
	WHERE  
		t.id = tb_molalar.tarife_id ) AS molaSayisi
FROM 
	tb_tarifeler AS t
WHERE t.firma_id =2 AND aktif = 1 
SQL;

/*Tarifeye Ait Molaları Getirme*/
$SQL_mola_getir = <<< SQL
SELECT 
	*
FROM 
	tb_molalar
WHERE 
	tarife_id	= ?
SQL;


$SQL_personel_ozluk_dosyalari = <<< SQL
SELECT
	 ot.adi
	,od.dosya
FROM
	tb_personel_ozluk_dosyalari AS od
JOIN
	tb_personel_ozluk_dosya_turleri AS ot ON od.dosya_turu_id = ot.id
WHERE
	od.tarife_id = ?
SQL;


$tarifeler					= $vt->select( $SQL_tum_tarife_oku, array($_SESSION['firma_id'] ) )[ 2 ];

//Günlük En fazla Mola Sayısı
foreach($tarifeler AS $mola){
	$molaSayisi[] = $mola[ "molaSayisi" ]; 
}
$molaSayisi = max($molaSayisi);

$tarifeyeAitmolaGetir = $vt->select( $SQL_mola_getir, array( $tarife_id ) )[ 2 ];

?>

<div class="modal fade" id="sil_onay">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Lütfen Dikkat</h4>
			</div>
			<div class="modal-body">
				<p>Bu kaydı silmek istediğinize emin misiniz?</p>
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
				<a class="btn btn-danger btn-evet">Evet</a>
			</div>
		</div>
	</div>
</div>

<script>
	/* Kayıt silme onay modal açar. */
	$( '#sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-7">
				<div class="card card-secondary" id = "card_personeller">
					<div class="card-header">
						<h3 class="card-title">molalar</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir tarife ekle" href = "?modul=molalar&islem=ekle" class="btn btn-tool" ><i class="fas fa-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personeller" class="table table-bordered table-hover table-sm" width = "100%" >
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Adı</th>
									<?php 
										$molaSay = 1;
										while ($molaSay <= $molaSayisi) {
											echo '<th>'.$molaSay.'. Mola</th>';
											$molaSay++;
										}
									?>
									<th data-priority="1" style="width: 20px">Düzenle</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $tarifeler AS $tarife ) { ?>
								<tr oncontextmenu="fun();" class ="personel-Tr <?php if( $tarife[ 'id' ] == $tarife_id ) echo $satir_renk; ?>" data-id="<?php echo $tarife[ 'id' ]; ?>">
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $tarife[ 'adi' ]; ?></td>
									<?php 
										$molaGetir = $vt->select( $SQL_mola_getir, array( $tarife[ "id" ] ) )[ 2 ];
										$tarifeMolaSayisi = count( $molaGetir );
										foreach ($molaGetir as $mola) {
											echo '<td>'.date( "H:i", strtotime( $mola[ "mola_baslangic" ] ) ).'-'.date( "H:i", strtotime( $mola[ "mola_bitis" ] ) ).'</td>';
										}

										$boslukSay = 0;
										$bosluk = $molaSayisi - $tarifeMolaSayisi;
										while ( $boslukSay < $bosluk) {
											echo '<td>-</td>';
											$boslukSay++;	
										}
									?>
									
									<td align = "center">
										<a modul = 'molalar' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=molalar&islem=guncelle&tarife_id=<?php echo $tarife[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-5">
				<div class="card <?php if( $tarife_id == 0 ) echo 'card-secondary' ?>">
					<div class="card-header p-2">
						<ul class="nav nav-pills tab-container">
							<?php if( $tarife_id > 0 ) { ?>
								<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Tarife Düzenle</h6>
							<?php } else {
								echo "<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Tarife Ekle</h6>";
								}
							?>
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<!-- GENEL BİLGİLER -->
							<div class="tab-pane active" id="_genel">
								<form class="form-horizontal" action = "_modul/molalar/molalarSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "tarife_id" value = "<?php echo $tarife_id; ?>">
									<div class="molaSatirlari">
									<?php 
										$molaSay = 1;
										if ( count( $tarifeyeAitmolaGetir ) > 0 ){
											foreach ($tarifeyeAitmolaGetir as $mola) {
												echo '<div class="row mola">
														<div class="col-sm-1">
															<div class="form-group">
																<label class="control-label">Mola</label><br>
																<span href="" class="btn btn-default">'.$molaSay.'</span>
															</div>
														</div>
														<div class="col-sm-5">
															<div class="form-group">
																<label class="control-label">Mesai Başlangıc Saati</label>
																<input type="text" class="form-control" name ="mola_baslangic[]" value = "'.date( "H:i", strtotime($mola[ "mola_baslangic" ] ) ).'" required placeholder="Örk: 08:00 ">
															</div>
														</div>
														<div class="col-sm-5">
															<div class="form-group">
																<label class="control-label">Mesai Bitis Saati</label>
																<input type="text" class="form-control" name ="mola_bitis[]" value = "'.date( "H:i", strtotime( $mola[ "mola_bitis" ] ) ).'" required placeholder="Örk: 18:30 ">
															</div>
														</div>
														<div class="col-sm-1">
															<div class="form-group">
																<label class="control-label">Sil</label><br>
																<a modul= "molalar" yetki_islem="sil" class="btn btn-danger" data-href="_modul/molalar/molalarSEG.php?islem=sil&tarife_id='.$tarife_id.'&mola_id='.$mola[ 'id' ].'" data-toggle="modal" data-target="#sil_onay"><i class="fas fa-trash"></i></a>
															</div>
														</div>
													</div>';
												$molaSay++;
											}
											$sonMola = count($tarifeyeAitmolaGetir);

										}else{
											echo '<div class="row mola">
													<div class="col-sm-1">
														<div class="form-group">
															<label class="control-label">Mola</label><br>
															<span href="" class="btn btn-default">1</span>
														</div>
													</div>
													<div class="col-sm-5">
														<div class="form-group">
															<label class="control-label">Mesai Başlangıc Saati</label>
															<input type="text" class="form-control" name ="mola_baslangic[]" required placeholder="Örk: 08:00 ">
														</div>
													</div>
													<div class="col-sm-5">
														<div class="form-group">
															<label class="control-label">Mesai Bitis Saati</label>
															<input type="text" class="form-control" name ="mola_bitis[]"  required placeholder="Örk: 18:30 ">
														</div>
													</div>
													<div class="col-sm-1">
														<div class="form-group">
															<label class="control-label">Sil</label><br>
															<span class="btn btn-danger" id="yenisil"><i class="fas fa-trash"></i></span>
														</div>
													</div>
												</div>';
												$sonMola = $molaSayisi;
										}

									?>
									</div>
									<div class="card-footer">
										<button modul= 'personel' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
										<span class=" btn btn-info float-right" id="MolaSatirEkle" data-sayi="<?php echo $sonMola; ?>">Mola Ekle</span>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">

	var simdi = new Date(); 
	//var simdi="11/25/2015 15:58";
	$(function () {
		$('#datetimepicker1').datetimepicker({
			//defaultDate: simdi,
			format: 'DD.MM.yyyy',
			icons: {
			time: "far fa-clock",
			date: "fa fa-calendar",
			up: "fa fa-arrow-up",
			down: "fa fa-arrow-down"
			}
		});
	});

	$( "body" ).on('click', '#MolaSatirEkle', function() {
		var molasatirSay = 0;
		$(".mola").each(function() {
	      	molasatirSay = molasatirSay + 1;
	    });
	    molasatirSay = molasatirSay + 1;

		var ekleneceksatir = '<div class="row mola"><div class="col-sm-1"><div class="form-group"><label class="control-label">Mola</label><br><span href="" class="btn btn-default">'+molasatirSay+'</span></div></div><div class="col-sm-5"><div class="form-group"><label class="control-label">Mesai Başlangıc Saati</label><input type="text" class="form-control" name ="mola_baslangic[]" required placeholder="Örk: 08:00 "></div></div><div class="col-sm-5"><div class="form-group"><label class="control-label">Mesai Bitis Saati</label><input type="text" class="form-control" name ="mola_bitis[]" required placeholder="Örk: 18:30 "></div></div><div class="col-sm-1"><div class="form-group"><label class="control-label">Sil</label><br><span class="btn btn-danger" id="yenisil"><i class="fas fa-trash"></i></span></div></div></div>';
		
		document.getElementById("MolaSatirEkle").removeAttribute("data-sayi");
		document.getElementById("MolaSatirEkle").setAttribute("data-sayi", molasatirSay); 
		$(".molaSatirlari").append(ekleneceksatir);
	})

	/*Tıklanan Mola Satırı Siliyoruz*/
	$('.row').on("click", "#yenisil", function (e) {
        e.preventDefault();
        $(this).closest(".mola").remove();

    });

$(function () {
	$('#datetimepicker2').datetimepicker({
		//defaultDate: simdi,
		format: 'DD.MM.yyyy',
		icons: {
		time: "far fa-clock",
		date: "fa fa-calendar",
		up: "fa fa-arrow-up",
		down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$(":input").inputmask();

	//Initialize Select2 Elements
	$('.select2').select2()

	//Initialize Select2 Elements
	$('.select2bs4').select2({
	  theme: 'bootstrap4'
	})
})

$( function () {
	$( '[ data-toggle="tooltip" ]' ).tooltip()
} );


var tbl_personeller = $( "#tbl_personeller" ).DataTable( {
	"responsive": true, "lengthChange": true, "autoWidth": true,
	"stateSave": true,
	"pageLength" : 25,
	//"buttons": ["excel", "pdf", "print","colvis"],

	buttons : [
		{
			extend	: 'colvis',
			text	: "Alan Seçiniz"
			
		},
		{
			extend	: 'excel',
			text 	: 'Excel',
			exportOptions: {
				columns: ':visible'
			},
			title: function(){
				return "Tarife Listesi";
			}
		},
		{
			extend	: 'print',
			text	: 'Yazdır',
			exportOptions : {
				columns : ':visible'
			},
			title: function(){
				return "Tarife Listesi";
			}
		}
	],
	"language": {
		"decimal"			: "",
		"emptyTable"		: "Gösterilecek kayıt yok!",
		"info"				: "Toplam _TOTAL_ kayıttan _START_ ve _END_ arası gösteriliyor",
		"infoEmpty"			: "Toplam 0 kayıttan 0 ve 0 arası gösteriliyor",
		"infoFiltered"		: "",
		"infoPostFix"		: "",
		"thousands"			: ",",
		"lengthMenu"		: "Show _MENU_ entries",
		"loadingRecords"	: "Yükleniyor...",
		"processing"		: "İşleniyor...",
		"search"			: "Ara:",
		"zeroRecords"		: "Eşleşen kayıt bulunamadı!",
		"paginate"			: {
			"first"		: "İlk",
			"last"		: "Son",
			"next"		: "Sonraki",
			"previous"	: "Önceki"
		}
	}
} ).buttons().container().appendTo('#tbl_personeller_wrapper .col-md-6:eq(0)');



$('#card_personeller').on('maximized.lte.cardwidget', function() {
	var tbl_personeller = $( "#tbl_personeller" ).DataTable();
	var column = tbl_personeller.column(  tbl_personeller.column.length - 1 );
	column.visible( ! column.visible() );
	var column = tbl_personeller.column(  tbl_personeller.column.length - 2 );
	column.visible( ! column.visible() );
});

$('#card_personeller').on('minimized.lte.cardwidget', function() {
	var tbl_personeller = $( "#tbl_personeller" ).DataTable();
	var column = tbl_personeller.column(  tbl_personeller.column.length - 1 );
	column.visible( ! column.visible() );
	var column = tbl_personeller.column(  tbl_personeller.column.length - 2 );
	column.visible( ! column.visible() );
} );


</script>