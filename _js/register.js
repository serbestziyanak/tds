$(document).ready(function() {
	$('#kayit_formu').bootstrapValidator( {
		message: 'Bu değer geçersiz',
		feedbackIcons: {
/* 			valid: 'glyphicon glyphicon-ok',
			invalid: 'glyphicon glyphicon-remove',
			validating: 'glyphicon glyphicon-refresh'
*/
		},
		fields: {
			/* SİSTEM KULLANICILARI MODÜLÜ*/
			sistem_kullanici_adi		: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			sistem_kullanici_soyadi		: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			sistem_kullanici_email		: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			sistem_kullanici_sifre		: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			sistem_kullanici_rol_id		: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			sistem_kullanici_telefon	: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			yetkiler_rol_adi			: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },

			/* ŞÖFÖRLER MODÜLÜ*/
			sofor_adi					: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			sofor_soyadi				: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			sofor_cep_telefonu			: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			sofor_iban					: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			sofor_firma_id				: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },

			/* ARAÇLAR MODÜLÜ*/
			arac_plaka					: {
				validators : { notEmpty	: { message: 'Boş bırakılamaz' },
					regexp : { regexp	: /[0-9]{2}(([A-Za-z]{1}([0-9]{4}))|([A-Za-z]{2}([0-9]{3,4}))|([A-Za-z]{3}([0-9]{2,3})))$/, message : 'Plaka formatı yanlış.' }
				}
			},
			arac_tasiyici_firma_id		: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },

			/* DEPOLAR MODÜLÜ*/
			depo_adi					: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			depo_adres					: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			
			/* ELEMENTLER MODÜLÜ*/
			elementler_adi				: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			elementler_element_adi		: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			
			/* MADEN CİNSLERİ MODÜLÜ*/
			maden_cinsi_adi				: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },

			/* FİRMALAR */
			firma_adi					: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			menseyi						: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },
			firma_telefon				: { validators : { notEmpty : { message : 'Boş bırakılamaz' } } },









			personel_tcno: { message: '11 Haneli Tc No giriniz',
				validators: { notEmpty: { message: 'Boş bırakılamaz' },
					regexp: { regexp: /^[0-9_\.]+$/, message: 'Lütfen sadece rakam giriniz' },
					stringLength: { min: 11, max: 11, message: 'TC No 11 haneli olmalı' }
				}
			},
			personel_emekli_sicil_no: { message: 'Emekli sşcşl no gşrşn',
				validators: { notEmpty: { message: 'Boş bırakılamaz' },
					regexp: { regexp: /^[0-9_\.]+$/, message: 'Lütfen sadece rakam giriniz' }
				}
			},

			sifre_unuttum_yeni_sifre : {
				validators: {
					notEmpty: {
						message: 'Boş bırakılamaz'
					},
					identical: {
						field: 'sifre_unuttum_yeni_sifre_tekrar',
						message: 'Şifreler uyuşmuyor'
					}
				}
			},
			sifre_unuttum_yeni_sifre_tekrar :{
				validators: {
					notEmpty: {
						message: 'Boş bırakılamaz'
					},
					identical: {
						field: 'sifre_unuttum_yeni_sifre',
						message: 'Şifreler uyuşmuyor'
					}
				}
			},

			/* ÖRNEK */
			ornek : {
				message: 'Kullanıcı adı uygun değil',
				validators: {
					notEmpty: {
						message: 'Boş bırakılamaz'
					},
					stringLength: {
						min: 3,
						max: 15,
						message: 'Kullanıcı adı enaz 3 en çok 15 karakter olabilir'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9]+$/,
						message: 'Kullanıcı adınız sadece harf rakam içerebilir'
					}
				}
			},
			password: {
				validators: {
					notEmpty: {
						message: 'The password is required and can\'t be empty'
					},
					identical: {
						field: 'confirmPassword',
						message: 'The password and its confirm are not the same'
					}
				}
			},
			confirmPassword: {
				validators: {
					notEmpty: {
						message: 'The confirm password is required and can\'t be empty'
					},
					identical: {
						field: 'password',
						message: 'The password and its confirm are not the same'
					}
				}
			}
		}
	});

});