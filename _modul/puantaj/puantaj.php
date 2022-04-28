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
@$listelenecekAy	= array_key_exists( 'tarih'	,$_REQUEST ) ? $_REQUEST[ 'tarih' ]	: date("Y-m");
 
$tarih = $listelenecekAy;

$tarihBol = explode("-", $tarih);
$ay = intval($tarihBol[1]);
$yil = $tarihBol[0];

$satir_renk				= $personel_id > 0	? 'table-warning'						: '';
$kaydet_buton_yazi			= $personel_id > 0	? 'Güncelle'							: 'Kaydet';
$kaydet_buton_cls			= $personel_id > 0	? 'btn btn-warning btn-sm pull-right'		: 'btn btn-success btn-sm pull-right';

$SQL_tum_personel_oku = <<< SQL
SELECT
	 p.*
FROM
	tb_personel AS p
WHERE
	firma_id = ? AND p.aktif = 1
SQL;


$SQL_tek_personel_oku = <<< SQL
SELECT
	 p.*
FROM
	tb_personel AS p
WHERE
	p.id = ? AND p.aktif = 1
SQL;

//belirli bir aya göre personelin giriş çıkış hareketleri
//SELECT *, COUNT(tarih) AS tarihSayisi FROM tb_giris_cikis GROUP BY tarih ORDER BY tarih ASC
$SQL_tum_giris_cikis = <<< SQL
SELECT
	id
	,tarih
	,COUNT(tarih) AS tarihSayisi
	
FROM
	tb_giris_cikis
WHERE
	personel_id = ? AND DATE_FORMAT(tarih,'%Y-%m') =? 
GROUP BY tarih
ORDER BY tarih ASC 
SQL;

//Belirli tarihe göre giriş çıkış yapılan saatler 
$SQL_belirli_tarihli_giris_cikis = <<< SQL
SELECT
     baslangic_saat
    ,bitis_saat
    ,maas_kesintisi
	,adi AS islemTipi
FROM
	tb_giris_cikis
LEFT JOIN tb_giris_cikis_tipi ON tb_giris_cikis_tipi.id =  tb_giris_cikis.islem_tipi
LEFT JOIN tb_giris_cikis_tipleri ON tb_giris_cikis_tipleri.id =  tb_giris_cikis_tipi.tip_id
WHERE
	personel_id = ? AND tarih =? 
ORDER BY baslangic_saat ASC 
SQL;


//FirmanınSectiği Giriş Cıkış Tipleri
$SQL_firma_giris_cikis_tipi = <<< SQL
SELECT
	 tip.id
	,tipler.adi
	,maas_kesintisi
FROM
	tb_giris_cikis_tipi AS tip
INNER JOIN tb_giris_cikis_tipleri AS tipler ON tip.tip_id = tipler.id
WHERE 
	tip.firma_id = ?
ORDER BY tipler.adi ASC
SQL;

//Tüm Giriş Çıkış Tipleri
$SQL_tum_giris_cikis_tipleri = <<< SQL
SELECT
tb_giris_cikis_tipleri.id,
tb_giris_cikis_tipleri.adi,
(SELECT tip_id from tb_giris_cikis_tipi WHERE tb_giris_cikis_tipi.tip_id = tb_giris_cikis_tipleri.id AND firma_id = 2) AS varmi
FROM
	tb_giris_cikis_tipleri
ORDER BY adi ASC
SQL;


$personeller				= $vt->select( $SQL_tum_personel_oku, array($_SESSION['firma_id']) );
$personel_id				= array_key_exists( 'personel_id', $_REQUEST ) ? $_REQUEST[ 'personel_id' ] : $personeller[ 2 ][ 0 ][ 'id' ];
$firma_giris_cikis_tipleri	= $vt->select( $SQL_firma_giris_cikis_tipi,array($_SESSION["firma_id"]))[2];
$giris_cikislar				= $vt->select( $SQL_tum_giris_cikis, array($personel_id,$listelenecekAy) )[2];


//Bir günde en fazla kaç giriş çıkış yapıldığını bulma
foreach($giris_cikislar AS $giriscikis){
	$tarihSayisi[] = $giriscikis["tarihSayisi"]; 
}

