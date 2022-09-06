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
  *
FROM
  tb_soru_kategorileri
ORDER BY sira
SQL;

$SQL_soru_kategorileri = <<< SQL
SELECT
  *
FROM
  tb_soru_kategorileri
WHERE
  id = ?
SQL;

$id     									= array_key_exists( 'id', $_REQUEST ) 		? $_REQUEST[ 'id' ] 		: 0;
$ust_id     							= array_key_exists( 'ust_id', $_REQUEST ) ? $_REQUEST[ 'ust_id' ] : 0;
$soru_kategorileri				= $vt->select( $SQL_oku, array() );
$soru_kategori           	= $vt->selectSingle( $SQL_soru_kategorileri, array( $id ) );
$soru_kategori_bilgileri 	= array();
$islem          					= array_key_exists( 'islem', $_REQUEST ) 	? $_REQUEST[ 'islem' ] 	: 'ekle';

if( $islem == 'guncelle' ){
	$soru_kategori_bilgileri = $soru_kategori[ 2 ];
	$ust_id					 				 = $soru_kategori_bilgileri['ust_id'];
}
?>
<!-- UYARI MESAJI VE BUTONU-->
<div class="modal fade" id="soru_kategorileri_sil_onay">
	<div class="modal-dialog">
	  <div class="modal-content bg-danger">
		<div class="modal-header">
		  <h4 class="modal-title">Lütfen Dikkat!</h4>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
		  <p><b>Bu kategoriyi sildiğinizde kategori altındaki alt kategoriler de silinecektir.</b></p>
		  <p>Bu kaydı <b>Silmek</b> istediğinize emin misiniz?</p>
		</div>
		<div class="modal-footer justify-content-between">
		  <button type="button" class="btn btn-outline-light" data-dismiss="modal">İptal</button>
		  <a type="button" class="btn btn-outline-light btn-evet">Evet</a>
		</div>
	  </div>
	  <!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<script>
	$( '#soru_kategorileri_sil_onay' ).on( 'show.bs.modal', function( e ) {
		$( this ).find( '.btn-evet' ).attr( 'href', $( e.relatedTarget ).data( 'href' ) );
	} );
</script>


