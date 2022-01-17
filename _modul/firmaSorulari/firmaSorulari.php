<?php
$fn = new Fonksiyonlar();

/* SEG dosyalarından gelen mesaj */
if( array_key_exists( 'sonuclar', $_SESSION ) ) {
  $mesaj                 = $_SESSION[ 'sonuclar' ][ 'mesaj' ];
  $mesaj_turu            = $_SESSION[ 'sonuclar' ][ 'hata' ] ? 'kirmizi' : 'yesil';
  $_REQUEST[ 'soru_kategori_id' ] = $_SESSION[ 'sonuclar' ][ 'soru_kategori_id' ];
  unset( $_SESSION[ 'sonuclar' ] );
  echo "<script>mesajVer('$mesaj', '$mesaj_turu')</script>";
}

$SQL_oku = <<< SQL
SELECT
	 sorular.*
	,kategoriler.adi AS kategori_adi
FROM
	tb_sorular AS sorular
LEFT JOIN 
	tb_soru_kategorileri AS kategoriler ON sorular.kategori_id = kategoriler.id
WHERE
	sorular.kategori_id = ?
SQL;

$SQL_secenek_altindaki_sorular = <<< SQL
SELECT
  count(*) sayi
FROM
  tb_sorular
WHERE
  soru_secenek_id = ?
SQL;


$SQL_soru_secenekleri = <<< SQL
SELECT
  *
FROM
  tb_soru_secenekleri
WHERE 
  soru_id = ?
SQL;

$SQL_soru_kategorileri = <<< SQL
SELECT
  *
FROM
  tb_soru_kategorileri
SQL;

$SQL_soru_kategori_adi = <<< SQL
SELECT
   adi
  ,aciklama
FROM
  tb_soru_kategorileri
WHERE 
  id = ?
SQL;

$SQL_soru_cevap_turleri = <<< SQL
SELECT
  *
FROM
  tb_soru_cevap_turleri
SQL;

$SQL_soru = <<< SQL
SELECT
  *
FROM
  tb_sorular
WHERE
  id = ?
SQL;

$SQL_soru_secenek = <<< SQL
SELECT
  *
FROM
  tb_soru_secenekleri
WHERE
  id = ?
SQL;

$id     					= array_key_exists( 'id', $_REQUEST ) ? $_REQUEST[ 'id' ] : 0;
$soru_id     				= array_key_exists( 'soru_id', $_REQUEST ) ? $_REQUEST[ 'soru_id' ] : 0;
$kategori_id     			= array_key_exists( 'kategori_id', $_REQUEST ) ? $_REQUEST[ 'kategori_id' ] : 0;
$secenek_id     			= array_key_exists( 'secenek_id', $_REQUEST ) ? $_REQUEST[ 'secenek_id' ] : 0;

$soru_kategorileri			= $vt->select( $SQL_soru_kategorileri, array() );
$soru_cevap_turleri			= $vt->select( $SQL_soru_cevap_turleri, array() );
$soru			           	= $vt->selectSingle( $SQL_soru, array( $soru_id ) );
$soru_secenek		        = $vt->selectSingle( $SQL_soru_secenek, array( $secenek_id ) );
$kategori		           	= $vt->selectSingle( $SQL_soru_kategori_adi, array( $kategori_id ) );
$kategori_adi				= $kategori[ 2 ][ 'adi' ];
$soru_bilgileri 			= array();
$islem          			= array_key_exists( 'islem', $_REQUEST ) ? $_REQUEST[ 'islem' ] : 'ekle';



if( $islem == 'guncelle' ){
	$soru_bilgileri = $soru[ 2 ];
}

?>
<style>
    .bs-stepper-label {
        font-size: 1.2rem;
        font-weight: 800;
        line-height: 2;
        color: #6c757d;
        font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
    }