@$tarihSayisi = max($tarihSayisi); 

?>

<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="container col-sm-12 card" style="display: block; padding: 15px 10px;">
				<div class="col-sm-2" style="float: right;display: flex;">
					<div class="">
						<div class="input-group date" id="datetimepickerAy" data-target-input="nearest">
							<div class="input-group-append" data-target="#datetimepickerAy" data-toggle="datetimepicker">
								<div class="input-group-text"><i class="fa fa-calendar"></i></div>
							</div>
							<input autocomplete="off" type="text" name="tarihSec" class="form-control datetimepicker-input" data-target="#datetimepickerAy" data-toggle="datetimepicker" id="tarihSec" value="<?php if($listelenecekAy) echo $listelenecekAy; ?>"/>
						</div>
					</div>
					<div style="float: right;display: flex;">
						<button class="btn btn-success" id="listeleBtn">listele</button>
					</div>
				</div>
			</div>
			
			<div class="col-12">
				<div class="card card-secondary" id = "card_giriscikislar">
					<div class="card-header">
						<h3 class="card-title">Resül EVİS Puantaj İşlemleri</h3>
						<div class = "card-tools">
							<button type="button" data-toggle = "tooltip" title = "Tam sayfa göster" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand fa-lg"></i></button>
							<a id = "yeni_personel" data-toggle = "tooltip" title = "Yeni bir personel ekle" href = "?modul=personel&islem=ekle" class="btn btn-tool" ><i class="fas fa-user-plus fa-lg"></i></a>
						</div>
					</div>
					<div class="card-body">
						<table id="tbl_giriscikislar" class="table table-bordered table-hover table-sm" width = "100%">
							<thead>
								<tr>
									<th style="width: 15px">#</th>
									<th>Tarih</th>
									<?php
										$i = 1;

										echo $tarihSayisi == 0 ? '<th>İlk Giriş</th><th>Son Çıkış</th>':'';
										while ($i <= $tarihSayisi) {
											
											$thBaslikilk = $i == 1 ? 'İlk Giriş' : 'Giriş';

											$thBaslikSon = $i == $tarihSayisi ? 'Son Çıkış' : 'Çıkış';

											echo '<th>'.$thBaslikilk.'</th><th>'.$thBaslikSon.'</th>';
											$i++;
										}
									?>
									<th>İzin</th>
									<th>Normal Mesai</th>
									<th>%50</th>
									<th>%100</th>
									<th>Hafta Tatili</th>
									<th>Ücretli İzin S.</th>
									<th>Ücretsiz İzin S.</th>
									<th>Toplam Kesinti S.</th>
									<th>Açıklama</th>
								</tr>
							</thead>
							<tbody>
								<?php 

									$gunSayisi = $fn->ikiHaneliVer($ay) == date("m") ? date("d") : date("t",mktime(0,0,0,$ay,01,$yil));

									$sayi = 1; 

									while( $sayi <= $gunSayisi ) { 
									$personel_giris_cikis_saatleri = $vt->select($SQL_belirli_tarihli_giris_cikis,array($personel_id,$tarih.'-'.$sayi))[2];
									$personel_giris_cikis_sayisi   = count($personel_giris_cikis_saatleri);
									$rows = $personel_giris_cikis_sayisi == 0 ?  1 : $personel_giris_cikis_sayisi;

								?>
									<tr>
										<td><?php echo $sayi; ?></td>
										<td><?php echo $sayi.'.'.$fn->ayAdiVer($ay,1).''.$fn->gunVer($tarih.'-'.$sayi); ?></td>
										<?php 
											$i = 1;
											$islemtipi = array();
											if ($personel_giris_cikis_sayisi == 0) {
												$col = ($tarihSayisi*2);
												$col = $col == 0 ? 2 : $col;
												$i = 1;
												while ($i <= $col) { 
													echo '<td class="text-center" >-</td>';
													$i++;
												}
												$islemtipi["gelmedi"] = "Gelmedi"; 
											}
											$giriscikisFarki = $tarihSayisi - $personel_giris_cikis_sayisi;
										
											//uygulanan işlem tipleri
											foreach($personel_giris_cikis_saatleri AS $giriscikis){
												$giriscikis["islemTipi"] != "" ? $islemtipi[] = $giriscikis["islemTipi"] : '';
											}
											$fark["UcretliIzin"] 	= 0;
											$fark["UcretsizIzin"] 	= 0;
											$fark["mesai"] 		= 0;
											//Bir Personel Bir günde en cok giris çıkıs sayısı en yüksek olan tarih ise
											if ($personel_giris_cikis_sayisi ==$tarihSayisi ) {
												foreach($personel_giris_cikis_saatleri AS $giriscikis){
													$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
													$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
													echo '
														<td class="text-center">'.$baslangicSaat.'</td>
														<td class="text-center">'.$bitisSaat.'</td>';

													//Giriş Çıkış Arasındakik Dakika Farkı
													$baslangicSaati = strtotime($baslangicSaat);
													$bitisSaati 	= strtotime($bitisSaat);
													$ToplamDakika 	= ($bitisSaati - $baslangicSaati) / 60;

													if ($giriscikis["islemTipi"] == "") {
														$fark["mesai"] 	+= $ToplamDakika;
													}else{
														//Maaş Kesintisi Yapılıp Yapılmayacağını kontrol ediyoruz
														$giriscikis["maas_kesintisi"] == 1 ? $fark["UcretsizIzin"]  += $ToplamDakika : $fark["UcretliIzin"]  += $ToplamDakika;
													}
													
													
												}
											}else if($personel_giris_cikis_sayisi == 1 ){ // 1 Günde sadece bir kes giriş çıkış yapmıs ise 
												echo '<td class="text-center">'.$personel_giris_cikis_saatleri[0][ 'baslangic_saat' ].'</td>';
												$i = 1;
												while ($i <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
													echo '
														<td class="text-center"> - </td>
														<td class="text-center"> - </td>	
													';
													$i++;
												}
												echo '<td class="text-center">'.$personel_giris_cikis_saatleri[0][ 'bitis_saat' ].'</td>';

												$baslangicSaati = strtotime($personel_giris_cikis_saatleri[0][ 'baslangic_saat' ]);
												$bitisSaati 	 = strtotime($personel_giris_cikis_saatleri[0][ 'bitis_saat' ]);
												$ToplamDakika 	 = ($bitisSaati - $baslangicSaati) / 60;

												if ($personel_giris_cikis_saatleri[0][ 'islemTipi' ] == "") {
													$fark["mesai"] 	+= $ToplamDakika;
												}else{
													//Maaş Kesintisi Yapılıp Yapılmayacağını kontrol ediyoruz
													$giriscikis["maas_kesintisi"] == 1 ? $fark["UcretsizIzin"]  += $ToplamDakika : $fark["UcretliIzin"]  += $ToplamDakika;
												}

											}else{ //Gündee birden fazla giriş çıkış var ise 
												$i = 1;
												foreach($personel_giris_cikis_saatleri AS $giriscikis){
													
													if($i < $personel_giris_cikis_sayisi){

														$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
														$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
														echo '
															<td class="text-center">'.$baslangicSaat.'</td>
															<td class="text-center">'.$bitisSaat.'</td>';
													}else{
														$baslangicSaat = $giriscikis[ 'baslangic_saat' ] == '' ? ' - ' : $giriscikis[ 'baslangic_saat' ];
														$bitisSaat = $giriscikis[ 'bitis_saat' ] == '' ? ' - ' : $giriscikis[ 'bitis_saat' ];
														echo '<td  class="text-center">'.$baslangicSaat.'</td>';
														$j = 1;
														while ($j <= $giriscikisFarki) {//Gün Farkı Kadar Bos Dönderme
															echo '
																<td class="text-center"> - </td>
																<td class="text-center"> - </td>	
															';
															$j++;
														}
														echo '<td class="text-center">'.$bitisSaat.'</td>';
														
														
													}
													$i++;
													$baslangicSaati = strtotime($baslangicSaat);
													$bitisSaati 	= strtotime($bitisSaat);
													$ToplamDakika 	= ($bitisSaati - $baslangicSaati) / 60;

													if ($giriscikis["islemTipi"] == "") {
														$fark["mesai"] 	+= $ToplamDakika;
													}else{
														//Maaş Kesintisi Yapılıp Yapılmayacağını kontrol ediyoruz
														$giriscikis["maas_kesintisi"] == 1 ? $fark["UcretsizIzin"]  += $ToplamDakika : $fark["UcretliIzin"]  += $ToplamDakika;
													}
												}
											}
										?>
										
										<td>
											<?php 
												echo array_key_exists("gelmedi", $islemtipi) ? '<b class="text-center text-danger">Gelmedi</b>' : '<b class="text-center text-warning">'.implode(", ", $islemtipi).'</b>';
												echo count($islemtipi) == 0  ? '<b class="text-center text-success">Mesaide</b>' : '';
											?>
										</td>
										<td>
											<?php 
												if ($fark["mesai"]>0) {
													$saat 	= $fn->ikiHaneliVer(floor($fark["mesai"]/60)); 
													$dakika 	= $fn->ikiHaneliVer($fark["mesai"] % 60); 
													echo $saat.'.'.$dakika; 
												}else{
													echo '-';
												}
											?>
										</td>
										<td >-</td>
										<td>-</td>
										<td><?php echo $fn->gunVer($tarih.'-'.$sayi) == "Pazar" ? '7:30' : 0;?></td>
										<td>
											<?php 
												if ($fark["UcretliIzin"]>0) {
													$saat 	= $fn->ikiHaneliVer(floor($fark["UcretliIzin"]/60)); 
													$dakika 	= $fn->ikiHaneliVer($fark["UcretliIzin"] % 60); 
													echo $saat.':'.$dakika; 
												}else{
													echo '-';
												}
											?>
										</td>
										<td>
											<?php 
												if ($fark["UcretsizIzin"]>0) {
													$saat 	= $fn->ikiHaneliVer(floor($fark["UcretsizIzin"]/60)); 
													$dakika 	= $fn->ikiHaneliVer($fark["UcretsizIzin"] % 60); 
													echo $saat.':'.$dakika;
												}else{
													echo '-';
												}
											?>
											
										</td>
										<td>
											<?php 
												$ToplamKesintiSaati = 540 - $fark["mesai"] + $fark["UcretliIzin"];

												if($ToplamKesintiSaati > 0){
													$saat 	= $fn->ikiHaneliVer(floor($ToplamKesintiSaati/60)); 
													$dakika 	= $fn->ikiHaneliVer($ToplamKesintiSaati % 60); 
													echo $saat.':'.$dakika;
												}else{
													echo '-';
												}

											?>
										</td>
										<td>-</td>
										
									</tr>
								<?php $sayi++; } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>	
		</div>
	</div>