<script>  
  $(document).ready(function() {
    $('#limit-belirle').change(function() {
		
        $(this).closest('form').submit();
		
    });
});
</script>
  <div class="row">
          <!-- left column -->
		<div class="col-md-4">
			<!-- general form elements -->
			<div class="card card-secondary">
			  <div class="card-header">
				<h3 class="card-title">Kategori Ekle / Güncelle</h3>
			  </div>
			  <!-- /.card-header -->
			  <!-- form start -->
			  <form id = "kayit_formu" action = "_modul/soruKategorileri/soruKategorileriSEG.php" method = "POST">
				<div class="card-body">
					<input type = "hidden" name = "id" value = "<?php echo $soru_kategori_bilgileri[ 'id' ]; ?>">
					<input type = "hidden" name = "islem" value = "<?php echo $islem; ?>">
					<div class="form-group">
						<label  class="control-label">Üst Kategori</label>
						<select  class="form-control select2" name = "ust_id" required>
						<option value="">Seçiniz...</option>
						<option value="0" <?php if( $soru_kategori_bilgileri[ 'ust_id' ] ==0 ) echo "selected" ?>>Üst Kategori Yok</option>
						<?php
							function kategoriListele2( $kategoriler, $ust_id, $parent = 0, $tab = 0 ){
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
										$secim = $kategori['id'] == $ust_id ? "selected" : "";
										$pasif = $kategori['kategori'] == 0 ? "disabled" : "";
										$html .= "<option value='$kategori[id]' $secim $pasif>" . $tabekle. "&rarr; " . $kategori['adi']."</option>
										";
										$tab++;
										
										$html .= kategoriListele2($kategoriler, $ust_id, $kategori['id'], $tab);
									}
								}
								return $html;
							}

							echo kategoriListele2($soru_kategorileri[ 2 ], $ust_id);
						?>
						</select>
					</div>
					<div class="form-group">
						<label  class="control-label">Adı</label>
						<input type="text" class="form-control" name ="adi" value = "<?php echo $soru_kategori_bilgileri[ 'adi' ]; ?>" required placeholder="Kategori adı giriniz">
					</div>
					<div class="form-group card p-2">
						<label  class="control-label">Bu kategori altına alt kategori eklenecek mi ?</label>
						<div class="form-group clearfix">
						  <div class="icheck-success d-inline">
							<input type="radio" id="kategori_evet" value="1" name="kategori" <?php if( $soru_kategori_bilgileri[ 'kategori' ] == 1 ) echo "checked"; ?> required>
							<label for="kategori_evet">
							Evet
							</label>
						  </div>
						  <div class="icheck-danger d-inline">
							<input type="radio" id="kategori_hayir" value="0" name="kategori" <?php if( $soru_kategori_bilgileri[ 'kategori' ] == 0 ) echo "checked"; ?> required>
							<label for="kategori_hayir">
							Hayır
							</label>
						  </div>
						</div>
					</div>
					<div class="form-group">
						<label  class="control-label">Sıra</label>
						<input type="number" class="form-control form-control-sm" name ="sira" id="sira" value = "<?php echo $soru_kategori_bilgileri[ 'sira' ]; ?>" required placeholder="Sırayı Giriniz">
					</div>
					<div class="form-group">
						<label  class="control-label">Açıklama</label>
						<textarea id="summernote" name="aciklama" rows="20"><?php echo $soru_kategori_bilgileri[ 'aciklama' ]; ?></textarea>
					</div>
				</div>
				<!-- /.card-body -->
				<div class="card-footer">
				  <button modul= 'soruKategorileri' yetki_islem="kaydet" type="submit" class="btn btn-success btn-sm pull-right"><span class="fa fa-save"></span> Kaydet</button>
					<button onclick="window.location.href = '?modul=soruKategorileri&islem=ekle'" type="reset" class="btn btn-primary btn-sm pull-right" ><span class="fa fa-plus"></span> Temizle / Yeni Kayıt</button>
				</div>
			  </form>
			</div>
			<!-- /.card -->
		</div>
          <!--/.col (left) -->
        <div class="col-md-8">
            <div class="card card-secondary">
              <div class="card-header">
                <h3 class="card-title">Kategoriler</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
				<?php
					function kategoriListele($kategoriler, $parent = 0, $renk = 0){
						$html = '';
						$html .= '';
						$ml = $renk*25+5;
						$ml = $ml."px";
						
						if( $renk == 0 ) $renkli ="-dark";
						if( $renk == 1 ) $renkli ="-primary";
						if( $renk == 2 ) $renkli ="-success";
						if( $renk == 3 ) $renkli ="-danger";
						if( $renk == 4 ) $renkli ="-warning";
						if( $renk == 5 ) $renkli ="-info";
						if( $renk == 6 ) $renkli ="-secondary";
						if( $renk == 7 ) $renkli ="-light";
						foreach ($kategoriler as $kategori){
							if ($kategori['ust_id'] == $parent){
								$butonlar = "
									<span class='float-sm-right'>
									<button modul = 'soruKategorileri' yetki_islem='sil' class='btn btn-sm btn-danger btn-xs ' data-href='_modul/soruKategorileri/soruKategorileriSEG.php?islem=sil&id=$kategori[id]' data-toggle='modal' data-target='#soru_kategorileri_sil_onay' >Sil</button>
									<button onclick='window.location.href = \"?modul=soruKategorileri&islem=ekle&ust_id=$kategori[id]\"' class='btn btn-primary btn-xs ' ><span class='fa fa-plus'></span></button>
									</span>
								";
								if( $kategori[ 'kategori' ] == 0 ){
								$butonlar2="
								<div id='btn_$kategori[id]' class='btn-group float-sm-right' style='display: none;' role='group' aria-label='Basic example'>
									<button onclick='window.location.href = \"?modul=soruKategorileri&islem=guncelle&id=$kategori[id]\"' class='btn btn-warning btn-xs ' ><span class='fa fa-plus'></span> Düzenle</button>
									<button modul = 'soruKategorileri' yetki_islem='sil' class='btn btn-sm btn-danger btn-xs ' data-href='_modul/soruKategorileri/soruKategorileriSEG.php?islem=sil&id=$kategori[id]' data-toggle='modal' data-target='#soru_kategorileri_sil_onay' ><i class='fas fa-trash-alt'></i> Sil</button>
								</div>	
								";
								}
								if( $kategori[ 'kategori' ] == 1 ){
								$butonlar2="
								<div id='btn_$kategori[id]' class='btn-group float-sm-right' style='display: none;' role='group' aria-label='Basic example'>
									<button onclick='window.location.href = \"?modul=soruKategorileri&islem=ekle&ust_id=$kategori[id]\"' class='btn btn-primary btn-xs ' ><span class='fa fa-plus'></span> Alt kategori ekle</button>
									<button onclick='window.location.href = \"?modul=soruKategorileri&islem=guncelle&id=$kategori[id]\"' class='btn btn-warning btn-xs ' ><span class='fa fa-plus'></span> Düzenle</button>
									<button modul = 'soruKategorileri' yetki_islem='sil' class='btn btn-sm btn-danger btn-xs ' data-href='_modul/soruKategorileri/soruKategorileriSEG.php?islem=sil&id=$kategori[id]' data-toggle='modal' data-target='#soru_kategorileri_sil_onay' ><i class='fas fa-trash-alt'></i> Sil</button>
								</div>	
								";
								}
								$sil_butonu = "";
								$li = "<li style='padding-left:$ml;' onmouseover=\"document.getElementById('btn_$kategori[id]').style.display = 'block';\" onmouseout=\"document.getElementById('btn_$kategori[id]').style.display = 'none';\" class='list-group-item list-group-item-action list-group-item$renkli '>";
								$html .= $li. "<i class='fas fa-arrow-right'></i> " . $kategori['sira'] . " - " . $kategori['adi'].$butonlar2;
								$html .= '</li>';
								$renk2=$renk+1;
								$html .= kategoriListele($kategoriler, $kategori['id'], $renk2);
							}
						}
						$html .= '';
						return $html;
					}

					echo kategoriListele($soru_kategorileri[ 2 ]);
				?>
                <!--table id="example2" class="table table-sm table-bordered table-striped">
                  <thead>
                    <tr>
                      <th style="width: 15px">#</th>
                      <th>Adı</th>
                      <th style="width: 20px">Düzenle</th>
                      <th style="width: 20px">Sil</th>
                    </tr>
                  </thead>
                  <tbody>
				  <?php $sira=1; foreach( $soru_kategorileri[ 2 ] AS $soru_kategori ) { ?>
                  <tr>
                    <td><?php echo $sira++; ?></td>
                    <td><?php echo $soru_kategori[ 'adi' ]; ?></td>
                    <td align = "center">
                      <a modul = 'soruKategorileri' yetki_islem="duzenle" class = "btn btn-sm btn-warning btn-xs" href = "?modul=soruKategorileri&islem=guncelle&id=<?php echo $soru_kategori[ 'id' ]; ?>" >
                        Düzenle
                      </a>
                    </td>
                    <td align = "center">
                      <button modul = 'soruKategorileri' yetki_islem="sil" class="btn btn-sm btn-danger btn-xs" data-href="_modul/soruKategorileri/soruKategorileriSEG.php?islem=sil&id=<?php echo $soru_kategori[ 'id' ]; ?>" data-toggle="modal" data-target="#soru_kategorileri_sil_onay" >Sil</button>
                    </td>
                  </tr>
				  <?php } ?>
                  </tbody>
                </table-->
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>


     
          <!-- right column -->

        </div>
        <!-- /.row -->
<script>
      $('#summernote').summernote({
        placeholder: 'Lütfen açıklama giriniz.',
        tabsize: 2,
		lang: 'tr-TR',
        height: 100
      });
</script>

