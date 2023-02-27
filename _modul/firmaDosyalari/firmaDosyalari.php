<?php
$fn	= new Fonksiyonlar();
$vt = new VeriTabani();

$islem 			= array_key_exists( 'islem', $_REQUEST ) 		? $_REQUEST[ 'islem' ] 			: '';
$dosyaTuru_id 	= array_key_exists( 'dosyaTuru_id', $_REQUEST ) ? $_REQUEST[ 'dosyaTuru_id' ] 	: 0;
$ust_id 		= array_key_exists( 'ust_id', $_REQUEST ) 		? $_REQUEST[ 'ust_id' ] 		: 0;
$linkAltListe 	= array_key_exists( 'alt-liste', $_REQUEST ) 	? $_REQUEST[ 'alt-liste' ] 		: 0;


//Firma_Dosya Trurlerini ve toplasm dosya sayısı ile birlikte listelemek
$SQL_tum_firma_dosyasi_oku = <<< SQL
SELECT
	fdt.id,
	fdt.adi,
	fdt.tarih,
	(
		SELECT COUNT(tb_firma_dosyalari.id) 
		FROM tb_firma_dosyalari 
		WHERE tb_firma_dosyalari.dosya_turu_id = fdt.id 
	) AS dosyaSayisi,
	(
		SELECT count(id) 
		FROM tb_firma_dosya_turleri
		WHERE kategori = fdt.id
	) AS altKategoriSayisi
FROM
	tb_firma_dosya_turleri AS fdt
WHERE 
	firma_id = ? AND 
	kategori = ? 
ORDER BY fdt.adi ASC
SQL;

$SQL_tum_firma_dosyalari = <<< SQL
SELECT
	fdt.id,
	fdt.adi,
	fdt.tarih,
	fdt.kategori,
	(
		SELECT COUNT(tb_firma_dosyalari.id) 
		FROM tb_firma_dosyalari 
		WHERE tb_firma_dosyalari.dosya_turu_id = fdt.id 
	) AS dosyaSayisi,
	(
		SELECT count(id) 
		FROM tb_firma_dosya_turleri
		WHERE kategori = fdt.id
	) AS altKategoriSayisi
FROM
	tb_firma_dosya_turleri AS fdt
WHERE 
	firma_id = ?
ORDER BY fdt.adi ASC
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
	fd.evrakTarihi,
	dt.id AS tur_id,
	dt.adi
FROM
	tb_firma_dosyalari AS fd
INNER JOIN
	tb_firma_dosya_turleri AS dt ON fd.dosya_turu_id = dt.id
WHERE
	dt.id 		= ? AND
	dt.firma_id = ?
ORDER BY dt.adi ASC, fd.aciklama ASC
SQL;

/*ANA KATEGORİLER*/
$SQL_firma_ana_kategori = <<< SQL
SELECT
	*
FROM
	tb_firma_dosya_turleri
WHERE
	firma_id 	= ? AND 
	kategori 	= 0
SQL;



$dosyaTurleri 		= $vt->select( $SQL_tum_firma_dosyasi_oku, 		array( $_SESSION[ 'firma_id' ], 0 ) );
$dosyaTurleri1 		= $vt->select( $SQL_tum_firma_dosyalari, 		array( $_SESSION[ 'firma_id' ] ) );
$dosyaTuru_id		= array_key_exists( 'dosyaTuru_id', $_REQUEST ) ? $_REQUEST[ 'dosyaTuru_id' ] : $dosyaTurleri[ 2 ][ 0 ][ 'id' ];
$tekDosyaTuru		= $vt->select( $SQL_tek_dosya_turu_oku, 		array( $dosyaTuru_id, $_SESSION[ 'firma_id' ] ) ) [ 2 ] ;
$firmaDosyalari		= $vt->select( $SQL_firma_dosyalari, 			array( $dosyaTuru_id, $_SESSION[ 'firma_id' ] ) ) [ 2 ];
$anaKategori		= $vt->select( $SQL_firma_ana_kategori, 		array( $_SESSION[ 'firma_id' ] ) ) [ 2 ];

$fdt 	= array();

foreach ($dosyaTurleri1[2] as $value) {

	if( array_key_exists( $value[ "kategori" ], $fdt )){

		array_push( $fdt[ $value[ "kategori" ] ], $value );
	
	}else{
		$fdt[ $value[ "kategori" ] ] = array();
		array_push( $fdt[ $value[ "kategori" ] ], $value );
	}
}

?>

<style>
	.select2-results__option{
		font-family: "Font Awesome 5 Free", Open Sans !important;
		font-weight:501;
	}
