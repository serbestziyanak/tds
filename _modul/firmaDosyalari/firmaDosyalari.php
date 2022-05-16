<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$islem 			= array_key_exists( 'islem', $_REQUEST ) 		? $_REQUEST[ 'islem' ] 			: 'ekle';
$dosyaTuru_id 	= array_key_exists( 'dosyaTuru_id', $_REQUEST ) ? $_REQUEST[ 'dosyaTuru_id' ] 	: 0;

//Firma_Dosya Trurlerini ve toplasm dosya sayısı ile birlikte listelemek
$SQL_tum_firma_dosyasi_oku = <<< SQL
SELECT
	tb_firma_dosya_turleri.id,
	tb_firma_dosya_turleri.adi,
	(SELECT COUNT(tb_firma_dosyalari.id) 
		FROM tb_firma_dosyalari 
		WHERE tb_firma_dosyalari.dosya_turu_id = tb_firma_dosya_turleri.id 
		) AS dosyaSayisi
FROM
	tb_firma_dosya_turleri
WHERE 
	firma_id = ? 
SQL;

$SQL_tek_dosya_turu_oku = <<< SQL
SELECT
	*
FROM
	tb_firma_dosya_turleri
WHERE
	id 			= ? AND 
	firma_id 	= ?
SQL;


$SQL_firma_dosyalari = <<< SQL
SELECT
	fd.id AS dosya_id,
	fd.dosya,
	fd.aciklama,
	dt.id AS tur_id,
	dt.adi
FROM
	tb_firma_dosyalari AS fd
INNER JOIN
	tb_firma_dosya_turleri AS dt ON fd.dosya_turu_id = dt.id
WHERE
	dt.id 		= ? AND
	dt.firma_id = ?
SQL;


$dosyaTurleri 		= $vt->select( $SQL_tum_firma_dosyasi_oku, array( $_SESSION[ 'firma_id' ] ) );
$dosyaTuru_id		= array_key_exists( 'dosyaTuru_id', $_REQUEST ) ? $_REQUEST[ 'dosyaTuru_id' ] : $dosyaTurleri[ 2 ][ 0 ][ 'id' ];
$tekDosyaTuru		= $vt->select( $SQL_tek_dosya_turu_oku, array( $dosyaTuru_id, $_SESSION[ 'firma_id' ] ) ) [ 2 ] ;
$firmaDosyalari		= $vt->select( $SQL_firma_dosyalari, array( $dosyaTuru_id, $_SESSION[ 'firma_id' ] ) ) [ 2 ];


$satir_renk			= $dosyaTuru_id > 0	? 'table-warning' : '';

