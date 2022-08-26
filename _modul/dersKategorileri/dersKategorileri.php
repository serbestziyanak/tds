<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();


/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
	$_REQUEST[ 'dersKategori_id' ]			= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$islem			= array_key_exists( 'islem'		,$_REQUEST ) 	 ? $_REQUEST[ 'islem' ]			: 'ekle';
$dersKategori_id    = array_key_exists( 'dersKategori_id'	,$_REQUEST ) ? $_REQUEST[ 'dersKategori_id' ]	: 0;


$satir_renk				= $dersKategori_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $dersKategori_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $dersKategori_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';


/*Tüm Ders Yılını Okuma*/
$SQL_tum_ders_kategorileri = <<< SQL
SELECT 
	*
FROM 
	tb_ders_kategorileri
WHERE 
	universite_id 	= ? AND
	aktif 		  	= 1
SQL;

/*Tek Ders Yılı Okuma*/
$SQL_tek_ders_yili_oku = <<< SQL
SELECT 
	*
FROM 
	tb_ders_kategorileri
WHERE 
	id 				= ? AND
	aktif 			= 1 
SQL;

$dersKategorileri	= $vt->select( $SQL_tum_ders_kategorileri, array( $_SESSION[ 'universite_id'] ) )[ 2 ];
@$tek_dersYili		= $vt->select( $SQL_tek_ders_yili_oku, array( $dersKategori_id ) )[ 2 ][ 0 ];

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
				<div class="card card-secondary" id = "card_dersKategorileri">
					<div class="card-header">
						<h3 class="card-title">Ders Kategorileri</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_fakulte" data-toggle = "tooltip" title = "Yeni Üviversite Ekle" href = "?modul=dersKategorileri&islem=ekle" class="btn btn-tool" ><i class="fas fa-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_dersKategorileri" class="table table-bordered table-hover table-sm" width = "100%" >
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Adı</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
									<th data-priority="1" style="width: 20px">Sil</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $dersKategorileri AS $fakulte ) { ?>
								<tr oncontextmenu="fun();" class ="fakulte-Tr <?php if( $fakulte[ 'id' ] == $dersKategori_id ) echo $satir_renk; ?>" data-id="<?php echo $fakulte[ 'id' ]; ?>">
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $fakulte[ 'adi' ]; ?></td>
									<td align = "center">
										<a modul = 'dersKategorileri' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=dersKategorileri&islem=guncelle&dersKategori_id=<?php echo $fakulte[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul= 'dersKategorileri' yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/dersKategorileri/dersKategorileriSEG.php?islem=sil&dersKategori_id=<?php echo $fakulte[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay">Sil</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card <?php if( $dersKategori_id == 0 ) echo 'card-secondary' ?>">
					<div class="card-header p-2">
						<ul class="nav nav-pills tab-container">
							<?php if( $dersKategori_id > 0 ) { ?>
								<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Ders Kategori Düzenle</h6>
							<?php } else {
								echo "<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Ders Kategori Ekle</h6>";
								}
							?>
							
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<!-- GENEL BİLGİLER -->
							<div class="tab-pane active" id="_genel">
								<form class="form-horizontal" action = "_modul/dersKategorileri/dersKategorileriSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "dersKategori_id" value = "<?php echo $dersKategori_id; ?>">
									<h3 class="profile-username text-center"><b> </b></h3>
									<div class="form-group">
										<label class="control-label">Adı</label>
										<input required type="text" class="form-control" name ="adi" value = "<?php echo $tek_dersYili[ "adi" ]; ?>"  autocomplete="off">
									</div>
									<div class="card-footer">
										<button modul= 'fakulte' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
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


var tbl_dersKategorileri = $( "#tbl_dersKategorileri" ).DataTable( {
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
				return "Fakülte Listesi";
			}
		},
		{
			extend	: 'print',
			text	: 'Yazdır',
			exportOptions : {
				columns : ':visible'
			},
			title: function(){
				return "Fakülte Listesi";
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
} ).buttons().container().appendTo('#tbl_dersKategorileri_wrapper .col-md-6:eq(0)');



$('#card_dersKategorileri').on('maximized.lte.cardwidget', function() {
	var tbl_dersKategorileri = $( "#tbl_dersKategorileri" ).DataTable();
	var column = tbl_dersKategorileri.column(  tbl_dersKategorileri.column.length - 1 );
	column.visible( ! column.visible() );
	var column = tbl_dersKategorileri.column(  tbl_dersKategorileri.column.length - 2 );
	column.visible( ! column.visible() );
});

$('#card_dersKategorileri').on('minimized.lte.cardwidget', function() {
	var tbl_dersKategorileri = $( "#tbl_dersKategorileri" ).DataTable();
	var column = tbl_dersKategorileri.column(  tbl_dersKategorileri.column.length - 1 );
	column.visible( ! column.visible() );
	var column = tbl_dersKategorileri.column(  tbl_dersKategorileri.column.length - 2 );
	column.visible( ! column.visible() );
} );



</script>