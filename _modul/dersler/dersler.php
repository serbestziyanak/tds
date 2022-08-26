<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();


/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' 	: 'yesil';
	$_REQUEST[ 'ders_id' ]				= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$islem			= array_key_exists( 'islem'		,$_REQUEST ) 	 ? $_REQUEST[ 'islem' ]		: 'ekle';
$ders_id		= array_key_exists( 'ders_id'	,$_REQUEST ) 	 ? $_REQUEST[ 'ders_id' ]	: 0;


$satir_renk				= $ders_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $ders_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $ders_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';



$SQL_tum_dersler = <<< SQL
SELECT 
	d.id,
	d.ders_kodu,
	d.adi AS ders_adi,
	p.adi AS program_adi,
	abd.adi AS anabilim_dali_adi,
	dk.adi AS ders_kategori_adi
FROM 
	tb_dersler AS d
LEFT JOIN tb_programlar AS p ON p.id = d.program_id
LEFT JOIN tb_anabilim_dallari AS abd ON abd.id = d.anabilim_dali_id
LEFT JOIN tb_ders_kategorileri AS dk ON dk.id = d.ders_kategori_id
WHERE
	p.universite_id = ? AND
	d.aktif 		  	= 1 
SQL;


$SQL_tek_ders_oku = <<< SQL
SELECT 
	*
FROM 
	tb_dersler
WHERE 
	id 				= ? AND
	aktif 			= 1 
SQL;

/*Üniversiteye Ait Anabilim Dalını Listele*/
$SQL_programlar = <<< SQL
SELECT
	*
FROM
	tb_programlar
WHERE
	universite_id   = ? AND
	aktif 			= 1
SQL;

/*Üniversiteye Ait Anabilim Dalını Listele*/
$SQL_anabilim_dallari = <<< SQL
SELECT
	abd.id,
	abd.adi
FROM
	tb_anabilim_dallari AS abd
LEFT JOIN tb_fakulteler AS f  ON f.id = abd.fakulte_id
WHERE
	f.universite_id   = ? AND
	abd.aktif 		  = 1
SQL;

/*Üniversiteye Ait Ders Kategorileri Şeçmeli VS Zorunlu vb. */
$SQL_ders_kategorileri = <<< SQL
SELECT
	id,
	adi
FROM
	tb_ders_kategorileri 
WHERE
	universite_id   = ? AND
	aktif 		  	= 1
SQL;


