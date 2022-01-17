/*
	Bütün button ve diğer nesnelerin tetiklenmesi burdan yakalanıp ne iş yapılacaksa ona göre parametreleri aja_data.php dosyasına yollar ve sonuç istenilen nesneye yansıtılır.
*/

$( document ).ready( function() {
	/* Yüklenen sayfadaki tüm nesnelerin yetkisini kontrol ederek yetkisiz elemanları sil */
	__F.nesneYetkilendir();

	/**** YETKİLER ****/
	/* ilk etapta zaten seçili olan rol ve modüle ait yetki işlemlerini yükle */
	rolModulYetkiListesiYukle();

	function rolModulYetkiListesiYukle() {
		var rol_id		= $( "#cmb_roller" ).val();
		var modul_id	= $( "#cmb_moduller" ).val();
		
		if( modul_id * 1 > 0 && rol_id * 1 > 0 ) {
			$.ajax( {
				 url	: "_modul/ajax/ajax_data.php"
				,type	: "post"
				,data	: { 
					 modul_id	: modul_id
					,rol_id		: rol_id
					,islem		: 'rol_modul_yetki_islem_oku' 
				}
				,async		: true
				,success	: function( sonuc ) {
					$( "#list_rol_modul_yetki_islemler" ).html( sonuc );
				}
				,error		: function() {
					alert( "Yetki işlemleri yüklenemedi" );
				}
			} );
		}
	}
	/* Roller ve Modüller select boxindan bir değer seçildiğinde hemen altındali cmb_yetki_islemler select boxi dolduruluyor*/
	$( "#cmb_roller,#cmb_moduller" ).each( function() {
		$( this ).change( function() {
			rolModulYetkiListesiYukle();
		} );
	});
	/* Rol yetkilerini kaydeden button */
	$( "#btn_rol_yetki_kaydet" ).click( function() {
		$.ajax( {
			 url	: "_modul/ajax/ajax_data.php"
			,type	: "post"
			,data	: {
				 rol_id			: $( "#cmb_roller" ).val()
				,modul_id		: $( "#cmb_moduller" ).val()
				,islem			: 'rol_modul_yetki_islem_kaydet'
				,yetki_islemler	: $( "#frm_yetki_islemler" ).serialize()
			}
			,async		: true
			,success	: function( sonuc ) {
				sonuc = jQuery.parseJSON( sonuc );
				/* Kaydettikten sonra yetkiler yükledikten sonra mesaj vermesi için  */
				rolModulYetkiListesiYukle( true, sonuc );
				location.reload(); 
			}
			,error		: function() {
				alert( "Yetki işlemleri yüklenemedi" );
			}
		} );
	} );
	
	/* Bildirim deneme*/
	function bildrim_getir() {
		$.ajax( {
			 url	: "_modul/ajax/ajax_data.php"
			,type	: "post"
			,data	: {
				 b_sayisi		: $( "#bildirim_deneme" ).html()
				,islem			: 'bildirim_deneme'
			}
			,async		: true
			,success	: function( sonuc ) {
				var dizi = sonuc.split("~");
				if( dizi[0] >0  )
					$( "#bildirim_deneme" ).html( dizi[0] );
				$( "#bildirimler" ).html( dizi[1] );
				$( "#bildirim_baslik" ).html( dizi[0]+" yeni bildiriminiz var." );
			}
			,error		: function() {
				//alert( "Bildirim yüklenemedi" );
			}
		} );
	}
	bildrim_getir();
	var sss = setInterval( bildrim_getir,10000 );
	/* Bildirim deneme !!!SON!!!*/
	
	/* Sipariş güzergah yükleme modülü*/
		$( "#cmb_siparis_kodlari" ).change( function() {
		$.ajax( {
			 url	: "_modul/ajax/ajax_data.php"
			,type	: "post"
			,data	: {
				 siparis_id		: $( "#cmb_siparis_kodlari" ).val()
				,islem			: 'siparis_guzergahlari_ver'
			}
			,async		: true
			,success	: function( sonuc ) {
				$( "#guzergahlar_div" ).html( sonuc );
			}
			,error		: function() {
				alert( "Güzergah yüklenemedi" );
			}
		} );
	} );
	
	/* Sevkiyat raporlarında seçilen sipariş koduna göre güzergah okuma */
	$( '#rapor_sevkiyat_siparis_kodu' ).on( 'change', function() {
		var siparis_idler = $( this ).val();
		$.ajax( {
			 url	: "_modul/ajax/ajax_data.php"
			,type	: "post"
			,data	: {
				 siparis_idler	: siparis_idler
				,islem			: 'rapor_sevkiyat_guzergahlari_ver'
			}
			,success	: function( sonuc ) {
				$( "#rapor_sevkiyat_guzergahlar" ).html( sonuc );
			}
			,error		: function() {
				alert( "Güzergah yüklenemedi" ); 
			}
		} );
	} );
	
	/* Üretim raporlarında seçilen firma isye göre Lot okuma */
	$( '#rapor_uretim_firmalar' ).on( 'change', function() {
		var firma_idler = $( this ).val();
		$.ajax( {
			 url	: "_modul/ajax/ajax_data.php"
			,type	: "post"
			,data	: {
				 firma_idler		: firma_idler
				,islem			: 'rapor_uretim_firmalara_ait_lotlar_ver'
			}
			,success	: function( sonuc ) {
				$( "#rapor_uretim_lotlar" ).html( sonuc );
				
			}
			,error		: function() {
				alert( "Firmalara ait lotlar yüklenemedi" ); 
			}
		} );
	} );
});