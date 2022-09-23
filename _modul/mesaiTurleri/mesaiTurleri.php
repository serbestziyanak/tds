<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();


/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'mesai_turu_id' ]			= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$islem			= array_key_exists( 'islem'			,$_REQUEST ) ? $_REQUEST[ 'islem' ]			: 'ekle';
$mesai_turu_id	= array_key_exists( 'mesai_turu_id'	,$_REQUEST ) ? $_REQUEST[ 'mesai_turu_id' ]	: 0;


$satir_renk				= $mesai_turu_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $mesai_turu_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $mesai_turu_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';



$SQL_tum_mesai_turleri_oku = <<< SQL
SELECT 
	*
FROM 
	tb_mesai_turu
WHERE 
	firma_id 	= ? AND
	aktif 		= 1
SQL;


$SQL_tek_mesai_turu_oku = <<< SQL
SELECT 
	*
FROM 
	tb_mesai_turu 
WHERE 
	id 			= ? AND
	firma_id 	= ? AND
	aktif 		= 1 
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



$mesai_turleri		= $vt->select( $SQL_tum_mesai_turleri_oku, array($_SESSION['firma_id'] ) )[ 2 ];
$tek_mesai_turu		= $vt->select( $SQL_tek_mesai_turu_oku, array( $mesai_turu_id, $_SESSION['firma_id'] ) )[ 2 ][ 0 ];

$secili_gunler = explode( ",", $tek_mesai_turu[ "gunler" ] );

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
			<div class="col-md-6">
				<div class="card card-secondary" id = "card_personeller">
					<div class="card-header">
						<h3 class="card-title">Mesai Türleri</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir mesai ekle" href = "?modul=mesaiTurleri&islem=ekle" class="btn btn-tool" ><i class="fas fa-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personeller" class="table table-bordered table-hover table-sm" width = "100%" >
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Adı</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
									<th data-priority="1" style="width: 20px">Sil</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $mesai_turleri AS $mesai_turu ) { ?>
								<tr oncontextmenu="fun();" class ="personel-Tr <?php if( $mesai_turu[ 'id' ] == $mesai_turu_id ) echo $satir_renk; ?>" data-id="<?php echo $mesai_turu[ 'id' ]; ?>">
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $mesai_turu[ 'adi' ]; ?></td>
									<td align = "center">
										<a modul = 'mesaiTurleri' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=mesaiTurleri&islem=guncelle&mesai_turu_id=<?php echo $mesai_turu[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul= 'mesaiTurleri' yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/mesaiTurleri/mesaiTurleriSEG.php?islem=sil&mesai_turu_id=<?php echo $mesai_turu[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay">Sil</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card <?php if( $mesai_turu_id == 0 ) echo 'card-secondary' ?>">
					<div class="card-header p-2">
						<ul class="nav nav-pills tab-container">
							<?php if( $mesai_turu_id > 0 ) { ?>
								<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Mesai Türünü Düzenle</h6>
							<?php } else {
								echo "<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Mesai Türü Ekle</h6>";
								}
							?>
							
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<!-- GENEL BİLGİLER -->
							<div class="tab-pane active" id="_genel">
								<form class="form-horizontal" action = "_modul/mesaiTurleri/mesaiTurleriSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "mesai_turu_id" value = "<?php echo $mesai_turu_id; ?>">
									<h3 class="profile-username text-center"><b> </b></h3>
									<div class="form-group">
										<label class="control-label">Adı</label>
										<input required type="text" class="form-control" name ="adi" value = "<?php echo $tek_mesai_turu[ "adi" ]; ?>"  autocomplete="off">
									</div>
									<div class="form-group">
										<label  class="control-label">Günler</label>
										<select  class="form-control select2"  multiple="multiple" name = "gunler[]" required>
											<option value=",Pazartesi," <?php echo in_array( "Pazartesi", $secili_gunler ) ? 'selected' : ''; ?>>Pazartesi</option>
											<option value=",Salı," <?php echo in_array( "Salı", $secili_gunler ) ? 'selected' : ''; ?> >Salı</option>
											<option value=",Çarşamba," <?php echo in_array( "Çarşamba", $secili_gunler ) ? 'selected' : ''; ?>>Çarşamba</option>
											<option value=",Perşembe," <?php echo in_array( "Perşembe", $secili_gunler ) ? 'selected' : ''; ?>>Perşembe</option>
											<option value=",Cuma," <?php echo in_array( "Cuma", $secili_gunler ) ? 'selected' : ''; ?>>Cuma</option>
											<option value=",Cumartesi," <?php echo in_array( "Cumartesi", $secili_gunler ) ? 'selected' : ''; ?>>Cumartesi</option>
											<option value=",Pazar," <?php echo in_array( "Pazar", $secili_gunler ) ? 'selected' : ''; ?>>Pazar</option>
										</select>
									</div>
									<div class="card-footer">
										<button modul= 'mesaiTurleri' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
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