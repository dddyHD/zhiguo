/**
 * Created by Administrator on 2016/12/16 0016.
 */
$('#container > img').cropper({
    aspectRatio: 1 / 1,
});
$('[data-role="change"]').click(function () {
    var result = $('#container > img').cropper('getCroppedCanvas');
    console.log(result);
   var a = $('#container > img').cropper('getImageData')
    console.log(a);
    var data = result.toDataURL();
    var uid = $('[name="uid"]').val();
    var dataUrl = U('Core/File/uploadMobAvatarBase64');
    $.post(dataUrl, {data: data}, function (msg) {
        if (msg.status == 1) {
            window.location.href = U('Ucenter/Index/edit', ['uid/' + uid]);
        }
    }, 'json')


});
$(function () {
    $image = $('#image')
    var $inputImage = $('#inputImage');
    var URL = window.URL || window.webkitURL;
    var blobURL;

    if (URL) {
        $inputImage.change(function () {
            var files = this.files;
            var file;

            if (!$image.data('cropper')) {
                return;
            }

            if (files && files.length) {
                file = files[0];

                if (/^image\/\w+$/.test(file.type)) {
                    blobURL = URL.createObjectURL(file);
                    $image.one('built.cropper', function () {

                        // Revoke when load complete
                        URL.revokeObjectURL(blobURL);
                    }).cropper('reset').cropper('replace', blobURL);
                    $inputImage.val('');
                } else {
                    window.alert('Please choose an image file.');
                }
            }
        });
    } else {
        $inputImage.prop('disabled', true).parent().addClass('disabled');
    }
})