</style>
<div class="modal fade" id="kategori_sec_modal">
	<div class="modal-dialog">
	  <div class="modal-content bg-secondary">
		<div class="modal-header">
		  <h6 class="modal-title">Soruları görmek istediğiniz kategoriyi seçiniz</h6>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<form id = "kategori_sec_form" action = "index.php" method = "GET">
			<div class="modal-body">
				<input type = "hidden" name = "modul" value = "soruEkle">
				<input type = "hidden" name = "tab_no" value = "1">
				<div class="form-group">
					<label>Kategori seçiniz</label>
					<select  class="form-control select2" name = "kategori_id" required>
							<option value="">Seçiniz</option>
							<option value="0" <?php if( $kategori_id == 0 ) echo "selected"; ?>>Tümü</option>
					<?php
						function kategoriListele( $kategoriler, $kategori_id, $parent = 0, $tab = 0 ){
							$html = '';

							if( $tab == 0 ) $tabekle = '';
							if( $tab == 1 ) $tabekle = '&emsp;';
							if( $tab == 2 ) $tabekle = '&emsp;&emsp;';
							if( $tab == 3 ) $tabekle = '&emsp;&emsp;&emsp;';
							if( $tab == 4 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;';
							if( $tab == 5 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;&emsp;';
							if( $tab == 6 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;';
							if( $tab == 7 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;';
							if( $tab == 8 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;';
							foreach ($kategoriler as $kategori){
								if( $kategori['ust_id'] == $parent ){
									if( $parent == 0 ) $tab = 0;
									$secim = $kategori['id'] == $kategori_id ? "selected" : "";
									$html .= "<option value='$kategori[id]' $secim >" . $tabekle. "&rarr; " . $kategori['adi']."</option>
									";
									$tab++;
									
									$html .= kategoriListele($kategoriler, $kategori_id, $kategori['id'], $tab);
								}
							}
							return $html;
						}

						echo kategoriListele($soru_kategorileri[ 2 ], $kategori_id);
					?>
					</select>
				</div>
			</div>
			<div class="modal-footer justify-content-between">
			  <button type="button" class="btn btn-outline-light" data-dismiss="modal">Kapat</button>
			  <button  modul= 'soruEkle' yetki_islem="kategori_sec" type="submit" class="btn btn-outline-light">Soruları Listele</button>
			</div>
		</form>
	  </div>
	  <!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php if( !($kategori_id > 0) ){ ?>
<script type="text/javascript">
        //$('#kategori_sec_modal').modal('show');
</script>
<? } ?>


<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="soru_sil_onay">
	<div class="modal-dialog">
	  <div class="modal-content bg-danger">
		<div class="modal-header">
		  <h4 class="modal-title">Lütfen Dikkat!</h4>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
		  <p>Bu kaydı <b>Silmek</b> istediğinize emin misiniz?</p>
		</div>
		<div class="modal-footer justify-content-between">
		  <button type="button" class="btn btn-outline-light" data-dismiss="modal">Kapat</button>
		  <a type="button" class="btn btn-outline-light btn-evet">Evet</a>
		</div>
	  </div>
	  <!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<script>
	$( '#soru_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>

<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="soru_secenek_sil_onay">
	<div class="modal-dialog">
	  <div class="modal-content bg-danger">
		<div class="modal-header">
		  <h4 class="modal-title">Lütfen Dikkat!</h4>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
		  <p>Bu kaydı <b>Silmek</b> istediğinize emin misiniz?</p>
		</div>
		<div class="modal-footer justify-content-between">
		  <button type="button" class="btn btn-outline-light" data-dismiss="modal">Kapat</button>
		  <a type="button" class="btn btn-outline-light btn-evet">Evet</a>
		</div>
	  </div>
	  <!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<script>
	$( '#soru_secenek_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>



  <div class="row">
          <!-- left column -->
		<div class="col-md-4">
			<!-- general form elements -->
			<div class="card card-success">
			  <div class="card-header">
				<h3 class="card-title">Soru Ekleme Aracı</h3>
			  </div>
			  <!-- /.card-header -->
			  <!-- form start -->
			  <form id = "kayit_formu" action = "_modul/soruEkle/soruEkleSEG.php" method = "POST">
				<div class="card-body">
					<?php if( $islem == "secenek_altina_soru_ekle" ){ ?>
					<div class="alert alert-warning alert-dismissible">
					  <h5><i class="icon fas fa-info"></i> Dikkat!</h5>
					  Bu soru aşağıdaki sorunun belirtilen seçeneği altına eklenecektir.
					  <br>
					  <b>Soru : </b><?php echo $soru[ 2 ]['soru']; ?>
					  <br>
					  <b>Seçenek : </b><?php echo $soru_secenek[ 2 ]['secenek']; ?>
					</div>
					<?php } ?>
                    <div class="bs-stepper linear">
                        <div class="bs-stepper-header" role="tablist">
                            <!-- your steps here -->
                            <div class="step active" data-target="#logins-part">
                                <span class="bs-stepper-circle"><b>1</b></span>
                                <span class="bs-stepper-label">Soru</span>
                            </div>
                            <div class="line"></div>
                        </div>
                    </div>
					<input type = "hidden" name = "id" value = "<?php echo $soru_kategori_bilgileri[ 'id' ]; ?>">
					<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
					<div class="form-group">
						<label  class="control-label">Kategori</label>
						<?php if( $islem == "secenek_altina_soru_ekle" ){ ?>
						<input type = "hidden" name = "kategori_id" value = "<?php echo $kategori_id; ?>">
						<input type = "hidden" name = "secenek_id" value = "<?php echo $secenek_id; ?>">
						<select  class="form-control select2" disabled required>
						<?php }else{ ?>
						<select  class="form-control select2" name = "kategori_id" required>
						<?php } ?>
						<option value="">Seçiniz...</option>
						<?php
							function kategoriListele2( $kategoriler, $kategori_id, $parent = 0, $tab = 0 ){
								$html = '';

								if( $tab == 0 ) $tabekle = '';
								if( $tab == 1 ) $tabekle = '&emsp;';
								if( $tab == 2 ) $tabekle = '&emsp;&emsp;';
								if( $tab == 3 ) $tabekle = '&emsp;&emsp;&emsp;';
								if( $tab == 4 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;';
								if( $tab == 5 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;&emsp;';
								if( $tab == 6 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;';
								if( $tab == 7 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;';
								if( $tab == 8 ) $tabekle = '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;';
								foreach ($kategoriler as $kategori){
									if( $kategori['ust_id'] == $parent ){
										if( $parent == 0 ) $tab = 0;
										$secim = $kategori['id'] == $kategori_id ? "selected" : "";
										//$pasif = $kategori['kategori'] == 1 ? "disabled" : "";
										$html .= "<option value='$kategori[id]' $secim $pasif>" . $tabekle. "&rarr; " . $kategori['adi']."</option>
										";
										$tab++;
										
										$html .= kategoriListele2($kategoriler, $kategori_id, $kategori['id'], $tab);
									}
								}
								return $html;
							}
							echo kategoriListele2($soru_kategorileri[ 2 ], $kategori_id);
						?>
						</select>
					</div>
					<div class="form-group">
						<label  class="control-label">Soru</label>
						<input type="text" class="form-control" name ="soru" id="soru" value = "<?php echo $soru_bilgileri[ 'soru' ]; ?>" required placeholder="Soruyu Giriniz">
					</div>
					<div class="form-group">
						<label  class="control-label">Soru Cevap Türü</label>
						<select  class="form-control select2" name = "soru_cevap_turu_id" id = "soru_cevap_turu_id" required>
								<option value="">Seçiniz</option>
							<?php foreach( $soru_cevap_turleri[ 2 ] AS $soru_cevap_turu ) { ?>
								<option value = "<?php echo $soru_cevap_turu[ 'id' ]; ?>" <?php if( $soru_cevap_turu[ 'id' ] ==  $soru_bilgileri[ 'soru_cevap_turu_id' ] ) echo 'selected'?>><?php echo $soru_cevap_turu[ 'adi' ]?></option>
							<?php } ?>
						</select>
						
					</div>
					<br>
                    <div class="bs-stepper linear" id="secenekler_label" style="display :none;">
                        <div class="bs-stepper-header" role="tablist">
                            <!-- your steps here -->
                            <div class="line"></div>
                            <div class="step active" data-target="#logins-part">
                                <span class="bs-stepper-label">Soru Seçenekleri</span>
                                <span class="bs-stepper-circle"><b>2</b></span>
                            </div>
                        </div>
                    </div>
					<div class="form-group" id="soru_secenekler">


					</div>
					<div class='form-group' id = "btn_secenek_ekle" style="display :none;">
						<button type='button' class='btn btn-primary' onclick='secenek_ekle()'>Seçenek Ekle</button>
					</div>
					
					<script>
						function secenek_ekle(){
							secenek_sayisi = parseInt(document.getElementById("secenek_sayisi").value);
							secenek_sayisi +=1;
							document.getElementById("secenek_sayisi").value = secenek_sayisi;
							
							var nesne=document.createElement("div");            
							nesne.setAttribute("id","yeni_secenek"+secenek_sayisi);            
							var yeni_secenek=document.getElementById("soru_secenekler");
							yeni_secenek.appendChild(nesne);
							
							if( parseInt(document.getElementById("soru_cevap_turu_id").value) == 4 )
								secenek = "<div class='input-group mb-3' id='secenek"+secenek_sayisi+"'><div class='input-group-prepend'><span class='input-group-text'><input type='radio' name='radiosecenek'></span></div><input type='text' class='form-control' name='secenekler[]' value='' required><div class='input-group-append'><button type='button' class='btn btn-danger' onclick='secenek_sil("+secenek_sayisi+")'><i class='fas fa-trash-alt'></i></button></div></div>";
							if( parseInt(document.getElementById("soru_cevap_turu_id").value) == 5 )
								secenek = "<div class='input-group mb-3' id='secenek"+secenek_sayisi+"'><div class='input-group-prepend'><span class='input-group-text'><input type='checkbox' ></span></div><input type='text' class='form-control' name='secenekler[]' value='' required><div class='input-group-append'><button type='button' class='btn btn-danger' onclick='secenek_sil("+secenek_sayisi+")'><i class='fas fa-trash-alt'></i></button></div></div>";
							
							document.getElementById('yeni_secenek'+secenek_sayisi).innerHTML = secenek;
							

						}
						
						function secenek_sil(secenek_no){
							secenek_sayisi = parseInt(document.getElementById("secenek_sayisi").value);
							secenek_sayisi -=1;
							document.getElementById("secenek_sayisi").value = secenek_sayisi;
							var elem = document.getElementById("secenek"+secenek_no);
							elem.parentNode.removeChild(elem);														
						}
					</script>
				</div>
				<!-- /.card-body -->
				<div class="card-footer">
				  <button modul= 'soruEkle' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
					<button onclick="window.location.href = '?modul=soruEkle&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
				</div>
			  </form>
			</div>
			<!-- /.card -->
		</div>
          <!--/.col (left) -->
        <div class="col-md-8">
            <div class="card card-dark">
              <div class="card-header">
				<h3 class="card-title float-sm-left">Sorular</b></h3>
                <h3 class="card-title float-sm-right">
					<a href="#" data-toggle="modal" data-target="#kategori_sec_modal"><h3 class="card-title">Kategori : <b><?php if( $kategori_adi !="" ){ ?><?php echo $kategori_adi; ?><?php }elseif($kategori_id == 0){ echo "Tümü"; }else{?>Kategori Seç<?php } ?></b></h3></a>
				</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
			  <?php
				function deneme1($id,$vt,$SQL_soru_secenekleri){
					print_r(deneme2($id,$vt,$SQL_soru_secenekleri));
				}
				function deneme2($id,$vt,$SQL_soru_secenekleri){
					$soru_secenekleri = $vt->select( $SQL_soru_secenekleri, array($id) );
					return $soru_secenekleri[2];
				}
				//deneme1(1,$vt,$SQL_soru_secenekleri);
			  ?>
				<?php
				function soruListele($sorular, $parent = 0,$vt,$SQL_soru_secenekleri,$SQL_secenek_altindaki_sorular){
				 $html = '';
				 if( $parent != 0 ){ 
					 $div_id = "collapse_".$parent; 
				 }
				 if( $parent == 0 ){
					 $collapse_show = "show";
				 }
				 $html .= "
						<div  id='$div_id' class='collapse $collapse_show'>
						

				 ";
				 foreach ($sorular as $soru){
					 if ($soru['soru_secenek_id'] == $parent){
						 $html .= "
						 <div class='card card-secondary ml-4 mr-4'  >
						  <div class='card-header pt-2 pb-2' onmouseover=\"document.getElementById('btn_soru_$soru[id]').style.display = 'block';\" onmouseout=\"document.getElementById('btn_soru_$soru[id]').style.display = 'none';\">
							<h6 class='card-title' style='font-size: 1rem;'><b>Soru : </b>$soru[soru]</h6>
							<div id='btn_soru_$soru[id]' class='btn-group float-sm-right' style='display: none;' role='group' aria-label='Basic example'>
								<button modul = 'soruEkle' yetki_islem='sil' class='btn btn-sm btn-danger btn-xs' data-href='_modul/soruEkle/soruEkleSEG.php?islem=sil&kategori_id=$soru[kategori_id]&id=$soru[id]' data-toggle='modal' data-target='#soru_sil_onay' ><i class='fas fa-trash-alt'></i> Soruyu Sil</button>
							</div>
						  </div>
						  <div class='card-body pt-1 pb-1'>						 
						 ";
						if( $soru['soru_cevap_turu_id'] == 6 ){
							$html .="
								<br>
								<div class='input-group mb-3'>
								  <div class='input-group-prepend'>
									<span class='input-group-text'>Cevabınız</span>
								  </div>
								  <input type='text' class='form-control' placeholder='Cevap bu şekilde text alanına girilecektir.'>			  
								</div>								
							";
						}
						if( $soru['soru_cevap_turu_id'] == 1 or $soru['soru_cevap_turu_id'] == 2 or $soru['soru_cevap_turu_id'] == 3 or $soru['soru_cevap_turu_id'] == 4 or $soru['soru_cevap_turu_id'] == 5 ){
							$soru_secenekleri = $vt->select( $SQL_soru_secenekleri, array($soru['id']) );
							$html .="<ul class='list-group list-group-flush'>";
							foreach( $soru_secenekleri[ 2 ] as $soru_secenek ){
								$secenek_altindaki_soru_sayisi = $vt->selectSingle( $SQL_secenek_altindaki_sorular, array($soru_secenek['id']) );
								$secenek_altindaki_soru_sayisi = $secenek_altindaki_soru_sayisi['2']['sayi'];
								if( $secenek_altindaki_soru_sayisi*1 > 0 ){
									$badge = "&nbsp;&nbsp;&nbsp;<a class='badge badge-primary'  data-toggle='collapse'  href='#collapse_$soru_secenek[id]' role='button' aria-expanded='false' aria-controls='collapse_$soru_secenek[id]'><i class='fas fa-arrow-down'></i></a>";
									$badge2 = "<button  data-toggle='collapse'  href='#collapse_$soru_secenek[id]' role='button' aria-expanded='false' aria-controls='collapse_$soru_secenek[id]' class='btn btn-primary btn-xs '>Seçeneğe bağlı soru gör.</button>";
								}else{
									$badge="";
									$badge2 = "";
								}
								if( $soru['soru_cevap_turu_id'] == 5 ){
									$butonlar="
									<div id='btn_secenek_$soru_secenek[id]' class='btn-group float-sm-right' style='display: none;' role='group' aria-label='Basic example'>
										<button modul = 'soruEkle' yetki_islem='secenek_sil' class='btn btn-sm btn-danger btn-xs ' data-href='_modul/soruEkle/soruEkleSEG.php?islem=secenek_sil&secenek_id=$soru_secenek[id]&kategori_id=$soru[kategori_id]' data-toggle='modal' data-target='#soru_secenek_sil_onay' ><i class='fas fa-trash-alt'></i>Seçenek Sil</button>
									</div>	
									";
									$li = "<li onmouseover=\"document.getElementById('btn_secenek_$soru_secenek[id]').style.display = 'block';\" onmouseout=\"document.getElementById('btn_secenek_$soru_secenek[id]').style.display = 'none';\" class='list-group-item list-group-item-action p-1'>";
									$html .= $li."
										<div class='custom-control custom-checkbox'>
										  <input class='custom-control-input' type='checkbox' name='$soru_secenek[id]' id='secenek_$soru_secenek[id]'>
										  <label for='secenek_$soru_secenek[id]' class='custom-control-label' style='font-weight:normal;font-size: 1rem;'>$soru_secenek[secenek]</label>
										  $badge
										  $butonlar
										</div>
										";
								}else{
									$butonlar="
									<div id='btn_secenek_$soru_secenek[id]' class='btn-group float-sm-right' style='display: none;' role='group' aria-label='Basic example'>
										$badge2
										<button onclick='window.location.href = \"?modul=soruEkle&islem=secenek_altina_soru_ekle&secenek_id=$soru_secenek[id]&soru_id=$soru[id]&kategori_id=$soru[kategori_id]\"' class='btn btn-success btn-xs ' ><span class='fa fa-plus'></span> Seçeneğe soru ekle</button>
										<button modul = 'soruEkle' yetki_islem='secenek_sil' class='btn btn-sm btn-danger btn-xs ' data-href='_modul/soruEkle/soruEkleSEG.php?islem=secenek_sil&secenek_id=$soru_secenek[id]&kategori_id=$soru[kategori_id]' data-toggle='modal' data-target='#soru_secenek_sil_onay' ><i class='fas fa-trash-alt'></i>Seçenek Sil</button>
									</div>	
									";									
									$li = "<li onmouseover=\"document.getElementById('btn_secenek_$soru_secenek[id]').style.display = 'block';\" onmouseout=\"document.getElementById('btn_secenek_$soru_secenek[id]').style.display = 'none';\" class='list-group-item list-group-item-action p-1'>";
									$html .= $li."
										<div class='custom-control custom-radio'>
										  <input class='custom-control-input' type='radio' name='secenek_soru_$soru_secenek[soru_id]' id='secenek_$soru_secenek[id]'>
										  <label for='secenek_$soru_secenek[id]' class='custom-control-label' style='font-weight:normal;font-size: 1rem;'>$soru_secenek[secenek]</label>
										  $badge
										  $butonlar
										</div>
										";									
								}
								$html .= '</li>';
								if( $secenek_altindaki_soru_sayisi*1 > 0 ){
									$html .= soruListele($sorular, $soru_secenek['id'],$vt,$SQL_soru_secenekleri,$SQL_secenek_altindaki_sorular);
								}
							}
							$html .="</ul>";
						}
						 $html .= "
						  </div>
						 </div>
						 ";
					 }
				 }
				 $html .= '</div>';
				 return $html;
				}
				
				function sorulariGetir($kategoriler, $kategori_id, $SQL_oku, $SQL_soru_kategori_adi,$vt,$SQL_soru_secenekleri,$SQL_secenek_altindaki_sorular ){
					$sorular = $vt->select( $SQL_oku, array( $kategori_id ) );
					$sorular = $sorular[ 2 ];
					$kategori2		= $vt->selectSingle( $SQL_soru_kategori_adi, array( $kategori_id ) );
					$kategori_adi	= $kategori2[ 2 ][ 'adi' ];				
					$kategori_aciklama	= $kategori2[ 2 ][ 'aciklama' ];				
					if( $kategori_aciklama != "" )
						echo "<div class='alert alert-warning' role='alert'>$kategori_aciklama</div>";
					echo soruListele($sorular,0,$vt,$SQL_soru_secenekleri,$SQL_secenek_altindaki_sorular);

					function altKategoriSorulariListele( $kategoriler, $parent = 0, $SQL_oku, $SQL_soru_kategori_adi,$vt,$SQL_soru_secenekleri,$SQL_secenek_altindaki_sorular ){
						$html = "";
						foreach ($kategoriler as $kategori){
							if( $kategori['ust_id'] == $parent ){
								$kategori2		= $vt->selectSingle( $SQL_soru_kategori_adi, array( $kategori['id'] ) );
								$kategori_adi	= $kategori2[ 2 ][ 'adi' ];				
								$kategori_aciklama	= $kategori2[ 2 ][ 'aciklama' ];				
								$html .="<div class='card card-primary' ><div class='card-header'><b>Alt Kategori : </b>$kategori_adi</div><div class='card-body'>";
								if( $kategori_aciklama != "" )
									$html .="<div class='alert alert-warning' role='alert'>$kategori_aciklama</div>";						
								$sorular 		= $vt->select( $SQL_oku, array( $kategori['id'] ) );
								$sorular = $sorular[ 2 ];
								
								$html .= soruListele($sorular,0,$vt,$SQL_soru_secenekleri,$SQL_secenek_altindaki_sorular);
								$html .= altKategoriSorulariListele($kategoriler, $kategori['id'], $SQL_oku, $SQL_soru_kategori_adi,$vt,$SQL_soru_secenekleri,$SQL_secenek_altindaki_sorular );
								$html .= "</div></div>";
							}
						}
						
						return $html;
					}
					echo altKategoriSorulariListele($kategoriler, $kategori_id, $SQL_oku, $SQL_soru_kategori_adi,$vt,$SQL_soru_secenekleri,$SQL_secenek_altindaki_sorular );

				}
				
				sorulariGetir($soru_kategorileri[ 2 ], $kategori_id, $SQL_oku, $SQL_soru_kategori_adi,$vt,$SQL_soru_secenekleri,$SQL_secenek_altindaki_sorular );
			
				?>			  

              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>


     
          <!-- right column -->

        </div>
        <!-- /.row -->


