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
	t.baslangic_tarih,
	t.bitis_tarih,
	t.mesai_baslangic,
	t.mesai_bitis,
	t.min_calisma_saati,
	t.gun_donumu,
	mt.adi AS mesai_adi,
	g.adi AS grup_adi
FROM 
	tb_tarifeler AS t
INNER JOIN tb_mesai_turu AS mt ON 
	mt.id = t.mesai_turu
INNER JOIN tb_gruplar AS g ON 
	g.id = t.grup_id
WHERE 
	t.firma_id 	= ? AND
	t.aktif 	= 1
SQL;


$SQL_tek_tarife_oku = <<< SQL
SELECT 
	t.*,
	mt.adi AS mesai_adi,
	g.adi AS grup_adi
FROM 
	tb_tarifeler AS t
INNER JOIN tb_mesai_turu AS mt ON 
	mt.id 	= t.mesai_turu
INNER JOIN tb_gruplar AS g ON 
	g.id 	= t.grup_id
WHERE 
	t.id 		= ? AND
	t.firma_id 	= ? AND
	t.aktif 	= 1 
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



/* Sabit tablolar */
$SQL_gruplar = <<< SQL
SELECT
	*
FROM
	tb_gruplar
WHERE
	aktif = 1
SQL;

/* Sabit tablolar */
$SQL_mesai_turleri = <<< SQL
SELECT
	*
FROM
	tb_mesai_turu
WHERE
	firma_id = ?
SQL;



