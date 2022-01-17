$( document ).ready( function() {
	$( ".form-control" ).change( function() {
		if( arguments.length > 0 ) {
			/* GÖREVLENDÝRMELER */
			/* Kapsam */
			if( $( "select[name=kapsam]" ).val() == "yurt_ici" ) {
				$( "select[name=ulke]" ).attr( "disabled", true );
				$( "select[name=sehir]" ).attr( "disabled", false );
				$( "select[name=ulke]" ).val( "0" );
			} if( $( "select[name=kapsam]" ).val() == "yurt_disi" ) {
				$( "select[name=ulke]" ).attr( "disabled", false );
				$( "select[name=sehir]" ).attr( "disabled", true );
				$( "select[name=sehir]" ).val( "0" );
			}
			/* Yolluk Yevmiye */
			if( $( "select[name=yolluk_yevmiye]" ).val() == "35.madde" || $( "select[name=yolluk_yevmiye]" ).val() == "38.madde" ) {
				$( "input[name=gorevlendirme_bitis_tarihi]" ).attr( "disabled", true );
			} else {
				$( "input[name=gorevlendirme_bitis_tarihi]" ).attr( "disabled", false );
			}
		}
	} );
});