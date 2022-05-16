Dropzone.options.dropzone = {
    autoProcessQueue: false,
    uploadMultiple:true,
    parallelUploads: 10,
    maxFiles: 10,
    acceptedFiles: ".jpeg,.jpg,.png,.pdf",

    init: function () {

        var submitButton = document.querySelector("#submit-all");
        var wrapperThis = this;

        submitButton.addEventListener("click", function () {
            wrapperThis.processQueue();
        });

        this.on("addedfile", function (file) {

            // Kaldır Butonu Oluşturma
            var removeButton = Dropzone.createElement("<div class'text-center' style='display: block;width: 35px;text-align: center;margin-top: 7px;height: 35px;position: absolute;top: -6px;right: -9px;z-index: 99;'><button style='display:block;width: 26px;border-radius:50%;' class='btn btn-xs btn-danger'>X</button>");

            // Kaldır Butonuna Tıklandığında
            removeButton.addEventListener("click", function (e) {
                // Make sure the button click doesn't submit the form:
                e.preventDefault();
                e.stopPropagation();

                // Remove the file preview.
                wrapperThis.removeFile(file);
                // If you want to the delete the file on the server as well,
                // you can do the AJAX request here.
            });

            // Add the button to the file preview element.
            file.previewElement.appendChild(removeButton);
        });

        this.on('sendingmultiple', function (data, xhr, formData) {
            $("#dropzonform").find("input").each(function(){
                formData.append($(this).attr("name"), $(this).val());
            });
        });
    },
    success: function(file, response){
        var response = JSON.parse(response);
        if ( response.sonuc == 'ok' ){
            mesajVer('Personel İçin Tutanaklar Eklendi', 'yesil');
            
            const yenile = setTimeout(sayfa_yenile, 2500);

            function sayfa_yenile() {
                location.reload();
            }
        }
    }
};