</section>

<script type="text/javascript">


	$(function () {
		$('#datetimepickerAy').datetimepicker({
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

	$("body").on('click', '#listeleBtn', function() {
		var tarih 		= $("#tarihSec").val();
		var  url 			= window.location;
		var origin		= url.origin;
		var path			= url.pathname;
		var search		= (new URL(document.location)).searchParams;
		var modul   		= search.get('modul');
		var detay   		= search.get('detay');
		var personel_id   = search.get('personel_id');
		if(detay == null) {
			detay 	= ''; 
		}else{
			detay  	= "&detay="+detay;
		}
		if(personel_id == null) {
			personel_id 	= ''; 
		}else{
			personel_id  	= "&personel_id="+personel_id;
		}
		
		window.location.replace(origin + path+'?modul='+modul+''+personel_id+''+detay+'&tarih='+tarih);
	})


	var tbl_giriscikislar = $( "#tbl_giriscikislar" ).DataTable( {
		"responsive": true, "lengthChange": true, "autoWidth": true,
		"stateSave": true,
		"pageLength" : 31,
		//"buttons": ["excel", "print"],

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
					return "Giriş Çıkış Bilgileri";
				}
			},
			{
				extend	: 'print',
				text	: 'Yazdır',
				exportOptions : {
					columns : ':visible'
				},
				title: function(){
					return "Giriş Çıkış Bilgileri";
				}
			}
		],
		"columnDefs": [
			{
				"targets" : [],
				"visible" : false
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
	} ).buttons().container().appendTo('#tbl_giriscikislar_wrapper .col-md-6:eq(0)');

</script>