$tarifeler					= $vt->select( $SQL_tum_tarife_oku, array($_SESSION['firma_id'] ) )[ 2 ];
$tek_tarife					= $vt->select( $SQL_tek_tarife_oku, array( $tarife_id, $_SESSION['firma_id'] ) )[ 2 ][ 0 ];
$gruplar					= $vt->select( $SQL_gruplar			,array() )[ 2 ];
$mesai_turleri				= $vt->select( $SQL_mesai_turleri	,array( $_SESSION['firma_id'] ) )[ 2 ];


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
			<div class="col-md-8">
				<div class="card card-secondary" id = "card_personeller">
					<div class="card-header">
						<h3 class="card-title">Tarifeler</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir tarife ekle" href = "?modul=tarifeler&islem=ekle" class="btn btn-tool" ><i class="fas fa-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personeller" class="table table-bordered table-hover table-sm" width = "100%" >
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Adı</th>
									<th>Grup</th>
									<th>Mesai Türü</th>
									<th>Baş. Tar.</th>
									<th>Bit. Tar.</th>
									<th>Mesai Baş.</th>
									<th>Mesai Bit.</th>
									<th>min. Çal. S.</th>
									<th>Gün Dön.</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
									<th data-priority="1" style="width: 20px">Sil</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $tarifeler AS $tarife ) { ?>
								<tr oncontextmenu="fun();" class ="personel-Tr <?php if( $tarife[ 'id' ] == $tarife_id ) echo $satir_renk; ?>" data-id="<?php echo $tarife[ 'id' ]; ?>">
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $tarife[ 'adi' ]; ?></td>
									<td><?php echo $tarife[ 'grup_adi' ]; ?></td>
									<td><?php echo $tarife[ 'mesai_adi' ]; ?></td>
									<td><?php echo $fn->tarihFormatiDuzelt( $tarife[ 'baslangic_tarih' ] ); ?></td>
									<td><?php echo $fn->tarihFormatiDuzelt( $tarife[ 'bitis_tarih' ] ); ?></td>
									<td><?php echo date( 'H:i', strtotime( $tarife[ 'mesai_baslangic' ] ) ); ?></td>
									<td><?php echo date( 'H:i', strtotime( $tarife[ 'mesai_bitis' ] ) );  ?></td>
									<td><?php echo $tarife[ 'min_calisma_saati' ].' dk'; ?></td>
									<td><?php echo date( 'H:i', strtotime( $tarife[ 'gun_donumu' ] ) );  ?></td>
									
									<td align = "center">
										<a modul = 'tarifeler' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=tarifeler&islem=guncelle&tarife_id=<?php echo $tarife[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul= 'tarifeler' yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/tarifeler/tarifelerSEG.php?islem=sil&tarife_id=<?php echo $tarife[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay">Sil</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-4">
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
								<form class="form-horizontal" action = "_modul/tarifeler/tarifelerSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "tarife_id" value = "<?php echo $tarife_id; ?>">
									<h3 class="profile-username text-center"><b> </b></h3>
									<div class="form-group">
										<label class="control-label">Adı</label>
										<input required type="text" class="form-control" name ="adi" value = "<?php echo $tek_tarife[ "adi" ]; ?>"  autocomplete="off">
									</div>
									<div class="form-group">
										<label class="control-label">Grubu</label>
										<select class="form-control" name = "grup_id" required>
											<option value="">Seçiniz</option>
											<?php foreach( $gruplar as $grup ) { ?>
												<option value = "<?php echo $grup[ 'id' ]; ?>" <?php if( $tek_tarife[ 'grup_id' ] == $grup[ 'id' ] ) echo 'selected'; ?>><?php echo $grup['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Mesai Türü</label>
										<select class="form-control" name = "mesai_turu" required>
											<option value="">Seçiniz</option>
											<?php foreach( $mesai_turleri as $mesai ) { ?>
												<option value="<?php echo $mesai[ 'id' ]; ?>" <?php if( $tek_tarife[ 'mesai_turu' ] == $mesai[ 'id' ] ) echo 'selected'; ?>><?php echo $mesai['adi']; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label class="control-label">Başlangıç Tarihi</label>
										<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-baslangic_tarih" value="<?php echo $fn->tarihFormatiDuzelt(  $tek_tarife[ "baslangic_tarih" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker1" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Bitiş Tarihi</label>
										<div class="input-group date" id="datetimepicker2" data-target-input="nearest">
											<div class="input-group-append" data-target="#datetimepicker2" data-toggle="datetimepicker">
												<div class="input-group-text"><i class="fa fa-calendar"></i></div>
											</div>
											<input autocomplete="off" type="text" name="tarihalani-bitis_tarih" value="<?php echo $fn->tarihFormatiDuzelt( $tek_tarife[ "bitis_tarih" ] ); ?>" class="form-control datetimepicker-input" data-target="#datetimepicker2" data-toggle="datetimepicker"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label">Mesai Başlangıc Saati</label>
										<input type="text" class="form-control" name ="mesai_baslangic" value = "<?php echo date( 'H:i', strtotime($tek_tarife[ "mesai_baslangic" ] ) ); ?>" required placeholder="Örk: 08:00 ">
									</div>
									
									<div class="form-group">
										<label class="control-label">Mesai Bitis Saati</label>
										<input type="text" class="form-control" name ="mesai_bitis" value = "<?php echo date( 'H:i', strtotime( $tek_tarife[ "mesai_bitis" ] ) ); ?>" required placeholder="Örk: 18:30 ">
									</div>
									<div class="form-group">
										<label class="control-label">Minimum Çalışma</label>
										<input required type="text" class="form-control" name ="min_calisma_saati" value = "<?php echo $tek_tarife[ "min_calisma_saati" ]; ?>" placeholder="25, 30, 40, 60, 120 vs.">
									</div>
									<div class="form-group">
										<label class="control-label">Gün Dönümü</label>
										<input required type="text" class="form-control" name ="gun_donumu" value = "<?php echo date( 'H:i', strtotime($tek_tarife[ "gun_donumu" ])); ?>" placeholder="06:59, 18:45 vs.">
									</div>
									<div class="card-footer">
										<button modul= 'personel' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
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
<div id="sagtikmenu" style="display: none;">asdasdasd</div>
<style type="text/css">
	.custom-menu {
	    z-index:1000;
	    position: absolute;
	    background-color:#fff;
	    border: 1px solid #000;
	    padding: 2px;
	    border-radius: 5px;
	}
	.custom-menu a{
		display: block;
		padding: 10px 30px 10px 10px;
		border-bottom: 1px solid #ddd;
		color: #000;
	}
	.custom-menu a:hover{
		background-color: #ddd;
		transition: initial;
	}
	
</style>
<script type="text/javascript">
//Adı sıyadını büyük harf yap
String.prototype.turkishToUpper = function(){
	var string = this;
	var letters = { "i": "İ", "ş": "Ş", "ğ": "Ğ", "ü": "Ü", "ö": "Ö", "ç": "Ç", "ı": "I" };
	string = string.replace(/(([iışğüçö]))/g, function(letter){ return letters[letter]; })
	return string.toUpperCase();
}

// ESC tuşuna basınca formu temizle
document.addEventListener( 'keydown', function( event ) {
	if( event.key === "Escape" ) {
		document.getElementById( 'yeni_personel' ).click();
	}
});

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