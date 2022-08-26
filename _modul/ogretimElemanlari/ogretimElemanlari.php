<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();


/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
	$mesaj								= $_SESSION[ 'sonuclar' ][ 'mesaj' ];
	$mesaj_turu							= $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' 	: 'yesil';
	$_REQUEST[ 'ogretim_elemani_id' ]				= $_SESSION[ 'sonuclar' ][ 'id' ];
	unset( $_SESSION[ 'sonuclar' ] );
	echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}


$islem					= array_key_exists( 'islem'		         ,$_REQUEST ) ? $_REQUEST[ 'islem' ]				: 'ekle';
$ogretim_elemani_id		= array_key_exists( 'ogretim_elemani_id' ,$_REQUEST ) ? $_REQUEST[ 'ogretim_elemani_id' ]	: 0;


$satir_renk				= $ogretim_elemani_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi		= $ogretim_elemani_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls		= $ogretim_elemani_id > 0	? 'btn btn-warning btn-sm pull-right'	: 'btn btn-success btn-sm pull-right';



$SQL_tum_ogretimElemanlari = <<< SQL
SELECT 
	oe.id,
	CONCAT( u.adi, ' ', oe.adi, ' ', oe.soyadi ) AS o_adi,
	f.adi AS fakulte_adi,
	abd.adi AS anabilim_dali_adi
FROM 
	tb_ogretim_elemanlari AS oe
LEFT JOIN tb_fakulteler AS f ON f.id = oe.fakulte_id
LEFT JOIN tb_anabilim_dallari AS abd ON abd.id = oe.anabilim_dali_id
LEFT JOIN tb_unvanlar AS u ON u.id = oe.unvan_id
WHERE
	oe.universite_id 	= ? AND
	oe.aktif 		  	= 1 
ORDER BY u.sira ASC, oe.adi ASC
SQL;


$SQL_tek_ogretim_elemani_oku = <<< SQL
SELECT 
	*
FROM 
	tb_ogretim_elemanlari
WHERE 
	id 				= ? AND
	aktif 			= 1 
SQL;

/*Üniversiteye Ait Anabilim Dalını Listele*/
$SQL_fakulteler = <<< SQL
SELECT
	*
FROM
	tb_fakulteler
WHERE
	universite_id   = ? AND
	aktif 			= 1
SQL;


/*Üniversiteye Ait Anabilim Dalını Listele*/
$SQL_unvanlar = <<< SQL
SELECT
	*
FROM
	tb_unvanlar
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


