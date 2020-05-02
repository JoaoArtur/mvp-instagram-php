$(document).ready(function () {
    $('.post-image[data-img!=""]').each(function (index, elem) {
        $(elem).css('background-image', 'url(' + $(elem).data('img') + ')');
    });
    $(document).on('click', '.likeButton', doLike);
    $(document).on('dblclick', '.post-image', doLike);

    function doLike() {
        var id = $(this).data('idpost');
        var likeBtn = $('.likeButton[data-idpost=' + id + ']');
        var likeBtn1 = likeBtn.children('svg');
        $.ajax({
            url: '/api/like',
            dataType: 'json',
            data: 'id_post=' + id,
            method: 'POST',
            success: function (data) {
                switch (data.status) {
                    case 'likeSuccess':
                        $(likeBtn).append("<i class='fas fa-heart text-danger'></i>");
                        $(likeBtn1[0]).remove();
                        break;
                    case 'deslikeSuccess':
                        $(likeBtn).append("<i class='far fa-heart'></i>");
                        $(likeBtn1[0]).remove();
                        break;
                }

            }
        });
    }
});