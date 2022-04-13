<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();


/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'personel_id' ]			= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$islem			= array_key_exists( 'islem'			,$_REQUEST ) ? $_REQUEST[ 'islem' ]			: 'ekle';
$personel_id	= array_key_exists( 'personel_id'	,$_REQUEST ) ? $_REQUEST[ 'personel_id' ]	: 0;
//Personele Ait Listelenecek Hareket Ay
$listelenecekAy	= array_key_exists( 'tarih'	,$_REQUEST ) ? $_REQUEST[ 'tarih' ]	: date("Y-m");

$satir_renk				= $personel_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $personel_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $personel_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';

$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
FROM
	tb_personel AS p
WHERE
	p.aktif = 1
SQL;


$SQL_tek_personel_oku = <<< SQL
SELECT
	 p.*
FROM
	tb_personel AS p
WHERE
	p.id = ? AND p.aktif = 1
SQL;

//
$SQL_tum_giris_cikis = <<< SQL
SELECT
	*
FROM
	tb_giris_cikis
WHERE
	personel_id = ? AND DATE_FORMAT(tarih,'%Y-%m') =? 
SQL;

$personeller					= $vt->select( $SQL_tum_personel_oku, array() );
$personel_id					= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];

$giris_cikislar					= $vt->select( $SQL_tum_giris_cikis, array($personel_id,$listelenecekAy) )[2];

$satir_renk				= $personel_id > 0	? 'table-warning' : '';
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
			<div class="container col-sm-12 card" style="display: block; padding: 15px 10px;">
				<button class="btn btn-outline-primary btn-lg col-xs-6 col-sm-2" data-toggle="modal" data-target="#PersonelHareketEkle">Personele Hareket Ekle</button>
				<button class="btn btn-outline-success btn-lg col-xs-6 col-sm-2">Toplu Hareket Ekle</button>
				<button class="btn btn-outline-warning btn-lg col-xs-6 col-sm-2">Toplu Hareket Düzenle</button>
				<div class="col-sm-2" style="float: right;display: flex;">
					<div class="">
						<div class="input-group date" id="datetimepicker1" data-target-input="nearest">
							<div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="tarihSec" class="form-control datetimepicker-input" data-target="#datetimepicker1" data-toggle="datetimepicker" id="tarihSec" value="<?php if($listelenecekAy) echo $listelenecekAy; ?>"/>
						</div>
					</div>
					<div style="float: right;">
						<button class="btn btn-success" id="listeleBtn">listele</button>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card card-secondary" id = "card_personeller">
					<div class="card-header">
						<h3 class="card-title">Personeller</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir personel ekle" href = "?modul=personel&islem=ekle" class="btn btn-tool" ><i class="fas fa-user-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personeller" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>TC No</th>
									<th>Adı</th>
									<th>Soyadı</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $personeller[ 2 ] AS $personel ) { ?>
								<tr <?php if( $personel[ 'id' ] == $personel_id ) echo "class = '$satir_renk'"; ?>>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $personel[ 'tc_no' ]; ?></td>
									<td><?php echo $personel[ 'adi' ]; ?></td>
									<td><?php echo $personel[ 'soyadi' ]; ?></td>
									
									<td align = "center">
										<a modul = 'personel' yetki_islem="duzenle" class = "btn btn-sm btn-success btn-xs" href = "?modul=giriscikis&personel_id=<?php echo $personel[ 'id' ]; ?>&tarih=<?php echo $listelenecekAy; ?>" >
											Hareketler
										</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				<div class="card card-secondary">
					<div class="card-header p-2">
						<ul class="nav nav-pills">
							<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Personel Hareketleri</h6>
						</ul>
					</div>
					<div class="card-body">
						<table id="tbl_giriscikislar" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Tarih</th>
									<th>Gün</th>
									<th>Bas. Saat</th>
									<th>Bit. Saat</th>
									<th>İşlem</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
									<th data-priority="1" style="width: 20px">Sil</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $giris_cikislar AS $giriscikis ) { ?>
								<tr>
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $fn->tarihVer($giriscikis[ 'tarih' ]); ?></td>
									<td><?php echo $fn->gunVer($giriscikis[ 'tarih' ]); ?></td>
									<td><?php echo $giriscikis[ 'bas_saat' ]; ?></td>
									<td><?php echo $giriscikis[ 'bit_saat' ]; ?></td>
									<td><?php echo $giriscikis[ 'islem' ]; ?></td>
									
									<td align = "center">
										<a modul = 'giriscikis' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=giriscikis&islem=guncelle&giriscikis_id=<?php echo $giriscikis[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul= 'giriscikis' yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/giriscikis/giriscikisSEG.php?islem=sil&personel_id=<?php echo $personel_id; ?>&giriscikis_id=<?php echo $giriscikis[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay">Sil</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!--Personel Hareket Ekleme MODAL-->
<div class="modal fade" id="PersonelHareketEkle"  aria-modal="true" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Personel Hareket Ekle</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Başlangıc Tarihi ve Saati</label>
					<div class="input-group date" id="baslangicDateTime" data-target-input="nearest">
						<div class="input-group-append" data-target="#baslangicDateTime" data-toggle="datetimepicker">
							<div class="input-group-text"><i class="fa fa-calendar"></i></div>
						</div>
						<input autocomplete="off" type="text" name="baslangıcTarihSaat" class="form-control datetimepicker-input" data-target="#baslangicDateTime" data-toggle="datetimepicker"/>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Bitiş Tarihi ve Saati</label>
					<div class="input-group date" id="bitisDateTime" data-target-input="nearest">
						<div class="input-group-append" data-target="#bitisDateTime" data-toggle="datetimepicker">
							<div class="input-group-text"><i class="fa fa-calendar"></i></div>
						</div>
						<input autocomplete="off" type="text" name="bitisTarihSaat" class="form-control datetimepicker-input" data-target="#bitisDateTime" data-toggle="datetimepicker"/>
					</div>
				</div>
				
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


<script type="text/javascript">


$(function () {
	$('#datetimepicker1').datetimepicker({
		//defaultDate: simdi,
		format: 'yyyy-MM',
		icons: {
			time: "far fa-clock",
			date: "fa fa-calendar",
			up: "fa fa-arrow-up",
			down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$('#baslangicDateTime').datetimepicker({
		//defaultDate: simdi,
		format: 'yyyy-MM-DD hh:mm',
		icons: {
			time: "far fa-clock",
			date: "fa fa-calendar",
			up: "fa fa-arrow-up",
			down: "fa fa-arrow-down"
		}
	});
});

$(function () {
	$('#bitisDateTime').datetimepicker({
		//defaultDate: simdi,
		format: 'yyyy-MM-DD hh:mm',
		icons: {
			time: "far fa-clock",
			date: "fa fa-calendar",
			date: "fa fa-calendar",
			up: "fa fa-arrow-up",
			down: "fa fa-arrow-down"
		}
	});
});

$("body").on('click', '#listeleBtn', function() {
	const tarih 		= $("#tarihSec").val();
	const  url 			= window.location;
	const origin		= url.origin;
	const path			= url.pathname;
	const search		= (new URL(document.location)).searchParams;
	const modul   		= search.get('modul');
	const personel_id   = search.get('personel_id');
	window.location.replace(origin + path+'?modul='+modul+'&personel_id='+personel_id+'&tarih='+tarih);
})

var tbl_personeller = $( "#tbl_personeller" ).DataTable( {
	"responsive": true, "lengthChange": true, "autoWidth": true,
	"stateSave": true,
	"pageLength" : 15,
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