</style>
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
<section class="content" modul = 'firmaDosyalari' yetki_islem="goruntule">
	<div class="container-fluid">
		<div class="row">
			<div class = "col-md-7">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">Firma Dosya Türleri</h3>
						<div class="card-tools">
							<a id="yeni_personel"  title="" href="?modul=firmaDosyalari&islem=ekle" class="btn btn-tool" data-original-title="Yeni Dosya Türü Ekle" ><i class="fas fa-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_personelOzlukDosyalari" class="table table-sm table-bordered table-hover">
							<thead>
								<tr>
									<th>#</th>
									<th>Adı</th>
									<th>Kalan G.S.</th>
									<th>Dosya S.</th>
									<th>Kategori S.</th>
									<th style='width:75px;'>İşlemler</th>
								</tr>
							</thead>
							<tbody>

								<?php 
									/*ftd => firma dosyaları listesi 
									*2. eleman hangi kategiriyi başta listelesin 
									*3. eleman alt kategoriyi sayma 
									*4. eleman çıkan sonuc 
									*5. eleman kategorisi 0 olan elemanları saysını verir
									*6. eleman aktif olan dosya türü idsini verir
									*/
									echo $fn->agacListeleTablo($fdt,0,0,"",0,$dosyaTuru_id,0,array(), $linkAltListe);
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class = "col-md-5">
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
								<div class="form-group">
									<div class="input-group date" id="evrakTarih" data-target-input="nearest">
										<input type="text" name="evrakTarih" class="form-control datetimepicker-input" data-target="#evrakTarih" data-target="#evrakTarih" data-toggle="datetimepicker" placeholder = "Evrak Uyarı Tarihi"/>
									</div>
									<!-- /.input group -->
								</div>
		                        <div class="dropzone" action="_modul/firmaDosyalari/firmaDosyalariSEG.php"  id="dropzone" style="min-height: 236px;">
		                            <div class="dz-message">
		                                <h3 class="m-h-lg">Yüklemek istediğiniz dosyaları buyara sürükleyiniz</h3>
		                                <p class="m-b-lg text-muted">(Yüklemek için dosyalarınızı sürükleyiniz yada buraya tıklayınız)<br>En Fazla 10 Resim Birden Yükleyebilirsiniz</p>
		                            </div>
		                        </div>
		                        <input type="hidden" name="dosyaTuru_id" value="<?php echo $dosyaTuru_id; ?>">
		                        <input type="hidden" name="konu" id="konu" value="dosya">
		                        <a modul = 'firmaDosyalari' yetki_islem="dosya_ekle" href="javascript:void(0);" class="btn btn-outline-info" style="margin-top:10px; width: 100%;" id="submit-all">Yükle</a>
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
														<td>
															<?php
																$suanki_tarih 		= date_create(date('Y-m-d'));
																$hatirlanacak_tarih = date_create($dosya[ 'evrakTarihi' ]);
																if ( $dosya[ 'evrakTarihi' ] != '0000-00-00' ) {
																	$kalan_gun 			= date_diff($suanki_tarih,$hatirlanacak_tarih);
																	$isaret = $kalan_gun->format("%R") == "+" ? 'Kaldı' : 'Geçti';
																	$renk = $kalan_gun->format("%R") == "+" ? 'success' : 'danger';
																	echo "<span class='text-$renk'>".$kalan_gun->format("%a Gün ").$isaret."</span>";
																}
															?>
														</td>
														<td align = "right" width = "5%">
															<a href = "firmaDosyalari/<?php echo $dosyaTuru_id; ?>/<?php echo $dosya[ 'dosya' ]; ?>"
																modul = 'firmaDosyalari' yetki_islem="dosya_indir"
																data-toggle="tooltip"
																data-placement="left"
																title="Dosyayı İndir" target="_blank">
																<i class = "fa fa-download"></i>
															</a>
														</td>
														<td align = "right" width = "5%">
															<a 
															modul = 'firmaDosyalari' yetki_islem="dosya_sil"
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
<div class="modal fade" id="dosyaTuru" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" modul = 'firmaDosyalari' yetki_islem="goruntule" >
	<div class="modal-dialog">
		<div class="modal-content ">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">Yeni Dosya Türü Ekleme</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<form action="_modul/firmaDosyalari/firmaDosyalariSEG.php" method="post">
					<input type="hidden" name="islem" value="<?php echo $islem; ?>">
					<input type="hidden" name="konu" value="tur">
					<input type="hidden" name="dosyaTuru_id" value="<?php echo $dosyaTuru_id; ?>">
					<div class="modal-body">
						<div class="form-group font-awesome">
							<label class="control-label">Kategori</label>
							<select name="kategori" class="form-control  select2 " id="dosyaTuruKategori">
								<option value="0" class="">Kategori Yok</option>
								<?php 
									echo $fn->agacListeleSelect( $fdt, 0, 0,$ust_id );
								?>
							</select>
						</div>

						<div class="form-group">
							<label class="control-label">Başlık</label>
							<input type="text" name="adi" placeholder="Başlık" class="form-control" value="<?php echo $islem == 'guncelle' ?  $tekDosyaTuru[ 0 ][ 'adi' ]: ''; ?>">
						</div>
						
						<div class="form-group">
		                    <label>Evrak Yenileme Tarihi</label>
		                    <div class="input-group date" id="tarih" data-target-input="nearest">
		                      <input type="text" name="tarih" class="form-control datetimepicker-input" data-target="#tarih" value="<?php echo $tekDosyaTuru[ 0 ]["tarih"]; ?>" data-target="#tarih" data-toggle="datetimepicker" placeholder = "Uyarı Tarihi"/>
		                    </div>
		                    <!-- /.input group -->
		                </div>
	              		<!-- /.form group -->
					</div>
						
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
						<button modul="firmaDosyalari" yetki_islem="kaydet" type="submit" class="btn btn-success">Kaydet</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<style type="text/css">
	.custom-menu {
	    z-index:1000;
	    position: absolute;
	    background-color:#fff;
	    border: 1px solid #000;
	    padding: 2px;
	    border-radius: 5px;
		width: 175px;
	}
	.custom-menu a{
		display: block;
		padding: 5px 0 5px 0;
		border-bottom: 1px solid #ddd;
		color: #000;
	}
	.custom-menu a:hover{
		background-color: #ddd;
		transition: initial;
	}
	
