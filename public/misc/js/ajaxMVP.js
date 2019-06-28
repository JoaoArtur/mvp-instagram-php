$(document).ready(function() {
    $('.post-image[data-img!=""]').each(function (index, elem) {
        $(elem).css('background', 'url('+$(elem).data('img')+')');
    });
});