$ders_kategorileri	= $vt->select( $SQL_ders_kategorileri, array( $_SESSION[ 'universite_id'] ) )[ 2 ];
$anabilim_dallari	= $vt->select( $SQL_anabilim_dallari, array( $_SESSION[ 'universite_id'] ) )[ 2 ];
$programlar			= $vt->select( $SQL_programlar, array( $_SESSION[ 'universite_id'] ) )[ 2 ];
$dersler			= $vt->select( $SQL_tum_dersler, array( $_SESSION[ 'universite_id'] ) )[ 2 ];
@$tek_ders			= $vt->select( $SQL_tek_ders_oku, array( $ders_id ) )[ 2 ][ 0 ];

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
				<div class="card card-secondary" id = "card_dersler">
					<div class="card-header">
						<h3 class="card-title">Dersler</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_ders" data-toggle = "tooltip" title = "Yeni Üviversite Ekle" href = "?modul=dersler&islem=ekle" class="btn btn-tool" ><i class="fas fa-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_dersler" class="table table-bordered table-hover table-sm" width = "100%" >
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Ders Kodu</th>
									<th>Kategori</th>
									<th>Program</th>
									<th>Anabilim Dalı</th>
									<th>Ders Adı</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
									<th data-priority="1" style="width: 20px">Sil</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $dersler AS $ders ) { ?>
								<tr oncontextmenu="fun();" class ="ders-Tr <?php if( $ders[ 'id' ] == $ders_id ) echo $satir_renk; ?>" data-id="<?php echo $ders[ 'id' ]; ?>">
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $ders[ 'ders_kodu' ]; ?></td>
									<td><?php echo $ders[ 'ders_kategori_adi' ]; ?></td>
									<td><?php echo $ders[ 'program_adi' ]; ?></td>
									<td><?php echo $ders[ 'anabilim_dali_adi' ]; ?></td>
									<td><?php echo $ders[ 'ders_adi' ]; ?></td>
									<td align = "center">
										<a modul = 'dersler' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=dersler&islem=guncelle&ders_id=<?php echo $ders[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul= 'dersler' yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/dersler/derslerSEG.php?islem=sil&ders_id=<?php echo $ders[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay">Sil</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card <?php if( $ders_id == 0 ) echo 'card-secondary' ?>">
					<div class="card-header p-2">
						<ul class="nav nav-pills tab-container">
							<?php if( $ders_id > 0 ) { ?>
								<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Ders Düzenle</h6>
							<?php } else {
								echo "<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Ders Ekle</h6>";
								}
							?>
							
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<!-- GENEL BİLGİLER -->
							<div class="tab-pane active" id="_genel">
								<form class="form-horizontal" action = "_modul/dersler/derslerSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "ders_id" value = "<?php echo $ders_id; ?>">
									<h3 class="profile-username text-center"><b> </b></h3>
									<div class="form-group">
										<label  class="control-label">Program</label>
										<select class="form-control select2" name = "program_id" required>
											<option>Seçiniz...</option>
											<?php 
												foreach( $programlar AS $program ){
													echo '<option value="'.$program[ "id" ].'" '.( $tek_ders[ "program_id" ] == $program[ "id" ] ? "selected" : null) .'>'.$program[ "adi" ].'</option>';
												}

											?>
										</select>
									</div>
									<div class="form-group">
										<label  class="control-label">Anabilim Dalı</label>
										<select class="form-control select2" name = "anabilim_dali_id" required>
											<option>Seçiniz...</option>
											<?php 
												foreach( $anabilim_dallari AS $anabilim_dali ){
													echo '<option value="'.$anabilim_dali[ "id" ].'" '.( $tek_ders[ "anabilim_dali_id" ] == $anabilim_dali[ "id" ] ? "selected" : null) .'>'.$anabilim_dali[ "adi" ].'</option>';
												}

											?>
										</select>
									</div>

									<div class="form-group">
										<label  class="control-label">Kategori</label>
										<select class="form-control select2" name = "ders_kategori_id" required>
											<option>Seçiniz...</option>
											<?php 
												foreach( $ders_kategorileri AS $ders_kategori ){
													echo '<option value="'.$ders_kategori[ "id" ].'" '.( $tek_ders[ "ders_kategori_id" ] == $ders_kategori[ "id" ] ? "selected" : null) .'>'.$ders_kategori[ "adi" ].'</option>';
												}

											?>
										</select>
									</div>
									
									
									<div class="form-group">
										<label class="control-label">Ders Kodu</label>
										<input required type="text" class="form-control" name ="ders_kodu" value = "<?php echo $tek_ders[ "ders_kodu" ]; ?>"  autocomplete="off">
									</div>

									<div class="form-group">
										<label class="control-label">Adı</label>
										<input required type="text" class="form-control" name ="adi" value = "<?php echo $tek_ders[ "adi" ]; ?>"  autocomplete="off">
									</div>
									
									<div class="card-footer">
										<button modul= 'dersler' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
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

// ESC tuşuna basınca formu temizle
document.addEventListener( 'keydown', function( event ) {
	if( event.key === "Escape" ) {
		document.getElementById( 'yeni_ders' ).click();
	}
});

var tbl_dersler = $( "#tbl_dersler" ).DataTable( {
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
} ).buttons().container().appendTo('#tbl_dersler_wrapper .col-md-6:eq(0)');



$('#card_dersler').on('maximized.lte.cardwidget', function() {
	var tbl_dersler = $( "#tbl_dersler" ).DataTable();
	var column = tbl_dersler.column(  tbl_dersler.column.length - 1 );
	column.visible( ! column.visible() );
	var column = tbl_dersler.column(  tbl_dersler.column.length - 2 );
	column.visible( ! column.visible() );
});

$('#card_dersler').on('minimized.lte.cardwidget', function() {
	var tbl_dersler = $( "#tbl_dersler" ).DataTable();
	var column = tbl_dersler.column(  tbl_dersler.column.length - 1 );
	column.visible( ! column.visible() );
	var column = tbl_dersler.column(  tbl_dersler.column.length - 2 );
	column.visible( ! column.visible() );
} );


</script>