?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="kayit_sil" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">Lütfen Dikkat!</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				Bu kaydı <b>Silmek</b> istediğinize emin misiniz?<br>
				<b>Bir daha geri getirilmeyecektir.</b>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
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
			<div class = "col-md-5">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Firma Dosya Türleri</h3>
						<div class="card-tools">
							<a id="yeni_personel"  title="" href="javascript:void(0)" class="btn btn-tool" data-original-title="Yeni Dosya Türü Ekle" data-toggle="modal" data-target="#dosyaTuru"><i class="fas fa-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personelOzlukDosyalari" class="table table-sm table-bordered table-hover">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Adı</th>
									<th style="width: 60px">Dosya Sayısı</th>
									<th data-priority=" 1" style="width: 20px">Düzenle</th>
								</tr>
							</thead>
							<tbody>
								<?php $sayi = 1;  foreach( $dosyaTurleri[ 2 ] AS $dosyaTuru ) { ?>
									<tr  <?php if( $dosyaTuru[ 'id' ] == $dosyaTuru_id ) echo "class = '$satir_renk'";?>>
										<td><?php echo $sayi++; ?></td>
										<td><?php echo $dosyaTuru[ 'adi' ]; ?></td>
										<td><?php echo $dosyaTuru[ 'dosyaSayisi' ]; ?></td>
										<td align = "center">
											<a modul = 'firmalar' yetki_islem="evraklar" class = "btn btn-sm btn-warning btn-xs" href = "?modul=firmaDosyalari&islem=guncelle&dosyaTuru_id=<?php echo $dosyaTuru[ 'id' ]; ?>" >
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
			<div class = "col-md-7">
				<div class="dropzonedosya" id="DosyaAlani" >
		            <div class=" card card-info">
		                <div class="card-header" id="CardHeader">
		                    <h3 class="card-title"><span id="baslik"><?php echo $tekDosyaTuru[ 0 ] [ "adi" ]; ?> Dosya Yükleyin</span></h3>
		                </div>
		                <div class="card-body" id="CardBody">
		                    <form enctype="multipart/form-data" method="POST"  name="mainFileUploader" class="" id="dropzonform">
		                        <div class="form-group">
		                            <input type="text" name="aciklama" id="aciklama" class="form-control" placeholder="Acıklama Kısmı">
		                        </div>
		                        <div class="dropzone" action="_modul/firmaDosyalari/firmaDosyalariSEG.php"  id="dropzone" style="min-height: 236px;">
		                            <div class="dz-message">
		                                <h3 class="m-h-lg">Yüklemek istediğiniz dosyaları buyara sürükleyiniz</h3>
		                                <p class="m-b-lg text-muted">(Yüklemek için dosyalarınızı sürükleyiniz yada buraya tıklayınız)<br>En Fazla 10 Resim Birden Yükleyebilirsiniz</p>
		                            </div>
		                        </div>
		                        <input type="hidden" name="dosyaTuru_id" value="<?php echo $dosyaTuru_id; ?>">
		                        <input type="hidden" name="konu" id="konu" value="dosya">
		                        <a href="javascript:void(0);" class="btn btn-outline-info" style="margin-top:10px; width: 100%;" id="submit-all">Yükle</a>
		                    </form>
		                </div>
		            </div>
		        </div> 
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title"><?php echo $tekDosyaTuru[ 0 ] [ "adi" ]; ?></h3>
					</div>
					<div class="card-body">
						<div class="card card-default">
							<div class="card-body">
								<div id="actions" class="row">
									<table class="table table-striped table-valign-middle">
										<tbody>
											<?php
											if( count( $firmaDosyalari ) > 0 ) {
												foreach( $firmaDosyalari AS $dosya ) { ?>
													<tr>
														<td>
															<?php echo $dosya[ 'aciklama' ] == '' ? $dosya[ 'adi' ] : $dosya[ 'aciklama' ]; ?>
														</td>
														<td align = "right" width = "5%">
															<a href = "firmaDosyalari/<?php echo $dosyaTuru_id; ?>/<?php echo $dosya[ 'dosya' ]; ?>"
																data-toggle="tooltip"
																data-placement="left"
																title="Dosyayı İndir" target="_blank">
																<i class = "fa fa-download"></i>

															</a>
														</td>
														<td align = "right" width = "5%">
															<a href = "" 
															modul = 'firmaDosyalari' yetki_islem="sil"
															data-href="_modul/firmaDosyalari/firmaDosyalariSEG.php?islem=sil&konu=dosya&dosya_id=<?php echo $dosya[ 'dosya_id' ]; ?>&dosyaTuru_id=<?php echo $dosya[ 'tur_id' ]; ?>"
															data-target="#kayit_sil"
															data-toggle="modal"
															data-toggle="tooltip" 
															data-placement="left" 
															title="Dosyayı Sil">
															<i class = "fa fa-trash color:red"></i>
														</a>
													</td>
												</tr>
												<?php
											}
										} else { ?>
											<h6>Listelenecek kayıt bulunamadı!</h6>
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
<div class="modal fade" id="dosyaTuru" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">Yeni Dosya Türü Ekleme</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<form action="_modul/firmaDosyalari/firmaDosyalariSEG.php" method="post">
					<input type="hidden" name="islem" value="ekle">
					<input type="hidden" name="konu" value="tur">
					<div class="modal-body">
						<div class="form-group">
							<label class="control-label">Başlık</label>
							<input type="text" name="adi" placeholder="Başlık" class="form-control">
						</div>

					</div>
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
						<button type="submit" class="btn btn-success">Kaydet</button>

					</div>
				</form>
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



</script>