$unvanlar							= $vt->select( $SQL_unvanlar, array( $_SESSION[ 'universite_id'] ) )[ 2 ];
$anabilim_dallari					= $vt->select( $SQL_anabilim_dallari, array( $_SESSION[ 'universite_id'] ) )[ 2 ];
$fakulteler							= $vt->select( $SQL_fakulteler, array( $_SESSION[ 'universite_id'] ) )[ 2 ];
$ogretimElemanlari					= $vt->select( $SQL_tum_ogretimElemanlari, array( $_SESSION[ 'universite_id'] ) )[ 2 ];
@$tek_ogretim_elemani				= $vt->select( $SQL_tek_ogretim_elemani_oku, array( $ogretim_elemani_id ) )[ 2 ][ 0 ];		

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
				<div class="card card-secondary" id = "card_ogretimElemanlari">
					<div class="card-header">
						<h3 class="card-title">Öğretim Elemanları</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_ogretim_elemanlari" data-toggle = "tooltip" title = "Yeni Öğretim Elemanı Ekle" href = "?modul=ogretimElemanlari&islem=ekle" class="btn btn-tool" ><i class="fas fa-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_ogretimElemanlari" class="table table-bordered table-hover table-sm" width = "100%" >
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Fakulte</th>
									<th>Anabilim Dalı</th>
									<th>Adı</th>
									<th data-priority="1" style="width: 20px">Düzenle</th>
									<th data-priority="1" style="width: 20px">Sil</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1; foreach( $ogretimElemanlari AS $ogretim_elemanlari ) { ?>
								<tr oncontextmenu="fun();" class ="ogretim_elemanlari-Tr <?php if( $ogretim_elemanlari[ 'id' ] == $ogretim_elemani_id ) echo $satir_renk; ?>" data-id="<?php echo $ogretim_elemanlari[ 'id' ]; ?>">
									<td><?php echo $sayi++; ?></td>
									<td><?php echo $ogretim_elemanlari[ 'fakulte_adi' ]; ?></td>
									<td><?php echo $ogretim_elemanlari[ 'anabilim_dali_adi' ]; ?></td>
									<td><?php echo $ogretim_elemanlari[ 'o_adi' ]; ?></td>
									<td align = "center">
										<a modul = 'ogretimElemanlari' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=ogretimElemanlari&islem=guncelle&ogretim_elemani_id=<?php echo $ogretim_elemanlari[ 'id' ]; ?>" >
											Düzenle
										</a>
									</td>
									<td align = "center">
										<button modul= 'ogretimElemanlari' yetki_islem="sil" class="btn btn-xs btn-danger" data-href="_modul/ogretimElemanlari/ogretimElemanlariSEG.php?islem=sil&ogretim_elemani_id=<?php echo $ogretim_elemanlari[ 'id' ]; ?>" data-toggle="modal" data-target="#sil_onay">Sil</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card <?php if( $ogretim_elemani_id == 0 ) echo 'card-secondary' ?>">
					<div class="card-header p-2">
						<ul class="nav nav-pills tab-container">
							<?php if( $ogretim_elemani_id > 0 ) { ?>
								<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Öğretim Elemanı Düzenle</h6>
							<?php } else {
								echo "<h6 style = 'font-size: 1rem;'> &nbsp;&nbsp;&nbsp; Öğretim Elemanı Ekle</h6>";
								}
							?>
							
						</ul>
					</div>
					<div class="card-body">
						<div class="tab-content">
							<!-- GENEL BİLGİLER -->
							<div class="tab-pane active" id="_genel">
								<form class="form-horizontal" action = "_modul/ogretimElemanlari/ogretimElemanlariSEG.php" method = "POST" enctype="multipart/form-data">
									<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>" >
									<input type = "hidden" name = "ogretim_elemani_id" value = "<?php echo $ogretim_elemani_id; ?>">
									<h3 class="profile-username text-center"><b> </b></h3>
									<div class="form-group">
										<label  class="control-label">Fakülte</label>
										<select class="form-control select2" name = "fakulte_id" required>
											<option>Seçiniz...</option>
											<?php 
												foreach( $fakulteler AS $fakulte ){
													echo '<option value="'.$fakulte[ "id" ].'" '.( $tek_ogretim_elemani[ "fakulte_id" ] == $fakulte[ "id" ] ? "selected" : null) .'>'.$fakulte[ "adi" ].'</option>';
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
													echo '<option value="'.$anabilim_dali[ "id" ].'" '.( $tek_ogretim_elemani[ "anabilim_dali_id" ] == $anabilim_dali[ "id" ] ? "selected" : null) .'>'.$anabilim_dali[ "adi" ].'</option>';
												}

											?>
										</select>
									</div>

									<div class="form-group">
										<label  class="control-label">Unvan</label>
										<select class="form-control select2" name = "unvan_id" required>
											<option>Seçiniz...</option>
											<?php 
												foreach( $unvanlar AS $unvan ){
													echo '<option value="'.$unvan[ "id" ].'" '.( $tek_ogretim_elemani[ "unvan_id" ] == $unvan[ "id" ] ? "selected" : null) .'>'.$unvan[ "adi" ].'</option>';
												}

											?>
										</select>
									</div>
									
									<div class="form-group">
										<label class="control-label">Adı</label>
										<input required type="text" class="form-control" name ="adi" value = "<?php echo $tek_ogretim_elemani[ "adi" ]; ?>"  autocomplete="off">
									</div>

									<div class="form-group">
										<label class="control-label">Soyadı</label>
										<input required type="text" class="form-control" name ="soyadi" value = "<?php echo $tek_ogretim_elemani[ "soyadi" ]; ?>"  autocomplete="off">
									</div>

									<div class="form-group">
										<label class="control-label">E Mail</label>
										<input required type="email" class="form-control" name ="email" value = "<?php echo $tek_ogretim_elemani[ "email" ]; ?>"  autocomplete="off">
									</div>
									
									<div class="form-group">
										<label class="control-label">Cep Telefonu</label>
										<input required type="text" class="form-control" name ="cep_tel" value = "<?php echo $tek_ogretim_elemani[ "cep_tel" ]; ?>"  autocomplete="off">
									</div>
									
									
									
									<div class="card-footer">
										<button modul= 'ogretimElemanlari' yetki_islem="kaydet" type="submit" class="<?php echo $kaydet_buton_cls; ?>"><span class="fa fa-save"></span> <?php echo $kaydet_buton_yazi; ?></button>
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
		document.getElementById( 'yeni_ogretim_elemanlari' ).click();
	}
});

var tbl_ogretimElemanlari = $( "#tbl_ogretimElemanlari" ).DataTable( {
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
} ).buttons().container().appendTo('#tbl_ogretimElemanlari_wrapper .col-md-6:eq(0)');



$('#card_ogretimElemanlari').on('maximized.lte.cardwidget', function() {
	var tbl_ogretimElemanlari = $( "#tbl_ogretimElemanlari" ).DataTable();
	var column = tbl_ogretimElemanlari.column(  tbl_ogretimElemanlari.column.length - 1 );
	column.visible( ! column.visible() );
	var column = tbl_ogretimElemanlari.column(  tbl_ogretimElemanlari.column.length - 2 );
	column.visible( ! column.visible() );
});

$('#card_ogretimElemanlari').on('minimized.lte.cardwidget', function() {
	var tbl_ogretimElemanlari = $( "#tbl_ogretimElemanlari" ).DataTable();
	var column = tbl_ogretimElemanlari.column(  tbl_ogretimElemanlari.column.length - 1 );
	column.visible( ! column.visible() );
	var column = tbl_ogretimElemanlari.column(  tbl_ogretimElemanlari.column.length - 2 );
	column.visible( ! column.visible() );
} );


</script>