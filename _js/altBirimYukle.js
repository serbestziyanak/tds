$( document ).ready( function() {
	 /* btn-personel-duzenle sahte bir class sadece butona ulaşmak için kullanıldı. id denendi olmadı */
	$( ".btn-personel-duzenle" ).click( function( arguments ) {
		btn			= this;
		personel_id	= btn.value * 1;
		if( personel_id > 0 ) {
			$.ajax({
				 url:"_modul/ajax_data.php"
				,type:"post"
				,data: { personel_id: personel_id, islem : 'duzenlemek_icin_oku' }
				,async:true
				,success: function( sonuc ) {
					$("#kayit_formu").html( sonuc );
				},
				error: function() {
					alert( "Alt birimler yüklenemedi: Ajax işlemler" );
				}
			});
		}
	});
	$( ".btn-personel-sil" ).click( function( arguments ) {
		btn = this;
		console.log( btn.value );
	});
	$( "#personel_birim" ).change( function() {
		
		var birim_id = $( this ).val();
		if( birim_id * 1 > 0 ) {
			$.ajax({
				 url:"_modul/ajax_data.php"
				,type:"post"
				,data: { id: birim_id, islem : 'alt_birim_oku' }
				,async:true
				,success: function( sonuc ) {
					$("#personel_alt_birim").html( sonuc );
				},
				error: function() {
					alert( "Alt birimler yüklenemedi: Ajax işlemler" );
				}
			});
		}
	});
});