</style>
<script  type="text/javascript"> 
	<?php if ( $islem =="guncelle" OR $islem =="ekle" ) {?> $('#dosyaTuru').modal( "show" ); <?php } ?>
	$(function () {
		$('#tarih').datetimepicker({
			//defaultDate: simdi,
			format: 'yyyy.MM.DD',
			locale:'tr',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});
	
	$(function () {
		$('#evrakTarih').datetimepicker({
			//defaultDate: simdi,
			format: 'DD.MM.yyyy',
			locale:'tr',
			icons: {
				time: "far fa-clock",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down"
			}
		});
	});

	$(".mouseSagTik").bind("contextmenu", function(event) {

		
		//Tıklanan tablo tr kategori_id sini al
		var id			= $(this).data("id");
		var kategoriId = $(this).data("kategoriId");
		var altListe 	= $(this).data("alt-liste");
		
		
		$('#dosyaTuruKategori option[value="'+id+'"]').attr('selected','selected');
		var baslik = $('#dosyaTuruKategori option[value="'+id+'"]').text();
		$('#select2-dosyaTuruKategori-container').empty();
		$('#select2-dosyaTuruKategori-container').append(baslik);

		
		//Acılan tüm Menüleri Gizle
		$("div.custom-menu").hide();
		// Genel Sağ Tık Menüsünü Kapat
		event.preventDefault(); 

		$(".mouseSagTik").each(function() {
			$(this).removeClass("table-warning")
		});
		$(this).addClass("table-warning");	
		//Açılacak Div İçeriği
		$("<div class='custom-menu '>"+
			"<a data-toggle='modal' data-target='#dosyaTuru' class='text-center'><i class='fas fa-plus'></i>&nbsp; Kategori Ekle</a>"+
			"<a modul = 'firmaDosyalari' yetki_islem='evraklar' class = 'btn btn-dark btn-xs text-white w-100' href = '?modul=firmaDosyalari&islem=evraklar&ust_id="+kategoriId+"&kategori_id="+id+"&dosyaTuru_id="+id+"&alt-liste="+altListe+" '>Evraklar</a>"+
			"<a modul = 'firmaDosyalari' yetki_islem='duzenle' class = 'btn  btn-warning btn-xs w-100' href = '?modul=firmaDosyalari&islem=guncelle&ust_id="+kategoriId+"&kategori_id="+id+"&dosyaTuru_id="+id+"&alt-liste="+altListe+"' >Düzenle</a>"+
			"<button modul= 'firmaDosyalari' yetki_islem='sil' class='btn btn-xs btn-danger w-100' data-href='_modul/firmaDosyalari/firmaDosyalariSEG.php?islem=sil&konu=tur&dosyaTuru_id="+id+"' data-toggle='modal' data-target='#kayit_sil'>Sil</button>"+
			"</div>").appendTo("body").css({
			top: event.pageY + "px",
			left: event.pageX + "px"
		});
	}).bind("click", function(event) {
		if (!$(event.target).is(".custom-menu")) {
			$("div.custom-menu").hide();
		}
		$(".mouseSagTik").each(function() {
			$(this).removeClass("table-warning")
		});
	});

	$('#tbl_personelOzlukDosyalari').DataTable({
		"paging": true,
		"lengthChange": true,
		"searching": true,
		"ordering": false,
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