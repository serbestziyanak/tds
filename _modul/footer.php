<footer class="main-footer">
    <strong>Copyright &copy; <?php echo date("Y") ?> <a href="https://syntaxyazilim.com/" target="_blank">SYNTAX YAZILIM</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.1.0-rc
    </div>
</footer>

<?php 

    if ( $_REQUEST['modul'] == "puantaj" OR $_REQUEST['modul'] == "kapatilmisDonem"  ) { 

        /*Personel Beyaz Yakalı Personel İse Maaş hesaplaması yapılmayıp aldığı ucret yazılacaktır.*/

        if ( $tek_personel[ 'grup_id' ] == $beyaz_yakali_personel ) {
            $aylikTutar = $personel_maas;
        }else{

            /*Personelin Kazandığı toplam tutar Maas Hesaplaması*/
            foreach ( $genelCalismaSuresiToplami as $carpan => $dakika ) {
                /* -- Maaş Hesaplasması == ( personelin aylık ucreti / 225 / 60 ) * carpan --*/
                $aylikTutar  += ( $personel_maas / $aylik_calisma_saati / 60 ) * $carpan * $dakika;
            }
            /*Ücreti odenen tatil günlerinin maaşa ekledik.*/
            $aylikTutar +=  ( $personel_maas / $aylik_calisma_saati / 60 ) * 1 * $tatilGunleriToplamDakika;

            /*Alınan ücretli izinleri maasa eklendi. */
            $aylikTutar +=  ( $personel_maas / $aylik_calisma_saati / 60 ) * 1 * $ucretliIzinGenelToplam;

        }

        /*Kazanılan ödemleri ücret üzerine eklemelerini yapıyyoruz*/
        $aylikTutar +=  $kazanilan[ "toplamTutar" ];

        /*Yapılan kesintileri ücret ücretten çıkarıyoruz*/
        $aylikTutar -=  $kesinti[ "toplamTutar" ];

?>
<!-- Control Sidebar -->
<aside class="control-sidebar personel-bilgileri-kapsa" >
        <div class="card card-outline">
            <h2 class="text-danger" style="margin-top: 10px;"><center>Net Ücret</center></h2>
            <h3 class=""><center><?php echo $fn->parabirimi($aylikTutar); ?>TL</center></h3>
            <center>Kazanç ve kesintiler dahildir.</center>
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" src="personel_resimler/<?php echo $tek_personel[ 'resim' ] . '?_dc = ' . time(); ?>" id = "personel_resim" alt="User profile picture">
                </div>
                <h3 class="profile-username text-center"><?php echo $tek_personel[ "adi" ].' '.$tek_personel[ "soyadi" ] ; ?></h3>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Kart No</b> <a class="float-right"><?php echo $tek_personel[ "kayit_no" ]; ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Sicili</b> <a class="float-right"><?php echo $tek_personel[ "sicil_no" ]; ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Şubesi</b> <a class="float-right"><?php echo $tek_personel[ "sube_adi" ]; ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Bölümü</b> <a class="float-right"><?php echo $tek_personel[ "bolum_adi" ]; ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Grubu</b> <a class="float-right"><?php echo $tek_personel[ "grup_adi" ]; ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>İşe Giriş Tarihi</b> <a class="float-right"><?php echo $fn->tarihFormatiDuzelt($tek_personel[ "ise_giris_tarihi" ]); ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>İşten Çıkış Tarihi</b> <a class="float-right"><?php  echo $fn->tarihFormatiDuzelt($tek_personel[ "isten_cikis_tarihi" ]); ?></a>
                    </li>
                </ul>
            </div>
        </div>
</aside>
<!-- /.control-sidebar -->
<script type="text/javascript">
    // ESC tuşuna basınca formu temizle
    document.addEventListener( 'keydown', function( event ) {
        
        if( event.ctrlKey ) {
            if ( event.shiftKey ) {
                document.getElementById( 'sagSidebar' ).click();
            }
        }
    });
</script>

 <?php } ?>
